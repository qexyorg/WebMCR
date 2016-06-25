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
			$this->lng['mod_name'] => BASE_URL."?mode=close"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	public function content(){

		if(!$this->cfg->func['close']){ $this->core->notify(); }

		$time = time();

		if($this->cfg->func['close_time']<=0){
			$for_time = $this->lng['time_for1'];
		}else{
			$for_time = $this->lng['time_for2'].' '.date('H:i:s - d.m.Y', $this->cfg->func['close_time']);
		}

		$data = array(
			'FOR_TIME' => $for_time,
		);

		echo $this->core->sp(MCR_THEME_MOD."close/main.html", $data);

		exit;
	}
}

?>