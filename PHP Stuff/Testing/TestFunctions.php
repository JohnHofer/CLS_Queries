<?php

require_once "../Queries.php";

function verify($input, $expected_output, $function_name)
{
	$result = call_user_func($function_name, $input);
	
	echo "<pre>".print_r($result)."</pre>";
}

?>