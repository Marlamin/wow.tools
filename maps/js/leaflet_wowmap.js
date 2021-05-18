/* global google:false */
(function()
{
    var LeafletMap = InitializeMap();
    var TileLayer;
    var Minimap;
    var MinimapLayer;
    var FlightPathLayer;
    var POILayer;
    var ADTGridLayer;
    var ADTGridTextLayer;
    var WorldMapLayer;
    var MNAMLayer;
    var Versions;
    var Elements =
    {
        Maps: document.getElementById( 'js-map-select' ),
        Versions: document.getElementById( 'js-version-select' ),
        PrevMap: document.getElementById( 'js-version-prev' ),
        NextMap: document.getElementById( 'js-version-next' ),
        Sidebar: document.getElementById( 'js-sidebar' ),
        Map: document.getElementById( 'js-map' ),
        TechBox: document.getElementById( 'js-techbox' ),
        Layers: document.getElementById('js-layers'),
        FlightLayer: document.getElementById('js-flightlayer'),
        POILayer: document.getElementById('js-poilayer'),
        ADTGrid: document.getElementById('js-adtgrid'),
        WorldMap: document.getElementById('js-worldmap'),
        MNAM: document.getElementById('js-mnam')
    };

    var Current =
    {
        Map: false,
        InternalMap: false,
        InternalMapID: false,
        Version: 0,
        wdtFileDataID: 0
    };

    var maxSize = 51200 / 3; 		//17066,66666666667
    var mapSize = maxSize * 2; 		//34133,33333333333
    var adtSize = mapSize / 64; 	//533,3333333333333

    // Sidebar button
    document.getElementById( 'js-sidebar-button' ).addEventListener( 'click', function( )
    {
        Elements.Sidebar.classList.toggle( 'closed' );
        document.getElementById( 'js-sidebar-button' ).classList.toggle( 'closed' );
    } );

    // Layer button
    document.getElementById( 'js-layers-button' ).addEventListener( 'click', function( )
    {
        Elements.Layers.classList.toggle( 'closed' );
    } );

    var d;
    var isDebug = window.location.hash === '#debug';

    if ( isDebug )
    {
        var debugEl = document.createElement( 'pre' );
        debugEl.style.zIndex = 1337;
        debugEl.style.color = '#FFF';
        debugEl.style.position = 'absolute';
        debugEl.style.bottom = '80px';
        debugEl.style.left = '5px';
        debugEl.style.maxHeight = '475px';
        debugEl.style.overflowY = 'hidden';
        debugEl.style.backgroundColor = 'rgba(0, 0, 0, .5)';

        document.body.appendChild( debugEl );

        d = function(text) { debugEl.textContent = text + "\n" + debugEl.textContent; };
    }
    else
    {
        d = function() {};
    }

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = Initialize;
    xhr.open( 'GET', '/maps/data/data.json?cb=23', true );
    xhr.responseType = 'json';
    xhr.send();

    var fpxhr = new XMLHttpRequest();
    fpxhr.onreadystatechange = ProcessFPResult;

    function Initialize()
    {
        if ( xhr.readyState !== 4 )
        {
            return;
        }

        d( 'JSON data loaded: ' + xhr.status );

        if ( xhr.status !== 200 || !xhr.response.maps )
        {
            alert( 'Failed to load JSON data. Whoops.' );

            return;
        }

        Versions = xhr.response.versions;

        InitializeMapOptions( xhr.response.maps );
        InitializeEvents();
    }

    function InitializeMap()
    {
        return new L.map('js-map', {
            center: [0, 0],
            zoom: 1,
            minZoom: 2,
            maxZoom: 7,
            crs: L.CRS.Simple,
            zoomControl: false,
            preferCanvas: true
        });
    }

    function InitializeMapOptions( maps )
    {
        var url = window.location.pathname.split( '/' ),
            option,
            fragment = document.createDocumentFragment();

        maps.forEach( function( map, i )
        {
            option = document.createElement( 'option' );
            option.dataset.internal = map.internal;
            option.dataset.imapid = map.internal_mapid;
            option.dataset.wdtfiledataid = map.wdtFileDataID;
            option.value = map.id;
            option.textContent = map.name;

            fragment.appendChild( option );

            // Either first map, or specified map
            if ( i === 0 || map.internal === decodeURIComponent(url[ 2 ]) )
            {
                d( 'Using ' + map.internal + ' for current status' );

                Current.Map = map.id;
                Current.InternalMap = map.internal;
                Current.InternalMapID = map.internal_mapid;
                Current.wdtFileDataID = map.wdtFileDataID;
                Current.Version = '' + parseInt( url[ 3 ], 10 );
                if ( map.internal === decodeURIComponent(url[ 2 ]) )
                {
                    option.selected = true;
                }
            }
        } );

        Elements.Maps.appendChild( fragment );

        UpdateMapVersions();

        d( 'Initialized map ' + Current.Map + ' on version ' + Current.Version );

        // Get zoom level, from url or fallback to default
        var zoom = parseInt( url[ 4 ], 10 ) || 0;

        var urlSet = false;

        // Get map coordinates
        if (parseFloat( url[ 5 ] ) && parseFloat( url[ 6 ] ) ){
            var latlng = new L.LatLng( parseFloat( url[ 5 ] ), parseFloat( url[ 6 ] ) );
        }

        // Fallback to map default if needed
        if ( !latlng || (isNaN( latlng.lat ) || isNaN( latlng.lng ) ) )
        {
            d('Falling back to center?');
            latlng = new L.LatLng(
                0,0
            );
        } else {
            urlSet = true;
        }

        RenderMap( latlng, zoom, true, urlSet);
    }

    function wowMapMatcher(params, data) {
        // If there are no search terms, return all of the data
        if ($.trim(params.term) === '') {
            return data;
        }

        // Do not display the item if there is no 'text' property
        if (typeof data.text === 'undefined') {
            return null;
        }

        if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
            var modifiedData = $.extend({}, data, true);
            return modifiedData;
        }

        if (data.element.dataset.internal.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
            var modifiedData = $.extend({}, data, true);
            modifiedData.text += ' (Internal match)';
            return modifiedData;
        }

        if (data.element.dataset.imapid != null && data.element.dataset.imapid == params.term){
            var modifiedData = $.extend({}, data, true);
            modifiedData.text += ' (MapID match)';
            return modifiedData;
        }

        return null;
    }

    function UpdateMapVersions()
    {
        var element,
            sortable = [],
            fragment = document.createDocumentFragment();

        // Turn versions object into a list so that it can be sorted
        Object.keys( Versions[ Current.Map ] ).forEach( function( versionId )
        {
            element = Versions[ Current.Map ][ versionId ];
            element.version = versionId;

            sortable.push( element );
        } );

        sortable
        // Sort versions by build
            .sort( function( a, b )
            {
                if ( a.build === b.build )
                {
                    return 0;
                }

                return a.build > b.build ? -1 : 1;
            } )
        // Append each version
            .forEach( function( version )
            {
                element = document.createElement( 'option' );
                element.value = version.version;

                // If we switch to another map, and current version is present in that map, select it
                if ( version.version === Current.Version )
                {
                    element.selected = true;
                }

                if ( version.branch != undefined && version.branch.length > 0 )
                {
                    element.textContent = version.fullbuild + ' (' + version.branch + ')';
                }
                else
                {
                    element.textContent = version.fullbuild;
                }

                fragment.appendChild( element );
            } );

        Elements.Versions.innerHTML = ''; // A bad way of removing children (from your life)
        Elements.Versions.appendChild( fragment );

        // If current version is not valid for this map, reset it
        if ( !Versions[ Current.Map ][ Current.Version ] )
        {
            d( 'Using first version' );

            Current.Version = Elements.Versions.firstChild.value;
        }

        UpdateArrowButtons();
    }

    function UpdateArrowButtons()
    {
        var element = Elements.Versions.options[ Elements.Versions.selectedIndex ];

        // Enable or disable arrow keys as necessary
        Elements.PrevMap.disabled = element.nextSibling === null;
        Elements.NextMap.disabled = element.previousSibling === null;
    }

    function InitializeEvents()
    {
        var select2El = $("#js-map-select").select2({ matcher: wowMapMatcher, disabled: false });
        Elements.MapSelect2 = select2El;
        Elements.MapSelect2.on( 'change', function(e)
        {
            d( '[SELECT2] Changed map to ' + this.value + ' from ' + Current.Map );

            Current.Map = this.value;
            Current.InternalMap = this.options[ this.selectedIndex ].dataset.internal;
            Current.InternalMapID = this.options[ this.selectedIndex ].dataset.imapid;
            Current.wdtFileDataID = this.options[ this.selectedIndex ].dataset.wdtfiledataid;
            UpdateMapVersions();

            RenderMap(
                LeafletMap.unproject(
                    [
                        Versions[ Current.Map ][ Current.Version ].config.resy / 2,
                        Versions[ Current.Map ][ Current.Version ].config.resx / 2
                    ], Versions[ Current.Map ][ Current.Version ].config.maxzoom)
                , 2, true, false
            );
        } );

        Elements.Versions.addEventListener( 'change', ChangeVersion );

        Elements.PrevMap.addEventListener( 'click', function( )
        {
            Elements.Versions.selectedIndex = Elements.Versions.selectedIndex + 1;

            ChangeVersion();
        } );

        Elements.NextMap.addEventListener( 'click', function( )
        {
            Elements.Versions.selectedIndex = Elements.Versions.selectedIndex - 1;

            ChangeVersion();
        } );

        Elements.FlightLayer.addEventListener( 'click', function( )
        {
            if (Elements.FlightLayer.checked){
                d('Enabled flight paths');
                if (Current.InternalMapID == undefined){
                    d("Unknown mapid, can't request fps");
                    return;
                }
                fpxhr.open( 'GET', '/maps/api.php?type=flightpaths&build=' + Versions[Current.Map][Current.Version].fullbuild + '&mapid=' + Current.InternalMapID);
                fpxhr.responseType = 'json';
                fpxhr.send();
            } else {
                d('Disabled flight paths');
                LeafletMap.removeLayer(FlightPathLayer);
                FlightPathLayer = new L.LayerGroup();
            }
        } );

        Elements.POILayer.addEventListener( 'click', function( )
        {
            if (Elements.POILayer.checked){
                d('Enabled POIs');
                if (Current.InternalMapID == undefined){
                    d("Unknown mapid, can't request POIs");
                    return;
                }

                var poixhr = new XMLHttpRequest();
                poixhr.responseType = 'json';
                poixhr.onreadystatechange = function() {
                    if (poixhr.readyState === 4){
                        ProcessPOIResult(poixhr.response);
                    }
                }

                poixhr.open( 'GET', '/maps/api.php?type=pois&build=' + Versions[Current.Map][Current.Version].fullbuild + '&mapid=' + Current.InternalMapID, true );
                poixhr.send();
            } else {
                d('Disabled POIs')
                LeafletMap.removeLayer(POILayer);
                POILayer = new L.LayerGroup();
            }
        } );

        Elements.ADTGrid.addEventListener( 'click', function( )
        {
            if (Elements.ADTGrid.checked){
                d('Enabled ADT grid');
                ADTGridLayer = new L.LayerGroup();
                ADTGridTextLayer = new L.LayerGroup();
                for (var x = 0; x < 64; x++){
                    for (var y = 0; y < 64; y++){
                        var fromlat = WoWtoLatLng(maxSize - (x * adtSize), -maxSize);
                        var tolat = WoWtoLatLng(maxSize - (x * adtSize), maxSize);
                        ADTGridLayer.addLayer(new L.polyline([fromlat, tolat], {weight: 0.1, color: 'red'}));

                        var fromlat = WoWtoLatLng(maxSize, maxSize - (x * adtSize));
                        var tolat = WoWtoLatLng(-maxSize , maxSize - (x * adtSize));
                        ADTGridLayer.addLayer(new L.polyline([fromlat, tolat], {weight: 0.1, color: 'red'}));
                    }
                }
                refreshADTGrid();
                ADTGridLayer.addTo(LeafletMap);
            } else {
                d('Disabled ADT grid')
                LeafletMap.removeLayer(ADTGridLayer);
                LeafletMap.removeLayer(ADTGridTextLayer);
            }
        } );

        Elements.WorldMap.addEventListener( 'click', function( )
        {
            if (Elements.WorldMap.checked){
                WorldMapLayer = new L.LayerGroup();
                drawWorldMap();
                LeafletMap.addLayer(WorldMapLayer);
            } else {
                LeafletMap.removeLayer(WorldMapLayer);
            }
        } );

        Elements.MNAM.addEventListener( 'click', function( )
        {
            if (Elements.MNAM.checked){
                MNAMLayer = new L.LayerGroup();
                drawMNAM();
                LeafletMap.addLayer(MNAMLayer);
            } else {
                LeafletMap.removeLayer(MNAMLayer);
            }
        } );

        LeafletMap.on('moveend zoomend dragend', function()
        {
            SynchronizeTitleAndURL();
            if (Elements.ADTGrid.checked){
                refreshADTGrid();
            }
        } );

        LeafletMap.on('click', function(e)
        {
            ProcessOffsetClick(e, Versions[Current.Map][Current.Version].config.offset.min);
        } );

        Elements.Maps.disabled = false;
        Elements.Versions.disabled = false;
    }

    function refreshADTGrid(){
        var drawing = 0;
        if (LeafletMap.getZoom() < 6){
            LeafletMap.removeLayer(ADTGridTextLayer);
            return;
        }

        LeafletMap.removeLayer(ADTGridTextLayer);
        ADTGridTextLayer = new L.LayerGroup();
        for (var x = 0; x < 64; x++){
            for (var y = 0; y < 64; y++){
                var latlng = WoWtoLatLng(maxSize - (x * adtSize) - 25, maxSize - (y * adtSize) - 25);
                if (LeafletMap.getBounds().contains(latlng)){
                    var myIcon = L.divIcon({className: 'adtcoordicon', html: '<div class="adtcoord">' + y + '_' + x + '</div>'});
                    ADTGridTextLayer.addLayer(new L.marker(latlng, {icon: myIcon}));
                    drawing++;
                }
            }
        }

        d( 'Refreshed ADT grid, drawing ' + drawing + ' coordinate boxes');
        LeafletMap.addLayer(ADTGridTextLayer);
    }

    function drawWorldMap(){
        var wmapxhr = new XMLHttpRequest();
        wmapxhr.open( 'GET', '/api/data/uimapassignment/?build=' + Versions[Current.Map][Current.Version].fullbuild + '&draw=1&start=0&length=10000&search%5Bvalue%5D=&search%5Bregex%5D=false&columns%5B13%5D%5Bsearch%5D%5Bvalue%5D=exact:' + Current.InternalMapID, true );
        wmapxhr.responseType = 'json';
        wmapxhr.onreadystatechange = function() {
            if (wmapxhr.readyState === 4){
                var drawn = new Array();
                for (var i = 0; i < wmapxhr.response.data.length; i++){
                    if (wmapxhr.response.data[i][13] == Current.InternalMapID && !drawn.includes(wmapxhr.response.data[i][11])){
                        var latlngx = WoWtoLatLng(wmapxhr.response.data[i][4], wmapxhr.response.data[i][5]);
                        var latlngy = WoWtoLatLng(wmapxhr.response.data[i][7], wmapxhr.response.data[i][8]);
                        WorldMapLayer.addLayer(new L.imageOverlay("/maps/worldmap/" + wmapxhr.response.data[i][11] + ".png", [latlngx, latlngy], {opacity: 0.67}));
                        drawn.push(wmapxhr.response.data[i][11]);
                        console.log("Drawn " + wmapxhr.response.data[i][11]);
                    }
                }
            }
        }
        wmapxhr.send();
    }

    function drawMNAM(){
        /* ICON STUFF */
        var coordArr = Array();
        var i = 0;
        var xPos = 0;
        var yPos = 0;
        for (var x = 0; x < 28; x++){
            for (var y = 0; y < 14; y++){
                coordArr[i] = [yPos, xPos];
                yPos += 18;
                i++;
            }
            yPos = 0;
            xPos += 18;
        }

        var mnamxhr = new XMLHttpRequest();
        mnamxhr.open( 'GET', '/maps/data/mnam/' + Current.wdtFileDataID + ".json", true );
        mnamxhr.responseType = 'json';
        mnamxhr.onreadystatechange = function() {
            if (mnamxhr.readyState === 4){
                if (mnamxhr.response.countB == 0){
                    return;
                }

                let mnam = mnamxhr.response.entriesB;
                for (var i = 0; i < mnam.length; i++){
                    var entry = mnam[i];
                    for (var j = 0; j < entry.posPlusNormalCount; j++){
                        var name = "ID " + entry.c + ", index " + j + ", type " + entry.type;
                        var icon = 45;
                        switch (entry.type){
                        case 2:
                            icon = 43;
                            break;
                        case 6:
                            icon = 44;
                            break;
                        default:
                            console.log("Encountered new type: " + entry.type);
                            icon = 45;
                            break;
                        }

                        var newLatLng = WoWtoLatLng(entry.posPlusNormal[j].position.X, entry.posPlusNormal[j].position.Y);

                        var leftPx = coordArr[icon][0] * -1;
                        var topPx = coordArr[icon][1] * -1;
                        let myIcon = L.divIcon({className: 'poiatlas', iconSize: [18, 18], html: '<div class="poiatlas" style="background-position: ' + leftPx + 'px ' + topPx + 'px;">&nbsp</div>'});

                        if (j > 1){

                            var prevLatLng = WoWtoLatLng(entry.posPlusNormal[j-1].position.X, entry.posPlusNormal[j-1].position.Y);
                            MNAMLayer.addLayer(new L.polyline([prevLatLng, newLatLng], {weight: 1, color: 'red'}));
                        }
                        MNAMLayer.addLayer(new L.marker(newLatLng, {icon: myIcon}).bindPopup(name));
                    }
                }
            }
        }
        mnamxhr.send();
    }

    function ProcessPOIResult(response){
        LeafletMap.removeLayer(POILayer);

        POILayer = new L.LayerGroup();

        if (Versions[Current.Map][Current.Version].config.offset.min.x == 63){
            RequestOffset();
        }

        var coordArr = Array();
        var i = 0;
        var xPos = 0;
        var yPos = 0;
        for (var x = 0; x < 28; x++){
            for (var y = 0; y < 14; y++){
                coordArr[i] = [yPos, xPos];
                yPos += 18;
                i++;
            }
            yPos = 0;
            xPos += 18;
        }

        for (var i = 0; i < response.length; i++){
            var entry = response[i];
            var leftPx = coordArr[entry.icon][0] * -1;
            var topPx = coordArr[entry.icon][1] * -1;
            var myIcon = L.divIcon({className: 'poiatlas', iconSize: [18, 18], html: '<div class="poiatlas" style="background-position: ' + leftPx + 'px ' + topPx + 'px;">&nbsp</div>'});

            POILayer.addLayer(new L.marker(WoWtoLatLng(entry.x, entry.y), {icon: myIcon}).bindPopup(entry.name));
        }

        POILayer.addTo(LeafletMap);
    }
    function RequestOffset(){
        d('Requesting offset');
        document.getElementById("clickedADT").textContent = "Loading..";
        document.getElementById("clickedCoord").textContent = "Loading..";
        var offsapixhr = new XMLHttpRequest();
        offsapixhr.responseType = 'json';
        offsapixhr.onreadystatechange = function() {
            if (offsapixhr.readyState === 4){
                if ('x' in offsapixhr.response){
                    offset = offsapixhr.response;
                    ProcessOffsetResult(offset);
                    document.getElementById("clickedADT").textContent = "Ready for click";
                    document.getElementById("clickedCoord").textContent = "Ready for click";
                } else {
                    document.getElementById("clickedADT").textContent = "Not supported on map.";
                    document.getElementById("clickedCoord").textContent = "Not supported on map.";
                }
            }
        }

        offsapixhr.open( 'GET', '/maps/api.php?type=offset&build=' + Versions[Current.Map][Current.Version].build + '&map=' + Current.InternalMap, true );
        offsapixhr.send();
    }

    function ProcessOffsetResult(offset){
        d('Processed new offset ' + offset.x +' ' + offset.y);
        var build = Versions[Current.Map][Current.Version].build;

        Versions[Current.Map][Current.Version].config.offset.min = offset;

        Elements.FlightLayer.disabled = false;
        Elements.POILayer.disabled = false;
        Elements.ADTGrid.disabled = false;
        Elements.WorldMap.disabled = false;
        Elements.MNAM.disabled = false;
    }

    function ProcessOffsetClick(e, offset){
        if (Versions[Current.Map][Current.Version].config.offset.min.x == -1 || Versions[Current.Map][Current.Version].config.offset.min.y == -1){
            document.getElementById("clickedCoord").textContent = "Not supported on map";
            document.getElementById("clickedADT").textContent = "Not supported on map";
            return;
        }

        var layerPoint = LeafletMap.project(e.latlng, Versions[ Current.Map ][ Current.Version ].config.maxzoom).floor();

        var build = Versions[Current.Map][Current.Version].build;
        var adt = PointToWoWTile(layerPoint, offset, build);
        var ingame = PointToWoW(layerPoint, offset, build);

        let zPos = 200;
        if (Current.InternalMapID == 2222){
            zPos = 5000;
        }
        document.getElementById("clickedCoord").textContent =  Math.floor(ingame.x) + ' ' + Math.floor(ingame.y) + ' ' + zPos + ' ' + Current.InternalMapID;
        document.getElementById("clickedADT").textContent = Current.InternalMap + '_' + adt.x + '_' + adt.y;
    }

    function ProcessFPResult()
    {
        if ( fpxhr.readyState !== 4 )
        {
            return;
        }

        LeafletMap.removeLayer(FlightPathLayer);

        FlightPathLayer = new L.LayerGroup();

        var allianceIcon = L.icon({
            iconUrl: '/maps/css/images/marker-icon-alliance.png',
            iconAnchor:  [12, 41],
            popupAnchor: [1, -34],
            shadowSize:  [41, 41]
        });

        var hordeIcon = L.icon({
            iconUrl: '/maps/css/images/marker-icon-horde.png',
            iconAnchor:  [12, 41],
            popupAnchor: [1, -34],
            shadowSize:  [41, 41]
        });

        var neutralIcon = L.icon({
            iconUrl: '/maps/css/images/marker-icon-neutral.png',
            iconAnchor:  [12, 41],
            popupAnchor: [1, -34],
            shadowSize:  [41, 41]
        });

        var unknownIcon = L.icon({
            iconUrl: '/maps/css/images/marker-icon-unknown.png',
            iconAnchor:  [12, 41],
            popupAnchor: [1, -34],
            shadowSize:  [41, 41]
        });

        for (var i = 0; i < fpxhr.response.ids.length; i++){
            var id = fpxhr.response.ids[i];

            var icon = unknownIcon;

            if (fpxhr.response.points[id].type == 'alliance'){
                icon = allianceIcon;
            } else if (fpxhr.response.points[id].type == 'horde'){
                icon = hordeIcon;
            } else if (fpxhr.response.points[id].type == 'neutral'){
                icon = neutralIcon;
            }

            FlightPathLayer.addLayer(new L.marker(WoWtoLatLng(fpxhr.response.points[id].x, fpxhr.response.points[id].y), {icon: icon}).bindPopup(fpxhr.response.points[id].name));
            if (fpxhr.response.points[id].connected){ // If it has connected flight points
                for (var j = 0; j < fpxhr.response.points[id].connected.length; j++){
                    var connectedID = fpxhr.response.points[id].connected[j];
                    if (fpxhr.response.points[connectedID]){ // If connected flight point exists, Blizzard actually references non-existant ones. :(
                        var fromlat = WoWtoLatLng(fpxhr.response.points[id].x, fpxhr.response.points[id].y);
                        var tolat = WoWtoLatLng(fpxhr.response.points[connectedID].x, fpxhr.response.points[connectedID].y);
                        if (fpxhr.response.points[id].type == 'alliance'){
                            FlightPathLayer.addLayer(new L.polyline([fromlat, tolat], {weight: 1, color: 'blue'}));
                        } else if (fpxhr.response.points[id].type == 'horde'){
                            FlightPathLayer.addLayer(new L.polyline([fromlat, tolat], {weight: 1, color: 'red'}));
                        } else if (fpxhr.response.points[id].type == 'neutral'){
                            FlightPathLayer.addLayer(new L.polyline([fromlat, tolat], {weight: 1, color: 'yellow'}));
                        }
                    }
                }
            }
        }

        FlightPathLayer.addTo(LeafletMap);
    }

    function WoWtoLatLng( x, y ){
        var pxPerCoord = adtSize / 256; //2.0833333333

        if (Versions[Current.Map][Current.Version].build > 26707){
            pxPerCoord = adtSize / 512;
        }

        if (Versions[Current.Map][Current.Version].config.offset.min.x == 63){
            d("Cannot do latlng lookup, no valid offset!");
            return;
        }

        offset = Versions[Current.Map][Current.Version].config.offset.min;

        var offsetX = (offset.y * adtSize) / pxPerCoord;
        var offsetY = (offset.x * adtSize) / pxPerCoord;

        var tempx = y * -1; //flip it (°□°）︵ ┻━┻)
        var tempx = (mapSize / 2 + tempx) / pxPerCoord - offsetX;
        var tempy = x * -1; //flip it (°□°）︵ ┻━┻)
        var tempy = (mapSize / 2 + tempy) / pxPerCoord - offsetY;
        return LeafletMap.unproject([tempx, tempy], Versions[ Current.Map ][ Current.Version ].config.maxzoom);
    }

    function LatLngToWoW( latlng ){
        return PointToWoW(LeafletMap.project(latlng, Versions[ Current.Map ][ Current.Version ].config.maxzoom), Versions[Current.Map][Current.Version].build);
    }

    function PointToWoW( point, offset, build ){
        var tileSize = 256;
        if (build > 26707){
            tileSize = 512;
        }

        var adtsToCenterX = ((point.y / tileSize) + offset.x) - 32;
        var adtsToCenterY = ((point.x / tileSize) + offset.y) - 32;

        var ingameX = -(adtsToCenterX * adtSize); // (╯°□°）╯︵ ┻━┻
        var ingameY = -(adtsToCenterY * adtSize); // (╯°□°）╯︵ ┻━┻

        return new L.Point(ingameX, ingameY);
    }

    function PointToWoWTile( point, offset, build ){
        var tileSize = 256;
        if (build > 26707){
            tileSize = 512;
        }
        var adtX = Math.floor((point.x / tileSize) + offset.y);
        var adtY = Math.floor((point.y / tileSize) + offset.x);

        return new L.Point(adtX, adtY);
    }

    function WoWTileAndCoordToMCNK(adt, ingame){
        var tileBaseY = -(adt.x - 32) * adtSize;
        var tileBaseX = -(adt.y - 32) * adtSize;

        return mcnkIndex = Math.floor((tileBaseX - ingame.x) / (adtSize / 16)) + 16 * Math.floor((tileBaseY - ingame.y) / (adtSize / 16));
    }

    function ChangeVersion()
    {
        d( 'Changed version to ' + Elements.Versions.value + ' from ' + Current.Version );

        var offsetBefore = Versions[Current.Map][Current.Version].config.offset.min;

        var center = LeafletMap.getCenter();

        if (Versions[Current.Map][Current.Version].config.offset.min.x == 63){
            RequestOffset();
        } else {
            if (isDebug){
                // Don't support offset adjustments when offset is initially unknown
                var offsetAfter = Versions[Current.Map][Elements.Versions.value].config.offset.min;

                d( 'Offset before: ' + offsetBefore.x + '_' + offsetBefore.y + ', after: ' + offsetAfter.x + '_' + offsetAfter.y);

                if (offsetBefore.x != offsetAfter.x || offsetBefore.y != offsetAfter.y){
                    d( 'Offset differs, map adjustment needed' );
                    if (offsetBefore.x != 63 && offsetAfter.x != 63){
                        // get current map loc and convert to wow
                        var wowCenter = LatLngToWoW(center);
                        d ('Current map center: ' + center.lat + ' ' + center.lng);
                        d ('Current wow center: ' + wowCenter.x + ' ' + wowCenter.y);
                        var newCenter = wowCenter;

                        // calculate offset... offset?
                        if (offsetBefore.x > offsetAfter.x){
                            // Positive x
                            var offsetX = offsetBefore.x - offsetAfter.x;
                            newCenter.x -= offsetX * adtSize;
                        } else if (offsetBefore.x < offsetAfter.x){
                            // Negative x
                            var offsetX = offsetAfter.x - offsetBefore.x;
                            newCenter.x += offsetX * adtSize;
                        }

                        if (offsetBefore.y > offsetAfter.y){
                            // Positive y
                            var offsetY = offsetBefore.y - offsetAfter.y;
                            newCenter.y -= offsetY * adtSize;
                        } else if (offsetBefore.y < offsetAfter.y){
                            // Negative y
                            var offsetY = offsetAfter.y - offsetBefore.y;
                            newCenter.y += offsetY * adtSize;
                        }

                        if (Number.isNaN(newCenter.x) || Number. isNaN(newCenter.y)){
                            center = LeafletMap.getCenter();
                        } else {
                            d ('New wow center: ' + newCenter.x + ' ' + newCenter.y);

                            center = WoWtoLatLng(newCenter.x, newCenter.y);

                            // bug?
                            center.lat = center.lat / 2;
                            center.lng = center.lng / 2;

                            d ('New map center: ' + center.lat + ' ' + center.lng);

                            // use old center for now
                            if (!isDebug){
                                center = LeafletMap.getCenter();
                            }
                        }
                    } else {
                        d( 'One of the offsets is unknown, not applying changes' );
                    }
                }
            }
        }

        Current.Version = Elements.Versions.value;

        RenderMap(center, LeafletMap.getZoom(), false, true);

        UpdateArrowButtons();

        SynchronizeTitleAndURL();
    }

    var markers = [];

    function RenderMap( center, zoom, isMapChange, urlSet )
    {
        var name = 'WoW_' + Current.Map + '_' + Current.Version;

        d( 'Loading map ' + name );

        LeafletMap.options.maxZoom = 10;

        var mapbounds = new L.LatLngBounds(LeafletMap.unproject([1, Versions[ Current.Map ][ Current.Version ].config.resy - 1], Versions[ Current.Map ][ Current.Version ].config.maxzoom), LeafletMap.unproject([Versions[ Current.Map ][ Current.Version ].config.resx - 1, 1], Versions[ Current.Map ][ Current.Version ].config.maxzoom));

        if (TileLayer){
            LeafletMap.removeLayer(TileLayer);
        }

        TileLayer = new L.tileLayer("https://wow.tools/maps/tiles/test/" + Current.Map + "/" + Versions[ Current.Map ][ Current.Version ].md5 + "/z{z}x{x}y{y}.png", {
            attribution: '<a href="/maps/list.php" title="Raw PNGs used to generate tiles for this viewer">Raw images</a> | World of Warcraft &copy; Blizzard Entertainment',
            continuousWorld: true,
            bounds: mapbounds,
            maxNativeZoom : Versions[ Current.Map ][ Current.Version ].config.maxzoom,
            maxZoom: 12
        }).addTo(LeafletMap);

        if (!center){
            var center = LeafletMap.getCenter();
        }

        if (!zoom){
            var zoom = LeafletMap.getZoom();
        }

        MinimapLayer = new L.TileLayer("https://wow.tools/maps/tiles/test/" + Current.Map + "/" + Versions[ Current.Map ][ Current.Version ].md5 + "/z{z}x{x}y{y}.png", {minZoom: 2, maxZoom: 2, continuousWorld: true, bounds: mapbounds});
        if (Minimap){
            Minimap.changeLayer(MinimapLayer);
        } else {
            Minimap = new L.Control.MiniMap(MinimapLayer, {toggleDisplay: true, zoomLevelFixed: 1, autoToggleDisplay: true}).addTo(LeafletMap);
        }

        SetMapCenterAndZoom( center, zoom, isMapChange, urlSet );

        if (isMapChange){
            document.getElementById("clickedCoord").textContent = "No click. :(";
            document.getElementById("clickedADT").textContent = "No click. :(";
        }

        Elements.FlightLayer.checked = false;
        Elements.POILayer.checked = false;
        Elements.ADTGrid.checked = false;
        Elements.WorldMap.checked = false;
        Elements.MNAM.checked = false;

        Elements.FlightLayer.disabled = true;
        Elements.POILayer.disabled = true;
        Elements.ADTGrid.disabled = true;
        Elements.WorldMap.disabled = true;
        Elements.MNAM.disabled = true;

        if (Versions[Current.Map][Current.Version].config.offset.min.x != 63){
            Elements.FlightLayer.disabled = false;
            Elements.POILayer.disabled = false;
            Elements.ADTGrid.disabled = false;
            Elements.MNAM.disabled = false;

            // uimapassignment builds only
            if (Versions[Current.Map][Current.Version].build > 26787){
                Elements.WorldMap.disabled = false;
            }
        }

        if (LeafletMap.hasLayer(FlightPathLayer)){ LeafletMap.removeLayer(FlightPathLayer); }
        FlightPathLayer = new L.LayerGroup();

        if (LeafletMap.hasLayer(POILayer)){ LeafletMap.removeLayer(POILayer); }
        POILayer = new L.LayerGroup();

        if (!isDebug){
            if (LeafletMap.hasLayer(ADTGridLayer)){ LeafletMap.removeLayer(ADTGridLayer); }
        }

        if (LeafletMap.hasLayer(ADTGridTextLayer)){ LeafletMap.removeLayer(ADTGridTextLayer); }
        if (!isDebug){
            ADTGridLayer = new L.LayerGroup();
        }
        ADTGridTextLayer = new L.LayerGroup();

        if (LeafletMap.hasLayer(WorldMapLayer)){ LeafletMap.removeLayer(WorldMapLayer); }
        WorldMapLayer = new L.LayerGroup();

        if (LeafletMap.hasLayer(MNAMLayer)){ LeafletMap.removeLayer(MNAMLayer); }
        MNAMLayer = new L.LayerGroup();
    }

    var markers = [];

    function SetMapCenterAndZoom( center, zoom, isMapChange, urlSet )
    {
        d("Setting center " + center + " and zoom " + zoom);

        LeafletMap.setView( center , zoom , {animate: false} );

        if (!urlSet)
        {
            d('Fitting map!');
            var mapbounds = new L.LatLngBounds(LeafletMap.unproject([1, Versions[ Current.Map ][ Current.Version ].config.resy - 1], Versions[ Current.Map ][ Current.Version ].config.maxzoom), LeafletMap.unproject([Versions[ Current.Map ][ Current.Version ].config.resx - 1, 1], Versions[ Current.Map ][ Current.Version ].config.maxzoom));
            LeafletMap.fitBounds(mapbounds);
        }
    }

    function SynchronizeTitleAndURL( isMapChange )
    {
        var latlng = LeafletMap.getCenter();

        var zoom = LeafletMap.getZoom();

        var current =
        {
            Zoom: zoom,
            LatLng: latlng,
            Current: Current
        };

        var title = Elements.Maps.options[ Elements.Maps.selectedIndex ].textContent + ' · ' + Versions[ Current.Map ][ Current.Version ].fullbuild + ' · Wow Minimap Browser';

        // Disable latlng linking for now
        var url = '/maps/' + Current.InternalMap + '/' + Current.Version + '/' + zoom + '/' + latlng.lat.toFixed(3) + '/' + latlng.lng.toFixed(3);

        if (isDebug){
            url += "#debug";
        }

        if ( isMapChange )
        {
            window.history.pushState( current, title, url );
        }
        else
        {
            window.history.replaceState( current, title, url );
        }

        document.title = title;

        d( 'URL: ' + url + ' (map change: ' + !!isMapChange + ')' );
    }
}());
