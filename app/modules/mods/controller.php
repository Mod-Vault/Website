<?php
namespace modules\mods;
class controller extends \Controller {

	function get() {

		$game_id = $_GET['game_id'];
		if(empty($game_id)) die('No game_id specified');

		$mods = $this->model('Mods');
		$catalog = $this->model('Catalog', 'discover');

		$this->f3->set('data', $mods->get_mods($game_id));
		$this->f3->set('game_data', $catalog->get_game($game_id));

		echo $this->render('index');
	}

	function approve_file() {

		if(!$this->f3->active_user_is_admin) {
			echo 'uh, no';
			die();
		}

		$values = $_GET;
		$this->f3->db->update('mod_attached_files', ['status' => 4], ['uid' => $values['id']]);

		header('Location: ' . $_SERVER['HTTP_REFERER']);
	}

	function details() {

		$mod_catalog_id = $_GET['uid'];

		if(empty($mod_catalog_id)) die('No mod catalog ID given...');

		$mods = $this->model('Mods');

		$mod_info = $mods->get_mod($mod_catalog_id);
		$is_owner = $mod_info['info']['owner'] == $_SESSION['user']['uid'] || $_SESSION['user']['is_admin'];

		if($this->f3->VERB == "POST") {

			$this->requires_account();

			if($is_owner) {

				$update = [];

				if(!empty($_POST['change_description'])) {
					$update['description'] = $_POST['change_description'];
				}

				if(!empty($_POST['change_version'])) {
					$update['current_version'] = $_POST['change_version'];
				}

				if(!empty($_POST['add_changelogs'])) {
					$logs = array_values(array_filter(explode(PHP_EOL, $_POST['add_changelogs'])));
					$mods->post_changelogs($mod_catalog_id, $_POST['version'], $logs);
				}

				if(!empty($_POST['update_type'])) {
					switch($_POST['update_type']) {
						case 'new_version_upload':
							$filehost = $this->model('Filehost');
							$filehost->upload_file($mod_catalog_id, $_POST['version'], $_FILES['host_file'], $_POST['set_current_version']);
							break;
						case 'edit_attached_links':
							$mods->update_mod_links($mod_catalog_id, $_POST['link_file'], $_POST['link_file_description']);
							break;
					}
				}

				if(!empty($update)) {
					$mods->update_mod($mod_catalog_id, $update);
				}

				$this->f3->reroute("/mods/details?uid={$mod_catalog_id}");

			} else {
				die("You don't have permission to do that.");
			}

		}

		$this->f3->set('data', $mod_info ?: $mods->get_mod($mod_catalog_id));
		$this->f3->set('is_owner', $is_owner);
		if($is_owner) {
			$this->f3->set('owner_data', $mods->get_owner_data($mod_catalog_id));
		}

		echo $this->render('details');
		echo $this->render('details_modals', 'templates/blank');
	}

	function add() {

		$this->requires_account();

		if($this->f3->VERB == "POST") {

			$mods = $this->model('Mods');
			if(!$mods->does_mod_exist($_POST['game_id'], $_POST['name'])) {

				$catalog_id = $mods->add_mod($_POST);

				if(!empty($_FILES['host_file']['size'])) {
					$filehost = $this->model('Filehost');
					$filehost->upload_file($catalog_id, $_POST['version'], $_FILES['host_file']);
				}

				$this->f3->reroute('/mods/user?user_id=' . $_SESSION['user']['uid']);
			} else {
				$this->f3->set('site_error', 'This mod with the same name already exists for this game.');
			}

		}

		$catalog = $this->model('Catalog', 'discover');
		$this->f3->set('games', $catalog->get_games());

		echo $this->render('add_mod');

	}

	function user() {

		//list uploaded mods by user by user_id
		$user_id = $_GET['user_id'];

		$mods = $this->model('Mods');

		$this->f3->set('data', $mods->get_user($user_id));

		echo $this->render('user_mods');

	}

	function download() {

		echo "<title>Download File - A4A</title>";

		$file_id = $_GET['file_id'];
		if(empty($file_id)) die('No File ID given...');

		$filehost = $this->model('Filehost');
		$mod_info = $filehost->get_file_info($file_id);

		//any status except for these will not be allowed to be downloaded
		//status 4 is "Available"
		if(!in_array($mod_info['status'], [4])) {
			switch($mod_info['status']) {
				case 1: //pending
				case 2: //scanning
					echo "Sorry, this download is still pending. Please try again in a little while.";
					break;
				case 3: //rejected
					echo "Sorry, this file has been rejected due to possibly being malicious. If you believe this to be a mistake, please contact the developer.";
					break;
				case 5: //removed
					echo "Sorry, this file has been removed either automatically, by the author, or an administrator.";
					break;
			}
			die();
		}

		$file = "{$mod_info['path']}{$mod_info['filename']}";

		if (file_exists($file)) {

		   	header('Content-Description: File Transfer');
		   	header('Content-Type: application/octet-stream');
		   	header('Content-Disposition: attachment; filename='.basename($file));
		   	header('Expires: 0');
		   	header('Cache-Control: must-revalidate');
		   	header('Pragma: public');
		   	header('Content-Length: ' . filesize($file));
		   	ob_clean();
		   	flush();
		   	readfile($file);

			$filehost->log_download($mod_info['uid'], $mod_info['mod_catalog_id']);

		   	exit;
	   } else {
		   	die("File {$mod_info['filename']} could not be found...");
	   }
	}

}
