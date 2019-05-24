<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");

include("../../inc/config.php");

function reverseLookup($bytes){
	$result = $bytes[14].$bytes[15];
	$result .= $bytes[12].$bytes[13];
	$result .= $bytes[10].$bytes[11];
	$result .= $bytes[8].$bytes[9];
	$result .= $bytes[6].$bytes[7];
	$result .= $bytes[4].$bytes[5];
	$result .= $bytes[2].$bytes[3];
	$result .= $bytes[0].$bytes[1];
	return $result;
}

$q = $pdo->query("SELECT hash, description FROM wow_buildconfig ORDER BY id DESC LIMIT 1");
$row = $q->fetch();

$encryptedfiles = array();

echo "[Encrypted file list] Parsing ".$row['description']."\n";

$cmd = "cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll dumpencrypted wow ".escapeshellarg($row['hash']);
$output = shell_exec($cmd);
foreach(explode("\n", $output) as $line){
	if(empty(trim($line))) continue;
	$line = explode(" ", trim($line));
	$filedataid = $line[0];
	$lookup = reverseLookup($line[1]);
	$encryptedfiles[$filedataid] = $lookup;
}

echo "[Encrypted file list] Currently have " . count($encryptedfiles) . " encrypted filedataids!\n";
ksort($encryptedfiles);

$currentids = array();
$q = $pdo->query("SELECT filedataid FROM wow_encrypted");
foreach($q->fetchAll() as $row){
	$currentids[] = $row['filedataid'];
}

$inserted = 0;

$q = $pdo->prepare("INSERT INTO wow_encrypted (filedataid, keyname) VALUES (:filedataid, :key)");

foreach($encryptedfiles as $filedataid => $key){
	if(!in_array($filedataid, $currentids)){
		$q->bindParam(":filedataid", $filedataid);
		$q->bindParam(":key", $key);
		$q->execute();
		$inserted++;
	}
}

echo "[Encrypted file list] Inserted " . $inserted . " new encrypted filedataids!\n";