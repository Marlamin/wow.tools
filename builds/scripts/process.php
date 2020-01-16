<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");

include(__DIR__ . "/../../inc/config.php");

$validargs = array("versions", "buildconfig", "buildconfiglong", "cdnconfig", "patchconfig");

if(empty($argv[1]) || !in_array(trim($argv[1]), $validargs)){
	echo "Valid commands: ".implode(", ", $validargs)."\n";
}else{
	if(!empty($argv[2])){
		if(!key_exists($argv[2], $allowedproducts)){
			die("Please select a product: ".implode(", ", array_keys($allowedproducts))."\n");
		}else{
			$products[$argv[2]] = $allowedproducts[$argv[2]];
		}
	}else{
		$products = $allowedproducts;
	}

	foreach($products as $code => $product){
		switch(trim($argv[1]))
		{
			case "versions":
			updateVersions($code);
			break;
			case "buildconfig":
			updateBuildConfig($code);
			break;
			case "buildconfiglong":
			updateBuildConfigLong($code);
			break;
			case "cdnconfig":
			updateCDNConfig($code);
			break;
			case "patchconfig":
			updatePatchConfig($code);
			break;
		}
	}
}

function updateVersions($product){
	global $pdo;

	$uq = $pdo->prepare("UPDATE ".$product."_versions SET cdnconfig = ? WHERE buildconfig = ?");
	$res = $pdo->query("SELECT * FROM ".$product."_versions");

	while($row = $res->fetch()){
		if(empty($row['cdnconfig'])){
			$updatedcdn = false;
			$incompleteconfigs = array();
			echo $row['buildconfig']." is without cdnconfig. Finding a match!\n";
			$cdnres = $pdo->prepare("SELECT * FROM ".$product."_cdnconfig WHERE builds LIKE :buildconfig");
			$cdnres->bindValue(":buildconfig", "%".$row['buildconfig']."%");
			$cdnres->execute();
			$cdnarr = $cdnres->fetchAll();
			if(count($cdnarr) > 0){
				foreach($cdnarr as $cdnrow){
					if($cdnrow['archivecount'] == $cdnrow['archivecomplete']){
						if($updatedcdn == false){
							$uq->execute([$cdnrow['hash'], $row['buildconfig']]);
							$updatedcdn = true;
						}
					}else{
						$idx = count($incompleteconfigs);
						$incompleteconfigs[$idx + 1]['complete'] = ($cdnrow['archivecomplete'] / $cdnrow['archivecount']) * 100;
						$incompleteconfigs[$idx + 1]['hash'] = $cdnrow['hash'];
					}
				}
			}

			if($updatedcdn == false){
				rsort($incompleteconfigs);
				if(count($incompleteconfigs) > 0 && $incompleteconfigs[0]['complete'] > 0){
					echo "Unable to find a complete cdnconfig for this build. Falling back to a non-complete one (".$incompleteconfigs[0]['hash'].", ".$incompleteconfigs[0]['complete']."% complete)!\n";
					$uq->execute([$incompleteconfigs[0]['hash'], $row['buildconfig']]);
				}else{
					echo "No non-complete cdnconfigs found, falling back to original one\n";
					$diffq = $pdo->prepare("SELECT * FROM ngdp_history WHERE newvalue LIKE :buildconfig LIMIT 1");
					$diffq->bindValue(":buildconfig", "%".$row['buildconfig']."%");
					$diffq->execute();
					$diffrow = $diffq->fetch();
					if(!empty($diffrow)){
						// TODO: Generic BPSV parser
						foreach(explode("\n", $diffrow['newvalue']) as $line){
							if(empty(trim($line))){
								continue;
							}

							$cols = explode("|", $line);

							if($cols[0] == "Region!STRING:0") {
								$headers = $cols;
								continue;
							}

							foreach($cols as $key => $col){
								$build[$headers[$key]] = $col;
							}
						}

						if(!empty($build['CDNConfig!HEX:16'])){
							$uq->execute([$build['CDNConfig!HEX:16'], $row['buildconfig']]);
						}

						unset($build);
					}
				}
			}
		}else{
			// CDNconfig is filled in, lets check if build is complete!

			// TODO: Somehow check if the archives available in the cdnconfig are enough for this build.
			//		 Maybe through dumping all keys from all available indexes and seeing if archive for that index is available
			//       Then checking whichever keys are left from uncoding AND aren't unarchived ones... hmm.

			// Select buildconfig
			$bcres = $pdo->prepare("SELECT * FROM ".$product."_buildconfig WHERE hash = ?");
			$bcres->execute([$row['buildconfig']]);
			$bcrow = $bcres->fetch();
			if(!empty($bcrow['product'])){
				if($row['product'] == "UNKNOWN"){
					echo "Unknown product for ID " . $row['id'] . ", backfilling to ".$bcrow['product']." from buildconfig!\n";
					$uvq = $pdo->prepare("UPDATE ".$product."_versions SET product = :product WHERE id = :id");
					$uvq->bindParam(":id", $row['id'], PDO::PARAM_INT);
					$uvq->bindParam(":product", $bcrow['product']);
					$uvq->execute();
				}
			}

			// Select CDNconfig -- unused?
			// $cdncrec = $pdo->prepare("SELECT * FROM ".$product."_cdnconfig WHERE hash = ?");
			// $cdncrec->query([$row['cdnconfig']]);
		}
	}
}

