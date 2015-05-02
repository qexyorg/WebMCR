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

		$this->core->title = $this->lng['t_admin'].' — Комментарии';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Комментарии' => BASE_URL."?mode=admin&do=comments"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function comment_array(){

		$start		= $this->core->pagination($this->config->pagin['adm_comments'], 0, 0); // Set start pagination
		$end		= $this->config->pagin['adm_comments']; // Set end pagination

		$query = $this->db->query("SELECT `c`.id, `c`.nid, `c`.text_html, `n`.title AS `new`
									FROM `mcr_comments` AS `c`
									LEFT JOIN `mcr_news` AS `n`
										ON `n`.id=`c`.nid
									ORDER BY `c`.id DESC
									LIMIT $start, $end");

		ob_start();

		if(!$query || $this->db->num_rows($query)<=0){
			echo $this->core->sp(MCR_THEME_MOD."admin/comments/com-none.html");
			return ob_get_clean();
		}

		while($ar = $this->db->fetch_assoc($query)){

			$text = strip_tags($ar['text_html']);

			$text = mb_substr($text, 0, 24, "UTF-8").'...';

			$new = (empty($ar['new'])) ? 'Новость удалена' : $this->db->HSC($ar['new']);

			$page_data = array(
				"ID" => intval($ar['id']),
				"NID" => intval($ar['nid']),
				"NEW" => $new,
				"TEXT" => $this->db->HSC($text)
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/comments/com-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function comment_list(){

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_comments`");

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->config->pagin['adm_comments'], "?mode=admin&do=comments&pid=", $ar[0]),
			"COMMENTS" => $this->comment_array()
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/comments/com-list.html", $data);

		return ob_get_clean();
	}

	private function delete(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->lng["e_msg"], $this->lng['e_hack'], 2, '?mode=admin&do=comments'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->lng["e_msg"], "Не выбрано ни одного пункта", 2, '?mode=admin&do=comments'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		$delete1 = $this->db->query("DELETE FROM `mcr_comments` WHERE id IN ($list)");

		if(!$delete1){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=comments'); }

		$count1 = $this->db->affected_rows();

		$this->core->notify($this->lng["e_success"], "Удалено элементов: комментариев - $count1", 3, '?mode=admin&do=comments');

	}

	private function news($selected=1){
		$selected = intval($selected);
		$query = $this->db->query("SELECT id, title FROM `mcr_news` ORDER BY title ASC");

		ob_start();

		if(!$query || $this->db->num_rows($query)<=0){

			$data = array(
				"ID" => 1,
				"TITLE" => 'Без новости',
				"SELECTED" => 'selected disabled'
			);

			echo $this->core->sp(MCR_THEME_MOD."admin/comments/nid-list-id.html", $data);
			return ob_get_clean();
		}

		while($ar = $this->db->fetch_assoc($query)){
			$data = array(
				"ID" => intval($ar['id']),
				"TITLE" => $this->db->HSC($ar['title']),
				"SELECTED" => ($selected==intval($ar['id'])) ? 'selected' : ''
			);

			echo $this->core->sp(MCR_THEME_MOD."admin/comments/nid-list-id.html", $data);
		}

		return ob_get_clean();
	}

	private function add(){

		$this->core->title .= ' — Добавление';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Комментарии' => BASE_URL."?mode=admin&do=comments",
			'Добавление' => BASE_URL."?mode=admin&do=comments&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$bb = $this->core->load_bb_class(); // Загрузка класса BB-кодов

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$nid = intval(@$_POST['nid']);

			// Обработка описания +
			$text_bb = @$_POST['text'];

			$text_bb_trim = trim($text_bb);

			if(empty($text_bb_trim)){ $this->core->notify($this->lng["e_msg"], "Не заполнено поле \"Комментарий\"", 2, '?mode=admin&do=comments&op=add'); }

			$text_bb				= $this->db->HSC($text_bb);

			$text_html				= $bb->decode($text_bb);

			$safe_text_html			= $this->db->safesql($text_html);

			$text_bb				= $this->db->safesql($text_bb);

			$text_html_strip		= trim(strip_tags($text_html, "<img>"));

			if(empty($text_html_strip)){ $this->core->notify($this->lng["e_msg"], "Не верно заполнено поле \"Комментарий\"", 2, '?mode=admin&do=comments&op=add'); }
			// Обработка описания -

			$new_data = array(
				"time_create" => time(),
				"time_last" => time()
			);

			$new_data = $this->db->safesql(json_encode($new_data));

			$insert = $this->db->query("INSERT INTO `mcr_comments`
											(nid, text_bb, text_html, uid, `data`)
										VALUES
											('$nid', '$text_bb', '$text_html', '{$this->user->id}', '$new_data')");

			if(!$insert){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=comments&op=add'); }
			
			$this->core->notify($this->lng["e_success"], "Комментарий успешно добавлен", 3, '?mode=admin&do=comments');
		}

		$data = array(
			"PAGE" => "Добавление комментария",
			"NEWS" => $this->news(),
			"TEXT" => "",
			"BB_PANEL" => $bb->bb_panel('bb-comment'),
			"BUTTON" => "Добавить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/comments/com-add.html", $data);

		return ob_get_clean();
	}

	private function edit(){

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT nid, text_bb, `data`
									FROM `mcr_comments`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=comments'); }

		$ar = $this->db->fetch_assoc($query);

		$data = json_decode($ar['data']);

		$this->core->title .= ' — Редактирование';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Комментарии' => BASE_URL."?mode=admin&do=comments",
			'Редактирование' => BASE_URL."?mode=admin&do=comments&op=edit&id=$id"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$bb = $this->core->load_bb_class(); // Загрузка класса BB-кодов

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$nid = intval(@$_POST['nid']);

			// Обработка описания +
			$text_bb = @$_POST['text'];

			$text_bb_trim = trim($text_bb);

			if(empty($text_bb_trim)){ $this->core->notify($this->lng["e_msg"], "Не заполнено поле \"Комментарий\"", 2, '?mode=admin&do=comments&op=add'); }

			$text_bb				= $this->db->HSC($text_bb);

			$text_html				= $bb->decode($text_bb);

			$safe_text_html			= $this->db->safesql($text_html);

			$text_bb				= $this->db->safesql($text_bb);

			$text_html_strip		= trim(strip_tags($text_html, "<img>"));

			if(empty($text_html_strip)){ $this->core->notify($this->lng["e_msg"], "Не верно заполнено поле \"Комментарий\"", 2, '?mode=admin&do=comments&op=add'); }
			// Обработка описания -

			$new_data = array(
				"time_create" => $data->time_create,
				"time_last" => time()
			);

			$new_data = $this->db->safesql(json_encode($new_data));

			$update = $this->db->query("UPDATE `mcr_comments`
										SET nid='$nid', text_bb='$text_bb', text_html='$safe_text_html', `data`='$new_data'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=comments&op=edit&id='.$id); }
			
			$this->core->notify($this->lng["e_success"], "Комментарий успешно изменен", 3, '?mode=admin&do=comments');
		}

		$data = array(
			"PAGE" => "Редактирование комментария",
			"NEWS" => $this->news($ar['nid']),
			"TEXT" => $this->db->HSC($ar['text_bb']),
			"BB_PANEL" => $bb->bb_panel('bb-comment'),
			"BUTTON" => "Сохранить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/comments/com-add.html", $data);

		return ob_get_clean();
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;

			default:		$content = $this->comment_list(); break;
		}

		ob_start();

		echo $content;

		return ob_get_clean();
	}
}

?>