<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class submodule{
	private $core, $db, $config, $user, $lng;

	public function __construct($core){
		$this->core = $core;
		$this->db	= $core->db;
		$this->config = $core->config;
		$this->user	= $core->user;
		$this->lng	= $core->lng_m;

		if(!$this->core->is_access('sys_adm_logs')){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=admin",
			$this->lng['logs'] => BASE_URL."?mode=admin&do=logs"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function logs_array(){

		$start		= $this->core->pagination($this->config->pagin['adm_logs'], 0, 0); // Set start pagination
		$end		= $this->config->pagin['adm_logs']; // Set end pagination

		$where		= "";
		$sort		= "`l`.id";
		$sortby		= "DESC";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$where = "WHERE `l`.`message` LIKE '%$search%'";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$expl = explode(' ', $_GET['sort']);

			$sortby = ($expl[0]=='asc') ? "ASC" : "DESC";

			switch(@$expl[1]){
				case 'user': $sort = "`u`.login"; break;
				case 'msg': $sort = "`l`.`message`"; break;
				case 'date': $sort = "`l`.`date`"; break;
			}
		}

		$query = $this->db->query("SELECT `l`.id, `l`.uid, `l`.`message`, `l`.`date`, `u`.login, `u`.`color`, `g`.`color` AS `gcolor`
									FROM `mcr_logs` AS `l`
									LEFT JOIN `mcr_users` AS `u`
										ON `u`.id=`l`.uid
									LEFT JOIN `mcr_groups` AS `g`
										ON `g`.id=`u`.gid
									$where
									ORDER BY $sort $sortby
									LIMIT $start, $end");

		if(!$query || $this->db->num_rows($query)<=0){ return $this->core->sp(MCR_THEME_MOD."admin/logs/log-none.html"); }

		ob_start();

		while($ar = $this->db->fetch_assoc($query)){

			$color = (empty($ar['color'])) ? $this->db->HSC($ar['gcolor']) : $this->db->HSC($ar['color']);

			$login = (!is_null($ar['login'])) ? $this->db->HSC($ar['login']) : 'Пользователь удален';

			$page_data = array(
				"ID" => intval($ar['id']),
				"UID" => intval($ar['uid']),
				"MESSAGE" => $this->db->HSC($ar['message']),
				"DATE" => date("d.m.Y в H:i:s", $ar['date']),
				"LOGIN" => $this->core->colorize($login, $color),
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/logs/log-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function logs_list(){

		$sql = "SELECT COUNT(*) FROM `mcr_logs`";
		$page = "?mode=admin&do=logs";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$sql = "SELECT COUNT(*) FROM `mcr_logs` WHERE `message` LIKE '%$search%'";
			$search = $this->db->HSC(urldecode($_GET['search']));
			$page = "?mode=admin&do=logs&search=$search";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$page .= '&sort='.$this->db->HSC(urlencode($_GET['sort']));
		}

		$query = $this->db->query($sql);

		$ar = @$this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->config->pagin['adm_logs'], $page.'&pid=', $ar[0]),
			"LOGS" => $this->logs_array()
		);

		return $this->core->sp(MCR_THEME_MOD."admin/logs/log-list.html", $data);
	}

	public function content(){

		return $this->logs_list();
	}
}

?>