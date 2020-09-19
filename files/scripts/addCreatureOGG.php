<?php
$disableBugsnag = true;
include("../../inc/config.php");

if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");

function getDBC($name, $build){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:5000/api/export/?name=".urlencode($name)."&build=".urlencode($build)."&useHotfixes=true&newLinesInStrings=false");
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
				if(array_key_exists($key, $headers)){
					$rows[$i - 1][$headers[$key]] = $value;
				}
			}
		}
		return $rows;
	}
}

function soundColToSoundName($soundCol){
	$soundCol = str_replace("ID", "", str_replace("Sound", "", $soundCol));
	switch($soundCol){
		case "InjuryCritical":
			return "woundcritical";
		case "Injury":
			return "wound";
		case "Exertion":
			return "attack";
		case "ExertionCritical":
			return "attackcritical";
		case "Fidget[0]":
		case "Fidget[1]":
		case "Fidget[2]":
		case "Fidget[3]":
		case "Fidget[4]":
		case "CustomAttack[0]":
		case "CustomAttack[1]":
			return strtolower(str_replace(['[', ']'], "", $soundCol));
		case "Death":
		case "Aggro":
		case "Alert":
		case "BattleShout":
		case "BattleShoutCritical":
		case "Loop":
		case "Stand":
		case "Birth":
			return strtolower($soundCol);
		default:
			die("!!! WARNING: UNMAPPED SOUND: " . $soundCol . "\n");
			return "";
	}
}

$build = "9.0.2.35854";
echo "Loading DBCs..";
$creatureDB = $pdo->query("SELECT * FROM wowdata.creatures ORDER BY ID DESC");
$creatureMap = [];
foreach($creatureDB as $creature){
	$json = json_decode($creature['json'], true);
	$creatureMap[$creature['id']] = ["name" => $creature['name'], "CDI" => $json['CreatureDisplayInfoID[0]']];
}

$creatureDisplayInfoDB = getDBC("creaturedisplayinfo", $build);
$creatureDisplayInfoMap = [];
foreach($creatureDisplayInfoDB as $cdi){
	$creatureDisplayInfoMap[$cdi['ID']] = $cdi;
}

$creatureModelDataDB = getDBC("creaturemodeldata", $build);
$creatureModelDataMap = [];
foreach($creatureModelDataDB as $cmd){
	$creatureModelDataMap[$cmd['ID']] = $cmd;
}

$creatureSoundDataDB = getDBC("creaturesounddata", $build);
$creatureSoundDataMap = [];
foreach($creatureSoundDataDB as $csd){
	$creatureSoundDataMap[$csd['ID']] = $csd;
}

$soundKitEntryDB = getDBC("soundkitentry", $build);
$soundKitMap = [];
foreach($soundKitEntryDB as $soundKitEntry){
	if(empty($soundKitEntry['SoundKitID']))
		continue;

	$soundKitMap[$soundKitEntry['SoundKitID']][] = $soundKitEntry['FileDataID'];
}

echo "..done!\n";
echo "Caching unnamed ogg IDs..";
$unnamedOggs = $pdo->query("SELECT id FROM wow_rootfiles WHERE type = 'ogg' AND filename IS NULL")->fetchAll(PDO::FETCH_COLUMN);
echo "..done!\n";

$getFilenameQ = $pdo->prepare("SELECT filename FROM wow_rootfiles WHERE id = ?");
// Creature
// CreatureDisplayInfo
// CreatureModelData
// CreatureSoundData
// SoundKitEntry

foreach($creatureMap as $creatureID => $creature){
	$consoleName = $creatureID . " " . $creature['name']."\n";
	$cdi = $creatureDisplayInfoMap[$creature['CDI']];
	$csd = [];
	if(!empty($cdi['SoundID'])){
		$csd = $creatureSoundDataMap[$cdi['SoundID']];
	}

	$cmd = $creatureModelDataMap[$cdi['ModelID']];
	if(!empty($cmd['SoundID']) && empty($csd)){
		$csd = $creatureSoundDataMap[$cmd['SoundID']];
	}

	$creatureName = "CREATURENAME";
	if(!empty($cmd['FileDataID'])){
		$res = $getFilenameQ->execute([$cmd['FileDataID']]);
		if($res){
			$filename = $getFilenameQ->fetch(PDO::FETCH_COLUMN);
			$creatureName = basename(str_replace(".m2", "", $filename));
		}
	}

	if(empty($csd))
		continue;

	$creatureSounds = [];

	foreach($csd as $colName => $skitID){
		if(empty($skitID))
			continue;

		if(!array_key_exists($skitID, $soundKitMap))
			continue;

		foreach($soundKitMap[$skitID] as $fdid){
			if(!in_array($fdid, $unnamedOggs))
				continue;

			$soundType = soundColToSoundName($colName);
			$creatureSounds[$soundType][] = ["skitid" => $skitID, "fdid" => $fdid];
		}
	}

	$outputFDIDs = [];

	if(!empty($creatureSounds)){
		echo $consoleName;
	}
	foreach($creatureSounds as $soundType => $fdids){
		for($i = 0; $i < count($fdids); $i++){
			if(in_array($fdids[$i]['fdid'], $outputFDIDs))
				continue;

			echo $fdids[$i]['fdid'].";sound/creature/".$creatureName."/mon_".$creatureName."_". $soundType. "_" . str_pad($i, 2, '0', STR_PAD_LEFT)."_".$fdids[$i]['skitid'].".ogg\n";
			$outputFDIDs[] = $fdids[$i]['fdid'];
		}
	}
}