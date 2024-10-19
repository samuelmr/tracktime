<?php

 $create_config = true;

 $a = getopt("l:n:u:p:t:");
 echo count($a);
 if (!isset($a) || count($a) != 5 ||
     !isset($a['l']) || !isset($a['n']) || !isset($a['t']) ||
     !isset($a['u']) || !isset($a['p'])) {
  echo "Usage:\n\n  php ".basename(__FILE__).
       " -l <db location> -n <db name> -u <db user> -p <db password> -t <db table>\n\n";
  die();
 }
 chdir(dirname(__FILE__));
 if (file_exists('dbconfig.php')) {
  $msg = "Configuration file dbconfig.php exists. Overwrite?\n".
         "y = Overwrite current configuration file. Create new db tables.\n".
	 "n = Keep existing configuration file. Create new db tables.\n".
	 "c = Cancel. Do nothing.\n\n";
  echo $msg;
  $response = trim(readline("Choice (y/n/c): "));
  switch ($response) {
   case 'c':
     die();
     break;
   case 'n':
     $create_config = false;
     break;
   case 'y':
     echo "Overwriting dbconfig.php\n";
     break;
   default:
     echo "Unknown response '$response'. Exiting.\n";
     die();
  }
 }
 if ($create_config) {
  $config_template = file_get_contents('dbconfig-sample.php');
  if (!$config_template) {
   echo "Configuration exsample 'dbconfig-sample.php' not found. Exiting.\n";
   die();
  }
  $search = array('localhost', 'mydatabase', 'myusername', 'mypassword', 'timetrack');
  $replace = array($a['l'], $a['n'], $a['u'], $a['p'], $a['t']);
  $config = str_replace($search, $replace, $config_template);
  file_put_contents('dbconfig.php', $config);
 }
 require_once('dbconfig.php');
 error_reporting(E_ALL);
 $conn = mysqli_connect(DB_ADDR, DB_USER, DB_PASS);
 if (!$conn) {
  die("Failed to connect to '".DB_ADDR."' with username '".DB_USER."'!\n");
 }
 $res = mysqli_select_db($conn, DB_DB);
 if (!$res) {
  echo "Failed to connect to '".DB_DB."' with username '".DB_USER."'!\n";
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

 $res = mysqli_query($conn, $query);
 @header('Content-Type: text/plain');
 if ($res) {
  echo "Database table ".DB_DB.".times created succesfully!\n";
 }
 else {
  echo "Could not create table ".DB_DB.".times: ".mysqli_error($conn)."\n";
 }

?>
