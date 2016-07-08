<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $cfg, $user, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;
		$this->user		= $core->user;
		$this->lng		= $core->lng_m;

		if(!$this->core->is_access('sys_adm_news_votes')){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['votes'] => ADMIN_URL."&do=news_votes"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$this->core->header .= $this->core->sp(MCR_THEME_MOD."admin/news_votes/header.html");
	}

	private function votes_array(){

		$start		= $this->core->pagination($this->cfg->pagin['adm_news_votes'], 0, 0); // Set start pagination
		$end		= $this->cfg->pagin['adm_news_votes']; // Set end pagination

		$ctables	= $this->cfg->db['tables'];

		$ug_f		= $ctables['ugroups']['fields'];
		$us_f		= $ctables['users']['fields'];

		$sort		= "`v`.id";
		$sortby		= "DESC";

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$expl = explode(' ', $_GET['sort']);

			$sortby = ($expl[0]=='asc') ? "ASC" : "DESC";

			switch(@$expl[1]){
				case 'news': $sort = "`n`.title"; break;
				case 'value': $sort = "`v`.`value`"; break;
				case 'user': $sort = "`u`.`{$us_f['login']}`"; break;
				case 'date': $sort = "`v`.`time`"; break;
			}
		}

		$query = $this->db->query("SELECT `v`.id, `v`.nid, `v`.uid, `v`.`value`, `v`.`time`, `n`.title,
										`u`.`{$us_f['login']}`, `u`.`{$us_f['color']}`, `g`.`{$ug_f['color']}` AS `gcolor`
									FROM `mcr_news_votes` AS `v`
									LEFT JOIN `mcr_news` AS `n`
										ON `n`.id=`v`.nid
									LEFT JOIN `{$this->cfg->tabname('users')}` AS `u`
										ON `u`.`{$us_f['id']}`=`v`.uid
									LEFT JOIN `{$this->cfg->tabname('ugroups')}` AS `g`
										ON `g`.`{$ug_f['id']}`=`u`.`{$us_f['group']}`
									ORDER BY $sort $sortby
									LIMIT $start, $end");

		if(!$query || $this->db->num_rows($query)<=0){ return $this->core->sp(MCR_THEME_MOD."admin/news_votes/vote-none.html"); }

		ob_start();

		while($ar = $this->db->fetch_assoc($query)){

			if(empty($ar['title'])){
				$status_class = 'error';
				$new = $this->lng['nvt_news_deleted'];
			}else{
				$status_class = '';
				$new = $this->db->HSC($ar['title']);
			}

			$value = (intval($ar['value'])===1) ? 'icon-thumbs-up' : 'icon-thumbs-down';

			$login = (is_null($ar[$us_f['login']])) ? 'Пользователь удален' : $this->db->HSC($ar[$us_f['login']]);

			$color = (empty($ar[$us_f['color']])) ? $this->db->HSC($ar['gcolor']) : $this->db->HSC($ar[$us_f['color']]);

			$page_data = array(
				"ID" => intval($ar['id']),
				"NID" => intval($ar['nid']),
				"NEW" => $new,
				"LOGIN" => $this->core->colorize($login, $color),
				"UID" => intval($ar['uid']),
				"TIME_CREATE" => date("d.m.Y в H:i", $ar['time']),
				"STATUS_CLASS" => $status_class,
				"VALUE" => $value
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/news_votes/vote-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function votes_list(){

		$page = "?mode=admin&do=news_votes";

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$page .= '&sort='.$this->db->HSC(urlencode($_GET['sort']));
		}

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_news_votes`");

		$ar = @$this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->cfg->pagin['adm_news_votes'], $page."&pid=", $ar[0]),
			"VOTES" => $this->votes_array()
		);

		return $this->core->sp(MCR_THEME_MOD."admin/news_votes/vote-list.html", $data);
	}

	private function delete(){
		if(!$this->core->is_access('sys_adm_news_votes_delete')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=news_votes'); }

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=news_votes'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['nvt_not_selected'], 2, '?mode=admin&do=news_votes'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		if(!$this->db->remove_fast("mcr_news_votes", "id IN ($list)")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=news_votes'); }

		$count1 = $this->db->affected_rows();

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_del_nvt']." $list ".$this->lng['log_nvt'], $this->user->id);

		$this->core->notify($this->core->lng["e_success"], $this->lng['nvt_del_elements']." $count1", 3, '?mode=admin&do=news_votes');

	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'delete':	$this->delete(); break;

			default:		$content = $this->votes_list(); break;
		}

		return $content;
	}
}

?>