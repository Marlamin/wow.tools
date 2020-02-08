<?php
require_once(__DIR__ . "/../inc/header.php");
?>
<div class='container-fluid'>
	<table class='table' id='hotfixTable'>
		<thead>
			<tr><th>Push ID</th><th>Table name</th><th>Record ID</th><th>Build</th><th>First seen at</th><th>&nbsp;</th></tr>
		</thead>
		<tbody>

		</tbody>
	</table>
</div>
<div class="modal" id="hotfixModal" tabindex="-1" role="dialog" aria-labelledby="hotfixModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="hotfixModalLabel">Hotfix diff</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p>Keep in mind hotfix diffs might be influenced by hotfixes that have since come out as well as not always being up-to-date (depending on <a href='https://wow.tools/uploader.php' target='_NEW'>user uploads</a>).</p>
			</div>
			<div class="modal-body" id="hotfixModalContent">
				<i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
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
<link href="/dbc/css/dbc.css?v=<?=filemtime(__DIR__ . "/css/dbc.css")?>" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/pagination/input.js" crossorigin="anonymous"></script>
<script src="/dbc/js/dbc.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/dbc.js")?>"></script>
<script type='text/javascript'>
	var table = $('#hotfixTable').DataTable({
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "/dbc/hotfix_api.php"
		},
		"pageLength": 25,
		"displayStart": 0,
		"autoWidth": true,
		"pagingType": "input",
		"orderMulti": false,
		"ordering": false,
		"searching": false,
		"language": { "search": "<a class='btn btn-dark btn-sm btn-outline-primary' href='#' onClick='toggleFilters()' style='margin-right: 10px'>Toggle filters</a> Search: _INPUT_ " },
		"search": { "search": "" },
		"columnDefs": [
		{
			"targets": 5,
			"render": function ( data, type, full, meta ) {
				if(full[5]){
					showRowDiff(full[1], full[3], full[2]);
					return "<div class='resultHolder' id='resultHolder-" + full[1] + "-" + full[3] + "-" + full[2] +"'><i class='fa fa-refresh fa-spin' style='font-size: 12px'></i></div>";
				}else{
					return "<i class='fa fa-ban'></i> Not available in client";
				}
			}
		}]
	});

	function showRowDiff(dbc, build, recordID){
		var beforeReq = fetch("/dbc/api/peek/" + dbc.toLowerCase() + "?build=" + build + "&col=ID&val=" + recordID + "&useHotfixes=false&calcOffset=false").then(data => data.json());
		var afterReq = fetch("/dbc/api/peek/" + dbc.toLowerCase() + "?build=" + build + "&col=ID&val=" + recordID + "&useHotfixes=true&calcOffset=false").then(data => data.json());

		Promise.all([beforeReq, afterReq])
		.then(json => {
			console.log(json[0]);
			let before = json[0].values;

			let after = json[1].values;

			let changes = "<table>";
			if(Object.keys(before).length == 0){
				Object.keys(after).forEach(function (key) {
					changes += "<tr><td>"+ key + "</td><td><ins class='diff-added'>"+after[key] + "</ins></td></tr>";
				});
			} else if(Object.keys(after).length == 0){
				Object.keys(before).forEach(function (key) {
					changes += "<tr><td>"+ key + "</td><td><del class='diff-removed'>"+before[key] + "</del></td></tr>";
				});
			}else{
				Object.keys(before).forEach(function (key) {
					if(before[key] != after[key]){
						changes += "<tr><td>" + key + "</td><td><del class='diff-removed'>" + before[key] + "</del> &rarr; <ins class='diff-added'>" + after[key] + "</ins></td></tr>";
					}
				});
			}

			changes += "</table>";

			if(changes == "<table></table>")
				changes = "No changes detected (<a href='#' data-toggle='modal' data-target='#fkModal' onclick='openFKModal(" + recordID + ", \"" + dbc.toLowerCase() + "::ID" + "\", \"" + build + "\")'>view record</a>)";

			var resultHolder = document.getElementById("resultHolder-" + dbc + "-" + build + "-" + recordID);
			if(resultHolder){
				resultHolder.innerHTML = changes;
			}
		});
	}
</script>
<?php
require_once(__DIR__ . "/../inc/footer.php");
?>