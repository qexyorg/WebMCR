<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $user, $cfg, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->cfg		= $core->cfg;
		$this->lng		= $core->lng_m;

		if(!$this->core->is_access('sys_adm_manager')){ $this->core->js_notify($this->core->lng['e_403']); }
	}

	private function get_files(){
		$limit = 10;
		$page = intval(@$_POST['page']);
		if($page<=0){ $this->core->js_notify($this->lng['fm_file_not_found']); }
		$page = $page*$limit-$limit;

		$ctables	= $this->cfg->db['tables'];
		$logs_f		= $ctables['logs']['fields'];

		$query = $this->db->query("SELECT `f`.id, `f`.`uniq`, `f`.`name`, `f`.`oldname`, `f`.`data`, `f`.uid, `u`.`{$logs_f['uid']}`
								FROM `mcr_files` AS `f`
								LEFT JOIN `{$this->cfg->tabname('users')}` AS `u`
									ON `u`.`{$logs_f['id']}`=`f`.uid
								ORDER BY `f`.id DESC
								LIMIT $page, $limit");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->js_notify($this->lng['fm_file_not_found']); }

		$list = array();

		while($ar = $this->db->fetch_assoc($query)){
			$uniq = $this->db->HSC($ar['uniq']);

			$data = json_decode($ar['data'], true);

			$oldname = $ar['oldname'];

			if(mb_strlen($oldname, "UTF-8")>22){
				$oldname = mb_substr($ar['oldname'], 0, 10, "UTF-8").'...';
				$oldname .= mb_substr($ar['oldname'], -9, mb_strlen($ar['oldname'], "UTF-8"), "UTF-8");
			}

			$list[] = array(
				'link'		=> BASE_URL.'?mode=file&uniq='.$uniq,
				'uid'		=> intval($ar['uid']),
				'login'		=> $this->db->HSC($ar[$logs_f['login']]),
				'oldname'	=> $this->db->HSC($oldname),
				'date'		=> date('d.m.Y в H:i', $data['date_upload']),
				'size'		=> floatval($data['size']),
				'uniq'		=> $uniq,
				'downloads'	=> intval($data['downloads']),
			);
		}

		$this->core->js_notify($this->lng['fm_success_load'], $this->core->lng['e_success'], true, $list);
	}

	private function upload(){
		$files = @$_FILES;

		if(empty($files)){ $this->core->js_notify($this->lng['fm_not_selected']); }

		$line = '';

		$result = $errors = array();

		foreach($files as $key => $file){

			switch($file['error']){
				case 0: break;
				case 1:
				case 2: $errors[] = $this->lng['fm_e_size_limit']; break;
				case 3:
				case 4: $errors[] = $this->lng['fm_e_load']; break;
				case 6: $errors[] = $this->lng['fm_e_temp_file']; break;
				case 7: $errors[] = $this->lng['fm_e_perm']; break;
				default: $errors[] = $this->lng['fm_e_unknow']; break;
			}

			if($file['error']!=0){ continue; }

			if(!file_exists($file['tmp_name'])){ $errors[] = $this->lng['fm_e_temp_file']; }


			$oldname = $file['name'];
			$ext = '.'.substr(strrchr($oldname, '.'), 1);


			$uniq = $this->core->random(12);
			$name = md5($this->core->random(12, false)).$ext;

			$safe_uniq = $this->db->safesql($uniq);
			$safe_name = $this->db->safesql($name);
			$safe_oldname = $this->db->safesql($oldname);
			$data = array(
				"date_upload" => time(),
				"size" => intval($file['size']),
				"downloads" => 0,
			);

			$safe_data = $this->db->safesql(json_encode($data));

			$line .= "('$safe_uniq', '$safe_name', '$safe_oldname', '{$this->user->id}', '$safe_data'),";

			if(!move_uploaded_file($file['tmp_name'], MCR_UPL_PATH.'files/'.$name)){ $errors[] = $this->lng['fm_e_not_loaded']; }

			if(mb_strlen($oldname, "UTF-8")>22){
				$oldname = mb_substr($file['name'], 0, 10, "UTF-8").'...';
				$oldname .= mb_substr($file['name'], -9, mb_strlen($file['name'], "UTF-8"), "UTF-8");
			}

			$result[] = array(
				'link'		=> BASE_URL.'?mode=file&uniq='.$this->db->HSC($uniq),
				'uid'		=> intval($this->user->id),
				'login'		=> $this->db->HSC($this->user->login),
				'date'		=> date('d.m.Y в H:i', time()),
				'size'		=> intval($file['size']),
				'uniq'		=> $this->db->HSC($uniq),
				'oldname'	=> $this->db->HSC($oldname),
				'downloads'	=> 0,
			);
		}

		if(empty($line)){ $this->core->js_notify($this->lng['fm_e_no_one']); }

		$line = mb_substr($line, 0, -1, "UTF-8");

		$insert = $this->db->query("INSERT INTO `mcr_files`
										(`uniq`, `name`, `oldname`, `uid`, `data`)
									VALUES
										$line");

		if(!$insert){ $this->core->js_notify($this->core->lng["e_sql_critical"]); }

		$array = array(
			"data" => $result,
			"errors" => $errors
		);

		$this->core->js_notify($this->lng['fm_success_upload'], $this->core->lng['e_success'], true, $array);
	}

	private function remove(){
		$uniq = $this->db->safesql(@$_POST['id']);

		$query = $this->db->query("SELECT id, `name` FROM `mcr_files` WHERE `uniq`='$uniq'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->js_notify($this->lng['fm_e_not_found']); }

		$ar = $this->db->fetch_assoc($query);

		$id = intval($ar['id']);
		$name = $ar['name'];

		if(!$this->db->remove_fast("mcr_files", "id='$id'")){ $this->core->js_notify($this->lng["e_sql_critical"]); }

		if($this->db->affected_rows()<=0){ $this->core->js_notify($this->lng['fm_e_not_deleted']); }

		if(file_exists(MCR_UPL_PATH.'files/'.$name)){ unlink(MCR_UPL_PATH.'files/'.$name); }

		$this->core->js_notify($this->lng['fm_success_del'], $this->core->lng['e_success'], true);
	}

	private function edit(){
		$uniq = $this->db->safesql(@$_POST['id']);
		$value = $this->db->safesql(trim(@$_POST['val']));

		if(empty($value)){ $this->core->js_notify($this->lng['fm_e_uniq']); }

		$query = $this->db->query("SELECT id FROM `mcr_files` WHERE `uniq`='$uniq'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->js_notify($this->lng['fm_e_not_found']); }

		$ar = $this->db->fetch_assoc($query);

		$id = intval($ar['id']);

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_files` WHERE `uniq`='$value' AND id!='$id'");

		if(!$query){ $this->core->js_notify($this->core->lng["e_sql_critical"]); }

		$ar = $this->db->fetch_array($query);

		if($ar[0]>0){ $this->core->js_notify($this->lng['fm_e_uniq_exist']); }

		$update = $this->db->query("UPDATE `mcr_files` SET `uniq`='$value' WHERE id='$id'");

		if(!$update){ $this->core->js_notify($this->core->lng["e_sql_critical"]); }

		$data = array(
			'link' => BASE_URL.'?mode=file&uniq='.$this->db->HSC(@$_POST['val']),
			'uniq' => $this->db->HSC(@$_POST['val']),
		);

		$this->core->js_notify($this->lng['fm_success_edit'], $this->core->lng['e_success'], true, $data);
	}

	public function content(){

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->js_notify($this->lng['e_method']); }

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'main';

		switch($op){
			case 'upload': $this->upload(); break;
			case 'remove': $this->remove(); break;
			case 'edit': $this->edit(); break;

			default: $this->get_files(); break;
		}
	}

}

?>