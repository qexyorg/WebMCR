<?php

class config{
	public $main	= array();
	public $db		= array();
	public $func	= array();
	public $pagin	= array();
	public $mail	= array();
	public $search	= array();

	public function __construct(){
		// Load main config
		require_once(MCR_ROOT.'configs/main.php');
		require_once(MCR_ROOT.'configs/mail.php');
		require_once(MCR_ROOT.'configs/db.php');
		require_once(MCR_ROOT.'configs/functions.php');
		require_once(MCR_ROOT.'configs/pagin.php');
		require_once(MCR_ROOT.'configs/search.php');

		$this->main		= $main;
		$this->mail		= $mail;
		$this->db		= $db;
		$this->func		= $func;
		$this->pagin	= $pagin;
		$this->search	= $search;
	}
	
	public function savecfg($cfg=array(), $file='main.php', $var='main'){

		if(!is_array($cfg) || empty($cfg)){ return false; }

		$filename = MCR_ROOT."configs/".$file;

		$txt  = '<?php'.PHP_EOL;
		$txt .= '$'.$var.' = '.var_export($cfg, true).';'.PHP_EOL;
		$txt .= '?>';

		$result = file_put_contents($filename, $txt);

		if($result === false){ return false; }

		return true;
	}
}


?>