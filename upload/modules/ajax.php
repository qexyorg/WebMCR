<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $user, $config, $lng;
	public $cfg = array();

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->config	= $core->config;
		$this->lng		= $core->lng_m;
	}

	public function content(){
		
		$ajax = (isset($_GET['do'])) ? $_GET['do'] : '';

		if(!preg_match("/^[\w\.]+$/i", $ajax) || !file_exists(MCR_MODE_PATH.'ajax/'.$ajax.'.php')){
			$this->core->js_notify('Hacking Attempt!');
		}

		require_once(MCR_MODE_PATH.'ajax/'.$ajax.'.php');

		if(!class_exists("submodule")){ $this->core->js_notify($this->lng['class_not_found']); }

		$submodule = new submodule($this->core);
		
		if(!method_exists($submodule, "content")){ $this->core->js_notify($this->lng['method_not_found']); }

		return $submodule->content();
	}

}

?>