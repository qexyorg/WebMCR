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

		$this->core->title = $this->lng['t_admin'].' — Просмотры';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Просмотры' => BASE_URL."?mode=admin&do=news_views"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function views_array(){

		$start		= $this->core->pagination($this->config->pagin['adm_news_views'], 0, 0); // Set start pagination
		$end		= $this->config->pagin['adm_news_views']; // Set end pagination

		$query = $this->db->query("SELECT `v`.id, `v`.nid, `v`.uid, `v`.`time`, `n`.title, `u`.login
									FROM `mcr_news_views` AS `v`
									LEFT JOIN `mcr_news` AS `n`
										ON `n`.id=`v`.nid
									LEFT JOIN `mcr_users` AS `u`
										ON `u`.id=`v`.uid
									ORDER BY `v`.id DESC
									LIMIT $start, $end");

		ob_start();

		if(!$query || $this->db->num_rows($query)<=0){
			echo $this->core->sp(MCR_THEME_MOD."admin/news_views/view-none.html");
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

			$page_data = array(
				"ID" => intval($ar['id']),
				"NID" => intval($ar['nid']),
				"NEW" => $new,
				"LOGIN" => $this->db->HSC($ar['login']),
				"UID" => intval($ar['uid']),
				"TIME_CREATE" => date("d.m.Y в H:i", $ar['time']),
				"STATUS_CLASS" => $status_class
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/news_views/view-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function views_list(){

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_news_views`");

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->config->pagin['adm_news_views'], "?mode=admin&do=news_views&pid=", $ar[0]),
			"VIEWS" => $this->views_array()
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/news_views/view-list.html", $data);

		return ob_get_clean();
	}

	private function delete(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->lng["e_msg"], $this->lng['e_hack'], 2, '?mode=admin&do=news_views'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->lng["e_msg"], "Не выбрано ни одного пункта", 2, '?mode=admin&do=news_views'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		$delete1 = $this->db->query("DELETE FROM `mcr_news_views` WHERE id IN ($list)");

		if(!$delete1){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=news_views'); }

		$count1 = $this->db->affected_rows();

		$this->core->notify($this->lng["e_success"], "Удалено элементов: просмотров - $count1", 3, '?mode=admin&do=news_views');

	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'delete':	$this->delete(); break;

			default:		$content = $this->views_list(); break;
		}

		ob_start();

		echo $content;

		return ob_get_clean();
	}
}

?>