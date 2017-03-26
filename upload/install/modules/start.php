<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $install, $cfg, $lng;

	public function __construct($install){
		$this->install		= $install;
		$this->cfg			= $install->cfg;
		$this->lng			= $install->lng;

		$this->install->title = $this->lng['mod_name'].' — '.$this->lng['step_1'];
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
		if(isset($_SESSION['start'])){ $this->install->notify('', '', 'install/?do=step_1'); }

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(phpversion()<5.1){ $this->install->notify($this->lng['e_msg'], $this->lng['e_php_version'], 'install/'); }

			if(@ini_get('register_globals')=='off'){ $this->install->notify($this->lng['e_msg'], $this->lng['e_register_globals'], 'install/'); }

			if(@ini_get('allow_url_fopen')=='0' || @ini_get('allow_url_fopen')=='false'){ $this->install->notify($this->lng['e_msg'], $this->lng['e_fopen'], 'install/'); }

			if(!function_exists('ImageCreateFromJpeg')){ $this->install->notify($this->lng['e_msg'], $this->lng['e_gd'], 'install/'); }

			if(!function_exists('mysql_query') && !function_exists('mysqli_query')){ $this->install->notify($this->lng['e_msg'], $this->lng['e_mysql_not_found'], 'install/'); }

			if(!function_exists('ob_start')){ $this->install->notify($this->lng['e_msg'], $this->lng['e_buffer'], 'install/'); }

			if(!$this->check_write_all(DIR_ROOT.'configs')){ $this->install->notify($this->lng['e_msg'], $this->lng['e_perm_configs'], 'install/'); }

			if(!$this->check_write_all(DIR_ROOT.'configs/modules')){ $this->install->notify($this->lng['e_msg'], $this->lng['e_perm_modules'], 'install/'); }

			if(!$this->check_write_all(DIR_ROOT.'cache')){ $this->install->notify($this->lng['e_msg'], $this->lng['e_perm_cache'], 'install/'); }

			if(!is_writable(DIR_ROOT.'uploads') || !is_readable(DIR_ROOT.'uploads')){ $this->install->notify($this->lng['e_msg'], $this->lng['e_perm_uploads'], 'install/'); }

			if(!is_writable(DIR_ROOT.$this->cfg['main']['cloak_path']) || !is_readable(DIR_ROOT.$this->cfg['main']['cloak_path'])){ $this->install->notify($this->lng['e_msg'], $this->lng['e_perm_cloaks'], 'install/'); }

			if(!is_writable(DIR_ROOT.'uploads/panel-icons') || !is_readable(DIR_ROOT.'uploads/panel-icons')){ $this->install->notify($this->lng['e_msg'], $this->lng['e_perm_icons'], 'install/'); }

			if(!is_writable(DIR_ROOT.$this->cfg['main']['skin_path']) || !is_readable(DIR_ROOT.$this->cfg['main']['skin_path'])){ $this->install->notify($this->lng['e_msg'], $this->lng['e_perm_skins'], 'install/'); }

			if(!is_writable(DIR_ROOT.$this->cfg['main']['skin_path'].'interface') || !is_readable(DIR_ROOT.$this->cfg['main']['skin_path'].'interface')){ $this->install->notify($this->lng['e_msg'], $this->lng['e_perm_intf'], 'install/'); }

			if(!is_writable(DIR_ROOT.'uploads/smiles') || !is_readable(DIR_ROOT.'uploads/smiles')){ $this->install->notify($this->lng['e_msg'], $this->lng['e_perm_smiles'], 'install/'); }

			$_SESSION['start'] = true;

			$this->install->notify('', '', 'install/?do=step_1');

		}

		$data = array(
			"PHP" => (phpversion()<5.1) ? '<b class="red">'.phpversion().'</b>' : '<b class="green">'.phpversion().'</b>',

			"REG_GLOB" => (@ini_get('register_globals')=='on') ? '<b class="red">'.$this->lng['on'].'</b>' : '<b class="green">Выкл.</b>',

			"URL_FOPEN" => (@ini_get('allow_url_fopen')=='1' || @ini_get('allow_url_fopen')=='true') ? '<b class="green">'.$this->lng['on'].'</b>' : '<b class="red">'.$this->lng['off'].'</b>',

			"GD" => (function_exists('ImageCreateFromJpeg')) ? '<b class="green">'.$this->lng['yes'].'</b>' : '<b class="red">'.$this->lng['no'].'</b>',

			"MYSQL" => (function_exists("mysql_query") || function_exists("mysqli_query")) ? '<b class="green">'.$this->lng['yes'].'</b>' : '<b class="red">'.$this->lng['no'].'</b>',

			"BUFER" => (function_exists("ob_start")) ? '<b class="green">'.$this->lng['yes'].'</b>' : '<b class="red">'.$this->lng['no'].'</b>',

			"FOLDER_CONFIGS" => ($this->check_write_all(DIR_ROOT.'configs')) ? '<b class="green">'.$this->lng['yes'].'</b>' : '<b class="red">'.$this->lng['no'].'</b>',

			"FOLDER_MODULES" => ($this->check_write_all(DIR_ROOT.'configs/modules')) ? '<b class="green">'.$this->lng['yes'].'</b>' : '<b class="red">'.$this->lng['no'].'</b>',

			"FOLDER_CACHE" => ($this->check_write_all(DIR_ROOT.'cache')) ? '<b class="green">'.$this->lng['yes'].'</b>' : '<b class="red">'.$this->lng['no'].'</b>',

			"FOLDER_UPLOADS" => (is_writable(DIR_ROOT.'uploads') && is_readable(DIR_ROOT.'uploads')) ? '<b class="green">'.$this->lng['yes'].'</b>' : '<b class="red">'.$this->lng['no'].'</b>',

			"FOLDER_CLOAKS" => (is_writable(DIR_ROOT.$this->cfg['main']['cloak_path']) && is_readable(DIR_ROOT.$this->cfg['main']['cloak_path'])) ? '<b class="green">'.$this->lng['yes'].'</b>' : '<b class="red">'.$this->lng['no'].'</b>',

			"FOLDER_SKINS" => (is_writable(DIR_ROOT.$this->cfg['main']['skin_path']) && is_readable(DIR_ROOT.$this->cfg['main']['skin_path'])) ? '<b class="green">'.$this->lng['yes'].'</b>' : '<b class="red">'.$this->lng['no'].'</b>',

			"FOLDER_ICONS" => (is_writable(DIR_ROOT.'uploads/panel-icons') && is_readable(DIR_ROOT.'uploads/panel-icons')) ? '<b class="green">'.$this->lng['yes'].'</b>' : '<b class="red">'.$this->lng['no'].'</b>',

			"FOLDER_INTERF" => (is_writable(DIR_ROOT.$this->cfg['main']['skin_path'].'interface') && is_readable(DIR_ROOT.$this->cfg['main']['skin_path'])) ? '<b class="green">'.$this->lng['yes'].'</b>' : '<b class="red">'.$this->lng['no'].'</b>',

			"FOLDER_SMILES" => (is_writable(DIR_ROOT.'uploads/smiles') && is_readable(DIR_ROOT.'uploads/smiles')) ? '<b class="green">'.$this->lng['yes'].'</b>' : '<b class="red">'.$this->lng['no'].'</b>',
		);

		return $this->install->sp('start.html', $data);
	}

}

?>