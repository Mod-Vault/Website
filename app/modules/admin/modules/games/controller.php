<?php
namespace modules\admin\modules\games;
class controller extends \AdminController {
	function get() {
		echo $this->render('index');
	}
	function edit($f3) {

		$game = !empty($params['resource_id']) ? new \Game($params['resource_id'], true) : new \Game();

		$f3->set('game', $game);

		echo $this->render('edit');
	}
}
