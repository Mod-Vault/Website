<?php
abstract class DBEntity extends Model {

    public ?int $uid;
    public stdClass $Data;

    protected bool $dataPulled = false;

    function __construct($uid = null, $pull_data = false) {
        parent::__construct();
        
        $this->uid = $uid;
        if($pull_data) $this->GetData();
    }

    public function GetData(bool $force_pull = false): stdClass 
    {
        if(empty($this->uid)) return null;
        if(!$this->dataPulled || $force_pull) {
            $this->PullData();
            $this->dataPulled = true;
        }

        return $this->Data;
    }

    protected function CleanData($data, $allowed_data_keys) {
        $tmp = [];
        foreach($data as $key => $value) {
            if(in_array($key, $allowed_data_keys)) {
                $tmp[$key] = $value;
            }
        }
        return $tmp;
    }

    //should always be used to fill the $Data property
    abstract protected function PullData();
    abstract public function Create(array $data);
}