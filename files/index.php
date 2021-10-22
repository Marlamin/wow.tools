<?php

require_once("../inc/header.php");
$buildq = $pdo->prepare("SELECT hash, description FROM `wow_buildconfig` WHERE product = ? ORDER BY id DESC LIMIT 1;");
$lfproducts = array("wow", "wowt", "wow_classic", "wow_classic_era", "wow_classic_era_ptr");
$lfbuilds = [];
foreach ($lfproducts as $lfproduct) {
    $buildq->execute([$lfproduct]);
    $lfbuilds[$lfproduct] = $buildq->fetch(PDO::FETCH_ASSOC);
}

?><link href="/files/css/files.css?v=<?=filemtime("/var/www/wow.tools/files/css/files.css")?>" rel="stylesheet">
<div class="container-fluid" id='files_container'>
    <div id='files_buttons' class='notree'>
        <a href='#' class='btn btn-primary btn-sm' data-toggle='modal' data-target='#settingsModal'><i class='fa fa-gear'></i> Settings</a>
        <a href='/files/submitFiles.php' class='btn btn-success btn-sm' data-trigger='hover' data-placement='bottom' data-container='body' data-toggle='popover' data-content='Submit suggestions for filenames'><i class='fa fa-upload'></i> Suggest names</a>
        <div class="btn-group">
            <a href='/casc/listfile/download/csv/unverified' class='btn btn-primary btn-sm'><i class='fa fa-download'></i> Listfile</a>
            <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div class="dropdown-menu">
                <a target='_BLANK' class="dropdown-item" href="/casc/listfile/download">TXT (Blizzard filenames only)</a>
                <a target='_BLANK' class="dropdown-item" href="/casc/listfile/download/csv">CSV (Blizzard filenames only)</a>
                <a target='_BLANK' class="dropdown-item" href="/casc/listfile/download/csv/unknown">CSV (FileDataIDs with no filenames)</a>
                <a target='_BLANK' class="dropdown-item" href="/casc/listfile/download/csv/unverified">Community CSV (all filenames, incl. guessed ones)</a>
                <?php foreach ($lfbuilds as $lfproduct => $lfbuild) {?>
                    <a target='_BLANK' class="dropdown-item" href="/casc/listfile/download/csv/build?buildConfig=<?=$lfbuild['hash']?>">Community CSV for <?=$lfproduct?> <?=prettyBuild($lfbuild['description'])?></a>
                <?php } ?>
            </div>
        </div>
        <a href='#' id='buildFilterButton' class='btn btn-info btn-sm' data-toggle='modal' data-target='#buildModal'><i class='fa fa-filter'></i> Filter build</a>
        <a href='#' id='clearBuildFilterButton' class='btn btn-danger btn-sm' style='display: none' data-toggle='modal' onClick='buildFilterClick()'>Clear filter</a>
        <a href='#' id='multipleFileDLButton' target='_BLANK' class='btn btn-warning btn-sm' style='display: none'>Download selected files (1)</a>
        <a href='#' id='multipleFileAddAll' class='btn btn-info btn-sm' style='display: none'>Add all files on page</a>
        <a href='#' id='multipleFileResetButton' class='btn btn-danger btn-sm' style='display: none'>Reset queue</a>
    </div>
    <div id='files_treeFilter' style='display: none'>
        <input type='text' id='treeFilter' oninput='treeFilterChange(this)'>
    </div>
    <div id='files_tree' style='display: none'><div id='tree'></div></div>
    <div id='files_treetoggle' class='collapsed' onClick='toggleTree()'>&gt;</div>
    <table id='files' class="table table-striped table-bordered table-condensed" cellspacing="0" style='margin: auto; ' width="100%">
        <thead>
            <tr>
                <th style='width: 50px;'>FD ID</th>
                <th>Filename</th>
                <th style='width: 100px;'>Lookup</th>
                <th style='width: 200px;'>Versions</th>
                <th style='width: 50px;'>Type</th>
                <th style='width: 20px;'>&nbsp;</th><th style='width: 20px;'>&nbsp;</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    <div id='files_preview'>Click on the <i class='fa fa-eye'></i> icon to preview a file.</div>
