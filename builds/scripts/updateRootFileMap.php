<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");

include(__DIR__ . "/../../inc/config.php");

function getFileDataIDs($buildconfig){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://wow.tools/casc/root/fdids?buildconfig=" . $buildconfig);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($ch);
	if(!$data){
		echo "cURL fail: " . print_r(curl_error($ch))."\n";
	}
	curl_close($ch);
	if($data == ""){
		return false;
	}else{
		return json_decode($data);
	}
}

$builds = $pdo->query("SELECT id, hash FROM wow_buildconfig ORDER BY id ASC")->fetchAll(PDO::FETCH_KEY_PAIR);


foreach($builds as $buildconfigid => $buildconfighash){
	$checkQ = $pdo->query("SELECT fileid FROM wow_rootfiles_builds WHERE FIND_IN_SET(".$buildconfigid.", buildconfigids) LIMIT 0,1");
	if(!empty($checkQ->fetchColumn())){
		echo "[".date("H:i:s")."] [Build ".$buildconfigid."] Skipping, already imported!\n";
		continue;
	}

	echo "[".date("H:i:s")."] [Build ".$buildconfigid."] Getting FileDataIDs for hash ". $buildconfighash . "...\n";
	$fdids = getFileDataIDs($buildconfighash);

	$tempFile = tempnam("/tmp", "filesperbuild");
	echo "[".date("H:i:s")."] [Build ".$buildconfigid."] Writing temporary CSV file with ".count($fdids)." FDIDs to disk (".$tempFile.")..\n";
	file_put_contents($tempFile, implode("\n", $fdids));

	echo "[".date("H:i:s")."] [Build ".$buildconfigid."] Creating temporary table..\n";
	$pdo->exec("DROP TABLE IF EXISTS wow_rootfiles_buildstemp");
	$pdo->exec("CREATE TABLE `wow_rootfiles_buildstemp` (
	  `filedataid` int(11) NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

	echo "[".date("H:i:s")."] [Build ".$buildconfigid."] Loading data into temporary table..\n";
	$pdo->exec("LOAD DATA LOCAL INFILE '".$tempFile."' INTO TABLE wow_rootfiles_buildstemp
		FIELDS TERMINATED BY ';' LINES TERMINATED BY '\n'
	");

	echo "[".date("H:i:s")."] [Build ".$buildconfigid."] Removing files from temporary table that already have this build set..\n";
	$pdo->exec("DELETE FROM wow_rootfiles_buildstemp WHERE filedataid IN (SELECT fileid FROM wow_rootfiles_builds WHERE FIND_IN_SET(".$buildconfigid.", buildconfigids))");

	echo "[".date("H:i:s")."] [Build ".$buildconfigid."] Updating main table..\n";
	$pdo->exec("INSERT INTO wow_rootfiles_builds (fileid, buildconfigids) SELECT filedataid, '".$buildconfigid."' FROM wow_rootfiles_buildstemp ON DUPLICATE KEY UPDATE buildconfigids = CONCAT(buildconfigids, ',".$buildconfigid."')
	");

	echo "[".date("H:i:s")."] [Build ".$buildconfigid."] Cleaning up..\n";
	$pdo->exec("DROP TABLE IF EXISTS wow_rootfiles_buildstemp");
	unlink($tempFile);
}
