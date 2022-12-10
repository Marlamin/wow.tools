<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

ini_set('memory_limit','1G');

require __DIR__ . "/../../inc/config.php";
function getFileDataIDs($root)
{
    if (!file_exists("/home/wow/buildbackup/manifests/" . $root . ".txt")) {
        echo "	Dumping manifest..";
        $output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet /home/wow/buildbackup/BuildBackup.dll dumproot2 " . $root . " > /home/wow/buildbackup/manifests/" . $root . ".txt");
        echo "..done!\n";

        if(!file_exists("/home/wow/buildbackup/manifests/" . $root . ".txt")){
            echo "	!!! Manifest missing, quitting..\n";
            die();
        }

        if(filesize("/home/wow/buildbackup/manifests/" . $root . ".txt") == 0){
            echo "	!!! Manifest dump empty, removing and quitting..\n";
            unlink("/home/wow/buildbackup/manifests/" . $root . ".txt");
            die();
        }
    }

    $fdids = [];

    if (($handle = fopen("/home/wow/buildbackup/manifests/" . $root . ".txt", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $fdids[] = $data[2];
        }
        fclose($handle);
    }

    return $fdids;
}

/* Erorus method: https://gist.github.com/erorus/560e53f434948b67314aa5bd0533d5ca */
/**
 * Returns a SQL statement to save a list of file IDs associated with the given build ID.
 *
 * @param int   $buildId
 * @param int[] $fileIds
 */
function generateBuildFilesQuery(int $buildId, array $fileIds): string
{
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
$builds = $pdo->query("SELECT id, hash, root_cdn FROM wow_buildconfig GROUP BY root ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($builds as $build) {
    $checkQ->execute([$build['id']]);
    if (!empty($checkQ->fetchColumn())) {
        continue;
    }

    echo "[" . date("H:i:s") . "] [Build " . $build['id'] . "] Getting FileDataIDs for hash " . $build['hash'] . "...\n";
    $fdids = getFileDataIDs($build['root_cdn']);
    print_r($fdids);
    if(!empty($fdids)){
        echo "[" . date("H:i:s") . "] [Build " . $build['id'] . "] Loading data into table..\n";
        $pdo->query(generateBuildFilesQuery($build['id'], $fdids));
    }else{
        echo "[" . date("H:i:s") . "] [Build " . $build['id'] . "] Failed to get list of filedataIDs from backend, skipping until next run..\n";
    }

}
