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

	public function content(){

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->js_notify($this->core->lng['e_hack']); }
		
		if($this->user->is_auth){ $this->core->js_notify($this->lng['reg_e_already']); }

		$login = $this->db->safesql(@$_POST['login']);
		$email = $this->db->safesql(@$_POST['email']);
		$uuid = $this->db->safesql($this->user->logintouuid(@$_POST['login']));
		$password = @$_POST['password'];

		if(intval($_POST['rules'])!==1){ $this->core->js_notify($this->lng['reg_e_rules']); }

		if(!preg_match("/^[\w\-]{3,}$/i", $login)){ $this->core->js_notify($this->lng['reg_e_login_regexp']); }
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){ $this->core->js_notify($this->lng['reg_e_email_regexp']); }

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_users` WHERE login='$login' OR email='$email'");

		if(!$query){ $this->core->js_notify($this->core->lng['e_sql_critical']); }

		$ar = $this->db->fetch_array($query);

		if($ar[0]>0){ $this->core->js_notify($this->lng['reg_e_exist']); }

		if(mb_strlen($password, "UTF-8")<6){ $this->core->js_notify($this->lng['reg_e_pass_length']); }

		if($password !== @$_POST['repassword']){ $this->core->js_notify($this->lng['reg_e_pass_match']); }

		if(!$this->core->captcha_check()){ $this->core->js_notify($this->core->lng['e_captcha']); }

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

		$notify_message = $this->core->lng['e_success'];

		$insert = $this->db->query("INSERT INTO `mcr_users`
										(gid, login, email, password, `uuid`, `salt`, `tmp`, ip_create, ip_last, `data`)
									VALUES
										('$gid', '$login', '$email', '$password', '$uuid', '$salt', '$tmp', '$ip', '$ip', '$newdata')");

		if(!$insert){ $this->core->js_notify($this->core->lng['e_sql_critical']); }
			
		$id = $this->db->insert_id();

		$insert1 = $this->db->query("INSERT INTO `mcr_iconomy`
										(login)
									VALUES
										('$login')");
		if(!$insert1){ $this->core->js_notify($this->core->lng['e_sql_critical']); }

		// Лог действия
		$this->db->actlog($this->lng['log_reg'], $id);

		if($this->config->main['reg_accept']){
			$data_mail = array(
				"LINK" => $this->config->main['s_root_full'].BASE_URL.'?mode=register&op=accept&key='.$id.'_'.md5($salt),
				"SITENAME" => $this->config->main['s_name'],
				"SITEURL" => $this->config->main['s_root_full'].BASE_URL,
				"LNG" => $this->lng,
			);

			$message = $this->core->sp(MCR_THEME_PATH."modules/register/body.mail.html", $data_mail);
				
			if(!$this->core->send_mail($email, $this->lng['reg_title'], $message)){ $this->core->js_notify($this->core->lng['e_sql_critical']); }

			$this->core->js_notify($this->lng['reg_e_success_mail'], $this->core->lng['e_success'], true);
		}

		$this->core->js_notify($this->lng['reg_e_success'], $this->core->lng['e_success'], true);
	}

}

?>