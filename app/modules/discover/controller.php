<?php
namespace modules\discover;
class controller extends \Controller {

	function get() {

		$catalog = $this->model('Catalog');

		$this->f3->set('games', $catalog->get_games());

		echo $this->render('index');
	}

}
