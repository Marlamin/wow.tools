<?php
function generateURL($type, $hash, $cdndir = "wow"){
	return "tpr/".$cdndir."/".$type."/".$hash[0].$hash[1]."/".$hash[2].$hash[3]."/".$hash;
}

function flushQueryCache(){
	global $memcached;
	$memcached->flush();
}

function doesFileExist($type, $hash, $cdndir = "wow"){
	if(strlen($hash) < 32){
		die("Empty hash! Hash: ".$hash." Type: ".$type);
	}

	if(file_exists($GLOBALS['basedir'] . "/" . generateURL($type, $hash, $cdndir))){
		return true;
	}else{
		return false;
	}
}

function parseBuildName($buildname){
	$build['original'] = $buildname;

	$buildname = str_replace("WOW-", "", $buildname);
	$split = explode("patch", $buildname);
	$buildnum = $split[0];
	$descexpl = explode("_", $split[1]);

	$build['full'] = $descexpl[0].".".$buildnum;
	$build['patch'] = $descexpl[0];
	$build['build'] = $buildnum;
	return $build;
}

function generateMeta($queryString){
	$url = parse_url($queryString);
	$tags = [];

	if(!empty($url['path'])){
		switch($url['path']){
			case "/":
			case "/index.php":
			default:
			$desc = "Collection of several World of Warcraft tools (DBC/file browser, modelviewer & more).";
			break;
			case "/dbc/":
			$desc = "Web database/DBC browser for World of Warcraft";
			break;
			case "/files/":
			$desc = "Web file browser for World of Warcraft game assets";
			break;
			case "/mv/":
			$desc = "Web model viewer for World of Warcraft versions 6.x-8.x";
			break;
			case "/maps/":
			$desc = "Top-down map/minimap viewer for World of Warcraft";
			break;
			case "/monitor/":
			$desc = "Blizzard patch server monitor";
			break;
			case "/builds/":
			$desc = "List of all World of Warcraft versions since 6.0 (including diff tool)";
			break;
		}

		$tags[] = "<meta name='description' content='" . $desc . "'>";
		$tags[] = "<meta property='og:description' content='" . $desc . "'>";
	}

	$tags[] = "<meta property='og:type' content='website'>";
	$tags[] = "<meta property='og:site_name' content='WoW.tools'>";
	$tags[] = "<meta property='og:title' content='" . prettyTitle($queryString) . "'>";
	$tags[] = "<meta property='og:image' content='https://wow.tools/img/cogw.png'>";

	$tags[] = "<meta property='twitter:image' content='https://wow.tools/img/cogw.png'>";
	$tags[] = "<meta property='twitter:card' content='summary'>";
	$tags[] = "<meta property='twitter:site' content='@Marlamin'>";

	$tags[] = "<meta name='application-name' content='WoW.tools'>";
	$tags[] = "<meta name='apple-mobile-web-app-title' content='WoW.tools'>";
	$tags[] = "<meta name='theme-color' content='#343a40'>";
	$tags[] = "";
	return implode("\n", $tags);
}

function prettyTitle($queryString){
	$url = parse_url($queryString);
	$addendum = "";
	switch($url['path']){
		case "/":
		case "/index.php":
		$addendum = "Home";
		break;
		case "/dbc/":
		$addendum = "Database browser";
		break;
		case "/files/":
		$addendum = "File browser";
		break;
		case "/mv/":
		$addendum = "Model viewer";
		break;
		case "/maps/":
		$addendum = "Minimaps";
		break;
		case "/monitor/":
		$addendum = "Monitor";
		break;
		case "/builds/":
		$addendum = "Builds";
		break;
	}

	if(!empty($addendum)){
		return "WoW.tools | " . $addendum;
	}else{
		// trigger_error("Unable to find title for querystring " . $queryString);
		return "WoW.tools";
	}
}

function prettyBranch($branch){
	switch($branch){
		case "wow":
		return "<span class='badge badge-primary'>Retail</span>";
		case "wowt":
		return "<span class='badge badge-warning'>PTR</span>";
		case "wow_beta":
		return "<span class='badge badge-danger'>Beta</span>";
		case "wowz":
		return "<span class='badge badge-success'>Submission</span>";
		case "wow_classic":
		return "<span class='badge badge-info'>Classic</span>";
		case "wow_classic_beta":
		return "<span class='badge badge-info'>Classic Beta</span>";
		case "wowe1":
		return "<span class='badge badge-secondary'>Event 1</span>";
		case "wowe2":
		return "<span class='badge badge-secondary'>Event 2</span>";
		case "wowe3":
		return "<span class='badge badge-secondary'>Event 3</span>";
		default:
		return "UNKNOWN";
	}
}

