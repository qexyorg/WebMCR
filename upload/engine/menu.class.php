<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class menu{
	private $core, $db, $user, $cfg;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->user		= $core->user;
		$this->cfg		= $core->cfg;
		//$this->lng	= $core->lng;
	}

	private function generate_sub_menu($tree){
		ob_start();

		foreach ($tree as $key=>$ar){

			$data = array(
				"TITLE"		=> $ar['title'],
				"URL"		=> $this->db->HSC($ar['url']),
				"STYLE"		=> $this->db->HSC($ar['style']),
				"TARGET"	=> $this->db->HSC($ar['target']),
				"ACTIVE"	=> ($this->is_active($ar['url'], $ar['sons'])) ? 'active' : '',
				"SUB_MENU"	=> (!empty($ar['sons'])) ? $this->generate_sub_menu($ar['sons']) : "",
			);

			if(!empty($ar['sons'])){
				echo $this->core->sp(MCR_THEME_PATH."menu/menu-id-sub-parented.html", $data);
				continue;
			}

			echo $this->core->sp(MCR_THEME_PATH."menu/menu-id-sub.html", $data);
		}

		return ob_get_clean();
	}

	private function get_url(){
		$protocol = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on')) ? 'https://' : 'http://';
		return $protocol.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	}

	private function is_active($url){

		if($this->cfg->main['s_root']==$url || $this->cfg->main['s_root_full']==$url){
			if(!isset($_GET['mode']) || @$_GET['mode']==$this->cfg->main['s_dpage']){
				return true;
			}
		}else{
			if(strripos($this->request_url, $url)!==false){
				return true;
			}
		}

		return false;
	}

	private function generate_menu($array){
		$this->request_url = $this->get_url();

		ob_start();

		$tree = $this->create_tree($array);

		foreach ($tree as $key=>$ar){

			$data = array(
				"TITLE"		=> $ar['title'],
				"URL"		=> $this->db->HSC($ar['url']),
				"STYLE"		=> $this->db->HSC($ar['style']),
				"TARGET"	=> $this->db->HSC($ar['target']),
				"ACTIVE"	=> ($this->is_active($ar['url'], $ar['sons'])) ? 'active' : '',
				"SUB_MENU"	=> (!empty($ar['sons'])) ? $this->generate_sub_menu($ar['sons']) : "",
			);

			if(!empty($ar['sons'])){
				echo $this->core->sp(MCR_THEME_PATH."menu/menu-id-parented.html", $data);
				continue;
			}
			
			echo $this->core->sp(MCR_THEME_PATH."menu/menu-id.html", $data);
			
		}

		return ob_get_clean();
	}

	private function create_tree($categories){
		$tree = array();

		$this->new_tree_element($categories, $tree, null);

		return $tree;
	}

	private function new_tree_element(&$categories, &$tree, $parent){

		foreach($categories as $key => $ar){

			if(intval($ar['parent']) == $parent){
				$tree[$key] = $categories[$key];
				$tree[$key]['sons'] = array();
				$this->new_tree_element($categories, $tree[$key]['sons'], $key);
			}
			if(empty($tree['sons'])){ unset ($tree['sons']); }

		}

		unset($categories[$parent]);
		return;
	}

	private function menu_array(){
		

		$query = $this->db->query("SELECT id, title, `parent`, `url`, `style`, `target`, `permissions`
									FROM `mcr_menu`
									ORDER BY `parent` DESC");

		if(!$query || $this->db->num_rows($query)<=0){ return; }

		$array = array();

		while($ar = $this->db->fetch_assoc($query)){

			if(!$this->core->is_access($ar['permissions'])){ continue; }
			
			$array[$ar['id']] = array(
				"id" => $ar['id'],
				"title" => $ar['title'],
				"parent" => $ar['parent'],
				"url" => $ar['url'],
				"style" => $ar['style'],
				"target" => $ar['target'],
				"permissions" => $ar['permissions']
			);
		}

		return $this->generate_menu($array);
	}

	public function _list(){

		return $this->menu_array();
	}
}

?>