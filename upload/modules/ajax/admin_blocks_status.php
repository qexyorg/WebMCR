<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $cfg, $user, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;
		$this->user		= $core->user;
		$this->lng		= $core->lng_m;
	}

	private function change_status($act){

		$ids = @$_POST['ids'];

		$status = ($act=='enable') ? true : false;

		if(empty($ids)){ $this->core->js_notify($this->lng['ams_block_not_selected']); }

		$ids = explode(',', $ids);

		foreach($ids as $key => $mod){
			if(!file_exists(MCR_CONF_PATH.'blocks/'.$mod.'.php')){ continue; }
			include(MCR_CONF_PATH.'blocks/'.$mod.'.php');

			if(!isset($cfg['ENABLE'])){ continue; }

			$cfg['ENABLE'] = $status;

			if(!$this->cfg->savecfg($cfg, 'blocks/'.$mod.'.php', 'cfg')){ continue; }
		}

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_change_abs'], $this->user->id);

		$this->core->js_notify($this->lng['ok'], $this->lng['ok'], true);
	}

	public function content(){

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->js_notify($this->lng['e_hack']); }

		$act = @$_POST['act'];

		switch($act){
			case 'enable':
			case 'disable':
				$this->change_status($act);
			break;

			default: $this->core->js_notify($this->lng['e_hack']); break;
		}

		$this->core->js_notify($this->lng['e_hack']);
	}

}

?>