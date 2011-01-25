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
	
	/*
	 * FeedHandler builds up an RSS Feed using PHP's DOM functions
	*/
	
	class FeedHandler{
		private $dom, $setupDone, $channelNode;
		
		public function addItems($itemsList) {
			if(!is_array($itemsList)) Core::fatalError("FeedHandler::addItems didn't get an array as input");
			Core::debugLog("FeedHandler::addItems got an array with ". count($itemsList) ." items");
			
			foreach($itemsList as $item) {
				// Create some cleaner titles etc
				if(!is_null($item["title"])) {
					$title = $item['id'] . " - " . $item["title"];
				} else {
					$title = $item['fileName'];
				}
				
				$this->addItem($title, $item["link"], !is_null($item["airDate"]) ? $item["airDate"] : $item["date"]);
			}
		}
		
		public function addItem($title, $link, $pubDate) {
			if(!$this->setupDone) Core::fatalError("DOM was not set up properly, first run FeedHandler::setupDOM");
			
			if(empty($title) || empty($link) || empty($pubDate))
				Core::fatalError("FeedHandler::addItem got wrong arguments");
			
			// Create item element
			$item = $this->dom->createElement("item");
			$this->channelNode->appendChild($item);
			
			$attr = $this->dom->createElement("title", $title);
			$item->appendChild($attr);
			
			$attr = $this->dom->createElement("link", $link);
			$item->appendChild($attr);
			
			$attr = $this->dom->createElement("pubDate", $pubDate);
			$item->appendChild($attr);
		}
		
		public function setupDOM($title) {
			if($this->setupDone) {
				Core::warning("DOM was already set up");
				return;
			}
			
			Core::debugLog("setting up DOM with title '$title'");
			$this->dom = new DOMDocument('1.0', 'UTF-8');
			$this->dom->formatOutput = true;
			
			// Create root element
			$root = $this->dom->createElement('rss');
			$root->setAttribute("version", "2.0");
			// Append root element
			$this->dom->appendChild($root);
			
			// Create channel element and save for later use
			$channel = $this->dom->createElement('channel');
			$root->appendChild($channel);
			$this->channelNode = &$channel;
			
			// Title
			$head = $this->dom->createElement('title', $title);
			$channel->appendChild($head);
			
			// Description
			$head = $this->dom->createElement('description', 'v'. Configuration::VERSION);
			$channel->appendChild($head);
			
			// Link
			$head = $this->dom->createElement('link', "http://feed.victorjacobs.com");
			$channel->appendChild($head);
			
			// Language
			$head = $this->dom->createElement('language', 'en-us');
			$channel->appendChild($head);
			
			// Lastbuilddate
			$head = $this->dom->createElement('lastBuildDate', date('r'));
			$channel->appendChild($head);
			
			$this->setupDone = true;
		}
		
		private function readOutDOM() {
			// Next line will always fail because there is no DTD for rss as far as I know
			//if(!$this->dom->validate()) Core::warning("XML feed not valid");
			
			echo $this->dom->saveXML();
		}
		
		public function writeOutDOM($location) {
			// If dirname($location) doesn't exist, create it
			if(!file_exists(dirname($location))) {
				Core::debugLog(dirname($location) ." doesn't exist, gonna try to create it");
				mkdir(dirname($location));
			}
			
			if(($fh = fopen($location, "w")) === false) Core::fatalError("couldn't open $location for writing");
			
			fwrite($fh, $this->dom->saveXML());
			fclose($fh);
			
			// After writeOut, we recreate the DOMDocument for new use
			Core::debugLog("resetting DOM");
			unset($this->dom, $this->channelnode);
			$this->setupDone = false;
			
			return true;
		}
	}

?>