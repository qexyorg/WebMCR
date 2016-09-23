<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $cfg, $user, $lng;

	public function __construct($core){
		$this->core		= $core;
		$this->db		= $core->db;
		$this->cfg		= $core->cfg;
		$this->user		= $core->user;
		$this->lng		= $core->lng_m;

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=news"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	private function get_likes($vote, $id, $likes, $dislikes){
		if(intval($vote)===0){ return; }
		$data = array(
			"ID" => intval($id),
			"LIKES" => intval($likes),
			"DISLIKES" => intval($dislikes)
		);

		return $this->core->sp(MCR_THEME_MOD."news/new-like.html", $data);
	}

	private function get_comments($discus, $count){
		if(intval($discus)<=0){ return; }

		$data = array(
			"COMMENTS" => intval($count)
		);

		return $this->core->sp(MCR_THEME_MOD."news/new-comments.html", $data);

	}

	private function get_admin($id, $attach){
		if(!$this->core->is_access('sys_adm_news')){ return; }

		$data = array(
			'ID' => $id,
			'ATTACH' => ($attach==1) ? $this->lng['unattach'] : $this->lng['attach']
		);

		return $this->core->sp(MCR_THEME_MOD."news/new-admin.html", $data);
	}

	private function news_array($cid=false){

		$start		= $this->core->pagination($this->cfg->pagin['news'], 0, 0); // Set start pagination
		$end		= $this->cfg->pagin['news']; // Set end pagination

		$where		= "";

		if($cid!==false){ $where .= "WHERE `n`.cid='$cid'"; }

		$query = $this->db->query("SELECT `n`.id, `n`.cid, `n`.title, `n`.text_html_short, `n`.vote, `n`.discus, `n`.uid, `n`.`data`, `n`.`attach`,
										`c`.title AS `category`,
										COUNT(DISTINCT `cm`.id) AS `comments`, COUNT(DISTINCT `v`.id) AS `views`, COUNT(DISTINCT `l`.id) AS `likes`, COUNT(DISTINCT `d`.id) AS `dislikes`
									FROM `mcr_news` AS `n`
									LEFT JOIN `mcr_news_cats` AS `c`
										ON `c`.id=`n`.cid
									LEFT JOIN `mcr_comments` AS `cm`
										ON `cm`.nid=`n`.id
									LEFT JOIN `mcr_news_views` AS `v`
										ON `v`.nid=`n`.id
									LEFT JOIN `mcr_news_votes` AS `l`
										ON `l`.nid=`n`.id AND `l`.`value`='1'
									LEFT JOIN `mcr_news_votes` AS `d`
										ON `d`.nid=`n`.id AND `d`.`value`='0'
									$where
									GROUP BY `n`.id
									ORDER BY `n`.`attach` DESC, `n`.id DESC
									LIMIT $start, $end");

		ob_start();

		if(!$query || $this->db->num_rows($query)<=0){ echo $this->core->sp(MCR_THEME_MOD."news/new-none.html"); return ob_get_clean(); }

		while($ar = $this->db->fetch_assoc($query)){
			$id = intval($ar['id']);
			$attach = intval($ar['attach']);

			$new_data	= array(
				"ID"		=> $id,
				"CID"		=> intval($ar['cid']),
				"TITLE"		=> $this->db->HSC($ar['title']),
				"CATEGORY"	=> $this->db->HSC($ar['category']),
				"TEXT"		=> $ar['text_html_short'],
				"UID"		=> intval($ar['uid']),
				"COMMENTS"	=> $this->get_comments($ar['discus'], $ar['comments']),
				"VIEWS"		=> intval($ar['views']),
				"DATA"		=> json_decode($ar['data'], true),
				"LIKES"		=> $this->get_likes($ar['vote'], $id, $ar['likes'], $ar['dislikes']),
				"ADMIN"		=> $this->get_admin($id, $attach),
			);

			$attached = ($attach==1) ? '-attached' : '';

			echo $this->core->sp(MCR_THEME_MOD."news/new-id".$attached.".html", $new_data);
		}

		if($cid!==false){
			$bc = array(
				$this->lng['mod_name'] => BASE_URL."?mode=news",
				$new_data['CATEGORY'] => BASE_URL."?mode=news&cid=$cid"
			);

			$this->core->bc = $this->core->gen_bc($bc);
		}

		return ob_get_clean();
	}

	private function news_list($cid=false){

		if(!$this->core->is_access('sys_news_list')){ $this->core->notify($this->core->lng['403'], $this->lng['access_denied'], 2, "?mode=403"); }

		$sql	= "SELECT COUNT(*) FROM `mcr_news`";
		$page	= "?mode=news&pid=";

		if($cid!==false){
			$cid = intval($cid);
			$sql = "SELECT COUNT(*) FROM `mcr_news` WHERE cid='$cid'";
			$page = "?mode=news&cid=$cid&pid=";
		}

		$query = $this->db->query($sql);

		if(!$query){ exit("SQL Error"); }

		$ar = $this->db->fetch_array($query);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->cfg->pagin['news'], $page, $ar[0]),
			"NEWS" => $this->news_array($cid)
		);

		return $this->core->sp(MCR_THEME_MOD."news/new-list.html", $data);

	}

	private function comments_array($nid=1){

		if(!$this->core->is_access('sys_comment_list')){
			return $this->core->sp(MCR_THEME_MOD."news/comments/comment-access.html");
		}

		$start		= $this->core->pagination($this->cfg->pagin['comments'], 0, 0); // Set start pagination
		$end		= $this->cfg->pagin['comments']; // Set end pagination

		$ctables	= $this->cfg->db['tables'];
		$ug_f		= $ctables['ugroups']['fields'];
		$us_f		= $ctables['users']['fields'];

		$query = $this->db->query("SELECT `c`.id, `c`.text_html, `c`.uid, `c`.`data`,
										`u`.`{$us_f['login']}`, `u`.`{$us_f['color']}`,
										`g`.`{$ug_f['color']}` AS `gcolor`
									FROM `mcr_comments` AS `c`
									LEFT JOIN `{$this->cfg->tabname('users')}` AS `u`
										ON `u`.`{$us_f['id']}`=`c`.uid
									LEFT JOIN `{$this->cfg->tabname('ugroups')}` AS `g`
										ON `g`.`{$ug_f['id']}`=`u`.`{$us_f['group']}`
									WHERE `c`.nid='$nid'
									ORDER BY `c`.id DESC
									LIMIT $start, $end");

		if(!$query || $this->db->num_rows($query)<=0){ return $this->core->sp(MCR_THEME_MOD."news/comments/comment-none.html"); }

		ob_start();
		
		while($ar = $this->db->fetch_assoc($query)){
			//$vote		= intval($ar['vote']);
			//$data		= json_decode($ar['data'], true);

			$act_del = $act_edt = $act_get = '';

			$id = intval($ar['id']);

			$data = array(
				"ID" => $id,
				"LNG" => $this->lng
			);

			if($this->core->is_access('sys_comment_del') || $this->core->is_access('sys_comment_del_all')){
				$act_del = $this->core->sp(MCR_THEME_MOD."news/comments/comment-act-del.html", $data);
			}

			if($this->core->is_access('sys_comment_edt') || $this->core->is_access('sys_comment_edt_all')){
				$act_edt = $this->core->sp(MCR_THEME_MOD."news/comments/comment-act-edt.html", $data);
			}

			if($this->user->is_auth){
				$act_get = $this->core->sp(MCR_THEME_MOD."news/comments/comment-act-get.html", $data);
			}

			$login = (is_null($ar[$us_f['login']])) ? 'Пользователь удален' : $this->db->HSC($ar[$us_f['login']]);

			$color = (!empty($ar[$us_f['color']])) ? $this->db->HSC($ar['color']) : $this->db->HSC($ar[$us_f['color']]);

			$com_data	= array(
				"ID"				=> $id,
				"NID"				=> $nid,
				"TEXT"				=> $ar['text_html'],
				"UID"				=> intval($ar['uid']),
				"DATA"				=> json_decode($ar['data'], true),
				"LOGIN"				=> $this->core->colorize($login, $color),
				"ACTION_DELETE"		=> $act_del,
				"ACTION_EDIT"		=> $act_edt,
				"ACTION_QUOTE"		=> $act_get
			);

			echo $this->core->sp(MCR_THEME_MOD."news/comments/comment-id.html", $com_data);
		}

		return ob_get_clean();
	
	}

	private function get_comment_form(){
		
		if(!$this->core->is_access('sys_comment_add')){ return; }

		$bb = $this->core->load_bb_class();

		$data['BB_PANEL'] = $bb->bb_panel('bb-comments');

		return $this->core->sp(MCR_THEME_MOD."news/comments/comment-form.html", $data);
	}

	private function comments_list($nid=1){

		$sql	= "SELECT COUNT(*) FROM `mcr_comments` WHERE nid='$nid'";
		$page	= "?mode=news&id=$nid&pid=";

		$query	= $this->db->query($sql);

		if(!$query){ return; }

		$ar = $this->db->fetch_array($query);

		$count = intval($ar[0]);

		$data = array(
			"PAGINATION" => $this->core->pagination($this->cfg->pagin['comments'], $page, $count),
			"COMMENTS" => $this->comments_array($nid),
			"COUNT" => $count,
			"COMMENTS_FORM"	=> $this->get_comment_form()
		);

		return $this->core->sp(MCR_THEME_MOD."news/comments/comment-list.html", $data);
	}

	private function update_views($nid){
		$query = $this->db->query("SELECT COUNT(*)
									FROM `mcr_news_views`
									WHERE nid='$nid' AND (uid='{$this->user->id}' OR ip='{$this->user->ip}')");

		if(!$query){ $this->core->notify($this->core->lng['e_sql_critical']); }

		$ar = $this->db->fetch_array($query);

		if(intval($ar[0])>0){ return false; }

		$time = time();

		$uid = ($this->user->id<=0) ? 1 : $this->user->id;

		$insert = $this->db->query("INSERT INTO `mcr_news_views`
									(nid, uid, ip, `time`)
									VALUES
									('$nid', '$uid', '{$this->user->ip}', '$time')");
		if(!$insert){ $this->core->notify($this->core->lng['e_sql_critical']); }

		// Последнее обновление пользователя
		$this->db->update_user($this->user);
		
		$_SESSION['views-new-'.$nid] = true;

		return true;
	}

	private function news_full(){
		
		if(!$this->core->is_access('sys_news_full')){ $this->core->notify($this->core->lng['403'], $this->lng['access_denied'], 2, "?mode=403"); }

		$id = intval($_GET['id']);

		$query = $this->db->query("SELECT `n`.id, `n`.cid, `n`.title, `n`.text_html, `n`.vote, `n`.discus, `n`.uid, `n`.`data`, `n`.`attach`,
										`c`.title AS `category`,
										COUNT(DISTINCT `v`.id) AS `views`,
										COUNT(DISTINCT `l`.id) AS `likes`,
										COUNT(DISTINCT `d`.id) AS `dislikes`
									FROM `mcr_news` AS `n`
									LEFT JOIN `mcr_news_cats` AS `c`
										ON `c`.id=`n`.cid
									LEFT JOIN `mcr_news_views` AS `v`
										ON `v`.nid=`n`.id
									LEFT JOIN `mcr_news_votes` AS `l`
										ON `l`.nid=`n`.id AND `l`.`value`='1'
									LEFT JOIN `mcr_news_votes` AS `d`
										ON `d`.nid=`n`.id AND `d`.`value`='0'
									WHERE `n`.id='$id'
									GROUP BY `n`.`id`");
		

		if(!$query || $this->db->num_rows($query)<=0){ $this->core->notify($this->core->lng['404'], $this->core->lng['t_404']); }

		$ar = $this->db->fetch_assoc($query);

		if(!isset($_SESSION['views-new-'.$id])){
			$this->update_views($id);
		}

		$comments = (intval($ar['discus']) === 1) ? $this->comments_list($id) : $this->core->sp(MCR_THEME_MOD."news/comments/comment-closed.html");;

		$attach = intval($ar['attach']);

		$new_data = array(
			"ID"			=> $id,
			"CID"			=> intval($ar['cid']),
			"TITLE"			=> $this->db->HSC($ar['title']),
			"TEXT"			=> $ar['text_html'],
			"UID"			=> intval($ar['uid']),
			"DATA"			=> json_decode($ar['data'], true),
			"CATEGORY"		=> $this->db->HSC($ar['category']),
			"VIEWS"			=> intval($ar['views']),
			"COMMENTS"		=> $comments,
			"LIKES"			=> $this->get_likes($ar['vote'], $id, $ar['likes'], $ar['dislikes']),
			"ADMIN"			=> $this->get_admin($id, $attach),
		);

		$bc = array(
			$this->lng['mod_name'] => BASE_URL."?mode=news",
			$new_data["CATEGORY"] => BASE_URL."?mode=news&cid=".$new_data["CID"],
			$new_data["TITLE"] => ""
		);
		
		$this->core->bc = $this->core->gen_bc($bc);

		return $this->core->sp(MCR_THEME_MOD."news/new-full.html", $new_data);
	}

	public function content(){

		if(isset($_GET['id'])){
			$this->core->header .= $this->core->sp(MCR_THEME_MOD."news/header-full.html");

			$content = $this->news_full();

		}elseif(isset($_GET['cid'])){
			$this->core->header .= $this->core->sp(MCR_THEME_MOD."news/header-list.html");

			$content = $this->news_list($_GET['cid']);

		}else{
			$this->core->header .= $this->core->sp(MCR_THEME_MOD."news/header-list.html");

			$content = $this->news_list();

		}

		if($this->core->is_access('sys_adm_news')){
			$this->core->header .= $this->core->sp(MCR_THEME_MOD."news/header-admin.html");
		}

		return $content;
	}
}

?>