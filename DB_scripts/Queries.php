<?php
// copyright SSD
require_once "Helpers/Connect.php";
require_once "Helpers/Hashing.php";
require_once "Helpers/Adders.php";
require_once "Helpers/Getters.php";
require_once "Helpers/Removers.php";

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
			$copy = $row;
			$copy['user_type'] = $table;
			return $copy;
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

function get_copy_info($barcode)
{
	global $mysqli;
	
	$mediaItemIdQuery 	= "SELECT * FROM `hardcopy` WHERE `barcode` = $barcode";
	$result = $mysqli->query($mediaItemIdQuery);
	
	if($temp = check_sql_error($result))
	{
		return $temp;
	}
	else
	{
		if($row = $result->fetch_assoc())
		{
			$mediaitem_id = $row['mediaitem_id'];
			$pending_result = get_general_item_info($mediaitem_id);
			
			foreach($row as $key => $value)
			{
				$pending_result[$key] = $value;
			}
			
			$isCheckedOutQuery 	= "SELECT `due_date`, `renew_count` FROM `checkedout` WHERE `hardcopy_barcode` = $barcode";
			$result2 = $mysqli->query($isCheckedOutQuery);
			
			if($temp2 = check_sql_error($result2))
			{
				return $temp2;
			}
			else
			{
				if($row2 = $result2->fetch_assoc())
				{
					foreach($row2 as $key => $value)
					{
						$pending_result[$key] = $value;
					}
				}
			}
	
			return $pending_result;
		}
		else
		{
			return array
			(
				'error'			=>	'Not found',
				'error_code'	=> 	1
			);
		}
	}
}

function get_general_item_info($mediaitem_id)
{
	global $mysqli;

	$mediaItemInfoQuery = "SELECT * FROM `mediaitem` WHERE `id` = $mediaitem_id";
	
	$result = $mysqli->query($mediaItemInfoQuery);
	
	$mediaitem = array();
	
	if(!$result)
	{
		// bad things happen \die?
	}
	else
	{
		if($row = $result->fetch_assoc())
		{
			$mediaitem_id = $row['id'];
			
			foreach($row as $key => $value)
			{
				$mediaitem[$key] = $value;
			}
		}
		else
		{
			$mediaitem['error'] = 'Not found';
			$mediaitem['error_code'] = 1;
			return $mediaitem;
		}
	}
	
	$tagsQuery 			= "SELECT `name` FROM `tag` JOIN `itemtag` ON tag_id = tag.id WHERE mediaitem_id = $mediaitem_id";
	
	$result = $mysqli->query($tagsQuery);
	
	
	if(!$result)
	{
		// it's ok to find no tags, just don't do anything then.
	}
	else
	{
		$tags = array();
		for($i = 0; $row = $result->fetch_assoc(); $i++)
		{
			$tags[$i] = $row['name'];
		}
		
		$mediaitem['tags'] = $tags;
	}
	
	$contributors = array();
	
	$contibutionsQuery 	= "SELECT `first`, `last`, `description` FROM `contribution` JOIN `contributor` ON contributor_id = contributor.id JOIN `role` ON role_id = role.id WHERE mediaitem_id = $mediaitem_id";
	
	$result = $mysqli->query($contibutionsQuery);
	
	if(!$result)
	{
		// it's ok to find no contributors, just don't do anything then.
	}
	else
	{
		while($row = $result->fetch_assoc())
		{
			if(isset($contributors[$row['description']]))
			{
				$contributors[$row['description']][] = array('first' => $row['first'], 'last' => $row['last']);
			}
			else
			{
				$contributors[$row['description']] = array();
				$contributors[$row['description']][] = array('first' => $row['first'], 'last' => $row['last']);
			}
		}
	}
	
	$mediaitem['contributors'] = $contributors;
	
	return $mediaitem;
}

function get_user_by_id($user_id)
{
	global $mysqli;
	
	clean_string($user_id);
	
	$user_info_query = "SELECT `first`, `last`, `email`, `phone`, `checkout_limit`, `renew_limit` FROM `patron` WHERE id = '$user_id'";
	
	$result = $mysqli->query($user_info_query);
	
	if($temp = check_sql_error($result))
		return $temp;
	
	//Check for a valid username (nonempty result on user_id query)
	if($row = $result->fetch_assoc())
	{
		return $row;
	}
			
	else //Empty query
	{
		$result = array();
		$result['error'] = "Bad user id";			
		$result['error_code'] = 3;
		return $result;
	}
}

