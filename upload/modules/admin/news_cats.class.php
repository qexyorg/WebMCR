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

		$this->core->title = $this->lng['t_admin'].' — Категории новостей';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Категории новостей' => BASE_URL."?mode=admin&do=news_cats"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function cats_array(){

		$start		= $this->core->pagination($this->config->pagin['adm_news_cats'], 0, 0); // Set start pagination
		$end		= $this->config->pagin['adm_news_cats']; // Set end pagination

		$query = $this->db->query("SELECT id, title, `data`
									FROM `mcr_news_cats`
									ORDER BY id DESC
									LIMIT $start, $end");

		ob_start();

		if(!$query || $this->db->num_rows($query)<=0){
			echo $this->core->sp(MCR_THEME_MOD."admin/news_cats/cat-none.html");
			return ob_get_clean();
		}

		while($ar = $this->db->fetch_assoc($query)){

			$page_data = array(
				"ID" => intval($ar['id']),
				"TITLE" => $this->db->HSC($ar['title']),
				"DATA" => json_decode($ar['data'])
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/news_cats/cat-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function cats_list(){

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_news_cats`");

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->config->pagin['adm_news_cats'], "?mode=admin&do=news_cats&pid=", $ar[0]),
			"CATEGORIES" => $this->cats_array()
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/news_cats/cat-list.html", $data);

		return ob_get_clean();
	}

	private function delete(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->lng["e_msg"], $this->lng['e_hack'], 2, '?mode=admin&do=news_cats'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->lng["e_msg"], "Не выбрано ни одного пункта", 2, '?mode=admin&do=news_cats'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		$delete = $this->db->query("DELETE FROM `mcr_news_cats` WHERE id IN ($list)");

		if(!$delete){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=news_cats'); }

		$count = $this->db->affected_rows();

		$query = $this->db->query("SELECT id FROM `mcr_news` WHERE id IN ($list)");

		if(!$query || $this->db->num_rows($query)<=0){
			$this->core->notify($this->lng["e_success"], "Выбранные элементы успешно удалены ($count)", 3, '?mode=admin&do=news_cats');
		}

		$elem = array();

		while($ar = $this->db->fetch_assoc($query)){ $elem[] = intval($ar['id']); }

		$elements = implode(", ", $elem);

		$delete2 = $this->db->query("DELETE FROM `mcr_news` WHERE id IN ($elements)");

		if(!$delete2){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=news_cats'); }

		$count2 = $this->db->affected_rows();

		$delete3 = $this->db->query("DELETE FROM `mcr_news_views` WHERE nid IN ($elements)");

		if(!$delete3){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=news_cats'); }

		$count3 = $this->db->affected_rows();

		$delete4 = $this->db->query("DELETE FROM `mcr_news_votes` WHERE nid IN ($elements)");

		if(!$delete4){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=news_cats'); }

		$count4 = $this->db->affected_rows();

		$this->core->notify($this->lng["e_success"], "Удалено элементов: категорий - $count, новостей - $count2, просмотров - $count3, голосов - $count4", 3, '?mode=admin&do=news_cats');

	}

	private function add(){

		$this->core->title .= ' — Добавление';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Категории новостей' => BASE_URL."?mode=admin&do=news_cats",
			'Добавление' => BASE_URL."?mode=admin&do=news_cats&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title = $this->db->safesql(@$_POST['title']);
			$text = $this->db->safesql(@$_POST['text']);

			$new_data = array(
				"time_create" => time(),
				"time_last" => time(),
				"user" => $this->user->login
			);

			$new_data = $this->db->safesql(json_encode($new_data));

			$insert = $this->db->query("INSERT INTO `mcr_news_cats`
											(title, description, `data`)
										VALUES
											('$title', '$text', '$new_data')");
			if(!$insert){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=news_cats'); }
			
			$this->core->notify($this->lng["e_success"], "Категория успешно добавлена", 3, '?mode=admin&do=news_cats');
		}

		$data = array(
			"PAGE" => "Добавление категории",
			"TITLE" => "",
			"TEXT" => "",
			"BUTTON" => "Добавить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/news_cats/cat-add.html", $data);

		return ob_get_clean();
	}

	private function edit(){

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT title, description, `data`
									FROM `mcr_news_cats`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=news_cats'); }

		$ar = $this->db->fetch_assoc($query);

		$this->core->title .= ' — Редактирование';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Категории новостей' => BASE_URL."?mode=admin&do=news_cats",
			'Редактирование' => BASE_URL."?mode=admin&do=news_cats&op=edit&id=$id",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$data = json_decode($ar['data']);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title = $this->db->safesql(@$_POST['title']);
			$text = $this->db->safesql(@$_POST['text']);

			$new_data = array(
				"time_create" => $data->time_create,
				"time_last" => time(),
				"user" => $data->user
			);

			$new_data = $this->db->safesql(json_encode($new_data));

			$update = $this->db->query("UPDATE `mcr_news_cats`
										SET title='$title', description='$text', `data`='$new_data'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=news_cats&op=edit&id='.$id); }
			
			$this->core->notify($this->lng["e_success"], "Категория успешно изменена", 3, '?mode=admin&do=news_cats&op=edit&id='.$id);
		}

		$data = array(
			"PAGE" => "Редактирование категории",
			"TITLE" => $this->db->HSC($ar['title']),
			"TEXT" => $this->db->HSC($ar['description']),
			"BUTTON" => "Сохранить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/news_cats/cat-add.html", $data);

		return ob_get_clean();
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;

			default:		$content = $this->cats_list(); break;
		}

		ob_start();

		echo $content;

		return ob_get_clean();
	}
}

?>