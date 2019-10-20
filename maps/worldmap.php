<?php
require_once("../inc/header.php");
?>
<script src="/js/bufo.js"></script>
<script src="/js/js-blp.js?v=2"></script>
<style type='text/css'>
	#breadcrumbs{
		position: absolute;
		left: 50px;
	}

</style>
<div class='container-fluid'>
	<p style='height: 35px;'>
		<select name='map' id='mapSelect'>
			<option value = ''>Select a map</option>
		</select>
		<input type='checkbox' id='showExplored'> <label for='showExplored'>Show explored?</label>
	</p>
	<div id='breadcrumbs'>
	</div>
	<div id ='map'>

	</div>
</div>
<script type='text/javascript'>
	var build = "8.3.0.32218";

	const dbsToLoad = ["uimap", "uimapxmapart", "uimaparttile", "worldmapoverlay", "worldmapoverlaytile"];
	const promises = dbsToLoad.map(db => loadDatabase(db, build));
	const finalPromise = Promise.all(promises).then(loadedDBs => databasesAreLoadedNow(loadedDBs));

	var uiMap = {};
	var uiMapXMapArt = {};
	var uiMapArtTile = {};
	var worldMapOverlay = {};
	var worldMapOverlayTile = {};

	function databasesAreLoadedNow(loadedDBs){
		console.log("Loaded DBs", loadedDBs);
		uiMap = loadedDBs[0];
		uiMapXMapArt = loadedDBs[1];
		uiMapArtTile = loadedDBs[2];
		worldMapOverlay = loadedDBs[3];
		worldMapOverlayTile = loadedDBs[4];
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

	$('#mapSelect').on( 'change', function () {
		generateBreadcrumb(this.value);
		renderMap(this.value);
	});

	function generateBreadcrumb(uiMapID){
		var parent = uiMapID;

		var breadcrumbs = [];
		while(parent != 0){
			var row = getParentMapByUIMapID(parent);

			if(row == false){
				console.log("No parent found for uiMapID " + uiMapID);
				return;
			}
			parent = row.ParentUiMapID;
			breadcrumbs.unshift([row.ID, row.Name_lang]);
		}
		console.log(breadcrumbs);

		$("#breadcrumbs").html("Root ");

		breadcrumbs.forEach(function (breadcrumb){
			console.log(breadcrumb);
			$("#breadcrumbs")[0].innerHTML += " => " + breadcrumb[1] + " (" + breadcrumb[0] + ")";
		});
	}

	function getParentMapByUIMapID(uiMapID){
		console.log("Getting parent map ID for " + uiMapID);
		if(uiMapID in uiMap){
			return uiMap[uiMapID];
		}else{
			return false;
		}
	}

	function renderMap(uiMapID){
		// Remove existing images
		$(".uiMapArt").remove();

		var showExplored = $("#showExplored").prop('checked');
		uiMapXMapArt.forEach(function(uiMapXMapArtRow){
			if(uiMapXMapArtRow.UiMapID == uiMapID){
				var uiMapArtID = uiMapXMapArtRow.UiMapArtID;
				console.log("Found uiMapArtID " + uiMapArtID + " for uiMapID " + uiMapID);
				if(uiMapXMapArtRow.PhaseID > 0){
					console.log("Ignoring PhaseID " + uiMapXMapArtRow.PhaseID);
					return;
				}
				uiMapArtTile.forEach(function(uiMapArtTileRow){
					if(uiMapArtTileRow.UiMapArtID == uiMapArtID){
						// console.log(uiMapArtTileRow.RowIndex + "x" + uiMapArtTileRow.ColIndex + " = fdid " + uiMapArtTileRow.FileDataID);

						var imagePosX = 150 + uiMapArtTileRow.RowIndex * 256;
						var imagePosY = 50 + uiMapArtTileRow.ColIndex * 256;
						var bgURL = "https://wow.tools/casc/file/fdid?buildconfig=deb02554fac3ac20d9344b3f9386b7da&cdnconfig=7af3569eea7becd9b9a9adb57f15a199&filename=maptile&filedataid=" + uiMapArtTileRow.FileDataID;

						$("#map").append("<img class='uiMapArt' id='art" + uiMapArtTileRow.ID + "' src='/img/loading-256px.png' style='z-index: 1; margin: 0px; width: 256px; height: 256px; position: absolute; top: " + imagePosX + "px; left: " + imagePosY + "px;'>");
						renderBLPToIMGElement(bgURL , "art" + uiMapArtTileRow.ID);
					}
				});

				if(showExplored){
					renderExplored();
				}
			}
		});

	}
	function renderExplored(){
		var showExplored = $("#showExplored").prop('checked');

		var uiMapID = $("#mapSelect").val();
		uiMapXMapArt.forEach(function(uiMapXMapArtRow){
			if(uiMapXMapArtRow.UiMapID == uiMapID){
				var uiMapArtID = uiMapXMapArtRow.UiMapArtID;
				console.log("Found uiMapArtID " + uiMapArtID + " for uiMapID " + uiMapID);

				worldMapOverlay.forEach(function(wmoRow){
					if(wmoRow.UiMapArtID == uiMapArtID){
						worldMapOverlayTile.forEach(function(wmotRow){
							if(wmotRow.WorldMapOverlayID == wmoRow.ID){
								var layerPosX = parseInt(wmoRow.OffsetX) + 50 + (wmotRow.ColIndex * 256);
								var layerPosY = parseInt(wmoRow.OffsetY) + 150 + (wmotRow.RowIndex * 256);
								var bgURL = "https://wow.tools/casc/file/fdid?buildconfig=deb02554fac3ac20d9344b3f9386b7da&cdnconfig=7af3569eea7becd9b9a9adb57f15a199&filename=exploredmaptile&filedataid=" + wmotRow.FileDataID;

								$("#map").append("<img class='uiMapArt uiMapExploredArt' id='exploredArt" + wmotRow.ID + "' src='/img/loading-256px.png' style='z-index: 2; margin: 0px; max-width: 256px; max-height: 256px; position: absolute; top: " + layerPosY + "px; left: " + layerPosX + "px;'>");
								renderBLPToIMGElement(bgURL, "exploredArt" + wmotRow.ID);
							}
						});
					}
				});
			}
		});
	}

	$("#showExplored").on("click", function (){
		if($(this).prop('checked') == false){
			$(".uiMapExploredArt").remove();
		}else{
			renderExplored();
		}
	});
</script>
