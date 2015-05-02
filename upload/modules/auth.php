<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $user, $config, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->config	= $core->config;
		$this->lng		= $core->lng;
	}

	public function content(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify('Hacking Attempt!'); }
		
		if($this->user->is_auth){ $this->core->notify('', $this->lng["e_auth_already"], 1); }

		$login = $this->db->safesql($_POST['login']);
		$remember = (isset($_POST['remember']) && intval($_POST['remember'])==1) ? true : false;

		$query = $this->db->query("SELECT `u`.id, `u`.password, `u`.`salt`, `u`.`data`,
											`g`.`permissions`
									FROM `mcr_users` AS `u`
									INNER JOIN `mcr_groups` AS `g`
										ON `g`.id=`u`.gid
									WHERE `u`.login='$login'
									LIMIT 1");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng["e_msg"], $this->lng['e_auth_wrong_pass']); }

		$ar = $this->db->fetch_assoc($query);

		$uid = intval($ar['id']);

		$password = $this->core->gen_password($_POST['password'], $ar['salt']);

		if($ar['password']!==$password){ $this->core->notify($this->lng["e_msg"], $this->lng['e_auth_wrong_pass']); }

		$permissions = json_decode($ar['permissions'], true);

		$data = json_decode($ar['data']);

		$new_data = array(
			"time_create" => intval($data->time_create),
			"time_last" => time(),
			"firstname" => $this->db->safesql($data->firstname),
			"lastname" => $this->db->safesql($data->lastname),
			"gender" => $data->gender,
			"birthday" => $data->birthday
		);

		$new_tmp = $this->db->safesql($this->core->random(16));
		$new_data = $this->db->safesql(json_encode($new_data));
		$new_ip = $this->user->ip;

		$update = $this->db->query("UPDATE `mcr_users`
									SET `tmp`='$new_tmp', ip_last='$new_ip', `data`='$new_data'
									WHERE login='$login' AND password='$password'
									LIMIT 1");

		if(!$update){ $this->core->notify($this->lng['e_attention'], $this->lng['e_sql_critical']); }

		if(!@$permissions['sys_auth']){ $this->core->notify("403", "Вам запрещена авторизация", 2, '?mode=403'); }

		$new_hash = $uid.$new_tmp.$new_ip.md5($this->config->main['mcr_secury']);

		$new_hash = $uid.'_'.md5($new_hash);

		$safetime = ($remember) ? 3600*24*30+time() : time()+3600;

		setcookie("mcr_user", $new_hash, $safetime, '/');

		$this->core->notify($this->lng['e_success'], $this->lng['e_auth_success'], 3);
	}

}

?>