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

	class thePirateBayParserPlugin extends parsePluginBase {
		
		public function hasUsefulData() {
			if(!is_null($this->dom->getElementByID("searchResult")) &&
						$this->dom->getElementByID("searchResult")->childNodes->length != 0) {
				Core::debugLog("thePirateBayParserPlugin::hasUsefulData() true");
				
				$this->parse();
				
				return true;
			} else {
				Core::debugLog("thePirateBayParserPlugin::hasUsefulData() false");
				return false;
			}
		}
		
		
		protected function parse() {
			$tableRows = $this->dom->getElementByID("searchResult")->childNodes;
			// Remove non data rows
			for($i = 0; $i < $tableRows->length; $i++) {
				$curr_el = $tableRows->item($i);
				if($curr_el->nodeName != "tr") $curr_el->parentNode->removeChild($curr_el);
			}

			// $table contains the information we need, now we need to parse every row seperately
			foreach($tableRows as $curr_row) {
				// Get third child of every row, this *should* be the <td> with needed data
				$curr_el = $curr_row->childNodes->item(2);
				// Check above condition
				if($curr_el->nodeName != "td" || $curr_el->hasAttributes())
					Core::fatalError("The Piratebay DOM changed!");

				// We only need the first encountered anchor, so break after finding it
				foreach($curr_el->childNodes as $tag) {
					if($tag->nodeName == "a") {
						$downloadLink = $tag;
						break;
					}
				}

				// Figure out the title and date of upload
				// Second element is just whitespace, cut it
				// Sometimes there is another empty entry, no idea why
				$temp = array_map("trim", explode("\n", trim($curr_el->nodeValue)));
				$fileName = $temp[0];
				$description = (empty($temp[1]) ? $temp[2] : $temp[1]);

				list($date, $fileSize, ) = explode(", ", $description);

				// Clean up
				$date = str_replace("Uploaded ", "", $date);
				// Today
				$date = str_replace("Today", date("m-d"), $date);
				// Yesterday
				$date = str_replace("Y-day", date('m-d', mktime(0, 0, 0, date("m") , date("d") - 1, date("Y"))), $date);

				$fileSize = str_replace("Size ", "", $fileSize);

				// Convert time to something more useful
				// First 5 chars are date (always)
				list($month, $day) = explode("-", $date);
				// Second depends on how old the torrent is:
				list(, $temp) = explode(" ", $date);
				if(substr_count($temp, ":") == 1) {
					// Time
					list($hours, $minutes) = explode(":", $temp);
					$date = date("r", mktime((int)$hours, (int)$minutes, 0, (int)$month, (int)$day, date("Y")));
				} else {
					$year = $temp;
					$date = date("r", mktime(0, 0, 0, (int)$month, (int)$day, (int)$year));
				}

				// Convert fileSize to something useful
				//$fileSize = str_replace(".", ",", $fileSize);
				if(substr_count($fileSize, "GiB") == 1) {
					$fileSize = (float)str_replace(" GiB", "", $fileSize) * 1000;
				}else {
					$fileSize = str_replace(" MiB", "", $fileSize);
				}

				// Get the episode id
				preg_match('$S[0-9]{2}E[0-9]{2}$i', $fileName, $id);
				$id = (empty($id[0]) ? null : strtoupper($id[0]));
				
				// We know for sure that download link is the first anchor, the rest is pretty straightforward
				$this->torrents[] = array("id" => $id,
									"link" => $downloadLink->getAttribute("href"),
									"fileSize" => (float)$fileSize,
									"date" => $date,
									"fileName" => $fileName);
			}
		}
		
	}

?>