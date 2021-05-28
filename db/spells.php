<?php

if (!empty($_GET['embed'])) {
    require_once("/var/www/wow.tools/inc/config.php");
} else {
    require_once("../inc/header.php");
}

if (!empty($_GET['embed']) && !empty($_GET['spellid'])) {
}

$_GET['dbc'] = "spellname";

foreach ($pdo->query("SELECT * FROM wow_dbc_tables WHERE name = 'spellname' ORDER BY name ASC") as $dbc) {
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
        <div class='col-md-8'>    
            <h3 id='spellName'></h3>
            <div id='spellInformation'>

            </div>
        </div>
    </div> 
</div>
<style type='text/css'>
tr.selected{
    background-color: #8bc34aa1 !important;
}
</style>
<script src="/files/js/files.js?v=<?=filemtime("/var/www/wow.tools/files/js/files.js")?>" crossorigin="anonymous"></script>
<script src="/dbc/js/dbc.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/dbc.js")?>"></script>
<script src="/dbc/js/enums.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/dbc.js")?>"></script>
<script src="/dbc/js/flags.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/dbc.js")?>"></script>
<script type='text/javascript'>
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    (function() {

        vars["build"] = "<?=$version['version']?>";

        // Promise.all([
        //     fetch("/dbc/api/peek/SpellXSpellVisual/?col=SpellID&val=" + id + "&build=" + vars["build"]),
        // ])
        // .then(function (responses) {
        //     return Promise.all(responses.map(function (response) {
        //         return response.json();
        //     })).catch(function (error) {
        //         console.log(error);
        //     });
        // }).then(function (data) {
        //     // do stuff
        // }).catch(function (error) {
        //     console.log("An error occurred: " + error);
        // });

        vars["dbc"] = "spellname";
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
            loadSpell(data[0], data[1]);
        });

    }());

    function loadSpell(id, name){
        document.getElementById("spellName").innerHTML = name + " <i class='fa fa-spinner fa-spin'></i>";
        document.getElementById("spellInformation").innerHTML = "";

        Promise.all([
            fetch("/dbc/api/tooltip/spell/" + id + "?build=" + vars["build"]),
            fetch("/dbc/api/peek/Spell/?col=ID&val=" + id + "&build=" + vars["build"]),
            fetch("/dbc/api/peek/SpellMisc/?col=SpellID&val=" + id + "&build=" + vars["build"]),
            fetch("/dbc/api/find/SpellEffect/?col=SpellID&val=" + id + "&build=" + vars["build"]),
            fetch("/dbc/api/peek/SpellCooldowns/?col=SpellID&val=" + id + "&build=" + vars["build"]),
            fetch("/dbc/api/peek/SpellXSpellVisual/?col=SpellID&val=" + id + "&build=" + vars["build"]),
        ])
        .then(function (responses) {
            return Promise.all(responses.map(function (response) {
                // TODO: Check if currently selected spell is still the same one we requested.
                return response.json();
            })).catch(function (error) {
                console.log(error);
            });
        }).then(function (data) {
            // Clear contents again
            /*
                                <li class="nav-item">
                        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">Contact</a>
                    </li>


                       <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">...</div>
                    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">...</div>
                    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">...</div>
                    */
            console.log(data);
            // TODO: Check if currently selected spell is still the same one we requested, return if so.

            document.getElementById("spellInformation").innerHTML = "<br><br><br><ul style='clear: both' class='nav nav-tabs' id='spellTabs'></ul><div class='tab-content' id='spellTabContent'></div>";
            loadTooltip(id, data[0]);

            const spellEntry = data[1].values;
            const spellMiscEntry = data[2].values;
            genSpellTable(spellEntry, spellMiscEntry);

            const spellEffectEntries = data[3];
            genSpellEffectsTab(spellEffectEntries);

            const spellXSpellVisualEntries = data[5].values;
            genSpellVisualsTab(spellXSpellVisualEntries);

            const spellCooldownsEntry = data[4];

            document.getElementById("spellName").innerHTML = name;
            // do stuff
        }).catch(function (error) {
            console.log("An error occurred: " + error);
        });
    }


    function genSpellVisualsTab(spellXSpellVisualEntries){
        console.log(spellXSpellVisualEntries);
        document.getElementById('spellTabs').insertAdjacentHTML("beforeend", "<li class='nav-item'><a class='nav-link' id='visuals-tab' data-toggle='tab' href='#visuals' role='tab'>Visuals/sounds</a></li>");
        let contents = "<div class='tab-pane fade show' id='visuals' role='tabpanel' aria-labelledby='visuals-tab'><table class='table table-sm table-striped' style='clear: both'>";

        contents += "</table></div>";
        document.getElementById("spellTabContent").insertAdjacentHTML("beforeend", contents);
    }

    function genSpellEffectsTab(spellEffectEntries){
        if(spellEffectEntries.length == 0)
            return;

        document.getElementById('spellTabs').insertAdjacentHTML("beforeend", "<li class='nav-item'><a class='nav-link' id='effects-tab' data-toggle='tab' href='#effects' role='tab'>Effects</a></li>");

        let contents = "<div class='tab-pane fade show' id='effects' role='tabpanel' aria-labelledby='effects-tab'><table class='table table-sm table-striped' style='clear: both'>";

        let effectIndex = 0;
        spellEffectEntries.forEach(effectEntry => {
            Object.keys(effectEntry).forEach(element => {
                if(element == "ID" || element == "SpellID" || effectEntry[element] == 0)
                    return;

                contents += "<tr><td>Effect " + effectEntry['EffectIndex'] + " " + element + "</td><td>" + effectEntry[element] + "</td></tr>";

                effectIndex++;
            });
        });
        
        contents += "</table></div>";

        document.getElementById("spellTabContent").insertAdjacentHTML("beforeend", contents);
    }

    function genSpellTable(spellEntry, spellMiscEntry){
        document.getElementById('spellTabs').insertAdjacentHTML("beforeend", "<li class='nav-item'><a class='nav-link active' id='base-tab' data-toggle='tab' href='#base' role='tab'>Base</a></li>");

        let contents = "<div class='tab-pane fade show active' id='base' role='tabpanel' aria-labelledby='base-tab'><table class='table table-sm table-striped' style='clear: both'>";

        if (spellEntry['NameSubtext_lang'] != ""){
            contents += "<tr><td>Name subtext (raw)</td><td>" + spellEntry['NameSubtext_lang'] + "</td></tr>";
        }

        if (spellEntry['Description_lang'] != ""){
            contents += "<tr><td>Description (raw)</td><td>" + spellEntry['Description_lang'] + "</td></tr>";
        }

        if (spellEntry['AuraDescription_lang'] != ""){
            contents += "<tr><td>Aura desc (raw)</td><td>" + spellEntry['AuraDescription_lang'] + "</td></tr>";
        }

        Object.keys(spellMiscEntry).forEach(element => {
            if(element == "ID" || element == "SpellID" || spellMiscEntry[element] == 0)
                return;

            if(element.substr(0, 10) == "Attributes"){
                var usedFlags = getFlagDescriptions("spellmisc", element, spellMiscEntry[element]);
                var flagTable = fancyFlagTable(usedFlags);
                contents += "<tr><td>" + element + "</td><td>" + spellMiscEntry[element] + "<br><br>" + flagTable + "</td></tr>";
            }else{
                contents += "<tr><td>" + element + "</td><td>" + spellMiscEntry[element] + "</td></tr>";
            }
        });
        
        contents += "</table></div>";

        document.getElementById("spellTabContent").insertAdjacentHTML("beforeend", contents);
    }

    function loadTooltip(id, data){
         let defaultTooltipHTML = "<div id='tooltip'><div class='tooltip-icon' style='display: none'><img src='https://wow.tools/casc/preview/chash?buildconfig=" + SiteSettings.buildConfig + "&cdnconfig=" + SiteSettings.cdnConfig + "&filename=interface%2Ficons%2Finv_misc_questionmark.blp&contenthash=45809010e72cafe336851539a9805b80'/></div><div class='tooltip-desc'>Generating tooltip..</div></div></div>";

        tooltipDiv = document.createElement("div");
        tooltipDiv.dataset.type = 'spell';
        tooltipDiv.dataset.id = id;
        tooltipDiv.innerHTML = defaultTooltipHTML;
        tooltipDiv.style.position = "relative";
        tooltipDiv.style.display = "block";
        tooltipDiv.id = "wtTooltip";
        tooltipDiv.classList.add('wt-tooltip');
        
        document.getElementById("spellInformation").insertAdjacentElement("afterBegin", tooltipDiv);
        const tooltipIcon = tooltipDiv.querySelector(".tooltip-icon img");
        const tooltipDesc = tooltipDiv.querySelector(".tooltip-desc");
        tooltipDesc.innerHTML = "<h2>" + data["name"] + "</h2>";
        if (data["description"] != null){
            tooltipDesc.innerHTML += "<p class='yellow'>" + data["description"].replace("\n", "<br><br>");
        }
        tooltipDiv.querySelector(".tooltip-icon").style.display = 'block';
        tooltipIcon.src = 'https://wow.tools/casc/preview/fdid?buildconfig=' + SiteSettings.buildConfig + '&cdnconfig=' + SiteSettings.cdnConfig + '&filename=icon.blp&filedataid=' + data["iconFileDataID"];
    }
</script>
<?php require_once("../inc/footer.php"); ?>