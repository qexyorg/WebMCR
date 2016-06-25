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
		
		if(!$this->core->is_access('sys_comment_add')){ $this->core->js_notify($this->lng['com_perm_add']); }

		$nid = @$_POST['id'];
		$nid = intval($nid);

		if(!$this->is_discus($nid)){ $this->core->js_notify($this->lng['com_disabled']); }

		$message = @$_POST['message'];

		$message_trim = trim($message);

		if(empty($message_trim)){ $this->core->js_notify($this->lng['com_msg_empty']); }

		if(isset($_SESSION['add_comment'])){
			if(intval($_SESSION['add_comment'])>time()){
				$expire = intval($_SESSION['add_comment'])-time();
				$this->core->js_notify($this->lng['com_wait']." $expire ".$this->lng['com_wait1']);
			}else{
				$_SESSION['add_comment'] = time()+30;
			}
		}else{
			$_SESSION['add_comment'] = time()+30;
		}

		$bb = $this->core->load_bb_class(); // Object

		$text_html		= $bb->parse($message);
		$safe_text_html	= $this->db->safesql($text_html);

		$text_bb		= $this->db->safesql($message);

		$message_strip = trim(strip_tags($text_html, "<img><hr><iframe>"));

		if(empty($message_strip)){ $this->core->js_notify($this->lng['com_msg_incorrect']); }

		$newdata = array(
			"time_create" => time(),
			"time_last" => time()
		);

		$safedata = $this->db->safesql(json_encode($newdata));

		$insert = $this->db->query("INSERT INTO `mcr_comments`
										(nid, text_html, text_bb, uid, `data`)
									VALUES
										('$nid', '$safe_text_html', '$text_bb', '{$this->user->id}', '$safedata')");

		if(!$insert){ $this->core->js_notify($this->core->lng['e_sql_critical']); }

		$id = $this->db->insert_id();

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_com_add']." #$id", $this->user->id);

		$act_del = $act_edt = $act_get = '';

		$data = array(
			"ID" => $id,
			"LNG" => $this->lng
		);

		if($this->core->is_access('sys_comment_del') || $this->core->is_access('sys_comment_del_all')){
			$act_del = $this->core->sp(MCR_THEME_MOD."news/comments/comment-act-del.html", $data);
		}

		if($this->core->is_access('sys_comment_edt') || $this->core->is_access('sys_comment_edt_all')){
			$act_edt = $this->core->sp(MCR_THEME_MOD."news/comments/comment-act-edt.html", $data);
		}

		if($this->user->is_auth){
			$act_get = $this->core->sp(MCR_THEME_MOD."news/comments/comment-act-get.html", $data);
		}

		$com_data	= array(
			"ID"				=> $id,
			"NID"				=> $nid,
			"TEXT"				=> $text_html,
			"UID"				=> $this->user->id,
			"DATA"				=> $newdata,
			"LOGIN"				=> $this->user->login_v2,
			"ACTION_DELETE"		=> $act_del,
			"ACTION_EDIT"		=> $act_edt,
			"ACTION_QUOTE"		=> $act_get
		);

		$content = $this->core->sp(MCR_THEME_MOD."news/comments/comment-id.html", $com_data);

		$this->core->js_notify($this->lng['com_add_success'], $this->core->lng['e_success'], true, $content);
	}

}

?>