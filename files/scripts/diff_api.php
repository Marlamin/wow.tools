<?php
require_once("../../inc/config.php");

if (empty($_GET['from']) || empty($_GET['to']) || empty($_GET['filedataid'])) {
    die("Not enough information!");
}

$from = getVersionByBuildConfigHash($_GET['from'], "wow");
$to = getVersionByBuildConfigHash($_GET['to'], "wow");
$fileDataID = $_GET['filedataid'];

$query = $pdo->prepare("SELECT id, filename, type FROM wow_rootfiles WHERE id = :id");
$query->bindParam(":id", $_GET['filedataid'], PDO::PARAM_INT);
$query->execute();
$row = $query->fetch();

if (empty($row)) {
    die("File not found in database!");
} else {
    $type = $row['type'];
}

function cascUrl($build, $fileDataID)
{
    $params = "/fdid?buildconfig=" . $build['buildconfig']['hash'] . "&cdnconfig=" . $build['cdnconfig']['hash'] . "&filedataid=" . $fileDataID;
    return 'https://wow.tools/casc/file' . $params;
}

function downloadFile($out, $build, $fileDataID)
{
    $url = cascUrl($build, $fileDataID);
    $fileHandle = fopen($out, 'w+');
    $curl = curl_init($url);

    curl_setopt($curl, CURLOPT_FILE, $fileHandle);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);

    $exec = curl_exec($curl);

    curl_close($curl);
    fclose($fileHandle);

    if ($exec) {
        return true;
    } else {
        return false;
    }
}

function getDiff($fromBuild, $toBuild, $fileDataID, $raw)
{
    $fromFile = tempnam('/tmp/', 'PREVIEW');
    $toFile = tempnam('/tmp/', 'PREVIEW');
    downloadFile($fromFile, $fromBuild, $fileDataID);
    downloadFile($toFile, $toBuild, $fileDataID);

    if($raw){
        $rawFromFile = tempnam('/tmp/', 'PREVIEW');
        $rawToFile = tempnam('/tmp/', 'PREVIEW');

        shell_exec("/usr/bin/hd -n62144 ".escapeshellarg($fromFile)." > " . escapeshellarg($rawFromFile));
        shell_exec("/usr/bin/hd -n62144 ".escapeshellarg($toFile)." > " . escapeshellarg($rawToFile));

        $cmd = "diff -u " . escapeshellarg($rawFromFile) . " " . escapeshellarg($rawToFile);
        $result = shell_exec($cmd);

        if(empty($result)){
            shell_exec("/usr/bin/hd ".escapeshellarg($fromFile)." > " . escapeshellarg($rawFromFile));
            shell_exec("/usr/bin/hd ".escapeshellarg($toFile)." > " . escapeshellarg($rawToFile));

            $cmd = "diff -u " . escapeshellarg($rawFromFile) . " " . escapeshellarg($rawToFile);
            $result = shell_exec($cmd);
        }
    }else{
        $cmd = "diff -u " . escapeshellarg($fromFile) . " " . escapeshellarg($toFile);
        $result = shell_exec($cmd);
    }
    unlink($fromFile);
    unlink($toFile);

    $fromBuildName = parseBuildName($fromBuild['buildconfig']['description'])['full'];
    $toBuildName = parseBuildName($toBuild['buildconfig']['description'])['full'];

    // Remove the paths from the diff, so we're not leaking paths.
    $result = str_replace($fromFile, $fromBuildName, $result);
    $result = str_replace($toFile, $toBuildName, $result);

    return $result;
}

echo getDiff($from, $to, $fileDataID, $_GET['raw']);
