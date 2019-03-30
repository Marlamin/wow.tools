<?
require_once("../inc/header.php");

$query = $pdo->query("SELECT id, filename FROM wow_rootfiles WHERE filename LIKE 'dbfilesclient%.db2'");

$allowedtables = array();
while($row = $query->fetch()){
	$allowedtables[] = str_replace("dbfilesclient/", "", $row['filename']);
	if(!empty($_GET['dbc']) && "dbfilesclient/".$_GET['dbc'] == $row['filename']){
		$id = $row['id'];
	}
}

if(!empty($id)){
	$query = $pdo->prepare("SELECT wow_rootfiles_chashes.root_cdn, wow_rootfiles_chashes.contenthash, wow_buildconfig.description, wow_buildconfig.hash, wow_buildconfig.description, wow_versions.cdnconfig FROM wow_rootfiles_chashes JOIN wow_buildconfig ON wow_buildconfig.root_cdn=wow_rootfiles_chashes.root_cdn JOIN wow_versions ON wow_buildconfig.hash=wow_versions.buildconfig WHERE filedataid = ? ORDER BY wow_buildconfig.description DESC");
	$query->execute([$id]);
	$versions = array();
	while($row = $query->fetch()){
		$rawdesc = str_replace("WOW-", "", $row['description']);
		$build = substr($rawdesc, 0, 5);
		$rawdesc = str_replace(array($build, "patch"), "", $rawdesc);
		$descexpl = explode("_", $rawdesc);
		$row['build'] = $descexpl[0].".".$build;
		if($build >= 25600){
			$versions[] = $row;
		}
	}
}

if(!empty($id) && !in_array($_GET['dbc'], $allowedtables)){
	die("Invalid DBC!");
}

