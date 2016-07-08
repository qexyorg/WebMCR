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

		if(!$this->core->is_access('sys_adm_menu_icons')){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['menuicons'] => ADMIN_URL."&do=menu_icons"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$this->core->header .= $this->core->sp(MCR_THEME_MOD."admin/menu_icons/header.html");
	}

	private function icon_array(){

		$start		= $this->core->pagination($this->cfg->pagin['adm_menu_icons'], 0, 0); // Set start pagination
		$end		= $this->cfg->pagin['adm_menu_icons']; // Set end pagination

		$where		= "";
		$sort		= "id";
		$sortby		= "DESC";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$where = "WHERE title LIKE '%$search%'";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$expl = explode(' ', $_GET['sort']);

			$sortby = ($expl[0]=='asc') ? "ASC" : "DESC";

			switch(@$expl[1]){
				case 'title': $sort = "title"; break;
			}
		}

		$query = $this->db->query("SELECT id, title, img
									FROM `mcr_menu_adm_icons`
									$where
									ORDER BY $sort $sortby
									LIMIT $start, $end");

		if(!$query || $this->db->num_rows($query)<=0){ return $this->core->sp(MCR_THEME_MOD."admin/menu_icons/icon-none.html"); }

		ob_start();

		while($ar = $this->db->fetch_assoc($query)){

			$page_data = array(
				"ID" => intval($ar['id']),
				"TITLE" => $this->db->HSC($ar['title']),
				"IMG" => $this->db->HSC($ar['img']),
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/menu_icons/icon-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function icon_list(){

		$sql = "SELECT COUNT(*) FROM `mcr_menu_adm_icons`";
		$page = "?mode=admin&do=menu_icons";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$sql = "SELECT COUNT(*) FROM `mcr_menu_adm_icons` WHERE title LIKE '%$search%'";
			$search = $this->db->HSC(urldecode($_GET['search']));
			$page = "?mode=admin&do=menu_icons&search=$search";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$page .= '&sort='.$this->db->HSC(urlencode($_GET['sort']));
		}

		$query = $this->db->query($sql);

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->cfg->pagin['adm_menu_icons'], $page.'&pid=', $ar[0]),
			"ICONS" => $this->icon_array()
		);

		return $this->core->sp(MCR_THEME_MOD."admin/menu_icons/icon-list.html", $data);
	}

	private function delete(){
		if(!$this->core->is_access('sys_adm_menu_icons_delete')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=menu_icons'); }

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=menu_icons'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['mi_not_selected'], 2, '?mode=admin&do=menu_icons'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		if(!$this->db->remove_fast("mcr_menu_adm_icons", "id IN ($list)")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=menu_icons'); }

		$count = $this->db->affected_rows();

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_del_mi']." $list ".$this->lng['log_mi'], $this->user->id);

		$this->core->notify($this->core->lng["e_success"], $this->lng['mi_del_elements']." $count", 3, '?mode=admin&do=menu_icons');

	}

	private function add(){
		if(!$this->core->is_access('sys_adm_menu_icons_add')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=menu_icons'); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['menuicons'] => ADMIN_URL."&do=menu_icons",
			$this->lng['mi_add'] => ADMIN_URL."&do=menu_icons&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title			= $this->db->safesql(@$_POST['title']);
			$img			= @$_POST['img'];
			$img			= (empty($img)) ? 'default.png' : $this->db->safesql($img);

			$insert = $this->db->query("INSERT INTO `mcr_menu_adm_icons`
											(title, img)
										VALUES
											('$title', '$img')");

			if(!$insert){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=menu_icons'); }

			$id = $this->db->insert_id();

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['mi_add_page_name']." #$id ".$this->lng['log_mi'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['mi_add_success'], 3, '?mode=admin&do=menu_icons');
		}

		$data = array(
			"PAGE" => $this->lng['mi_add_page_name'],
			"TITLE" => '',
			"IMG" => 'default.png',
			"BUTTON" => $this->lng['mi_add_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/menu_icons/icon-add.html", $data);
	}

	private function edit(){
		if(!$this->core->is_access('sys_adm_menu_icons_edit')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=menu_icons'); }

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT title, img
									FROM `mcr_menu_adm_icons`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=menu_icons'); }

		$ar = $this->db->fetch_assoc($query);

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['menuicons'] => ADMIN_URL."&do=menu_icons",
			$this->lng['mi_edit'] => ADMIN_URL."&do=menu_icons&op=edit&id=$id",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title			= $this->db->safesql(@$_POST['title']);
			$img			= @$_POST['img'];
			$img			= (empty($img)) ? 'default.png' : $this->db->safesql($img);

			$update = $this->db->query("UPDATE `mcr_menu_adm_icons`
										SET title='$title', img='$img'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=menu_icons&op=edit&id='.$id); }

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_edit_mi']." #$id ".$this->lng['log_mi'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['mi_edit_success'], 3, '?mode=admin&do=menu_icons&op=edit&id='.$id);
		}

		$data = array(
			"PAGE"			=> $this->lng['mi_edit_page_name'],
			"TITLE"			=> $this->db->HSC($ar['title']),
			"IMG"			=> $this->db->HSC($ar['img']),
			"BUTTON"		=> $this->lng['mi_edit_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/menu_icons/icon-add.html", $data);
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;

			default:		$content = $this->icon_list(); break;
		}

		return $content;
	}
}

?>