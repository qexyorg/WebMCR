<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

if($this->is_access("sys_adm_main")){
	echo $this->sp(MCR_THEME_PATH."blocks/top/main.html");
}

?>