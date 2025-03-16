<?php
namespace modules\testing;
class controller extends \Controller {

	function __construct() {
		if(!array_key_exists('user', $_SESSION) || !$_SESSION['user']['is_admin']) {
			die('You do not have permission to access this');
		}

		$user = new \User(1, true);
		die("<pre>" . print_r($user,true) . "</pre>");
	}

	function get() {
		echo $this->render('index');
	}

	function user() {

	}

}
