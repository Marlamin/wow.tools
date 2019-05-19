<?
require_once("../inc/header.php");

if (empty($_GET['from']) || empty($_GET['to'])) {
	die("From and to buildconfig hashes required");
}

$fromBuild = getBuildConfigByBuildConfigHash($_GET['from']);
$toBuild = getBuildConfigByBuildConfigHash($_GET['to']);

$fromCDN = getVersionByBuildConfigHash($_GET['from'])['cdnconfig']['hash'];
$toCDN = getVersionByBuildConfigHash($_GET['to'])['cdnconfig']['hash'];

if (empty($fromBuild) || empty($toBuild)) {
	die("Invalid builds!");
}

$fromBuildName = parseBuildName($fromBuild['description'])['full'];
$toBuildName = parseBuildName($toBuild['description'])['full'];

?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/pagination/input.js" crossorigin="anonymous"></script>
<script src="/files/js/files.js?v=<?=filemtime("/var/www/wow.tools/files/js/files.js")?>"></script>
<script type="text/javascript" charset="utf-8">
	function debounce(func, wait, immediate) {
		var timeout;
		return function() {
			var context = this,
				args = arguments;
			var later = function() {
				timeout = null;
				if (!immediate) func.apply(context, args);
			};
			var callNow = immediate && !timeout;
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
			if (callNow) func.apply(context, args);
		};
	};

	String.prototype.capitalize = function() {
		return this.charAt(0).toUpperCase() + this.slice(1)
	}

	$(document).ready(function() {
		var table = $('#buildtable').DataTable({
			ajax: '//wow.tools/casc/root/diff_api?from=<?=$fromBuild['root_cdn']?>&to=<?=$toBuild['root_cdn']?>&cb=<?=strtotime("now")?>',
			columns: [{
					data: 'action'
				},
				{
					data: 'id'
				},
				{
					data: 'filename'
				},
				{
					data: 'type'
				}
			],
			pagingType: "input",
			pageLength: 25,
			autoWidth: false,
			deferRender: true,
			dom: "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'li><'col-sm-12 col-md-7'p>>",
			columnDefs: [{
					"targets": 0,
					"orderable": true,
					"render": function(data, type, full, meta) {

						var badge = "";
						switch (full.action) {
							case "added":
								badge = "success";
								break;
							case "removed":
								badge = "danger";
								break;
							case "modified":
								badge = "warning";
								break;
						}
						var content = "<span class='badge badge-" + badge + "'>" + full.action.capitalize() + "</span>";
						return content;
					}
				},
				{
					"targets": 2,
					"orderable": true,
					"createdCell": function(td, cellData, rowData, row, col) {
						if (!cellData) {
							$(td).css('background-color', '#ff5858');
						}
					}
				},
				{
					"targets": 4,
					"render": function(data, type, full, meta) {
						var content = "";

						switch (full.action) {
							case "added":
								switch (full.type) {
									case "db2":
										if (full.filename && full.filename != "Unknown") {
											var db2name = full.filename.replace("dbfilesclient/", "");
											content = "<a href='//wow.tools/dbc/?dbc=" + db2name + "&bc=<?= $toBuild['hash'] ?>' target='_BLANK'>View table</a>";
										}
										break;
									case "m2":
									case "wmo":
										content = "<a style='cursor: pointer' data-toggle='modal' data-target='#previewModal' onClick='fillPreviewModal(\"<?= $toBuild['hash'] ?>\", \"" + full.id + "\")'>Preview</a>";
										break;
									default:
										content = "<a style='cursor: pointer' data-toggle='modal' data-target='#previewModal' onClick='fillPreviewModal(\"<?= $toBuild['hash'] ?>\", \"" + full.id + "\")'>Preview</a>";
										break;
								}
								break;
							case "modified":
								switch (full.type) {
									case "db2":
										if (full.filename && full.filename != "Unknown") {
											var db2name = full.filename.replace("dbfilesclient/", "");
											content = "<a style='cursor: pointer' data-toggle='modal' data-target='#previewModal' onClick='fillDBCDiffModal(\"<?= $fromBuild['hash'] ?>\", \"<?= $toBuild['hash'] ?>\", \"" + db2name + "\")'>Preview</a>";

										}
										break;
									// case "blp":
									case "lua":
									case "xml":
									case "toc":
										content = "<a style='cursor: pointer' data-toggle='modal' data-target='#previewModal' onClick='fillDiffModal(\"<?= $fromBuild['hash'] ?>\", \"<?= $toBuild['hash'] ?>\", \"" + full.id + "\")'>Preview</a>";
										break;
								}
								break;
							case "removed":
								switch (full.type) {
									case "db2":
										if (full.filename && full.filename != "Unknown") {
											var db2name = full.filename.replace("dbfilesclient/", "");
											content = "<a href='//wow.tools/dbc/?dbc=" + db2name + "&bc=<?= $fromBuild['hash'] ?>' target='_BLANK'>Preview</a>";
										}
										break;
									default:
										content = "<a style='cursor: pointer' data-toggle='modal' data-target='#previewModal' onClick='fillPreviewModal(\"<?= $fromBuild['hash'] ?>\", \"" + full.id + "\")'>Preview</a>";
										break;
								}
								break;
						}

						return content;
					}
				}
			],
			initComplete: function() {
				var table = this.api();
				$('#buildtable thead tr.filters th').each(function(index, element) {
					element = $(element);
					var column = table.column(index);
					console.log(column);
					if (element.hasClass("filterable")) {
						var select = $('<select style="height: 20px" class="form-control form-control-sm"><option value=""></option></select>')
							.appendTo(element)
							.on('change', function() {
								var val = $(this).val()

								table.column(index)
									.search(val, true, false)
									.draw();
							});

						column.data().unique().sort().each(function(d, j) {
							select.append('<option value="' + d + '">' + d + '</option>')
						});
					} else if (element.hasClass("searchable")) {
						$(this).html('<input class="form-control form-control-sm" type="text" style="height: 20px;" placeholder="Search" />');
						$("input", this).on('keyup change', debounce(function() {
							table.column(index).search(this.value).draw();
						}, 50));
					}
				});
			}
		});

		window.table = table;
	});
</script>
<div class='container-fluid' id='diffContainer'>
	<h3>Showing differences between <?= $fromBuildName ?> and <?= $toBuildName ?></h3>
	<table id='buildtable' class='table table-sm table-hover maintable'>
		<thead>
			<tr class="filters">
				<th class="filterable"></th>
				<th class="searchable"></th>
				<th class="searchable"></th>
				<th class="filterable"></th>
			</tr>
			<tr>
				<th style='width: 80px'>Action</th>
				<th style='width: 170px;'>FileData ID</th>
				<th>Filename</th>
				<th style='width: 50px'>Type</th>
				<th style='width: 75px'>&nbsp;</th>
			</tr>
		</thead>
	</table>
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
<?
require_once("../inc/footer.php");
?>