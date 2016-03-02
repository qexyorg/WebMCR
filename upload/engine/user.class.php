<?php

class user{
	// Set default system vars
	private $core, $db, $config, $lng;

	// Set default user vars
	public $email, $login, $login_v2, $group, $group_v2, $group_desc, $password, $salt, $tmp, $ip, $ip_create, $data, $permissions, $permissions_v2, $gender;

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

	public function __construct($core){
		$this->core			= $core;
		$this->db			= $core->db;
		$this->config		= $core->config;
		$this->lng			= $core->lng;

		$this->login		= $this->lng['u_group_def'];
		$this->group		= $this->lng['u_group_def'];

		$this->group_desc	= $this->lng['u_group_desc_def'];

		// Set now ip
		$this->ip	= $this->ip();

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

		$query = $this->db->query("SELECT `u`.gid, `u`.login, `u`.email, `u`.password, `u`.`salt`, `u`.`tmp`, `u`.ip_create, `u`.`data`, `u`.`is_skin`, `u`.`is_cloak`, `u`.`color`,
											`g`.title, `g`.`description`, `g`.`permissions`, `g`.`color` AS `gcolor`,
											`i`.`money`, `i`.realmoney, `i`.bank
									FROM `mcr_users` AS `u`
									INNER JOIN `mcr_groups` AS `g`
										ON `g`.id=`u`.gid
									LEFT JOIN `mcr_iconomy` AS `i`
										ON `i`.login=`u`.login
									WHERE `u`.id='$uid'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->set_unauth(); $this->core->notify(); }

		$ar			= $this->db->fetch_assoc($query);

		$tmp		= $this->db->HSC($ar['tmp']);
		$password	= $this->db->HSC($ar['password']);

		$new_hash	= $uid.$tmp.$this->ip.md5($this->config->main['mcr_secury']);

		$ar_hash	= $uid.'_'.md5($new_hash);

		// Check security auth
		if($_COOKIE['mcr_user'] !== $ar_hash){ $this->set_unauth(); $this->core->notify(); }

		$login				= $this->db->HSC($ar['login']);

		$color				= (!empty($ar['color'])) ? $this->db->HSC($ar['color']) : $this->db->HSC($ar['gcolor']);

		$group				= $this->db->HSC($ar['title']);

		$gcolor				= $this->db->HSC($ar['gcolor']);

		// Identificator
		$this->id			= $uid;

		// Group identificator
		$this->gid			= intval($ar['gid']);

		// Username
		$this->login		= $login;

		// Username
		$this->login_v2		= $this->core->colorize($login, $color);

		// E-Mail
		$this->email		= $this->db->HSC($ar['email']);

		// Password hash
		$this->password		= $password;

		// Salt of password
		$this->salt			= $ar['salt'];

		// Temp hash
		$this->tmp			= $tmp;

		// Register ip
		$this->ip_create	= $this->db->HSC($ar['ip_create']);

		// Other information
		$this->data			= json_decode($ar['data']);

		// Group title
		$this->group		= $group;

		// Group title with colorize
		$this->group_v2		= $this->core->colorize($group, $gcolor);

		// Group description
		$this->group_desc	= $this->db->HSC($ar['description']);

		// Permissions
		$this->permissions	= @json_decode($ar['permissions']);

		// Permissions
		$this->permissions_v2	= @json_decode($ar['permissions'], true);

		// Is auth status
		$this->is_auth		= true;

		// Is default skin
		$this->is_skin		= (intval($ar['is_skin'])==1) ? true : false;

		// Is isset cloak
		$this->is_cloak		= (intval($ar['is_cloak'])==1) ? true : false;

		$this->skin			= ($this->is_skin || $this->is_cloak) ? $this->login : 'default';

		$this->cloak		= ($this->is_cloak) ? $this->login : '';

		// Gender
		$this->gender		= (intval($this->data->gender)==1) ? $this->lng['gender_w'] : $this->lng['gender_m'];

		// Game money balance
		$this->money		= floatval($ar['money']);

		// Real money balance
		$this->realmoney	= floatval($ar['realmoney']);

		// Bank money balance (for plugins)
		$this->bank			= floatval($ar['bank']);

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