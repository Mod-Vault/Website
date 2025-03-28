<?php
namespace modules\admin\modules\games;
class controller extends \AdminController {
	function get() {
		echo $this->render('index');
	}
	function edit($f3, $params) {

		$game = !empty($params['resource_id']) ? new \Game($params['resource_id'], true) : new \Game();

		if($f3->VERB == "POST") {
			if(empty($game->Data)) {
				if(($error = $game->Create($_POST)) !== true) {
                    $f3->set('site_error', $error);
                }
			} else {
				$game->Update($_POST);
			}

			if(empty($f3->site_error))
				$f3->reroute('admin/games/edit/' . $game->uid);
		}

		$f3->set('game', $game);

		echo $this->render('edit');
	}
	function get_tags($f3) {
        echo json_encode((array)(new \Game())->GetGames());
    }
}
