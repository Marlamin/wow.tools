<?php

include("inc/config.php");
include("inc/header.php");

$arr = $pdo->query("SELECT * FROM catalogs_buildconfig ORDER BY description DESC")->fetchAll();
?>
<div class='container-fluid'>
    <form action='catalog.php' method='GET' class='form-inline'>
        <div class='input-group'>
            <select class='form-control' name='oldbuild'>
                <?php foreach ($arr as $row) {?>
                    <option value='<?=$row['hash']?>'<?php if (!empty($_GET['oldbuild']) && $row['hash'] == $_GET['oldbuild']) {
                        echo " SELECTED";
                                   }?>>
                        <?=$row['description']?>
                    </option>
                    <?php
                }
                ?>
            </select>
            <div class="input-group-addon"> 
                => 
            </div>
            <select class='form-control' name='newbuild'>
                <?php foreach ($arr as $row) {?>
                    <option value='<?=$row['hash']?>'<?php if (!empty($_GET['newbuild']) && $row['hash'] == $_GET['newbuild']) {
                        echo " SELECTED";
                                   }?>>
                        <?=$row['description']?>
                    </option>
                    <?php
                }
                ?>
            </select>
        </div>
        <input type='submit' class='form-control btn btn-primary' style='margin-left: 10px;'>
    </form>
    <?php
    if (empty($_GET['oldbuild']) || empty($_GET['newbuild'])) {
        die("<br>Please select two builds to diff.");
    }
    if (!empty($_GET['oldbuild'])) {
        foreach ($arr as $row) {
            if ($row['hash'] == $_GET['oldbuild']) {
                $oldbuild = $row;
                break;
            }
        }
    }

    if (!empty($_GET['newbuild'])) {
        foreach ($arr as $row) {
            if ($row['hash'] == $_GET['newbuild']) {
                $newbuild = $row;
                break;
            }
        }
    }

    if ($_GET['oldbuild'] == $_GET['newbuild']) {
        die("Nothing to diff!");
    }

    if (empty($oldbuild) || empty($newbuild)) {
        die("No valid builds selected for diff");
    }

    if(!file_exists("/var/www/wow.tools/tpr/catalogs/data/" . $oldbuild['root_cdn'][0] . $oldbuild['root_cdn'][1] . "/" . $oldbuild['root_cdn'][2] . $oldbuild['root_cdn'][3] . "/" . $oldbuild['root_cdn'])){
        die("Old catalog to diff with does not exist, if this is an old catalog this will likely never exist, if this is a new catalog please check back in 5 minutes.");
    }

    $oldjson = json_decode(file_get_contents("/var/www/wow.tools/tpr/catalogs/data/" . $oldbuild['root_cdn'][0] . $oldbuild['root_cdn'][1] . "/" . $oldbuild['root_cdn'][2] . $oldbuild['root_cdn'][3] . "/" . $oldbuild['root_cdn']), true);
    if(!empty($oldjson['fragments'])){
        for($i = 0; $i < count($oldjson['fragments']); $i++){
            $fragment = $oldjson['fragments'][$i];
            if (doesFileExist("data", $fragment['hash'], "catalogs")) {
                $fragmentjson = json_decode(file_get_contents("/var/www/wow.tools/tpr/catalogs/data/" . $fragment['hash'][0] . $fragment['hash'][1] . "/" . $fragment['hash'][2] . $fragment['hash'][3] . "/" . $fragment['hash']), true);
                $oldjson['fragments'][$i]['content'] = $fragmentjson;
            }
        }
    }

    if (!empty($oldjson['files']['default'])) {
        $curr = current($oldjson['files']['default']);
        if (doesFileExist("data", $curr['hash'], "catalogs")) {
            $resourcejson = json_decode(file_get_contents("/var/www/wow.tools/tpr/catalogs/data/" . $curr['hash'][0] . $curr['hash'][1] . "/" . $curr['hash'][2] . $curr['hash'][3] . "/" . $curr['hash']), true);
            $oldjson['files']['default']['content'] = $resourcejson;
        }
    }

    if (doesFileExist("data", $newbuild['root_cdn'], "catalogs")) {
        $newjson = json_decode(file_get_contents("/var/www/wow.tools/tpr/catalogs/data/" . $newbuild['root_cdn'][0] . $newbuild['root_cdn'][1] . "/" . $newbuild['root_cdn'][2] . $newbuild['root_cdn'][3] . "/" . $newbuild['root_cdn']), true);
        if (!empty($newjson['fragments'])) {
            for($i = 0; $i < count($newjson['fragments']); $i++){
                $fragment = $newjson['fragments'][$i];
                if (doesFileExist("data", $fragment['hash'], "catalogs")) {
                    $fragmentjson = json_decode(file_get_contents("/var/www/wow.tools/tpr/catalogs/data/" . $fragment['hash'][0] . $fragment['hash'][1] . "/" . $fragment['hash'][2] . $fragment['hash'][3] . "/" . $fragment['hash']), true);
                    $newjson['fragments'][$i]['content'] = $fragmentjson;
                }
            }
        }
    }

    if (!empty($newjson['files']['default'])) {
        $curr = current($newjson['files']['default']);
        if (doesFileExist("data", $curr['hash'], "catalogs")) {
            $resourcejson = json_decode(file_get_contents("/var/www/wow.tools/tpr/catalogs/data/" . $curr['hash'][0] . $curr['hash'][1] . "/" . $curr['hash'][2] . $curr['hash'][3] . "/" . $curr['hash']), true);
            $newjson['files']['default']['content'] = $resourcejson;
        }
    }
    if (empty($_GET['parsedDiff'])) {
        // Write JSON structs to file
        $tmpoldname = tempnam("/tmp", "diff");
        $handle = fopen($tmpoldname, "w");
        fwrite($handle, json_encode($oldjson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fclose($handle);

        $tmpnewname = tempnam("/tmp", "diff");
        $handle = fopen($tmpnewname, "w");
        fwrite($handle, json_encode($newjson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fclose($handle);
        // Diff
        exec("git diff " . $tmpoldname . " " . $tmpnewname, $output);
        echo "<pre>";
        foreach ($output as $line) {
            $line = htmlspecialchars($line);
            if ($line[0] == "-") {
                echo "<span style='background-color: rgba(255,59,48,.15)'>" . $line . "</span>";
            } elseif ($line[0] == "+") {
                echo "<span style='background-color: rgba(90,249,178,.15);'>" . $line . "</span>";
            } else {
                echo "<span>" . $line . "</span>";
            }
            echo "\n";
        }
        echo "</pre>";
    } else {
         $diffs = CompareArrays::Diff(json_decode(json_encode($oldjson), true), json_decode(json_encode($newjson), true));
        if (!empty($diffs)) {
            $diffs = CompareArrays::Flatten($diffs);
        }
        $difftext = "<table class='table table-condensed table-hover subtable' style='width: 100%; font-size: 11px;'>";
        $difftext .= "<thead><tr><th style='width: 20px'>&nbsp;</th><th style='width: 100px'>Name</th><th>Before</th><th>After</th><th>&nbsp;</th></thead>";
        foreach ($diffs as $name => $diff) {
            switch ($diff->Type) {
                case "added":
                    $icon = 'plus';
                    break;
                case "modified":
                    $icon = 'pencil';
                    break;
                case "removed":
                    $icon = 'times';
                    break;
            }

            $difftext .= "<tr><td><i class='fa fa-" . $icon . "'></i></td><td>" . $name . "</td><td>" . $diff->OldValue . "</td><td>" . $diff->NewValue . "</td><td></td></tr>";
        }

        $difftext .= "</table>";
        echo $difftext;
    }

    ?>
</div>
<?php include "inc/footer.php"; ?>