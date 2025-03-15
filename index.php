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

//$f3->set('a4a_referer', preg_match("/https:\/\/{$_SERVER[HTTP_HOST]}/", $_SERVER[HTTP_REFERER]));

if(empty($_SESSION['user']) && !empty($_COOKIE['keep_me_logged_in'])) {
    $token_details = json_decode($_COOKIE['keep_me_logged_in'], true);

    if($token = $db->row("SELECT user_id, hash FROM users_login_tokens WHERE user_id=? AND hash=?", [$token_details['user_id'], $token_details['hash']])) {

        //log in user
        $user = $db->row('SELECT * FROM users WHERE uid=?', [$token['user_id']]);
        unset($user['password']);
        $_SESSION['user'] = $user;

        //regenerate new login token
        $token = [
            'user_id' => $_SESSION['user']['uid'],
            'hash' => password_hash($_SESSION['user']['uid'], PASSWORD_DEFAULT)
        ];

        $db->run("DELETE FROM users_login_tokens WHERE user_id=?", [$token['user_id']]);
        $db->insert('users_login_tokens', $token);
        setcookie("keep_me_logged_in", json_encode($token));

    }

}

//calculate page load time
$f3->set('loadtime', round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3));
$f3->run();
