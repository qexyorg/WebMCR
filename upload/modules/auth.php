<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $user, $cfg, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->cfg		= $core->cfg;
		$this->lng		= $core->lng_m;
	}

	public function content(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify('Hacking Attempt!'); }
		
		if($this->user->is_auth){ $this->core->notify('', $this->lng["e_already"], 1); }

		$login = $this->db->safesql($_POST['login']);
		$remember = (isset($_POST['remember']) && intval($_POST['remember'])==1) ? true : false;

		$ctables	= $this->cfg->db['tables'];
		$ug_f		= $ctables['ugroups']['fields'];
		$us_f		= $ctables['users']['fields'];

		$query = $this->db->query("SELECT `u`.`{$us_f['id']}`, `u`.`{$us_f['pass']}`, `u`.`{$us_f['salt']}`,
											`g`.`{$ug_f['perm']}`
									FROM `{$this->cfg->tabname('users')}` AS `u`
									INNER JOIN `{$this->cfg->tabname('ugroups')}` AS `g`
										ON `g`.`{$ug_f['id']}`=`u`.`{$us_f['group']}`
									WHERE `u`.`{$us_f['login']}`='$login' OR `u`.`{$us_f['email']}`='$login'
									LIMIT 1");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng["e_msg"], $this->lng['e_wrong_pass']); }

		$ar = $this->db->fetch_assoc($query);

		$uid = intval($ar[$us_f['id']]);

		$permissions = json_decode($ar[$ug_f['perm']], true);

		$password = $this->user->auth->createHash(@$_POST['password'], $ar[$us_f['salt']]);

		if(!$this->user->auth->authentificate(@$_POST['password'], $ar[$us_f['pass']], $ar[$us_f['salt']])){ $this->core->notify($this->core->lng["e_msg"], $this->lng['e_wrong_pass']); }

		$time = time();

		$new_tmp = $this->db->safesql($this->user->auth->createTmp());

		$new_ip = $this->user->ip;
		$password = $this->db->safesql($password);

		$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}`
									SET `{$us_f['tmp']}`='$new_tmp', `{$us_f['ip_last']}`='$new_ip', `{$us_f['date_last']}`='$time'
									WHERE `{$us_f['id']}`='$uid'
									LIMIT 1");

		if(!$update){ $this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical']); }

		if(!@$permissions['sys_auth']){ $this->core->notify($this->core->lng['403'], $this->lng['e_access'], 2, '?mode=403'); }

		$new_hash = $uid.$new_tmp.$new_ip.md5($this->cfg->main['mcr_secury']);

		$new_hash = $uid.'_'.md5($new_hash);

		$safetime = ($remember) ? 3600*24*30+time() : time()+3600;

		setcookie("mcr_user", $new_hash, $safetime, '/');

		// Лог действия
		$this->db->actlog($this->lng['log_auth'], $this->user->id);

		$this->core->notify($this->core->lng['e_success'], $this->lng['e_success'], 3);
	}

}

?>