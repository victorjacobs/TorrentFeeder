<?php

	class Run{
		
		public function __construct() {
			$th = new TorrentFetcher("thepiratebay");
			
			$results = $th->lookup("futurama", 2);
			
			$fh = new FeedHandler;
			
			$fh->addItems($results['sd']);
			
			$fh->writeOutDOM("test.xml");
		}
		
		public static function cron() {
			// Read out what feeds should be created from xml
			
		}
		
	}

?>