<?php

require_once(__DIR__ . "/../inc/header.php");

// if(empty($_SESSION['loggedin']) || $_SESSION['rank'] < 1) {
//     die("Back in a bit!");
// }
?>
<div class='container-fluid'>
    <table class='table' id='hotfixTable'>
        <thead>
            <tr><th>Push ID</th><th>Table name</th><th>Record ID</th><th>Build</th><th>Valid?</th><th>First seen at <small>(CE<i>(S)</i>T)</small></th><th>&nbsp;</th></tr>
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
<?php if(!empty($_SESSION['loggedin']) && $_SESSION['rank'] > 0){ ?>
    const showHotfixButtons = true;
<?php }else{ ?>
    const showHotfixButtons = false;
<?php } ?>

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
                if(showHotfixButtons){
                    if(full[7]){
                        return "<a href='/dbc/hotfix_log.php#" + full[0] + "'>" + full[0] + " <i class='fa fa-info-circle'></i></a> <span class='badge badge-warning'><a onclick='loadLogForm(" + full[0] + ")' data-toggle='modal' href='' data-target='#hotfixDialogModal'>Edit</a></span>";
                    }else{
                        return full[0] +  " <span class='badge badge-success'><a onclick='loadLogForm(" + full[0] + ")' data-toggle='modal' href='' data-target='#hotfixDialogModal'>Add</a></span>";
                    }
                }else{
                    if(full[7]){
                        return "<a href='/dbc/hotfix_log.php#" + full[0] + "'>" + full[0] + " <i class='fa fa-info-circle'></i></a>";
                    }else{
                        return full[0];
                    }
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
                    showRowDiff(full[1], full[3], full[2], full[0]);
                    return "<div class='resultHolder' id='resultHolder-" + full[1] + "-" + full[3] + "-" + full[2] + "-" + full[0] + "'><i class='fa fa-refresh fa-spin' style='font-size: 12px'></i> Diffing..</div>";
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
            var enumRes = enumMap.get(dbc + "." + col)[value];
            if(Array.isArray(enumRes)){
                addendum = " (" + enumRes[0] + ")";
            }else{
                addendum = " (" + enumRes + ")";
            }
        }

        if(flagMap.has(dbc + "." + col)){
            let usedFlags = getFlagDescriptions(dbc, col, value).join(", ");
            addendum = " (" + usedFlags + ")";
        }

        return addendum;
    }

    function richValue(dbc, col, row, build, fk){
        let returnedValue = "";
        let val = row[col];
        let displayValue = val;

        if(flagMap.has(dbc.toLowerCase() + "." + col)){
            displayValue = "0x" + Number(val).toString(16);
        }

        if(conditionalFKs.has(dbc.toLowerCase() + "." + col)){
            let conditionalFK = conditionalFKs.get(dbc.toLowerCase() + "." + col);
            conditionalFK.forEach(function(conditionalFKEntry){
                let condition = conditionalFKEntry[0].split('=');
                let conditionTarget = condition[0].split('.');
                let conditionValue = condition[1];
                let resultTarget = conditionalFKEntry[1];
                let colTarget = Object.keys(row).indexOf(conditionTarget[1]);

                // Col target found?
                if(colTarget > -1){
                    if(row[conditionTarget[1]] == conditionValue){
                        fk = resultTarget.toLowerCase();
                    }
                }
            });
        }

        if(fk === undefined){
            returnedValue = displayValue;
        }else{
            if (fk.toLowerCase() == "filedata::id"){
                returnedValue = "<a data-toggle='modal' style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' data-toggle='modal' data-target='#moreInfoModal' onclick='fillModal(" + val + ")'>" + val + "</a>";
            } else if (fk.toLowerCase() == "soundentries::id" && parseInt(build[0]) > 6){
                returnedValue = "<a data-toggle='modal' data-target='#fkModal' style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' onclick='openFKModal(" + val + ", \"SoundKit::ID\", \"" + build + "\")'>" + val + "</a>";
            } else if (fk.toLowerCase() == "item::id" && val > 0){
                returnedValue = "<a data-toggle='modal' data-target='#fkModal' data-build='" + build + "' data-tooltip='item' data-id='" + val + "' style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' onclick='openFKModal(" + val + ", \"" + fk + "\", \"" + build + "\")'>" + val + "</a>";
            } else if (fk.toLowerCase() == "spell::id" || fk == "spellname::id" && val > 0){
                returnedValue = "<a data-toggle='modal' data-target='#fkModal' data-build='" + build + "' data-tooltip='spell' data-id='" + val + "' style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' onclick='openFKModal(" + val + ", \"" + fk + "\", \"" + build + "\")'>" + val + "</a>";
            } else if (fk.toLowerCase() == "creature::id" && val > 0){
                returnedValue = "<a data-toggle='modal' data-target='#fkModal' data-build='" + build + "' data-tooltip='creature' data-id='" + val + "' style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' onclick='openFKModal(" + val + ", \"" + fk + "\", \"" + build + "\")'>" + val + "</a>";
            } else if (fk.toLowerCase() == "questv2::id" && val > 0){
                returnedValue = "<a data-toggle='modal' data-target='#fkModal' data-build='" + build + "' data-tooltip='quest' data-id='" + val + "' style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' onclick='openFKModal(" + val + ", \"" + fk + "\", \"" + build + "\")'>" + val + "</a>";
            } else {
                returnedValue = "<a data-toggle='modal' data-target='#fkModal' data-build='" + build + "' data-tooltip='fk' data-id='" + val + "' data-fk='" + fk + "'  style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' onclick='openFKModal(" + val + ", \"" + fk + "\", \"" + build + "\")'>" + val + "</a>";
            }
        }

        if(enumMap.has(dbc.toLowerCase() + "." + col)){
            var enumRes = enumMap.get(dbc.toLowerCase() + "." + col);
            if(val in enumRes){
                if(Array.isArray(enumRes[val])){
                    returnedValue += " (" + enumRes[val][1] + ")";
                }else{
                    returnedValue += " (" + enumRes[val] + ")";
                }
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

        if(dateFields.includes(dbc.toLowerCase() + "." + col)){
            returnedValue += " (" + parseDate(val) + ")";
        }

        return returnedValue;
    }

    function handleHotfixError(dbc, build, recordID, pushID, error){
        console.log("HandleError");
        console.log(dbc, build, recordID, pushID, error);
        var resultHolder = document.getElementById("resultHolder-" + dbc + "-" + build + "-" + recordID + "-" + pushID);
        if(resultHolder){
            resultHolder.innerHTML = "<i class='fa fa-exclamation-triangle' style='font-size: 12px'></i> Error generating diff, backend might be overloaded. Try again later!";
        }
    }

    function showRowDiff(dbc, build, recordID, pushID){
        let beforeReq = fetch("/dbc/hotfix_api.php?cacheproxy=1&dbc=" + dbc.toLowerCase() + "&build=" + build + "&col=ID&val=" + recordID + "&useHotfixes=false").then(data => data.json());
        let afterReq = fetch("/dbc/hotfix_api.php?cacheproxy=1&dbc=" + dbc.toLowerCase() + "&build=" + build + "&col=ID&val=" + recordID + "&useHotfixes=true&pushID=" + pushID).then(data => data.json()).catch(error => handleHotfixError(dbc, build, recordID, pushID, error));
        
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
                    const displayedValue = richValue(dbc, key, after, build, header.fks[key]);
                    changes += "<tr><td><i style='color: green;' class='fa fa-plus-circle'></i> <b>" + key + "</b></td><td>" + displayedValue + "</td></tr>";
                });
            } else if(Object.keys(after).length == 0){
                Object.keys(before).forEach(function (key) {
                    const displayedValue = richValue(dbc, key, before, build, header.fks[key]);
                    changes += "<tr><td><i style='color: red;' class='fa fa-minus-circle'></i> <b>" + key + "</b></td><td>" + displayedValue + "</td></tr>";
                });
            }else{
                Object.keys(before).forEach(function (key) {
                    if(before[key] != after[key]){
                        if (!isNaN(before[key]) && !isNaN(after[key])) {
                            if(flagMap.has(dbc.toLowerCase() + "." + key)){
                                // flag specific diffing
                                changes += "<tr><td style='min-width: 140px;'><i style='color: orange' class='fa fa-pencil-square'></i> <b>" + key + "</b></td><td>";
                                
                                changes += "0x" + Number(before[key]).toString(16);
                                
                                changes += " &rarr; ";
                                
                                changes += "0x" + Number(after[key]).toString(16) + " (";

                                let usedFlagsBefore = getFlagDescriptions(dbc.toLowerCase(), key, before[key]);
                                let usedFlagsAfter = getFlagDescriptions(dbc.toLowerCase(), key, after[key]);

                                let allFlags = [];
                                let usedFlagNumsBefore = [];
                                usedFlagsBefore.forEach(function (beforeFlag) {
                                    usedFlagNumsBefore.push(beforeFlag[0]);
                                    allFlags.push(beforeFlag);
                                });

                                let usedFlagNumsAfter = [];
                                usedFlagsAfter.forEach(function (afterFlag) {
                                    usedFlagNumsAfter.push(afterFlag[0]);
                                    allFlags.push(afterFlag);
                                });

                                let seenFlags = [];
                                allFlags.forEach(function (flag) {
                                    if(!usedFlagNumsAfter.includes(flag[0])){
                                        if(!seenFlags.includes(flag[0])){
                                            if(flag[1] != ""){
                                                changes += "<span class='diff-removed'>" + flag[0] + ": " + flag[1] + "</span> "; 
                                            }else{
                                                changes += "<span class='diff-removed'>" + flag[0] + "</span> "; 
                                            }

                                            seenFlags.push(flag[0]);
                                        }
                                    } else if(!usedFlagNumsBefore.includes(flag[0])){
                                        if(!seenFlags.includes(flag[0])){
                                            if(flag[1] != ""){
                                                changes += "<span class='diff-added'>" + flag[0] + ": " + flag[1] + "</span> "; 
                                            }else{
                                                changes += "<span class='diff-added'>" + flag[0] + "</span> "; 
                                            } 

                                            seenFlags.push(flag[0]);
                                        }
                                    }else{
                                        if(!seenFlags.includes(flag[0])){
                                            if(flag[1] != ""){
                                                changes += "<span class=''>" + flag[0] + ": " + flag[1] + "</span> "; 
                                            }else{
                                                changes += "<span class=''>" + flag[0] + "</span> "; 
                                            }

                                            seenFlags.push(flag[0]);
                                        }
                                    }
                                });

                                changes += ")</td></tr>";
                            }else{
                                let displayedValBefore = richValue(dbc, key, before, build, header.fks[key]);
                                let displayedValAfter = richValue(dbc, key, after, build, header.fks[key]);
                                changes += "<tr><td><i style='color: orange' class='fa fa-pencil-square'></i> <b>" + key + "</b></td><td>" + displayedValBefore  + " &rarr; " + displayedValAfter  + "</td></tr>";
                            }
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

            var resultHolder = document.getElementById("resultHolder-" + dbc + "-" + build + "-" + recordID + "-" + pushID);
            if(resultHolder){
                resultHolder.innerHTML = changes;
            }
        });
    }

    function fillModal(fileDataID){
        $( "#moreInfoModalContent" ).load( "/files/scripts/filedata_api.php?filedataid=" + fileDataID );
    }

    function fillPreviewModal(buildconfig, filedataid){
        $( "#previewModalContent" ).load( "/files/scripts/preview_api.php?buildconfig=" + buildconfig + "&filedataid=" + filedataid);
    }
</script>
<div class="modal" id="moreInfoModal" tabindex="-1" role="dialog" aria-labelledby="moreInfoModalLabel"
    aria-hidden="true">
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
            <div class="modal-body" id="previewModalContent">
                <i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="hotfixDialogModal" tabindex="-1" role="dialog" aria-labelledby="hotfixDialogLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hotfixDialogLabel">Add/edit hotfix log entry</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="hotfixDialogContent">
                <form method="POST" action="/dbc/hotfix_log.php?showAll=true">
                <div class="form-group">
                    <label for="logPushID">PushID</label>
                    <input type="number" class="form-control" name="logPushID" id="logPushID" READONLY>
                </div>
                <div class="form-group">
                    <label for="logName">Name</label>
                    <input type="text" class="form-control" name="logName" id="logName" placeholder="As short as possible while still being clear." maxlength="255" REQUIRED>
                </div>
                <div class="form-group">
                    <label for="logDescription">Description (optional)</label>
                    <textarea class="form-control" name="logDescription" id="logDescription" rows="10"></textarea>
                </div>
                <div class="form-group">
                    <label for="logStatus">Status</label>
                    <select class='form-control' id="logStatus" name="logStatus">
                        <option value='unknown'>Unknown</option>
                        <option value='unverified'>Unverified</option>
                        <option value='verified'>Verified</option>
                        <option value='official'>Official</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="logContributed">UserID as note author (optional, your user ID is <?php if(!empty($_SESSION['userid'])){ echo $_SESSION['userid']; } else { echo "unknown"; }?>)</label>
                    <input type="number" class="form-control" name="logContributed" id="logContributed">
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php
require_once(__DIR__ . "/../inc/footer.php");
?>