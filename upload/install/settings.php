<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $cfg, $lng, $lng_m, $user;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->cfg		= $core->cfg;
		$this->lng		= $core->lng;
		$this->lng_m	= $core->lng_m;

		$this->core->title = $this->lng_m['mod_name'].' — '.$this->lng_m['settings'];

		$bc = array(
			$this->lng_m['mod_name'] => BASE_URL."install/",
			$this->lng_m['settings'] => BASE_URL."install/?mode=settings"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	public function content(){
		if(!isset($_SESSION['step_3'])){ $this->core->notify('', '', 4, 'install/?mode=step_3'); }
		if(isset($_SESSION['settings'])){ $this->core->notify('', '', 4, 'install/?mode=finish'); }

		if(!isset($_SESSION['fs_name'])){
			$_SESSION['fs_name']		= $this->cfg->main['s_name'];
			$_SESSION['fs_about']		= $this->cfg->main['s_about'];
			$_SESSION['fs_keywords']	= $this->cfg->main['s_keywords'];
			$_SESSION['fs_from']		= $this->cfg->mail['from'];
			$_SESSION['fs_from_name']	= $this->cfg->mail['from_name'];
			$_SESSION['fs_reply']		= $this->cfg->mail['reply'];
			$_SESSION['fs_reply_name']	= $this->cfg->mail['reply_name'];
			$_SESSION['fs_smtp']		= '';
			$_SESSION['fs_smtp_host']	= $this->cfg->mail['smtp_host'];
			$_SESSION['fs_smtp_user']	= $this->cfg->mail['smtp_user'];
			$_SESSION['fs_smtp_pass']	= $this->cfg->mail['smtp_pass'];
		}

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$_SESSION['fs_name']		= $this->db->HSC(@$_POST['s_name']);
			$_SESSION['fs_about']		= $this->db->HSC(@$_POST['s_about']);
			$_SESSION['fs_keywords']	= $this->db->HSC(@$_POST['s_keywords']);
			$_SESSION['fs_from']		= $this->db->HSC(@$_POST['from']);
			$_SESSION['fs_from_name']	= $this->db->HSC(@$_POST['from_name']);
			$_SESSION['fs_reply']		= $this->db->HSC(@$_POST['reply']);
			$_SESSION['fs_reply_name']	= $this->db->HSC(@$_POST['reply_name']);
			$_SESSION['fs_smtp']		= (intval(@$_POST['smtp'])==1) ? 'selected' : '';
			$_SESSION['fs_smtp_host']	= $this->db->HSC(@$_POST['smtp_host']);
			$_SESSION['fs_smtp_user']	= $this->db->HSC(@$_POST['smtp_user']);
			$_SESSION['fs_smtp_pass']	= $this->db->HSC(@$_POST['smtp_pass']);

			$this->cfg->main['s_name'] = $this->db->HSC($this->core->safestr(@$_POST['s_name']));

			$this->cfg->main['s_about'] = $this->db->HSC($this->core->safestr(@$_POST['s_about']));

			$this->cfg->main['s_keywords'] = $this->db->HSC($this->core->safestr(@$_POST['s_keywords']));

			$url = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'install'));

			$http = (@$_SERVER["HTTPS"] == "on") ? 'https' : 'http';

			$full_url = $http.'://'.$_SERVER['HTTP_HOST'];

			$this->cfg->main['s_root'] = $url;

			$this->cfg->main['s_root_full'] = $full_url;

			$this->cfg->main['mcr_secury'] = $this->core->random(20, false);

			$this->cfg->main['install'] = false;

			$this->cfg->mail['from'] = $this->db->HSC($this->core->safestr(@$_POST['from']));

			$this->cfg->mail['from_name'] = $this->db->HSC($this->core->safestr(@$_POST['from_name']));

			$this->cfg->mail['reply'] = $this->db->HSC($this->core->safestr(@$_POST['reply']));

			$this->cfg->mail['reply_name'] = $this->db->HSC($this->core->safestr(@$_POST['reply_name']));

			$this->cfg->mail['smtp'] = (intval(@$_POST['smtp'])===1) ? true : false;

			$this->cfg->mail['smtp_host'] = $this->db->HSC($this->core->safestr(@$_POST['smtp_host']));

			$this->cfg->mail['smtp_user'] = $this->db->HSC($this->core->safestr(@$_POST['smtp_user']));

			$this->cfg->mail['smtp_pass'] = $this->db->HSC($this->core->safestr(@$_POST['smtp_pass']));

			if(!$this->cfg->savecfg($this->cfg->main, 'main.php', 'main')){
				$this->core->notify($this->lng['e_msg'], $this->lng_m['e_settings'], 2, 'install/?mode=settings');
			}

			if(!$this->cfg->savecfg($this->cfg->mail, 'mail.php', 'mail')){
				$this->core->notify($this->lng['e_msg'], $this->lng_m['e_settings'], 2, 'install/?mode=settings');
			}

			$_SESSION['settings'] = true;

			$this->core->notify($this->lng_m['finish'], $this->lng_m['mod_name'], 4, 'install/?mode=finish');

		}

		return $this->core->sp(MCR_ROOT."install/theme/settings.html");
	}

}

?>