<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

use xPaw\MinecraftQuery;
use xPaw\MinecraftQueryException;

class monitoring{
	private $ip, $port, $socket;
	public $data, $type;

	public function __construct($type=0){
		$this->type = $type;

		switch($type){

			default:
				$this->socket = $this->get_socket_libs();
			break;
		}

	}

	private function get_socket_libs(){

		require_once(MCR_TOOL_PATH.'libs/status_query/MinecraftQuery.php');
		require_once(MCR_TOOL_PATH.'libs/status_query/MinecraftQueryException.php');

		return new MinecraftQuery();
	}

	private function socket_connection($ip, $port){
		try{
			$this->socket->Connect($ip, $port, 5);
		} catch( MinecraftQueryException $e ) {
			$exception = $e;
		}

		if(isset($exception) || ($data = $this->socket->GetInfo()) === false){ return false; }

		$array = array(
			'status'		=> 'online',
			'title'			=> $data['HostName'],
			'type'			=> $data['GameType'],
			'version'		=> $data['Version'],
			'players'		=> intval($data['Players']),
			'slots'			=> intval($data['MaxPlayers']),
			'player_list'	=> array()
		);

		if(($list = $this->socket->GetPlayers()) !== false){ $array['player_list'] = $list; }

		return $array;
	}

	private function minetools_connection($ip, $port){
		$json = file_get_contents("http://www.api.minetools.eu/query/$ip/$port");

		if(!$json){ return false; }

		$data = @json_decode($json, true);

		if(!$data || isset($data['error'])){ return false; }

		$array = array(
			'status'		=> 'online',
			'type'			=> $data['GameType'],
			'version'		=> $data['Version'],
			'players'		=> intval($data['Players']),
			'slots'			=> intval($data['MaxPlayers'])
		);

		$array['title'] = (is_null($data['HostName'])) ? '' : $data['HostName'];
		$array['player_list'] = ($data['Playerlist']=='null') ? array() : $data['Playerlist'];

		return $array;
	}

	public function connect($ip='localhost', $port=25565){

		$array = array(
			'status'		=> 'offline',
			'title'			=> '',
			'type'			=> 'SMT',
			'version'		=> 0,
			'players'		=> 0,
			'slots'			=> 0,
			'player_list'	=> array()
		);

		$this->data = json_encode($array);
		
		if($this->type==1){
			$data = $this->minetools_connection($ip, $port);
			if($data===false){ return false; }
		}else{
			$data = $this->socket_connection($ip, $port);
			if($data===false){ return false; }
		}

		$this->data = json_encode($data);
		
		return true;
	}

}

?>