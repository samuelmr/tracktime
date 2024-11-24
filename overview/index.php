<title>Aika-analyysi</title>
<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro" rel="stylesheet" />
<style>
 body {
  background-color: #EEE;
  color: #000;
  font-family: Source Sans Pro,sans-serif;
  margin: 0;
  padding: 0;
 }
 header {
  background-color: rgb(37, 55, 100);
  box-shadow: 0px 4px 8px #666;
  color: #FFF;
  overflow: hidden;
  padding: 0.5em 1em;
  position: sticky;
  top: 0;
 }
 h1 {
  float: left;
  width: 13em;
 }
 header a {
  background-color: #FFF;
  border-radius: 0.25em;
  color: rgb(37, 55, 100);
  display: block;
  float: right;
  margin: 1.75em 1em 0.5em 1em;
  padding: 0.5em;
 }
 table {
  border-collapse: collapse;
  float: left;
  margin: 1em;
 }
 table.total {
  position: sticky;
  top: 2em;
 }
 th a, td a {
  color: inherit;
  text-decoration: none;
 }
 table.total th, table.total td {
   background-color: #FFF;
   border: 1px solid #CCC;
   padding: 0 0.5em;
 }
 table.total th {
   text-align: right;
 }
 th {
  color: rgb(37, 55, 100);
  min-width: 3ex;
 }
 td {
/*  border-radius: 0.25em; */
  padding: 0 4px;
  text-align: right;
 }
 td.summary {
  border-color: rgb(37, 55, 100);
  border-style: dashed;
  border-width: 1px 0 0 0;
  color: rgb(37, 55, 100);
  font-weight: bold;
  padding-bottom: 1em;
 }
 td.total {
  font-weight: bold;
 }
 p.total {
  padding: 0 0 0 1em;
 }
 p.error {
  padding: 0 0 0 1em;
  font-family: Consolas,"Courier New",mono-space;
 }
 th a {
  color: #000;
  text-decoration: none;
 }
 th a:hover {
  text-decoration: underline;
 }
 td.unit {
   text-align: left;
 }
 span.subject {
  display: block;
  font-size: x-small;
 }
</style>
<header>
 <h1>Time analysis</h1>
 <div id="download">
  <a href="./export.php?format=csv">Export CSV</a>
  <a href="./export.php?format=excel">Export to Excel</a>
  <a href="./export.php?format=json">Export JSON</a>
 </div>
