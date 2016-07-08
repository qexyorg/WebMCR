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

		if(!$this->core->is_access('sys_adm_comments')){ $this->core->notify($this->core->lng['403'], $this->core->lng['e_403']); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['comments'] => ADMIN_URL."&do=comments"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$this->core->header .= $this->core->sp(MCR_THEME_MOD."admin/comments/header.html");
	}

	private function comment_array(){

		$start		= $this->core->pagination($this->cfg->pagin['adm_comments'], 0, 0); // Set start pagination
		$end		= $this->cfg->pagin['adm_comments']; // Set end pagination

		$where		= "";
		$sort		= "`c`.id";
		$sortby		= "DESC";

		$ctables	= $this->cfg->db['tables'];

		$ug_f		= $ctables['ugroups']['fields'];
		$us_f		= $ctables['users']['fields'];

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$expl = explode(' ', $_GET['sort']);

			$sortby = ($expl[0]=='asc') ? "ASC" : "DESC";

			switch(@$expl[1]){
				case 'comment': $sort = "`c`.text_html"; break;
				case 'news': $sort = "`n`.title"; break;
				case 'user': $sort = "`u`.`{$us_f['login']}`"; break;
			}
		}

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$where = "WHERE `c`.text_html LIKE '%$search%'";
		}

		$query = $this->db->query("SELECT `c`.id, `c`.nid, `c`.text_html, `n`.title AS `new`,
											`u`.`{$us_f['login']}`, `u`.`{$us_f['color']}`, `g`.`{$ug_f['color']}` AS `gcolor`
									FROM `mcr_comments` AS `c`
									LEFT JOIN `mcr_news` AS `n`
										ON `n`.id=`c`.nid
									LEFT JOIN `{$this->cfg->tabname('users')}` AS `u`
										ON `u`.`{$us_f['id']}`=`c`.uid
									LEFT JOIN `{$this->cfg->tabname('ugroups')}` AS `g`
										ON `g`.`{$ug_f['id']}`=`u`.`{$us_f['group']}`
									$where
									ORDER BY $sort $sortby
									LIMIT $start, $end");

		if(!$query || $this->db->num_rows($query)<=0){ return $this->core->sp(MCR_THEME_MOD."admin/comments/com-none.html"); }

		ob_start();

		while($ar = $this->db->fetch_assoc($query)){

			$text = strip_tags($ar['text_html']);

			$text = mb_substr($text, 0, 24, "UTF-8").'...';

			$new = (empty($ar['new'])) ? 'Новость удалена' : $this->db->HSC($ar['new']);

			$login = (is_null($ar[$us_f['login']])) ? 'Пользователь удален' : $this->db->HSC($ar[$us_f['login']]);

			$color = (empty($ar[$us_f['color']])) ? $this->db->HSC($ar['gcolor']) : $this->db->HSC($ar[$us_f['color']]);

			$page_data = array(
				"ID" => intval($ar['id']),
				"NID" => intval($ar['nid']),
				"NEW" => $new,
				"TEXT" => $text,
				"LOGIN" => $this->core->colorize($login, $color),
			);
		
			echo $this->core->sp(MCR_THEME_MOD."admin/comments/com-id.html", $page_data);
		}

		return ob_get_clean();
	}

	private function comment_list(){

		$sql = "SELECT COUNT(*) FROM `mcr_comments`";
		$page = "?mode=admin&do=comments";

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $this->db->safesql(urldecode($_GET['search']));
			$sql = "SELECT COUNT(*) FROM `mcr_comments` WHERE text_html LIKE '%$search%'";
			$search = $this->db->HSC(urldecode($_GET['search']));
			$page = "?mode=admin&do=comments&search=$search";
		}

		if(isset($_GET['sort']) && !empty($_GET['sort'])){
			$page .= '&sort='.$this->db->HSC(urlencode($_GET['sort']));
		}

		$query = $this->db->query($sql);

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->cfg->pagin['adm_comments'], $page.'&pid=', $ar[0]),
			"COMMENTS" => $this->comment_array()
		);

		return $this->core->sp(MCR_THEME_MOD."admin/comments/com-list.html", $data);
	}

	private function delete(){

		if(!$this->core->is_access('sys_adm_comments_delete')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=comments'); }

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_hack'], 2, '?mode=admin&do=comments'); }
			
		$list = @$_POST['id'];

		if(empty($list)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['com_not_selected'], 2, '?mode=admin&do=comments'); }

		$list = $this->core->filter_int_array($list);

		$list = array_unique($list);

		$list = $this->db->safesql(implode(", ", $list));

		if(!$this->db->remove_fast("mcr_comments", "id IN ($list)")){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=comments'); }

		$count1 = $this->db->affected_rows();

		// Последнее обновление пользователя
		$this->db->update_user($this->user);

		// Лог действия
		$this->db->actlog($this->lng['log_del_com']." $list ".$this->lng['log_com'], $this->user->id);

		$this->core->notify($this->core->lng["e_success"], $this->lng['com_del_elements']." - $count1", 3, '?mode=admin&do=comments');

	}

	private function news($selected=1){
		$selected = intval($selected);
		$query = $this->db->query("SELECT id, title FROM `mcr_news` ORDER BY title ASC");

		if(!$query || $this->db->num_rows($query)<=0){

			$data = array(
				"ID" => 1,
				"TITLE" => $this->lng['com_without_news'],
				"SELECTED" => 'selected disabled'
			);

			return $this->core->sp(MCR_THEME_MOD."admin/comments/nid-list-id.html", $data);
		}

		ob_start();

		while($ar = $this->db->fetch_assoc($query)){
			$data = array(
				"ID" => intval($ar['id']),
				"TITLE" => $this->db->HSC($ar['title']),
				"SELECTED" => ($selected==intval($ar['id'])) ? 'selected' : ''
			);

			echo $this->core->sp(MCR_THEME_MOD."admin/comments/nid-list-id.html", $data);
		}

		return ob_get_clean();
	}

	private function add(){

		if(!$this->core->is_access('sys_adm_comments_add')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=comments'); }

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['comments'] => ADMIN_URL."&do=comments",
			$this->lng['com_add'] => ADMIN_URL."&do=comments&op=add",
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$bb = $this->core->load_bb_class(); // Загрузка класса BB-кодов

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$nid = intval(@$_POST['nid']);

			// Обработка описания +
			$text_bb = @$_POST['text'];

			$text_bb_trim = trim($text_bb);

			if(empty($text_bb_trim)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['com_empty'], 2, '?mode=admin&do=comments&op=add'); }

			$text_bb				= $this->db->HSC($text_bb);

			$text_html				= $bb->decode($text_bb);

			$safe_text_html			= $this->db->safesql($text_html);

			$text_bb				= $this->db->safesql($text_bb);

			$text_html_strip		= trim(strip_tags($text_html, "<img>"));

			if(empty($text_html_strip)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['com_incorrect'], 2, '?mode=admin&do=comments&op=add'); }
			// Обработка описания -

			$new_data = array(
				"time_create" => time(),
				"time_last" => time()
			);

			$new_data = $this->db->safesql(json_encode($new_data));

			$insert = $this->db->query("INSERT INTO `mcr_comments`
											(nid, text_bb, text_html, uid, `data`)
										VALUES
											('$nid', '$text_bb', '$text_html', '{$this->user->id}', '$new_data')");

			if(!$insert){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=comments&op=add'); }

			$id = $this->db->insert_id();

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_add_com']." #$id ".$this->lng['log_com'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['com_add_success'], 3, '?mode=admin&do=comments');
		}

		$data = array(
			"PAGE" => $this->lng['com_add_page_name'],
			"NEWS" => $this->news(),
			"TEXT" => "",
			"BB_PANEL" => $bb->bb_panel('bb-comment'),
			"BUTTON" => $this->lng['com_add_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/comments/com-add.html", $data);
	}

	private function edit(){

		if(!$this->core->is_access('sys_adm_comments_edit')){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng['e_403'], 2, '?mode=admin&do=comments'); }

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT nid, text_bb, `data`
									FROM `mcr_comments`
									WHERE id='$id'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=comments'); }

		$ar = $this->db->fetch_assoc($query);

		$data = json_decode($ar['data']);

		$bc = array(
			$this->lng['mod_name'] => ADMIN_URL,
			$this->lng['comments'] => ADMIN_URL."&do=comments",
			$this->lng['com_edit'] => ADMIN_URL."&do=comments&op=edit&id=$id"
		);

		$this->core->bc = $this->core->gen_bc($bc);

		$bb = $this->core->load_bb_class(); // Загрузка класса BB-кодов

		if($_SERVER['REQUEST_METHOD']=='POST'){

			$nid = intval(@$_POST['nid']);

			// Обработка описания +
			$text_bb = @$_POST['text'];

			$text_bb_trim = trim($text_bb);

			if(empty($text_bb_trim)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['com_empty'], 2, '?mode=admin&do=comments&op=add'); }

			$text_bb				= $this->db->HSC($text_bb);

			$text_html				= $bb->decode($text_bb);

			$safe_text_html			= $this->db->safesql($text_html);

			$text_bb				= $this->db->safesql($text_bb);

			$text_html_strip		= trim(strip_tags($text_html, "<img>"));

			if(empty($text_html_strip)){ $this->core->notify($this->core->lng["e_msg"], $this->lng['com_incorrect'], 2, '?mode=admin&do=comments&op=add'); }
			// Обработка описания -

			$new_data = array(
				"time_create" => $data->time_create,
				"time_last" => time()
			);

			$new_data = $this->db->safesql(json_encode($new_data));

			$update = $this->db->query("UPDATE `mcr_comments`
										SET nid='$nid', text_bb='$text_bb', text_html='$safe_text_html', `data`='$new_data'
										WHERE id='$id'");

			if(!$update){ $this->core->notify($this->core->lng["e_msg"], $this->core->lng["e_sql_critical"], 2, '?mode=admin&do=comments&op=edit&id='.$id); }

			// Последнее обновление пользователя
			$this->db->update_user($this->user);

			// Лог действия
			$this->db->actlog($this->lng['log_edit_com']." #$id ".$this->lng['log_com'], $this->user->id);
			
			$this->core->notify($this->core->lng["e_success"], $this->lng['com_edit_success'], 3, '?mode=admin&do=comments');
		}

		$data = array(
			"PAGE" => $this->lng['com_edit_page_name'],
			"NEWS" => $this->news($ar['nid']),
			"TEXT" => $this->db->HSC($ar['text_bb']),
			"BB_PANEL" => $bb->bb_panel('bb-comment'),
			"BUTTON" => $this->lng['com_edit_btn']
		);

		return $this->core->sp(MCR_THEME_MOD."admin/comments/com-add.html", $data);
	}

	public function content(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'list';

		switch($op){
			case 'add':		$content = $this->add(); break;
			case 'edit':	$content = $this->edit(); break;
			case 'delete':	$this->delete(); break;

			default:		$content = $this->comment_list(); break;
		}

		return $content;
	}
}

?>