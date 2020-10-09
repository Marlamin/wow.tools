<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}
require_once(__DIR__ . "/../../inc/config.php");

$processedMD5s = $pdo->query("SELECT DISTINCT(md5) FROM wow_hotfixes_parsed")->fetchAll(PDO::FETCH_COLUMN);
$insertMD5 = $pdo->prepare("INSERT IGNORE INTO wow_hotfixes_parsed (md5) VALUES (?)");
$knownPushIDs = $pdo->query("SELECT DISTINCT pushID FROM wow_hotfixes")->fetchAll(PDO::FETCH_COLUMN);
$knownKeys = $pdo->query("SELECT keyname FROM wow_tactkey")->fetchAll(PDO::FETCH_COLUMN);
$keyInsert = $pdo->prepare("INSERT IGNORE INTO wow_tactkey (keyname, keybytes) VALUES (?, ?)");
$files = glob('/home/wow/dbcdumphost/caches/*.bin');

if (!empty($argv[1])) {
    echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Only parsing one cache file: " . $argv[1] . "\n";
    $files = array($argv[1]);
}

$filesToProcess = [];

foreach ($files as $file) {
    if (empty($argv[1]) && filemtime($file) < strtotime("-2 hours")) {
        continue;
    }

    $md5 = md5_file($file);
    if (in_array($md5, $processedMD5s)) {
        // echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Skipping already processed DBCache " . $file . " (" . $md5 . ")\n";
        continue;
    }

    $filesToProcess[] = $file;
}

if (count($filesToProcess) > 0) {
    $knownCachedEntries = $pdo->query("SELECT CONCAT(tableName, \".\", recordID, \".\", md5) FROM wow_cachedentries")->fetchAll(PDO::FETCH_COLUMN);
}

foreach ($filesToProcess as $file) {
    $md5 = md5_file($file);
    if (in_array($md5, $processedMD5s)) {
        continue;
    }

    echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Reading " . $file . "\n";
    $output = shell_exec("cd /home/wow/hotfixdumper; dotnet WoWTools.HotfixDumper.dll " . escapeshellarg($file) . " " . escapeshellarg("/home/wow/dbd/WoWDBDefs/definitions"));
    $json = json_decode($output, true);

    if ($json['build'] < 32593) {
        continue;
    }

    $insertQ = $pdo->prepare("INSERT IGNORE INTO wow_hotfixes (pushID, recordID, tableName, isValid, build, cachename) VALUES (?, ?, ?, ?, ?, ?)");
    $insertCachedEntryQ = $pdo->prepare("INSERT IGNORE INTO wow_cachedentries (recordID, tableName, md5, build, cachename) VALUES (?, ?, ?, ?, ?)");
    $messages = [];
    foreach ($json['entries'] as $entry) {
        if ($entry['pushID'] > 999999 && !($entry['pushID'] & 0x40000000)) {
            $messages[] = "Got hotfix with a very high push ID: " . $entry['pushID'] . ", Table " . $entry['tableName'] . " ID " . $entry['recordID'] . " from build " . $json['build'] . ", ignoring!!!\n\n@" . $file . "\n\n";
            continue;
        }

        if ($entry['pushID'] != "-1" && in_array($entry['pushID'], $knownPushIDs)) {
            continue;
        }

        if ($entry['pushID'] != "-1") {
            // With Push ID
            $insertQ->execute([$entry['pushID'], $entry['recordID'], $entry['tableName'], $entry['isValid'], $json['build'], basename($file)]);
            if ($insertQ->rowCount() == 1) {
                echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Inserted new hotfix: Push ID " . $entry['pushID'] . ", Table " . $entry['tableName'] . " ID " . $entry['recordID'] . " from build " . $json['build'] . "\n";

                if (!array_key_exists($entry['pushID'], $messages)) {
                    $messages[$entry['pushID']] = "Push ID " . $entry['pushID'] . " for build " . $json['build'] . "\nhttps://wow.tools/dbc/hotfixes.php?search=pushid:" . $entry['pushID'] . "\n";
                }

                $messages[$entry['pushID']] .= $entry['tableName'] . " ID " . $entry['recordID'] . " (" . $entry['isValid'] . ")\n";
            }
        } else {
            // Without Push ID
            if ($entry['isValid'] == 1) {
                if (in_array($entry['tableName'] . "." . $entry['recordID'] . "." . $entry['dataMD5'], $knownCachedEntries)) {
                    continue;
                }

                $insertCachedEntryQ->execute([$entry['recordID'], $entry['tableName'], $entry['dataMD5'], $json['build'], basename($file)]);
                if ($insertCachedEntryQ->rowCount() == 1) {
                    echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Inserted new cached entry, Table " . $entry['tableName'] . " ID " . $entry['recordID'] . " from build " . $json['build'] . " with MD5 " . $entry['dataMD5'] . " \n";

                    if (!array_key_exists(filemtime($file), $messages)) {
                        $messages[filemtime($file)] = "Discovered new DBCache entries for build " . $json['build'] . "\n";
                    }

                    $messages[filemtime($file)] .= $entry['tableName'] . " ID " . $entry['recordID'] . "\n";
                }
            }
        }
    }

    foreach ($messages as $message) {
        telegramSendMessage($message);
    }

    $foundNewKeys = false;
    $output2 = shell_exec("cd /home/wow/hotfixdumper; dotnet WoWTools.HotfixDumper.dll " . escapeshellarg($file) . " " . escapeshellarg("/home/wow/dbd/WoWDBDefs/definitions") . " true");
    foreach (explode("\n", $output2) as $line) {
        if (empty($line)) {
            continue;
        }

        $expl = explode(" ", trim($line));

        if (strlen($expl[0]) != 16 || strlen($expl[1]) != 32) {
            if ($expl[3] == "TactKey" && $expl[9] == "BroadcastText") {
                continue;
            }
            echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Read line that is not a key: " . $line . "\n";
            continue;
        }

        if (!in_array($expl[0], $knownKeys)) {
            echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Found new key! " . $expl[0] . " " . $expl[1] . "\n";
            $knownKeys[] = $expl[0];
            $keyInsert->execute([$expl[0], $expl[1]]);
            $foundNewKeys = true;
        }
    }

    if ($foundNewKeys) {
        file_get_contents("https://wow.tools/casc/reloadkeys?t=" . strtotime("now"));
        echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Reloaded TACT keys\n";
    }

    if (!in_array($md5, $processedMD5s)) {
        $insertMD5->execute([$md5]);
        $processedMD5s[] = $md5;
        echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Inserted " . $md5 . " as processed cache\n";
    }
}
