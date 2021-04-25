var Module = {
    onRuntimeInitialized: function() {
        createscene();
    }
};

function showError(error){
    $("#errors").append("<div class='error alert alert-danger'>" + error +"</div>")
}

window.onerror = function(message, source, lineno, colno, error) {
    showError("An error occured! You might have to force reload the page with <kbd>CTRL-F5</kbd>. <br>Let us know what the error is by opening the console (<kbd>CTRL-SHIFT-J</kbd> on Chrome) and posting a screenshot of the error in <a href='https://discord.gg/SFKz4Nx'>Discord</a>.");
}

var Elements =
{
    Sidebar: document.getElementById('js-sidebar'),
    Counter: document.getElementById('fpsLabel'),
    EventLabel: document.getElementById('eventLabel'),
    DownloadLabel: document.getElementById('downloadLabel'),
};

var Current =
{
    buildConfig: "e759532efbc5fbc3ce094f8da5d9aa2e",
    cdnConfig: "76f0f23d8299e48169c578174da17835",
    buildName: "9.1.0.38394",
    fileDataID: 397940,
    type: "m2",
    embedded: false
}

var Settings =
{
    showFPS: true,
    paused: false,
    retailOnly: false,
    clearColor: [0.117, 0.207, 0.392],
    farClip: 500,
    farClipCull: 500,
    speed: 1000.0,
    portalCulling: true,
    newDisplayInfo: false

}

var DownloadQueue = [];
var isDownloading = false;
var numDownloading = 0;

var screenshot = false;
var stats = new Stats();

function loadSettings(applyNow = false){
    /* Show/hide FPS counter */
    var storedShowFPS = localStorage.getItem('settings[showFPS]');
    if (storedShowFPS){
        if (storedShowFPS== "1"){
            Settings.showFPS = true;
            stats.showPanel(1);
            Elements.Counter.appendChild(stats.dom);
        } else {
            Settings.showFPS = false;
            Elements.Counter.innerHTML = "";
        }
    }

    document.getElementById("showFPS").checked = Settings.showFPS;

    /* Enable/disable retail-only */
    // var storedRetailOnly = localStorage.getItem('settings[retailOnly]');
    // if (storedRetailOnly){
    //     if (storedRetailOnly== "1"){
    //         Settings.retailOnly = true;
    //     } else {
    Settings.retailOnly = false;
    //     }
    // }

    // document.getElementById("retailOnly").checked = Settings.retailOnly;

    /* Clear color */
    var storedCustomClearColor = localStorage.getItem('settings[customClearColor]');
    if (storedCustomClearColor){
        document.getElementById("customClearColor").value = storedCustomClearColor;
    } else {
        document.getElementById("customClearColor").value = '#1e3564';
    }

    var rawClearColor = document.getElementById("customClearColor").value.replace('#', '');
    var r = parseInt('0x' + rawClearColor.substring(0, 2)) / 255;
    var g = parseInt('0x' + rawClearColor.substring(2, 4)) / 255;
    var b = parseInt('0x' + rawClearColor.substring(4, 6)) / 255;
    Settings.clearColor = [r, g, b];

    /* Far clip */
    var storedFarClip = localStorage.getItem('settings[farClip]');
    if (storedFarClip){
        Settings.farClip = storedFarClip;
        document.getElementById('farClip').value = storedFarClip;
    } else {
        document.getElementById('farClip').value = Settings.farClip;
    }

    /* Far clip (model culling) */
    var storedFarClipCull = localStorage.getItem('settings[farClipCull]');
    if (storedFarClipCull){
        Settings.farClipCull = storedFarClipCull;
        document.getElementById('farClipCull').value = storedFarClipCull;
    } else {
        document.getElementById('farClipCull').value = Settings.farClipCull;
    }

    /* Portal culling */
    var storedPortalCulling = localStorage.getItem('settings[portalCulling]');
    if (storedPortalCulling){
        if (storedPortalCulling== "1"){
            Settings.portalCulling = true;
        } else {
            Settings.portalCulling = false;
        }
    }

    document.getElementById("portalCulling").checked = Settings.portalCulling;

    /* New Display Info */
    var newDisplayInfo = localStorage.getItem('settings[newDisplayInfo]');
    if (newDisplayInfo){
        if (newDisplayInfo== "1"){
            Settings.newDisplayInfo = true;
        } else {
            Settings.newDisplayInfo = false;
        }   
    }

    document.getElementById("newDisplayInfo").checked = Settings.newDisplayInfo;

    /* If settings should be applied now (don't do this on page load!) */
    if (applyNow){
        Module._setClearColor(Settings.clearColor[0], Settings.clearColor[1], Settings.clearColor[2]);
        Module._setFarPlane(Settings.farClip);
        Module._setFarPlaneForCulling(Settings.farClipCull);
        Module._enablePortalCulling(Settings.portalCulling);
    }
}

