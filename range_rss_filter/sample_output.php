
<?php
require_once("./displayRangeRssFeed.php");
//instatiate the Class
$instance = new displayRangeRssFeed() ;

//Run the build RSS feed function in 'displayRangeRssFeed' class , Results should be passed to $arrItems.
$arrItems = $instance->bldRSSfeed('http://localhost/www/range_rss_filter/ferrum.xml');
	
	
	foreach($arrItems as $key => $val)
	{
		if (is_array($val))
		{
		$title = $val['title'];
		$link = $val['link'];
		$desc = $val['description'];
		$pubdate = getDate($val['pubdate']);
echo "<div>
<ol>
<li>".$title."</li>
<li>".$link."</li>
<li>".$desc."</li>
<li>".$pubdate['year']."-".$pubdate['month']."-".$pubdate['mday']." -----  ".$pubdate['hours'].":".$pubdate['minutes']."</li>
</ol></div>";

		}
	
		
		
	}
 // var_dump($arrItems);		
?>