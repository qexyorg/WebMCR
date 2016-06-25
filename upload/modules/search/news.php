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

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=search",
			$this->lng['by_news'] => BASE_URL."?mode=search&type=news"
		);
		
		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function results_array($value){

		$start		= $this->core->pagination($this->cfg->pagin['search_news'], 0, 0); // Set start pagination
		$end		= $this->cfg->pagin['search_news']; // Set end pagination
		
		//, `n`.cid, `c`.title AS `category`

		$query = $this->db->query("SELECT `n`.id, `n`.title, `n`.text_html
									FROM `mcr_news` AS `n`
									LEFT JOIN `mcr_news_cats` AS `c`
										ON `c`.id=`n`.cid
									WHERE `n`.title LIKE '%$value%' OR `n`.text_bb LIKE '%$value%'
									LIMIT $start, $end");

		if(!$query || $this->db->num_rows($query)<=0){ /*echo $this->core->sp(MCR_THEME_MOD."search/news/news-none.html");*/ return; }

		ob_start();
		
		while($ar = $this->db->fetch_assoc($query)){

			$title = $this->db->HSC($ar['title']);
			$text = trim(strip_tags($ar['text_html']));

			$safe_value = $this->db->HSC($value);

			$text = $this->db->HSC($text);

			//$text_len = mb_strlen($text, "UTF-8");
			//if($text_len > 255){ $text = mb_substr($text, 0, 255, "UTF-8").'...'; }

			$title = preg_replace("/$value/iu", '<span class="search-selected">$0</span>', $title);

			$text = preg_replace("/$value/iu", '<span class="search-selected">$0</span>', $text);

			$data = array(
				"ID"		=> intval($ar['id']),
				"TITLE"		=> $title,
				//"CID"		=> intval($ar['cid']),
				//"CATEGORY"	=> $this->db->HSC($ar['category']),
				"TEXT"		=> $text
			);

			echo $this->core->sp(MCR_THEME_MOD."search/news/news-id.html", $data);
		}

		return ob_get_clean();
	}

	public function results(){
		
		if(!$this->core->is_access('sys_search_news')){ $this->core->notify($this->core->lng['403'], $this->core->lng['t_403'], 1, "?mode=403"); }

		$value = (isset($_GET['value'])) ? $_GET['value'] : '';

		$value = trim(urldecode($value));

		if(empty($value)){ $this->core->notify($this->core->lng['404'], $this->lng['empty_query'], 2, "?mode=403"); }

		$safe_value = $this->db->safesql($value);
		$html_value = $this->db->HSC($value);

		$sql = "SELECT COUNT(*) FROM `mcr_news` WHERE title LIKE '%$safe_value%' OR text_bb LIKE '%$safe_value%'";

		$query = $this->db->query($sql);

		if(!$query){ $this->core->notify($this->core->lng['e_msg'], $this->core->lng['e_sql_critical'], 2); }

		$ar = $this->db->fetch_array($query);

		$page = "?mode=search&type=news&value=$html_value&pid=";

		$data = array(
			"PAGINATION" => $this->core->pagination($this->cfg->pagin['search_news'], $page, $ar[0]),
			"RESULT" => $this->results_array($safe_value),
			"QUERY" => $html_value,
			"QUERY_COUNT" => intval($ar[0])
		);

		return $this->core->sp(MCR_THEME_MOD."search/news/news-list.html", $data);
	}
}

?>