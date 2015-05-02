<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $config, $user, $lng;

	public function __construct($core){
		$this->core = $core;
		$this->db	= $core->db;
		$this->config = $core->config;
		$this->user	= $core->user;
		$this->lng	= $core->lng;

		$this->core->title = $this->lng['t_admin'].' — Настройки';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Настройки' => BASE_URL."?mode=admin&do=settings"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function themes($select=''){

		$scan = scandir(MCR_ROOT.'themes/');

		$compare = array("ThemeName", "Author", "AuthorUrl", "About", "Version");

		ksort($compare);

		ob_start();

		foreach($scan as $key => $value) {
			if($value=='.' || $value=='..' || !is_dir(MCR_ROOT.'themes/'.$value)){ continue; }

			if(!file_exists(MCR_ROOT.'themes/'.$value.'/theme.php')){ continue; }

			require(MCR_ROOT.'themes/'.$value.'/theme.php');

			$uniq = array_keys($theme);

			ksort($uniq);

			if($uniq!==$compare){ continue; }

			$selected = ($value==$select) ? 'selected' : '';

			echo '<option value="'.$value.'" '.$selected.'>'.$theme['ThemeName'].'</option>';
		}

		return ob_get_clean();
	}

	private function captcha($select=0){

		$select = intval($select);

		ob_start();

		foreach($this->core->captcha as $key => $value){

			$selected = ($key == $select) ? 'selected' : '';

			echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
		}

		return ob_get_clean();
	}

	private function is_theme_exist($var){
		$scan = scandir(MCR_ROOT.'themes/');

		$scan = array_flip($scan);

		if(isset($scan['.'])){ unset($scan['.']); }
		if(isset($scan['..'])){ unset($scan['..']); }

		if(!isset($scan[$var])){ return false; }

		if(!file_exists(MCR_ROOT.'themes/'.$var.'/theme.php')){ return false; }

		include(MCR_ROOT.'themes/'.$var.'/theme.php');

		$uniq = array_keys($theme);

		rsort($uniq);

		$compare = array("ThemeName", "Author", "AuthorUrl", "About", "Version");

		rsort($compare);

		if($uniq!==$compare){ return false; }

		return true;
	}

	private function is_captcha_exist($id=0){
		$id = intval($id);
		if(!isset($this->core->captcha[$id])){ return false; }

		return true;
	}

	private function main(){

		$cfg = $this->config->main;

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$cfg['s_name']		= $this->core->safestr(@$_POST['s_name']);

			$cfg['s_about']		= $this->core->safestr(@$_POST['s_about']);

			$cfg['s_keywords']	= $this->core->safestr(@$_POST['s_keywords']);

			$cfg['s_dpage']		= $this->core->safestr(@$_POST['s_dpage']);

			$s_theme = $this->core->safestr(@$_POST['s_theme']);
			if(!$this->is_theme_exist($s_theme)){ $this->core->notify($this->lng["e_msg"], "Шаблон указан некорректно", 2, '?mode=admin&do=settings'); }
			$cfg['s_theme'] = $s_theme;

			$cfg['log']			= (intval(@$_POST['log']) === 1) ? true : false;

			$cfg['debug']		= (intval(@$_POST['debug']) === 1) ? true : false;

			$captcha = intval(@$_POST['captcha']);

			if(!$this->is_captcha_exist($captcha)){ $this->core->notify($this->lng["e_msg"], "Капча указана некорректно", 2, '?mode=admin&do=settings'); }
			$cfg['captcha']		= $captcha;

			$cfg['rc_public']	= $this->core->safestr(@$_POST['rc_public']);

			$cfg['rc_private']	= $this->core->safestr(@$_POST['rc_private']);

			$cfg['kc_public']	= $this->core->safestr(@$_POST['kc_public']);

			$cfg['kc_private']	= $this->core->safestr(@$_POST['kc_private']);

			$cfg['mon_type']	= (intval(@$_POST['mon_type']) === 1) ? 1 : 0;

			if(!$this->core->savecfg($cfg)){ $this->core->notify($this->lng["e_msg"], "Не удалось сохранить файл конфигурации", 2, '?mode=admin&do=settings'); }
			
			$this->core->notify($this->lng["e_success"], "Настройки успешно сохранены", 3, '?mode=admin&do=settings');
		}

		$data = array(
			"THEMES"		=> $this->themes($cfg['s_theme']),
			"CFG"			=> $cfg,
			"LOG"			=> ($cfg['log']) ? 'selected' : '',
			"DEBUG"			=> ($cfg['debug']) ? 'selected' : '',
			"REG_ACCEPT"	=> ($cfg['reg_accept']) ? 'selected' : '',
			"MON_TYPE"		=> ($cfg['mon_type']==1) ? 'selected' : '',
			"CAPTHA"		=> $this->captcha($cfg['captcha']),
		);

		ob_start();

		echo $this->core->sp(MCR_THEME_MOD."admin/settings/main.html", $data);

		return ob_get_clean();
	}

	private function to_int_keys($array=array()){
		if(empty($array)){ return false; }

		$cfg = $this->config->pagin;

		foreach($array as $key => $value){
			$cfg[$key] = (intval($value)<=0) ? 1 : intval($value);
		}

		return $cfg;
	}

	private function pagin(){

		$cfg = $this->config->pagin;

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$post = $_POST;

			unset($post['mcr_secure']);
			unset($post['submit']);

			$cfg_keys = array_keys($cfg);
			rsort($cfg_keys);

			$post_keys = array_keys($post);
			rsort($post_keys);

			if($cfg_keys!==$post_keys){ $this->core->notify($this->lng["e_msg"], "Неверная хэш-сумма полей", 2, '?mode=admin&do=settings&op=pagin'); }

			$cfg = $this->to_int_keys($post);

			if(!$this->core->savecfg($cfg, 'pagin.php', 'pagin')){ $this->core->notify($this->lng["e_msg"], "Не удалось сохранить файл конфигурации", 2, '?mode=admin&do=settings&op=pagin'); }
			
			$this->core->notify($this->lng["e_success"], "Настройки успешно сохранены", 3, '?mode=admin&do=settings&op=pagin');
		}

		$data = array(
			"CFG"			=> $cfg
		);

		ob_start();

		echo $this->core->sp(MCR_THEME_MOD."admin/settings/pagin.html", $data);

		return ob_get_clean();
	}

	private function _mail(){

		$cfg = $this->config->mail;

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$cfg['smtp']			= (intval(@$_POST['smtp']) === 1) ? true : false;

			$cfg['from']			= $this->core->safestr(@$_POST['from']);

			$cfg['from_name']		= $this->core->safestr(@$_POST['from_name']);

			$cfg['reply']			= $this->core->safestr(@$_POST['reply']);

			$cfg['reply_name']		= $this->core->safestr(@$_POST['reply_name']);

			$cfg['smtp_host']		= $this->core->safestr(@$_POST['smtp_host']);

			$cfg['smtp_user']		= $this->core->safestr(@$_POST['smtp_user']);

			$cfg['smtp_pass']		= $this->core->safestr(@$_POST['smtp_pass']);

			if(!$this->core->savecfg($cfg, 'mail.php', 'mail')){ $this->core->notify($this->lng["e_msg"], "Не удалось сохранить файл конфигурации", 2, '?mode=admin&do=settings&op=mail'); }
			
			$this->core->notify($this->lng["e_success"], "Настройки успешно сохранены", 3, '?mode=admin&do=settings&op=mail');
		}

		$data = array(
			"SMTP"			=> ($cfg['smtp']) ? 'selected' : '',
			"CFG"			=> $cfg,
		);

		ob_start();

		echo $this->core->sp(MCR_THEME_MOD."admin/settings/mail.html", $data);

		return ob_get_clean();
	}

	private function search_items($cfg){

		ob_start();

		foreach($cfg as $key => $value){

			$data = array(
				"KEY" => $this->db->HSC($key),
				"TITLE" => $this->db->HSC($value['title']),
				"PERMISSIONS" => $this->core->perm_list($value['permissions']),
			);

			echo $this->core->sp(MCR_THEME_MOD."admin/settings/search-id.html", $data);
		}

		return ob_get_clean();
	}

	private function search(){

		$cfg = $this->config->search;

		if($_SERVER['REQUEST_METHOD']=='POST'){

			if(!isset($_POST['key']) || !isset($cfg[$_POST['key']])){ $this->core->notify($this->lng["e_msg"], $this->lng['e_hack'], 2, '?mode=admin&do=settings&op=search'); }

			if(!$this->core->validate_perm(@$_POST['permissions'])){ $this->core->notify($this->lng["e_msg"], $this->lng['e_hack'], 2, '?mode=admin&do=settings&op=search'); }

			$key = $_POST['key'];

			$cfg[$key] = array(
				"title" => $this->core->safestr(@$_POST['title']),
				"permissions" => $this->core->safestr(@$_POST['permissions']),
			);

			if(!$this->core->savecfg($cfg, 'search.php', 'search')){ $this->core->notify($this->lng["e_msg"], "Не удалось сохранить файл конфигурации", 2, '?mode=admin&do=settings&op=search'); }
			
			$this->core->notify($this->lng["e_success"], "Настройки успешно сохранены", 3, '?mode=admin&do=settings&op=search');
		}

		$data = array(
			"ITEMS"			=> $this->search_items($cfg),
		);

		ob_start();

		echo $this->core->sp(MCR_THEME_MOD."admin/settings/search.html", $data);

		return ob_get_clean();
	}

	private function functions(){

		$cfg = $this->config->func;

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$cfg['advice'] = (intval(@$_POST['advice'])===1) ? true : false;

			$cfg['breadcrumbs'] = (intval(@$_POST['breadcrumbs'])===1) ? true : false;

			if(!$this->core->savecfg($cfg, 'functions.php', 'func')){ $this->core->notify($this->lng["e_msg"], "Не удалось сохранить файл конфигурации", 2, '?mode=admin&do=settings&op=functions'); }
			
			$this->core->notify($this->lng["e_success"], "Настройки успешно сохранены", 3, '?mode=admin&do=settings&op=functions');
		}

		$data = array(
			"ADVICE" => ($cfg['advice']) ? 'selected' : '',
			"BREADCRUMBS" => ($cfg['breadcrumbs']) ? 'selected' : '',
		);

		ob_start();

		echo $this->core->sp(MCR_THEME_MOD."admin/settings/functions.html", $data);

		return ob_get_clean();
	}

	private function base(){

		include(MCR_ROOT.'configs/db.php');

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$db['backend'] = $this->core->safestr(@$_POST['backend']);
			
			$db['host'] = $this->core->safestr(@$_POST['host']);
			
			$db['base'] = $this->core->safestr(@$_POST['base']);
			
			$db['user'] = $this->core->safestr(@$_POST['user']);
			
			$db['pass'] = $this->core->safestr(@$_POST['pass']);
			
			$db['port'] = intval(@$_POST['port']);

			if(!$this->core->savecfg($db, 'db.php', 'db')){ $this->core->notify($this->lng["e_msg"], "Не удалось сохранить файл конфигурации", 2, '?mode=admin&do=settings&op=base'); }
			
			$this->core->notify($this->lng["e_success"], "Настройки успешно сохранены", 3, '?mode=admin&do=settings&op=base');
		}

		$data = array(
			"MYSQL" => '',
			"PDO" => '',
			"MYSQLI" => ($db['backend']=='mysqli') ? 'selected' : '',
			"HOST" => $this->db->HSC($db['host']),
			"USER" => $this->db->HSC($db['user']),
			"PASS" => $this->core->safestr($db['pass']),
			"BASE" => $this->db->HSC($db['base']),
			"PORT" => intval($db['port']),
		);

		ob_start();

		echo $this->core->sp(MCR_THEME_MOD."admin/settings/base.html", $data);

		return ob_get_clean();
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'pagin':		$content = $this->pagin(); break;
			case 'mail':		$content = $this->_mail(); break;
			case 'search':		$content = $this->search(); break;
			case 'base':		$content = $this->base(); break;
			case 'functions':	$content = $this->functions(); break;

			default:		$content = $this->main(); break;
		}

		ob_start();

		echo $content;

		return ob_get_clean();
	}
}

?>