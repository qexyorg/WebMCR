<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class XPawPing{

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

	public function __construct(){
		require_once(MCR_MON_PATH.'xpaw/MinecraftPing.php');
		require_once(MCR_MON_PATH.'xpaw/MinecraftPingException.php');
	}

	public function connect($ip='127.0.0.1', $port=25565){
		$this->status = $this->online = $this->slots = 0;
		$this->version = $this->players = $this->motd = $this->plugins = $this->map = $this->error = '';
		
		$this->ip = $ip;
		$this->port = $port;

		$array = false;
		$Query = null;

		try{
			$Query = new MinecraftPing($ip, $port, 3);

			$array = $Query->Query();

			if($array === false){
				$Query->Close();
				$Query->Connect();

				$array = $Query->QueryOldPre17();
			}
		}catch(MinecraftPingException $e){
			$Exception = $e;
		}

		if($Query !== null){ $Query->Close(); }

		if(isset($Exception)){ return false; }

		if($array == false){ return false; }

		$this->status = 1;

		$this->version = @$array['version']['name'];

		$this->motd = @$array['description'];

		$this->online = intval(@$array['players']['online']);

		$this->slots = intval(@$array['players']['max']);

		return true;
	}
}

?>