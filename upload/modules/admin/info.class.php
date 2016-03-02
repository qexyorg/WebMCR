<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $config, $user, $lng;

	public function __construct($core){
		$this->core = $core;
		$this->db	= $core->db;
		$this->config = $core->config;
		$this->user	= $core->user;
		$this->lng	= $core->lng_m;

		if(!$this->core->is_access('sys_adm_info')){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=admin",
			$this->lng['info'] => BASE_URL."?mode=admin&do=statics"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function main(){

		$this->core->header .= '<script src="'.LANG_URL.'js/modules/info-main.js"></script>';
		$this->core->header .= '<script src="'.STYLE_URL.'js/modules/admin/info-main.js"></script>';

		return $this->core->sp(MCR_THEME_MOD."admin/info/main.html");
	}

	private function users_stats(){
		$query = $this->db->query("SELECT `g`.id, `g`.title, COUNT(`u`.`id`) AS `count`
									FROM `mcr_groups` AS `g`
									LEFT JOIN `mcr_users` AS `u`
										ON `u`.`gid`=`g`.`id`
									GROUP BY `g`.`id`");
		if(!$query || $this->db->num_rows($query)<=0){ return; }

		ob_start();

		while($ar = $this->db->fetch_assoc($query)){

			switch(intval($ar['id'])){
				case 0: $class='error'; break;
				case 1: $class='warning'; break;
				case 2: $class='success'; break;
				case 3: $class='info'; break;

				default: $class=''; break;
			}

			$data = array(
				"CLASS" => $class,
				"TITLE" => $this->db->HSC($ar['title']),
				"COUNT" => intval($ar['count'])
			);

			echo $this->core->sp(MCR_THEME_MOD."admin/info/group-id.html", $data);
		}

		return ob_get_clean();
	}

	private function stats(){

		$query = $this->db->query("SELECT COUNT(*) AS `users`,
										(SELECT COUNT(*) FROM `mcr_news`) AS `news`,
										(SELECT COUNT(*) FROM `mcr_news_cats`) AS `categories`,
										(SELECT COUNT(*) FROM `mcr_comments`) AS `comments`,
										(SELECT COUNT(*) FROM `mcr_statics`) AS `statics`,
										(SELECT COUNT(*) FROM `mcr_groups`) AS `groups`,
										(SELECT COUNT(*) FROM `mcr_news_views`) AS `views`,
										(SELECT COUNT(*) FROM `mcr_news_votes`) AS `votes`,
										(SELECT COUNT(*) FROM `mcr_permissions`) AS `permissions`
									FROM `mcr_users`");

		if(!$query || $this->db->num_rows($query)<=0){ return; }

		$ar = $this->db->fetch_assoc($query);

		$data = array(
			"COUNT_USERS"			=> intval($ar['users']),
			"COUNT_GROUPS"			=> intval($ar['groups']),
			"COUNT_NEWS"			=> intval($ar['news']),
			"COUNT_COMMENTS"		=> intval($ar['comments']),
			"COUNT_CATEGORIES"		=> intval($ar['categories']),
			"COUNT_STATICS"			=> intval($ar['statics']),
			"COUNT_VIEWS"			=> intval($ar['views']),
			"COUNT_VOTES"			=> intval($ar['votes']),
			"COUNT_PERMISSIONS"		=> intval($ar['permissions']),
			"USERS_STATS"			=> $this->users_stats()
		);

		return $this->core->sp(MCR_THEME_MOD."admin/info/stats.html", $data);
	}

	private function extensions(){
		$this->core->header .= '<script src="'.LANG_URL.'js/modules/info-extensions.js"></script>';
		$this->core->header .= '<script src="'.STYLE_URL.'js/modules/admin/info-extensions.js"></script>';

		return $this->core->sp(MCR_THEME_MOD."admin/info/extensions.html");
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'stats':		$content = $this->stats(); break;
			case 'extensions':	$content = $this->extensions(); break;

			default:			$content = $this->main(); break;
		}

		return $content;
	}
}

?>