<?php
	
	/**
	* TODO:
	*  - Sort torrents by airDate before writing to xml -> consistent order is needed for torrent client
	*		to see that nothing's changed between feed refreshes. Now ordered by seeders == not consistent.
	*  - Automatic plugin loading
	*  - General code cleanup, writing comments and making it more robust
	*/
	
	define("DEBUG", true);
	set_time_limit(2);
	
	require_once "include/autoload.func.php";
	
	define("__START__", Core::timer()); // Define as constant so it can be accessed in objects at runtime
	
	new Run();

?>