function saveSettings(){
    if (document.getElementById("showFPS").checked){
        localStorage.setItem('settings[showFPS]', '1');
    } else {
        localStorage.setItem('settings[showFPS]', '0');
    }

    // if (document.getElementById("retailOnly").checked){
    //     localStorage.setItem('settings[retailOnly]', '1');
    // } else {
    //     localStorage.setItem('settings[retailOnly]', '0');
    // }

    localStorage.setItem('settings[customClearColor]', document.getElementById("customClearColor").value);
    localStorage.setItem('settings[farClip]', document.getElementById("farClip").value);
    localStorage.setItem('settings[farClipCull]', document.getElementById("farClipCull").value);

    if (document.getElementById("portalCulling").checked){
        localStorage.setItem('settings[portalCulling]', '1');
    } else {
        localStorage.setItem('settings[portalCulling]', '0');
    }

    if (document.getElementById("newDisplayInfo").checked){
        localStorage.setItem('settings[newDisplayInfo]', '1');
    } else {
        localStorage.setItem('settings[newDisplayInfo]', '0');
    }
    loadSettings(true);
}

// Sidebar button, might not exist in embedded mode
if (document.getElementById( 'js-sidebar-button' )){
    document.getElementById( 'js-sidebar-button' ).addEventListener( 'click', function( )
    {
        Elements.Sidebar.classList.toggle( 'closed' );
    } );
}

try {
    if (typeof WebAssembly === "object" && typeof WebAssembly.instantiate === "function") {
        const module = new WebAssembly.Module(Uint8Array.of(0x0, 0x61, 0x73, 0x6d, 0x01, 0x00, 0x00, 0x00));
        if (module instanceof WebAssembly.Module)
            var testModule = new WebAssembly.Instance(module) instanceof WebAssembly.Instance;
        if (!testModule) showError("WebAssembly support is required but not supported by your browser.");
    }
} catch (e) {
    showError("WebAssembly support is required but not supported by your browser.");
}

var urlBuildConfig = new URL(window.location).searchParams.get("buildconfig");
if (urlBuildConfig){
    Current.buildConfig = urlBuildConfig;
}

var urlCDNConfig = new URL(window.location).searchParams.get("cdnconfig");
if (urlCDNConfig){
    Current.cdnConfig = urlCDNConfig;
}

var urlFileDataID = new URL(window.location).searchParams.get("filedataid");
if (urlFileDataID){
    Current.fileDataID = urlFileDataID;
}

var urlType = new URL(window.location).searchParams.get("type");
if (urlType){
    Current.type = urlType;
}

var urlEmbed = new URL(window.location).searchParams.get("embed");
if (urlEmbed){
    Current.embedded = true;
    $("#js-sidebar-button").hide();
    $("#fpsLabel").hide();
    console.log("Running modelviewer in embedded mode!");
}

var urlClearColor = new URL(window.location).searchParams.get("clearColor");
if (urlClearColor){
    var r = parseInt('0x' + urlClearColor.substring(0, 2)) / 255;
    var g = parseInt('0x' + urlClearColor.substring(2, 4)) / 255;
    var b = parseInt('0x' + urlClearColor.substring(4, 6)) / 255;
    Settings.clearColor = [r, g, b];
}

