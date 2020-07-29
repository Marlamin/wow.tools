<?php require_once(__DIR__ . "/../inc/header.php");

if($embed){
	// Embed
	?>
	<style type='text/css'>
		nav{
			display: none !important;
		}
	</style>
	<?php
}else{
	// Non-embed
	?>
	<link href="/maps/css/leaflet.css?v=<?=filemtime(__DIR__ . "/../maps/css/leaflet.css")?>" rel="stylesheet">
	<link href="/mv/mapviewer.css?v=<?=filemtime(__DIR__ . "/mapviewer.css")?>" rel="stylesheet">
<?php } ?>
<link href="/mv/modelviewer.css?v=<?=filemtime(__DIR__ . "/modelviewer.css")?>" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js" crossorigin="anonymous"></script>
<?php if(!$embed){ ?>
<button id="js-sidebar-button" class="hamburger">
	<i class='fa fa-reorder'></i>
</button>
<div id="js-sidebar" class="overlay sidebar closed container">
	<b style='margin-left: 75px; margin-top: 0px;'>Uses WIP viewer by Deamon</b>
	<div class='row justify-content-md-center'>
		<div class='col-md-11'>
			<div class="btn-group" role="group">
				<button style='margin-left: 48px;' class='btn btn-mv btn-sm' data-toggle='modal' data-target='#settingsModal'><i class='fa fa-gear'></i> Settings</button>
				<a class='btn btn-mv btn-sm' href='#' data-toggle='modal' data-target='#changelogModal'><i class='fa fa-bug'></i> Changelog</a>
				<button class='btn btn-mv btn-sm' data-toggle='modal' data-target='#helpModal'><i class='fa fa-info-circle'></i> Help/About</button>
				<button class='btn btn-mv btn-sm' data-toggle='modal' data-target='#textureModal'>Tex debug</button>
			</div>
		</div>
	</div>
	<ul class="nav nav-pills nav-fill" style='margin-top: 10px'>
		<li class="nav-item">
			<a class="nav-link active" href="#model" data-toggle="tab" role="tab">Model viewer</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#map" data-toggle="tab" role="tab" id="mapViewerButton">Map viewer</a>
		</li>
	</ul>
	<div class="tab-content" id="mvTabs">
		<div class="tab-pane fade show active" id="model" role="tabpanel" aria-labelledby="model-tab">
			<div class='row justify-content-center' style='margin-top: 10px;'>
				<div class='col-md-4' style='text-align: center'><label title='terrain files (makes search slower)' for='showADT'>Show ADT: <input class='filterBox' type='checkbox' id='showADT'></label></div>
				<div class='col-md-4' style='text-align: center'><label title='larger models (buildings, cities, dungeons, raids etc)' for='showWMO'>Show WMO: <input class='filterBox' type='checkbox' id='showWMO' CHECKED></label></div>
				<div class='col-md-4' style='text-align: center'><label title='smaller more complex models (creatures, foliage, props etc)' for='showM2'>Show M2: <input class='filterBox' type='checkbox' id='showM2' CHECKED></label></div>
			</div>
			<div class='row'>
				<div class='col'>
					<table id='mvfiles' class="table table-striped table-bordered table-condensed" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th style='width: 50px;'>ID</th>
								<th>Filename</th>
								<th style='width: 15px;'>&nbsp;</th>
							</tr>
						</thead>
					</table>
				</div>
			</div>
		</div>
		<div class="tab-pane fade" id="map" role="tabpanel" aria-labelledby="map-tab">
			Keep in mind the map viewer is even more experimental than the regular model viewer and might be more laggy/unstable.
			<div>
				<select id="js-map-select" disabled></select>
				<select id="js-version-select" disabled></select><br>
				<label for="mapZPos">Teleport height</label> <input id="mapZPos" value="5000">
			</div>
			<div id="js-map" class="map-canvas">&nbsp;</div>
		</div>
	</div>
</div>
<?php } ?>
<div id="js-controls" class="overlay controls closed">
	<div>
		<select id='animationSelect' class='form-control' style='display: none'>
			<option>No options for model</option>
		</select>
		<select id='skinSelect' class='form-control'>
			<option>No options for model</option>
		</select>
	</div>
