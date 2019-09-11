<?php
require_once("../inc/header.php");

echo "<div class='container-fluid'>";
$encryptedfileq = $pdo->prepare("SELECT * FROM wow_rootfiles WHERE id IN (SELECT filedataid FROM wow_encrypted WHERE keyname = ?)");
foreach($pdo->query("SELECT * FROM wow_tactkey ORDER BY id DESC") as $tactkey){

	$encryptedfileq->execute([$tactkey['keyname']]);
	$filesforkey = $encryptedfileq->fetchAll(PDO::FETCH_ASSOC);

	if(empty($tactkey['keybytes'])){
		$status = "<span style='color: red'>Unknown</span>";
	}else{
		$status = "<span style='color: green'>Known</span>";
	}

	echo "<h3>Key ".$tactkey['id']." - ".$tactkey['keyname']." - ".$status."</h3>";
	if(count($filesforkey) > 0) {
		echo "<p><a target='_BLANK' href='https://wow.tools/files/#search=encrypted%3A".$tactkey['keyname']."'>View list of ".count($filesforkey)." files currently encrypted by this key</a></p>";
	}else{
		echo "<p>No files currently encrypted by this key.</p>";
	}
	echo "<table class='table table-condensed table-sm table-striped'>";
	echo "<tr><td style='width: 400px'>Added in</td><td>".$tactkey['added']."</td></tr>";
	if(!empty($tactkey['description'])){ echo "<tr><td>Description (manually updated, possibly outdated)</td><td>".$tactkey['description']."</td></tr>"; }

	echo "<tr><td colspan='2'></td></tr>";

	$types = [];
	foreach($filesforkey as $file){
		if(!isset($types[$file['type']])){
			$types[$file['type']] = 1;
		}else{
			$types[$file['type']]++;
		}

	}

	if(count($filesforkey) > 0){
		echo "<tr><td>Types</td><td>";
		echo "<table class='table table-sm table-striped'>";
		foreach($types as $type => $count){
			echo "<tr><td style='width: 100px'>".$type."</td><td>".$count."</td></tr>";
		}
		echo "</table>";
		echo "</td></tr>";
	}

	echo "</table>";
	echo "<hr>";
}

echo "</div>";
?>