window.createscene = function () {
    Module["canvas"] = document.getElementById("wowcanvas");

    var url = "https://wow.tools/casc/file/fname?buildconfig=" + Current.buildConfig + "&cdnconfig=" + Current.cdnConfig +"&filename=";
    let urlFileId;

    if (Settings.retailOnly){
        urlFileId = "https://wow.tools/casc/extract/";
    } else {
        urlFileId = "https://wow.tools/casc/file/fdid?buildconfig=" + Current.buildConfig + "&cdnconfig=" + Current.cdnConfig +"&filename=data&filedataid=";
    }

    var ptrUrl = allocate(intArrayFromString(url), 'i8', ALLOC_NORMAL);
    var ptrUrlFileDataId = allocate(intArrayFromString(urlFileId), 'i8', ALLOC_NORMAL);

    Module._createWebJsScene(document.body.clientWidth, document.body.clientHeight, ptrUrl, ptrUrlFileDataId);

    Module._setClearColor(Settings.clearColor[0], Settings.clearColor[1], Settings.clearColor[2]);

    loadModel(Current.type, Current.fileDataID, Current.buildConfig, Current.cdnConfig)

    _free(ptrUrl);
    _free(ptrUrlFileDataId);
    var lastTimeStamp = new Date().getTime();

    Module["canvas"].width = document.body.clientWidth;
    Module["canvas"].height = document.body.clientHeight;

    Module["animationArrayCallback"] = function(array) {
        $("#animationSelect").empty();

        if (array.length > 1){
            $("#animationSelect").show();
            $("#js-controls").removeClass("closed");

            array.forEach(function(a) {
                if (a in animationNames){
                    $('#animationSelect').append('<option value="' + a + '">' + animationNames[a] + ' (' + a + ')</option>');
                } else {
                    console.log("Missing animation name for " + a + ", let a dev know!");
                    $('#animationSelect').append('<option value="' + a + '">Animation ' + a + '</option>');
                }
            })
        }
    };

    var renderfunc = function(now){
        stats.begin();

        var timeDelta = 0;

        if (lastTimeStamp !== undefined) {
            timeDelta = now - lastTimeStamp;
        }

        lastTimeStamp = now;

        Module._gameloop(timeDelta / Settings.speed);

        if (screenshot){
            screenshot = false;

            let canvasImage = Module["canvas"].toDataURL('image/png');

            let xhr = new XMLHttpRequest();
            xhr.responseType = 'blob';
            xhr.onload = function () {
                let a = document.createElement('a');
                a.href = window.URL.createObjectURL(xhr.response);
                a.download = 'wowtoolsmv-' + lastTimeStamp + '.png';
                a.style.display = 'none';
                document.body.appendChild(a);
                a.click();
                a.remove()
            };
            xhr.open('GET', canvasImage);
            xhr.send();
        }

        if (numDownloading > 0){
            Elements.DownloadLabel.innerText = "Downloading " + numDownloading + " files..";
        }

        stats.end();
        window.requestAnimationFrame(renderfunc);
    };

    window.requestAnimationFrame(renderfunc);
}

window.addEventListener('resize', () => {
    var canvas = document.getElementById("wowcanvas");
    if (canvas){
        canvas.width = document.body.clientWidth;
        canvas.height = document.body.clientHeight;
        if (Module && Module._setSceneSize){
            window.Module._setSceneSize(document.body.clientWidth, document.body.clientHeight);
        }
    }
});

$('#mvfiles').on('click', 'tbody tr td:first-child', function() {
    var data = Elements.table.row($(this).parent()).data();
    var mostRecentVersion = data[3][0];

    if (mostRecentVersion['buildconfig'] == Current.buildConfig && data[0] == Current.fileDataID){
        console.log("Clicked model already open in viewer, ignoring.");
        return;
    }

    $(".selected").removeClass("selected");
    $(this).parent().addClass('selected');
    loadModel(data[4], data[0], mostRecentVersion['buildconfig'], mostRecentVersion['cdnconfig']);
});

