<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class block_profile{
	private $core, $user;

	public function __construct($core){
		$this->core = $core;
		$this->user = $core->user;
	}

	public function content(){
		$authfile = (!$this->user->is_auth) ? "unauth" : "auth";

		$this->core->header .= $this->core->sp(MCR_THEME_PATH."blocks/profile/header.html");

		return $this->core->sp(MCR_THEME_PATH."blocks/profile/$authfile.html");
	}
}

?>