<?php
namespace modules\auth;
class controller extends \Controller {

	function get() {
		echo $this->render('index');
	}

    function post($f3) {
        $user = new \User();
        if($user->GetUserByName($_POST['user']) && $user->ValidateCredentials($_POST['password'])) {
            $this->Login($user, $_POST['keep_logged_in']);
            return;
        } else {
            $f3->set('site_error', 'The login credentials that were entered are invalid');
        }

        $this->get();
    }

    function logout($f3) {
		$f3->db->run("DELETE FROM users_login_tokens WHERE user_id=?", [$f3->active_user->uid]);
        $_SESSION = [];
		$_COOKIE['keep_me_logged_in'] = null;
        $f3->reroute('/');
    }

    function register($f3) {
        if($f3->VERB == "POST") {
            if($_POST['password'] == $_POST['password_verify']) {

                $user = new \User();
                if(($error = $user->Create($_POST)) !== true) {
                    $f3->set('site_error', $error);
                } else {
                    $this->Login($user);
                    $f3->reroute('/');
                }
            } else {
                $f3->set('site_error', 'Passwords did not match');
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

        $this->f3()->reroute('/');
    }

}
