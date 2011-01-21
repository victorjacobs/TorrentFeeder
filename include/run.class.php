<?php

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
			$fhAggregateSD = new FeedHandler;
			$fhAggregateSD->setupDOM("TorrentFeeder v". Configuration::VERSION ." - All - Standard Definition");
			$fhAggregateHD = new FeedHandler;
			$fhAggregateHD->setupDOM("TorrentFeeder v". Configuration::VERSION ." - All - High Definition");
			
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
				
				// Lookup, just do one page for now, I don't have all day
				Core::debugLog("starting TorrentHandler::lookup");
				$results = $th->lookup($settings["searchString"], $settings['epGuidesPath'], Configuration::NUM_RESULT_PAGES);
				
				// Write feed
				$path = Configuration::FEEDS_DIR . $settings["feedPath"];
				
				
				$fh->setupDOM("TorrentFeeder v". Configuration::VERSION . " - ". $feed->attributes->getNamedItem("name")->value . 
								" - Standard Definition");
				$fh->addItems($results['sd']);
				$fhAggregateSD->addItems($results['sd']);
				
				Core::debugLog("writing feed to ". $path . "sd.xml");
				$fh->writeOutDOM($path . "sd.xml");
				
				$fh->setupDOM("TorrentFeeder v". Configuration::VERSION . " - ". $feed->attributes->getNamedItem("name")->value . 
								" - High Definition");
				$fh->addItems($results['hd']);
				$fhAggregateHD->addItems($results['hd']);
				
				Core::debugLog("writing feed to ". $path . "hd.xml");
				$fh->writeOutDOM($path . "hd.xml");
			}
			
			// Write aggregate feeds
			Core::debugLog("writing aggregate feeds");
			$fhAggregateSD->writeOutDOM(Configuration::FEEDS_DIR ."all/sd.xml");
			$fhAggregateHD->writeOutDOM(Configuration::FEEDS_DIR ."all/hd.xml");
		}
		
	}

?>