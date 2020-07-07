<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");
require_once(__DIR__ . "/../../inc/config.php");

foreach($pdo->query("SELECT internal, wdtFileDataID FROM wow_maps_maps WHERE wdtFileDataID IS NOT NULL") as $map){
	echo $map['internal']."\n";

	$destinationjson = __DIR__ . "/../data/mnam/".$map['wdtFileDataID'].".json";

	if(file_exists($destinationjson))
		continue;

	$tempname = tempnam("/tmp", "mnamdump");

	$data = @file_get_contents("https://wow.tools/casc/file/fdid?filedataid=" . $map['wdtFileDataID'] . "&buildconfig=08cc8b6aa4c2a4468d71cd1b98b2d542&cdnconfig=8c3b2d29f6746551e930930d1359531b&filename=data.wdt");
	if(!$data || empty($data)){
		echo "Unable to dump WDT (fdid " . $map['wdtFileDataID'] . ") for " . $map['internal'].", likely unshipped!\n";
		continue;
	}

	file_put_contents($tempname, $data);

	$output = shell_exec("cd /home/wow/jsondump; dotnet WoWJsonDumper.dll wdt " . escapeshellarg($tempname) . " > " . escapeshellarg($destinationjson));
	print_r($output);

	unlink($tempname);
}

?>