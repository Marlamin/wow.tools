<?php
require_once("../inc/header.php");

// Map old URL to new url for backwards compatibility
if(!empty($_GET['bc'])){
	$bcq = $pdo->prepare("SELECT description FROM wow_buildconfig WHERE hash = ?");
	$bcq->execute([$_GET['bc']]);
	$row = $bcq->fetch();

	if(!empty($row)){
		$build = parseBuildName($row['description'])['full'];
		$newurl = str_replace("bc=".$_GET['bc'], "build=".$build, $_SERVER['REQUEST_URI']);
		$newurl = str_replace(".db2", "", $newurl);
		echo "<meta http-equiv='refresh' content='0; url=https://wow.tools".$newurl."'>";
		die();
	}
}else if(!empty($_GET['dbc']) && strpos($_GET['dbc'], "db2") !== false){
	$newurl = str_replace(".db2", "", $_SERVER['REQUEST_URI']);
	echo "<meta http-equiv='refresh' content='0; url=https://wow.tools".$newurl."'>";
	die();
}

$tables = [];

foreach($pdo->query("SELECT * FROM wow_dbc_tables ORDER BY name ASC") as $dbc){
	$tables[$dbc['id']] = $dbc;
	if(!empty($_GET['dbc']) && $_GET['dbc'] == $dbc['name']) $currentDB = $dbc;
}

$dbFound = false;
?>
<link href="/dbc/css/dbc.css?v=<?=filemtime("/var/www/wow.tools/dbc/css/dbc.css")?>" rel="stylesheet">
<div class="container-fluid">
	<select id='fileFilter' class='form-control form-control-sm'>
		<option value="">Select a table</option>
		<?php foreach($tables as $table){ ?>
			<option value='<?=$table['name']?>' <? if(!empty($_GET['dbc']) && $_GET['dbc'] == $table['name']){ echo " SELECTED"; } ?>><?=$table['displayName']?></option>
		<?php }?>
	</select>
	<?php if(!empty($currentDB)){ ?>
		<form id='dbcform' action='/dbc/' method='GET'>
			<input type='hidden' name='dbc' value='<?=$_GET['dbc']?>'>
			<select id='buildFilter' name='build' class='form-control form-control-sm buildFilter'>
				<?php
				$vq = $pdo->prepare("SELECT * FROM wow_dbc_table_versions LEFT JOIN wow_dbc_versions ON wow_dbc_table_versions.versionid=wow_dbc_versions.id WHERE wow_dbc_table_versions.tableid = ?  AND wow_dbc_table_versions.hasDefinition = 1 ORDER BY version DESC");
				$vq->execute([$currentDB['id']]);
				$versions = $vq->fetchAll();
				foreach($versions as $row){
					?>
					<option value='<?=$row['version']?>'<? if(!empty($_GET['build']) && $row['version'] == $_GET['build']){ echo " SELECTED"; }?>><?=$row['version']?></option>
					<?php
				}
				?>
			</select>
			<input type='submit' id='browseButton' class='form-control form-control-sm btn btn-sm btn-primary' value='Browse'>
			<a href='' id='downloadCSVButton' class='form-control form-control-sm btn btn-sm btn-secondary'><i class='fa fa-download'></i> CSV</a>
		</form><br>
	<?php } ?>
	<?php if(!empty($_GET['build'])){ ?>
		<div id='tableContainer'>
			<table id='dbtable' class="table table-striped table-bordered table-condensed" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
		</div>
	<?php } ?>
</div>
<div class="modal" id="fkModal" tabindex="-1" role="dialog" aria-labelledby="fkModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="fkModalLabel">Foreign key lookup</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="fkModalContent">
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
<div class="modal" id="chashModal" tabindex="-1" role="dialog" aria-labelledby="chashModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="chashModalLabel">Content hash lookup</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="chashModalContent">
				<i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/pagination/input.js" crossorigin="anonymous"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>
