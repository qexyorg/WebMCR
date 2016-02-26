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

		$this->core->title = $this->lng_m['mod_name'].' — '.$this->lng_m['step_2'];

		$bc = array(
			$this->lng_m['mod_name'] => BASE_URL."install/",
			$this->lng_m['step_2'] => BASE_URL."install/?mode=step_2"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	public function content(){
		if(!isset($_SESSION['step_1'])){ $this->core->notify('', '', 4, 'install/?mode=step_1'); }
		if(isset($_SESSION['step_2'])){ $this->core->notify('', '', 4, 'install/?mode=step_3'); }

		if(!isset($_SESSION['f_host'])){
			$_SESSION['f_host'] = 'localhost';
			$_SESSION['f_port'] = 3306;
			$_SESSION['f_base'] = '';
			$_SESSION['f_backend'] = '';
			$_SESSION['f_user'] = 'root';
		}

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$this->config->db['host'] = @$_POST['host'];
			$_SESSION['f_host'] = $this->db->HSC(@$_POST['host']);

			$this->config->db['port'] = intval(@$_POST['port']);
			$_SESSION['f_port'] = intval(@$_POST['port']);

			$this->config->db['base'] = @$_POST['base'];
			$_SESSION['f_base'] = $this->db->HSC(@$_POST['base']);

			$this->config->db['user'] = @$_POST['user'];
			$_SESSION['f_user'] = $this->db->HSC(@$_POST['user']);

			$this->config->db['pass'] = @$_POST['pass'];

			$this->config->db['backend'] = (@$_POST['type']=='mysqli') ? 'mysqli' : 'mysql';
			$_SESSION['f_backend'] = (@$_POST['type']=='mysql') ? 'selected' : '';

			$connect = @mysql_connect($this->config->db['host'].':'.$this->config->db['port'], $this->config->db['user'], $this->config->db['pass']);

			if(!@mysql_select_db($this->config->db['base'], $connect)){
				$this->core->notify($this->lng['e_msg'], $this->lng_m['e_set_base'], 2, 'install/?mode=step_2');
			}

			if(!$this->config->savecfg($this->config->db, 'db.php', 'db')){
				$this->core->notify($this->lng['e_msg'], $this->lng_m['e_settings'], 2, 'install/?mode=step_2');
			}

			$_SESSION['step_2'] = true;

			$this->core->notify($this->lng_m['step_3'], $this->lng_m['mod_name'], 4, 'install/?mode=step_3');

		}

		return $this->core->sp(MCR_ROOT."install/theme/step_2.html");
	}

}

?>