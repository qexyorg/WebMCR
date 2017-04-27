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

		if(!$this->core->is_access('sys_adm_settings')){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['settings'] => ADMIN_URL."&do=settings"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$this->core->header .= $this->core->sp(MCR_THEME_MOD."admin/settings/header.html");
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

		$cfg = $this->cfg->main;

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$cfg['s_name']		= $this->core->safestr(@$_POST['s_name']);

			$cfg['s_about']		= $this->core->safestr(@$_POST['s_about']);

			$cfg['s_keywords']	= $this->core->safestr(@$_POST['s_keywords']);

			$cfg['s_dpage']		= $this->core->safestr(@$_POST['s_dpage']);

			$cfg['s_client']		= $this->core->safestr(@$_POST['s_client']);

			$s_theme = $this->core->safestr(@$_POST['s_theme']);
			if(!$this->is_theme_exist($s_theme)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['set_theme_incorrect'], 2, '?mode=admin&do=settings'); }
			$cfg['s_theme'] = $s_theme;

			$this->cfg->db['log']			= (intval(@$_POST['log']) === 1) ? true : false;

			$cfg['debug']		= (intval(@$_POST['debug']) === 1) ? true : false;

			$cfg['reg_accept']		= (intval(@$_POST['reg_accept']) === 1) ? true : false;

			$captcha = intval(@$_POST['captcha']);

			if(!$this->is_captcha_exist($captcha)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['set_captcha_incorrect'], 2, '?mode=admin&do=settings'); }
			$cfg['captcha']		= $captcha;

			$cfg['rc_public']	= $this->core->safestr(@$_POST['rc_public']);

			$cfg['rc_private']	= $this->core->safestr(@$_POST['rc_private']);

			$cfg['kc_public']	= $this->core->safestr(@$_POST['kc_public']);

			$cfg['kc_private']	= $this->core->safestr(@$_POST['kc_private']);

			if(!$this->cfg->savecfg($cfg)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['set_e_cfg_save'], 2, '?mode=admin&do=settings'); }
			
			if(!$this->cfg->savecfg($this->cfg->db, 'db.php', 'db')){ $this->core->notify($this->core->lng["e_msg"], $this->lng['set_e_cfg_save'], 2, '?mode=admin&do=settings'); }

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_set_main_save'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['set_save_success'], 3, '?mode=admin&do=settings');
		}

		$data = array(
			"THEMES"		=> $this->themes($cfg['s_theme']),
			"CFG"			=> $cfg,
			"LOG"			=> ($this->cfg->db['log']) ? 'selected' : '',
			"DEBUG"			=> ($cfg['debug']) ? 'selected' : '',
			"REG_ACCEPT"	=> ($cfg['reg_accept']) ? 'selected' : '',
			"CAPTHA"		=> $this->captcha($cfg['captcha']),
		);

		return $this->core->sp(MCR_THEME_MOD."admin/settings/main.html", $data);
	}

	private function to_int_keys($array=array()){
		if(empty($array)){ return false; }

		$cfg = $this->cfg->pagin;

		foreach($array as $key => $value){
			$cfg[$key] = (intval($value)<=0) ? 1 : intval($value);
		}

		return $cfg;
	}

	private function pagin(){

		$cfg = $this->cfg->pagin;

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$post = $_POST;

			unset($post['mcr_secure']);
			unset($post['submit']);

			$cfg_keys = array_keys($cfg);
			rsort($cfg_keys);

			$post_keys = array_keys($post);
			rsort($post_keys);

			if($cfg_keys!==$post_keys){ $this->core->notify($this->core->lng["e_msg"], $this->lng['set_e_hash'], 2, '?mode=admin&do=settings&op=pagin'); }

			$cfg = $this->to_int_keys($post);

			if(!$this->cfg->savecfg($cfg, 'pagin.php', 'pagin')){ $this->core->notify($this->core->lng["e_msg"], $this->lng['set_e_cfg_save'], 2, '?mode=admin&do=settings&op=pagin'); }

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_set_pagin_save'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['set_save_success'], 3, '?mode=admin&do=settings&op=pagin');
		}

		$data = array(
			"CFG" => $cfg
		);

		return $this->core->sp(MCR_THEME_MOD."admin/settings/pagin.html", $data);
	}

	private function _mail(){

		$cfg = $this->cfg->mail;

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$cfg['smtp']			= (intval(@$_POST['smtp']) === 1) ? true : false;

			$cfg['from']			= $this->core->safestr(@$_POST['from']);

			$cfg['from_name']		= $this->core->safestr(@$_POST['from_name']);

			$cfg['reply']			= $this->core->safestr(@$_POST['reply']);

			$cfg['reply_name']		= $this->core->safestr(@$_POST['reply_name']);

			$cfg['smtp_host']		= $this->core->safestr(@$_POST['smtp_host']);

			$cfg['smtp_user']		= $this->core->safestr(@$_POST['smtp_user']);

			$cfg['smtp_pass']		= $this->core->safestr(@$_POST['smtp_pass']);

			$cfg['smtp_tls']		= (intval(@$_POST['smtp_tls']) === 1) ? true : false;

			if(!$this->cfg->savecfg($cfg, 'mail.php', 'mail')){ $this->core->notify($this->core->lng["e_msg"], $this->lng['set_e_cfg_save'], 2, '?mode=admin&do=settings&op=mail'); }

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_set_mail_save'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['set_save_success'], 3, '?mode=admin&do=settings&op=mail');
		}

		$data = array(
			"SMTP"			=> ($cfg['smtp']) ? 'selected' : '',
			"SMTP_TLS"		=> ($cfg['smtp_tls']) ? 'selected' : '',
			"CFG"			=> $cfg,
		);

		return $this->core->sp(MCR_THEME_MOD."admin/settings/mail.html", $data);
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

		$cfg = $this->cfg->search;

		if($_SERVER['REQUEST_METHOD']=='POST'){

			if(!isset($_POST['key']) || !isset($cfg[$_POST['key']])){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=settings&op=search'); }

			if(!$this->core->validate_perm(@$_POST['permissions'])){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=settings&op=search'); }

			$key = $_POST['key'];

			$cfg[$key] = array(
				"title" => $this->core->safestr(@$_POST['title']),
				"permissions" => $this->core->safestr(@$_POST['permissions']),
			);

			if(!$this->cfg->savecfg($cfg, 'search.php', 'search')){ $this->core->notify($this->core->lng["e_msg"], $this->lng['set_e_cfg_save'], 2, '?mode=admin&do=settings&op=search'); }

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_set_search_save'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['set_save_success'], 3, '?mode=admin&do=settings&op=search');
		}

		$data = array(
			"ITEMS"			=> $this->search_items($cfg),
		);

		return $this->core->sp(MCR_THEME_MOD."admin/settings/search.html", $data);
	}

	private function functions(){

		$cfg = $this->cfg->func;

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$cfg['advice'] = (intval(@$_POST['advice'])===1) ? true : false;

			$cfg['breadcrumbs'] = (intval(@$_POST['breadcrumbs'])===1) ? true : false;

			$cfg['close'] = (intval(@$_POST['close'])===1) ? true : false;

			$cfg['close_time'] = (@$_POST['close_time']=='') ? 0 : intval(strtotime(@$_POST['close_time']));

			$cfg['ipreglimit'] = (intval(@$_POST['input_reglimit'])<=0) ? 0 : intval(@$_POST['input_reglimit']);

			if(!$this->cfg->savecfg($cfg, 'functions.php', 'func')){ $this->core->notify($this->core->lng["e_msg"], $this->lng['set_e_cfg_save'], 2, '?mode=admin&do=settings&op=functions'); }

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_set_func_save'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['set_save_success'], 3, '?mode=admin&do=settings&op=functions');
		}

		$data = array(
			"ADVICE" => ($cfg['advice']) ? 'selected' : '',
			"BREADCRUMBS" => ($cfg['breadcrumbs']) ? 'selected' : '',
			"CLOSE" => ($cfg['close']) ? 'selected' : '',
			"REGLIMIT" => intval(@$cfg['ipreglimit']),
			'CLOSE_TIME' => (intval($cfg['close_time'])<=0) ? '' : date("d.m.Y H:i:s", $cfg['close_time']),
		);

		return $this->core->sp(MCR_THEME_MOD."admin/settings/functions.html", $data);
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

			if(!$this->cfg->savecfg($db, 'db.php', 'db')){ $this->core->notify($this->core->lng["e_msg"], $this->lng['set_e_cfg_save'], 2, '?mode=admin&do=settings&op=base'); }

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_set_base_save'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['set_save_success'], 3, '?mode=admin&do=settings&op=base');
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

		return $this->core->sp(MCR_THEME_MOD."admin/settings/base.html", $data);
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

		return $content;
	}
}

?>