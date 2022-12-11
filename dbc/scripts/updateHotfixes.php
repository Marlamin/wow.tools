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
$buildLookup = $pdo->query("SELECT build, version FROM wow_builds")->fetchAll(PDO::FETCH_KEY_PAIR);

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

$pushIDIcon[0] = "🗑️";
$pushIDIcon[1] = "✏️";
$pushIDIcon[2] = "🗑️";
$pushIDIcon[3] = "❌";
$pushIDIcon[4] = "🔒";
if (count($filesToProcess) > 0) {
    $knownCachedEntries = $pdo->query("SELECT CONCAT(tableName, \".\", recordID, \".\", md5) FROM wow_cachedentries")->fetchAll(PDO::FETCH_COLUMN);
}

foreach ($filesToProcess as $file) {
    $md5 = md5_file($file);
    if (in_array($md5, $processedMD5s)) {
        echo "Skipping " . $md5 . "\n";
        continue;
    }

    echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Reading " . $file . "\n";
    $output = shell_exec("cd /home/wow/hotfixdumper; dotnet WoWTools.HotfixDumper.dll " . escapeshellarg($file) . " " . escapeshellarg("/home/wow/dbd/WoWDBDefs/definitions"));
    echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Decoding output..\n";
    $json = json_decode($output, true);

    if(empty($json)){
        echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Empty JSON, likely encountered an error during parsing!\n";
        continue;
    }

    if ($json['build'] < 32593) {
        continue;
    }
    echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Processing entries..\n";
    
    $insertQ = $pdo->prepare("INSERT IGNORE INTO wow_hotfixes (pushID, recordID, tableName, isValid, build, cachename) VALUES (?, ?, ?, ?, ?, ?)");
    $insertCachedEntryQ = $pdo->prepare("INSERT IGNORE INTO wow_cachedentries (recordID, tableName, md5, build, cachename) VALUES (?, ?, ?, ?, ?)");
    $messages = [];
    $entriesProcessed = 0;
    foreach ($json['entries'] as $entry) {
        if ($entry['pushID'] > 999999 || $entry['pushID'] == 12345) {
            // $messages[] = "Got hotfix with a very high push ID: " . $entry['pushID'] . ", Table " . $entry['tableName'] . " ID " . $entry['recordID'] . " from build " . $json['build'] . ", ignoring!!!\n\n@" . $file . "\n\n";
            continue;
        }

        if ($entry['pushID'] != "-1" && in_array($entry['pushID'], $knownPushIDs)) {
            continue;
        }

        if($entriesProcessed > 0 && $entriesProcessed % 2500 == 0){
            echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Still processing entries, currently at " . $entriesProcessed . ", current record: Table " . $entry['tableName'] . ", ID " . $entry['recordID'] . ", IsValid " . $entry['isValid'] .", MD5: (".$entry['dataMD5'].")\n";
            // Useless query to keep the connection alive
            $pdo->query("SELECT pushID FROM wow_hotfixes LIMIT 1");
        }
        
        $entriesProcessed++;

        if ($entry['pushID'] != "-1") {
            // With Push ID
            $insertQ->execute([$entry['pushID'], $entry['recordID'], $entry['tableName'], $entry['isValid'], $json['build'], basename($file)]);
            if ($insertQ->rowCount() == 1) {
                echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Inserted new hotfix: Push ID " . $entry['pushID'] . ", Table " . $entry['tableName'] . " ID " . $entry['recordID'] . " from build " . $json['build'] . "\n";

                if (!array_key_exists($entry['pushID'], $messages)) {
                    $messages[$entry['pushID']] = "Push ID **[" . $entry['pushID'] . "](https://wow.tools/dbc/hotfixes.php#search=pushid:" . $entry['pushID'] . ")** for build " . $json['build'] . "\n";
                }

                $messages[$entry['pushID']] .= $pushIDIcon[$entry['isValid']] . " " . $entry['tableName'] . " " . $entry['recordID'] . "\n";
            }
        } else {
            // Without Push ID
            if ($entry['isValid'] == 1) {
                if (in_array($entry['tableName'] . "." . $entry['recordID'] . "." . $entry['dataMD5'], $knownCachedEntries)) {
                    continue;
                }

                $insertCachedEntryQ->execute([$entry['recordID'], $entry['tableName'], $entry['dataMD5'], $json['build'], basename($file)]);
                if ($insertCachedEntryQ->rowCount() == 1) {
                    echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Inserted new cached entry, Table " . $entry['tableName'] . " " . $entry['recordID'] . " from build " . $json['build'] . " with MD5 " . $entry['dataMD5'] . " \n";

                    if (!array_key_exists(filemtime($file), $messages)) {
                        $messages[filemtime($file)] = "Discovered new cache entries for build " . $json['build'] . "\n";
                    }

                    if ($entry['tableName'] == "BroadcastText") {
                        $colIndex = 2;
                    } else {
                        $colIndex = 0;
                    }
                    
                    if (array_key_exists($json['build'], $buildLookup)) {
                        $link = "https://wow.tools/dbc/?dbc=" . $entry['tableName'] . "&build=" . $buildLookup[$json['build']] . "&hotfixes=true#page=1&colFilter[" . $colIndex . "]=exact:" . $entry['recordID'];
                    } else {
                        $link = "https://wow.tools/dbc/?dbc=" . $entry['tableName'] . "&hotfixes=true#page=1&colFilter[" . $colIndex . "]=exact:" . $entry['recordID'];
                    }

                    $messages[filemtime($file)] .= $entry['tableName'] . " [" . $entry['recordID'] . "](" . $link . ")\n";
                }
            }
        }
    }

    foreach ($messages as $message) {
        if (strlen($message) > 3000) {
            $splitLines = explode("\n", $message);
            telegramSendMessage($splitLines[0] . "\nMessage was too long, see  for full details.");
        } else {
            telegramSendMessage($message);
        }

        foreach ($discordHotfixes as $discordHotfix) {
            if (strlen($message) > 2000) {
                $dumpFile = "/var/www/wow.tools/pub/hotfixes/" . strtotime("now") . "." . md5($message) . ".txt";
                file_put_contents($dumpFile, $message);
                $splitLines = explode("\n", $message);
                discordSendMessage($splitLines[0] . "\nMessage was too long, see [this file](" . str_replace("/var/www/", "https://", $dumpFile) . ") for the full message.", $discordHotfix);
            } else {
                discordSendMessage($message, $discordHotfix);
            }
        }
    }

    echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Processing big one..\n";

    $foundNewKeys = false;
    $output2 = shell_exec("cd /home/wow/hotfixdumper; dotnet WoWTools.HotfixDumper.dll " . escapeshellarg($file) . " " . escapeshellarg("/home/wow/dbd/WoWDBDefs/definitions") . " true");
    if($output2 != null){
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
    }

    // if ($foundNewKeys) {
    //     file_get_contents("https://wow.tools/casc/reloadkeys?t=" . strtotime("now"));
    //     echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Reloaded TACT keys\n";
    // }

    if (!in_array($md5, $processedMD5s)) {
        $insertMD5->execute([$md5]);
        $processedMD5s[] = $md5;
        echo "[Hotfix updater] [" . date("Y-m-d H:i:s") . "] Inserted " . $md5 . " as processed cache\n";
    }
}
