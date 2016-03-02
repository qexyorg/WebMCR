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

	private function is_discus($nid=1){
		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_news` WHERE id='$nid' AND discus='1'");

		if(!$query){ return false; }

		$ar = $this->db->fetch_array($query);

		if($ar[0]<=0){ return false; }
		
		return true;
	}

	private function add_comment(){

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

		$message_strip = trim(strip_tags($text_html, "<img>"));

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

	private function edt_comment(){

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

		$message_strip = trim(strip_tags($text_html, "<img><hr>"));

		if(empty($message_strip)){ $this->core->js_notify($this->lng['com_msg_incorrect']); }

		$newdata = array(
			"time_create" => $data->time_create,
			"time_last" => time()
		);

		$safedata = $this->db->safesql(json_encode($newdata));

		$sql_update = "UPDATE `mcr_comments`
						SET 
							text_html='$safe_text_html',
							text_bb='$text_bb',
							`data`='$safedata'
						WHERE id='$id' AND nid='$nid' AND uid='{$this->user->id}'";

		if($this->core->is_access('sys_comment_edt_all')){

			$sql_update = "UPDATE `mcr_comments`
							SET 
								text_html='$safe_text_html',
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

	private function del_comment(){

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

	private function get_comment(){

		if(!$this->user->is_auth){ $this->core->js_notify($this->lng['com_unauth']); }

		$id = intval(@$_POST['id']);

		$nid = intval(@$_POST['nid']);

		$query = $this->db->query("SELECT text_bb FROM `mcr_comments` WHERE nid='$nid' AND id='$id'");

		if(!$query){ $this->core->js_notify($this->core->lng['e_sql_critical']); }

		if($this->db->num_rows($query)<=0){ $this->core->js_notify($this->core->lng['e_hack']); }

		$ar = $this->db->fetch_assoc($query);

		$this->core->js_notify($this->lng['com_load_success'], $this->core->lng['e_success'], true, $ar['text_bb']);
	}

	private function like(){

		if(!$this->core->is_access('sys_news_like')){ $this->core->js_notify($this->lng['com_vote_perm']); }

		$nid = intval(@$_POST['nid']);

		$value = intval(@$_POST['value']);

		if($value<0 || $value>1){ $this->core->js_notify($this->core->lng['e_hack']); }

		$query = $this->db->query("SELECT `n`.`vote`, COUNT(DISTINCT `l`.id) AS `likes`, COUNT(DISTINCT `d`.id) AS `dislikes`, `m`.`value`
									FROM `mcr_news` AS `n`
									LEFT JOIN `mcr_news_votes` AS `l`
										ON `l`.nid=`n`.id AND `l`.`value`='1'
									LEFT JOIN `mcr_news_votes` AS `d`
										ON `d`.nid=`n`.id AND `d`.`value`='0'
									LEFT JOIN `mcr_news_votes` AS `m`
										ON `m`.nid=`n`.id AND (`m`.uid='{$this->user->id}' OR `m`.ip='{$this->user->ip}')
									WHERE `n`.id='$nid'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->js_notify($this->core->lng['e_hack']); }

		$ar = $this->db->fetch_assoc($query);

		if(intval($ar['vote'])<=0){ $this->core->js_notify($this->lng['com_vote_disabled']); }

		$likes = intval($ar['likes']);
		$dislikes = intval($ar['dislikes']);

		$uid = (!$this->user->is_auth) ? -1 : $this->user->id;
		$time = time();

		$old_value = $ar['value'];

		if(is_null($old_value)){
			$insert = $this->db->query("INSERT INTO `mcr_news_votes`
											(nid, uid, `value`, ip, `time`)
										VALUES
											('$nid', '$uid', '$value', '{$this->user->ip}', '$time')");

			if(!$insert){ $this->core->js_notify($this->core->lng['e_sql_critical']); }

			$likes = ($value===1) ? $likes+1 : $likes;
			$dislikes = ($value===0) ? $dislikes+1 : $dislikes;
		}else{
			$update = $this->db->query("UPDATE `mcr_news_votes`
										SET uid='$uid', `value`='$value', `time`='$time'
										WHERE nid='$nid' AND (uid='{$this->user->id}' OR ip='{$this->user->ip}')
										LIMIT 1");

			if(!$update){ $this->core->js_notify($this->core->lng['e_sql_critical']); }

			if($value===1){
				$likes = (intval($old_value)===1) ? $likes : $likes+1;
				$dislikes = (intval($old_value)===1) ? $dislikes : $dislikes-1;
			}else{
				$likes = (intval($old_value)===0) ? $likes : $likes-1;
				$dislikes = (intval($old_value)===0) ? $dislikes : $dislikes+1;
			}
		}

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_com_vote']." #$nid", $this->user->id);

		$data = array(
			'likes' => $likes,
			'dislikes' => $dislikes
		);

		$this->core->js_notify($this->lng['com_vote_success'], $this->core->lng['e_success'], true, $data);
	}

	public function content(){

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->js_notify($this->core->lng['e_hack']); }
		if(!isset($_POST['act']) || empty($_POST['act'])){ $this->core->js_notify($this->core->lng['e_hack']); }

		$act = $_POST['act'];

		switch($act){
			case 'add_comment': $this->add_comment(); break;
			case 'del_comment': $this->del_comment(); break;
			case 'get_comment': $this->get_comment(); break;
			case 'edt_comment': $this->edt_comment(); break;
			case 'like': $this->like(); break;

			default: $this->core->js_notify($this->core->lng['e_hack']); break;
		}

		exit;
	}

}

?>