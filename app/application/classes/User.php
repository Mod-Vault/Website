<?php
class User extends DBEntity {

    public bool $IsAdmin = false;

    function IsUser($user_id) {
        if($this->uid == null) return false;
        return $this->uid == $user_id;
    }

    function ValidateCredentials($password) {
        if(empty($this->uid)) return false;
        $hash = $this->db->cell('SELECT password FROM users WHERE uid=?', [$this->uid]);
        return password_verify($password, $hash);
    }

    function GetUserByName($name_or_email, $pull_data = false) {
        $this->dataPulled = false;
        $this->uid = $this->db->cell('SELECT uid FROM users WHERE display_name=? OR email=?', [$name_or_email, $name_or_email]);
        if(!empty($this->uid) && $pull_data) {
            $this->GetData(true);
        }

        return !empty($this->uid);
    }

    function GenerateLoginToken() {
        $token = [
            'user_id' => $this->uid,
            'hash' => bin2hex(random_bytes(32))
        ];

        $token_hashed = $token;
        $token_hashed['hash'] = password_hash($token_hashed['hash'], PASSWORD_DEFAULT);

        $this->db->run("DELETE FROM users_login_tokens WHERE user_id=?", [$this->uid]);
        $this->db->insert('users_login_tokens', $token_hashed);
        setcookie("keep_me_logged_in", json_encode($token));
    }

    function CreateUser($display_name, $email, $password) {
        if(!empty($this->Data)) return;
        
        $user_exists = $this->db->column('SELECT uid FROM users WHERE display_name=? OR email=? AND email IS NOT NULL', [$display_name, $email]);
        
        if(!empty($user_exists)) {
            return 'The specified username or email is already in use.';
        }

        $this->db->insert('users', [
            'display_name' => $display_name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
        $this->uid = $this->db->lastInsertId();
        $this->GetData();
        return true;
    }

    function PullData() {
        $this->Data = (object)$this->db->row('
            SELECT
                uid,
                display_name,
                is_admin
            FROM
                users
            WHERE
                uid=?
        ', [$this->uid]);
        
        $this->IsAdmin = $this->Data->is_admin;
    }
}