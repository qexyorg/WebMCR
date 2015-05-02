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
}


?>