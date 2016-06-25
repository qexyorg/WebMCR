<?php
define("DEBUG_PLT", microtime(true));
define('MCR', '');

require_once("./system.php");

$core->def_header = $core->sp(MCR_THEME_PATH."header.html");

$mode = (isset($_GET['mode'])) ? $_GET['mode'] : $core->cfg->main['s_dpage'];

if($core->cfg->main['install']){ $core->notify($core->lng['e_attention'], $core->lng['e_install'], 4, 'install/'); }

if($core->cfg->func['close'] && !$core->is_access('sys_adm_main')){
	if($core->cfg->func['close_time']<=0 || $core->cfg->func['close_time']>time()){
		$mode = ($mode=='auth') ? 'auth' : 'close';
	}
}

switch($mode){
	case 'news':
	case 'search':
	case 'auth':
	case 'register':
	case 'profile':
	case 'file':
	case 'restore':
	case 'ajax':
	case 'statics':
	case 'close':
		$content = $core->load_def_mode($mode);
	break;

	case '403':
		$core->title = $core->lng['t_403'];
		$content = $core->sp(MCR_THEME_PATH."default_sp/403.html");
	break;

	default:
		$content = $core->load_mode($mode);
	break;
}

$data_global = array(
	"CONTENT"		=> $content,
	"TITLE"			=> $core->title,
	"L_BLOCKS"		=> $core->load_def_blocks(),
	"HEADER"		=> $core->header,
	"DEF_HEADER"	=> $core->def_header,
	"CFG"			=> $core->cfg->main,
	"ADVICE"		=> $core->advice(),
	"MENU"			=> $core->menu->_list(),
	"BREADCRUMBS"	=> $core->bc,
	"SEARCH"		=> $core->search()
);

// Write global template
echo $core->sp(MCR_THEME_PATH."global.html", $data_global);

if(!$core->cfg->main['debug'] || !@$core->user->permissions->sys_debug){ exit; }

$data_debug = array(
	"PLT" => number_format(microtime(true)-DEBUG_PLT,3),
	"QUERIES" => $core->db->count_queries,
	"MEMORY_USAGE" => intval(memory_get_usage()/1024),
	"MEMORY_PEAK" => intval(memory_get_peak_usage()/1024),
	"BASE_ERROR" => $core->db->error(),
	"PHP_ERROR" => error_get_last()
);

echo $core->sp(MCR_THEME_PATH."debug.html", $data_debug);
?>
