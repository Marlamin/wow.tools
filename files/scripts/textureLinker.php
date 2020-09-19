<?php

include("../../inc/config.php");
if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

$inslinkq = $pdo->prepare("INSERT IGNORE INTO wow_rootfiles_links (parent, child, type) VALUES (?, ?, 'm2 texture')");
foreach ($pdo->query("SELECT ID FROM wow_rootfiles WHERE type = 'm2' ORDER BY ID DESC") as $m2) {
    echo $m2['ID'] . "\n";
    $parent = $m2['ID'];
    $raw = file_get_contents("https://wow.tools/dbc/api/texture/" . $parent . "?build=8.2.5.31812");
    $json = json_decode($raw, true);
    foreach ($json as $key => $filedataids) {
        foreach ($filedataids as $child) {
            echo $parent . " => " . $child . "\n";
            $inslinkq->execute([$parent, $child]);
        }
    }
}
