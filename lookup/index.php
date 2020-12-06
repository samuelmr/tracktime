<title>Aika-analyysi</title>
<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro" rel="stylesheet" />
<style>
 body {
  color: #000;
  font-family: Source Sans Pro,sans-serif;
  margin: 0;
  padding: 0;
 }
 h1 {
  background-color: rgb(37, 55, 100);
  box-shadow: 0px 4px 8px #666;
  color: #FFF;
  padding: 0.5em 1em;
 }
 th {
  color: rgb(37, 55, 100);
 }
 td {
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
</style>
<h1>Time analysis</h1>
<?php
 require_once('dbconfig.php');
 $conn = mysqli_connect(DB_ADDR, DB_USER, DB_PASS);
 $res = mysqli_select_db($conn, DB_DB);
 if (!$res) {
  header('500 Internal Server Error');
  echo "<h1>Internal Server error</h1>\n";
  echo "<p class=\"error\">MySQL error ".mysqli_errno($conn).": ".
       mysqli_error($conn)."</p>\n";
  exit;
 }

 $select = "SELECT UNIX_TIMESTAMP(starttime) AS `tstamp`".
   ", SUM(UNIX_TIMESTAMP(endtime) - UNIX_TIMESTAMP(starttime)) AS `Time`".
   ", YEAR(starttime) AS `Y`, MONTH(starttime) AS `M`, DAY(starttime) AS `D`".
   " FROM `times` t";
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
 if (count($params) > 0) {
  $select .= " WHERE ";
  $select .= implode($oper, $params);
 }
 
 # $select .= " GROUP BY YEAR(starttime), MONTH(starttime), DAY(starttime) ORDER BY starttime";
 $select .= " GROUP BY Y, M, D ORDER BY starttime";
 # $select .= " GROUP BY DATE_FORMAT(starttime, '%Y%m%d') ORDER BY starttime";
 echo "<!-- $select -->\n";
 $stmt = mysqli_query($conn, $select);
 if (!$stmt) {
  header('500 Internal Server Error');
  echo "<h1>Internal Server error</h1>\n";
  echo "<p class=\"error\">MySQL error ".mysqli_errno($conn).": ".
       mysqli_error($conn)."</p>\n";
  exit;
 }
 else {
  echo "<table>\n";
  echo "<tr><th>Month</th>";
  for ($d=1; $d<=31; $d++) {
    echo "<th>$d</th>";
  }
  echo "<th>Total</th></tr>\n";
  echo "<tr>";
  $total = 0;
  $monthly = 0;
  $month = 0;
  $monthnr = 0;
  $lastmonth = 0;
  $lastmonthnr = 0;
  $wday = 0;
  $prev = 0;
  $init = FALSE;
  while ($row = mysqli_fetch_assoc($stmt)) {
   # $date = date('j.n.', $row['tstamp']);
   $date = $row['D'].".".$row['M'];
   echo "\n<!-- $date: $row[Y], $row[M], $row[D] ($row[tstamp]) -->";
   # $year = date('y', $row['tstamp']);
   # $month = date('n', $row['tstamp']);
   $year = $row['Y'];
   $month = $row['M'];
   $monthnr = date('ym', $row['tstamp']);
   # $mday = date('j', $row['tstamp']);
   $mday = $row['D'];
   $time = $row['Time'];
   $dur = $time / 60 / 60;
   $hours = floor($time / 3600);
   $mins = ($time % 3600)/60;
   if ($mins < 10) {
     $mins = '0'.$mins;
   }
   $fmtdur = str_replace('.', ',', sprintf('%.1f', $dur));
   if ($init === FALSE) {
    $init = TRUE;
    echo "<tr><td>$month/$year</td>";
    $lastmonth = $month;
    $lastmonthnr = $monthnr;
   }
   elseif ($monthnr != $lastmonthnr) {
    if ($prev != 31) {
      echo "<td class=\"this\" colspan=\"".(31-$prev)."\">";
    }
    $bgcolor = "hsl(".(120-$monthly/3).", 75%, 75%)";
    echo '<td class="total" style="background-color: '.$bgcolor.'">'.
         str_replace('.', ',', sprintf('%.1f', $monthly)).
         "</td></tr>\n";
    while ($monthnr - $lastmonthnr > 0) {
     list ($y, $m) = str_split($lastmonthnr, 2);
     # echo "<tr><td>$m/$y</td><td colspan=\"31\">&nbsp;</td><td>0</td></tr>\n";
     $lastmonth++;
     $lastmonthnr++;
     if ($lastmonth > 12) {
       $lastmonth = 1;
       $lastmonthnr = sprintf('%02d', intval($y)+1).'01';
     }
    }
    list ($y, $m) = str_split($lastmonthnr, 2);
    echo "<tr><td>$m/$y</td>";
    $monthly = 0;
    $prev = 0;
    $lastmonth = $month;
    $lastmonthnr = $monthnr;
   }
   if ($diff = ($mday - ($prev + 1))) {
    echo "<td colspan=\"$diff\">&nbsp;</td>";
    echo "<!-- $diff = ($mday - ($prev + 1)) -->\n";
   }

   $monthly += $dur;
   $total += $dur;
   $prev = $mday;

   $bgcolor = "hsl(".(120-$dur*10).", 75%, 75%)";
   echo "<td title=\"$date: $fmtdur\" style=\"background-color: $bgcolor\">$hours:$mins</td>";
   if ($mday == 31) {
    $monthnr++;
    if ($monthnr > 12) {
     $monthnr = 1;
     $year++;
    }
   }
  }
 }
 if ($mday !== 31) {
   echo "<td class=\"that\" colspan=\"".(31-$mday)."\"></td>";
 }
 echo '<td class="total">'.
      str_replace('.', ',', sprintf('%.1f', $monthly))."</td></tr>\n";
 $monthly = 0;
 $monthnr = $month;
?>
</table>
<?php
  echo '<p class="total">Yhteensä <strong>'.
       str_replace('.', ',', sprintf('%.1f', $total)).
       "</strong> tuntia (<strong>".
       str_replace('.', ',', sprintf('%.1f', $total/7.5)).
       "</strong> ty&ouml;p&auml;iv&auml;&auml;).</p>\n";
?>
