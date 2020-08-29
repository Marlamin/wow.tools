<?php
$disableBugsnag = true;
include("../../inc/config.php");

if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");


foreach($pdo->query("SELECT GROUP_CONCAT(id) as ids, filename FROM wow_rootfiles GROUP BY filename HAVING COUNT(*) > 1") as $dupeName){
	if(empty($dupeName['filename']))
		continue;

	$dupeIDs = explode(",", $dupeName['ids']);
	foreach($dupeIDs as $dupeID){
		$ext = pathinfo($dupeName['filename'], PATHINFO_EXTENSION);
		$newName = str_replace("." . $ext, "_" . $dupeID . "." . $ext, $dupeName['filename']);
		echo $dupeID . ";" . $newName."\n";
	}
}