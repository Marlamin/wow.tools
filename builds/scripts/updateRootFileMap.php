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

$checkSetQ = $pdo->prepare("SELECT COUNT(fileid) FROM wow_rootfiles_builds WHERE fileid = ? AND FIND_IN_SET(?, buildconfigids)");
$addToSetQ = $pdo->prepare("INSERT INTO wow_rootfiles_builds (fileid, buildconfigids) VALUES(?, ?) ON DUPLICATE KEY UPDATE buildconfigids = CONCAT(buildconfigids, ?)");

$builds = $pdo->query("SELECT id, hash FROM wow_buildconfig")->fetchAll(PDO::FETCH_KEY_PAIR);

foreach($builds as $buildconfigid => $buildconfighash){
	echo "[Build ".$buildconfigid."] Preparing transaction for hash ". $buildconfighash . "...\n";
	$i = 0;
	$pdo->beginTransaction();
	$fdids = getFileDataIDs($buildconfighash);
	$totalFDIDs = count($fdids);
	foreach($fdids as $fileid){
		$checkSetQ->execute([$fileid, $buildconfigid]);

		if((int)$checkSetQ->fetchColumn() === 0){
			$addToSetQ->execute([$fileid, $buildconfigid, "," . $buildconfigid]);
		}

		if($i % 10000 == 0){
			echo "[Build ".$buildconfigid."] ".$i."/".$totalFDIDs."\n";
		}

		$i++;
	}
	echo "[Build ".$buildconfigid."] Committing...";
	$pdo->commit();
	echo "done!\n";

}
