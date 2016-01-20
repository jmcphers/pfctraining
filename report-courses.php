<?php
require('../../../wp-blog-header.php');
header('Content-type:text/csv');
header('Content-Disposition: attachment; filename="Course Taken Report ' . date("m d Y") . '.csv"');

global $wpdb;

$query = "select first, last, email, address, number, title, takenon, media from "
	. $wpdb->prefix . "pfctraining_users, " 
	. $wpdb->prefix . "pfctraining_courses, "
	. $wpdb->prefix . "pfctraining_coursestaken "
	. "where " 
	. $wpdb->prefix . "pfctraining_users.id = " 
		. $wpdb->prefix . "pfctraining_coursestaken.userid and "
	. $wpdb->prefix . "pfctraining_courses.id =  "
		. $wpdb->prefix . "pfctraining_coursestaken.courseid ";

if ($_GET["last30"] == "1")
{
	$query = $query . " and takenon > DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
}

$query = $query . " order by takenon desc;";

$data = $wpdb->get_results($query, ARRAY_A);

echo 'First name,Last name,E-mail address,Course #,Course Title,Date Taken,Format
';

foreach($data as $row)
{
	echo $row["first"] . ',' .
		$row["last"] . ',' .
		$row["email"] . ',' .
		$row["number"] . ',' .
		$row["title"] . ',' .
		$row["takenon"] . ',' .
		$row["media"] . '
';
}
?>
