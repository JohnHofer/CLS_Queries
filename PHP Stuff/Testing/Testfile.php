<!DOCTYPE html>

<html lang="en">
	<head>
		<meta charset = "utf-8">
		<title>Test File!</title>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>


<?php
//error_reporting(E_ALL);

//$error_descriptions[E_ERROR]   = "A fatal error has occurred";
//$error_descriptions[E_WARNING] = "PHP issued a warning";
//$error_descriptions[E_NOTICE]  = "This is just an informal notice";

require_once "../Queries.php";
require_once "TestFunctions.php";

//////////////////////////////////////////////////////////////////////////////////////////////////////////
	$functionName = 'login';

	$input_expected_output_pairs = array();
	$input_expected_output_pairs[] = generate_IEO_pair(	array("Tester", "Hello World!", "librarian"), 
														array('id'=> 1));
	$input_expected_output_pairs[] = generate_IEO_pair(	array("Not the username!", "Hello World!", "librarian"), 
														array('error' => 'Bad username', 'error_code' => 1));
	$input_expected_output_pairs[] = generate_IEO_pair(	array("Tester", "Not the password!", "librarian"), 
														array('error' => 'Bad password', 'error_code' => 2));
	$input_expected_output_pairs[] = generate_IEO_pair(	array("Not the username", "Not the password!", "librarian"), 
														array('error' => 'Bad username', 'error_code' => 1));
				
	functionTestBlock($input_expected_output_pairs, $functionName);
//////////////////////////////////////////////////////////////////////////////////////////////////////////
	$functionName = 'add_mediaitem';

	$input_expected_output_pairs = array();
	$input_expected_output_pairs[] = generate_IEO_pair
	(	
		array
		(	
			array
			(
				'title' 		=> 'The Hunger Games', 
				'year' 			=> '2010',
				'isbn' 			=> '24234256',
				'media_type' 	=> 'book'
			)
		), 
		array
		() 
	);
	$input_expected_output_pairs[] = generate_IEO_pair
	(	
		array
		(	
			array
			(
				'title'	=> 'The Bible',
				'year' 	=> '0'
			)
		), 
		array
		(
			'error' 		=> 'Column \'media_type\' cannot be null', 
			'error_code' 	=> 0
		)
	);
	$input_expected_output_pairs[] = generate_IEO_pair
	(
		array
		(
			array
			(
				'year' 			=> '2014',
				'isbn' 			=> '213457',
				'media_type' 	=> 'book'
			)
		), 
		array
		(
			'error' 		=> 'Column \'title\' cannot be null',
			'error_code' 	=> 0
		)
	);
	
	echo "<pre>";
	print_r($input_expected_output_pairs);
	echo "</pre>";
	
	functionTestBlock($input_expected_output_pairs, $functionName);
//////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
		<fieldset> 	<legend>	add_mediaitem()	</legend>
			<?php
			$book1 = array('title'=>'The Hunger Games',
						'year'=>'2010','isbn'=>'24234256',
						'media_type'=>'book');
			$book2 = array('title'=>'The Bible',
						'year'=>'0');
			$book3 = array('year'=>'2014','isbn'=>'213457',
						'media_type'=>'book');
			?>
			<pre><?php 	print_r(add_mediaitem($book2)); ?></pre>
			<pre><?php print_r(add_mediaitem($book3)); ?></pre>
		</fieldset>
		<fieldset> 	<legend>	get_user_by_id()	</legend>
			<pre><?php	print_r(get_user_by_id(1));	?></pre>
			<pre><?php	print_r(get_user_by_id(0)); ?></pre>
		</fieldset>
		<fieldset> 	<legend>	Test : get_book_by_mediaItem_id()	</legend>
			<h4>get_book_by_mediaItem_id(0)</h4>
			<p>Failure Expected</p>
			<pre><?php 	print_r(get_book_by_mediaItem_id(0)); ?></pre>
			<p>Success Expected</p>
			<pre><?php	print_r(get_book_by_mediaItem_id(1)); ?></pre>
			<p>Success Expected</p>
			<pre><?php 	print_r(get_book_by_mediaItem_id(2)); ?></pre>
		</fieldset>
		<fieldset> 	<legend>	get_book_by_barcode()	</legend>
			<p>Success Expected</p>
			<pre><?php	print_r(get_book_by_barcode(1));	?></pre>
			<p>Failure Expected</p>
			<pre><?php	print_r(get_book_by_barcode(0));	?></pre>
		</fieldset>
		<fieldset> 	<legend>	delete_from_admin()	</legend>
			<p>Failure Expected</p>
			<pre><?php	print_r(delete_from_admin(1));	?></pre>
		</fieldset>
		<fieldset> 	<legend>	change_status()	</legend>
			<p>Success Expected</p>
			<pre><?php	print_r(change_status(1, 'Lost'));	?></pre>
			<p>Failure Expected</p>
			<pre><?php	print_r(change_status(2, 'Damaged/In Repair'));	?></pre>
			<p>Failure Expected</p>
			<pre><?php	print_r(change_status(0, 'Lost'));	?></pre>
			<p>Failure Expected</p>
			<pre><?php	print_r(change_status(1, 'notarealstatus'));	?></pre>
		</fieldset>
		<fieldset> 	<legend>	check_in()	</legend>
			<p>Success Expected</p>
			<pre><?php	print_r(check_in(1));	?></pre>
			<p>Failure Expected</p>
			<pre><?php	print_r(check_in(0));	?></pre>
		</fieldset>
		<fieldset> 	<legend>	check_out()	</legend>
			<p>Success Expected</p>
			<pre><?php	print_r(check_out(1));	?></pre>
			<p>Failure Expected</p>
			<pre><?php	print_r(check_out(0));	?></pre>
		</fieldset>
	</body>
</html>