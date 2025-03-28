<?php
namespace modules\mods;
class controller extends \Controller {

	function list($f3, $params) {

		$game_id = $params['resource_id'];
		if(empty($game_id)) die('No game_id specified');

		$game = new \Game($game_id, true);

		$f3->set('game', $game);

		echo $this->render('list');
	}

	function approve_file($f3) {

		if(!$f3->active_user->IsAdmin) {
			echo 'uh, no';
			die();
		}

		$values = $_GET;
		$file_data = $f3->db->row('SELECT mod_catalog_id, version, set_new_version_on_approval FROM mod_attached_files WHERE uid=?', [$values['id']]);

		$f3->db->update('mod_attached_files', ['status' => 4], ['uid' => $values['id']]);

		if($file_data['set_new_version_on_approval']) {
			$f3->db->update('mod_catalog', ['current_version' => $file_data['version']], ['uid' => $file_data['mod_catalog_id']]);
		}

		header('Location: ' . $_SERVER['HTTP_REFERER']);
	}

	function details($f3, $params) {

		$mod_id = $params['resource_id'];
		if(empty($mod_id)) die('No mod ID given...');

		$mod = new \Mod($mod_id, true);
		$is_owner = $f3->active_user->IsUser($mod->Data->owner) || $f3->active_user->IsAdmin;

		$f3->set('mod', $mod);
		$f3->set('is_owner', $is_owner);

		if($f3->VERB == "POST") {

			$this->requires_account();

			if($is_owner) {
				$mod->Update($_POST);
				$f3->reroute("/mods/details/{$mod_id}");
			} else {
				die("You don't have permission to do that.");
			}
		}

		if($is_owner) {
			$f3->set('owner_data', $mod->GetRestrictedData());
		}

		echo $this->render('details');
		echo $this->render('details_modals', 'templates/blank');
	}

	function add($f3) {

		$this->requires_account();

		if($f3->VERB == "POST") {
			$mod = new \Mod();
			if(($error = $mod->Create($_POST)) !== true) {
				$f3->set('site_error', $error);
			} else {
				if(!empty($_FILES['host_file']['size'])) {
					$filehost = new \Filehost();
					$filehost->UploadFile($mod->uid, $mod->Data->current_version, $_FILES['host_file'], 1);
				}

				$f3->reroute('/mods/details/' . $mod->uid);
			}
		}

		$catalog = $this->model('Catalog', 'discover');
		$f3->set('games', $catalog->get_games());

		echo $this->render('add_mod');

	}

	function user($f3, $params) {
		$f3->set('user', new \User($params['resource_id'], true));
		echo $this->render('user_mods');
	}

	function download() {

		$file_id = $_GET['file_id'];
		if(empty($file_id)) die('No File ID given...');

		$filehost = new \Filehost();
		$mod_info = $filehost->GetFileInfo($file_id);

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

			$filehost->LogDownload($mod_info['uid'], $mod_info['mod_catalog_id']);

		   	exit;
	   } else {
		   	die("File {$mod_info['filename']} could not be found...");
	   }
	}

}
