<?php
class Mod extends DBEntity {

    function IsMod($mod_id) {
        if($this->uid == null) return false;
        return $this->uid == $mod_id;
    }

    function Create($data) {
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