<? require_once("../inc/header.php"); ?>
<link href="/maps/css/leaflet.css?v=<?=filemtime($basedir."/maps/css/leaflet.css")?>" rel="stylesheet" type="text/css">
<link href="/maps/css/style.css?v=<?=filemtime($basedir."/maps/css/style.css")?>" rel="stylesheet" type="text/css">
<div id='maps'>
<button id="js-sidebar-button" class="hamburger">
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
	</button>

	<div id="js-sidebar" class="overlay sidebar">
		<div>
			<select id="js-map-select" disabled></select>
		</div>

		<div>
			<button id="js-version-prev" class="version-arrow" disabled>&larr;</button><select id="js-version-select" disabled></select><button id="js-version-next" class="version-arrow" disabled>&rarr;</button>
		</div>
		<div style='font-size: 12px; margin-top: 5px'>
		<b>Details for last click:</b><br>
		Coord: <span id="clickedCoord">No click. :(</span><br>
		Tile: <span id="clickedADT">No click. :(</span><br>
		Name: <span id="clickedName">No click. :(</span>
		</div>
	</div>

	<button id="js-layers-button" class="layerbutton">
		<i class="fa fa-map-marker fa-2x"></i>
	</button>

	<div id="js-layers" class="overlay layers">
		<h2>Map layers</h2>
		<div style='font-size: 12px; margin-top: 5px'>Currently working on this, still buggy! <br>Big data sets might take a few seconds to load.
		<br>
		More layers coming in the future.</div>
		<div style='font-size: 12px; margin-top: 5px'><input type='checkbox' name='flightpoints' id='js-flightlayer'> <label for="js-flightlayer">Flight masters (only some maps)</label></div>
	</div>

	<div id="js-map" class="map-canvas">&nbsp;</div>
	<script type="text/javascript" src="/maps/js/leaflet.js?v=<?=filemtime($basedir."/maps/js/leaflet.js")?>"></script>
	<script type="text/javascript" src="/maps/js/leaflet_wowmap.js?v=<?=filemtime($basedir."/maps/js/leaflet_wowmap.js")?>"></script>
	<script type="text/javascript" src="/maps/js/Control.MiniMap.min.js?v=<?=filemtime($basedir."/maps/js/Control.MiniMap.min.js")?>"></script>
</div>
<? require_once("../inc/footer.php"); ?>