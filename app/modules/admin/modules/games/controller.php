<?php
namespace modules\admin\modules\games;
class controller extends \AdminController {
	function get() {
		echo $this->render('index');
	}
}
