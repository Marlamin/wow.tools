<?php

require_once(__DIR__ . "/../inc/header.php");

if (!empty($_GET['limit'])) {
    $limit = (int)$_GET['limit'];
}else{
    $limit = 200;
}

if(!empty($_GET['server'])){
    $showServer = true;
}else{
    $showServer = false;
}

// if(empty($_SESSION['loggedin']) || $_SESSION['rank'] < 1) {
//     die("Back in a bit!");
// }

// Handle add/edits
if(!empty($_POST) && !empty($_SESSION['loggedin']) && $_SESSION['rank'] > 0) {
    if(empty($_POST['logContributed'])){
        $_POST['logContributed'] = null;
    }

    $hotfixLogQ = $pdo->prepare("INSERT INTO wow_hotfixlogs (`pushID`, `name`, `description`, `status`, `contributedby`) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `name` = ?, `description` = ?, `status` = ?, `contributedby` = ?");
    $hotfixRes = $hotfixLogQ->execute([$_POST['logPushID'], $_POST['logName'], $_POST['logDescription'], $_POST['logStatus'], $_POST['logContributed'], $_POST['logName'], $_POST['logDescription'], $_POST['logStatus'], $_POST['logContributed']]);
}
?>
<script src="/dbc/js/dbc.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/dbc.js")?>"></script>
<script src="/dbc/js/flags.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/flags.js")?>"></script>
<script src="/dbc/js/enums.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/enums.js")?>"></script>
<div class='container-fluid'>
<p>
This page is an experiment to see if we can figure out enough information about a hotfix push to find out what exactly is being hotfixed. Sometimes they're pretty straightforward but other times not so much. <a href='https://wow.tools/uploader/' target='_BLANK'>Hotfix detecting depends on data uploaded to the site.</a><br><br>
<?php
if (isset($_GET['showAll']) && $_GET['showAll'] === "true") {
    echo "<a class='btn btn-sm btn-primary' href='/dbc/hotfix_log.php?showAll=false'>Only show documented hotfixes</a>";
    $hotfixes = $pdo->query("SELECT GROUP_CONCAT(DISTINCT(tableName)) as tables, COUNT(recordID) as rowCount, GROUP_CONCAT(tableName) as fullTables, wow_hotfixes.pushID, build, firstdetected, wow_hotfixlogs.name, wow_hotfixlogs.description, wow_hotfixlogs.status, wow_hotfixlogs.contributedby FROM wow_hotfixes LEFT JOIN wow_hotfixlogs ON wow_hotfixes.pushID=wow_hotfixlogs.pushID GROUP BY wow_hotfixes.pushID ORDER BY firstdetected DESC, wow_hotfixes.pushID DESC LIMIT 0," . $limit)->fetchAll();
} else {
    echo "<a class='btn btn-sm btn-outline-warning' href='/dbc/hotfix_log.php?showAll=true'>Show all incl. unknown hotfixes (last 200)</a>";
    $hotfixes = $pdo->query("SELECT GROUP_CONCAT(DISTINCT(tableName)) as tables, COUNT(recordID) as rowCount, GROUP_CONCAT(tableName) as fullTables, wow_hotfixes.pushID, build, firstdetected, wow_hotfixlogs.name, wow_hotfixlogs.description, wow_hotfixlogs.status, wow_hotfixlogs.contributedby FROM wow_hotfixes LEFT JOIN wow_hotfixlogs ON wow_hotfixes.pushID=wow_hotfixlogs.pushID WHERE wow_hotfixlogs.name IS NOT NULL GROUP BY wow_hotfixes.pushID ORDER BY firstdetected DESC, wow_hotfixes.pushID DESC")->fetchAll();
}
?>
</p>
<?php
$fullbuilds = $pdo->query("SELECT build, version FROM wow_builds")->fetchAll(PDO::FETCH_KEY_PAIR);

function getStatusColor($status)
{

    switch ($status) {
        case "unknown":
            return "danger";
        case "unverified":
            return "warning";
        case "verified":
            return "success";
        case "official":
            return "primary";
        default:
            return "secondary";
    }
}

