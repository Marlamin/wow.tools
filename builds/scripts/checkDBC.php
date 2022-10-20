<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

include(__DIR__ . "/../../inc/config.php");

$disableBugsnag = true;

// Manually update this once the script has been ran and hasn't detected any encrypted sections before a certain build number to speed this up in the future. Only update after 2 runs without detected encryptions.
$startAtBuild = 29599; 

$dbcFDIDMap = $pdo->query("SELECT REPLACE(REPLACE(`filename`, \"dbfilesclient/\", \"\"), \".db2\", \"\"), `id` FROM wow_rootfiles WHERE `filename` LIKE 'DBFilesClient/%.db2'")->fetchAll(PDO::FETCH_KEY_PAIR);
$dbcFDIDs = array_values($dbcFDIDMap);
$dbcMap = $pdo->query("SELECT `id`, `name` FROM wow_dbc_tables ORDER BY id ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
$versionMap = $pdo->query("SELECT `id`, `version` FROM wow_builds ORDER BY id ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
$tableVersions = $pdo->query("SELECT versionid, tableid, contenthash FROM wow_dbc_table_versions ORDER BY versionid ASC")->fetchAll(PDO::FETCH_ASSOC);
$selectRootByBuild = $pdo->prepare("SELECT `hash`, `root_cdn` FROM wow_buildconfig WHERE description LIKE ?");
$getCDNCByBuildC = $pdo->prepare("SELECT cdnconfig FROM wow_versions WHERE buildconfig = ?");
$prevVersion = "";
$manifest = [];
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
        if($buildEx[0] < 8 || $buildEx[3] < $startAtBuild){
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

        $manifest = [];

        if(!file_exists("/home/wow/buildbackup/manifests/" . $root . ".txt") || filesize("/home/wow/buildbackup/manifests/" . $root . ".txt") == 0){
            echo "Dumping manifest..";
            $output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet /home/wow/buildbackup/BuildBackup.dll dumproot2 " . $root . " > /home/wow/buildbackup/manifests/" . $root . ".txt");
            echo "..done!\n";
        }
    
        echo "Parsing manifest " .$root . "\n";

        if (($handle = fopen("/home/wow/buildbackup/manifests/" . $root . ".txt", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $manifest[$data[2]] = $data[3];
            }
            fclose($handle);
        }

        $prevVersion = $version;
    }
    
    $filename = "/home/wow/dbcs/" . $version . "/dbfilesclient/" . $dbcMap[$tableVersion['tableid']] . ".db2";
    if(file_exists($filename)){
        if($manifest[$dbcFDIDMap[$dbcMap[$tableVersion['tableid']]]] != md5_file($filename)){
            echo "File " . $dbcMap[$tableVersion['tableid']] . " does not match MD5 " . $manifest[$dbcFDIDMap[$dbcMap[$tableVersion['tableid']]]] ."\n";
            $toExtract[] = $dbcFDIDMap[$dbcMap[$tableVersion['tableid']]] . ";" . "dbfilesclient/".$dbcMap[$tableVersion['tableid']].".db2";
        }
    }else{
        echo "File " . $dbcMap[$tableVersion['tableid']] . " does not exist\n";
        $toExtract[] = $dbcFDIDMap[$dbcMap[$tableVersion['tableid']]] . ";" . "dbfilesclient/".$dbcMap[$tableVersion['tableid']].".db2";
    }
}