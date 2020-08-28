<?php
require_once("inc/config.php");

if(empty($_GET['type'])){
	die("No API given!");
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

if($_GET['type'] == "token" && !empty($_GET['token'])){
	$q = $pdo->prepare("SELECT COUNT(id) as count, id FROM users WHERE apitoken = ?");
	$q->execute([$_GET['token']]);
	$row = $q->fetch(PDO::FETCH_ASSOC);
	if($row['count'] == 0){
		echo 0;
	}else{
		echo $row['id'];
	}
}

if($_GET['type'] == "tactkeys"){
	$q = $pdo->query("SELECT * FROM wow_tactkey WHERE keybytes IS NOT NULL");
	$tactkeys = $q->fetchAll(PDO::FETCH_ASSOC);
	foreach($tactkeys as $tactkey){
		echo $tactkey['keyname']." ".$tactkey['keybytes']."\n";
	}
}

if($_GET['type'] == "dblist"){
	if(empty($_GET['build'])){
		die("No build given");
	}

	$buildExpl = explode(".", $_GET['build']);
	if(count($buildExpl) != 4){
		die("Invalid build");
	}

	$buildDesc = "WOW-".$buildExpl[3]."patch".$buildExpl[0].".".$buildExpl[1].".".$buildExpl[2];
	$q = $pdo->prepare("SELECT filename FROM wow_rootfiles JOIN wow_rootfiles_builds_erorus ON ORD(MID(wow_rootfiles_builds_erorus.files, 1 + FLOOR(wow_rootfiles.id / 8), 1)) & (1 << (wow_rootfiles.id % 8)) WHERE wow_rootfiles_builds_erorus.build = (SELECT id FROM wow_buildconfig WHERE description LIKE ? LIMIT 1) AND wow_rootfiles.type = 'db2'");
	$q->execute([$buildDesc."%"]);

	echo implode(',', $q->fetchAll(PDO::FETCH_COLUMN));
}

if($_GET['type'] == "latestbuilds"){
	$builds = [];
	$urlq = $pdo->query("SELECT id, name, url FROM ngdp_urls WHERE url LIKE '%wow%versions' ORDER BY ID ASC");
	$histq = $pdo->prepare("SELECT newvalue, timestamp FROM ngdp_history WHERE url_id = ? AND event = 'valuechange' ORDER BY ID DESC LIMIT 1");
	while($row = $urlq->fetch(PDO::FETCH_ASSOC)){
		$histq->execute([$row['id']]);

		$product = str_replace("/versions", "", substr($row['url'], strpos($row['url'], "wow")));
		$highestBuild = 0;
		$highestBuildName = "";
		$histr = $histq->fetch(PDO::FETCH_ASSOC);
		if(!empty($histr)){
			$bc = parseBPSV(explode("\n", $histr['newvalue']));
			foreach($bc as $bcregion){
				if($bcregion['BuildId'] > $highestBuild){
					$highestBuild = $bcregion['BuildId'];
					$highestBuildName = $bcregion['VersionsName'];
				}
			}
		}

		if(!empty($highestBuildName))
			$builds[$product] = $highestBuildName;
	}

	echo json_encode($builds);
}
?>