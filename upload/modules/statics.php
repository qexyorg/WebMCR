<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $cfg, $user, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;
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

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$query = $this->db->query("SELECT `s`.title, `s`.text_html, `s`.uid, `s`.`permissions`, `s`.`data`,
											`u`.`{$us_f['login']}`
									FROM `mcr_statics` AS `s`
									LEFT JOIN `{$this->cfg->tabname('users')}` AS `u`
										ON `u`.`{$us_f['id']}`=`s`.uid
									WHERE `s`.`uniq`='$uniq'");
		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$ar = $this->db->fetch_assoc($query);

		if(!$this->core->is_access($ar['permissions'])){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

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
			"LOGIN" => $this->db->HSC($ar[$us_f['login']]),
			"DATA" => json_decode($ar['data'], true),

		);

		return $this->core->sp(MCR_THEME_MOD."statics/static-id.html", $page_data);
	}
}

?>