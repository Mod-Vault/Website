<?php
class Game extends DBEntity {

    function IsGame($game_id) {
        return $this->uid == $game_id;
    }

    function GetMods() {
        if(!$this->IsValidEntity()) return false;

        $all_mods = $this->db->all('
            SELECT
                mc.*,
                u.display_name as author
            FROM
                mod_catalog mc
            LEFT JOIN users u ON u.uid=mc.owner
            WHERE
                mc.game_id=?
        ', [$this->uid]);

        return json_decode(json_encode($all_mods));
    }

    function Create($data) {
        if($this->IsValidEntity()) return;
    }

    function PullData() {
        if(!$this->IsValidEntity()) return false;
        $this->Data = (object)$this->db->row('
            SELECT
                uid,
                thumbnail,
                name
            FROM
                games
            WHERE
                uid=?
        ', [$this->uid]);
    }
}