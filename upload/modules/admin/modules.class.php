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

		if(!$this->core->is_access('sys_adm_modules')){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['modules'] => ADMIN_URL."&do=modules"
		);

		$this->core->bc = $this->core->gen_bc($bc);
		
		$this->core->header .= $this->core->sp(MCR_THEME_MOD."admin/modules/header.html");
	}

	private function module_array(){

		$list = $this->filter_folder(scandir(MCR_MODE_PATH));

		if(!is_array($list) || count($list)<=0){ return $this->core->sp(MCR_THEME_MOD."admin/modules/module-none.html"); }

		ob_start();

		foreach($list as $id => $name){

			include(MCR_CONF_PATH.'modules/'.$name.'.php');

			$page_data = array(
				"STATUS"	=> (@$cfg['MOD_ENABLE']) ? 'icon_status_on' : 'icon_status_off',
				"NAME"		=> $this->db->HSC($name),
				"TITLE"		=> $this->db->HSC(@$cfg['MOD_TITLE']),
				"AUTHOR"	=> $this->db->HSC(@$cfg['MOD_AUTHOR']),
				"VERSION"	=> $this->db->HSC(@$cfg['MOD_VERSION']),
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/modules/module-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function filter_folder($array){

		$filtered = array();

		foreach($array as $key => $value){
			if($value=='..' || $value=='.'){ continue; }
			if(is_dir(MCR_MODE_PATH.$value)){ continue; }
			if(!file_exists(MCR_CONF_PATH.'modules/'.$value)){ continue; }

			$expl = explode('.', $value);

			if(count($expl)!=2 || !isset($expl[1]) || $expl[1]!='php'){ continue; }

			$filtered[] = $expl[0];
		}

		return $filtered;
	}

	private function module_list(){

		$data = array(
			"MODULES" => $this->module_array()
		);

		return $this->core->sp(MCR_THEME_MOD."admin/modules/module-list.html", $data);
	}

	private function check_update($cfg){
		if(empty($cfg['MOD_URL_UPDATE'])){ return $this->lng['mod_not_check']; }

		$json = file_get_contents($cfg['MOD_URL_UPDATE']);

		if(!$json){ return $this->lng['mod_not_check']; }

		if(!isset($json['version'])){ return $this->lng['mod_not_check']; }
		if(!isset($json['url'])){ return $this->lng['mod_not_check']; }

		if($cfg['MOD_VERSION']==$json['version']){ return $this->lng['mod_last_version']; }

		return '(<a href="'.$this->db->HSC($json['url']).'" target="_blank" title="'.$this->lng['mod_download'].'">'.$this->lng['mod_new_version'].' '.$this->db->HSC($json['version']).'</a>)';
	}

	private function edit(){
		if(!$this->core->is_access('sys_adm_modules_edit')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=modules'); }

		$name = @$_GET['id'];

		if(!file_exists(MCR_CONF_PATH.'modules/'.$name.'.php')){ $this->core->notify($this->core->lng["e_msg"], $this->lng['mod_cfg_not_found'], 2, '?mode=admin&do=modules'); }

		require(MCR_CONF_PATH.'modules/'.$name.'.php');

		if(!$this->core->check_cfg($cfg)){
			$this->core->notify($this->core->lng["e_msg"], $this->lng['mod_cfg_incorrect'], 2, '?mode=admin&do=modules');
		}

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL."",
			$this->lng['modules'] => ADMIN_URL."&do=modules",
			$this->lng['mod_edit'] => ADMIN_URL."&do=modules&op=edit&id=$name"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$cfg['MOD_ENABLE'] = (intval(@$_POST['status'])==1) ? true : false;
			$cfg['MOD_CHECK_UPDATE'] = (intval(@$_POST['updates'])==1) ? true : false;
			$cfg['MOD_URL_UPDATE'] = $this->core->safestr(@$_POST['update_url']);

			if(!$this->cfg->savecfg($cfg, 'modules/'.$name.'.php', 'cfg')){
				$this->core->notify($this->core->lng["e_msg"], $this->lng['mod_cfg_unsave'], 3, '?mode=admin&do=modules');
			}

			// Лог действия
			$this->db->actlog($this->lng['log_edit_mod']." #$name ".$this->lng['log_mod'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['mod_edit_success'], 3, '?mode=admin&do=modules');
		}

		$update_result = $this->check_update($cfg);

		$data = array(
			"PAGE"			=> $this->lng['mod_edit_page_name'],
			"STATUS"		=> ($cfg['MOD_ENABLE']) ? 'selected' : '',
			"TITLE"			=> $this->db->HSC($cfg['MOD_TITLE']),
			"DESC"			=> $this->db->HSC($cfg['MOD_DESC']),
			"AUTHOR"		=> $this->db->HSC($cfg['MOD_AUTHOR']),
			"SITE"			=> $this->db->HSC($cfg['MOD_SITE']),
			"EMAIL"			=> $this->db->HSC($cfg['MOD_EMAIL']),
			"VERSION"		=> $this->db->HSC($cfg['MOD_VERSION']),
			"UPDATE_URL"	=> $this->db->HSC($cfg['MOD_URL_UPDATE']),
			"UPDATE_CHECK"	=> ($cfg['MOD_CHECK_UPDATE']) ? 'selected' : '',
			"UPDATE_RESULT"	=> $update_result,
			"BUTTON"		=> $this->lng['mod_edit_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/modules/module-add.html", $data);
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'edit':	$content = $this->edit(); break;

			default:		$content = $this->module_list(); break;
		}

		return $content;
	}
}

?>