$('#js-sidebar').on('input', '.paginate_input', function(){
    if ($(".paginate_input")[0].value != ''){
        $("#mvfiles").DataTable().page($(".paginate_input")[0].value - 1).ajax.reload(null, false)
    }
});

window.addEventListener('keydown', function(event){
    if (document.activeElement.tagName == "SELECT"){
        return;
    }

    if ($(".selected").length == 1){
        if (event.key == "ArrowDown"){
            if ($(".selected")[0].rowIndex == 20) return;
            $(document.getElementById('mvfiles').rows[$(".selected")[0].rowIndex + 1].firstChild).trigger("click");
        } else if (event.key == "ArrowUp"){
            if ($(".selected")[0].rowIndex == 1) return;
            $(document.getElementById('mvfiles').rows[$(".selected")[0].rowIndex - 1].firstChild).trigger("click");
        }
    }

    if (document.activeElement.tagName == "INPUT" || document.activeElement.tagName == "SELECT"){
        event.stopImmediatePropagation();
    } else {
        if (event.key == " "){
            if (Settings.paused){
                Settings.paused = false;
                Elements.EventLabel.textContent = "";
            } else {
                Settings.paused = true;
                Elements.EventLabel.innerHTML = "<i class='fa fa-pause'></i> Paused";
            }

            if (Settings.paused){
                Settings.speed = 1000000000000.0;
            } else {
                Settings.speed = 1000.0;
            }
        }
    }
}, true);

window.addEventListener('keyup', function(event){
    if (event.key == "PrintScreen" && !event.shiftKey && !event.ctrlKey && !event.altKey) screenshot = true;
    if (document.activeElement.tagName == "INPUT" || document.activeElement.tagName == "SELECT"){
        event.stopImmediatePropagation();
    }
}, true);

window.addEventListener('keypress', function(event){
    if (document.activeElement.tagName == "INPUT" || document.activeElement.tagName == "SELECT"){
        event.stopImmediatePropagation();
    }

    if (event.key == "Z" && event.shiftKey){
        toggleUI();
    }
}, true);

$("#animationSelect").change(function () {
    var display = this.options[this.selectedIndex].value;
    Module._setAnimationId(display);
});

$("#skinSelect").change(function() {
    if(this.options[this.selectedIndex].dataset.displayid == undefined){
        // Backwards compat
        var display = this.options[this.selectedIndex].value.split(',');
        if (display.length == 3 || display.length == 4){
            // Creature
            if (display.length == 3){
                Module._resetReplaceParticleColor();
            }
            setModelTexture(display, 11);
        } else {
            // Item
            setModelTexture(display, 2);
        }
    }else{
        setModelDisplay(this.options[this.selectedIndex].dataset.displayid, this.options[this.selectedIndex].dataset.type);
    }
});

function toggleUI(){
    $(".navbar").toggle();
    $("#js-sidebar-button").toggle();
    $("#js-controls").toggle();
}

