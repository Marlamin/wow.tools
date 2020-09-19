<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");

include(__DIR__ . "/../../inc/config.php");

if(empty($argv) || count($argv) < 3){
	die("Not enough arguments");
}

$cdnc1 = getCDNConfigByCDNConfigHash($argv[1]);
$cdnc2 = getCDNConfigByCDNConfigHash($argv[2]);

if(empty($cdnc1) || empty($cdnc2))
	die("Invalid configs!");

$config1 = parseConfig("/var/www/wow.tools/" . generateURL("config", $cdnc1['hash']));
$config2 = parseConfig("/var/www/wow.tools/" . generateURL("config", $cdnc2['hash']));

if($config1['file-index'] === $config2['file-index'])
	die("File indices match");

echo "Dumping file-index entries..\n";
$output1 = shell_exec("cd /home/wow/buildbackup/; dotnet BuildBackup.dll dumpindex wow ".$config1['file-index']);
$output2 = shell_exec("cd /home/wow/buildbackup/; dotnet BuildBackup.dll dumpindex wow ".$config2['file-index']);

$expl1 = explode("\n", $output1);
$expl2 = explode("\n", $output2);

$entries1 = array();
foreach($expl1 as $line){
	$entries1[] = strtolower(explode(" ", $line)[0]);
}

$entries2 = array();
foreach($expl2 as $line){
	$entries2[] = strtolower(explode(" ", $line)[0]);
}

$results = array();
$addedFiles = array_merge(array_diff($entries1, $entries2), array_diff($entries2, $entries1));

echo "Checking magic for " . count($addedFiles) . " files..\n";

$checkQ = $pdo->prepare("SELECT * FROM wow_buildconfig WHERE install_cdn = ? OR encoding_cdn = ? OR root_cdn = ? OR download_cdn = ?");
foreach($addedFiles as $addedFile){
	// Check if file already belongs to existing buildconfig
	$checkQ->execute([$addedFile, $addedFile, $addedFile, $addedFile]);
	if(!empty($checkQ->fetch())){
		//continue;
	}

	// Identify file based on magic
	$path = "/var/www/wow.tools/".generateURL("data", $addedFile);
	$firstChars = shell_exec("cd /home/wow/buildbackup/; dotnet BuildBackup.dll dumprawfile ".$path." 2");
	switch($firstChars){
		case "TS": // TSFM: Root
			$results[$addedFile] = "root_cdn";
			break;
		case "EN": // EN: Encoding
			$results[$addedFile] = "encoding_cdn";
			break;
		case "IN": // IN: Install
			$results[$addedFile] = "install_cdn";
			break;
		case "DL": // DL: Download
			$results[$addedFile] = "download_cdn";
			break;
		default:
			// echo "Unknown magic for file ".$addedFile.": " . $firstChars."\n";
			break;
	}
}

echo "Found these results: \n".print_r($results, true);

$requiredTypes = ["root_cdn", "encoding_cdn", "install_cdn", "download_cdn"];

$build = array();
foreach($results as $key => $type){
	if(in_array($type, $requiredTypes)){
		if(!empty($build[$type])){
			die("Already have a " . $type . " file for this half-push, needs manual checking.");
		}else{
			$build[$type] = $key;
		}
	}
}

if(empty($build['root_cdn']) || empty($build['encoding_cdn']) || empty($build['install_cdn']) || empty($build['download_cdn'])){
	die("Don't have enough to generate a build config.");
}

echo "Generating MD5 contenthashes for BLTE-decoded build files..\n";
foreach($requiredTypes as $type){
	$tempName = tempnam("/tmp", "halfpush");
	$path = "/var/www/wow.tools/".generateURL("data", $build[$type]);
	shell_exec("cd /home/wow/buildbackup/; dotnet BuildBackup.dll dumprawfiletofile ".$path." ".$tempName);
	$build[str_replace("_cdn", "", $type)] = md5_file($tempName);
	$build[str_replace("_cdn", "_size", $type)] = filesize($tempName);
	unlink($tempName);
}


// Patch stuff
echo "Dumping patch-file-index entries..\n";
$output1 = shell_exec("cd /home/wow/buildbackup/; dotnet BuildBackup.dll dumpindex wow ".$config1['patch-file-index']." patch");
$output2 = shell_exec("cd /home/wow/buildbackup/; dotnet BuildBackup.dll dumpindex wow ".$config2['patch-file-index']." patch");

$expl1 = explode("\n", $output1);
$expl2 = explode("\n", $output2);

$entries1 = array();
foreach($expl1 as $line){
	$entries1[] = strtolower(explode(" ", $line)[0]);
}

$entries2 = array();
foreach($expl2 as $line){
	$entries2[] = strtolower(explode(" ", $line)[0]);
}

$addedFiles = array_merge(array_diff($entries1, $entries2), array_diff($entries2, $entries1));

