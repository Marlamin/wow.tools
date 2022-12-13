<?php

require_once("/var/www/wow.tools/inc/config.php");

if(empty($_GET['build']) || empty($_GET['id']))
    die("Not enough parameters");

$staticBuild = trim(file_get_contents("/var/www/wow.tools/casc/extract/lastextractedroot.txt"));

if($_GET['build'] != $staticBuild)
    die("Invalid build, it might still be extracting, try again later");

if(!is_numeric($_GET['id']))
    die("Invalid ID");

$id = intval($_GET['id']);

$fnameq = $pdo->prepare("SELECT `filename`, `type` FROM wow_rootfiles WHERE `id` = ?");
$fnameq->execute([$id]);
$file = $fnameq->fetch(PDO::FETCH_ASSOC);

if(empty($file))
    die("File not found");

if(empty($file['filename'])){
    $filename = $id . "." . $file['type'];
}else{
    $filename = basename($file['filename']);
}

header('Content-type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
readfile("/var/www/wow.tools/casc/extract/" . $staticBuild . "/" . $id);