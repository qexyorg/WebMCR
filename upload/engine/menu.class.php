<?php

class menu{
	private $core, $db; // , $user, $lng

	public function __construct($core){
		$this->core	= $core;
		$this->db	= $core->db;
		$this->user = $core->user;
		//$this->lng = $core->lng;
	}

	private function generate_sub_menu($tree){
		ob_start();

		foreach ($tree as $key=>$ar){
			$id = intval($ar['id']);
			$parent = intval($ar['parent']);

			$data = array(
				"TITLE"		=> $this->db->HSC($ar['title']),
				"URL"		=> $this->db->HSC($ar['url']),
				"TARGET"	=> $this->db->HSC($ar['target']),
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

	private function generate_menu($array){
		ob_start();

		$tree = $this->create_tree($array);

		foreach ($tree as $key=>$ar){

			$id = intval($ar['id']);
			$parent = intval($ar['parent']);

			$data = array(
				"TITLE"		=> $this->db->HSC($ar['title']),
				"URL"		=> $this->db->HSC($ar['url']),
				"TARGET"	=> $this->db->HSC($ar['target']),
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

		$this->new_tree_element(&$categories, &$tree, null);

		return $tree;
	}

	private function new_tree_element($categories, &$tree, $parent){

		foreach($categories as $key => $ar){

			if(intval($ar['parent']) == $parent){
				$tree[$key] = $categories[$key];
				$tree[$key]['sons'] = array();
				$this->new_tree_element($categories, &$tree[$key]['sons'], $key);
			}
			if(empty($tree['sons'])){ unset ($tree['sons']); }

		}

		unset($categories[$parent]);
		return;
	}

	private function menu_array(){
		ob_start();

		$query = $this->db->query("SELECT id, title, `parent`, `url`, `target`, `permissions`
									FROM `mcr_menu`
									ORDER BY `parent` DESC");

		if(!$query || $this->db->num_rows($query)<=0){ return ob_get_clean(); }

		$array = array();

		while($ar = $this->db->fetch_assoc($query)){

			if(!$this->core->is_access($ar['permissions'])){ continue; }
			
			$array[$ar['id']] = array(
				"id" => $ar['id'],
				"title" => $ar['title'],
				"parent" => $ar['parent'],
				"url" => $ar['url'],
				"target" => $ar['target'],
				"permissions" => $ar['permissions']
			);
		}

		$tree = $this->generate_menu($array);

		echo $tree;

		return ob_get_clean();
	}

	public function _list(){
		ob_start();

		echo $this->menu_array();

		return ob_get_clean();
	}
}

?>