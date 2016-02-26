<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class monitoring{
	public $type = 'MineToolsAPIPing';
	public $connect = false;
	public $loaded = array();

	public function __construct($type='MineToolsAPIPing'){
		$this->type = $type;
	}

	public function loading($type=false){
		if($type===false){ $type = $this->type; }

		if(!file_exists(MCR_MON_PATH.$type.'.php')){ return false; }

		require(MCR_MON_PATH.$type.'.php');

		if(!class_exists($type)){ return false; }

		$this->loaded[$type] = new $type();

		return $this->loaded[$type];
	}

}

?>