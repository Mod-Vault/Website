<?php

ini_set("display_errors",1); ini_set("display_startup_errors",1); error_reporting(E_ALL);

if (!(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' ||
   $_SERVER['HTTPS'] == 1) ||
   isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
   $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'))
{
   $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
   header('HTTP/1.1 301 Moved Permanently');
   header('Location: ' . $redirect);
   exit();
}

ini_set('session.gc_maxlifetime', 315360000);

session_start();

require_once("app/application/vendor/fatfree-core-master/base.php");

//define base
$f3 = Base::instance();

//configs
$f3->config('config.ini.php');
$f3->config('app/application/routes.ini');

$f3->map('/', "modules\\{$f3->get('defaultModule')}\\Controller");

//setup grumpypdo as db
$db = new GrumpyPDO($f3->get('db_host'), $f3->get('db_username'), $f3->get('db_password'), $f3->get('db_database'));
$f3->set('db', $db);

$f3->set('active_user_is_admin', false);
$generate_new_login_token = false;
if(!array_key_exists('user_id', $_SESSION) && !empty($_COOKIE['keep_me_logged_in'])) {
    $token_details = json_decode($_COOKIE['keep_me_logged_in'], true);

    if($token = $db->row("SELECT user_id, hash FROM users_login_tokens WHERE user_id=?", [$token_details['user_id']])) {
        if(password_verify($token_details['hash'], $token['hash'])) {
            $_SESSION['user_id'] = $token['user_id'];
            $generate_new_login_token = true;
        }
    }
}
if(array_key_exists('user_id', $_SESSION)) {
    $f3->set('active_user', $user = new User($_SESSION['user_id'], true));
    $f3->set('active_user_is_admin', $user->IsAdmin);

    if($generate_new_login_token) {
        $user->GenerateLoginToken();
    }
} else {
    $f3->set('active_user', new User());
}


//die("<pre>" . print_r($f3,true) . "</pre>");

//calculate page load time
$f3->set('loadtime', round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3));
$f3->run();
