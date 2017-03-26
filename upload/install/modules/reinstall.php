<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $install, $cfg, $lng;

	public function __construct($install){
		$this->install		= $install;
		$this->cfg			= $install->cfg;
		$this->lng			= $install->lng;

		$this->install->title = $this->lng['mod_name'].' — '.$this->lng['reinstall'];
	}

	public function content(){

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(intval(@$_POST['type'])!=1){ $this->install->notify(); }

			$tables = array('mcr_comments', 'mcr_files', 'mcr_iconomy', 'mcr_logs', 'mcr_menu', 'mcr_menu_adm', 'mcr_menu_adm_icons',
							'mcr_monitoring', 'mcr_news_views', 'mcr_news_votes', 'mcr_online', 'mcr_permissions',
							'mcr_statics', 'mcr_news', 'mcr_users', 'mcr_news_cats', 'mcr_menu_adm_groups', 'mcr_groups');



			require_once(DIR_ROOT.'engine/db/'.$this->cfg['db']['backend'].'.class.php');

			$db = new db($this->cfg['db']['host'], $this->cfg['db']['user'], $this->cfg['db']['pass'], $this->cfg['db']['base'], $this->cfg['db']['port']);

			$error = $db->error();

			if(empty($error)){

				$tables = implode(', ', $tables);

				$drop = $db->query("DROP TABLE IF EXISTS $tables");

				if(!$drop){ $this->install->notify($this->lng['e_sql'].__LINE__, $this->lng['e_msg'], 'install/?do=reinstall'); }

				$this->cfg['main']['install'] = true;
				$this->cfg['main']['debug'] = true;

				$this->install->savecfg($this->cfg['main'], 'main.php', 'main');

				$this->cfg['db']['host'] = '127.0.0.1';
				$this->cfg['db']['user'] = 'root';
				$this->cfg['db']['pass'] = '';
				$this->cfg['db']['base'] = 'database';
				$this->cfg['db']['port'] = 3306;

				session_destroy();

				$this->install->savecfg($this->cfg['db'], 'db.php', 'db');
			}

			$this->install->notify('', '', 'install/');

		}

		return $this->install->sp('reinstall.html');
	}

}

?>