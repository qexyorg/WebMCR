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

		if(!$this->core->is_access('sys_adm_news_cats')){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']);; }

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=admin",
			$this->lng['categories'] => BASE_URL."?mode=admin&do=news_cats"
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

		

		if(!$query || $this->db->num_rows($query)<=0){ return $this->core->sp(MCR_THEME_MOD."admin/news_cats/cat-none.html"); }

		ob_start();

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

		return $this->core->sp(MCR_THEME_MOD."admin/news_cats/cat-list.html", $data);
	}

	private function delete(){
		if(!$this->core->is_access('sys_adm_news_cats_delete')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=news_cats'); }

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=news_cats'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['cn_not_selected'], 2, '?mode=admin&do=news_cats'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		if(!$this->db->remove_fast("mcr_news_cats", "id IN ($list)")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=news_cats'); }

		$count = $this->db->affected_rows();

		$query = $this->db->query("SELECT id FROM `mcr_news` WHERE id IN ($list)");

		if(!$query || $this->db->num_rows($query)<=0){
			$this->core->notify($this->core->lng["e_success"], $this->lng['cn_del_elements4']." ($count)", 3, '?mode=admin&do=news_cats');
		}

		$elem = array();

		while($ar = $this->db->fetch_assoc($query)){ $elem[] = intval($ar['id']); }

		$elements = implode(", ", $elem);

		if(!$this->db->remove_fast("mcr_news", "id IN ($elements)")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=news_cats'); }

		$count2 = $this->db->affected_rows();

		if(!$this->db->remove_fast("mcr_news_views", "nid IN ($elements)")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=news_cats'); }

		$count3 = $this->db->affected_rows();

		if(!$this->db->remove_fast("mcr_news_votes", "nid IN ($elements)")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=news_cats'); }

		$count4 = $this->db->affected_rows();

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_del_cn']." $list ".$this->lng['log_cn'], $this->user->id);

		$this->core->notify($this->core->lng["e_success"], $this->lng['cn_del_elements']." $count, ".$this->lng['cn_del_elements2']." $count2, ".$this->lng['cn_del_elements3']." $count3, ".$this->lng['cn_del_elements5']." $count4", 3, '?mode=admin&do=news_cats');

	}

	private function add(){
		if(!$this->core->is_access('sys_adm_news_cats_add')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=news_cats'); }

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=admin",
			$this->lng['categories'] => BASE_URL."?mode=admin&do=news_cats",
			$this->lng['cn_add'] => BASE_URL."?mode=admin&do=news_cats&op=add",
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
			if(!$insert){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=news_cats'); }

			$id = $this->db->insert_id();

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_add_cn']." #$id ".$this->lng['log_cn'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['cn_add_success'], 3, '?mode=admin&do=news_cats');
		}

		$data = array(
			"PAGE" => $this->lng['cn_add_page_name'],
			"TITLE" => "",
			"TEXT" => "",
			"BUTTON" => $this->lng['cn_add_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/news_cats/cat-add.html", $data);
	}

	private function edit(){
		if(!$this->core->is_access('sys_adm_news_cats_edit')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=news_cats'); }

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT title, description, `data`
									FROM `mcr_news_cats`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=news_cats'); }

		$ar = $this->db->fetch_assoc($query);

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=admin",
			$this->lng['categories'] => BASE_URL."?mode=admin&do=news_cats",
			$this->lng['cn_edit'] => BASE_URL."?mode=admin&do=news_cats&op=edit&id=$id",
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

			if(!$update){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=news_cats&op=edit&id='.$id); }

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_edit_cn']." #$id ".$this->lng['log_cn'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['cn_edit_success'], 3, '?mode=admin&do=news_cats&op=edit&id='.$id);
		}

		$data = array(
			"PAGE" => $this->lng['cn_edit_page_name'],
			"TITLE" => $this->db->HSC($ar['title']),
			"TEXT" => $this->db->HSC($ar['description']),
			"BUTTON" => $this->lng['cn_edit_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/news_cats/cat-add.html", $data);
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;

			default:		$content = $this->cats_list(); break;
		}

		return $content;
	}
}

?>