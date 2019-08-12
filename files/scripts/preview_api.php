<?php
require_once("/var/www/wow.tools/inc/config.php");

if(empty($_GET['buildconfig']) || empty($_GET['filedataid'])){
	die("Not enough information!");
}

$build = getVersionByBuildConfigHash($_GET['buildconfig'], "wow");

if(empty($build)) die("Invalid build!");

$_GET['filedataid'] = (int)$_GET['filedataid'];

$q2 = $pdo->prepare("SELECT id, filename, type FROM wow_rootfiles WHERE id = :id");
$q2->bindParam(":id", $_GET['filedataid'], PDO::PARAM_INT);
$q2->execute();
$row2 = $q2->fetch();
if(empty($row2)) {
	die("File not found in database!");
}else{
	$type = $row2['type'];
	$dbid = $row2['id'];
}

if(empty($row2['filename'])){
	$row2['filename'] = $row2['id'].".".$type;
}

function downloadFile($params, $outfile){
	$fp = fopen($outfile, 'w+');
	$url = 'http://localhost:5005/casc/file' . $params;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$exec = curl_exec($ch);
	curl_close($ch);
	fclose($fp);

	if($exec){
		return true;
	}else{
		return false;
	}
}


$cascparams = "/fdid?buildconfig=".$build['buildconfig']['hash']."&cdnconfig=".$build['cdnconfig']['hash']."&filename=".urlencode($row2['filename'])."&filedataid=".$_GET['filedataid'];
if($type == "ogg"){
	echo "<audio autoplay controls><source src='//wow.tools/casc/preview".$cascparams."' type='audio/ogg'></audio>";
	die();
}else if($type == "mp3"){
	echo "<audio autoplay controls><source src='//wow.tools/casc/preview".$cascparams."' type='audio/mpeg'></audio>";
	die();
}else if($type == "blp"){
	echo "<body style='margin: 0px; padding:0px;'><img style='max-width: 100%;' src='//wow.tools/casc/preview".$cascparams."'></body>";
	die();
}else{
	// Dump to file
	$tempfile = tempnam('/tmp/', 'PREVIEW');
	downloadFile($cascparams, $tempfile);
	if($type == "m2" || $type == "wmo"){
		// dump json
		$output = shell_exec("cd /home/wow/jsondump; /usr/bin/dotnet WoWJsonDumper.dll ".$type." ".escapeshellarg($tempfile)." 2>&1");
		?>
		<ul class="nav nav-tabs" id="myTab" role="tablist">
			<li class="nav-item">
				<a class="nav-link active" id="model-tab" data-toggle="tab" href="#model" role="tab" aria-controls="model" aria-selected="true">Model</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" id="raw-tab" data-toggle="tab" href="#raw" role="tab" aria-controls="raw" aria-selected="false">Raw</a>
			</li>
		</ul>
		<div class="tab-content" id="myTabContent">
			<div class="tab-pane show active" id="model" role="tabpanel" aria-labelledby="model-tab">
				<iframe style='border:0px;width:100%;min-height: 60vh' src='https://wow.tools/mv/?embed=true&buildconfig=<?=$build['buildconfig']['hash']?>&cdnconfig=<?=$build['cdnconfig']['hash']?>&filedataid=<?=$_GET['filedataid']?>&type=<?=$type?>'></iframe><br>
				<center><a href='https://wow.tools/mv/?buildconfig=<?=$build['buildconfig']['hash']?>&cdnconfig=<?=$build['cdnconfig']['hash']?>&filedataid=<?=$_GET['filedataid']?>&type=<?=$type?>' target='_BLANK'>Open in modelviewer</a></center>
			</div>
			<div class="tab-pane" id="raw" role="tabpanel" aria-labelledby="raw-tab"><pre style='max-height: 500px;'><code><?=htmlentities($output)?></pre></code></div>
		</div>
		<?php
	}else if($type == "xml" || $type == "xsd" || $type == "lua" || $type == "toc" || $type == "htm" || $type == "html" || $type == "sbt" || $type == "txt"){
		echo "<pre style='max-height: 500px;'><code>".htmlentities(file_get_contents($tempfile))."</pre></code>";
	}else{
		// dump via hd
		echo "Not a supported file for previews, dumping hex output (until 1MB).";
		$output = shell_exec("/usr/bin/hd -n1048576 ".escapeshellarg($tempfile));
		echo "<pre style='max-height: 500px;'><code>".htmlentities($output)."</pre></code>";
	}
	unlink($tempfile);
}
?>