<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class block_online{
	private $core, $user;

	public function __construct($core){
		$this->core = $core;
		$this->user = $core->user;
	}

	public function content(){

		if(!$this->core->is_access(@$this->core->cfg_b['PERMISSIONS'])){ return; }

		$this->core->header .= $this->core->sp(MCR_THEME_PATH."blocks/online/header.html");

		return $this->core->sp(MCR_THEME_PATH."blocks/online/main.html");
	}
}

?>