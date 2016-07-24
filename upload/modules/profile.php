<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $cfg, $user, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;
		$this->user		= $core->user;
		$this->lng		= $core->lng_m;

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=profile"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function delete_skin(){
		if(!$this->user->is_skin){ $this->core->notify("", $this->lng['skin_not_set'], 1, '?mode=profile'); }

		if(file_exists(MCR_SKIN_PATH.$this->user->skin.'.png')){
			unlink(MCR_SKIN_PATH.$this->user->skin.'.png');
		}

		if(file_exists(MCR_SKIN_PATH.'interface/'.$this->user->skin.'.png')){
			unlink(MCR_SKIN_PATH.'interface/'.$this->user->skin.'.png');
		}

		if(file_exists(MCR_SKIN_PATH.'interface/'.$this->user->skin.'_mini.png')){
			unlink(MCR_SKIN_PATH.'interface/'.$this->user->skin.'_mini.png');
		}

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

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];
		
		$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}` SET `{$us_f['is_skin']}`='0' WHERE `{$us_f['id']}`='{$this->user->id}'");
		if(!$update){ $this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical']); }

		// Лог действия
		$this->db->actlog($this->lng['log_delete'], $this->user->id);

		$this->core->notify($this->core->lng['e_success'], $this->lng['skin_success_del'], 3, '?mode=profile');

	}

	private function delete_cloak(){

		if(!$this->user->is_cloak){ $this->core->notify("", $this->lng['cloak_not_set'], 1, '?mode=profile'); }

		if(file_exists(MCR_CLOAK_PATH.$this->user->login.'.png')){
			unlink(MCR_CLOAK_PATH.$this->user->login.'.png');
		}

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

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];
		
		$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}` SET `{$us_f['is_cloak']}`='0' WHERE `{$us_f['id']}`='{$this->user->id}'");
		if(!$update){ $this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical']); }

		// Лог действия
		$this->db->actlog($this->lng['log_delete_cl'], $this->user->id);

		$this->core->notify($this->core->lng['e_success'], $this->lng['cloak_success_del'], 3, '?mode=profile');

	}

	private function upload_skin(){
		require_once(MCR_TOOL_PATH.'skin.class.php');
		$skin = new skin($this->core, $_FILES['skin']); // create new skin in folder

		if($this->user->is_cloak){
			$cloak = array(
				"tmp_name" => MCR_CLOAK_PATH.$this->user->login.'.png',
				"size" => (!file_exists(MCR_CLOAK_PATH.$this->user->login.'.png')) ? 0 : filesize(MCR_CLOAK_PATH.$this->user->login.'.png'),
				"error" => 0,
				"name" => $this->user->login.'.png'
			);
			require_once(MCR_TOOL_PATH.'cloak.class.php');
			$cloak = new cloak($this->core, $cloak);
		}

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}` SET `{$us_f['is_skin']}`='1' WHERE `{$us_f['id']}`='{$this->user->id}'");

		if(!$update){ $this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical']); }

		// Лог действия
		$this->db->actlog($this->lng['log_edit_sk'], $this->user->id);

		$this->core->notify($this->core->lng['e_success'], $this->lng['skin_success_edit'], 3, '?mode=profile');
	}

	private function upload_cloak(){
		require_once(MCR_TOOL_PATH.'cloak.class.php');
		$cloak = new cloak($this->core, $_FILES['cloak']); // create new cloak in folder

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}` SET `{$us_f['is_cloak']}`='1' WHERE `{$us_f['id']}`='{$this->user->id}'");

		if(!$update){ $this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical']); }

		// Лог действия
		$this->db->actlog($this->lng['log_edit_cl'], $this->user->id);

		$this->core->notify($this->core->lng['e_success'], $this->lng['cloak_success_edit'], 3, '?mode=profile');
	}

	private function settings(){

		if(!empty($_POST['firstname']) && !preg_match("/^[a-zа-яА-ЯёЁ]+$/iu", $_POST['firstname'])){ $this->core->notify($this->core->lng['e_msg'], $this->lng['e_valid_fname'], 2, '?mode=profile'); }
		if(!empty($_POST['lastname']) && !preg_match("/^[a-zа-яА-ЯёЁ]+$/iu", $_POST['lastname'])){ $this->core->notify($this->core->lng['e_msg'], $this->lng['e_valid_lname'], 2, '?mode=profile'); }
		if(!empty($_POST['birthday']) && !preg_match("/^(\d{2}-\d{2}-\d{4})?$/", $_POST['birthday'])){ $this->core->notify($this->core->lng['e_msg'], $this->lng['e_valid_bday'], 2, '?mode=profile'); }

		$firstname = $this->db->safesql(@$_POST['firstname']);
		$lastname = $this->db->safesql(@$_POST['lastname']);
		$birthday = @$_POST['birthday'];
		
		$birthday = intval(strtotime($birthday));
		$newpass = $this->user->password;
		$newsalt = $this->user->salt;

		if(isset($_POST['newpass']) && !empty($_POST['newpass'])){
			$old_pass = @$_POST['oldpass'];
			$old_pass = $this->core->gen_password($old_pass, $this->user->salt);

			if($old_pass !== $this->user->password){ $this->core->notify($this->core->lng['e_msg'], $this->lng['e_valid_oldpass'], 2, '?mode=profile'); }

			if($_POST['newpass'] !== @$_POST['repass']){ $this->core->notify($this->core->lng['e_msg'], $this->lng['e_valid_repass'], 2, '?mode=profile'); }
			
			$newsalt = $this->db->safesql($this->core->random());
			$newpass = $this->db->safesql($this->core->gen_password($_POST['newpass'], $newsalt));
		}

		$time = time();

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$update = $this->db->query("UPDATE `{$this->cfg->tabname('users')}`
									SET `{$us_f['pass']}`='$newpass', `{$us_f['salt']}`='$newsalt', `{$us_f['ip_last']}`='{$this->user->ip}',
										`{$us_f['date_last']}`='$time', `{$us_f['fname']}`='$firstname', `{$us_f['lname']}`='$lastname',
										`{$us_f['bday']}`='$birthday'
									WHERE `{$us_f['id']}`='{$this->user->id}'");

		if(!$update){ $this->core->notify($this->core->lng['e_attention'], $this->core->lng['e_sql_critical'], 2, '?mode=profile'); }

		// Лог действия
		$this->db->actlog($this->lng['log_settings'], $this->user->id);

		$this->core->notify($this->core->lng['e_success'], $this->lng['set_success_save'], 3, '?mode=profile');
	}

	public function content(){

		if(!$this->user->is_auth){ $this->core->notify($this->core->lng['e_403'], $this->lng['auth_required'], 1, "?mode=403"); }

		if(!$this->core->is_access('sys_profile')){ $this->core->notify($this->core->lng['e_403'], $this->lng['access_by_admin'], 1, "?mode=403"); }

		$this->core->header = $this->core->sp(MCR_THEME_MOD."profile/header.html");

		if($_SERVER['REQUEST_METHOD']=='POST'){

			// Последнее обновление пользователя
			$this->db->update_user($this->user);
			
			if(isset($_POST['del-skin'])){
				if(!$this->core->is_access('sys_profile_del_skin')){ $this->core->notify($this->core->lng['e_403'], $this->lng['skin_access_by_admin'], 1, "?mode=403"); }
				$this->delete_skin();
			}elseif(isset($_POST['del-cloak'])){
				if(!$this->core->is_access('sys_profile_del_cloak')){ $this->core->notify($this->core->lng['e_403'], $this->lng['cloak_access_by_admin'], 1, "?mode=403"); }
				$this->delete_cloak();
			}elseif(isset($_FILES['skin'])){
				if(!$this->core->is_access('sys_profile_skin')){ $this->core->notify($this->core->lng['e_403'], $this->lng['skin_edit_by_admin'], 1, "?mode=403"); }
				$this->upload_skin();
			}elseif(isset($_FILES['cloak'])){
				if(!$this->core->is_access('sys_profile_cloak')){ $this->core->notify($this->core->lng['e_403'], $this->lng['cloak_edit_by_admin'], 1, "?mode=403"); }
				$this->upload_cloak();
			}elseif(isset($_POST['settings'])){
				if(!$this->core->is_access('sys_profile_settings')){ $this->core->notify($this->core->lng['e_403'], $this->lng['set_save_by_admin'], 1, "?mode=403"); }
				$this->settings();
			}else{
				$this->core->notify('', '', 3, '?mode=profile');
			}
		}

		return $this->core->sp(MCR_THEME_MOD."profile/profile.html");
	}
}

?>