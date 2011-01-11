<?php

	// Very simple script, handles scheduled tasks
	
	require_once "include/autoload.func.php";
	
	define("__START__", Core::timer()); // Define as constant so it can be accessed in objects at runtime
	
	run::Cron();

?>