<script src="/files/js/files.js" crossorigin="anonymous"></script>
<script src="/dbc/js/dbc.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/dbc.js")?>"></script>
<script type='text/javascript'>
	var currentBuild = 0;

	(function() {
		$('#fileFilter').select2();
		var vars = {};
		var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
			vars[key] = value;
		});

		var tableHeaders = "";

		var idHeader = 0;

		if(vars["dbc"] == null){
			var cleanDBC = "";
		}else{
			var cleanDBC = vars["dbc"].replace(".db2", "").toLowerCase();
		}

		$('#fileFilter').on( 'change', function () {
			if($(this).val() != ""){
				document.location = "https://wow.tools/dbc/?dbc=" + $(this).val();
			}
		});

		if(!cleanDBC || !vars["build"]){
			// Don't bother doing anything else if no DBC is selected
			return;
		}

		$.ajax({
			"url": "/api/header/" + cleanDBC + "/?build=" + vars["build"],
			"success": function(json) {
				if(json['error'] != null){
					if(json['error'] == "No valid definition found for this layouthash or build!"){
						json['error'] += "\n\nPlease open an issue on the WoWDBDefs repository with the DBC name and selected version on GitHub to request a definition for this build.\n\nhttps://github.com/wowdev/WoWDBDefs";
					}
					alert("An error occured on the server:\n" + json['error']);
				}
				var allCols = [];
				$.each(json['headers'], function(i, val){
					tableHeaders += "<th>" + val + "</th>";
					if(val == "ID"){
						idHeader = i;
					}
					allCols.push(i);
				});

				var fkCols = getFKCols(json['headers'], json['fks']);
				$("#tableContainer").empty();
				$("#tableContainer").append('<table id="dbtable" class="table table-striped table-bordered table-condensed" cellspacing="0" width="100%"><thead><tr>' + tableHeaders + '</tr></thead></table>');

				var searchHash = location.hash.substr(1),
				searchString = searchHash.substr(searchHash.indexOf('search=')).split('&')[0].split('=')[1];

				if(searchString != undefined && searchString.length > 0){
					searchString = decodeURIComponent(searchString);
				}

				var page = (parseInt(searchHash.substr(searchHash.indexOf('page=')).split('&')[0].split('=')[1], 10) || 1) - 1;
				var highlightRow = parseInt(searchHash.substr(searchHash.indexOf('row=')).split('&')[0].split('=')[1], 10) - 1;

				var table = $('#dbtable').DataTable({
					"processing": true,
					"serverSide": true,
					"ajax": {
						"url": "/api/data/" + vars["dbc"].toLowerCase() + "/?build=" + vars["build"],
						"data": function( result ) {
							delete result.columns;
							return result;
						}
					},
					"pageLength": 25,
					"displayStart": page * 25,
					"autoWidth": true,
					"pagingType": "input",
					"orderMulti": false,
					"ordering": false,
					"search": { "search": searchString },
					"columnDefs": [
					{
						"targets": allCols,
						"render": function ( data, type, full, meta ) {
							var returnVar = full[meta.col];

							if(meta.col in fkCols){
								if(fkCols[meta.col] == "FileData::ID"){
									returnVar = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' data-toggle='modal' data-target='#moreInfoModal' onclick='fillModal(" + full[meta.col] + ")'>" + full[meta.col] + "</a>";
								}else{
									returnVar = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' data-toggle='modal' data-target='#fkModal' onclick='openFKModal(" + full[meta.col] + ", \"" + fkCols[meta.col] + "\")'>" + full[meta.col] + "</a>";
								}
							}else{
								returnVar = full[meta.col];
							}

							return returnVar;
						}
					}],
					"createdRow": function( row, data, dataIndex ) {
						if(dataIndex == highlightRow){
							$(row).addClass('highlight');
							highlightRow = -1;
						}
					},
				});

				$('#dbtable').on( 'draw.dt', function () {
					var currentSearch = encodeURIComponent($("#dbtable_filter label input").val());
					var currentPage = $('#dbtable').DataTable().page() + 1;
					window.location.hash = "search=" + currentSearch + "&page=" + currentPage;
				});

			},
			"dataType": "json"
		});

		$('#buildFilter').on('change', function(){
			document.getElementById('downloadCSVButton').href = "https://wow.tools/api/export/?name=" + cleanDBC + "&build=" + vars["build"];
		});

		document.getElementById('downloadCSVButton').href = "https://wow.tools/api/export/?name=" + cleanDBC + "&build=" + vars["build"];
	}());
</script>
<?php require_once("../inc/footer.php"); ?>