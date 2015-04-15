<?php

require_once "../Queries.php";

function goodbye_world()
{
	global $mysqli;
	
	$tables = array('admin','librarian','patron',
				'checkedout','contribution','contributor',
				'fine','hardcopy','hold','itemtag',
				'mediaitem','role','tag');
	foreach($tables as $table)
		$result = $mysqli->query("TRUNCATE $table");
}

function verify($input, $expected_output, $function_name)
{
	$result = call_user_func($function_name, $input);
	
	echo "<pre>".print_r($result)."</pre>";
}

?>