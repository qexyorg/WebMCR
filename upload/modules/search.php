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
			$this->lng['mod_name'] => BASE_URL."?mode=search"
		);
		
		$this->core->bc = $this->core->gen_bc($bc);
	}

	public function content(){
		
		if(!$this->core->is_access('sys_search')){ $this->core->notify($this->core->lng['403'], $this->lng['search_perm'], 1, "?mode=403"); }

		if(!isset($_GET['type']) || !file_exists(MCR_MODE_PATH.'search/'.$_GET['type'].'.php')){ $this->core->notify(); }

		require_once(MCR_MODE_PATH.'search/'.$_GET['type'].'.php');

		$submodule = new submodule($this->core);

		$data['CONTENT'] = $submodule->results();

		return $this->core->sp(MCR_THEME_MOD."search/main.html", $data);
	}
}

?>