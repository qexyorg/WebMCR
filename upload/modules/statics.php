<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $config, $user, $lng;
	public $cfg = array();

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->config	= $core->config;
		$this->user		= $core->user;
		$this->lng		= $core->lng_m;

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=statics"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	public function content(){

		if(!isset($_GET['id']) || empty($_GET['id'])){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$uniq = $this->db->safesql(@$_GET['id']);

		$query = $this->db->query("SELECT `s`.title, `s`.text_html, `s`.uid, `s`.`permissions`, `s`.`data`,
											`u`.login
									FROM `mcr_statics` AS `s`
									LEFT JOIN `mcr_users` AS `u`
										ON `u`.id=`s`.uid
									WHERE `s`.`uniq`='$uniq'");
		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng['403'], $this->lng['e_403']); }

		$ar = $this->db->fetch_assoc($query);

		if(!$this->core->is_access($ar['permissions'])){ $this->core->notify($this->core->lng['403'], $this->lng['e_403']); }

		$uniq = $this->db->HSC($uniq);
		$title = $this->db->HSC($ar['title']);

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=statics&id=$uniq",
			$title => BASE_URL."?mode=statics&id=$uniq"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$page_data = array(
			"TITLE" => $this->db->HSC($ar['title']),
			"TEXT" => $ar['text_html'],
			"UID" => intval($ar['uid']),
			"LOGIN" => $this->db->HSC($ar['login']),
			"DATA" => json_decode($ar['data'], true),

		);

		return $this->core->sp(MCR_THEME_MOD."statics/static-id.html", $page_data);
	}
}

?>
