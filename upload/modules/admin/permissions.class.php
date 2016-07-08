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

		if(!$this->core->is_access('sys_adm_permissions')){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['permissions'] => ADMIN_URL."&do=permissions"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$this->core->header .= $this->core->sp(MCR_THEME_MOD."admin/permissions/header.html");
	}

	private function permissions_array(){

		$start		= $this->core->pagination($this->cfg->pagin['adm_groups'], 0, 0); // Set start pagination
		$end		= $this->cfg->pagin['adm_groups']; // Set end pagination

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
				case 'value': $sort = "`value`"; break;
			}
		}

		$query = $this->db->query("SELECT id, title, description, `value`, `system`, `data`
									FROM `mcr_permissions`
									$where
									ORDER BY $sort $sortby
									LIMIT $start, $end");

		if(!$query || $this->db->num_rows($query)<=0){ return $this->core->sp(MCR_THEME_MOD."admin/permissions/perm-none.html"); }

		ob_start();

		while($ar = $this->db->fetch_assoc($query)){

			$data = json_decode($ar['data'], true);

			$page_data = array(
				"ID" => intval($ar['id']),
				"TITLE" => $this->db->HSC($ar['title']),
				"TEXT" => $this->db->HSC($ar['description']),
				"VALUE" => $this->db->HSC($ar['value']),
				"SYSTEM" => (intval($ar['system'])===1) ? $this->core->sp(MCR_THEME_MOD."admin/permissions/perm-system.html") : '',
				"DATA" => $data
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/permissions/perm-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function permissions_list(){

		$sql = "SELECT COUNT(*) FROM `mcr_permissions`";
		$page = "?mode=admin&do=permissions";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$sql = "SELECT COUNT(*) FROM `mcr_permissions` WHERE title LIKE '%$search%'";
			$search = $this->db->HSC(urldecode($_GET['search']));
			$page = "?mode=admin&do=permissions&search=$search";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$page .= '&sort='.$this->db->HSC(urlencode($_GET['sort']));
		}

		$query = $this->db->query($sql);

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->cfg->pagin['adm_groups'], $page.'&pid=', $ar[0]),
			"PERMISSIONS" => $this->permissions_array()
		);

		return $this->core->sp(MCR_THEME_MOD."admin/permissions/perm-list.html", $data);
	}

	private function delete(){
		if(!$this->core->is_access('sys_adm_permissions_delete')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=permissions'); }

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=permissions'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['perm_not_selected'], 2, '?mode=admin&do=permissions'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		if(!$this->db->remove_fast("mcr_permissions", "id IN ($list) AND `system`='0'")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=permissions'); }

		$count = $this->db->affected_rows();

		@$this->user->update_default_permissions();

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_del_perm']." $list ".$this->lng['log_perm'], $this->user->id);

		$this->core->notify($this->core->lng["e_success"], $this->lng['perm_del_elements']." $count", 3, '?mode=admin&do=permissions');

	}

	private function switch_loop($group_perm, $perm){
		switch($perm['type']){
			case 'integer':
				$new_perm[$perm['value']] = (isset($group_perm[$perm['value']])) ? intval($group_perm[$perm['value']]) : intval($perm['default']);
			break;

			case 'float':
				$new_perm[$perm['value']] = (isset($group_perm[$perm['value']])) ? floatval($group_perm[$perm['value']]) : floatval($perm['default']);
			break;

			case 'string':
				$new_perm[$perm['value']] = (isset($group_perm[$perm['value']])) ? $this->db->safesql($group_perm[$perm['value']]) : $this->db->safesql($perm['default']);
			break;

			default:
				if(isset($group_perm[$perm['value']])){
					$new_perm[$perm['value']] = ($group_perm[$perm['value']]=='true' || $group_perm[$perm['value']]=='false') ? $group_perm[$perm['value']] : $perm['default'];
					$new_perm[$perm['value']] = ($new_perm[$perm['value']]=='true') ? true : false;
				}else{
					$new_perm[$perm['value']] = ($perm['default']=='true') ? true : false;
				}
			break;
		}

		return $new_perm[$perm['value']];
	}

	private function update_groups(){

		$ctables	= $this->cfg->db['tables'];

		$ug_f		= $ctables['ugroups']['fields'];

		$def_perm = $this->get_permissions();

		$query = $this->db->query("SELECT `{$ug_f['id']}`, `{$ug_f['perm']}` FROM `{$this->cfg->tabname('ugroups')}`");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"]); }

		$array = array();

		$return = true;

		while($ar = $this->db->fetch_assoc($query)){

			$group_perm = @json_decode($ar[$ug_f['perm']], true);

			$id = intval($ar[$ug_f['id']]);

			$new_perm = array();

			foreach($def_perm as $key => $perm){
				$new_perm[$perm['value']] = $this->switch_loop($group_perm, $perm);
			}

			$new_perm = $this->db->safesql(json_encode($new_perm));

			$update = $this->db->obj->query("UPDATE `{$this->cfg->tabname('ugroups')}`
										SET `{$ug_f['perm']}`='$new_perm'
										WHERE `{$ug_f['id']}`='$id'");
			if(!$update){ $return = false; }
		}

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		return $return;
	}

	private function get_permissions(){
		$query = $this->db->query("SELECT `value`, `type`, `default` FROM `mcr_permissions`");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"]); }
		
		$array = array();

		while($ar = $this->db->fetch_assoc($query)){
			
			$array[] = array(
				"type" => $ar['type'],
				"value" => $ar['value'],
				"default" => $ar['default'],
			);

		}

		return $array;

	}

	private function add(){
		if(!$this->core->is_access('sys_adm_permissions_add')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=permissions'); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL."",
			$this->lng['permissions'] => ADMIN_URL."&do=permissions",
			$this->lng['perm_add'] => ADMIN_URL."&do=permissions&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title			= $this->db->safesql(@$_POST['title']);
			$text			= $this->db->safesql(@$_POST['text']);
			$value			= $this->db->safesql(@$_POST['value']);

			$filter_type	= $this->filter_type(@$_POST['type'], @$_POST['default']);

			$default		= $filter_type['default'];
			$type			= $filter_type['type'];

			$new_data		= array(
				"time_create"	=> time(),
				"time_last"		=> time(),
				"login_create"	=> $this->user->login,
				"login_last"	=> $this->user->login,
			);

			$new_data = $this->db->safesql(json_encode($new_data));

			$insert = $this->db->query("INSERT INTO `mcr_permissions`
											(title, `description`, `value`, `default`, `type`, `data`)
										VALUES
											('$title', '$text', '$value', '$default', '$type', '$new_data')");

			if(!$insert){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=permissions'); }

			$id = $this->db->insert_id();
			
			if(!$this->update_groups()){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"].' #2', 2, '?mode=admin&do=permissions'); }

			@$this->user->update_default_permissions();

			// Лог действия
			$this->db->actlog($this->lng['log_add_perm']." #$id ".$this->lng['log_perm'], $this->user->id);

			$this->core->notify($this->core->lng["e_success"], $this->lng['perm_add_success'], 3, '?mode=admin&do=permissions');
		}

		$data = array(
			"PAGE" => $this->lng['perm_add_page_name'],
			"TITLE" => '',
			"TEXT" => '',
			"VALUE" => '',
			"DEFAULT" => $this->get_default_value(),
			"TYPES" => $this->get_types(),
			"BUTTON" => $this->lng['perm_add_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/permissions/perm-add.html", $data);
	}

	private function edit(){
		if(!$this->core->is_access('sys_adm_permissions_edit')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=permissions'); }

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT title, description, `value`, `system`, `default`, `type`, `data`
									FROM `mcr_permissions`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=permissions'); }

		$ar = $this->db->fetch_assoc($query);

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL."",
			$this->lng['permissions'] => ADMIN_URL."&do=permissions",
			$this->lng['perm_edit'] => ADMIN_URL."&do=permissions&op=edit&id=$id",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$data = json_decode($ar['data']);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title			= $this->db->safesql(@$_POST['title']);
			$text			= $this->db->safesql(@$_POST['text']);
			$value			= $this->db->safesql(@$_POST['value']);
			
			$filter_type	= $this->filter_type(@$_POST['type'], @$_POST['default']);
			$default		= $filter_type['default'];
			$type			= $filter_type['type'];

			if(intval($ar['system'])===1 && ($type!=$ar['type'] || $value!=$ar['value'])){
				$this->core->notify($this->core->lng["e_msg"], $this->lng['perm_change_system'], 2, '?mode=admin&do=permissions&op=edit&id='.$id);
			}

			$new_data		= array(
				"time_create"	=> $data->time_create,
				"time_last"		=> time(),
				"login_create"	=> $data->login_create,
				"login_last"	=> $this->user->login,
			);

			$new_data = $this->db->safesql(json_encode($new_data));

			$update = $this->db->query("UPDATE `mcr_permissions`
										SET title='$title', description='$text', `value`='$value',
											`default`='$default', `type`='$type', `data`='$new_data'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=permissions&op=edit&id='.$id); }
			
			if(!$this->update_groups()){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"].' #2', 2, '?mode=admin&do=permissions&op=edit&id='.$id); }

			@$this->user->update_default_permissions();

			// Лог действия
			$this->db->actlog($this->lng['log_edit_perm']." #$id ".$this->lng['log_perm'], $this->user->id);

			$this->core->notify($this->core->lng["e_success"], $this->lng['perm_edit_success'], 3, '?mode=admin&do=permissions&op=edit&id='.$id);
		}

		$data = array(
			"PAGE"			=> $this->lng['perm_edit_page_name'],
			"TITLE"			=> $this->db->HSC($ar['title']),
			"TEXT"			=> $this->db->HSC($ar['description']),
			"VALUE"			=> $this->db->HSC($ar['value']),
			"DEFAULT"		=> $this->get_default_value($ar['default'], $ar['type']),
			"TYPES"			=> $this->get_types($ar['type']),
			"BUTTON"		=> $this->lng['perm_edit_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/permissions/perm-add.html", $data);
	}

	private function filter_type($type='boolean', $default='false'){
		switch($type){
			case 'integer':
				$default = intval($default);
			break;

			case 'float':
				$default = floatval($default);
			break;

			case 'string':
				$default = $this->db->safesql($default);
			break;

			default:
				$type = 'boolean';
				$default = ($default=='true') ? 'true' : 'false';
				
			break;
		}

		return array("type" => $type, "default" => $default);
	}

	private function get_default_value($value='false', $type='boolean'){
		switch($type){
			case 'integer':
				$data['VALUE'] = intval($value);
				$input = $this->core->sp(MCR_THEME_MOD."admin/permissions/perm-id-integer.html", $data);
			break;

			case 'float':
				$data['VALUE'] = floatval($value);
				$input = $this->core->sp(MCR_THEME_MOD."admin/permissions/perm-id-float.html", $data);
			break;

			case 'string':
				$data['VALUE'] = $this->db->HSC($value);
				$input = $this->core->sp(MCR_THEME_MOD."admin/permissions/perm-id-string.html", $data);
			break;

			default:
				$data['VALUE'] = ($value=='true') ? 'selected' : '';
				$input = $this->core->sp(MCR_THEME_MOD."admin/permissions/perm-id-boolean.html", $data);
			break;
		}

		return $input;
	}

	private function get_types($selected='boolean', $check=false){
		$array = array(
			"boolean" => $this->lng['perm_boolean'],
			"integer" => $this->lng['perm_integer'],
			"float" => $this->lng['perm_float'],
			"string" => $this->lng['perm_string'],
		);

		if($check){ return (isset($array[$selected])) ? true : false; }

		ob_start();

		foreach($array as $value => $title){
			$select = ($selected==$value) ? 'selected' : '';

			echo "<option value=\"$value\" $select>$title</option>";
		}

		return ob_get_clean();
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		$this->core->header .= '<script src="'.LANG_URL.'js/modules/permissions.js"></script>';
		$this->core->header .= '<script src="'.STYLE_URL.'js/modules/admin/permissions.js"></script>';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;

			default:		$content = $this->permissions_list(); break;
		}

		return $content;
	}
}

?>