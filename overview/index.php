<?php
 $lang = 'en';
 if (isset($_REQUEST['lang']) && $_REQUEST['lang'] == 'fi') {
  $lang = $_REQUEST['lang'];
 }
 $view = (isset($_REQUEST['view']) && $_REQUEST['view'] === 'weekly') ? 'weekly' : 'monthly';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="HandheldFriendly" content="True">
<meta name="MobileOptimized" content="width">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="white">
<meta name="apple-mobile-web-app-title" content="TrackTime">
<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700&amp;display=swap" rel="stylesheet">
<meta name="theme-color" content="#FFF" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#333" media="(prefers-color-scheme: dark)">
<link rel="icon" href="../favicon.ico">
<link rel="manifest" href="../public/manifest.json">
<title lang="en">Time analysis</title>
<style>
 *[lang]:not([lang="<?php echo $lang; ?>"]) {
  display: none !important;
 }
 body {
  background-color: #EEE;
  color: #000;
  font-family: 'Open Sans','Unicode Sans','Lucida Sans Unicode',Helvetica,Arial,Verdana,sans-serif;
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
  z-index: 3;
 }
 h1 {
  float: left;
  width: 13em;
 }
 #download {
  margin-right: 7em;
 }
 #viewswitch {
  float: right;
  margin: 1.75em 1em 0.5em 0;
 }
 #viewswitch a {
  background-color: #FFF;
  border-radius: 0.25em;
  color: rgb(37, 55, 100);
  display: block;
  float: right;
  margin: 0 0 0 0.5em;
  padding: 0.5em;
 }
 #viewswitch a.selected {
  background-color: rgb(37, 55, 100);
  color: #FFF;
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
 header a {
  background-color: #FFF;
  border-radius: 0.25em;
  color: rgb(37, 55, 100);
  display: block;
  float: right;
  margin: 1.75em 1em 0.5em 1em;
  padding: 0.5em;
 }
 form {
  display: flex;
  flex-wrap: wrap;
  margin: 2em 1em 1em 1em;
  width: 100em;
 }
 input[type="submit"] {
  border-width: 2px;
  border-radius: 0.5em;
  min-width: 6em;
  height: 2em;
  margin: 1.5em 0 0 1em;
 }
 table {
  border-collapse: collapse;
/*
  float: left;
*/
  margin: 1em;
 }
 thead {
  position: sticky;
  top: 6.4em;
  z-index: 2;
 }
 thead th {
  background-color: #EEE;
  color: #000;
  padding-top: 0em;
 }
/*
 table.total {
  position: sticky;
  top: 2em;
 }
*/
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
 #language {
  background-color: #CCC;
  border: 1px outset #CCC;
  border-radius: 2em;
  color: #333;
  display: flex;
  padding: 0.25em 0 0.25em 0.5em;
  position: fixed;
  right: 2em;
  top: 2em;
 }
 #language ul {
  display: flex;
  margin: 0;
  padding: 0;
 }
 .language-switcher li {
  background-color: #666;
  border: 1px outset #CCC;
  border-color: #CCC;
  border-width: 1px;
  border-style: outset;
  border-radius: 2em;
  box-sizing: border-box;
  color: #FFF;
  list-style-type: none;
  margin: 0em 0.5em 0 0;
  min-height: 2em;
  min-width: 2em;
  padding: 0.25em 0 0 0;
  text-align: center;
  width: 2em;
 }
 .language-switcher li.selected {
  background-color: #FFF;
  border-color: #CCC;
  border-style: inset;
  color: #0d0342;
 }
</style>
<header>
 <div id="language"></div>
 <h1 lang="en">Time analysis</h1>
 <h1 lang="fi">Aika-analyysi</h1>
 <div id="download">
  <a href="./export.php?<?php echo http_build_query($_REQUEST, '&amp;')?>&format=csv">Export CSV</a>
  <a href="./export.php?<?php echo http_build_query($_REQUEST, '&amp;')?>&format=excel">Export to Excel</a>
  <a href="./export.php?<?php echo http_build_query($_REQUEST, '&amp;')?>&format=json">Export JSON</a>
 </div>
 <?php
  $viewParams = $_REQUEST;
  $viewParams['view'] = 'monthly';
  $monthlyHref = './?'.http_build_query($viewParams, '&amp;');
  $viewParams['view'] = 'weekly';
  $weeklyHref = './?'.http_build_query($viewParams, '&amp;');
 ?>
 <div id="viewswitch">
  <a href="<?php echo $weeklyHref; ?>" class="<?php echo $view == 'weekly' ? 'selected' : ''; ?>"><span lang="en">Weekly</span><span lang="fi">Viikoittain</span></a>
  <a href="<?php echo $monthlyHref; ?>" class="<?php echo $view == 'monthly' ? 'selected' : ''; ?>"><span lang="en">Monthly</span><span lang="fi">Kuukausittain</span></a>
 </div>
