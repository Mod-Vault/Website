<?php

class Model {
	
	protected $base, $db;

	function __construct() {
		//This allows you to use `$this->db` to call GrumpyPDO in module models.
		$this->base = Base::instance();
		$this->db = $this->base->db;
	}
	
	

}
