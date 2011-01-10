<?php
	
	/**
	* TODO:
	*  - When someone uploads an older episode again, it will come at the top of the feed. But since
	*     it's an older episode, this behavior is not wanted. ==> http://epguides.com/ parsing
	*  - Give different feeds different titles
	*  - General code cleanup, writing comments and making it more robust
	*/
	
	define(DEBUG, true);
	set_time_limit(2);
	
	require_once "include/autoload.func.php";
	
	define("__START__", Core::timer()); // Define as constant so it can be accessed in objects at runtime
	
	new Run();
	
	Core::debugLog("page generating took <b>". sprintf("%f", Core::timer() - __START__) ."s</b>");

?>