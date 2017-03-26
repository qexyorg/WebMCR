<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $install, $cfg, $lng;

	public function __construct($install){
		$this->install		= $install;
		$this->cfg			= $install->cfg;
		$this->lng			= $install->lng;

		$this->install->title = $this->lng['mod_name'].' — '.$this->lng['step_3'];
	}

	public function content(){
		if(!isset($_SESSION['step_2'])){ $this->install->notify('', '', 'install/?do=step_2'); }
		if(isset($_SESSION['step_3'])){ $this->install->notify('', '', 'install/?do=finish'); }

		$_SESSION['fs_name']		= $this->cfg['main']['s_name'];
		$_SESSION['fs_about']		= $this->cfg['main']['s_about'];
		$_SESSION['fs_keywords']	= $this->cfg['main']['s_keywords'];
		$_SESSION['fs_from']		= $this->cfg['mail']['from'];
		$_SESSION['fs_from_name']	= $this->cfg['mail']['from_name'];
		$_SESSION['fs_reply']		= $this->cfg['mail']['reply'];
		$_SESSION['fs_reply_name']	= $this->cfg['mail']['reply_name'];
		$_SESSION['fs_smtp']		= ($this->cfg['mail']['smtp']) ? 'selected' : '';
		$_SESSION['fs_smtp_host']	= $this->cfg['mail']['smtp_host'];
		$_SESSION['fs_smtp_user']	= $this->cfg['mail']['smtp_user'];
		$_SESSION['fs_smtp_pass']	= $this->cfg['mail']['smtp_pass'];
		$_SESSION['fs_smtp_tls']	= ($this->cfg['mail']['smtp_tls']) ? 'selected' : '';

		$time = time();

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$this->cfg['main']['s_name'] = $this->install->HSC(@$_POST['s_name']);

			$this->cfg['main']['s_about'] = $this->install->HSC(@$_POST['s_about']);

			$this->cfg['main']['s_keywords'] = $this->install->HSC(@$_POST['s_keywords']);

			$this->cfg['main']['s_root'] = URL_ROOT;

			$this->cfg['main']['s_root_full'] = URL_ROOT_FULL;

			$this->cfg['main']['mcr_secury'] = $this->install->random(20, false);

			$this->cfg['main']['install'] = false;

			$this->cfg['mail']['from'] = $this->install->HSC(@$_POST['from']);

			$this->cfg['mail']['from_name'] = $this->install->HSC(@$_POST['from_name']);

			$this->cfg['mail']['reply'] = $this->install->HSC(@$_POST['reply']);

			$this->cfg['mail']['reply_name'] = $this->install->HSC(@$_POST['reply_name']);

			$this->cfg['mail']['smtp'] = (intval(@$_POST['smtp'])===1) ? true : false;

			$this->cfg['mail']['smtp_host'] = $this->install->HSC(@$_POST['smtp_host']);

			$this->cfg['mail']['smtp_user'] = $this->install->HSC(@$_POST['smtp_user']);

			$this->cfg['mail']['smtp_pass'] = $this->install->HSC(@$_POST['smtp_pass']);

			$this->cfg['mail']['smtp_tls'] = (intval(@$_POST['smtp_tls'])===1) ? true : false;

			if(!$this->install->savecfg($this->cfg['main'], 'main.php', 'main')){
				$this->install->notify($this->lng['e_write'], $this->lng['e_msg'], 'install/?mode=finish');
			}

			if(!$this->install->savecfg($this->cfg['mail'], 'mail.php', 'mail')){
				$this->install->notify($this->lng['e_write'], $this->lng['e_msg'], 'install/?mode=finish');
			}

			$_SESSION['step_3'] = true;

			if(!($api = file_get_contents("http://api.webmcr.com/?do=install&domain=".$_SERVER['SERVER_NAME']))){ /* SUCCESS */ }

			$this->install->notify($this->lng_m['finish'], $this->lng_m['mod_name'], 'install/?mode=settings');

		}

		$data = array();

		return $this->install->sp('step_3.html', $data);
	}

}

?>