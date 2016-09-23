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
			$this->lng['mod_name'] => BASE_URL."?mode=banned"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	public function content(){

		$time = time();

		if($this->user->is_banned===false){ $this->core->notify(); }

		$expire = date("d.m.Y - H:i:s", $this->user->is_banned);

		$data = array(
			'EXPIRE' => ($this->user->is_banned<=0) ? $this->lng['ban_forever'] : $this->lng['ban_expired'].' '.$expire,
		);

		echo $this->core->sp(MCR_THEME_MOD."banned/main.html", $data);

		exit;
	}
}

?>