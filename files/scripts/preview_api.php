<?php

require_once("/var/www/wow.tools/inc/config.php");

if (empty($_GET['buildconfig']) || empty($_GET['filedataid'])) {
    die("Not enough information!");
}

$staticBuild = trim(file_get_contents("/var/www/wow.tools/casc/extract/lastextractedroot.txt"));

if(empty($_GET['buildconfig']) || $_GET['buildconfig'] == "undefined"){
    $selectBuildFilterQ = $pdo->prepare("SELECT `hash` FROM wow_buildconfig WHERE root_cdn = ? GROUP BY root ORDER BY id ASC");
    $selectBuildFilterQ->execute([$staticBuild]);
    $_GET['buildconfig'] = $selectBuildFilterQ->fetchColumn();
}

$build = getVersionByBuildConfigHash($_GET['buildconfig'], "wow");

if (empty($build)) {
    die("Invalid build!");
}

$_GET['filedataid'] = (int)$_GET['filedataid'];

$q2 = $pdo->prepare("SELECT id, filename, type FROM wow_rootfiles WHERE id = :id");
$q2->bindParam(":id", $_GET['filedataid'], PDO::PARAM_INT);
$q2->execute();
$row2 = $q2->fetch();
if (empty($row2)) {
    die("File not found in database!");
} else {
    $type = $row2['type'];
    $dbid = $row2['id'];
}

if (empty($row2['filename'])) {
    $row2['filename'] = $row2['id'] . "." . $type;
}

function downloadFile($params, $outfile)
{
    $fp = fopen($outfile, 'w+');
    $url = 'http://localhost:5005/casc/file' . $params;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $exec = curl_exec($ch);
    curl_close($ch);
    fclose($fp);

    if ($exec) {
        return true;
    } else {
        return false;
    }
}

if (!empty($_GET['contenthash'])) {
    // If contenthash is available, use that for faster lookups
    $cascparams = "/chash?buildconfig=" . $build['buildconfig']['hash'] . "&cdnconfig=" . $build['cdnconfig']['hash'] . "&filename=" . urlencode($row2['filename']) . "&contenthash=" . $_GET['contenthash'];
} else {
    // Otherwise, use filedataid
    $cascparams = "/fdid?buildconfig=" . $build['buildconfig']['hash'] . "&cdnconfig=" . $build['cdnconfig']['hash'] . "&filename=" . urlencode($row2['filename']) . "&filedataid=" . $_GET['filedataid'];
}

$previewURL = "/casc/extract/" . $staticBuild . "/" . $_GET['filedataid'];

if ($type == "ogg") {
    echo "<audio autoplay controls><source src='" . $previewURL . "' type='audio/ogg'></audio>";
} elseif ($type == "mp3") {
    echo "<audio autoplay controls><source src='" . $previewURL . "' type='audio/mpeg'></audio>";
} elseif ($type == "blp") {
        ?>
    <canvas id='mapCanvas' width='1' height='1'></canvas>
    <script type='text/javascript'>renderBLPToCanvasElement("<?php echo $previewURL ?>", "mapCanvas", 0, 0, true);</script>
    <?php
} else {
    $tempfile = "/var/www/wow.tools/casc/extract/" . $staticBuild . "/" . $_GET['filedataid'];
    if ($type == "m2" || $type == "wmo") {
        // dump json
        $output = shell_exec("cd /home/wow/jsondump; /usr/bin/dotnet WoWJsonDumper.dll " . $type . " " . escapeshellarg($tempfile) . " 2>&1");
        ?>
            <div class='alert alert-danger'>As mentioned in the October update (see <a href='/2022.php' target='_BLANK'>2022</a>), the model viewer is now using static files. This means previewing models from recent builds will likely not work.</div>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="model-tab" data-toggle="tab" href="#model" role="tab" aria-controls="model" aria-selected="true">Model</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="raw-tab" data-toggle="tab" href="#raw" role="tab" aria-controls="raw" aria-selected="false">Raw</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane show active" id="model" role="tabpanel" aria-labelledby="model-tab">
                <iframe style='border:0px;width:100%;min-height: 75vh' src='/mv/?embed=true&buildconfig=<?=$build['buildconfig']['hash']?>&cdnconfig=<?=$build['cdnconfig']['hash']?>&filedataid=<?=$_GET['filedataid']?>&type=<?=$type?>'></iframe><br>
                <center><a href='/mv/?buildconfig=<?=$build['buildconfig']['hash']?>&cdnconfig=<?=$build['cdnconfig']['hash']?>&filedataid=<?=$_GET['filedataid']?>&type=<?=$type?>' target='_BLANK'>Open in modelviewer</a></center>
            </div>
            <div class="tab-pane" id="raw" role="tabpanel" aria-labelledby="raw-tab"><pre style='max-height: 80vh'><code><?=htmlentities($output)?></pre></code></div>
        </div>
        <?php
    } elseif ($type == "xml" || $type == "xsd" || $type == "lua" || $type == "toc" || $type == "htm" || $type == "html" || $type == "sbt" || $type == "txt" || $type == "wtf") {
        echo "<pre style='max-height: 80vh'><code>" . htmlentities(file_get_contents($tempfile)) . "</pre></code>";
    } else if ($type == "wwf") {
        $output = shell_exec("/usr/bin/tail -c +9 " . escapeshellarg($tempfile) . "");
        echo "<pre style='max-height: 80vh'><code id='jsonHolder'></code></pre><script type='text/javascript'>var jsonString = \"" . addslashes($output) . "\"; document.getElementById('jsonHolder').innerHTML = JSON.stringify(JSON.parse(jsonString),null,2);</script>";
    } else {
        // dump via hd
        echo "Not a supported file for previews, dumping hex output (until 1MB).";
        $output = shell_exec("/usr/bin/hd -n1048576 " . escapeshellarg($tempfile));
        echo "<pre style='max-height: 80vh'><code>" . htmlentities($output) . "</pre></code>";
    }
}
?>