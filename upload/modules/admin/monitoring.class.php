<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $cfg, $user, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;
		$this->user		= $core->user;
		$this->lng		= $core->lng_m;

		if(!$this->core->is_access('sys_adm_monitoring')){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['monitoring'] => ADMIN_URL."&do=monitoring"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$this->core->header .= $this->core->sp(MCR_THEME_MOD."admin/monitoring/header.html");
	}

	private function monitor_array(){

		$start		= $this->core->pagination($this->cfg->pagin['adm_monitoring'], 0, 0); // Set start pagination
		$end		= $this->cfg->pagin['adm_monitoring']; // Set end pagination

		$where		= "";
		$sort		= "id";
		$sortby		= "DESC";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$where = "WHERE title LIKE '%$search%'";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$expl = explode(' ', $_GET['sort']);

			$sortby = ($expl[0]=='asc') ? "ASC" : "DESC";

			switch(@$expl[1]){
				case 'title': $sort = "title"; break;
				case 'address': $sort = "CONCAT(`ip`, `port`)"; break;
			}
		}

		$query = $this->db->query("SELECT id, title, ip, `port`
									FROM `mcr_monitoring`
									$where
									ORDER BY $sort $sortby
									LIMIT $start, $end");

		if(!$query || $this->db->num_rows($query)<=0){ return $this->core->sp(MCR_THEME_MOD."admin/monitoring/monitor-none.html"); }

		ob_start();

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

		$sql = "SELECT COUNT(*) FROM `mcr_monitoring`";
		$page = "?mode=admin&do=monitoring";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$sql = "SELECT COUNT(*) FROM `mcr_monitoring` WHERE title LIKE '%$search%'";
			$search = $this->db->HSC(urldecode($_GET['search']));
			$page = "?mode=admin&do=monitoring&search=$search";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$page .= '&sort='.$this->db->HSC(urlencode($_GET['sort']));
		}

		$query = $this->db->query($sql);

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->cfg->pagin['adm_monitoring'], $page.'&pid=', $ar[0]),
			"SERVERS" => $this->monitor_array()
		);

		return $this->core->sp(MCR_THEME_MOD."admin/monitoring/monitor-list.html", $data);
	}

	private function delete(){
		if(!$this->core->is_access('sys_adm_monitoring_delete')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=monitoring'); }

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=monitoring'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['mon_not_selected'], 2, '?mode=admin&do=monitoring'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		if(!$this->db->remove_fast("mcr_monitoring", "id IN ($list)")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=monitoring'); }

		$count = $this->db->affected_rows();

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_del_mon']." $list ".$this->lng['log_mon'], $this->user->id);

		$this->core->notify($this->core->lng["e_success"], $this->lng['mon_del_elements']." $count", 3, '?mode=admin&do=monitoring');

	}

	private function add(){
		if(!$this->core->is_access('sys_adm_monitoring_add')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=monitoring'); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL."",
			$this->lng['monitoring'] => ADMIN_URL."&do=monitoring",
			$this->lng['mon_add'] => ADMIN_URL."&do=monitoring&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title		= $this->db->safesql(@$_POST['title']);
			$text		= $this->db->safesql(@$_POST['text']);
			$ip			= $this->db->safesql(@$_POST['ip']);
			$port		= intval(@$_POST['port']);
			$updater	= intval(@$_POST['cache']);
			$type		= $this->db->safesql(@$_POST['type']);

			if(!file_exists(MCR_MON_PATH.$type.'.php')){ $type = 'MineToolsAPIPing'; }

			$insert = $this->db->query("INSERT INTO `mcr_monitoring`
											(title, `text`, ip, `port`, `players`, `motd`, `plugins`, last_error, `type`, updater)
										VALUES
											('$title', '$text', '$ip', '$port', '', '', '', '', '$type', '$updater')");
			if(!$insert){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=monitoring'); }

			$id = $this->db->insert_id();

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_add_mon']." #$id ".$this->lng['log_mon'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['mon_add_success'], 3, '?mode=admin&do=monitoring');
		}

		$data = array(
			"PAGE"		=> $this->lng['mon_add_page_name'],
			"TITLE"		=> "",
			"TEXT"		=> "",
			"IP"		=> "127.0.0.1",
			"PORT"		=> "25565",
			"TYPES"		=> $this->types(),
			"CACHE"		=> 60,
			"ERROR"		=> "",
			"BUTTON"	=> $this->lng['mon_add_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/monitoring/monitor-add.html", $data);
	}

	private function edit(){
		if(!$this->core->is_access('sys_adm_monitoring_edit')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=monitoring'); }

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT title, `text`, ip, `port`, last_error, updater, last_error, `type`
									FROM `mcr_monitoring`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=monitoring'); }

		$ar = $this->db->fetch_assoc($query);

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL."",
			$this->lng['monitoring'] => ADMIN_URL."&do=monitoring",
			$this->lng['mon_edit'] => ADMIN_URL."&do=monitoring&op=edit&id=$id",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title		= $this->db->safesql(@$_POST['title']);
			$text		= $this->db->safesql(@$_POST['text']);
			$ip			= $this->db->safesql(@$_POST['ip']);
			$port		= intval(@$_POST['port']);
			$updater	= intval(@$_POST['cache']);
			$type		= $this->db->safesql(@$_POST['type']);

			if(!file_exists(MCR_MON_PATH.$type.'.php')){ $type = 'MineToolsAPIPing'; }


			$update = $this->db->query("UPDATE `mcr_monitoring`
										SET title='$title', `text`='$text', ip='$ip', `port`='$port', `type`='$type', updater='$updater'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=monitoring&op=edit&id='.$id); }

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_edit_mon']." #$id ".$this->lng['log_mon'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['mon_edit_success'], 3, '?mode=admin&do=monitoring&op=edit&id='.$id);
		}

		$data = array(
			"PAGE"		=> $this->lng['mon_edit_page_name'],
			"TITLE"		=> $this->db->HSC($ar['title']),
			"TEXT"		=> $this->db->HSC($ar['text']),
			"IP"		=> $this->db->HSC($ar['ip']),
			"PORT"		=> intval($ar['port']),
			"CACHE"		=> intval($ar['updater']),
			"TYPES"		=> $this->types($ar['type']),
			"ERROR"		=> $this->db->HSC($ar['last_error']),
			"BUTTON"	=> $this->lng['mon_edit_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/monitoring/monitor-add.html", $data);
	}

	private function types($selected=''){
		$list = scandir(MCR_MON_PATH);

		if(empty($list)){ return false; }

		ob_start();

		foreach($list as $key => $file){
			$name = substr($file, 0, -4);

			if($file=='.' || $file=='..' || substr($file, -4)!='.php'){ continue; }

			$select = ($selected==$name) ? 'selected' : '';

			echo '<option value="'.$name.'" '.$select.'>'.$name.'</option>';
		}
		
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

		return $content;
	}
}

?>