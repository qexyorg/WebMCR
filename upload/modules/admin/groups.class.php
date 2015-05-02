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

		$this->core->title = $this->lng['t_admin'].' — Группы пользователей';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Группы пользователей' => BASE_URL."?mode=admin&do=groups"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function group_array(){

		$start		= $this->core->pagination($this->config->pagin['adm_groups'], 0, 0); // Set start pagination
		$end		= $this->config->pagin['adm_groups']; // Set end pagination

		$query = $this->db->query("SELECT id, title, description
									FROM `mcr_groups`
									ORDER BY id DESC
									LIMIT $start, $end");

		ob_start();

		if(!$query || $this->db->num_rows($query)<=0){
			echo $this->core->sp(MCR_THEME_MOD."admin/groups/group-none.html");
			return ob_get_clean();
		}

		while($ar = $this->db->fetch_assoc($query)){

			$page_data = array(
				"ID" => intval($ar['id']),
				"TITLE" => $this->db->HSC($ar['title']),
				"TEXT" => $this->db->HSC($ar['description']),
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/groups/group-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function group_list(){

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_groups`");

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->config->pagin['adm_groups'], "?mode=admin&do=groups&pid=", $ar[0]),
			"GROUPS" => $this->group_array()
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/groups/group-list.html", $data);

		return ob_get_clean();
	}

	private function delete(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->lng["e_msg"], $this->lng['e_hack'], 2, '?mode=admin&do=groups'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->lng["e_msg"], "Не выбрано ни одного пункта", 2, '?mode=admin&do=groups'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		$delete = $this->db->query("DELETE FROM `mcr_groups` WHERE id IN ($list)");

		if(!$delete){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=groups'); }

		$count = $this->db->affected_rows();

		$delete1 = $this->db->query("DELETE FROM `mcr_users` WHERE gid IN ($list)");

		if(!$delete1){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=groups'); }

		$count1 = $this->db->affected_rows();

		$this->core->notify($this->lng["e_success"], "Удалено элементов: групп - $count, пользователей - $count1", 3, '?mode=admin&do=groups');

	}

	private function get_default_value($name='false', $value, $type='boolean'){
		switch($type){
			case 'integer':
				$value = intval($value);
				$input = '<input type="text" class="span8" name="'.$name.'" value="'.$value.'" id="inputDefault" placeholder="Значение по умолчанию">';
			break;

			case 'float':
				$value = floatval($value);
				$input = '<input type="text" class="span8" name="'.$name.'" value="'.$value.'" id="inputDefault" placeholder="Значение по умолчанию">';
			break;

			case 'string':
				$value = $this->db->HSC($value);
				$input = '<input type="text" class="span8" name="'.$name.'" value="'.$value.'" id="inputDefault" placeholder="Значение по умолчанию">';
			break;

			default:
				$select = ($value=='true') ? 'selected' : '';
				$input = '<select name="'.$name.'" class="span8"><option value="false">FALSE</option><option value="true" '.$select.'>TRUE</option></select>';
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

			$data['DEFAULT'] = $this->get_default_value($ar['value'], $json[$ar['value']], $ar['type']);

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

		$this->core->title .= ' — Добавление';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Группы пользователей' => BASE_URL."?mode=admin&do=groups",
			'Добавление' => BASE_URL."?mode=admin&do=groups&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title			= $this->db->safesql(@$_POST['title']);
			$text			= $this->db->safesql(@$_POST['text']);
			$permissions	= $this->db->safesql(@$_POST['permissions']);

			$perm_data = $_POST;

			unset($perm_data['submit'], $perm_data['mcr_secure'], $perm_data['title'], $perm_data['text']);

			$new_permissions = $this->db->safesql($this->gen_permissions($perm_data));

			$insert = $this->db->query("INSERT INTO `mcr_groups`
											(title, description, `permissions`)
										VALUES
											('$title', '$text', '$new_permissions')");

			if(!$insert){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=groups'); }
			
			$this->core->notify($this->lng["e_success"], "Группа пользователей успешно добавлена", 3, '?mode=admin&do=groups');
		}

		$data = array(
			"PAGE" => "Добавление группы",
			"TITLE" => '',
			"TEXT" => '',
			"PERMISSIONS" => $this->perm_list(),
			"BUTTON" => "Добавить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/groups/group-add.html", $data);

		return ob_get_clean();
	}

	private function edit(){

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT title, `description`, `permissions`
									FROM `mcr_groups`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=groups'); }

		$ar = $this->db->fetch_assoc($query);

		$this->core->title .= ' — Редактирование';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Группы пользователей' => BASE_URL."?mode=admin&do=groups",
			'Редактирование' => BASE_URL."?mode=admin&do=groups&op=edit&id=$id",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title			= $this->db->safesql(@$_POST['title']);
			$text			= $this->db->safesql(@$_POST['text']);
			$permissions	= $this->db->safesql(@$_POST['permissions']);

			$perm_data = $_POST;

			unset($perm_data['submit'], $perm_data['mcr_secure'], $perm_data['title'], $perm_data['text']);

			$new_permissions = $this->db->safesql($this->gen_permissions($perm_data));

			$update = $this->db->query("UPDATE `mcr_groups`
										SET title='$title', description='$text', `permissions`='$new_permissions'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=groups&op=edit&id='.$id); }
			
			$this->core->notify($this->lng["e_success"], "Группа пользователей успешно изменена", 3, '?mode=admin&do=groups&op=edit&id='.$id);
		}

		$data = array(
			"PAGE"			=> "Редактирование группы",
			"TITLE"			=> $this->db->HSC($ar['title']),
			"TEXT"			=> $this->db->HSC($ar['description']),
			"PERMISSIONS"	=> $this->perm_list($ar['permissions']),
			"BUTTON"		=> "Сохранить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/groups/group-add.html", $data);

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