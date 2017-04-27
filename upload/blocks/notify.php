<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class block_notify{
	private $core, $db;

	public function __construct($core){
		$this->core = $core;
		$this->db = $core->db;
	}

	public function content(){
		$this->core->header .= $this->core->sp(MCR_THEME_PATH."blocks/notify/header.html");

		if(!isset($_SESSION['mcr_notify'])){ return ''; }

		$new_data = array(
			"TYPE" => $this->db->HSC(@$_SESSION['notify_type']),
			"TITLE" => $this->db->HSC(@$_SESSION['notify_title']),
			"MESSAGE" => $this->db->HSC(@$_SESSION['notify_msg'])
		);

		$result = $this->core->sp(MCR_THEME_PATH."blocks/notify/alert.html", $new_data);
	
		unset($_SESSION['mcr_notify']);
		unset($_SESSION['notify_type']);
		unset($_SESSION['notify_title']);
		unset($_SESSION['notify_msg']);

		return $result;
	}
}

?>