<title>Ajank&auml;ytt&ouml;</title>
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
 h1 input[type=text] {
  font-size: inherit;
  padding: 0 0.25em;
 }
 tr:hover td {
  background-color: #CCC;
 }
 th {
  background-color: #FFF;
  color: rgb(37, 55, 100);
  position: sticky;
  top: 0px;
 }
 td {
  padding: 0 1em;
  text-align: right;
 }
 .weeksummary td {
  border-color: rgb(37, 55, 100);
  border-style: dashed;
  border-width: 1px 0 0 0;
  color: rgb(37, 55, 100);
  font-weight: bold;
  padding-bottom: 1em;
 }
 tr.yeartotal td {
/*
  background-color: #666;
  color: #FFF;
*/
  font-size: small;
  font-style: italic;
  font-weight: bold;
  padding-bottom: 2em;
 }
 td.total {
  font-weight: bold;
 }
 td.total.ok {
  color: #060;
 }
 td.total.warning {
  color: #930;
 }
 td.total.overload {
  color: #600;
 }
 p.total {
  padding: 0 0 0 1em;
 }
 p.error {
  padding: 0 0 0 1em;
  font-family: Consolas,"Courier New",mono-space;
 }
</style>
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
 $query = isset($_SERVER['QUERY_STRING']) ? urldecode($_SERVER['QUERY_STRING']) : '';

 $select = "SELECT UNIX_TIMESTAMP(t.starttime) AS `tstamp`, YEAR(t.starttime) AS `year`, SEC_TO_TIME(SUM(UNIX_TIMESTAMP(t.endtime) - UNIX_TIMESTAMP(t.starttime))) AS `Time` FROM `times` t WHERE description LIKE '%".mysqli_real_escape_string($conn, $query)."%' GROUP BY YEAR(starttime), MONTH(starttime), DAY(starttime) ORDER BY t.starttime";
 $stmt = mysqli_query($conn, $select);
 if (!$stmt) {
  header('500 Internal Server Error');
  echo "<h1>Internal Server error</h1>\n";
  echo "<p class=\"error\">MySQL error ".mysqli_errno($conn).": ".
       mysqli_error($conn)."</p>\n";
  exit;
 }
 else {
?>
<form onsubmit="window.location.href='./?' + encodeURIComponent(this.query.value); return false;">
<h1>Aikaa k&auml;ytetty: <input type="text" size="12" id="query" value="<?php echo htmlentities($query); ?>" /><input type="submit" value="P&auml;ivit&auml;" /></h1>
</form>
<table>
<?php
  $total = 0;
  $yeartotal = 0;
  $weekly = 0;
  $week = 0;
  $lastweek = 0;
  $currentyear = 0;
  $lastweeksyear = 0;
  $wday = 0;
  $prev = 0;
  $init = FALSE;
  while ($row = mysqli_fetch_assoc($stmt)) {
   $date = date('j.n.', $row['tstamp']);
   $week = date('W', $row['tstamp']);
   $wday = date('w', $row['tstamp']);
   # $year = date('Y', $row['tstamp']);
   $year = intval($row['year']);
   echo "<!-- $date $week/$year ($lastweek/$currentyear [$lastweeksyear]) -->\n";
   if ($wday == 0) {
    $wday = 7;
   }
   $time = $row['Time'];
   list($hour, $min, $sec) = explode(':', $time);
   $dur = $hour + ($min/60);
   $fmtdur = date('G:i', strtotime($time));

   if ($init === FALSE) {
    $init = TRUE;
    $currentyear = $year;
    echo <<<EOH
<thead>
<tr><th>$currentyear</th><th>Ma</th><th>Ti</th><th>Ke</th><th>To</th><th>Pe</th><th>La</th><th>Su</th><th>Yht</th></tr>
</thead>
<tbody>

EOH;
    echo "<tr><td>$week</td>";
    $lastweek = $week;
   }
   elseif ($week != $lastweek) {
    if ($prev !== 7) {
     echo "<td colspan=\"".(7-$prev)."\">";
    }
    $prev = 7;
    $class = 'ok';
    if ($weekly > 20) {
     $class = 'overload';
    }
    elseif ($weekly > 7) {
     $class = 'warning';
    }
    echo '<td class="total HERE '.$class.'">'.
         str_replace('.', ',', sprintf('%.1f', $weekly)).
         "</td></tr>\n";
   }
   if (intval($year) > intval($currentyear)) {
    if ($prev !== 7) {
     echo "<td colspan=\"".(7-$prev)."\">";
     echo '<td class="total FILLER">'.
          str_replace('.', ',', sprintf('%.1f', $weekly))."</td></tr>\n";
     $weekly = 0;
     $weeknr = $week;
    }
    $prev = 0;
/*
    if ($year == $lastweeksyear) {
     while ($lastweek < date('W', mktime(0, 0, 0, 12, 28, $lastweeksyear))) {
      $lastweek++;
      echo "<tr><td>".sprintf('%02d', $lastweek).
           "</td><td colspan=\"7\">&nbsp;</td><td>0</td></tr>\n";
     }
    }
*/
echo "<tr class=\"yeartotal\"><td colspan=\"8\">$currentyear yhteens&auml;</td><td>".str_replace('.', ',', sprintf('%.01f', $yeartotal))."</td></tr>\n";
    $yeartotal = 0;
    $currentyear = $year;
    $lastweeksyear = $year;
    echo <<<EOH
<tr><th>$year</th><th>Ma</th><th>Ti</th><th>Ke</th><th>To</th><th>Pe</th><th>La</th><th>Su</th><th>Yht</th></tr>

EOH;
/*
    while ((intval($week) - intval($lastweek)) > 1) {
     $lastweek++;
     echo "<tr><td>".sprintf('%02d', $lastweek).
          "</td><td colspan=\"7\">&nbsp;</td><td>0</td></tr>\n";
    }
*/
    echo "<tr><td>$week</td>";
    $lastweek = $week;
    $weekly = 0;
   }

   if ($week != $lastweek) {
    echo "<tr><td>$week</td>";
    $weekly = 0;
    $prev = 0;
    if ($week < $lastweek) {
     $lastweeksyear = $currentyear;
    }
    $lastweek = $week;
   }

   echo "<!-- $wday, $prev -->\n";
   $diff = ($wday - ($prev + 1));
   if ($diff > 0) {
    echo "<td colspan=\"$diff\">&nbsp;</td>";
   }

   $weekly += $dur;
   $total += $dur;
   $yeartotal += $dur;
   $prev = $wday;

   echo "<td title=\"$date\">$fmtdur</td>";
   if ($wday == 7) {
    $weeknr++;
   }
 }
 if ($wday !== 7) {
   echo "<td colspan=\"".(7-$wday)."\">";
 }
 echo '<td class="total">'.
      str_replace('.', ',', sprintf('%.1f', $weekly))."</td></tr>\n";
 $weekly = 0;
 $weeknr = $week;
 echo "<tr class=\"yeartotal\"><td colspan=\"8\">$year yhteens&auml;</td><td>".str_replace('.', ',', sprintf('%.01f', $yeartotal))."</td></tr>\n";
?>
</tbody>
</table>
<?php
  echo '<p class="total">Yhteensä <strong>'.
       str_replace('.', ',', sprintf('%.1f', $total)).
       "</strong> tuntia (<strong>".
       str_replace('.', ',', sprintf('%.1f', $total/7.5)).
       "</strong> ty&ouml;p&auml;iv&auml;&auml;).</p>\n";
 }
?>
