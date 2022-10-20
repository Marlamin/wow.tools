<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

function getFileDataIDs($buildconfig, $cdnconfig)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://wow.tools/casc/root/fdids?buildconfig=" . $buildconfig . "&cdnconfig=" . $cdnconfig);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    if (!$data) {
        echo "cURL fail: " . print_r(curl_error($ch)) . "\n";
    }
    curl_close($ch);
    if ($data == "") {
        return false;
    } else {
        return json_decode($data);
    }
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

if (empty($argv[1])) {
    $query = "SELECT wow_versions.cdnconfig, wow_versions.buildconfig, wow_buildconfig.description FROM wow_versions LEFT OUTER JOIN wow_buildconfig ON wow_versions.buildconfig=wow_buildconfig.hash ORDER BY wow_buildconfig.description DESC LIMIT 5";
} else {
    if($argv[1] == "fullrun" || $argv[1] == "true"){
        $query = "SELECT wow_versions.cdnconfig, wow_versions.buildconfig, wow_buildconfig.description FROM wow_versions LEFT OUTER JOIN wow_buildconfig ON wow_versions.buildconfig=wow_buildconfig.hash ORDER BY wow_versions.ID DESC";
    }else{
        $query = "SELECT wow_versions.cdnconfig, wow_versions.buildconfig, wow_buildconfig.description FROM wow_versions LEFT OUTER JOIN wow_buildconfig ON wow_versions.buildconfig=wow_buildconfig.hash WHERE wow_buildconfig.description LIKE '" . $argv[1] . "%' ORDER BY wow_versions.ID DESC";
    }
}

// Walk through versions
foreach ($pdo->query($query) as $row) {
    if (!empty($argv[1]) && ($argv[1] == "fullrun" || $argv[1] == "true")) {
        $rawdesc = str_replace("WOW-", "", $row['description']);
        $build = substr($rawdesc, 0, 5);
        if ($build != 40725) {
            continue;
        }
    }

    if(empty($row['cdnconfig'])){
        echo "[DB2 export] CDN config not known for build " . $row['description'] . ", skipping..";
        continue;
    }
    
    $buildNeedsExtract = false;

    $outdir = makeOutDir($row['description']);
    $extractList = "/tmp/dbcs-" . $row['buildconfig'] . ".txt";

    // Open file handle to extraction list
    $fhandle = fopen($extractList, "w");

    if(!empty($argv[2])){
        // Use input from cmdline 
        $missingFiles = [];
        fwrite($fhandle, $argv[2] . "\n");
        fclose($fhandle);
        $buildNeedsExtract = true;
    }else{
        // Retrieve list of available filedatads in this build
        echo "[DB2 export] Requesting filedataids for build " . $row['description'] . "\n";
        $fdids = getFileDataIDs($row['buildconfig'], $row['cdnconfig']);
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
        $output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll extractfilesbyfdidlist " . $row['buildconfig'] . " " . $row['cdnconfig'] . " /home/wow/dbcs/" . $outdir . "/ " . escapeshellarg($extractList));
    }

    // Clean up extract list
    unlink($extractList);
}
