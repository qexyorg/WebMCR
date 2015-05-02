<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

if(isset($_SESSION['mcr_notify'])){

	$new_data = array(
		"TYPE" => $this->db->HSC($_SESSION['notify_type']),
		"TITLE" => $this->db->HSC($_SESSION['notify_title']),
		"MESSAGE" => $this->db->HSC($_SESSION['notify_msg'])
	);

	echo $this->sp(MCR_THEME_PATH."blocks/notify/alert.html", $new_data);
	
	unset($_SESSION['mcr_notify']);
	unset($_SESSION['notify_type']);
	unset($_SESSION['notify_title']);
	unset($_SESSION['notify_msg']);
}



?>