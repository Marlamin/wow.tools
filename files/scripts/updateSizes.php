<?php
include("../../inc/config.php");

if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");

if(empty($argv[1])){
	die("Need buildconfig as argument");
}

$q = $pdo->prepare("SELECT hash, description FROM wow_buildconfig WHERE hash = ?");
$q->execute([$argv[1]]);
$row = $q->fetch();

// foreach($pdo->query("SELECT hash, description FROM wow_buildconfig ORDER BY builton ASC") as $row){
	echo "Updating sizes for ".$row['description']."\n";

	$tempname = tempnam("/tmp", "SIZES");

	$output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll dumpsizes wow ".escapeshellarg($row['hash'])." > ".escapeshellarg($tempname));

	$pdo->exec("
		LOAD DATA LOCAL INFILE '".$tempname."'
		INTO TABLE `wow_rootfiles_sizes`
		FIELDS TERMINATED BY ' ' ESCAPED BY '\b'
		LINES TERMINATED BY '\n'
		(contenthash, size)
	");

	unlink($tempname);
// }

?>