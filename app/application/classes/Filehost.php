<?php
class Filehost extends \Model {

    public function LogDownload($mod_attached_file_id, $game_catalog_id) {
        $this->db->insert('mod_file_downloads', [
            'game_catalog_id' => $game_catalog_id,
            'mod_attached_file_id' => $mod_attached_file_id
        ]);
    }

    public function GetFileInfo($file_id) {
        return $this->db->row('SELECT * FROM mod_attached_files WHERE uid=?', [$file_id]);
    }

    public function UploadFile($game_catalog_id, $version, $file, $set_new_version = 0) {

        $nfo = $this->db->row("
            SELECT
                m.game_id, m.name, g.name as game_name
            FROM mod_catalog m
            LEFT JOIN games g ON g.uid=m.game_id
            WHERE m.uid=?", [$game_catalog_id]);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

        $allowed_exts = ['zip'];
        if(!in_array($ext, $allowed_exts)) {
            die('Could not upload mod. We only accept these file extensions: ' . implode(', ', $allowed_exts) . '<br>If you need an exception to be made, please contact the developer.');
        }

        $max_mb = 100;
        if($file['size'] > $max_mb * 1048576) {
            die("Could not upload mod. We only accept files under {$max_mb}MB. If you need an exception to be made, please contact the developer.");
        }

        $upload_path = "uploads/{$nfo['game_name']}/{$nfo['name']}/";
        $storage_name = "{$nfo['name']}-{$version}.{$ext}";

        if(!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $full_path = "{$upload_path}{$storage_name}";
        $moved = move_uploaded_file($file['tmp_name'], $full_path);

        if($moved) {
            $this->db->insert('mod_attached_files', [
                'mod_catalog_id' => $game_catalog_id,
                'path' => $upload_path,
                'filename' => $storage_name,
                'version' => $version,
                'set_new_version_on_approval' => $set_new_version
            ]);
        }

    }

    public function UploadImgBB($image, $name = null) {

        $f3 = Base::instance();
        if(empty($f3->imgbb_api_key)) return "No API Key";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload?key=' . $f3->imgbb_api_key);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
        $file_name = ($name) ? $name . '.' . $extension : $image['name'];
        $data = array('image' => base64_encode(file_get_contents($image['tmp_name'])), 'name' => $file_name);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return 'Error:' . curl_error($ch);
        } else {
            return json_decode($result, true);
        }
        curl_close($ch);
    }
}
