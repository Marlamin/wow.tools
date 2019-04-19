<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");
include("../../inc/config.php");

$res = $pdo->query(
	"SELECT
	wow_versions.cdnconfig,
	wow_versions.buildconfig,
	wow_buildconfig.id as bdid,
	wow_buildconfig.description,
	wow_buildconfig.install_cdn,
	wow_buildconfig.product,
	wow_buildconfig.builton
	FROM wow_versions
	LEFT OUTER JOIN wow_buildconfig ON wow_versions.buildconfig=wow_buildconfig.hash
	ORDER BY wow_buildconfig.description
	");

while($row = $res->fetch()){
	if($row['bdid'] > 1315){
		if($row['product'] == "wow" || $row['product'] == "wow_classic") $target = "Wow.exe";
		if($row['product'] == "wowt") $target = "WowT.exe";
		if($row['product'] == "wow_beta" || $row['product'] == "wowz" || $row['product'] == "wow_classic_beta") $target = "WowB.exe";
	}else{
		if($row['product'] == "wow") $target = "Wow-64.exe";
		if($row['product'] == "wowt") $target = "WowT-64.exe";
		if($row['product'] == "wow_beta" || $row['product'] == "wowz" || $row['product'] == "wow_classic_beta") $target = "WowB-64.exe";
	}

	if(empty($target)){
		die();
	}

	$filename = "/home/wow/exes/".$row['description']."-".$row['buildconfig']."-".$target;

	// Only extract file if it does not exist
	if(!file_exists($filename)){
		$output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll dumpinstall wow ".$row['install_cdn']);
		foreach(explode("\n", $output) as $line){
			if(substr($line, 0, strlen($target)) == $target){
				if(empty(trim($line))) continue;
				$split1 = explode(" (", $line);
				$split2 = explode (", ", $split1[1]);
				$md5 = str_replace("md5: ", "", $split2[1]);
			// Remove if you magically get 18179 archives complete again
				if($row['buildconfig'] == "cc7af6d878238d1c78d828db5146d343") continue;

				echo $row['description'].": ".$row['buildconfig']."\" \"".$row['cdnconfig']."\" \"".$md5."\" \"".$filename."\"\n";
				$output = shell_exec("/usr/bin/dotnet /home/wow/buildbackup/BuildBackup.dll extractfilebycontenthash wow \"".$row['buildconfig']."\" \"".$row['cdnconfig']."\" \"".$md5."\" \"".$filename."\"");
				shell_exec("chmod -R 777 /home/wow/exes/*");
				print_r($output);
			}
		}
	}

	if($row['builton'] == NULL){
		// Parse file only if exists (could be that previous step failed)
		if(file_exists($filename)){
			echo "	File exists, adding build time to DB\n";
			$output = shell_exec("/usr/bin/strings ".$filename." | grep Built");
			$output = str_replace("Exe Built: ", "", $output);
			$date = date('Y-m-d H:i:s',strtotime($output))."\n";
			$uq = $pdo->prepare("UPDATE wow_buildconfig SET builton = ? WHERE hash = ?");
			$uq->execute([$date, $row['buildconfig']]);
		}else{
			echo "	File " . $filename . " does not exist.\n";
		}
	}else{
	}
}
