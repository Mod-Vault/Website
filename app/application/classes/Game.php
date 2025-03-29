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

    function Update($data) {
        if(!$this->IsValidEntity()) return;

        $values = $this->CleanData($data, ['name', 'description', 'link']);

        if(array_key_exists('thumbnail', $_FILES) && $_FILES['thumbnail']['size'] > 0) {
            $fh = new Filehost();

            echo "<pre>" . print_r($_FILES,true) . "</pre>";

            $image_url = $fh->UploadImgBB($_FILES['thumbnail']);
            if(!is_array($image_url) || !$image_url['success']) {
                die("<pre>" . print_r(['problem uploading image', $image_url],true) . "</pre>");
            }

            $values['thumbnail'] = $image_url['data']['url'];
        }

        $this->db->update('games', $values, ['uid' => $this->uid]);
    }

    function Create($data) {
        if($this->IsValidEntity()) return;

        $values = $this->CleanData($data, ['name', 'description', 'link']);

        $game_exists = $this->db->column('SELECT uid FROM games WHERE name=?', [$values['name']]);

        if(!empty($game_exists)) {
            return "A game with that name already exists (UID: {$game_exists})";
        }

        if(array_key_exists('thumbnail', $_FILES) && $_FILES['thumbnail']['size'] > 0) {
            $fh = new Filehost();

            $image_url = $fh->UploadImgBB($_FILES['thumbnail']);
            if(!is_array($image_url) || !$image_url['success']) {
                die("<pre>" . print_r(['problem uploading image', $image_url],true) . "</pre>");
            }

            $values['thumbnail'] = $image_url['data']['url'];
        }

        $this->db->insert('games', $values);
        $this->uid = $this->db->lastInsertId();

        $this->GetData();
    }

    function PullData() {
        if(!$this->IsValidEntity()) return false;
        $this->Data = (object)$this->db->row('
            SELECT
                uid,
                thumbnail,
                name,
                link,
                description
            FROM
                games
            WHERE
                uid=?
        ', [$this->uid]);
    }

    function GetGames() {
        return (object)$this->db->all('
            SELECT
                games.*,
                COUNT(mc.uid) as mod_count
            FROM
                games
            LEFT JOIN mod_catalog mc ON mc.game_id=games.uid
            GROUP BY games.uid
        ');
    }
}