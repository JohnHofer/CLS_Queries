<!DOCTYPE html>

<html lang="en">
	<head>
		<meta charset = "utf-8">
		<title>Test File!</title>
	</head>
	<body>	


<?php
//error_reporting(E_ALL);

//$error_descriptions[E_ERROR]   = "A fatal error has occurred";
//$error_descriptions[E_WARNING] = "PHP issued a warning";
//$error_descriptions[E_NOTICE]  = "This is just an informal notice";

require_once "Queries.php";

$result = login("Tester", "Hello World!", "librarian");
$result = get_librarian_permissions(1);

$book = array('title'=>'The Hunger Games',
			'year'=>'2010','isbn'=>'24234256',
			'media_type'=>'book');

$result = add_mediaitem($book);
echo "<pre>".print_r($result)."</pre>";

$book = array('title'=>'The Bible',
			'year'=>'0');

$result = add_mediaitem($book);
echo "<pre>".print_r($result)."</pre>";

$book = array('year'=>'2014','isbn'=>'213457',
			'media_type'=>'book');

$result = add_mediaitem($book);
echo "<pre>".print_r($result)."</pre>";

?>
	</body>
</html>