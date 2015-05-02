<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $config, $lng, $user;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->config	= $core->config;
		$this->lng		= $core->lng;

		$bc = array(
			$this->lng['t_restore'] => BASE_URL."?mode=restore"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function check_exist($value='', $email=false){

		$selector = (!$email) ? "login='$value'" : "email='$value'";

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_users` WHERE $selector");

		if(!$query){ return true; }

		$ar = $this->db->fetch_array($query);

		if($ar[0]>0){ return true; }

		return false;
	}

	private function send(){

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$_SESSION['m_send_id'] = (isset($_SESSION['m_send_id'])) ? $_SESSION['m_send_id']+1 : 1;

			if($_SESSION['m_send_id']>5){ $this->core->notify($this->lng['e_msg'], $this->lng['e_rest_limit'], 1, "?mode=restore"); }

			$email = $this->db->safesql(@$_POST['email']);

			if(empty($email)){ $this->core->notify($this->lng['e_msg'], $this->lng['e_rest_eamil'], 1, "?mode=restore"); }

			$query = $this->db->query("SELECT `id`, `tmp` FROM `mcr_users` WHERE email='$email'");

			if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng['e_msg'], $this->lng['e_rest_email'], 1, "?mode=restore"); }

			$ar = $this->db->fetch_assoc($query);

			$id = intval($ar['id']);
			$tmp = md5($ar['tmp']);

			$data = array(
				"LINK" => $this->config->main['s_root_full'].BASE_URL.'?mode=restore&op=accept&key='.$id.'_'.$tmp,
				"SITENAME" => $this->config->main['s_name'],
				"SITEURL" => $this->config->main['s_root_full'].BASE_URL
			);

			$message = $this->core->sp(MCR_THEME_PATH."modules/restore/body.mail.html", $data);

			if(!$this->core->send_mail($email, $this->lng['rest_title'], $message)){ $this->core->notify($this->lng['e_msg'], $this->lng['e_critical'], 1, "?mode=restore"); }

			$this->core->notify('', $this->lng['e_rest_success'], 3);
		}

		ob_start();

		echo $this->core->sp(MCR_THEME_PATH."modules/restore/main.html");

		return ob_get_clean();
	}

	private function accept(){
		if(!isset($_GET['key'])){ $this->core->notify($this->lng['e_msg'], $this->lng['e_403'], 2, '?mode=403'); }

		$key_string = $_GET['key'];

		$array = explode("_", $key_string);

		if(count($array)!==2){ $this->core->notify($this->lng['e_msg'], $this->lng['e_403'], 2, '?mode=403'); }

		$uid = intval($array[0]);

		$key = $array[1];

		$query = $this->db->query("SELECT `tmp`, `data` FROM `mcr_users` WHERE id='$uid'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng['e_attention'], $this->lng['e_sql_critical'], 1, "?mode=restore"); }

		$ar = $this->db->fetch_assoc($query);

		if($key!==md5($ar['tmp'])){ $this->core->notify($this->lng['e_msg'], $this->lng['e_403'], 2, '?mode=403'); }

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$newpass = @$_POST['newpass'];

			if(mb_strlen($newpass, "UTF-8")<6){ $this->core->notify($this->lng['e_msg'], $this->lng['e_reg_pass_length'], 2, '?mode=restore&op=accept&key='.$key_string); }

			$tmp = $this->db->safesql($this->core->random(16));

			$salt = $this->db->safesql($this->core->random());

			$password = $this->core->gen_password($newpass, $salt);

			$data = json_decode($ar['data']);

			$newdata = array(
				"time_create" => $data->time_create,
				"time_last" => time(),
				"firstname" => $data->firstname,
				"lastname" => $data->lastname,
				"gender" => $data->gender,
				"birthday" => $data->birthday
			);

			$newdata = $this->db->safesql(json_encode($newdata));

			$update = $this->db->query("UPDATE `mcr_users`
										SET password='$password', `salt`='$salt', `tmp`='$tmp', ip_last='{$this->user->ip}', `data`='$newdata'
										WHERE id='$uid'");

			if(!$update){ $this->core->notify($this->lng['e_attention'], $this->lng['e_sql_critical'], 1, "?mode=restore"); }

			$this->core->notify($this->lng['e_success'], $this->lng['e_rest_success2'], 3);
		}

		ob_start();

		echo $this->core->sp(MCR_THEME_PATH."modules/restore/newpass.html");

		return ob_get_clean();
	}

	public function content(){
		
		if($this->user->is_auth){ $this->core->notify($this->lng['e_msg'], $this->lng['e_403'], 2, '?mode=403'); }
		
		if(!$this->core->is_access('sys_restore')){ $this->core->notify($this->lng['e_msg'], $this->lng['e_rest_perm'], 1, "?mode=403"); }

		$this->core->title = $this->lng['t_restore'];

		$op = (isset($_GET['op'])) ? $_GET['op'] : false;

		switch($op){
			case 'accept': $content = $this->accept(); break;

			default: $content = $this->send(); break;
		}

		ob_start();

			echo $content;

		return ob_get_clean();
	}

}

?>