<?php

include("../../inc/config.php");

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

if (!empty($argv[1])) {
    // Specific build
    echo "Adding file sizes for " . $argv[1] . "\n";
    processBuildSizes($argv[1]);
} else {
    // Full run
    $bq = $pdo->prepare("SELECT hash, description FROM wow_buildconfig WHERE root_cdn = ? LIMIT 1");
    foreach ($pdo->query("SELECT DISTINCT(root_cdn) as root_cdn FROM wow_rootfiles_chashes WHERE contenthash NOT IN (SELECT contenthash FROM wow_rootfiles_sizes)") as $res) {
        // These 2 early 6.0.1 have a discrepancy between encoding/root, ignore :()
        if ($res['root_cdn'] == "86f801aef9832aefcaba3dc9f29aa74d" || $res['root_cdn'] == "16c46bfac3a322fb741424980457d1d3") {
            continue;
        }

        $bq->execute([$res['root_cdn']]);
        $row = $bq->fetch();
        echo "Adding file sizes for " . $row['description'] . "\n";
        processBuildSizes($row['hash']);
    }
}

function processBuildSizes($hash)
{
    global $pdo;

    $tempname = tempnam("/tmp", "SIZES");

    $output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll dumpsizes wow " . escapeshellarg($hash) . " > " . escapeshellarg($tempname));

    $pdo->exec("
        LOAD DATA LOCAL INFILE '" . $tempname . "'
        INTO TABLE `wow_rootfiles_sizes`
        FIELDS TERMINATED BY ' ' ESCAPED BY '\b'
        LINES TERMINATED BY '\n'
        (contenthash, size)
    ");

    unlink($tempname);
}
