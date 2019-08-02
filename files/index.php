<?php
require_once("../inc/header.php");
$buildq = $pdo->prepare("SELECT hash, description FROM `wow_buildconfig` WHERE product = ? ORDER BY description DESC LIMIT 1;");
$lfproducts = array("wow", "wowt", "wow_classic_beta");
$lfbuilds = [];
foreach($lfproducts as $lfproduct){
	$buildq->execute([$lfproduct]);
	$lfbuilds[$lfproduct] = $buildq->fetch(PDO::FETCH_ASSOC);
}
?><link href="/files/css/files.css?v=<?=filemtime("/var/www/wow.tools/files/css/files.css")?>" rel="stylesheet">
<div class="container-fluid" id='files_container'>
	<div id='files_buttons'>
		<a href='/files/stats.php' class='btn btn-outline-primary btn-sm'>Stats</a>
		<a href='/files/checkFiles.php' class='btn btn-outline-success btn-sm'>Add filenames</a>
		<a href='/files/submitFiles.php' class='btn btn-success btn-sm'>Suggest filenames</a>
		<div class="btn-group">
			<a href='/casc/listfile/download/csv/unverified' class='btn btn-primary btn-sm'>Download community CSV listfile</a>
			<button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<span class="sr-only">Toggle Dropdown</span>
			</button>
			<div class="dropdown-menu">
				<a target='_BLANK' class="dropdown-item" href="/casc/listfile/download">TXT (Blizzard filenames only)</a>
			    <a target='_BLANK' class="dropdown-item" href="/casc/listfile/download/csv">CSV (Blizzard filenames only)</a>
			    <a target='_BLANK' class="dropdown-item" href="/casc/listfile/download/csv/unverified">Community CSV (all filenames, incl. guessed ones)</a>
<?php foreach($lfbuilds as $lfproduct => $lfbuild){?>
				<a target='_BLANK' class="dropdown-item" href="/casc/listfile/download/csv/build?buildConfig=<?=$lfbuild['hash']?>">Community CSV for <?=$lfproduct?> (<?=$lfbuild['description']?>)</a>
<?php } ?>
			</div>
		</div>
		<a href='#' id='multipleFileDLButton' target='_BLANK' class='btn btn-warning btn-sm' style='display: none'>Download selected files (1)</a>
		<a href='#' id='multipleFileAddAll' class='btn btn-info btn-sm' style='display: none'>Add all files on page</a>
		<a href='#' id='multipleFileResetButton' class='btn btn-danger btn-sm' style='display: none'>Reset queue</a>
	</div>
	<table id='files' class="table table-striped table-bordered table-condensed" cellspacing="0" style='margin: auto; ' width="100%">
		<thead>
			<tr>
				<th style='width: 50px;'>FD ID</th>
				<th>Filename</th>
				<th style='width: 100px;'>Lookup</th>
				<th style='width: 215px;'>Versions</th>
				<th style='width: 50px;'>Type</th>
				<th style='width: 20px;'>&nbsp;</th><th style='width: 20px;'>&nbsp;</th>
			</tr>
		</thead>
		<tbody>

		</tbody>
	</table>
</div>
<div class="modal" id="moreInfoModal" tabindex="-1" role="dialog" aria-labelledby="moreInfoModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
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
<div class="modal" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="helpModalLabel">Help</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="helpModalContent">
				<kbd>%</kbd> for wildcard<br>
				<kbd>^</kbd> string must start with<br>
				<kbd>type:type</kbd> for filtering by type<br>
				<kbd>chash:md5</kbd> for filter by contenthash<br>
				<kbd>unnamed</kbd> for files without filenames<br>
				<kbd>communitynames</kbd> for files with community filenames<br>
				<kbd>encrypted</kbd> for encrypted files<br>
				<kbd>encrypted:KEY</kbd> for encrypted by key<br>
				<kbd>skit:soundkitid</kbd> for searching by SoundKitID<br>
				<p>It is also possible to combine some of these options by separating them by a <kbd>,</kbd>. <br>Examples: <kbd>unnamed,type:wmo</kbd> <kbd>encrypted,creature,type:m2</kbd></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<input type='hidden' value='' id='fileBuildFilter'>
