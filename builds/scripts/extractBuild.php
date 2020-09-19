<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

include(__DIR__ . "/../../inc/config.php");

$q = $pdo->query("SELECT id FROM wow_rootfiles WHERE type IN ('adt', 'adtdat', 'anim', 'blp', 'm2', 'skel', 'skin', 'tex', 'wdl', 'wdt', 'wmo', '_lodadt', '_lod_doodaddefsadt', '_lod_fuddlewizzadt', '_objadt', '_tex0adt', '_xxxwmo')");

$fdids = $q->fetchAll(PDO::FETCH_COLUMN);
sort($fdids);
file_put_contents("/home/wow/buildbackup/mvfdids.txt", implode("\n", $fdids));
