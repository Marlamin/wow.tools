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
?>