</div>
<div class="modal" id="moreInfoModal" tabindex="-1" role="dialog" aria-labelledby="moreInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
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
<div class="modal" id="chashModal" tabindex="-1" role="dialog" aria-labelledby="chashModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chashModalLabel">Content hash lookup</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="chashModalContent">
                <i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="helpModalLabel">Help</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="helpModalContent">
                <kbd>%</kbd> for wildcard<br>
                <kbd>^</kbd> string must start with<br>
                <kbd>type:type</kbd> for filtering by type<br>
                <kbd>chash:md5</kbd> for filter by contenthash<br>
                <kbd>unnamed</kbd> for files without filenames<br>
                <kbd>communitynames</kbd> for files with community filenames<br>
                <kbd>encrypted</kbd> for encrypted files<br>
                <kbd>encrypted:KEY</kbd> for encrypted by key<br>
                <kbd>skit:soundkitid</kbd> for searching by SoundKitID<br>
                <kbd>range:start-end</kbd> search within a specific set of FileDataIDs<br>
                <kbd>vo:searchterm</kbd> show sound files in which this dialogue appears<br>
                <p>It is also possible to combine some of these options (but not others, it will complain) by separating them by a <kbd>,</kbd>. <br>Examples: <kbd>unnamed,type:wmo</kbd> <kbd>encrypted,creature,type:m2</kbd></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="buildModal" tabindex="-1" role="dialog" aria-labelledby="buildModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buildModalLabel">Filter by build</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="buildModalContent">
                <select id='buildFilter' style='width: 100%'>
                <?php foreach ($pdo->query("SELECT description, root_cdn FROM wow_buildconfig ORDER BY wow_buildconfig.description DESC") as $build) { ?>
                    <option value='<?=$build['root_cdn']?>'><?=prettyBuild($build['description'])?></option>
                <?php } ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal" onClick="buildFilterClick()">Select Build</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="settingsModal" tabindex="-1" role="dialog" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="settingsModalLabel">Settings</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id='settingsForm'>
                    <input type='checkbox' id='showFileLookup' name='settings[showFileLookup]'> <label for='showFileLookup'>Show lookup column (requires reload)</label><br>
                    <input type='checkbox' id='showFileType' name='settings[showFileType]'> <label for='showFileType'>Show type column (requires reload)</label><br>
                    <input type='checkbox' id='showFileBranch' name='settings[showFileBranch]'> <label for='showFileBranch'>Show branch in versions (requires reload)</label><br>
                    <input type='checkbox' id='showFileTree' name='settings[showFileTree]'> <label for='showFileTree'>Show file tree (experimental)</label><br>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveSettings();" data-dismiss="modal">Save</button>
            </div>
        </div>
    </div>
