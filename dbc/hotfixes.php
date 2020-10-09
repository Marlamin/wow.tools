<?php

require_once(__DIR__ . "/../inc/header.php");
?>
<div class='container-fluid'>
    <table class='table' id='hotfixTable'>
        <thead>
            <tr><th>Push ID</th><th>Table name</th><th>Record ID</th><th>Build</th><th>Valid?</th><th>First seen at</th><th>&nbsp;</th></tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>
<div class="modal" id="hotfixModal" tabindex="-1" role="dialog" aria-labelledby="hotfixModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hotfixModalLabel">Hotfix diff</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Keep in mind hotfix diffs might be influenced by hotfixes that have since come out as well as not always being up-to-date (depending on <a href='https://wow.tools/uploader.php' target='_NEW'>user uploads</a>).</p>
            </div>
            <div class="modal-body" id="hotfixModalContent">
                <i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="fkModal" tabindex="-1" role="dialog" aria-labelledby="fkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fkModalLabel">Foreign key lookup</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="fkModalContent">
                <i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<link href="/dbc/css/dbc.css?v=<?=filemtime(__DIR__ . "/css/dbc.css")?>" rel="stylesheet">
<script src="/dbc/js/dbc.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/dbc.js")?>"></script>
<script src="/dbc/js/flags.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/flags.js")?>"></script>
<script src="/dbc/js/enums.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/enums.js")?>"></script>
<script src="https://wow.tools/js/diff_match_patch.js"></script>
<script type='text/javascript'>
    let vars = {};
    let parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        if(value.includes('#')){
            const splitString = value.split('#');
            vars[key] = splitString[0];
        }else{
            vars[key] = value;
        }
    });

    let currentParams = [];

    let cachedDBCHeaders = [];

    if(vars["search"] == null){
        currentParams["search"] = "";
    }else{
        currentParams["search"] = vars["search"];
    }

    var table = $('#hotfixTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "/dbc/hotfix_api.php"
        },
        "pageLength": 25,
        "displayStart": 0,
        "autoWidth": true,
        "pagingType": "input",
        "orderMulti": false,
        "ordering": false,
        "searching": true,
        "language": { "search": "Search: _INPUT_ " },
        "search": { "search": currentParams["search"] },
        "columnDefs": [
        {
            "targets": 0,
            "render": function ( data, type, full, meta ) {
                if(full[7]){
                    return "<a href='/dbc/hotfix_log.php#" + full[0] + "'>" + full[0] + " <i class='fa fa-info-circle'></i></a>";
                }else{
                    return full[0];
                }
            }
        },
        {
            "targets": 2,
            "render": function ( data, type, full, meta ) {
                if(full[1].toLowerCase() == "spellname"){
                    var build = full[3];
                    return "<a href='#' data-tooltip='spell' data-build='" + full[3] + "' data-id='" + full[2] + "' style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' data-toggle='modal' data-target='#fkModal' onclick='openFKModal(" + full[2] + ", \"" + full[1].toLowerCase() + "::ID" + "\", \"" + full[3] + "\")'>" + full[2] + "</a>";
                }else{
                    return "<a href='#' style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' data-toggle='modal' data-target='#fkModal' onclick='openFKModal(" + full[2] + ", \"" + full[1].toLowerCase() + "::ID" + "\", \"" + full[3] + "\")'>" + full[2] + "</a>";
                }
            }
        },
        {
            "targets": 4,
            "render": function ( data, type, full, meta ) {
                if(full[4] == 1){
                    return "<i class='fa fa-check'></i> Valid";
                }else{
                    return "<i class='fa fa-trash'></i> Invalidated (" + full[4] + ")";
                }
            }
        },
        {
            "targets": 6,
            "render": function ( data, type, full, meta ) {
                if(full[6]){
                    showRowDiff(full[1], full[3], full[2]);
                    return "<div class='resultHolder' id='resultHolder-" + full[1] + "-" + full[3] + "-" + full[2] +"'><i class='fa fa-refresh fa-spin' style='font-size: 12px'></i></div>";
                }else{
                    return "<i class='fa fa-ban'></i> Not available in client";
                }
            }
        }]
    });

    function getAddendum(dbc, col, value){
        let addendum = "";
        dbc = dbc.toLowerCase();
        if(enumMap.has(dbc + "." + col)){
            addendum = " (" + enumMap.get(dbc + "." + col)[value] + ")";
        }

        if(flagMap.has(dbc + "." + col)){
            let usedFlags = getFlagDescriptions(dbc, col, value).join(", ");
            addendum = " (" + usedFlags + ")";
        }

        return addendum;
    }

    function richValue(dbc, col, val, build, fk){
        let returnedValue = "";
        let displayValue = val;

        if(flagMap.has(dbc.toLowerCase() + "." + col)){
            displayValue = "0x" + Number(val).toString(16);
        }

        if (fk == "FileData::ID"){
            returnedValue = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' data-toggle='modal' data-target='#moreInfoModal' onclick='fillModal(" + val + ")'>" + val + "</a>";
        } else if (fk == "SoundEntries::ID" && parseInt(build[0]) > 6){
            returnedValue = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' onclick='openFKModal(" + val + ", \"SoundKit::ID\", \"" + build + "\")'>" + val + "</a>";
        } else if (fk == "Item::ID" && val > 0){
            returnedValue = "<a data-build='" + build + "' data-tooltip='item' data-id='" + val + "' style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' onclick='openFKModal(" + val + ", \"" + fk + "\", \"" + build + "\")'>" + val + "</a>";
        } else if (fk == "Spell::ID" || fk == "SpellName::ID" && val > 0){
            returnedValue = "<a data-build='" + build + "' data-tooltip='spell' data-id='" + val + "' style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' onclick='openFKModal(" + val + ", \"" + fk + "\", \"" + build + "\")'>" + val + "</a>";
        } else if (fk == "Creature::ID" && val > 0){
            returnedValue = "<a data-build='" + build + "' data-tooltip='creature' data-id='" + val + "' style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' onclick='openFKModal(" + val + ", \"" + fk + "\", \"" + build + "\")'>" + val + "</a>";
        } else if (fk == "QuestV2::ID" && val > 0){
            returnedValue = "<a data-build='" + build + "' data-tooltip='quest' data-id='" + val + "' style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' onclick='openFKModal(" + val + ", \"" + fk + "\", \"" + build + "\")'>" + val + "</a>";
        } else if (fk !== undefined && val > 0){
            returnedValue = "<a data-build='" + build + "' data-tooltip='fk' data-id='" + val + "' data-fk='" + fk + "'  style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' onclick='openFKModal(" + val + ", \"" + fk + "\", \"" + build + "\")'>" + val + "</a>";
        } else{
             returnedValue = displayValue;
        }

        if(enumMap.has(dbc.toLowerCase() + "." + col)){
            if(val in enumMap.get(dbc.toLowerCase() + "." + col)){
                returnedValue += " (" + enumMap.get(dbc.toLowerCase() + "." + col)[val] + ")";
            }else{
                returnedValue += " (unknown)";
            }
        }

        if(flagMap.has(dbc.toLowerCase() + "." + col)){
            let usedFlags = getFlagDescriptions(dbc.toLowerCase(), col, val);
            usedFlags.forEach(function (flag) {
                returnedValue += " (" + flag[0] + ": " + flag[1] + ")";
            });
        }

        return returnedValue;
    }

    function showRowDiff(dbc, build, recordID){
        let beforeReq = fetch("/dbc/hotfix_api.php?cacheproxy=1&dbc=" + dbc.toLowerCase() + "&build=" + build + "&col=ID&val=" + recordID + "&useHotfixes=false&calcOffset=false").then(data => data.json());
        let afterReq = fetch("/dbc/hotfix_api.php?cacheproxy=1&dbc=" + dbc.toLowerCase() + "&build=" + build + "&col=ID&val=" + recordID + "&useHotfixes=true&calcOffset=false").then(data => data.json());
        
        const cachedHeaderName = dbc + "-" + build;
        let headerPromise;
        if(cachedHeaderName in cachedDBCHeaders){
            headerPromise = new Promise(function(resolve, reject) {
                resolve(cachedDBCHeaders[cachedHeaderName]);
            });
        }else{
            headerPromise = fetch("/dbc/api/header/" + dbc.toLowerCase() + "?build=" + build, {cache: "force-cache"}).then(data => data.json());
        }

        Promise.all([headerPromise, beforeReq, afterReq])
        .then(json => {
            const header = json[0];
            const before = json[1].values;
            const after = json[2].values;

            if(!(cachedHeaderName in cachedDBCHeaders)){
                cachedDBCHeaders[cachedHeaderName] = header;
            }

            let changes = "<table class='diffTable'>";

            if(Object.keys(before).length == 0){
                Object.keys(after).forEach(function (key) {
                    const displayedValue = richValue(dbc, key, after[key], build, header.fks[key]);
                    changes += "<tr><td><i style='color: green;' class='fa fa-plus-circle'></i> <b>" + key + "</b></td><td>" + displayedValue + "</td></tr>";
                });
            } else if(Object.keys(after).length == 0){
                Object.keys(before).forEach(function (key) {
                    const displayedValue = richValue(dbc, key, before[key], build, header.fks[key]);
                    changes += "<tr><td><i style='color: red;' class='fa fa-minus-circle'></i> <b>" + key + "</b></td><td>" + displayedValue + "</td></tr>";
                });
            }else{
                Object.keys(before).forEach(function (key) {
                    if(before[key] != after[key]){
                        if (!isNaN(before[key]) && !isNaN(after[key])) {
                            let displayedValBefore = richValue(dbc, key, before[key], build, header.fks[key]);
                            let displayedValAfter = richValue(dbc, key, after[key], build, header.fks[key]);
                            changes += "<tr><td><i style='color: orange' class='fa fa-pencil-square'></i> <b>" + key + "</b></td><td>" + displayedValBefore  + " &rarr; " + displayedValAfter  + "</td></tr>";
                        } else {
                            var dmp = new diff_match_patch();
                            var dmp_diff = dmp.diff_main(before[key], after[key]);
                            dmp.diff_cleanupSemantic(dmp_diff);
                            data = dmp.diff_prettyHtml(dmp_diff);
                            changes += "<tr><td>" + key + "</td><td>" + data + "</td></tr>";
                        }
                    }
                });
            }

            changes += "</table>";

            if(changes == "<table class='diffTable'></table>"){
                changes = "No changes found (<a href='#' data-toggle='modal' data-target='#fkModal' onclick='openFKModal(" + recordID + ", \"" + dbc.toLowerCase() + "::ID" + "\", \"" + build + "\")'>view record</a>)";
            }

            var resultHolder = document.getElementById("resultHolder-" + dbc + "-" + build + "-" + recordID);
            if(resultHolder){
                resultHolder.innerHTML = changes;
            }
        });
    }
</script>
<?php
require_once(__DIR__ . "/../inc/footer.php");
?>