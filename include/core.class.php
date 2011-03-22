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
	
	class Core {
		
		// Log methods
		public static function debugLog($data) {
			if(!DEBUG) return;
			
			if(is_object($data) && is_a($data, "DOMNodeList")) {
				$out = "<pre>DEBUG: ";
				
				for($i = 0; $i < $data->length; $i++){
					$out .= "<b>ID: ". $i . " ". $data->item($i)->nodeName ."</b>";
					$out .= $data->item($i)->nodeValue;
				}
				
				$out .= "\n</pre>";
				
				// php_sapi_name() returns the name of api between php and output
				if(php_sapi_name() == "cli") $out = strip_tags($out);
				
				echo $out;
			} elseif(!is_string($data)) {
				var_dump($data);
			} else {
				self::logLine("DEBUG: ". ucfirst($data));
			}
		}
		
		public static function fatalError($data) {
			self::logLine("FATAL ERROR: ". $data);
			die;
		}
		
		public static function warning($data) {
			// If not debugging, ignore warnings
			
			if(DEBUG) self::logLine("WARNING: ". $data);
		}
		
		private static function logLine($string) {
			$backtrace = debug_backtrace();
			list(, $file) = explode("htdocs", $backtrace[1]["file"]);
			
			// If called in autoload function, __START__ is not yet defined, just use 0
			$runTime = (!defined("__START__") ? 0 : Core::timer() - __START__);
			
			$out = "<pre><b>[". sprintf("%f", $runTime) ."]</b> ". ucfirst($string) .". Triggered in file <b>".
					$file . "</b> on line <b>". $backtrace[1]["line"] ."\n</b></pre>";
			
			unset($backtrace);
			if(php_sapi_name() == "cli") $out = strip_tags($out);
			echo $out;
			
			return;
		}
		
		public static function log($string) {
			$runTime = (!defined("__START__") ? 0 : Core::timer() - __START__);
			if (php_sapi_name() == "cli") {
				echo "[". sprintf("%f", $runTime) ."] INFO: ". ucfirst($string) ." \n";
			} else {
				echo "<pre><b>[". sprintf("%f", $runTime) ."]</b> INFO: ". ucfirst($string) . "</pre>";
			}
		}
		
		public static function timer() {
			list($usec, $sec) = explode(" ", microtime());
		    return ((float)$usec + (float)$sec);
		}
	}
	
?>