</div>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>
<script src="/files/js/files.js?v=<?=filemtime("/var/www/wow.tools/files/js/files.js")?>"></script>
<script type='text/javascript'>
    var Settings =
    {
        showFileLookup: false,
        showFileType: true,
        showFileBranch: true,
        showFileTree: false
    }

    function loadSettings(){
        /* Show/hide file lookup column */
        var showFileLookup = localStorage.getItem('settings[showFileLookup]');
        if (showFileLookup){
            if (showFileLookup== "1"){
                Settings.showFileLookup = true;
            } else {
                Settings.showFileLookup = false;
            }
        }

        document.getElementById("showFileLookup").checked = Settings.showFileLookup;

        /* Show/hide file type column */
        var showFileType = localStorage.getItem('settings[showFileType]');
        if (showFileType){
            if (showFileType== "1"){
                Settings.showFileType = true;
            } else {
                Settings.showFileType = false;
            }
        }

        document.getElementById("showFileType").checked = Settings.showFileType;

        /* Show/hide file branch in versions */
        var showFileBranch = localStorage.getItem('settings[showFileBranch]');
        if (showFileBranch){
            if (showFileBranch== "1"){
                Settings.showFileBranch = true;
            } else {
                Settings.showFileBranch = false;
            }
        }

        document.getElementById("showFileBranch").checked = Settings.showFileBranch;

        /* Show/hide file tree */
        var showFileTree = localStorage.getItem('settings[showFileTree]');
        if (showFileTree){
            if (showFileTree== "1"){
                Settings.showFileTree = true;
            } else {
                Settings.showFileTree = false;
            }
        }

        document.getElementById("showFileTree").checked = Settings.showFileTree;

        if(Settings.showFileTree){
            document.getElementById("files_treetoggle").classList.remove("hidden");
        }else{
            document.getElementById("files_treetoggle").classList.add("hidden");
            toggleTree(true);
        }
    }

    function saveSettings(){
        if (document.getElementById("showFileLookup").checked){
            localStorage.setItem('settings[showFileLookup]', '1');
        } else {
            localStorage.setItem('settings[showFileLookup]', '0');
        }

        if (document.getElementById("showFileType").checked){
            localStorage.setItem('settings[showFileType]', '1');
        } else {
            localStorage.setItem('settings[showFileType]', '0');
        }

        if (document.getElementById("showFileBranch").checked){
            localStorage.setItem('settings[showFileBranch]', '1');
        } else {
            localStorage.setItem('settings[showFileBranch]', '0');
        }

        if (document.getElementById("showFileTree").checked){
            localStorage.setItem('settings[showFileTree]', '1');
        } else {
            localStorage.setItem('settings[showFileTree]', '0');
        }

        loadSettings();
    }

    (function() {
        loadSettings();

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

        var build = searchHash.substr(searchHash.indexOf('build=')).split('&')[0].split('=')[1];

        var previewTypes = ["ogg", "mp3", "blp", "wmo", "m2"];

        var table = $('#files').DataTable({
            "processing": true,
            "serverSide": true,
            "search": { "search": searchString },
            "ajax": {
                url: 'scripts/api.php',
                type: 'GET',
                beforeSend: function() {
                    if (table && table.hasOwnProperty('settings')) {
                        //table.settings()[0].jqXHR.abort();
                    }
                }
            },
            "pageLength": 25,
            "language": { 
                "search": "Search: _INPUT_ <a style='margin-top: -5px;' class='btn btn-outline-primary btn-sm' href='#' data-toggle='modal' data-target='#helpModal'><i class='fa fa-question'></i></a> <a role='button' style='margin-top: -5px;' id='togglePreviewWindow' onClick='togglePreviewPane()' class='btn btn-danger btn-sm' style='color: white' data-trigger='hover' data-container='body' data-toggle='popover' data-content='Click this to toggle between showing previews on the right of the table, or in a separate popup.'><i class='fa fa-columns'></i></a>",
                "info": "Showing _START_ to _END_ of _TOTAL_ files (<a href='/files/stats.php'>stats</a>)",
            },
            "displayStart": page * 25,
            "autoWidth": false,
            "pagingType": "input",
            "orderMulti": false,
            "order": [[sortCol, sortDesc]],
            "columnDefs": [
            {
                "targets": 1,
                "createdCell": function (td, cellData, rowData, row, col) {
                    if (!cellData) {
                        if(!rowData[7]){
                            $(td).css('background-color', '#ff585850');
                        }else{
                            $(td).css('background-color', '#673ab750');
                        }
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

                    if(full[6]){
                        test += "<span style='float: right'><a tabindex='0' role='button' data-trigger='hover' data-container='body' data-html='true' data-toggle='popover' style='color: ;' data-content='";

                        full[6].forEach(function(comment) {
                            test += "By <b>" + comment['username'] +"</b> on <b>" + comment['lastedited'] +"</b><br>";
                            test += comment['comment'] + "<br>";
                        });

                        test += "'><i class='fa fa-comment'></i></a></span>";
                    }

                    if(full[5]){
                        if(full[5]['soundkit']){
                            if(test == ""){
                                test = full[5]['soundkit'].split("<br>").join("");
                            }
                            test += "<span style='float: right'><a tabindex='0' role='button' data-trigger='hover' data-container='body' data-html='true' data-toggle='popover' style='color: ;' data-content='" + full[5]['soundkit'] +"'><i class='fa fa-music'></i>&nbsp;</a></span>";
                        }
                        if(full[5]['cmd']){
                            test += "<span style='float: right'><a tabindex='0' role='button' data-trigger='hover' data-container='body' data-html='true' data-toggle='popover' style='color: ;' data-content='" + full[5]['cmd'] +"'><i class='fa fa-bug'></i>&nbsp;</a></span>";
                        }
                        if(full[5]['tfd']){
                            test += "<span style='float: right'><a tabindex='0' role='button' data-trigger='hover' data-container='body' data-html='true' data-toggle='popover' style='color: ;' data-content='" + full[5]['tfd'] +"'><i class='fa fa-picture-o'></i>&nbsp;</a></span>";
                        }
                        if(full[5]['mfd']){
                            test += "<span style='float: right'><a tabindex='0' role='button' data-trigger='hover' data-container='body' data-html='true' data-toggle='popover' style='color: ;' data-content='" + full[5]['mfd'] +"'><i class='fa fa-cube'></i>&nbsp;</a></span>";
                        }
                    }

                    return test;
                }
            },
            {
                "targets": 2,
                "orderable": true,
                "visible": Settings.showFileLookup,
                "render": function ( data, type, full, meta ) {
                    return "<span style='font-family: monospace; text-overflow: ellipsis;'>" + full[2] + "</span>";
                }
            },
            {
                "targets": 3,
                "orderable": false,
                "render": function ( data, type, full, meta ) {
                    if(full[3].length > 0){
                        if(full[3][0].enc == 1){
                            var test = "<i style='color: red' title='File is encrypted (key " + full[3][0].key + " unknown)' class='fa fa-lock'></i> ";
                        }else if(full[3][0].enc == 2){
                            var test = "<i style='color: green' title='File is encrypted (key " + full[3][0].key + " is available)' class='fa fa-unlock'></i> ";
                        }else if(full[3][0].enc == 3){
                            var test = "<i style='color: yellow' title='File is encrypted (keys " + full[3][0].key + " are partially available)' class='fa fa-lock'></i> ";
                        }else{
                            var test = "";
                        }
                    }else{
                        var test = "";
                    }

                    if(full[3].length > 1){
                        test += "<a data-toggle='collapse' href='#versions"  + full[0] + "'>> Show " + full[3].length + " versions</a><div class='collapse' id='versions" + full[0] + "'>";
                        full[3].forEach(function(entry) {
                            if(full[1]){
                                var filename = full[1].replace(/^.*[\\\/]/, '');
                            }else{
                                if(full[7]){
                                    var filename = full[7].replace(/^.*[\\\/]/, '');
                                }else{
                                    var filename = full[0] + "." + full[4];
                                }
                            }
                            test += "<a class='fileTableDL' href='https://wow.tools/casc/file/chash?contenthash=" + entry.contenthash + "&filedataid=" + full[0] + "&buildconfig=" + entry.buildconfig + "&cdnconfig=" + entry.cdnconfig + "&filename=" + encodeURIComponent(filename) + "'>" + entry.description;
                            
                            if(Settings.showFileBranch){
                                test += " (" + entry.branch + ")";
                            }
                            
                            test += "</a>";

                            if(entry.firstseen && entry.description == "WOW-18125patch6.0.1_Beta" && entry.firstseen != "WOW-18125patch6.0.1_Beta"){
                                test += "<span style='float: right'><a tabindex='0' role='button' data-trigger='hover' data-container='body' data-html='true' data-toggle='popover' data-placement='top' style='color: ;' data-content='<b>(WIP, more builds coming)</b> First seen in " + entry.firstseen + "'><i class='fa fa-archive'></i></a></span>";
                            }

                            test += "<br>";
                        });

                        test += "</div>";
                    }else if(full[3].length == 1 && full[3][0].buildconfig != null){
                        if(full[1]){
                            var filename = full[1].replace(/^.*[\\\/]/, '');
                        }else{
                            if(full[7]){
                                var filename = full[7].replace(/^.*[\\\/]/, '');
                            }else{
                                var filename = full[0] + "." + full[4];
                            }
                        }
                        test += "<a class='fileTableDL' href='https://wow.tools/casc/file/chash?contenthash=" + full[3][0].contenthash + "&filedataid=" + full[0] + "&buildconfig=" + full[3][0].buildconfig + "&cdnconfig=" + full[3][0].cdnconfig + "&filename=" + encodeURIComponent(filename) + "'>" + full[3][0].description;
                        if(Settings.showFileBranch){
                            test += " (" + full[3][0].branch + ")";
                        }
                        test += "</a>";

                        if(full[3][0].contenthash == "de6135861a6cacfe176830f18f597c3e"){
                            test += "<span style='float: right'><a tabindex='0' role='button' data-trigger='hover' data-container='body' data-html='true' data-toggle='popover' data-placement='top' style='color: ;' data-content='<b>Placeholder audio</b><br> This file has no audio yet'><span class='fa-stack'><i class='fa fa-volume-off fa-stack-1x'></i><i class='fa fa-ban fa-stack-1x text-danger'></i></span></i></a></span>";
                        }

                        if(full[3][0].firstseen && full[3][0].firstseen != "WOW-18125patch6.0.1_Beta"){
                            test += "<span style='float: right'><a tabindex='0' role='button' data-trigger='hover' data-container='body' data-html='true' data-toggle='popover' data-placement='top' style='color: ;' data-content='<b>(WIP, more builds coming)</b> First seen in " + full[3][0].firstseen + "'><i class='fa fa-archive'></i></a></span>";
                        }
                    }else{
                        test += "No versions available";
                    }

                    return test;
                }
            },
            {
                "targets": 4,
                "orderable": false,
                "visible": Settings.showFileType
            },
            {
                "targets": 5,
                "orderable": false,
                "render": function ( data, type, full, meta ) {
                    if(full[3].length && full[3].length > 0){
                        var test = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer' data-toggle='modal' data-target='#moreInfoModal' onClick='fillModal(" + full[0] + ")'><i class='fa fa-info-circle'></i></a></td>";
                    }else{
                        var test = "N/A";
                    }
                    return test;
                }
            },
            {
                "targets": 6,
                "orderable": false,
                "render": function ( data, type, full, meta ) {
                    if(full[4] == "db2" && (full[1] || full[7])){
                        let filename = "";
                        if(full[1]){
                            filename = full[1];
                        }else{
                            if(full[7]){
                                filename = full[7];
                            }
                        }
                        var db2name = filename.replace("dbfilesclient/", "").replace(".db2", "");
                        var test = "<a href='//wow.tools/dbc/?dbc=" + db2name + "' target='_BLANK'><i class='fa fa-table'></i></a>";
                    }else{
                        if(full[3].length && full[3][0].enc != 1){
                            if($("#files_preview").is(":visible")){
                                var test = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer' onClick='fillPreviewModalByContenthash(\"" + full[3][0].buildconfig + "\",\"" + full[0] + "\",\"" + full[3][0].contenthash + "\")'><i class='fa fa-eye'></i></a></td>";
                            }else{
                                var test = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer' data-toggle='modal' data-target='#previewModal' onClick='fillPreviewModalByContenthash(\"" + full[3][0].buildconfig + "\",\"" + full[0] + "\",\"" + full[3][0].contenthash + "\")'><i class='fa fa-eye'></i></a></td>";
                            }
                        }else{
                            var test = "<i class='fa fa-ban' style='opacity: 0.3'></i></td>";
                        }
                    }
                    return test;
                }
            }
            ]
        });

$("#files").on( 'draw.dt', function () {
    var currentSearch = encodeURIComponent($("#files_filter label input").val());
    var currentPage = $('#files').DataTable().page() + 1;

    var sort = $('#files').DataTable().order();
    var sortCol = sort[0][0];
    var sortDir = sort[0][1];

    var build = $('#files').DataTable().ajax.url().split("buildConfig=")[1];

    var url = "search=" + currentSearch + "&page=" + currentPage + "&sort=" + sortCol +"&desc=" + sortDir;
    if(build){
        url += "&build=" + build;
    }

    window.location.hash = url;

    $('.popover').remove();

    $("[data-toggle=popover]").popover();
});

$("#buildFilter").select2();
}());

function locationHashChanged(event) {
    var searchHash = location.hash.substr(1),
    searchString = searchHash.substr(searchHash.indexOf('search=')).split('&')[0].split('=')[1];

    if(searchString != undefined && searchString.length > 0){
        searchString = decodeURIComponent(searchString);
    }

    if($("#files_filter label input").val() != searchString){
        console.log("Setting search to " + searchString);
        //$("#files_filter label input").val(searchString); // This causes issues where search field is overwritten while typing
        $('#files').DataTable().search(searchString).draw(false);
    }
    var page = (parseInt(searchHash.substr(searchHash.indexOf('page=')).split('&')[0].split('=')[1], 10) || 1) - 1;
    if($('#files').DataTable().page() != page){
        console.log("Setting page to " + page);
        $('#files').DataTable().page(page).draw(false);
    }

    var sortCol = searchHash.substr(searchHash.indexOf('sort=')).split('&')[0].split('=')[1];
    if(!sortCol){
        sortCol = 0;
    }

    var sortDesc = searchHash.substr(searchHash.indexOf('desc=')).split('&')[0].split('=')[1];
    if(!sortDesc){
        sortDesc = "asc";
    }

    var curSort = $('#files').DataTable().order();
    if(sortCol != curSort[0][0] || sortDesc != curSort[0][1]){
        console.log("Setting sort to " + sortCol + ", " + sortDesc);
        $('#files').DataTable().order([sortCol, sortDesc]).draw(false);
    }
}

window.onhashchange = locationHashChanged;

<?php if (!empty($_SESSION['buildfilterid'])) { ?>
var rootFiltering = true;
<?php } else { ?>
var rootFiltering = false;
<?php } ?>
updateBuildFilterButton();
</script>
<?php require_once("../inc/footer.php"); ?>