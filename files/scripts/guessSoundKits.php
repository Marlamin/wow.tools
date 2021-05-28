<?php

require_once("/var/www/wow.tools/inc/config.php");

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
                if (array_key_exists($key, $headers)) {
                    $rows[$i - 1][$headers[$key]] = $value;
                }
            }
        }
        return $rows;
    }
}

function listSoundKitsByType($type) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://wow.tools/dbc/api/find/SoundKit?col=SoundType&val=" . $type . "&build=9.0.2.37176");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    if (!$data) {
        echo "cURL fail: " . print_r(curl_error($ch)) . "\n";
    }
    curl_close($ch);

    return json_decode($data, true);
}

$unnamedFileInSkitQ = $pdo->prepare("SELECT wow_rootfiles.id, wow_rootfiles.`type` FROM wow_rootfiles INNER JOIN `wowdata`.soundkitentry ON `wowdata`.soundkitentry.id=wow_rootfiles.id AND `wowdata`.soundkitentry.entry = ? AND wow_rootfiles.filename IS NULL");

$build = "9.0.2.37176";

$soundKitEntryDB = getDBC("soundkitentry", $build);
$soundKitMap = [];
foreach ($soundKitEntryDB as $soundKitEntry) {
    if (empty($soundKitEntry['SoundKitID'])) {
        continue;
    }

    $soundKitMap[$soundKitEntry['SoundKitID']][] = $soundKitEntry['FileDataID'];
}

$zoneAmbiences = listSoundKitsByType(50);

foreach ($zoneAmbiences as $zoneAmbience) {
    echo $zoneAmbience['ID'] . "\n";

    if ($zoneAmbience['ID'] < 155000) {
        continue;
    }

    $unnamedFileInSkitQ->execute([$zoneAmbience['ID']]);
    $unnameds = $unnamedFileInSkitQ->fetchAll(PDO::FETCH_ASSOC);
    if (count($unnameds) > 0){
        print_r($unnameds);
    }
}