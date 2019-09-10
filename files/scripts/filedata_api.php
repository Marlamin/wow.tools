<?php
require_once("/var/www/wow.tools/inc/config.php");

if(!empty($_GET['filedataid'])){
	$q = $pdo->prepare("SELECT * FROM wow_rootfiles WHERE id = :id");
	$q->bindParam(":id", $_GET['filedataid'], PDO::PARAM_INT);
	$q->execute();
	$row = $q->fetch();

	if(!empty($_GET['filename']) && $_GET['filename'] == 1){
		$exploded = explode(",", $_GET['filedataid']);
		if(count($exploded) == 1){
			if(!empty($row['filename'])){ echo $row['filename']; }
		}else{
			$q->bindParam(":id", $exploded[0], PDO::PARAM_INT);
			$q->execute();
			$row = $q->fetch();
			if(!empty($row['filename'])){ echo $row['filename']; }
		}
		die();
	}

	if(empty($row)){
		die("Could not find file!");
	}

	if(!empty($_GET['lookup']) && $_GET['lookup'] == 1){
		echo $row['lookup'];
		die();
	}

	$contenthashes = array();
	$subq = $pdo->prepare("SELECT wow_rootfiles_chashes.root_cdn, wow_rootfiles_chashes.contenthash, wow_rootfiles_sizes.size, wow_buildconfig.hash as buildconfig, wow_buildconfig.description FROM wow_rootfiles_chashes LEFT JOIN wow_buildconfig on wow_buildconfig.root_cdn=wow_rootfiles_chashes.root_cdn LEFT OUTER JOIN wow_rootfiles_sizes on wow_rootfiles_sizes.contenthash=wow_rootfiles_chashes.contenthash WHERE filedataid = :id ORDER BY wow_buildconfig.description DESC");
	$subq->bindParam(":id", $row['id'], PDO::PARAM_INT);
	$subq->execute();
	$versions = array();
	$prevcontenthash = '';

	while($subrow = $subq->fetch()){
		if(in_array($subrow['contenthash'], $contenthashes)){
			continue;
		}else{
			$contenthashes[] = $subrow['contenthash'];
		}
		if($subrow['contenthash'] == $prevcontenthash){
			continue;
		}

		if(empty($subrow['size'])){
			$subrow['size'] = 0;
		}

		$versions[] = $subrow;
	}

	$returndata = array("filedataid" => $row['id'], "filename" => $row['filename'], "lookup" => $row['lookup'], "versions" => $versions, "type" => $row['type']);

	echo "<table class='table table-striped'>";
	echo "<thead><tr><th style='width: 400px'></th><th></th></tr></thead>";
	echo "<tr><td>FileDataID</td><td>".$returndata['filedataid']."</td></tr>";
	if(!empty($returndata['filename'])) { echo "<tr><td>Filename</td><td>".$returndata['filename']."</td></tr>"; }
	echo "<tr><td>Lookup</td><td>".$returndata['lookup']."</td></tr>";
	echo "<tr><td>Type</td><td>".$returndata['type']."</td></tr>";

	if($returndata['type'] == "ogg" || $returndata['type'] == "mp3"){
		$soundkitq = $pdo->prepare("SELECT soundkitentry.entry as entry, soundkitname.name as name FROM `wowdata`.soundkitentry INNER JOIN `wowdata`.soundkitname ON soundkitentry.entry=`wowdata`.soundkitname.id WHERE soundkitentry.id = :id");
		$soundkitq->bindParam(":id", $returndata['filedataid']);
		$soundkitq->execute();
		$soundkits = $soundkitq->fetchAll();
		if(count($soundkits) > 0){
			echo "<tr><td>SoundKit</td><td>";
			foreach($soundkits as $soundkitrow){
				echo $soundkitrow['entry'] . " (" .htmlentities($soundkitrow['name'], ENT_QUOTES) . ")<br>";
			}
			echo "</td></tr>";
		}
	}

	$eq = $pdo->prepare("SELECT wow_tactkey.id, wow_encrypted.keyname, wow_tactkey.description, wow_tactkey.keybytes FROM wow_encrypted LEFT JOIN wow_tactkey ON wow_encrypted.keyname = wow_tactkey.keyname WHERE wow_encrypted.filedataid = :id");
	$eq->bindParam(":id", $returndata['filedataid']);
	$eq->execute();
	foreach($eq->fetchAll(PDO::FETCH_ASSOC) as $er){
		if(!empty($er['keybytes'])){
			$keyAvailable = "<span style='color: green;'>known</span>";
		}else{
			$keyAvailable = "<span style='color: red;'>unknown</span>";
		}
		echo "<tr><td>Encrypted with ".$keyAvailable." key <span class='hash'>".$er['keyname']." (".$er['id'].")</span> </td><td>".$er['description']."</td></tr>";
	}
	echo "<tr><td colspan='2'><b>Known versions</b></td></tr>";
	echo "<tr><td colspan='2'>
	<table class='table table-condensed'>";
	echo "<tr><th>Description</th><th>Buildconfig</th><th>Contenthash</th><th>Size</th><th>&nbsp;</th></tr>";
	foreach($versions as $version){
		echo "<tr><td>".$version['description']."</td><td class='hash'>".$version['buildconfig']."</td><td class='hash'><a href='#' data-toggle='modal' data-target='#chashModal' onClick='fillChashModal(\"".$version['contenthash']."\")'>".$version['contenthash']."</a></td><td>".$version['size']." bytes</td>";
		echo "<td><a href='#' data-toggle='modal' data-target='#previewModal' onClick='fillPreviewModal(\"".$version['buildconfig']."\", \"".$returndata['filedataid']."\")'>Preview</a></td>";
		echo "</tr>";
	}
	echo "</table>
	</td></tr>";
	echo "<tr><td colspan='2'><b>Neighbouring files</b></td></tr>";
	$nbq = $pdo->prepare("(SELECT * FROM wow_rootfiles WHERE id >= :id1 ORDER BY id ASC LIMIT 4) UNION (SELECT * FROM wow_rootfiles WHERE id < :id2 ORDER BY id DESC LIMIT 3) ORDER BY id ASC");
	$nbq->bindParam(":id1", $row['id']);
	$nbq->bindParam(":id2", $row['id']);
	$nbq->execute();

	echo "<tr><td colspan='2'>
	<table class='table table-condensed'>";
	echo "<tr><th>ID</th><th>Filename</th></tr>";
	while ($nbrow = $nbq->fetch()){
		echo "<tr>";
		if($nbrow['id'] == $row['id']){
			echo "<td><b style='color: red'>".$nbrow['id']."</b></td>";
			echo "<td><b style='color: red'>".$nbrow['filename']."</b></td>";
		}else{
			echo "<td>".$nbrow['id']."</td>";
			echo "<td>".$nbrow['filename']."</td>";
		}
		echo "</tr>";
	}
	echo "</table>
	</td></tr>";
	$lq = $pdo->prepare("SELECT wow_rootfiles_links.*, wow_rootfiles.id, wow_rootfiles.filename, wow_rootfiles.type as filetype FROM wow_rootfiles_links INNER JOIN wow_rootfiles ON wow_rootfiles.id=wow_rootfiles_links.parent WHERE child = :id");
	$lq->bindParam(":id", $row['id']);
	$lq->execute();
	$parents = $lq->fetchAll();
	if(count($parents) > 0){
		echo "<tr><td colspan='2'><b>Linked parent files</b></td></tr>";
		echo "<tr><td colspan='2'>
		<table class='table table-condensed'>";
		echo "<tr><th>Link type</th><th>ID</th><th>Filename</th><th>Type</th></tr>";
		foreach ($parents as $lrow){
			echo "<tr>";
			echo "<td>".$lrow['type']."</td>";
			echo "<td>".$lrow['parent']."</td>";
			echo "<td>".$lrow['filename']."</td>";
			echo "<td>".$lrow['filetype']."</td>";
			echo "</tr>";
		}
		echo "</table>
		</td></tr>";
	}

	$lq = $pdo->prepare("SELECT wow_rootfiles_links.*, wow_rootfiles.id, wow_rootfiles.filename, wow_rootfiles.type as filetype FROM wow_rootfiles_links INNER JOIN wow_rootfiles ON wow_rootfiles.id=wow_rootfiles_links.child WHERE parent = :id");
	$lq->bindParam(":id", $row['id']);
	$lq->execute();
	$children = $lq->fetchAll();
	if(count($children) > 0){
		echo "<tr><td colspan='2'><b>Linked child files</b></td></tr>";
		echo "<tr><td colspan='2'>
		<table class='table table-condensed'>";
		echo "<tr><th>Link type</th><th>ID</th><th>Filename</th><th>Type</th></tr>";
		foreach ($children as $lrow){
			echo "<tr>";
			echo "<td>".$lrow['type']."</td>";
			echo "<td>".$lrow['child']."</td>";
			echo "<td>".$lrow['filename']."</td>";
			echo "<td>".$lrow['filetype']."</td>";
			echo "</tr>";
		}
		echo "</table>
		</td></tr>";
	}
	echo "</table>";
}

