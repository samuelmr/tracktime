<?php
 require_once('dbconfig.php');
 error_reporting(E_ALL);
 $conn = mysqli_connect(DB_ADDR, DB_USER, DB_PASS);
 $res = mysqli_select_db($conn, DB_DB);
 if (!$res) {
  die(mysqli_error($conn));
 }

 $response = array();

 $id = 0;
 if (isset($_REQUEST['id']) && $_REQUEST['id']) {
  $id = intval($_REQUEST['id']);
 }
 $subject = isset($_REQUEST['subject']) ? mysqli_real_escape_string($conn, $_REQUEST['subject']) : '';

 if ($id) {
  $where = "`id`=$id" . ($subject ? " AND `subject`='$subject'" : '');
  $query = 'DELETE FROM '.DB_TABLE." WHERE $where";
  $result = mysqli_query($conn, $query);
  if ($result) {
   $response[] = "Deleted entry $id";
  }
  else {
   http_response_code(500);
   trigger_error($query, E_USER_WARNING);
   $code = mysqli_errno($conn);
   $msg = mysqli_error($conn);
   $response = array('code' => $code, 'msg' => $msg);
  }
 }
 else {
  http_response_code(400);
  $response = array('code' => 400, 'msg' => 'Required parameter: id');
 }

 $json = json_encode($response);
 header('Access-Control-Allow-Origin: *');
 header('Content-Type: application/json');
 if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
     in_array('gzip', explode(',', $_SERVER['HTTP_ACCEPT_ENCODING']))) {
  header('Content-Encoding: gzip');
  $json = gzencode($json);
 }
 header('Content-Length: '.strlen($json));
 echo $json;

?>
