<?
require_once("../inc/header.php");

if(empty($_GET['from']) || empty($_GET['to'])){
	die("From and to buildconfig hashes required");
}

$fromBuild = getBuildConfigByBuildConfigHash($_GET['from']);
$toBuild = getBuildConfigByBuildConfigHash($_GET['to']);

if(empty($fromBuild) || empty($toBuild)){
	die("Invalid builds!");
}

$fromBuildName = parseBuildName($fromBuild['description'])['full'];
$toBuildName = parseBuildName($toBuild['description'])['full'];

?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/pagination/input.js" crossorigin="anonymous"></script>
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
				},
				{
					data: 'content_hash'
				}
			],
			pagingType: "input",
			pageLength: 100,
			autoWidth: false,
			initComplete: function() {
				var table = this.api();
				$('#buildtable thead tr.filters th').each(function(index, element) {
					element = $(element);
					var column = table.column(index);
					console.log(column);
					if (element.hasClass("filterable")) {
						var select = $('<select><option value=""></option></select>')
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
	<h3>Showing root file differences between <?=$fromBuildName?> and <?=$toBuildName?></h3>
	<table id='buildtable' class='table table-sm table-hover maintable'>
		<thead>
			<tr class="filters">
				<th class="filterable"></th>
				<th class="searchable"></th>
				<th class="searchable"></th>
				<th class="filterable"></th>
				<th></th>
			</tr>
			<tr>
				<th style='width: 80px'>Action</th>
				<th style='width: 170px;'>FileData ID</th>
				<th>Filename</th>
				<th style='width: 50px'>Type</th>
				<th style='width: 240px'>Content Hash</th>
			</tr>
		</thead>
	</table>
</div>
<?
require_once("../inc/footer.php");
?>