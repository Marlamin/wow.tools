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
                if (array_key_exists($key, $headers)) {
                    $rows[$i - 1][$headers[$key]] = $value;
                }
            }
        }
        return $rows;
    }
}


$build = "9.0.1.34902";
echo "Loading DBCs..";
$soundKitEntryDB = getDBC("soundkitentry", $build);
$soundKitMap = [];
foreach ($soundKitEntryDB as $soundKitEntry) {
    if (empty($soundKitEntry['SoundKitID'])) {
        continue;
    }

    $soundKitMap[$soundKitEntry['SoundKitID']][] = $soundKitEntry['FileDataID'];
}

$conversationLineDB = getDBC("conversationline", $build);
$conversationLineMap = [];
foreach ($conversationLineDB as $conversationLine) {
    if (empty($conversationLine['ID'])) {
        continue;
    }
    $conversationLineMap[$conversationLine['ID']] = $conversationLine;
}

$broadCastTextDB = getDBC("broadcasttext", $build);
$broadCastTextMap = [];
foreach ($broadCastTextDB as $broadCastText) {
    if (empty($broadCastText['ID'])) {
        continue;
    }
    $broadCastTextMap[$broadCastText['ID']] = $broadCastText;
}

echo "..done!\n";
echo "Caching unnamed ogg IDs..";
$unnamedOggs = $pdo->query("SELECT id FROM wow_rootfiles WHERE type = 'ogg' AND filename IS NULL")->fetchAll(PDO::FETCH_COLUMN);
echo "..done!\n";

$knownSkitToCreature[159506] = "lord_chamberlain";
$knownSkitToCreature[159508] = "lord_chamberlain";
$knownSkitToCreature[159509] = "lord_chamberlain";
$knownSkitToCreature[159521] = "lord_chamberlain";
$knownSkitToCreature[159523] = "lord_chamberlain";
$knownSkitToCreature[159524] = "lord_chamberlain";
$knownSkitToCreature[159495] = "rendle";
$knownSkitToCreature[159494] = "cudgelface";

$voFDIDByCreature = [];
$getFilenameQ = $pdo->prepare("SELECT filename FROM wow_rootfiles WHERE id = ?");

foreach ($conversationLineMap as $conversationLineID => $conversationLine) {
    $bText = [];
    $text = "";
    $skitID = 0;
    if (array_key_exists($conversationLine['BroadcastTextID'], $broadCastTextMap)) {
        $bText = $broadCastTextMap[$conversationLine['BroadcastTextID']];
        if (!empty($bText['Text_lang'])) {
            $text = $bText['Text_lang'];
            $skitID = $bText['SoundKitID[0]'];
        } elseif (!empty($bText['Text1_lang'])) {
            $text = $bText['Text1_lang'];
            $skitID = $bText['SoundKitID[1]'];
        }
    }

    if (!array_key_exists($skitID, $knownSkitToCreature)) {
        continue;
    }

    $currCreature = $knownSkitToCreature[$skitID];

    if (!empty($bText)) {
        echo $conversationLineID . " " . $conversationLine['BroadcastTextID'] . " " . $text . "\n";
    } else {
        echo $conversationLineID . " " . $conversationLine['BroadcastTextID'] . "\n";
    }

    if (!empty($skitID)) {
        $voFDIDByCreature[$currCreature][] = $soundKitMap[$skitID][0];
    }

    while ($conversationLine['NextConversationLineID'] != 0) {
        echo "\t NEXT: " . $conversationLine['NextConversationLineID'] . "\n";
        $conversationLine = $conversationLineMap[$conversationLine['NextConversationLineID']];
        if (array_key_exists($conversationLine['BroadcastTextID'], $broadCastTextMap)) {
            $bText = $broadCastTextMap[$conversationLine['BroadcastTextID']];
            if (!empty($bText['Text_lang'])) {
                $text = $bText['Text_lang'];
                $skitID = $bText['SoundKitID[0]'];
            } elseif (!empty($bText['Text1_lang'])) {
                $text = $bText['Text1_lang'];
                $skitID = $bText['SoundKitID[1]'];
            }
        }

        if (!empty($bText)) {
            echo $conversationLine['ID'] . " " . $conversationLine['BroadcastTextID'] . " " . $text . "\n";
        } else {
            echo $conversationLine['ID'] . " " . $conversationLine['BroadcastTextID'] . "\n";
        }

        if (!empty($skitID)) {
            $voFDIDByCreature[$currCreature][] = $soundKitMap[$skitID][0];
        }
    }
}

print_r($voFDIDByCreature);
