<?php

require_once("../inc/header.php");
?>
<div class='container-fluid'>
<?php
$versionCacheByBuild = [];
foreach ($pdo->query("SELECT id, version, build FROM wow_builds ORDER BY build DESC") as $version) {
    $versionCacheByBuild[$version['build']] = $version['version'];
}

$versionCacheByID = [];
foreach ($pdo->query("SELECT id, version, build FROM wow_builds ORDER BY build DESC") as $version) {
    $versionCacheByID[$version['id']] = $version;
}


$mapCacheByID = [];
foreach ($pdo->query("SELECT id, name, internal, firstseen FROM wow_maps_maps ORDER BY firstseen ASC") as $map) {
    $mapCacheByID[$map['id']] = $map;
}

$mapConfigCache = [];
foreach ($pdo->query("SELECT * FROM wow_maps_config") as $mapConfig) {
    $mapConfigCache[$mapConfig['versionid']][$mapConfig['mapid']] = $mapConfig;
}

$versionMapCacheByMap = [];
foreach ($pdo->query("SELECT map_id as mapid, versionid FROM wow_maps_versions") as $mapVersion) {
    $versionMapCacheByMap[$mapVersion['mapid']][] = $mapVersion['versionid'];
}

function cmp_by_build($a, $b)
{

    return $a["build"] - $b["build"];
}


echo "<table class='table table-condensed table-striped'>";
echo "<thead><tr><th>Name</th><th>Internal</th><th>First seen</th><th>Versions</th></tr></thead><tbody>";
foreach ($mapCacheByID as $mapid => $map) {
    echo "<tr><td>" . $map['name'] . "</td><td>" . $map['internal'] . "</td><td>" . $versionCacheByBuild[$map['firstseen']] . "</td>";
    echo "<td>";
    $mapVersions = [];
    foreach ($versionMapCacheByMap[$mapid] as $versionmap) {
        $mapVersions[] = $versionCacheByID[$versionmap];
    }
    usort($mapVersions, "cmp_by_build");
    foreach ($mapVersions as $mapVersion) {
        echo "<a href='/maps/png/" . $mapVersion['version'] . "/" . rawurldecode($map['internal']) . ".png'>" . $mapVersion['version'] . "</a> ";
    }
    echo "</td>";
    echo "</tr>";
}
echo "</tbody></table>";
?>
</div>
<?php
require_once("../inc/footer.php");
?>
