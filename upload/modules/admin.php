<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $cfg, $user, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;
		$this->user		= $core->user;
		$this->lng		= $core->lng_m;

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$this->core->header .= $this->core->sp(MCR_THEME_PATH."modules/admin/header.html");
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

		$this->core->lng_m = $this->core->load_language('admin/'.$do);
		
		$submodule = new submodule($this->core);

		return $submodule->content();
	}
}

?>