function loadModel(type, filedataid, buildconfig, cdnconfig){
    Current.buildConfig = buildconfig;
    Current.cdnConfig = cdnconfig;
    Current.fileDataID = filedataid;
    Current.type = type;

    Module._setClearColor(Settings.clearColor[0], Settings.clearColor[1], Settings.clearColor[2]);
    Module._setFarPlane(Settings.farClip);
    Module._setFarPlaneForCulling(Settings.farClipCull);
    Module._enablePortalCulling(Settings.portalCulling);

    DownloadQueue = [];
    isDownloading = false;
    numDownloading = 0;
    $.ajax({
        url: "https://wow.tools/files/scripts/filedata_api.php?filename=1&filedataid=" + Current.fileDataID
    })
        .done(function( filename ) {
            Current.filename = filename;

            updateURLs();

            if (!embeddedMode){
                history.pushState({id: 'modelviewer'}, 'Model Viewer', '/mv/?buildconfig=' + Current.buildConfig + '&cdnconfig=' + Current.cdnConfig + '&filedataid=' + Current.fileDataID + '&type=' + Current.type);
            }

            $("#js-controls").addClass("closed");
            $("#animationSelect").hide();
            $("#skinSelect").hide();

            var alwaysLoadByFDID = false;
            if (noNameBuilds.includes(buildconfig)){
                alwaysLoadByFDID = true;
            }

            if (Current.type == "adt"){
                alwaysLoadByFDID = false;
            }

            if (Current.type == "m2"){
                $("#exportButton").prop('disabled', false);
            } else {
                $("#exportButton").prop('disabled', true);
            }
            
            if (Current.filename != "" && !alwaysLoadByFDID) {
                console.log("Loading " + Current.filename + " " + Current.fileDataID + " (" + Current.type + ")");
                var ptrName = allocate(intArrayFromString(Current.filename), 'i8', ALLOC_NORMAL);
                if (Current.type == "adt") {
                    Module._setScene(2, ptrName, -1);
                } else if (Current.type == "wmo") {
                    Module._setScene(1, ptrName, -1);
                } else if (Current.type == "m2") {
                    Module._setScene(0, ptrName, -1);
                    if(!Settings.newDisplayInfo){
                        loadModelTextures();
                    }else{
                        loadModelDisplays();
                    }
                } else {
                    console.log("Unsupported type: " + Current.type);
                }
            } else {
                console.log("Loading " + Current.fileDataID + " (" + Current.type + ")");
                if (Current.type == "adt") {
                    Module._setSceneFileDataId(2, Current.fileDataID, -1);
                } else if (Current.type == "wmo") {
                    Module._setSceneFileDataId(1, Current.fileDataID, -1);
                } else if (Current.type == "m2") {
                    Module._setSceneFileDataId(0, Current.fileDataID, -1);
                    if(!Settings.newDisplayInfo){
                        loadModelTextures();
                    }else{
                        loadModelDisplays();
                    }
                } else {
                    console.log("Unsupported type: " + Current.type);
                }
            }
        });
}

async function loadModelDisplays() {
    const cmdRows = await findCreatureModelDataRows();

    let results;
    if(cmdRows.length > 0){
        results = await loadCreatureDisplays(cmdRows);
    }else{
        results = await loadItemDisplays();
    }

    if (results == undefined || results.length == 0)
        return;
    
    const skinSelect = document.getElementById("skinSelect");
    skinSelect.length = 0;

    // Filenames?
    for(const result of results){
        var opt = document.createElement('option');

        if(result.ResultType == "creature"){
            // Backwards compat with current model texture setting
            opt.value = result['TextureVariationFileDataID[0]'] + "," + result['TextureVariationFileDataID[1]'] + "," + result['TextureVariationFileDataID[2]'];
            opt.dataset.displayid = result.ID;
            opt.dataset.type = 'creature';
        }else if(result.ResultType == "item"){
            // TODO: Material -> FDID lookup
            opt.value = result.ID;
            opt.dataset.displayid = result.ID;
            opt.dataset.type = 'item';
        }

        const givenDisplayID = 0;

        // TODO: If display ID is given (through URL params??), set to selected otherwise select first
        if(result.ID == givenDisplayID){
            opt.selected = true;
            setModelDisplay(result.ID, result.ResultType);
        } else if(skinSelect.children.length == 0){
            opt.selected = true;
            setModelDisplay(result.ID, result.ResultType);   
        }

        opt.innerHTML = "DisplayID " + result.ID;
        skinSelect.appendChild(opt);
    }

    skinSelect.style.display = "block";
}

async function findCreatureModelDataRows(){
    const response = await fetch("/dbc/api/find/CreatureModelData/?build=" + Current.buildName + "&col=FileDataID&val=" + Current.fileDataID);
    const json = await response.json();
    return json;
}

