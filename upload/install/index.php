<?php

define('MCR', '');

require_once("../system.php");

$core->def_header = $core->sp(MCR_ROOT."install/theme/header.html");

$mode = (isset($_GET['mode'])) ? $_GET['mode'] : 'step_1';

if(!$core->config->main['install'] && $mode!='finish'){ $core->notify('','', 3, 'install/?mode=finish'); }

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
		$content = $core->sp(MCR_ROOT."install/theme/finish.html");
	break;

	default:
		$content = $core->notify('Установка!', 'Шаг #1', 4, 'install/?mode=step_1');
	break;
}

function load_left_block($core, $mode){
	$array = array(
		"step_1" => "Шаг #1",
		"step_2" => "Шаг #2",
		"step_3" => "Шаг #3",
		"settings" => "Настройки",
		"finish" => "Завершение установки"
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

	return $core->sp(MCR_ROOT."blocks/1_notify.php").$core->sp(MCR_ROOT."install/theme/left-block.html", $data);
}

$data_global = array(
	"CONTENT"		=> $content,
	"TITLE"			=> $core->title,
	"L_BLOCKS"		=> load_left_block($core, $mode),
	"HEADER"		=> $core->header,
	"DEF_HEADER"	=> $core->def_header,
	"CFG"			=> $core->config->main,
	"ADVICE"		=> '',//$core->advice(),
	"MENU"			=> '',//$core->menu->_list(),
	"BREADCRUMBS"	=> $core->bc,
	"SEARCH"		=> ''
);

// Write global template
echo $core->sp(MCR_THEME_PATH."global.html", $data_global);


?>