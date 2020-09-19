<?php

include("../../inc/config.php");

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

$knownfdids = $pdo->query("SELECT id FROM wow_rootfiles")->fetchAll(PDO::FETCH_COLUMN);
$q = $pdo->query("SELECT * FROM `wowdata`.manifestinterfacedata");

$files = [];

$manifestTableID = $pdo->query("SELECT id FROM wow_dbc_tables WHERE name = 'manifestinterfacedata'")->fetch(PDO::FETCH_COLUMN);

$versionCacheByID = [];
foreach ($pdo->query("SELECT id, version FROM wow_builds") as $version) {
    $versionCacheByID[$version['id']] = $version['version'];
}

$versions = [];
foreach ($pdo->query("SELECT versionid, tableid FROM wow_dbc_table_versions WHERE hasDefinition = 1 AND tableid = (SELECT id FROM wow_dbc_tables WHERE name = 'manifestinterfacedata')") as $tv) {
    $versions[] = $tv['versionid'];
}

foreach ($versions as $version) {
    $version = $versionCacheByID[$version];
    echo $version . "\n";

    $db2 = "https://wow.tools/dbc/api/export/?name=manifestinterfacedata&build=" . $version;

    if (!file_exists("/tmp/mid." . $version . ".csv")) {
        $outputdump = shell_exec("/usr/bin/curl " . escapeshellarg($db2) . " -o /tmp/mid." . $version . ".csv 2>&1");
    }

    $file = fopen("/tmp/mid." . $version . ".csv", 'r');
    while (($line = fgetcsv($file, 1000, ',', '"')) !== false) {
        if ($line[0] == 'ID') {
            continue;
        }
        // 0 = fdid
        // 1 = path
        // 2 = filename
        if (!array_key_exists($line[0], $files)) {
            $files[$line[0]] = strtolower(str_replace("\\", "/", $line[1] . $line[2]));
        }
    }
    fclose($file);
}

echo "Inserting..\n";
$insq = $pdo->prepare("INSERT INTO wow_rootfiles (id, filename, lookup, verified) VALUES (?, ?, ?, 1)");
foreach ($files as $fdid => $fname) {
    if (!in_array($fdid, $knownfdids)) {
        $output = shell_exec("dotnet /home/wow/buildbackup/BuildBackup.dll calchash " . escapeshellarg($fname));
        $lookup = explode(" ", trim($output))[1];
        echo $fdid . ": " . $fname . " (" . $lookup . ")\n";
        $insq->execute([$fdid, $fname, $lookup]);
    }
}
