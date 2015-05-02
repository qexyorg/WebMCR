<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $config, $user, $lng;

	public function __construct($core){
		$this->core = $core;
		$this->db	= $core->db;
		$this->config = $core->config;
		$this->user	= $core->user;
		$this->lng	= $core->lng;

		$this->core->title = $this->lng['t_admin'].' — Голоса';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Голоса' => BASE_URL."?mode=admin&do=news_votes"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function votes_array(){

		$start		= $this->core->pagination($this->config->pagin['adm_news_votes'], 0, 0); // Set start pagination
		$end		= $this->config->pagin['adm_news_votes']; // Set end pagination

		$query = $this->db->query("SELECT `v`.id, `v`.nid, `v`.uid, `v`.`value`, `v`.`time`, `n`.title, `u`.login
									FROM `mcr_news_votes` AS `v`
									LEFT JOIN `mcr_news` AS `n`
										ON `n`.id=`v`.nid
									LEFT JOIN `mcr_users` AS `u`
										ON `u`.id=`v`.uid
									ORDER BY `v`.id DESC
									LIMIT $start, $end");

		ob_start();

		if(!$query || $this->db->num_rows($query)<=0){
			echo $this->core->sp(MCR_THEME_MOD."admin/news_votes/vote-none.html");
			return ob_get_clean();
		}

		while($ar = $this->db->fetch_assoc($query)){

			if(empty($ar['title'])){
				$status_class = 'error';
				$new = 'Новость удалена';
			}else{
				$status_class = '';
				$new = $this->db->HSC($ar['title']);
			}

			$value = (intval($ar['value'])===1) ? 'icon-thumbs-up' : 'icon-thumbs-down';

			$page_data = array(
				"ID" => intval($ar['id']),
				"NID" => intval($ar['nid']),
				"NEW" => $new,
				"LOGIN" => $this->db->HSC($ar['login']),
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

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_news_votes`");

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->config->pagin['adm_news_votes'], "?mode=admin&do=news_votes&pid=", $ar[0]),
			"VOTES" => $this->votes_array()
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/news_votes/vote-list.html", $data);

		return ob_get_clean();
	}

	private function delete(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->lng["e_msg"], $this->lng['e_hack'], 2, '?mode=admin&do=news_votes'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->lng["e_msg"], "Не выбрано ни одного пункта", 2, '?mode=admin&do=news_votes'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		$delete1 = $this->db->query("DELETE FROM `mcr_news_votes` WHERE id IN ($list)");

		if(!$delete1){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=news_votes'); }

		$count1 = $this->db->affected_rows();

		$this->core->notify($this->lng["e_success"], "Удалено элементов: голосов - $count1", 3, '?mode=admin&do=news_votes');

	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'delete':	$this->delete(); break;

			default:		$content = $this->votes_list(); break;
		}

		ob_start();

		echo $content;

		return ob_get_clean();
	}
}

?>