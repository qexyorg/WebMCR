<?php

define('MCR', true);

session_save_path(dirname(dirname(__FILE__)).'/uploads/tmp');
if(!session_start()){ session_start(); }

require_once('./install.class.php');

$install = new install();

$data = array(
	'NOTIFY' => $install->get_notify(),
);

echo $install->sp('global.html', $data);


?>