<?php

class db{
	public $obj = false;

	public $result = false;

	private $config, $lng;

	public $count_queries = 0;
	public $count_queries_real = 0;

	public function __construct($config, $lng){

		$this->obj = @new mysqli($config->db['host'], $config->db['user'], $config->db['pass'], $config->db['base'], $config->db['port']);

		if(mysqli_connect_errno($this->obj)){ return; }

		if(!$this->obj->set_charset("utf8")){ return; }
	}

	public function query($string){
		$this->count_queries += 1;

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
		return $this->obj->error;
	}
}

?>