<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");
include "../../inc/config.php";

function getConfigByMapVersion($mapid, $versionid){
	global $pdo;

	$q = $pdo->prepare("SELECT resx, resy, zoom, minzoom, maxzoom FROM wow_maps_config WHERE mapid = ? AND versionid = ? AND resx != 0");
	$q->execute([$mapid, $versionid]);

	$config = $q->fetch(PDO::FETCH_ASSOC);
	if(empty($config)){
		$config = [];
		$config['resx'] = 0;
		$config['resy'] = 0;
		$config['zoom'] = 4;
		$config['minzoom'] = 4;
		$config['maxzoom'] = 7;
		$config['fallback'] = true;
	}

	return $config;
}

// Map config JSON, this is used for everything important

header("Content-Type: application/json");
$data = [
	'maps' => [],
	'versions' => []
];

foreach($pdo->query("SELECT * FROM wow_maps_maps ORDER BY firstseen ASC") as $map){
	$mapnameraw = $map['internal'];
	$map['internal'] = str_replace("'", '', str_replace('-', '', $map['internal']));
	$data['maps'][] = $map;

	$mvq = $pdo->prepare("SELECT * FROM wow_maps_versions JOIN wow_builds ON wow_maps_versions.versionid = wow_builds.id WHERE map_id = ? ORDER BY build ASC");
	$mvq->execute([$map['id']]);
	$mvs = $mvq->fetchAll(PDO::FETCH_ASSOC);
	$prevmd5 = '';

	foreach($mvs as $mapversion){
		if($mapversion['md5'] == $prevmd5){ continue; }
		$mapversion['fullbuild'] = $mapversion['expansion'].".".$mapversion['major'].".".$mapversion['minor'].".".$mapversion['build'];
		$mapversion['config'] = getConfigByMapVersion($map['id'], $mapversion['id']);

		$prevmd5 = $mapversion['md5'];
		$version = $mapversion['versionid'];

		$mapversion['config']['zoom'] = (int) $mapversion['config']['zoom'];
		$mapversion['config']['minzoom'] = (int) $mapversion['config']['minzoom'];
		$mapversion['config']['maxzoom'] = (int) $mapversion['config']['maxzoom'];

		$mapversion['config']['offset']['min']['y'] = 63;
		$mapversion['config']['offset']['min']['x'] = 63;

		$mapversion['config']['offset']['max']['y'] = 0;
		$mapversion['config']['offset']['max']['x'] = 0;

		$maprawdir = "/home/wow/minimaps/raw/" . $mapversion['expansion'].".".$mapversion['major'].".".$mapversion['minor'].".".$mapversion['build']."/world/minimaps/".$mapnameraw;

		if(is_dir($maprawdir)){

			foreach(glob($maprawdir."/*") as $tileindir){
				$tileparts = explode ("/", $tileindir);
				$tile = str_replace(".blp", "", $tileparts[9]);
				$tile = str_replace("map", "", $tile);
				$tile = explode("_", $tile);

				if(!is_numeric($tile[0])){ continue; }

				if($mapversion['config']['offset']['min']['y'] > $tile[0]){ $mapversion['config']['offset']['min']['y'] = (int) $tile[0]; }
				if($mapversion['config']['offset']['min']['x'] > $tile[1]){ $mapversion['config']['offset']['min']['x'] = (int) $tile[1]; }
				if($mapversion['config']['offset']['max']['y'] < $tile[0]){ $mapversion['config']['offset']['max']['y'] = (int) $tile[0]; }
				if($mapversion['config']['offset']['max']['x'] < $tile[1]){ $mapversion['config']['offset']['max']['x'] = (int) $tile[1]; }
			}
		}

		/* Unship data not used in JSON! */
		unset($mapversion['id'], $mapversion['expansion'], $mapversion['major'], $mapversion['minor'], $mapversion['map_id'], $mapversion['builton'], $mapversion['version'], $mapversion['tilemd5']);

		$mapversion['build'] = (int)$mapversion['build'];

		$data['versions'][$map['id']][$version] = $mapversion;
	}
	// $data['versions'][$map['id']] = array_reverse($data['versions'][$map['id']], true);
	uasort($data['versions'][$map['id']], function($a, $b) { if($a['build'] === $b['build']) return 0; return $a['build'] < $b['build']; } );
}

file_put_contents("/var/www/wow.tools/maps/data/data.new.json", json_encode($data));

// Offsets json. Gets top-left ADT tile. Generated from partial tilesets. Needs to be complete before production!

die("Offsets stuff is todo");

// $offsets = array();

// foreach(glob("/home/marlamin/wow/automatic/*", GLOB_ONLYDIR) as $dir){
// 	$parts = explode("/", $dir);
// 	$ver = explode(".", $parts[5]);

// 	if(count($ver) != 4){ continue;	} // Skip dirs without versions

// 	$offsets[$ver[3]] = array();

// 	foreach(glob($dir."/World/Minimaps/*", GLOB_ONLYDIR) as $versiondir){
// 		$versionparts = explode("/", $versiondir);
// 		if($versionparts[8] == "WMO" || $versionparts[8] == "wmo"){ continue; } // Skip WMOs... for now :)
// 		$versionparts[8] = str_replace("'", '', str_replace('-', '', $versionparts[8]));
// 		$offsets[$ver[3]][$versionparts[8]] = array("x" => 63, "y" => 63);

// 		foreach(glob($versiondir."/*") as $tileindir){
// 			$tileparts = explode ("/", $tileindir);
// 			$tile = str_replace(".blp", "", $tileparts[9]);
// 			$tile = str_replace("map", "", $tile);
// 			$tile = explode("_", $tile);

// 			if(!is_numeric($tile[0])){ continue; }

// 			//Y and X are flipped yo

// 			if($offsets[$ver[3]][$versionparts[8]]['y'] > $tile[0]){ $offsets[$ver[3]][$versionparts[8]]['y'] = (int) $tile[0]; }
// 			if($offsets[$ver[3]][$versionparts[8]]['x'] > $tile[1]){ $offsets[$ver[3]][$versionparts[8]]['x'] = (int) $tile[1]; }
// 		}
// 	}
// }


// file_put_contents("offsets70.json", json_encode($offsets));