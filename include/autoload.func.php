<?php
	
	/*    This file is part of TorrentFeeder
	 *    Copyright (C) 2011  Victor Jacobs
	 *
	 *    TorrentFeeder is free software: you can redistribute it and/or modify
	 *    it under the terms of the GNU Affero General Public License as
	 *    published by the Free Software Foundation, either version 3 of the
	 *    License, or (at your option) any later version.
	 *
	 *    TorrentFeeder is distributed in the hope that it will be useful,
	 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
	 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 *    GNU Affero General Public License for more details.
	 *
	 *    You should have received a copy of the GNU Affero General Public License
	 *    along with TorrentFeeder.  If not, see <http://www.gnu.org/licenses/>.
	 */

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