</div>
<?php if($embed){ ?>
<div id="embeddedLogo">
	<a target='_BLANK' href='https://wow.tools<?=str_replace("embed=true", "", filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL))?>' title='View in full WoW.tools modelviewer'><img style='width: 50px' src='/img/newlogo.svg'></a>
</div>
<?php } ?>
<div id='errors'></div>
<div id='fpsLabel'></div>
<div id='eventLabel'></div>
<canvas id="wowcanvas"></canvas>
<?php if(!$embed){ ?>
<div class="modal" id="settingsModal" tabindex="-1" role="dialog" aria-labelledby="settingsModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="settingsModalLabel">Settings</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<form id='settingsForm'>
					<input type='checkbox' id='showFPS' name='settings[showFPS]'> <label for='showFPS'>Show FPS</label><br>
					<input type='checkbox' id='retailOnly' name='settings[retailOnly]'> <label for='retailOnly'>Use static files (fastest, limited to current retail build)</label><br>
					<input type='color' id='customClearColor' name='settings[customClearColor]'> <label for='customClearColor'>Background color (applied on next model load)</label><br>
					<input type='text' id='farClip' name='settings[farClip]'> <label for='farClip'>View distance</label><br>
					<input type='text' id='farClipCull' name='settings[farClipCull]'> <label for='farClipCull'>Model culling distance</label>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="saveSettings();" data-dismiss="modal">Save</button>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="textureModal" tabindex="-1" role="dialog" aria-labelledby="textureModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="textureModalLabel">Texture</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<form id='textureForm'>
					<input type='text' id='tex0' name='textures[0]'> <label for='tex0'>Texture #0</label><br>
					<input type='text' id='tex1' name='textures[1]'> <label for='tex1'>Texture #1</label><br>
					<input type='text' id='tex2' name='textures[2]'> <label for='tex2'>Texture #2 (Object #1)</label><br>
					<input type='text' id='tex3' name='textures[3]'> <label for='tex3'>Texture #3</label><br>
					<input type='text' id='tex4' name='textures[4]'> <label for='tex4'>Texture #4</label><br>
					<input type='text' id='tex5' name='textures[5]'> <label for='tex5'>Texture #5</label><br>
					<input type='text' id='tex6' name='textures[6]'> <label for='tex6'>Texture #6</label><br>
					<input type='text' id='tex7' name='textures[7]'> <label for='tex7'>Texture #7</label><br>
					<input type='text' id='tex8' name='textures[8]'> <label for='tex8'>Texture #8</label><br>
					<input type='text' id='tex9' name='textures[9]'> <label for='tex9'>Texture #9</label><br>
					<input type='text' id='tex10' name='textures[10]'> <label for='tex10'>Texture #10</label><br>
					<input type='text' id='tex11' name='textures[11]'> <label for='tex11'>Texture #11 (Monster #1)</label><br>
					<input type='text' id='tex12' name='textures[12]'> <label for='tex12'>Texture #12 (Monster #2)</label><br>
					<input type='text' id='tex13' name='textures[13]'> <label for='tex13'>Texture #13 (Monster #3)</label><br>
					<input type='text' id='tex14' name='textures[14]'> <label for='tex14'>Texture #14</label><br>
					<input type='text' id='tex15' name='textures[15]'> <label for='tex15'>Texture #15</label><br>
					<input type='text' id='tex16' name='textures[16]'> <label for='tex16'>Texture #16</label><br>
					<input type='text' id='tex17' name='textures[17]'> <label for='tex17'>Texture #17</label><br>
					<input type='text' id='tex18' name='textures[18]'> <label for='tex18'>Texture #18</label><br>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="updateTextures();" data-dismiss="modal">Save</button>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="helpModalLabel">Help/About</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<p>
					This tool is being developed by the WoW community for the WoW community and as such won't have watermarks all over it but will likely also be more unstable/bleeding edge. Currently it supports rendering most M2 models (smaller more complex models, characters unsupported for now), WMO models (larger models) and ADT files (terrain files with WMOs and M2s).
					<br>These parts of it are open source (more coming in the future!):
					<ul>
						<li><a target='_BLANK' href='https://github.com/Deamon87/WebWowViewerCpp/tree/emscripten'>Model viewer itself</a></li>
						<li><a target='_BLANK' href='https://github.com/Marlamin/WoWFormatTest/tree/master/DBCDumpHost'>DBC backend (used for texture lookups)</a></li>
						<li><a target='_BLANK' href='https://github.com/Marlamin/CASCToolHost'>CASC backend (serves files)</a></li>
					</ul>
				</p>
				<h5>Requirements</h5>
				<p>
					This requires a browser that supports both WebAssembly (<a href='https://caniuse.com/#search=wasm' target='_BLANK'>see list here</a>) and WebGL 2.0 (<a href='https://caniuse.com/#search=webgl2' target='_BLANK'>see list here</a>). If your browser does not support this the modelviewer will definitely get upset and will definitely throw errors towards your general direction. We're aware this is not always supported on some configurations, but are hopeful it will be in the future!
				</p>
				<h5>Controls</h5>
				<p>
					For ADTs and WMOs, the model viewer uses a free-roam camera. It can be controlled via dragging the mouse and <tt>WASD</tt> keys. Holding <tt>SHIFT</tt> increases camera speed.<br><br>
					For M2s, the model viewer uses a rotational camera. You can rotate the model by dragging the mouse and zoom out with the scroll wheel. If skins/animations are available, a menu will pop up with options for these.
				</p>
				<h5>Issues</h5>
				<p>
					This modelviewer (and especially the UI) is still a heavy work in progress. Many bugs will appear. Report any issues/feature requests via GitHub by clicking the "Report issues" button or via Discord by joining through the link in the menu.
				</p>
				<h5>Embed</h5>
				<p>
					An embeddable version of the modelviewer <a href='//marlamin.com/u/mvxdomain.html' target='_BLANK'>is available</a> , but how exactly/if it will continue to work (or how costs will be dealt with if it gets popular) is still to be decided. Please contact me if you want to embed it. If you do, keep in mind it is still WIP/experimental and to not rely on it in any way.
				</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="changelogModal" tabindex="-1" role="dialog" aria-labelledby="changelogModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="changelogModalLabel">Changelog</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<h5>Version 0.9.7 <small>(29-07-2020)</small></h5>
				<ul>
					<li>(MV) Various updates</li>
				</ul>
				<h5>Version 0.9.6 <small>(17-04-2020)</small></h5>
				<ul>
					<li>(MV) Crash fixes</li>
					<li>(MV) Performance updates</li>
					<li>(MV) Shadowlands support</li>
				</ul>
				<h5>Version 0.9.5 <small>(12-02-2019)</small></h5>
				<ul>
					<li>(MV) Map viewer interface</li>
					<li>(MV) Optional 'retail-only' mode for faster loading of assets</li>
				</ul>
				<h5>Version 0.9.4 <small>(13-12-2019)</small></h5>
				<ul>
					<li>(MV) Experimental WebGL1 support</li>
					<li>(MV) Fix issue loading some animations</li>
				</ul>
				<h5>Version 0.9.3 <small>(17-10-2019)</small></h5>
				<ul>
					<li>(MV) Terrain rendering improvements</li>
					<li>(MV) Add support for future UI options</li>
					<li>(UI) Add support for changing background color</li>
				</ul>
				<h5>Version 0.9.2 <small>(12-04-2019)</small></h5>
				<ul>
					<li>(UI) Fix manual page input not working</li>
					<li>(UI) More UI changes to fit to new site theme</li>
				</ul>
				<h5>Version 0.9.1 <small>(08-04-2019)</small></h5>
				<ul>
					<li>(UI) Add navigation through the list with arrows keys</li>
					<li>(UI) Various UI changes to fit to new site theme</li>
					<li>(UI) Fix issue where same file row could be clicked twice</li>
				</ul>
				<h5>Version 0.9.0 <small>(18-01-2019)</small></h5>
				<ul>
					<li>(MV) PARTICLE SUPPORT!</li>
					<li>(UI) Performance improvements</li>
				</ul>
				<h5>Version 0.8.1 <small>(01-12-2018)</small></h5>
				<ul>
					<li>(UI) Layout improvements</li>
					<li>(UI) Add filters for ADT/WMO/M2 (ADT disabled by default for search speed)</li>
					<li>(UI) Removed (hopefully all) non-supported files from list</li>
					<li>(UI) Updated help/about</li>
					<li>(UI) Decrease FPS counter update speed for better page performance (Thanks Pavel!)</li>
					<li>(UI) Highlight currently selected file</li>
				</ul>
				<h5>Version 0.8.0 <small>(21-11-2018)</small></h5>
				<ul>
					<li>(MV) Animation changing support</li>
					<li>(UI) Animation picker</li>
				</ul>
				<h5>Version 0.7.1 <small>(12-11-2018)</small></h5>
				<ul>
					<li>(MV) Replaceable item texture support</li>
				</ul>
				<h5>Version 0.7.0 <small>(07-11-2018)</small></h5>
				<ul>
					<li>(MV) Touch controls</li>
					<li>(MV) Prep for item texture support</li>
					<li>(MV) Fix WMO water</li>
					<li>(UI) History feature re-enabled</li>
				</ul>
				<h5>Version 0.6.2 <small>(29-10-2018)</small></h5>
				<ul>
					<li>(MV) Environment mapping improvements</li>
					<li>(MV) Billboarding improvements</li>
					<li>(UI) Various UI improvements suggested by Balkron</li>
					<li>(UI) Temporarily disable history feature</li>
				</ul>
				<h5>Version 0.6.1 <small>(22-10-2018)</small></h5>
				<ul>
					<li>(MV) Fix crash when loading some WMOs</li>
					<li>(MV) Load less textures at a time to prevent lagspikes</li>
				</ul>
				<h5>Version 0.6.0 <small>(18-10-2018)</small></h5>
				<ul>
					<li>(UI) Enable history function</li>
					<li>(MV) Support history feature</li>
				</ul>
				<h5>Version 0.5.2 <small>(16-10-2018)</small></h5>
				<ul>
					<li>(UI) Add changelog</li>
					<li>(UI) Fix some scaling issues</li>
					<li>(UI) Add (currently disabled) history feature</li>
					<li>(MV) Add rotational M2 camera</li>
					<li>(MV) Fix M2 lights</li>
				</ul>
				<h5>Version 0.5.1 <small>(10-10-2018)</small></h5>
				<ul>
					<li>(UI) Hide file IDs</li>
					<li>(UI) Show ID/type if filename is unknown</li>
					<li>(MV) Add SHIFT hotkey to increase camera speed</li>
					<li>(MV) Fix M2 animations not working properly</li>
				</ul>
				<h5>Version 0.5.0 <small>(09-10-2018)</small></h5>
				<ul>
					<li>(UI) Move to /mv/</li>
					<li>(UI) Add search</li>
					<li>(UI) Add URL updating when switching models</li>
					<li>(UI) Add FPS counter</li>
					<li>(UI) Add settings</li>
				</ul>
				<h5>Version 0.4.0 <small>(08-10-2018)</small></h5>
				<ul>
					<li>(MV) Add support for 8.1 WMO format changes</li>
					<li>(MV) Add M2s to file picker</li>
					<li>(MV) Fix M2 animations not working properly</li>
				</ul>
				<h5>Version 0.3.2 <small>(04-10-2018)</small></h5>
				<ul>
					<li>(MV) Fix flickering issue on Intel embedded GPUs</li>
					<li>(MV) Fix resizing issue</li>
				</ul>
				<h5>Version 0.3.1 <small>(02-10-2018)</small></h5>
				<ul>
					<li>(MV) Fix issue where some missing WMOs would crash</li>
				</ul>
				<h5>Version 0.3.0 <small>(01-10-2018)</small></h5>
				<ul>
					<li>(UI) Add checks to see if user meets WebGL/WASM requirements</li>
					<li>(UI) More datatables functionality copied from regular file browser</li>
					<li>(MV) Add "O" hotkey to reset camera</li>
					<li>(MV) Fix issue where some missing WMO meshes were missing</li>
				</ul>
				<h5>Version 0.2.0 <small>(30-09-2018)</small></h5>
				<ul>
					<li>(UI) Add UI</li>
					<li>(UI) Add file picker (WMO only)</li>
					<li>(MV) Fix issue where WMO exterior would disappear</li>
				</ul>
				<h5>Version 0.1.0 <small>(29-09-2018)</small></h5>
				<ul>
					<li>WMO viewing</li>
				</ul>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<?php } ?>
<script type="text/javascript"><?php $nonfilenamebuilds = $pdo->query("SELECT hash FROM wow_buildconfig WHERE description LIKE '%8.2%' OR description LIKE '%8.3%'")->fetchAll(PDO::FETCH_COLUMN); ?>
var noNameBuilds = <?=json_encode($nonfilenamebuilds)?>;
const embeddedMode = <?php if(!empty($_GET['embed'])){ ?>true<?php }else{ ?>false<? } ?>;
</script>
<script src="/mv/modelviewer.js?v=<?=filemtime(__DIR__ . "/modelviewer.js")?>"></script>
<script src="/mv/anims.js?v=<?=filemtime(__DIR__ . "/anims.js")?>"></script>
<script src="/mv/project.js?v=<?=filemtime(__DIR__ . "/project.js")?>"></script>
<?php if(!$embed){ ?>
<script src="/maps/js/leaflet.js?v=<?=filemtime(__DIR__ . "/../maps/js/leaflet.js")?>"></script>
<script src="/mv/mapviewer.js?v=<?=filemtime(__DIR__ . "/mapviewer.js")?>"></script>
<?php } ?>
</body>
</html>