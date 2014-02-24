<?php
require('../../../wp-blog-header.php');
header('Content-type:text/csv');
header('Content-Disposition: attachment; filename="Course Count Report ' . date("m d Y") . '.csv"');

global $wpdb;

$query = "select "
	. $wpdb->prefix. "pfctraining_courses.id, title, count("
	. $wpdb->prefix. "pfctraining_coursestaken.userid) as users, count(if(media='MP3', 1, NULL)) as mp3s, count(if(media='PDF', 1, NULL)) as pdfs from "
	. $wpdb->prefix . "pfctraining_courses left join "
	. $wpdb->prefix . "pfctraining_coursestaken "
	. "on " 
	. $wpdb->prefix . "pfctraining_courses.id =  "
		. $wpdb->prefix . "pfctraining_coursestaken.courseid "
	. "group by " 
	. $wpdb->prefix . "pfctraining_courses.id ";


$data = mysql_query($query);
if (!$data) {
	$message  = 'Invalid query: ' . mysql_error() . "\n";
	$message .= 'Whole query: ' . $query;
	die($message);
}

echo 'Course Description,Users,MP3,PDF
';

while ($row = mysql_fetch_assoc($data))
{
	echo $row["title"] . ',' .
		$row["users"] . ',' .
		$row["mp3s"] . ',' .
		$row["pdfs"] . ',
';
}
?>
