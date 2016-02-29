<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $config, $user, $lng;
	public $cfg = array();

	public function __construct($core){
		$this->core = $core;
		$this->db	= $core->db;
		$this->config = $core->config;
		$this->user	= $core->user;
		$this->lng	= $core->lng_m;

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=admin"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$this->core->header .= '<script src="'.STYLE_URL.'js/admin/global.js"></script>';
	}

	public function content(){
		if(!$this->core->is_access('sys_adm_main')){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$do = (isset($_GET['do'])) ? $_GET['do'] : 'panel_menu';

		if(!preg_match("/^[\w\.\-]+$/i", $do)){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		if(!file_exists(MCR_MODE_PATH.'admin/'.$do.'.class.php')){
			$this->core->notify($this->core->lng['404'], $this->core->lng['e_404']);
		}

		require_once(MCR_MODE_PATH.'admin/'.$do.'.class.php');

		if(!class_exists('submodule')){
			$this->core->notify($this->core->lng['404'], $this->core->lng['e_404']);
		}
		
		$submodule = new submodule($this->core);

		return $submodule->content();
	}
}

?>