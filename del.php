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
 if ($id) {
  $query = 'DELETE FROM '.DB_TABLE." WHERE `id`=$id";
  $result = mysqli_query($conn, $query);
  if ($result) {
   $response[] = "Deleted entry $id";
  }
  else {
   header('500 Internal Server Error');
   trigger_error($insert, E_USER_WARNING);
   $code = mysqli_errno($conn);
   $msg = mysqli_error($conn);
   $response = array('code' => $code, 'msg' => $msg);
  }
 }
 else {
  header('400 Bad Request');
  $code = '400';
  $msg = 'Required parameter: id';
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
