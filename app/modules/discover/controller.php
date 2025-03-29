<?php
namespace modules\discover;
class controller extends \Controller {

	function get($f3) {
		$f3->set('games', (new \Game())->GetGames());

		echo $this->render('index');
	}

	function terms() {
		echo $this->render('terms');
	}

	function privacy() {
		echo $this->render('privacy');
	}

}
