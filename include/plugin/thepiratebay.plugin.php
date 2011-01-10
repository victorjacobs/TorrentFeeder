<?php

	$table = $dom->getElementsByTagName("table")->item(0)->childNodes;
	// Remove non data rows
	for($i = 0; $i < $table->length; $i++) {
		$curr_el = $table->item($i);
		if($curr_el->nodeName != "tr") $curr_el->parentNode->removeChild($curr_el);
	}
	
	// $table contains the information we need, now we need to parse every row seperately
	// NOTE: rewrite for loops to foreach loops, since PHP is nice enough to use Iterators for DOMLists
	for($i = 0; $i < $table->length; $i++) {
		// Get third child of every row, this *should* be the <td> with needed data
		$curr_el = $table->item($i)->childNodes->item(2);
		// Check above condition
		if($curr_el->nodeName != "td" || $curr_el->hasAttributes())
			Core::fatalError("The Piratebay DOM changed!");
		
		// We only need the first encountered anchor, so break after finding it
		for($j = 0; $j < $curr_el->childNodes->length; $j++) {
			if($curr_el->childNodes->item($j)->nodeName == "a") {
				$downloadLink = $curr_el->childNodes->item($j);
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
		$output[] = array("id" => $id,
							"link" => $downloadLink->getAttribute("href"),
							"fileSize" => (float)$fileSize,
							"date" => $date,
							"fileName" => $fileName);
	}

?>