function change_status($barcode, $new_status)
{
	global $mysqli;
	
	clean_string($barcode);
	clean_string($new_status);
	
	$check_barcode_query = "SELECT * FROM `hardcopy` WHERE `barcode` = $barcode";
	$result = $mysqli->query($check_barcode_query);
	if($temp = check_sql_error($result))
	{
		return $temp;
	}
	
	if($item = $result->fetch_assoc())
	{
		if(!is_possible_enum_val($new_status,'hardcopy_status'))
		{
			return array('error'=>'Not a valid enum value', 'error_code'=>9);
		}
		$status_query = "UPDATE `hardcopy` SET `status`= '$new_status' WHERE `barcode` = $barcode";
		
		$result = $mysqli->query($status_query);
	
		if($temp = check_sql_error($result))
		{
			return $temp;
		}
		return array();
	}
	else
	{
		return array('error'=>'Barcode not found', 'error_code'=>4);
	}
}

function check_out($barcode,$patron_id)
{
	global $mysqli;
	
	clean_string($barcode);
	clean_string($patron_id);
	
	$item = get_hardcopy_by_barcode($barcode);
	if(!$item)
		return array('error'=>'barcode not found', 'error_code'=>4);
	$patron = get_patron_by_id($patron_id);
	if(!$patron)
		return array('error'=>'id not found', 'error_code'=>3);
	
	$checkout_duration	= $item['checkout_duration'];
	$renew_limit 		= $item['renew_limit'];
		
	//If checkout_duration or renew_count = 0, this mediaitem cannot be checked out
	if($checkout_duration === 0 || $renew_limit === 0)
	{
		return array(
					'error'			=>	'mediaitem cannot be checked out of library',
					'error_code'	=>	5
				);
	}
	
	$checkout_list = get_checkouts_by_patron_id($patron_id);
	if(!$checkout_list)
		return array('error'=>'unknown error','error_code'=>-1);
		
	if(sizeof($checkout_list) < $patron['checkout_limit'] )		//Go ahead and checkout the mediaitem!
	{
		$date = new DateTime();
		$date->add(DateInterval::createFromDateString("$checkout_duration days"));

		$arr = array
				(
					'patron_id'			=>	$patron_id, 
					'hardcopy_barcode'	=>	$barcode,
					'due_date'			=>	$date->format('Y-m-d'), 
					'renew_count'		=>	0
				);

		return add_checkedout($arr);
	}
	else
	{
		return array('error'=>'Checkout limit exceeded', 'error_code'=>7);
	}
}

//	needed implementation : is overdue -> make fine
function check_in($barcode)
{
	global $mysqli;
	
	clean_string($barcode);
	
	$item = get_hardcopy_by_barcode($barcode);
	if(!$item)
	{
		return array('error'=>'barcode not found', 'error_code'=>4);
	}
	
	$check_for_item_query = "SELECT * FROM `checkedout` WHERE `hardcopy_barcode` = $barcode"; 
	$result = $mysqli->query($check_for_item_query);
	
	if($temp = check_sql_error($result))
		return $temp;
	
	if($result->fetch_assoc())
	{ 	//The mediaitem is checked out, check it in
		return delete_from_table('hardcopy_barcode',$barcode,'checkedout');
	}
	
	return array('error'=>"item not checked out", 'error_code'=>9);
}


