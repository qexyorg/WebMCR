<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class db{
	public $obj = false;

	public $result = false;

	private $cfg;

	public $count_queries = 0;
	public $count_queries_real = 0;

	public function __construct($host='127.0.0.1', $user='root', $pass='', $base='base', $port=3306, $core=array()){

		$this->cfg = $core->cfg;

		$connect = $this->connect($host, $user, $pass, $base, $port);
		
		if(!$connect){ return; }
	}

	public function connect($host='127.0.0.1', $user='root', $pass='', $base='base', $port=3306){
		$this->obj = @new mysqli($host, $user, $pass, $base, $port);

		if(mb_strlen($this->obj->connect_error, 'UTF-8')>0){ return false; }

		if($this->obj->connect_errno){ return false; }

		if(!$this->obj->set_charset("utf8")){ return false; }

		$this->count_queries_real = 2;
		
		@$this->query("SET sql_mode=''");
	}

	public function query($string){
		$this->count_queries += 1;
		$this->count_queries_real +=1;

		$this->result = @$this->obj->query($string);

		return $this->result;
	}

	public function affected_rows(){
		return $this->obj->affected_rows;
	}

	public function fetch_array($query=false){
		return $this->result->fetch_array();
	}

	public function fetch_assoc($query=false){
		return $this->result->fetch_assoc();
	}

	public function free(){
		return $this->result->free();
	}

	public function num_rows($query=false){
		return $this->result->num_rows;
	}

	public function insert_id(){
		return $this->obj->insert_id;
	}

	public function safesql($string){
		return $this->obj->real_escape_string($string);
	}

	public function HSC($string=''){
		return htmlspecialchars($string);
	}

	public function error(){

		if(!is_null(mysqli_connect_error())){ return mysqli_connect_error(); }
		if(!empty($this->obj->error)){ return $this->obj->error; }

		return;
	}

	public function remove_fast($from="", $where=""){
		if(empty($from) || empty($where)){ return false; }

		$delete = $this->query("DELETE FROM `$from` WHERE $where");

		if(!$delete){ return false; }

		return true;
	}

	public function actlog($msg='', $uid=0){
		if(!$this->cfg->db['log']){ return false; }

		$uid = intval($uid);
		$msg = $this->safesql($msg);
		$date = time();

		$ctables	= $this->cfg->db['tables'];
		$logs_f		= $ctables['logs']['fields'];

		$insert = $this->query("INSERT INTO `{$this->cfg->tabname('logs')}`
										(`{$logs_f['uid']}`, `{$logs_f['msg']}`, `{$logs_f['date']}`)
									VALUES
										('$uid', '$msg', '$date')");

		if(!$insert){ return false; }

		return true;
	}

	public function update_user($user){
		if(!$user->is_auth){ return false; }

		$time = time();

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$update = $this->query("UPDATE `{$this->cfg->tabname('users')}`
								SET `{$us_f['ip_last']}`='{$user->ip}', `{$us_f['date_last']}`='$time'
								WHERE `{$us_f['id']}`='{$user->id}'");

		if(!$update){ return false; }

		return true;
	}
}

?>
