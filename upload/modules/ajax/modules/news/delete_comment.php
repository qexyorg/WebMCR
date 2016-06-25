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
		
		if(!$this->core->is_access('sys_comment_del') && !$this->core->is_access('sys_comment_del_all')){ $this->core->js_notify($this->lng['com_perm_del']); }

		$id = @$_POST['id'];
		$id = intval($id);

		$nid = @$_POST['nid'];
		$nid = intval($nid);

		$newdata = array(
			"time_create" => time(),
			"time_last" => time()
		);

		$cond = "id='$id' AND nid='$nid' AND uid='{$this->user->id}'";

		if($this->core->is_access('sys_comment_del_all')){
			$cond = "id='$id' AND nid='$nid'";
		}

		if(!$this->db->remove_fast("mcr_comments", $cond)){ $this->core->js_notify($this->core->lng['e_sql_critical']); }

		if($this->db->affected_rows()<=0){ 
			$this->core->js_notify($this->lng['com_del_empty']);
		}

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_com_del']." #$id", $this->user->id);

		$this->core->js_notify($this->lng['com_del_success'], $this->core->lng['e_success'], true);
	}

}

?>