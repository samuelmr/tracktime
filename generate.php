<?php
 require_once('dbconfig.php');
 $conn = mysqli_connect(DB_ADDR, DB_USER, DB_PASS);
 $res = mysqli_select_db($conn, DB_DB);
 if (!$res) {
  die(mysqli_error($conn));
 }

 $SUBJECTS = 13;
 $MIN_DAYS = 7;
 $MAX_DAYS = 14;
 $START_DATE = mktime(0, 0, 0, 1, 1, 2025);
 $END_DATE = mktime(0, 0, 0, 31, 12, 2025);

 $actfile = '../activities.json';
 $json = file_get_contents($actfile);
 $activities = json_decode($json, TRUE);

 $acts = array("" => "", NULL => "");
 for ($i=0; $i<count($activities); $i++) {
  $cat = $activities[$i];
  if (isset($cat['activities'])) {
   for ($j=0; $j<count($cat['activities']); $j++) {
    $a = $cat['activities'][$j];
    $acts[$a['id']] = $a[$lang];
   }
  }
  else {
   $a = $cat;
   $acts[$a['id']] = $a[$lang];
  }
 }

 $locfile = '../locations.json';
 $json = file_get_contents($locfile);
 $locations = json_decode($json, TRUE);

 $locs = array("" => "", NULL => "");
 for ($i=0; $i<count($locations); $i++) {
  $cat = $locations[$i];
  if (isset($cat['options'])) {
   for ($j=0; $j<count($cat['options']); $j++) {
    $l = $cat['options'][$j];
    $locs[$l['id']] = $l[$lang];
   }
  }
  else {
   $l = $cat;
   $locs[$l['id']] = $l[$lang];
  }
 }

 $with_labels = array("alone", "partner", "parent", "kids", "family", "others");

 $truncate = "TRUNCATE TABLE ".DB_TABLE;
 $result = mysqli_query($conn, $truncate);

 $inserts = 0;
 for ($i=0; $i<$SUBJECTS; $i++) {
    $id = substr(md5(microtime()), rand(0, 22), 8);
    $st = mktime(0, 0, 0, rand(1, 12), rand(1, 31), 2023);
    $et = $st + 7 * 24 * 60 * 60; // one week
    echo "$id $st - $et\n";
    while ($st < $et) {
        $sts = date("Y-m-d H:i:s", $st);
        $st += floor(rand(1, 8 * 6)) * 10 * 60; // between 10 minutes and 8 hours
        if ($st > $et) {
          $st = $et;
        }
        $ets = date("Y-m-d H:i:s", $st);
        $ma = array_rand($acts);
        $sa = array_rand($acts);
        $wi = pow(2, array_rand($with_labels));
        $lo = array_rand($locs);
        $uc = floor(rand(0, 1));
        $de = "'".mysqli_real_escape_string($conn, "$acts[$ma] / $acts[$sa] @ $locs[$lo]")."'";
        $ra = floor(rand(0, 5));
        $insert = "INSERT INTO ".DB_TABLE." (`subject`, `starttime`, `endtime`, `mainaction`, `sideaction`, `with`, `location`, `rating`, `description`)".
         " VALUES('$id', '$sts', '$ets', '$ma', '$sa', $wi, $lo, $ra, $de)";
        $result = mysqli_query($conn, $insert);
        if ($result) {
         $values['id'] = isset($id) ? $id : mysqli_insert_id($conn);
         $inserts++;
        }
        else {
         echo mysqli_errno($conn).": ".mysqli_error($conn)."\n";
        }      
    }
 }

?>
