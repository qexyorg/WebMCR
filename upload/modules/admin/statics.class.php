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

		if(!$this->core->is_access('sys_adm_statics')){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=admin",
			$this->lng['statics'] => BASE_URL."?mode=admin&do=statics"
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

		

		if(!$query || $this->db->num_rows($query)<=0){ return $this->core->sp(MCR_THEME_MOD."admin/statics/static-none.html"); }

		ob_start();

		while($ar = $this->db->fetch_assoc($query)){

			$perm = (is_null($ar['perm'])) ? $this->lng['stc_perm_not_exist'] : $this->db->HSC($ar['perm']);

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

		$ar = @$this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->config->pagin['adm_statics'], "?mode=admin&do=statics&pid=", $ar[0]),
			"STATICS" => $this->static_array()
		);

		return $this->core->sp(MCR_THEME_MOD."admin/statics/static-list.html", $data);
	}

	private function delete(){
		if(!$this->core->is_access('sys_adm_statics_delete')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=statics'); }

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=statics'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['stc_not_selected'], 2, '?mode=admin&do=statics'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		if(!$this->db->remove_fast("mcr_statics", "id IN ($list)")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=statics'); }

		$count1 = $this->db->affected_rows();

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_del_stc']." $list ".$this->lng['log_stc'], $this->user->id);

		$this->core->notify($this->core->lng["e_success"], $this->lng['stc_del_elements']." $count1", 3, '?mode=admin&do=statics');

	}

	private function add(){
		if(!$this->core->is_access('sys_adm_statics_add')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=statics'); }

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=admin",
			$this->lng['statics'] => BASE_URL."?mode=admin&do=statics",
			$this->lng['stc_add'] => BASE_URL."?mode=admin&do=statics&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);
		
		$bb = $this->core->load_bb_class(); // Загрузка класса BB-кодов

		$preview		= '';
		$title			= '';
		$uniq			= '';
		$text			= '';
		$permissions	= $this->core->perm_list();

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title = $this->db->safesql(@$_POST['title']);

			$uniq = $this->db->safesql(@$_POST['uniq']);

			$permissions = $this->db->safesql(@$_POST['permissions']);

			// Обработка описания +
			$text_bb = @$_POST['text'];

			$text_bb_trim = trim($text_bb);

			if(empty($text_bb_trim)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['stc_e_text_empty'], 2, '?mode=admin&do=statics&op=add'); }

			$text_html				= $bb->parse($text_bb);

			$safe_text_html			= $this->db->safesql($text_html); // in base
			$safe_text_bb			= $this->db->safesql($text_bb); // in base

			$text_html_strip		= trim(strip_tags($text_html, "<img>"));

			if(empty($text_html_strip)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['stc_e_text_incorrect'], 2, '?mode=admin&do=statics&op=add'); }
			// Обработка описания -

			if(isset($_POST['preview'])){
				$preview		= $this->get_preview($title, $text_html);
				$title			= $this->db->HSC($title);
				$uniq			= $this->db->HSC($uniq);
				$text			= $this->db->HSC($text_bb);
				$permissions	= $this->core->perm_list($permissions);
			}else{
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
												('$uniq', '$title', '$safe_text_bb', '$safe_text_html', '{$this->user->id}', '$permissions', '$new_data')");

				if(!$insert){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=statics&op=add'); }

				$id = $this->db->insert_id();

				// Последнее обновление пользователя
				$this->db->update_user($this->user);

				// Лог действия
				$this->db->actlog($this->lng['log_add_stc']." #$id ".$this->lng['log_stc'], $this->user->id);
				
				$this->core->notify($this->core->lng["e_success"], $this->lng['stc_add_success'], 3, '?mode=admin&do=statics');
			}
		}

		$data = array(
			"PAGE" => $this->lng['stc_add_page_name'],
			"TITLE" => $title,
			"UNIQ" => $uniq,
			"TEXT" => $text,
			"PERMISSIONS" => $permissions,
			"BB_PANEL" => $bb->bb_panel('stc-field'),
			"BUTTON" => $this->core->lng['add'],
			"PREVIEW" => $preview,
		);

		return $this->core->sp(MCR_THEME_MOD."admin/statics/static-add.html", $data);
	}

	private function get_preview($title='', $text=''){
		$data = array(
			"TITLE" => $this->db->HSC($title),
			"TEXT" => $text
		);

		return $this->core->sp(MCR_THEME_MOD."admin/statics/static-preview.html", $data);
	}

	private function edit(){
		if(!$this->core->is_access('sys_adm_statics_edit')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=statics'); }

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT `uniq`, title, text_bb, `permissions`, `data`
									FROM `mcr_statics`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=statics'); }

		$ar = $this->db->fetch_assoc($query);

		$preview		= '';
		$title			= $this->db->HSC($ar['title']);
		$uniq			= $this->db->HSC($ar['uniq']);
		$text			= $this->db->HSC($ar['text_bb']);
		$permissions	= $this->core->perm_list($ar['permissions']);

		$data = json_decode($ar['data']);

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=admin",
			$this->lng['statics'] => BASE_URL."?mode=admin&do=statics",
			$this->lng['stc_edit'] => BASE_URL."?mode=admin&do=statics&op=edit&id=$id"
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

			if(empty($text_bb_trim)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['stc_e_text_empty'], 2, '?mode=admin&do=statics&op=add'); }

			$text_html				= $bb->parse($text_bb);

			$safe_text_html			= $this->db->safesql($text_html); // in base
			$safe_text_bb			= $this->db->safesql($text_bb); // in base

			$text_html_strip		= trim(strip_tags($text_html, "<img>"));

			if(empty($text_html_strip)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['stc_e_text_incorrect'], 2, '?mode=admin&do=statics&op=add'); }
			// Обработка описания -

			if(isset($_POST['preview'])){
				$preview		= $this->get_preview($title, $text_html);
				$title			= $this->db->HSC($title);
				$uniq			= $this->db->HSC($uniq);
				$text			= $this->db->HSC($text_bb);
				$permissions	= $this->core->perm_list($permissions);
			}else{
				$new_data = array(
					"time_create" => $data->time_create,
					"time_last" => time(),
					"login_create" => $data->login_create,
					"login_last" => $this->user->login
				);

				$new_data = $this->db->safesql(json_encode($new_data));

				$update = $this->db->query("UPDATE `mcr_statics`
											SET `uniq`='$uniq', title='$title', text_bb='$safe_text_bb', text_html='$safe_text_html',
												`permissions`='$permissions', `data`='$new_data'
											WHERE id='$id'");

				if(!$update){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=statics&op=edit&id='.$id); }

				// Последнее обновление пользователя
				$this->db->update_user($this->user);

				// Лог действия
				$this->db->actlog($this->lng['log_edit_stc']." #$id ".$this->lng['log_stc'], $this->user->id);
				
				$this->core->notify($this->core->lng["e_success"], $this->lng['stc_edit_success'], 3, '?mode=admin&do=statics');
			}
		}

		$data = array(
			"PAGE" => $this->lng['stc_edit_page_name'],
			"TITLE" => $title,
			"UNIQ" => $uniq,
			"TEXT" => $text,
			"PERMISSIONS" => $permissions,
			"BB_PANEL" => $bb->bb_panel('stc-field'),
			"BUTTON" => $this->core->lng['save'],
			"PREVIEW" => $preview,
		);

		return $this->core->sp(MCR_THEME_MOD."admin/statics/static-add.html", $data);
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;

			default:		$content = $this->static_list(); break;
		}

		return $content;
	}
}

?>