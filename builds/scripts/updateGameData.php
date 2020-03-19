<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");

include(__DIR__ . "/../../inc/config.php");

$q = $pdo->query("SELECT description FROM wow_buildconfig WHERE product = 'wowt' AND ID > 1575 ORDER BY description DESC LIMIT 1");
$row = $q->fetch();

$rawdesc = str_replace("WOW-", "", $row['description']);
$build = substr($rawdesc, 0, 5);
$rawdesc = str_replace(array($build, "patch"), "", $rawdesc);
$descexpl = explode("_", $rawdesc);
$outdir = $descexpl[0].".".$build;

function importDB2($name, $outdir, $fields){
	global $pdo;
	$db2 = "http://127.0.0.1:5000/api/export/?name=".$name."&build=".$outdir."&t=".strtotime("now");
	$csv = "/tmp/".$name.".csv";
	if(file_exists($csv)){ unlink($csv); }
	$outputdump = shell_exec("/usr/bin/curl ".escapeshellarg($db2)." -o ".escapeshellarg($csv)." 2>&1");
	if(!file_exists($csv)){
		echo "An error occured during ".$name." import: ".$outputdump;
	}else{
		echo "	Writing ".$name." (".$outdir.")..";
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

importDB2("modelfiledata", $outdir, "(@FileDataID, @Flags, @LodCount, @ModelResourcesID) SET FileDataID = @FileDataID, ModelResourcesID = @ModelResourcesID");
importDB2("texturefiledata", $outdir, "(@FileDataID, @UsageType, @MaterialResourcesID)  SET FileDataID = @FileDataID, MaterialResourcesID = @MaterialResourcesID");
importDB2("moviefiledata", $outdir, "(ID)");
importDB2("manifestmp3", $outdir, "(ID)");
importDB2("soundkitname", $outdir, "(id, name)");
importDB2("soundkitentry", $outdir, "(@id, @soundkitid, @filedataid) SET id=@filedataid, entry=@soundkitid");
importDB2("manifestinterfacedata", $outdir, "(filedataid, path, name)");
importDB2("creaturemodeldata", $outdir, "(@id, @geobox1, @geobox2, @geobox3, @geobox4, @geobox5, @geobox6, @flags, @filedataid, @bloodid, @footprinttextureid, @footprinttexturelength, @footprinttexturewidth, @footprintparticlescale, @foleymaterialid, @footstepcameraeffectid, @deaththudcameraeffectid, @soundid) SET id=@id, filedataid=@filedataid, soundid=@soundid");
importDB2("creaturesounddata", $outdir, "(ID, SoundExertionID, SoundExertionCriticalID, SoundInjuryID, SoundInjuryCriticalID, SoundInjuryCrushingBlowID, SoundDeathID, SoundStunID, SoundStandID, SoundFootstepID, SoundAggroID, SoundWingFlapID, SoundWingGlideID, SoundAlertID, SoundJumpStartID, SoundJumpEndID, SoundPetAttackID, SoundPetOrderID, SoundPetDismissID, LoopSoundID, BirthSoundID, SpellCastDirectedSoundID, SubmergeSoundID, SubmergedSoundID, WindupSoundID, WindupCriticalSoundID, ChargeSoundID, ChargeCriticalSoundID, BattleShoutSoundID, BattleShoutCriticalSoundID, TauntSoundID, CreatureSoundDataIDPet, FidgetDelaySecondsMin, FidgetDelaySecondsMax, CreatureImpactType, NPCSoundID, `SoundFidget[0]`, `SoundFidget[1]`, `SoundFidget[2]`, `SoundFidget[3]`, `SoundFidget[4]`, `CustomAttack[0]`, `CustomAttack[1]`, `CustomAttack[2]`, `CustomAttack[3]`)");
importDB2("creaturedisplayinfo", $outdir, "(@ID, @ModelID, @SoundID, @SizeClass, @CreatureModelScale, @CreatureModelAlpha, @BloodID, @ExtendedDisplayInfoID, @NPCSoundID, @ParticleColorID, @PortraitCreatureDisplayInfoID, @PortraitTextureFileDataID, @ObjectEffectPackageID, @AnimReplacementSetID, @Flags, @StateSpellVisualKitID, @PlayerOverrideScale, @PetInstanceScale, @UnarmedWeaponType, @MountPoofSpellVisualKitID, @DissolveEffectID, @Gender, @DissolveOutEffectID, @CreatureModelMinLod, @TextureVariationFileDataID0, @TextureVariationFileDataID1, @TextureVariationFileDataID2) SET ID=@ID, ModelID=@ModelID, PortraitTextureFileDataID=@PortraitTextureFileDataID, `TextureVariationFileDataID[0]`=@TextureVariationFileDataID0, `TextureVariationFileDataID[1]` = @TextureVariationFileDataID1, `TextureVariationFileDataID[2]` = @TextureVariationFileDataID2");
importDB2("itemdisplayinfo", $outdir, "(@ID, @ItemVisual, @ParticleColorID, @ItemRangedDisplayInfoID, @OverrideSwooshSoundKitID, @SheatheTransformMatrixID, @StateSpellVisualKitID, @SheathedSpellVisualKitID, @UnsheathedSpellVisualKitID, @Flags, @ModelResourcesID0, @ModelResourcesID1, @ModelMaterialResourcesID0, @ModelMaterialResourcesID1) SET ID = @ID, ModelResourcesID0 = @ModelResourcesID0, ModelResourcesID1 = @ModelResourcesID1, ModelMaterialResourcesID0	= @ModelMaterialResourcesID0, ModelMaterialResourcesID1 = @ModelMaterialResourcesID1");
importDB2("componentmodelfiledata", $outdir, "(ID, GenderIndex, ClassID, RaceID, PositionIndex)");
importDB2("chrraces", $outdir, "(ClientPrefix, ClientFileString, Name_lang, Name_female_lang, Name_lowercase_lang, Name_female_lowercase_lang, ID)");
