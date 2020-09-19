<?php
require_once(__DIR__ . "/inc/header.php");

if(empty($_GET['from']) || empty($_GET['to']))
	die("Not enough to diff");

$cdnc1 = getCDNConfigByCDNConfigHash($_GET['from']);
$cdnc2 = getCDNConfigByCDNConfigHash($_GET['to']);

if(empty($cdnc1) || empty($cdnc2))
	die("Invalid configs!");

$config1 = parseConfig("/var/www/wow.tools/" . generateURL("config", $cdnc1['hash']));
$config2 = parseConfig("/var/www/wow.tools/" . generateURL("config", $cdnc2['hash']));

?>
<div class='container-fluid' id='diffContainer'>
<h3>Diff between <?=$cdnc1['hash']?> and <?=$cdnc2['hash']?></h3>
<pre>
<?php
if($config1['file-index'] === $config2['file-index'])
	die("File indexes match");

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

$checkQ = $pdo->prepare("SELECT * FROM wow_buildconfig WHERE install_cdn = ? OR encoding_cdn = ? OR root_cdn = ? OR download_cdn = ?");
foreach($addedFiles as $addedFile){

	// Check if file already belongs to existing buildconfig
	$checkQ->execute([$addedFile, $addedFile, $addedFile, $addedFile]);
	if(!empty($checkQ->fetch())){
		continue;
	}

	// Identify file based on magic
	$path = "/var/www/wow.tools/".generateURL("data", $addedFile);
	$firstChars = shell_exec("cd /home/wow/buildbackup/; dotnet BuildBackup.dll dumprawfile ".$path." 2");
	switch($firstChars){
		case "TS": // TSFM: Root
			$results[$addedFile] = "root";
			break;
		case "EN": // EN: Encoding
			$results[$addedFile] = "encoding";
			break;
		case "IN": // IN: Install
			$results[$addedFile] = "install";
			break;
		case "DL": // DL: Download
			$results[$addedFile] = "download";
			break;
		default:
			echo "Unknown magic for file ".$addedFile.": " . $firstChars."\n";
			break;
	}
}

print_r($results);

$build = array();
$build['build-creator'] = "wow.tools";
foreach($results as $key => $type){
	if($type == "root"){
		if(!empty($build['root_cdn'])){
			die("Already have a root file for this half-push, needs manual checking.");
		}else{
			$build['root_cdn'] = $key;
		}
	}

	if($type == "encoding"){
		if(!empty($build['encoding_cdn'])){
			die("Already have a encoding file for this half-push, needs manual checking.");
		}else{
			$build['encoding_cdn'] = $key;
		}
	}

	if($type == "install"){
		if(!empty($build['install_cdn'])){
			die("Already have a install file for this half-push, needs manual checking.");
		}else{
			$build['install_cdn'] = $key;
		}
	}

	if($type == "download"){
		if(!empty($build['download_cdn'])){
			die("Already have a download file for this half-push, needs manual checking.");
		}else{
			$build['download_cdn'] = $key;
		}
	}
}

print_r($build);
?>
</pre>
</div>
<?php
require_once(__DIR__ . "/inc/header.php");
?>