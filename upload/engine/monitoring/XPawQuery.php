<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class XPawQuery{

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
	private $object		= false;

	public function __construct(){
		require_once(MCR_MON_PATH.'xpaw/MinecraftQuery.php');
		require_once(MCR_MON_PATH.'xpaw/MinecraftQueryException.php');

		$this->object = new MinecraftQuery();
	}

	public function connect($ip='127.0.0.1', $port=25565){
		$this->status = $this->online = $this->slots = 0;
		$this->version = $this->players = $this->motd = $this->plugins = $this->map = $this->error = '';
		
		$this->ip = $ip;
		$this->port = $port;

		try{
			$this->object->Connect($ip, $port, 3);
		}catch(MinecraftQueryException $e){
			$Exception = $e;
		}

		if(isset($Exception)){ return false; }

		if(($array = $this->object->GetInfo()) == false){ return false; }

		$this->status = 1;

		$this->motd = @$array['HostName'];

		$this->map = @$array['Map'];

		$this->version = @$array['Version'];

		$this->online = intval(@$array['Players']);

		$this->slots = intval(@$array['MaxPlayers']);

		if(($players = $this->object->GetPlayers()) !== false){
			$this->players = @implode(', ', $players);
		}

		$this->plugins = @implode(', ', @$array['Plugins']);

		return true;
	}
}

?>