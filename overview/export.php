<?php
 require_once('../dbconfig.php');
 $conn = mysqli_connect(DB_ADDR, DB_USER, DB_PASS);
 $res = mysqli_select_db($conn, DB_DB);
 if (!$res) {
  die(mysqli_error($conn));
 }
 $format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'json';
 $limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : NULL;
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
  "93" => "Travel r. to shopping, services, childcare &amp;c.",
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

 $with_labels = array("", "alone", "partner", "parent", "kids", "family", "others");

 $select = 'SELECT * FROM '.DB_TABLE.' WHERE 1 ORDER BY starttime';
 if ($limit) {
  $select .= " LIMIT $limit";
 }
 $stmt = mysqli_query($conn, $select);
 if (!$stmt) {
  header('500 Internal Server Error');
  $values = array('code' => mysqli_errno($conn), 'msg' => mysqli_error($conn));
 }
 else {
  $titlerow = array(
   'subject',
   'timestamp',
   'starttime',
   'endtime',
   'duration',
   'mainaction',
   'sideaction',
   'with',
   'location',
   'usecomputer',
   'rating',
   'description',
  );
  header('Access-Control-Allow-Origin: *');
  $filename = "tracktime-".date('Y-m-d\THis');
  if ($format == 'csv') {
   header('Content-Type: text/csv');
   header("Content-Disposition: attachment; filename=\"$filename.csv\"");
   echo join("\t", $titlerow)."\n";
  }
  if ($format == 'excel') {
   header('Content-Type: application/vnd.ms-excel');
   header("Content-Disposition: attachment; filename=\"$filename.xls\"");
   echo "<table><tr><th>".join("</th><th>", $titlerow)."</th></tr>\n";
  }
  else {
   header('Content-Type: application/json');
   echo "[\n";
  }
  while ($row = mysqli_fetch_assoc($stmt)) {
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
    $with = join(" ", $with);
   }
   $start = strtotime($row['starttime']);
   $end = strtotime($row['endtime']);
   $values = array('subject' => $row['subject'],
                   'timestamp' => date("c", $start),
                   'starttime' => $start,
                   'endtime' => $end,
                   'duration' => ($end - $start),
                   'mainaction' => $acts[$row['mainaction']],
                   'sideaction' => $acts[$row['sideaction']],
                   'with' => $with,
                   'location' => $locs[$row['location']],
                   'usecomputer' => $row['usecomputer'] ? TRUE : FALSE,
                   'rating' => $row['rating'],
                   'description' => $row['description'],
                  );
   if ($row['sideaction']) {
    $values['sideaction'] = $acts[$row['sideaction']];
   }
   if ($format == 'csv') {
    echo join("\t", $values)."\n";
   }
   else if ($format == 'excel') {
    echo "<tr><td>".join("</td><td>", $values)."</td></tr>\n";
   }
   else {
    echo json_encode($values).",\n";
   }
   # if (!$row['location'] || ! $locs[$row['location']]) {
    # var_dump($row);
    # var_dump($with);
    # exit;
   # }
  }
  if ($format == 'excel') {
   echo "</table>\n";
  }
  else if ($format == 'json') {
   echo "]\n";
  }
 }
?>
