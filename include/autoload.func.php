<?php

	function __autoload($class_name) {
		if($class_name == "parsePluginBase") {
			require_once "include/plugin/plugin.base.class.php";
			return;
		}
		
		// Do something with file_exists for handling plugins
		$class_file = "include/". strtolower(str_replace("_", ".", trim($class_name))) . ".class.php";
		
		// Plugins
		if(!file_exists($class_file) && substr_count($class_name, "ParserPlugin") == 1) {
			require_once "include/plugin/". strtolower(str_replace("ParserPlugin", "", $class_name)) . ".plugin.class.php";
		} else {
			require_once $class_file;
		}
		
		Core::debugLog("loading $class_name");
	}

?>