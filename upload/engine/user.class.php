<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class user{
	// Set default system vars
	private $core, $db, $cfg, $lng;

	// Set default user vars
	public $email, $login, $login_v2, $group, $group_v2, $uuid, $group_desc, $password, $salt, $tmp, $ip, $ip_create, $data, $permissions, $permissions_v2;
	public $gender = 0;
	public $time_create = 0;
	public $time_last = 0;
	public $firstname = '';
	public $lastname = '';
	public $birthday = 0;
	public $id = 0;
	public $is_auth = false;
	public $is_skin = false;
	public $is_cloak = false;
	public $skin = 'default';
	public $cloak = '';
	public $money= 0;
	public $realmoney = 0;
	public $bank = 0;
	public $gid = -1;
	public $auth;

	public function __construct($core){
		$this->core			= $core;
		$this->db			= $core->db;
		$this->cfg			= $core->cfg;
		$this->lng			= $core->lng;

		$this->login		= $this->lng['u_group_def'];
		$this->group		= $this->lng['u_group_def'];

		$this->group_desc	= $this->lng['u_group_desc_def'];

		// Set now ip
		$this->ip			= $this->ip();

		$this->auth			= $this->load_auth();

		// Check cookies
		if(!isset($_COOKIE['mcr_user'])){
			$perm_ar = @$this->get_default_permissions();
			$this->permissions = $perm_ar[0];
			$this->permissions_v2 = $perm_ar[1];
			return false;
		}

		$cookie	= explode("_", $_COOKIE['mcr_user']);

		if(!isset($cookie[0], $cookie[1])){ $this->set_unauth(); $this->core->notify(); }

		$uid	= intval($cookie[0]);
		$hash	= $cookie[1];

		$ctables	= $this->cfg->db['tables'];

		$ug_f	= $ctables['ugroups']['fields'];
		$us_f	= $ctables['users']['fields'];
		$ic_f	= $ctables['iconomy']['fields'];

		$query = $this->db->query("SELECT `u`.`{$us_f['group']}`, `u`.`{$us_f['login']}`, `u`.`{$us_f['email']}`, `u`.`{$us_f['pass']}`, `u`.`{$us_f['salt']}`,
											`u`.`{$us_f['tmp']}`, `u`.`{$us_f['ip_create']}`, `u`.`{$us_f['date_reg']}`, `u`.`{$us_f['date_last']}`,
											`u`.`{$us_f['fname']}`, `u`.`{$us_f['lname']}`, `u`.`{$us_f['gender']}`, `u`.`{$us_f['bday']}`,
											`u`.`{$us_f['is_skin']}`, `u`.`{$us_f['is_cloak']}`, `u`.`{$us_f['color']}`, `u`.`{$us_f['uuid']}`,
											`g`.`{$ug_f['title']}`, `g`.`{$ug_f['text']}`, `g`.`{$ug_f['perm']}`, `g`.`{$ug_f['color']}` AS `gcolor`,
											`i`.`{$ic_f['money']}`, `i`.`{$ic_f['rm']}`, `i`.`{$ic_f['bank']}`
									FROM `{$this->cfg->tabname('users')}` AS `u`
									INNER JOIN `{$this->cfg->tabname('ugroups')}` AS `g`
										ON `g`.`{$ug_f['id']}`=`u`.`{$us_f['group']}`
									LEFT JOIN `{$this->cfg->tabname('iconomy')}` AS `i`
										ON `i`.`{$ic_f['login']}`=`u`.`{$us_f['login']}`
									WHERE `u`.`{$us_f['id']}`='$uid'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->set_unauth(); $this->core->notify(); }

		$ar			= $this->db->fetch_assoc($query);

		$tmp		= $this->db->HSC($ar[$us_f['tmp']]);
		$password	= $this->db->HSC($ar[$us_f['pass']]);

		$new_hash	= $uid.$tmp.$this->ip.md5($this->cfg->main['mcr_secury']);

		$ar_hash	= $uid.'_'.md5($new_hash);

		// Check security auth
		if($_COOKIE['mcr_user'] !== $ar_hash){ $this->set_unauth(); $this->core->notify(); }

		$login				= $this->db->HSC($ar[$us_f['login']]);

		$color				= (!empty($ar[$us_f['color']])) ? $this->db->HSC($ar[$us_f['color']]) : $this->db->HSC($ar['gcolor']);

		$group				= $this->db->HSC($ar[$ug_f['title']]);

		$gcolor				= $this->db->HSC($ar['gcolor']);

		// Identificator
		$this->id			= $uid;

		// Group identificator
		$this->gid			= intval($ar[$us_f['group']]);

		// Username
		$this->login		= $login;

		// Username
		$this->login_v2		= $this->core->colorize($login, $color);

		// E-Mail
		$this->email		= $this->db->HSC($ar[$us_f['email']]);

		// UUID
		$this->uuid			= $this->db->HSC($ar[$us_f['uuid']]);

		// Password hash
		$this->password		= $password;

		// Salt of password
		$this->salt			= $ar[$us_f['salt']];

		// Temp hash
		$this->tmp			= $tmp;

		// Register ip
		$this->ip_create	= $this->db->HSC($ar[$us_f['ip_create']]);

		// Group title
		$this->group		= $group;

		// Group title with colorize
		$this->group_v2		= $this->core->colorize($group, $gcolor);

		// Group description
		$this->group_desc	= $this->db->HSC($ar[$ug_f['text']]);

		// Permissions
		$this->permissions	= @json_decode($ar[$ug_f['perm']]);

		// Permissions
		$this->permissions_v2	= @json_decode($ar[$ug_f['perm']], true);

		// Is auth status
		$this->is_auth		= true;

		// Is default skin
		$this->is_skin		= (intval($ar[$us_f['is_skin']])==1) ? true : false;

		// Is isset cloak
		$this->is_cloak		= (intval($ar[$us_f['is_cloak']])==1) ? true : false;

		$this->skin			= ($this->is_skin || $this->is_cloak) ? $this->login : 'default';

		$this->cloak		= ($this->is_cloak) ? $this->login : '';

		// Gender
		$this->gender		= (intval($ar[$us_f['gender']])==1 || $ar[$us_f['gender']]=='female') ? $this->lng['gender_w'] : $this->lng['gender_m'];

		$this->time_create	= intval($ar[$us_f['date_reg']]);

		$this->time_last	= intval($ar[$us_f['date_last']]);

		$this->firstname	= $this->db->HSC($ar[$us_f['fname']]);

		$this->lastname		= $this->db->HSC($ar[$us_f['lname']]);

		$this->birthday		= intval($ar[$us_f['bday']]);

		// Game money balance
		$this->money		= floatval($ar[$ic_f['money']]);

		// Real money balance
		$this->realmoney	= floatval($ar[$ic_f['rm']]);

		// Bank money balance (for plugins)
		$this->bank			= floatval($ar[$ic_f['bank']]);

	}

	private function load_auth(){
		if(!file_exists(MCR_LIBS_PATH.'auth/'.$this->cfg->main['p_logic'].'.php')){ exit('Auth Type Error!'); }

		require_once(MCR_LIBS_PATH.'auth/'.$this->cfg->main['p_logic'].'.php');

		return new auth($this->core);
	}

	public function logintouuid($string){
		$string = "OfflinePlayer:".$string;
		$val = md5($string, true);
		$byte = array_values(unpack('C16', $val));

		$tLo = ($byte[0] << 24) | ($byte[1] << 16) | ($byte[2] << 8) | $byte[3];
		$tMi = ($byte[4] << 8) | $byte[5];
		$tHi = ($byte[6] << 8) | $byte[7];
		$csLo = $byte[9];
		$csHi = $byte[8] & 0x3f | (1 << 7);

		if (pack('L', 0x6162797A) == pack('N', 0x6162797A)) {
			$tLo = (($tLo & 0x000000ff) << 24) | (($tLo & 0x0000ff00) << 8) | (($tLo & 0x00ff0000) >> 8) | (($tLo & 0xff000000) >> 24);
			$tMi = (($tMi & 0x00ff) << 8) | (($tMi & 0xff00) >> 8);
			$tHi = (($tHi & 0x00ff) << 8) | (($tHi & 0xff00) >> 8);
		}

		$tHi &= 0x0fff;
		$tHi |= (3 << 12);

		$uuid = sprintf(
			'%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x',
			$tLo, $tMi, $tHi, $csHi, $csLo,
			$byte[10], $byte[11], $byte[12], $byte[13], $byte[14], $byte[15]
		);
		return $uuid;
	}

	public function update_default_permissions(){

		$query = $this->db->query("SELECT `value`, `type`, `default` FROM `mcr_permissions`");

		if(!$query || $this->db->num_rows($query)<=0){ return; }

		$array = array();

		while($ar = $this->db->fetch_assoc($query)){

			switch($ar['type']){
				case 'integer':
					$array[$ar['value']] = intval($ar['default']);
				break;

				case 'float':
					$array[$ar['value']] = floatval($ar['default']);
				break;

				case 'string':
					$array[$ar['value']] = $this->db->safesql($ar['default']);
				break;

				default:
					$array[$ar['value']] = ($ar['default']=='true') ? true : false;
				break;
			}

		}

		$permissions = json_encode($array);

		@file_put_contents(MCR_CACHE_PATH.'permissions', $permissions);

		return $permissions;
	}

	public function get_default_permissions(){
		if(file_exists(MCR_CACHE_PATH.'permissions')){
			$json = file_get_contents(MCR_CACHE_PATH.'permissions');
			$array = json_decode($json, true);
			$object = json_decode($json);

			return array($object, $array);
		}

		$permissions = @$this->update_default_permissions();

		return array(json_decode($permissions), json_decode($permissions, true));
	}

	public function set_unauth(){
		if(isset($_COOKIE['mcr_user'])){ setcookie("mcr_user", "", time()-3600, '/'); }

		return true;
	}

	private function ip(){

		if(!empty($_SERVER['HTTP_CF_CONNECTING_IP'])){
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		}elseif(!empty($_SERVER['HTTP_X_REAL_IP'])){
			$ip = $_SERVER['HTTP_X_REAL_IP'];
		}elseif(!empty($_SERVER['HTTP_CLIENT_IP'])){
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return mb_substr($ip, 0, 16, "UTF-8");
	}
}

?>