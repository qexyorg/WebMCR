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

		$this->core->title = $this->lng['t_admin'].' — Меню ПУ';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Меню ПУ' => BASE_URL."?mode=admin&do=menu_adm"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function menu_array(){

		$start		= $this->core->pagination($this->config->pagin['adm_menu_adm'], 0, 0); // Set start pagination
		$end		= $this->config->pagin['adm_menu_adm']; // Set end pagination

		$query = $this->db->query("SELECT `m`.id, `m`.gid, `m`.title, `m`.`url`, `m`.`target`, `g`.title AS `group`
									FROM `mcr_menu_adm` AS `m`
									LEFT JOIN `mcr_menu_adm_groups` AS `g`
										ON `g`.id=`m`.gid
									ORDER BY `m`.`priority` ASC
									LIMIT $start, $end");

		ob_start();

		if(!$query || $this->db->num_rows($query)<=0){
			echo $this->core->sp(MCR_THEME_MOD."admin/menu_adm/menu-none.html");
			return ob_get_clean();
		}

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

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_menu_adm`");

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->config->pagin['adm_menu_adm'], "?mode=admin&do=menu_adm&pid=", $ar[0]),
			"MENU" => $this->menu_array()
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/menu_adm/menu-list.html", $data);

		return ob_get_clean();
	}

	private function delete(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->lng["e_msg"], $this->lng['e_hack'], 2, '?mode=admin&do=menu_adm'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->lng["e_msg"], "Не выбрано ни одного пункта", 2, '?mode=admin&do=menu_adm'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		$delete = $this->db->query("DELETE FROM `mcr_menu_adm` WHERE id IN ($list)");

		if(!$delete){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=menu_adm'); }

		$count = $this->db->affected_rows();

		$this->core->notify($this->lng["e_success"], "Удалено элементов: пунктов меню - $count", 3, '?mode=admin&do=menu_adm');

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

		$this->core->title .= ' — Добавление';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Меню ПУ' => BASE_URL."?mode=admin&do=menu_adm",
			'Добавление' => BASE_URL."?mode=admin&do=menu_adm&op=add",
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

			if(!$this->core->validate_perm($permissions)){ $this->core->notify($this->lng["e_msg"], "Привилегия не существует", 2, '?mode=admin&do=menu_adm'); }

			// Check exist fields in base
			if(!$this->validate_element($gid, 'mcr_menu_adm_groups')){ $this->core->notify($this->lng["e_msg"], $this->lng["e_hack"], 2, '?mode=admin&do=menu_adm'); }
			if(!$this->validate_element($icon, 'mcr_menu_adm_icons')){ $this->core->notify($this->lng["e_msg"], $this->lng["e_hack"], 2, '?mode=admin&do=menu_adm'); }

			$insert = $this->db->query("INSERT INTO `mcr_menu_adm`
											(title, gid, `text`, `url`, `target`, `access`, `priority`, icon)
										VALUES
											('$title', '$gid', '$text', '$url', '$target', '$permissions', '$priority', '$icon')");

			if(!$insert){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=menu_adm'); }
			
			$this->core->notify($this->lng["e_success"], "Пункт меню успешно добавлен", 3, '?mode=admin&do=menu_adm');
		}

		$data = array(
			"PAGE" => "Добавление меню",
			"TITLE" => '',
			"TEXT" => '',
			"URL" => '',
			"PERMISSIONS" => $this->core->perm_list(),
			"GROUPS" => $this->groups(),
			"ICONS" => $this->icons(),
			"TARGET" => '',
			"PRIORITY" => 1,
			"BUTTON" => "Добавить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/menu_adm/menu-add.html", $data);

		return ob_get_clean();
	}

	private function edit(){

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT title, gid, `text`, `url`, `target`, `access`, `priority`, icon
									FROM `mcr_menu_adm`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=menu_adm'); }

		$ar = $this->db->fetch_assoc($query);

		$this->core->title .= ' — Редактирование';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Меню ПУ' => BASE_URL."?mode=admin&do=menu_adm",
			'Редактирование' => BASE_URL."?mode=admin&do=menu_adm&op=edit&id=$id",
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

			if(!$this->core->validate_perm($permissions)){ $this->core->notify($this->lng["e_msg"], "Привилегия не существует", 2, '?mode=admin&do=menu_adm'); }

			// Check exist fields in base
			if(!$this->validate_element($gid, 'mcr_menu_adm_groups')){ $this->core->notify($this->lng["e_msg"], $this->lng["e_hack"], 2, '?mode=admin&do=menu_adm'); }
			if(!$this->validate_element($icon, 'mcr_menu_adm_icons')){ $this->core->notify($this->lng["e_msg"], $this->lng["e_hack"], 2, '?mode=admin&do=menu_adm'); }

			$update = $this->db->query("UPDATE `mcr_menu_adm`
										SET title='$title', gid='$gid', `text`='$text', `url`='$url', `target`='$target',
											`access`='$permissions', `priority`='$priority', icon='$icon'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=menu_adm&op=edit&id='.$id); }
			
			$this->core->notify($this->lng["e_success"], "Пункт меню успешно изменен", 3, '?mode=admin&do=menu_adm&op=edit&id='.$id);
		}

		$data = array(
			"PAGE" => "Редактирование меню",
			"TITLE" => $this->db->HSC($ar['title']),
			"TEXT" => $this->db->HSC($ar['text']),
			"URL" => $this->db->HSC($ar['url']),
			"PERMISSIONS" => $this->core->perm_list($ar['access']),
			"GROUPS" => $this->groups($ar['gid']),
			"ICONS" => $this->icons($ar['icon']),
			"TARGET" => ($ar['target']=='_blank') ? 'selected' : '',
			"PRIORITY" => intval($ar['priority']),
			"BUTTON" => "Сохранить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/menu_adm/menu-add.html", $data);

		return ob_get_clean();
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
		
		echo '<option value="0" '.$selected.'>Верхний уровень</option>';

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

		ob_start();

		echo $content;

		return ob_get_clean();
	}
}

?>