</header>
<?php
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

 $oper = ' AND ';
 if (isset($_REQUEST['oper']) && $_REQUEST['oper'] == 'OR') {
  $oper = ' OR ';
 }
 $query = array();
 $query['subject'] = (isset($_REQUEST['subject'])) ? $_REQUEST['subject'] : NULL;
 $query['starttime'] = (isset($_REQUEST['starttime'])) ? $_REQUEST['starttime'] : NULL;
 $query['endtime'] = (isset($_REQUEST['endtime'])) ? $_REQUEST['endtime'] : NULL;
 $query['action'] = (isset($_REQUEST['action'])) ?  $_REQUEST['action'] : NULL;
 $query['mainaction'] = (isset($_REQUEST['mainaction'])) ? $_REQUEST['mainaction'] : NULL;
 $query['sideaction'] = (isset($_REQUEST['sideaction'])) ? $_REQUEST['sideaction'] : NULL;
 $query['location'] = (isset($_REQUEST['location'])) ? (int) $_REQUEST['location'] : NULL;
 $query['withOnly'] = (isset($_REQUEST['withOnly'])) ? $_REQUEST['withOnly'] : NULL;
 $query['with'] = NULL;
 if (isset($_REQUEST['with'])) {
  $with = 0;
  if (is_array($_REQUEST['with'])) {
   for ($i=0; $i<count($_REQUEST['with']); $i++) {
    $with += 2 ** (intval($_REQUEST['with'][$i])-1);
   }
  }
  elseif (is_numeric($_REQUEST['with'])) {
   $with = intval($_REQUEST['with']);
  }
  $query['with'] = $with;
 }
 $query['usecomputer'] = isset($_REQUEST['usecomputer']) ? ($_REQUEST['usecomputer'] ? 1 : 0) : NULL;
 $query['rating'] = (isset($_REQUEST['rating'])) ? $_REQUEST['rating'] : NULL;
 $query['desc'] = (isset($_REQUEST['desc'])) ? $_REQUEST['desc'] : NULL;
 $query['not'] = (isset($_REQUEST['not'])) ? $_REQUEST['not'] : NULL;
