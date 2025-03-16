<?php
namespace modules\auth;
class controller extends \Controller {

	function get() {
		echo $this->render('index');
	}

    function post() {
        $user = new \User();
        if($user->GetUserByName($_POST['user']) && $user->ValidateCredentials($_POST['password'])) {
            $this->Login($user);
            return;
        } else {
            $this->f3->set('site_error', 'The login credentials that were entered are invalid');
        }

        $this->get();
    }

    function logout() {
		$this->f3->db->run("DELETE FROM users_login_tokens WHERE user_id=?", [$this->f3->active_user->uid]);
        $_SESSION = [];
		$_COOKIE['keep_me_logged_in'] = null;
        $this->f3->reroute('/');
    }

    function register() {
        if($this->f3->VERB == "POST") {
            if($_POST['password'] == $_POST['password_verify']) {

                $user = new \User();
                if(($error = $user->CreateUser($_POST['display_name'], $_POST['email'], $_POST['password'])) !== true) {
                    $this->f3->set('site_error', $error);
                } else {
                    $this->Login($user);
                    $this->f3->reroute('/');
                }
            } else {
                $this->f3->set('site_error', 'Passwords did not match');
            }
		}

        echo $this->render('register');
    }

    private function Login(\User $user, bool $keep_logged_in = false) {
        if(empty($user->uid)) return;

        $user->GetData();

        $_SESSION['user_id'] = $user->uid;

        if($keep_logged_in) {
            $user->GenerateLoginToken();
        }

        $this->f3->reroute('/');
    }

}
