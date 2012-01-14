<?php
error_reporting(0);
$order = array('Title','LastPlayed','EarnedGamerscore','AvailableGamerscore','EarnedAchievements','AvailableAchievements','PercentageComplete');
function objectToArray($d) {
    if (is_object($d)) {
        // Gets the properties of the given object
        // with get_object_vars function
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        /*
        * Return array converted to object
        * Using __FUNCTION__ (Magic constant)
        * for recursive call
        */
        return array_map(__FUNCTION__, $d);
    }
    else {
        // Return array
        return $d;
    }
}
if (isset($_GET['gamertag']))  {
$_GET['gamertag'] = stripslashes(strip_tags($_GET['gamertag']));
$gt = objectToArray(simplexml_load_file('http://gamercard.xbox.com/en-US/' . $_GET['gamertag'] . '.card'));
$gt = $gt['body']['div'];
$meta = explode(' ',$gt['@attributes']['class']);
if ($gt['div'][1]['div']=="--" || $gt['div'][1]['div']==NULL) {
$ar['GamertagExists'] = FALSE;
} else {
$ar['GamertagExists'] = TRUE;
}
$ar['Gamertag'] = $gt['a'][0];
$ar['Subscription'] = $meta[1];
$ar['Gender'] = $meta['2'];
$ar['Gamerscore'] = $gt['div'][1]['div'];
if ($gt['a'][1]['img']['@attributes']['src']=='http://image.xboxlive.com//global/t.FFFE07D1/tile/0/20000') {
$ar['Pictures'] = array();
} else {
$ar['Pictures']['Tile64px'] = 'http://avatar.xboxlive.com/avatar/' . urldecode($ar['Gamertag']) . '/avatarpic-l.png';
$ar['Pictures']['Tile32px'] = 'http://avatar.xboxlive.com/avatar/' . urldecode($ar['Gamertag']) . '/avatarpic-s.png';
$ar['Pictures']['FullBody'] = 'http://avatar.xboxlive.com/avatar/' . urldecode($ar['Gamertag']) . '/avatar-body.png';
}
$ar['Reputation'] = 0;
foreach($gt['div'][0]['div'] as $star) {
if ($star['@attributes']['class'] === 'Star Full') { $ar['Reputation'] = $ar['Reputation']+1; }
elseif ($star['@attributes']['class'] == 'Star ThreeQuarter') { $ar['Reputation'] = $ar['Reputation']+.75; }
elseif ($star['@attributes']['class'] == 'Star Quarter') { $ar['Reputation'] = $ar['Reputation']+.25; }
elseif ($star['@attributes']['class'] == 'Star Half') { $ar['Reputation'] = $ar['Reputation']+.50; }
}
$ar['LastPlayed'] = array();
$i = 0;
foreach($gt['ol']['li'] as $pg) {
if (1) {
foreach($pg['a']['span'] as $key => $meta) {
if ($order[$key]=='LastPlayed') {
$ar['LastPlayed'][$i][$order[$key]] = @strtotime($meta);
} elseif ($order[$key]=='Title') {
$ar['LastPlayed'][$i][$order[$key]] = trim(str_replace("\n",'',preg_replace('/[^a-zA-Z0-9\s]/', ' ', $meta)));
} elseif ($order[$key]=='EarnedAchievements'||$order[$key]=='AvailableAchievements'||$order[$key]=='EarnedGamerscore'||$order[$key]=='AvailableGamerscore') {
$ar['LastPlayed'][$i][$order[$key]] = (int)$meta;
} else {
$ar['LastPlayed'][$i][$order[$key]] = str_replace('\n',' ',$meta);
}
}
$ar['LastPlayed'][$i]['Pictures']['Tile32px'] = $pg['a']['img']['@attributes']['src'];
}
$i++;
}
if (isset($_GET['callback'])) {
echo strip_tags($_GET['callback']) . '(';
}
echo stripslashes(trim(json_encode($ar)));
if (isset($_GET['callback'])) {
echo ');';
}
} else {
if ($_GET['p']=='examples') {
$title = "Gamertag API Examples";
} else {
$title = "Xbox 360 Gamertag API";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title><?php echo $title; ?></title>
		<link rel="stylesheet" href="/style.css"/>
	</head>
	<body>
		<div id="container">
			<div id="top">

				<h1><a href="http://360api.chary.us/">Xbox 360 Gamertag API</a></h1>
				<div id="nav">Pages:
<ul>
<li class="page_item"><a href="/" title="Home">Home</a></li>
<li class="page_item"><a href="http://za.chary.us/content/x360-api-structure.html" title="API Info">API Info</a></li>
<li class="page_item"><a href="http://za.chary.us/content/x360-api.html" title="About">About</a></li>
<li class="page_item"><a href="/?p=examples" title="Examples">Examples</a></li>
<li class="page_item"><a href="http://za.chary.us/" title="Za.chary.us">Za.chary.us</a></li>
</ul>

				</div>
			</div>			<div id="main">
	
				<h2><a href="<?php echo $_SERVER['REQUEST_URI']; ?>" rel="bookmark" title="Permanent Link to <?php echo $title; ?>"><?php echo $title; ?></a></h2>
				<p>
				<?php
if ($_GET['p']=='examples') {
?>
If you would like to be included on this list, please contact me at <a href="mailto:zbloomq@live.com">zbloomq@live.com</a>.
<ul>
<li><a href="http://experiments.chary.us/gamercard/">Xbox 360 Gamercard Picture Generator</a></li>
</ul>
<?php
} else {
				echo '<a href="http://za.chary.us/content/x360-api-structure.html">Read about the API\'s return format</a><br/>
<a href="http://za.chary.us/content/x360-api.html">Read about the API</a><br/>
<a href="http://experiments.chary.us/gamercard/">Try out a fancy example</a><br/>
<a href="https://github.com/flotwig/Xbox-360-Gamertag-API">Get the source code!</a><br/>';
} ?>
		  </div>
	</div>
		<div id="footer">
			Code &copy;2010-<?php echo date('Y'); ?> <a href="http://za.chary.us/">me</a>. Results &copy;<?php echo date('Y'); ?> Microsoft Corporation.
        </div>
</body>
</html>
<?php
}
die();
?>
