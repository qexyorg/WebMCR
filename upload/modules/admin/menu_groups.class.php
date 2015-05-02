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

		$this->core->title = $this->lng['t_admin'].' — Группы меню ПУ';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Группы меню ПУ' => BASE_URL."?mode=admin&do=menu_groups"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function group_array(){

		$start		= $this->core->pagination($this->config->pagin['adm_menu_groups'], 0, 0); // Set start pagination
		$end		= $this->config->pagin['adm_menu_groups']; // Set end pagination

		$query = $this->db->query("SELECT `g`.id, `g`.title, `g`.`text`, `p`.id AS `pid`, `p`.`title` AS `perm`
									FROM `mcr_menu_adm_groups` AS `g`
									LEFT JOIN `mcr_permissions` AS `p`
										ON `p`.`value`=`g`.`access`
									ORDER BY `g`.`priority` ASC
									LIMIT $start, $end");

		ob_start();

		if(!$query || $this->db->num_rows($query)<=0){
			echo $this->core->sp(MCR_THEME_MOD."admin/menu_groups/group-none.html");
			return ob_get_clean();
		}

		while($ar = $this->db->fetch_assoc($query)){

			$page_data = array(
				"ID" => intval($ar['id']),
				"TITLE" => $this->db->HSC($ar['title']),
				"TEXT" => $this->db->HSC($ar['text']),
				"PERMISSIONS" => $this->db->HSC($ar['perm']),
				"PID" => intval($ar['pid']),
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/menu_groups/group-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function group_list(){

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_menu_adm_groups`");

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->config->pagin['adm_menu_groups'], "?mode=admin&do=menu_groups&pid=", $ar[0]),
			"GROUPS" => $this->group_array()
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/menu_groups/group-list.html", $data);

		return ob_get_clean();
	}

	private function delete(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->lng["e_msg"], $this->lng['e_hack'], 2, '?mode=admin&do=menu_groups'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->lng["e_msg"], "Не выбрано ни одного пункта", 2, '?mode=admin&do=menu_groups'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		$delete = $this->db->query("DELETE FROM `mcr_menu_adm_groups` WHERE id IN ($list)");

		if(!$delete){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=menu_groups'); }

		$count = $this->db->affected_rows();

		$delete1 = $this->db->query("DELETE FROM `mcr_menu_adm` WHERE gid IN ($list)");

		if(!$delete1){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=menu_groups'); }

		$count1 = $this->db->affected_rows();

		$this->core->notify($this->lng["e_success"], "Удалено элементов: групп меню - $count, пунктов меню - $count1", 3, '?mode=admin&do=menu_groups');

	}

	private function add(){

		$this->core->title .= ' — Добавление';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Группы меню ПУ' => BASE_URL."?mode=admin&do=menu_groups",
			'Добавление' => BASE_URL."?mode=admin&do=menu_groups&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title			= $this->db->safesql(@$_POST['title']);
			$text			= $this->db->safesql(@$_POST['text']);
			$permissions	= $this->db->safesql(@$_POST['permissions']);
			$priority		= intval(@$_POST['priority']);

			if(!$this->core->validate_perm($permissions)){ $this->core->notify($this->lng["e_msg"], "Привилегия не существует", 2, '?mode=admin&do=menu'); }

			$insert = $this->db->query("INSERT INTO `mcr_menu_adm_groups`
											(title, `text`, `access`, `priority`)
										VALUES
											('$title', '$text', '$permissions', '$priority')");

			if(!$insert){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=menu_groups'); }
			
			$this->core->notify($this->lng["e_success"], "Группа меню успешно добавлена", 3, '?mode=admin&do=menu_groups');
		}

		$data = array(
			"PAGE" => "Добавление меню",
			"TITLE" => '',
			"TEXT" => '',
			"PERMISSIONS" => $this->core->perm_list(),
			"PRIORITY" => 1,
			"BUTTON" => "Добавить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/menu_groups/group-add.html", $data);

		return ob_get_clean();
	}

	private function edit(){

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT title, `text`, `access`, `priority`
									FROM `mcr_menu_adm_groups`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=menu_groups'); }

		$ar = $this->db->fetch_assoc($query);

		$this->core->title .= ' — Редактирование';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Группы меню ПУ' => BASE_URL."?mode=admin&do=menu_groups",
			'Редактирование' => BASE_URL."?mode=admin&do=menu_groups&op=edit&id=$id",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title			= $this->db->safesql(@$_POST['title']);
			$text			= $this->db->safesql(@$_POST['text']);
			$permissions	= $this->db->safesql(@$_POST['permissions']);
			$priority		= intval(@$_POST['priority']);

			if(!$this->core->validate_perm($permissions)){ $this->core->notify($this->lng["e_msg"], "Привилегия не существует", 2, '?mode=admin&do=menu'); }

			$update = $this->db->query("UPDATE `mcr_menu_adm_groups`
										SET title='$title', `text`='$text', `access`='$permissions', `priority`='$priority'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=menu_groups&op=edit&id='.$id); }
			
			$this->core->notify($this->lng["e_success"], "Группа меню успешно изменена", 3, '?mode=admin&do=menu_groups&op=edit&id='.$id);
		}

		$data = array(
			"PAGE"			=> "Редактирование группы",
			"TITLE"			=> $this->db->HSC($ar['title']),
			"TEXT"			=> $this->db->HSC($ar['text']),
			"PERMISSIONS"	=> $this->core->perm_list($ar['access']),
			"PRIORITY"		=> intval($ar['priority']),
			"BUTTON"		=> "Сохранить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/menu_groups/group-add.html", $data);

		return ob_get_clean();
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;

			default:		$content = $this->group_list(); break;
		}

		ob_start();

		echo $content;

		return ob_get_clean();
	}
}

?>