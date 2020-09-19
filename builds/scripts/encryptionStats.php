<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

include(__DIR__ . "/../../inc/config.php");

function reverseLookup($bytes)
{
    $result = $bytes[14] . $bytes[15];
    $result .= $bytes[12] . $bytes[13];
    $result .= $bytes[10] . $bytes[11];
    $result .= $bytes[8] . $bytes[9];
    $result .= $bytes[6] . $bytes[7];
    $result .= $bytes[4] . $bytes[5];
    $result .= $bytes[2] . $bytes[3];
    $result .= $bytes[0] . $bytes[1];
    return $result;
}

$knownLookups = $pdo->query("SELECT DISTINCT(keyname) FROM wow_tactkey WHERE keybytes IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);

if (empty($argv[1])) {
    die("Need buildconfig hash");
}

$usedLookups = [];
$encryptedfiles = [];

$cmd = "cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll dumpencrypted wow " . escapeshellarg($argv[1]);
$output = shell_exec($cmd);
foreach (explode("\n", $output) as $line) {
    if (empty(trim($line))) {
        continue;
    }

    $line = explode(" ", trim($line));
    $filedataid = $line[0];
    foreach (explode(",", $line[1]) as $key) {
        $lookup = reverseLookup($key);
        $encryptedfiles[$filedataid][] = $lookup;

        if (!in_array($lookup, $usedLookups)) {
            $usedLookups[] = $lookup;
        }
    }
}

echo count($encryptedfiles) . " encrypted files\n";
echo count($usedLookups) . " keys are used\n";

$knownKeyCount = 0;
$unknownKeyCount = 0;
foreach ($usedLookups as $usedLookup) {
    if (in_array($usedLookup, $knownLookups)) {
        $knownKeyCount++;
    } else {
        $unknownKeyCount++;
    }
}

echo $knownKeyCount . " are known\n";
echo $unknownKeyCount . " are unknown\n";
