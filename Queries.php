<?php
require_once "Connect.php";
include_once "Hashing.php";
include_once "Helpers.php";

function login($username, $password, $table)
{
	global $mysqli;
	
	clean_string($username);
	clean_string($password);
	clean_string($table);
	
	$login_salt_query = "SELECT `salt` FROM `$table` WHERE username = '$username'";
	$result = $mysqli->query($login_salt_query);
	
	//Check for sql errors
	if($temp = check_sql_error($result))
	{
		return $temp;
	}
	
	//Check for a valid username (nonempty result on username query)
	if($row = $result->fetch_assoc())
	{
		$salt = $row['salt'];
		$password_hash = myhash($password, $salt);
		
		//Check for valid password (nonempty result on password_hash query)
		$login_query = "SELECT `id` FROM `$table` WHERE username = '$username' AND password_hash = '$password_hash'";
		$result = $mysqli->query($login_query);
		//Check for sql errors
		if($temp = check_sql_error($result))
		{
			return $temp;
		}
		if($row = $result->fetch_assoc())
		{
			return $row;
		}
		else //Empty password query
		{
			$result = array();
			$result['error'] = "Bad password";
			$result['error_code'] = 2;
			return $result;
		}
	}
	else //Empty username query
	{
		$result = array();
		$result['error'] = "Bad username";
		$result['error_code'] = 1;
		return $result;
	}
}

function get_librarian_permissions($id)
{
	global $mysqli;
	$result = $mysqli->query("SELECT `id`, `check_in`, `check_out`, `add_book`,
		`remove_book`, `add_patron`, `remove_patron`, `manage_accounts`,
		`pay_fines`, `extend_due_date`, `waive_fines`, `edit_media_entry`,
		`add_tag` FROM `librarian` WHERE `id`=$id");

	if($temp = check_sql_error($result))
		return $temp;

	if($row = $result->fetch_assoc())
		return $row;
	
	$err = array('error'=>'ID not found', 'error_code'=>3);
	return $err;
}

function add_to_table($arr,$tablename)
{
	global $mysqli;
	
	surround_in_quotes($arr);
	append_required_fields($arr, $tablename);
	$fields = array_keys($arr);
	$vals = array_values($arr);
	
	$query = "INSERT INTO `$tablename`(`".implode("`, `", $fields)."`)
		VALUES (".implode(", ", $vals).")";
	
	$result = $mysqli->query($query);

	if($temp = check_sql_error($result))
		return $temp;
	
	return array();
}

function add_mediaitem($arr)
{
	return add_to_table($arr,'mediaitem');
}

function add_patron($arr)
{
	return add_to_table($arr,'patron');
}
?>