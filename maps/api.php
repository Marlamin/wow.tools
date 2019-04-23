<?php

function getFileDataIDs($buildconfig){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://wow.tools/casc/root/fdids?buildconfig=" . $buildconfig);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($ch);
	if(!$data){
		echo "cURL fail: " . print_r(curl_error($ch))."\n";
	}
	curl_close($ch);
	if($data == ""){
		return false;
	}else{
		return json_decode($data);
	}
}

if($_GET['type'] == "areaname"){
	// $mapid = intval($_GET['id']);

	// $index = intval($_GET['index']);

	// $assignments = json_decode(file_get_contents("map_json/".$mapid.".json"), true);

	// $areas = json_decode(file_get_contents("areas.json"), true);

	// //print_r($areas);

	// $name = $areas[$assignments['Value']['adts'][$_GET['adt']]['ids'][$index]];

	// if(empty($name)){
	// 	$name = $assignments['Value']['name'];
	// 	if(empty($name)){
	// 		$name = "No name found :(";
	// 	}
	// }

	$name = "Unsupported map";
	$return = array("name" => $name);

	echo json_encode($return);
}elseif($_GET['type'] == "flightpaths"){
	$csv = array_map('str_getcsv', file('TaxiNodes.csv'));

	$mapid = intval($_GET['id']);

	$pathcsv = array_map('str_getcsv', file('TaxiPath.csv'));
	$paths = array();

	foreach($pathcsv as $path){
		$paths[$path[1]][] = $path[2];
	}

	$return = array();

	foreach($csv as $entry){
		if($entry[0] == "ID") continue;
		if($entry[9] != $mapid) continue;
		if(strpos($entry[4], 'Quest') !== false) continue;
		if(strpos($entry[4], 'DISABLED') !== false) continue;
		if($entry[1] == 0 && $entry[2] == 0) continue;

		if($entry['5'] != 0 && $entry['6'] != 0){ $type = "neutral"; }elseif($entry['5'] != 0){ $type="horde"; }elseif($entry['6'] != 0){ $type = "alliance"; }else{ $type = "unknown"; }
		$return['ids'][] = $entry[0];
		$return['points'][$entry[0]] = array("x" => $entry[1], "y" => $entry[2], "name" => $entry[4], "type" => $type, "connected" => $paths[$entry[0]]);
	}
	echo json_encode($return);
}else if($_GET['type'] == "offset"){
	require_once("../inc/config.php");

	$build = filter_var($_GET['build'], FILTER_VALIDATE_INT);
	if(!$build){
		echo json_encode(array("error" => "Invalid build"));
		die();
	}

	$buildq = $pdo->prepare("SELECT hash FROM wow_buildconfig WHERE description LIKE :desc LIMIT 1");
	$buildq->bindValue(":desc", "WOW-" . $build . "%");
	$buildq->execute();
	$buildrow = $buildq->fetch();
	if(empty($buildrow)){
		die("Build not found in database!");
	}

	$fdids = getFileDataIDs($buildrow['hash']);

	$map = trim(strtolower(filter_var($_GET['map'], FILTER_SANITIZE_STRING)));

	$offset['y'] = 63;
	$offset['x'] = 63;

	$fq = $pdo->prepare("SELECT id, filename FROM wow_rootfiles WHERE filename LIKE :minimapdir ORDER BY filename ASC");
	$fq->bindValue(":minimapdir", "world/minimaps/".$map."/map%");
	$fq->execute();
	$minimaptiles = $fq->fetchAll();

	if(count($minimaptiles) == 0){
		echo json_encode(array("error" => "No results", "map" => $map));
		die();
	}

	foreach($minimaptiles as $row){
		if(!in_array($row['id'], $fdids))
			continue;

		$cleaned = str_replace(array("world/minimaps/".$map."/map", ".blp"), "", $row['filename']);

		$tile = explode("_", $cleaned);

		if(!is_numeric($tile[0])){ continue; }

		if($offset['y'] > $tile[0]){ $offset['y'] = (int) $tile[0]; }
		if($offset['x'] > $tile[1]){ $offset['x'] = (int) $tile[1]; }
	}

	if($offset['x'] == 63 && $offset['y'] == 63){
		echo json_encode(array("error" => "Error calculating offset"));
		die();
	}

	echo json_encode($offset);
}else{
	die("Invalid request!");
}
?>