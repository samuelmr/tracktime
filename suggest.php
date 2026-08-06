<?php
 require_once('dbconfig.php');
 $conn = mysqli_connect(DB_ADDR, DB_USER, DB_PASS);
 $res = mysqli_select_db($conn, DB_DB);
 if (!$res) {
  die(mysqli_error($conn));
 }

 $subject = isset($_REQUEST['subject']) ? mysqli_real_escape_string($conn, $_REQUEST['subject']) : '';
 $starttime = isset($_REQUEST['starttime']) ? $_REQUEST['starttime'] : date('Y-m-d H:i:s');
 # lat/lng accepted but reserved for future GPS-based location scoring
 # $lat = isset($_REQUEST['lat']) ? (float)$_REQUEST['lat'] : null;
 # $lng = isset($_REQUEST['lng']) ? (float)$_REQUEST['lng'] : null;

 if (!$subject) {
  http_response_code(400);
  echo json_encode(array('code' => 400, 'msg' => 'subject is required'));
  exit;
 }

 $ts = is_numeric($starttime) ? (int)$starttime : strtotime($starttime);
 if (!$ts) {
  http_response_code(400);
  echo json_encode(array('code' => 400, 'msg' => 'invalid starttime'));
  exit;
 }

 # day type: weekday (Mon–Fri) vs weekend (Sat–Sun), using PHP date('N') 1=Mon 7=Sun
 $dayN = (int)date('N', $ts);
 $isWeekend = $dayN >= 6;

 # time window: ±90 minutes of the requested time, as minutes-of-day
 $centerMinute = (int)date('H', $ts) * 60 + (int)date('i', $ts);
 $windowWidth = 90;
 $minMinute = ($centerMinute - $windowWidth + 1440) % 1440;
 $maxMinute = ($centerMinute + $windowWidth) % 1440;
 $wraps = $minMinute > $maxMinute;

 # DAYOFWEEK: 1=Sun, 2=Mon ... 7=Sat
 $dayFilter = $isWeekend
  ? 'DAYOFWEEK(starttime) IN (1, 7)'
  : 'DAYOFWEEK(starttime) NOT IN (1, 7)';

 $timeExpr = '(HOUR(starttime)*60 + MINUTE(starttime))';
 $timeFilter = $wraps
  ? "($timeExpr >= $minMinute OR $timeExpr <= $maxMinute)"
  : "$timeExpr BETWEEN $minMinute AND $maxMinute";

 $select = 'SELECT mainaction, sideaction, location, `with`, usecomputer,'.
           ' COUNT(*) AS freq, MAX(starttime) AS last_seen'.
           ' FROM '.DB_TABLE.
           " WHERE subject = '$subject'".
           " AND $dayFilter".
           " AND $timeFilter".
           ' GROUP BY mainaction, sideaction, location, `with`, usecomputer'.
           ' ORDER BY freq DESC'.
           ' LIMIT 20';

 $stmt = mysqli_query($conn, $select);
 if (!$stmt) {
  http_response_code(500);
  echo json_encode(array('code' => mysqli_errno($conn), 'msg' => mysqli_error($conn)));
  exit;
 }

 $rows = array();
 while ($row = mysqli_fetch_assoc($stmt)) {
  $rows[] = $row;
 }

 # fetch the most recent entry before starttime for last-entry boost
 $lastSelect = 'SELECT mainaction FROM '.DB_TABLE.
               " WHERE subject = '$subject'".
               " AND endtime <= '".date('Y-m-d H:i:s', $ts)."'".
               ' ORDER BY endtime DESC, id DESC'.
               ' LIMIT 1';
 $lastStmt = mysqli_query($conn, $lastSelect);
 $lastAction = null;
 if ($lastStmt && $lastRow = mysqli_fetch_assoc($lastStmt)) {
  $lastAction = $lastRow['mainaction'];
 }

 # score each candidate: frequency × recency decay × optional last-entry boost
 $now = time();
 $scored = array();
 foreach ($rows as $row) {
  $daysSince = ($now - strtotime($row['last_seen'])) / 86400;
  # exponential decay: full weight ~recently, ~20% weight after ~4 months
  $recency = max(0.2, exp(-$daysSince / 60));
  $score = (int)$row['freq'] * $recency;
  # boost if this is a continuation of the previous activity
  if ($lastAction !== null && $row['mainaction'] === $lastAction) {
   $score *= 1.3;
  }
  $scored[] = array('row' => $row, 'score' => $score);
 }

 usort($scored, function($a, $b) {
  return $b['score'] <=> $a['score'];
 });

 $output = array();
 foreach (array_slice($scored, 0, 5) as $s) {
  $row = $s['row'];
  $item = array('mainaction' => $row['mainaction']);
  if ($row['sideaction'] !== null) $item['sideaction'] = $row['sideaction'];
  if ($row['location'] !== null) $item['location'] = (int)$row['location'];
  $item['with'] = (int)$row['with'];
  if ($row['usecomputer'] !== null) $item['usecomputer'] = (bool)$row['usecomputer'];
  $item['freq'] = (int)$row['freq'];
  $output[] = $item;
 }

 $json = json_encode($output);
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
