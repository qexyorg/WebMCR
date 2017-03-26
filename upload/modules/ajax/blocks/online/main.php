<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $user, $db, $cfg;

	public function __construct($core){
		$this->core		= $core;
		$this->user		= $core->user;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;

		include(MCR_CONF_PATH.'blocks/online.php');

		$this->core->cfg_b = $cfg;
	}

	public function content(){

		$result = array(
			'guests' => 0,
			'users' => 0,
			'all' => 0,
			'list' => '',
		);

		if(!$this->core->is_access(@$this->core->cfg_b['PERMISSIONS'])){ $this->core->js_notify('', '', true, $result); }

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];
		$ug_f		= $ctables['ugroups']['fields'];

		$time = time();

		$expire = $time-$this->core->cfg_b['TIMEOUT'];

		$query = $this->db->query("SELECT `o`.online, `u`.`{$us_f['login']}`, `u`.`{$us_f['color']}`,
										`g`.`{$us_f['color']}` AS `gcolor`,
										`u_id`.`{$us_f['login']}` AS `uidlogin`,
										`g_id`.`{$us_f['color']}` AS `guidcolor`
									FROM `mcr_online` AS `o`
									LEFT JOIN `{$this->cfg->tabname('users')}` AS `u`
										ON `u`.`{$us_f['ip_last']}`=`o`.`ip`
									LEFT JOIN `{$this->cfg->tabname('users')}` AS `u_id`
										ON `u_id`.`{$us_f['id']}`=`o`.`ip`
									LEFT JOIN `{$this->cfg->tabname('ugroups')}` AS `g`
										ON `g`.`{$ug_f['id']}`=`u`.`{$us_f['group']}`
									LEFT JOIN `{$this->cfg->tabname('ugroups')}` AS `g_id`
										ON `g_id`.`{$ug_f['id']}`=`u_id`.`{$us_f['group']}`
									WHERE `o`.`date_update`>='$expire'
									GROUP BY `o`.id");

		if(!$query){ $this->core->js_notify($this->core->lng['e_sql_critical'].$this->db->error()); }

		if($this->db->num_rows($query)<=0){ $this->core->js_notify($this->core->lng['e_success'], $this->core->lng['e_success'], true, $result); }

		while($ar = $this->db->fetch_assoc($query)){
			$result['all']++;
			if(intval($ar['online'])==0){ $result['guests']++; continue; }

			$result['users']++;
			
			$color = (empty($ar[$us_f['color']])) ? $this->db->HSC($ar['gcolor']) : $this->db->HSC($ar[$us_f['color']]);
			$color = (!empty($ar['guidcolor'])) ? $this->db->HSC($ar['guidcolor']) : $color;

			$login = (!is_null($ar['uidlogin'])) ? $this->db->HSC($ar['uidlogin']) : $this->db->HSC($ar[$us_f['login']]);

			$result['list'][] = $this->core->colorize($login, $color);
		}

		if(empty($result['list'])){ $result['list'] = 'Нет зарегистрированных пользователей'; }

		$this->core->js_notify($this->core->lng['e_success'], $this->core->lng['e_success'], true, $result);
	}
}

?>