echo "<table class='table table-sm'>";
echo "<thead><tr><th>Status</th><th>Push</th><th style='width: 350px;'>Name</th><th>Build</th><th style='min-width: 190px'>First detected at</th><th style='width: 50%'>Description</th><th>&nbsp;</th></tr></thead>";
echo "<tbody>";
foreach ($hotfixes as $hotfix) {
    $tableCounts = [];
    foreach (explode(",", $hotfix['fullTables']) as $table) {
        if (!isset($tableCounts[$table])) {
            $tableCounts[$table] = 1;
        } else {
            $tableCounts[$table]++;
        }
    }

    $tableDesc = "";
    arsort($tableCounts);
    foreach ($tableCounts as $tableName => $rowCount) {
        $tableDesc .= "<b>" . $tableName . "</b>" . ": <i>" . $rowCount . "x</i>";
        if ($tableName != array_key_last($tableCounts)) {
            $tableDesc .= "<br>";
        }
    }

    if (empty($hotfix['status'])) {
        $hotfix['status'] = "unknown";
    }

    echo "<tr>";
    echo "<td id='" . $hotfix['pushID'] . "'><span class='badge badge-" . getStatusColor($hotfix['status']) . "'>" . ucfirst($hotfix['status']) . "</span></td>";
    echo "<td>" . $hotfix['pushID'] . "</td>";
    if (empty($hotfix['name'])) {
        echo "<td></td>";
    } else {
        echo "<td>" . $hotfix['name'] . "</td>";
    }
    echo "<td>" . $fullbuilds[$hotfix['build']] . "</td>";
    echo "<td>" . $hotfix['firstdetected'] . " CE(S)T</td>";

    echo "<td>";
    if ($showServer) {
        echo "<div id='p" . $hotfix['pushID'] . "c' style='float: left; margin-bottom: 10px;'><h3>Client</h3>";
        echo $tableDesc . "<br>";
        echo "</div>";
        echo "<div id='p" . $hotfix['pushID'] . "s' data-pushid='" . $hotfix['pushID'] . "' class='serverHotfix' style='margin-left: 10px; margin-bottom: 10px; float: left;'><h3>Server</h3>";
        echo "</div>";
    } else {
        echo "<div id='p" . $hotfix['pushID'] . "c' style='margin-bottom: 10px;'>";
        echo $tableDesc . "<br>";
        echo "</div>";
    }
    echo "<p style='clear: both'>";
    if (!empty($hotfix['description'])) {
        if ($hotfix['contributedby'] != null) {
            echo "<b>Note added by " . getUsernameByUserID($hotfix['contributedby']) . ":</b><br>";
        } else {
            echo "<b>Note:</b><br>";
        }
        echo $hotfix['description'];
    }
    echo "</p>";
    echo "</td>";
    echo "<td>";
    if (!empty($_SESSION['loggedin']) && $_SESSION['rank'] > 0) {
        if (empty($hotfix['name'])) {
            echo "<a style='vertical-align: top;' class='btn btn-success btn-sm' onclick='loadLogForm(".$hotfix['pushID'].")' data-toggle='modal' href='' data-target='#hotfixDialogModal'><i class='fa fa-plus'></i> Add</a> ";
        }else{
            echo "<a style='vertical-align: top;' class='btn btn-warning btn-sm' onclick='loadLogForm(".$hotfix['pushID'].")' data-toggle='modal' href='' data-target='#hotfixDialogModal'><i class='fa fa-pencil'></i> Edit</a> ";
        }
    }
    echo "<a class='btn btn-primary btn-sm' target='_BLANK' href='https://wow.tools/dbc/hotfixes.php?search=pushid:" . $hotfix['pushID'] . "'>View " . $hotfix['rowCount'] . " hotfix" . ($hotfix['rowCount'] > 1 ? "es" : "") . "</a></td>";
    echo "</tr>";
}

echo "</tbody></table>";
?>
</div>
<script type='text/javascript'>
<?php if($showServer){ ?>
const showServer = true;
<?php }else{ ?>
const showServer = false;
<?php } ?>
if(showServer){
    Promise.all([
        fetch("/api.php?type=latestbuilds"),
        fetch("https://api.wow.tools/databases/")
    ])
    .then(function (responses) {
        return Promise.all(responses.map(function (response) {
            return response.json();
        })).catch(function (error) {
            console.log("An error occurred retrieving data to generate the tooltip: " + error);
        });
    }).then(function (data) {
        let latestbuilds = data[0];
        let tables = data[1];
        let tableNames = [];
        for (const table of tables){
            tableNames[table.name.toUpperCase()] = table.displayName;
        }

        let build = latestbuilds["wow"];
        let serverHotfixDivs = document.getElementsByClassName("serverHotfix");
        for(let i = 0; i < serverHotfixDivs.length; i++){
            let el = serverHotfixDivs[i];
            const pushID = el.dataset.pushid;
            fetch("/dbc/api/find/Hotfixes?col=PushID&val=" + pushID + "&build=" + build + "&useHotfixes=true")
            .then(function (response) {
                return response.json();
            }).then(function (hotfixesEntry) {
                var hotfixes = [];
                for(const hotfix of hotfixesEntry){
                    if(hotfix.Tablename in hotfixes){
                        hotfixes[hotfix.Tablename]++;
                    }else{
                        hotfixes[hotfix.Tablename] = 1;
                    }
                }

                let sortedArr = Object.keys(hotfixes).sort(function(a,b){return hotfixes[b]-hotfixes[a]});
                let tableDesc = "";

                for(let tableName of sortedArr){
                    if(tableName in tableNames){
                        prettyName = tableNames[tableName];
                    }else{
                        prettyName = "<u>" + tableName + "</u>";
                    }

                    tableDesc += "<b>" + prettyName + "</b>" + ": <i>" + hotfixes[tableName] + "x</i><br>";
                }

                if(hotfixesEntry.length == 0){
                    el.innerHTML += "N/A";
                }else{
                    el.innerHTML += tableDesc;
                }
            }).catch(function (error) {
                console.log("An error occurred retrieving data: " + error);
            });
        }

    }).catch(function (error) {
        console.log("An error occurred retrieving data: " + error);
    });

}
</script>
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
