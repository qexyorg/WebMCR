<?php

define("DEBUG_PLT", microtime(true));
define('MCR', '');
define('ENABLE', true); // Вкл./Выкл. планировщик (true|false)
define('DAYS', 10); // Максимальное время жизни логов (в днях)
define('MCR_ROOT', str_replace('cron', '', dirname(__FILE__)));
define('MCR_TOOL_PATH', MCR_ROOT.'engine/');
define('MCR_CONF_PATH', MCR_ROOT.'configs/');

if(!ENABLE){ exit('DISABLED'); }

require_once(MCR_TOOL_PATH.'config.class.php');

$cfg = new config();

require_once(MCR_TOOL_PATH.'db/'.$cfg->db['backend'].'.class.php');

$db = new db($cfg);

$expire = time()-(3600*24*DAYS);

$lt = $cfg->db['tables']['logs'];
$fl = $lt['fields'];

$delete = $db->remove_fast($lt['name'], "`date`<'$expire'");

if(!$delete){ exit('Ошибка запроса! #'.__LINE__); }

echo '<p>SUCCESS!</p>';
echo '<p>DELETED ROWS: '.$db->affected_rows().'</p>';

// Script load time
echo '<p>PAGE LOAD TIME: '.number_format(microtime(true)-DEBUG_PLT,3).'</p>';

?>