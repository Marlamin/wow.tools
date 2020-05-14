<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");
require_once(__DIR__ . "/../../inc/config.php");

$fullrun = false;
if(!empty($argv[1]) && $argv[1] == "fullrun"){
	echo "Full run, reimporting all caches!\n";
	$fullrun = true;
}

$processedMD5s = [];
$files = glob('/home/wow/dbcdumphost/caches/*.wdb');
foreach($files as $file) {
	// Only process hotfixes newer than 6 hours ago
	if(!$fullrun && filemtime($file) < strtotime("-6 hours"))
		continue;

	$md5 = md5_file($file);
	if(in_array($md5, $processedMD5s))
		continue;

	echo "[Cache updater] Reading " . $file . "\n";
	$output = shell_exec("cd /home/wow/wdbupdater; dotnet WoWTools.WDBUpdater.dll " . escapeshellarg($file) . " mysql");
	if(!$fullrun && substr($output, -34) != "New entries: 0\nUpdated entries: 0\n"){
		telegramSendMessage($output);
	}
	$processedMD5s[] = $md5;
}
?>
