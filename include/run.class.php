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

	class Run{
		
		public function __construct() {
			$foo = new TorrentFetcher("thepiratebay");
			var_dump($foo->lookup("how i met your mother", "howimetyourmother", 1));
		}
		
		public static function cron() {
			Core::debugLog("starting cron task");
			// Read out what feeds should be created from xml
			$dom = new DOMDocument;
			$dom->preserveWhiteSpace = false;
			$dom->loadXML(file_get_contents("feeds.xml"));
			
			// Create TorrentFetcher and three FeedHandlers (one that writes every feed seperately and three for aggregate feed)
			$th = new TorrentFetcher("thepiratebay");
			$fh = new FeedHandler;
			
			// Assume it is regenerate time, so just run feed when called
			// Note: we do everything in one loop. Saves time and memory
			//  Sidenote: gotta love how PHP uses Iterators in foreach
			foreach($dom->getElementsByTagName("feed") as $feed) {
				Core::debugLog("starting feed ". $feed->attributes->getNamedItem("name")->value);
				
				foreach($feed->childNodes as $setting) {
					switch($setting->nodeName) {
						case "searchString": $settings["searchString"] = $setting->nodeValue; break;
						case "feedPath": $settings["feedPath"] = $setting->nodeValue; break;
						case "epGuidesPath": $settings["epGuidesPath"] = $setting->nodeValue; break;
					}
				}
				
				// Lookup
				Core::debugLog("starting TorrentHandler::lookup");
				$results = $th->lookup($settings["searchString"], $settings['epGuidesPath'], Configuration::NUM_RESULT_PAGES);
				
				// Write feed
				$path = Configuration::FEEDS_DIR . $settings["feedPath"];
				
				
				$fh->setupDOM("TorrentFeeder v". Configuration::VERSION . " - ". $feed->attributes->getNamedItem("name")->value . 
								" - Standard Definition");
				$fh->addItems($results['sd']);
				
				Core::debugLog("writing feed to ". $path . "sd.xml");
				$fh->writeOutDOM($path . "sd.xml");
				
				
				$fh->setupDOM("TorrentFeeder v". Configuration::VERSION . " - ". $feed->attributes->getNamedItem("name")->value . 
								" - High Definition");
				$fh->addItems($results['hd']);
				
				Core::debugLog("writing feed to ". $path . "hd.xml");
				$fh->writeOutDOM($path . "hd.xml");
			}
			
			Core::debugLog("cron task completed successfully!");
		}
		
	}

?>