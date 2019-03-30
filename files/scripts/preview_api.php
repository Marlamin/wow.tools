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
}

die("Can't preview this type of file yet.");
?>