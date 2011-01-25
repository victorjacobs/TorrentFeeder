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
	
	/**
	* EpGuides is a wrapper around the same-named site which is used to get proper titles
	* and air dates for episodes so feed entries look nicer.
	* Luckily EpGuides' response time is a lot lower
	*/
	
	class EpGuides {
		private $sitePath, $data, $episodes;
		
		public function __construct($sitePath) {
			$this->sitePath;
			
			if(!$this->parseData(@file_get_contents("http://epguides.com/". $sitePath))) Core::warning("epGuides fetch failed");
		}
		
		public function lookup($episodeID) {
			// Return null, normally array_merge will behave nicely when merging an array with null
			if(is_null($this->episodes[$episodeID])) return null;
			
			return $this->episodes[$episodeID];
		}
		
		private function parseData($data) {
			if(is_null($data)) return false;
			
			$dom = new DOMDocument;
			$dom->preserveWhiteSpace = false;
			@$dom->loadHTML(preg_replace("/&#?[a-z0-9]{2,8};/i", "", str_replace("&nbsp;", " ", $data)));
			
			if($dom->getElementsByTagName("pre")->length == 0) {
				Core::warning("Something weird has come up in the EpGuides DOM, so let's not use it!");
				return false;
			}
			
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
			
			return true;
		
		}
	}

?>