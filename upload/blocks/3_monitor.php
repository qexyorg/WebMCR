<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

if($this->is_access("sys_monitoring")){
	echo $this->sp(MCR_THEME_PATH."blocks/monitor/main.html");
}

?>