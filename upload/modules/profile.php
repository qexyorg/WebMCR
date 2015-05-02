<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $config, $user, $lng;

	public function __construct($core){
		$this->core = $core;
		$this->db	= $core->db;
		$this->config = $core->config;
		$this->user	= $core->user;
		$this->lng	= $core->lng;

		$this->core->title = $this->lng['t_profile'];

		$bc = array(
			$this->lng['t_profile'] => BASE_URL."?mode=profile"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function delete_skin(){
		if(!$this->user->is_skin){ $this->core->notify("", "Скин не установлен", 1, '?mode=profile'); }

			unlink(MCR_SKIN_PATH.$this->user->skin.'.png');
			unlink(MCR_SKIN_PATH.'interface/'.$this->user->skin.'.png');
			unlink(MCR_SKIN_PATH.'interface/'.$this->user->skin.'_mini.png');

		if($this->user->is_cloak){
			$cloak = array(
				"tmp_name" => MCR_CLOAK_PATH.$this->user->cloak.'.png',
				"size" => filesize(MCR_CLOAK_PATH.$this->user->cloak.'.png'),
				"error" => 0,
				"name" => $this->user->cloak.'.png'
			);
			require_once(MCR_TOOL_PATH.'cloak.class.php');
			$cloak = new cloak($this->core, $cloak);
		}
		
		$update = $this->db->query("UPDATE `mcr_users` SET is_skin='0' WHERE id='{$this->user->id}'");
		if(!$update){ $this->core->notify($this->lng['e_attention'], $this->lng['e_sql_critical']); }

		$this->core->notify($this->lng['e_success'], "Ваш скин успешно удален", 3, '?mode=profile');

	}

	private function delete_cloak(){

		if(!$this->user->is_cloak){ $this->core->notify("", "Плащ не установлен", 1, '?mode=profile'); }

		unlink(MCR_CLOAK_PATH.$this->user->login.'.png');

		if(!$this->user->is_skin){
			unlink(MCR_SKIN_PATH.'interface/'.$this->user->login.'.png');
			unlink(MCR_SKIN_PATH.'interface/'.$this->user->login.'_mini.png');
		}else{
			require_once(MCR_TOOL_PATH.'skin.class.php');

			$skin = array(
				"tmp_name" => MCR_SKIN_PATH.$this->user->login.'.png',
				"size" => filesize(MCR_SKIN_PATH.$this->user->login.'.png'),
				"error" => 0,
				"name" => $this->user->login.'.png'
			);

			$skin = new skin($this->core, $skin);
		}
		
		$update = $this->db->query("UPDATE `mcr_users` SET is_cloak='0' WHERE id='{$this->user->id}'");
		if(!$update){ $this->core->notify($this->lng['e_attention'], $this->lng['e_sql_critical']); }

		$this->core->notify($this->lng['e_success'], "Ваш плащ успешно удален", 3, '?mode=profile');

	}

	private function upload_skin(){
		require_once(MCR_TOOL_PATH.'skin.class.php');
		$skin = new skin($this->core, $_FILES['skin']); // create new skin in folder

		if($this->user->is_cloak){
			$cloak = array(
				"tmp_name" => MCR_CLOAK_PATH.$this->user->login.'.png',
				"size" => filesize(MCR_CLOAK_PATH.$this->user->login.'.png'),
				"error" => 0,
				"name" => $this->user->login.'.png'
			);
			require_once(MCR_TOOL_PATH.'cloak.class.php');
			$cloak = new cloak($this->core, $cloak);
		}

		$update = $this->db->query("UPDATE `mcr_users` SET is_skin='1' WHERE id='{$this->user->id}'");

		if(!$update){ $this->core->notify($this->lng['e_attention'], $this->lng['e_sql_critical']); }

		$this->core->notify($this->lng['e_success'], "Ваш скин успешно изменен", 3, '?mode=profile');
	}

	private function upload_cloak(){
		require_once(MCR_TOOL_PATH.'cloak.class.php');
		$cloak = new cloak($this->core, $_FILES['cloak']); // create new cloak in folder

		$update = $this->db->query("UPDATE `mcr_users` SET is_cloak='1' WHERE id='{$this->user->id}'");

		if(!$update){ $this->core->notify($this->lng['e_attention'], $this->lng['e_sql_critical']); }

		$this->core->notify($this->lng['e_success'], "Ваш плащ успешно изменен", 3, '?mode=profile');
	}

	private function settings(){
		$firstname = @$_POST['firstname'];
		$lastname = @$_POST['lastname'];
		$birthday = @$_POST['birthday'];

		if(!preg_match("/^[a-zа-яА-ЯёЁ]+$/iu", $firstname)){ $this->core->notify($this->lng['e_msg'], $this->lng['e_valid_fname'], 2, '?mode=profile'); }
		if(!preg_match("/^[a-zа-яА-ЯёЁ]+$/iu", $lastname)){ $this->core->notify($this->lng['e_msg'], $this->lng['e_valid_lname'], 2, '?mode=profile'); }
		if(!preg_match("/^(\d{2}-\d{2}-\d{4})?$/", $birthday)){ $this->core->notify($this->lng['e_msg'], $this->lng['e_valid_bday'], 2, '?mode=profile'); }

		$birthday = intval(strtotime($birthday));
		$newpass = $this->user->password;
		$newsalt = $this->user->salt;

		if(isset($_POST['newpass']) && !empty($_POST['newpass'])){
			$old_pass = @$_POST['oldpass'];
			$old_pass = $this->core->gen_password($old_pass, $this->user->salt);

			if($old_pass !== $this->user->password){ $this->core->notify($this->lng['e_msg'], $this->lng['e_valid_oldpass'], 2, '?mode=profile'); }

			if($_POST['newpass'] !== @$_POST['repass']){ $this->core->notify($this->lng['e_msg'], $this->lng['e_valid_repass'], 2, '?mode=profile'); }
			
			$newsalt = $this->db->safesql($this->core->random());
			$newpass = $this->db->safesql($this->core->gen_password($_POST['newpass'], $salt));
		}

		$newdata = array(
			"time_create" => $this->user->data->time_create,
			"time_last" => time(),
			"firstname" => $this->db->safesql($firstname),
			"lastname" => $this->db->safesql($lastname),
			"gender" => $this->user->data->gender,
			"birthday" => $birthday
		);

		$newdata = $this->db->safesql(json_encode($newdata));

		$update = $this->db->query("UPDATE `mcr_users`
									SET `password`='$newpass', `salt`='$newsalt', ip_last='{$this->user->ip}', `data`='$newdata'
									WHERE id='{$this->user->id}'");

		if(!$update){ $this->core->notify($this->lng['e_attention'], $this->lng['e_sql_critical'], 2, '?mode=profile'); }

		$this->core->notify($this->lng['e_success'], "Настройки успешно сохранены", 3, '?mode=profile');
	}

	public function content(){

		if(!$this->user->is_auth){ $this->core->notify("Доступ запрещен!", "Для доступа к профилю необходима авторизация", 1, "?mode=403"); }

		if(!$this->core->is_access('sys_profile')){ $this->core->notify("Доступ запрещен!", "Доступ к профилю ограничен администрацией", 1, "?mode=403"); }

		$this->core->header = $this->core->sp(MCR_THEME_MOD."profile/header.html");
		ob_start();

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(isset($_POST['del-skin'])){
				if(!$this->core->is_access('sys_profile_del_skin')){ $this->core->notify("Доступ запрещен!", "Удаление скина ограничено администрацией", 1, "?mode=403"); }
				$this->delete_skin();
			}elseif(isset($_POST['del-cloak'])){
				if(!$this->core->is_access('sys_profile_del_cloak')){ $this->core->notify("Доступ запрещен!", "Удаление плаща ограничено администрацией", 1, "?mode=403"); }
				$this->delete_cloak();
			}elseif(isset($_FILES['skin'])){
				if(!$this->core->is_access('sys_profile_skin')){ $this->core->notify("Доступ запрещен!", "Изменение скина ограничено администрацией", 1, "?mode=403"); }
				$this->upload_skin();
			}elseif(isset($_FILES['cloak'])){
				if(!$this->core->is_access('sys_profile_cloak')){ $this->core->notify("Доступ запрещен!", "Изменение плаща ограничено администрацией", 1, "?mode=403"); }
				$this->upload_cloak();
			}elseif(isset($_POST['settings'])){
				if(!$this->core->is_access('sys_profile_settings')){ $this->core->notify("Доступ запрещен!", "Настройки профиля ограничены администрацией", 1, "?mode=403"); }
				$this->settings();
			}else{
				$this->core->notify('', '', 3, '?mode=profile');
			}
		}

		echo $this->core->sp(MCR_THEME_MOD."profile/profile.html");

		return ob_get_clean();
	}
}

?>