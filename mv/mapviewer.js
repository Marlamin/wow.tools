(function()
{
    var LeafletMap = InitializeMap();
    var TileLayer;
    var Current =
    {
        Map: false,
        InternalMap: false,
        InternalMapID: false,
        Version: 803,
        wdtFileDataID: 0
    };

    var Elements =
    {
        Maps: document.getElementById( 'js-map-select' ),
        Versions: document.getElementById( 'js-version-select' ),
        Map: document.getElementById( 'js-map' ),
        TabButton: document.getElementById( 'mapViewerButton' )
    };

    var Versions;

    var maxSize = 51200 / 3; 		//17066,66666666667
    var mapSize = maxSize * 2; 		//34133,33333333333
    var adtSize = mapSize / 64; 	//533,3333333333333

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = Initialize;
    xhr.open( 'GET', '/maps/data/data.json?cb=26', true );
    xhr.responseType = 'json';
    xhr.send();

    Elements.TabButton.addEventListener("click", function (){
        setTimeout(function(){ LeafletMap.invalidateSize()}, 400);
    });
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

    function Initialize()
    {
        if ( xhr.readyState !== 4 )
        {
            return;
        }

        if ( xhr.status !== 200 || !xhr.response.maps )
        {
            alert( 'Failed to load JSON data. Whoops.' );
            return;
        }

        Versions = xhr.response.versions;
        InitializeMapOptions( xhr.response.maps );
        InitializeEvents();
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

            if (map.wdtFileDataID != undefined){
                fragment.appendChild( option );
            }

            // Either first map, or specified map
            if ( i === 0 || map.internal === decodeURIComponent(url[ 2 ]) )
            {
                Current.Map = map.id;
                Current.InternalMap = map.internal;
                Current.InternalMapID = map.internal_mapid;
                Current.wdtFileDataID = map.wdtFileDataID;
                //Current.Version = '' + parseInt( url[ 3 ], 10 );
                if ( map.internal === decodeURIComponent(url[ 2 ]) )
                {
                    option.selected = true;
                }
            }
        } );

        Elements.Maps.appendChild( fragment );

        UpdateMapVersions();

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
            latlng = new L.LatLng(
                0,0
            );
        } else {
            urlSet = true;
        }

        RenderMap( latlng, zoom, true, urlSet);
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
            Current.Version = Elements.Versions.firstChild.value;
        }
    }


    function InitializeEvents()
    {
        Elements.Maps.addEventListener( 'change', function( )
        {
            //d( 'Changed map to ' + this.value + ' from ' + Current.Map );

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

        LeafletMap.on('click', function(e)
        {
            ProcessOffsetClick(e, Versions[Current.Map][Current.Version].config.offset.min);
        } );

        Elements.Maps.disabled = false;
        Elements.Versions.disabled = false;
    }

    function ProcessOffsetClick(e, offset){
        $("#js-controls").addClass("closed");

        var layerPoint = LeafletMap.project(e.latlng, Versions[ Current.Map ][ Current.Version ].config.maxzoom).floor();

        var build = Versions[Current.Map][Current.Version].build;
        var ingame = PointToWoW(layerPoint, offset, build);
        console.log("Setting map to " + Current.wdtFileDataID);
        var zPos = $("#mapZPos").val();
        window.Module._setMap(0, Current.wdtFileDataID, Math.floor(ingame.x), Math.floor(ingame.y), zPos);

        history.pushState({id: 'modelviewer'}, 'Model Viewer', '/mv/?wdt=' + Current.wdtFileDataID + '&pos=' + Math.floor(ingame.x) + ',' + Math.floor(ingame.y));
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

    function ChangeVersion()
    {
        if (Versions[Current.Map][Current.Version].config.offset.min.x == 63){
            RequestOffset();
        }

        Current.Version = Elements.Versions.value;

        RenderMap(LeafletMap.getCenter(), LeafletMap.getZoom(), false, true);
    }

    function RequestOffset(){
        var offsapixhr = new XMLHttpRequest();
        offsapixhr.responseType = 'json';
        offsapixhr.onreadystatechange = function() {
            if (offsapixhr.readyState === 4){
                if ('x' in offsapixhr.response){
                    offset = offsapixhr.response;
                    ProcessOffsetResult(offset);
                }
            }
        }

        offsapixhr.open( 'GET', '/maps/api.php?type=offset&build=' + Versions[Current.Map][Current.Version].build + '&map=' + Current.InternalMap, true );
        offsapixhr.send();
    }

    function RenderMap( center, zoom, isMapChange, urlSet )
    {
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

        SetMapCenterAndZoom( center, zoom, isMapChange, urlSet );
    }

    function SetMapCenterAndZoom( center, zoom, isMapChange, urlSet )
    {
        LeafletMap.setView( center , zoom , {animate: false} );

        if (!urlSet)
        {
            var mapbounds = new L.LatLngBounds(LeafletMap.unproject([1, Versions[ Current.Map ][ Current.Version ].config.resy - 1], Versions[ Current.Map ][ Current.Version ].config.maxzoom), LeafletMap.unproject([Versions[ Current.Map ][ Current.Version ].config.resx - 1, 1], Versions[ Current.Map ][ Current.Version ].config.maxzoom));
            LeafletMap.fitBounds(mapbounds);
        }
    }
}())