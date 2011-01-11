<?php
	
	class Core {
		
		// Log methods
		public static function debugLog($data) {
			if(!DEBUG) return;
			
			if(is_object($data) && is_a($data, "DOMNodeList")) {
				echo "<pre>DEBUG: ";
				
				for($i = 0; $i < $data->length; $i++){
					echo "<b>ID: ". $i . " ". $data->item($i)->nodeName ."</b>";
					echo $data->item($i)->nodeValue;
				}
				
				echo "</pre>";
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
			
			echo "<pre><b>[". sprintf("%f", $runTime) ."]</b> ". ucfirst($string) .". Triggered in file <b>".
					$file . "</b> on line <b>". $backtrace[1]["line"] ."</b></pre>";
			
			unset($backtrace);
			return;
		}
		
		public static function timer() {
			list($usec, $sec) = explode(" ", microtime());
		    return ((float)$usec + (float)$sec);
		}
	}
	
?>
