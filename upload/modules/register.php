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
			$this->lng['t_reg'] => BASE_URL."?mode=register"
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

	private function regmain(){
		
		if(!$this->core->is_access('sys_register')){ $this->core->notify($this->lng['e_msg'], $this->lng['e_reg_perm'], 1, "?mode=403"); }
		
		if($this->user->is_auth){ $this->core->notify($this->lng['e_msg'], $this->lng['e_reg_already'], 2, '?mode=403'); }

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!$this->core->captcha_check()){ $this->core->notify('', $this->lng['e_captcha'], 2, '?mode=403'); }
			if(intval($_POST['accept'])!==1){ $this->core->notify($this->lng['e_msg'], $this->lng['e_reg_rules'], 1, '?mode=register'); }

			if(!preg_match("/^[\w\-]{3,}$/i", $_POST['login'])){ $this->core->notify($this->lng['e_msg'], $this->lng['e_reg_login_regexp'], 1, '?mode=register'); }
			
			$login = $this->db->safesql($_POST['login']);

			if($this->check_exist($login)){ $this->core->notify($this->lng['e_msg'], $this->lng['e_reg_login_exist'], 1, '?mode=register'); }

			if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){ $this->core->notify($this->lng['e_msg'], $this->lng['e_reg_email_regexp'], 1, '?mode=register'); }

			$email = $this->db->safesql($_POST['email']);

			if($this->check_exist($email, true)){ $this->core->notify($this->lng['e_msg'], $this->lng['e_reg_email_exist'], 1, '?mode=register'); }

			if(mb_strlen($_POST['password'], "UTF-8")<6){ $this->core->notify($this->lng['e_msg'], $this->lng['e_reg_pass_length'], 1, '?mode=register'); }

			$password = $_POST['password'];

			if($password !== $_POST['repassword']){ $this->core->notify($this->lng['e_msg'], $this->lng['e_reg_pass_match'], 1, '?mode=register'); }

			$tmp = $this->db->safesql($this->core->random(16));

			$salt = $this->db->safesql($this->core->random());

			$password = $this->core->gen_password($password, $salt);
			$password = $this->db->safesql($password);

			$ip = $this->user->ip;

			$gender = (intval($_POST['gender'])===1) ? 1 : 0; 

			$newdata = array(
				"time_create" => time(),
				"time_last" => time(),
				"firstname" => '',
				"lastname" => '',
				"gender" => $gender,
				"birthday" => 0
			);

			$newdata = $this->db->safesql(json_encode($newdata));

			$gid = ($this->config->main['reg_accept']) ? 1 : 2;

			$notify_message = $this->lng['e_reg_success'];

			$insert = $this->db->query("INSERT INTO `mcr_users`
										(gid, login, email, password, `salt`, `tmp`, ip_create, ip_last, `data`)
										VALUES
										('$gid', '$login', '$email', '$password', '$salt', '$tmp', '$ip', '$ip', '$newdata')");
			if(!$insert){ $this->core->notify($this->lng['e_msg'], $this->lng['e_sql_critical'], 1, '?mode=register'); }

			$insert1 = $this->db->query("INSERT INTO `mcr_iconomy`
										(login)
										VALUES
										('$login')");
			if(!$insert1){ $this->core->notify($this->lng['e_msg'], $this->lng['e_sql_critical'], 1, '?mode=register'); }

			if($this->config->main['reg_accept']){
				$id = $this->db->insert_id();
				$data_mail = array(
					"LINK" => $this->config->main['s_root_full'].BASE_URL.'?mode=register&op=accept&key='.$id.'_'.md5($salt),
					"SITENAME" => $this->config->main['s_name'],
					"SITEURL" => $this->config->main['s_root_full'].BASE_URL
				);

				$message = $this->core->sp(MCR_THEME_PATH."modules/register/body.mail.html", $data_mail);
				
				if(!$this->core->send_mail($email, $this->lng['reg_title'], $message)){ $this->core->notify($this->lng['e_msg'], "Обратитесь к администрации", 1, "?mode=register"); }

				$notify_message = $this->lng['e_reg_success_mail'];
			}

			$this->core->notify($this->lng['e_success'], $notify_message, 3);
		}

		ob_start();

		echo $this->core->sp(MCR_THEME_PATH."modules/register/main.html");

		return ob_get_clean();

	}

	private function accept(){
		if(!isset($_GET['key'])){ $this->core->notify($this->lng['e_msg'], $this->lng['e_403'], 2, '?mode=403'); }

		$key_string = $_GET['key'];

		$array = explode("_", $key_string);

		if(count($array)!==2){ $this->core->notify($this->lng['e_msg'], $this->lng['e_403'], 2, '?mode=403'); }

		$uid = intval($array[0]);

		$key = $array[1];

		$query = $this->db->query("SELECT `salt`, `data` FROM `mcr_users` WHERE id='$uid' AND gid='1'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng['e_attention'], $this->lng['e_sql_critical'], 1, "?mode=register"); }

		$ar = $this->db->fetch_assoc($query);

		if($key!==md5($ar['salt'])){ $this->core->notify($this->lng['e_msg'], $this->lng['e_403'], 2, '?mode=403'); }

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
									SET gid='2', ip_last='{$this->user->ip}', `data`='$newdata'
									WHERE id='$uid' AND gid='1'");

		if(!$update){ $this->core->notify($this->lng['e_attention'], $this->lng['e_sql_critical'], 1, "?mode=register"); }

		$this->core->notify($this->lng['e_success'], $this->lng['e_reg_accept'], 3);
	}

	public function content(){

		$this->core->header = $this->core->sp(MCR_THEME_MOD."register/header.html");

		$this->core->title = $this->lng['t_reg'];

		$op = (isset($_GET['op'])) ? $_GET['op'] : false;

		switch($op){

			case 'accept': $content = $this->accept(); break;

			default: $content = $this->regmain(); break;
		}

		ob_start();

		echo $content;

		return ob_get_clean();
	}

}

?>