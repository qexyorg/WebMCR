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

		$this->core->title = $this->lng['t_admin'].' — Привилегии';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Привилегии' => BASE_URL."?mode=admin&do=permissions"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function permissions_array(){

		$start		= $this->core->pagination($this->config->pagin['adm_groups'], 0, 0); // Set start pagination
		$end		= $this->config->pagin['adm_groups']; // Set end pagination

		$query = $this->db->query("SELECT id, title, description, `value`, `system`, `data`
									FROM `mcr_permissions`
									ORDER BY `value` ASC
									LIMIT $start, $end");

		ob_start();

		if(!$query || $this->db->num_rows($query)<=0){
			echo $this->core->sp(MCR_THEME_MOD."admin/permissions/perm-none.html");
			return ob_get_clean();
		}

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

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_permissions`");

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->config->pagin['adm_groups'], "?mode=admin&do=permissions&pid=", $ar[0]),
			"PERMISSIONS" => $this->permissions_array()
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/permissions/perm-list.html", $data);

		return ob_get_clean();
	}

	private function delete(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->lng["e_msg"], $this->lng['e_hack'], 2, '?mode=admin&do=permissions'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->lng["e_msg"], "Не выбрано ни одного пункта", 2, '?mode=admin&do=permissions'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		$delete = $this->db->query("DELETE FROM `mcr_permissions` WHERE id IN ($list) AND `system`='0'");

		if(!$delete){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=permissions'); }

		$count = $this->db->affected_rows();

		$this->core->notify($this->lng["e_success"], "Удалено элементов: привилегий - $count", 3, '?mode=admin&do=permissions');

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

		$def_perm = $this->get_permissions();

		$query = $this->db->query("SELECT id, `permissions` FROM `mcr_groups`");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"]); }

		$array = array();

		$return = true;

		while($ar = $this->db->fetch_assoc($query)){

			$group_perm = @json_decode($ar['permissions'], true);

			$id = intval($ar['id']);

			$new_perm = array();

			foreach($def_perm as $key => $perm){
				$new_perm[$perm['value']] = $this->switch_loop($group_perm, $perm);
			}

			$new_perm = $this->db->safesql(json_encode($new_perm));

			$update = $this->db->obj->query("UPDATE `mcr_groups`
										SET `permissions`='$new_perm'
										WHERE id='$id'");
			if(!$update){ $return = false; }
		}

		return $return;
	}

	private function get_permissions(){
		$query = $this->db->query("SELECT `value`, `type`, `default` FROM `mcr_permissions`");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"]); }
		
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

		$this->core->title .= ' — Добавление';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Привилегии' => BASE_URL."?mode=admin&do=permissions",
			'Добавление' => BASE_URL."?mode=admin&do=permissions&op=add",
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

			if(!$insert){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=permissions'); }
			
			if(!$this->update_groups()){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"].' #2', 2, '?mode=admin&do=permissions'); }

			$this->core->notify($this->lng["e_success"], "Привилегия успешно добавлена", 3, '?mode=admin&do=permissions');
		}

		$data = array(
			"PAGE" => "Добавление привилегий",
			"TITLE" => '',
			"TEXT" => '',
			"VALUE" => '',
			"DEFAULT" => $this->get_default_value(),
			"TYPES" => $this->get_types(),
			"BUTTON" => "Добавить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/permissions/perm-add.html", $data);

		return ob_get_clean();
	}

	private function edit(){

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT title, description, `value`, `system`, `default`, `type`, `data`
									FROM `mcr_permissions`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=permissions'); }

		$ar = $this->db->fetch_assoc($query);

		$this->core->title .= ' — Редактирование';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Привилегии' => BASE_URL."?mode=admin&do=permissions",
			'Редактирование' => BASE_URL."?mode=admin&do=permissions&op=edit&id=$id",
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
				$this->core->notify($this->lng["e_msg"], "Запрещено менять тип и значение системных привилегий", 2, '?mode=admin&do=permissions&op=edit&id='.$id);
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

			if(!$update){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=permissions&op=edit&id='.$id); }
			
			if(!$this->update_groups()){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"].' #2', 2, '?mode=admin&do=permissions&op=edit&id='.$id); }

			$this->core->notify($this->lng["e_success"], "Привилегия успешно изменена", 3, '?mode=admin&do=permissions&op=edit&id='.$id);
		}

		$data = array(
			"PAGE"			=> "Редактирование привилегии",
			"TITLE"			=> $this->db->HSC($ar['title']),
			"TEXT"			=> $this->db->HSC($ar['description']),
			"VALUE"			=> $this->db->HSC($ar['value']),
			"DEFAULT"		=> $this->get_default_value($ar['default'], $ar['type']),
			"TYPES"			=> $this->get_types($ar['type']),
			"BUTTON"		=> "Сохранить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/permissions/perm-add.html", $data);

		return ob_get_clean();
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
				$value = intval($value);
				$input = '<input type="text" class="span8" name="default" value="'.$value.'" id="inputDefault" placeholder="Значение по умолчанию">';
			break;

			case 'float':
				$value = floatval($value);
				$input = '<input type="text" class="span8" name="default" value="'.$value.'" id="inputDefault" placeholder="Значение по умолчанию">';
			break;

			case 'string':
				$value = $this->db->HSC($value);
				$input = '<input type="text" class="span8" name="default" value="'.$value.'" id="inputDefault" placeholder="Значение по умолчанию">';
			break;

			default:
				$select = ($value=='true') ? 'selected' : '';
				$input = '<select name="default" class="span8"><option value="false">FALSE</option><option value="true" '.$select.'>TRUE</option></select>';
			break;
		}

		return $input;
	}

	private function get_types($selected='boolean', $check=false){
		$array = array(
			"boolean" => "Булевое значение",
			"integer" => "Целое число",
			"float" => "Число с плавающей точкой",
			"string" => "Строка",
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

		$this->core->header .= '<script src="'.STYLE_URL.'js/admin/permissions.js"></script>';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;

			default:		$content = $this->permissions_list(); break;
		}

		ob_start();

		echo $content;

		return ob_get_clean();
	}
}

?>