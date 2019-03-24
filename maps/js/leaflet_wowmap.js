/* global google:false */
(function()
{
	var LeafletMap = InitializeMap();
	var TileLayer;
	var Minimap;
	var MinimapLayer;
	var FlightPathLayer;
	var Versions;
	var Offsets;
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
		FlightLayer: document.getElementById('js-flightlayer')
	};

	var Current =
	{
		Map: false,
		InternalMap: false,
		InternalMapID: false,
		Version: 0
	};

	// Sidebar button
	document.getElementById( 'js-sidebar-button' ).addEventListener( 'click', function( )
	{
		Elements.Sidebar.classList.toggle( 'closed' );
	} );

	// Layer button
	document.getElementById( 'js-layers-button' ).addEventListener( 'click', function( )
	{
		Elements.Layers.classList.toggle( 'closed' );
	} );

	var d;
	// var isDebug = window.location.hash !== '#nodebug';
	var isDebug = false;

	if( isDebug )
	{
		var debugEl = document.createElement( 'pre' );
		debugEl.style.zIndex = 1337;
		debugEl.style.color = '#FFF';
		debugEl.style.position = 'absolute';
		debugEl.style.bottom = '0px';
		debugEl.style.left = '5px';
		debugEl.style.maxHeight = '150px';
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
	xhr.open( 'GET', '/maps/data/data.json', true );
	xhr.responseType = 'json';
	xhr.send();

	var offsxhr = new XMLHttpRequest();
	offsxhr.onreadystatechange = InitializeOffs;
	offsxhr.open( 'GET', '/maps/data/offsets.json', true );
	offsxhr.responseType = 'json';
	offsxhr.send();

	var anxhr = new XMLHttpRequest();
	anxhr.onreadystatechange = ProcessAreaResult;

	var fpxhr = new XMLHttpRequest();
	fpxhr.onreadystatechange = ProcessFPResult;

	function Initialize()
	{
		if( xhr.readyState !== 4 )
		{
			return;
		}

		d( 'JSON data loaded: ' + xhr.status );

		if( xhr.status !== 200 || !xhr.response.maps )
		{
			alert( 'Failed to load JSON data. Whoops.' );

			return;
		}

		Versions = xhr.response.versions;

		InitializeMapOptions( xhr.response.maps );
		InitializeEvents();
	}

	function InitializeOffs()
	{
		if( offsxhr.readyState !== 4 )
		{
			return;
		}

		d( 'Offset data loaded: ' + offsxhr.status );

		if( offsxhr.status !== 200 )
		{
			alert( 'Failed to load offset data. Whoops.' );

			return;
		}

		Offsets = offsxhr.response;
	}

	function InitializeMap()
	{
		return new L.map('js-map', {
			center: [0, 0],
			zoom: 1,
			minZoom: 2,
			maxZoom: 7,
			crs: L.CRS.Simple,
			zoomControl: false
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
			option.value = map.id;
			option.textContent = map.name;

			fragment.appendChild( option );

			// Either first map, or specified map
			if( i === 0 || map.internal === decodeURIComponent(url[ 2 ]) )
			{
				d( 'Using ' + map.internal + ' for current status' );

				Current.Map = map.id;
				Current.InternalMap = map.internal;
				Current.InternalMapID = map.internal_mapid;
				Current.Version = '' + parseInt( url[ 3 ], 10 );
				console.log(url);
				if( map.internal === decodeURIComponent(url[ 2 ]) )
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
		if(parseFloat( url[ 5 ] ) && parseFloat( url[ 6 ] ) ){
			var latlng = new L.LatLng( parseFloat( url[ 5 ] ), parseFloat( url[ 6 ] ) );
		}

		// Fallback to map default if needed
		if( !latlng || (isNaN( latlng.lat ) || isNaN( latlng.lng ) ) )
		{
			d('Falling back to center?');
			latlng = new L.LatLng(
				0,0
				);
		}else{
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
				if( a.build === b.build )
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
				if( version.version === Current.Version )
				{
					element.selected = true;
				}

				if( version.desc.length > 0 )
				{
					element.textContent = version.fullbuild + ' (' + version.desc + ')';
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
		if( !Versions[ Current.Map ][ Current.Version ] )
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
		Elements.Maps.addEventListener( 'change', function( )
		{
			d( 'Changed map to ' + this.value + ' from ' + Current.Map );

			Current.Map = this.value;
			Current.InternalMap = this.options[ this.selectedIndex ].dataset.internal;
			Current.InternalMapID = this.options[ this.selectedIndex ].dataset.imapid;

			UpdateMapVersions();

			RenderMap(
				LeafletMap.unproject(
					[
					Versions[ Current.Map ][ Current.Version ].config.resy / 2,
					Versions[ Current.Map ][ Current.Version ].config.resx / 2
					], LeafletMap.getMaxZoom())
				, 2, true, false
				);

			if(!Offsets[Versions[Current.Map][Current.Version].build]){
				Elements.FlightLayer.disabled = true;
			}else{
				Elements.FlightLayer.disabled = false;
			}
		} );

		Elements.Versions.addEventListener( 'change', ChangeVersion );

		Elements.PrevMap.addEventListener( 'click', function( )
		{
			Elements.Versions.selectedIndex = Elements.Versions.selectedIndex + 1;

			ChangeVersion();
		} );

		// L.event.addDomListener( document, 'keydown', function (e) {
		// 	console.log(e);

		// 	var element = Elements.Versions.options[ Elements.Versions.selectedIndex ];

		// 	if (e.keyCode == '37' && element.nextSibling !== null) {
		// 		Elements.Versions.selectedIndex = Elements.Versions.selectedIndex + 1;
		// 		ChangeVersion();
		// 	}
		// 	else if (e.keyCode == '39' && element.previousSibling !== null) {
		// 		Elements.Versions.selectedIndex = Elements.Versions.selectedIndex - 1;
		// 		ChangeVersion();
		// 	}
		// });

		Elements.NextMap.addEventListener( 'click', function( )
		{
			Elements.Versions.selectedIndex = Elements.Versions.selectedIndex - 1;

			ChangeVersion();
		} );

		Elements.FlightLayer.addEventListener( 'click', function( )
		{
			if(Elements.FlightLayer.checked){
				fpxhr.open( 'GET', '/api.php?type=flightpaths&id=' + Current.InternalMapID);
				fpxhr.responseType = 'json';
				fpxhr.send();
			}else{
				LeafletMap.removeLayer(FlightPathLayer);
				FlightPathLayer = new L.LayerGroup();
			}

		} );

		LeafletMap.on('dragend', function()
		{
			SynchronizeTitleAndURL();
		} );

		LeafletMap.on('zoomend', function()
		{
			SynchronizeTitleAndURL();
		} );

		LeafletMap.on('click', function(e)
		{
			var point = LeafletMap.project(e.latlng);

			var offset = null;

			if(!Offsets[Versions[Current.Map][Current.Version].build] || !(Current.InternalMap in Offsets[Versions[Current.Map][Current.Version].build])){
				var offsapixhr = new XMLHttpRequest();
				offsapixhr.responseType = 'json';
				offsapixhr.onreadystatechange = function() {
					if (offsapixhr.readyState === 4) {
						if('x' in offsapixhr.response){
							offset = offsapixhr.response;
							ProcessOffsetResult(e, offset);
						}else{
							document.getElementById("clickedADT").textContent = "Not supported on map.";
							document.getElementById("clickedCoord").textContent = "Not supported on map.";
						}
					}else{
						document.getElementById("clickedADT").textContent = "Not supported on map.";
						document.getElementById("clickedCoord").textContent = "Not supported on map.";
					}
				}

				offsapixhr.open( 'GET', '/api.php?type=offset&build=' + Versions[Current.Map][Current.Version].build + '&map=' + Current.InternalMap, true );
				offsapixhr.send();
			}else{
				offset = Offsets[Versions[Current.Map][Current.Version].build][Current.InternalMap];
				ProcessOffsetResult(e, offset);
			}
		} );

		Elements.Maps.disabled = false;
		Elements.Versions.disabled = false;
	}

	function ProcessOffsetResult(e, offset){
		if(offset != null && 'x' in offset){
			var layerPoint = LeafletMap.project(e.latlng, LeafletMap.getMaxZoom()).floor();

			var build = Versions[Current.Map][Current.Version].build;
			var adt = PointToWoWTile(layerPoint, offset, build);
			var ingame = PointToWoW(layerPoint, offset, build);

			document.getElementById("clickedCoord").textContent =  Math.floor(ingame.x) + ' ' + Math.floor(ingame.y) + ' 200 ' + Current.InternalMapID;
			document.getElementById("clickedADT").textContent = Current.InternalMap + '_' + adt.x + '_' + adt.y;

			var mcnkIndex = WoWTileAndCoordToMCNK(adt, ingame);

			anxhr.open( 'GET', '/api.php?type=areaname&id=' + Current.InternalMapID + '&adt=' + adt.x + '_' + adt.y + '&index=' + Math.floor(mcnkIndex), true );
			anxhr.responseType = 'json';
			anxhr.send();
		}else{
			document.getElementById("clickedADT").textContent = "Not supported on map.";
			document.getElementById("clickedCoord").textContent = "Not supported on map.";
		}
	}
	function ProcessAreaResult()
	{
		if( anxhr.readyState !== 4 )
		{
			return;
		}

		document.getElementById("clickedName").textContent = anxhr.response.name;
	}

	function ProcessFPResult()
	{
		if( fpxhr.readyState !== 4 )
		{
			return;
		}

		LeafletMap.removeLayer(FlightPathLayer);

		FlightPathLayer = new L.LayerGroup();

		var allianceIcon = L.icon({
			iconUrl: '/images/marker-icon-alliance.png',
			iconAnchor:  [12, 41],
			popupAnchor: [1, -34],
			shadowSize:  [41, 41]
		});

		var hordeIcon = L.icon({
			iconUrl: '/images/marker-icon-horde.png',
			iconAnchor:  [12, 41],
			popupAnchor: [1, -34],
			shadowSize:  [41, 41]
		});

		var neutralIcon = L.icon({
			iconUrl: '/images/marker-icon-neutral.png',
			iconAnchor:  [12, 41],
			popupAnchor: [1, -34],
			shadowSize:  [41, 41]
		});

		var unknownIcon = L.icon({
			iconUrl: '/images/marker-icon-unknown.png',
			iconAnchor:  [12, 41],
			popupAnchor: [1, -34],
			shadowSize:  [41, 41]
		});

		console.log(fpxhr.response);
		if(!Offsets[Versions[Current.Map][Current.Version].build]){
			console.log("Flight paths not supported");
		}else{
			for(var i = 0; i < fpxhr.response.ids.length; i++){
				var id = fpxhr.response.ids[i];

				var icon = unknownIcon;

				if(fpxhr.response.points[id].type == 'alliance'){
					icon = allianceIcon;
				}else if(fpxhr.response.points[id].type == 'horde'){
					icon = hordeIcon;
				}else if(fpxhr.response.points[id].type == 'neutral'){
					icon = neutralIcon;
				}

				FlightPathLayer.addLayer(new L.marker(WoWtoLatLng(fpxhr.response.points[id].x, fpxhr.response.points[id].y), {icon: icon}).bindPopup(fpxhr.response.points[id].name));
				if(fpxhr.response.points[id].connected){ // If it has connected flight points
					for(var j = 0; j < fpxhr.response.points[id].connected.length; j++){
						var connectedID = fpxhr.response.points[id].connected[j];
						if(fpxhr.response.points[connectedID]){ // If connected flight point exists, Blizzard actually references non-existant ones. :(
							var fromlat = WoWtoLatLng(fpxhr.response.points[id].x, fpxhr.response.points[id].y);
							var tolat = WoWtoLatLng(fpxhr.response.points[connectedID].x, fpxhr.response.points[connectedID].y);
							FlightPathLayer.addLayer(new L.polyline([fromlat, tolat], {weight: 1, color: 'yellow'}));
						}
					}
				}
			}

			FlightPathLayer.addTo(LeafletMap);
		}
	}

	var maxSize = 51200 / 3; 		//17066,66666666667
	var mapSize = maxSize * 2; 		//34133,33333333333
	var adtSize = mapSize / 64; 	//533,3333333333333
	var pxPerCoord = adtSize / 256; //2.0833333333

	function WoWtoLatLng( x, y ){
		var offset = Offsets[Versions[Current.Map][Current.Version].build][Current.InternalMap];

		var offsetX = (offset.y * adtSize) / pxPerCoord;
		var offsetY = (offset.x * adtSize) / pxPerCoord;

		var tempx = y * -1; //flip it (°□°）︵ ┻━┻)
		var tempx = (mapSize / 2 + tempx) / pxPerCoord - offsetX;
		var tempy = x * -1; //flip it (°□°）︵ ┻━┻)
		var tempy = (mapSize / 2 + tempy) / pxPerCoord - offsetY;

		return LeafletMap.unproject([tempx,tempy], LeafletMap.getMaxZoom());
	}

	function LatLngToWoW( latlng ){
		return PointToWoW(LeafletMap.project(latlng, LeafletMap.getMaxZoom()), Offsets[Versions[Current.Map][Current.Version].build][Current.InternalMap]);
	}

	function PointToWoW( point, offset, build ){
		var tileSize = 256;
		if(build > 26707){
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
		if(build > 26707){
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

		Current.Version = Elements.Versions.value;

		if(!Offsets[Versions[Current.Map][Current.Version].build]){
			Elements.FlightLayer.disabled = true;
		}else{
			Elements.FlightLayer.disabled = false;
		}

		RenderMap(LeafletMap.getCenter(), LeafletMap.getZoom(), false, true);

		UpdateArrowButtons();
	}

	var markers = [];

	function RenderMap( center, zoom, isMapChange, urlSet )
	{
		var name = 'WoW_' + Current.Map + '_' + Current.Version;

		d( 'Loading map ' + name );

		LeafletMap.options.maxZoom = Versions[ Current.Map ][ Current.Version ].config.maxzoom;

		var mapbounds = new L.LatLngBounds(LeafletMap.unproject([1, Versions[ Current.Map ][ Current.Version ].config.resy - 1], LeafletMap.getMaxZoom()), LeafletMap.unproject([Versions[ Current.Map ][ Current.Version ].config.resx - 1, 1], LeafletMap.getMaxZoom()));

		if(TileLayer){
			LeafletMap.removeLayer(TileLayer);
		}

		TileLayer = new L.tileLayer("https://newmaps.marlam.in/tiles/test/" + Current.Map + "/" + Versions[ Current.Map ][ Current.Version ].md5 + "/z{z}x{x}y{y}.png", {
			attribution: 'Map data &copy; Blizzard Entertainment',
			continuousWorld: true,
			bounds: mapbounds
		}).addTo(LeafletMap);

		if(!center){
			var center = LeafletMap.getCenter();
		}

		if(!zoom){
			var zoom = LeafletMap.getZoom();
		}

		var mapCenter = LeafletMap.unproject([Versions[ Current.Map ][ Current.Version ].config.resy / 2,Versions[ Current.Map ][ Current.Version ].config.resx / 2], LeafletMap.getMaxZoom());

		MinimapLayer = new L.TileLayer("https://newmaps.marlam.in/tiles/test/" + Current.Map + "/" + Versions[ Current.Map ][ Current.Version ].md5 + "/z{z}x{x}y{y}.png", {minZoom: 2, maxZoom: 2, continuousWorld: true, bounds: mapbounds});
		if(Minimap){
			Minimap.changeLayer(MinimapLayer);
		}else{
			Minimap = new L.Control.MiniMap(MinimapLayer, {toggleDisplay: true, zoomLevelFixed: 1, autoToggleDisplay: true}).addTo(LeafletMap);
		}

		SetMapCenterAndZoom( center, zoom, isMapChange, urlSet );

		if(isMapChange){
			document.getElementById("clickedCoord").textContent = "No click. :(";
			document.getElementById("clickedADT").textContent = "No click. :(";
			document.getElementById("clickedName").textContent = Elements.Maps.options[ Elements.Maps.selectedIndex ].textContent;
		}

		Elements.FlightLayer.checked = false;
		if(LeafletMap.hasLayer(FlightPathLayer)){ LeafletMap.removeLayer(FlightPathLayer); }
		FlightPathLayer = new L.LayerGroup();
	}

	var markers = [];

	function SetMapCenterAndZoom( center, zoom, isMapChange, urlSet )
	{
		d("Setting center " + center + " and zoom " + zoom);

		LeafletMap.setView( center , zoom );

		if(!urlSet)
		{
			d('Fitting map!');
			var mapbounds = new L.LatLngBounds(LeafletMap.unproject([1, Versions[ Current.Map ][ Current.Version ].config.resy - 1], LeafletMap.getMaxZoom()), LeafletMap.unproject([Versions[ Current.Map ][ Current.Version ].config.resx - 1, 1], LeafletMap.getMaxZoom()));
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

		if( isMapChange )
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
