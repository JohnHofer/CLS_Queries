<?php
require_once 'Helpers/Helpers.php'

function search_by_subject($str)
{
	return array_keys(get_hits($str,'subject'));
}

function search_by_title($str)
{
	return array_keys(get_hits($str,'title'));
}

function search_by_genre($str)
{
	return array_keys(get_hits($str,'genre'));
}

function search_by_language($str)
{
	return array_keys(get_hits($str,'language'));
}

function search_by_contributor($str)
{
	return array_keys(get_hits($str,'contributor'));
}

function search_all($str)
{
	$subject = search_by_subject($str);
	$title = search_by_title($str);
	$genre = search_by_genre($str);
	$language = search_by_language($str);
	$contributor = search_by_contributor($str);
	$results = array();
	foreach($subject as $id=>$count)
		if(array_key_exists($id,$results))
			$results[$id]+=$count;
		else
			$results[$id] = $count;
	}
	foreach($title as $id=>$count)
		if(array_key_exists($id,$results))
			$results[$id]+=$count;
		else
			$results[$id] = $count;
	}
	foreach($genre as $id=>$count)
		if(array_key_exists($id,$results))
			$results[$id]+=$count;
		else
			$results[$id] = $count;
	}
	foreach($language as $id=>$count)
		if(array_key_exists($id,$results))
			$results[$id]+=$count;
		else
			$results[$id] = $count;
	}
	foreach($contributor as $id=>$count)
		if(array_key_exists($id,$results))
			$results[$id]+=$count;
		else
			$results[$id] = $count;
	}
	arsort($results);
	return array_keys($results);
}
?>