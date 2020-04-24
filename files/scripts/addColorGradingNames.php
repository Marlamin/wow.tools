<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");
function getDBC($name, $build){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:5000/api/export/?name=".urlencode($name)."&build=".urlencode($build));
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
			if(empty(trim($expl[$i])))
				continue;

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

require_once("../../inc/config.php");

if(empty($argv[1])){
	die("Need buildconfig hash as argument");
}

if(strlen($argv[1]) == 32){
	// CASC
	$build = getVersionByBuildConfigHash($argv[1]);
	if(empty($build)){
		die("Could not find build!");
	}

	if(empty($build['buildconfig']['description'])){
		die("Empty build description!");
	}

	$rawdesc = str_replace("WOW-", "", $build['buildconfig']['description']);
	$buildnum = substr($rawdesc, 0, 5);
	$rawdesc = str_replace(array($buildnum, "patch"), "", $rawdesc);
	$descexpl = explode("_", $rawdesc);
	$buildName = $descexpl[0].".".$buildnum;
}else{
	die("Invalid buildconfig specified");
}

$unkCheckQ = $pdo->prepare("SELECT filename FROM wow_rootfiles WHERE id = ?");

$light = getDBC("light", $buildName);
$lightParams = getDBC("lightparams", $buildName);
$lightData = getDBC("lightdata", $buildName);
$rawLightSkybox  = getDBC("lightskybox", $buildName);
$lightSkybox = [];
foreach($rawLightSkybox as $rawLightSkyboxEntry){
	$lightSkybox[$rawLightSkyboxEntry['ID']] = $rawLightSkyboxEntry;
}
$zoneLight  = getDBC("zonelight", $buildName);
$rawMaps = getDBC("map", $buildName);
$maps = [];
foreach($rawMaps as $rawMapEntry){
	$maps[$rawMapEntry['ID']] = $rawMapEntry;
}

$doneFdids = [];

foreach($lightData as $lightDataEntry){
	if(empty($lightDataEntry['ColorGradingFileDataID']))
		continue;

	$cgFDID = $lightDataEntry['ColorGradingFileDataID'];
	if(in_array($cgFDID, $doneFdids))
		continue;

	$unkCheckQ->execute([$cgFDID]);
	$filename = $unkCheckQ->fetchColumn();
	if(empty($filename)){
		$match = false;
		$lightParamID = $lightDataEntry['LightParamID'];
		echo "Checking LightParamID " . $lightParamID." for unknown color grading filedataid " . $cgFDID . "\n";
		foreach($light as $lightEntry){
			for($i = 0; $i < 8; $i++){
				if($lightEntry['LightParamsID['.$i.']'] == $lightParamID){
					if(array_key_exists($lightEntry['ContinentID'], $maps)){
						echo "Map: " . $maps[$lightEntry['ContinentID']]['MapName_lang']." (".$maps[$lightEntry['ContinentID']]['Directory'].")\n";
						echo "Coords: " . $lightEntry['GameCoords[0]']." ". $lightEntry['GameCoords[1]']." ". $lightEntry['GameCoords[2]']."\n";
						foreach($zoneLight as $zoneLightEntry){
							if($zoneLightEntry['LightID'] == $lightEntry['ID']){
								echo "!!! Matched zoneLight " . $zoneLightEntry['Name']."\n";

								$match = true;
							}
						}
					}else{
						echo "Unknown map: " . $lightEntry['ContinentID']."\n";
					}
				}
			}
		}

		foreach($lightParams as $lightParamsEntry){
			if($lightParamsEntry['ID'] != $lightParamID)
				continue;

			if($lightParamsEntry['LightSkyboxID'] != 0){
				echo "!!! Matched skybox " . $lightParamsEntry['LightSkyboxID']."\n";
				if(array_key_exists($lightParamsEntry['LightSkyboxID'], $lightSkybox)){
					print_r($lightSkybox[$lightParamsEntry['LightSkyboxID']]);
					$match = true;
				}
			}else{
				echo "No skybox set for LightParamID " . $lightParamID."\n";
			}
		}

		if($match){
			$doneFdids[] = $cgFDID;
		}else{
			echo "Unable to find match for " . $cgFDID."\n";
		}
		// $suggestedName = "environments/colorgrading/colorgrading_" . $description . ".blp";

		echo "\n";
	}
}