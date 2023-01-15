<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}
include(__DIR__ . "/../../inc/config.php");

if(empty($argv[1])){
    $product = "wow";
}else{
    $product = $argv[1];
}

if(substr($product, 0, 3) != "wow")
    die("Invalid product");

$res = $pdo->query(
    "SELECT
    " . $product . "_versions.cdnconfig,
    " . $product . "_versions.buildconfig,
    " . $product . "_buildconfig.id as bdid,
    " . $product . "_buildconfig.description,
    " . $product . "_buildconfig.install_cdn,
    " . $product . "_buildconfig.product,
    " . $product . "_buildconfig.builton
    FROM " . $product . "_versions
    LEFT OUTER JOIN " . $product . "_buildconfig ON " . $product . "_versions.buildconfig=" . $product . "_buildconfig.hash
    ORDER BY " . $product . "_buildconfig.description
    "
);

if (!file_exists("/home/wow/exes")) {
    mkdir("/home/wow/exes");
}

while ($row = $res->fetch()) {

    $buildInfo = parseBuildName($row['description']);
    if($buildInfo['build'] < 40000)
        continue;

    $targets = [
        // Mainline
        "Wow.exe", "WowT.exe", "WowB.exe", 
        // Classic
        "WowClassic.exe", "WowClassicT.exe", "WowClassicB.exe", 
        // Old 64-bit specific builds
        "Wow-64.exe", "WowT-64.exe", "WowB-64.exe"    
    ];
    
    $filename = "/home/wow/exes/" . $row['description'] . "-" . $row['buildconfig'] . ".exe";

    $needsExtract = !file_exists($filename);

    if($needsExtract){
        // Check if one of the older variants exists 
        foreach($targets as $target){
            $targetEXE = str_replace(".exe", "-" . $target, $filename);
            if (file_exists($targetEXE)) {
                $needsExtract = false;
                break;
            }
        }
    }

    // Only extract file if it does not exist
    if ($needsExtract) {
        echo "[EXE dump] File " . $filename . " does not exist or is empty.\n";

        // Remove if you magically get 18179 archives complete again
        if ($row['buildconfig'] == "cc7af6d878238d1c78d828db5146d343" || $row['buildconfig'] == "4c8a087dd7415e8eaa25ee870cb970b3" || $row['buildconfig'] == "887532e2e1a00e140d7aee051f37ca95" || $row['buildconfig'] == "479bd6c342bd6b7f363cb9bf95d9b9b2") {
            continue;
        }

        $output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll dumpinstall " . $product . " " . $row['install_cdn']);
        foreach (explode("\n", $output) as $line) {
            if (in_array(explode(" ", $line)[0], $targets)){
                if (empty(trim($line))) {
                    continue;
                }
                $split1 = explode(" (", $line);
                $split2 = explode(", ", $split1[1]);
                $md5 = str_replace("md5: ", "", $split2[1]);
                
                echo "[EXE dump] " . $row['description'] . ": " . $row['buildconfig'] . "\" \"" . $row['cdnconfig'] . "\" \"" . $md5 . "\" \"" . $filename . "\"\n";
                $output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll extractfilebycontenthash " . $product . " \"".$row['buildconfig']."\" \"".$row['cdnconfig']."\" \"".$md5."\" \"".$filename."\"");          

                if (file_exists($filename)) {
                    if (filesize($filename) == 0) {
                        echo "[EXE dump] Dumped file is 0 bytes, deleting...\n";
                        unlink($filename);
                    } else {
                        echo "[EXE dump] File exists, adding build time to DB\n";
                        shell_exec("chmod 777 " . $filename);
                        $output = shell_exec("/usr/bin/strings " . $filename . " | grep \"Exe Built:\"");
                        $output = str_replace("Exe Built: ", "", $output);
                        $date = date('Y-m-d H:i:s', strtotime($output)) . "\n";
                        $uq = $pdo->prepare("UPDATE " . $product . "_buildconfig SET builton = ? WHERE hash = ?");
                        $uq->execute([$date, $row['buildconfig']]);
                    }
                } else {
                    echo "[EXE dump] File " . $filename . " does not exist or is empty.\n";
                }
            }
        }
    }
}
