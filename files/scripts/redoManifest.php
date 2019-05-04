<?php
include("../../inc/config.php");

if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");

if(empty($argv[1])){
	die("Need buildconfig as argument");
}

$q = $pdo->prepare("SELECT root_cdn, description FROM wow_buildconfig WHERE hash = ?");
$q->execute([$argv[1]]);
$build = $q->fetch();
$root = $build['root_cdn'];
$desc = $build['description'];

echo "Processing ".$desc."\n";

$manifest = "/home/wow/buildbackup/manifests/".$root.".txt";
if(file_exists($manifest)){
	echo "	Removing current manifest..";
	unlink($manifest);
	echo "..done!\n";
}

echo "	Dumping manifest..";
$output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll dumproot2 ".$root." > ".escapeshellarg($manifest));
echo "..done!\n";

$unverifiedq = $pdo->query("SELECT id, lookup FROM wow_rootfiles WHERE verified = 0 AND lookup != ''")->fetchAll();
$unverifieds = [];
foreach($unverifiedq as $unverified){
	$unverifieds[$unverified['id']] = $unverified['lookup'];
}

$uq = $pdo->prepare("UPDATE wow_rootfiles SET lookup = '' WHERE id = ? AND verified = 0");

$file = fopen($manifest, 'r');
while (($line = fgetcsv($file, 1000, ';', '"')) !== FALSE) {
	// 0 = filename
	// 1 = lookup
	// 2 = filedata
	// 3 = contenthash

	if(array_key_exists($line[2], $unverifieds) && empty($line[1])){
		$output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll calchash ".escapeshellarg("by_fdid_" . $line[2]));
		$lookups = explode(" ", trim($output));
		$lookup = $lookups[1];

		if($unverifieds[$line[2]] == $lookup){
			echo "Setting " . $line[2] . " to ''\n";
		}else{
			echo "Not setting " . $line[2] . " to '', lookups do not match! DB: " . $unverifieds[$line[2]] . ", PH lookup: " . $lookup . "\n";
		}
		$uq->execute([$line[2]]);
	}
}
fclose($file);
?>