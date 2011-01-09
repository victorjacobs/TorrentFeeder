<?php

	class TorrentFetcher {
		private $trackers = array("thepiratebay" => "http://www.thepiratebay.org/search/%s/%p/99/0",
									"placeholder" => "http://yaddayadda/");
		private $tracker;
		
		public function __construct($tracker) {
			if(!isset($this->trackers[$tracker])) {
				// Default to thepiratebay
				$tracker = "thepiratebay";
				Core::warning('invalid $tracker argument, going to default');
			}
			
			$this->tracker = $tracker;
		}
		
		public function lookup($lookup_string, $numPages = 1) {
			$query_string = str_replace("%s", str_replace(" ", "%20", $lookup_string), $this->trackers[$this->tracker]);
			
			if($numPages != 1) {
				
			}else{
				$i = 0;
				while(is_null($data = $this->processRawDataDOM(file_get_contents(str_replace("%p", 1, $query_string))))
					&& $i <= 10) $i++;
				
				if(is_null($data)) Core::fatalError("error fetching tracker (tried $i times)");
			}
			
			
			//$data = $this->processRawDataDom(file_get_contents("cache"));
			
			// Remove all junk and doubles
			$ids = array();
			foreach($data as $torrent) {
				// Torrents are discarted when: no episode id, 150mb < size < 250 mb (note: HD?)
				// We also don't want anything with swesub, psp, ipod, zune in it
				if(!is_null($torrent["id"]) && $torrent['fileSize'] > 150 && $torrent['fileSize'] < 250
						&& !$ids[$torrent['id']] && !preg_match('$(swesub|psp|ipod|zune){1}$i', $torrent['fileName'])) {
					$validTorrents[] = $torrent;
					$ids[$torrent['id']] = true;
				}
			}
			
			// Sort
			usort($validTorrents, create_function('$a, $b', '
				$a = str_replace(array("S", "E"), "", strtoupper($a["id"]));
				$b = str_replace(array("S", "E"), "", strtoupper($b["id"]));
				
				// Compare season
				if((int)substr($a, 0, 2) < (int)substr($b, 0, 2)) {
					return 1;
				}elseif((int)substr($a, 0, 2) > (int)substr($b, 0, 2)) {
					return -1;
				}elseif((int)substr($a, 0, 2) == (int)substr($b, 0, 2)){
					// Compare episode
					if((int)substr($a, 2, 2) < (int)substr($b, 2, 2)){
						return 1;
					}elseif((int)substr($a, 2, 2) > (int)substr($b, 2, 2)){
						return -1;
					}else{
						return 0;
					}
				}
			'));
			
			Core::debugLog($validTorrents);
		}
		
		private function processRawDataDOM($data) {
			$dom = new DOMDocument;
			$dom->preserveWhiteSpace = false;
			
			// Supress warnings caused by non-validness + remove all html entities
			@$dom->loadHTML(preg_replace("/&#?[a-z0-9]{2,8};/i", "", str_replace("&nbsp;", " ", $data)));
			
			switch($this->tracker){
				case "thepiratebay":
					require_once "include/plugin/thepiratebay.plugin.php";
					
					return $output;
				break;
				
				default: return false;
			}
		}
		
		public static function is_online($tracker) {
			
		}
		
	}

?>