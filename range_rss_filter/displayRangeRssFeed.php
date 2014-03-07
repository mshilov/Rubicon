<?php

class displayRangeRssFeed extends DateTime {

 public function getTimestamp(){
    return $this->format ("U = Y-m-d H:i:s");
}

 
/** GEt concatenated dates functions,
* this function receives input from RSS additional nodes for scheduled dates and time , 
* if blank it uses todays date time to initiate variables and results in returning timestamp
*/
 public function cnctStartDate($year1,$month1,$day1,$time,$ampm1)
 {
 	
 	$year1 = ($year1 == '') ? $this->format("Y")  : $year1; 
 	$month1 = ($month1 == '') ? $this->format("m")  : $month1; 
 	$day1 = ($day1 == '') ? $this->format("d")  : $day1; 
 	
 	
 	
 	// now we check if time was empty string and use NOW timestamp for start time of the start event 
 	if ($time =='')
 	{ 	
 		$arrStartTime = explode(':',$this->format("H:i"));
 			
 	} else {   
 	
 	// We need to check the time variable for correct time format, if single numbers are used :00 is appended to $time as minutes
 	if (preg_match('/[0-9]{2}:[0-9]{2}/', $time) == 0 ){
 	$time .= ":00";
 	} 
 		
 		$arrStartTime = explode(':',$time);
 	}
 	// checking for empty time and extracting hour and minutes from NOW or if $time was set then get it from arrayStartime that was build earlier
 	$hour1 = ($time == '') ? $this->format("H")  : $arrStartTime[0]; 
 	$min1 = ($time == '') ? $this->format("i")  : $arrStartTime[1]; 
 	// not using ap/pm to calculate time in this function
 	
 	$ampm1 = ($ampm1 != "" && $this->format("H") > '12') ? "pm"  : "am"; 
 	// build a timestamp
	$d1 = strtotime($year1."-".$month1."-".$day1." ".$hour1.":".$min1);
			
	return $d1;
 }


 public function cnctEndDate($year2,$month2,$day2,$endtime,$ampm2,$year1,$month1,$day1)
 
 { 	
 	// if dates for the end of event are not set use dates of start of event + 1 day
	$year2 = ($year2 == '') ? $year1  : $year2; 
 	$month2 = ($month2 == '') ? $month1  : $month2; 
 	$day2 = ($day2 == '') ? ++$day1  : $day2; 
 	
 	// if endtime is not set for end of event adding a day interval, however time is extracted from NOW, otherwise get end time from variable passed to function
 	if ($endtime =='')
 	{ 
 		$this->add(new DateInterval('P1D'));
 		$arrEndTime = explode(':',$this->format("H:i"));
 	} else {   
 		$arrEndTime = explode(':',$endtime);
 	}
 	
 	$hour2 = ($endtime == '') ? $arrEndTime[0] : $arrEndTime[0]; 
 	$min2 = ($endtime == '') ?  $arrEndTime[1]  : $arrEndTime[1];  	
 	
 	// not using ap/pm to calculate time in this function
 	$ampm2 = ($ampm2 != "" && $this->format("H") > '12') ? "pm"  :  "am"; 
	// build a timestamp
	$d2 = strtotime($year2."-".$month2."-".$day2." ".$hour2.":".$min2);
	
	return $d2;

 }



//Validate URL function 
public static function  is_valid_url ( $url )
{
	$url = @parse_url($url);

	if ( ! $url) {
		return false;
	}

	$url = array_map('trim', $url);
	$url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];
	$path = (isset($url['path'])) ? $url['path'] : '';

	if ($path == '')
	{
		$path = '/';
	}

	$path .= ( isset ( $url['query'] ) ) ? "?$url[query]" : '';

