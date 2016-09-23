<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $user, $cfg, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->cfg		= $core->cfg;
		$this->lng		= $core->lng_m;

		if(!$this->user->is_auth || !$this->core->is_access('sys_share')){ $this->core->js_notify($this->core->lng['e_403']); }
	}

	public function content(){

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->js_notify($this->lng['e_method']); }

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$login = $this->db->safesql(urldecode(@$_POST['query']));

		$query = $this->db->query("SELECT `{$us_f['login']}` FROM `{$this->cfg->tabname('users')}` WHERE `{$us_f['login']}` LIKE '%$login%' ");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->js_notify($this->lng['ok']); }

		$array = array();

		while($ar = $this->db->fetch_assoc($query)){
			$array[] = $this->db->HSC($ar[$us_f['login']]);
		}
		
		$this->core->js_notify($this->lng['ok'], $this->lng['ok'], true, $array);
	}

}

?>