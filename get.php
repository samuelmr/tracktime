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

 if ($interval) {
  $select = 'SELECT starttime, endtime, mainaction FROM '.DB_TABLE.
            ' ORDER BY starttime';
  $stmt = mysqli_query($conn, $select);
  if (!$stmt) {
   header('500 Internal Server Error');
   $values = array('code' => mysqli_errno($conn), 'msg' => mysqli_error($conn));
  }
  else {
   $values = array();
   while ($row = mysqli_fetch_assoc($stmt)) {
    $start = strtotime($row['starttime']);
    $end = strtotime($row['endtime']);
    for ($st = $start; $st <= $end; $st += $interval) {
     $timekey = date('H:i', $st);
     if (!$values[$timekey]) {
      $values[$timekey] = array();
     }
     $values[$timekey][$row['mainaction']] += 1;
    }
   }
   $json = json_encode($values);
   header('Access-Control-Allow-Origin: *');
   header('Access-Control-Allow-Credentials: true');
   header('Content-Type: application/json');
   header('Content-Length: '.strlen($json));
   if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
       (in_array('gzip', explode(',', $_SERVER['HTTP_ACCEPT_ENCODING'])))) {
    header('Content-Encoding: gzip');
    echo gzencode($json);
   }
   else {
    echo $json;
   }
  }
  exit;
 }

 $select = 'SELECT * FROM '.DB_TABLE.' WHERE 1';
 if ($start) {
  $select .= " AND endtime >= '".date('Y-m-d H:i:s',$start)."'";
 }
 if ($end) {
  $select .= " AND starttime <= '".date('Y-m-d H:i:s',$end)."'";
 }
 if ($act) {
  $select .= ' AND (mainaction = '.$act.' OR sideaction = '.$act.')';
 }
 if ($rat) {
  $select .= ' AND rating = '.$rat;
 }
 if ($min) {
  $select .= ' AND rating >= '.$min;
 }
 if ($max) {
  $select .= ' AND rating <= '.$max;
 }
 if ($desc) {
  $select .= " AND description LIKE '%".mysqli_real_escape_string($desc)."%'";
 }
 if ($with) {
  $select .= ' AND with = '.$with;
 }
 $select .= ' ORDER BY endtime DESC, starttime DESC, id DESC';
 if ($limit) {
  $select .= ' LIMIT '.$limit;
 }
 # trigger_error($select, E_USER_NOTICE);

 $stmt = mysqli_query($conn, $select);
 if (!$stmt) {
  header('500 Internal Server Error');
  $values = array('code' => mysqli_errno($conn), 'msg' => mysqli_error($conn));
 }
 else {
  $values = array();
  while ($row = mysqli_fetch_assoc($stmt)) {
   $row = array_filter($row, 'is_not_null');
   # $row['description'] = isset($row['description']) ? utf8_encode($row['description']) : '';
   $values[] = $row;
  }
 }
 $json = json_encode($values);
 # trigger_error(count($values), E_USER_WARNING);
 header('Access-Control-Allow-Origin: *');
 header('Content-Type: application/json');
 if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
     in_array('gzip', explode(',', $_SERVER['HTTP_ACCEPT_ENCODING']))) {
  header('Content-Encoding: gzip');
  $json = gzencode($json);
 }
 header('Content-Length: '.strlen($json));
 echo $json;

 function is_not_null($val){
   return !is_null($val);
 }
    
?>
