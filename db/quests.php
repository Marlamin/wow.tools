<?php

require_once("../inc/header.php");
?>
<link href="/db/css/style.css?v=<?=filemtime("/var/www/wow.tools/db/css/style.css")?>" rel="stylesheet"><?php
if (!empty($_GET['id'])) {
    $q = $pdo->prepare("SELECT json FROM wowdata.quests WHERE id = ?");
    $q->execute([$_GET['id']]);

    $quest = json_decode($q->fetch(PDO::FETCH_ASSOC)['json'], true);
    if (empty($quest)) {
        die("Creature not found!");
    }

    echo json_encode($quest);
    die();
}
?>
<div class='container-fluid'>
    <h3>Quests</h3>
    <table class='table table-striped' id='quests'>
        <thead><tr><th style='width: 100px'>ID</th><th>Name</th><th style='width: 120px'>First seen build</th><th style='width: 120px'>Last update build</th></tr>
    </table>
    <div id="quests_preview" style="display: block;"></div>
</div>
<script type='text/javascript'>
var Elements = {};

    (function() {
var searchHash = location.hash.substr(1),
searchString = searchHash.substr(searchHash.indexOf('search=')).split('&')[0].split('=')[1];

if(searchString != undefined && searchString.length > 0){
    searchString = decodeURIComponent(searchString);
}

var page = (parseInt(searchHash.substr(searchHash.indexOf('page=')).split('&')[0].split('=')[1], 10) || 1) - 1;
var sortCol = searchHash.substr(searchHash.indexOf('sort=')).split('&')[0].split('=')[1];
if(!sortCol){
    sortCol = 0;
}

var sortDesc = searchHash.substr(searchHash.indexOf('desc=')).split('&')[0].split('=')[1];
if(!sortDesc){
    sortDesc = "asc";
}

Elements.table = $('#quests').DataTable({
    "processing": true,
    "serverSide": true,
    "search": { "search": searchString },
    "ajax": "/db/quest_api.php",
    "pageLength": 25,
    "displayStart": page * 25,
    "autoWidth": false,
    "pagingType": "input",
    "orderMulti": false,
    "order": [[sortCol, sortDesc]]
});

$('#quests').on( 'draw.dt', function () {
    var currentSearch = encodeURIComponent($("#quests_filter label input").val());
    var currentPage = $('#quests').DataTable().page() + 1;

    var sort = $('#quests').DataTable().order();
    var sortCol = sort[0][0];
    var sortDir = sort[0][1];

    var url = "search=" + currentSearch + "&page=" + currentPage + "&sort=" + sortCol +"&desc=" + sortDir;

    window.location.hash = url;

    $("[data-toggle=popover]").popover();
});

$('#quests').on('click', 'tbody tr td', function() {
    $("#quests_preview").html("Loading..");
    var data = Elements.table.row($(this).parent()).data();
    loadQuestInfo(data[0])
    .then(data => {
        renderQuestInfo(data); // JSON data parsed by `response.json()` call
    });

    $(".selected").removeClass("selected");
    $(this).parent().addClass('selected');
});

}());

async function loadQuestInfo(id){
    const response = await fetch("/db/quest_api.php?id=" + id, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    });
    return response.json();
}

function renderQuestInfo(info){
    let result = "";
    result += "<h2>" + info["LogTitle"] + "</h2>";
    if("QuestDescription" in info){
        result += "<p>" + info["QuestDescription"] + "</p>";
    }
    result += "<div id='tableContainer' style='max-height: 1080px'><table class='table table-sm table-striped table-hover' id='questInfoTable'></table></div>";

    $("#quests_preview").html(result);

    Object.keys(info).forEach(function (key) {
        const val = info[key];
        if(val != ""){
            $("#questInfoTable").append("<tr><td>" + key + "</td><td>" + val + "</td></tr>");
        }

    });
}

function locationHashChanged(event) {
    var searchHash = location.hash.substr(1),
    searchString = searchHash.substr(searchHash.indexOf('search=')).split('&')[0].split('=')[1];

    if(searchString != undefined && searchString.length > 0){
        searchString = decodeURIComponent(searchString);
    }

    if($("#quests_filter label input").val() != searchString){
        $('#quests').DataTable().search(searchString).draw(false);
    }
    var page = (parseInt(searchHash.substr(searchHash.indexOf('page=')).split('&')[0].split('=')[1], 10) || 1) - 1;
    if($('#quests').DataTable().page() != page){
        $('#quests').DataTable().page(page).draw(false);
    }

    var sortCol = searchHash.substr(searchHash.indexOf('sort=')).split('&')[0].split('=')[1];
    if(!sortCol){
        sortCol = 0;
    }

    var sortDesc = searchHash.substr(searchHash.indexOf('desc=')).split('&')[0].split('=')[1];
    if(!sortDesc){
        sortDesc = "asc";
    }

    var curSort = $('#quests').DataTable().order();
    if(sortCol != curSort[0][0] || sortDesc != curSort[0][1]){
        $('#quests').DataTable().order([sortCol, sortDesc]).draw(false);
    }
}

window.onhashchange = locationHashChanged;
</script>