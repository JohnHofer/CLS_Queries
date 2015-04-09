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
	if($tablename == 'admin')
	{
		if(!array_key_exists('username',$arr))
			$arr['username'] = 'NULL';
		if(!array_key_exists('password',$arr))
			$arr['password'] = 'NULL';
		if(!array_key_exists('salt',$arr))
			$arr['salt'] = 'NULL';
	}
	if($tablename == 'checkedout')
	{
		if(!array_key_exists('patron_id',$arr))
			$arr['patron_id'] = 'NULL';
		if(!array_key_exists('hardcopy_barcode',$arr))
			$arr['hardcopy_barcode'] = 'NULL';
		if(!array_key_exists('due_date',$arr))
			$arr['due_date'] = 'NULL';
		if(!array_key_exists('renew_count',$arr))
			$arr['renew_count'] = 'NULL';
	}
	if($tablename == 'contribution')
	{
		if(!array_key_exists('media_item',$arr))
			$arr['media_item'] = 'NULL';
		if(!array_key_exists('contributor_id',$arr))
			$arr['contributor_id'] = 'NULL';
		if(!array_key_exists('role_id',$arr))
			$arr['role_id'] = 'NULL';
	}
	if($tablename == 'contributor')
		if(!array_key_exists('last',$arr))
			$arr['last'] = 'NULL';

	if($tablename == 'fine')
	{
		if(!array_key_exists('patron_id',$arr))
			$arr['patron_id'] = 'NULL';
		if(!array_key_exists('hardcopy_barcode',$arr))
			$arr['hardcopy_barcode'] = 'NULL';
		if(!array_key_exists('amount',$arr))
			$arr['amount'] = 'NULL';
		if(!array_key_exists('reason',$arr))
			$arr['reason'] = 'NULL';
		if(!array_key_exists('date',$arr))
			$arr['date'] = 'NULL';
		if(!array_key_exists('paid',$arr))
			$arr['paid'] = 'NULL';
	}
	if($tablename == 'hardcopy')
	{
		if(!array_key_exists('barcode',$arr))
			$arr['barcode'] = 'NULL';
		if(!array_key_exists('mediaitem_id',$arr))
			$arr['mediaiem_id'] = 'NULL';
		if(!array_key_exists('copy_number',$arr))
			$arr['copy_number'] = 'NULL';
	}
	if($tablename == 'hold')
	{
		if(!array_key_exists('patron_id',$arr))
			$arr['patron_id'] = 'NULL';
		if(!array_key_exists('mediaitem_id)',$arr))
			$arr['mediaitem_id'] = 'NULL';
		if(!array_key_exists('queue_position',$arr))
			$arr['queue_position'] = 'NULL';
		if(!array_key_exists('expiration_date',$arr))
			$arr['expiration_date'] = 'NULL';
	}
	if($tablename == 'itemtag')
	{
		if(!array_key_exists('mediaitem_id',$arr))
			$arr['mediaitem_id'] = 'NULL';
		if(!array_key_exists('tag_id',$arr))
			$arr['tag_id'] = 'NULL';
	}
	if($tablename == 'librarian')
	{
		if(!array_key_exists('username',$arr))
			$arr['username'] = 'NULL';
		if(!array_key_exists('password_hash',$arr))
			$arr['password'] = 'NULL';
		if(!array_key_exists('salt',$arr))
			$arr['salt'] = 'NULL';
		if(!array_key_exists('check_in',$arr))
			$arr['username'] = 'NULL';
		if(!array_key_exists('check_out',$arr))
			$arr['password'] = 'NULL';
		if(!array_key_exists('add_book',$arr))
			$arr['salt'] = 'NULL';
		if(!array_key_exists('remove_book',$arr))
			$arr['username'] = 'NULL';
		if(!array_key_exists('add_patron',$arr))
			$arr['password'] = 'NULL';
		if(!array_key_exists('remove_patron',$arr))
			$arr['salt'] = 'NULL';
		if(!array_key_exists('manage_account',$arr))
			$arr['username'] = 'NULL';
		if(!array_key_exists('pay_fines',$arr))
			$arr['password'] = 'NULL';
		if(!array_key_exists('extend_due_date',$arr))
			$arr['salt'] = 'NULL';
		if(!array_key_exists('waive_fines',$arr))
			$arr['username'] = 'NULL';
		if(!array_key_exists('edit_media_entry',$arr))
			$arr['password'] = 'NULL';
		if(!array_key_exists('add_tag',$arr))
			$arr['salt'] = 'NULL';
	}
	if($tablename == 'mediaitem')
	{
		if(!array_key_exists('title',$arr))
			$arr['title'] = 'NULL';
		if(!array_key_exists('media_type',$arr))
			$arr['media_type'] = 'NULL';
	}
	if($tablename == 'patron')
	{
		if(!array_key_exists('username',$arr))
			$arr['username'] = 'NULL';
		if(!array_key_exists('password',$arr))
			$arr['password'] = 'NULL';
		if(!array_key_exists('salt',$arr))
			$arr['salt'] = 'NULL';
	}
	if($tablename == 'role')
	{
		if(!array_key_exists('username',$arr))
			$arr['username'] = 'NULL';
		if(!array_key_exists('password',$arr))
			$arr['password'] = 'NULL';
		if(!array_key_exists('salt',$arr))
			$arr['salt'] = 'NULL';
	}
	if($tablename == 'tag')
	{
		if(!array_key_exists('username',$arr))
			$arr['username'] = 'NULL';
		if(!array_key_exists('password',$arr))
			$arr['password'] = 'NULL';
		if(!array_key_exists('salt',$arr))
			$arr['salt'] = 'NULL';
	}
}
?>