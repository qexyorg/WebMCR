<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

$authfile = (!$this->user->is_auth) ? "unauth" : "auth";

echo $this->sp(MCR_THEME_PATH."blocks/profile/$authfile.html");

?>