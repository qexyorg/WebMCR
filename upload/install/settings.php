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

		$this->core->title = 'Установка — Настройки';

		$bc = array(
			'Установка' => BASE_URL."install/",
			'Настройки' => BASE_URL."install/?mode=settings"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	public function content(){
		if(!isset($_SESSION['step_3'])){ $this->core->notify('', '', 4, 'install/?mode=step_3'); }
		if(isset($_SESSION['settings'])){ $this->core->notify('', '', 4, 'install/?mode=finish'); }

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$this->config->main['s_name'] = $this->db->HSC($this->core->safestr(@$_POST['s_name']));

			$this->config->main['s_about'] = $this->db->HSC($this->core->safestr(@$_POST['s_about']));

			$this->config->main['s_keywords'] = $this->db->HSC($this->core->safestr(@$_POST['s_keywords']));

			$this->config->main['s_keywords'] = $this->db->HSC($this->core->safestr(@$_POST['s_keywords']));

			$url = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'install'));

			$http = (@$_SERVER["HTTPS"] == "on") ? 'https' : 'http';

			$full_url = $http.'://'.$_SERVER['HTTP_HOST'];

			$this->config->main['s_root'] = $url;

			$this->config->main['s_root_full'] = $full_url;

			$this->config->main['mcr_secury'] = $this->core->random(20, false);

			$this->config->main['install'] = false;

			if(!$this->core->savecfg($this->config->main, 'main.php', 'main')){
				$this->core->notify('Ошибка!', 'Настройки не могут быть сохранены', 2, 'install/?mode=settings');
			}

			$this->config->mail['from'] = $this->db->HSC($this->core->safestr(@$_POST['from']));

			$this->config->mail['from_name'] = $this->db->HSC($this->core->safestr(@$_POST['from_name']));

			$this->config->mail['reply'] = $this->db->HSC($this->core->safestr(@$_POST['reply']));

			$this->config->mail['reply_name'] = $this->db->HSC($this->core->safestr(@$_POST['reply_name']));

			$this->config->mail['smtp'] = (intval(@$_POST['from'])===1) ? true : false;

			$this->config->mail['smtp_host'] = $this->db->HSC($this->core->safestr(@$_POST['smtp_host']));

			$this->config->mail['smtp_user'] = $this->db->HSC($this->core->safestr(@$_POST['smtp_user']));

			$this->config->mail['smtp_pass'] = $this->db->HSC($this->core->safestr(@$_POST['smtp_pass']));

			if(!$this->core->savecfg($this->config->mail, 'mail.php', 'mail')){
				$this->core->notify('Ошибка!', 'Настройки не могут быть сохранены', 2, 'install/?mode=settings');
			}

			$_SESSION['settings'] = true;

			$this->core->notify('Шаг #3', 'Установка', 4, 'install/?mode=finish');

		}

		return $this->core->sp(MCR_ROOT."install/theme/settings.html");
	}

}

?>