<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");
include(__DIR__ . "/../../inc/config.php");

$bq = $pdo->prepare("SELECT product FROM wow_buildconfig WHERE description LIKE ? ORDER BY id ASC");
$uq = $pdo->prepare("UPDATE wow_builds SET branch = ? WHERE id = ?");
foreach($pdo->query("SELECT * FROM wow_builds WHERE branch IS NULL") as $row){
	if($row['build'] < 18125) continue;
	$bq->execute(["%".$row['build']."%"]);
	$brow = $bq->fetch();
	if(!empty($brow)){
		$branch = prettyBranch($brow['product'], false);
		echo $row['version']." maps to " . $brow['product']." (".$branch.")\n";
		$uq->execute([$branch, $row['id']]);
	}
}
?>