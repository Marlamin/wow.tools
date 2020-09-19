<?php

$disableBugsnag = true;
include("../../inc/config.php");

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

function getDBC($name, $build)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:5000/api/export/?name=" . urlencode($name) . "&build=" . urlencode($build) . "&useHotfixes=true&newLinesInStrings=false");
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
        $rows = [];
        $expl = explode("\n", $data);
        for ($i = 0; $i < count($expl); $i++) {
            $parsed = str_getcsv($expl[$i]);
            if ($i == 0) {
                $headers = $parsed;
                continue;
            }

            foreach ($parsed as $key => $value) {
                if (!isset($value)) {
                    continue;
                }
                if (array_key_exists($key, $headers)) {
                    $rows[$i - 1][$headers[$key]] = $value;
                }
            }
        }
        return $rows;
    }
}

$build = "9.0.2.35854";

$unkMP3s = [];
$knownMP3s = $pdo->query("SELECT ID FROM wow_rootfiles WHERE type = 'mp3' AND filename IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
$slMP3s = $pdo->query("SELECT filename FROM wow_rootfiles WHERE filename LIKE 'sound/music/shadowlands/%'")->fetchAll(PDO::FETCH_COLUMN);

$manifestMP3 = getDBC("ManifestMP3", $build);
foreach ($manifestMP3 as $manMP3) {
    if (!in_array($manMP3['ID'], $knownMP3s)) {
        $unkMP3s[] = $manMP3['ID'];
    }
}

if (count($unkMP3s) == 0) {
    return;
}

$soundKitEntry = getDBC("SoundKitEntry", $build);
$soundKitMap = [];
foreach ($soundKitEntry as $entry) {
    $soundKitMap[$entry['SoundKitID']][] = $entry['FileDataID'];
}

$results = [];
$zoneMusic = getDBC("ZoneMusic", $build);
foreach ($zoneMusic as $zmEntry) {
    if (!array_key_exists("Sounds[0]", $zmEntry)) {
        continue;
    }

    if (array_key_exists($zmEntry['Sounds[0]'], $soundKitMap)) {
        foreach ($unkMP3s as $mp3) {
            if (in_array($mp3, $soundKitMap[$zmEntry['Sounds[0]']])) {
                $results[$mp3] = $zmEntry['SetName'];
            }
        }
    }

    if (array_key_exists($zmEntry['Sounds[1]'], $soundKitMap)) {
        foreach ($unkMP3s as $mp3) {
            if (in_array($mp3, $soundKitMap[$zmEntry['Sounds[1]']])) {
                $results[$mp3] = $zmEntry['SetName'];
            }
        }
    }
}

$tokenCount = [];
foreach ($results as $filedataid => $name) {
    if (!array_key_exists($name, $tokenCount)) {
        $tokenCount[$name] = 1;
    }

    $filename = "sound/music/shadowlands/mus_" . str_replace("zone_901_", "", strtolower($name)) . "_" . str_pad($tokenCount[$name], 2, '0', STR_PAD_LEFT) . ".mp3";
    while (in_array($filename, $slMP3s)) {
        echo $filename . " already exists, incrementing token count!\n";
        $tokenCount[$name]++;
        $filename = "sound/music/shadowlands/mus_" . str_replace("zone_901_", "", strtolower($name)) . "_" . str_pad($tokenCount[$name], 2, '0', STR_PAD_LEFT) . ".mp3";
    }

    echo $filedataid . ";" . $filename . "\n";
    $slMP3s[] = $filename;

    $tokenCount[$name]++;
}
