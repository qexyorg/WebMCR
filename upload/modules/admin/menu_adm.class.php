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

		if(!$this->core->is_access('sys_adm_menu_adm')){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['manucp'] => ADMIN_URL."&do=menu_adm"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$this->core->header .= $this->core->sp(MCR_THEME_MOD."admin/menu_adm/header.html");
	}

	private function menu_array(){

		$start		= $this->core->pagination($this->cfg->pagin['adm_menu_adm'], 0, 0); // Set start pagination
		$end		= $this->cfg->pagin['adm_menu_adm']; // Set end pagination

		$where		= "";
		$sort		= "`m`.`id`";
		$sortby		= "DESC";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$where = "WHERE `m`.title LIKE '%$search%'";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$expl = explode(' ', $_GET['sort']);

			$sortby = ($expl[0]=='asc') ? "ASC" : "DESC";

			switch(@$expl[1]){
				case 'title': $sort = "`m`.title"; break;
				case 'group': $sort = "`g`.title"; break;
			}
		}

		$query = $this->db->query("SELECT `m`.id, `m`.gid, `m`.title, `m`.`url`, `m`.`target`, `g`.title AS `group`
									FROM `mcr_menu_adm` AS `m`
									LEFT JOIN `mcr_menu_adm_groups` AS `g`
										ON `g`.id=`m`.gid
									$where
									ORDER BY $sort $sortby
									LIMIT $start, $end");

		if(!$query || $this->db->num_rows($query)<=0){ return $this->core->sp(MCR_THEME_MOD."admin/menu_adm/menu-none.html"); }

		ob_start();

		while($ar = $this->db->fetch_assoc($query)){

			$page_data = array(
				"ID" => intval($ar['id']),
				"GID" => intval($ar['gid']),
				"TITLE" => $this->db->HSC($ar['title']),
				"URL" => $this->db->HSC($ar['url']),
				"TARGET" => $this->db->HSC($ar['target']),
				"GROUP" => $this->db->HSC($ar['group']),
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/menu_adm/menu-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function menu_list(){

		$sql = "SELECT COUNT(*) FROM `mcr_menu_adm`";
		$page = "?mode=admin&do=menu_adm";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$sql = "SELECT COUNT(*) FROM `mcr_menu_adm` WHERE title LIKE '%$search%'";
			$search = $this->db->HSC(urldecode($_GET['search']));
			$page = "?mode=admin&do=menu_adm&search=$search";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$page .= '&sort='.$this->db->HSC(urlencode($_GET['sort']));
		}

		$query = $this->db->query($sql);

		$ar = @$this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->cfg->pagin['adm_menu_adm'], $page.'&pid=', $ar[0]),
			"MENU" => $this->menu_array()
		);

		return $this->core->sp(MCR_THEME_MOD."admin/menu_adm/menu-list.html", $data);
	}

	private function delete(){
		if(!$this->core->is_access('sys_adm_menu_adm_delete')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=menu_adm'); }

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=menu_adm'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['mcp_not_selected'], 2, '?mode=admin&do=menu_adm'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		if(!$this->db->remove_fast("mcr_menu_adm", "id IN ($list)")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=menu_adm'); }

		$count = $this->db->affected_rows();

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_del_mcp']." $list ".$this->lng['log_mcp'], $this->user->id);

		$this->core->notify($this->core->lng["e_success"], $this->lng['mcp_del_elements']." $count", 3, '?mode=admin&do=menu_adm');

	}

	private function groups($selected=1){

		$selected = intval($selected);

		$query = $this->db->query("SELECT id, title FROM `mcr_menu_adm_groups`");

		if(!$query || $this->db->num_rows($query)<=0){ return; }

		ob_start();

		while($ar = $this->db->fetch_assoc($query)){
			$id = intval($ar['id']);
			$title = $this->db->HSC($ar['title']);

			$select = ($selected==$id) ? 'selected' : '';

			echo "<option value=\"$id\" $select>$title</option>";
		}
		
		return ob_get_clean();
	}

	private function icons($selected=1){

		$selected = intval($selected);

		$query = $this->db->query("SELECT id, title, img FROM `mcr_menu_adm_icons`");

		if(!$query || $this->db->num_rows($query)<=0){ return; }

		ob_start();

		$i = 0;
		while($ar = $this->db->fetch_assoc($query)){

			$data = array(
				"ID" => intval($ar['id']),
				"TITLE" => $this->db->HSC($ar['title']).$i,
				"CHECKED" => ($selected==intval($ar['id'])) ? 'checked' : '',
				"IMG" => $this->db->HSC($ar['img'])
			);

			echo $this->core->sp(MCR_THEME_MOD."admin/menu_adm/icon-id.html", $data);
			$i++;
		}
		
		return ob_get_clean();

	}

	private function validate_element($id=0, $table=''){
		$id = intval($id);

		$query = $this->db->query("SELECT COUNT(*) FROM `$table` WHERE id='$id'");

		if(!$query){ return false; }

		$ar = $this->db->fetch_array($query);

		if($ar[0]<=0){ return false; }

		return true;
	}

	private function add(){
		if(!$this->core->is_access('sys_adm_menu_adm_add')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=menu_adm'); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['manucp'] => ADMIN_URL."&do=menu_adm",
			$this->lng['mcp_add'] => ADMIN_URL."&do=menu_adm&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title			= $this->db->safesql(@$_POST['title']);
			$gid			= intval(@$_POST['gid']);
			$text			= $this->db->safesql(@$_POST['text']);
			$url			= $this->db->safesql(@$_POST['url']);
			$target			= (@$_POST['target']=="_blank") ? "_blank" : "_self";
			$permissions	= $this->db->safesql(@$_POST['permissions']);
			$priority		= intval(@$_POST['priority']);
			$icon			= intval(@$_POST['icon']);

			if(!$this->core->validate_perm($permissions)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['mcp_perm_not_exist'], 2, '?mode=admin&do=menu_adm'); }

			// Check exist fields in base
			if(!$this->validate_element($gid, 'mcr_menu_adm_groups')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_hack"], 2, '?mode=admin&do=menu_adm'); }
			if(!$this->validate_element($icon, 'mcr_menu_adm_icons')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_hack"], 2, '?mode=admin&do=menu_adm'); }

			$insert = $this->db->query("INSERT INTO `mcr_menu_adm`
											(title, gid, `text`, `url`, `target`, `access`, `priority`, icon)
										VALUES
											('$title', '$gid', '$text', '$url', '$target', '$permissions', '$priority', '$icon')");

			if(!$insert){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=menu_adm'); }

			$id = $this->db->insert_id();

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_add_mcp']." #$id ".$this->lng['log_mcp'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['mcp_add_success'], 3, '?mode=admin&do=menu_adm');
		}

		$data = array(
			"PAGE" => $this->lng['mcp_add_page_name'],
			"TITLE" => '',
			"TEXT" => '',
			"URL" => '',
			"PERMISSIONS" => $this->core->perm_list(),
			"GROUPS" => $this->groups(),
			"ICONS" => $this->icons(),
			"TARGET" => '',
			"PRIORITY" => 1,
			"BUTTON" => $this->lng['mcp_add_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/menu_adm/menu-add.html", $data);
	}

	private function edit(){
		if(!$this->core->is_access('sys_adm_menu_adm_edit')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=menu_adm'); }

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT title, gid, `text`, `url`, `target`, `access`, `priority`, icon
									FROM `mcr_menu_adm`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=menu_adm'); }

		$ar = $this->db->fetch_assoc($query);

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['manucp'] => ADMIN_URL."&do=menu_adm",
			$this->lng['mcp_edit'] => ADMIN_URL."&do=menu_adm&op=edit&id=$id",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title			= $this->db->safesql(@$_POST['title']);
			$gid			= intval(@$_POST['gid']);
			$text			= $this->db->safesql(@$_POST['text']);
			$url			= $this->db->safesql(@$_POST['url']);
			$target			= (@$_POST['target']=="_blank") ? "_blank" : "_self";
			$permissions	= $this->db->safesql(@$_POST['permissions']);
			$priority		= intval(@$_POST['priority']);
			$icon			= intval(@$_POST['icon']);

			if(!$this->core->validate_perm($permissions)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['mcp_perm_not_exist'], 2, '?mode=admin&do=menu_adm'); }

			// Check exist fields in base
			if(!$this->validate_element($gid, 'mcr_menu_adm_groups')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_hack"], 2, '?mode=admin&do=menu_adm'); }
			if(!$this->validate_element($icon, 'mcr_menu_adm_icons')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_hack"], 2, '?mode=admin&do=menu_adm'); }

			$update = $this->db->query("UPDATE `mcr_menu_adm`
										SET title='$title', gid='$gid', `text`='$text', `url`='$url', `target`='$target',
											`access`='$permissions', `priority`='$priority', icon='$icon'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=menu_adm&op=edit&id='.$id); }

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_edit_mcp']." #$id ".$this->lng['log_mcp'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['mcp_edit_success'], 3, '?mode=admin&do=menu_adm&op=edit&id='.$id);
		}

		$data = array(
			"PAGE" => $this->lng['mcp_edit_page_name'],
			"TITLE" => $this->db->HSC($ar['title']),
			"TEXT" => $this->db->HSC($ar['text']),
			"URL" => $this->db->HSC($ar['url']),
			"PERMISSIONS" => $this->core->perm_list($ar['access']),
			"GROUPS" => $this->groups($ar['gid']),
			"ICONS" => $this->icons($ar['icon']),
			"TARGET" => ($ar['target']=='_blank') ? 'selected' : '',
			"PRIORITY" => intval($ar['priority']),
			"BUTTON" => $this->lng['mcp_edit_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/menu_adm/menu-add.html", $data);
	}

	private function parents($select=0, $not=false){

		$select = intval($select);

		$not = ($not===false) ? -1 : intval($not);

		$query = $this->db->query("SELECT id, title
									FROM `mcr_menu`
									WHERE id!='$not'
									ORDER BY title ASC");

		ob_start();

		$selected = ($select===0) ? 'selected' : '';
		
		echo '<option value="0" '.$selected.'>'.$this->lng['mcp_top_lvl'].'</option>';

		if(!$query || $this->db->num_rows($query)<=0){ return ob_get_clean(); }

		while($ar = $this->db->fetch_assoc($query)){
			$id = intval($ar['id']);
			$selected = ($id == $select) ? "selected" : "";

			$title = $this->db->HSC($ar['title']);

			echo "<option value=\"$id\" $selected>$title</option>";
		}

		return ob_get_clean();
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;

			default:		$content = $this->menu_list(); break;
		}

		return $content;
	}
}

?>