?>
<form method="get" action="./" id="query">
 <input type="hidden" name="lang">
 <input type="hidden" name="view" value="<?php echo htmlentities($view); ?>">
 <fieldset>
  <legend>
   <label for="subject">Subject</label>
  </legend>
  <input id="subject" type="text" name="subject" value="<?php echo htmlentities($query['subject']); ?>">
 </fieldset>
 <fieldset>
  <legend>
   <label for="mainaction"><span lang="fi">Päätoiminto</span><span lang="en">Main action</span></label>
  </legend>
  <select id="mainaction" name="mainaction">
   <option label=" "></option>
   <option label="-" value="0"></option>
  </select>
 </fieldset>
 <fieldset>
  <legend>
  <label for="sideaction"><span lang="fi">Sivutoiminto</span><span lang="en">Parallel action</span></label>
  </legend>
  <select id="sideaction" name="sideaction">
   <option label=" "></option>
   <option label="-" value="0"></option>
  </select>
 </fieldset>
 <fieldset>
  <legend>
   <label for="location">Location</label>
  </legend>
  <select id="location" name="location">
   <option label=" " value=""></option>
  </select>
 </fieldset>
 <fieldset class="starttime">
  <legend>
   <label for="starttime"><span lang="fi">Alkaen</span><span lang="en">From</span></label>
  </legend>
  <input id="starttime" name="starttime" type="date" size="10" value="<?php echo htmlentities($query['starttime']); ?>">
 </fieldset>
 <fieldset class="endtime">
  <legend>
   <label for="endtime"><span lang="fi">Päättyen</span><span lang="en">To</span></label>
  </legend>
  <input id="endtime" name="endtime" type="date" size="10" value="<?php echo htmlentities($query['endtime']); ?>">
 </fieldset>
 <fieldset class="with">
  <legend>
   <label for="withOnly"><span lang="fi">Kanssa (vain)</span><span lang="en">With only</span></label>
  </legend>
  <select name="withOnly" id="withOnly">
   <option label=" " value=""></option>
   <option value="1" lang="fi"<?php echo $query['withOnly'] == 1 ? ' selected' : ''; ?>>yksin</option><option value="1" lang="en"<?php echo $query['withOnly'] == 1 ? ' selected' : ''; ?>>alone</option>
   <option value="2" lang="fi"<?php echo $query['withOnly'] == 2 ? ' selected' : ''; ?>>kumppanin</option><option value="2" lang="en"<?php echo $query['withOnly'] == 2 ? ' selected' : ''; ?>>partner</option>
   <option value="3" lang="fi"<?php echo $query['withOnly'] == 3 ? ' selected' : ''; ?>>vanhemman</option><option value="3" lang="en"<?php echo $query['withOnly'] == 3 ? ' selected' : ''; ?>>parent</option>
   <option value="4" lang="fi"<?php echo $query['withOnly'] == 4 ? ' selected' : ''; ?>>lasten</option><option value="4" lang="en"<?php echo $query['withOnly'] == 4 ? ' selected' : ''; ?>>kids</option>
   <option value="5" lang="fi"<?php echo $query['withOnly'] == 5 ? ' selected' : ''; ?>>perheen</option><option value="5" lang="en"<?php echo $query['withOnly'] == 5 ? ' selected' : ''; ?>>family</option>
   <option value="6" lang="fi"<?php echo $query['withOnly'] == 6 ? ' selected' : ''; ?>>muiden</option><option value="6" lang="en"<?php echo $query['withOnly'] == 6 ? ' selected' : ''; ?>>others</option>
  </select>
 </fieldset>
 <fieldset class="with">
  <legend>
   <span lang="fi">Kanssa (ainakin)</span><span lang="en">With at least</span>
  </legend>
  <input id="withpartner" name="with[]" value="2" type="checkbox"<?php echo (2 & $query['with']) ? ' checked' : ''; ?>>
  <label for="withpartner"><span lang="fi">kumppanin</span><span lang="en">partner</span></label>
  <input id="withparent" name="with[]" value="3" type="checkbox"<?php echo (4 & $query['with']) ? ' checked' : ''; ?>>
  <label for="withparent"><span lang="fi">vanhemman</span><span lang="en">parent</span></label>
  <input id="withkids" name="with[]" value="4" type="checkbox"<?php echo (8 & $query['with']) ? ' checked' : ''; ?>>
  <label for="withkids"><span lang="fi">lasten</span><span lang="en">kids</span></label>
  <input id="withfamily" name="with[]" value="5" type="checkbox"<?php echo (16 & $query['with']) ? ' checked' : ''; ?>>
  <label for="withfamily"><span lang="fi">perheen</span><span lang="en">family</span></label>
  <input id="withothers" name="with[]" value="6" type="checkbox"<?php echo (32 & $query['with']) ? ' checked' : ''; ?>>
  <label for="withothers"><span lang="fi">muiden</span><span lang="en">others</span></label>
 </fieldset>
 <fieldset class="check computer">
  <legend>
   <label for="usingcomputer"><span lang="fi">Tietokoneella</span><span lang="en">Using computer</span></label>
  </legend>
  <input id="usingcomputer" name="usecomputer" value="1" type="checkbox"<?php echo ($query['usecomputer']) ? ' checked' : ''; ?>>
 </fieldset>
 <fieldset class="extra description">
  <legend>
   <label for="desc"><span lang="fi">Kuvaus sisältää</span><span lang="en">Description includes</span></label>
  </legend>
  <input id="desc" name="desc" type="text" size="30" maxlength="255" value="<?php echo htmlentities($query['desc']); ?>">
 </fieldset>
 <fieldset class="extra description">
  <legend>
   <label for="not"><span lang="fi">Kuvaus ei sisällä</span><span lang="en">Description does not include</span></label>
  </legend>
  <input id="not" name="not" type="text" size="30" maxlength="255" value="<?php echo htmlentities($query['not']); ?>">
 </fieldset>
 <fieldset class="rating">
  <legend>
   <label for="rating"><span lang="fi">Arvio</span><span lang="en">Rating</span></label>
  </legend>
  <select name="rating" id="rating">
   <option label=" " value=""></option>
   <option value="1"<?php echo $query['rating'] == 1 ? ' selected' : ''; ?>>1</option>
   <option value="lte2" lang="fi"<?php echo $query['rating'] == "lte2" ? ' selected' : ''; ?>>2 tai pienempi</option><option value="lte2" lang="en"<?php echo $query['rating'] == "lte2" ? ' selected' : ''; ?>>2 or less</option>
   <option value="2"<?php echo $query['rating'] == 2 ? ' selected' : ''; ?>>2</option>
   <option value="gte2" lang="fi"<?php echo $query['rating'] == "gte2" ? ' selected' : ''; ?>>2 tai suurempi</option><option value="gte2" lang="en"<?php echo $query['rating'] == "gte2" ? ' selected' : ''; ?>>2 or more</option>
   <option value="lte3" lang="fi"<?php echo $query['rating'] == "lte3" ? ' selected' : ''; ?>>3 tai pienempi</option><option value="lte3" lang="en"<?php echo $query['rating'] == "lte3" ? ' selected' : ''; ?>>3 or less</option>
   <option value="3"<?php echo $query['rating'] == 3 ? ' selected' : ''; ?>>3</option>
   <option value="gte3" lang="fi"<?php echo $query['rating'] == "gte3" ? ' selected' : ''; ?>>3 tai suurempi</option><option value="gte3" lang="en"<?php echo $query['rating'] == "gte3" ? ' selected' : ''; ?>>3 or more</option>
   <option value="lte4" lang="fi"<?php echo $query['rating'] == "lte4" ? ' selected' : ''; ?>>4 tai pienempi</option><option value="lte4" lang="en"<?php echo $query['rating'] == "lte4" ? ' selected' : ''; ?>>4 or less</option>
   <option value="4"<?php echo $query['rating'] == 4 ? ' selected' : ''; ?>>4</option>
   <option value="gte4" lang="fi"<?php echo $query['rating'] == "gte4" ? ' selected' : ''; ?>>4 tai suurempi</option><option value="gte4" lang="en"<?php echo $query['rating'] == "gte4" ? ' selected' : ''; ?>>4 or more</option>
   <option value="lte5" lang="fi"<?php echo $query['rating'] == "lte5" ? ' selected' : ''; ?>>5 tai pienempi</option><option value="lte5" lang="en"<?php echo $query['rating'] == "lte5" ? ' selected' : ''; ?>>5 or less</option>
   <option value="5"<?php echo $query['rating'] == 5 ? ' selected' : ''; ?>>5</option>
  </select>
 </fieldset>
 <input lang="fi" type="submit" id="submitFi" value="Hae">
 <input lang="en" type="submit" id="submit" value="Get">
