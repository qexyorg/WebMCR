<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $cfg, $lng, $lng_m, $user;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->cfg		= $core->cfg;
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

		$time = time();

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

			$login		= $this->db->safesql(@$_POST['login']);
			$email		= $this->db->safesql(@$_POST['email']);

			$salt		= $this->db->safesql($this->core->random());
			$password	= $this->core->gen_password(@$_POST['password'], $salt, $method);
			$password	= $this->db->safesql($password);
			$uuid		= $this->db->safesql($this->user->logintouuid(@$_POST['login']));
			$ip			= $this->user->ip;

			$tables = file(MCR_ROOT.'install/tables.sql');

			$ctables	= $this->cfg->db['tables'];

			$ug_f		= $ctables['ugroups']['fields'];
			$ic_f		= $ctables['iconomy']['fields'];
			$logs_f		= $ctables['logs']['fields'];
			$us_f		= $ctables['users']['fields'];

			$string = "";

			$search = array(
				'~ug~',
				'~ug_id~', '~ug_title~', '~ug_text~', '~ug_color~', '~ug_perm~',

				'~ic~',
				'~ic_id~', '~ic_login~', '~ic_money~', '~ic_rc~', '~ic_bank~',

				'~logs~',
				'~logs_id~', '~logs_uid~', '~logs_msg~', '~logs_date~',

				'~us~',
				'~us_id~', '~us_gid~', '~us_login~', '~us_email~', '~us_pass~', '~us_uuid~', '~us_salt~', '~us_tmp~', '~us_is_skin~', '~us_is_cloak~', '~us_ip_create~', '~us_ip_last~', '~us_color~', '~us_date_reg~', '~us_date_last~', '~us_fname~', '~us_lname~', '~us_gender~', '~us_bday~', '~us_ban_server~',

				'~base_url~',
			);

			$replace = array(
				$this->cfg->tabname('ugroups'),
				$ug_f['id'], $ug_f['title'], $ug_f['text'], $ug_f['color'], $ug_f['perm'],

				$this->cfg->tabname('iconomy'),
				$ic_f['id'], $ic_f['login'], $ic_f['money'], $ic_f['rm'], $ic_f['bank'],

				$this->cfg->tabname('logs'),
				$logs_f['id'], $logs_f['uid'], $logs_f['msg'], $logs_f['date'],

				$this->cfg->tabname('users'),
				$us_f['id'], $us_f['group'], $us_f['login'], $us_f['email'], $us_f['pass'], $us_f['uuid'], $us_f['salt'], $us_f['tmp'], $us_f['is_skin'], $us_f['is_cloak'], $us_f['ip_create'], $us_f['ip_last'], $us_f['color'], $us_f['date_reg'], $us_f['date_last'], $us_f['fname'], $us_f['lname'], $us_f['gender'], $us_f['bday'], $us_f['ban_server'],

				BASE_URL,
			);

			foreach($tables as $key => $value){

				$value = trim($value);

				if($value=='#line'){
					$string = trim($string);

					@$this->db->obj->query($string);

					$string = "";
					continue;
				}

				$value = str_replace($search, $replace, $value);

				$string .= $value;

			}

			$sql1 = $this->db->query("INSERT INTO `{$this->cfg->tabname('users')}`
											(`{$us_f['group']}`, `{$us_f['login']}`, `{$us_f['email']}`, `{$us_f['pass']}`, `{$us_f['uuid']}`, `{$us_f['salt']}`, `{$us_f['ip_create']}`, `{$us_f['ip_last']}`, `{$us_f['date_reg']}`, `{$us_f['date_last']}`, `{$us_f['fname']}`, `{$us_f['lname']}`)
										VALUES
											('3', '$login', '$email', '$password', '$uuid', '$salt', '$ip', '$ip', '$time', '$time', '', '')");

			if(!$sql1){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_add_admin'], 2, 'install/?mode=step_3'); }

			$url = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'install'));

			$sql2 = $this->db->query("INSERT INTO `{$this->cfg->tabname('iconomy')}`
										(`{$ic_f['login']}`, `{$ic_f['money']}`, `{$ic_f['rm']}`, `{$ic_f['bank']}`)
									VALUES
										('$login', 0, 0, 0)");

			if(!$sql2){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_add_economy'], 2, 'install/?mode=step_3'); }
			
			$sql9 = $this->db->query("UPDATE `{$this->cfg->tabname('ugroups')}` SET `{$ug_f['id']}`='0' WHERE `{$ug_f['id']}`='4'");

			if(!$sql9){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_upd_group'], 2, 'install/?mode=step_3'); }

			$sql10 = $this->db->query("ALTER TABLE `{$this->cfg->tabname('ugroups')}` AUTO_INCREMENT=0");

			if(!$sql10){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_upd_group'], 2, 'install/?mode=step_3'); }

			$this->cfg->main['crypt'] = $method;

			if(!$this->cfg->savecfg($this->cfg->main, 'main.php', 'main')){
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