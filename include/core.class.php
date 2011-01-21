<?php
	
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
		
		public static function timer() {
			list($usec, $sec) = explode(" ", microtime());
		    return ((float)$usec + (float)$sec);
		}
	}
	
?>
