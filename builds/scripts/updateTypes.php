<?php

if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");

include("../../inc/config.php");

while(true){
	$uq = $pdo->prepare("UPDATE wow_rootfiles SET type = :type WHERE id = :id");

	/* Known filenames */
	foreach($pdo->query("SELECT id, filename FROM wow_rootfiles WHERE type IS NULL AND filename IS NOT NULL OR type = 'unk' AND filename != '' ORDER BY id DESC") as $row){
		if($row['id'] == 841983) continue; // Skip signaturefile
		$ext = pathinfo($row['filename'], PATHINFO_EXTENSION);

		if($ext == "unk") continue;

		echo "Adding type ".$ext." for FileData ID " . $row['id']."\n";

		$uq->bindParam(":type", $ext);
		$uq->bindParam(":id", $row['id']);
		$uq->execute();
	}

	/* Known types */
	$modelFileData = $pdo->query("SELECT FileDataID FROM wowdata.modelfiledata")->fetchAll(PDO::FETCH_COLUMN);
	$textureFileData = $pdo->query("SELECT FileDataID FROM wowdata.texturefiledata")->fetchAll(PDO::FETCH_COLUMN);
	$movieFileData = $pdo->query("SELECT ID FROM wowdata.moviefiledata")->fetchAll(PDO::FETCH_COLUMN);
	$mp3Manifest = $pdo->query("SELECT ID FROM wowdata.manifestmp3")->fetchAll(PDO::FETCH_COLUMN);

	foreach($pdo->query("SELECT id, filename FROM wow_rootfiles WHERE type = 'unk'") as $file){
		if(in_array($file['id'], $modelFileData)){
			echo "File " . $file['id'] . " is a model!\n";
			$uq->bindValue(":type", "m2");
			$uq->bindParam(":id", $file['id']);
			$uq->execute();
		}

		if(in_array($file['id'], $textureFileData)){
			echo "File " . $file['id'] . " is a blp!\n";
			$uq->bindValue(":type", "blp");
			$uq->bindParam(":id", $file['id']);
			$uq->execute();
		}

		if(in_array($file['id'], $movieFileData)){
			echo "File " . $file['id'] . " is an avi!\n";
			$uq->bindValue(":type", "avi");
			$uq->bindParam(":id", $file['id']);
			$uq->execute();
		}

		if(in_array($file['id'], $mp3Manifest)){
			echo "File " . $file['id'] . " is an mp3!\n";
			$uq->bindValue(":type", "mp3");
			$uq->bindParam(":id", $file['id']);
			$uq->execute();
		}
	}
	/* Unknown filenames */
	$files = array();

	foreach($pdo->query("SELECT filedataid, wow_rootfiles_chashes.contenthash, wow_buildconfig.hash as buildconfig FROM wow_rootfiles_chashes LEFT JOIN wow_buildconfig on wow_buildconfig.root_cdn=wow_rootfiles_chashes.root_cdn WHERE filedataid IN (SELECT id FROM wow_rootfiles WHERE type = '' OR type IS NULL AND filename IS NULL) GROUP BY filedataid ORDER BY buildconfig ASC") as $row){
		$files[$row['buildconfig']][] = array("chash" => $row['contenthash'], "id" => $row['filedataid']);
	}

	foreach($files as $buildconfig => $filelist){
		$cdncq = $pdo->prepare("SELECT cdnconfig FROM wow_versions WHERE buildconfig = ?");
		$cdncq->execute([$buildconfig]);
		$cdnrow = $cdncq->fetch();

		if(empty($cdnrow)){
			die("Unable to locate CDNConfig for this build (".$buildconfig.")!");
		}

		if(!file_exists("/tmp/casc/".$buildconfig."/")){
			mkdir("/tmp/casc/".$buildconfig."/");
		}

		$toextract = 0;
		$extracted = 0;
		$fhandle = fopen("/tmp/casc/".$buildconfig.".txt", "w");
		foreach($filelist as $file){
			if(!file_exists("/tmp/casc/".$buildconfig."/".$file['id'].".unk")){
				fwrite($fhandle, $file['chash'].",".$file['id'].".unk\n");
				$toextract++;
			}else{
				$extracted++;
			}

			if($toextract > 500){
				break;
			}
		}
		fclose($fhandle);

		echo("Extracting " . $toextract . " unknown files (".$extracted ." already extracted) for buildconfig ".$buildconfig."\n");

		if($toextract > 0){
			$output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet /home/wow/buildbackup/BuildBackup.dll extractfilesbylist ".$buildconfig." ".$cdnrow['cdnconfig']." /tmp/casc/".$buildconfig."/ /tmp/casc/".$buildconfig.".txt");
		}

		foreach(glob("/tmp/casc/".$buildconfig."/*.unk") as $extractedfile){
			$ext = guessFileExtByExtractedFilename($extractedfile);
			if(empty($ext)){
				$ext = "unk";
			}

			$id = str_replace(array("/tmp/casc/".$buildconfig."/", ".unk"), "", $extractedfile);

			echo $id." is of type ".$ext."\n";
			$uq->bindValue(":type", str_replace(".", "", $ext));
			$uq->bindParam(":id", $id);
			$uq->execute();
			unlink($extractedfile);
		}

		$memcached->flush();
	}

	echo "[" . date('h:i:s'). "] Sleeping for 10 sec..\n";
	sleep(10);
}

