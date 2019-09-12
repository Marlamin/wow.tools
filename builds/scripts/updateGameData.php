<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");

include("../../inc/config.php");

$q = $pdo->query("SELECT description FROM wow_buildconfig WHERE product = 'wowt' ORDER BY description DESC LIMIT 1");
$row = $q->fetch();

$rawdesc = str_replace("WOW-", "", $row['description']);
$build = substr($rawdesc, 0, 5);
$rawdesc = str_replace(array($build, "patch"), "", $rawdesc);
$descexpl = explode("_", $rawdesc);
$outdir = $descexpl[0].".".$build;

function importDB2($name, $outdir, $fields){
	global $pdo;
	$db2 = "https://wow.tools/api/export/?name=".$name."&build=".$outdir;
	$csv = "/tmp/".$name.".csv";
	if(file_exists($csv)){ unlink($csv); }
	$outputdump = shell_exec("/usr/bin/curl ".escapeshellarg($db2)." -o ".escapeshellarg($csv)." 2>&1");
	if(!file_exists($csv)){
		echo "An error occured during ".$name." import: ".$outputdump;
	}else{
		echo "	Writing ".$name."..";
		$pdo->exec("
			LOAD DATA LOCAL INFILE '".$csv."'
			INTO TABLE `wowdata`.".$name."
			FIELDS TERMINATED BY ',' ESCAPED BY '\b'
			LINES TERMINATED BY '\n'
			IGNORE 1 LINES
			".$fields."
		");
		echo "..done!\n";
	}
}

importDB2("modelfiledata", $outdir, "(FileDataID)");
importDB2("texturefiledata", $outdir, "(FileDataID)");
importDB2("moviefiledata", $outdir, "(ID)");
importDB2("soundkitname", $outdir, "(id, name)");
importDB2("soundkitentry", $outdir, "(@id, @soundkitid, @filedataid) SET id=@filedataid, entry=@soundkitid");
importDB2("manifestinterfacedata", $outdir, "(filedataid, path, name)");
importDB2("creaturemodeldata", $outdir, "(@id, @geobox1, @geobox2, @geobox3, @geobox4, @geobox5, @geobox6, @flags, @filedataid, @bloodid, @footprinttextureid, @footprinttexturelength, @footprinttexturewidth, @footprintparticlescale, @foleymaterialid, @footstepcameraeffectid, @deaththudcameraeffectid, @soundid) SET id=@id, filedataid=@filedataid, soundid=@soundid");
importDB2("creaturesounddata", $outdir, "(ID, SoundExertionID, SoundExertionCriticalID, SoundInjuryID, SoundInjuryCriticalID, SoundInjuryCrushingBlowID, SoundDeathID, SoundStunID, SoundStandID, SoundFootstepID, SoundAggroID, SoundWingFlapID, SoundWingGlideID, SoundAlertID, SoundJumpStartID, SoundJumpEndID, SoundPetAttackID, SoundPetOrderID, SoundPetDismissID, LoopSoundID, BirthSoundID, SpellCastDirectedSoundID, SubmergeSoundID, SubmergedSoundID, WindupSoundID, WindupCriticalSoundID, ChargeSoundID, ChargeCriticalSoundID, BattleShoutSoundID, BattleShoutCriticalSoundID, TauntSoundID, CreatureSoundDataIDPet, FidgetDelaySecondsMin, FidgetDelaySecondsMax, CreatureImpactType, NPCSoundID, `SoundFidget[0]`, `SoundFidget[1]`, `SoundFidget[2]`, `SoundFidget[3]`, `SoundFidget[4]`, `CustomAttack[0]`, `CustomAttack[1]`, `CustomAttack[2]`, `CustomAttack[3]`)");