echo "Checking magic for " . count($addedFiles) . " patch files..\n";

$patchCheckQ = $pdo->prepare("SELECT * FROM wow_buildconfig WHERE patch = ?");
foreach($addedFiles as $addedFile){
	// Check if file already belongs to existing buildconfig
	$patchCheckQ->execute([$addedFile]);
	if(!empty($patchCheckQ->fetch())){
		//continue;
	}

	$path = "/var/www/wow.tools/".generateURL("patch", $addedFile);
	$firstChars = shell_exec("head -c 2 ".$path);
	switch($firstChars){
		case "ZB": // ZBSDIFF
			break;
		case "PA":
			if(!empty($build['patch'])){
				die("Already have a patch file for this half-push, needs manual checking.");
			}else{
				$build['patch'] = $addedFile;
			}
		default:
			echo "Unknown magic for file ".$addedFile.": " . $firstChars."\n";
			break;
	}
}

echo "Finding espec for build files in encoding..\n";
$espec = array();
$output = shell_exec("cd /home/wow/buildbackup/; dotnet BuildBackup.dll dumpencoding wow ".$build['encoding_cdn']);
foreach(explode("\n", $output) as $line){
	$entry = explode(" ", $line);
	if($entry[0] == "ENCODINGESPEC"){
		$espec["encoding"] = $entry[1];
	}

	if($entry[0] == $build['download'])
		$espec["download"] = $entry[4];

	if($entry[0] == $build['install'])
		$espec["install"] = $entry[4];

	// TODO: SIZE
}

print_r($espec);

$pclines = array();
$pclines[] = "# Patch Configuration";
$pclines[] = "";
$pclines[] = "patch-entry = install " . $build['install'] . " " . $build['install_size'] . " " . $build['install_cdn'] . " " . filesize("/var/www/wow.tools/".generateURL("data", $build['install_cdn']))." ".$espec["install"];
$pclines[] = "patch-entry = encoding " . $build['encoding'] . " " . $build['encoding_size'] . " " . $build['encoding_cdn'] . " " . filesize("/var/www/wow.tools/".generateURL("data", $build['encoding_cdn']))." ".$espec["encoding"];
$pclines[] = "patch-entry = size TODO";
$pclines[] = "patch-entry = download " . $build['download'] . " " . $build['download_size'] . " " . $build['download_cdn'] . " " . filesize("/var/www/wow.tools/".generateURL("data", $build['download_cdn']))." ".$espec["download"];
$pclines[] = "patch = " . $build['patch'];
$pclines[] = "patch-size = " . filesize("/var/www/wow.tools/".generateURL("patch", $build['patch']));

$patchconfig = implode("\n", $pclines);
$patchconfigmd5 = md5($patchconfig);
echo "Resulting patchconfig (md5 ".$patchconfigmd5."):\n";
echo $patchconfig."\n";

// TODO: Extract build from exe referenced in install (oof)
echo "TODO: THIS IS WHERE BUILD NUMBER SHOULD MAGICALLY BE FOUND SOMEWHERE\n";

$bclines = array();
$bclines[] = "# Build Configuration";
$bclines[] = "";
$bclines[] = "build-creator = wow.tools";
$bclines[] = "root = " . $build['root']; // we have root_cdn but it is not usually given in buildconfig
$bclines[] = "install = " . $build['install'] . " " . $build['install_cdn'];
$bclines[] = "install-size = " . $build['install_size'] . " " . filesize("/var/www/wow.tools/".generateURL("data", $build['install_cdn']));
$bclines[] = "download = " . $build['download'] . " " . $build['download_cdn'];
$bclines[] = "download-size = " . $build['download_size'] . " " . filesize("/var/www/wow.tools/".generateURL("data", $build['download_cdn']));
$bclines[] = "size = TODO TODO";
$bclines[] = "size-size = TODO TODO";
$bclines[] = "encoding = " . $build['encoding'] . " " . $build['encoding_cdn'];
$bclines[] = "encoding-size = " . $build['encoding_size'] . " " . filesize("/var/www/wow.tools/".generateURL("data", $build['encoding_cdn']));
$bclines[] = "patch = " . $build['patch'];
$bclines[] = "patch-size = " . filesize("/var/www/wow.tools/".generateURL("patch", $build['patch']));
$bclines[] = "patch-config = TODO";
$bclines[] = "build-name = WOW-XXXXXpatchX.X.X_TODO";
$bclines[] = "build-uid = TODO"; // wow, wowt, wow_beta, wow_classic, wow_classic_beta
$bclines[] = "build-product = WoW";
$bclines[] = "build-playbuild-installer = ngdptool_casc2";
$bclines[] = "build-partial-priority = TODO";


$buildconfig = implode("\n", $bclines);
$buildconfigmd5 = md5($buildconfig);
echo "Resulting buildconfig (md5 ".$buildconfigmd5."):\n";
echo $buildconfig."\n";
