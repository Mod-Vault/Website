<?php
namespace modules\discover;
class controller extends \Controller {

	function get($f3) {

		$catalog = $this->model('Catalog');

		$f3->set('games', $catalog->get_games());

		echo $this->render('index');
	}

}
