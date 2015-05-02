<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $user, $config, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->config	= $core->config;
		$this->lng		= $core->lng;
	}

	private function check_login(){
		if(!isset($_POST['value'])){ exit("Hacking Attempt!"); }

		if(!preg_match("/^[\w\-]{3,}$/i", $_POST['value'])){ exit($this->lng['e_reg_login_regexp']); }

		$login = $this->db->safesql($_POST['value']);

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_users` WHERE login='$login'");

		if(!$query){ exit($this->lng['e_sql_critical']); }

		$ar = $this->db->fetch_array($query);

		if($ar[0]>0){ exit($this->lng['e_reg_login_exist']); }

		$array = array(
			"_status" => "success"
		);

		echo json_encode($array);

		exit;
	}

	private function check_email(){
		if(!isset($_POST['value'])){ exit("Hacking Attempt!"); }

		if(!filter_var($_POST['value'], FILTER_VALIDATE_EMAIL)){ exit($this->lng['e_reg_email_regexp']); }

		$email = $this->db->safesql($_POST['value']);

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_users` WHERE email='$email'");

		if(!$query){ exit($this->lng['e_sql_critical']); }

		$ar = $this->db->fetch_array($query);

		if($ar[0]>0){ exit($this->lng['e_reg_email_exist']); }

		$array = array(
			"_status" => "success"
		);

		echo json_encode($array);

		exit;
	}

	private function get_session(){
		if(!isset($_GET['name'])){ exit("undefined"); }

		$name = "ajx_".$_GET['name'];

		if(!isset($_SESSION[$name])){ exit("undefined"); }

		exit($_SESSION[$name]."");
	}

	private function set_session(){
		if(!isset($_GET['name']) || !isset($_GET['value'])){ exit; }

		$name = "ajx_".$_GET['name'];
		$value = $_GET['value'];

		$_SESSION[$name] = $value;
		exit;
	}

	private function remove_session(){
		if(!isset($_GET['name'])){ exit("undefined"); }

		$name = "ajx_".$_GET['name'];

		if(!isset($_SESSION[$name])){ exit("undefined"); }

		unset($_SESSION[$name]);
	}

	private function session(){
		$op = (isset($_GET['op'])) ? $_GET['op'] : false;

		switch($op){
			case "get": $this->get_session(); break;

			case "set": $this->set_session(); break;

			case "remove": $this->remove_session(); break;
		}

		exit("403");
	}

	private function monitor(){
		if(!$this->core->is_access("sys_monitoring")){ $this->core->js_notify('Access Denied'); }

		$query = $this->db->query("SELECT id, title, `text`, ip, `port` FROM `mcr_monitoring`");
		if(!$query || $this->db->num_rows($query)<=0){ $this->core->js_notify('Нет доступных серверов'); }
		
		$array = array();

		require_once(MCR_TOOL_PATH.'monitoring.class.php');

		$m = new monitoring($this->config->main['mon_type']);

		while($ar = $this->db->fetch_assoc($query)){
			$address = $this->db->HSC($ar['ip']);
			$port = intval($ar['port']);

			$m->connect($address, $port);

			$json = json_decode($m->data);

			if($json->status=='online'){
				$status = 'progress-info';
				$stats = $json->players.' / '.$json->slots;
				$progress = ceil($json->slots / 100 * $json->players);
			}else{
				$status = 'progress-danger';
				$stats = 'Сервер оффлайн';
				$progress = 100;
			}

			$data = array(
				"ID" => intval($ar['id']),
				"TITLE" => $this->db->HSC($ar['title']),
				"TEXT" => $this->db->HSC($ar['text']),
				"STATUS" => $status,
				"STATS" => $stats,
			);

			$array[] = array(
				"id" => intval($ar['id']),
				//"ip" => $address,
				//"port" => $port,
				"progress" => $progress,
				"form" => $this->core->sp(MCR_THEME_BLOCK."monitor/monitor-id.html", $data),
			);

		}

		$this->core->js_notify('Серверы успешно получены', true, $array);
	}

	public function content(){

		if($_SERVER['REQUEST_METHOD']=='POST'){
		
			$do = (isset($_POST['do'])) ? $_POST['do'] : false;

			switch($do){
				case 'check_login': $this->check_login(); break;
				case 'check_email': $this->check_email(); break;

				default: exit("403"); break;
			}

		}else{

			$do = (isset($_GET['do'])) ? $_GET['do'] : false;

			switch($do){
				case 'session': $this->session(); break;
				case 'monitor': $this->monitor(); break;

				default: exit("403"); break;
			}

		}
	}

}

?>