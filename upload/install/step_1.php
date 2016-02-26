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

		$this->core->title = $this->lng_m['mod_name'].' — '.$this->lng_m['step_1'];

		$bc = array(
			$this->lng_m['mod_name'] => BASE_URL."install/",
			$this->lng_m['step_1'] => BASE_URL."install/?mode=step_1"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function check_write_all($folder){
		if(!is_writable($folder) || !is_readable($folder)){ return false; }

		$scan = scandir($folder);

		$result = true;

		foreach($scan as $key => $value) {
			if($value=='.' || $value=='..'){ continue; }

			$path = $folder.'/'.$value;

			if(!is_writable($path) || !is_readable($path)){ $result = false; }
		}

		return $result;
	}

	public function content(){
		if(isset($_SESSION['step_1'])){ $this->core->notify('', '', 4, 'install/?mode=step_2'); }

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(phpversion()<5.1){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_php_version'], 2, 'install/?mode=step_1'); }

			if(@ini_get('register_globals')=='off'){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_register_globals'], 2, 'install/?mode=step_1'); }

			if(@ini_get('allow_url_fopen')=='0' || @ini_get('allow_url_fopen')=='false'){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_fopen'], 2, 'install/?mode=step_1'); }

			if(!function_exists('ImageCreateFromJpeg')){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_gd'], 2, 'install/?mode=step_1'); }

			if(!function_exists('mysql_query') && !function_exists('mysqli_query')){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_mysql_not_found'], 2, 'install/?mode=step_1'); }

			if(!function_exists('ob_start')){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_buffer'], 2, 'install/?mode=step_1'); }

			if(!$this->check_write_all(MCR_ROOT.'configs')){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_perm_configs'], 2, 'install/?mode=step_1'); }

			if(!$this->check_write_all(MCR_ROOT.'configs/modules')){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_perm_modules'], 2, 'install/?mode=step_1'); }

			if(!$this->check_write_all(MCR_ROOT.'cache')){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_perm_cache'], 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'uploads') || !is_readable(MCR_ROOT.'uploads')){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_perm_uploads'], 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'uploads/cloaks') || !is_readable(MCR_ROOT.'uploads/cloaks')){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_perm_cloaks'], 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'uploads/panel-icons') || !is_readable(MCR_ROOT.'uploads/panel-icons')){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_perm_icons'], 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'uploads/skins') || !is_readable(MCR_ROOT.'uploads/skins')){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_perm_skins'], 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'uploads/skins/interface') || !is_readable(MCR_ROOT.'uploads/skins/interface')){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_perm_intf'], 2, 'install/?mode=step_1'); }

			if(!is_writable(MCR_ROOT.'uploads/smiles') || !is_readable(MCR_ROOT.'uploads/smiles')){ $this->core->notify($this->lng['e_msg'], $this->lng_m['e_perm_smiles'], 2, 'install/?mode=step_1'); }

			$_SESSION['step_1'] = true;

			$this->core->notify($this->lng_m['step_2'], $this->lng_m['db_settings'], 4, 'install/?mode=step_2');

		}

		$data = array(
			"PHP" => (phpversion()<5.1) ? '<b class="text-error">'.phpversion().'</b>' : '<b class="text-success">'.phpversion().'</b>',
			"REG_GLOB" => (@ini_get('register_globals')=='on') ? '<b class="text-error">'.$this->lng_m['on'].'</b>' : '<b class="text-success">Выкл.</b>',
			"URL_FOPEN" => (@ini_get('allow_url_fopen')=='1' || @ini_get('allow_url_fopen')=='true') ? '<b class="text-success">'.$this->lng_m['on'].'</b>' : '<b class="text-error">'.$this->lng_m['off'].'</b>',
			"GD" => (function_exists('ImageCreateFromJpeg')) ? '<b class="text-success">'.$this->lng['yes'].'</b>' : '<b class="text-error">'.$this->lng['no'].'</b>',
			"MYSQL" => (function_exists("mysql_query") || function_exists("mysqli_query")) ? '<b class="text-success">'.$this->lng['yes'].'</b>' : '<b class="text-error">'.$this->lng['no'].'</b>',
			"BUFER" => (function_exists("ob_start")) ? '<b class="text-success">'.$this->lng['yes'].'</b>' : '<b class="text-error">'.$this->lng['no'].'</b>',

			"FOLDER_CONFIGS" => ($this->check_write_all(MCR_ROOT.'configs')) ? '<b class="text-success">'.$this->lng['yes'].'</b>' : '<b class="text-error">'.$this->lng['no'].'</b>',

			"FOLDER_MODULES" => ($this->check_write_all(MCR_ROOT.'configs/modules')) ? '<b class="text-success">'.$this->lng['yes'].'</b>' : '<b class="text-error">'.$this->lng['no'].'</b>',

			"FOLDER_CACHE" => ($this->check_write_all(MCR_ROOT.'cache')) ? '<b class="text-success">'.$this->lng['yes'].'</b>' : '<b class="text-error">'.$this->lng['no'].'</b>',

			"FOLDER_UPLOADS" => (is_writable(MCR_ROOT.'uploads') && is_readable(MCR_ROOT.'uploads')) ? '<b class="text-success">'.$this->lng['yes'].'</b>' : '<b class="text-error">'.$this->lng['no'].'</b>',

			"FOLDER_CLOAKS" => (is_writable(MCR_ROOT.'uploads/cloaks') && is_readable(MCR_ROOT.'uploads/cloaks')) ? '<b class="text-success">'.$this->lng['yes'].'</b>' : '<b class="text-error">'.$this->lng['no'].'</b>',

			"FOLDER_ICONS" => (is_writable(MCR_ROOT.'uploads/panel-icons') && is_readable(MCR_ROOT.'uploads/panel-icons')) ? '<b class="text-success">'.$this->lng['yes'].'</b>' : '<b class="text-error">'.$this->lng['no'].'</b>',

			"FOLDER_SKINS" => (is_writable(MCR_ROOT.'uploads/skins') && is_readable(MCR_ROOT.'uploads/skins')) ? '<b class="text-success">'.$this->lng['yes'].'</b>' : '<b class="text-error">'.$this->lng['no'].'</b>',

			"FOLDER_INTERF" => (is_writable(MCR_ROOT.'uploads/skins/interface') && is_readable(MCR_ROOT.'uploads/skins')) ? '<b class="text-success">'.$this->lng['yes'].'</b>' : '<b class="text-error">'.$this->lng['no'].'</b>',

			"FOLDER_SMILES" => (is_writable(MCR_ROOT.'uploads/smiles') && is_readable(MCR_ROOT.'uploads/smiles')) ? '<b class="text-success">'.$this->lng['yes'].'</b>' : '<b class="text-error">'.$this->lng['no'].'</b>',
		);

		return $this->core->sp(MCR_ROOT."install/theme/step_1.html", $data);
	}

}

?>