<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");
include "../../inc/config.php";

function getConfigByMapVersion($mapid, $versionid){
	global $pdo;

	$q = $pdo->prepare("SELECT resx, resy, zoom, minzoom, maxzoom, offsetx, offsety FROM wow_maps_config WHERE mapid = ? AND versionid = ? AND resx != 0");
	$q->execute([$mapid, $versionid]);
	$config = $q->fetch(PDO::FETCH_ASSOC);

	if(empty($config)){
		$config = [];
		$config['resx'] = 0;
		$config['resy'] = 0;
		$config['zoom'] = 4;
		$config['minzoom'] = 4;
		$config['maxzoom'] = 7;
		$config['offsety'] = -1;
		$config['offsetx'] = -1;
		$config['fallback'] = true;
	}

	return $config;
}

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

		if(!empty($mapversion['config']['offsety'])){
			$mapversion['config']['offset']['min']['y'] = (int) $mapversion['config']['offsety'];
			$mapversion['config']['offset']['min']['x'] = (int) $mapversion['config']['offsetx'];
		}else{
			$mapversion['config']['offset']['min']['y'] = -1;
			$mapversion['config']['offset']['min']['x'] = -1;
		}

		/* Unship data not used in JSON! */
		unset($mapversion['id'], $mapversion['expansion'], $mapversion['major'], $mapversion['minor'], $mapversion['map_id'], $mapversion['builton'], $mapversion['version'], $mapversion['tilemd5'], $mapversion['config']['offsetx'], $mapversion['config']['offsety']);

		$mapversion['build'] = (int)$mapversion['build'];

		$data['versions'][$map['id']][$version] = $mapversion;
	}

	uasort($data['versions'][$map['id']], function($a, $b) { if($a['build'] === $b['build']) return 0; return $a['build'] < $b['build']; } );
}

file_put_contents(__DIR__ . "/../data/data.json", json_encode($data));