<link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/pagination/input.js" crossorigin="anonymous"></script>
<script src="/files/js/files.js?v=<?=filemtime("/var/www/wow.tools/files/js/files.js")?>"></script>
<script type='text/javascript'>
	(function() {
		var searchHash = location.hash.substr(1),
		searchString = searchHash.substr(searchHash.indexOf('search=')).split('&')[0].split('=')[1];

		if(searchString != undefined && searchString.length > 0){
			searchString = decodeURIComponent(searchString);
		}

		var page = (parseInt(searchHash.substr(searchHash.indexOf('page=')).split('&')[0].split('=')[1], 10) || 1) - 1;

		var build = searchHash.substr(searchHash.indexOf('build=')).split('&')[0].split('=')[1];

		var apiUrl = "scripts/api.php";

		if(build != undefined && build != 'undefined' && build.length > 0){
			apiUrl = "/casc/root/files?buildConfig=" + build;
			$("#fileBuildFilter").val(build);
			// $("#fileBuildFilter").trigger('change');
		}

		var sortCol = searchHash.substr(searchHash.indexOf('sort=')).split('&')[0].split('=')[1];
		var sortDesc = searchHash.substr(searchHash.indexOf('desc=')).split('&')[0].split('=')[1];

		if(!sortCol){
			sortCol = 0;
		}

		if(!sortDesc){
			sortDesc = "asc";
		}
		var previewTypes = ["ogg", "mp3", "blp", "wmo", "m2"];

		var table = $('#files').DataTable({
			"processing": true,
			"serverSide": true,
			"search": { "search": searchString },
			"ajax": apiUrl,
			"pageLength": 25,
			"language": { "search": "Search: _INPUT_ <a class='btn btn-dark btn-sm' href='#' data-toggle='modal' data-target='#helpModal'>Help</a>" },
			"displayStart": page * 25,
			"autoWidth": false,
			"pagingType": "input",
			"orderMulti": false,
			"order": [[sortCol, sortDesc]],
			"columnDefs": [
			{
				"targets": 1,
				"orderable": true,
				"createdCell": function (td, cellData, rowData, row, col) {
					if (!cellData) {
						if(!rowData[7]){
							$(td).css('background-color', '#ff5858');
						}else{
							$(td).css('background-color', '#673AB7');
						}
						$(td).css('color', 'white');
					}
				},
				"render": function ( data, type, full, meta ) {
					if(full[1]){
						var test = full[1];
					}else{
						var test = "";
						if(full[7]){
							test = full[7];
						}
					}

					if(full[6]){
						test += "<span style='float: right'><a tabindex='0' role='button' data-trigger='hover' data-container='body' data-html='true' data-toggle='popover' style='color: ;' data-content='";

						full[6].forEach(function(comment) {
							test += "By <b>" + comment['username'] +"</b> on <b>" + comment['lastedited'] +"</b><br>";
							test += comment['comment'] + "<br>";
						});

						test += "'><i class='fa fa-comment'></i></a></span>";
					}

					if(full[5]){
						if(full[5]['soundkit']){
							if(test == ""){
								test = full[5]['soundkit'].split("<br>").join("");
							}
							test += "<span style='float: right'><a tabindex='0' role='button' data-trigger='hover' data-container='body' data-html='true' data-toggle='popover' style='color: ;' data-content='" + full[5]['soundkit'] +"'><i class='fa fa-music'></i></a></span>";
						}
						if(full[5]['cmd']){
							test += "<span style='float: right'><a tabindex='0' role='button' data-trigger='hover' data-container='body' data-html='true' data-toggle='popover' style='color: ;' data-content='" + full[5]['cmd'] +"'><i class='fa fa-bug'></i></a></span>";
						}
					}

					return test;
				}
			},
			{
				"targets": 3,
				"orderable": true,
				"render": function ( data, type, full, meta ) {
					if(full[3].length > 0){
						if(full[3][0].enc == 1){
							var test = "<i style='color: red' title='File is encrypted (key " + full[3][0].key + " unknown)' class='fa fa-lock'></i> ";
						}else if(full[3][0].enc == 2){
							var test = "<i style='color: green' title='File is encrypted (key " + full[3][0].key + " is available)' class='fa fa-unlock'></i> ";
						}else if(full[3][0].enc == 3){
							var test = "<i style='color: yellow' title='File is encrypted (keys " + full[3][0].key + " are partially available)' class='fa fa-lock'></i> ";
						}else{
							var test = "";
						}
					}else{
						var test = "";
					}

					if(full[3].length > 1){
						test += "<a data-toggle='collapse' href='#versions"  + full[0] + "'>Show file versions (" + full[3].length + ")</a><div class='collapse' id='versions" + full[0] + "'>";
						full[3].forEach(function(entry) {
							if(full[1]){
								var filename = full[1].replace(/^.*[\\\/]/, '');
							}else{
								var filename = full[0] + "." + full[4];
							}
							test += "<a class='fileTableDL' href='https://wow.tools/casc/file/chash?contenthash=" + entry.contenthash + "&filedataid=" + full[0] + "&buildconfig=" + entry.buildconfig + "&cdnconfig=" + entry.cdnconfig + "&filename=" + encodeURIComponent(filename) + "'>" + entry.description + "</a>";

							if(entry.firstseen && entry.description == "WOW-18125patch6.0.1_Beta" && entry.firstseen != "WOW-18125patch6.0.1_Beta"){
								test += "<span style='float: right'><a tabindex='0' role='button' data-trigger='hover' data-container='body' data-html='true' data-toggle='popover' data-placement='top' style='color: ;' data-content='<b>(WIP, more builds coming)</b> First seen in " + entry.firstseen + "'><i class='fa fa-archive'></i></a></span>";
							}

							test += "<br>";
						});

						test += "</div>";
					}else if(full[3].length == 1){
						if(full[1]){
							var filename = full[1].replace(/^.*[\\\/]/, '');
						}else{
							var filename = full[0] + "." + full[4];
						}
						test += "<a class='fileTableDL' href='https://wow.tools/casc/file/chash?contenthash=" + full[3][0].contenthash + "&filedataid=" + full[0] + "&buildconfig=" + full[3][0].buildconfig + "&cdnconfig=" + full[3][0].cdnconfig + "&filename=" + encodeURIComponent(filename) + "'>" + full[3][0].description + "</a>";

						if(full[3][0].firstseen && full[3][0].firstseen != "WOW-18125patch6.0.1_Beta"){
							test += "<span style='float: right'><a tabindex='0' role='button' data-trigger='hover' data-container='body' data-html='true' data-toggle='popover' data-placement='top' style='color: ;' data-content='<b>(WIP, more builds coming)</b> First seen in " + full[3][0].firstseen + "'><i class='fa fa-archive'></i></a></span>";
						}
					}else{
						test += "Not shipped or non-enUS";
					}

					return test;
				}
			},
			{
				"targets": 5,
				"orderable": false,
				"render": function ( data, type, full, meta ) {
					if(full[3].length && full[3].length > 0){
						var test = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer' data-toggle='modal' data-target='#moreInfoModal' onClick='fillModal(" + full[0] + ")'><i class='fa fa-info-circle'></i></a></td>";
					}else{
						var test = "N/A";
					}
					return test;
				}
			},
			{
				"targets": 6,
				"orderable": false,
				"render": function ( data, type, full, meta ) {
					if(full[3].length && full[3][0].enc != 1){
						var test = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer' data-toggle='modal' data-target='#previewModal' onClick='fillPreviewModal(\"" + full[3][0].buildconfig + "\",\"" + full[0] + "\")'><i class='fa fa-eye'></i></a></td>";

					}else{
						var test = "<i class='fa fa-ban' style='opacity: 0.3'></i></td>";
					}
					return test;
				}
			}
			]
		});


$('#files').on( 'draw.dt', function () {
	var currentSearch = encodeURIComponent($("#files_filter label input").val());
	var currentPage = $('#files').DataTable().page() + 1;
	var currentBuild = $('#fileBuildFilter').val();
	window.location.hash = null;

	var sort = $('#files').DataTable().order();
	var sortCol = sort[0][0];
	var sortDir = sort[0][1];

	if(currentBuild != undefined && currentBuild != ''){
		window.location.hash = "search=" + currentSearch + "&page=" + currentPage + "&sort=" + sortCol +"&desc=" + sortDir + "&build=" + currentBuild;
	}else{
		window.location.hash = "search=" + currentSearch + "&page=" + currentPage + "&sort=" + sortCol +"&desc=" + sortDir;
	}

	$("[data-toggle=popover]").popover();
});

$('#fileBuildFilter').on( 'change', function () {
	console.log("Build filter changed!");
	var build = $(this).val();
	$.ajax({
		url: "/files/scripts/api.php?switchbuild=" + build,
		beforeSend: function() {
			$("#files_processing").show();
		}
	})
	.done(function( data ) {
		console.log(data);
		table.ajax.reload();
		$("#files_processing").hide();
	});
});
}());
</script>

<?php require_once("../inc/footer.php"); ?>