<?php
require('../../../wp-blog-header.php');
header('Content-type:text/csv');
header('Content-Disposition: attachment; filename="User Report ' . date("m d Y") . '.csv"');

global $wpdb;

$query = "select "
	. $wpdb->prefix . "pfctraining_users.id, first, last, email, address, city, state, zip, country, registered, max(takenon) as lasttrain, greatest(registered, ifnull(max(takenon), '1970-1-1')) as lastactivity from "
	. $wpdb->prefix . "pfctraining_users left outer join "
	. $wpdb->prefix . "pfctraining_coursestaken on "
	. $wpdb->prefix . "pfctraining_users.id = "
	. $wpdb->prefix . "pfctraining_coursestaken.userid group by "
	. $wpdb->prefix . "pfctraining_users.id order by lastactivity desc";
$data = mysql_query($query);

if (!$data) {
	$message  = 'Invalid query: ' . mysql_error() . "\n";
	$message .= 'Whole query: ' . $query;
	die($message);
}

echo 'First name,Last name,E-mail address,Address,City,State,Zip,Country,Registered,Last Trained
';

while ($row = mysql_fetch_assoc($data))
{
	echo '"' . 
		str_replace('"', '""', $row["first"]) . '","' .
		str_replace('"', '""', $row["last"]) . '","' .
		str_replace('"', '""', $row["email"]) . '","' .
		str_replace('"', '""', $row["address"]) . '","' .
		str_replace('"', '""', $row["city"]) . '","' .
		str_replace('"', '""', $row["state"]) . '","' .
		str_replace('"', '""', $row["zip"]) . '","' .
		str_replace('"', '""', $row["country"]) . '","' .
		str_replace('"', '""', strlen($row["registered"]) > 0 ? date("m/d/Y", strtotime($row["registered"])) : '') . '","' .
		str_replace('"', '""', strlen($row["lasttrain"]) > 0 ? date("m/d/Y", strtotime($row["lasttrain"])) : '') . '"
';
}
?>
