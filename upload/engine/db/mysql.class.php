<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class db{
	public $obj = false;

	public $result = false;

	private $cfg;

	public $count_queries = 0;
	public $count_queries_real = 0;

	public function __construct($cfg){

		$this->cfg = $cfg;

		$this->obj = @mysql_connect($cfg->db['host'].':'.$cfg->db['port'], $cfg->db['user'], $cfg->db['pass']);

		if(!@mysql_select_db($cfg->db['base'], $this->obj)){ return; }

		@mysql_set_charset("UTF8", $this->obj);

		$this->count_queries_real = 2;
	}

	public function query($string){
		$this->count_queries += 1;
		$this->count_queries_real +=1;

		return @mysql_query($string, $this->obj);
	}

	public function affected_rows(){
		return mysql_affected_rows();
	}

	public function fetch_array($query=false){
		return mysql_fetch_array($query);
	}

	public function fetch_assoc($query=false){
		return mysql_fetch_assoc($query);
	}

	public function free($query=false){
		return mysql_free_result($query);
	}

	public function num_rows($query=false){
		return mysql_num_rows($query);
	}

	public function insert_id(){
		return mysql_insert_id();
	}

	public function safesql($string){
		return mysql_real_escape_string($string);
	}

	public function HSC($string){
		return htmlspecialchars($string);
	}

	public function error(){
		return mysql_error();
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

		$data = array(
			'time_create' => $user->data->time_create,
			'time_last' => time(),
			'firstname' => $user->data->firstname,
			'lastname' => $user->data->lastname,
			'gender' => $user->data->gender,
			'birthday' => $user->data->birthday,
		);

		$data = $this->safesql(json_encode($data));

		$ctables	= $this->cfg->db['tables'];
		$us_f		= $ctables['users']['fields'];

		$update = $this->query("UPDATE `{$this->cfg->tabname('users')}`
								SET `{$us_f['ip_last']}`='{$user->ip}', `{$us_f['data']}`='$data'
								WHERE `{$us_f['id']}`='{$user->id}'");

		if(!$update){ return false; }

		return true;
	}
}

?>