if(!empty($_GET['contenthash'])){
	if(strlen($_GET['contenthash']) != 32 || !ctype_xdigit($_GET['contenthash'])) die("Invalid contenthash!");

	$chashq = $pdo->prepare("SELECT wow_rootfiles_chashes.filedataid, wow_rootfiles_chashes.root_cdn, wow_rootfiles.filename FROM wow_rootfiles_chashes JOIN wow_rootfiles ON wow_rootfiles.id = wow_rootfiles_chashes.filedataid WHERE contenthash = :contenthash ORDER BY filedataid ASC");
	$chashq->bindParam(":contenthash", $_GET['contenthash']);
	$chashq->execute();

	$chashes = $chashq->fetchAll();

	echo count($chashes)." results for this contenthash!";
	echo "<table class='table table-condensed' id='chashtable'>";
	foreach($chashes as $chashrow){
		echo "<tr><td style='width: 85px;'>".$chashrow['filedataid']."</td><td>".$chashrow['filename']."</td></tr>";
	}
	echo "</table>";
}

if(!empty($_GET['type']) && $_GET['type'] == "gettypes"){
	$types = array();
	foreach($pdo->query("SELECT DISTINCT(TYPE) FROM wow_rootfiles") as $row){
		$types[] = $row['TYPE'];
	}
	echo implode(",", $types);
}
?>