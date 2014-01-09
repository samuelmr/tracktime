<?php
 require_once('dbconfig.php');
 $conn = mysqli_connect(DB_ADDR, DB_USER, DB_PASS);
 $res = mysqli_select_db($conn, DB_DB);
 if (!$res) {
  die(mysqli_error($conn));
 }
 $latest = isset($_REQUEST['latest']) ? intval($_REQUEST['latest']) : NULL;

 $select = 'SELECT * FROM '.DB_TABLE.' ORDER BY endtime DESC, id DESC';
 if ($latest) {
  $select .= ' LIMIT '.$latest;
 }

 $stmt = mysqli_query($conn, $select);
 if (!$stmt) {
  header('500 Internal Server Error');
  $values = array('code' => mysqli_errno($conn), 'msg' => mysqli_error($conn));
 }
 else {
  $values = array();
  while ($row = mysqli_fetch_assoc($stmt)) {
   $values[] = $row;
  }
 }
 $json = json_encode($values);
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
