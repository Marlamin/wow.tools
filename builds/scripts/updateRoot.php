<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

include(__DIR__ . "/../../inc/config.php");
if (empty($argv[1])) {
    // Full run
    $q = $pdo->query("SELECT root_cdn, description FROM wow_buildconfig WHERE processed = 0 GROUP BY `root_cdn` ORDER BY description ASC");
    $processedRootFiles = array();
    $roots = $q->fetchAll();
    foreach ($roots as $row) {
        if (in_array($row['root_cdn'], $processedRootFiles)) {
            continue;
        }

        processRoot($row['root_cdn'], $row['description']);
        $processedRootFiles[] = $row['root_cdn'];
        $pq = $pdo->prepare("UPDATE wow_buildconfig SET processed = 1 WHERE root_cdn = :root");
        $pq->execute([$row['root_cdn']]);
        $memcached->delete("files.total");
    }
} else {
    $q = $pdo->prepare("SELECT description FROM wow_buildconfig WHERE root_cdn = :root");
    $q->bindParam(":root", $argv[1]);
    $q->execute();
    $row = $q->fetch();
    processRoot($argv[1], $row['description']);
}

function processRoot($root, $build)
{
    global $pdo;
    if (empty(trim($root))) {
        echo "No root known for this build! Skipping..";
        return;
    }

    echo "Processing " . $build . "\n";
    if (!file_exists("/home/wow/buildbackup/manifests")) {
        mkdir("/home/wow/buildbackup/manifests");
    }

    if (!file_exists("/home/wow/buildbackup/manifests/" . $root . ".txt")) {
        echo "	Dumping manifest..";
        $output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet /home/wow/buildbackup/BuildBackup.dll dumproot2 " . $root . " > /home/wow/buildbackup/manifests/" . $root . ".txt");
        echo "..done!\n";
    } else {
        echo "	Manifest already dumped, skipping..\n";
    }

    echo "	Writing rootfiles..";
    $q = $pdo->exec("LOAD DATA LOCAL INFILE '/home/wow/buildbackup/manifests/" . $root . ".txt' INTO TABLE wow_rootfiles
        FIELDS TERMINATED BY ';' LINES TERMINATED BY '\n'
        (@filename, @lookup, @filedataid, @contenthash) SET id=@filedataid, lookup=@lookup, filename=@filename
    ");
    echo "..done!\n";
    $pdo->query("UPDATE wow_rootfiles SET filename = NULL WHERE filename = ' '");
    echo "	Writing content hashes..";
    $pdo->exec("LOAD DATA LOCAL INFILE '/home/wow/buildbackup/manifests/" . $root . ".txt' INTO TABLE wow_rootfiles_chashes
        FIELDS TERMINATED BY ';' LINES TERMINATED BY '\n'
        (@filename, @lookup, @filedataid, @contenthash) SET filedataid=@filedataid, root_cdn='" . $root . "', contenthash=@contenthash
    ");
    echo "..done!\n";
}
