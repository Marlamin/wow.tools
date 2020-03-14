<?php
require_once("../inc/header.php");
?>
<div class="modal" id="moreInfoModal" tabindex="-1" role="dialog" aria-labelledby="moreInfoModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="moreInfoModalLabel">More information</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="moreInfoModalContent">
				<i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="previewModalLabel">Preview</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="previewModalContent">
				<i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<?php
echo "<div class='container-fluid'>";

$encryptedfileq = $pdo->prepare("SELECT * FROM wow_rootfiles WHERE id IN (SELECT filedataid FROM wow_encrypted WHERE keyname = ?)");

$cmdq = $pdo->prepare("SELECT * FROM wowdata.creaturemodeldata WHERE filedataid IN (SELECT filedataid FROM wow_encrypted WHERE keyname = ?)");
$csdq = $pdo->prepare("SELECT * FROM wowdata.creaturesounddata WHERE ID = ?");
$contenthashMatchQ = $pdo->prepare("SELECT filedataid, contenthash FROM wow_rootfiles_chashes WHERE contenthash IN (SELECT contenthash FROM wow_rootfiles_chashes WHERE filedataid IN (SELECT filedataid FROM wow_encrypted WHERE wow_encrypted.keyname = ?)) AND filedataid NOT IN (SELECT filedataid FROM wow_encrypted)
");
foreach($pdo->query("SELECT * FROM wow_tactkey WHERE id > 120 ORDER BY id DESC") as $tactkey){

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

	if(array_key_exists("m2", $types)){
		$cmdq->execute([$tactkey['keyname']]);
		$cmds = $cmdq->fetchAll();
		if(count($cmds) > 0){
			echo "<tr><td>Creatures</td><td><table class='table table-sm table-striped'>";
			foreach($cmds as $cmd){
				echo "<tr><td>FileDataID " . $cmd['filedataid']."</td><td>&nbsp;</td></tr>";
				echo "<tr><td>CreatureModelData " . $cmd['id']."</td><td>&nbsp;</td></tr>";
				echo "<tr><td>CreatureSoundData " . $cmd['soundid']."</td><td>&nbsp;</td></tr>";
				$csdq->execute([$cmd['soundid']]);
				$csds = $csdq->fetchAll(PDO::FETCH_ASSOC);
				if(count($csds) == 0){
					echo "<tr><td>Creature Sound Data entry not found, either encrypted or not yet implemented</td></tr>";
				}
				foreach($csds as $csd){
					$shown = false;
					foreach($csd as $type => $soundkitid){
						if($type == "ID") continue;
						if($soundkitid != 0){
							$shown = true;
							echo "<tr><td>" . $type . " = SoundKitID <a style=\"padding-top: 0px; padding-bottom: 0px; cursor: pointer\" data-toggle=\"modal\" data-target=\"#moreInfoModal\" onclick=\"fillSkitModal(".$soundkitid.")\">" . $soundkitid ."</a></td><td>&nbsp;</td></tr>";
						}
					}

					if(!$shown){
						echo "<tr><td>No non-0 sounds</td></tr>";
					}
				}
				echo "</tr>";
				echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
			}
			echo "</table>";
		}
	}

	if(empty($tactkey['keybytes'])){
		$contenthashMatchQ->execute([$tactkey['keyname']]);
		$matches = $contenthashMatchQ->fetchAll();
		if(count($matches) > 0){
			echo "<tr><td>Content hash matches with other files</td><td><table class='table table-sm table-striped'>";
			$prevhash = "";
			$matchCount = 0;
			foreach($matches as $match){
				$matchCount++;
				if($prevhash != $match['contenthash']){
					echo "<tr><td>A file (TODO: which file??) in this key matches contenthash <span class='hash'>".$match['contenthash']."</span> from non-encrypted file " .$match['filedataid']."</td></tr>";
				}
				$prevhash = $match['contenthash'];
			}
			echo "</table></td></tr>";
		}
	}
	echo "</table>";
	echo "<hr>";
}
echo "<p>Older keys than key ID 120 are hidden for performance reasons.</p>";
echo "</div>";
?>
<script src="/files/js/files.js?v=<?=filemtime("/var/www/wow.tools/files/js/files.js")?>" crossorigin="anonymous"></script>
<?php require_once("../inc/footer.php"); ?>