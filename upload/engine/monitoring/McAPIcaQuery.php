<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class McAPIcaQuery{

	// Get server information from https://mcapi.ca/

	// Set default values
	public $ip			= '127.0.0.1';
	public $port		= 25565;

	public $status		= 0; // server status
	public $version		= '';
	public $online		= 0;
	public $slots		= 0;
	public $players		= '';
	public $motd		= '';
	public $plugins		= '';
	public $map			= '';
	public $error		= '';

	public function connect($ip='127.0.0.1', $port=25565){
		$this->status = $this->online = $this->slots = 0;
		$this->version = $this->players = $this->motd = $this->plugins = $this->map = $this->error = '';
		
		$this->ip = $ip;
		$this->port = $port;

		$json = file_get_contents("https://mcapi.ca/query/$ip:$port/list");

		if($json===false){ $this->error = var_export($json, true); return false; }

		$array = @json_decode($json, true);

		if(@$array['Status']===false){ $this->error = 'Connection error'; return false; }

		$this->status = 1;

		$this->version = @$array['Version'];

		$this->online = intval(@$array['Players']['online']);

		$this->slots = intval(@$array['Players']['max']);

		$this->players = @implode(', ', @$array['Players']['list']);

		return true;
	}
}

?>