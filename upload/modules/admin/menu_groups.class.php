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

		if(!$this->core->is_access('sys_adm_menu_groups')){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['menugrp'] => ADMIN_URL."&do=menu_groups"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$this->core->header .= $this->core->sp(MCR_THEME_MOD."admin/menu_groups/header.html");
	}

	private function group_array(){

		$start		= $this->core->pagination($this->cfg->pagin['adm_menu_groups'], 0, 0); // Set start pagination
		$end		= $this->cfg->pagin['adm_menu_groups']; // Set end pagination

		$where		= "";
		$sort		= "`g`.`id`";
		$sortby		= "DESC";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$where = "WHERE `g`.title LIKE '%$search%'";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$expl = explode(' ', $_GET['sort']);

			$sortby = ($expl[0]=='asc') ? "ASC" : "DESC";

			switch(@$expl[1]){
				case 'title': $sort = "`g`.title"; break;
				case 'perm': $sort = "`p`.title"; break;
			}
		}

		$query = $this->db->query("SELECT `g`.id, `g`.title, `g`.`text`, `p`.id AS `pid`, `p`.`title` AS `perm`
									FROM `mcr_menu_adm_groups` AS `g`
									LEFT JOIN `mcr_permissions` AS `p`
										ON `p`.`value`=`g`.`access`
									$where
									ORDER BY $sort $sortby
									LIMIT $start, $end");

		if(!$query || $this->db->num_rows($query)<=0){ return $this->core->sp(MCR_THEME_MOD."admin/menu_groups/group-none.html"); }

		ob_start();

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

		$sql = "SELECT COUNT(*) FROM `mcr_menu_adm_groups`";
		$page = "?mode=admin&do=menu_groups";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$sql = "SELECT COUNT(*) FROM `mcr_menu_adm_groups` WHERE title LIKE '%$search%'";
			$search = $this->db->HSC(urldecode($_GET['search']));
			$page = "?mode=admin&do=menu_groups&search=$search";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$page .= '&sort='.$this->db->HSC(urlencode($_GET['sort']));
		}

		$query = $this->db->query($sql);

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->cfg->pagin['adm_menu_groups'], $page.'&pid=', $ar[0]),
			"GROUPS" => $this->group_array()
		);

		return $this->core->sp(MCR_THEME_MOD."admin/menu_groups/group-list.html", $data);
	}

	private function delete(){
		if(!$this->core->is_access('sys_adm_menu_groups_delete')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=menu_groups'); }

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=menu_groups'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['mgrp_not_selected'], 2, '?mode=admin&do=menu_groups'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		if(!$this->db->remove_fast("mcr_menu_adm_groups", "id IN ($list)")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=menu_groups'); }

		$count = $this->db->affected_rows();

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_del_mgrp']." $list ".$this->lng['log_mgrp'], $this->user->id);

		$this->core->notify($this->core->lng["e_success"], $this->lng['mgrp_del_elements']." $count", 3, '?mode=admin&do=menu_groups');

	}

	private function add(){
		if(!$this->core->is_access('sys_adm_menu_groups_add')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=menu_groups'); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['menugrp'] => ADMIN_URL."&do=menu_groups",
			$this->lng['mgrp_add'] => ADMIN_URL."&do=menu_groups&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title			= $this->db->safesql(@$_POST['title']);
			$text			= $this->db->safesql(@$_POST['text']);
			$permissions	= $this->db->safesql(@$_POST['permissions']);
			$priority		= intval(@$_POST['priority']);

			if(!$this->core->validate_perm($permissions)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['mgrp_perm_not_exist'], 2, '?mode=admin&do=menu'); }

			$insert = $this->db->query("INSERT INTO `mcr_menu_adm_groups`
											(title, `text`, `access`, `priority`)
										VALUES
											('$title', '$text', '$permissions', '$priority')");

			if(!$insert){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=menu_groups'); }

			$id = $this->db->insert_id();

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_add_mgrp']." #$id ".$this->lng['log_mgrp'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['mgrp_add_success'], 3, '?mode=admin&do=menu_groups');
		}

		$data = array(
			"PAGE" => $this->lng['mgrp_add_page_name'],
			"TITLE" => '',
			"TEXT" => '',
			"PERMISSIONS" => $this->core->perm_list(),
			"PRIORITY" => 1,
			"BUTTON" => $this->lng['mgrp_add_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/menu_groups/group-add.html", $data);
	}

	private function edit(){
		if(!$this->core->is_access('sys_adm_menu_groups_edit')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=menu_groups'); }

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT title, `text`, `access`, `priority`
									FROM `mcr_menu_adm_groups`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=menu_groups'); }

		$ar = $this->db->fetch_assoc($query);

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['menugrp'] => ADMIN_URL."&do=menu_groups",
			$this->lng['mgrp_edit'] => ADMIN_URL."&do=menu_groups&op=edit&id=$id",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title			= $this->db->safesql(@$_POST['title']);
			$text			= $this->db->safesql(@$_POST['text']);
			$permissions	= $this->db->safesql(@$_POST['permissions']);
			$priority		= intval(@$_POST['priority']);

			if(!$this->core->validate_perm($permissions)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['mgrp_perm_not_exist'], 2, '?mode=admin&do=menu'); }

			$update = $this->db->query("UPDATE `mcr_menu_adm_groups`
										SET title='$title', `text`='$text', `access`='$permissions', `priority`='$priority'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=menu_groups&op=edit&id='.$id); }

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_edit_mgrp']." #$id ".$this->lng['log_mgrp'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['mgrp_edit_success'], 3, '?mode=admin&do=menu_groups&op=edit&id='.$id);
		}

		$data = array(
			"PAGE"			=> $this->lng['mgrp_edit_page_name'],
			"TITLE"			=> $this->db->HSC($ar['title']),
			"TEXT"			=> $this->db->HSC($ar['text']),
			"PERMISSIONS"	=> $this->core->perm_list($ar['access']),
			"PRIORITY"		=> intval($ar['priority']),
			"BUTTON"		=> $this->lng['mgrp_edit_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/menu_groups/group-add.html", $data);
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;

			default:		$content = $this->group_list(); break;
		}

		return $content;
	}
}

?>