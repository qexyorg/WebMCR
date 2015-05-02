<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $core, $db, $config, $user, $lng;

	public function __construct($core){
		$this->core = $core;
		$this->db	= $core->db;
		$this->config = $core->config;
		$this->user	= $core->user;
		$this->lng	= $core->lng;

		$this->core->title = $this->lng['t_admin'];

		$bc = array(
			$this->lng['t_admin'] => BASE_URL."?mode=admin"
		);

		$this->core->bc = $this->core->gen_bc($bc);
	}

	public function content(){
		if(!$this->core->is_access('sys_adm_main')){ $this->core->notify('403', $this->lng['e_403']); }

		$do = (isset($_GET['do'])) ? $_GET['do'] : 'main';

		switch($do){
			case 'news':
				if(!$this->core->is_access('sys_adm_news')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/news.class.php');
			break;

			case 'news_cats':
				if(!$this->core->is_access('sys_adm_news_cats')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/news_cats.class.php');
			break;

			case 'news_views':
				if(!$this->core->is_access('sys_adm_news_views')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/news_views.class.php');
			break;

			case 'news_votes':
				if(!$this->core->is_access('sys_adm_news_votes')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/news_votes.class.php');
			break;

			case 'comments':
				if(!$this->core->is_access('sys_adm_comments')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/comments.class.php');
			break;

			case 'menu':
				if(!$this->core->is_access('sys_adm_menu')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/menu.class.php');
			break;

			case 'menu_adm':
				if(!$this->core->is_access('sys_adm_menu_adm')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/menu_adm.class.php');
			break;

			case 'menu_groups':
				if(!$this->core->is_access('sys_adm_menu_groups')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/menu_groups.class.php');
			break;
			
			case 'menu_icons':
				if(!$this->core->is_access('sys_adm_menu_icons')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/menu_icons.class.php');
			break;
			
			case 'users':
				if(!$this->core->is_access('sys_adm_users')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/users.class.php');
			break;
			
			case 'groups':
				if(!$this->core->is_access('sys_adm_groups')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/groups.class.php');
			break;
			
			case 'permissions':
				if(!$this->core->is_access('sys_adm_permissions')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/permissions.class.php');
			break;
			
			case 'statics':
				if(!$this->core->is_access('sys_adm_statics')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/statics.class.php');
			break;
			
			case 'info':
				if(!$this->core->is_access('sys_adm_info')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/info.class.php');
			break;
			
			case 'settings':
				if(!$this->core->is_access('sys_adm_settings')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/settings.class.php');
			break;
			
			case 'monitoring':
				if(!$this->core->is_access('sys_adm_monitoring')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/monitoring.class.php');
			break;
			
			case 'modules':
				if(!$this->core->is_access('sys_adm_modules')){ $this->core->notify('403', $this->lng['e_403']); }
				require_once(MCR_MODE_PATH.'admin/modules.class.php');
			break;

			default:
				require_once(MCR_MODE_PATH.'admin/panel_menu.class.php');
			break;
		}
		
		$submodule = new submodule($this->core);
		
		$content = $submodule->content();

		ob_start();

		echo $content;

		return ob_get_clean();
	}
}

?>