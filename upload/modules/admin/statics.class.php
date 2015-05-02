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

		$this->core->title = $this->lng['t_admin'].' — Статические страницы';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Статические страницы' => BASE_URL."?mode=admin&do=statics"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function static_array(){

		$start		= $this->core->pagination($this->config->pagin['adm_statics'], 0, 0); // Set start pagination
		$end		= $this->config->pagin['adm_statics']; // Set end pagination

		$query = $this->db->query("SELECT `s`.id, `s`.`uniq`, `s`.title, `s`.uid,
										`p`.title AS `perm`
									FROM `mcr_statics` AS `s`
									LEFT JOIN `mcr_permissions` AS `p`
										ON `p`.`value`=`s`.`permissions`
									ORDER BY `s`.id DESC
									LIMIT $start, $end");

		ob_start();

		if(!$query || $this->db->num_rows($query)<=0){
			echo $this->core->sp(MCR_THEME_MOD."admin/statics/static-none.html");
			return ob_get_clean();
		}

		while($ar = $this->db->fetch_assoc($query)){

			$perm = (empty($ar['perm'])) ? "ОТСУТСТВУЕТ" : $this->db->HSC($ar['perm']);

			$page_data = array(
				"ID" => intval($ar['id']),
				"UID" => intval($ar['uid']),
				"UNIQ" => $this->db->HSC($ar['uniq']),
				"TITLE" => $this->db->HSC($ar['title']),
				"PERMISSIONS" => $perm
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/statics/static-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function static_list(){

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_statics`");

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->config->pagin['adm_statics'], "?mode=admin&do=statics&pid=", $ar[0]),
			"STATICS" => $this->static_array()
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/statics/static-list.html", $data);

		return ob_get_clean();
	}

	private function delete(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->lng["e_msg"], $this->lng['e_hack'], 2, '?mode=admin&do=statics'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->lng["e_msg"], "Не выбрано ни одного пункта", 2, '?mode=admin&do=statics'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		$delete1 = $this->db->query("DELETE FROM `mcr_statics` WHERE id IN ($list)");

		if(!$delete1){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=statics'); }

		$count1 = $this->db->affected_rows();

		$this->core->notify($this->lng["e_success"], "Удалено элементов: страниц - $count1", 3, '?mode=admin&do=statics');

	}

	private function add(){

		$this->core->title .= ' — Добавление';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Статические страницы' => BASE_URL."?mode=admin&do=statics",
			'Добавление' => BASE_URL."?mode=admin&do=statics&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);
		
		$bb = $this->core->load_bb_class(); // Загрузка класса BB-кодов

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title = $this->db->safesql(@$_POST['title']);

			$uniq = $this->db->safesql(@$_POST['uniq']);

			$permissions = $this->db->safesql(@$_POST['permissions']);

			// Обработка описания +
			$text_bb = @$_POST['text'];

			$text_bb_trim = trim($text_bb);

			if(empty($text_bb_trim)){ $this->core->notify($this->lng["e_msg"], "Не заполнено поле \"Текст страницы\"", 2, '?mode=admin&do=statics&op=add'); }

			$text_bb				= $this->db->HSC($text_bb);

			$text_html				= $bb->decode($text_bb);

			$safe_text_html			= $this->db->safesql($text_html); // in base
			$text_bb				= $this->db->safesql($text_bb); // in base

			$text_html_strip		= trim(strip_tags($text_html, "<img>"));

			if(empty($text_html_strip)){ $this->core->notify($this->lng["e_msg"], "Не верно заполнено поле \"Текст страницы\"", 2, '?mode=admin&do=statics&op=add'); }
			// Обработка описания -

			$new_data = array(
				"time_create" => time(),
				"time_last" => time(),
				"login_create" => $this->user->login,
				"login_last" => $this->user->login
			);

			$new_data = $this->db->safesql(json_encode($new_data));

			$insert = $this->db->query("INSERT INTO `mcr_statics`
											(`uniq`, title, text_bb, text_html, uid, `permissions`, `data`)
										VALUES
											('$uniq', '$title', '$text_bb', '$safe_text_html', '{$this->user->id}', '$permissions', '$new_data')");

			if(!$insert){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=statics&op=add'); }
			
			$this->core->notify($this->lng["e_success"], "Статическая страница успешно добавлена", 3, '?mode=admin&do=statics');
		}

		$data = array(
			"PAGE" => "Добавление статической страницы",
			"TITLE" => "",
			"UNIQ" => "",
			"TEXT" => "",
			"PERMISSIONS" => $this->core->perm_list(),
			"BB_PANEL" => $bb->bb_panel('stc-field'),
			"BUTTON" => "Добавить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/statics/static-add.html", $data);

		return ob_get_clean();
	}

	private function edit(){

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT `uniq`, title, text_bb, `permissions`, `data`
									FROM `mcr_statics`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=statics'); }

		$ar = $this->db->fetch_assoc($query);

		$data = json_decode($ar['data']);

		$this->core->title .= ' — Редактирование';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Статические страницы' => BASE_URL."?mode=admin&do=statics",
			'Редактирование' => BASE_URL."?mode=admin&do=statics&op=edit&id=$id"
		);

		$this->core->bc = $this->core->gen_bc($bc);
		
		$bb = $this->core->load_bb_class(); // Загрузка класса BB-кодов

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title = $this->db->safesql(@$_POST['title']);

			$uniq = $this->db->safesql(@$_POST['uniq']);

			$permissions = $this->db->safesql(@$_POST['permissions']);

			// Обработка описания +
			$text_bb = @$_POST['text'];

			$text_bb_trim = trim($text_bb);

			if(empty($text_bb_trim)){ $this->core->notify($this->lng["e_msg"], "Не заполнено поле \"Текст страницы\"", 2, '?mode=admin&do=statics&op=add'); }

			$text_bb				= $this->db->HSC($text_bb);

			$text_html				= $bb->decode($text_bb);

			$safe_text_html			= $this->db->safesql($text_html); // in base
			$text_bb				= $this->db->safesql($text_bb); // in base

			$text_html_strip		= trim(strip_tags($text_html, "<img>"));

			if(empty($text_html_strip)){ $this->core->notify($this->lng["e_msg"], "Не верно заполнено поле \"Текст страницы\"", 2, '?mode=admin&do=statics&op=add'); }
			// Обработка описания -

			$new_data = array(
				"time_create" => $data->time_create,
				"time_last" => time(),
				"login_create" => $data->login_create,
				"login_last" => $this->user->login
			);

			$new_data = $this->db->safesql(json_encode($new_data));

			$update = $this->db->query("UPDATE `mcr_statics`
										SET `uniq`='$uniq', title='$title', text_bb='$text_bb', text_html='$safe_text_html',
											`permissions`='$permissions', `data`='$new_data'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=statics&op=edit&id='.$id); }
			
			$this->core->notify($this->lng["e_success"], "Статическая страница успешно изменена", 3, '?mode=admin&do=statics');
		}

		$data = array(
			"PAGE" => "Редактирование статической страницы",
			"TITLE" => $this->db->HSC($ar['title']),
			"UNIQ" => $this->db->HSC($ar['uniq']),
			"TEXT" => $this->db->HSC($ar['text_bb']),
			"PERMISSIONS" => $this->core->perm_list($ar['permissions']),
			"BB_PANEL" => $bb->bb_panel('stc-field'),
			"BUTTON" => "Сохранить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/statics/static-add.html", $data);

		return ob_get_clean();
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;

			default:		$content = $this->static_list(); break;
		}

		ob_start();

		echo $content;

		return ob_get_clean();
	}
}

?>