async function loadCreatureDisplays(cmdRows){
    const cdiPromises = Array(cmdRows.length);
    let index = 0;
    for (const cmdRow of cmdRows) {
        cdiPromises[index++] = fetch("/dbc/api/find/CreatureDisplayInfo/?build=" + Current.buildName + "&col=ModelID&val=" + cmdRow.ID);
    }

    const cdiResult = await Promise.all(cdiPromises);
    const data = Array(cdiResult.length);
    index = 0;
    for(const response of cdiResult)
        data[index++] = await response.json();

    const result = [];
    index = 0
    for (const entry of data){
        for (const row of entry){
            // TODO: Generic result format?
            result[index] = row;
            result[index].ResultType = "creature";
            index++;
        }
    }

    return result;
}

async function loadItemDisplays(){
    const response = await fetch("/dbc/api/peek/ModelFileData/?build=" + Current.buildName + "&col=FileDataID&val=" + Current.fileDataID);
    const modelFileData = await response.json();

    if(modelFileData.values['ModelResourcesID'] === undefined)
        return [];

    const idiPromises = [
        fetch("/dbc/api/find/ItemDisplayInfo/?build=" + Current.buildName + "&col=ModelResourcesID[0]&val=" + modelFileData.values['ModelResourcesID']),
        fetch("/dbc/api/find/ItemDisplayInfo/?build=" + Current.buildName + "&col=ModelResourcesID[1]&val=" + modelFileData.values['ModelResourcesID'])
    ];

    const idiResult = await Promise.all(idiPromises);
    const data = Array(idiResult.length);
    index = 0;
    for(const response of idiResult)
        data[index++] = await response.json();

    const result = [];
    index = 0
    for (const entry of data){
        for (const row of entry){
            // TODO: Generic result format?
            result[index] = row;
            result[index].ResultType = "item";
            index++;
        }
    }

    return result;
}

function loadModelTextures() {
    //TODO build, fix wrong skin showing up after initial load
    var loadedTextures = Array();
    var currentFDID = Current.fileDataID;
    $.ajax({url: "https://wow.tools/dbc/api/texture/" + Current.fileDataID + "?build=" + Current.buildName}).done( function(data) {
        var forFDID = this.url.replace("https://wow.tools/dbc/api/texture/", "").replace("?build=" + Current.buildName, "");
        if (Current.fileDataID != forFDID){
            console.log("This request is not for this filedataid, discarding..");
            return;
        }

        $("#skinSelect").empty();
        for (let displayId in data) {
            if (!data.hasOwnProperty(displayId)) continue;

            var intArray = data[displayId];
            if (intArray.every(fdid => fdid === 0)){
                continue;
            }

            // Open controls overlay
            $("#js-controls").removeClass("closed");
            $("#skinSelect").show();

            if (loadedTextures.includes(intArray.join(',')))
                continue;

            loadedTextures.push(intArray.join(','));

            $.ajax({
                type: 'GET',
                url: "https://wow.tools/files/scripts/filedata_api.php",
                data: {
                    filename: 1,
                    filedataid : intArray.join(",")
                }
            })
                .done(function( filename ) {
                    var textureFileDataIDs = decodeURIComponent(this.url.replace("https://wow.tools/files/scripts/filedata_api.php?filename=1&filedataid=", '')).split(',');
          
                    var textureFileDataID = textureFileDataIDs[0];

                    var optionHTML = '<option value="' + textureFileDataIDs + '"';

                    if ($('#skinSelect option').length == 0){
                        optionHTML += " SELECTED>";
                        if (textureFileDataIDs.length == 3 || textureFileDataIDs.length == 4){
                        // Creature
                            setModelTexture(textureFileDataIDs, 11);
                        } else {
                        // Item
                            setModelTexture(textureFileDataIDs, 2);
                        }
                    } else {
                        optionHTML += ">";
                    }

                    if (filename != ""){
                        var nopathname = filename.replace(/^.*[\\\/]/, '');
                        optionHTML += "(" + textureFileDataID + ") " + nopathname + "</option>";
                    } else {
                        optionHTML += textureFileDataID + "</option>";
                    }

                    $("#skinSelect").append(optionHTML);
                });
        }
    });
}

