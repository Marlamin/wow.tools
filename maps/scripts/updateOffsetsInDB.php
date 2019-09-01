<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");
require_once("../../inc/config.php");

$versionCacheByID = [];
foreach($pdo->query("SELECT id, version FROM wow_builds") as $version){
	$versionCacheByID[$version['id']] = $version['version'];
}

$mapCacheByID = [];
foreach($pdo->query("SELECT id, internal FROM wow_maps_maps") as $map){
	$mapCacheByID[$map['id']] = $map['internal'];
}

$mapConfigCache = [];
foreach($pdo->query("SELECT * FROM wow_maps_config") as $mapConfig){
	$mapConfigCache[$mapConfig['versionid']][$mapConfig['mapid']] = $mapConfig;
}

$updateOffsetQuery = $pdo->prepare("UPDATE wow_maps_config SET offsetx = ?, offsety = ? WHERE versionid = ? AND mapid = ?");

$knownOldOffsets = json_decode(file_get_contents("../data/offsets.json"), true);

foreach($mapConfigCache as $versionid => $maparr){
	$version = $versionCacheByID[$versionid];
	$versionex = explode(".", $version);
	$build = $versionex[3];
	foreach($maparr as $mapid => $config){
		$mapname = $mapCacheByID[$mapid];
		$cleanedMapName = str_replace("'", "", $mapname); // Apparently Zul'Gurub used to break things
		$knownOffset = false;

		if(!isset($config['offsetx']) && !isset($config['offsety'])){
			echo "[" . $version ."] [". $mapname."] Map has empty offset!\n";
			// Check disk for each possible variation of folders (yay for project being very old now!)
			$dirVariations = [];
			$dirVariations[] = "/home/wow/minimaps/raw/". $version ."/World/Minimaps/".$mapname; // Uppercase World/Minimaps normal mapname
			$dirVariations[] = "/home/wow/minimaps/raw/". $version ."/world/minimaps/".$mapname; // Lowercase World/Minimaps, normal mapname
			$dirVariations[] = "/home/wow/minimaps/raw/". $version ."/World/Minimaps/".$cleanedMapName; // Uppercase World/Minimaps, cleaned mapname
			$dirVariations[] = "/home/wow/minimaps/raw/". $version ."/world/minimaps/".$cleanedMapName; // Lowercase World/Minimaps, cleaned mapname
			$dirVariations[] = "/home/wow/minimaps/raw/". $version ."/World/Minimaps/".strtolower($mapname); // Uppercase World/Minimaps, lowercase mapname
			$dirVariations[] = "/home/wow/minimaps/raw/". $version ."/world/minimaps/".strtolower($mapname); // Lowercase World/Minimaps, lowercase mapname

			$dir = false;

			foreach($dirVariations as $possibleDir){
				if(is_dir($possibleDir)){
					$dir = $possibleDir;
				}
			}

			if(!empty($dir)){
				// Found directory, find offset
				echo "[" . $version ."] [". $mapname."] Found raw tiles on disk in dir " . $dir."\n";
				$offsetx = 63;
				$offsety = 63;
				if(is_dir($dir)){
					foreach(glob($dir."/*") as $tileindir){
						$tileparts = explode ("/", $tileindir);
						$tile = str_replace(".blp", "", $tileparts[9]);
						$tile = str_replace("map", "", $tile);
						$tile = explode("_", $tile);

						if(!is_numeric($tile[0])){ continue; }

						if($offsety > $tile[0]){ $offsety = (int) $tile[0]; }
						if($offsetx > $tile[1]){ $offsetx = (int) $tile[1]; }
					}

					echo "[" . $version ."] [". $mapname."] Found offset " . $offsetx." ".$offsety."\n";
					$updateOffsetQuery->execute([$offsetx, $offsety, $versionid, $mapid]);
				}
			}

			// Check archived offsets
			if(!$knownOffset && array_key_exists($build, $knownOldOffsets)){
				if(array_key_exists($cleanedMapName, $knownOldOffsets[$build])){
					echo "[" . $version ."] [". $mapname."] Found old offset " . $knownOldOffsets[$build][$cleanedMapName]['x']." ".$knownOldOffsets[$build][$cleanedMapName]['y']."\n";
					$updateOffsetQuery->execute([$knownOldOffsets[$build][$cleanedMapName]['x'], $knownOldOffsets[$build][$cleanedMapName]['y'], $versionid, $mapid]);
				}
			}
		}
	}
}

?>