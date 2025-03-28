<?php
class Tag extends DBEntity {
    function IsTag($tag_id) {
        return $this->uid == $tag_id;
    }

    function Update($data) {
        if(!$this->IsValidEntity()) return;

        $values = $this->CleanData($data, ['name', 'description', 'staff_notes']);

        echo "<pre>" . print_r($values,true) . "</pre>";

        $this->db->update('tags', $values, ['uid' => $this->uid]);

        $this->GetData();
    }

    function Create($data) {
        if($this->IsValidEntity()) return;

        $values = $this->CleanData($data, ['name', 'description', 'staff_notes']);

        $tag_exists = $this->db->column('SELECT uid FROM tags WHERE name=?', [$values['name']]);

        if(!empty($tag_exists)) {
            return "A tag with that name already exists (UID: {$tag_exists})";
        }

        $this->db->insert('tags', $values);
        $this->uid = $this->db->lastInsertId();

        $this->GetData();
    }

    function PullData() {
        if(!$this->IsValidEntity()) return false;
        $this->Data = (object)$this->db->row('
            SELECT
                uid,
                name,
                description,
                staff_notes
            FROM
                tags
            WHERE
                uid=?
        ', [$this->uid]);
    }

    function GetTags() {
        return (object)$this->db->all('
            SELECT
                *
            FROM
                tags
            WHERE
                disabled=0
        ');
    }
}