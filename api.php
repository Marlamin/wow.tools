<?php
require_once("inc/config.php");

if(empty($_GET['type'])){
	die("No API given!");
}

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
?>