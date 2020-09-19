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
				if(empty($value))
					continue;
				if(array_key_exists($key, $headers)){
					$rows[$i - 1][$headers[$key]] = $value;
				}
			}
		}
		return $rows;
	}
}
$build = "9.0.2.35854";

// Manually defined ChrModelMap, saves lots of lookups/DB loading!
$chrModelMap = [];
$chrModelMap[1] = 'character/human/male/humanmale_hd';
$chrModelMap[2] = 'character/human/female/humanfemale_hd';
$chrModelMap[3] = 'character/orc/male/orcmale_hd';
$chrModelMap[4] = 'character/orc/female/orcfemale_hd';
$chrModelMap[5] = 'character/dwarf/male/dwarfmale';
$chrModelMap[6] = 'character/dwarf/female/dwarffemale';
$chrModelMap[7] = 'character/nightelf/male/nightelfmale_hd';
$chrModelMap[8] = 'character/nightelf/female/nightelffemale_hd';
$chrModelMap[9] = 'character/scourge/male/scourgemale_hd';
$chrModelMap[10] = 'character/scourge/female/scourgefemale_hd';
$chrModelMap[11] = 'character/tauren/male/taurenmale_hd';
$chrModelMap[12] = 'character/tauren/female/taurenfemale_hd';
$chrModelMap[13] = 'character/gnome/male/gnomemale_hd';
$chrModelMap[14] = 'character/gnome/female/gnomefemale_hd';
$chrModelMap[15] = 'character/troll/male/trollmale_hd';
$chrModelMap[16] = 'character/troll/female/trollfemale_hd';
$chrModelMap[17] = 'character/goblin/male/goblinmale';
$chrModelMap[18] = 'character/goblin/female/goblinfemale';
$chrModelMap[19] = 'character/bloodelf/male/bloodelfmale_hd';
$chrModelMap[20] = 'character/bloodelf/female/bloodelffemale_hd';
$chrModelMap[21] = 'character/draenei/male/draeneimale_hd';
$chrModelMap[22] = 'character/draenei/female/draeneifemale_hd';
// $chrModelMap[23] = 'Fel Orc Male';
// $chrModelMap[24] = 'Fel Orc Female';
// $chrModelMap[25] = 'Naga Male';
// $chrModelMap[26] = 'Naga Female';
// $chrModelMap[27] = 'Broken Male';
// $chrModelMap[28] = 'Broken Female';
// $chrModelMap[29] = 'Skeleton Male';
// $chrModelMap[30] = 'Skeleton (Fe)male';
// $chrModelMap[31] = 'Vrykul Male';
// $chrModelMap[32] = 'Vrykul (Fe)male';
// $chrModelMap[33] = 'Tuskarr Male';
// $chrModelMap[34] = 'Tuskarr Fe(male)';
// $chrModelMap[35] = 'Forest Troll Male';
// $chrModelMap[36] = 'Forest Troll (Fe)male';
// $chrModelMap[37] = 'Taunka Male';
// $chrModelMap[38] = 'Taunka (Fe)male';
// $chrModelMap[39] = 'Northrend Skeleton Male';
// $chrModelMap[40] = 'Northrend Skeleton (Fe)male';
// $chrModelMap[41] = 'Ice Troll Male';
// $chrModelMap[42] = 'Ice Troll (Fe)male';
$chrModelMap[43] = 'character/worgen/male/worgenmale';
$chrModelMap[44] = 'character/worgen/female/worgenfemale';
$chrModelMap[45] = 'character/human/male/humanmale_hd'; // Gilnean
$chrModelMap[46] = 'character/human/female/humanfemale_hd'; // Gilnean
$chrModelMap[47] = 'character/pandaren/male/pandarenmale';
$chrModelMap[48] = 'character/pandaren/female/pandarenfemale';
$chrModelMap[53] = 'character/nightborne/male/nightbornemale';
$chrModelMap[54] = 'character/nightborne/female/nightbornefemale';
$chrModelMap[55] = 'character/highmountaintauren/male/highmountaintaurenmale';
$chrModelMap[56] = 'character/highmountaintauren/female/highmountaintaurenfemale';
$chrModelMap[57] = 'character/voidelf/male/voidelfmale';
$chrModelMap[58] = 'character/voidelf/female/voidelffemale';
$chrModelMap[59] = 'character/lightforgeddraenei/male/lightforgeddraeneimale';
$chrModelMap[60] = 'character/lightforgeddraenei/female/lightforgeddraeneifemale';
$chrModelMap[61] = 'character/zandalaritroll/male/zandalaritrollmale';
$chrModelMap[62] = 'character/zandalaritroll/female/zandalaritrollfemale';
$chrModelMap[63] = 'character/kultiran/male/kultiranmale';
$chrModelMap[64] = 'character/kultiran/female/kultiranfemale';
// $chrModelMap[65] = 'Thin Human Male';
// $chrModelMap[66] = 'Thin Human (Fe)male';
$chrModelMap[67] = 'character/darkirondwarf/male/darkirondwarfmale';
$chrModelMap[68] = 'character/darkirondwarf/female/darkirondwarffemale';
$chrModelMap[69] = 'character/vulpera/male/vulperamale';
$chrModelMap[70] = 'character/vulpera/female/vulperafemale';
$chrModelMap[71] = 'character/orc/male/orcmale_hd';
$chrModelMap[72] = 'character/orc/female/orcfemale_hd';
$chrModelMap[73] = 'character/mechagnome/male/mechagnomemale';
$chrModelMap[74] = 'character/mechagnome/female/mechagnomefemale';

