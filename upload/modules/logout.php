<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $user, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->lng		= $core->lng;
	}

	public function content(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify('Hacking Attempt!'); }

		if(!$this->user->is_auth){ $this->core->notify('403', $this->lng['e_unauth_not'], 1, '?mode=403'); }

		$new_data = array(
			"time_create" => intval($this->user->data->time_create),
			"time_last" => time(),
			"firstname" => $this->db->safesql($this->user->data->firstname),
			"lastname" => $this->db->safesql($this->user->data->lastname),
			"gender" => $this->user->data->gender,
			"birthday" => $this->user->data->birthday
		);

		$new_data = $this->db->safesql(json_encode($new_data));
		$new_tmp = $this->db->safesql($this->core->random(16));

		$update = $this->db->query("UPDATE `mcr_users` SET `tmp`='$new_tmp', `data`='$new_data' WHERE id='{$this->user->id}' LIMIT 1");

		if(!$update){ $this->core->notify($this->lng['e_attention'], $this->lng['e_sql_critical']); }

		setcookie("mcr_user", "", time()-3600, '/');

		$this->core->notify('', $this->lng['e_unauth_yes'], 1);
	}

}

?>