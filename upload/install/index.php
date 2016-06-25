<?php

define('MCR', '');

require_once("../system.php");

require_once(MCR_LANG_DIR."install.php");

$core->lng_m = $lng;

$core->def_header = $core->sp(MCR_ROOT."install/theme/header.html");

$mode = (isset($_GET['mode'])) ? $_GET['mode'] : 'step_1';

if(!$core->cfg->main['install'] && $mode!='finish'){ $core->notify('','', 3, 'install/?mode=finish'); }

switch($mode){
	case 'step_1':
	case 'step_2':
	case 'step_3':
	case 'settings':

		require_once(MCR_ROOT.'install/'.$mode.'.php');
		$module = new module($core);
		$content = $module->content();

	break;

	case 'finish':
		$core->bc = $core->gen_bc(array($lng['mod_name'] => ''));
		$content = $core->sp(MCR_ROOT."install/theme/finish.html");
		if(isset($_SESSION['step_1'])){ unset($_SESSION['step_1']); }
		if(isset($_SESSION['step_2'])){ unset($_SESSION['step_2']); }
		if(isset($_SESSION['step_3'])){ unset($_SESSION['step_3']); }
		if(isset($_SESSION['settings'])){ unset($_SESSION['settings']); }
	break;

	default:
		$content = $core->notify($lng['mod_name'], 'Шаг #1', 4, 'install/?mode=step_1');
	break;
}

function load_left_block($core, $mode){
	$array = array(
		"step_1" => $core->lng_m['step_1'],
		"step_2" => $core->lng_m['step_2'],
		"step_3" => $core->lng_m['step_3'],
		"settings" => $core->lng_m['settings'],
		"finish" => $core->lng_m['finish']
	);

	ob_start();
	foreach($array as $key => $value) {
		if($mode==$key){
			echo '<li class="active"><a href="javascript://">'.$value.'</a></li>';
		}else{
			echo '<li class="muted">'.$value.'</li>';
		}
	}
	$data['ITEMS'] = ob_get_clean();

	return $core->sp(MCR_ROOT."install/theme/left-block.html", $data);
}

$data_global = array(
	"CONTENT"		=> $content,
	"TITLE"			=> $core->title,
	"L_BLOCKS"		=> load_left_block($core, $mode).$core->load_def_blocks(),
	"HEADER"		=> $core->header,
	"DEF_HEADER"	=> $core->def_header,
	"CFG"			=> $core->cfg->main,
	"ADVICE"		=> '',//$core->advice(),
	"MENU"			=> '',//$core->menu->_list(),
	"BREADCRUMBS"	=> $core->bc,
	"SEARCH"		=> ''
);

// Write global template
echo $core->sp(MCR_THEME_PATH."global.html", $data_global);


?>