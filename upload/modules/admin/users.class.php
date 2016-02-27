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

		if(!$this->core->is_access('sys_adm_users')){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=admin",
			$this->lng['users'] => BASE_URL."?mode=admin&do=users"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function user_array(){

		$start		= $this->core->pagination($this->config->pagin['adm_users'], 0, 0); // Set start pagination
		$end		= $this->config->pagin['adm_users']; // Set end pagination

		$query = $this->db->query("SELECT `u`.id, `u`.gid, `u`.login, `u`.email, `g`.title AS `group`, `u`.ip_create, `u`.ip_last
									FROM `mcr_users` AS `u`
									LEFT JOIN `mcr_groups` AS `g`
										ON `g`.id=`u`.gid
									ORDER BY `u`.login ASC
									LIMIT $start, $end");

		if(!$query || $this->db->num_rows($query)<=0){ return $this->core->sp(MCR_THEME_MOD."admin/users/user-none.html"); }

		ob_start();

		while($ar = $this->db->fetch_assoc($query)){

			$page_data = array(
				"ID" => intval($ar['id']),
				"GID" => intval($ar['gid']),
				"LOGIN" => $this->db->HSC($ar['login']),
				"EMAIL" => $this->db->HSC($ar['email']),
				"GROUP" => $this->db->HSC($ar['group']),
				"IP_LAST" => $this->db->HSC($ar['ip_last']),
				"IP_CREATE" => $this->db->HSC($ar['ip_create']),
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/users/user-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function user_list(){

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_users`");

		$ar = @$this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->config->pagin['adm_users'], "?mode=admin&do=users&pid=", $ar[0]),
			"USERS" => $this->user_array()
		);

		return $this->core->sp(MCR_THEME_MOD."admin/users/user-list.html", $data);
	}

	private function ban($list, $ban=1){
		if(!$this->core->is_access('sys_adm_users_ban')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=users'); }

		$update = $this->db->query("UPDATE `mcr_users` SET ban_server='$ban' WHERE id IN ($list)");

		if(!$update){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=users'); }

		$message = ($ban==1) ? $this->lng['user_ban'] : $this->lng['user_unban'];

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_ban_user']." $list ".$this->lng['log_user'], $this->user->id);

		$this->core->notify($this->core->lng["e_success"], $this->lng['user_success']." ".$message, 3, '?mode=admin&do=users');
	}

	private function get_logins($list){
		$query = $this->db->query("SELECT `login` FROM `mcr_users` WHERE id IN ($list)");

		if(!$query || $this->db->num_rows($query)<=0){ return false; }

		$logins = array();

		while($ar = $this->db->fetch_assoc($query)){ $logins[] = $ar['login']; }

		return $logins;
	}

	private function delete(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=users'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['user_not_selected'], 2, '?mode=admin&do=users'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		$logins = $this->get_logins($list);

		if($logins===false){$this->core->notify($this->core->lng["e_msg"], $this->lng['user_not_found'], 2, '?mode=admin&do=users');  }

		if(isset($_POST['ban'])){
			$this->ban($list);
		}elseif(isset($_POST['unban'])){
			$this->ban($list, 0);
		}

		if(!$this->core->is_access('sys_adm_users_delete')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=users'); }

		if(!isset($_POST['delete'])){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=users'); }

		if(!$this->db->remove_fast("mcr_users", "id IN ($list)")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=users'); }

		$count = $this->db->affected_rows();

		if(!$this->db->remove_fast("mcr_news_votes", "uid IN ($list)")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=users'); }

		$count1 = $this->db->affected_rows();

		if(!$this->db->remove_fast("mcr_news_views", "uid IN ($list)")){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=users'); }

		$count2 = $this->db->affected_rows();

		if(!$this->db->remove_fast("mcr_comments", "uid IN ($list)")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=users'); }

		$count3 = $this->db->affected_rows();

		foreach($logins as $key => $value){
			if(file_exists(MCR_SKIN_PATH.$value.'.png')){ @unlink(MCR_SKIN_PATH.$value.'.png'); }
			if(file_exists(MCR_SKIN_PATH.'interface/'.$value.'.png')){ @unlink(MCR_SKIN_PATH.'interface/'.$value.'.png'); }
			if(file_exists(MCR_SKIN_PATH.'interface/'.$value.'_mini.png')){ @unlink(MCR_SKIN_PATH.'interface/'.$value.'_mini.png'); }
			if(file_exists(MCR_CLOAK_PATH.$value.'.png')){ @unlink(MCR_CLOAK_PATH.$value.'.png'); }
		}

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_del_user']." $list ".$this->lng['log_user'], $this->user->id);

		$this->core->notify($this->core->lng["e_success"], $this->lng['user_del_elements']." $count, ".$this->lng['user_del_elements2']." $count3, ".$this->lng['user_del_elements3']." $count1, ".$this->lng['user_del_elements4']." $count2", 3, '?mode=admin&do=users');

	}

	private function exist_group($id){
		$id = intval($id);
		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_groups` WHERE id='$id'");
		if(!$query){ return false; }
		$ar = $this->db->fetch_array($query);

		if($ar[0]<=0){ return false; }

		return true;
	}

	private function add(){
		if(!$this->core->is_access('sys_adm_users_add')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=users'); }

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=admin",
			$this->lng['users'] => BASE_URL."?mode=admin&do=users",
			$this->lng['user_add'] => BASE_URL."?mode=admin&do=users&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$login			= $this->db->safesql(@$_POST['login']);
			$uuid			= $this->db->safesql($this->user->logintouuid(@$_POST['login']));

			$salt		= $this->db->safesql($this->core->random());
			$password	= $this->core->gen_password($_POST['password'], $salt);
			$password	= $this->db->safesql($password);

			if(mb_strlen($_POST['password'], "UTF-8")<6){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_e_reg_pass_length'], 2, '?mode=admin&do=users&op=add'); }

			$email			= $this->db->safesql(@$_POST['email']);

			$gid			= intval(@$_POST['gid']);

			$firstname = @$_POST['firstname'];
			$lastname = @$_POST['lastname'];
			$birthday = @$_POST['birthday'];

			$gender = (intval(@$_POST['gender'])==1) ? 1 : 0;

			if(!empty($firstname) && !preg_match("/^[a-zA-Zа-яА-ЯёЁ]+$/iu", $firstname)){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_e_incorrect_fname'], 2, '?mode=admin&do=users&op=add'); }
			if(!empty($lastname) && !preg_match("/^[a-zA-Zа-яА-ЯёЁ]+$/iu", $lastname)){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_e_incorrect_lname'], 2, '?mode=admin&do=users&op=add'); }
			if(!empty($birthday) && !preg_match("/^(\d{2}-\d{2}-\d{4})?$/", $birthday)){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_e_incorrect_bday'], 2, '?mode=admin&do=users&op=add'); }

			$birthday = intval(strtotime($birthday));

			if(!$this->exist_group($gid)){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_group_not_found'], 1, '?mode=admin&do=users&op=add'); }

			$money = floatval(@$_POST['money']);
			$realmoney = floatval(@$_POST['realmoney']);

			$new_data = array(
				"time_create" => time(),
				"time_last" => time(),
				"firstname" => $firstname,
				"lastname" => $lastname,
				"gender" => $gender,
				"birthday" => $birthday
			);

			$new_data = $this->db->safesql(json_encode($new_data));

			$insert = $this->db->query("INSERT INTO `mcr_users`
											(gid, login, email, password, `uuid`, `salt`, ip_create, ip_last, `data`)
										VALUES
											('$gid', '$login', '$email', '$password', '$uuid', '$salt', '{$this->user->ip}', '{$this->user->ip}', '$new_data')");

			if(!$insert){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=users'); }

			$id = $this->db->insert_id();
			
			$insert1 = $this->db->query("INSERT INTO `mcr_iconomy`
											(login, `money`, `realmoney`)
										VALUES
											('$login', '$money', '$realmoney')");

			if(!$insert1){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=users'); }

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_add_user']." #$id ".$this->lng['log_user'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['user_add_success'], 3, '?mode=admin&do=users');
		}

		$data = array(
			"PAGE" => $this->lng['user_add_page_name'],
			"LOGIN" => '',
			"EMAIL" => '',
			"FIRSTNAME" => '',
			"LASTNAME" => '',
			"BIRTHDAY" => date("d-m-Y"),
			"GENDER" => '',
			"GROUPS" => $this->groups(),
			"MONEY" => 0,
			"REALMONEY" => 0,
			"BUTTON" => $this->lng['user_add_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/users/user-add.html", $data);
	}

	private function edit(){
		if(!$this->core->is_access('sys_adm_users_edit')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=users'); }

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT `u`.login, `u`.gid, `u`.email, `u`.`data`,
											`i`.`money`, `i`.realmoney
									FROM `mcr_users` AS `u`
									LEFT JOIN `mcr_iconomy` AS `i`
										ON `i`.login=`u`.login
									WHERE `u`.id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=users'); }

		$ar = $this->db->fetch_assoc($query);

		$data = json_decode($ar['data']);

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=admin",
			$this->lng['users'] => BASE_URL."?mode=admin&do=users",
			$this->lng['user_edit'] => BASE_URL."?mode=admin&do=users&op=edit&id=$id",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$login			= $this->db->safesql(@$_POST['login']);
			$uuid			= $this->db->safesql($this->user->logintouuid(@$_POST['login']));

			$password		= "`password`";
			$salt			= "`salt`";

			if(isset($_POST['password']) && !empty($_POST['password'])){
				$salt		= $this->db->safesql($this->core->random());
				$salt		= "'$salt'";
				
				if(mb_strlen($_POST['password'], "UTF-8")<6){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_e_reg_pass_length'], 2, '?mode=admin&do=users&op=edit&id='.$id); }
				
				$password	= $this->core->gen_password($_POST['password'], $salt);
				$password	= $this->db->safesql($password);
				$password	= "'$password'";
			}

			$email			= $this->db->safesql(@$_POST['email']);

			$gid			= intval(@$_POST['gid']);

			$firstname = @$_POST['firstname'];
			$lastname = @$_POST['lastname'];
			$birthday = @$_POST['birthday'];

			$gender = (intval(@$_POST['gender'])==1) ? 1 : 0;

			if(!empty($firstname) && !preg_match("/^[a-zA-Zа-яА-ЯёЁ]+$/i", $firstname)){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_e_incorrect_fname'], 2, '?mode=admin&do=users&op=edit&id='.$id); }
			if(!empty($lastname) && !preg_match("/^[a-zA-Zа-яА-ЯёЁ]+$/i", $lastname)){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_e_incorrect_lname'], 2, '?mode=admin&do=users&op=edit&id='.$id); }
			if(!empty($birthday) && !preg_match("/^(\d{2}-\d{2}-\d{4})?$/", $birthday)){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_e_incorrect_bday'], 2, '?mode=admin&do=users&op=edit&id='.$id); }

			$birthday = intval(strtotime($birthday));

			if(!$this->exist_group($gid)){ $this->core->notify($this->core->lng['e_msg'], $this->lng['user_group_not_found'], 1, '?mode=admin&do=users&op=edit&id='.$id); }

			$money = floatval(@$_POST['money']);
			$realmoney = floatval(@$_POST['realmoney']);

			$new_data = array(
				"time_create" => $data->time_create,
				"time_last" => $data->time_last,
				"firstname" => $firstname,
				"lastname" => $lastname,
				"gender" => $gender,
				"birthday" => $birthday
			);

			$new_data = $this->db->safesql(json_encode($new_data));

			$update = $this->db->query("UPDATE `mcr_users`
										SET gid='$gid', login='$login', gid='$gid', email='$email',
											password=$password, `uuid`='$uuid', `salt`=$salt, `data`='$new_data'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=users&op=edit&id='.$id); }
			
			$old_login = $this->db->safesql($ar['login']);

			if(file_exists(MCR_SKIN_PATH.$old_login.'.png')){
				if(!rename(MCR_SKIN_PATH.$old_login.'.png', MCR_SKIN_PATH.$login.'.png')){
					$this->core->notify($this->lng["e_msg"], $this->lng['user_e_skin_name'], 2, '?mode=admin&do=users&op=edit&id='.$id);
				}
			}

			if(file_exists(MCR_CLOAK_PATH.$old_login.'.png')){
				if(!rename(MCR_CLOAK_PATH.$old_login.'.png', MCR_CLOAK_PATH.$login.'.png')){
					$this->core->notify($this->core->lng["e_msg"], $this->lng['user_e_cloak_name'], 2, '?mode=admin&do=users&op=edit&id='.$id);
				}
			}

			$update2 = $this->db->query("UPDATE `mcr_iconomy`
										SET login='$login', `money`='$money', `realmoney`='$realmoney'
										WHERE login='$old_login'");

			if(!$update2){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=users&op=edit&id='.$id); }

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_edit_user']." #$id ".$this->lng['log_user'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['user_edit_success'], 3, '?mode=admin&do=users&op=edit&id='.$id);
		}

		$birthday = date("d-m-Y", $data->birthday);
		$gender = (intval($data->gender)==1) ? "selected" : "";

		$data = array(
			"PAGE" => $this->lng['user_edit_page_name'],
			"LOGIN" => $this->db->HSC($ar['login']),
			"EMAIL" => $this->db->HSC($ar['email']),
			"FIRSTNAME" => $this->db->HSC($data->firstname),
			"LASTNAME" => $this->db->HSC($data->lastname),
			"BIRTHDAY" => $birthday,
			"GENDER" => $gender,
			"GROUPS" => $this->groups($ar['gid']),
			"MONEY" => floatval($ar['money']),
			"REALMONEY" => floatval($ar['realmoney']),
			"BUTTON" => $this->lng['user_edit_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/users/user-add.html", $data);
	}

	private function groups($select=1){

		$select = intval($select);

		$query = $this->db->query("SELECT id, title
									FROM `mcr_groups`
									ORDER BY title ASC");

		if(!$query || $this->db->num_rows($query)<=0){ return; }

		ob_start();

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
			case 'ban':		$this->delete(); break;

			default:		$content = $this->user_list(); break;
		}

		return $content;
	}
}

?>