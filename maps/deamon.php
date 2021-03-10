<!DOCTYPE html>
<html>
<head>
    <title>WoW.tools | World of Warcraft from above</title>
    <meta name='description' content='Collection of several World of Warcraft tools (DBC/file browser, modelviewer & more).'>
	<meta property='og:description' content='Collection of several World of Warcraft tools (DBC/file browser, modelviewer & more).'>
	<meta property='og:type' content='website'>
	<meta property='og:site_name' content='WoW.tools'>
	<meta property='og:title' content='WoW.tools | World of Warcraft from above'>
	<meta property='og:image' content='https://wow.tools/pub/prevdeamon.png'>
	<meta property='twitter:image' content='https://wow.tools/pub/prevdeamon.png'>
	<meta property='twitter:card' content='summary'>
	<meta property='twitter:site' content='@WoWdotTools'>
	<meta name='application-name' content='WoW.tools'>
	<meta name='apple-mobile-web-app-title' content='WoW.tools'>
	<meta name='theme-color' content='#343a40'>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="icon" type="image/png" href="/img/cogw.png" />
    <link rel="apple-touch-icon" href="/img/cogw-192.png">
</head>
<body>
<link href="/maps/css/leaflet.css" rel="stylesheet" type="text/css">
<link href="/maps/css/style.css" rel="stylesheet" type="text/css">
<style type='text/css'>
    html{
        height: 100%;
    }

    body{
        margin: 0px;
        padding: 0px;
    }

    nav{
        display: none;
    }

    .map-canvas {
        width: 100%;
        height: 100%;
        z-index: 0;
    }

    #embeddedLogo {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 50px;
    margin-bottom: 5px;
    margin-left: 5px;
}
</style>
<div id='maps'>
    <div id="js-map" class="map-canvas">&nbsp;</div>
    <script type="text/javascript" src="/maps/js/leaflet.js"></script>
    <script type='text/javascript'>
    /* global google:false */
    (function(){
        var originalInitTile = L.GridLayer.prototype._initTile
        L.GridLayer.include({
            _initTile: function (tile) {
                originalInitTile.call(this, tile);

                var tileSize = this.getTileSize();

                tile.style.width = tileSize.x + 1 + 'px';
                tile.style.height = tileSize.y + 1 + 'px';
            }
        });
    var LeafletMap = InitializeMap();

    function InitializeMap()
    {
        var map = new L.map('js-map', {
            center: [-50, 100],
            zoom: 3,
            minZoom: 1,
            maxNativeZoom : 7,
            maxZoom: 10,
            crs: L.CRS.Simple,
            zoomControl: false,
            preferCanvas: true
        });

        var layer  = new L.tileLayer("https://deamon.marlam.in/kalimdor/rot0/{z}/{y}/{x}.png", {
            attribution: '<a href="https://twitter.com/dmitolm">Deamon, Map author</a> | World of Warcraft &copy; Blizzard Entertainment',
            continuousWorld: true,
            maxNativeZoom : 7,
            maxZoom: 10
        }).addTo(map);

        return map;
    }
}());
    </script>
    </div>
    <div id="embeddedLogo">
        <a target='_BLANK' href='https://wow.tools/' title='WoW.tools'><img style='width: 50px' src='/img/newlogo.svg'></a>
    </div>
</body>
</html>