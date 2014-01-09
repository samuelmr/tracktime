<?php
 require_once('dbconfig.php');
 error_reporting(E_ALL);
 $conn = mysqli_connect('localhost', DB_USER, DB_PASS);
 $res = mysqli_select_db($conn, DB_DB);
 if (!$res) {
  die(mysqli_error($conn));
 }

 $query = "

CREATE TABLE IF NOT EXISTS ".DB_TABLE." (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `starttime` time NOT NULL,
  `endtime` time NOT NULL,
  `mainaction` char(2) NOT NULL DEFAULT '26',
  `sideaction` char(2) DEFAULT NULL,
  `with` int(1) NOT NULL DEFAULT '0',
  `description` varchar(255) DEFAULT NULL,
  `rating` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

";

 $res = mysqli_query($query);
 @header('Content-Type: text/plain');
 if ($res) {
  echo "Database table ".DB_DB.".times created succesfully!"
 }
 else {
  echo "Could not create table ".DB_DB.".times: ".mysqli_error($conn);
 }

?>
