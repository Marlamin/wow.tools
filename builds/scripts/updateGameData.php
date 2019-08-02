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

$db2 = "https://wow.tools/api/export/?name=soundkitentry&build=".$outdir;
$csv = "/tmp/soundkitentry.csv";
if(file_exists($csv)){ unlink($csv); }
$outputdump = shell_exec("/usr/bin/curl ".escapeshellarg($db2)." -o ".escapeshellarg($csv)." 2>&1");
if(!file_exists($csv)){
	echo "An error occured during soundkitentry import: ".$outputdump;
}else{
	echo "	Writing soundkitentry..";
	$pdo->exec("
		LOAD DATA LOCAL INFILE '".$csv."'
		INTO TABLE `wowdata`.soundkitentry
		FIELDS TERMINATED BY ','
		LINES TERMINATED BY '\n'
		IGNORE 1 LINES
		(@id, @soundkitid, @filedataid) SET id=@filedataid, entry=@soundkitid
	");
	echo "..done!\n";
}

$db2 = "https://wow.tools/api/export/?name=manifestinterfacedata&build=".$outdir;
$csv = "/tmp/manifestinterfacedata.csv";
if(file_exists($csv)){ unlink($csv); }
$outputdump = shell_exec("/usr/bin/curl ".escapeshellarg($db2)." -o ".escapeshellarg($csv)." 2>&1");
if(!file_exists($csv)){
	echo "An error occured during manifestinterfacedata import: ".$outputdump;
}else{
	echo "	Writing manifestinterfacedata..";
	$pdo->exec("
		LOAD DATA LOCAL INFILE '".$csv."'
		INTO TABLE `wowdata`.manifestinterfacedata
		FIELDS TERMINATED BY ',' ESCAPED BY '\b'
		LINES TERMINATED BY '\n'
		IGNORE 1 LINES
		(filedataid, path, name)
	");
	echo "..done!\n";
}

$db2 = "https://wow.tools/api/export/?name=creaturemodeldata&build=".$outdir;
$csv = "/tmp/creaturemodeldata.csv";
if(file_exists($csv)){ unlink($csv); }
$outputdump = shell_exec("/usr/bin/curl ".escapeshellarg($db2)." -o ".escapeshellarg($csv)." 2>&1");
if(!file_exists($csv)){
	echo "An error occured during creaturemodeldata import: ".$outputdump;
}else{
	echo "	Writing creaturemodeldata..";
	$pdo->exec("
		LOAD DATA LOCAL INFILE '".$csv."'
		INTO TABLE `wowdata`.creaturemodeldata
		FIELDS TERMINATED BY ',' ESCAPED BY '\b'
		LINES TERMINATED BY '\n'
		IGNORE 1 LINES
		(@id, @geobox1, @geobox2, @geobox3, @geobox4, @geobox5, @geobox6, @flags, @filedataid) SET id=@id, filedataid=@filedataid
	");
	echo "..done!\n";
}
