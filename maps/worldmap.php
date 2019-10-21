<?php
require_once("../inc/header.php");
?>
<script src="/js/bufo.js"></script>
<script src="/js/js-blp.js?v=<?=filemtime("/var/www/wow.tools/js/js-blp.js")?>"></script>
<style type='text/css'>
	#breadcrumbs{
		position: absolute;
		left: 50px;
		top: 100px;
	}

	.breadcrumb{
		background-color: rgba(0,0,0,0);
	}

	#mapCanvas{
		position: absolute;
		top: 150px;
		left: 50px;
		z-index: 0;
	}
</style>
<div class='container-fluid'>
	<p style='height: 35px;'>
		<select name='map' id='mapSelect'>
			<option value = ''>Select a map</option>
		</select>
		<input type='checkbox' id='showExplored' CHECKED> <label for='showExplored'>Show explored?</label>
	</p>
	<div id='breadcrumbs'>
	</div>
	<canvas id='mapCanvas' width='1024' height='1024'></canvas>
</div>
<script type='text/javascript'>
	var build = "8.3.0.32218";

	const dbsToLoad = ["uimap", "uimapxmapart", "uimaparttile", "worldmapoverlay", "worldmapoverlaytile", "uimapart", "uimapartstylelayer"];
	const promises = dbsToLoad.map(db => loadDatabase(db, build));
	const finalPromise = Promise.all(promises).then(loadedDBs => databasesAreLoadedNow(loadedDBs));

	var uiMap = {};
	var uiMapXMapArt = {};
	var uiMapArtTile = {};
	var worldMapOverlay = {};
	var worldMapOverlayTile = {};
	var uiMapArt = {};
	var uiMapArtStyleLayer = {};

	function databasesAreLoadedNow(loadedDBs){
		console.log("Loaded DBs", loadedDBs);
		uiMap = loadedDBs[0];
		uiMapXMapArt = loadedDBs[1];
		uiMapArtTile = loadedDBs[2];
		worldMapOverlay = loadedDBs[3];
		worldMapOverlayTile = loadedDBs[4];
		uiMapArt = loadedDBs[5];
		uiMapArtStyleLayer = loadedDBs[6];

		loadedDBs[0].forEach(function (data){
			$("#mapSelect").append("<option value='" + data.ID + "'>" + data.ID + " - " + data.Name_lang);
		});

		let params = (new URL(document.location)).searchParams;
		if(params.has('id')){
			var id = params.get('id');
			renderMap(id);
		}
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

		var idCol = -1;
		header.responseJSON.headers.forEach(function (data, key){
			if (data == "ID"){
				idCol = key;
			}
		});

		data.responseJSON.data.forEach(function (data, rowID) {
			dbEntries[data[idCol]] = {};
			Object.values(data).map(function(value, key) {
				dbEntries[data[idCol]][header.responseJSON.headers[key]] = value;
			});
		});

		return dbEntries;
	}

	function generateBreadcrumb(uiMapID){
		var parent = uiMapID;

		var breadcrumbs = [];
		while(parent != 0){
			var row = getParentMapByUIMapID(parent);

			if(row == false){
				return;
			}

			parent = row.ParentUiMapID;
			breadcrumbs.unshift([row.ID, row.Name_lang]);
		}

		$("#breadcrumbs").html("<nav aria-label='breadcrumb'><ol id='breadcrumbList' class='breadcrumb'></ol></nav>");

		breadcrumbs.forEach(function (breadcrumb){
			$("#breadcrumbList").append("<li class='breadcrumb-item'><a onclick='renderMap("+ breadcrumb[0] + ")' href='#'>" + breadcrumb[1] + "</a></li>");
		});

	}

	function getParentMapByUIMapID(uiMapID){
		if(uiMapID in uiMap){
			return uiMap[uiMapID];
		}else{
			return false;
		}
	}

	function renderMap(uiMapID) {
		if ($("#mapSelect").val() != uiMapID) {
			$("#mapSelect").val(uiMapID);
		}

		generateBreadcrumb(uiMapID);

		const artStyle = getArtStyleByUIMapID(uiMapID);
		const canvas = document.getElementById("mapCanvas");
		canvas.width = artStyle.LayerWidth;
		canvas.height = artStyle.LayerHeight;

		const showExplored = $("#showExplored").prop("checked");

		const uiMapXMapArtRow = uiMapXMapArt.find(row => row && row.UiMapID == uiMapID);
		const uiMapArtID = uiMapXMapArtRow.UiMapArtID;

		if (uiMapXMapArtRow.PhaseID > 0) {
			console.log("Ignoring PhaseID " + uiMapXMapArtRow.PhaseID);
			return;
		}

		const unexploredPromises = uiMapArtTile
		.filter(row => row.UiMapArtID == uiMapArtID)
		.map(row => {
			const imagePosX = row.RowIndex * (artStyle.TileWidth / 1);
			const imagePosY = row.ColIndex * (artStyle.TileHeight / 1);
			const bgURL = `https://wow.tools/casc/file/fdid?buildconfig=deb02554fac3ac20d9344b3f9386b7da&cdnconfig=7af3569eea7becd9b9a9adb57f15a199&filename=maptile&filedataid=${row.FileDataID}`;

			return renderBLPToCanvasElement(bgURL, "mapCanvas", imagePosY, imagePosX);
		});

		Promise.all(unexploredPromises).then(_ => {
			if (showExplored) {
				renderExplored();
			}
		});

		updateURL();
	}

	function getArtStyleByUIMapID(uiMapID){
		const uiMapXMapArtRow = uiMapXMapArt.find(row => row && row.UiMapID == uiMapID);
		const uiMapArtID = uiMapXMapArtRow.UiMapArtID;
		const uiMapArtRow = uiMapArt[uiMapArtID];
		return uiMapArtStyleLayer.find(row => row && row.UiMapArtStyleID == uiMapArtRow.UiMapArtStyleID);
	}

	function renderExplored(){
		var showExplored = $("#showExplored").prop('checked');

		var uiMapID = $("#mapSelect").val();

		const artStyle = getArtStyleByUIMapID(uiMapID);
		const uiMapXMapArtRow = uiMapXMapArt.find(row => row && row.UiMapID == uiMapID);
		const uiMapArtID = uiMapXMapArtRow.UiMapArtID;

		worldMapOverlay.forEach(function(wmoRow){
			if(wmoRow.UiMapArtID == uiMapArtID){
				worldMapOverlayTile.forEach(function(wmotRow){
					if(wmotRow.WorldMapOverlayID == wmoRow.ID){
						var layerPosX = parseInt(wmoRow.OffsetX) + (wmotRow.ColIndex * (artStyle.TileWidth / 1));
						var layerPosY = parseInt(wmoRow.OffsetY) + (wmotRow.RowIndex * (artStyle.TileHeight / 1));
						var bgURL = "https://wow.tools/casc/file/fdid?buildconfig=deb02554fac3ac20d9344b3f9386b7da&cdnconfig=7af3569eea7becd9b9a9adb57f15a199&filename=exploredmaptile&filedataid=" + wmotRow.FileDataID;

						renderBLPToCanvasElement(bgURL, "mapCanvas", layerPosX, layerPosY);
					}
				});
			}
		});
	}

	function updateURL(){
		var uiMapID =  $("#mapSelect").val();
		if(uiMapID in uiMap){
			var title = "WoW.tools | Map Browser | " + uiMap[uiMapID].Name_lang;
		}else{
			var title = "WoW.tools | Map Browser";
		}

		var url = '/maps/worldmap.php?id=' + $("#mapSelect").val();

		window.history.pushState( {uiMapID: uiMapID}, title, url );

		document.title = title;
	}

	$('#mapSelect').on( 'change', function () {
		renderMap(this.value);
	});


	$("#showExplored").on("click", function (){
		if($(this).prop('checked') == false){
			renderMap($("#mapSelect").val());
		}else{
			renderExplored();
		}
	});
</script>