function queueDL(url){
    DownloadQueue.push(url);
    numDownloading++;

    if (!isDownloading){
        isDownloading = true;
        $("#downloadLabel").show();
    }
}

function unqueueDL(url){
    DownloadQueue = DownloadQueue.filter(function(queuedURL){ 
        return queuedURL != url; 
    });

    if (DownloadQueue.length == 0){
        isDownloading = false;
        $("#downloadLabel").hide();
    }

    numDownloading--;
}

function handleDownloadStarted(url){
    queueDL(url);
}

function handleDownloadFinished(url){
    unqueueDL(url);
}

// Called by texture model save button
function updateTextures(){
    const textureArray = new Int32Array(18);
    for (let i = 0; i < 18; i++){
        if (document.getElementById('tex' + i)){
            textureArray[i] = document.getElementById('tex' + i).value;
        }
    }
    setModelTexture(textureArray, 0);
}

async function setModelDisplay(displayID, type){
    if(type == "creature"){
        const response = await fetch("/dbc/api/peek/CreatureDisplayInfo/?build=" + Current.buildName + "&col=ID&val=" + displayID);
        const cdiRow = await response.json();

        if(Object.keys(cdiRow.values).length == 0)
            return;

        // Textures
        setModelTexture([cdiRow.values['TextureVariationFileDataID[0]'], cdiRow.values['TextureVariationFileDataID[1]'], cdiRow.values['TextureVariationFileDataID[2]']], 11);

        // Particle colors
        if(cdiRow.values['ParticleColorID'] != 0){
            const particleResponse = await fetch("/dbc/api/peek/ParticleColor/?build=" + Current.buildName + "&col=ID&val=" + cdiRow.values['ParticleColorID']);
            const particleRow = await particleResponse.json();
            Module._resetReplaceParticleColor();
            Module._setReplaceParticleColors(
                particleRow.values["Start[0]"], particleRow.values["Start[1]"], particleRow.values["Start[2]"],
                particleRow.values["MID[0]"], particleRow.values["MID[1]"], particleRow.values["MID[2]"],
                particleRow.values["End[0]"], particleRow.values["End[1]"], particleRow.values["End[2]"]
            );
        }else{
            Module._resetReplaceParticleColor();
        }

        // TODO: CreatureModelData for scale?
        // TODO: Geosets
    }else if(type == "item"){
        // TODO: Items
    }
}

function setModelTexture(textures, offset){
    //Create real texture replace array
    const typedArray = new Int32Array(18);

    for (let i = 0; i < textures.length; i++){
        if (offset == 11 && i == 3){
            var particleColorID = textures[3];
            console.log("Particle Color should be set to " + particleColorID);
            fetch("/dbc/api/peek/particlecolor?build=" + Current.buildName + "&col=ID&val=" + particleColorID)
                .then(function (response) {
                    return response.json();
                }).then(function (particleColorEntry) {
                    const row = particleColorEntry.values;
                    Module._setReplaceParticleColors(
                        row["Start[0]"], row["Start[1]"], row["Start[2]"],
                        row["MID[0]"], row["MID[1]"], row["MID[2]"],
                        row["End[0]"], row["End[1]"], row["End[2]"]
                    );
                }).catch(function (error) {
                    console.log("An error occured retrieving particle colors for ID " + particleColorID);
                });
        } else {
            typedArray[offset + i] = textures[i];
            const inputTarget = offset + i;
            if (document.getElementById('tex' + inputTarget)){
                document.getElementById('tex' + inputTarget).value = textures[i];
            }
        }
    }

    // Allocate some space in the heap for the data (making sure to use the appropriate memory size of the elements)
    let buffer = Module._malloc(typedArray.length * typedArray.BYTES_PER_ELEMENT);

    // Assign the data to the heap - Keep in mind bytes per element
    Module.HEAP32.set(typedArray, buffer >> 2);

    Module._setTextures(buffer, typedArray.length);

    Module._free(buffer);
}

