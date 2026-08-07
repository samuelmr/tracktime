<?php
 require_once('dbconfig.php');
 error_reporting(E_ALL);
 $conn = mysqli_connect(DB_ADDR, DB_USER, DB_PASS);
 $res = mysqli_select_db($conn, DB_DB);
 if (!$res) {
  die(mysqli_error($conn));
 }

 $response = array();

 # echo "<pre>\n";
 # var_dump($_REQUEST);
 # echo "</pre>\n";
 if (isset($_REQUEST['id']) && $_REQUEST['id']) {
  $id = intval($_REQUEST['id']);
 }
 $subject = $_REQUEST['subject'];
 $starttime = $_REQUEST['starttime'];
 $endtime = $_REQUEST['endtime'];
 $mainaction = mysqli_real_escape_string($conn, sprintf("%'02.2s", $_REQUEST['mainaction']));
 $values = array('subject' => $subject,
                 'starttime' => $starttime,
                 'endtime' => $endtime,
                 'mainaction' => $mainaction);
 if (isset($_REQUEST['sideaction']) && $_REQUEST['sideaction']) {
  $sideaction = mysqli_real_escape_string($conn, sprintf("%'02.2s", $_REQUEST['sideaction']));
  $values['sideaction'] = $sideaction;
 }
 $with = 0;
 if (isset($_REQUEST['with'])) {
  if (is_array($_REQUEST['with'])) {
   for ($i=0; $i<count($_REQUEST['with']); $i++) {
    $with += pow(2, intval($_REQUEST['with'][$i])-1);
   }
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
 if (isset($_REQUEST['usecomputer']) && $_REQUEST['usecomputer']) {
  $usecomputer = $_REQUEST['usecomputer'] ? 1 : 0;
  $values['usecomputer'] = $usecomputer;
 }
 if (isset($_REQUEST['location']) && $_REQUEST['location']) {
  $location = intval($_REQUEST['location']);
  $values['location'] = $location;
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
            (isset($usecomputer) ? ', usecomputer' : '').
            (isset($location) ? ', location' : '').
            (isset($description) ? ', description' : '').
            (isset($rating) ? ', rating' : '').
            (isset($subject) ? ', subject' : '').
            ") VALUES (".
            "'".mysqli_real_escape_string($conn, $starttime)."'".
            ", '".mysqli_real_escape_string($conn, $endtime)."'".
            ", '$mainaction'".
            ", $with".
            (isset($id) ? ", $id" : '').
            (isset($sideaction) ? ", '$sideaction'" : '').
            (isset($usecomputer) ? ", $usecomputer" : '').
            (isset($location) ? ", $location" : '').
            # (isset($description) ? ", '".mysqli_real_escape_string($conn, utf8_decode($description))."'" : '').
            (isset($description) ? ", '".mysqli_real_escape_string($conn, $description)."'" : '').
            (isset($rating) ? ", $rating" : '').
            (isset($subject) ? ", '".mysqli_real_escape_string($conn, $subject)."'" : '').
            ')';
  $result = mysqli_query($conn, $insert);
  if ($result) {
   $values['id'] = isset($id) ? $id : mysqli_insert_id($conn);
   $response[] = $values;
  }
  else {
   http_response_code(500);
   trigger_error($insert, E_USER_WARNING);
   $code = mysqli_errno($conn);
   $msg = mysqli_error($conn);
   $response = array('code' => $code, 'msg' => $msg);
  }
 }
 else {
  http_response_code(400);
  $code = '400';
  $msg = 'Required parameters: subject, starttime, endtime, mainaction, with[]';
  $response = array('code' => $code, 'msg' => $msg);
 }

 $json = json_encode($response);
 header('Access-Control-Allow-Origin: *');
 header('Content-Type: application/json');
if (!empty($_SERVER['HTTP_ACCEPT_ENCODING']) && stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
  header('Content-Encoding: gzip');
  $json = gzencode($json);
 }
 header('Content-Length: '.strlen($json));
 echo $json;

?>
