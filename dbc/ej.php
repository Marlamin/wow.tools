<?php
require_once(__DIR__ . "/../inc/header.php");
$builds = $pdo->query("SELECT version FROM wow_builds ORDER BY version DESC")->fetchAll(PDO::FETCH_COLUMN);

if(!empty($_GET['build']) && in_array($_GET['build'], $builds)){
	$selectedBuild = $_GET['build'];
}else{
	$selectedBuild = $builds[0];
}
?>
<script src="/js/bufo.js"></script>
<script src="/js/js-blp.js?v=2"></script>
<link href="/dbc/css/dbc.css?v=<?=filemtime(__DIR__ . "/css/dbc.css")?>" rel="stylesheet">
<div class='container-fluid'>
	<h3>Encounter Journal
		<select class='form-control form-control-sm buildFilter' style='width: 250px; float: right;' id='buildFilter'>
			<?php foreach($builds as $build){ ?>
				<option value='<?=$build?>' <?php if($build == $selectedBuild){?>SELECTED<?php } ?>><?=$build?></option>
			<?php } ?>
		</select>
	</h3>
	<div id='encounterJournalInstanceHolder'>

	</div>
	<script type='text/javascript'>
		$('#buildFilter').on('change', function(){
			var build = $("#buildFilter").val();
			document.location = 'https://wow.tools/dbc/ej.php?build=' + build;
			loadEncounterJournal(build);
		});

		var build = $("#buildFilter").val();
		loadEncounterJournal(build);

		function loadEncounterJournal(build){
			var postVars = { draw: 1, start: 0, length: 1000};
			$.get("https://wow.tools/dbc/api/header/journalinstance/?build=" + build, function (header) {
				$.post( "https://wow.tools/dbc/api/data/journalinstance/?build=" + build, postVars, function( data ) {
					var journalInstance = [];
					data.data.forEach(function (data, rowID) {
						journalInstance[rowID] = {};
						Object.values(data).map(function(value, key) {
							journalInstance[rowID][header.headers[key]] = value;
						});
					});

					journalInstance.reverse();

					journalInstance.forEach(function(row, id) {
						var bgURL = "https://wow.tools/casc/file/fdid?buildconfig=eb9dc13f6f32a1b4992b61d6217dd6ab&cdnconfig=590010eef6130ebf592c43f48caea382&filename=data&filedataid=" + row.BackgroundFileDataID;

						$("#encounterJournalInstanceHolder").append("<div class='encounterJournalInstance'><h4>" + row.Name_lang + "</h4><p>" + row.Description_lang + "</p><a class='btn btn-xs btn-primary ejButton' href='#' data-toggle='modal' data-target='#moreInfoModal' onclick='loadInstance(" + row.ID + ")'>View</a></div>");
						//renderBLPToIMGElement(bgURL, "encounterBackground" + row.ID);
					});
				});
			});
		}

		function loadInstance(instanceID){
			$("#moreInfoModalContent").html("<h2>Encounters</h2><ul class='nav nav-tabs' id='encountersTab' role='tablist'></ul><div class='tab-content' id='encountersTabContent'></div>");
			var postVars = { draw: 1, start: 0, length: 1000, "columns[5][search][value]": instanceID};
			$.get("https://wow.tools/dbc/api/header/journalencounter/?build=" + build, function (header) {
				$.post( "https://wow.tools/dbc/api/data/journalencounter/?build=" + build, postVars, function( data ) {
					var journalEncounter = [];
					data.data.forEach(function (data, rowID) {
						journalEncounter[rowID] = {};
						Object.values(data).map(function(value, key) {
							journalEncounter[rowID][header.headers[key]] = value;
						});
					});

					journalEncounter.reverse();

					journalEncounter.forEach(function(row, id) {
						$("#encountersTab").append("<li class='nav-item'><a class='nav-link' id='encounter" + row.ID + "-tab' data-toggle='tab' href='#encounterTab" + row.ID + "' role='tab' aria-controls='home' aria-selected='true'>" + row.Name_lang + "</a></li>");
						$("#encountersTabContent").append("<div class='tab-pane fade show' id='encounterTab" + row.ID + "' role='tabpanel'>" + row.Description_lang + "</div>");
						console.log(loadSections(row.ID));
					});
				});
			});
		}

		function loadSections(encounterID){
			var postVars = { draw: 1, start: 0, length: 1000, "columns[3][search][value]": encounterID};
			$.get("https://wow.tools/dbc/api/header/journalencountersection/?build=" + build, function (header) {
				$.post( "https://wow.tools/dbc/api/data/journalencountersection/?build=" + build, postVars, function( data ) {
					var journalEncounterSection = [];
					data.data.forEach(function (data, rowID) {
						journalEncounterSection[rowID] = {};
						Object.values(data).map(function(value, key) {
							journalEncounterSection[rowID][header.headers[key]] = value;
						});
					});

					journalEncounterSection.forEach(function(row, id) {
						console.log(row);
						$("#encounterTab" + encounterID).append("<h5>" + row.Title_lang + "</h5><p>" + parseWoWText(row.BodyText_lang) + "</p>");
					});
				});
			});
		}

		// By Maku
		function parseWoWText(input) {
			if (typeof input !== 'string' || input instanceof String) {
				return input
			}

			var output = input
			.replace(/\|n/g, "\n")
			.replace(/\|H[^\|]+\|h/g, "")
			.replace(/\|[cC][0-9A-Fa-f]{8}/g, "")
			.replace(/\|[rRh]/g, "")
			.replace(/\$bullet;/g, "<br>•")
			return output
		}
	</script>
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
</html>