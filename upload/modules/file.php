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
			$this->lng['mod_name'] => BASE_URL."?mode=file"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	public function content(){

		$images = array('png', 'jpg', 'gif', 'jpeg');

		//$this->core->notify("Доступ запрещен!", "Для доступа к профилю необходима авторизация", 1, "?mode=403");

		$uniq = $this->db->safesql(@$_GET['uniq']);

		$query = $this->db->query("SELECT id, `name`, `data` FROM `mcr_files` WHERE `uniq`='$uniq'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng['404'], $this->core->lng['t_404'], 1, "?mode=404"); }

		$ar = $this->db->fetch_assoc($query);

		$id = intval($ar['id']);
		$name = $ar['name'];
		$data = json_decode($ar['data'], true);

		if(!file_exists(MCR_UPL_PATH.'files/'.$name)){ $this->core->notify($this->core->lng['404'], $this->core->lng['t_404'], 1, "?mode=404"); }

		$ext = substr(strrchr($name, '.'), 1);

		if(in_array($ext, $images)){
			header('Content-Type: image/'.$ext);
			header('Content-Disposition: filename="'.$uniq.'.'.$ext.'"');
			echo file_get_contents(MCR_UPL_PATH.'files/'.$name);

			exit;
		}

		header('Content-Type: application/octet-stream');
		header('Cache-Control:no-cache, must-revalidate');
		header('Expires:0');
		header('Pragma:no-cache');
		header('Content-Length:' . filesize(MCR_UPL_PATH.'files/'.$name));
		header('Content-Disposition: attachment; filename="'.$uniq.'.'.$ext.'"');
		header('Content-Transfer-Encoding:binary');

		readfile(MCR_UPL_PATH.'files/'.$name);

		$data['downloads']++;

		$data = $this->db->safesql(json_encode($data));

		$update = $this->db->query("UPDATE `mcr_files` SET `data`='$data' WHERE id='$id'");

		if(!$update){ $this->core->notify($this->core->lng['e_msg'], $this->core->lng['e_sql_critical'], 1, "?mode=403"); }

		exit;
	}
}

?>