$unnamedBLPs = $pdo->query("SELECT id FROM wow_rootfiles WHERE type = 'blp' AND filename IS NULL")->fetchAll(PDO::FETCH_COLUMN);

$textureFileDataDB = getDBC("TextureFileData", $build);
$textureFileDataMap = [];
foreach($textureFileDataDB as $textureFileDataEntry){
	$textureFileDataMap[$textureFileDataEntry['MaterialResourcesID']] = $textureFileDataEntry['FileDataID'];
}

$chrCustomizationMaterialDB = getDBC("ChrCustomizationMaterial", $build);
$chrCustomizationMaterialFDIDMap = [];
foreach($chrCustomizationMaterialDB as $ChrCustomizationMaterialEntry){
	if(!array_key_exists($ChrCustomizationMaterialEntry['MaterialResourcesID'], $textureFileDataMap))
		continue;

	$chrCustomizationMaterialFDIDMap[$ChrCustomizationMaterialEntry['ID']] = $textureFileDataMap[$ChrCustomizationMaterialEntry['MaterialResourcesID']];
}

$chrCustomizationOptionDB = getDBC("ChrCustomizationOption", $build);
$chrCustomizationOptionMap = [];
foreach($chrCustomizationOptionDB as $chrCustomizationOptionEntry){
	$chrCustomizationOptionMap[$chrCustomizationOptionEntry['ID']] = $chrCustomizationOptionEntry;
}

$chrCustomizationChoiceDB = getDBC("ChrCustomizationChoice", $build);
$chrCustomizationChoiceMap = [];
foreach($chrCustomizationChoiceDB as $chrCustomizationChoiceEntry){
	$chrCustomizationChoiceMap[$chrCustomizationChoiceEntry['ID']] = $chrCustomizationChoiceEntry;
}

$chrCustomizationCategoryDB = getDBC("ChrCustomizationCategory", $build);
$chrCustomizationCategoryMap = [];
foreach($chrCustomizationCategoryDB as $chrCustomizationCategoryEntry){
	$chrCustomizationCategoryMap[$chrCustomizationCategoryEntry['ID']] = $chrCustomizationCategoryEntry;
}


$chrCustomizationElementDB = getDBC("ChrCustomizationElement", $build);

$doneFdids = [];
foreach($chrCustomizationElementDB as $chrCustElementEntry){
	if(empty($chrCustElementEntry['ChrCustomizationMaterialID']))
		continue;

	if(!in_array($chrCustomizationMaterialFDIDMap[$chrCustElementEntry['ChrCustomizationMaterialID']], $unnamedBLPs))
		continue;

	if(!array_key_exists($chrCustElementEntry['ChrCustomizationChoiceID'], $chrCustomizationChoiceMap))
		continue;

	$choice = $chrCustomizationChoiceMap[$chrCustElementEntry['ChrCustomizationChoiceID']];

	if(!array_key_exists($choice['ChrCustomizationOptionID'], $chrCustomizationOptionMap))
		continue;

	$option = $chrCustomizationOptionMap[$choice['ChrCustomizationOptionID']];
	if(!array_key_exists($option['ChrModelID'], $chrModelMap))
		continue;

	$modelPrefix = $chrModelMap[$option['ChrModelID']];
	$category = $chrCustomizationCategoryMap[$option['ChrCustomizationCategoryID']];
	$fdid = $chrCustomizationMaterialFDIDMap[$chrCustElementEntry['ChrCustomizationMaterialID']];

	if(in_array($fdid, $doneFdids))
		continue;

	$doneFdids[] = $fdid;

	echo $fdid . ";" . $modelPrefix . "_" . str_replace(" ", "_", strtolower($option['Name_lang'])) . "_" . $fdid . ".blp\n";
}
?>