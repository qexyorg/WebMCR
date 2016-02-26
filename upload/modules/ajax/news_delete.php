<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $config, $user, $lng;

	public function __construct($core){
		$this->core = $core;
		$this->db	= $core->db;
		$this->config = $core->config;
		$this->user	= $core->user;
		$this->lng	= $core->lng_m;
	}

	public function content(){

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->js_notify($this->core->lng['e_hack']); }

		if(!$this->core->is_access('sys_adm_news')){ $this->core->js_notify($this->core->lng['e_403']); }

		$id = intval(@$_POST['id']);

		$delete = $this->db->query("DELETE FROM `mcr_news` WHERE id='$id'");
		if(!$delete){ $this->core->js_notify($this->core->lng['e_sql_critical'].' #'.__LINE__); }

		$delete = $this->db->query("DELETE FROM `mcr_news_views` WHERE nid='$id'");
		if(!$delete){ $this->core->js_notify($this->core->lng['e_sql_critical'].' #'.__LINE__); }

		$delete = $this->db->query("DELETE FROM `mcr_news_votes` WHERE id='$id'");
		if(!$delete){ $this->core->js_notify($this->core->lng['e_sql_critical'].' #'.__LINE__); }

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_new_del']." #$id", $this->user->id);

		$this->core->js_notify($this->lng['new_success_del'], $this->core->lng['e_success'], true);
	}

}

?>