</form>
<script>
 const languages = ['en', 'fi']
 const tb = document.getElementById('results')
 const f = document.getElementById('query')
 const et = document.getElementById('endtime')
 const st = document.getElementById('starttime')
 const reset = document.getElementById('reset')
 const wo = document.getElementById('withOnly')
 wo.onchange = function() {
  const checkboxes = f.querySelectorAll('[name="with[]"]')
  checkboxes.forEach(input => {
    input.disabled = (this.value != '')
  })
 }

 const acts = {'null': '', 'undefined': ''}
 const locs = {}
 const selectedLoc = '<?php echo $query['location'] ?>'
 let activities = []
 let locations = []
 let guesses = {}
 let llabels = {}
 const filePromises = []

 try {
  filePromises.push((async () => {
   const file = '../activities.json'
   const resp = await fetch(file)
   activities = await resp.json()
   // console.log(activities)
   for (const cat of activities) {
    const grp = []
    if (cat.activities) {
     for (const lang of languages) {
      grp[lang] = document.createElement('optgroup')
      grp[lang].lang = lang
      grp[lang].label = cat[lang]
     }
     for (const act of cat.activities) {
      acts[act.id] = act
      for (const lang of languages) {
       const opt = document.createElement('option')
       opt.lang = lang
       opt.value = act.id
       opt.textContent = act[lang]
       grp[lang].appendChild(opt)
      }
     }
     for (const lang of languages) {
      f.mainaction.appendChild(grp[lang])
      f.sideaction.appendChild(grp[lang].cloneNode(true))
     }
    }
    else {
     acts[cat.id] = cat
     for (const lang of languages) {
      const opt = document.createElement('option')
      opt.lang = lang
      opt.value = cat.id
      opt.textContent = cat[lang]
      f.mainaction.appendChild(opt)
      f.sideaction.appendChild(opt.cloneNode(true))
     }
    }
   }
  })())
  filePromises.push((async () => {
   const file = '../locations.json'
   const resp = await fetch(file)
   locations = await resp.json()
   // console.log(locations)
   for (const cat of locations) {
    const grp = []
    if (cat.options) {
     for (const lang of languages) {
      grp[lang] = document.createElement('optgroup')
      grp[lang].lang = lang
      grp[lang].label = cat[lang]
     }
     for (const loc of cat.options) {
      locs[loc.id] = loc
      llabels[loc.id] = {en: loc.atEn, fi: loc.atFi}
      for (const lang of languages) {
       const opt = document.createElement('option')
       opt.lang = lang
       opt.value = loc.id
       if (loc.id === selectedLoc) {
         opt.setAttribute('selected', 'selected')
       }
       opt.textContent = loc[lang]
       grp[lang].appendChild(opt)
      }
     }
     for (const lang of languages) {
      f.location.appendChild(grp[lang])
     }
    }
    else {
     locs[cat.id] = cat
     for (const lang of languages) {
      const opt = document.createElement('option')
      opt.lang = lang
      opt.value = cat.id
      opt.textContent = cat[lang]
      f.location.appendChild(opt)
     }
    }
   }
  })())
 }
 catch(e) {
  console.error(e)
 }
 const params = new URLSearchParams(document.location.search)
 let currentLanguage = params.get('lang') || '<?php echo $lang; ?>'
 Promise.all(filePromises).then(results => {
  const maOpt = f.mainaction.querySelector(`option[lang="${currentLanguage}"][value="<?php echo mysqli_real_escape_string($conn, $query['mainaction']); ?>"]`)
  if (maOpt) maOpt.selected = "selected"
  const saOpt = f.sideaction.querySelector(`option[lang="${currentLanguage}"][value="<?php echo mysqli_real_escape_string($conn, $query['sideaction']); ?>"]`)
  if (saOpt) saOpt.selected = "selected"
 })
 const lcontainer = document.querySelector('#language')
 const translatedElementsSelector = '[lang]'
 const switcher = document.createElement('ul')
 const css = document.styleSheets[1]
 switcher.className = 'language-switcher'
 setLanguage(currentLanguage)
 languages.forEach((lang) => {
  const li = document.createElement('li')
  li.textContent = lang
  if (lang == currentLanguage) {
   li.className = 'selected'
  }
  li.onclick = (e) => {
   params.set('lang', lang)
   history.replaceState({lang}, '', './?' + params.toString())
   const prev = switcher.querySelector('li.selected')
   prev.classList.remove('selected')
   li.classList.add('selected')
   setLanguage(lang)
  }
  switcher.appendChild(li)
 })
 lcontainer.appendChild(switcher)
 window.addEventListener("popstate", (event) => {
  const lang = event.state?.lang || '<?php echo $lang; ?>'
  if (lang) {
    setLanguage(lang)
  }
 })

 function setLanguage(lang) {
  currentLanguage = lang
  f.querySelector('input[name="lang"]').value = currentLanguage
  const html = document.querySelector(':root')
   const oldLang = html.lang
   html.lang = lang
   for (let i = 0; i < css.cssRules.length; i++) {
    const rule = css.cssRules[i]
    if (rule.selectorText == `[lang]:not([lang="${oldLang}"])`) {
     css.deleteRule(i)
     css.insertRule(`[lang]:not([lang="${lang}"]) { display: none !important; }`, i)
    }
   }
   const opts = document.querySelectorAll('option[lang]:checked')
   for (opt of opts) {
    opt.selected = false
    const otherOpt = opt.parentNode.parentNode.querySelector(`option[lang="${lang}"][value="${opt.value}"]`)
    if (otherOpt) {
     otherOpt.selected = "selected"
    }
   }
 }

