<?php
require_once("../inc/header.php");
$builds = $pdo->query("SELECT version FROM wow_builds ORDER BY version DESC")->fetchAll(PDO::FETCH_COLUMN);

if(!empty($_GET['build']) && in_array($_GET['build'], $builds)){
	$selectedBuild = $_GET['build'];
}else{
	$selectedBuild = $builds[0];
}
?>
<script src="/js/bufo.js"></script>
<script src="/js/js-blp.js?v=2"></script>
<link href="/dbc/css/dbc.css?v=<?=filemtime("/var/www/wow.tools/dbc/css/dbc.css")?>" rel="stylesheet">
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

						$("#encounterJournalInstanceHolder").append("<div class='encounterJournalInstance'><h4>" + row.Name_lang + "</h4><p>" + row.Description_lang + "</p><a class='btn btn-xs btn-primary ejButton' href='#'>View</a></div>");
						//renderBLPToIMGElement(bgURL, "encounterBackground" + row.ID);
					});
				});
			});
		}
	</script>
</div>
<?php
// require_once("../inc/footer.php");
?>