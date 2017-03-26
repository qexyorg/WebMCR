<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $install, $cfg, $lng;

	public function __construct($install){
		$this->install		= $install;
		$this->cfg			= $install->cfg;
		$this->lng			= $install->lng;

		$this->install->title = $this->lng['mod_name'].' — '.$this->lng['finish'];
	}

	public function content(){
		if(!isset($_SESSION['step_3'])){ $this->install->notify('', '', 'install/?do=step_3'); }

		$data = array();

		return $this->install->sp('finish.html', $data);
	}

}

?>