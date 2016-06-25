<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class config{
	public $main	= array();
	public $db		= array();
	public $func	= array();
	public $pagin	= array();
	public $mail	= array();
	public $search	= array();

	public function __construct(){
		// Load main config
		require_once(MCR_CONF_PATH.'main.php');
		require_once(MCR_CONF_PATH.'mail.php');
		require_once(MCR_CONF_PATH.'db.php');
		require_once(MCR_CONF_PATH.'functions.php');
		require_once(MCR_CONF_PATH.'pagin.php');
		require_once(MCR_CONF_PATH.'search.php');

		$this->main		= $main;
		$this->mail		= $mail;
		$this->db		= $db;
		$this->func		= $func;
		$this->pagin	= $pagin;
		$this->search	= $search;
	}

	public function tabname($name){
		return $this->db['tables'][$name]['name'];
	}
	
	public function savecfg($cfg=array(), $file='main.php', $var='main'){

		if(!is_array($cfg) || empty($cfg)){ return false; }

		$filename = MCR_CONF_PATH.$file;

		$txt  = '<?php'.PHP_EOL;
		$txt .= '$'.$var.' = '.var_export($cfg, true).';'.PHP_EOL;
		$txt .= '?>';

		$result = file_put_contents($filename, $txt);

		if($result === false){ return false; }

		return true;
	}
}


?>