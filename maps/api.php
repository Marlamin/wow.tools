<?php

if($_GET['type'] == "areaname"){
	$mapid = intval($_GET['id']);

	$index = intval($_GET['index']);

	$assignments = json_decode(file_get_contents("map_json/".$mapid.".json"), true);

	$areas = json_decode(file_get_contents("areas.json"), true);

	//print_r($areas);

	$name = $areas[$assignments['Value']['adts'][$_GET['adt']]['ids'][$index]];

	if(empty($name)){
		$name = $assignments['Value']['name'];
		if(empty($name)){
			$name = "No name found :(";
		}
	}

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
die("Not yet finished");
	$q = $pdo->prepare("SELECT root_cdn FROM wow_buildconfig WHERE description LIKE :build");
	$q->bindValue(":build", "%".$build."%");
	$q->execute();
	$res = $q->fetchAll();
	if(count($res) == 0){
		echo json_encode(array("error" => "No build found"));
		die();
	}

	$root = $res[0]['root_cdn'];
	if(strlen($root) != 32 || !ctype_xdigit($root)){
		echo json_encode(array("error" => "Invalid root file"));
		die();
	}

	$root8 = substr($root, 0, 8);
	$root8dec = hexdec($root8);

	$map = strtolower(filter_var($_GET['map'], FILTER_SANITIZE_STRING));

	$offset['y'] = 63;
	$offset['x'] = 63;

	$q = $pdo->prepare("SELECT id FROM wow_rootfiles_available_roots WHERE root8 = :root8dec");
	$q->bindParam(":root8dec", $root8dec);
	$q->execute();
	$rootid = $q->fetch()['id'];

	
	$fq = $pdo->query("SELECT wow_rootfiles.filename FROM wow_rootfiles_available build JOIN wow_rootfiles ON wow_rootfiles.id = build.filedataid WHERE build.root8id = ".$rootid." AND wow_rootfiles.filename LIKE 'world/minimaps/".$mysqli->escape_string($map)."/map%' ORDER BY wow_rootfiles.filename ASC");
	if($fq->num_rows == 0){
		echo json_encode(array("error" => "No results"));
		die();
	}

	while($row = $fq->fetch()){
		$cleaned = str_replace(array("world/minimaps/".$map."/map", ".blp"), "", $row['filename']);

		$tile = explode("_", $cleaned);

		if(!is_numeric($tile[0])){ continue; }

		if($offset['y'] > $tile[0]){ $offset['y'] = (int) $tile[0]; }
		if($offset['x'] > $tile[1]){ $offset['x'] = (int) $tile[1]; }
	}

	echo json_encode($offset);
}else{
	die("Invalid request!");
}
?>