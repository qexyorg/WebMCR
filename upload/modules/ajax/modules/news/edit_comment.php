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

	private function is_discus($nid=1){
		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_news` WHERE id='$nid' AND discus='1'");

		if(!$query){ return false; }

		$ar = $this->db->fetch_array($query);

		if($ar[0]<=0){ return false; }
		
		return true;
	}

	public function content(){

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->js_notify($this->core->lng['e_hack']); }
		
		if(!$this->core->is_access('sys_comment_edt') && !$this->core->is_access('sys_comment_edt_all')){ $this->core->js_notify($this->lng['com_perm_edit']); }

		$id = intval(@$_POST['id']);

		$nid = intval(@$_POST['nid']);

		if(!$this->is_discus($nid)){ $this->core->js_notify($this->lng['com_disabled']); }

		$sql_query = "SELECT `data` FROM `mcr_comments` WHERE uid='{$this->user->id}' AND id='$id' AND nid='$nid'";

		if($this->core->is_access('sys_comment_edt_all')){
			$sql_query = "SELECT `data` FROM `mcr_comments` WHERE id='$id' AND nid='$nid'";
		}

		$query = $this->db->query($sql_query);

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->js_notify($this->core->lng['e_hack']); }

		$ar = $this->db->fetch_assoc($query);

		$data = json_decode($ar['data']);

		$message = @$_POST['message'];

		$message_trim = trim($message);

		if(empty($message_trim)){ $this->core->js_notify($this->lng['com_msg_empty']); }

		$bb = $this->core->load_bb_class(); // Object

		$text_html		= $bb->parse($message);
		$safe_text_html	= $this->db->safesql($text_html);

		$text_bb		= $this->db->safesql($message);

		$message_strip = trim(strip_tags($text_html, "<img><hr><iframe>"));

		if(empty($message_strip)){ $this->core->js_notify($this->lng['com_msg_incorrect']); }

		$newdata = array(
			"time_create" => $data->time_create,
			"time_last" => time()
		);

		$safedata = $this->db->safesql(json_encode($newdata));

		$sql_update = "UPDATE `mcr_comments`
						SET text_html='$safe_text_html',
							text_bb='$text_bb',
							`data`='$safedata'
						WHERE id='$id' AND nid='$nid' AND uid='{$this->user->id}'";

		if($this->core->is_access('sys_comment_edt_all')){

			$sql_update = "UPDATE `mcr_comments`
							SET text_html='$safe_text_html',
								text_bb='$text_bb',
								`data`='$safedata'
							WHERE id='$id' AND nid='$nid'";

		}

		$update = $this->db->query($sql_update);

		if(!$update){ $this->core->js_notify($this->core->lng['e_sql_critical']); }

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_com_edit']." #$id", $this->user->id);

		$this->core->js_notify($this->lng['com_edit_success'], $this->core->lng['e_success'], true, $text_html);
	}

}

?>