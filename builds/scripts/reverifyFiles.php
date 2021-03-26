<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

include(__DIR__ . "/../../inc/config.php");

$filesToVerify = $pdo->query("SELECT id, lookup, filename FROM wow_rootfiles WHERE verified = 0 AND lookup != '' AND filename IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
$filesToVerifyByLookup = [];


$tmpfname = tempnam("/tmp", "bnetlistfile");
$tmpfile = fopen($tmpfname, "w");

foreach ($filesToVerify as $fileToVerify) {
    $file = $fileToVerify['filename'];

    $filesToVerifyByLookup[$fileToVerify['lookup']] = $fileToVerify;

    if (empty(trim($file))) {
        continue;
    }

    if (strpos($file, " (in: ") !== false) {
        $expl = explode(" (in: ", $file);
        $file = $expl[0];
    }

    $file = trim($file);
    $file = strtolower(str_replace("\\", "/", $file));
    if (substr($file, 0, 2) == "by") {
        continue;
    }
    fwrite($tmpfile, $file . "\n");
}

fclose($tmpfile);

$cmd = "cd /home/wow/buildbackup; /usr/bin/dotnet /home/wow/buildbackup/BuildBackup.dll calchashlistfile " . escapeshellarg($tmpfname);
$output = explode("\n", shell_exec($cmd));

$addq = $pdo->prepare("UPDATE wow_rootfiles SET verified = 1 WHERE lookup = ? AND filename = ?");
$numadded = 0;

foreach ($output as $line) {
    $expl = explode(" = ", trim($line));
    if (count($expl) < 2) {
        continue;
    }

    if (array_key_exists($expl[1], $filesToVerifyByLookup)) {
        echo "[OK] " . $expl[0] . " (" . $expl[1] . ")\n";
        $validfiles[] = $expl[0];
        $addq->execute([$expl[1], $expl[0]]);
    } else {
        echo "[FAIL] " . $expl[0] . " (db lookup does not match with generated lookup for filename " . $expl[1] . ")\n";
        $invalidfiles[] = $expl[0];
    }
}

echo count($validfiles) . " valid files\n";
echo count($invalidfiles) . " invalid files\n";

unlink($tmpfname);
