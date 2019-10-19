<?php
require_once("../inc/header.php");
?>
<script src="/js/bufo.js"></script>
<script src="/js/js-blp.js?v=2"></script>
<div class='container-fluid'>
	<p style='height: 35px;'>
		<select name='map' id='mapSelect'>
			<option value = ''>Select a map</option>
		</select>
	</p>
	<div id ='map'>

	</div>
</div>
<script type='text/javascript'>
	var build = "8.3.0.32218";

	const dbsToLoad = ["uimap", "uimapxmapart", "uimaparttile"];
	const promises = dbsToLoad.map(db => loadDatabase(db, build));
	const finalPromise = Promise.all(promises).then(loadedDBs => databasesAreLoadedNow(loadedDBs));

	var uiMap = {};
	var uiMapXMapArt = {};
	var uiMapArtTile = {};

	function databasesAreLoadedNow(loadedDBs){
		console.log("Loaded DBs", loadedDBs);
		uiMap = loadedDBs[0];
		uiMapXMapArt = loadedDBs[1];
		uiMapArtTile = loadedDBs[2];
		loadedDBs[0].forEach(function (data){
			$("#mapSelect").append("<option value='" + data.ID + "'>" + data.ID + " - " + data.Name_lang);
		});
	}

	function loadDatabase(database, build){
		console.log("Loading database " + database + " for build " + build);
		const header = loadHeaders(database, build);
		const data = loadData(database, build);
		return mapEntries(database, header, data);
	}

	function loadHeaders(database, build){
		console.log("Loading " + database + " headers for build " + build);
		return $.get("https://wow.tools/api/header/" + database + "/?build=" + build);
	}

	function loadData(database, build){
		console.log("Loading " + database + " data for build " + build);
		return $.post("https://wow.tools/api/data/" + database + "/?build=" + build, { draw: 1, start: 0, length: 50000});
	}

	async function mapEntries(database, header, data){
		await header;
		await data;

		var dbEntries = [];

		data.responseJSON.data.forEach(function (data, rowID) {
			dbEntries[rowID] = {};
			Object.values(data).map(function(value, key) {
				dbEntries[rowID][header.responseJSON.headers[key]] = value;
			});
		});

		return dbEntries;
	}

	$('#mapSelect').on( 'change', function () {
		// Remove existing images
		$(".uiMapArt").remove();

		var uiMapID = this.value;
		uiMapXMapArt.forEach(function(uiMapXMapArtRow){
			if(uiMapXMapArtRow.UiMapID == uiMapID){
				var uiMapArtID = uiMapXMapArtRow.UiMapArtID;
				console.log("Found uiMapArtID " + uiMapArtID + " for uiMapID " + uiMapID);
				uiMapArtTile.forEach(function(uiMapArtTileRow){
					if(uiMapArtTileRow.UiMapArtID == uiMapArtID){
						// console.log(uiMapArtTileRow.RowIndex + "x" + uiMapArtTileRow.ColIndex + " = fdid " + uiMapArtTileRow.FileDataID);

						var imagePosX = 100 + uiMapArtTileRow.RowIndex * 256;
						var imagePosY = 100 + uiMapArtTileRow.ColIndex * 256;
						var bgURL = "https://wow.tools/casc/file/fdid?buildconfig=	deb02554fac3ac20d9344b3f9386b7da&cdnconfig=7af3569eea7becd9b9a9adb57f15a199&filename=maptile&filedataid=" + uiMapArtTileRow.FileDataID;

						$("#map").append("<img class='uiMapArt' id='art" + uiMapArtTileRow.ID + "' src='/img/loading-256px.png' style='margin: 0px; width: 256px; height: 256px; position: absolute; top: " + imagePosX + "px; left: " + imagePosY + "px;' onload=\"renderBLPToIMGElement('" + bgURL + "', 'art" + uiMapArtTileRow.ID +"')\" >");
					}
				});
			}
		});
	});
</script>
