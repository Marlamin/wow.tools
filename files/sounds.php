<?php

if (!empty($_GET['embed'])) {
    require_once("/var/www/wow.tools/inc/config.php");
} else {
    require_once("../inc/header.php");
}

if (!empty($_GET['embed']) && !empty($_GET['skitid'])) {
    
?>
    <table id='files' class="table table-striped table-bordered table-condensed" cellspacing="0" style='margin: auto; ' width="100%">
        <thead>
            <tr>
                <th style='width: 50px;'>File ID</th>
                <th>Filename</th>
                <th style='width: 100px;'>Lookup</th>
                <th style='width: 215px;'>Versions</th>
                <th style='width: 50px;'>Type</th>
                <th style='width: 20px;'>&nbsp;</th><th style='width: 20px;'>&nbsp;</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>

<script type='text/javascript'>
    (function() {
        var apiUrl = "/files/scripts/api.php";

        var previewTypes = ["ogg", "mp3", "blp", "wmo", "m2"];

        var table = $('#files').DataTable({
            "processing": true,
            "serverSide": true,
            "search": { "search": "skit:<?=$_GET['skitid']?>" },
            "ajax": apiUrl,
            "pageLength": 25,
            "dom":
"<'row'<'col-sm-12'tr>>" +
"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            "autoWidth": false,
            "pagingType": "input",
            "orderMulti": false,
            "order": [[0, "asc"]],
            "columnDefs": [
            {
                "targets": 1,
                "orderable": true,
                "createdCell": function (td, cellData, rowData, row, col) {
                    if (!cellData) {
                        if(!rowData[7]){
                            $(td).css('background-color', '#ff5858');
                        }else{
                            $(td).css('background-color', '#673AB7');
                        }
                        $(td).css('color', 'white');
                    }
                },
                "render": function ( data, type, full, meta ) {
                    if(full[1]){
                        var test = full[1];
                    }else{
                        var test = "";
                        if(full[7]){
                            test = full[7];
                        }
                    }

                    return test;
                }
            },
            {
                "targets": [2,3,4,5],
                "visible": false
            },
            {
                "targets": 6,
                "orderable": false,
                "render": function ( data, type, full, meta ) {
                    if(full[3].length && full[3][0].enc != 1){
                        var test = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer' data-toggle='modal' data-target='#previewModal' onClick='fillPreviewModal(\"" + full[3][0].buildconfig + "\",\"" + full[0] + "\")'><i class='fa fa-play'></i></a></td>";

                    }else{
                    if(full[3].length > 0){
                        if(full[3][0].enc == 1){
                            var test = "<i style='color: red' title='File is encrypted (key " + full[3][0].key + " unknown)' class='fa fa-lock'></i> ";
                        }else if(full[3][0].enc == 2){
                            var test = "<i style='color: green' title='File is encrypted (key " + full[3][0].key + " is available)' class='fa fa-unlock'></i> ";
                        }else if(full[3][0].enc == 3){
                            var test = "<i style='color: yellow' title='File is encrypted (keys " + full[3][0].key + " are partially available)' class='fa fa-lock'></i> ";
                        }else{
                            var test = "<i class='fa fa-ban' style='opacity: 0.3'></i></td>";
                        }
                    }else{
                        var test = "";
                    }
                    }
                    return test;
                }
            }
            ]
        });
}());
</script>
    <?

    die();
}
$_GET['dbc'] = "soundkitname";

foreach($pdo->query("SELECT * FROM wow_dbc_tables WHERE name = 'soundkitname' ORDER BY name ASC") as $dbc){
    $tables[$dbc['id']] = $dbc;
    if(!empty($_GET['dbc']) && $_GET['dbc'] == $dbc['name']) $currentDB = $dbc;
}

$vq = $pdo->prepare("SELECT * FROM wow_dbc_table_versions LEFT JOIN wow_builds ON wow_dbc_table_versions.versionid=wow_builds.id WHERE wow_dbc_table_versions.tableid = ?  AND wow_dbc_table_versions.hasDefinition = 1 ORDER BY version DESC");
$vq->execute([$currentDB['id']]);
$version = $vq->fetch();
?>
<link href="/dbc/css/dbc.css?v=<?=filemtime("/var/www/wow.tools/dbc/css/dbc.css")?>" rel="stylesheet">
<div class="container-fluid">
<div class='alert alert-warning'>
    Blizzard removed SoundKit names during 8.3 so soundkits more recent than that will not be available on this page.
</div>
    <br>
    <div id='tableContainer'>
        <table id='dbtable' class="table table-striped table-bordered table-condensed" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Preview</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<div class="modal" id="moreInfoModal" tabindex="-1" role="dialog" aria-labelledby="moreInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moreInfoModalLabel">More information</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="moreInfoModalContent">
                <i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="previewModalContent">
                <i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script src="/files/js/files.js?v=<?=filemtime("/var/www/wow.tools/files/js/files.js")?>" crossorigin="anonymous"></script>
<script src="/dbc/js/dbc.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/dbc.js")?>"></script>
<script type='text/javascript'>
    (function() {
        var vars = {};
        var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
            vars[key] = value;
        });

        vars["dbc"] = "soundkitname";

        vars["build"] = "<?=$version['version']?>";
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
            "pageLength": 25,
            "displayStart": page * 25,
            "autoWidth": true,
            "pagingType": "input",
            "orderMulti": false,
            "ordering": false,
            "search": { "search": searchString },
            "columnDefs": [
            {
                "targets": 0,
                "visible": false
            },
            {
                "targets": 2,
                "render": function ( data, type, full, meta ) {
                    return "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer' data-toggle='modal' data-target='#moreInfoModal' onClick='fillSkitModal(" + full[0] + ")'>Show files</a></td>"
                }

            }],
            "createdRow": function( row, data, dataIndex ) {
                if(dataIndex == highlightRow){
                    $(row).addClass('highlight');
                    highlightRow = -1;
                }
            },
        });

        $('#dbtable').on( 'draw.dt', function () {
            var currentSearch = encodeURIComponent($("#dbtable_filter label input").val());
            var currentPage = $('#dbtable').DataTable().page() + 1;
            window.location.hash = "search=" + currentSearch + "&page=" + currentPage;
        });

    }());
</script>
<?php require_once("../inc/footer.php"); ?>