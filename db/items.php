<?php

if (!empty($_GET['embed'])) {
    require_once("/var/www/wow.tools/inc/config.php");
} else {
    require_once("../inc/header.php");
}

if (!empty($_GET['embed']) && !empty($_GET['spellid'])) {
}

$_GET['dbc'] = "itemsearchname";

foreach ($pdo->query("SELECT * FROM wow_dbc_tables WHERE name = 'itemsearchname' ORDER BY name ASC") as $dbc) {
    $tables[$dbc['id']] = $dbc;
    if (!empty($_GET['dbc']) && $_GET['dbc'] == $dbc['name']) {
        $currentDB = $dbc;
    }
}

$vq = $pdo->prepare("SELECT * FROM wow_dbc_table_versions LEFT JOIN wow_builds ON wow_dbc_table_versions.versionid=wow_builds.id WHERE wow_dbc_table_versions.tableid = ?  AND wow_dbc_table_versions.hasDefinition = 1 ORDER BY version DESC");
$vq->execute([$currentDB['id']]);
$version = $vq->fetch();
?>
<link href="/dbc/css/dbc.css?v=<?=filemtime("/var/www/wow.tools/dbc/css/dbc.css")?>" rel="stylesheet">
<div class="container-fluid">
    <div class='row'>
        <div class='col-md-4'>    
            <br>   
            <div id='tableContainer'>
                <table id='dbtable' class="table table-striped table-bordered table-condensed" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
        <div class='col-md-8' id='itemFrame'>    
        </div>
    </div> 
</div>
<style type='text/css'>
tr.selected{
    background-color: #8bc34aa1 !important;
}
</style>
<script src="/files/js/files.js?v=<?=filemtime("/var/www/wow.tools/files/js/files.js")?>" crossorigin="anonymous"></script>
<script type='text/javascript'>
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    (function() {
        vars["build"] = SiteSettings.buildName;
        vars["dbc"] = "itemsearchname";
        var cleanDBC = vars["dbc"];

        var searchHash = location.hash.substr(1),
        searchString = searchHash.substr(searchHash.indexOf('search=')).split('&')[0].split('=')[1];

        if(searchString != undefined && searchString.length > 0){
            searchString = decodeURIComponent(searchString);
        }

        var page = (parseInt(searchHash.substr(searchHash.indexOf('page=')).split('&')[0].split('=')[1], 10) || 1) - 1;
        var highlightRow = parseInt(searchHash.substr(searchHash.indexOf('row=')).split('&')[0].split('=')[1], 10) - 1;

        var table = $('#dbtable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "/dbc/api/data/" + vars["dbc"] + "/?build=" + vars["build"],
                "data": function( result ) {
                    delete result.columns;
                    return result;
                }
            },
            "pageLength": 22,
            "displayStart": page * 22,
            "dom": "<'row'<'col-sm-12 col-md-12'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-12'p>>",
            "autoWidth": true,
            "pagingType": "input",
            "orderMulti": false,
            "ordering": false,
            "search": { "search": searchString },
            "createdRow": function( row, data, dataIndex ) {
                if(dataIndex == highlightRow){
                    $(row).addClass('highlight');
                    highlightRow = -1;
                }
            },
            "columns": [
                { "data" : 2 },
                { "data" : 1 }
            ]
        });

        $('#dbtable').on( 'draw.dt', function () {
            var currentSearch = encodeURIComponent($("#dbtable_filter label input").val());
            var currentPage = $('#dbtable').DataTable().page() + 1;
            window.location.hash = "search=" + currentSearch + "&page=" + currentPage;
        });

        $('#dbtable').on('click', 'tbody tr td', function() {
            $(".selected").removeClass("selected");
            $(this).parent().addClass('selected');

            var data = table.row($(this).parent()).data();
            loadItem(data[2], data[1]);
        });

    }());

    function loadItem(id){
        var iframe = document.createElement('iframe');
        iframe.frameBorder=0;
        iframe.width="100%";
        iframe.height="900px";
        iframe.setAttribute("src", "/db/item.php?itemID="+ id + "&embed=true");
        document.getElementById('itemFrame').innerHTML = "";
        document.getElementById('itemFrame').appendChild(iframe);
    }
</script>
<?php require_once("../inc/footer.php"); ?>