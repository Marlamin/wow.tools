<?php
die();
if(!empty($_GET['embed'])){
    require_once(__DIR__ . "/../inc/config.php");
}else{
    require_once(__DIR__ . "/../inc/header.php");
}

$fromBuild = getBuildConfigByBuildConfigHash($_GET['from']);
$toBuild = getBuildConfigByBuildConfigHash($_GET['to']);

if (empty($fromBuild) || empty($toBuild)) {
    die("Invalid builds!");
}

$fromBuildName = parseBuildName($fromBuild['description'])['full'];
$toBuildName = parseBuildName($toBuild['description'])['full'];

$fromCDN = getVersionByBuildConfigHash($_GET['from'])['cdnconfig']['hash'];
$toCDN = getVersionByBuildConfigHash($_GET['to'])['cdnconfig']['hash'];

?>

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/diff2html/bundles/css/diff2html.min.css" />
<script src="https://cdn.jsdelivr.net/npm/diff2html/bundles/js/diff2html.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/diff2html/bundles/js/diff2html-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.13.1/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.13.1/languages/lua.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/masonry/4.2.2/masonry.pkgd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/4.1.4/imagesloaded.pkgd.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/pagination/input.js" crossorigin="anonymous"></script>

<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.13.1/styles/github.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/diff2html/2.12.1/diff2html.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" type="text/css" href="https://wow.tools/css/style.css" />
<link rel="stylesheet" type="text/css" href="https://wow.tools/dbc/css/dbc.css" />

<script src="https://wow.tools//js/diff_match_patch.js"></script>

<style>
    :root {
        --background-color: #343a40;
        --text-color: rgba(255, 255, 255, 0.8);
        --hover-color: #fff;
        --diff-added-color: #368a23;
        --diff-added-color-odd: #4da739;
        --diff-removed-color: #9b0d0d;
    }

    .grid-item {
        border-width: 1px;
        border-color: black;
        background: lightblue;
        border-radius: 0.5em;
        padding: 0.5em;
        margin: 1em;
    }

    .grid-item>img {
        border-radius: 0.5em;
        background: white;
    }

    .d2h-files-diff {
        height: auto;
    }

    .dataTables_length {
        display: none;
    }


    .datatable_container {
        padding: 2em;
        overflow-x: auto;
    }

    .datatable_container td {
        white-space: pre !important;
    }

    .datatable_container td span.numeric {
        text-align: center;
    }

    .datatable_container td span.text {
        text-align: left;
    }

    .datatable_container tr.added.even {
        background-color: var(--diff-added-color-odd) !important;
    }
</style>

