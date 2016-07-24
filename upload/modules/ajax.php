<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $user, $cfg, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->cfg		= $core->cfg;
		$this->lng		= $core->lng_m;
	}

	private function load_hook($param){
		if(!empty($hook) || !preg_match("/^[\w\|]+$/i", $param)){ return false; }

		$pathlist = explode('|', $hook);

		$hookpath = MCR_MODE_PATH.'ajax/'.implode('/', $pathlist).'.class.php';

		if(!file_exists($hookpath)){ return false; }

		require_once($hookpath);

		if(!class_exists('hook')){ return false; }

		return new hook($this->core);
	}

	public function content(){
		
		$ajax = (isset($_GET['do'])) ? $_GET['do'] : '';

		$hook = (isset($_GET['hook'])) ? $_GET['hook'] : '';

		$list = explode('|', $ajax);
		$path = implode('/', $list);

		if(!preg_match("/^[\w\|]+$/i", $ajax) || !file_exists(MCR_MODE_PATH.'ajax/'.$path.'.php')){
			$this->core->js_notify('Hacking Attempt!');
		}

		require_once(MCR_MODE_PATH.'ajax/'.$path.'.php');

		if(!class_exists("submodule")){ $this->core->js_notify($this->lng['class_not_found']); }

		$this->core->hook = $this->load_hook($hook);

		$submodule = new submodule($this->core);
		
		if(!method_exists($submodule, "content")){ $this->core->js_notify($this->lng['method_not_found']); }

		return $submodule->content();
	}

}

?>