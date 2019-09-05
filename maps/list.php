<?php
require_once("../inc/header.php");
?>
<div class='container-fluid'>
<?php
$firstseenq = $pdo->prepare("SELECT * FROM wow_maps_maps WHERE firstseen = ?");

foreach($pdo->query("SELECT version, build FROM wow_builds ORDER BY build DESC") as $row){
	$firstseenq->execute([$row['build']]);
	$firstseen = $firstseenq->fetchAll();
	if(count($firstseen) > 0){
		echo "<h3>".$row['version']."</h3>";
		foreach($firstseen as $map){
			echo "<span class='badge badge-success'>Added</span> <a href='/maps/png/".$row['version']."/".rawurlencode($map['internal']).".png'>".$map['name']."</a><br>";
		}
		echo "<br>";
	}
}
?>
</div>
<?php
require_once("../inc/footer.php");
?>