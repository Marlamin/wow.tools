<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

include(__DIR__ . "/../../inc/config.php");

$disableBugsnag = true;

// Manually update this once the script has been ran and hasn't detected any encrypted sections before a certain build number to speed this up in the future. Only update after 2 runs without detected encryptions.
// Currently set at 8.3.0.32151 as it adds currently unknown keys EA6C3B8F210A077F (coming in 10.x) and 2A430C60DDCC75FF (unknown)
$startAtBuild = 32151; 

$dbcFDIDMap = $pdo->query("SELECT REPLACE(REPLACE(`filename`, \"dbfilesclient/\", \"\"), \".db2\", \"\"), `id` FROM wow_rootfiles WHERE `filename` LIKE 'DBFilesClient/%.db2'")->fetchAll(PDO::FETCH_KEY_PAIR);
$dbcMap = $pdo->query("SELECT `id`, `name` FROM wow_dbc_tables ORDER BY id ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
$versionMap = $pdo->query("SELECT `id`, `version` FROM wow_builds WHERE id IN (SELECT DISTINCT versionid FROM `wow_dbc_table_versions` WHERE `complete` = 0) ORDER BY id ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
$tableVersions = $pdo->query("SELECT versionid, tableid, contenthash FROM wow_dbc_table_versions WHERE complete = 0 AND contenthash IS NOT NULL ORDER BY versionid DESC")->fetchAll(PDO::FETCH_ASSOC);
$setTableVersionComplete = $pdo->prepare("UPDATE wow_dbc_table_versions SET complete = 1 WHERE versionid = ? AND tableid = ?");
$selectRootByBuild = $pdo->prepare("SELECT `hash`, `root_cdn` FROM wow_buildconfig WHERE description LIKE ?");
$getCDNCByBuildC = $pdo->prepare("SELECT cdnconfig FROM wow_versions WHERE buildconfig = ?");
$prevVersion = "";
$root = "";
$toExtract = [];

echo "WARNING: Only checking builds newer than " . $startAtBuild ."..\n";
foreach($tableVersions as $tableVersion){
    $version = $versionMap[$tableVersion['versionid']];
    if($prevVersion != $version){
        if($prevVersion != null && isset($version)){
            $getCDNCByBuildC->execute([$buildconfig]);
            $cdnconfig = $getCDNCByBuildC->fetch(PDO::FETCH_COLUMN);
            if(empty($cdnconfig)){
                echo "Empty cdnconfig for " .$buildconfig.", cannot extract\n";
            }else{
                if(count($toExtract) > 0){
                    file_put_contents("/tmp/dbcs-" . $root . ".txt", implode(PHP_EOL, $toExtract));
                    echo "[DB2 export] Exporting " . count($toExtract) . " DBCs to " . $prevVersion . "\n";
                    $output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll extractfilesbyfdidlist " . $buildconfig . " " . $cdnconfig . " /home/wow/dbcs/" . $prevVersion . "/ " . "/tmp/dbcs-" . $root . ".txt");
                    print_r($output);
                    unlink("/tmp/dbcs-" . $root . ".txt");
                    $toExtract = [];
                }
            }
        }

        $buildEx = explode(".", $version);

        // 1.x and 2.x have no remaining encrypted files, still check 3.x and 8.3.x+
        if($buildEx[0] == 1 || $buildEx[1] == 2 || $buildEx[3] < $startAtBuild){
            continue;
        }

        echo "Checking " . $version . "..\n";

        $selectRootByBuild->execute(["WOW-" . $buildEx[3] . "patch%"]);
        $build = $selectRootByBuild->fetch(PDO::FETCH_ASSOC);
        if(empty($build)){
            echo "Build not found, skipping..\n";
            continue;
        }

        $buildconfig = $build['hash'];
        $root = $build['root_cdn'];
        $prevVersion = $version;
    }
    
    $filename = "/home/wow/dbcs/" . $version . "/dbfilesclient/" . $dbcMap[$tableVersion['tableid']] . ".db2";
    if(file_exists($filename)){
        if($tableVersion['contenthash'] != md5_file($filename)){
            echo "File " . $dbcMap[$tableVersion['tableid']] . " does not match MD5 " . $tableVersion['contenthash'] ."\n";
            $toExtract[] = $dbcFDIDMap[$dbcMap[$tableVersion['tableid']]] . ";" . "dbfilesclient/".$dbcMap[$tableVersion['tableid']].".db2";
        }else{
            $setTableVersionComplete->execute([$tableVersion['versionid'], $tableVersion['tableid']]);
        }
    }else{
        echo "File " . $dbcMap[$tableVersion['tableid']] . " does not exist\n";
        $toExtract[] = $dbcFDIDMap[$dbcMap[$tableVersion['tableid']]] . ";" . "dbfilesclient/".$dbcMap[$tableVersion['tableid']].".db2";
    }
}