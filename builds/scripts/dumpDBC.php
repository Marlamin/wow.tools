<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

if(empty($argv[1])){
    $product = "wow";
}else{
    $product = $argv[1];
}

function getFileDataIDs($root, $product = "wow")
{
    if (!file_exists("/home/wow/buildbackup/manifests/" . $root . ".txt")) {
        echo "	Dumping manifest..";
        $output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet /home/wow/buildbackup/BuildBackup.dll dumproot2 " . $root . " " . $product . " > /home/wow/buildbackup/manifests/" . $root . ".txt");
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

function makeOutDir($description)
{
    $rawdesc = str_replace("WOW-", "", $description);
    $build = substr($rawdesc, 0, 5);
    $rawdesc = str_replace(array($build, "patch"), "", $rawdesc);
    $descexpl = explode("_", $rawdesc);
    $outdir = $descexpl[0] . "." . $build;

    return $outdir;
}

include(__DIR__ . "/../../inc/config.php");

$disableBugsnag = true;

// TODO: Filter this by type when needing to support non-named db2s
$dbcs = $pdo->query("SELECT id, filename FROM wow_rootfiles WHERE filename LIKE 'DBFilesClient/%.db2'")->fetchAll(PDO::FETCH_ASSOC);

if (empty($argv[2])) {
    $query = "SELECT " . $product . "_versions.cdnconfig, " . $product . "_versions.buildconfig, " . $product . "_buildconfig.description, " . $product . "_buildconfig.root_cdn FROM " . $product . "_versions LEFT OUTER JOIN " . $product . "_buildconfig ON " . $product . "_versions.buildconfig=" . $product . "_buildconfig.hash ORDER BY " . $product . "_buildconfig.description DESC LIMIT 5";
} else {
    if($argv[2] == "fullrun" || $argv[2] == "true"){
        $query = "SELECT " . $product . "_versions.cdnconfig, " . $product . "_versions.buildconfig, " . $product . "_buildconfig.description, " . $product . "_buildconfig.root_cdn FROM " . $product . "_versions LEFT OUTER JOIN " . $product . "_buildconfig ON " . $product . "_versions.buildconfig=" . $product . "_buildconfig.hash ORDER BY " . $product . "_versions.ID DESC";
    }else{
        $query = "SELECT " . $product . "_versions.cdnconfig, " . $product . "_versions.buildconfig, " . $product . "_buildconfig.description, " . $product . "_buildconfig.root_cdn FROM " . $product . "_versions LEFT OUTER JOIN " . $product . "_buildconfig ON " . $product . "_versions.buildconfig=" . $product . "_buildconfig.hash WHERE " . $product . "_buildconfig.description LIKE '" . $argv[2] . "%' ORDER BY " . $product . "_versions.ID DESC";
    }
}

// Walk through versions
foreach ($pdo->query($query) as $row) {
    if (!empty($argv[2]) && ($argv[2] == "fullrun" || $argv[2] == "true")) {
        $rawdesc = str_replace("WOW-", "", $row['description']);
        $build = substr($rawdesc, 0, 5);
    }

    if(empty($row['root_cdn'])){
        echo "[DB2 export] Root not known for build " . $row['description'] . ", skipping..\n";
        continue;
    }

    if(empty($row['cdnconfig'])){
        echo "[DB2 export] CDN config not known for build " . $row['description'] . ", skipping..\n";
        continue;
    }
    
    $buildNeedsExtract = false;

    $outdir = makeOutDir($row['description']);
    $extractList = "/tmp/dbcs-" . $row['buildconfig'] . ".txt";

    // Open file handle to extraction list
    $fhandle = fopen($extractList, "w");

    if(!empty($argv[3])){
        // Use input from cmdline 
        $missingFiles = [];
        fwrite($fhandle, $argv[3] . "\n");
        fclose($fhandle);
        $buildNeedsExtract = true;
    }else{
        // Retrieve list of available filedatads in this build
        echo "[DB2 export] Requesting filedataids for build " . $row['description'] . " (".$row['root_cdn'].")\n";
        $fdids = getFileDataIDs($row['root_cdn'], $product);
        if (empty($fdids)) {
            echo "[DB2 export] !!! Error retrieving filedataids for build " . $row['description'] . "\n";
            fclose($fhandle);
            unlink($extractList);
            continue;
        }

        echo "[DB2 export] Got " . count($fdids) . " filedataids for build " . $row['description'] . "\n";

        $missingFiles = [];
        foreach ($dbcs as $dbc) {
            // Check if DBC is available in this build
            if (in_array($dbc['id'], $fdids) && !file_exists("/home/wow/dbcs/" . $outdir . "/" . $dbc['filename'])) {
                // Write to extraction list
                fwrite($fhandle, $dbc['id'] . ";" . $dbc['filename'] . "\n");
                $missingFiles[] = $dbc['filename'];
                $buildNeedsExtract = true;
            } 
        }

        fclose($fhandle);
    }

    if ($buildNeedsExtract) {
        echo "[DB2 export] Build " . $row['description'] . " needs an update! Missing files:\n";
        foreach ($missingFiles as $missingFile) {
            echo "	" . $missingFile . "\n";
        }

        echo "[DB2 export] Exporting DBCs to " . $outdir . "\n";
        $output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll extractfilesbyfdidlist " . $row['buildconfig'] . " " . $row['cdnconfig'] . " /home/wow/dbcs/" . $outdir . "/ " . escapeshellarg($extractList) . " " . $product);
    }

    // Clean up extract list
    unlink($extractList);
}
