<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $config, $lng, $user;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->config	= $core->config;
		$this->lng		= $core->lng;

		$this->core->title = 'Установка — Шаг #2';

		$bc = array(
			'Установка' => BASE_URL."install/",
			'Шаг #2' => BASE_URL."install/?mode=step_2"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	public function content(){
		if(!isset($_SESSION['step_1'])){ $this->core->notify('', '', 4, 'install/?mode=step_1'); }
		if(isset($_SESSION['step_2'])){ $this->core->notify('', '', 4, 'install/?mode=step_3'); }

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$this->config->db['host'] = $this->db->HSC(@$_POST['host']);

			$this->config->db['port'] = intval(@$_POST['port']);

			$this->config->db['base'] = $this->db->HSC(@$_POST['base']);

			$this->config->db['user'] = $this->db->HSC(@$_POST['user']);

			$this->config->db['pass'] = $this->db->HSC(@$_POST['pass']);

			$this->config->db['backend'] = (@$_POST['type']=='mysqli') ? 'mysqli' : 'mysql';

			$connect = @mysql_connect($this->config->db['host'].':'.$this->config->db['port'], $this->config->db['user'], $this->config->db['pass']);

			if(!@mysql_select_db($this->config->db['base'], $connect)){
				$this->core->notify('Ошибка!', 'Неверно указаны данные для подключения к базе', 2, 'install/?mode=step_2');
			}

			if(!$this->core->savecfg($this->config->db, 'db.php', 'db')){
				$this->core->notify('Ошибка!', 'Настройки не могут быть сохранены', 2, 'install/?mode=step_2');
			}

			$_SESSION['step_2'] = true;

			$this->core->notify('Шаг #3', 'Установка', 4, 'install/?mode=step_3');

		}

		return $this->core->sp(MCR_ROOT."install/theme/step_2.html");
	}

}

?>