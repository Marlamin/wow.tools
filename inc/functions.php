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

	if(file_exists(__DIR__ . "/../" . generateURL($type, $hash, $cdndir))){
		return true;
	}else{
		return false;
	}
}

function parseBuildName($buildname){
	$build = [];
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
			case "/dbc/diff.php":
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

	if(empty($url['path']))
		return "WoW.tools";

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

function prettyBranch($branch, $pretty = true){
	switch($branch){
		case "wow":
		$color = "primary";
		$branch = "Retail";
		break;
		case "wowt":
		$color = "warning";
		$branch = "PTR";
		break;
		case "wow_beta":
		$color = "danger";
		$branch = "Beta";
		break;
		case "wowz":
		$color = "success";
		$branch = "Submission";
		break;
		case "wow_classic":
		$color = "info";
		$branch = "Classic";
		break;
		case "wow_classic_beta":
		$color = "info";
		$branch = "Classic Beta";
		break;
		case "wowe1":
		$color = "secondary";
		$branch = "Event 1";
		break;
		case "wowe2":
		$color = "secondary";
		$branch = "Event 2";
		break;
		case "wowe3":
		$color = "secondary";
		$branch = "Event 3";
		break;
		default:
		$color = "danger";
		$branch = "unknown";
	}

	if($pretty){
		return "<span class='badge badge-".$color."'>".$branch."</span>";
	}else{
		return $branch;
	}
}

function parseBPSV($bpsv){
	$result = [];
	$headers = [];
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

function githubRequest($path, $data = null){
	global $github;
	$ch = curl_init('https://api.github.com/' . $path);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if(!empty($data)){
		$data_string = json_encode($data);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	}

	curl_setopt($ch, CURLOPT_USERPWD, $github['username'] . ":" . $github['oauthkey']);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'User-Agent: WoW.tools changelog'
	));

	$result = curl_exec($ch);

	if(!$result){
		die("An error occured contacting GitHub!");
	}

	$json = json_decode($result, true);

	if(!$json){
		die("An error occured during JSON decoding. cURL result: " . $result);
	}

	return $json;
}

function compareTimestamp($a, $b)
{
	return ($a['timestamp']< $b['timestamp']);
}

function getOrCreateVersionID($version){
	global $pdo;

	$versionCache = [];
	foreach($pdo->query("SELECT id, version FROM wow_builds") as $ver){
		$versionCache[$ver['version']] = $ver['id'];
	}

	if(!array_key_exists($version, $versionCache)){
		// Version does not exist, create and return id
		echo "Creating version id for " . $version . "\n";
		$expl = explode(".", $version);

		$q = $pdo->prepare("INSERT INTO wow_builds (version, expansion, major, minor, build) VALUES (?, ?, ?, ?, ?)");
		$q->execute([$version, $expl[0], $expl[1], $expl[2], $expl[3]]);
		$insertId = $pdo->lastInsertId();
		$versionCache[$version] = $insertId;
	}

	return $versionCache[$version];
}

function humanBytes(float $Bytes, int $Precision = 2) : string
{
	if($Bytes < 1024)
	{
		return $Bytes . ' B';
	}

	$Units = [ 'B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB' ];

	$i = floor( log( $Bytes, 1024 ) );

	return number_format( $Bytes / pow( 1024, $i ), $Precision, '.', '' ) . ' ' . $Units[ $i ];
}

function telegramRequest ($method, $params)
{
	global $telegram;

	$payload = http_build_query ($params);

	$ch = curl_init ('https://api.telegram.org/bot' . $telegram['token'] . '/' . $method);
	curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_HTTPHEADER, array (
		'Content-Type: application/x-www-form-urlencoded',
		'Content-Length: ' . strlen($payload))
	);

	$result = curl_exec($ch);

	if(!$result){
		die("Error contact Telegram!");
	}

	return json_decode($result, true);
}

function telegramSendMessage ($text)
{
	global $telegram;

	return telegramRequest ( "sendMessage"
		, array ( "chat_id" => $telegram["chat_id"]
			, "text" => $text
		)
	);
}
?>