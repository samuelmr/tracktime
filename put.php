<?php
 require_once('dbconfig.php');
 error_reporting(E_ALL);
 $conn = mysqli_connect('localhost', DB_USER, DB_PASS);
 $res = mysqli_select_db($conn, DB_DB);
 if (!$res) {
  die(mysqli_error($conn));
 }

 $response = array();

 if (isset($_REQUEST['id']) && $_REQUEST['id']) {
  $id = intval($_REQUEST['id']);
 }
 $starttime = $_REQUEST['starttime'];
 $endtime = $_REQUEST['endtime'];
 $mainaction = sprintf('%02d', $_REQUEST['mainaction']);
 $values = array('starttime' => $starttime,
                 'endtime' => $endtime,
                 'mainaction' => $mainaction);
 if (isset($_REQUEST['sideaction']) && $_REQUEST['sideaction']) {
  $sideaction = sprintf('%02d', $_REQUEST['sideaction']);
  $values['sideaction'] = $sideaction;
 }
 $with = 0;
 if (isset($_REQUEST['with'])) {
  if (is_array($_REQUEST['with'])) {
   $with = intval(array_sum($_REQUEST['with']));
  }
  elseif (is_numeric($_REQUEST['with'])) {
   $with = intval($_REQUEST['with']);
  }
 }
 $values['with'] = $with;
 if (isset($_REQUEST['description']) && $_REQUEST['description']) {
  $description = $_REQUEST['description'];
  $values['description'] = $description;
 }
 if (isset($_REQUEST['rating']) && $_REQUEST['rating']) {
  $rating = intval($_REQUEST['rating']);
  $values['rating'] = $rating;
 }

 if ($starttime && $endtime && $mainaction) {
  $insert = 'REPLACE INTO '.DB_TABLE.
            ' (starttime, endtime, mainaction, `with`'.
            (isset($id) ? ', id' : '').
            (isset($sideaction) ? ', sideaction' : '').
            (isset($description) ? ', description' : '').
            (isset($rating) ? ', rating' : '').
            ") VALUES (".
            "'".mysqli_real_escape_string($conn, $starttime)."'".
            ", '".mysqli_real_escape_string($conn, $endtime)."'".
            ", '$mainaction'".
            ", $with".
            (isset($id) ? ", $id" : '').
            (isset($sideaction) ? ", '$sideaction'" : '').
            (isset($description) ? ", '".mysqli_real_escape_string($conn, $description)."'" : '').
            (isset($rating) ? ", $rating" : '').
            ')';
  $result = mysqli_query($conn, $insert);
  if ($result) {
   $values['id'] = isset($id) ? $id : mysqli_insert_id($conn);
   $response[] = $values;
  }
  else {
   header('500 Internal Server Error');
   trigger_error($insert, E_USER_NOTICE);
   $code = mysqli_errno($conn);
   $msg = mysqli_error($conn);
   $response = array('code' => $code, 'msg' => $msg);
  }
 }
 else {
  header('400 Bad Request');
  $code = '400';
  $msg = 'Required parameters: starttime, endtime, mainaction, with[]';
  $response = array('code' => $code, 'msg' => $msg);
 }

 $json = json_encode($response);
 header('Access-Control-Allow-Origin: *');
 header('Content-Type: application/json');
 header('Content-Length: '.strlen($json));
 if (in_array('gzip', explode(',', $_SERVER['HTTP_ACCEPT_ENCODING']))) {
  header('Content-Encoding: gzip');
  echo gzencode($json);
 }
 else {
  echo $json;
 }

?>
