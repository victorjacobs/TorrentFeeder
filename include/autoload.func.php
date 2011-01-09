<?php

	function __autoload($class_name) {
		$class_file = strtolower(str_replace("_", ".", trim($class_name))) . ".class.php";
		
		$path = "include/" . $class_file;
		
		require_once $path;
		
	}

?>