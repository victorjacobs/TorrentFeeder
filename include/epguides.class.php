<?php
	
	/**
	* EpGuides is a wrapper around the same-named site which is used to get proper titles
	* and air dates for episodes so feed entries look nicer.
	* Luckily EpGuides' response time is a lot lower
	*/
	
	class EpGuides {
		private $sitePath, $data, $episodes;
		
		public function __construct($sitePath) {
			$this->sitePath;
			
			$this->parseData($arr = file_get_contents("http://epguides.com/". $sitePath));
		}
		
		public function lookup($episodeID) {
			if(is_null($this->episodes[$episodeID])) return false
			
			return $this->episodes[$episodeID];
		}
		
		private function parseData($data) {
			$dom = new DOMDocument;
			$dom->preserveWhiteSpace = false;
			@$dom->loadHTML(preg_replace("/&#?[a-z0-9]{2,8};/i", "", str_replace("&nbsp;", " ", $data)));
			
			// Run through the data line per line, needed information is enclosed in <pre> tags
			$rawText = array_map("trim", explode("\n", trim($dom->getElementsByTagName("pre")->item(0)->nodeValue)));
			
			// Useful information is in the lines that start with a number
			foreach($rawText as $line) {
				if(preg_match("$[0-9]{1}$", substr($line, 0, 1))) {
					preg_match("$[0-9]{1,2}-[0-9]{1,2}$", $line, $temp);
					list($season, $episode) = explode("-", $temp[0]);
					
					// If strlen($season) < 2, pad with zero
					$season = (strlen($season) == 1 ? "0". $season : $season);
					$episodeID = "S". $season ."E". $episode;
					
					preg_match('$[0-9]{2}/[a-z]{3}/[0-9]{2}$i', $line, $temp);
					$airDate = $temp[0];
					
					$temp = explode("   ", $line);
					$episodeTitle = end($temp);
					
					$this->episodes[$episodeID] = array("airDate" => date("r", strtotime(str_replace("/", "", strtoupper($airDate)))), 
														"title" => trim(preg_replace('$\[[a-z]+\]$i', "", $episodeTitle)));
				}
			}
		}
	}

?>