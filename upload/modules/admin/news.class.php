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

		$this->core->title = $this->lng['t_admin'].' — Новости';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Новости' => BASE_URL."?mode=admin&do=news"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function news_array(){

		$start		= $this->core->pagination($this->config->pagin['adm_news'], 0, 0); // Set start pagination
		$end		= $this->config->pagin['adm_news']; // Set end pagination

		$query = $this->db->query("SELECT `n`.id, `n`.cid, `n`.title, `c`.title AS `category`
									FROM `mcr_news` AS `n`
									LEFT JOIN `mcr_news_cats` AS `c`
										ON `c`.id=`n`.cid
									ORDER BY `n`.id DESC
									LIMIT $start, $end");

		ob_start();

		if(!$query || $this->db->num_rows($query)<=0){
			echo $this->core->sp(MCR_THEME_MOD."admin/news/new-none.html");
			return ob_get_clean();
		}

		while($ar = $this->db->fetch_assoc($query)){

			$page_data = array(
				"ID" => intval($ar['id']),
				"CID" => intval($ar['cid']),
				"TITLE" => $this->db->HSC($ar['title']),
				"CATEGORY" => $this->db->HSC($ar['category'])
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/news/new-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function news_list(){

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_news`");

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->config->pagin['adm_news'], "?mode=admin&do=news&pid=", $ar[0]),
			"NEWS" => $this->news_array()
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/news/new-list.html", $data);

		return ob_get_clean();
	}

	private function delete(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->lng["e_msg"], $this->lng['e_hack'], 2, '?mode=admin&do=news'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->lng["e_msg"], "Не выбрано ни одного пункта", 2, '?mode=admin&do=news'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		$delete1 = $this->db->query("DELETE FROM `mcr_news` WHERE id IN ($list)");

		if(!$delete1){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=news'); }

		$count1 = $this->db->affected_rows();

		$delete2 = $this->db->query("DELETE FROM `mcr_news_views` WHERE nid IN ($list)");

		if(!$delete2){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=news'); }

		$count2 = $this->db->affected_rows();

		$delete3 = $this->db->query("DELETE FROM `mcr_news_votes` WHERE nid IN ($list)");

		if(!$delete3){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=news'); }

		$count3 = $this->db->affected_rows();

		$this->core->notify($this->lng["e_success"], "Удалено элементов: новостей - $count1, просмотров - $count2, голосов - $count3", 3, '?mode=admin&do=news');

	}

	private function categories($selected=1){
		$selected = intval($selected);
		$query = $this->db->query("SELECT id, title FROM `mcr_news_cats` ORDER BY title ASC");

		ob_start();

		if(!$query || $this->db->num_rows($query)<=0){

			$data = array(
				"ID" => 1,
				"TITLE" => 'Без категории',
				"SELECTED" => 'selected disabled'
			);

			echo $this->core->sp(MCR_THEME_MOD."admin/news/cid-list-id.html", $data);
			return ob_get_clean();
		}

		while($ar = $this->db->fetch_assoc($query)){
			$data = array(
				"ID" => intval($ar['id']),
				"TITLE" => $this->db->HSC($ar['title']),
				"SELECTED" => ($selected==intval($ar['id'])) ? 'selected' : ''
			);

			echo $this->core->sp(MCR_THEME_MOD."admin/news/cid-list-id.html", $data);
		}

		return ob_get_clean();
	}

	private function add(){

		$this->core->title .= ' — Добавление';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Новости' => BASE_URL."?mode=admin&do=news",
			'Добавление' => BASE_URL."?mode=admin&do=news&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$bb = $this->core->load_bb_class(); // Загрузка класса BB-кодов

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title = $this->db->safesql(@$_POST['title']);

			$cid = intval(@$_POST['cid']);

			$vote	= (intval(@$_POST['vote'])===1) ? 1 : 0;
			$discus	= (intval(@$_POST['discus'])===1) ? 1 : 0;
			$attach	= (intval(@$_POST['attach'])===1) ? 1 : 0;

			// Обработка описания +
			$text_bb_short = @$_POST['text_short'];
			$text_bb = @$_POST['text'];

			$text_bb_trim = trim($text_bb);
			$text_bb_short_trim = trim($text_bb_short);

			if(empty($text_bb_trim)){ $this->core->notify($this->lng["e_msg"], "Не заполнено поле \"Полное описание\"", 2, '?mode=admin&do=news&op=add'); }
			if(empty($text_bb_short_trim)){ $this->core->notify($this->lng["e_msg"], "Не заполнено поле \"Краткое описание\"", 2, '?mode=admin&do=news&op=add'); }

			$text_bb				= $this->db->HSC($text_bb);
			$text_bb_short			= $this->db->HSC($text_bb_short);

			$text_html				= $bb->decode($text_bb);
			$text_html_short		= $bb->decode($text_bb_short);

			$safe_text_html			= $this->db->safesql($text_html);
			$safe_text_html_short	= $this->db->safesql($text_html_short);

			$text_bb				= $this->db->safesql($text_bb);
			$text_bb_short			= $this->db->safesql($text_bb_short);

			$text_html_strip		= trim(strip_tags($text_html, "<img>"));
			$text_html_short_strip	= trim(strip_tags($text_html_short, "<img>"));

			if(empty($text_html_strip)){ $this->core->notify($this->lng["e_msg"], "Не верно заполнено поле \"Полное описание\"", 2, '?mode=admin&do=news&op=add'); }
			if(empty($text_html_short_strip)){ $this->core->notify($this->lng["e_msg"], "Не верно заполнено поле \"Краткое описание\"", 2, '?mode=admin&do=news&op=add'); }
			// Обработка описания -

			$new_data = array(
				"time_create" => time(),
				"time_last" => time(),
				"uid_create" => $this->user->id,
				"uid_last" => $this->user->id
			);

			$new_data = $this->db->safesql(json_encode($new_data));

			$insert = $this->db->query("INSERT INTO `mcr_news`
											(cid, title, text_bb, text_html, text_bb_short, text_html_short, vote, discus, attach, uid, `data`)
										VALUES
											('$cid', '$title', '$text_bb', '$safe_text_html', '$text_bb_short', '$safe_text_html_short', '$vote', '$discus', '$attach', '{$this->user->id}', '$new_data')");

			if(!$insert){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=news&op=add'); }
			
			$this->core->notify($this->lng["e_success"], "Новость успешно добавлена", 3, '?mode=admin&do=news');
		}

		$data = array(
			"PAGE" => "Добавление новости",
			"CATEGORIES" => $this->categories(),
			"BB_PANEL_SHORT" => $bb->bb_panel('bb-short'),
			"BB_PANEL_FULL" => $bb->bb_panel('bb-full'),
			"TITLE" => "",
			"TEXT_SHORT" => "",
			"TEXT" => "",
			"VOTE" => "checked",
			"DISCUS" => "checked",
			"ATTACH" => "",
			"BUTTON" => "Добавить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/news/new-add.html", $data);

		return ob_get_clean();
	}

	private function edit(){

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT cid, title, text_bb, text_bb_short, vote, discus, attach, `data`
									FROM `mcr_news`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=news'); }

		$ar = $this->db->fetch_assoc($query);

		$data = json_decode($ar['data']);

		$this->core->title .= ' — Редактирование';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Новости' => BASE_URL."?mode=admin&do=news",
			'Редактирование' => BASE_URL."?mode=admin&do=news&op=edit&id=$id"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$bb = $this->core->load_bb_class(); // Загрузка класса BB-кодов

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title = $this->db->safesql(@$_POST['title']);

			$cid = intval(@$_POST['cid']);

			$vote	= (intval(@$_POST['vote'])===1) ? 1 : 0;
			$discus	= (intval(@$_POST['discus'])===1) ? 1 : 0;
			$attach	= (intval(@$_POST['attach'])===1) ? 1 : 0;

			// Обработка описания +
			$text_bb_short = @$_POST['text_short'];
			$text_bb = @$_POST['text'];

			$text_bb_trim = trim($text_bb);
			$text_bb_short_trim = trim($text_bb_short);

			if(empty($text_bb_trim)){ $this->core->notify($this->lng["e_msg"], "Не заполнено поле \"Полное описание\"", 2, '?mode=admin&do=news&op=edit&id='.$id); }
			if(empty($text_bb_short_trim)){ $this->core->notify($this->lng["e_msg"], "Не заполнено поле \"Краткое описание\"", 2, '?mode=admin&do=news&op=edit&id='.$id); }

			$text_bb				= $this->db->HSC($text_bb);
			$text_bb_short			= $this->db->HSC($text_bb_short);

			$text_html				= $bb->decode($text_bb);
			$text_html_short		= $bb->decode($text_bb_short);

			$safe_text_html			= $this->db->safesql($text_html);
			$safe_text_html_short	= $this->db->safesql($text_html_short);

			$text_bb				= $this->db->safesql($text_bb);
			$text_bb_short			= $this->db->safesql($text_bb_short);

			$text_html_strip		= trim(strip_tags($text_html, "<img>"));
			$text_html_short_strip	= trim(strip_tags($text_html_short, "<img>"));

			if(empty($text_html_strip)){ $this->core->notify($this->lng["e_msg"], "Не верно заполнено поле \"Полное описание\"", 2, '?mode=admin&do=news&op=edit&id='.$id); }
			if(empty($text_html_short_strip)){ $this->core->notify($this->lng["e_msg"], "Не верно заполнено поле \"Краткое описание\"", 2, '?mode=admin&do=news&op=edit&id='.$id); }
			// Обработка описания -

			$new_data = array(
				"time_create" => $data->time_create,
				"time_last" => time(),
				"uid_create" => $data->uid_create,
				"uid_last" => $this->user->id
			);

			$new_data = $this->db->safesql(json_encode($new_data));

			$update = $this->db->query("UPDATE `mcr_news`
										SET cid='$cid', title='$title', text_bb='$text_bb', text_html='$safe_text_html',
											text_bb_short='$text_bb_short', text_html_short='$safe_text_html_short',
											vote='$vote', discus='$discus', attach='$attach', `data`='$new_data'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=news&op=edit&id='.$id); }
			
			$this->core->notify($this->lng["e_success"], "Новость успешно изменена", 3, '?mode=admin&do=news');
		}

		$data = array(
			"PAGE" => "Редактирование новости",
			"CATEGORIES" => $this->categories($ar['cid']),
			"TITLE" => $this->db->HSC($ar['title']),
			"TEXT_SHORT" => $this->db->HSC($ar['text_bb_short']),
			"TEXT" => $this->db->HSC($ar['text_bb']),
			"VOTE" => (intval($ar['vote'])===1) ? 'checked' : '',
			"DISCUS" => (intval($ar['discus'])===1) ? 'checked' : '',
			"ATTACH" => (intval($ar['attach'])===1) ? 'checked' : '',
			"BB_PANEL_SHORT" => $bb->bb_panel('bb-short'),
			"BB_PANEL_FULL" => $bb->bb_panel('bb-full'),
			"BUTTON" => "Сохранить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/news/new-add.html", $data);

		return ob_get_clean();
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;

			default:		$content = $this->news_list(); break;
		}

		ob_start();

		echo $content;

		return ob_get_clean();
	}
}

?>