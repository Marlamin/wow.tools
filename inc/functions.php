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

function parseBuildName($buildname){
	$build['original'] = $buildname;

	$buildname = str_replace("WOW-", "", $buildname);
	$split = explode("patch", $buildname);
	$buildnum = $split[0];
	$descexpl = explode("_", $split[1]);

	$build['full'] = $descexpl[0].".".$buildnum;
	$build['patch'] = $descexpl[0];
	$build['build'] = $buildnum;
	return $build;
}

function prettyBranch($branch){
	switch($branch){
		case "wow":
			return "<span class='badge badge-primary'>Retail</span>";
		case "wowt":
			return "<span class='badge badge-warning'>PTR</span>";
		case "wow_beta":
			return "<span class='badge badge-danger'>Beta</span>";
		case "wowz":
			return "<span class='badge badge-success'>Submission</span>";
		case "wow_classic":
			return "<span class='badge badge-info'>Classic</span>";
		case "wow_classic_beta":
			return "<span class='badge badge-info'>Classic Beta</span>";
		case "wowe1":
			return "<span class='badge badge-secondary'>Event 1</span>";
		case "wowe2":
			return "<span class='badge badge-secondary'>Event 2</span>";
		case "wowe3":
			return "<span class='badge badge-secondary'>Event 3</span>";
		default:
			return "UNKNOWN";
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

function parseConfig($file){
	$handle = fopen($file, "r");
	$config = array();
	$t = explode("/", $file);
	$config['original-filename'] = basename($file);

	if(strlen($t[9]) == 2){
		die("Faulty config!");
	}

	if ($handle) {
		while (($line = fgets($handle)) !== false) {
			$line = trim($line);
			if(empty($line) || $line[0] == "#") continue;
			$vars = explode(" = ", $line);
			if($vars[0] == "patch-entry"){
				if(!isset($config['patch-entry'])){
					$config['patch-entry'] = array();
				}

				// Patch entry has double entries, append
				$config['patch-entry'][count($config['patch-entry'])] = $vars[1];
			}else if(!empty($vars[1])){
				$config[$vars[0]] = $vars[1];
			}
		}
		fclose($handle);
	}

	ksort($config);

	return $config;
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
	$r = $query->fetch();
	if(!empty($r)){
		return $r;
	}else{
		return false;
	}
}

function getCDNConfigbyCDNConfigHash($hash, $product = "wow"){
	global $pdo;
	$query = $pdo->prepare("SELECT * FROM ".$product."_cdnconfig WHERE hash = ?");
	$query->execute([$hash]);
	$r = $query->fetch();
	if(!empty($r)){
		return $r;
	}else{
		return false;
	}
}

function getPatchConfigByPatchConfigHash($hash, $product = "wow"){
	global $pdo;
	$query = $pdo->prepare("SELECT * FROM ".$product."_patchconfig WHERE hash = ?");
	$query->execute([$hash]);
	$r = $query->fetch();
	if(!empty($r)){
		return $r;
	}else{
		return false;
	}
}
?>