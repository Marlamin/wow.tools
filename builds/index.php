<?php
if(!empty($_GET['api']) && $_GET['api'] == "buildinfo"){
	require_once(__DIR__ . "/../inc/config.php");

	if(empty($_GET['versionid']) || !filter_var($_GET['versionid'], FILTER_VALIDATE_INT)){
		die("Invalid build ID!");
	}

	$query = $pdo->prepare("SELECT
	wow_versions.id as versionid,
	wow_versions.cdnconfig,
	wow_versions.buildconfig,
	wow_versions.patchconfig,
	wow_versions.complete as versioncomplete,
	wow_versions.product as versionproduct,
	wow_buildconfig.id as buildconfigid,
	wow_buildconfig.description,
	wow_buildconfig.product,
	wow_buildconfig.encoding,
	wow_buildconfig.encoding_cdn,
	wow_buildconfig.root,
	wow_buildconfig.root_cdn,
	wow_buildconfig.install,
	wow_buildconfig.install_cdn,
	wow_buildconfig.download,
	wow_buildconfig.download_cdn,
	wow_buildconfig.size,
	wow_buildconfig.size_cdn,
	wow_buildconfig.unarchivedcount,
	wow_buildconfig.unarchivedcomplete,
	wow_buildconfig.complete as buildconfigcomplete,
	wow_buildconfig.builton,
	wow_cdnconfig.archivecount,
	wow_cdnconfig.archivecomplete,
	wow_cdnconfig.indexcomplete,
	wow_cdnconfig.patcharchivecount,
	wow_cdnconfig.patcharchivecomplete,
	wow_cdnconfig.patchindexcomplete,
	wow_cdnconfig.complete as cdnconfigcomplete,
	wow_patchconfig.patch,
	wow_patchconfig.complete as patchconfigcomplete
	FROM wow_versions
	LEFT OUTER JOIN wow_buildconfig ON wow_versions.buildconfig=wow_buildconfig.hash
	LEFT OUTER JOIN wow_cdnconfig ON wow_versions.cdnconfig=wow_cdnconfig.hash
	LEFT OUTER JOIN wow_patchconfig ON wow_versions.patchconfig=wow_patchconfig.hash
	WHERE wow_versions.id = ?
	");

	$query->execute([$_GET['versionid']]);

	$build = $query->fetch(PDO::FETCH_ASSOC);

	if(empty($build)){
		die("Version not found!");
	}

	echo "<table class='table table-striped table-condensed'>";
	echo "<tr><td>Description</td><td>".$build['description']."</td></tr>";
	echo "<tr><td>Product</td><td>".$build['product']."</td></tr>";
	if(!empty($build['builton'])) { echo "<tr><td>Compiled at</td><td>".$build['builton']."</td></tr>"; }
	echo "</table>";
	echo "<h4>Configs</h4>";
	echo "<table class='table table-sm table-striped table-condensed'>";
	echo "<thead>
	<tr>
	<th>File</th>
	<th>Encoding/CDN hash</th>
	</tr>
	</thead>";
	if(!empty($build['buildconfig'])){
		echo "<tr><td>Build config (<a href='#' data-toggle='modal' data-target='#configModal' onClick='fillConfigModal(\"".$build['buildconfig']."\")'>show</a>)</td><td>";
		if($build['buildconfigcomplete'] == 1){
			echo "<span class='badge badge-success hash'>".$build['buildconfig']."</span>";
		}else{
			echo "<span class='badge badge-danger hash'>".$build['buildconfig']."</span>";
		}
		echo "</td></tr>";
	}

	if(!empty($build['cdnconfig'])){
		echo "<tr><td>CDN config (<a href='#' data-toggle='modal' data-target='#configModal' onClick='fillConfigModal(\"".$build['cdnconfig']."\")'>show</a>)</td><td>";
		if($build['cdnconfigcomplete'] == 1){
			echo "<span class='badge badge-success hash'>".$build['cdnconfig']."</span>";
		}else{
			echo "<span class='badge badge-danger hash'>".$build['cdnconfig']."</span>";
		}
		echo "</td></tr>";		
	}

	if(!empty($build['patchconfig'])){
		echo "<tr><td>Patch config (<a href='#' data-toggle='modal' data-target='#configModal' onClick='fillConfigModal(\"".$build['patchconfig']."\")'>show</a>)</td><td>";
		if($build['patchconfigcomplete'] == 1){
			echo "<span class='badge badge-success hash'>".$build['patchconfig']."</span>";
		}else{
			echo "<span class='badge badge-danger hash'>".$build['patchconfig']."</span>";
		}
		echo "</td></tr>";		
	}

	echo "</table>";
	echo "<h4>Build config files</h4>";
	echo "<table class='table table-sm table-striped table-condensed'>";
	echo "<thead>
	<tr>
	<th>File</th>
	<th>Content hash</th>
	<th>Encoding/CDN hash</th>
	</tr>
	</thead>";

	if(!empty($build['encoding'])) {
		echo "<tr><td>Encoding</td><td><span class='badge badge-secondary hash'>".$build['encoding']."</span></td><td>"; 
		if(!empty($build['encoding_cdn']) && doesFileExist("data", $build['encoding_cdn'], $allowedproducts["wow"]['cdndir'])) {
			echo "<span class='badge badge-success hash'>".$build['encoding_cdn']."</span>";
		} else {
			echo "<span class='badge badge-danger hash'>".$build['encoding_cdn']."</span>";
		}
		echo "</td></tr>";
	}
		
	if(!empty($build['root'])) {
		echo "<tr><td>Root</td><td><span class='badge badge-secondary hash'>".$build['root']."</span></td><td>"; 
		if(!empty($build['root_cdn']) && doesFileExist("data", $build['root_cdn'], $allowedproducts["wow"]['cdndir'])) {
			echo "<span class='badge badge-success hash'>".$build['root_cdn']."</span>";
		} else {
			echo "<span class='badge badge-danger hash'>".$build['root_cdn']."</span>";
		}
		echo "</td></tr>";
	}

	if(!empty($build['install'])) {
		echo "<tr><td>Install (<a target='_BLANK' href='/builds/extract.php?build=".$build['buildconfig']."'>file list</a>)</td><td><span class='badge badge-secondary hash'>".$build['install']."</span></td><td>"; 
		if(!empty($build['install_cdn']) && doesFileExist("data", $build['install_cdn'], $allowedproducts["wow"]['cdndir'])) {
			echo "<span class='badge badge-success hash'>".$build['install_cdn']."</span>";
		} else {
			echo "<span class='badge badge-danger hash'>".$build['install_cdn']."</span>";
		}
		echo "</td></tr>";
	}

	if(!empty($build['download'])) {
		echo "<tr><td>Download</td><td><span class='badge badge-secondary hash'>".$build['download']."</span></td><td>"; 
		if(!empty($build['download_cdn']) && doesFileExist("data", $build['download_cdn'], $allowedproducts["wow"]['cdndir'])) {
			echo "<span class='badge badge-success hash'>".$build['download_cdn']."</span>";
		} else {
			echo "<span class='badge badge-danger hash'>".$build['download_cdn']."</span>";
		}
		echo "</td></tr>";
	}

		if(!empty($build['size'])) {
		echo "<tr><td>Size</td><td><span class='badge badge-secondary hash'>".$build['size']."</span></td><td>"; 
		if(!empty($build['size_cdn']) && doesFileExist("data", $build['size_cdn'], $allowedproducts["wow"]['cdndir'])) {
			echo "<span class='badge badge-success hash'>".$build['size_cdn']."</span>";
		} else {
			echo "<span class='badge badge-danger hash'>".$build['size_cdn']."</span>";
		}
		echo "</td></tr>";
	}

	echo "<tr><td>Unarchived</td>";

	if($build['unarchivedcomplete'] == $build['unarchivedcount']){
		echo "<td colspan='2' style='color: green'>";
	}else{
		echo "<td colspan='2' style='color: red'>";
	}

	if($build['unarchivedcount'] == 0){ 
		$build['unarchivedcount'] = "???"; 
	}

	echo $build['unarchivedcomplete']."/".$build['unarchivedcount']."</td></tr>";
	echo "</table>";
	echo "<h4>CDN config files</h4>";
	echo "<table class='table table-sm table-striped table-condensed'>";
	echo "<thead>
	<tr>
	<th style='width: 200px'>Type</th>
	<th>Status</th>
	</tr>
	</thead>";

	echo "<tr><td>Archives</td>";
	if($build['archivecomplete'] == $build['archivecount']){
		echo "<td style='color: green'>";
	}else{
		echo "<td style='color: red'>";
	}
	echo $build['archivecomplete']."/".$build['archivecount']."</td></tr>";

	echo "<tr><td>Archive indexes</td>";
	if($build['indexcomplete'] == $build['archivecount']){
		echo "<td style='color: green'>";
	}else{
		echo "<td style='color: red'>";
	}
	echo  $build['indexcomplete']."/".$build['archivecount']."</td></tr>";

	echo "<tr><td>Patch archives</td>";
	if($build['patcharchivecomplete'] == $build['patcharchivecount']){
		echo "<td style='color: green'>";
	}else{
		echo "<td style='color: red'>";
	}
	echo $build['patcharchivecomplete']."/".$build['patcharchivecount']."</td></tr>";

	echo "<tr><td>Patch archive indexes</td>";
	if($build['patchindexcomplete'] == $build['patcharchivecount']){
		echo "<td style='color: green'>";
	}else{
		echo "<td style='color: red'>";
	}
	echo $build['patchindexcomplete']."/".$build['patcharchivecount']."</td></tr>";
	echo "</table>";
	die();
}else if(!empty($_GET['api']) && $_GET['api'] == "configdump"){
	if(!empty($_GET['config']) && strlen($_GET['config']) == 32 && ctype_xdigit($_GET['config'])){
		echo "<pre>";
		echo file_get_contents(__DIR__ . "/../tpr/wow/config/".$_GET['config'][0].$_GET['config'][1]."/".$_GET['config'][2].$_GET['config'][3]."/".$_GET['config']);
		echo "</pre>";
	}else{
		die("Invalid config!");
	}

	die();
}

require_once("../inc/header.php");

$query = "SELECT
wow_versions.id as versionid,
wow_versions.cdnconfig,
wow_versions.buildconfig,
wow_versions.patchconfig,
wow_versions.complete as versioncomplete,
wow_versions.product as versionproduct,
wow_buildconfig.id as buildconfigid,
wow_buildconfig.description,
wow_buildconfig.product,
wow_buildconfig.complete as buildconfigcomplete,
wow_buildconfig.builton,
wow_cdnconfig.archivecomplete,
wow_cdnconfig.indexcomplete,
wow_cdnconfig.patcharchivecomplete,
wow_cdnconfig.patchindexcomplete,
wow_cdnconfig.complete as cdnconfigcomplete,
wow_patchconfig.patch,
wow_patchconfig.complete as patchconfigcomplete
FROM wow_versions
LEFT OUTER JOIN wow_buildconfig ON wow_versions.buildconfig=wow_buildconfig.hash
LEFT OUTER JOIN wow_cdnconfig ON wow_versions.cdnconfig=wow_cdnconfig.hash
LEFT OUTER JOIN wow_patchconfig ON wow_versions.patchconfig=wow_patchconfig.hash
ORDER BY wow_buildconfig.description DESC
";
$res = $pdo->query($query);
$allbuilds = $res->fetchAll();
?>
<link href="/builds/css/builds.css?v=<?=filemtime(__DIR__ . "/css/builds.css")?>" rel="stylesheet">
<script type='text/javascript' src='/builds/js/builds.js?v=<?=filemtime(__DIR__ . "/js/builds.js")?>'></script>
<div class="modal" id="installDiffModal" tabindex="-1" role="dialog" aria-labelledby="installDiffModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="installDiffModalLabel">Install diff</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="installDiffModalContent">
				<i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="moreInfoModal" tabindex="-1" role="dialog" aria-labelledby="moreInfoModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="moreInfoModalLabel">Version information</h5>
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
<div class="modal" id="configModal" tabindex="-1" role="dialog" aria-labelledby="configModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="configModalLabel">Raw config</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="configModalContent">
				<i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div class='container-fluid'>
	<h3 style='float: left'><?=count($allbuilds)?> builds in DB</h3>
	<div style='float: left; margin-left: 10px; position: sticky; top: 0;'><a href='#' class='btn btn-primary btn-sm disabled' id='diffButton'>Diff builds</a> <a href='#' class='btn btn-success btn-sm' style='display :none' id='openDiffButton' target='_BLANK'>Open diff</a> <a href='#' class='btn btn-info btn-sm' style='display :none' id='openInstallDiffButton' href='#'>Open install diff</a> <a href='#' class='btn btn-danger btn-sm' style='display: none' id='resetButton'>Reset</a></div>
	<table id='buildtable' class='table table-sm table-hover maintable' style='clear: both'>
		<thead><tr><th>Patch</th><th>Build</th><th>Branch</th><th>Build config</th><th>Patch config</th><th>CDN config</th><th>Build time</th><th>&nbsp;</th></tr></thead>
		<?php foreach($allbuilds as $row){
			if(empty($row['product'])) $row['product'] = $row['versionproduct'];

			$buildarr = parseBuildName($row['description']);
			echo "<td style='width: 50px'>".$buildarr['patch']."</td>";
			echo "<td style='width: 50px'>".$buildarr['build']."</td>";
			echo "<td style='width: 100px'>".prettyBranch($row['product'])."</td>";
			echo "<td style='width: 600px'>";
			echo "<span class='hash buildconfighash'>".$row['buildconfig']."</span>";

			if($row['buildconfigcomplete'] == 0) {
				echo " <span class='badge badge-danger'>Incomplete</span>";
			}

			echo "</td>";
			echo "<td style='width: 300px'>";
			echo "<span class='hash'>".$row['patchconfig']."</span>";

			if(isset($row['patchconfigcomplete'])){
				if($row['patchconfigcomplete'] == 0){
					echo " <span class='badge badge-danger'>Incomplete</span>";
				}
			}

			echo "</td>";
			echo "<td style='width: 300px;'>";
			echo "<span class='hash'>".$row['cdnconfig']."</span>";

			if(isset($row['cdnconfigcomplete'])){
				if($row['cdnconfigcomplete'] == 0){
					echo " <span class='badge badge-danger'>Incomplete</span>";
				}
			}
			echo "</td>";

			echo "<td style='width: 150px'>".$row['builton']."</td>";
			echo "<td style='width: 100px'>";
			echo "<a href='#' data-toggle='modal' data-target='#moreInfoModal' onClick='fillVersionModal(".$row['versionid'].")'>Show details</a>";
			echo "</td>";
			echo "</tr>\n";
		} ?>
	</table>
</div>
<link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/pagination/input.js" crossorigin="anonymous"></script>
<script type='text/javascript'>
var table = $('#buildtable').DataTable({
			"pagingType": "input",
			"pageLength": 25,
			"order": [[1, 'desc']],
			"lengthMenu": [[25, 100, 500, 1000], [25, 100, 500, 1000]],
			"columnDefs": [
			{
				"targets": [2,3,4,5,7],
				"orderable": false,
			}],
		});

</script>
<?php require_once("../inc/footer.php"); ?>