<script type="text/javascript">
    // Taken by grepping tables with string columns
    const display_tables = ["achievement", "achievement_category", "achievement_criteria", "adventurejournal", "adventuremappoi", "alliedraceracialability", "animationdata", "animationnames", "animkitboneset", "areaconditionaldata", "areamidiambiences", "areapoi", "areapoistate", "areatable", "areatrigger", "artifact", "artifactappearance", "artifactappearanceset", "attackanimtypes", "auctionhouse", "azeriteessence", "azeriteessencepower", "bannedaddons", "barbershopstyle", "battlemasterlist", "battlepetability", "battlepeteffectproperties", "battlepetnpcteammember", "battlepetspecies", "battlepetstate", "battlepetvisual", "broadcasttext", "broadcasttextsoundstate", "cameramode", "campaign", "cfg_categories", "cfg_regions", "characterserviceinfo", "charsections", "charshipmentcontainer", "chartexturevariationsv2", "chartitles", "chatchannels", "chatprofanity", "chrclasses", "chrclasstitle", "chrclassvillain", "chrcustomization", "chrraces", "chrspecialization", "chrupgradetier", "cinematic", "cinematiccamera", "cinematicsubtitle", "configurationwarning", "consolescripts", "contenttuningdescription", "contribution", "contributionstyle", "creature", "creaturedisplayinfo", "creaturedisplayinfoextra", "creaturefamily", "creaturemodeldata", "creaturetype", "criteriatree", "currencycategory", "currencycontainer", "currencytypes", "dancemoves", "declinedword", "declinedwordcases", "difficulty", "dungeonencounter", "emoteanims", "emotes", "emotestext", "emotestextdata", "exhaustion", "faction", "factiongroup", "filedata", "filedatacomplete", "filepaths", "footprinttextures", "friendshiprepreaction", "friendshipreputation", "gameobjectartkit", "gameobjectdisplayinfo", "gameobjects", "gametables", "gametips", "garrability", "garrabilitycategory", "garrbuilding", "garrclassspec", "garrclassspecplayercond", "garrencounter", "garrfamilyname", "garrfollower", "garrgivenname", "garrmechanictype", "garrmission", "garrmissiontype", "garrplot", "garrplotinstance", "garrplotuicategory", "garrspecialization", "garrstring", "garrtalent", "garrtalenttree", "globalstrings", "glyphexclusivecategory", "gmsurveyanswers", "gmsurveyquestions", "gmticketcategory", "groundeffectdoodad", "groundeffecttexture", "groupfinderactivity", "groupfinderactivitygrp", "groupfindercategory", "heirloom", "holidaydescriptions", "holidaynames", "holidays", "hotfix", "hotfixes", "invasionclientdata", "item-sparse", "itembagfamily", "itembonuslist", "itemclass", "itemdisplayinfo", "itemlimitcategory", "itemnamedescription", "itempetfood", "itempurchasegroup", "itemrandomproperties", "itemrandomsuffix", "itemsearchname", "itemset", "itemsparse", "itemsubclass", "itemsubclassmask", "itemvisualeffects", "journalencounter", "journalencountercreature", "journalencountersection", "journalinstance", "journaltier", "keystoneaffix", "languages", "languagewords", "lfgdungeongroup", "lfgdungeons", "lightskybox", "liquidtype", "loadingscreens", "locktype", "mailtemplate", "manifestinterfacedata", "manifestinterfacetocdata", "map", "mapchallengemode", "mapdifficulty", "mapdifficultyxcondition", "marketingpromotionsxlocale", "modelmanifest", "modelnametomanifest", "mount", "movie", "namegen", "namesprofanity", "namesreserved", "namesreservedlocale", "objecteffect", "objecteffectgroup", "objecteffectpackage", "objecteffectstatename", "overridespelldata", "package", "pagetextmaterial", "paperdollitemframe", "petitiontype", "petloyalty", "petpersonality", "phase", "playercondition", "powerdisplay", "powertype", "prestigelevelinfo", "pvpbrawl", "pvpscalingeffecttype", "pvpscoreboardcolumnheader", "pvpstat", "pvptalent", "pvptier", "questfeedbackeffect", "questinfo", "questline", "questobjective", "questsort", "questv2clitask", "researchbranch", "researchfield", "researchproject", "researchsite", "resistances", "scenario", "scenariostep", "scenescript", "scenescriptglobaltext", "scenescriptpackage", "scenescripttext", "screeneffect", "screenlocation", "servermessages", "skillline", "skilllinecategory", "soundbusname", "soundemitters", "soundentries", "soundentriesadvanced", "soundfilter", "soundkitname", "soundoverride", "soundproviderpreferences", "sourceinfo", "spammessages", "specializationspells", "spell", "spellauranames", "spellcategory", "spellchaineffects", "spelldescriptionvariables", "spelldispeltype", "spelleffectautodescription", "spelleffectnames", "spellflyout", "spellfocusobject", "spellicon", "spellitemenchantment", "spellkeyboundoverride", "spellmechanic", "spellmissilemotion", "spellname", "spelloverridename", "spellrange", "spellshapeshiftform", "spellvisualanimname", "spellvisualeffectname", "spellvisualkitareamodel", "spellvisualprecasttransitions", "startup_strings", "stationery", "stringlookups", "tabardbackgroundtextures", "tabardemblemtextures", "talent", "talenttab", "taxinodes", "terrainmaterial", "terraintype", "terraintypesounds", "totemcategory", "toy", "tradeskillcategory", "transmogset", "transmogsetgroup", "trophy", "uicamera", "uicameratype", "uiexpansiondisplayinfoicon", "uimap", "uimapgroupmember", "uimodelsceneactor", "uimodelscenecamera", "uisoundlookups", "uitextureatlaselement", "uitextureatlasmember", "uitexturekit", "uiwidget", "uiwidgetstringsource", "uiwidgetvistypedatareq", "uiwidgetvisualization", "unitblood", "unitpowerbar", "unittest", "unittestsparse", "vehicle", "vehicleuiindicator", "videohardware", "vignette", "virtualattachment", "waypointnode", "wbaccesscontrollist", "wbcertblacklist", "wbcertwhitelist", "wbpermissions", "weather", "wmoareatable", "worldbosslockout", "worldelapsedtimer", "worldmaparea", "worldmapoverlay", "worldsafelocs", "worldstateexpression", "worldstateui", "wowerror_strings", "zoneintromusictable", "zonelight", "zonemusic"]

    const zip = (arr1, arr2) => arr1.map((k, i) => [k, arr2[i]]);
    String.prototype.capitalize = function () {
        return this.charAt(0).toUpperCase() + this.slice(1)
    }

    const to = {
        version: "<?=$toBuildName?>",
        build: "<?=$toBuild['hash']?>",
        cdn: "<?=$toCDN?>",
        root: "<?=$toBuild['root_cdn']?>"
    }

    const from = {
        version: "<?=$fromBuildName?>",
        build: "<?=$fromBuild['hash']?>",
        cdn: "<?=$fromCDN?>",
        root: "<?=$fromBuild['root_cdn']?>"
    }

    async function fetch(from, to, action, type) {
        const promise = $.getJSON(`https://wow.tools/casc/root/diff_api?from=${from.root}&to=${to.root}`)
        return promise.then(result => {
            return result.data.filter(i => i.action == action).filter(i => i.type == type).map(i => {
                return { id: i.id, name: i.filename }
            })
        })
    }

    function parseWoWText(input) {
        if (typeof input !== 'string' || input instanceof String) {
            return input
        }

        var output = input
            .replace(/\|n/g, "\n")
            .replace(/\|H[^\|]+\|h/g, "")
            .replace(/\|[cC][0-9A-Fa-f]{8}/g, "")
            .replace(/\|[rRh]/g, "")
            .replace(/\$bullet;/g, "•")
        return output
    }

    async function render(from, to, action, type) {
        var files = await fetch(from, to, action, type)

        switch (type) {
            case "blp":
                const elements = files.map(file => getURL(file.id, to)).map(link => `<div class="grid-item"><img src="${link}"/></div>`)
                $(".grid").append(elements);
                $grid = $('.grid').masonry({
                    itemSelector: '.grid-item',
                    percentPosition: true,
                    columnWidth: 160
                });

                $grid.imagesLoaded().progress(function () {
                    $grid.masonry('layout');
                });
                break;
            case "lua":
                files = files.sort((l, r) => l.name > r.name)
                const promises = files.map(file => getDiff(file.id, from, to))
                const diffs = await Promise.all(promises)

                const uis = zip(files, diffs).map(row => {
                    const id = row[0].id
                    const filename = row[0].name
                    const diff = row[1]
                    const div = `diff_${id}`
                    var ui = new Diff2HtmlUI({ diff: diff })

                    var container = $(`<div><h1>${filename}</h1><div id="${div}"></div></div>`)

                    $("#diffs").append(container)

                    ui.draw(`#${div}`, {
                        inputFormat: 'diff',
                        showFiles: false,
                        matching: 'lines',
                        outputFormat: 'side-by-side',
                    });
                })
                break;
            case "db2":
                const names = files
                    .map(file => file.name.replace("dbfilesclient/", "").replace(".db2", ""))
                    .filter(row => display_tables.includes(row))
                    .sort()
                names.forEach(table => renderDataTable(table, from, to))
        }
    }

    function getDBCPreview(table, from, to) {
        return `https://wow.tools/dbc/diff.php?dbc=${table}&old=${from.version}&new=${to.version}&embed=1`
    }

    function getURL(fileDataID, build) {
        return `https://wow.tools/casc/preview/fdid?buildconfig=${build.build}&cdnconfig=${build.cdn}&filename=${fileDataID}.blp&filedataid=${fileDataID}`
    }

    function getDiff(fileDataID, from, to) {
        return $.get(`https://wow.tools/files/scripts/diff_api.php?from=${from.build}&to=${to.build}&filedataid=${fileDataID}&raw=0`)
    }

    function filterRows(data) {
        var shouldShow = false

        switch (data.op) {
            case "Replaced":
                const changes = data.diff.filter(change => isNaN(change.previousvalue) || isNaN(change.previousvalue))
                shouldShow = changes.length > 0
                break
            case "Added":
                const entries = Object.entries(data.row).map(i => i[1])
                const entriesWithText = entries.filter(entry => isNaN(entry))

                shouldShow = entriesWithText.length > 0
                break
            case "Removed":
                // We only care about new data
                shouldShow = false
                break
        }

        return shouldShow
    }

    function cleanupRows(data) {
        if (data.diff) {
            data.diff.forEach(diff => {
                diff.currentvalue = diff.currentvalue ? parseWoWText(diff.currentvalue) : diff.currentvalue
                diff.previousvalue = diff.previousvalue ? parseWoWText(diff.previousvalue) : diff.currentvalue
            })
        }

        Object.entries(data.row).forEach((field) => data.row[field[0]] = parseWoWText(field[1]))

        return data
    }

    function hideNonUniqueColumns(rows) {

    }

    function alignRow(data) {
        const alignmentClass = isNaN(data) ? "text" : "numeric"
        return `<span class="${alignmentClass}>${data}</span>`
    }

    async function renderDataTable(tableName, from, to) {
        var container = $(`<div class="datatable_container"><h1>${tableName}</h1><div class="loader">Loading...</div></div>`)
        $("#dbcs").append(container)

        const dataURL = `https://wow.tools/dbc/api/diff?name=${tableName}&build1=${from.version}&build2=${to.version}`
        const json = await $.getJSON(dataURL);

        const rows = json.data.filter(filterRows).map(cleanupRows)

        if (rows.length == 0) {
            container.hide()
            return
        }

        container.find(".loader").hide()

        const headersPromises = [from, to].map(build => `https://wow.tools/dbc/api/header/${tableName}/?build=${build.version}`).map($.get)
        const headers = await Promise.all(headersPromises)
        const fields = [...new Set([].concat(...headers[0].headers, ...headers[1].headers))];
        const tableHeaders = fields.map(field => `<th>${field}</th>`)

        var table = $('<table class="table table-striped table-bordered table-condensed" cellspacing="0" width="100%">')
            .attr("id", `table_${tableName}`)
        var header = $("<thead>")
        header.append($("<tr>").append(tableHeaders))

        table.append(header)

        table.DataTable({
            "data": rows,
            "pageLength": Number.MAX_SAFE_INTEGER,
            "ordering": false,
            "bFilter": false,
            "bInfo": false,
            "pagingType": "input",
            "headerCallback": function (head, data, start, end, display) {
                const headers = $(head).find('th')

                headers.each((index, header) => $(header).html($(header).text().replace("_lang", "")))
            },
            "columnDefs": [{
                "targets": "_all",
                "render":
                    /*
                    Overrides cell rendering in particular the cell's value if there is an applicable diff
                    - for Added/Removed, this applies a flat +/- diff snippet
                    - for Replaced this applies a html snippet containing diff information
                        - for numbers this is a flat '-x+y', for text diff_match_patch is used
                        */
                    function (data, type, row, meta) {

                        // grab the formatted field name
                        var field = meta.settings.aoColumns[meta.col].sTitle;

                        //! USE THIS
                        // if an array split out the field and ordinal
                        //var match = /^(.*)\[(\d+)\]$/.exec(field);
                        var match = false;

                        // assign the cell value
                        data = match ? row.row[match[1]][match[2]] : row.row[field];

                        const alignmentClass = isNaN(data) ? "text" : "numeric"
                        $(row).addClass(alignmentClass)

                        // only apply on the initial display event for replaced rows that have a diff
                        if (type !== 'display' || row.op !== "Replaced" || row.diff === null) {
                            return data;
                        }

                        // find and apply the specific diff for this field
                        // if no diff is found then return the default data value
                        var diff = row.diff.find(x => x.property == field);
                        if (!diff) {
                            return data;
                        }

                        // apply the diff html information

                        switch (diff.op) {
                            case "Added":
                                data = `<ins class="diff-added">${diff.currentvalue}</ins>`
                                break
                            case "Removed":
                                data = `<del class="diff-removed">${diff.currentvalue}</del>`;
                                break
                            case "Replaced":
                                if (!isNaN(diff.previousvalue) && !isNaN(diff.currentvalue)) {
                                    // for numbers return a fake diff to save on computation
                                    data = `<del class="diff-removed">${diff.previousvalue}</del> &rarr; <ins class="diff-added">${diff.currentvalue}</ins>`;
                                } else {
                                    // for text use diff_match_patch to compute a real diff
                                    var dmp = new diff_match_patch();
                                    var dmp_diff = dmp.diff_main(diff.previousvalue, diff.currentvalue);
                                    dmp.diff_cleanupSemantic(dmp_diff);
                                    data = dmp.diff_prettyHtml(dmp_diff);
                                }
                        }

                        return data;
                    },
                "defaultContent": ""
            }],
            "language": {
                "emptyTable": "No meaningful differences were found"
            },
            "createdRow":
                /* Overrides row rendering for Added/Removed rows */
                function (ele, row, rowIndex) {
                    if (row.op == "Added" || row.op == "Removed") {
                        $(ele).addClass(row.op.toLowerCase()); // apply the formatting class
                    }
                }
        });

        container.append(table)
    }

    $(document).ready(function () {
        render(from, to, "modified", "db2")
    });
</script>


<div class="grid"></div>

<div id="diffs"></div>
<div id="dbcs"></div>