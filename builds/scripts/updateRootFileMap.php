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

/* Erorus method: https://gist.github.com/erorus/560e53f434948b67314aa5bd0533d5ca */
/**
 * Returns a SQL statement to save a list of file IDs associated with the given build ID.
 *
 * @param int $buildId
 * @param int[] $fileIds
 */
function generateBuildFilesQuery(int $buildId, array $fileIds): string {
    $maxId = max($fileIds);
    $buffer = array_fill(0, floor($maxId / 8) + 1, 0);
    foreach ($fileIds as $fileId) {
        $bufferIndex = (int)floor($fileId / 8);
        $bitPosition = $fileId % 8;

        $buffer[$bufferIndex] = $buffer[$bufferIndex] | (1 << $bitPosition);
    }

    $binString = '';
    foreach ($buffer as $char) {
        $binString .= chr($char);
    }

    return "REPLACE INTO wow_rootfiles_builds_erorus (build, files) VALUES ($buildId, FROM_BASE64('" . base64_encode($binString) . "'));\n";
}

$checkQ = $pdo->prepare("SELECT build FROM wow_rootfiles_builds_erorus WHERE build = ?");
$builds = $pdo->query("SELECT id, hash FROM wow_buildconfig GROUP BY root ORDER BY id ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
foreach($builds as $buildconfigid => $buildconfighash){
	$checkQ->execute([$buildconfigid]);
	if(!empty($checkQ->fetchColumn())){
		continue;
	}

	echo "[".date("H:i:s")."] [Build ".$buildconfigid."] Getting FileDataIDs for hash ". $buildconfighash . "...\n";;
	$fdids = getFileDataIDs($buildconfighash);

	echo "[".date("H:i:s")."] [Build ".$buildconfigid."] Loading data into table..\n";
	$pdo->query(generateBuildFilesQuery($buildconfigid, $fdids));
}
