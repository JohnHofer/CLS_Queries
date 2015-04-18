<?php

function add_item($arr)
{	
	$title = clean_string($arr['title']); //mediaitem
	$year = clean_string($arr['year']); //mediaitem
	$media_type = clean_string($arr['media_type']); //mediaitem
	$isbn = clean_string($arr['isbn']); //mediaitem
	$edition = clean_string($arr['edition']); //mediaitem
	$volume = clean_string($arr['volume']); //mediaitem
	$issue_no = clean_string($arr['issue_no']); //mediaitem
	$barcode = clean_string($arr['barcode']); //mediaitem
	$contributors = $arr['contributor']; //contributors array
	$tags = $arr['tag'];//tag array
	$call_no = clean_string($arr['call_no']); //hardcopy
	$status = clean_string($arr['status']); //hardcopy
	$checkout_duration = clean_string($arr['checkout_duration']); //hardcopy
	$renew_limit = clean_string($arr['renew_limit']); //hardcopy
		
	$copy_number = 1; //if the media item doesn't exist
		
	$query = "SELECT * FROM `hardcopy` WHERE `barcode` = $barcode";
	$result = $mysqli->query($query);
	
	if($temp = check_sql_error($result))
		return $temp;
		
	if($item = $result->fetch_assoc())	//barcode already in hardcopy
	{
		return array('error'=>"Barcode $barcode is already in mediaItem", 'error_code'=>10);
	}
		
	//Check for already existing media item 
	$query = "SELECT `id` FROM `mediaitem` 
		WHERE `title` = $title AND `year` = $year AND `media_type` = $media_type 
			AND `edition` = $edition AND `volume` = $volume AND `issue_no` = $issue_no";
		
	$result = $mysqli->query($query);
		
	if($temp = check_sql_error($result))
		return $temp;
		
	if($row = $result->fetch_assoc())	//The media item already exists
	{	
		$mediaitem_id = $row['id'];
		$query = "SELECT COUNT(*) AS count FROM `hardcopy` WHERE `mediaitem_id` = $mediaitem_id"
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
						'mediaitem_id'		=>	$mediaitem_id
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