</script>

<?php

 function mkhref($sy, $sm, $sd, $ey, $em, $ed, $subject=NULL) {
/*
  $st = date('Y-m-d', mktime(0, 0, 0, $sm, $sd, $sy));
  $et = date('Y-m-d', mktime(0, 0, 0, $em, $ed, $ey));
  $params = $_REQUEST;
  $params['limit'] = 0;
  if ($subject) {
    $params['subject'] = $subject;
  }
  $params['starttime'] = $st;
  $params['endtime'] = $et;
  return "../?".http_build_query($params, '&amp;');
*/
  $st = date('Y-m-d\TH:i', mktime(0, 0, 0, $sm, $sd, $sy));
  $et = date('Y-m-d\TH:i', mktime(0, 0, 0, $em, $ed, $ey));
  return "../dashboard.html?subject=".htmlentities($subject)."#$st,$et";
 }

 # corrects a day's raw duration for entries that were split at midnight by
 # the previous row's leftover (see the "leftover" carry below); returns
 # [correctedStartTimestamp, correctedDurationSeconds]
 function corrected_day_duration(&$leftover, $row) {
  $subject = $row['subject'];
  $time = $row['Time'];
  $start = $row['tstamp'];
  if (!empty($leftover[$subject])) {
   $time += $leftover[$subject];
   $leftover[$subject] = 0;
   $start = mktime(0, 0, 0, $row['M'], $row['D'], $row['Y']);
  }
  $end = $start + $time;
  $midnight = mktime(0, 0, 0, $row['M'], $row['D']+1, $row['Y']);
  $time_to_midnight = $midnight - $end;
  if ($time_to_midnight < 0) {
   $leftover[$subject] = 0 - $time_to_midnight;
   $time -= $leftover[$subject];
  }
  return array($start, $time);
 }

 function close_week_row($prevDow, $weekly, $warnHours, $overloadHours) {
  if ($prevDow < 7) {
   echo "<td colspan=\"".(7-$prevDow)."\">";
  }
  $class = 'ok';
  if ($weekly > $overloadHours) {
   $class = 'overload';
  }
  elseif ($weekly > $warnHours) {
   $class = 'warning';
  }
  echo '<td class="total '.$class.'">'.
       str_replace('.', ',', sprintf('%.1f', $weekly)).
       "</td></tr>\n";
 }

 function open_week_row($isoWeek, $isoYear, $subject) {
  $d = new DateTime();
  $d->setISODate($isoYear, $isoWeek);
  $d->setTime(0, 0, 0);
  $sy = (int) $d->format('Y');
  $sm = (int) $d->format('n');
  $sd = (int) $d->format('j');
  $endD = clone $d;
  $endD->modify('+7 days');
  $ey = (int) $endD->format('Y');
  $em = (int) $endD->format('n');
  $ed = (int) $endD->format('j');
  $href = mkhref($sy, $sm, $sd, $ey, $em, $ed, $subject);
  $label = $isoYear.'-W'.sprintf('%02d', $isoWeek);
  echo "<tr><th><a href=\"$href\">$label</a></th>";
 }

 # renders one calendar table per subject, one row per month, one column per
 # day of month
 function render_monthly($rows, $hsl_base) {
  $month = 0;
  $monthnr = 0;
  $lastmonth = 0;
  $lastmonthnr = 0;
  $prev = 0;
  $prevsub = '';
  $leftover = array();
  $monthly = 0;
  $init = FALSE;
  $y = null;
  $m = null;
  $mday = null;
  foreach ($rows as $row) {
   $subject = $row['subject'];
   if ($subject != $prevsub) {
    if ($prevsub) {
     if ($mday < 31) {
      echo "<td class=\"that\" colspan=\"".(31-$mday)."\"></td>";
     }
     $bgcolor = "hsla(".($hsl_base-$monthly/1.35).", 75%, 75%, 100%)";
     $href = mkhref($y, $m, 1, $y, $m+1, 1, $prevsub);
     echo '<td class="total" style="background-color: '.$bgcolor.'">'.
          "<a href=\"$href\">".
          str_replace('.', ',', sprintf('%.1f', $monthly)).
          "</a>".
          "</td></tr></table>\n";
     $monthly = 0;
     $monthnr = $month;
    }
    echo "<table class=\"days\"><caption>".htmlentities($subject)."</caption>\n";
    echo "<thead><tr><th>Month</th>";
    for ($d=1; $d<=31; $d++) {
      echo "<th>$d</th>";
    }
    echo "<th>Total</th></tr></thead><tbody>\n";
    $init = FALSE;
    $prevsub = $subject;
    $prev = 0;
   }
   $date = $row['D'].".".$row['M'];
   $month = $row['M'];
   list($start, $time) = corrected_day_duration($leftover, $row);
   $monthnr = date('Ym', $start);
   $mday = $row['D'];
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
    $href = mkhref($y, $m, 1, $y, $m+1, 1, $subject);
    echo "<tr><th><a href=\"$href\">$m/$y</a></th>";
    $lastmonth = $month;
    $lastmonthnr = $monthnr;
   }
   elseif ($monthnr != $lastmonthnr) {
    if ($prev < 31) {
      echo "<td class=\"this\" colspan=\"".(31-$prev)."\">";
    }
    $bgcolor = "hsla(".($hsl_base-$monthly/1.35).", 75%, 75%, 100%)";
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
    $monthly = 0;
    $prev = 0;
    $lastmonth = $month;
    $lastmonthnr = $monthnr;
   }
   if ($diff = ($mday - ($prev + 1))) {
    echo "<td class=\"diff\" colspan=\"$diff\">&nbsp;</td>";
   }

   $monthly += $dur;
   $prev = $mday;

   $bgcolor = "hsla(".($hsl_base-$dur*15).", 75%, 75%, 100%)";
   $href = mkhref($y, $m, $mday, $y, $m, $mday+1, $subject);
   if ($dur == 23 || $dur == 24 || $dur == 25) {
     $bgcolor = "#CCC";
   }
   echo "<td title=\"$date: $fmtdur\" style=\"background-color: $bgcolor\">".
        "<a href=\"$href\">".
        "$hours:$mins</a></td>\n";
  }
  if (count($rows) > 0) {
   if ($mday < 31) {
    echo "<td class=\"that\" colspan=\"".(31-$mday)."\"></td>";
   }
   $bgcolor = "hsla(".($hsl_base-$monthly/1.35).", 75%, 75%, 100%)";
   $href = mkhref($y, $m, 1, $y, $m+1, 1, $subject);
   echo '<td class="total" style="background-color: '.$bgcolor.'">'.
        "<a href=\"$href\">".
        str_replace('.', ',', sprintf('%.1f', $monthly)).
        "</a>".
        "</td></tr></tbody></table>\n";
  }
 }

 # renders one calendar table per subject, one row per ISO-8601 week, one
 # column per weekday; the total column is color-coded against $warnHours /
 # $overloadHours so a specific filtered activity's weekly load stands out
 function render_weekly($rows, $warnHours=7, $overloadHours=20) {
  $prevsub = null;
  $weekKey = null;
  $weekly = 0;
  $prevDow = 0;
  $leftover = array();
  foreach ($rows as $row) {
   $subject = $row['subject'];
   list($start, $time) = corrected_day_duration($leftover, $row);
   $dur = $time / 3600;
   $dow = (int) date('N', $start); // 1 = Monday .. 7 = Sunday
   $isoYear = (int) date('o', $start);
   $isoWeek = (int) date('W', $start);
   $curWeekKey = sprintf('%04d-W%02d', $isoYear, $isoWeek);

   if ($subject !== $prevsub) {
    if ($prevsub !== null) {
     close_week_row($prevDow, $weekly, $warnHours, $overloadHours);
     echo "</tbody></table>\n";
    }
    echo "<table class=\"weeks\"><caption>".htmlentities($subject)."</caption>\n";
    echo "<thead><tr><th>Week</th><th>Mon</th><th>Tue</th><th>Wed</th>".
         "<th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th><th>Total</th></tr></thead><tbody>\n";
    $prevsub = $subject;
    $weekKey = null;
    $weekly = 0;
    $prevDow = 0;
   }

   if ($weekKey === null) {
    open_week_row($isoWeek, $isoYear, $subject);
   }
   elseif ($curWeekKey !== $weekKey) {
    close_week_row($prevDow, $weekly, $warnHours, $overloadHours);
    open_week_row($isoWeek, $isoYear, $subject);
    $weekly = 0;
    $prevDow = 0;
   }
   $weekKey = $curWeekKey;

   if ($dow - ($prevDow + 1) > 0) {
    echo "<td colspan=\"".($dow-($prevDow+1))."\">&nbsp;</td>";
   }
   $href = mkhref($row['Y'], $row['M'], $row['D'], $row['Y'], $row['M'], $row['D']+1, $subject);
   $hours = floor($time / 3600);
   $mins = ($time % 3600) / 60;
   if ($mins < 10) {
    $mins = '0'.$mins;
   }
   $fmtdur = str_replace('.', ',', sprintf('%.1f', $dur));
   $date = $row['D'].".".$row['M'].".".$row['Y'];
   echo "<td title=\"$date: $fmtdur\"><a href=\"$href\">$hours:$mins</a></td>\n";

   $prevDow = $dow;
   $weekly += $dur;
  }
  if ($prevsub !== null) {
   close_week_row($prevDow, $weekly, $warnHours, $overloadHours);
   echo "</tbody></table>\n";
  }
 }

 $select = "SELECT subject, UNIX_TIMESTAMP(starttime) AS `tstamp`".
   ", SUM(UNIX_TIMESTAMP(endtime) - UNIX_TIMESTAMP(starttime)) AS `Time`".
   ", YEAR(starttime) AS `Y`, MONTH(starttime) AS `M`, DAY(starttime) AS `D`".
   " FROM `".DB_TABLE."` t";
 $params = [];

 if ($query['subject']) {
  $params[] = "subject = '".mysqli_real_escape_string($conn, $query['subject'])."'";
 }
 if ($query['starttime']) {
  $params[] = "starttime >= '".date('Y-m-d', strtotime($query['starttime']))."'";
 }
 if ($query['endtime']) {
  $params[] = "endtime <= '".date('Y-m-d', strtotime($query['endtime']))."'";
 }
 if ($query['action']) {
  $params[] = "(`mainaction` = ".intval($query['action'])." OR `sideaction` = ".intval($query['action']).")";
 }
 if ($query['mainaction']) {
  $params[] = "(`mainaction` = ".sprintf("%'02.2s", $query['mainaction']).")";
 }
 if ($query['sideaction'] == '0') {
  $params[] = "(`sideaction` IS NULL)";
 }
 elseif ($query['sideaction']){
  $params[] = "(`sideaction` = ".sprintf("%'02.2s", $query['sideaction']).")";
 }
 if ($query['location']) {
  $params[] = "(`location` = $query[location])";
 }
 if ($query['with']) {
  $params[] = "(`with` & $query[with] != 0)";
 }
 if ($query['withOnly']) {
  $params[] = "(`with` = ".(2**(intval($query['withOnly']) - 1)).")";
 }
 if ($query['usecomputer']) {
  $params[] = "(`usecomputer` = $query[usecomputer])";
 }
 if ($query['rating']) {
  if (preg_match('/lte(\d)/', $query['rating'], $match)) {
   $params[] = "(`rating` <= $match[1])";
  }
  elseif (preg_match('/gte(\d)/', $query['rating'], $match)) {
   $params[] = "(`rating` >= $match[1])";
  }
  elseif (preg_match('/(\d)/', $query['rating'], $match)) {
   $params[] = "(`rating` = $match[1])";
  }
 }
 if ($query['desc']) {
   $params[] = "`description` LIKE '%".mysqli_real_escape_string($conn, $query['desc'])."%'";
 }
 if ($query['not']) {
   $params[] = "(`description` IS NULL OR `description` NOT LIKE '%".mysqli_real_escape_string($conn, $query['not'])."%')";
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
  $rows = array();
  while ($row = mysqli_fetch_assoc($stmt)) {
   $rows[] = $row;
  }

  # aggregate stats shared by both views, computed once regardless of which
  # calendar granularity is displayed
  $total = 0;
  $firstts = null;
  $lastts = null;
  $totalMonthSet = array();
  $aggLeftover = array();
  foreach ($rows as $row) {
   list($start, $time) = corrected_day_duration($aggLeftover, $row);
   $total += $time / 3600;
   $totalMonthSet[$row['subject'].'|'.$row['Y'].'-'.$row['M']] = TRUE;
   if ($firstts === null || $start < $firstts) {
    $firstts = $start;
   }
   if ($lastts === null || $start > $lastts) {
    $lastts = $start;
   }
  }
  $totalmonths = count($totalMonthSet);

  if ($view === 'weekly') {
   render_weekly($rows);
  }
  else {
   render_monthly($rows, $hsl_base);
  }
 }
 if ($days) {
  $totaltimespan = $lastts - $firstts;
  $totaldays = ceil($totaltimespan/60/60/24);
  $dayaverage = $total/$totaldays;
  $adayaverage = $total/$days;
  $totalweeks = ceil($totaldays/7);
  $weekaverage = $total/$totalweeks;
  $monthaverage = $total/$totalmonths;

  if (isset($query['starttime'])) {
   $firstts = strtotime($query['starttime']);
  }
  if (isset($query['endtime'])) {
   $lastts = strtotime($query['endtime']);
  }
  $from = date('Y-m-d\TH:i:s', $firstts);
  $to = date('Y-m-d\TH:i:s', $lastts);

  echo '<table class="total">'."\n";
  echo '<tr class="hour"><th>Hours</th><td colspan="2" class="total">'.
       "<a href=\"../dashboard.html#$from,$to\">".
       number_format($total, 1, ',', '&nbsp;').
       "</a>".
       "</td><td class=\"unit\">h</td></tr>\n";
  echo '<tr class="hour"><th>Man days</th><td colspan="2" class="total">'.
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
  echo '</table>'."\n";
 }

?>
