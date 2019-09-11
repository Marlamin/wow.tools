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

$q = $pdo->query("SELECT hash, description FROM wow_buildconfig WHERE product IN ('wowt', 'wow_beta') ORDER BY id DESC LIMIT 1");
$row = $q->fetch();

$rawdesc = str_replace("WOW-", "", $row['description']);
$build = substr($rawdesc, 0, 5);
$rawdesc = str_replace(array($build, "patch"), "", $rawdesc);
$descexpl = explode("_", $rawdesc);
$fullbuild = $descexpl[0].".".$build;

$encryptedfiles = array();

echo "[Encrypted file list] Parsing ".$fullbuild."\n";

$cmd = "cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll dumpencrypted wow ".escapeshellarg($row['hash']);
$output = shell_exec($cmd);
foreach(explode("\n", $output) as $line){
	if(empty(trim($line))) continue;
	$line = explode(" ", trim($line));
	$filedataid = $line[0];
	foreach(explode(",", $line[1]) as $key){
		$lookup = reverseLookup($key);
		$encryptedfiles[$filedataid][] = $lookup;
	}
}

echo "[Encrypted file list] Currently have " . count($encryptedfiles) . " encrypted filedataids!\n";
ksort($encryptedfiles);

$current = array();
$q = $pdo->query("SELECT * FROM wow_encrypted");
foreach($q->fetchAll() as $enc){
	$current[] = $enc['filedataid'].".".$enc['keyname'];
}

$inserted = 0;

$q = $pdo->prepare("INSERT INTO wow_encrypted (filedataid, keyname) VALUES (:filedataid, :key)");

foreach($encryptedfiles as $filedataid => $keysarr){
	foreach($keysarr as $key){
		if(!in_array($filedataid.".".$key, $current)){
			$q->bindParam(":filedataid", $filedataid);
			$q->bindParam(":key", $key);
			$q->execute();
			$inserted++;
		}
	}
}

echo "[Encrypted file list] Inserted " . $inserted . " new encrypted filedataids!\n";
echo "[TACT key list] Dumping current TACT keys for ".$fullbuild."..\n";

$inserted = 0;

$db2 = file_get_contents("https://wow.tools/api/data/tactkeylookup/?build=".$fullbuild."&draw=1&start=0&length=1000");
$tactkeylookups = json_decode($db2, true)['data'];
echo "[TACT key list] Have " . count($tactkeylookups) ." TACT key lookups from tactkeylookup.db2..\n";

$q = $pdo->prepare("INSERT IGNORE INTO wow_tactkey (id, keyname, added) VALUES (?, ?, ?)");
foreach($tactkeylookups as $tactkeylookup){
	$id = $tactkeylookup[0];

	$lookup = "";
	for($i = 8; $i > 0; $i--){
		$lookup = $lookup . str_pad(dechex((int)$tactkeylookup[$i]), 2, '0', STR_PAD_LEFT);
	}
	$lookup = strtoupper($lookup);

	$q->execute([$id, $lookup, $row['description']]);

	if($q->rowCount() > 0){
		$inserted++;
	}
}

echo "[TACT key list] Done, inserted " . $inserted . " new TACT keys!\n";