	if ( isset ( $url['host'] ) AND $url['host'] != gethostbyname ( $url['host'] ) )
	{
		if ( PHP_VERSION >= 5 )
		{
			$headers = get_headers("$url[scheme]://$url[host]:$url[port]$path");
		}
		else
		{
			$fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);

			if ( ! $fp )
			{
				return false;
			}
			fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
			$headers = fread ( $fp, 128 );
			fclose ( $fp );
		}
		$headers = ( is_array ( $headers ) ) ? implode ( "\n", $headers ) : $headers;
		return ( bool ) preg_match ( '#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers );
	}
	return false;
}
//get URLs for the RSS feeds
 public function bldRSSfeed($path)
	{
      	$items = array();
	if($this->is_valid_url($path)) {
		$domDoc = new DOMDocument();
		$domDoc->load($path);
		$xpathContext = new DOMXPath($domDoc);
		
		$contextQuery = "//rss/channel";
					
		$resultDOM = $xpathContext->query( $contextQuery ."/item" );
	
	//return $resultDOM;
	//exit();
	foreach ($resultDOM as $node) {
			//if ($node->getElementsByTagName("category")->item(0)->nodeValue != "LHU_Internal"){		
				$item_date = strtotime($node->getElementsByTagName("pubDate")->item(0)->nodeValue);
				
				$items[$item_date]["title"] = $node->getElementsByTagName("title")->item(0)->nodeValue;
				$items[$item_date]["link"] = $node->getElementsByTagName("link")->item(0)->nodeValue;
				$items[$item_date]["description"] = $node->getElementsByTagName("description")->item(0)->nodeValue;	
				//$items[$item_date]["category"] = $node->getElementsByTagName("category")->item(0)->nodeValue;	
				$items[$item_date]["pubdate"] = $item_date;
				$items[$item_date]["author"] = $node->getElementsByTagName("author")->item(0)->nodeValue;
				
				//get range startd date
				$items[$item_date]["month1"] = $node->getElementsByTagName("month")->item(0)->nodeValue;
				$items[$item_date]["day1"] =   $node->getElementsByTagName("day")->item(0)->nodeValue;
				$items[$item_date]["year1"] =  $node->getElementsByTagName("year")->item(0)->nodeValue;
				
				$items[$item_date]["time"] =  $node->getElementsByTagName("time")->item(0)->nodeValue;
				
				//$items[$item_date]["hour1"] = $node->getElementsByTagName("hour1")->item(0)->nodeValue;
				//$items[$item_date]["min1"] = $node->getElementsByTagName("min1")->item(0)->nodeValue;
				$items[$item_date]["ampm1"] =  $node->getElementsByTagName("ampm")->item(0)->nodeValue;
				
				//get range end date
				$items[$item_date]["month2"] = $node->getElementsByTagName("endmonth")->item(0)->nodeValue;
				$items[$item_date]["day2"] =  $node->getElementsByTagName("endday")->item(0)->nodeValue;
				$items[$item_date]["year2"] = $node->getElementsByTagName("endyear")->item(0)->nodeValue;
				
				$items[$item_date]["endtime"] =  $node->getElementsByTagName("endtime")->item(0)->nodeValue;
				
				//$items[$item_date]["hour2"] = $node->getElementsByTagName("hour2")->item(0)->nodeValue;
				//$items[$item_date]["min2"] = $node->getElementsByTagName("min2")->item(0)->nodeValue;
				$items[$item_date]["ampm2"] = $node->getElementsByTagName("endampm")->item(0)->nodeValue;
			//}//exclude condt.
			
			 /**
			  * parse through array and extract date range for each event by sending it to a concatenate functions above, 
			  * we get back  timestamps concatenated from month ,day years and time.
			  */
		
$arrDateRange = array($this->cnctStartDate($items[$item_date]["year1"],$items[$item_date]["month1"],$items[$item_date]["day1"],$items[$item_date]["time"], $items[$item_date]["ampm1"]),
$this->cnctEndDate($items[$item_date]["year2"],$items[$item_date]["month2"],$items[$item_date]["day2"],$items[$item_date]["endtime"],$items[$item_date]["ampm2"],$items[$item_date]["year1"],$items[$item_date]["month1"],$items[$item_date]["day1"]),$items[$item_date]["title"],$items[$item_date]["link"],$items[$item_date]["description"],$items[$item_date]["pubdate"]);
		
		
		// while still being in "foreach" loop send each array to the range comparison function it will filter out the current items to display and return it as $items.
		
	//var_dump($items);
	//exit();
		$items[$item_date] = $this->compareNowToRange($arrDateRange);
		 
		
	
		
		} //end foreach
		
	//DEBUGGING var_dump($items);
		
		
		
		if(count($items) > 0 ) {		
			//DEBUGGING ksort($items);
			//$items = array_slice($items, 0, 15);	
			//DEBUGGING
			/*
			foreach($items as $k => $v)
			{
			print($k);
			echo"----->>";
			print_r($v);
			echo"<br/>";
			}
			*/
			
			//exit();
			
			return $items;
	
		
		} else {
			return FALSE;
		}
	
		
	} //end if path is valid
	else {
		return FALSE;
	}
		
} //end function

public function compareNowToRange($arrDateRange) {

//$arrDateRange is an array, consisting from Start date and End Date Unit timestamps of the event  and $now is accurrent timestamps
	
	$now = strtotime(date('Y-m-d H:i'));
	
	 
	 $startDate = $arrDateRange[0];
	 $endDate = $arrDateRange[1];
	

//the logic is as follow: display events where $NOW is current timestamp, equals or falls in between start and end dates.

if ($startDate <= $now && $endDate >= $now )
	{
	$items = array();
	// display event
	$items["title"] = $arrDateRange[2];
	$items["link"] = $arrDateRange[3];
	$items["description"] = $arrDateRange[4];	
	//$items[$pubdate]["category"] = $category;	
	$items["pubdate"] = $arrDateRange[5];
	//$items[$pubdate]["author"] = $author;
	
	return $items;
	
 
 
	} else {

	// This ELSE implies that either $startdate > $now in which case the event is in the future or
	// $endDate < $now which makes event to be in the past

		if ($startDate > $now)
			{
			//future event
			return NULL;
			} 

		if ($endDate < $now )
        		{

			//past event
                       return NULL;
         		}
         	
         	
       }



} //end functon



} //end Class
?>
