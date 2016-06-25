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

		$query = $this->db->query("SELECT `attach` FROM `mcr_news` WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->js_notify($this->lng['news_not_found']); }

		$ar = $this->db->fetch_assoc($query);

		$attach = (intval($ar['attach'])==1) ? 0 : 1;

		$update = $this->db->query("UPDATE `mcr_news` SET `attach`='$attach' WHERE id='$id'");

		if(!$update){ $this->core->js_notify($this->core->lng['e_sql_critical']); }

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		$msg = ($attach===1) ? $this->lng['att_attach'] : $this->lng['att_unattach'];

		// Лог действия
		$this->db->actlog("$msg ".$this->lng['att_news']." #$id", $this->user->id);

		$this->core->js_notify($this->lng['att_success'], $this->core->lng['e_success'], true);
	}

}

?>