function place_hold($mediaitem_id,$patron_id)
{
	global $mysqli;
	
	clean_string($mediaitem_id);
	clean_string($patron_id);
	
	//Query for the media item
	$check_for_item_query = "SELECT * FROM `mediaitem` WHERE `id` = $mediaitem_id";
	$item_result = $mysqli->query($check_for_item_query);
	
	if($temp = check_sql_error($item_result))
	{
		return $temp;
	}
		
	//Query for the patron
	$check_for_patron_query = "SELECT * FROM `patron` WHERE `id` = $patron_id";
	$patron_result = $mysqli->query($check_for_patron_query);	
	
	if($temp = check_sql_error($patron_result))
	{
		return $temp;
	}
	
	if($item = $item_result->fetch_assoc())				//The item exists
	{ 
		if($patron = $patron_result->fetch_assoc())		//The patron exists.
		{ 
			$date 				= new DateTime();
			$time_placed 		= new DateTime();
			$date->add(DateInterval::createFromDateString("3 days"));
			
			$string1 = $time_placed->format('Y-m-d');
			$string2 = $date->format('Y-m-d');
			
			$arr = array
					(
						'patron_id'			=>	$patron_id, 
						'mediaitem_id'		=>	$mediaitem_id,
						'time_placed'		=>	$string1,
						'expiration_date'	=>	$string2
					);
					
//			echo "<pre>";
//			print_r($arr);
//			echo "</pre>";
			
			return add_hold($arr);
		}
		
		else
		{
			return array('error'=>'The patron could not be found', 'error_code'=>6);
		}
	}
	else
	{
		return array('error'=>"No such item exists", 'error_code'=>4);
	}
}

// Testing Query : "INSERT INTO `hold`(`patron_id`, `mediaitem_id`, `expiration_date`) VALUES (1, 1, '2015-04-01')"
function remove_hold($mediaitem_id, $patron_id)
{
	global $mysqli;
	
	clean_string($mediaitem_id);
	clean_string($patron_id);
	
	$check_for_hold_query = "SELECT * FROM `hold` WHERE `mediaitem_id` = $mediaitem_id AND `patron_id` = $patron_id"; 
	$result = $mysqli->query($check_for_hold_query);
	
	if($temp = check_sql_error($result))
	{
		return $temp;
	}
	
	if($result->fetch_assoc())
	{	//The mediaitem is on hold
		$query = "DELETE FROM `hold` WHERE `mediaitem_id` = $mediaitem_id AND `patron_id` = $patron_id";
	
		$result = $mysqli->query($query);
	}
	else
	{
		return array
		(
			'error'			=>	'Not found', 
			'error_code'	=>	1
		);
	}
	
	return array();
}

