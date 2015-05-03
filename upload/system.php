<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

ini_set("upload_max_filesize", "50M");
ini_set("post_max_size", "50M");

// System constants
define('PROGNAME', 'WebMCR Reloaded'. MCR);
define('VERSION', 'Alpha 0.15');
define('FEEDBACK', '<a href="http://webmcr.com" target="_blank">'.PROGNAME.'</a> &copy; 2013-'.date("Y").' Qexy'); 
define('MCR_ROOT', dirname(__FILE__).'/');
define('MCR_MODE_PATH', MCR_ROOT.'modules/');
define('MCR_TOOL_PATH', MCR_ROOT.'engine/');
define('MCR_SIDE_PATH', MCR_ROOT.'blocks/');
define('MCR_LANG_PATH', MCR_ROOT.'language/');
define('MCR_UPL_PATH', MCR_ROOT.'uploads/');
define('MCR_SKIN_PATH', MCR_UPL_PATH.'skins/');
define('MCR_CLOAK_PATH', MCR_UPL_PATH.'cloaks/');

session_save_path(MCR_UPL_PATH.'tmp');
if(!session_start()){ session_start(); }

// Set default charset
header('Content-Type: text/html; charset=UTF-8');

// Load core
require_once(MCR_TOOL_PATH.'core.class.php');

// Create new core object
$core = new core();

// Debug
ini_set("display_errors", $core->config->main['debug']);
$warn_type = ($core->config->main['debug']) ? E_ALL : 0;
error_reporting($warn_type);

$base_url = ($core->config->main['install']) ? $core->base_url() : $core->config->main['s_root'];

// System constants
define('MCR_LANG', $core->config->main['s_lang']);
define('MCR_THEME_PATH', MCR_ROOT.'themes/'.$core->config->main['s_theme'].'/');
define('MCR_THEME_MOD', MCR_THEME_PATH.'modules/');
define('MCR_THEME_BLOCK', MCR_THEME_PATH.'blocks/');
define('BASE_URL', $base_url);
define('ADMIN_URL', BASE_URL.'?mode=admin');
define('STYLE_URL', BASE_URL.'themes/'.$core->config->main['s_theme'].'/');
define('UPLOAD_URL', BASE_URL.'uploads/');

// Csrf security validation
$core->csrf_check();
?>