function updateURLs(){
    var url = "https://wow.tools/casc/file/fname?buildconfig=" + Current.buildConfig + "&cdnconfig=" + Current.cdnConfig +"&filename=";

    let urlFileId;
    if (Settings.retailOnly){
        urlFileId = "https://wow.tools/casc/extract/";
    } else {
        urlFileId = "https://wow.tools/casc/file/fdid?buildconfig=" + Current.buildConfig + "&cdnconfig=" + Current.cdnConfig +"&filename=data&filedataid=";
    }

    var ptrUrl = allocate(intArrayFromString(url), 'i8', ALLOC_NORMAL);
    var ptrUrlFileDataId = allocate(intArrayFromString(urlFileId), 'i8', ALLOC_NORMAL);

    Module._setNewUrls(ptrUrl, ptrUrlFileDataId);

    _free(ptrUrl);
    _free(ptrUrlFileDataId);
}

function exportScene(){
    if (Current.type == "m2"){
        Module._startExport();
    }
}

(function() {
    $('#wowcanvas').bind('contextmenu', function(e){
        return false;
    });

    // Skip further initialization in embedded mode
    if (embeddedMode){
        return;
    }

    loadSettings();

    Elements.table = $('#mvfiles').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "/files/scripts/api.php",
            "data": function ( d ) {
                return $.extend( {}, d, {
                    "src": "mv",
                    "showADT": $("#showADT").is(":checked"),
                    "showWMO": $("#showWMO").is(":checked"),
                    "showM2": $("#showM2").is(":checked")
                } );
            }
        },
        "pageLength": 20,
        "autoWidth": false,
        "pagingType": "input",
        "orderMulti": false,
        "ordering": true,
        "order": [[0, 'asc']],
        "dom": 'fprt',
        "columnDefs":
        [
            {
                "targets": 0,
                "orderable": false,
                "visible": false
            },
            {
                "targets": 1,
                "orderable": false,
                "createdCell": function (td, cellData, rowData, row, col) {
                    if (!cellData && !rowData[7]) {
                        $(td).css('background-color', '#ff5858');
                        $(td).css('color', 'white');
                    }
                },
                "render": function ( data, type, full, meta ) {
                    if (full[1]) {
                        var test = full[1].replace(/^.*[\\\/]/, '');
                    } else {
                        if (!full[4]){
                            full[4] = "unk";
                        }
                        if (full[7]){
                            var test = full[7].replace(/^.*[\\\/]/, '');
                        } else {
                            var test = "Unknown filename (Type: " + full[4] + ", ID " + full[0] + ")";
                        }
                    }

                    return test;
                }
            },
            {
                "targets": 2,
                "orderable": false,
                "render": function ( data, type, full, meta ) {
                    var test = "";
                    if (full[3].length > 1){
                        test = "<div class='btn-group'><button class='btn btn-sm dropdown-toggle historybutton' type='button' data-toggle='dropdown'><i class='fa fa-clock-o'></i></button>";
                        test += "<div class='dropdown-menu'>";

                        full[3].sort(function(a, b){
                            if (a['description'] < b['description']) { return 1; }
                            if (a['description'] > b['description']) { return -1; }
                            return 0;
                        })

                        full[3].forEach(function (value) {
                            test += "<a class='dropdown-item filedropdown' href='#' onClick='loadModel(\"" + full[4] + "\", " + full[0] + ", \"" + value['buildconfig'] + "\", \"" + value['cdnconfig'] + "\");'>" + value['description'] + "</a>";
                        });

                        test += "</div></div>";
                    } else {
                        test = "<div class='btn-group'><button class='btn btn-sm historybutton' type='button' disabled><i class='fa fa-clock-o'></i></button></div>";
                    }

                    return test;
                }
            }
        ],
        "language": {
            search: "",
            searchPlaceholder: "Search"
        }
    });

    $(".filterBox").on('change', function(){
        Elements.table.ajax.reload();
    });
}());