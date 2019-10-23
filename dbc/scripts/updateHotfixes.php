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
	$messages = [];
	foreach($json['entries'] as $entry){
		$insertQ->execute([$entry['pushID'], $entry['recordID'], $entry['tableName'], $entry['isValid'], $json['build']]);
		if($insertQ->rowCount() == 1){
			echo "Inserted new hotfix: Push ID " . $entry['pushID'] .", Table " . $entry['tableName'] . " ID " .$entry['recordID']." from build " . $json['build']."\n";

			if(!array_key_exists($entry['pushID'], $messages)){
				$messages[$entry['pushID']] = "Push ID " . $entry['pushID'] . " for build " . $json['build']."\n";
			}

			$messages[$entry['pushID']] .= $entry['tableName'] . " ID " .$entry['recordID']."\n";
		}
	}

	foreach($messages as $message){
		telegramSendMessage($message);
	}

	$processedMD5s[] = $md5;
}
?>