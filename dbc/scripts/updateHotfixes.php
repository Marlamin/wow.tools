<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");
require_once(__DIR__ . "/../../inc/config.php");

$knownPushIDs = $pdo->query("SELECT DISTINCT pushID FROM wow_hotfixes")->fetchAll(PDO::FETCH_COLUMN);
$knownKeys = $pdo->query("SELECT keyname FROM wow_tactkey")->fetchAll(PDO::FETCH_COLUMN);

$processedMD5s = [];
$files = glob('/home/wow/dbcdumphost/caches/*.bin');
foreach($files as $file) {
	// Only process hotfixes newer than 1 day ago
	if(filemtime($file) < strtotime("-6 hours"))
		continue;

	$md5 = md5_file($file);
	if(in_array($md5, $processedMD5s))
		continue;

	echo "[Hotfix updater] Reading " . $file . "\n";
	$output = shell_exec("cd /home/wow/hotfixdumper; dotnet WoWTools.HotfixDumper.dll " . escapeshellarg($file) . " " . escapeshellarg("/home/wow/dbd/WoWDBDefs/definitions"));
	$json = json_decode($output, true);

	if($json['build'] < 32593)
		continue;

	$insertQ = $pdo->prepare("INSERT IGNORE INTO wow_hotfixes (pushID, recordID, tableName, isValid, build) VALUES (?, ?, ?, ?, ?)");
	$insertCachedEntryQ = $pdo->prepare("INSERT IGNORE INTO wow_cachedentries (recordID, tableName, md5, build) VALUES (?, ?, ?, ?)");
	$messages = [];
	foreach($json['entries'] as $entry){
		if($entry['pushID'] > 999999){
			$messages[] = "Got hotfix with a very high push ID: " . $entry['pushID'] .", Table " . $entry['tableName'] . " ID " .$entry['recordID']." from build " . $json['build'].", ignoring!!!\n";
			continue;
		}

		if($entry['pushID'] != "-1" && in_array($entry['pushID'], $knownPushIDs))
			continue;

		if($entry['pushID'] != "-1"){
			// With Push ID
			$insertQ->execute([$entry['pushID'], $entry['recordID'], $entry['tableName'], $entry['isValid'], $json['build']]);
			if($insertQ->rowCount() == 1){
				echo "[Hotfix updater] Inserted new hotfix: Push ID " . $entry['pushID'] .", Table " . $entry['tableName'] . " ID " .$entry['recordID']." from build " . $json['build']."\n";

				if(!array_key_exists($entry['pushID'], $messages)){
					$messages[$entry['pushID']] = "Push ID " . $entry['pushID'] . " for build " . $json['build']."\n";
				}

				$messages[$entry['pushID']] .= $entry['tableName'] . " ID " .$entry['recordID']."\n";
			}
		}else{
			// Without Push ID
			if($entry['isValid'] == 1){
				$insertCachedEntryQ->execute([$entry['recordID'], $entry['tableName'], $entry['dataMD5'], $json['build']]);
				if($insertCachedEntryQ->rowCount() == 1){
					echo "[Hotfix updater] Inserted new cached entry, Table " . $entry['tableName'] . " ID " .$entry['recordID']." from build " . $json['build']." with MD5 " . $entry['dataMD5']." \n";

					if(!array_key_exists(filemtime($file), $messages)){
						$messages[filemtime($file)] = "Discovered new DBCache entries for build " . $json['build']."\n";
					}

					$messages[filemtime($file)] .= $entry['tableName'] . " ID " .$entry['recordID']."\n";
				}
			}
		}
	}

	foreach($messages as $message){
		telegramSendMessage($message);
	}

	$output2 = shell_exec("cd /home/wow/hotfixdumper; dotnet WoWTools.HotfixDumper.dll " . escapeshellarg($file) . " " . escapeshellarg("/home/wow/dbd/WoWDBDefs/definitions") . " true");
	foreach(explode("\n", $output2) as $line){
		if(empty($line))
			continue;

		$expl = explode(" ", trim($line));
		if(!in_array($expl[0], $knownKeys)){
			echo "Found new key! " . $expl[0]." " . $expl[1]."\n";
		}
	}

	$processedMD5s[] = $md5;
}
?>
