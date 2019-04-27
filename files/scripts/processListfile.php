<?php
require_once("/var/www/wow.tools/inc/config.php");

if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");

if(empty($argv[1])){
	die("Missing listfile argument");
}

$listfile = $argv[1];

if(!file_exists($listfile)){
	die("File does not exist!");
}

$cq = $pdo->prepare("SELECT * FROM wow_rootfiles WHERE id = ? AND verified = 0");
$uq = $pdo->prepare("UPDATE wow_rootfiles SET filename = ? WHERE id = ?");

$file = fopen($listfile, 'r');
if(!$file){
	die("Unable to open file " . $listfile);
}

$write = false;
$changesMade = false;
while (($line = fgets($file)) !== false) {
	if(empty($line))
		continue;

	$split = explode(";", $line);
	$fdid = $split[0];
	if(count($split) != 2)
		continue;
	$fname = strtolower(str_replace("\\", "/", trim($split[1])));
	$cq->execute([$fdid]);
	$row = $cq->fetch();
	if(empty($row['id'])){
		// Nothing at all
	}else if(empty($row['filename'])){
		// No filename currently set
		echo "Adding ".$fname." to ".$row['id']." (".$row['type'].")\n";
		if($write){
			$uq->execute([$fname, $fdid]);
			$changesMade = true;
		}
	}else if($row['filename'] == $fname){
		// echo "Skipping ".$fname.", same as ".$row['filename']." (".$row['id'].",".$row['type'].")\n";
	}else{
		// Filename currently set. Overwrite?
		echo "Overriding ".$row['filename']." (".$row['id'].",".$row['type'].") with ".$fname."\n";
		if($write){
			$uq->execute([$fname, $fdid]);
			$changesMade = true;
		}
	}
}

if($changesMade){
	flushQueryCache();
}

?>