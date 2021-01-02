<?php

require_once(__DIR__ . "/../inc/header.php");

if (!empty($_GET['limit'])) {
    $limit = (int)$_GET['limit'];
}else{
    $limit = 200;
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
echo "<thead><tr><th>Status</th><th style='width: 450px;'>Name</th><th>Build</th><th style='min-width: 190px'>First detected at</th><th style='width: 50%'>Description</th><th>&nbsp;</th></tr></thead>";
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
    foreach ($tableCounts as $tableName => $rowCount) {
        $tableDesc .= "<b>" . $tableName . "</b>" . ": <i>" . $rowCount . " record" . ($rowCount > 1 ? "s" : "") . "</i>";
        if ($tableName != array_key_last($tableCounts)) {
            $tableDesc .= "<br>";
        }
    }

    if (empty($hotfix['status'])) {
        $hotfix['status'] = "unknown";
    }

    echo "<tr>";
    echo "<td id='" . $hotfix['pushID'] . "'><span class='badge badge-" . getStatusColor($hotfix['status']) . "'>" . ucfirst($hotfix['status']) . "</span></td>";
    if (empty($hotfix['name'])) {
        echo "<td>Hotfix push " . $hotfix['pushID'] . "</td>";
    } else {
        echo "<td>" . $hotfix['name'] . " (push " . $hotfix['pushID'] . ")</td>";
    }
    echo "<td>" . $fullbuilds[$hotfix['build']] . "</td>";
    echo "<td>" . $hotfix['firstdetected'] . " CE(S)T</td>";

    echo "<td>";
    echo "<p>";
    echo $tableDesc . "<br>";
    echo "</p>";
    echo "<p>";
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
    echo "<td><a class='btn btn-primary btn-sm' target='_BLANK' href='https://wow.tools/dbc/hotfixes.php?search=pushid:" . $hotfix['pushID'] . "'>View " . $hotfix['rowCount'] . " hotfix" . ($hotfix['rowCount'] > 1 ? "es" : "") . "</a></td>";
    echo "</tr>";
}

echo "</tbody></table>";
?>
</div>
<?php
require_once(__DIR__ . "/../inc/footer.php");
?>
