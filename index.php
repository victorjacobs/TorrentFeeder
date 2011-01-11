<?php
	
	/**
	* TODO:
	*  - Plugin hierarchy!!!
	*  - Give different feeds different titles
	*  - General code cleanup, writing comments and making it more robust
	*  - Add time codes in debug messages
	*  - It's not because we get a http response, we get any useful stuff
	*/
	
	define(DEBUG, true);
	set_time_limit(2);
	
	require_once "include/autoload.func.php";
	
	define("__START__", Core::timer()); // Define as constant so it can be accessed in objects at runtime
	
	new Run();
	
	Core::debugLog("page generating took <b>". sprintf("%f", Core::timer() - __START__) ."s</b>");

?>