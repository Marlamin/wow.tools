<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");
require_once("../../inc/config.php");

if(empty($argv[1])){
	die("Need x.x.x.x build as argument!");
}

$build = $argv[1];

$headers = json_decode(file_get_contents("https://wow.tools/api/header/map/?build=" . $build), true)['headers'];
$mapjson = json_decode(file_get_contents("https://wow.tools/api/data/map/?build=" . $build . "&start=0&length=3000"), true);
if(empty($mapjson['data'])){
	die("Unable to retrieve map DBC data for this build!");
}
$mapsByInternal = [];
foreach($mapjson['data'] as $data){
	$mapsByInternal[$data[1]] = [];
	foreach($data as $index => $field){
		$mapsByInternal[$data[1]][$headers[$index]] = $field;
	}
}

$updq = $pdo->prepare("UPDATE wow_maps_maps SET wdtFileDataID = ?, internal_mapid = ? WHERE id = ?");
foreach($pdo->query("SELECT id, internal FROM wow_maps_maps WHERE wdtFileDataID IS NULL OR internal_mapid IS NULL") as $map){
	echo $map['internal']."\n";
	if(array_key_exists($map['internal'], $mapsByInternal))
	{
		$updq->execute([$mapsByInternal[$map['internal']]['WdtFileDataID'], $mapsByInternal[$map['internal']]['ID'], $map['id']]);
	}
}

?>