function guessFileExtByExtractedFilename($name){
	$output = shell_exec("/usr/bin/file -b -i -m /var/www/wow.tools/builds/scripts/wow.mg ".escapeshellarg($name));
	$cleaned = str_replace("; charset=binary", "", $output);
	switch(trim($cleaned)){
		case "wow/blp2":
		$ext = ".blp";
		break;
		case "wow/m2/legacy":
		case "wow/m2":
		$ext = ".m2";
		break;
		case "wow/m2/skin":
		$ext = ".skin";
		break;
		case "wow/m2/bone":
		$ext = ".bone";
		break;
		case "wow/m2/phys":
		$ext = ".phys";
		break;
		case "wow/m2/anim":
		$ext = ".anim";
		break;
		case "wow/modelblob":
		$ext = ".blob";
		break;
		case "wow/tex":
		$ext = ".tex";
		break;
		case "wow/wdbc":
		$ext = ".dbc";
		break;
		case "wow/wdb2":
		case "wow/wdb3":
		case "wow/wdb4":
		case "wow/wdb5":
		case "wow/wdb6":
		case "wow/wdb7":
		case "wow/wdb8":
		case "wow/wdc1":
		case "wow/wdc2":
		case "wow/wdc3":
		case "wow/cls1":
		$ext = ".db2";
		break;
		case "wow/adt/root":
		$ext = ".adt";
		break;
		case "wow/adt/tex0":
		$ext = "_tex0.adt";
		break;
		case "wow/adt/tex1":
		$ext = "_tex1.adt";
		break;
		case "wow/adt/lod-fuddlewizz":
		case "wow/adt/obj":
		$ext = "_obj.adt";
		break;
		case "wow/adt/lod":
		$ext = "_lod.adt";
		break;
		case "wow/adt/dat":
		$ext = ".adt.dat";
		break;
		case "wow/wmo/root":
		$ext = ".wmo";
		break;
		case "wow/wmo/group":
		$ext = "_xxx.wmo";
		break;
		case "wow/bls":
		case "wow/bls/pixel":
		case "wow/bls/vertex":
		case "wow/bls/metal":
		$ext = ".bls";
		break;
		case "wow/wdt":
		case "wow/wdt/occ":
		case "wow/wdt/lgt":
		case "wow/wdt/lgt2":
		$ext = ".wdt";
		break;
		case "wow/adt/lod-doodaddefs":
		case "wow/wdl":
		$ext = ".wdl";
		break;
		case "wow/wwf":
		$ext = ".wwf";
		break;
		case "audio/mpeg":
		$ext = ".mp3";
		break;
		case "text/plist":
		$ext = ".plist";
		break;
		case "wow/sig":
		$ext = ".sig";
		break;
		case "wow/m2/skel":
		$ext = ".skel";
		break;
		case "text/xml";
		$ext = ".xml";
		break;
		case "audio/ogg":
		$ext = ".ogg";
		break;
		default:
		$ext = ".unk";
		break;
	}
	return $ext;
}

?>