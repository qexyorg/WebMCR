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

		if(!$this->core->is_access('sys_adm_groups')){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['groups'] => ADMIN_URL."&do=groups"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$this->core->header .= $this->core->sp(MCR_THEME_MOD."admin/groups/header.html");
	}

	private function group_array(){

		$start		= $this->core->pagination($this->cfg->pagin['adm_groups'], 0, 0); // Set start pagination
		$end		= $this->cfg->pagin['adm_groups']; // Set end pagination

		$ctables	= $this->cfg->db['tables'];

		$ug_f	= $ctables['ugroups']['fields'];

		$where		= "";
		$sort		= "id";
		$sortby		= "DESC";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$where = "WHERE `{$ug_f['title']}` LIKE '%$search%'";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$expl = explode(' ', $_GET['sort']);

			$sortby = ($expl[0]=='asc') ? "ASC" : "DESC";

			switch(@$expl[1]){
				case 'title': $sort = "`{$ug_f['title']}`"; break;
				case 'desc': $sort = "`{$ug_f['text']}`"; break;
			}
		}

		$query = $this->db->query("SELECT `{$ug_f['id']}`, `{$ug_f['title']}`, `{$ug_f['color']}`, `{$ug_f['text']}`
									FROM `{$this->cfg->tabname('ugroups')}`
									$where
									ORDER BY $sort $sortby
									LIMIT $start, $end");

		if(!$query || $this->db->num_rows($query)<=0){ return $this->core->sp(MCR_THEME_MOD."admin/groups/group-none.html"); }

		ob_start();

		while($ar = $this->db->fetch_assoc($query)){

			$color = $this->db->HSC($ar[$ug_f['color']]);

			$page_data = array(
				"ID" => intval($ar[$ug_f['id']]),
				"TITLE" => $this->core->colorize($this->db->HSC($ar[$ug_f['title']]), $color),
				"TEXT" => $this->db->HSC($ar[$ug_f['text']]),
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/groups/group-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function group_list(){

		$ctables	= $this->cfg->db['tables'];
		$ug_f		= $ctables['ugroups']['fields'];

		$sql = "SELECT COUNT(*) FROM `{$this->cfg->tabname('ugroups')}`";
		$page = "?mode=admin&do=groups";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$sql = "SELECT COUNT(*) FROM `{$this->cfg->tabname('ugroups')}` WHERE `{$ug_f['title']}` LIKE '%$search%'";
			$search = $this->db->HSC(urldecode($_GET['search']));
			$page = "?mode=admin&do=groups&search=$search";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$page .= '&sort='.$this->db->HSC(urlencode($_GET['sort']));
		}

		$query = $this->db->query($sql);

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->cfg->pagin['adm_groups'], $page.'&pid=', $ar[0]),
			"GROUPS" => $this->group_array()
		);

		return $this->core->sp(MCR_THEME_MOD."admin/groups/group-list.html", $data);
	}

	private function delete(){
		if(!$this->core->is_access('sys_adm_groups_delete')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=groups'); }

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=groups'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['grp_not_selected'], 2, '?mode=admin&do=groups'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		$ctables	= $this->cfg->db['tables'];
		$ug_f		= $ctables['ugroups']['fields'];

		if(!$this->db->remove_fast($this->cfg->tabname('ugroups'), "`{$ug_f['id']}` IN ($list)")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=groups'); }

		$count = $this->db->affected_rows();

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_del_grp']." $list ".$this->lng['log_grp'], $this->user->id);

		$this->core->notify($this->core->lng["e_success"], $this->lng['grp_del_msg1']." $count", 3, '?mode=admin&do=groups');

	}

	private function get_default_value($name='false', $value, $type='boolean'){
		$data = array(
			'NAME' => $name,
			'VALUE' => ''
		);

		switch($type){
			case 'integer':
				$data['VALUE'] = intval($value);
				$input = $this->core->sp(MCR_THEME_MOD."admin/groups/perm-id-integer.html", $data);
			break;

			case 'float':
				$data['VALUE'] = floatval($value);
				$input = $this->core->sp(MCR_THEME_MOD."admin/groups/perm-id-float.html", $data);
			break;

			case 'string':
				$data['VALUE'] = $this->db->HSC($value);
				$input = $this->core->sp(MCR_THEME_MOD."admin/groups/perm-id-string.html", $data);
			break;

			default:
				$data['VALUE'] = ($value=='true') ? 'selected' : '';
				$input = $this->core->sp(MCR_THEME_MOD."admin/groups/perm-id-boolean.html", $data);
			break;
		}

		return $input;
	}

	private function perm_list($perm=''){
		$query = $this->db->query("SELECT title, `value`, `default`, `type` FROM `mcr_permissions`");
		if(!$query || $this->db->num_rows($query)<=0){ return; }

		if(!empty($perm)){ $json = json_decode($perm, true); }

		ob_start();

		while($ar = $this->db->fetch_assoc($query)){
			$data["TITLE"] = $this->db->HSC($ar['title']);
			$data["VALUE"] = $this->db->HSC($ar['value']);

			$value = (!isset($json[$ar['value']])) ? $ar['default'] : $json[$ar['value']];

			$data['DEFAULT'] = @$this->get_default_value($ar['value'], $value, $ar['type']);

			echo $this->core->sp(MCR_THEME_MOD."admin/groups/perm-id.html", $data);
		}

		return ob_get_clean();
	}

	private function gen_permissions($data){
		if(empty($data)){ exit("System permissions error"); }

		foreach($data as $key => $value){
			if($value=='true' || $value=='false'){
				$data[$key] = ($value=='true') ? true : false;
			}else{
				$data[$key] = intval($value);
			}
		}

		return json_encode($data);
	}

	private function add(){
		if(!$this->core->is_access('sys_adm_groups_add')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=groups'); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['groups'] => ADMIN_URL."&do=groups",
			$this->lng['grp_add'] => ADMIN_URL."&do=groups&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$ctables	= $this->cfg->db['tables'];
		$ug_f		= $ctables['ugroups']['fields'];

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title			= $this->db->safesql(@$_POST['title']);
			$text			= $this->db->safesql(@$_POST['text']);
			$color			= $this->db->safesql(@$_POST['color']);
			$permissions	= $this->db->safesql(@$_POST['permissions']);

			if(!empty($color) && !preg_match("/^\#[a-f0-9]{6}|[a-f0-9]{3}$/i", $color)){ $this->core->notify($this->core->lng["e_msg"], $this->lng["grp_e_color_format"], 2, '?mode=admin&do=groups&op=add'); }

			$perm_data = $_POST;

			unset($perm_data['submit'], $perm_data['mcr_secure'], $perm_data['title'], $perm_data['text']);

			$new_permissions = $this->db->safesql($this->gen_permissions($perm_data));

			$insert = $this->db->query("INSERT INTO `{$this->cfg->tabname('ugroups')}`
											(`{$ug_f['title']}`, `{$ug_f['text']}`, `{$ug_f['color']}`, `{$ug_f['perm']}`)
										VALUES
											('$title', '$text', '$color', '$new_permissions')");

			if(!$insert){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=groups&op=add'); }

			$id = $this->db->insert_id();

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_add_grp']." #$id ".$this->lng['log_grp'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['grp_del_success'], 3, '?mode=admin&do=groups');
		}

		$data = array(
			"PAGE" => $this->lng['grp_add_page_name'],
			"TITLE" => '',
			"TEXT" => '',
			"COLOR" => '',
			"PERMISSIONS" => $this->perm_list(),
			"BUTTON" => $this->lng['grp_add_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/groups/group-add.html", $data);
	}

	private function edit(){
		if(!$this->core->is_access('sys_adm_groups_edit')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=groups'); }

		$id = intval($_GET['id']);

		$ctables	= $this->cfg->db['tables'];
		$ug_f		= $ctables['ugroups']['fields'];

		$query = $this->db->query("SELECT `{$ug_f['title']}`, `{$ug_f['text']}`, `{$ug_f['color']}`, `{$ug_f['perm']}`
									FROM `{$this->cfg->tabname('ugroups')}`
									WHERE `{$ug_f['id']}`='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=groups'); }

		$ar = $this->db->fetch_assoc($query);

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['groups'] => ADMIN_URL."&do=groups",
			$this->lng['grp_edit'] => ADMIN_URL."&do=groups&op=edit&id=$id",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title			= $this->db->safesql(@$_POST['title']);
			$text			= $this->db->safesql(@$_POST['text']);
			$color			= $this->db->safesql(@$_POST['color']);
			$permissions	= $this->db->safesql(@$_POST['permissions']);

			if(!empty($color) && !preg_match("/^\#[a-f0-9]{6}|[a-f0-9]{3}$/i", $color)){ $this->core->notify($this->core->lng["e_msg"], $this->lng["grp_e_color_format"], 2, '?mode=admin&do=groups&op=edit&id='.$id); }

			$perm_data = $_POST;

			unset($perm_data['submit'], $perm_data['mcr_secure'], $perm_data['title'], $perm_data['text']);

			$new_permissions = $this->db->safesql($this->gen_permissions($perm_data));

			$update = $this->db->query("UPDATE `{$this->cfg->tabname('ugroups')}`
										SET `{$ug_f['title']}`='$title', `{$ug_f['color']}`='$color', `{$ug_f['text']}`='$text', `{$ug_f['perm']}`='$new_permissions'
										WHERE `{$ug_f['id']}`='$id'");

			if(!$update){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=groups&op=edit&id='.$id); }

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_edit_grp']." #$id ".$this->lng['log_grp'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['grp_edit_success'], 3, '?mode=admin&do=groups&op=edit&id='.$id);
		}

		$data = array(
			"PAGE"			=> $this->lng['grp_edit_page_name'],
			"TITLE"			=> $this->db->HSC($ar[$ug_f['title']]),
			"COLOR"			=> $this->db->HSC($ar[$ug_f['color']]),
			"TEXT"			=> $this->db->HSC($ar[$ug_f['text']]),
			"PERMISSIONS"	=> $this->perm_list($ar[$ug_f['perm']]),
			"BUTTON"		=> $this->lng['grp_edit_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/groups/group-add.html", $data);
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