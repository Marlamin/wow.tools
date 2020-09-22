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

    echo "<pre>";
    $oldjson = json_decode(file_get_contents("/var/www/wow.tools/tpr/catalogs/data/" . $oldbuild['root_cdn'][0] . $oldbuild['root_cdn'][1] . "/" . $oldbuild['root_cdn'][2] . $oldbuild['root_cdn'][3] . "/" . $oldbuild['root_cdn']));
    foreach ($oldjson->fragments as $fragment) {
        if (doesFileExist("data", $fragment->hash, "catalogs")) {
            $fragmentjson = json_decode(file_get_contents("/var/www/wow.tools/tpr/catalogs/data/" . $fragment->hash[0] . $fragment->hash[1] . "/" . $fragment->hash[2] . $fragment->hash[3] . "/" . $fragment->hash));
            $fragment->content = $fragmentjson;
        }
    }

    if (!empty($oldjson->files->default)) {
        $curr = current($oldjson->files->default);
        if (doesFileExist("data", $curr->hash, "catalogs")) {
            $resourcejson = json_decode(file_get_contents("/var/www/wow.tools/tpr/catalogs/data/" . $curr->hash[0] . $curr->hash[1] . "/" . $curr->hash[2] . $curr->hash[3] . "/" . $curr->hash));
            $oldjson->files->default->content = $resourcejson;
        }
    }

    if (doesFileExist("data", $newbuild['root_cdn'], "catalogs")) {
        $newjson = json_decode(file_get_contents("/var/www/wow.tools/tpr/catalogs/data/" . $newbuild['root_cdn'][0] . $newbuild['root_cdn'][1] . "/" . $newbuild['root_cdn'][2] . $newbuild['root_cdn'][3] . "/" . $newbuild['root_cdn']));
        if (!empty($newjson->fragments)) {
            foreach ($newjson->fragments as $fragment) {
                if (doesFileExist("data", $fragment->hash, "catalogs")) {
                    $fragmentjson = json_decode(file_get_contents("/var/www/wow.tools/tpr/catalogs/data/" . $fragment->hash[0] . $fragment->hash[1] . "/" . $fragment->hash[2] . $fragment->hash[3] . "/" . $fragment->hash));
                    $fragment->content = $fragmentjson;
                }
            }
        }
    }

    if (!empty($newjson->files->default)) {
        $curr = current($newjson->files->default);
        if (doesFileExist("data", $curr->hash, "catalogs")) {
            $resourcejson = json_decode(file_get_contents("/var/www/wow.tools/tpr/catalogs/data/" . $curr->hash[0] . $curr->hash[1] . "/" . $curr->hash[2] . $curr->hash[3] . "/" . $curr->hash));
            $newjson->files->default->content = $resourcejson;
        }
    }

    // Write JSON structs to file
    $tmpoldname = tempnam("/tmp", "diff");
    $handle = fopen($tmpoldname, "w");
    fwrite($handle, json_encode($oldjson, JSON_PRETTY_PRINT));
    fclose($handle);

    $tmpnewname = tempnam("/tmp", "diff");
    $handle = fopen($tmpnewname, "w");
    fwrite($handle, json_encode($newjson, JSON_PRETTY_PRINT));
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
    ?>
</div>
<?php include "inc/footer.php"; ?>