<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

include(__DIR__ . "/../../inc/config.php");

$q = $pdo->query("SELECT id FROM `wow_rootfiles` JOIN wow_rootfiles_builds_erorus ON ORD(MID(wow_rootfiles_builds_erorus.files, 1 + FLOOR(wow_rootfiles.id / 8), 1)) & (1 << (wow_rootfiles.id % 8)) WHERE wow_rootfiles_builds_erorus.build = 2284 ORDER BY `wow_rootfiles`.`id` ASC");

$fdids = $q->fetchAll(PDO::FETCH_COLUMN);
sort($fdids);
file_put_contents("/home/wow/buildbackup/cacheFDIDs.txt", implode("\n", $fdids));
