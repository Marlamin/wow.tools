<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");
require_once("../../inc/config.php");

$versionCache = [];
foreach($pdo->query("SELECT id, version FROM wow_dbc_versions") as $version){
	$versionCache[$version['version']] = $version['id'];
}

$tableCache = [];
foreach($pdo->query("SELECT id, name FROM wow_dbc_tables") as $table){
	$tableCache[$table['name']] = $table['id'];
}

$versionTableCache = [];
foreach($pdo->query("SELECT versionid, tableid FROM wow_dbc_table_versions") as $tv){
	$versionTableCache[$tv['versionid']][] = $tv['tableid'];
}

function getOrCreateVersionID($version){
	global $pdo;
	global $versionCache;

	if(!array_key_exists($version, $versionCache)){
		// Version does not exist, create and return id
		echo "Creating version id for " . $version . "\n";
		$q = $pdo->prepare("INSERT INTO wow_dbc_versions (version) VALUES (?)");
		$q->execute([$version]);
		$insertId = $pdo->lastInsertId();
		$versionCache[$version] = $insertId;
	}

	return $versionCache[$version];
}

function getOrCreateTableID($table, $displayname){
	global $pdo;
	global $tableCache;

	if(!array_key_exists($table, $tableCache)){
		// Table does not exist, create and return id
		echo "Creating table id for " . $displayname . "\n";
		$q = $pdo->prepare("INSERT INTO wow_dbc_tables (displayName, name) VALUES (?, ?)");
		$q->execute([$displayname, $table]);
		$insertId = $pdo->lastInsertId();
		$tableCache[$table] = $insertId;
	}

	return $tableCache[$table];
}

$dbcdir = "/home/wow/dbcs/";
$insertTVq = $pdo->prepare("INSERT INTO wow_dbc_table_versions (versionid, tableid, contenthash) VALUES (?, ?, ?)");

foreach(glob($dbcdir."*", GLOB_ONLYDIR) as $versiondir){
	$version = str_replace($dbcdir, "", $versiondir);
	$versionid = getOrCreateVersionID($version);

	$di = new RecursiveDirectoryIterator($versiondir, RecursiveDirectoryIterator::SKIP_DOTS);
	$it = new RecursiveIteratorIterator($di);
	foreach($it as $file) {
		$basename = basename($file);

		// Skip non-dbc/db2 files
		if(substr($basename, -3) != "dbc" && substr($basename, -3) != "db2") continue;

		$displayname = str_replace(array(".dbc", ".db2"), "", $basename);
		$cleantablename = strtolower($displayname);

		$tableid = getOrCreateTableID($cleantablename, $displayname);

		if(!array_key_exists($versionid, $versionTableCache)){
			$versionTableCache[$versionid] = [];
		}

		if(!in_array($tableid, $versionTableCache[$versionid])){
			echo "[" . $version . " (".$versionid.")] [" . $displayname . " (".$tableid.")] Inserting..\n";
			$md5 = md5_file($file);
			$insertTVq->execute([$versionid, $tableid, $md5]);
			$versionTableCache[$versionid][] = $tableid;
		}
	}
}
?>