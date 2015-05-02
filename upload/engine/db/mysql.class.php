<?php

class db{
	public $obj = false;

	public $result = false;

	private $config, $lng;

	public $count_queries = 0;
	public $count_queries_real = 0;

	public function __construct($config, $lng){

		$this->obj = @mysql_connect($config->db['host'].':'.$config->db['port'], $config->db['user'], $config->db['pass']);

		if(!@mysql_select_db($config->db['base'], $this->obj)){ return; }

		@mysql_set_charset("UTF8", $this->obj);
	}

	public function query($string){
		$this->count_queries += 1;

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
}

?>