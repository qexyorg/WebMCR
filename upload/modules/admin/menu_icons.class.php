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

		$this->core->title = $this->lng['t_admin'].' — Иконки меню ПУ';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Иконки меню ПУ' => BASE_URL."?mode=admin&do=menu_icons"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function icon_array(){

		$start		= $this->core->pagination($this->config->pagin['adm_menu_icons'], 0, 0); // Set start pagination
		$end		= $this->config->pagin['adm_menu_icons']; // Set end pagination

		$query = $this->db->query("SELECT id, title, img
									FROM `mcr_menu_adm_icons`
									ORDER BY id DESC
									LIMIT $start, $end");

		ob_start();

		if(!$query || $this->db->num_rows($query)<=0){
			echo $this->core->sp(MCR_THEME_MOD."admin/menu_icons/icon-none.html");
			return ob_get_clean();
		}

		while($ar = $this->db->fetch_assoc($query)){

			$page_data = array(
				"ID" => intval($ar['id']),
				"TITLE" => $this->db->HSC($ar['title']),
				"IMG" => $this->db->HSC($ar['img']),
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/menu_icons/icon-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function icon_list(){

		$query = $this->db->query("SELECT COUNT(*) FROM `mcr_menu_adm_icons`");

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->config->pagin['adm_menu_icons'], "?mode=admin&do=menu_icons&pid=", $ar[0]),
			"ICONS" => $this->icon_array()
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/menu_icons/icon-list.html", $data);

		return ob_get_clean();
	}

	private function delete(){
		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->lng["e_msg"], $this->lng['e_hack'], 2, '?mode=admin&do=menu_icons'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->lng["e_msg"], "Не выбрано ни одного пункта", 2, '?mode=admin&do=menu_icons'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		$delete = $this->db->query("DELETE FROM `mcr_menu_adm_icons` WHERE id IN ($list)");

		if(!$delete){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=menu_icons'); }

		$count = $this->db->affected_rows();

		$this->core->notify($this->lng["e_success"], "Удалено элементов: иконок - $count", 3, '?mode=admin&do=menu_icons');

	}

	private function add(){

		$this->core->title .= ' — Добавление';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Иконки меню ПУ' => BASE_URL."?mode=admin&do=menu_icons",
			'Добавление' => BASE_URL."?mode=admin&do=menu_icons&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title			= $this->db->safesql(@$_POST['title']);
			$img			= @$_POST['img'];
			$img			= (empty($img)) ? 'default.png' : $this->db->safesql($img);

			$insert = $this->db->query("INSERT INTO `mcr_menu_adm_icons`
											(title, img)
										VALUES
											('$title', '$img')");

			if(!$insert){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=menu_icons'); }
			
			$this->core->notify($this->lng["e_success"], "Иконка меню успешно добавлена", 3, '?mode=admin&do=menu_icons');
		}

		$data = array(
			"PAGE" => "Добавление иконки",
			"TITLE" => '',
			"IMG" => 'default.png',
			"BUTTON" => "Добавить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/menu_icons/icon-add.html", $data);

		return ob_get_clean();
	}

	private function edit(){

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT title, img
									FROM `mcr_menu_adm_icons`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=menu_icons'); }

		$ar = $this->db->fetch_assoc($query);

		$this->core->title .= ' — Редактирование';

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin",
			'Иконки меню ПУ' => BASE_URL."?mode=admin&do=menu_icons",
			'Редактирование' => BASE_URL."?mode=admin&do=menu_icons&op=edit&id=$id",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$title			= $this->db->safesql(@$_POST['title']);
			$img			= @$_POST['img'];
			$img			= (empty($img)) ? 'default.png' : $this->db->safesql($img);

			$update = $this->db->query("UPDATE `mcr_menu_adm_icons`
										SET title='$title', img='$img'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->lng["e_msg"], $this->lng["e_sql_critical"], 2, '?mode=admin&do=menu_icons&op=edit&id='.$id); }
			
			$this->core->notify($this->lng["e_success"], "Иконка меню успешно изменена", 3, '?mode=admin&do=menu_icons&op=edit&id='.$id);
		}

		$data = array(
			"PAGE"			=> "Редактирование иконки",
			"TITLE"			=> $this->db->HSC($ar['title']),
			"IMG"			=> $this->db->HSC($ar['img']),
			"BUTTON"		=> "Сохранить"
		);

		ob_start();
		
		echo $this->core->sp(MCR_THEME_MOD."admin/menu_icons/icon-add.html", $data);

		return ob_get_clean();
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;

			default:		$content = $this->icon_list(); break;
		}

		ob_start();

		echo $content;

		return ob_get_clean();
	}
}

?>