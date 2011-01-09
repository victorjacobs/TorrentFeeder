<?php

	class TorrentFetcher {
		private $trackers = array("thepiratebay" => "http://www.thepiratebay.org/search/%s/%p/99/0",
									"placeholder" => "http://yaddayadda/");
		private $tracker;
		
		/**
		* TorrentFetcher::__construct
		*  Sets up the TorrentFetcher object. The method checks whether $tracker exists and sets
		*  self::tracker to the tracker name. This variable is then used in other methods.
		*/
		
		public function __construct($tracker) {
			if(!isset($this->trackers[$tracker])) {
				// Default to thepiratebay
				$tracker = "thepiratebay";
				Core::warning('invalid $tracker argument, going to default');
			}
			
			$this->tracker = $tracker;
		}
		
		/**
		* TorrentFetcher::lookup
		*  Goes out to fetch html from tracker, parses it and returns a pretty assoc array with
		*  valid torrents.
		*  It has following structure:
		*		"id" => 		Episode id
		*		"fileSize" => 	Size of torrent
		*		"link" =>		Link to torrent file
		*		"date" =>		Date uploaded
		*		"fileName" =>	Name of the file
		*/
		
		public function lookup($lookup_string, $numPages = 1) {
			// Firstly replace the search string in placeholder url
			$query_string = str_replace("%s", str_replace(" ", "%20", $lookup_string), $this->trackers[$this->tracker]);
			
			// Fetch multiple pages
			if($numPages != 1) {
				$data = array();
				
				for($i = 1; $i <= $numPages; $i++) {
					$temp = $this->fetchData($query_string, $i);
					$data = array_merge($data, $temp);
				}
				
			}else{
				$data = $this->fetchData($query_string, 1);
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
			usort($validTorrents, array("TorrentFetcher", "compareEpisodes"));
			
			Core::debugLog($validTorrents);
		}
		
		/**
		* TorrentFetcher::fetchData
		*  Wrapper around torrentFetcher::processRawDataDOM to account for multiple tries, alternative pages
		*  and error handling
		*/
		private function fetchData($query_string, $page) {
			$i = 0;
			while(is_null($data = $this->processRawDataDOM(@file_get_contents(str_replace("%p", $page, $query_string))))
				&& $i < Configuration::TRACKER_FETCH_RETRY) $i++;
			
			if($i != 0 && $i != Configuration::TRACKER_FETCH_RETRY) Core::warning("fetching tracker took $i tries");
			if(is_null($data)) Core::fatalError("error fetching tracker (tried $i times)");
			
			return $data;
		}
		
		/**
		* TorrentFetcher::compareEpisodes
		*  Compares episodes, used for usort
		*/
		public static function compareEpisodes($a, $b) {
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
		}
		
		/**
		* torrentFetcher::processRawDataDOM
		*  Processes $data through DOM parsing. Includes include/plugin/$tracker.plugin.php for
		*  site-specific parsing.
		*/
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
		
		/**
		* torrentFetcher::isOnline
		*  Placeholder for checking whether a tracker is online or not
		*/
		public static function isOnline($tracker) {
			
		}
		
	}

?>