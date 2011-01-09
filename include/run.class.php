<?php

	class Run{
		
		public function __construct() {

			
			$th = new TorrentFetcher("thepiratebay");
			
			$th->lookup("family guy");
			
			$fh = new FeedHandler;
			
		}
		
	}

?>