function updateBuildConfig($product){
	global $pdo;

	global $allowedproducts;
	$builds = array();

	$di = new RecursiveDirectoryIterator(__DIR__ . "/../../tpr/".$allowedproducts[$product]['cdndir']."/config",RecursiveDirectoryIterator::SKIP_DOTS);
	$it = new RecursiveIteratorIterator($di);

	$bcs = array();

	foreach($it as $file) {
		$type = trim(fgets(fopen($file, 'r')));
		switch($type){
			case "# Build Configuration":
			$bcs[] = parseConfig($file);
			break;
		}
	}

	foreach($bcs as $build){
		if(empty($build['build-uid'])){ $build['build-uid'] = "UNKNOWN"; }
		if(empty($build['build-name'])){ $build['build-name'] = "UNKNOWN"; }

		$existingBuild = getBuildConfigByBuildConfigHash($build['original-filename'], $product);

		if(!$existingBuild){
			if($product == "catalogs") $build['build-name'] = str_replace($build['root']."-", "", $build['build-name']);
			$iq = $pdo->prepare("INSERT INTO ".$product."_buildconfig (id, hash, description, product) VALUES (NULL, :hash, :description, :product)");
			$iq->bindParam(":hash", $build['original-filename']);
			$iq->bindParam(":description", $build['build-name']);
			$iq->bindParam(":product", $build['build-uid']);
			$iq->execute();
		}

		$existingBuild = getBuildConfigByBuildConfigHash($build['original-filename'], $product);

		if(empty($existingBuild['encoding']) || empty($existingBuild['encoding_cdn']) || empty($existingBuild['root']) || empty($existingBuild['install']) || empty($existingBuild['download']))){
			if($product == "catalogs"){
				if(empty($existingBuild['root_cdn'])){
					$ucq = $pdo->prepare("UPDATE ".$product."_buildconfig SET root_cdn = ? WHERE hash = ?");
					$ucq->execute([$build['root'], $build['original-filename']]);
				}
			}else{
				$encoding = explode(" ", $build['encoding']);
				$install = explode(" ", $build['install']);
				$download = explode(" ", $build['download']);
				if(!empty($build['size'])){
					$size = explode(" ", $build['size']);
				}

				$uq = $pdo->prepare("UPDATE ".$product."_buildconfig SET
					encoding = :encoding,
					encoding_cdn = :encoding_cdn,
					root = :root,
					install = :install,
					install_cdn = :install_cdn,
					download = :download,
					download_cdn = :download_cdn,
					size = :size,
					size_cdn = :size_cdn
					WHERE hash = :hash");
				$uq->bindParam(":encoding", $encoding[0]);
				$uq->bindParam(":encoding_cdn", $encoding[1]);
				$uq->bindParam(":root", $build['root']);
				$uq->bindParam(":install", $install[0]);

				if(count($install) > 1){
					$uq->bindParam(":install_cdn", $install[1]);
				}else{
					$uq->bindValue(":install_cdn", null, PDO::PARAM_NULL);
				}

				$uq->bindParam(":download", $download[0]);
				if(count($download) > 1){
					$uq->bindParam(":download_cdn", $download[1]);
				}else{
					$uq->bindValue(":download_cdn", null, PDO::PARAM_NULL);
				}

				// Size is optional and not present in all products
				if(isset($size)){
					if(!empty($size[0])){
						$uq->bindParam(":size", $size[0]);
						if(count($size) > 1){
							$uq->bindParam(":size_cdn", $size[1]);
						}else{
							$uq->bindValue(":size_cdn", null, PDO::PARAM_NULL);
						}
					}else{
						$uq->bindValue(":size", null, PDO::PARAM_NULL);
					}
				}else{
					$uq->bindValue(":size", null, PDO::PARAM_NULL);
					$uq->bindValue(":size_cdn", null, PDO::PARAM_NULL);
				}

				$uq->bindParam(":hash", $build['original-filename']);
				$uq->execute();

				unset($size);
			}
		}

		if(!empty($existingBuild['unarchived']) && ($existingBuild['unarchivedcount'] != $existingBuild['unarchivedcomplete'])){
			$unarchived = explode(" ", trim($existingBuild['unarchived']));
			$unarchivedcount = count($unarchived);
			$unarchivedcomplete = 0;

			foreach($unarchived as $unarchivedfile){
				if(doesFileExist("data", $unarchivedfile, $allowedproducts[$product]['cdndir'])){
					$unarchivedcomplete++;
				}
			}

			if(($existingBuild['unarchivedcomplete'] != $unarchivedcomplete) || ($existingBuild['unarchivedcount'] != $unarchivedcount)){
				echo "Adjusting unarchived file status from ".$existingBuild['unarchivedcomplete']."/".$existingBuild['unarchivedcount']." to ".$unarchivedcomplete."/".$unarchivedcount.".. \n";

				$unq = $pdo->prepare("UPDATE ".$product."_buildconfig
					SET
					unarchivedcount = :unarchivedcount,
					unarchivedcomplete = :unarchivedcomplete
					WHERE hash = :hash");
				$unq->bindParam(":unarchivedcount", $unarchivedcount);
				$unq->bindParam(":unarchivedcomplete", $unarchivedcomplete);
				$unq->bindParam(":hash", $build['original-filename']);
				$unq->execute();
			}
		}

		if($existingBuild['description'] == "UNKNOWN" || $existingBuild['product'] == "UNKNOWN" || strpos($existingBuild['description'], 'prometheus') !== false){
			$diffq = $pdo->prepare("SELECT * FROM `wow`.`URLHistory` WHERE newvalue LIKE :hash ORDER BY timestamp DESC LIMIT 1");
			$diffq->bindParam(":hash", "%".$build['original-filename']."%");
			$diffq->execute();
			$diffrow = $diffq->fetch();

			if(!empty($diffrow)){
				$i = 0;
				foreach(explode("\n", $diffrow['newvalue']) as $line){
					if(empty(trim($line))){
						continue;
					}

					$cols = explode("|", $line);

					if($cols[0] == "Region!STRING:0") {
						$headers = $cols;
						continue;
					}

					foreach($cols as $key => $col){
						$versionsfile[$i][$headers[$key]] = $col;
					}
					$i++;
				}
			}
		}

		if($product == "wow" && (empty($existingBuild['patch']) || empty($existingBuild['patchconfig']))){
			if(!empty($build['patch-config'])){
				echo "Patch config ".$build['patch-config']." is set for ".$build['original-filename']."\n";
				$puq = $pdo->prepare("UPDATE ".$product."_buildconfig SET patchconfig = :patchconfig, patch = :patch WHERE hash = :hash");
				$puq->bindParam(":patchconfig", $build['patch-config']);
				$puq->bindParam(":patch", $build['patch']);
				$puq->bindParam(":hash", $build['original-filename']);
				$puq->execute();
			}
		}

		$versionres = $pdo->prepare("SELECT COUNT(*) FROM ".$product."_versions WHERE buildconfig = :hash");
		$versionres->bindParam(":hash", $build['original-filename']);
		$versionres->execute();

		if($versionres->fetchColumn() === 0){
			// Buildconfig doesn't exist yet!
			echo "Inserting new build ".$build['original-filename']." (".$build['build-uid']." ".$build['build-name'].")\n";
			$biq = $pdo->prepare("INSERT INTO ".$product."_versions (buildconfig, product) VALUES (:hash, :product)");
			$biq->bindParam(":hash", $build['original-filename']);
			$biq->bindParam(":product", $build['build-uid']);
			$biq->execute();
		}

		if($product == "wow" && !empty($build['patch-config'])){
			$pq = $pdo->prepare("SELECT patchconfig FROM ".$product."_versions WHERE buildconfig = :hash AND patchconfig IS NOT NULL");
			$pq->bindParam(":hash", $build['original-filename']);
			$pq->execute();

			if(count($pq->fetchAll()) == 0){
				$puq = $pdo->prepare("UPDATE ".$product."_versions SET patchconfig = :patchconfig WHERE buildconfig = :hash");
				$puq->bindParam(":patchconfig", $build['patch-config']);
				$puq->bindParam(":hash", $build['original-filename']);
				$puq->execute();
			}
		}
	}
}

function updateBuildConfigLong($product){
	global $pdo;
	global $allowedproducts;

	// Catalogs doesn't need additional parsing
	if($product === "catalogs") return;

	$res = $pdo->query("SELECT * FROM ".$product."_versions WHERE cdnconfig IS NOT NULL AND buildconfig IS NOT NULL");
	while($row = $res->fetch()){
		// Skip builds that encoding does not exist for
		$encodingres = $pdo->prepare("SELECT encoding_cdn, install_cdn, unarchived FROM ".$product."_buildconfig WHERE hash = :hash");
		$encodingres->bindParam(":hash", $row['buildconfig']);
		$encodingres->execute();
		$encodingrow = $encodingres->fetch();

		if(empty($encodingrow['encoding_cdn'])){
			echo "[".$product."] Skipping build " . $row['buildconfig'] . ", encoding file not set!\n";
			continue;
		}

		if(!doesFileExist("data", $encodingrow['encoding_cdn'], $allowedproducts[$product]['cdndir'])){
			echo "[".$product."] Skipping build " . $row['buildconfig'] . ", encoding file " . $encodingrow['encoding_cdn'] . " not found!\n";
			continue;
		}

		// Skip builds that cdnconfig does not exist for
		if(!doesFileExist("config", $row['cdnconfig'], $allowedproducts[$product]['cdndir'])) continue;

		// Already done
		if(!empty($encodingrow['install_cdn']) && !empty($encodingrow['unarchived'])){
			continue;
		}

		echo "[".$product."] Processing new build ". $row['buildconfig'] . "...\n";

		// Create array for unarchived files
		$unarchived = array();
		$unarchivedcomplete = 0;

		// Run buildbackup tool in dump mode
		$output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet /home/wow/buildbackup/BuildBackup.dll dumpinfo ".$product." ".$row['buildconfig']." ".$row['cdnconfig']);
		if(!$output){ echo "\n\nEncountered exception, skipping build.. \n\n\n"; continue; }
		foreach(explode("\n", $output) as $line){
			if(empty(trim($line))) continue;
				//echo $line."\n";
			if(strpos($line, "root = ") !== false){
				$exploded = explode("root = ", $line);
				$file = $exploded[1];
				$uq = $pdo->prepare("UPDATE ".$product."_buildconfig SET root_cdn = ? WHERE hash = ?");
				$uq->execute([$file, $row['buildconfig']]);
			}

			if(strpos($line, "install = ") !== false){
				$exploded = explode("install = ", $line);
				$file = $exploded[1];
				$uq = $pdo->prepare("UPDATE ".$product."_buildconfig SET install_cdn = ? WHERE hash = ?");
				$uq->execute([$file, $row['buildconfig']]);
			}

			if(strpos($line, "download = ") !== false){
				$exploded = explode("download = ", $line);
				$file = $exploded[1];
				$uq = $pdo->prepare("UPDATE ".$product."_buildconfig SET download_cdn = ? WHERE hash = ?");
				$uq->execute([$file, $row['buildconfig']]);
			}

			if(strpos($line, "unarchived = ") !== false){
				$exploded = explode("unarchived = ", $line);
				$unarchived[] = $exploded[1];
				if(doesFileExist("data", $exploded[1], $product)){
					$unarchivedcomplete++;
				}
			}
		}

		$uuq = $pdo->prepare("UPDATE ".$product."_buildconfig
			SET
			unarchived = '".implode(" ", $unarchived)."',
			unarchivedcount = :unarchivedcount,
			unarchivedcomplete = :unarchivedcomplete
			WHERE hash = :hash");
		$uuq->bindValue(":unarchivedcount", count($unarchived));
		$uuq->bindParam(":unarchivedcomplete", $unarchivedcomplete);
		$uuq->bindParam(":hash", $row['buildconfig']);
		$uuq->execute();
		// insert unarchived
		unset($unarchivedcomplete);
		unset($unarchived);
	}
}

function updatePatchConfig($product){
	global $pdo;
	global $allowedproducts;
	if($product != "wow") return;
	$di = new RecursiveDirectoryIterator(__DIR__ . "/../../tpr/".$allowedproducts[$product]['cdndir']."/config",RecursiveDirectoryIterator::SKIP_DOTS);
	$it = new RecursiveIteratorIterator($di);

	$pcs = array();

	foreach($it as $file) {
		$type = trim(fgets(fopen($file, 'r')));
		switch($type){
			case "# Patch Configuration":
			$pcs[] = parseConfig($file);
			break;
		}
	}

	foreach($pcs as $pc){
		$q = $pdo->prepare("SELECT * FROM ".$product."_patchconfig WHERE hash = :hash");
		$q->bindParam(":hash", $pc['original-filename']);
		$q->execute();
		if(empty($q->fetch())){
			if(empty($pc['patch-size'])) $pc['patch-size'] = 0;
			$iq = $pdo->prepare("INSERT INTO ".$product."_patchconfig (hash, patch, `patch-entry`, `patch-size`)
				VALUES (:hash, :patch, :patchentry, :patchsize)");
			$iq->bindParam(":hash", $pc['original-filename']);
			$iq->bindParam(":patch", $pc['patch']);
			$iq->bindValue(":patchentry", json_encode($pc['patch-entry']));
			$iq->bindParam(":patchsize", $pc['patch-size']);
			$iq->execute();
		}
	}
}

function updateCDNConfig($product){
	global $pdo;
	global $allowedproducts;

	$builds = array();

	$di = new RecursiveDirectoryIterator(__DIR__ . "/../../tpr/".$allowedproducts[$product]['cdndir']."/config",RecursiveDirectoryIterator::SKIP_DOTS);
	$it = new RecursiveIteratorIterator($di);

	$cdncs = array();

	foreach($it as $file) {
		$type = trim(fgets(fopen($file, 'r')));
		switch($type){
			case "# CDN Configuration":
			$cdncs[] = parseConfig($file);
			break;
		}
	}

	foreach($cdncs as $cdn){
		$archivecount = 0;
		$archivecomplete = 0;
		$indexcomplete = 0;

		if(!empty($cdn['archives'])){
			$archivearray = explode(" ", trim($cdn['archives']));

			$archivecount = count($archivearray);
			foreach($archivearray as $archive){
				if(doesFileExist("data", $archive, $allowedproducts[$product]['cdndir'])){
					$archivecomplete++;
				}else{

				}
				if(doesFileExist("data", $archive.".index", $allowedproducts[$product]['cdndir'])){
					$indexcomplete++;
				}
			}
		}else{
			$cdn['archives'] = "";
		}

		$patcharchivecount = 0;
		$patcharchivecomplete = 0;
		$patchindexcomplete = 0;

		if(!empty($cdn['patch-archives'])){
			$patcharchivearray = explode(" ", trim($cdn['patch-archives']));

			$patcharchivecount = count($patcharchivearray);

			foreach($patcharchivearray as $patcharchive){
				if(doesFileExist("patch", $patcharchive, $allowedproducts[$product]['cdndir'])){
					$patcharchivecomplete++;
				}
				if(doesFileExist("patch", $patcharchive.".index", $allowedproducts[$product]['cdndir'])){
					$patchindexcomplete++;
				}
			}
		}else{
			$cdn['patch-archives'] = NULL;
		}

		if(!isset($cdn['builds'])){
			$cdn['builds'] = NULL;
		}

		$existingConfig = getCDNConfigbyCDNConfigHash($cdn['original-filename'], $product);

		if(!$existingConfig){
			echo "Inserting CDN config ".$cdn['original-filename']."..\n";
			$iq = $pdo->prepare("
				INSERT INTO ".$product."_cdnconfig (hash, archives, archivecount, archivecomplete, indexcomplete, patcharchives, patcharchivecount, patcharchivecomplete, patchindexcomplete, builds)
				VALUES (
				:hash,
				:archives,
				:archivecount,
				:archivecomplete,
				:indexcomplete,
				:patcharchives,
				:patcharchivecount,
				:patcharchivecomplete,
				:patchindexcomplete,
				:builds
			)");

			$iq->bindParam(":hash", $cdn['original-filename']);
			$iq->bindParam(":archives", $cdn['archives']);
			$iq->bindParam(":archivecount", $archivecount);
			$iq->bindParam(":archivecomplete", $archivecomplete);
			$iq->bindParam(":indexcomplete", $indexcomplete);
			$iq->bindParam(":patcharchives", $cdn['patch-archives']);
			$iq->bindParam(":patcharchivecount", $patcharchivecount);
			$iq->bindParam(":patcharchivecomplete", $patcharchivecomplete);
			$iq->bindParam(":patchindexcomplete", $patchindexcomplete);
			$iq->bindParam(":builds", $cdn['builds']);
			$iq->execute();
		}else{
			if(
				$existingConfig['archives'] == $cdn['archives'] &&
				$existingConfig['archivecount'] == $archivecount &&
				$existingConfig['archivecomplete'] == $archivecomplete &&
				$existingConfig['indexcomplete'] == $indexcomplete &&
				$existingConfig['patcharchives'] == $cdn['patch-archives'] &&
				$existingConfig['patcharchivecount'] == $patcharchivecount &&
				$existingConfig['patcharchivecomplete'] == $patcharchivecomplete &&
				$existingConfig['patchindexcomplete'] == $patchindexcomplete &&
				$existingConfig['builds'] == $cdn['builds']
			){
				continue;
			}

			echo "Updating CDN config ".$cdn['original-filename']."..\n";
			$uq = $pdo->prepare("
				UPDATE ".$product."_cdnconfig SET
				archives = :archives,
				archivecount = :archivecount,
				archivecomplete = :archivecomplete,
				indexcomplete = :indexcomplete,
				patcharchives = :patcharchives,
				patcharchivecount = :patcharchivecount,
				patcharchivecomplete = :patcharchivecomplete,
				patchindexcomplete = :patchindexcomplete,
				builds = :builds
				WHERE hash = :hash");

			$uq->bindParam(":archives", $cdn['archives']);
			$uq->bindParam(":archivecount", $archivecount);
			$uq->bindParam(":archivecomplete", $archivecomplete);
			$uq->bindParam(":indexcomplete", $indexcomplete);
			$uq->bindParam(":patcharchives", $cdn['patch-archives']);
			$uq->bindParam(":patcharchivecount", $patcharchivecount);
			$uq->bindParam(":patcharchivecomplete", $patcharchivecomplete);
			$uq->bindParam(":patchindexcomplete", $patchindexcomplete);
			$uq->bindParam(":builds", $cdn['builds']);
			$uq->bindParam(":hash", $cdn['original-filename']);
			$uq->execute();
		}
	}
}
?>