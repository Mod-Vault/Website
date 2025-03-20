<?php
namespace modules\admin;
class controller extends \Controller {
	function __construct($f3, $params) {
		parent::__construct($f3, $params);

		if($f3->active_user->uid == null || !$f3->active_user->IsAdmin) {
			die('You do not have permission to do that.');
		}
	}
	function get() {
        echo $this->render('index');
	}
}
