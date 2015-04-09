<?php
function clean_string(&$arg)
{
	global $mysqli;
	htmlentities($arg);
	$mysqli->real_escape_string($arg);
}

function check_sql_error($result)
{
	global $mysqli;
	
	if(!$result)
	{
		$error_arr = array();
		$error_arr['error'] = $mysqli->error;
		$error_arr['error_code'] = 0;
		return $error_arr;
	}
	return false;
}

function surround_in_quotes(&$arr)
{
	array_walk($arr, create_function('&$str', '$str = "\'$str\'";'));
}

//NOT FINISHED - NEED FOR EACH TABLE
function append_required_fields(&$arr,$tablename)
{
	if($tablename = 'mediaitem')
	{
		if(!array_key_exists('title',$arr))
			$arr['title'] = 'NULL';
		if(!array_key_exists('media_type',$arr))
			$arr['media_type'] = 'NULL';
	}
	
}
?>