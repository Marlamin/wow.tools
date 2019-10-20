<?php
require_once("../inc/header.php");
?>
<script src="/js/bufo.js"></script>
<script src="/js/js-blp.js?v=2"></script>
<style type='text/css'>
	#breadcrumbs{
		position: absolute;
		left: 50px;
		top: 100px;
	}

	.breadcrumb{
		background-color: rgba(0,0,0,0);
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
	<div id ='map'>

	</div>
</div>
<script type='text/javascript'>
	var build = "8.3.0.32218";

	const dbsToLoad = ["uimap", "uimapxmapart", "uimaparttile", "worldmapoverlay", "worldmapoverlaytile", "uimapart"];
	const promises = dbsToLoad.map(db => loadDatabase(db, build));
	const finalPromise = Promise.all(promises).then(loadedDBs => databasesAreLoadedNow(loadedDBs));

	var uiMap = {};
	var uiMapXMapArt = {};
	var uiMapArtTile = {};
	var worldMapOverlay = {};
	var worldMapOverlayTile = {};
	var uiMapArt = {};

	function databasesAreLoadedNow(loadedDBs){
		console.log("Loaded DBs", loadedDBs);
		uiMap = loadedDBs[0];
		uiMapXMapArt = loadedDBs[1];
		uiMapArtTile = loadedDBs[2];
		worldMapOverlay = loadedDBs[3];
		worldMapOverlayTile = loadedDBs[4];
		uiMapArt = loadedDBs[5];

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

	$('#mapSelect').on( 'change', function () {
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

		$("#breadcrumbs").html("<nav aria-label='breadcrumb'><ol id='breadcrumbList' class='breadcrumb'></ol></nav>");

		breadcrumbs.forEach(function (breadcrumb){
			$("#breadcrumbList").append("<li class='breadcrumb-item'><a onclick='renderMap("+ breadcrumb[0] + ")' href='#'>" + breadcrumb[1] + "</a></li>");
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

		if($("#mapSelect").val() != uiMapID){
			$("#mapSelect").val(uiMapID);
		}
		generateBreadcrumb(uiMapID);
		var scale = getScaleByUIMapID(uiMapID);
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

						var imagePosX = 150 + uiMapArtTileRow.RowIndex * (256 / scale);
						var imagePosY = 50 + uiMapArtTileRow.ColIndex * (256 / scale);
						var bgURL = "https://wow.tools/casc/file/fdid?buildconfig=deb02554fac3ac20d9344b3f9386b7da&cdnconfig=7af3569eea7becd9b9a9adb57f15a199&filename=maptile&filedataid=" + uiMapArtTileRow.FileDataID;

						$("#map").append("<img class='uiMapArt' id='art" + uiMapArtTileRow.ID + "' src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=' style='z-index: 1; margin: 0px; width: " + (256 / scale) + "px; height: " + (256 / scale) + "px; position: absolute; top: " + imagePosX + "px; left: " + imagePosY + "px;'>");
						renderBLPToIMGElement(bgURL , "art" + uiMapArtTileRow.ID);
					}
				});

				if(showExplored){
					renderExplored();
				}
			}
		});

		updateURL();

	}

	function getScaleByUIMapID(uiMapID) {
		for (var i = 0; i < uiMapXMapArt.length; i++) {
			const row = uiMapXMapArt[i];

			if (row == undefined) { continue; }
			if (row.UiMapID != uiMapID) { continue; }
			if (!(row.UiMapArtID in uiMapArt)) { continue; }

			const uiMapArtRow = uiMapArt[row.UiMapArtID];
			console.log(uiMapArtRow.UiMapArtStyleID);
			switch (uiMapArtRow.UiMapArtStyleID) {
				case "1":
				case "4":
					return 1
				case "2":
				case "3":
				case "5":
				case "106":
				case "107":
					return 4
				default:
					return 1
			}
		}
	}

	function renderExplored(){
		var showExplored = $("#showExplored").prop('checked');

		var uiMapID = $("#mapSelect").val();
		var scale = getScaleByUIMapID(uiMapID);

		uiMapXMapArt.forEach(function(uiMapXMapArtRow){
			if(uiMapXMapArtRow.UiMapID == uiMapID){
				var uiMapArtID = uiMapXMapArtRow.UiMapArtID;
				console.log("Found uiMapArtID " + uiMapArtID + " for uiMapID " + uiMapID);

				worldMapOverlay.forEach(function(wmoRow){
					if(wmoRow.UiMapArtID == uiMapArtID){
						worldMapOverlayTile.forEach(function(wmotRow){
							if(wmotRow.WorldMapOverlayID == wmoRow.ID){
								var layerPosX = parseInt(wmoRow.OffsetX) + 50 + (wmotRow.ColIndex * (256 / scale));
								var layerPosY = parseInt(wmoRow.OffsetY) + 150 + (wmotRow.RowIndex * (256 / scale));
								var bgURL = "https://wow.tools/casc/file/fdid?buildconfig=deb02554fac3ac20d9344b3f9386b7da&cdnconfig=7af3569eea7becd9b9a9adb57f15a199&filename=exploredmaptile&filedataid=" + wmotRow.FileDataID;

								$("#map").append("<img class='uiMapArt uiMapExploredArt' id='exploredArt" + wmotRow.ID + "' src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=' style='z-index: 2; margin: 0px; max-width: " + (256 / scale) + "px; max-height: " + (256 / scale) + "px; position: absolute; top: " + layerPosY + "px; left: " + layerPosX + "px;'>");
								renderBLPToIMGElement(bgURL, "exploredArt" + wmotRow.ID);
							}
						});
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

	$("#showExplored").on("click", function (){
		if($(this).prop('checked') == false){
			$(".uiMapExploredArt").remove();
		}else{
			renderExplored();
		}
	});
</script>
