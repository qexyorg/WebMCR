<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class block_banner{
	private $core;

	public function __construct($core){
		$this->core = $core;
	}

	public function content(){
		if(!$this->core->is_access("sys_adm_main")){ return; }

		return $this->core->sp(MCR_THEME_PATH."blocks/banner/main.html");
	}
}

?>