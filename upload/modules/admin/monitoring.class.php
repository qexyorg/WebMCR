<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $config, $user, $lng;

	public function __construct($core){
		$this->core = $core;
		$this->db	= $core->db;
		$this->config = $core->config;
		$this->user	= $core->user;
		$this->lng	= $core->lng;

		$this->core->title = $this->lng['t_admin'].' — Мониторинг серверов';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Мониторинг серверов' => BASE_URL."?mode=admin&do=monitoring"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function monitor_array(){

		$start		= $this->core->pagination($this->config->pagin['adm_monitoring'], 0, 0); // Set start pagination
		$end		= $this->config->pagin['adm_monitoring']; // Set end pagination

		$query = $this->db->query("SELECT id, title, ip, `port`
									FROM `mcr_monitoring`
									ORDER BY id DESC
									LIMIT $start, $end");

		ob_start();

		if(!$query || $this->db->num_rows($query)<=0){
			echo $this->core->sp(MCR_THEME_MOD."admin/monitoring/monitor-none.html");
			return ob_get_clean();
		}

		while($ar = $this->db->fetch_assoc($query)){

			$page_data = array(
				"ID" => intval($ar['id']),
				"TITLE" => $this->db->HSC($ar['title']),
				"IP" => $this->db->HSC($ar['ip']),
				"PORT" => intval($ar['port'])
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/monitoring/monitor-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function monitor_list(){

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_monitoring`");

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->config->pagin['adm_monitoring'], "?mode=admin&do=monitoring&pid=", $ar[0]),
			"SERVERS" => $this->monitor_array()
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/monitoring/monitor-list.html", $data);

		return ob_get_clean();
	}

	private function delete(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->lng["e_msg"], $this->lng['e_hack'], 2, '?mode=admin&do=monitoring'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->lng["e_msg"], "Не выбрано ни одного пункта", 2, '?mode=admin&do=monitoring'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		$delete = $this->db->query("DELETE FROM `mcr_monitoring` WHERE id IN ($list)");

		if(!$delete){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=monitoring'); }

		$count = $this->db->affected_rows();

		$this->core->notify($this->lng["e_success"], "Удалено элементов: серверов - $count", 3, '?mode=admin&do=monitoring');

	}

	private function add(){

		$this->core->title .= ' — Добавление';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Мониторинг серверов' => BASE_URL."?mode=admin&do=monitoring",
			'Добавление' => BASE_URL."?mode=admin&do=monitoring&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title = $this->db->safesql(@$_POST['title']);
			$text = $this->db->safesql(@$_POST['text']);
			$ip = $this->db->safesql(@$_POST['ip']);
			$port = intval(@$_POST['port']);

			$insert = $this->db->query("INSERT INTO `mcr_monitoring`
											(title, `text`, ip, `port`)
										VALUES
											('$title', '$text', '$ip', '$port')");
			if(!$insert){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=monitoring'); }
			
			$this->core->notify($this->lng["e_success"], "Сервер успешно добавлен", 3, '?mode=admin&do=monitoring');
		}

		$data = array(
			"PAGE" => "Добавление сервера",
			"TITLE" => "",
			"TEXT" => "",
			"IP" => "localhost",
			"PORT" => "25565",
			"BUTTON" => "Добавить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/monitoring/monitor-add.html", $data);

		return ob_get_clean();
	}

	private function edit(){

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT title, `text`, ip, `port`
									FROM `mcr_monitoring`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=monitoring'); }

		$ar = $this->db->fetch_assoc($query);

		$this->core->title .= ' — Редактирование';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Мониторинг серверов' => BASE_URL."?mode=admin&do=monitoring",
			'Редактирование' => BASE_URL."?mode=admin&do=monitoring&op=edit&id=$id",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title = $this->db->safesql(@$_POST['title']);
			$text = $this->db->safesql(@$_POST['text']);
			$ip = $this->db->safesql(@$_POST['ip']);
			$port = intval(@$_POST['port']);


			$update = $this->db->query("UPDATE `mcr_monitoring`
										SET title='$title', `text`='$text', ip='$ip', `port`='$port'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=monitoring&op=edit&id='.$id); }
			
			$this->core->notify($this->lng["e_success"], "Сервер успешно изменен", 3, '?mode=admin&do=monitoring&op=edit&id='.$id);
		}

		$data = array(
			"PAGE" => "Редактирование сервера",
			"TITLE" => $this->db->HSC($ar['title']),
			"TEXT" => $this->db->HSC($ar['text']),
			"IP" => $this->db->HSC($ar['ip']),
			"PORT" => intval($ar['port']),
			"BUTTON" => "Сохранить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/monitoring/monitor-add.html", $data);

		return ob_get_clean();
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;

			default:		$content = $this->monitor_list(); break;
		}

		ob_start();

		echo $content;

		return ob_get_clean();
	}
}

?>