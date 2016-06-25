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

}

?>