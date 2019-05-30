<?php
require_once("../inc/header.php");

if(empty($_GET['bc']) && empty($_GET['cdnc'])){
	die("No config set");
}

if(!empty($_GET['bc'])){
	$res = $pdo->prepare("SELECT * FROM wow_buildconfig WHERE hash = ?");
	$res->execute([$_GET['bc']]);
	$row = $res->fetch();
	if(empty($row)){
		die("Buildconfig not found!");
	}
	echo "<div class='container-fluid'>";
	echo "<h4>Processed information</h4>";
	echo "<table class='table table-sm'>";
	echo "<thead><tr><th style='width:250px;'>Key</th><th>Value</th></tr></thead>";
	echo "<tr><td>Hash</td><td class='hash'>".$row['hash']."</td></tr>";
	echo "<tr><td>Description</td><td>".$row['description']."</td></tr>";
	echo "<tr><td>Product</td><td>".$row['product']."</td></tr>";
	echo "<tr><td>Encoding</td><td class='hash'>".$row['encoding']."</td></tr>";
	echo "<tr><td>Encoding (CDN)</td>";
	if(!empty($row['encoding_cdn']) && doesFileExist("data", $row['encoding_cdn'], $allowedproducts["wow"]['cdndir'])){
		echo "<td><span style='color: green' class='hash'>".$row['encoding_cdn']."</span>";
	}else{
		echo "<td><span style='color: red' class='hash'><b>".$row['encoding_cdn']."</b></span>";
	}
	echo "</td></tr>";
	echo "<tr><td>Root</td><td class='hash'>".$row['root']."</td></tr>";
	echo "<tr><td>Root (CDN)</td>";
	if(!empty($row['root_cdn']) && doesFileExist("data", $row['root_cdn'], $allowedproducts["wow"]['cdndir'])){
		echo "<td><span style='color: green' class='hash'>".$row['root_cdn']."</span>";
	}else{
		echo "<td><span style='color: red' class='hash'><b>".$row['root_cdn']."</b></span>";
	}
	echo "</td></tr>";
	echo "<tr><td>Install</td><td class='hash'>".$row['install']."</td></tr>";
	echo "<tr><td>Install (CDN)</td>";
	if(!empty($row['install_cdn']) && doesFileExist("data", $row['install_cdn'], $allowedproducts["wow"]['cdndir'])){
		echo "<td><span style='color: green' class='hash'>".$row['install_cdn']."</span>";
	}else{
		echo "<td><span style='color: red' class='hash'><b>".$row['install_cdn']."</b></span>";
	}
	echo "</td></tr>";
	echo "<tr><td>Download</td><td class='hash'>".$row['download']."</td></tr>";
	echo "<tr><td>Download (CDN)</td>";
	if(!empty($row['download_cdn']) && doesFileExist("data", $row['download_cdn'], $allowedproducts["wow"]['cdndir'])){
		echo "<td><span style='color: green' class='hash'>".$row['download_cdn']."</span>";
	}else{
		echo "<td><span style='color: red' class='hash'><b>".$row['download_cdn']."</b></span>";
	}
	echo "</td></tr>";
	echo "<tr><td>Unarchived</td><td>";
	$unarchiveds = explode(" ", $row['unarchived']);
	sort($unarchiveds);
	foreach($unarchiveds as $unarchived){
		if(empty($unarchived)) continue;
		if(doesFileExist("data", $unarchived, $allowedproducts["wow"]['cdndir'])){
			echo "<span class='hash' style='color: green'>".$unarchived."</span> ";
		}else{
			echo "<span class='hash' style='color: red'><b>".$unarchived."</b></span> ";
		}
	}
	echo "</td></tr>";
	echo "<tr><td>Unarchived (Count) </td><td>".$row['unarchivedcount']."</td></tr>";
	echo "<tr><td>Unarchived (Completed) </td><td>".$row['unarchivedcomplete']."</td></tr>";
	echo "</table>";
	echo "<h3>Raw file</h3>";
	echo "<pre>";
	echo file_get_contents("/var/www/wow.tools/tpr/".$allowedproducts["wow"]['cdndir']."/config/".$row['hash'][0].$row['hash'][1]."/".$row['hash'][2].$row['hash'][3]."/".$row['hash']);
	echo "</pre>";
	echo "</div>";
}else if(!empty($_GET['cdnc'])){
	$res = $pdo->prepare("SELECT * FROM wow_cdnconfig WHERE hash = ?");
	$res->execute([$_GET['cdnc']]);
	$row = $res->fetch();
	echo "<div class='container-fluid'>";
	echo "<h4>Processed information</h4>";
	echo "<table class='table table-sm'>";
	echo "<thead><tr><th style='width:250px;'>Key</th><th>Value</th></tr></thead>";
	echo "<tr><td>Hash</td><td>";
	if(doesFileExist("config", $row['hash'], $allowedproducts["wow"]['cdndir'])){
		echo "<span class='hash' style='color: green'>".$row['hash']."</span> ";
	}else{
		echo "<span class='hash' style='color: red'><b>".$row['hash']."</b></span> ";
	}
	echo "</td></tr>";
	echo "<tr><td>Archives</td><td>";
	$archives = explode(" ", $row['archives']);
	foreach($archives as $archive){
		if(doesFileExist("data", $archive, $allowedproducts["wow"]['cdndir'])){
			echo "<span class='hash' style='color: green'>".$archive."</span> ";
		}else{
			echo "<span class='hash' style='color: red'><b>".$archive."</b></span> ";
		}
	}
	echo "</td></tr>";
	echo "<tr><td>Archive count</td><td>".$row['archivecount']."</td></tr>";
	echo "<tr><td>Archive complete</td><td>".$row['archivecomplete']."</td></tr>";
	echo "<tr><td>Archive indexes</td><td>";
	$archives = explode(" ", $row['archives']);
	foreach($archives as $archive){
		if(doesFileExist("data", $archive.".index", $allowedproducts["wow"]['cdndir'])){
			echo "<span class='hash' style='color: green'>".$archive.".index</span> ";
		}else{
			echo "<span class='hash' style='color: red'><b>".$archive.".index</b></span> ";
		}
	}
	echo "</td></tr>";
	echo "<tr><td>Archive index complete</td><td>".$row['indexcomplete']."</td></tr>";
	echo "<tr><td>Patch archives</td><td>";
	if(!empty($row['patcharchives'])){
		$patcharchives = explode(" ", $row['patcharchives']);
		foreach($patcharchives as $patcharchive){
			if(doesFileExist("patch", $patcharchive, $allowedproducts["wow"]['cdndir'])){
				echo "<span class='hash' style='color: green'>".$patcharchive."</span> ";
			}else{
				echo "<span class='hash' style='color: red'><b>".$patcharchive."</b></span> ";
			}
		}
	}
	echo "</td></tr>";
	echo "<tr><td>Patch archive count</td><td>".$row['patcharchivecount']."</td></tr>";
	echo "<tr><td>Patch archive complete</td><td>".$row['patcharchivecomplete']."</td></tr>";
	echo "<tr><td>Patch archive indexes</td><td>";
	$patcharchives = explode(" ", $row['patcharchives']);
	if(!empty($row['patcharchives'])){
		foreach($patcharchives as $patcharchive){
			if(doesFileExist("patch", $patcharchive.".index", $allowedproducts["wow"]['cdndir'])){
				echo "<span class='hash' style='color: green'>".$patcharchive.".index</span> ";
			}else{
				echo "<span class='hash' style='color: red; font-family: monospace; '>".$patcharchive.".index</span> ";
			}
		}
	}
	echo "</td></tr>";
	echo "<tr><td>Patch archive index complete</td><td>".$row['patchindexcomplete']."</td></tr>";
	if(!empty($row['builds'])){
		echo "<tr><td>Builds</td><td>";
		$builds = explode(" ", $row['builds']);
		if(!empty($row['builds'])){
			foreach($builds as $build){
				if(doesFileExist("config", $build, "wow")){
					echo "<span class='hash' style='color: green'>".$build."</span> ";
				}else{
					echo "<span class='hash' style='color: red'><b>".$build."</b></span> ";
				}
			}
		}
		echo "</td></tr>";
	}
	echo "</table>";
	echo "<h4>Raw file</h4>";
	echo "<pre>";
	echo file_get_contents("/var/www/wow.tools/tpr/".$allowedproducts["wow"]['cdndir']."/config/".$row['hash'][0].$row['hash'][1]."/".$row['hash'][2].$row['hash'][3]."/".$row['hash']);
	echo "</pre>";
	echo "</div>";
}
