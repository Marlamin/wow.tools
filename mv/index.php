<?php

require_once(__DIR__ . "/../inc/header.php");

if ($embed) {
    // Embed
    ?>
    <style type='text/css'>
        nav{
            display: none !important;
        }
    </style>
    <?php
} else {
    // Non-embed
    ?>
    <link href="/maps/css/leaflet.css?v=<?=filemtime(__DIR__ . "/../maps/css/leaflet.css")?>" rel="stylesheet">
    <link href="/mv/mapviewer.css?v=<?=filemtime(__DIR__ . "/mapviewer.css")?>" rel="stylesheet">
<?php } ?>
<link href="/mv/modelviewer.css?v=<?=filemtime(__DIR__ . "/modelviewer.css")?>" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js" crossorigin="anonymous"></script>
<script type='text/javascript'>
// stats.js - http://github.com/mrdoob/stats.js
(function(f,e){"object"===typeof exports&&"undefined"!==typeof module?module.exports=e():"function"===typeof define&&define.amd?define(e):f.Stats=e()})(this,function(){var f=function(){function e(a){c.appendChild(a.dom);return a}function u(a){for(var d=0;d<c.children.length;d++)c.children[d].style.display=d===a?"block":"none";l=a}var l=0,c=document.createElement("div");c.style.cssText="cursor:pointer;opacity:0.9;z-index:10000";c.addEventListener("click",function(a){a.preventDefault();
u(++l%c.children.length)},!1);var k=(performance||Date).now(),g=k,a=0,r=e(new f.Panel("FPS","#0ff","#002")),h=e(new f.Panel("MS","#0f0","#020"));if(self.performance&&self.performance.memory)var t=e(new f.Panel("MB","#f08","#201"));u(0);return{REVISION:16,dom:c,addPanel:e,showPanel:u,begin:function(){k=(performance||Date).now()},end:function(){a++;var c=(performance||Date).now();h.update(c-k,200);if(c>=g+1E3&&(r.update(1E3*a/(c-g),100),g=c,a=0,t)){var d=performance.memory;t.update(d.usedJSHeapSize/
1048576,d.jsHeapSizeLimit/1048576)}return c},update:function(){k=this.end()},domElement:c,setMode:u}};f.Panel=function(e,f,l){var c=Infinity,k=0,g=Math.round,a=g(window.devicePixelRatio||1),r=80*a,h=48*a,t=3*a,v=2*a,d=3*a,m=15*a,n=74*a,p=30*a,q=document.createElement("canvas");q.width=r;q.height=h;q.style.cssText="width:80px;height:48px";var b=q.getContext("2d");b.font="bold "+9*a+"px Helvetica,Arial,sans-serif";b.textBaseline="top";b.fillStyle=l;b.fillRect(0,0,r,h);b.fillStyle=f;b.fillText(e,t,v);
b.fillRect(d,m,n,p);b.fillStyle=l;b.globalAlpha=.9;b.fillRect(d,m,n,p);return{dom:q,update:function(h,w){c=Math.min(c,h);k=Math.max(k,h);b.fillStyle=l;b.globalAlpha=1;b.fillRect(0,0,r,m);b.fillStyle=f;b.fillText(g(h)+" "+e+" ("+g(c)+"-"+g(k)+")",t,v);b.drawImage(q,d+a,m,n-a,p,d,m,n-a,p);b.fillRect(d+n-a,m,a,p);b.fillStyle=l;b.globalAlpha=.9;b.fillRect(d+n-a,m,a,g((1-h/w)*p))}}};return f});
</script>
<?php if (!$embed) { ?>
<button id="js-sidebar-button" class="hamburger">
    <i class='fa fa-reorder'></i>
</button>
<div id="js-sidebar" class="overlay sidebar closed container">
    <b style='margin-left: 75px; margin-top: 0px;'>Uses WIP viewer by Deamon</b>
    <div class='row justify-content-md-center'>
        <div class='col-md-11'>
            <div class="btn-group" role="group">
                <button style='margin-left: 48px;' class='btn btn-mv btn-sm' data-toggle='modal' data-target='#settingsModal'><i class='fa fa-gear'></i> Settings</button>
                <button class='btn btn-mv btn-sm' data-toggle='modal' data-target='#helpModal'><i class='fa fa-info-circle'></i> Help/About</button>
                <button class='btn btn-mv btn-sm' onclick='exportScene()' id='exportButton' data-toggle="tooltip" data-placement="left" title="Exports the current model/animation to glTF"><i class='fa fa-download' disabled></i> Export glTF</a>
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
<div id="js-model-control" class="overlay model-control closed">
    <div class='row justify-content-md-center'>
        <div class='col-md-11'>
            <ul class="nav nav-pills nav-fill">
                <li class="nav-item">
                    <a class="nav-link active" href="#textures" data-toggle="tab" role="tab">Textures</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#geosets" data-toggle="tab" role="tab" id="mapViewerButton">Geosets</a>
                </li>
            </ul>
            <div class="tab-content">
                <p>Changing the skin/displayID will reset these values.</p>
                <div class="tab-pane fade show active" id="textures" role="tabpanel" aria-labelledby="textures-tab">
                    <p>Changing values in this form will let you assign FileDataIDs of your choosing to a certain texture slot in the model.</p>
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
                    <button type="button" class="btn btn-primary" onclick="updateTextures();" data-dismiss="modal">Save</button>
                </div>
                <div class="tab-pane fade" id="geosets" role="tabpanel" aria-labelledby="geosets-tab">
                    
                </div>
            </div>
        </div>
    </div>
</div>
<div id="js-controls" class="overlay controls">
    <div>
        <button id="modelControlButton" class="btn btn-primary btn-sm" ><i class='fa fa-cube'></i> Model control (WIP)</button>
        <select id='animationSelect' class='form-control' style='display: none'>
            <option>No options for model</option>
        </select>
        <select id='skinSelect' class='form-control' style='display: none'>
            <option>No options for model</option>
        </select>
    </div>
</div>
<?php if ($embed) { ?>
<div id="embeddedLogo">
    <a target='_BLANK' href='https://wow.tools<?=str_replace("embed=true", "", filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL))?>' title='View in full WoW.tools modelviewer'><img style='width: 50px' src='/img/newlogo.svg'></a>
</div>
<?php } ?>
<div id='errors'></div>
<div id='fpsLabel'></div>
<div id='eventLabel'></div>
<div id='downloadLabel'></div>
<canvas id="wowcanvas"></canvas>
<?php if (!$embed) { ?>
<div class="modal" id="settingsModal" tabindex="-1" role="dialog" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="settingsModalLabel">Settings</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id='settingsForm'>
                    <input type='checkbox' id='showFPS' name='settings[showFPS]'> <label for='showFPS'>Show stats</label><br>
                    <!--<input type='checkbox' id='retailOnly' name='settings[retailOnly]'> <label for='retailOnly'>Use static files (fastest, limited to current retail build)</label><br>-->
                    <input type='color' id='customClearColor' name='settings[customClearColor]'> <label for='customClearColor'>Background color (applied on next model load)</label><br>
                    <input type='text' id='farClip' name='settings[farClip]'> <label for='farClip'>View distance</label><br>
                    <input type='text' id='farClipCull' name='settings[farClipCull]'> <label for='farClipCull'>Model culling distance</label><br>
                    <input type='checkbox' id='portalCulling' name='settings[portalCulling]'> <label for='portalCulling'>Enable portal culling?</label><br>
                    <input type='checkbox' id='newDisplayInfo' name='settings[newDisplayInfo]'> <label for='newDisplayInfo'>(WIP) Use new DisplayID based skin selection?</label>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveSettings();" data-dismiss="modal">Save</button>
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
                        <li><a target='_BLANK' href='https://github.com/Deamon87/WebWowViewerCpp/tree/development'>Model viewer itself</a></li>
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
                    <kbd>SHIFT-Z</kbd> hides the UI.<br>
                    <kbd>Spacebar</kbd> pauses the current model (entirely, including rotation and stuff).<br><br>
                    For ADTs and WMOs, the model viewer uses a free-roam camera. It can be controlled via dragging the mouse and <kbd>WASD</kbd> keys. Holding <kbd>SHIFT</kbd> increases camera speed.<br><br>
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
<?php } ?>
<script type="text/javascript"><?php $nonfilenamebuilds = $pdo->query("SELECT hash FROM wow_buildconfig WHERE description LIKE '%8.2%' OR description LIKE '%8.3%'")->fetchAll(PDO::FETCH_COLUMN); ?>
var noNameBuilds = <?=json_encode($nonfilenamebuilds)?>;
const embeddedMode = <?php if (!empty($_GET['embed'])) {
    ?>true<?php
} else {
    ?>false<?php
} ?>;
</script>
<script src="/mv/modelviewer.js?v=<?=filemtime(__DIR__ . "/modelviewer.js")?>"></script>
<script src="/mv/anims.js?v=<?=filemtime(__DIR__ . "/anims.js")?>"></script>
<script src="/mv/project.js?v=<?=filemtime(__DIR__ . "/project.js")?>"></script>
<?php if (!$embed) { ?>
<script src="/maps/js/leaflet.js?v=<?=filemtime(__DIR__ . "/../maps/js/leaflet.js")?>"></script>
<script src="/mv/mapviewer.js?v=<?=filemtime(__DIR__ . "/mapviewer.js")?>"></script>
<?php } ?>
</body>
</html>