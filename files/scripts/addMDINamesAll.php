<?php
include("../../inc/config.php");

if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");

$q = $pdo->query("SELECT filedataid, concat(path, name) as filename FROM `wowdata`.manifestinterfacedata WHERE filedataid NOT IN (SELECT id FROM `casc`.wow_rootfiles)");

$files = array();
while($row = $q->fetch(PDO::FETCH_ASSOC)){
	$files[$row['filedataid']] = $row['filename'];
}

foreach($files as $filedataid => $file){
	echo $filedataid.";".$file."\n";
}

$numadded = 0;

if($numadded > 0){
	flushQueryCache();
	$lq = $pdo->prepare("INSERT INTO wow_namelog (userid, userip, numadded) VALUES (:id, :ip, :numadded)");
	$lq->bindValue(':id', 2, PDO::PARAM_INT);
	$lq->bindValue(':ip', "127.0.0.1");
	$lq->bindParam(':numadded', $numadded);
	$lq->execute();
}
