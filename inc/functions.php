<?php
function generateURL($type, $hash, $cdndir = "wow"){
	return "tpr/".$cdndir."/".$type."/".$hash[0].$hash[1]."/".$hash[2].$hash[3]."/".$hash;
}

function doesFileExist($type, $hash, $cdndir = "wow"){
	if(strlen($hash) < 32){
		die("Empty hash! Hash: ".$hash." Type: ".$type);
	}

	if(file_exists($GLOBALS['basedir'] . "/" . generateURL($type, $hash, $cdndir))){
		return true;
	}else{
		return false;
	}
}

function parseBPSV($bpsv){
	$result = [];
	foreach($bpsv as $key => $line){
		if(empty(trim($line))){
			continue;
		}
		if($line[0] == "#") continue;
		$cols = explode("|", $line);
		if($key == 0) {
			foreach($cols as $key => $col){
				$exploded = explode("!", $col);
				$headers[] = $exploded[0];
			}
		}else{
			foreach($cols as $key => $col){
				$result[$cols[0]][$headers[$key]] = $col;
			}
		}
	}
	return $result;
}

function getVersionByBuildConfigHash($hash, $product = "wow"){
	global $pdo;
	$query = $pdo->prepare("SELECT * FROM ".$product."_versions WHERE buildconfig = ?");
	$query->execute([$hash]);
	$row = $query->fetch();
	if(!empty($row['cdnconfig'])){ $row['cdnconfig'] = getCDNConfigbyCDNConfigHash($row['cdnconfig'], $product); }
	if(!empty($row['buildconfig'])){ $row['buildconfig'] = getBuildConfigByBuildConfigHash($row['buildconfig'], $product); }
	return $row;
}

function getBuildConfigByBuildConfigHash($hash, $product = "wow"){
	global $pdo;
	$query = $pdo->prepare("SELECT * FROM ".$product."_buildconfig WHERE hash = ?");
	$query->execute([$hash]);
	return $query->fetch();
}
function getCDNConfigbyCDNConfigHash($hash, $product = "wow"){
	global $pdo;
	$query = $pdo->prepare("SELECT * FROM ".$product."_cdnconfig WHERE hash = ?");
	$query->execute([$hash]);
	return $query->fetch();
}
?>