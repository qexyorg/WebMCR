<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $cfg, $user, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;
		$this->user		= $core->user;
		$this->lng		= $core->load_language('news');
	}

	public function content(){

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->js_notify($this->core->lng['e_hack']); }

		if(!$this->core->is_access('sys_adm_news')){ $this->core->js_notify($this->core->lng['e_403']); }

		$id = intval(@$_POST['id']);

		if(!$this->db->remove_fast("mcr_news", "id='$id'")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_sql_critical'].' #'.__LINE__, 2, '?mode=admin&do=menu'); }

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_new_del']." #$id", $this->user->id);

		$this->core->js_notify($this->lng['new_success_del'], $this->core->lng['e_success'], true);
	}

}

?>