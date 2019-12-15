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

function getDBC($name, $build){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, " http://127.0.0.1:5000/api/export/?name=".urlencode($name)."&build=".urlencode($build));
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
		$rows = [];
		$expl = explode("\n", $data);
		for($i = 0; $i < count($expl); $i++){
			$parsed = str_getcsv($expl[$i]);
			if($i == 0){
				$headers = $parsed;
				continue;
			}

			foreach($parsed as $key => $value){
				$rows[$i - 1][$headers[$key]] = $value;
			}
		}
		return $rows;
	}
}


require_once("../inc/config.php");

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
	$csv = getDBC("taxinodes", $_GET['build']);
	$mapid = intval($_GET['mapid']);

	$pathcsv = getDBC("taxipath", $_GET['build']);
	$paths = array();

	foreach($pathcsv as $path){
		if(empty($path['FromTaxiNode']) || empty($path['ToTaxiNode'])) continue;
		$paths[$path['FromTaxiNode']][] = $path['ToTaxiNode'];
	}

	$return = array();

	foreach($csv as $entry){
		if(!isset($entry['ContinentID']) || $entry['ContinentID'] != $mapid) continue;
		if(empty($entry['Pos[0]']) && empty($entry['Pos[1]'])) continue;
		if(!isset($entry['MountCreatureID[0]'])) continue;

		if($entry['MountCreatureID[0]'] != 0 && $entry['MountCreatureID[1]'] != 0){
			$type = "neutral";
		}elseif($entry['MountCreatureID[0]'] != 0){
			$type="horde";
		}elseif($entry['MountCreatureID[1]'] != 0){
			$type = "alliance";
		}else{
			$type = "unknown";
		}

		$return['ids'][] = $entry['ID'];
		$return['points'][$entry['ID']] = array("x" => $entry['Pos[0]'], "y" => $entry['Pos[1]'], "name" => $entry['Name_lang'], "type" => $type);
		if(!empty($paths[$entry['ID']])){
			$return['points'][$entry['ID']]["connected"] = $paths[$entry['ID']];
		}
	}
	echo json_encode($return);
}else if($_GET['type'] == "offset"){
	$build = filter_var($_GET['build'], FILTER_VALIDATE_INT);
	if(!$build){
		echo json_encode(array("error" => "Invalid build"));
		die();
	}

	$map = trim(filter_var($_GET['map'], FILTER_SANITIZE_STRING));

	$buildq = $pdo->prepare("SELECT hash FROM wow_buildconfig WHERE description LIKE :desc LIMIT 1");
	$buildq->bindValue(":desc", "WOW-" . $build . "%");
	$buildq->execute();
	$buildrow = $buildq->fetch();
	if(empty($buildrow)){
		// Check old offset DB
		$oldOffsets = json_decode(file_get_contents("data/offsets.json"), true);
		if(array_key_exists($build, $oldOffsets)){
			if(array_key_exists($map, $oldOffsets[$build])){
				echo json_encode($oldOffsets[$build][$map]);
			}else{
				echo json_encode(array("error" => "Map not found in offsets", "map" => $map));
			}
		}else{
			echo json_encode(array("error" => "Offset build not found", "map" => $map));
		}
		die();
	}

	$fdids = getFileDataIDs($buildrow['hash']);
	if(!$fdids){
		die("Got empty filedataids for build " . print_r($buildrow, true));
	}

	$map = strtolower($map);

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
}else if($_GET['type'] == "pois"){
	if(empty($_GET['build']) || !isset($_GET['mapid'])){
		die("Invalid arguments, need build, mapid");
	}

	header('Content-Type: application/json');

	$dbc = getDBC("areapoi", $_GET['build']);

	if(empty($dbc)){
		echo json_encode(array("error" => "Error retrieving DBC!"));
		die();
	}
	$pois=[];
	foreach($dbc as $row){
		if(empty($row['ID']) || empty($row['Name_lang']))
			continue;

		if((int)$row['ContinentID'] != (int)$_GET['mapid'])
			continue;

		$poi['x'] = $row['Pos[0]'];
		$poi['y'] = $row['Pos[1]'];
		$poi['icon'] = $row['Icon'];
		$poi['name'] = $row['Name_lang'];
		$poi['desc'] = $row['Description_lang'];

		$pois[] = $poi;

		unset($poi);
	}

	echo json_encode($pois);
}else{
	die("Invalid request!");
}
?>