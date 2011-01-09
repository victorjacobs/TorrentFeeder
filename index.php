<?php
	
	define(DEBUG, true);
	set_time_limit(2);
	
	require_once "include/autoload.func.php";
	
	define("__START_TIME", Core::timer());
	
	new Run();
	
	Core::debugLog("page generating took <b>". Core::timer() - __START_TIME ."s</b>");

?>