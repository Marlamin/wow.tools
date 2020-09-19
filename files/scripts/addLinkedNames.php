<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}
include("../../inc/config.php");

$unkFiles = $pdo->query("SELECT * FROM wow_rootfiles WHERE filename IS NULL ORDER BY ID DESC")->fetchAll(PDO::FETCH_COLUMN);

$linkq = $pdo->prepare("SELECT * FROM wow_rootfiles_links WHERE child = ?");
$fnameq = $pdo->prepare("SELECT filename, type FROM wow_rootfiles WHERE id = ?");

$newNames = [];

foreach ($unkFiles as $unkFDID) {
    $linkq->execute([$unkFDID]);
    $res = $linkq->fetch(PDO::FETCH_ASSOC);

    if (!empty($res)) {
        $fnameq->execute([$res['parent']]);
        $fres = $fnameq->fetch(PDO::FETCH_ASSOC);

        if (!empty($fres['filename'])) {
            if ($fres['type'] == "m2") {
                if ($res['type'] == "m2 texture") {
                    $newNames[$unkFDID] = str_replace(".m2", "", $fres['filename']) . "_" . $unkFDID . ".blp";
                } else {
                    // echo "Unhandled child type " . $res['type'] . " (".$res['parent'].") for child file " . $unkFDID."\n";
                }
            } else {
                // echo "Unhandled parent type " . $fres['type'] . " (".$res['parent'].") for child file " . $unkFDID."\n";
            }
        }
    }
}

foreach ($newNames as $FDID => $filename) {
    echo $FDID . ";" . $filename . "\n";
}
