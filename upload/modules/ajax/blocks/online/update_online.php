<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $user, $db, $cfg;

	public function __construct($core){
		$this->core		= $core;
		$this->user		= $core->user;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;

		include(MCR_CONF_PATH.'blocks/online.php');

		$this->core->cfg_b = $cfg;
	}

	public function content(){

		$online = ($this->user->is_auth) ? 1 : 0;

		$time = time();

		$expire = $time-$this->core->cfg_b['TIMEOUT'];

		$id = ($this->user->is_auth) ? $this->user->id : $this->user->ip;

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_online` WHERE ip='$id'");

		if(!$query){ $this->core->js_notify($this->core->lng['e_sql_critical']); }

		$ar = $this->db->fetch_array($query);

		if($ar[0]<=0){
			$sql = "INSERT INTO `mcr_online` (ip, online, date_create, date_update) VALUES ('$id', '$online', '$time', '$time')";
		}else{
			$sql = "UPDATE `mcr_online` SET online='$online', date_update='$time' WHERE ip='$id'";
		}

		if(!$this->db->query($sql)){ $this->core->js_notify($this->core->lng['e_sql_critical']); }

		if(!$this->db->remove_fast('mcr_online', "`date_update`<'$expire'")){ $this->core->js_notify($this->core->lng['e_sql_critical']); }

		if($this->user->is_auth){
			if(!$this->db->remove_fast('mcr_online', "`ip`='{$this->user->ip}'")){ $this->core->js_notify($this->core->lng['e_sql_critical']); }
		}

		$this->core->js_notify('Поздравляем!', $this->core->lng['e_success'], true);
	}
}

?>