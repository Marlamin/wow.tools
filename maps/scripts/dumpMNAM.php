<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");
require_once("../../inc/config.php");

foreach($pdo->query("SELECT internal, wdtFileDataID FROM wow_maps_maps WHERE wdtFileDataID IS NOT NULL") as $map){
	echo $map['internal']."\n";

	$destinationjson = "/var/www/wow.tools/maps/data/mnam/".$map['wdtFileDataID'].".json";

	$tempname = tempnam("/tmp", "mnamdump");

	$data = @file_get_contents("https://wow.tools/casc/file/fdid?filedataid=" . $map['wdtFileDataID'] . "&buildconfig=a5d0282ea0d408bdd865de4bd45371c0&cdnconfig=c7ece5fa19f4e4c538cb0a2cf15589d4&filename=data.wdt");
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