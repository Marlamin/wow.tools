<?php

require_once("../inc/header.php");

$productq = $pdo->query("SELECT * FROM ngdp_products WHERE program LIKE 'wow%' ORDER BY name DESC");
$products = [];
while ($row = $productq->fetch()) {
    $products[] = array("name" => $row['name'], "product" => $row['program']);
}
?>
<div class='container-fluid'>
<div class='alert alert-info'>
Due to incompetent reporting by some "press" I've decided to limit the monitor to only showing World of Warcraft products, possibly permanently. If you were using the monitor for non-WoW products, please find another source.
</div>
<table id='files' class="table table-striped table-bordered table-condensed" cellspacing="0" style='margin: auto; table-layout: fixed;' width="100%">
<thead>
<tr>
<th style='width: 120px;'>Timestamp</th>
<th style='width: 220px;'>URL</th>
<th style='max-width: calc(100% - calc(120px + 220px)); overflow: hidden'>Diff</th>
</tr>
</thead>
<tbody>
</tbody>
</table>
</div>
<link href="css/monitor.css?v=<?=filemtime(__DIR__ . "/css/monitor.css")?>" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/diff2html/bundles/css/diff2html.min.css" />
<script src="https://cdn.jsdelivr.net/npm/diff2html/bundles/js/diff2html.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/diff2html/bundles/js/diff2html-ui.min.js"></script>
<script src="/files/js/files.js?v=<?=filemtime(__DIR__ . "/../files/js/files.js")?>"></script>
<style type='text/css'>
#files_paginate{
    float: right;
}
</style>
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
<script type='text/javascript'>
    function fillDiffModal(from, to){
        $( "#previewModalContent" ).load( "/monitor/scripts/diff.php?from=" + from + "&to=" + to);
    }

    (function() {
        var searchHash = location.hash.substr(1),
        searchString = searchHash.substr(searchHash.indexOf('search=')).split('&')[0].split('=')[1];

        if(searchString != undefined && searchString.length > 0){
            searchString = decodeURIComponent(searchString);
        }

        var page = (parseInt(searchHash.substr(searchHash.indexOf('page=')).split('&')[0].split('=')[1], 10) || 1) - 1;
        var selectedProduct = searchHash.substr(searchHash.indexOf('product=')).split('&')[0].split('=')[1];
        var products = <?=json_encode($products)?>;

        var sortCol = searchHash.substr(searchHash.indexOf('sort=')).split('&')[0].split('=')[1];
        var sortDesc = searchHash.substr(searchHash.indexOf('desc=')).split('&')[0].split('=')[1];

        if(!sortCol){
            sortCol = 0;
        }

        if(!sortDesc){
            sortDesc = "desc";
        }
        var previewTypes = ["ogg", "mp3", "blp", "wmo", "m2"];

        var table = $('#files').DataTable({
            "processing": true,
            "serverSide": true,
            "searching": true,
            "search": { "search": searchString },
            "dom": "<'row'<'col-sm-6 col-md-2'l><'col-sm-12 col-md-10'pf>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            "ajax": "scripts/api.php",
            "pageLength": 5,
            "displayStart": page * 5,
            "autoWidth": false,
            "pagingType": "input",
            "orderMulti": false,
            "order": [[sortCol, sortDesc]],
            "lengthMenu": [[5, 10, 25, 50], [5, 10, 25, 50]],
            "searchCols": [null, {"search": selectedProduct ? '/'+selectedProduct+'/' : '', regex: true }, null],
            initComplete: function () {
                this.api().columns().every( function (col) {
                    var column = this;
                    if(col == 1){
                        var select = $('<select id="productSelect" style="max-width: 100%"><option value="">Product</option></select>')
                        .appendTo( $(column.header()).empty() )
                        .on( 'change', function () {
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                                );

                            column
                            .search( val ? '/'+val+'/' : '', true, false )
                            .draw();
                        } );

                        products.forEach(function(product){
                            if(selectedProduct == product.product){
                                select.append('<option value="'+ product.product +'" SELECTED>'+product.name+' ('+product.product+')</option>');
                            }else{
                                select.append('<option value="'+ product.product +'">'+product.name+' ('+product.product+')</option>');
                            }
                        });
                    }
                } );
            },
            "columnDefs": [
            {
                "targets": [1,2],
                "orderable": false,
            }],
            "language": {"search": ""}
        });
    }());

    $('#files').on( 'draw.dt', function () {
        var currentSearch = encodeURIComponent($("#files_filter label input").val());
        var currentPage = $('#files').DataTable().page() + 1;

        var sort = $('#files').DataTable().order();
        var sortCol = sort[0][0];
        var sortDir = sort[0][1];

        var product = $('#files').DataTable().column(1).search();

        var url = "search=" + currentSearch + "&page=" + currentPage + "&sort=" + sortCol +"&desc=" + sortDir;
        if(product){
            url += "&product=" + product.replace('/', '').replace('/', '');
        }

        window.location.hash = url;

        $("[data-toggle=popover]").popover();
    });
</script>
<?php require_once("../inc/footer.php"); ?>