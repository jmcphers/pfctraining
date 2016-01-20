<?php
require('../../../wp-blog-header.php');
 
global $wpdb;
$query = 'insert into ' . $wpdb->prefix . 'pfctraining_coursestaken (courseid, userid, media) values ('
		. $wpdb->escape($_GET['courseid']) . ', '
		. $wpdb->escape($_GET['userid']) . ', "'
		. $wpdb->escape($_GET['mediatype']) . '");';
$wpdb->query($query);

header('Location: ' . $_GET['mediafile']);
?>
