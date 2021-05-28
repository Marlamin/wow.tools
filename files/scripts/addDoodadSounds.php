<?php

require_once("/var/www/wow.tools/inc/config.php");

function downloadFile($params, $outfile)
{
    $fp = fopen($outfile, 'w+');
    $url = 'http://localhost:5005/casc/file' . $params;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $exec = curl_exec($ch);
    curl_close($ch);
    fclose($fp);

    if ($exec) {
        return true;
    } else {
        return false;
    }
}

$cascparams = "/fdid?buildconfig=5c696374a39c18dafa7e5feae9851df3&cdnconfig=b793919fae4b0c67f8dd8578d084e607&filename=temp.m2&filedataid=";
$q = $pdo->query("SELECT * FROM wow_rootfiles WHERE wow_rootfiles.filename LIKE \"world/expansion08/doodads%\" AND type = 'm2' ORDER BY id DESC");
$unnamedFileInSkitQ = $pdo->prepare("SELECT wow_rootfiles.id, wow_rootfiles.`type` FROM wow_rootfiles INNER JOIN `wowdata`.soundkitentry ON `wowdata`.soundkitentry.id=wow_rootfiles.id AND `wowdata`.soundkitentry.entry = ? AND wow_rootfiles.filename IS NULL ORDER BY wow_rootfiles.ID DESC");

$seenFiles = [];

while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
    $tempfile =  "/tmp/doodadname." . $row['id'];

    if (!file_exists($tempfile)) {
        echo "Downloading " . $row['id'] . ": " . $row['filename'] . "\n";
        downloadFile($cascparams . $row['id'], $tempfile);
    } else {
        continue;
    }

    $output = shell_exec("cd /home/wow/jsondump; /usr/bin/dotnet WoWJsonDumper.dll m2 " . escapeshellarg($tempfile) . " 2>&1");

    $json = json_decode($output, true);
    if (!$json) {
        echo "!!! Unable to parse JSON for " . $row['id'];
        continue;
    }

    $json = $json['model'];

    if (!empty($json['events']) && count($json['events']) > 0) {
        echo $row['id'] . ": " . $row['filename'] . "\n";
        foreach ($json['events'] as $event) {
            if ($event['identifier'] == '$DSL') {
                $soundType = "loop";
            } elseif ($event['identifier'] == '$DSO') {
                $soundType = "oneshot";
            } else {
                continue;
            }

            echo "SoundKit ID: " . $event['data'] . " is of type " . $soundType . "\n";
            $unnamedFileInSkitQ->execute([$event['data']]);
            $unnamedFiles = $unnamedFileInSkitQ->fetchAll(PDO::FETCH_ASSOC);

            if (count($unnamedFiles) > 0) {
                foreach ($unnamedFiles as $unnamedFile) {
                    if (in_array($unnamedFile['id'], $seenFiles)) {
                        continue;
                    }

                    if ($unnamedFile['type'] != "ogg") {
                        echo "Warning, unnamed file " . $unnamedFile['id'] . " is not a sound!\n";
                        continue;
                    }

                    echo "\tFound unnamed file: " . $unnamedFile['id'] . "\n";
                    echo "\tGenerated filename: " . $unnamedFile['id'] . ";sound/doodad/go_" . basename(str_replace(".m2", "", $row['filename'])) . "_" . $soundType . "_" . $unnamedFile['id'] . ".ogg\n";
                    $seenFiles[] = $unnamedFile['id'];
                }

                // Hackfixy way of forcing files that have these to reprocess
                if (file_exists($tempfile)) {
                    unlink($tempfile);
                }
            }
        }
    }
}
