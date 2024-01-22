<?php

if (empty($includedProcessing) && php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

require_once(__DIR__ . "/../../inc/config.php");

$versionCacheByID = [];
foreach ($pdo->query("SELECT id, version FROM wow_builds") as $version) {
    $versionCacheByID[$version['id']] = $version['version'];
}

$tableCacheByID = [];
foreach ($pdo->query("SELECT id, name FROM wow_dbc_tables") as $table) {
    $tableCacheByID[$table['id']] = $table['name'];
}

$versionTableCache = [];
foreach ($pdo->query("SELECT versionid, tableid FROM wow_dbc_table_versions WHERE hasDefinition = 0") as $tv) {
    $versionTableCache[$tv['versionid']][] = $tv['tableid'];
}

$extractionDir = "/home/wow/dbcs/";
$extractedVersions = [];
foreach (glob($extractionDir . "*", GLOB_ONLYDIR) as $dir) {
    $extractedVersions[] = str_replace($extractionDir, "", $dir);
}

echo "[DBD processing] Parsing DBDs.";
$dbdCache = [];
foreach (glob("/home/wow/dbd/WoWDBDefs/definitions/*.dbd") as $dbd) {
    $reader = new DBDReader();
    $dbdCache[strtolower(basename(str_replace(".dbd", "", $dbd)))] = $reader->read($dbd);
}
echo ".done.\n";

$defq = $pdo->prepare("UPDATE wow_dbc_table_versions SET hasDefinition = ? WHERE versionid = ? AND tableid = ?");
foreach ($versionTableCache as $versionID => $versions) {
    $version = $versionCacheByID[$versionID];
    $build = new Build($version);

    foreach ($versions as $tableID) {
        $versionCompat = false;
        $layouthash = "";

        $table = $tableCacheByID[$tableID];
        if (!array_key_exists(strtolower($table), $dbdCache)) {
            echo "No DBD found for " . $table . " (".$version.")\n";
            continue;
        }

        if (!$versionCompat && in_array($version, $extractedVersions)) {
            // Read table on disk and find layouthash
            $db2 = $extractionDir . $version . "/dbfilesclient/" . $tableCacheByID[$tableID] . ".db2";
            if (file_exists($db2) && $build->build > 23436) {
                $file = fopen($db2, "r");
                fseek($file, 20);
                $header = fread($file, 8);
                $val = unpack("VTableHash/VLayoutHash", $header);
                $layouthash = strtoupper(str_pad(dechex($val['LayoutHash']), 8, '0', STR_PAD_LEFT));
                fclose($file);
            }
        }

        foreach ($dbdCache[strtolower($table)]['versionDefinitions'] as $versionDef) {
            if (in_array($build, $versionDef['builds'])) {
                $versionCompat = true;
            }

            foreach ($versionDef['buildRanges'] as $range) {
                if ($range->Contains($build)) {
                    $versionCompat = true;
                }
            }

            if (!empty($layouthash) && in_array($layouthash, $versionDef['layoutHashes'])) {
                echo "[DBD processing] Table " . $tableCacheByID[$tableID] . " for build " . $version . " has compatible layouthash: " . $layouthash . "\n";
                $versionCompat = true;
            }
        }

        if (!empty($layouthash) && !$versionCompat) {
            echo "No compatible definition found for " . $tableCacheByID[$tableID] . " for build " . $version . ", layouthash: " . $layouthash."\n";
        }

        if ($versionCompat) {
            $defq->execute([1, $versionID, $tableID]);
        }
    }
}
