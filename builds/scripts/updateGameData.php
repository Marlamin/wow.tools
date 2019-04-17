<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");

include("../../inc/config.php");

$q = $pdo->query("SELECT description FROM wow_buildconfig WHERE product = 'wowt' ORDER BY description DESC LIMIT 1");
$row = $q->fetch();

$rawdesc = str_replace("WOW-", "", $row['description']);
$build = substr($rawdesc, 0, 5);
$rawdesc = str_replace(array($build, "patch"), "", $rawdesc);
$descexpl = explode("_", $rawdesc);
$outdir = $descexpl[0].".".$build;

$db2 = "https://wow.tools/api/export/?name=soundkitname&build=".$outdir;
$csv = "/tmp/soundkitname.csv";
if(file_exists($csv)){ unlink($csv); }
$outputdump = shell_exec("/usr/bin/curl ".escapeshellarg($db2)." -o ".escapeshellarg($csv)." 2>&1");
if(!file_exists($csv)){
	echo "An error occured during soundkitname import: ".$outputdump;
}else{
	echo "	Writing soundkitname..";
	$pdo->exec("
		LOAD DATA LOCAL INFILE '".$csv."'
		INTO TABLE `wowdata`.soundkitname
		FIELDS TERMINATED BY ','
		LINES TERMINATED BY '\n'
		IGNORE 1 LINES
		(id, name)
	");
	echo "..done!\n";
}

$db2 = "https://wow.tools/api/export/?name=soundkitname&build=".$outdir;
$csv = "/tmp/soundkitname.csv";
if(file_exists($csv)){ unlink($csv); }
$outputdump = shell_exec("/usr/bin/curl ".escapeshellarg($db2)." -o ".escapeshellarg($csv)." 2>&1");
if(!file_exists($csv)){
	echo "An error occured during soundkitname import: ".$outputdump;
}else{
	echo "	Writing soundkitname..";
	$pdo->exec("
		LOAD DATA LOCAL INFILE '".$csv."'
		INTO TABLE `wowdata`.soundkitname
		FIELDS TERMINATED BY ','
		LINES TERMINATED BY '\n'
		IGNORE 1 LINES
		(id, name)
	");
	echo "..done!\n";
}

// $db2 = "https://wow.tools/api/export/?name=manifestinterfacedata&build=".$outdir;
// $csv = "/tmp/manifestinterfacedata.csv";
// if(file_exists($csv)){ unlink($csv); }
// $outputdump = shell_exec("/usr/bin/curl ".escapeshellarg($db2)." -o ".escapeshellarg($csv)." 2>&1");
// if(!file_exists($csv)){
// 	echo "An error occured during manifestinterfacedata import: ".$outputdump;
// }else{
// 	echo "	Writing manifestinterfacedata..";
// 	$pdo->exec("
// 		LOAD DATA LOCAL INFILE '".$csv."'
// 		INTO TABLE `wowdata`.manifestinterfacedata
// 		FIELDS TERMINATED BY ',' ESCAPED BY '\b'
// 		LINES TERMINATED BY '\n'
// 		IGNORE 1 LINES
// 		(filedataid, path, name)
// 	");
// 	echo "..done!\n";
// }