function parseBPSV($bpsv){
	$result = [];
	foreach($bpsv as $key => $line){
		if(empty(trim($line))){
			continue;
		}
		if($line[0] == "#") continue;
		$cols = explode("|", $line);
		if($key == 0) {
			foreach($cols as $key => $col){
				$exploded = explode("!", $col);
				$headers[] = $exploded[0];
			}
		}else{
			foreach($cols as $key => $col){
				$result[$cols[0]][$headers[$key]] = $col;
			}
		}
	}
	return $result;
}

function parseConfig($file){
	$handle = fopen($file, "r");
	$config = array();
	$t = explode("/", $file);
	$config['original-filename'] = basename($file);

	if(strlen($t[9]) == 2){
		die("Faulty config!");
	}

	if ($handle) {
		while (($line = fgets($handle)) !== false) {
			$line = trim($line);
			if(empty($line) || $line[0] == "#") continue;
			$vars = explode(" = ", $line);
			if($vars[0] == "patch-entry"){
				if(!isset($config['patch-entry'])){
					$config['patch-entry'] = array();
				}

				// Patch entry has double entries, append
				$config['patch-entry'][count($config['patch-entry'])] = $vars[1];
			}else if(!empty($vars[1])){
				$config[$vars[0]] = $vars[1];
			}
		}
		fclose($handle);
	}

	ksort($config);

	return $config;
}

function getVersionByBuildConfigHash($hash, $product = "wow"){
	global $pdo;
	$query = $pdo->prepare("SELECT * FROM ".$product."_versions WHERE buildconfig = ?");
	$query->execute([$hash]);
	$row = $query->fetch();
	if(!empty($row['cdnconfig'])){ $row['cdnconfig'] = getCDNConfigbyCDNConfigHash($row['cdnconfig'], $product); }
	if(!empty($row['buildconfig'])){ $row['buildconfig'] = getBuildConfigByBuildConfigHash($row['buildconfig'], $product); }
	return $row;
}

function getBuildConfigByBuildConfigHash($hash, $product = "wow"){
	global $pdo;
	$query = $pdo->prepare("SELECT * FROM ".$product."_buildconfig WHERE hash = ?");
	$query->execute([$hash]);
	$r = $query->fetch();
	if(!empty($r)){
		return $r;
	}else{
		return false;
	}
}

function getCDNConfigbyCDNConfigHash($hash, $product = "wow"){
	global $pdo;
	$query = $pdo->prepare("SELECT * FROM ".$product."_cdnconfig WHERE hash = ?");
	$query->execute([$hash]);
	$r = $query->fetch();
	if(!empty($r)){
		return $r;
	}else{
		return false;
	}
}

function getPatchConfigByPatchConfigHash($hash, $product = "wow"){
	global $pdo;
	$query = $pdo->prepare("SELECT * FROM ".$product."_patchconfig WHERE hash = ?");
	$query->execute([$hash]);
	$r = $query->fetch();
	if(!empty($r)){
		return $r;
	}else{
		return false;
	}
}


function sendgridMail($to, $subject, $content){
	global $sendgrid;
	$sendgridData = [
		'personalizations' =>
		[
			[
				'to' =>
				[
					[
						'email' => $to,
					],
				],
				'subject' => $subject,
			],
		],
		'from' =>
		[
			'email' => 'noreply@wow.tools',
		],
		'content' =>
		[
			[
				'type' => 'text/plain',
				'value' => $content,
			],
		],
	];
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://api.sendgrid.com/v3/mail/send");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendgridData));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$headers = ['Authorization: Bearer ' . $sendgrid['apikey'], 'Content-Type: application/json'];
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$res = curl_exec($ch);
}

function getUsernameByUserID($id){
	global $pdo;
	$q = $pdo->prepare("SELECT username FROM users WHERE id = ?");
	$q->execute([$id]);
	$user = $q->fetch();
	if(empty($user)){
		return false;
	}else{
		return $user['username'];
	}
}
?>