?>
<link href="/dbc/css/dbc.css?v=<?=filemtime("/var/www/wow.tools/dbc/css/dbc.css")?>" rel="stylesheet">
<div class='container-fluid'>
	Select file: &nbsp; &nbsp;
	<select id='fileFilter' style='width: 225px; display: inline-block; margin-left: 5px; margin-bottom: 5px;' class='form-control form-control-sm'>
		<option value=''>Select a DBC</option>
		<? foreach($allowedtables as $table){ ?>
			<option value='<?=$table?>' <? if(!empty($_GET['dbc']) && $_GET['dbc'] == $table){ echo " SELECTED"; } ?>><?=$table?></option>
		<? }?>
	</select>
	<? if(!empty($id)){ ?>
		<form action='/dbc/' method='GET'>
			<input type='hidden' name='dbc' value='<?=$_GET['dbc']?>'>
			Select build:
			<select name='bc' id='buildFilter' style='width: 225px; display: inline-block; margin-left: 5px; margin-bottom: 5px;' class='form-control form-control-sm'>
				<?foreach($versions as $row){?>
					<option value='<?=$row['hash']?>'<? if(!empty($_GET['bc']) && $row['hash'] == $_GET['bc']){ echo " SELECTED"; }?>><?=$row['description']?></option>
				<? } ?>
			</select><br>
			<input type='submit' class='form-control form-control-sm btn btn-sm btn-primary' value='Browse' style='width: 100px; display: inline-block; margin-left: 5px;'>
			<a href='' id='downloadCSVButton' class='form-control form-control-sm btn btn-sm btn-success' style='width: 150px; display: inline-block; margin-left: 5px;'>Download as CSV</a>
		</form><br>
	<? } ?>
	<? if(!empty($_GET['bc'])){ ?>
		<div id='tableContainer'>
			<table id='dbtable' class="table table-striped table-bordered table-condensed" cellspacing="0" style='margin: auto; ' width="100%">
				<thead>
					<tr>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
		</div>
	<? } ?>
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
<script type='text/javascript'>
	var currentBuild = 0;
	function getFKCols(headers, fks){
		var fkCols = [];
		headers.forEach(function(header, index){
			var cleanedHeader = header.split('[');
			Object.keys(fks).forEach(function(key) {
				if(key == cleanedHeader[0]){
					fkCols[index] = fks[key];
				}
			});
		});
		return fkCols;
	}

	function openFKModal(value, location){
		console.log("Opening FK link to " + location + " (bc " +  $("#buildFilter").val() + ") with value " + value);
		var splitLocation = location.split("::");
		var url = "/api/peek/" + splitLocation[0].toLowerCase() + "?build=" + makeBuild() + "&bc=" + $("#buildFilter").val() + "&col=" + splitLocation[1] + "&val=" + value;
		$("#fkModalContent").html("<b>Lookup into table " + splitLocation[0].toLowerCase() + " on col '" + splitLocation[1] + "' value '" + value + "'</b><br><br><table id='fktable' class='table table-condensed table-striped'>");
		$.ajax({
			"url": url,
			"success": function(json) {
				json.values.forEach(function (value) {
					$("#fktable").append("<tr><td>" + value.item1 + "</td><td>" + value.item2 + "</td></tr>");
				});
				var numRecordsIntoPage = json.offset - Math.floor((json.offset - 1) / 25) * 25;
				var page = Math.floor(((json.offset - 1) / 25) + 1);
				$("#fkModalContent").append("<a target=\"_BLANK\" href=\"/?dbc=" + splitLocation[0].replace(".db2", "").toLowerCase() + ".db2&bc=" + $("#buildFilter").val() + "#page=" + page + "&row=" + numRecordsIntoPage + "\" class=\"btn btn-primary\">Jump to record</a>");
			}
		});

	}

	function makeBuild(){
		var text = $("#buildFilter option:selected").text();
		if(text == null){
			return "";
		}

		var rawdesc = text.replace("WOW-", "");
		var build  = rawdesc.substring(0, 5);

		var rawdesc = rawdesc.replace(build, "").replace("patch", "");
		var descexpl = rawdesc.split("_");

		return descexpl[0] + "." + build;
	}

	(function() {
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

		if(!cleanDBC){
			// Don't bother doing anything else if no DBC is selected
			return;
		}

		$.ajax({
			"url": "/api/header/" + cleanDBC + "/?build=" + makeBuild(),
			"success": function(json) {
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
				$("#tableContainer").append('<table id="dbtable" class="table table-striped table-bordered table-condensed" cellspacing="0" style="margin: auto;" width="100%"><thead><tr>' + tableHeaders + '</tr></thead></table>');

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
						"url": "/api/data/" + vars["dbc"].replace(".db2", "").toLowerCase() + "/?build=" + makeBuild(),
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
			document.getElementById('downloadCSVButton').href = "https://wow.tools/dbc/scripts/api.php?dbc=" + $("#fileFilter").val() + "&bc=" + $("#buildFilter").val() + "&type=download";
		});

		document.getElementById('downloadCSVButton').href = "https://wow.tools/dbc/scripts/api.php?dbc=" + $("#fileFilter").val() + "&bc=" + $("#buildFilter").val() + "&type=download";
	}());
</script>
<!-- <script src="https://cdn.datatables.net/scroller/2.0.0/js/dataTables.scroller.min.js" crossorigin="anonymous"></script> -->
<!-- <script type='text/javascript'>
	var Elements =
{
};

	Elements.table = $('#dbcfiles').DataTable({
		"processing": true,
		"serverSide": true,
		"searching": true,
		"ajax": {
			"url": "/files/scripts/api.php",
			"data": function ( d ) {
				return $.extend( {}, d, {
					"src": "dbc"
				} );
			}
		},
		"pageLength": 25,
		"autoWidth": false,
		"dom": "frt",
		"order": [[1, 'asc']],
		"scroller": true,
		"scrollY": 800,
		"searchDelay": 400,
		"columnDefs":
		[
		{
			"targets": 0,
			"orderable": false,
			"visible": false
		},
		{
			"targets": 1,
			"orderable": false,
			"render": function ( data, type, full, meta ) {
				if(full[1]) {
					var test = full[1].replace("dbfilesclient/", "");
				}else{
					var test = "Unknown DBC name, not supported!";
				}

				return test;
			}
		},]
	});

	$('#dbcfiles').on('click', 'tbody tr td:first-child', function() {
		var data = Elements.table.row($(this).parent()).data();
		var mostRecentVersion = data[3][0];

		$(".selected").removeClass("selected");
		$(this).parent().addClass('selected');
		console.log(mostRecentVersion);
	});
</script> -->
<? require_once("../inc/footer.php"); ?>