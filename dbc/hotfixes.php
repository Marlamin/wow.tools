<?php
require_once(__DIR__ . "/../inc/header.php");
?>
<div class='container-fluid'>
	<table class='table' id='hotfixTable'>
		<thead>
			<tr><th>Push ID</th><th>Table name</th><th>Record ID</th><th>Build</th><th>Valid?</th><th>First seen at</th><th>&nbsp;</th></tr>
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
<script src="/dbc/js/dbc.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/dbc.js")?>"></script>
<script src="/dbc/js/flags.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/flags.js")?>"></script>
<script src="/dbc/js/enums.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/enums.js")?>"></script>
<script src="https://wow.tools/js/diff_match_patch.js"></script>
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
		"searching": true,
		"language": { "search": "Search: _INPUT_ " },
		"search": { "search": "" },
		"columnDefs": [
		{
			"targets": 2,
			"render": function ( data, type, full, meta ) {
				return "<a href='#' style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' data-toggle='modal' data-target='#fkModal' onclick='openFKModal(" + full[2] + ", \"" + full[1].toLowerCase() + "::ID" + "\", \"" + full[3] + "\")'>" + full[2] + "</a>";
			}
		},
		{
			"targets": 4,
			"render": function ( data, type, full, meta ) {
				if(full[4] == 1){
					return "Valid";
				}else{
					return "Invalidated";
				}
			}
		},
		{
			"targets": 6,
			"render": function ( data, type, full, meta ) {
				if(full[6]){
					showRowDiff(full[1], full[3], full[2]);
					return "<div class='resultHolder' id='resultHolder-" + full[1] + "-" + full[3] + "-" + full[2] +"'><i class='fa fa-refresh fa-spin' style='font-size: 12px'></i></div>";
				}else{
					return "<i class='fa fa-ban'></i> Not available in client";
				}
			}
		}]
	});

	function getAddendum(dbc, col, value){
		let addendum = "";
		dbc = dbc.toLowerCase();
		if(enumMap.has(dbc + "." + col)){
			addendum = " (" + enumMap.get(dbc + "." + col)[value] + ")";
		}

		return addendum;
	}

	function showRowDiff(dbc, build, recordID){
		var beforeReq = fetch("/dbc/api/peek/" + dbc.toLowerCase() + "?build=" + build + "&col=ID&val=" + recordID + "&useHotfixes=false&calcOffset=false").then(data => data.json());
		var afterReq = fetch("/dbc/api/peek/" + dbc.toLowerCase() + "?build=" + build + "&col=ID&val=" + recordID + "&useHotfixes=true&calcOffset=false").then(data => data.json());

		Promise.all([beforeReq, afterReq])
		.then(json => {
			const before = json[0].values;
			const after = json[1].values;

			let changes = "<table>";

			if(Object.keys(before).length == 0){
				Object.keys(after).forEach(function (key) {
					let addendum = getAddendum(dbc, key, after[key]);
					changes += "<tr><td>"+ key + "</td><td><ins class='diff-added'>"+ after[key] + addendum + "</ins></td></tr>";
				});
			} else if(Object.keys(after).length == 0){
				Object.keys(before).forEach(function (key) {
					let addendum = getAddendum(dbc, key, before[key]);
					changes += "<tr><td>"+ key + "</td><td><del class='diff-removed'>"+ before[key] + addendum + "</del></td></tr>";
				});
			}else{
				Object.keys(before).forEach(function (key) {
					if(before[key] != after[key]){
						if (!isNaN(before[key]) && !isNaN(after[key])) {
							let addendumBefore = getAddendum(dbc, key, before[key]);
							let addendumAfter = getAddendum(dbc, key, after[key]);
							changes += "<tr><td>" + key + "</td><td><del class='diff-removed'>" + before[key] + addendumBefore + "</del> &rarr; <ins class='diff-added'>" + after[key] + addendumAfter + "</ins></td></tr>";
						} else {
							var dmp = new diff_match_patch();
							var dmp_diff = dmp.diff_main(before[key], after[key]);
							dmp.diff_cleanupSemantic(dmp_diff);
							data = dmp.diff_prettyHtml(dmp_diff);
							changes += "<tr><td>" + key + "</td><td>" + data + "</td></tr>";
						}
					}
				});
			}

			changes += "</table>";

			if(changes == "<table></table>"){
				changes = "No changes detected (<a href='#' data-toggle='modal' data-target='#fkModal' onclick='openFKModal(" + recordID + ", \"" + dbc.toLowerCase() + "::ID" + "\", \"" + build + "\")'>view record</a>)";
			}

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