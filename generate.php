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

 $acts = array(
  "01" => "Sleeping",
  "02" => "Eating",
  "03" => "Other personal care",
  "11" => "Main job and second job",
  "12" => "Activities related to employment",
  "21" => "School or university",
  "22" => "Free time study",
  "30" => "Unspecified household and family care",
  "31" => "Food management",
  "32" => "Household upkeep",
  "33" => "Care for textiles",
  "34" => "Gardening and pet care",
  "35" => "Construction and repairs",
  "36" => "Shopping and services",
  "37" => "Household management",
  "38" => "Childcare",
  "39" => "Help to an adult household member",
  "41" => "Organisational work",
  "42" => "Informal help to other households",
  "43" => "Participatory and religious activities",
  "51" => "Social life",
  "52" => "Entertainment and culture",
  "53" => "Resting - time out",
  "61" => "Physical excercise",
  "62" => "Productive excercise",
  "63" => "Sports related activities",
  "71" => "Arts and hobbies",
  "72" => "Computing",
  "73" => "Games",
  "81" => "Reading",
  "82" => "TV, video and DVD",
  "83" => "Radio and recordings",
  "91" => "Travel to/from work",
  "92" => "Travel related to study",
  "93" => "Travel r. to shopping, services, childcare &c.",
  "94" => "Travel related to voluntary work and meetings",
  "95" => "Travel related to social life",
  "96" => "Travel related to other leisure", 
  "98" => "Travel related to changing locality",
  "90" => "Other or unspecified travel purpose",
  "99" => "Other unspecified time use"
 );
 $locs = array(
  "0" => "Unspecified",
  "10" => "Unspecified",
  "11" => "Home",
  "12" => "Weekend home or holiday apartment",
  "13" => "Workplace or school",
  "14" => "Other people's home",
  "15" => "Restaurant, cafe or pub",
  "16" => "Shopping centres, malls, markets, other shops",
  "17" => "Hotel, guesthouse, camping site",
  "19" => "Other specified location",
  "20" => "Unspecified",
  "21" => "Travelling on foot",
  "22" => "Travelling by bicycle",
  "23" => "Travelling by moped, motorcycle or motorboat",
  "24" => "Travelling by passenger car",
  "29" => "Other or unspecified private transport mode",
  "31" => "Travelling by public transport"
 );

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
        $de = "'".mysqli_real_escape_string($conn, "$acts[$ma] / $acts[$sa]")."'";
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
