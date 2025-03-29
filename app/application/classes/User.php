<?php
class User extends DBEntity {

    public bool $IsAdmin = false;

    function IsUser($user_id) {
        return $this->uid == $user_id;
    }

    function ValidateCredentials($password) {
        if(!$this->IsValidEntity()) return false;
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
        if(!$this->IsValidEntity()) return;
        
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

    function Create($data) {
        if($this->IsValidEntity()) return false;

        $values = $this->CleanData($data, ['display_name', 'email', 'password']);

        if(!array_key_exists('email', $values)) $values['email'] = '';
        $user_exists = $this->db->column('SELECT uid FROM users WHERE display_name=? OR email=? AND email IS NOT NULL', [$values['display_name'], $values['email']]);

        if(!empty($user_exists)) {
            return 'The specified username or email is already in use.';
        }

        if($values['email'] == '') unset($values['email']);
        $values['password'] = password_hash($values['password'], PASSWORD_DEFAULT);

        $this->db->insert('users', $values);
        $this->uid = $this->db->lastInsertId();

        $this->GetData();
        return true;
    }

    function GetMods() {
        if(!$this->IsValidEntity()) return false;
        
        $all_mods = $this->db->all('
            SELECT
                mc.*,
                g.name as game_name
            FROM
                mod_catalog mc
            LEFT JOIN games g ON g.uid=mc.game_id
            WHERE
                mc.owner=?
        ', [$this->uid]);

        $sorted_mods = [];
        foreach($all_mods as $mod) {
            if(!array_key_exists($mod['game_id'], $sorted_mods)) {
                $sorted_mods[$mod['game_id']] = [
                    'game_name' => $mod['game_name']
                ];
            }
            $sorted_mods[$mod['game_id']]['mods'][$mod['uid']] = [
                'name' => $mod['name'],
                'description' => $mod['description'],
                'current_version' => $mod['current_version']
            ];
        }

        return json_decode(json_encode($sorted_mods));
    }

    function PullData() {
        if(!$this->IsValidEntity()) return;

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
        $this->IsAdmin = $this->Data->is_admin == "1";
    }
}