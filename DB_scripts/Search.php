<?php
function search_all($str)
{
	global $mysqli;
	
	//Throws out useless words and parses string
	$str = strtolower($str);
	include 'Stoplist.php';
	$str = preg_replace($stoplist, "", $str);
	$words = $str.explode(" ",$str);
	
	//Build results array
	$results = array();
	foreach($words as $word)
	{
		$query_result = $mysqli->query("SELECT mediaitem_id FROM itemtag
										JOIN tag ON tag_id = tag.id
										JOIN mediaitem ON mediaitem_id = mediaitem.id
										WHERE name = $word");
		if($error = check_sql_error($query_result))
			return $error;
		while($row = $query_result->fetch_assoc())
		{
			$id = $row['mediaitem_id'];
			if(array_key_exists($id,$results))
				$results[$id]++;
			else
				$results[$id] = 1;
		}
		arsort($results);
		return array_keys($results);
	}
}
?>