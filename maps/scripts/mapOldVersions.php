<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}
require_once("../../inc/config.php");

die("Conversion done, script kept in case something needs redoing. Can be removed when whole map system is succesfully ported.");

$builds = $pdo->query("SELECT * FROM wow_builds")->fetchAll();

$updq = $pdo->prepare("UPDATE wow_builds SET expansion = ?, major = ?, minor = ?, build = ? WHERE id = ?");
foreach ($builds as $build) {
    $expl = explode(".", $build['version']);
    $updq->execute([$expl[0], $expl[1], $expl[2], $expl[3], $build['id']]);
}

die("Map stuff");

$versionCache = [];
foreach ($pdo->query("SELECT id, version FROM wow_builds") as $version) {
    $versionCache[$version['version']] = $version['id'];
}

$oldVersionCacheByID = [];
foreach ($pdo->query("SELECT * FROM wow_maps_builds_old") as $version) {
    $oldVersionCacheByID[$version['id']] = $version['expansion'] . "." . $version['major'] . "." . $version['minor'] . "." . $version['build'];
}

function getOrCreateVersionID($version)
{
    global $pdo;
    global $versionCache;

    if (!array_key_exists($version, $versionCache)) {
        // Version does not exist, create and return id
        echo "Creating version id for " . print_r($version, true) . "\n";
        $q = $pdo->prepare("INSERT INTO wow_builds (version) VALUES (?)");
        $q->execute([$version]);
        $insertId = $pdo->lastInsertId();
        $versionCache[$version] = $insertId;
    }

    return $versionCache[$version];
}

$insertVersionTmp = $pdo->prepare("INSERT INTO wow_maps_versions_temp VALUES(?, ?, ?, ?)");
$oldversions = $pdo->query("SELECT * FROM wow_maps_versions")->fetchAll();
$oldversioncount = count($oldversions);
$oldvi = 0;
foreach ($oldversions as $oldversion) {
    if (!array_key_exists($oldversion['version'], $oldVersionCacheByID)) {
        continue;
    }

    $newVersionID = getOrCreateVersionID($oldVersionCacheByID[$oldversion['version']]);

    $insertVersionTmp->execute([$oldversion['map_id'], $newVersionID, $oldversion['md5'], $oldversion['tilemd5']]);

    $oldvi++;

    if (($oldvi % 1000) == 0) {
        echo "Generating new version entries: " . $oldvi . "/" . $oldversioncount . "\r";
    }
}

echo "\n";

$insertConfigTmp = $pdo->prepare("INSERT INTO wow_maps_config_temp VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$oldcfgs = $pdo->query("SELECT * FROM wow_maps_config")->fetchAll();
$oldcfgcount = count($oldcfgs);
$oldci = 0;
foreach ($oldcfgs as $oldcfg) {
    if (!array_key_exists($oldcfg['versionid'], $oldVersionCacheByID)) {
        continue;
    }
    $newVersionID = getOrCreateVersionID($oldVersionCacheByID[$oldcfg['versionid']]);

    $insertConfigTmp->execute([$newVersionID, $oldcfg['mapid'], $oldcfg['offsetx'], $oldcfg['offsety'], $oldcfg['resx'], $oldcfg['resy'], $oldcfg['zoom'], $oldcfg['minzoom'], $oldcfg['maxzoom'], $oldcfg['bgcolor']]);

    $oldci++;

    if (($oldci % 1000) == 0) {
        echo "Generating new config entries: " . $oldci . "/" . $oldcfgcount . "\r";
    }
}

echo "\n";
