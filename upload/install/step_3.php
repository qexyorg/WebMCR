<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $config, $lng, $lng_m, $user;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->config	= $core->config;
		$this->lng		= $core->lng;
		$this->lng_m	= $core->lng_m;

		$this->core->title = $this->lng_m['mod_name'].' — '.$this->lng_m['step_3'];

		$bc = array(
			$this->lng_m['mod_name'] => BASE_URL."install/",
			$this->lng_m['step_3'] => BASE_URL."install/?mode=step_3"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	public function content(){
		if(!isset($_SESSION['step_2'])){ $this->core->notify('', '', 4, 'install/?mode=step_2'); }
		if(isset($_SESSION['step_3'])){ $this->core->notify('', '', 4, 'install/?mode=settings'); }

		if(!isset($_SESSION['f_login'])){
			$_SESSION['f_login']	= 'admin';
			$_SESSION['f_email']	= '';
			$_SESSION['f_pass']		= '';
			$_SESSION['f_repass']	= '';
		}

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$method = (intval(@$_POST['method'])<0 || intval(@$_POST['method'])>15) ? 0 : intval(@$_POST['method']);

			$_SESSION['f_login']	= $this->db->HSC(@$_POST['login']);
			$_SESSION['f_email']	= $this->db->HSC(@$_POST['email']);
			$_SESSION['f_pass']		= $this->db->HSC(@$_POST['password']);
			$_SESSION['f_repass']	= $this->db->HSC(@$_POST['repassword']);

			if(!preg_match("/^[\w\-]{3,}$/i", @$_POST['login'])){
				$this->core->notify($this->lng['e_msg'], $this->lng_m['e_login_format'], 2, 'install/?mode=step_3');
			}

			if(mb_strlen(@$_POST['password'], "UTF-8")<6){
				$this->core->notify($this->lng['e_msg'], $this->lng_m['e_pass_len'], 2, 'install/?mode=step_3');
			}

			if(@$_POST['password'] !== @$_POST['repassword']){
				$this->core->notify($this->lng['e_msg'], $this->lng_m['e_pass_match'], 2, 'install/?mode=step_3');
			}

			if(!filter_var(@$_POST['email'], FILTER_VALIDATE_EMAIL)){
				$this->core->notify($this->lng['e_msg'], $this->lng_m['e_email_format'], 2, 'install/?mode=step_3');
			}

			$login = $this->db->safesql(@$_POST['login']);
			$email = $this->db->safesql(@$_POST['email']);

			$salt = $this->db->safesql($this->core->random());
			$password = $this->core->gen_password(@$_POST['password'], $salt, $method);
			$password = $this->db->safesql($password);
			$uuid = $this->db->safesql($this->user->logintouuid(@$_POST['login']));
			$ip = $this->user->ip;

			$data = array(
				"time_create" => time(),
				"time_last" => time(),
				"firstname" => "",
				"lastname" => "",
				"gender" => 0,
				"birthday" => 0
			);

			$data = $this->db->safesql(json_encode($data));

			$tables = file(MCR_ROOT.'install/tables.sql');

			$string = "";

			foreach($tables as $key => $value){

				$value = trim($value);

				if($value=='#line'){
					$string = trim($string);

					@$this->db->obj->query($string);

					$string = "";
					continue;
				}

				$string .= $value;

			}

			$sql1 = $this->db->query("INSERT INTO `mcr_users`
											(`gid`, `login`, `email`, `password`, `uuid`, `salt`, `ip_create`, `ip_last`, `data`)
										VALUES
											('3', '$login', '$email', '$password', '$uuid', '$salt', '$ip', '$ip', '$data')");

			if(!$sql1){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_add_admin'], 2, 'install/?mode=step_3'); }

			$url = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'install'));

			$sql2 = $this->db->query("INSERT INTO `mcr_iconomy`
										(`login`, `money`, `realmoney`, `bank`)
									VALUES
										('$login', 0, 0, 0)");

			if(!$sql2){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_add_economy'], 2, 'install/?mode=step_3'); }
			
			$sql9 = $this->db->query("UPDATE `mcr_groups` SET id='0' WHERE id='4'");

			if(!$sql9){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_upd_group'], 2, 'install/?mode=step_3'); }

			$sql10 = $this->db->query("ALTER TABLE `mcr_groups` AUTO_INCREMENT=0");

			if(!$sql10){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_upd_group'], 2, 'install/?mode=step_3'); }

			$this->config->main['crypt'] = $method;

			if(!$this->config->savecfg($this->config->main, 'main.php', 'main')){
				$this->core->notify($this->lng['e_msg'], $this->lng_m['e_settings'], 2, 'install/?mode=step_3');
			}

			$_SESSION['step_3'] = true;

			@file_get_contents("http://api.webmcr.com/?do=install&domain=".$_SERVER['SERVER_NAME']);

			$this->core->notify($this->lng_m['finish'], $this->lng_m['mod_name'], 4, 'install/?mode=settings');

		}

		return $this->core->sp(MCR_ROOT."install/theme/step_3.html");
	}

}

?>