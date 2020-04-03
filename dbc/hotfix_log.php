<?php
require_once(__DIR__ . "/../inc/header.php");
?>
<div class='container-fluid'>
<p>
This page is an experiment to see if we can figure out enough information about a hotfix push to find out what exactly is being hotfixed. Sometimes they're pretty straightforward but other times not so much.<br><br>
<?php
if($_GET['showAll'] && $_GET['showAll'] == "true"){
    echo "<a class='btn btn-sm btn-primary' href='/dbc/hotfix_log.php'>Only show known hotfixes</a>";
    $hotfixes = $pdo->query("SELECT GROUP_CONCAT(DISTINCT(tableName)) as tables, COUNT(recordID) as rowCount, GROUP_CONCAT(tableName) as fullTables, wow_hotfixes.pushID, firstdetected, wow_hotfixlogs.name, wow_hotfixlogs.description FROM wow_hotfixes LEFT JOIN wow_hotfixlogs ON wow_hotfixes.pushID=wow_hotfixlogs.pushID GROUP BY wow_hotfixes.pushID ORDER BY firstdetected DESC, wow_hotfixes.pushID DESC LIMIT 0,20")->fetchAll();
}else{
    echo "<a class='btn btn-sm btn-outline-warning' href='/dbc/hotfix_log.php?showAll=true'>Show unknown hotfixes</a>";
    $hotfixes = $pdo->query("SELECT GROUP_CONCAT(DISTINCT(tableName)) as tables, COUNT(recordID) as rowCount, GROUP_CONCAT(tableName) as fullTables, wow_hotfixes.pushID, firstdetected, wow_hotfixlogs.name, wow_hotfixlogs.description FROM wow_hotfixes LEFT JOIN wow_hotfixlogs ON wow_hotfixes.pushID=wow_hotfixlogs.pushID WHERE wow_hotfixlogs.name IS NOT NULL GROUP BY wow_hotfixes.pushID ORDER BY firstdetected DESC, wow_hotfixes.pushID DESC")->fetchAll();
}
?>
</p>
<?php
foreach($hotfixes as $hotfix){
    if(empty($hotfix['name'])){
        $hotfix['name'] = "unknown";
    }

    $tableCounts = [];
    foreach(explode(",", $hotfix['fullTables']) as $table){
        if(!isset($tableCounts[$table])){
            $tableCounts[$table] = 1;
        }else{
            $tableCounts[$table]++;
        }
    }

    $tableDesc = "";
    foreach($tableCounts as $tableName => $rowCount){
        $tableDesc .= $tableName.": <i>".$rowCount." row(s)</i>";
        if($tableName != array_key_last($tableCounts)){
            $tableDesc .= "<br>";
        }
    }
    echo "<hr><h3>Hotfix push " . $hotfix['pushID']." (".$hotfix['name'].") <a class='btn btn-outline-primary btn-sm' target='_BLANK' href='https://wow.tools/dbc/hotfixes.php?search=pushid:".$hotfix['pushID']."'>View ".$hotfix['rowCount']." hotfix(es)</a></h3>";
    echo "<span class='text-muted'>First detected at " . $hotfix['firstdetected']." CE(S)T</span><br>";
    echo "<p>";
    echo "<h5>Affected tables:</h5>";
    echo $tableDesc."<br>";
    echo "</p>";
    echo "<p>";
    if(!empty($hotfix['description'])){
        echo "<h5>Description (manually set):</h5>";
        echo $hotfix['description'];
    }
    echo "</p>";
}
?>
</div>
<?php
require_once(__DIR__ . "/../inc/footer.php");
?>