</header>
<?php

 function mkhref($sy, $sm, $sd, $ey, $em, $ed, $subject=NULL) {
  $st = sprintf("%d-%02d-%02d", $sy, $sm, $sd);
  $et = sprintf("%d-%02d-%02d", $ey, $em, $ed);
  $params = $_REQUEST;
  if ($subject) {
    $params['subject'] = $subject;
  }
  $params['starttime'] = $st;
  $params['endtime'] = $et;
  return "../?".http_build_query($params, '&amp;');
 }

 require_once('../dbconfig.php');
 $conn = mysqli_connect(DB_ADDR, DB_USER, DB_PASS);
 $res = mysqli_select_db($conn, DB_DB);
 if (!$res) {
  header('500 Internal Server Error');
  echo "<h1>Internal Server error</h1>\n";
  echo "<p class=\"error\">MySQL error ".mysqli_errno($conn).": ".
       mysqli_error($conn)."</p>\n";
  exit;
 }

 $hsl_base = 200; // degrees of HSL color wheel

 $select = "SELECT subject, UNIX_TIMESTAMP(starttime) AS `tstamp`".
   ", SUM(UNIX_TIMESTAMP(endtime) - UNIX_TIMESTAMP(starttime)) AS `Time`".
   ", YEAR(starttime) AS `Y`, MONTH(starttime) AS `M`, DAY(starttime) AS `D`".
   " FROM `".DB_TABLE."` t";
 $params = [];
 $oper = ' AND ';
 if (isset($_REQUEST['oper']) && $_REQUEST['oper'] == 'OR') {
  $oper = ' OR ';
 }

 if (isset($_REQUEST['starttime']) && $_REQUEST['starttime']) {
   $params[] = "starttime >= '".date('Y-m-d', strtotime($_REQUEST['starttime']))."'";
 }
 if (isset($_REQUEST['endtime']) && $_REQUEST['endtime']) {
   $params[] = "endtime <= '".date('Y-m-d', strtotime($_REQUEST['endtime']))."'";
 }
 if (isset($_REQUEST['action']) && $_REQUEST['action']) {
   $params[] = "(`mainaction` = ".intval($_REQUEST['action'])." OR `sideaction` = ".intval($_REQUEST['action']).")";
 }
 if (isset($_REQUEST['mainaction']) && $_REQUEST['mainaction']) {
   $params[] = "(`mainaction` = ".intval($_REQUEST['mainaction']).")";
 }
 if (isset($_REQUEST['sideaction']) && $_REQUEST['sideaction']) {
   $params[] = "(`sideaction` = ".intval($_REQUEST['sideaction']).")";
 }
 if (isset($_REQUEST['with']) && $_REQUEST['with']) {
   $params[] = "(`with` & POW(2, ".($_REQUEST['with'] - 1).") != 0)";
 }
 if (isset($_REQUEST['desc']) && $_REQUEST['desc']) {
   $params[] = "description LIKE '%".mysqli_real_escape_string($conn, $_REQUEST['desc'])."%'";
 }
 if (isset($_REQUEST['not']) && $_REQUEST['not']) {
   $params[] = "(description IS NULL OR description NOT LIKE '%".mysqli_real_escape_string($conn, $_REQUEST['not'])."%')";
 }
 if (count($params) > 0) {
  $select .= " WHERE ";
  $select .= implode($oper, $params);
 }

 $select .= " GROUP BY Y, M, D, subject ORDER BY subject, starttime";
 # $select .= " GROUP BY YEAR(starttime), MONTH(starttime), DAY(starttime) ORDER BY starttime";
 # $select .= " GROUP BY DATE_FORMAT(starttime, '%Y%m%d') ORDER BY starttime";
 # echo "<!-- $select -->\n";
 $stmt = mysqli_query($conn, $select);
 if (!$stmt) {
  header('500 Internal Server Error');
  echo "<h1>Internal Server error</h1>\n";
  echo "<p class=\"error\">MySQL error ".mysqli_errno($conn).": ".
       mysqli_error($conn)."</p>\n";
  exit;
 }
 else {
  $days = mysqli_num_rows($stmt) + 1;
  $total = 0;
  $totalmonths = 0;
  $monthly = 0;
  $month = 0;
  $monthnr = 0;
  $lastmonth = 0;
  $lastmonthnr = 0;
  $wday = 0;
  $prev = 0;
  $prevsub = '';
  $leftover = array();
  while ($row = mysqli_fetch_assoc($stmt)) {
   $start = $row['tstamp'];
   $subject = $row['subject'];
   if ($subject != $prevsub) {
    if ($prevsub) {
     if ($mday < 31) {
      echo "<td class=\"that\" colspan=\"".(31-$mday)."\"></td>";
     }
     $bgcolor = "hsla(".($hsl_base-$monthly/1.35).", 75%, 75%, 100%)";
     $from = "$y-$m-01T00:00:00";
     $to = "$y-".sprintf('%02d', $m+1)."-01T00:00:00";
     $href = mkhref($y, $m, 1, $y, $m+1, 1, $subject);
     echo '<td class="total" style="background-color: '.$bgcolor.'">'.
          # "<a href=\"../dashboard.html?subject=".urlencode($subject)."#$from,$to\">".
          "<a href=\"$href\">".
          str_replace('.', ',', sprintf('%.1f', $monthly)).
          "</a>".
          "</td></tr></table>\n";
     $monthly = 0;
     $monthnr = $month;
    }
    echo "<table class=\"days\"><caption>$subject</caption>\n";
    echo "<tr><th>Month</th>";
    for ($d=1; $d<=31; $d++) {
      echo "<th>$d</th>";
    }
    echo "<th>Total</th></tr>\n";
    $init = FALSE;
    $prevsub = $subject;
    $prev = 0;
   }
   $lastts = $start;
   $date = $row['D'].".".$row['M'];
   $year = $row['Y'] - 2000; // simple way of changing year to 2 digit format
   $month = $row['M'];
   $monthnr = date('Ym', $start);
   $mday = $row['D'];
   $time = $row['Time'];
   if (isset($leftover[$subject]) && $leftover[$subject]) {
    $time += $leftover[$subject];
    $leftover[$subject] = 0;
    $start = mktime(0, 0, 0, $month, $mday, $row['Y']);
   }
   $end = $start + $time;
   $midnight = mktime(0, 0, 0, $month, $mday+1, $row['Y']);
   $time_to_midnight = $midnight - $end;
   if ($time_to_midnight < 0) {
    $leftover[$subject] = 0 - $time_to_midnight;
    $time -= $leftover[$subject];
   }
   $dur = $time / 60 / 60;
   $hours = floor($time / 3600);
   $mins = ($time % 3600)/60;
   if ($mins < 10) {
     $mins = '0'.$mins;
   }
   $fmtdur = str_replace('.', ',', sprintf('%.1f', $dur));
   if ($init === FALSE) {
    $init = TRUE;
    list ($y, $m) = str_split($monthnr, 4);
    $firstts = $row['tstamp'];
    $href = mkhref($y, $m, 1, $y, $m+1, 1, $subject);
    echo "<tr><th><a href=\"$href\">$m/$y</a></th>";
    $totalmonths++;
    $lastmonth = $month;
    $lastmonthnr = $monthnr;
   }
   elseif ($monthnr != $lastmonthnr) {
    if ($prev < 31) {
      echo "<td class=\"this\" colspan=\"".(31-$prev)."\">";
    }
    $bgcolor = "hsla(".($hsl_base-$monthly/1.35).", 75%, 75%, 100%)";
    $from = "$y-$m-01T00:00:00";
    $to = "$y-".sprintf('%02d', $m+1)."-01T00:00:00";
    $href = mkhref($y, $m, 1, $y, $m+1, 1, $subject);
    echo '<td class="total" style="background-color: '.$bgcolor.'">'.
         "<a href=\"$href\">".
         str_replace('.', ',', sprintf('%.1f', $monthly)).
         "</a>".
         "</td></tr>\n";
    while ($monthnr - $lastmonthnr > 0) {
     list ($y, $m) = str_split($lastmonthnr, 4);
     $lastmonth++;
     $lastmonthnr++;
     if ($lastmonth > 12) {
       $lastmonth = 1;
       $lastmonthnr = sprintf('%02d', intval($y)+1).'01';
     }
    }
    list ($y, $m) = str_split($lastmonthnr, 4);
    $href = mkhref($y, $m, 1, $y, $m+1, 1, $subject);
    echo "<tr><th><a href=\"$href\">$m/$y</a></th>";
    $totalmonths++;
    $monthly = 0;
    $prev = 0;
    $lastmonth = $month;
    $lastmonthnr = $monthnr;
   }
   if ($diff = ($mday - ($prev + 1))) {
    echo "<td class=\"diff\" colspan=\"$diff\">&nbsp;</td>";
    echo "<!-- $diff = ($mday - ($prev + 1)) -->\n";
   }

   $monthly += $dur;
   $total += $dur;
   $prev = $mday;

   # $bgcolor = "hsla(".($hsl_base-$dur*15).", 75%, 75%, 100%)";
   $from = "$y-$m-".sprintf('%02d', $mday)."T00:00:00";
   $to = "$y-$m-".sprintf('%02d', $mday+1)."T00:00:00";
   $href = mkhref($y, $m, $mday, $y, $m, $mday+1, $subject);
   $bgcolor = "#8C8";
   if ($dur != 23 && $dur != 24 && $dur != 25) {
    $bgcolor = "#C00";
   }
   echo "<td title=\"$date: $fmtdur\" style=\"background-color: $bgcolor\">".
        # "<a href=\"../dashboard.html?subject=".urlencode($subject)."#$from,$to\">".
        "<a href=\"$href\">".
        # "<span class=\"subject\">".htmlentities($subject)."</span> ".
        "$hours:$mins</a></td>\n";
   if ($mday == 31) {
    $monthnr++;
    if ($monthnr > 12) {
     $monthnr = 1;
     $year++;
    }
   }
  }
 }
 if ($mday < 31) {
   echo "<td class=\"that\" colspan=\"".(31-$mday)."\"></td>";
 }
 $bgcolor = "hsla(".($hsl_base-$monthly/1.35).", 75%, 75%, 100%)";
 $from = "$y-$m-01T00:00:00";
 $to = "$y-".sprintf('%02d', $m+1)."-01T00:00:00";
 $href = mkhref($y, $m, 1, $y, $m+1, 1, $subject);

 echo '<td class="total" style="background-color: '.$bgcolor.'">'.
      # "<a href=\"../dashboard.html?subject=".urlencode($subject)."#$from,$to\">".
      "<a href=\"$href\">".
      str_replace('.', ',', sprintf('%.1f', $monthly)).
      "</a>".
      "</td></tr></table>\n";
 $monthly = 0;
 $monthnr = $month;
 $totaltimespan = $lastts - $firstts;
 $totaldays = ceil($totaltimespan/60/60/24);
 $dayaverage = $total/$totaldays;
 $adayaverage = $total/$days;
 $totalweeks = ceil($totaldays/7);
 $weekaverage = $total/$totalweeks;
 $monthaverage = $total/$totalmonths;

 if (isset($_REQUEST['starttime'])) {
  $firstts = strtotime($_REQUEST['starttime']);
 }
 if (isset($_REQUEST['endtime'])) {
  $lastts = strtotime($_REQUEST['endtime']);
 }
 $from = date('Y-m-d\TH:i:s', $firstts);
 $to = date('Y-m-d\TH:i:s', $lastts);

 /*

 echo '<table class="total">'."\n";
 echo '<tr class="hour"><th>Hours</th><td></td><td class="total">'.
      "<a href=\"../dashboard.html#$from,$to\">".
      number_format($total, 1, ',', '&nbsp;').
      "</a>".
      "</td><td class=\"unit\">h</td></tr>\n";
 echo '<tr class="hour"><th>Man days</th><td></td><td class="total">'.
      number_format($total/7.5, 1, ',', '&nbsp;').
      "</td><td class=\"unit\">mwd</td></tr>\n";
 echo '<tr class="week"><th>Months</th><td>'.$totalmonths.
      '</td><td class="total">'.
      number_format($monthaverage, 1, ',', '&nbsp;').
      "</td><td class=\"unit\">h/month</td></tr>\n";
 echo '<tr class="week"><th>Weeks</th><td>'.$totalweeks.
      '</td><td class="total">'.
      number_format($weekaverage, 1, ',', '&nbsp;').
      "</td><td class=\"unit\">h/week</td></tr>\n";
 echo '<tr class="aday"><th>Active days</th><td>'.$days.
      '</td><td class="total">'.
      number_format($adayaverage, 1, ',', '&nbsp;').
      "</td><td class=\"unit\">h/day</td></tr>\n";
 echo '<tr class="day"><th>Days</th><td>'.$totaldays.
      '</td><td class="total">'.
      number_format($dayaverage, 1, ',', '&nbsp;').
      "</td><td class=\"unit\">h/day</td></tr>\n";
*/

?>
