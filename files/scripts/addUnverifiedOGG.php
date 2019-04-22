<?php
include("../../inc/config.php");

if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");

$q = $pdo->query("SELECT soundkitentry.id as filedataid, soundkitentry.entry as entry, soundkitname.name as name FROM `wowdata`.soundkitentry INNER JOIN `wowdata`.soundkitname ON soundkitentry.entry=`wowdata`.soundkitname.id WHERE soundkitentry.id IN (SELECT id FROM `casc`.wow_rootfiles WHERE filename IS NULL)");

$files = array();
$globnameindex = [];
while($row = $q->fetch()){
	$row['name'] = strtolower($row['name']);
	if(substr($row['name'], 0, 3) == "vo_"){
		if(substr($row['name'], -2, 2) == "_f" || substr($row['name'], -2, 2) == "_m"){
			$isNumericVO = true;
		}else{
			$isNumericVO = false;
		}

		if(strpos($row['name'], '_pc_') !== false){
			// character vo
		}else{
			// creature vo
			if($isNumericVO){
				$cleaned = str_replace(array("vo_82_", "vo_815_", "vo_81_", "vo_801_", "vo_735_"), "", substr($row['name'], 0, -2));

				if(is_numeric(substr($cleaned, -3, 3))){
					// _xxx
					$cleaned = substr($cleaned, 0, -4);
				}elseif(is_numeric(substr($cleaned, -2, 2))){
					// _xx
					$cleaned = substr($cleaned, 0, -3);
				}else{
					echo "	Don't know how to handle this numeric vo: " . $row['name']."\n";
				}

				$guess = "sound/creature/" . $cleaned . "/" . $row['name'] . ".ogg";

				//$files[$row['filedataid']] = $guess;
			}else{
				$vosuffixes = array("aggro", "attack", "attack_ranged", "attackcrit", "attackcrit_ranged", "battle_cry", "battle_shout", "battleshout", "battlecry", "battleroar", "battleshout", "charge", "chuff", "clickable", "clickables", "death", "emerge", "idle", "idle_loop", "injury", "laugh", "preaggro", "preagro", "stand", "wound", "wound_crit", "woundcrit", "loop");

				$exploded = explode("_", $row['name']);

				$cleaned = str_replace(array("vo_82_", "vo_815_", "vo_81_", "vo_801_", "vo_735_", "_f", "_m"), "", $row['name']);

				if(!array_key_exists($row['name'], $globnameindex)){
					$globnameindex[$row['name']] = 1;
				}
				if(in_array($exploded[count($exploded) - 1], $vosuffixes)){
					$cleaned = str_replace("_". $exploded[count($exploded) - 1], "", $cleaned);
					$files[$row['filedataid']] = "sound/creature/" . $cleaned . "/" . $row['name'] . "_" . str_pad($globnameindex[$row['name']]++, 2, '0', STR_PAD_LEFT). ".ogg";
				}
			}
		}
	}else if(substr($row['name'], 0, 4) == "mon_"){
		// mon
		$vosuffixes = array("aggro", "attack", "attack_ranged", "attackcrit", "attackcrit_ranged", "battle_cry", "battle_shout", "battleshout", "battlecry", "battleroar", "battleshout", "charge", "chuff", "clickable", "clickables", "death", "emerge", "idle", "idle_loop", "injury", "laugh", "preaggro", "preagro", "stand", "wound", "wound_crit", "woundcrit", "loop");

		$exploded = explode("_", $row['name']);

		$cleaned = str_replace(array("mon_8.0_", "mon_8.2_", "mon_82_", "mon_815", "mon_81_", "mon_80_", "mon_"), "", $row['name']);
		//echo $cleaned."\n";
		if(in_array($exploded[count($exploded) - 1], $vosuffixes)){
			$cleaned = str_replace("_". $exploded[count($exploded) - 1], "", $cleaned);
			if(!array_key_exists($row['name'], $globnameindex)){
				$globnameindex[$row['name']] = 1;
			}
			$files[$row['filedataid']] = "sound/creature/" . $cleaned . "/" . $row['name'] . "_" . str_pad($globnameindex[$row['name']]++, 2, '0', STR_PAD_LEFT). ".ogg";
		}
	}else if(substr($row['name'], 0, 4) == "amb_"){
		// zone ambience
	}else if(substr($row['name'], 0, 5) == "spell"){
		// spell sounds
	}else if(substr($row['name'], 0, 4) == "mus_"){
		// muzak
	}else if(substr($row['name'], 0, 3) == "fx_"){
		// fx
		for($i = 0; $i < 99; $i++){
			// $files[$row['filedataid']] = "sound/spells/" . $row['name'] . "_" . str_pad($i, 2, '0', STR_PAD_LEFT). ".ogg";
			// $files[$row['filedataid']] = "sound/spells/" . $row['name'] . "" . str_pad($i, 2, '0', STR_PAD_LEFT). ".ogg";
		}
	}else if(substr($row['name'], 0, 3) == "go_"){
		// gameobject sounds
		for($i = 0; $i < 99; $i++){
			// $files[$row['filedataid']] = "sound/doodad/" . $row['name'] . "_" . str_pad($i, 2, '0', STR_PAD_LEFT). ".ogg";
			// $files[$row['filedataid']] = "sound/doodad/" . $row['name'] . "" . str_pad($i, 2, '0', STR_PAD_LEFT). ".ogg";
		}
	}else{
		//echo "Don't know how to handle SoundKitName " . $row['name'] . "!\n";
	}
}

$cq = $pdo->prepare("SELECT filename FROM wow_rootfiles WHERE id = :id AND verified = 0");
$iq = $pdo->prepare("UPDATE wow_rootfiles SET filename = ? WHERE id = ? AND verified = 0");
$fq = $pdo->prepare("SELECT filename FROM wow_rootfiles WHERE filename = ?");
foreach($files as $filedataid => $filename){
	$cq->execute([$filedataid]);
	if(empty($cq->fetch()['filename'])){
		$fq->execute([$filename]);
		if(count($fq->fetchAll()) == 0){
			echo "Updating ".$filedataid." ".$filename."\n";
			$iq->execute([$filename, $filedataid]);
		}else{
			echo "Ignoring as duplicate ".$filedataid." ".$filename."\n";
		}
	}
}

flushQueryCache();