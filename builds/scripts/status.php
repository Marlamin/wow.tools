<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

include(__DIR__ . "/../../inc/config.php");
$missingfiles = array();

function insertMissingFile($dir, $file, $type, $product = "wow")
{
    global $missingfiles;
    $filename = "tpr/" . $product . "/" . $dir . "/" . $file[0] . $file[1] . "/" . $file[2] . $file[3] . "/" . $file;
    $idx = count($missingfiles) + 1;
    $missingfiles[$idx]['filename'] = $filename;
    $missingfiles[$idx]['type'] = $type;
}

if (!empty($argv[1])) {
    if (!key_exists($argv[1], $allowedproducts)) {
        die("Please select a product: " . implode(", ", array_keys($allowedproducts)) . "\n");
    }
    $products = array($argv[1] => $allowedproducts[$argv[1]]);
} else {
    $products = $allowedproducts;
}

foreach ($products as $code => $product) {
    echo "Processing " . $code . "..\n";

    $di = new RecursiveDirectoryIterator(__DIR__ . "/../../tpr/" . $product['cdndir'] . "/config", RecursiveDirectoryIterator::SKIP_DOTS);
    $it = new RecursiveIteratorIterator($di);

    $bcs = array();
    $cdncs = array();
    $pcs = array();

    foreach ($it as $file) {
        if($product == "wowdev"){
            if(!file_exists("/tmp/wowdevcache/" . basename($file))){
                $output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet /home/wow/buildbackup/BuildBackup.dll dumpconfig wowdev " .  basename($file) . " > " . "/tmp/wowdevcache/" . basename($file) . " 2>&1");
            }
            $file = "/tmp/wowdevcache/" . basename($file);
        }
        $type = trim(fgets(fopen($file, 'r')));
        switch ($type) {
            case "# Build Configuration":
                $bcs[] = parseConfig($file);
                break;
            case "# CDN Configuration":
                $cdncs[] = parseConfig($file);
                break;
            case "# Patch Configuration":
                $pcs[] = parseConfig($file);
                break;
            //default:
                //echo file_get_contents($file);
        }
    }

    foreach ($bcs as $bc) {
        if (!doesFileExist("config", $bc['original-filename'], $product['cdndir'])) {
            echo "Missing build config " . $bc['original-filename'] . "\n";
            insertMissingFile("config", $bc['original-filename'], "buildconfig", $product['cdndir']);
        } else {
            if($code != "wowdev"){
                $urlt = explode("/", $bc['original-filename']);
                $md5 = md5_file(__DIR__ . "/../../tpr/" . $product['cdndir'] . "/config/" . $bc['original-filename'][0] . $bc['original-filename'][1] . "/" . $bc['original-filename'][2] . $bc['original-filename'][3] . "/" . $bc['original-filename']);
                if ($md5 != $bc['original-filename']) {
                    echo "MD5 mismatch on file " . $bc['original-filename'] . " (actual md5: " . $md5 . ")\n";
                }
            }
        }

        if (!empty($bc['encoding'])) {
            $encoding = explode(" ", $bc['encoding']);

            if (!doesFileExist("data", $encoding[1], $product['cdndir'])) {
                echo "Missing encoding " . $encoding[1] . " for build " . $bc['original-filename'] . "\n";
                insertMissingFile("data", $encoding[1], "encoding", $product['cdndir']);
            }
        }

        if (!empty($bc['patch'])) {
            if (!doesFileExist("patch", $bc['patch'], $product['cdndir'])) {
                echo "Missing patch " . $bc['patch'] . " for build " . $bc['original-filename'] . "\n";
                insertMissingFile("patch", $bc['patch'], "patch", $product['cdndir']);
            }
        }

        if (!empty($bc['patch-config'])) {
            if (!doesFileExist("config", $bc['patch-config'], $product['cdndir'])) {
                echo "Missing patch config " . $bc['patch-config'] . " for build " . $bc['original-filename'] . "\n";
                insertMissingFile("config", $bc['patch-config'], "patchconfig", $product['cdndir']);
            }
        }
    }

    foreach ($cdncs as $cdnc) {
        if (!doesFileExist("config", $cdnc['original-filename'], $product['cdndir'])) {
            echo "Missing CDNconfig: " . $cdnc['original-filename'] . "\n";
            insertMissingFile("config", $cdnc['original-filename'], "cdnconfig", $product['cdndir']);
        } else {
            $urlt = explode("/", $cdnc['original-filename']);
            $md5 = md5_file(__DIR__ . "/../../tpr/" . $product['cdndir'] . "/config/" . $cdnc['original-filename'][0] . $cdnc['original-filename'][1] . "/" . $cdnc['original-filename'][2] . $cdnc['original-filename'][3] . "/" . $cdnc['original-filename']);
            if ($md5 != $cdnc['original-filename']) {
                echo "MD5 mismatch on file " . $cdnc['original-filename'] . " (actual md5: " . $md5 . ")\n";
            }
        }

        if (!empty($cdnc['archives'])) {
            foreach (explode(" ", $cdnc['archives']) as $archive) {
                if (!doesFileExist("data", $archive, $product['cdndir'])) {
                    echo "Missing archive: " . $archive . "\n";
                    insertMissingFile("data", $archive, "archive", $product['cdndir']);
                }

                if (!doesFileExist("data", $archive . ".index", $product['cdndir'])) {
                    echo "Missing archive index: " . $archive . ".index\n";
                    insertMissingFile("data", $archive . ".index", "archiveindex", $product['cdndir']);
                }
            }
        }

        if (!empty($cdnc['patch-archives'])) {
            foreach (explode(" ", $cdnc['patch-archives']) as $patcharchive) {
                if (!empty($patcharchive)) {
                    if (!doesFileExist("patch", $patcharchive, $product['cdndir'])) {
                        echo "Missing patch archive: " . $patcharchive . "\n";
                        insertMissingFile("patch", $patcharchive, "patcharchive", $product['cdndir']);
                    }
                    if (!doesFileExist("patch", $patcharchive . ".index", $product['cdndir'])) {
                        echo "Missing patch archive index: " . $patcharchive . ".index\n";
                        insertMissingFile("patch", $patcharchive . ".index", "patcharchiveindex", $product['cdndir']);
                    }
                }
            }
        }

        if (!empty($cdnc['builds'])) {
            foreach (explode(" ", $cdnc['builds']) as $build) {
                if (empty($build)) {
                    continue;
                }
                if (!doesFileExist("config", $build, $product['cdndir'])) {
                    echo "Missing build config " . $build . " (from cdn config " . $cdnc['original-filename'] . ")\n";
                    insertMissingFile("config", $build, "buildconfig", $product['cdndir']);
                } else {
                    $md5 = md5_file(__DIR__ . "/../../tpr/" . $product['cdndir'] . "/config/" . $build[0] . $build[1] . "/" . $build[2] . $build[3] . "/" . $build);
                    if ($md5 != $build) {
                        echo "MD5 mismatch on file " . $build . " (actual md5: " . $md5 . ")\n";
                    }
                }
            }
        }
    }

    foreach ($pcs as $pc) {
        if (!empty($pc['patch'])) {
            if (!doesFileExist("patch", $pc['patch'], $product['cdndir'])) {
                echo "Missing patch file: " . $pc['patch'] . "\n";
                insertMissingFile("patch", $pc['patch'], "patch", $product['cdndir']);
            }
        }

        foreach ($pc['patch-entry'] as $pe) {
            $explpe = explode(" ", $pe);
            $type = $explpe[0];
            if ($type == "partial-priority" || substr($type, 0, 3) == "vfs") {
                continue;
            }

            $ckey = $explpe[1];
            $ckeysize = $explpe[2];
            $ekey = $explpe[3];
            $ekeysize = $explpe[4];

            if (!doesFileExist("data", $ekey, $product['cdndir'])) {
                echo "Missing patch-config referenced " . $type . " " . $ekey . " (from patch config " . $pc['original-filename'] . ")\n";
                insertMissingFile("data", $ekey, $type, $product['cdndir']);
            }

            $remaining = array_slice($explpe, 6);
            for ($pi = 0; $pi < count($remaining);) {
                $ekey = $remaining[$pi++];
                $ekeysize = $remaining[$pi++];
                $pkey = $remaining[$pi++];
                $pkeysize = $remaining[$pi++];

                if (!doesFileExist("data", $ekey, $product['cdndir'])) {
                    echo "Missing patch-config referenced " . $type . " " . $ekey . " (from patch config " . $pc['original-filename'] . ")\n";
                    insertMissingFile("data", $ekey, $type, $product['cdndir']);
                }

                if (!doesFileExist("patch", $pkey, $product['cdndir'])) {
                    echo "Missing patch file referenced " . $type . "-patch " . $pkey . " (from patch config " . $pc['original-filename'] . ")\n";
                    insertMissingFile("patch", $pkey, $type . "-patch", $product['cdndir']);
                }
            }
        }
    }

    foreach ($pdo->query("SELECT * FROM " . $code . "_versions") as $row) {
        // Skip version if already complete!
        if ($row['complete'] == 1) {
            continue;
        }

        $buildconfigcomplete = 0;
        if (!empty($row['buildconfig'])) {
            $existingBuild = getBuildConfigByBuildConfigHash($row['buildconfig'], $code);

            if ($existingBuild['complete'] == 1) {
                echo "Build " . $existingBuild['description'] . " already marked as complete, skipping!\n";
                $buildconfigcomplete = 1;
            } else {
                $buildconfigcomplete = 1;

                if (!doesFileExist("config", $row['buildconfig'], $product['cdndir'])) {
                    echo "Missing build config " . $row['buildconfig'] . "\n";
                    insertMissingFile("config", $row['buildconfig'], "buildconfig", $product['cdndir']);
                    $buildconfigcomplete = 0;
                } else {
                    $md5 = md5_file(__DIR__ . "/../../tpr/" . $product['cdndir'] . "/config/" . $row['buildconfig'][0] . $row['buildconfig'][1] . "/" . $row['buildconfig'][2] . $row['buildconfig'][3] . "/" . $row['buildconfig']);
                    if ($md5 != $row['buildconfig']) {
                        echo "MD5 mismatch on file " . $row['buildconfig'] . " (actual md5: " . $md5 . ")\n";
                    }

                    $bcres = $pdo->prepare("SELECT encoding_cdn, root_cdn, install_cdn, download_cdn, unarchived, unarchivedcount, unarchivedcomplete FROM " . $code . "_buildconfig WHERE hash = :hash");
                    $bcres->bindParam(":hash", $row['buildconfig']);
                    $bcres->execute();
                    $bcrow = $bcres->fetch();

                    foreach ($bcrow as $key => $value) {
                        if (!empty($value)) {
                            if ($key == "unarchived" || $key == "unarchivedcount" || $key == "unarchivedcomplete") {
                                continue;
                            }
                            if (!doesFileExist("data", $value, $product['cdndir'])) {
                                echo "File " . $value . " of type " . $key . " does not exist!\n";
                                insertMissingFile("data", $value, str_replace("_cdn", "", $key), $product['cdndir']);
                                $buildconfigcomplete = 0;
                            } else {
                                if ($key == "root_cdn" && $code == "catalogs") {
                                    $json = json_decode(file_get_contents(__DIR__ . "/../../tpr/catalogs/data/" . $value[0] . $value[1] . "/" . $value[2] . $value[3] . "/" . $value), true);

                                    if (!empty($json['files']['default'])) {
                                        $curr = current($json['files']['default']);
                                        if (!doesFileExist("data", $curr['hash'], "catalogs")) {
                                            insertMissingFile("data", $curr['hash'], "fragment", $product['cdndir']);
                                            $buildconfigcomplete = 0;
                                        }
                                    }
                                    foreach ($json['fragments'] as $fragment) {
                                        if (!doesFileExist("data", $fragment['hash'], $product['cdndir'])) {
                                            insertMissingFile("data", $fragment['hash'], "fragment", $product['cdndir']);
                                            $buildconfigcomplete = 0;
                                        } else {
                                            $fragmentjson = json_decode(file_get_contents(__DIR__ . "/../../tpr/catalogs/data/" . $fragment['hash'][0] . $fragment['hash'][1] . "/" . $fragment['hash'][2] . $fragment['hash'][3] . "/" . $fragment['hash']), true);
                                            if (!empty($fragmentjson['files']['default'])) {
                                                foreach ($fragmentjson['files']['default'] as $file) {
                                                    if (!doesFileExist("data", $file['hash'], $product['cdndir'])) {
                                                        insertMissingFile("data", $file['hash'], "resource", $product['cdndir']);
                                                        $buildconfigcomplete = 0;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($code != "catalogs") {
                                $buildconfigcomplete = 0;
                            }
                        }
                    }

                    if (!empty($bcrow['unarchived']) && $bcrow['unarchivedcount'] != $bcrow['unarchivedcomplete']) {
                        // Check which unarchived files are missing
                        foreach (explode(" ", $bcrow['unarchived']) as $unarchivedfile) {
                            if (!doesFileExist("data", $unarchivedfile, $product['cdndir'])) {
                                echo "Unarchived file " . $unarchivedfile . " does not exist!\n";
                                insertMissingFile("data", $unarchivedfile, "unarchived", $product['cdndir']);
                                $buildconfigcomplete = 0;
                            }
                        }
                    }
                }

                $uq = $pdo->prepare("UPDATE " . $code . "_buildconfig SET complete = :complete WHERE hash = :hash");
                $uq->bindParam(":complete", $buildconfigcomplete);
                $uq->bindParam(":hash", $row['buildconfig']);
                $uq->execute();
            }
        }

        $cdnconfigcomplete = 0;
        if (!empty($row['cdnconfig'])) {
            $existingConfig = getCDNConfigbyCDNConfigHash($row['cdnconfig'], $code);
            if ($existingConfig['complete'] == 1) {
                echo "CDN config " . $row['cdnconfig'] . " already marked as complete, skipping!\n";
                $cdnconfigcomplete = 1;
            } else {
                $cdnconfigcomplete = 1;
                if (!doesFileExist("config", $row['cdnconfig'], $product['cdndir'])) {
                    echo "Missing cdn config " . $row['cdnconfig'] . "\n";
                    insertMissingFile("config", $row['cdnconfig'], "cdnconfig", $product['cdndir']);
                    $cdnconfigcomplete = 0;
                } else {
                    $md5 = md5_file(__DIR__ . "/../../tpr/" . $product['cdndir'] . "/config/" . $row['cdnconfig'][0] . $row['cdnconfig'][1] . "/" . $row['cdnconfig'][2] . $row['cdnconfig'][3] . "/" . $row['cdnconfig']);
                    if ($md5 != $row['cdnconfig']) {
                        echo "MD5 mismatch on file " . $row['cdnconfig'] . " (actual md5: " . $md5 . ")\n";
                    }

                    $cdncres = $pdo->prepare("SELECT * FROM " . $code . "_cdnconfig WHERE hash = :hash");
                    $cdncres->bindParam(":hash", $row['cdnconfig']);
                    $cdncres->execute();
                    $cdncrow = $cdncres->fetch();

                    if ($cdncrow['archivecount'] != $cdncrow['archivecomplete']) {
                        foreach (explode(" ", $cdncrow['archives']) as $archive) {
                            if (!doesFileExist("data", $archive, $product['cdndir'])) {
                                echo "Archive " . $archive . " does not exist!\n";
                                insertMissingFile("data", $archive, "archive", $product['cdndir']);
                                $cdnconfigcomplete = 0;
                            }
                            if (!doesFileExist("data", $archive . ".index", $product['cdndir'])) {
                                echo "Archive index " . $archive . ".index does not exist!\n";
                                insertMissingFile("data", $archive . ".index", "archiveindex", $product['cdndir']);
                                $cdnconfigcomplete = 0;
                            }
                        }
                    }

                    if ($cdncrow['patcharchivecount'] != $cdncrow['patcharchivecomplete'] && !empty($cdncrow['patch-archives'])) {
                        foreach (explode(" ", $cdncrow['patch-archives']) as $patcharchive) {
                            if (!doesFileExist("patch", $patcharchive, $product['cdndir'])) {
                                echo "Patch archive " . $patcharchive . " does not exist!\n";
                                insertMissingFile("patch", $patcharchive, "patcharchive", $product['cdndir']);
                                $cdnconfigcomplete = 0;
                            }
                            if (!doesFileExist("patch", $patcharchive . ".index", $product['cdndir'])) {
                                echo "Patch archive index " . $patcharchive . ".index does not exist!\n";
                                insertMissingFile("patch", $patcharchive . ".index", "patcharchiveindex", $product['cdndir']);
                                $cdnconfigcomplete = 0;
                            }
                        }
                    }
                }

                $ucq = $pdo->prepare("UPDATE " . $code . "_cdnconfig SET complete = :complete WHERE hash = :hash");
                $ucq->bindParam(":complete", $cdnconfigcomplete);
                $ucq->bindParam(":hash", $row['cdnconfig']);
                $ucq->execute();
            }
        }

        if (substr($code, 0, 3) == "wow" && !empty($row['patchconfig'])) {
            $patchconfigcomplete = 0;
            $existingConfig = getPatchConfigbyPatchConfigHash($row['patchconfig'], $code);
            if($existingConfig == false){
                $patchconfigcomplete = 0;
            }else if ($existingConfig['complete'] == 1) {
                echo "Patch config " . $row['patchconfig'] . " already marked as complete, skipping!\n";
                $patchconfigcomplete = 1;
            } else {
                $patchconfigcomplete = 1;
                echo "Patch config " . $row['patchconfig'] . " is currently marked as incomplete!\n";

                if (!doesFileExist("config", $row['patchconfig'], $product['cdndir'])) {
                    echo "Missing patch config " . $row['patchconfig'] . "\n";
                    insertMissingFile("config", $row['patchconfig'], "patchconfig", $product['cdndir']);
                    $patchconfigcomplete = 0;
                } else {
                    $md5 = md5_file(__DIR__ . "/../../tpr/" . $product['cdndir'] . "/config/" . $row['patchconfig'][0] . $row['patchconfig'][1] . "/" . $row['patchconfig'][2] . $row['patchconfig'][3] . "/" . $row['patchconfig']);
                    if ($md5 != $row['patchconfig']) {
                        echo "MD5 mismatch on file " . $row['patchconfig'] . " (actual md5: " . $md5 . ")\n";
                    }

                    if (!empty($existingConfig['patch'])) {
                        if (!doesFileExist("patch", $existingConfig['patch'], $product['cdndir'])) {
                            echo "Patch file " . $existingConfig['patch'] . " does not exist!\n";
                            insertMissingFile("patch", $existingConfig['patch'], "patch", $product['cdndir']);
                            $patchconfigcomplete = 0;
                        }
                    }
                }

                $upq = $pdo->prepare("UPDATE " . $code . "_patchconfig SET complete = :complete WHERE hash = :hash");
                $upq->bindParam(":complete", $patchconfigcomplete);
                $upq->bindParam(":hash", $row['patchconfig']);
                $upq->execute();
            }
        }

        $uvq = $pdo->prepare("UPDATE " . $code . "_versions SET complete = :complete WHERE id = :id");
        $uvq->bindParam(":id", $row['id']);
        if (substr($code, 0, 3) == "wow" && !empty($row['patchconfig'])) {
            $isComplete = $buildconfigcomplete == 1 && $cdnconfigcomplete == 1 && $patchconfigcomplete == 1;
        }else{
            $isComplete = $buildconfigcomplete == 1 && $cdnconfigcomplete == 1;
        }
        
        if ($buildconfigcomplete == 1 && $cdnconfigcomplete == 1) {
            $uvq->bindValue(":complete", 1);
        } else {
            $uvq->bindValue(":complete", 0);
        }
        $uvq->execute();
    }
}

sort($missingfiles);

$missingfilesindb = array();

foreach ($pdo->query("SELECT url FROM missingfiles") as $row) {
    $missingfilesindb[] = $row['url'];
}

foreach ($missingfiles as $missingfile) {
    if (in_array($missingfile['filename'], $missingfilesindb)) {
        continue;
    }
    echo "Inserting " . $missingfile['filename'] . "\n";

    $msq = $pdo->prepare("INSERT IGNORE INTO missingfiles VALUES (NULL, :filename, 0, :type)");
    $msq->bindParam(":filename", $missingfile['filename']);
    $msq->bindParam(":type", $missingfile['type']);
    $msq->execute();
    $missingfilesindb[] = $missingfile['filename'];
}
