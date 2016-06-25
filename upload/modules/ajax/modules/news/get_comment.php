<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $cfg, $user, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;
		$this->user		= $core->user;
		$this->lng		= $core->load_language('news');
	}

	public function content(){

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->js_notify($this->core->lng['e_hack']); }

		if(!$this->user->is_auth){ $this->core->js_notify($this->lng['com_unauth']); }

		$id = intval(@$_POST['id']);

		$nid = intval(@$_POST['nid']);

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$query = $this->db->query("SELECT `c`.text_bb, `c`.`data`, `u`.`{$us_f['login']}`
									FROM `mcr_comments` AS `c`
									LEFT JOIN `{$this->cfg->tabname('users')}` AS `u`
										ON `c`.uid=`u`.`{$us_f['id']}`
									WHERE `c`.nid='$nid' AND `c`.id='$id'");

		if(!$query){ $this->core->js_notify($this->core->lng['e_sql_critical']); }

		if($this->db->num_rows($query)<=0){ $this->core->js_notify($this->core->lng['e_hack']); }

		$ar = $this->db->fetch_assoc($query);
		
		$data = json_decode($ar['data'], true);

		$json = array(
			'create' => date("d.m.Y - H:i:s", $data['time_create']),
			'login' => $this->db->HSC($ar[$us_f['login']]),
			'text' => $this->db->HSC($ar['text_bb']),
		);

		$this->core->js_notify($this->lng['com_load_success'], $this->core->lng['e_success'], true, $json);
	}

}

?>