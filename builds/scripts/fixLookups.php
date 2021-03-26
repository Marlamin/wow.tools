<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

function processRoot($root, $build, $buildid)
{
    global $pdo;
    if (empty(trim($root))) {
        echo "No root known for this build! Skipping..";
        return;
    }

    echo "Processing " . $build . "\n";
    if (!file_exists("/home/wow/buildbackup/manifests/" . $root . ".txt")) {
        echo "	Dumping manifest..";
        $output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet /home/wow/buildbackup/BuildBackup.dll dumproot2 " . $root . " > /home/wow/buildbackup/manifests/" . $root . ".txt");
        echo "..done!\n";
    } else {
        echo "	Manifest already dumped, skipping..\n";
    }

    echo "  Getting list of files without lookups..";
    $noLookupIDs = $pdo->query("SELECT id FROM wow_rootfiles JOIN wow_rootfiles_builds_erorus ON ORD(MID(wow_rootfiles_builds_erorus.files, 1 + FLOOR(wow_rootfiles.id / 8), 1)) & (1 << (wow_rootfiles.id % 8)) WHERE lookup = '' AND wow_rootfiles_builds_erorus.build = " . intval($buildid))->fetchAll(PDO::FETCH_COLUMN);
    echo "..done!\n";

    $fixLookupQ = $pdo->prepare("UPDATE wow_rootfiles SET lookup = ? WHERE id = ?");
    
    $handle = fopen("/home/wow/buildbackup/manifests/" . $root . ".txt", "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $expl = explode(";", $line);
            if (!empty($expl[1]) && in_array($expl[2], $noLookupIDs)) {
                echo "Fixed " . $expl[1] . " " . $expl[2] . "\n";
                $fixLookupQ->execute([$expl[1], $expl[2]]);
            }
        }

        fclose($handle);
    }
}

include(__DIR__ . "/../../inc/config.php");

if (empty($argv[1])) {
    // Full run
    $q = $pdo->query("SELECT id, root_cdn, description FROM wow_buildconfig GROUP BY `root_cdn` ORDER BY description DESC");
    $processedRootFiles = array();
    $roots = $q->fetchAll();
    foreach ($roots as $row) {
        if (in_array($row['root_cdn'], $processedRootFiles)) {
            continue;
        }

        processRoot($row['root_cdn'], $row['description'], $row['id']);
        $processedRootFiles[] = $row['root_cdn'];
    }
} else {
    $q = $pdo->prepare("SELECT id, description FROM wow_buildconfig WHERE root_cdn = :root");
    $q->bindParam(":root", $argv[1]);
    $q->execute();
    $row = $q->fetch();
    processRoot($argv[1], $row['description'], $row['id']);
}
