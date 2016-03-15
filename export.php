<?php
 require_once('dbconfig.php');
 $conn = mysqli_connect(DB_ADDR, DB_USER, DB_PASS);
 $res = mysqli_select_db($conn, DB_DB);
 if (!$res) {
  die(mysqli_error($conn));
 }
 $limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : NULL;
 $interval = isset($_REQUEST['interval']) ? intval($_REQUEST['interval']) : 0;
 $end = time();
 $start = $end - 24 * 60 * 60;
 if (isset($_REQUEST['latest'])) {
  $limit = intval($_REQUEST['latest']);
  $start = NULL;
  $end = NULL;
 }
 if (isset($_REQUEST['starttime'])) {
  if (is_numeric($_REQUEST['starttime'])) {
   $start = $_REQUEST['starttime'];
  }
  else {
   $start = strtotime($_REQUEST['starttime']);
  }
 }
 if (isset($_REQUEST['endtime'])) {
  if (is_numeric($_REQUEST['endtime'])) {
   $end = $_REQUEST['endtime'];
  }
  else {
   $end = strtotime($_REQUEST['endtime']);
  }
 }
 $act = isset($_REQUEST['act']) ? intval($_REQUEST['act']) : NULL;
 $rat = isset($_REQUEST['rating']) ? intval($_REQUEST['rating']) : NULL;
 $min = isset($_REQUEST['minrating']) ? intval($_REQUEST['minrating']) : NULL;
 $max = isset($_REQUEST['maxrating']) ? intval($_REQUEST['maxrating']) : NULL;
 $with = isset($_REQUEST['with']) ? intval($_REQUEST['with']) : NULL;
 $desc = isset($_REQUEST['description']) ? $_REQUEST['description'] : NULL;

 $acts = array(
"1" => "Sleep",
"2" => "Eating",
"3" => "Other personal care",
"4" => "Main and second job",
"5" => "Employment activities",
"6" => "School and university",
"7" => "Homework",
"8" => "Freetime study",
"9" => "Food preparation",
"10" => "Dish washing",
"11" => "Cleaning dwelling",
"12" => "Other household upkeep",
"13" => "Laundry",
"14" => "Ironing",
"15" => "Handicraft",
"16" => "Gardening",
"17" => "Tending domestic animals",
"18" => "Caring for pets",
"19" => "Walking the dog",
"20" => "Construction and repairs",
"21" => "Shopping and services",
"22" => "Child care",
"23" => "Playing with and teaching kids",
"24" => "Other domestic work",
"25" => "Organisational work",
"26" => "Help to other households",
"27" => "Participatory activities",
"28" => "Visits and feasts",
"29" => "Other social life",
"30" => "Entertainment and culture",
"31" => "Resting",
"32" => "Walking and hiking",
"33" => "Sports and outdoors",
"34" => "Computer and video games",
"35" => "Other computing",
"36" => "Other hobbies and games",
"37" => "Reading books",
"38" => "Other reading",
"39" => "TV and video",
"40" => "Radio and music",
"41" => "Unspecified leisure",
"42" => "Travel to/from work",
"43" => "Travel related to study",
"44" => "Travel related to shopping",
"45" => "Transporting a child",
"46" => "Travel related to other domestic",
"47" => "Travel related to leisure",
"48" => "Unspecified travel",
"49" => "Unspecified");

$locs = array(
"10" => "Unspecified",
"11" => "Home",
"12" => "Second home",
"13" => "Workplace/school",
"14" => "Other's home",
"15" => "Restaurant",
"16" => "Shop, market",
"17" => "Hotel, camping",
"19" => "Other",
"20" => "Unspecified",
"21" => "Walking, waiting",
"22" => "Bicycle",
"23" => "Motorbike",
"24" => "Car",
"29" => "Other private",
"31" => "Public transport");

$with_labels = array("", "alone", "partner", "parent", "kids", "family", "others");

 $select = 'SELECT * FROM '.DB_TABLE.' WHERE 1';
 $stmt = mysqli_query($conn, $select);
 if (!$stmt) {
  header('500 Internal Server Error');
  $values = array('code' => mysqli_errno($conn), 'msg' => mysqli_error($conn));
 }
 else {
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  while ($row = mysqli_fetch_assoc($stmt)) {
   $id = array('index' => array('_index' => 'correl8-time', '_type' => 'time', '_id'=> $row['id']));
   echo json_encode($id)."\n";
   if ($row['with'] == 1) {
    $with = 'Alone';
   }
   else {
    $with = array();
    for ($i=0; $i<=6; $i++) {
     $val = $i;
     if ($row['with'] & pow(2, ($val-1))) {
      $with[] = strtolower($with_labels[$i]);
     }
    }
    $with = join($with, " ");
   }
   $start = strtotime($row['starttime']);
   $end = strtotime($row['endtime']);
   $values = array('timestamp' => date("c", $start),
                   'starttime' => $start,
                   'endtime' => $end,
		   'duration' => ($end - $start),
		   'mainaction' => $acts[$row['mainaction']],
                   'with' => $with,
                   'location' => $locs[$row['location']],
                   'usecomputer' => $row['usecomputer'] ? true : false
		   );
   if ($row['sideaction']) {
    $values['sideaction'] = $acts[$row['sideaction']];
   }
   echo json_encode($values)."\n";
   # if (!$row['location'] || ! $locs[$row['location']]) {
    # var_dump($row);
    # var_dump($with);
    # exit;
   # }
  }
 }

?>
