<?php
class Mod extends DBEntity {

    function IsMod($mod_id) {
        return $this->uid == $mod_id;
    }

    function Update($data) {
        if(!$this->IsValidEntity()) return;

        $update_type = array_key_exists('update_type', $data) ? $data['update_type'] : null;

        $values = $this->CleanData(
            $data,
            ['description', 'change_version', 'add_changelogs', 'changelog_version', 'set_current_version'],
            [
                'link_file' => ['link_file_description', 'link_file', 'required']
            ]
        );

        if(array_key_exists('change_version', $values)) {
            $values['current_version'] = $values['change_version'];
        }

        if(!empty($values['add_changelogs'])) {
            $this->PostChangelogs($values['changelog_version'],  array_values(array_filter(explode(PHP_EOL, $values['add_changelogs']))));
        }

        switch($update_type) {
            case 'new_version_upload':
                $filehost = new \Filehost();
                $filehost->UploadFile($this->uid, $values['changelog_version'], $_FILES['host_file'], $values['set_current_version']);
                break;
            case 'edit_attached_links':
                $this->UpdateModLinks($values['link_file']);
                break;
        }

        $values = $this->CleanData($values, ['description', 'current_version']);

        if(!empty($values))
            $this->db->update('mod_catalog', $values, ['uid' => $this->uid]);
    }

    function Create($data) {
        if($this->IsValidEntity()) return;

        $values = $this->CleanData(
            $data, 
            ['game_id', 'name', 'current_version', 'add_changelogs', 'description'],
            [
                'link_file' => ['link_file_description', 'link_file', 'required']
            ]
        );

        $mod_exists = $this->db->column('SELECT uid FROM mod_catalog WHERE name=? AND game_id=?', [$values['name'], $values['game_id']]);

        if(!empty($mod_exists)) {
            return 'A mod with that name already exists for this game';
        }

        $changelogs = $values['add_changelogs'];
        $mod_links = $values['link_file'];
        unset($values['link_file']);
        unset($values['add_changelogs']);

        $this->db->insert('mod_catalog', $values);
        $this->uid = $this->db->lastInsertId();

        $this->Update([
            'link_file' => $mod_links,
            'add_changelogs' => $changelogs
        ]);

        $this->GetData();
        return true;
    }

    private function PostChangelogs($version, $logs) {
        if(!$this->IsValidEntity()) return;
        foreach($logs as $log) {
            if(empty($log)) continue;
            $this->db->insert('mod_catalog_changelogs', [
                'mod_catalog_id' => $this->uid,
                'version' => $version,
                'log' => $log
            ]);
        }
    }

    public function UpdateModLinks($links) {
        if(!$this->IsValidEntity()) return;
        $this->db->run('DELETE FROM mod_attached_links WHERE mod_catalog_id=?', [$this->uid]);
        foreach($links ?: [] as $key => $link_data) {
            $this->db->insert('mod_attached_links', [
                'mod_catalog_id' => $this->uid,
                'href' => $link_data['link_file'],
                'description' => $link_data['link_file_description'],
                'required' => $link_data['required']
            ]);
        }
    }

    public function GetRestrictedData() {
        if(!$this->IsValidEntity()) return;
        return [
            'pending_rejected_files' => $this->db->all("
                SELECT
                    mod_attached_files.*,
                    attached_file_status.description as status_description
                FROM
                    mod_attached_files
                LEFT JOIN attached_file_status ON attached_file_status.uid=mod_attached_files.status
                WHERE
                    mod_catalog_id=?
                    AND status IN (1,2,3,5)
            ", [$this->uid]),
            'all_versions' => $this->db->column("SELECT version FROM mod_attached_files WHERE mod_catalog_id=?", [$this->uid])  + [$this->db->cell("SELECT current_version FROM mod_catalog WHERE uid=?", [$this->uid])]
        ];
    }

    function PullData() {
        if(!$this->IsValidEntity()) return;

        $data = $this->db->row('
            SELECT
                mc.uid,
                mc.game_id,
                g.name as game_name,
                mc.owner,
                u.display_name as owner_name,
                mc.name,
                mc.description,
                mc.current_version
            FROM
                mod_catalog mc
            LEFT JOIN users u ON u.uid=mc.owner
            LEFT JOIN games g ON g.uid=mc.game_id
            WHERE
                mc.uid=?
        ', [$this->uid]);

        $data['current_file'] = [];
        $data['other_files'] = [];
        $data['mod_links'] = [];
        $data['changelogs'] = [];

        $data['changelogs'] = $this->db->run("SELECT version, log FROM mod_catalog_changelogs WHERE mod_catalog_id=? ORDER BY version DESC, uid ASC", [$this->uid])->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_COLUMN);
        $data['mod_links'] = $this->db->keypairs("SELECT uid, href, description, required FROM mod_attached_links WHERE mod_catalog_id=? ORDER BY required DESC, description ASC", [$this->uid], true);

        $active_files = $this->db->all("SELECT * FROM mod_attached_files WHERE status=4 AND mod_catalog_id=? ORDER BY version DESC", [$this->uid]);

        foreach($active_files as $file) {
            if($file['version'] == $data['current_version']) {
                $data['current_file'] = $file;
                continue;
            }

            $data['other_files'][$file['version']] = $file;
        }

        $this->Data = json_decode(json_encode($data));
    }
}