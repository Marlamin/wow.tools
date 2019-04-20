<?php
include("../inc/config.php");
include("../inc/header.php");
function getFileCount($root){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://wow.tools/casc/root/fdidcount?rootcdn=" . $root);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($ch);
	if(!$data){
		echo "cURL fail: " . print_r(curl_error($ch))."\n";
	}
	curl_close($ch);
	if($data == ""){
		return false;
	}else{
		return $data;
	}
}

$arr = $pdo->query("SELECT wow_versions.buildconfig, wow_versions.cdnconfig, wow_buildconfig.description, wow_buildconfig.root_cdn FROM wow_versions LEFT OUTER JOIN wow_buildconfig ON wow_versions.buildconfig=wow_buildconfig.hash ORDER BY wow_buildconfig.description DESC")->fetchAll();

?>
<div class="container-fluid" style="width: 80%; margin-left: 10%; margin-top: 10px;">
	<div class="row">
		<div class="col-sm">
			<h4>File types</h4>
			<table class='table table-condensed table-striped'>
			<?php
			$typeq = $pdo->query("SELECT type, count(type) FROM wow_rootfiles GROUP BY type ORDER BY count(type) DESC");
			while($typerow = $typeq->fetch()){
				echo "<tr><td>".$typerow['type']."</td><td>".$typerow['count(type)']."</td></tr>";
			}
			?>
			</table>
		</div>
		<div class="col-sm">
			<h4>File count per build</h4>
			<table class='table table-condensed table-striped'>
			<?php
			foreach($arr as $build){
				if(!($bnum = $memcached->get("files.available_".$build['root_cdn']))){
					$bnum = getFileCount($build['root_cdn']);
					$memcached->set("files.available_".$build['root_cdn'], $bnum);
				}
				echo "<tr><td>".$build['description']."</td><td>".$bnum."</td></tr>";
			}
			?>
			</table>
		</div>
		<div class="col-sm">
			<h4>Unnamed</h4>
			<b>Types</b>
			<table class='table table-condensed table-striped'>
			<?php
			$typeq = $pdo->query("SELECT type, count(type) FROM wow_rootfiles WHERE filename IS NULL GROUP BY type ORDER BY count(type) DESC");
			while($typerow = $typeq->fetch()){
				echo "<tr><td>".$typerow['type']."</td><td>".$typerow['count(type)']."</td></tr>";
			}
			?>
			</table>
		</div>
	</div>
</div>
<? include "../inc/footer.php"; ?>