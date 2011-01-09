<?php

	class Run{
		
		public function __construct() {

			
			$th = new TorrentFetcher("thepiratebay");
			
			$th->lookup("how i met your mother", 2);
			
			$fh = new FeedHandler;
			
		}
		
	}

?>