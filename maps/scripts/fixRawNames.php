<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");
require_once("../../inc/config.php");

$mapCache = [];
foreach($pdo->query("SELECT id, internal FROM wow_maps_maps") as $map){
	$mapCache[strtolower($map['internal'])] = $map['id'];
}

$mapCacheNotNormalized = [];
foreach($pdo->query("SELECT id, internal FROM wow_maps_maps") as $map){
	$mapCacheNotNormalized[$map['internal']] = $map['id'];
}

$versionMapCache = [];
foreach($pdo->query("SELECT map_id, versionid FROM wow_maps_versions") as $mapVersion){
	$versionMapCache[$mapVersion['versionid']][] = $mapVersion['map_id'];
}

$mapCacheByID = [];
foreach($pdo->query("SELECT id, internal FROM wow_maps_maps") as $map){
	$mapCacheByID[$map['id']] = $map['internal'];
}

foreach(glob("/home/wow/minimaps/png/*") as $dir){
	$version = str_replace("/home/wow/minimaps/png/", "", $dir);
	$versionex = explode(".", $version);
	$versionid = getOrCreateVersionID($version);
	echo "Version: ".$versionex[0].".".$versionex[1].".".$versionex[2].".".$versionex[3]." (".$versionid.")\n";
	foreach(glob($dir."/*.png") as $map){
		$mapname = str_replace(array($dir."/", ".png"), "", $map);

		if(!array_key_exists(strtolower($mapname), $mapCache)){
			echo "[".$version."] [".$mapname."] Map is not known!\n";
			continue;
		}

		if(!array_key_exists($mapname, $mapCacheNotNormalized)){
			$actualMap = $mapCacheByID[$mapCache[strtolower($mapname)]];
			echo "[".$version."] [".$mapname."] Map is not known under this exact name, renaming to ".$actualMap."\n";
			shell_exec("mv ".escapeshellarg($map)." ".escapeshellarg(str_replace($mapname, $actualMap, $map)));
		}

		$mapid = $mapCache[strtolower($mapname)];

		if(!array_key_exists($versionid, $versionMapCache) || !in_array($mapid, $versionMapCache[$versionid])){
			echo "[".$version."] [".$mapname."] Map is unknown in version ".$version." (".$versionid.")\n";
		}
	}
}
