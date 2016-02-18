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

		$this->core->title = $this->lng['t_admin'].' — Пользователи';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Пользователи' => BASE_URL."?mode=admin&do=users"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function user_array(){

		$start		= $this->core->pagination($this->config->pagin['adm_users'], 0, 0); // Set start pagination
		$end		= $this->config->pagin['adm_users']; // Set end pagination

		$query = $this->db->query("SELECT `u`.id, `u`.gid, `u`.login, `u`.email, `g`.title AS `group`
									FROM `mcr_users` AS `u`
									LEFT JOIN `mcr_groups` AS `g`
										ON `g`.id=`u`.gid
									ORDER BY `u`.login ASC
									LIMIT $start, $end");

		ob_start();

		if(!$query || $this->db->num_rows($query)<=0){
			echo $this->core->sp(MCR_THEME_MOD."admin/users/user-none.html");
			return ob_get_clean();
		}

		while($ar = $this->db->fetch_assoc($query)){

			$page_data = array(
				"ID" => intval($ar['id']),
				"GID" => intval($ar['gid']),
				"LOGIN" => $this->db->HSC($ar['login']),
				"EMAIL" => $this->db->HSC($ar['email']),
				"GROUP" => $this->db->HSC($ar['group'])
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/users/user-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function user_list(){

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_users`");

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->config->pagin['adm_users'], "?mode=admin&do=users&pid=", $ar[0]),
			"USERS" => $this->user_array()
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/users/user-list.html", $data);

		return ob_get_clean();
	}

	private function ban($list, $ban=1){
		$update = $this->db->query("UPDATE `mcr_users` SET ban_server='$ban' WHERE id IN ($list)");

		if(!$update){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=users'); }

		$message = ($ban==1) ? "забанены" : "разбанены";

		$this->core->notify($this->lng["e_success"], "Выбранные пользователи успешно $message", 3, '?mode=admin&do=users');
	}

	private function delete(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->lng["e_msg"], $this->lng['e_hack'], 2, '?mode=admin&do=users'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->lng["e_msg"], "Не выбрано ни одного пункта", 2, '?mode=admin&do=users'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		if(isset($_POST['ban'])){
			$this->ban($list);
		}elseif(isset($_POST['unban'])){
			$this->ban($list, 0);
		}

		if(!isset($_POST['delete'])){ $this->core->notify($this->lng["e_msg"], $this->lng['e_hack'], 2, '?mode=admin&do=users'); }

		$delete = $this->db->query("DELETE FROM `mcr_users` WHERE id IN ($list)");

		if(!$delete){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=users'); }

		$count = $this->db->affected_rows();

		$delete1 = $this->db->query("DELETE FROM `mcr_news_votes` WHERE uid IN ($list)");

		if(!$delete1){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=users'); }

		$count1 = $this->db->affected_rows();

		$delete2 = $this->db->query("DELETE FROM `mcr_news_views` WHERE uid IN ($list)");

		if(!$delete2){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=users'); }

		$count2 = $this->db->affected_rows();

		$delete3 = $this->db->query("DELETE FROM `mcr_comments` WHERE uid IN ($list)");

		if(!$delete3){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=users'); }

		$count3 = $this->db->affected_rows();

		$this->core->notify($this->lng["e_success"], "Удалено элементов: пользователей - $count, комментариев - $count3, голосов - $count1, просмотров - $count2", 3, '?mode=admin&do=users');

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

		$this->core->title .= ' — Добавление';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Пользователи' => BASE_URL."?mode=admin&do=users",
			'Добавление' => BASE_URL."?mode=admin&do=users&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$login			= $this->db->safesql(@$_POST['login']);

			$salt		= $this->db->safesql($this->core->random());
			$password	= $this->core->gen_password($_POST['password'], $salt);
			$password	= $this->db->safesql($password);

			if(mb_strlen($_POST['password'], "UTF-8")<6){ $this->core->notify($this->lng['e_msg'], $this->lng['e_reg_pass_length'], 2, '?mode=admin&do=users&op=add'); }

			$email			= $this->db->safesql(@$_POST['email']);

			$gid			= intval(@$_POST['gid']);

			$firstname = @$_POST['firstname'];
			$lastname = @$_POST['lastname'];
			$birthday = @$_POST['birthday'];

			$gender = (intval(@$_POST['gender'])==1) ? 1 : 0;

			if(!preg_match("/^[а-яА-ЯёЁa-zA-Z]+$/ui", $firstname)){ $this->core->notify($this->lng['e_msg'], $this->lng['e_valid_fname'], 2, '?mode=admin&do=users&op=add'); }
			if(!preg_match("/^[а-яА-ЯёЁa-zA-Z]+$/ui", $lastname)){ $this->core->notify($this->lng['e_msg'], $this->lng['e_valid_lname'], 2, '?mode=admin&do=users&op=add'); }
			if(!preg_match("/^(\d{2}-\d{2}-\d{4})?$/", $birthday)){ $this->core->notify($this->lng['e_msg'], $this->lng['e_valid_bday'], 2, '?mode=admin&do=users&op=add'); }

			$birthday = intval(strtotime($birthday));

			if(!$this->exist_group($gid)){ $this->core->notify($this->lng['e_msg'], "Группа не существует", 1, '?mode=admin&do=users&op=add'); }

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
											(gid, login, email, password, `salt`, ip_create, ip_last, `data`)
										VALUES
											('$gid', '$login', '$email', '$password', '$salt', '{$this->user->ip}', '{$this->user->ip}', '$new_data')");

			if(!$insert){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=users'); }
			
			$insert1 = $this->db->query("INSERT INTO `mcr_iconomy`
											(login, `money`, `realmoney`)
										VALUES
											('$login', '$money', '$realmoney')");

			if(!$insert1){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=users'); }
			
			$this->core->notify($this->lng["e_success"], "Пункт меню успешно добавлен", 3, '?mode=admin&do=users');
		}

		$data = array(
			"PAGE" => "Добавление пользователя",
			"LOGIN" => '',
			"EMAIL" => '',
			"FIRSTNAME" => '',
			"LASTNAME" => '',
			"BIRTHDAY" => date("d-m-Y"),
			"GENDER" => '',
			"GROUPS" => $this->groups(),
			"MONEY" => 0,
			"REALMONEY" => 0,
			"BUTTON" => "Добавить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/users/user-add.html", $data);

		return ob_get_clean();
	}

	private function edit(){

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT `u`.login, `u`.gid, `u`.email, `u`.`data`,
											`i`.`money`, `i`.realmoney
									FROM `mcr_users` AS `u`
									LEFT JOIN `mcr_iconomy` AS `i`
										ON `i`.login=`u`.login
									WHERE `u`.id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=users'); }

		$ar = $this->db->fetch_assoc($query);

		$data = json_decode($ar['data']);

		$this->core->title .= ' — Редактирование';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Пользователи' => BASE_URL."?mode=admin&do=users",
			'Редактирование' => BASE_URL."?mode=admin&do=users&op=edit&id=$id",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$login			= $this->db->safesql(@$_POST['login']);

			$password		= "`password`";
			$salt			= "`salt`";

			if(isset($_POST['password']) && !empty($_POST['password'])){
				$salt		= $this->db->safesql($this->core->random());
				$salt		= "'$salt'";
				
				if(mb_strlen($_POST['password'], "UTF-8")<6){ $this->core->notify($this->lng['e_msg'], $this->lng['e_reg_pass_length'], 2, '?mode=admin&do=users&op=edit&id='.$id); }
				
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

			if(!preg_match("/^[а-яА-ЯёЁa-zA-Z]+$/ui", $firstname)){ $this->core->notify($this->lng['e_msg'], $this->lng['e_valid_fname'], 2, '?mode=admin&do=users&op=edit&id='.$id); }
			if(!preg_match("/^[а-яА-ЯёЁa-zA-Z]+$/ui", $lastname)){ $this->core->notify($this->lng['e_msg'], $this->lng['e_valid_lname'], 2, '?mode=admin&do=users&op=edit&id='.$id); }
			if(!preg_match("/^(\d{2}-\d{2}-\d{4})?$/", $birthday)){ $this->core->notify($this->lng['e_msg'], $this->lng['e_valid_bday'], 2, '?mode=admin&do=users&op=edit&id='.$id); }

			$birthday = intval(strtotime($birthday));

			if(!$this->exist_group($gid)){ $this->core->notify($this->lng['e_msg'], "Группа не существует", 1, '?mode=admin&do=users&op=edit&id='.$id); }

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
											password=$password, `salt`=$salt, `data`='$new_data'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=users&op=edit&id='.$id); }
			
			$old_login = $this->db->safesql($ar['login']);

			$update2 = $this->db->query("UPDATE `mcr_iconomy`
										SET login='$login', `money`='$money', `realmoney`='$realmoney'
										WHERE login='$old_login'");

			if(!$update2){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=users&op=edit&id='.$id); }
			
			$this->core->notify($this->lng["e_success"], "Информация о пользователе успешно изменена", 3, '?mode=admin&do=users&op=edit&id='.$id);
		}

		$birthday = date("d-m-Y", $data->birthday);
		$gender = (intval($data->gender)==1) ? "selected" : "";

		$data = array(
			"PAGE" => "Редактирование пользователя",
			"LOGIN" => $this->db->HSC($ar['login']),
			"EMAIL" => $this->db->HSC($ar['email']),
			"FIRSTNAME" => $this->db->HSC($data->firstname),
			"LASTNAME" => $this->db->HSC($data->lastname),
			"BIRTHDAY" => $birthday,
			"GENDER" => $gender,
			"GROUPS" => $this->groups($ar['gid']),
			"MONEY" => floatval($ar['money']),
			"REALMONEY" => floatval($ar['realmoney']),
			"BUTTON" => "Сохранить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/users/user-add.html", $data);

		return ob_get_clean();
	}

	private function groups($select=1){

		$select = intval($select);

		$query = $this->db->query("SELECT id, title
									FROM `mcr_groups`
									ORDER BY title ASC");

		ob_start();

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
			case 'ban':		$this->delete(); break;

			default:		$content = $this->user_list(); break;
		}

		ob_start();

		echo $content;

		return ob_get_clean();
	}
}

?>
