<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $install, $cfg, $lng;

	public function __construct($install){
		$this->install		= $install;
		$this->cfg			= $install->cfg;
		$this->lng			= $install->lng;

		$this->install->title = $this->lng['mod_name'].' — '.$this->lng['step_1'];
	}

	public function content(){
		if(!isset($_SESSION['start'])){ $this->install->notify('', '', 'install/'); }
		if(isset($_SESSION['step_1'])){ $this->install->notify('', '', 'install/?do=step_2'); }

		$_SESSION['f_host'] = (isset($_POST['host'])) ? $this->install->HSC($_POST['host']) : $this->cfg['db']['host'];

		$_SESSION['f_port'] = (isset($_POST['port'])) ? intval($_POST['port']) : $this->cfg['db']['port'];

		$_SESSION['f_base'] = (isset($_POST['base'])) ? $this->install->HSC($_POST['base']) : $this->cfg['db']['base'];

		$_SESSION['f_user'] = (isset($_POST['user'])) ? $this->install->HSC($_POST['user']) : $this->cfg['db']['user'];

		$_SESSION['f_pass'] = (isset($_POST['pass'])) ? $this->install->HSC($_POST['pass']) : $this->cfg['db']['pass'];

		$_SESSION['f_backend'] = (@$_POST['type']=='mysql') ? 'selected' : ($this->cfg['db']['backend']=='mysql') ? 'selected' : '';

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$type = (isset($_POST['type']) && $_POST['type']=='mysql') ? 'mysql' : 'mysqli';

			require_once(DIR_ROOT.'engine/db/'.$type.'.class.php');

			$db = new db($_SESSION['f_host'], $_SESSION['f_user'], $_SESSION['f_pass'], $_SESSION['f_base'], $_SESSION['f_port']);

			$error = $db->error();

			if(!empty($error)){
				$this->install->notify($this->lng['e_connection'].' | '.$db->error(), $this->lng['e_msg'], 'install/?do=step_1');
			}

			$this->cfg['db']['host'] = $_SESSION['f_host'];
			$this->cfg['db']['port'] = $_SESSION['f_port'];
			$this->cfg['db']['base'] = $_SESSION['f_base'];
			$this->cfg['db']['user'] = $_SESSION['f_user'];
			$this->cfg['db']['pass'] = $_SESSION['f_pass'];
			$this->cfg['db']['backend'] = $type;

			if(!$this->install->savecfg($this->cfg['db'], 'db.php', 'db')){
				$this->core->notify($this->lng['e_msg'], $this->lng_m['e_write'], 'install/?do=step_1');
			}

			$tables = file(DIR_INSTALL.'tables.sql');

			$ctables	= $this->cfg['db']['tables'];

			$ug_f		= $ctables['ugroups']['fields'];
			$ic_f		= $ctables['iconomy']['fields'];
			$logs_f		= $ctables['logs']['fields'];
			$us_f		= $ctables['users']['fields'];

			@$db->query("SET GLOBAL sql_mode='NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");

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
				$ctables['ugroups']['name'],
				$ug_f['id'], $ug_f['title'], $ug_f['text'], $ug_f['color'], $ug_f['perm'],

				$ctables['iconomy']['name'],
				$ic_f['id'], $ic_f['login'], $ic_f['money'], $ic_f['rm'], $ic_f['bank'],

				$ctables['logs']['name'],
				$logs_f['id'], $logs_f['uid'], $logs_f['msg'], $logs_f['date'],

				$ctables['users']['name'],
				$us_f['id'], $us_f['group'], $us_f['login'], $us_f['email'], $us_f['pass'], $us_f['uuid'], $us_f['salt'], $us_f['tmp'], $us_f['is_skin'], $us_f['is_cloak'], $us_f['ip_create'], $us_f['ip_last'], $us_f['color'], $us_f['date_reg'], $us_f['date_last'], $us_f['fname'], $us_f['lname'], $us_f['gender'], $us_f['bday'], $us_f['ban_server'],

				URL_ROOT,
			);

			foreach($tables as $key => $value){

				$value = trim($value);

				if($value=='#line'){
					$string = trim($string);

					@$db->query($string);

					$string = "";
					continue;
				}

				$value = str_replace($search, $replace, $value);

				$string .= $value;

			}

			$query = $db->query("UPDATE `{$ctables['ugroups']['name']}` SET `{$ug_f['id']}`='0' WHERE `{$ug_f['id']}`='4'");

			if(!$query){ $this->install->notify($this->lng['e_upd_group'], $this->lng['e_msg'], 'install/?do=step_1'); }

			$query = $db->query("ALTER TABLE `{$ctables['ugroups']['name']}` AUTO_INCREMENT=0");

			if(!$query){ $this->install->notify($this->lng['e_upd_group'], $this->lng['e_msg'], 'install/?do=step_1'); }

			$_SESSION['step_1'] = true;

			$this->install->notify($this->lng_m['step_2'], $this->lng_m['db_settings'], 'install/?do=step_2');

		}

		$data = array();

		return $this->install->sp('step_1.html', $data);
	}

}

?>