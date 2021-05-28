<?php
require_once("/var/www/wow.tools/inc/config.php");

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

if (empty($argv[1])) {
    die("Missing listfile argument (and optional overwrite argument)");
}

$listfile = $argv[1];

if (!file_exists($listfile)) {
    die("File does not exist!");
}

$files = explode("\n", file_get_contents($listfile));
$kfq = $pdo->query("SELECT id, filename FROM wow_rootfiles WHERE verified = 0")->fetchAll();
foreach ($kfq as $row) {
    $knownfiles[$row['id']] = $row['filename'];
}

$cq = $pdo->prepare("SELECT id, filename FROM wow_rootfiles WHERE filename = ? OR id = ?");

$log = [];
$suggestedfiles = [];

foreach ($files as $file) {
    if (empty($file)) {
        continue;
    }

    $split = explode(";", $file);
    $fdid = (int)$split[0];
    if (count($split) != 2) {
        $log[] = "An error occurred parsing a line, please check that the format is valid: fdid;filename.";
        continue;
    }

    $fname = strtolower(str_replace("\\", "/", trim($split[1])));

    if (strlen($fname) > 255) {
        $log[] = "WARNING! Filename " . $fname . " exceeds max filename length of 255, skipping..";
        continue;
    }

    if (array_key_exists($fdid, $knownfiles)) {
        if (empty($knownfiles[$fdid])) {
            // No filename currently set
            $cq->execute([$fname, $fdid]);
            $cr = $cq->fetch(PDO::FETCH_ASSOC);
            if (empty($cr)) {
                $log[] = "Adding " . $fname . " to " . $fdid;
                $suggestedfiles[$fdid] = $fname;
            } else {
                if (!empty($cr['filename'])) {
                    $log[] = "WARNING! Submitted fileDataID " . $fdid . " or filename " . $fname . " conflicts with FileDataID " . $cr['id'] . " or filename " . $cr['filename'] . ", skipping!";
                } else {
                    $log[] = "Adding " . $fname . " to " . $fdid;
                    $suggestedfiles[$fdid] = $fname;
                }
            }
        } elseif ($knownfiles[$fdid] != $fname) {
            // Submitted filename differs from current filename
            if (!isset($_POST['onlynew'])) {
                $log[] = "Overriding " . $knownfiles[$fdid] . " (" . $fdid . ") with " . $fname . "";
                $suggestedfiles[$fdid] = $fname;
            } else {
                $log[] = "Would usually overriding " . $knownfiles[$fdid] . " (" . $fdid . ") with " . $fname . ", but checkbox to skip known files is set";
            }
        } else {
            // Submitted filename is the same
            $log[] = "Skipping " . $fname . ", same as " . $knownfiles[$fdid] . " (" . $fdid . ")";
        }
    } else {
        // File does not exist
        $cq->execute([$fname, $fdid]);
        $cr = $cq->fetch(PDO::FETCH_ASSOC);
        if (empty($cr)) {
            $log[] = "Adding entirely new file " . $fname . " to new filedataid " . $fdid;
            $suggestedfiles[$fdid] = $fname;
        } else {
            if (!empty($cr['filename'])) {
                $log[] = "WARNING! Submitted fileDataID " . $fdid . " or filename " . $fname . " conflicts with FileDataID " . $cr['id'] . " or filename " . $cr['filename'] . ", skipping!";
            } else {
                $log[] = "Adding " . $fname . " to " . $fdid;
                $suggestedfiles[$fdid] = $fname;
            }
        }
    }
}

print_r($log);