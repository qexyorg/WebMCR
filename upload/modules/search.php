<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $config, $user, $lng;

	public function __construct($core){
		$this->core = $core;
		$this->db	= $core->db;
		$this->config = $core->config;
		$this->user	= $core->user;
		$this->lng	= $core->lng;

		$bc = array(
			$this->lng['t_search'] => BASE_URL."?mode=search"
		);

		$this->core->title = $this->lng['t_search'];
		$this->core->bc = $this->core->gen_bc($bc);
	}

	public function content(){
		
		if(!$this->core->is_access('sys_search')){ $this->core->notify($this->lng['e_403'], $this->lng['e_search_perm'], 1, "?mode=403"); }

		if(!isset($_GET['type']) || !file_exists(MCR_MODE_PATH.'search/'.$_GET['type'].'.php')){ $this->core->notify(); }

		require_once(MCR_MODE_PATH.'search/'.$_GET['type'].'.php');

		$submodule = new submodule($this->core);

		$data['CONTENT'] = $submodule->results();

		ob_start();

		echo $this->core->sp(MCR_THEME_MOD."search/main.html", $data);

		return ob_get_clean();
	}
}

?>