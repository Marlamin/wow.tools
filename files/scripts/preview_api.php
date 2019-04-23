<?php
require_once("/var/www/wow.tools/inc/config.php");

if(empty($_GET['contenthash']) || empty($_GET['buildconfig']) || empty($_GET['filedataid'])){
	die("Not enough information!");
}

$build = getVersionByBuildConfigHash($_GET['buildconfig'], "wow");

if(empty($build)) die("Invalid build!");

if(strlen($_GET['contenthash']) != 32 || !ctype_xdigit($_GET['contenthash'])) die("Invalid contenthash!");

$_GET['filedataid'] = (int)$_GET['filedataid'];

$q2 = $pdo->prepare("SELECT id, filename, type FROM wow_rootfiles WHERE id = :id");
$q2->bindParam(":id", $_GET['filedataid'], PDO::PARAM_INT);
$q2->execute();
$row2 = $q2->fetch();
if(empty($row2)) {
	die("File not found in database!");
}else{
	$type = $row2['type'];
	$dbid = $row2['id'];
}

if(empty($row2['filename'])){
	$row2['filename'] = $row2['id'].".".$type;
}

function downloadFile($params, $outfile){
	$fp = fopen($outfile, 'w+');
	$url = 'http://localhost:5005/casc/file/chash' . $params;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$exec = curl_exec($ch);
	curl_close($ch);
	fclose($fp);

	if($exec){
		return true;
	}else{
		return false;
	}
}


$cascparams = "?buildconfig=".$build['buildconfig']['hash']."&cdnconfig=".$build['cdnconfig']['hash']."&contenthash=".$_GET['contenthash']."&filename=".urlencode($row2['filename']);
if($type == "ogg"){
	echo "<audio autoplay controls><source src='//wow.tools/casc/preview".$cascparams."' type='audio/ogg'></audio>";
	die();
}else if($type == "mp3"){
	echo "<audio autoplay controls><source src='//wow.tools/casc/preview".$cascparams."' type='audio/mpeg'></audio>";
	die();
}else if($type == "blp"){
	echo "<body style='margin: 0px; padding:0px;'><img style='max-width: 100%;' src='//wow.tools/casc/preview".$cascparams."'></body>";
	die();
}else{
	// Dump to file
	$tempfile = tempnam('/tmp/', 'PREVIEW');
	downloadFile($cascparams, $tempfile);
	if($type == "m2" || $type == "wmo"){
		// dump json
		$output = shell_exec("cd /home/wow/jsondump; /usr/bin/dotnet WoWJsonDumper.dll ".$type." ".escapeshellarg($tempfile)." 2>&1");
		echo "<pre style='max-height: 500px; color: var(--text-color)'><code>".htmlentities($output)."</pre></code>";
	}else{
		// dump via hd
		$output = shell_exec("/usr/bin/hd ".escapeshellarg($tempfile)." 2>&1");
		echo "<pre style='max-height: 500px; color: var(--text-color)'><code>".htmlentities($output)."</pre></code>";
	}
	unlink($tempfile);
}
?>