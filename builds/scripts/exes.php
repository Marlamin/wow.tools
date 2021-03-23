<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}
include(__DIR__ . "/../../inc/config.php");

$res = $pdo->query(
    "SELECT
    wow_versions.cdnconfig,
    wow_versions.buildconfig,
    wow_buildconfig.id as bdid,
    wow_buildconfig.description,
    wow_buildconfig.install_cdn,
    wow_buildconfig.product,
    wow_buildconfig.builton
    FROM wow_versions
    LEFT OUTER JOIN wow_buildconfig ON wow_versions.buildconfig=wow_buildconfig.hash
    ORDER BY wow_buildconfig.description
    "
);

while ($row = $res->fetch()) {
    if ($row['bdid'] > 1315) {
        switch ($row['product']) {
            case "wow":
            case "wowlivetest":
                $target = "Wow.exe";
                break;
            case "wow_classic":
                $target = "WowClassic.exe";
                break;
            case "wow_classic_ptr":
                $target = "WowClassicT.exe";
                break;
            case "wowt":
                $target = "WowT.exe";
                break;
            case "wow_beta":
            case "wowz":
            case "wowe1":
                $target = "WowB.exe";
                break;
            case "wow_classic_beta":
                $target = "WowClassicB.exe";
                break;
        }
    } else {
        switch ($row['product']) {
            case "wow":
                $target = "Wow-64.exe";
                break;
            case "wowt":
                $target = "WowT-64.exe";
                break;
            case "wow_beta":
            case "wowz":
            case "wow_classic_beta":
                $target = "WowB-64.exe";
                break;
        }
    }
    
    if (empty($target)) {
        echo "Unable to find target " . $target . "\n";
        die();
    }
    
    $filename = "/home/wow/exes/" . $row['description'] . "-" . $row['buildconfig'] . "-" . $target;
    
    // Only extract file if it does not exist
    if (!file_exists($filename)) {
        echo "[EXE dump] File " . $filename . " does not exist or is empty.\n";

        // Remove if you magically get 18179 archives complete again
        if ($row['buildconfig'] == "cc7af6d878238d1c78d828db5146d343") {
            continue;
        }

        $output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll dumpinstall wow " . $row['install_cdn']);
        foreach (explode("\n", $output) as $line) {
            if (substr($line, 0, strlen($target)) == $target) {
                if (empty(trim($line))) {
                    continue;
                }
                $split1 = explode(" (", $line);
                $split2 = explode(", ", $split1[1]);
                $md5 = str_replace("md5: ", "", $split2[1]);
                
                echo "[EXE dump] " . $row['description'] . ": " . $row['buildconfig'] . "\" \"" . $row['cdnconfig'] . "\" \"" . $md5 . "\" \"" . $filename . "\"\n";
                $output = shell_exec("/usr/bin/wget -O \"" . $filename . "\" \"https://wow.tools/casc/file/chash?contenthash=" . $md5 . "&buildconfig=" . $row['buildconfig'] . "&cdnconfig=" . $row['cdnconfig'] . "&filename=out.exe\"");
                
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
                        $uq = $pdo->prepare("UPDATE wow_buildconfig SET builton = ? WHERE hash = ?");
                        $uq->execute([$date, $row['buildconfig']]);
                    }
                } else {
                    echo "[EXE dump] File " . $filename . " does not exist or is empty.\n";
                }
            }
        }
    }
}