function add_item($arr)
{	
	global $mysqli;

	// Destination : mediaitem
	$title 				= clean_string($arr['title']); 
	$year 				= clean_string($arr['year']); 
	$media_type 		= clean_string($arr['media_type']); 
	$isbn 				= clean_string($arr['isbn']); 
	$edition 			= clean_string($arr['edition']);
	$volume 			= clean_string($arr['volume']);
	$issue_no 			= clean_string($arr['issue_no']);
	
	$contributors 		= $arr['contributor']; //contributors array
	
	$tags 				= $arr['tag'];//tag array
	// Destination : hardcopy
	$barcode 			= clean_string($arr['barcode']);
	$call_no 			= clean_string($arr['call_no']); 
	$status 			= clean_string($arr['status']); //hardcopy
	$checkout_duration 	= clean_string($arr['checkout_duration']); //hardcopy
	$renew_limit 		= clean_string($arr['renew_limit']); //hardcopy
		
	$copy_number = 1; //if the media item doesn't exist
		
	$query = "SELECT * FROM `hardcopy` WHERE `barcode` = $barcode";
	$result = $mysqli->query($query);
	
	if($temp = check_sql_error($result))
		return $temp;
		
	if($item = $result->fetch_assoc())	//barcode already in hardcopy
	{
		return array('error'=>"Barcode $barcode is already in use", 'error_code'=>10);
	}
		
	//Check for already existing media item 
	$query = "SELECT `id` FROM `mediaitem` WHERE `title` = \'$title\' AND `year` = $year AND `media_type` = \'$media_type\' AND `edition` = \'$edition\' AND `volume` = \'$volume\' AND `issue_no` = \'$issue_no\'";
		
	$result = $mysqli->query($query);
		
	if($temp = check_sql_error($result))
		return $temp;
		
	if($row = $result->fetch_assoc())	//The media item already exists
	{	
		$mediaitem_id = $row['id'];
		$query = "SELECT COUNT(*) AS count FROM `hardcopy` WHERE `mediaitem_id` = $mediaitem_id";
		$result = $mysqli->query($query);
		
		if($temp = check_sql_error($result))
			return $temp;
		
		if($row = $result->fetch_assoc())
			$copy_no = $row['count'] + 1;
	}
	else	
	{
		//add new mediaitem
		$arr = array(
						'title'		 =>	$title, 
						'year'	     =>	$year,
						'isbn'		 =>	$isbn,
						'media_type' =>	$media_type,
						'edition'	 =>	$edition,
						'volume'     =>	$volume,
						'issue_no'   => $issue_no
						
				);
		add_mediaitem($arr);
		//get mediaitem_id
		$query = "SELECT `id` FROM `media_item` WHERE `title` = $title AND `year` = $year AND `media_type` = $media_type 
			AND `edition` = $edition AND `volume` = $volume AND `issue_no` = $issue_no";
		$result = $mysqli->query($query);
				
		if($temp = check_sql_error($result))
			return $temp;
				
		if($row = $result->fetch_assoc())
			$mediaitem_id = $row['id'];
			
		foreach($contributors as $key=> $subarray)
		{	
			foreach($subarray as $val)
			{
				//check if contributor already exists
				$first = $val['first'];
				$last = $val['last'];
				$description = $val;
				$query = "SELECT `id` FROM `contributor` WHERE `first` = $first AND `last` = $last";
				$result = $mysqli->query($query);
			
				if($temp = check_sql_error($result))
					return $temp;
				
				if($row = $result->fetch_assoc())//contributor already exists
				$contributor_id = $row['id'];
				
				else //add contributor
				{
					$arr = array(
									'first'		 =>	$first, 
									'last'	     =>	$last
							);
					add_contributor($arr);
					//get contributor_id
					$query = "SELECT `id` FROM `contributor` WHERE `first` = $first AND `last` = $last";
					$result = $mysqli->query($query);
					
					if($temp = check_sql_error($result))
						return $temp;
					
					if($row = $result->fetch_assoc())
						$contributor_id = $row['id'];
				}
			
				//check if role already exists
				$query = "SELECT `id` FROM `role` WHERE `description` = $description";
				$result = $mysqli->query($query);
				
				if($temp = check_sql_error($result))
					return $temp;
					
				if($row = $result->fetch_assoc()) //role already exists
					$role_id = $row['id'];
				
				else //add role
				{
					$arr = array(
									'description'		 =>	$description
							);
					add_role($arr);
					//get role_id
					$query = "SELECT `id` FROM `role` WHERE `description` = $description";
					$result = $mysqli->query($query);
					
					if($temp = check_sql_error($result))
						return $temp;
					
					if($row = $result->fetch_assoc())
						$role_id = $row['id'];
				}
				
				//add contribution
				$arr = array(
								'mediaitem_id'		 =>	$mediaitem_id, 
								'role_id'	    	 =>	$role_id,
								'contributor_id'	 =>	$contributor_id
						);
				add_contribution($arr);
				
				//add tags
				//TODO: Put in all the words from title as title tags
				//TODO: Put in all words from author as author tags
				foreach($tags as $name)
				{
					//check if it already exists
					$query = "SELECT `id` FROM `tag` WHERE `name` = $name";
					$result = $mysqli->query($query);
				
					if($temp = check_sql_error($result))
						return $temp;
					
					if($row = $result->fetch_assoc())
						$tag_id = $row['id'];
						
					else
					{
						$type = "subject";
						$arr = array(
										'name'		 =>	$name, 
										'type'	     =>	$type
										
								);
						add_tag($arr);
						
						//get tag_id
						$query = "SELECT `id` FROM `tag` WHERE `name` = $name AND `type` = $type";
						$result = $mysqli->query($query);
						
						if($temp = check_sql_error($result))
							return $temp;
						
						if($row = $result->fetch_assoc())
							$tag_id = $row['id'];
					}
							
				//add itemtag
				$arr = array(
								'tag_id'		 =>	$tag_id, 
								'mediaitem_id'	 =>	$mediaitem_id
						);
				add_itemtag($arr);
					
				}
			}
		}
	}
		
	//add hardcopy 
	$arr = array(
						'barcode'		    =>	$barcode, 
						'mediaitem_id'		=>	$mediaitem_id,
						'copy_no'		    =>	$copy_no,
						'call_no'	        =>	$call_no,
						'status'	        =>	$status,
						'checkout_duration' =>	$checkout_duration,
						'renew_limit'       =>  $renew_limit
				);
			
	add_hardcopy($arr);
	
	return array();
}


?>