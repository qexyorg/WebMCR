<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $cfg, $user, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;
		$this->user		= $core->user;
		$this->lng		= $core->load_language('register');
	}

	public function content(){

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->js_notify($this->core->lng['e_hack']); }
		
		if($this->user->is_auth){ $this->core->js_notify($this->lng['e_already']); }

		$login = $this->db->safesql(@$_POST['login']);
		$email = $this->db->safesql(@$_POST['email']);
		$uuid = $this->db->safesql($this->user->logintouuid(@$_POST['login']));
		$password = @$_POST['password'];

		if(intval($_POST['rules'])!==1){ $this->core->js_notify($this->lng['e_rules']); }

		if(!preg_match("/^[\w\-]{3,}$/i", $login)){ $this->core->js_notify($this->lng['e_login_regexp']); }
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){ $this->core->js_notify($this->lng['e_email_regexp']); }

		if($login=='default'){ $this->core->js_notify($this->lng['e_exist']); }

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];
		$ic_f		= $ctables['iconomy']['fields'];

		$query = $this->db->query("SELECT COUNT(*) FROM `{$this->cfg->tabname('users')}` WHERE `{$us_f['login']}`='$login' OR `{$us_f['email']}`='$email'");

		if(!$query){ $this->core->js_notify($this->core->lng['e_sql_critical']); }

		$ar = $this->db->fetch_array($query);

		if($ar[0]>0){ $this->core->js_notify($this->lng['e_exist']); }

		if(mb_strlen($password, "UTF-8")<6){ $this->core->js_notify($this->lng['e_pass_length']); }

		if($password !== @$_POST['repassword']){ $this->core->js_notify($this->lng['e_pass_match']); }

		if(!$this->core->captcha_check()){ $this->core->js_notify($this->core->lng['e_captcha']); }

		$tmp = $this->db->safesql($this->core->random(16));

		$salt = $this->db->safesql($this->core->random());

		$password = $this->core->gen_password($password, $salt);
		$password = $this->db->safesql($password);

		$ip = $this->user->ip;

		$gender = (intval($_POST['gender'])===1) ? 1 : 0;

		$time = time();

		$gid = ($this->cfg->main['reg_accept']) ? 1 : 2;

		$notify_message = $this->core->lng['e_success'];

		$insert = $this->db->query("INSERT INTO `{$this->cfg->tabname('users')}`
										(`{$us_f['group']}`, `{$us_f['login']}`, `{$us_f['email']}`, `{$us_f['pass']}`, `{$us_f['uuid']}`,
										`{$us_f['salt']}`, `{$us_f['tmp']}`, `{$us_f['ip_create']}`, `{$us_f['ip_last']}`, `{$us_f['date_reg']}`, `{$us_f['date_last']}`, `{$us_f['fname']}`, `{$us_f['lname']}`, `{$us_f['gender']}`)
									VALUES
										('$gid', '$login', '$email', '$password', '$uuid', '$salt', '$tmp', '$ip', '$ip', '$time', '$time', '', '', '$gender')");

		if(!$insert){ $this->core->js_notify($this->core->lng['e_sql_critical']); }
			
		$id = $this->db->insert_id();

		$insert1 = $this->db->query("INSERT INTO `{$this->cfg->tabname('iconomy')}`
										(`{$ic_f['login']}`)
									VALUES
										('$login')");
		if(!$insert1){ $this->core->js_notify($this->core->lng['e_sql_critical']); }

		// Лог действия
		$this->db->actlog($this->lng['log_reg'], $id);

		if($this->cfg->main['reg_accept']){
			$data_mail = array(
				"LINK" => $this->cfg->main['s_root_full'].BASE_URL.'?mode=register&op=accept&key='.$id.'_'.md5($salt),
				"SITENAME" => $this->cfg->main['s_name'],
				"SITEURL" => $this->cfg->main['s_root_full'].BASE_URL,
				"LNG" => $this->lng,
			);

			$message = $this->core->sp(MCR_THEME_PATH."modules/register/body.mail.html", $data_mail);
				
			if(!$this->core->send_mail($email, $this->lng['msg_title'], $message)){ $this->core->js_notify($this->core->lng['e_mail_send']); }

			$this->core->js_notify($this->lng['e_success_mail'], $this->core->lng['e_success'], true);
		}

		$this->core->js_notify($this->lng['e_success'], $this->core->lng['e_success'], true);
	}

}

?>
