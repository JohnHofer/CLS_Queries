<?php
require_once 'Helpers/Helpers.php'

function search_by_subject($str)
{
		return search_by_generic_tag_type($str,'subject');
}
?>