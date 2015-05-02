<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $config, $user, $lng;

	public function __construct($core){
		$this->core = $core;
		$this->db	= $core->db;
		$this->config = $core->config;
		$this->user	= $core->user;
		$this->lng	= $core->lng;
	}

	private function is_discus($nid=1){
		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_news` WHERE id='$nid' AND discus='1'");

		if(!$query){ return false; }

		$ar = $this->db->fetch_array($query);

		if($ar[0]<=0){ return false; }
		
		return true;
	}

	private function notify($message='', $status='error'){
		$return = array(
			"_status" => $status,
			"_content" => $message
		);

		echo json_encode($return);

		exit;
	}

	private function add_comment(){

		if(!$this->core->is_access('sys_comment_add')){ $this->notify("У вас нет доступа для добалвения комментариев"); }

		$nid = @$_POST['id'];
		$nid = intval($nid);

		if(!$this->is_discus($nid)){ $this->notify("Комментарии отключены для данной новости"); }

		$message = @$_POST['message'];

		$message_trim = trim($message);

		if(empty($message_trim)){ $this->notify("Не заполнено поле \"Сообщение\""); }

		if(isset($_SESSION['add_comment'])){
			if(intval($_SESSION['add_comment'])>time()){
				$expire = intval($_SESSION['add_comment'])-time();
				$this->notify("Для повторного добовления комментария необходимо подождать $expire сек.", 'error');
			}else{
				$_SESSION['add_comment'] = time()+30;
			}
		}else{
			$_SESSION['add_comment'] = time()+30;
		}

		$bb = $this->core->load_bb_class(); // Object

		$text_bb		= $this->db->HSC($message);
		$text_html		= $bb->decode($text_bb);
		$safe_text_html	= $this->db->safesql($text_html);

		$text_bb		= $this->db->safesql($text_bb);

		$message_strip = trim(strip_tags($text_html, "<img>"));

		if(empty($message_strip)){ $this->notify("Не верно заполнено поле сообщения"); }

		$newdata = array(
			"time_create" => time(),
			"time_last" => time()
		);

		$safedata = $this->db->safesql(json_encode($newdata));

		$insert = $this->db->query("INSERT INTO `mcr_comments`
										(nid, text_html, text_bb, uid, `data`)
									VALUES
										('$nid', '$safe_text_html', '$text_bb', '{$this->user->id}', '$safedata')");

		if(!$insert){ $this->notify($this->lng['e_sql_critical']); }

		$id = $this->db->insert_id();

		$act_del = $act_edt = $act_get = '';

		if($this->core->is_access('sys_comment_del') || $this->core->is_access('sys_comment_del_all')){
			$act_del = $this->core->sp(MCR_THEME_MOD."news/comments/comment-act-del.html", array("ID" => $id));
		}

		if($this->core->is_access('sys_comment_edt') || $this->core->is_access('sys_comment_edt_all')){
			$act_edt = $this->core->sp(MCR_THEME_MOD."news/comments/comment-act-edt.html", array("ID" => $id));
		}

		if($this->user->is_auth){
			$act_get = $this->core->sp(MCR_THEME_MOD."news/comments/comment-act-get.html", array("ID" => $id));
		}

		$com_data	= array(
			"ID"				=> $id,
			"NID"				=> $nid,
			"TEXT"				=> $text_html,
			"UID"				=> $this->user->id,
			"DATA"				=> $newdata,
			"LOGIN"				=> $this->db->HSC($this->user->login),
			"ACTION_DELETE"		=> $act_del,
			"ACTION_EDIT"		=> $act_edt,
			"ACTION_QUOTE"		=> $act_get
		);

		$content = $this->core->sp(MCR_THEME_MOD."news/comments/comment-id.html", $com_data);

		$this->notify($content, 'success');

		exit;
	}

	private function del_comment(){

		if(!$this->core->is_access('sys_comment_del') && !$this->core->is_access('sys_comment_del_all')){ $this->notify("У вас нет прав на удаление комментариев"); }

		$id = @$_POST['id'];
		$id = intval($id);

		$nid = @$_POST['nid'];
		$nid = intval($nid);

		$newdata = array(
			"time_create" => time(),
			"time_last" => time()
		);

		$sql = "DELETE FROM `mcr_comments` WHERE id='$id' AND nid='$nid' AND uid='{$this->user->id}'";

		if($this->core->is_access('sys_comment_del_all')){
			$sql = "DELETE FROM `mcr_comments` WHERE id='$id' AND nid='$nid'";
		}

		$delete = $this->db->query($sql);

		if(!$delete){ $this->notify($this->lng['e_sql_critical']); }

		if($this->db->affected_rows()<=0){ 
			$this->notify("Ничего не удалено");
		}

		$this->notify("Выбранный комментарий успешно удален", 'success');

		exit;
	}

	private function get_comment(){

		if(!$this->user->is_auth){ $this->notify("У вас нет прав на цитирование"); }

		$id = @$_POST['id'];
		$id = intval($id);

		$nid = @$_POST['nid'];
		$nid = intval($nid);

		$query = $this->db->query("SELECT text_bb FROM `mcr_comments` WHERE nid='$nid' AND id='$id'");

		if(!$query){ $this->notify($this->lng['e_sql_critical']); }

		if($this->db->num_rows($query)<=0){ $this->notify($this->lng['e_hack']); }

		$ar = $this->db->fetch_assoc($query);

		$text_bb = $this->db->HSC($ar['text_bb']);

		$this->notify($text_bb, 'success');

		exit;
	}

	private function edt_comment(){

		if(!$this->core->is_access('sys_comment_edt') && !$this->core->is_access('sys_comment_edt_all')){ $this->notify("У вас нет доступа для изменения комментариев"); }

		$id = @$_POST['id'];
		$id = intval($id);

		$nid = @$_POST['nid'];
		$nid = intval($nid);

		if(!$this->is_discus($nid)){ $this->notify("Комментарии отключены для данной новости"); }

		$sql_query = "SELECT `data` FROM `mcr_comments` WHERE uid='{$this->user->id}' AND id='$id' AND nid='$nid'";

		if($this->core->is_access('sys_comment_edt_all')){
			$sql_query = "SELECT `data` FROM `mcr_comments` WHERE id='$id' AND nid='$nid'";
		}

		$query = $this->db->query($sql_query);

		if(!$query || $this->db->num_rows($query)<=0){ $this->notify($this->lng['e_hack']); }

		$ar = $this->db->fetch_assoc($query);

		$data = json_decode($ar['data']);

		$message = @$_POST['message'];

		$message_trim = trim($message);

		if(empty($message_trim)){ $this->notify("Не заполнено поле \"Сообщение\""); }

		$bb = $this->core->load_bb_class(); // Object

		$text_bb		= $this->db->HSC($message);
		$text_html		= $bb->decode($text_bb);
		$safe_text_html	= $this->db->safesql($text_html);

		$text_bb		= $this->db->safesql($text_bb);

		$message_strip = trim(strip_tags($text_html, "<img>"));

		if(empty($message_strip)){ $this->notify("Не верно заполнено поле сообщения"); }

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

		if(!$update){ $this->notify($this->lng['e_sql_critical']); }

		$this->notify($text_html, 'success');

		exit;
	}

	private function like(){

		if(!$this->core->is_access('sys_news_like')){ $this->notify("У вас нет доступа для голосования"); }

		$nid = @$_POST['nid'];
		$nid = intval($nid);

		$value = @$_POST['value'];
		$value = intval($value);

		if($value<0 || $value>1){ $this->notify($this->lng['e_hack']); }

		$query = $this->db->query("SELECT `n`.`vote`, COUNT(DISTINCT `l`.id) AS `likes`, COUNT(DISTINCT `d`.id) AS `dislikes`, `m`.`value`
									FROM `mcr_news` AS `n`
									LEFT JOIN `mcr_news_votes` AS `l`
										ON `l`.nid=`n`.id AND `l`.`value`='1'
									LEFT JOIN `mcr_news_votes` AS `d`
										ON `d`.nid=`n`.id AND `d`.`value`='0'
									LEFT JOIN `mcr_news_votes` AS `m`
										ON `m`.nid=`n`.id AND (`m`.uid='{$this->user->id}' OR `m`.ip='{$this->user->ip}')
									WHERE `n`.id='$nid'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->notify($this->lng['e_hack']); }

		$ar = $this->db->fetch_assoc($query);

		if(intval($ar['vote'])<=0){ $this->notify("Голосование для данной новости отключено"); }

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

			if(!$insert){ $this->notify($this->lng['e_sql_critical']); }

			$likes = ($value===1) ? $likes+1 : $likes;
			$dislikes = ($value===0) ? $dislikes+1 : $dislikes;
		}else{
			$update = $this->db->query("UPDATE `mcr_news_votes`
										SET uid='$uid', `value`='$value', `time`='$time'
										WHERE nid='$nid' AND (uid='{$this->user->id}' OR ip='{$this->user->ip}')
										LIMIT 1");

			if(!$update){ $this->notify($this->lng['e_sql_critical']); }

			if($value===1){
				$likes = (intval($old_value)===1) ? $likes : $likes+1;
				$dislikes = (intval($old_value)===1) ? $dislikes : $dislikes-1;
			}else{
				$likes = (intval($old_value)===0) ? $likes : $likes-1;
				$dislikes = (intval($old_value)===0) ? $dislikes : $dislikes+1;
			}
		}

		$content = $dislikes.'_'.$likes;

		$this->notify($content, 'success');

		exit;
	}

	public function content(){

		if($_SERVER['REQUEST_METHOD']!='POST'){ exit($this->lang['e_hack']); }
		if(!isset($_POST['act']) || empty($_POST['act'])){ exit($this->lang['e_hack']); }

		$act = $_POST['act'];

		switch($act){
			case 'add_comment': $this->add_comment(); break;
			case 'del_comment': $this->del_comment(); break;
			case 'get_comment': $this->get_comment(); break;
			case 'edt_comment': $this->edt_comment(); break;
			case 'like': $this->like(); break;

			default: exit($this->lang['e_hack']); break;
		}

		exit;
	}

}

?>