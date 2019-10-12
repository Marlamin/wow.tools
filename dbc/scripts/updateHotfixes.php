<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");
require_once("../../inc/config.php");

$processedMD5s = [];
$files = glob('/home/wow/dbcdumphost/caches/*.bin');
foreach($files as $file) {
	$md5 = md5_file($file);
	if(in_array($md5, $processedMD5s))
		continue;

	echo $file."\n";
	$output = shell_exec("cd /home/wow/hotfixdumper; dotnet WoWTools.HotfixDumper.dll " . escapeshellarg($file) . " " . escapeshellarg("/home/wow/dbd/WoWDBDefs/definitions"));
	$json = json_decode($output, true);

	$insertQ = $pdo->prepare("INSERT IGNORE INTO wow_hotfixes (pushID, recordID, tableName, isValid, build) VALUES (?, ?, ?, ?, ?)");
	foreach($json['entries'] as $entry){
		$insertQ->execute([$entry['pushID'], $entry['recordID'], $entry['tableName'], $entry['isValid'], $json['build']]);
	}

	$processedMD5s[] = $md5;
}
?>