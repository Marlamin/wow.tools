<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

require __DIR__ . "/../../inc/config.php";
$cdnconfigs = $pdo->query("SELECT DISTINCT(cdnconfig) FROM `wow_versions` ORDER BY ID DESC")->fetchAll(PDO::FETCH_COLUMN);

for (
    $i = 1; $i < count($cdnconfigs);
    $i++
) {
    echo "Comparing " . $cdnconfigs[$i - 1] . " and " . $cdnconfigs[$i] . "\n";
    $output = shell_exec("cd /home/wow/halfpush/; /usr/bin/dotnet HalfPushConfigGenerator.dll " . $cdnconfigs[$i - 1] . " " . $cdnconfigs[$i]);
    print_r($output);
}

die();
$product['cdndir'] = "wow";
$encodings = $pdo->query("SELECT DISTINCT(encoding) FROM wow_buildconfig")->fetchAll(PDO::FETCH_COLUMN);
$installs = $pdo->query("SELECT DISTINCT(install) FROM wow_buildconfig")->fetchAll(PDO::FETCH_COLUMN);
$downloads = $pdo->query("SELECT DISTINCT(download) FROM wow_buildconfig")->fetchAll(PDO::FETCH_COLUMN);
$patchconfigs = array();
$pcq = $pdo->query("SELECT description, patchconfig FROM wow_buildconfig");
foreach ($pcq->fetchAll() as $pc) {
    $patchconfigs[$pc['patchconfig']] = $pc['description'];
}

$di = new RecursiveDirectoryIterator(__DIR__ . "/../../tpr/" . $product['cdndir'] . "/config", RecursiveDirectoryIterator::SKIP_DOTS);
$it = new RecursiveIteratorIterator($di);
$bcs = array();
$cdncs = array();
$pcs = array();
foreach ($it as $file) {
    $type = trim(fgets(fopen($file, 'r')));
    if ($type ==  "# Patch Configuration") {
        $pcs[] = parseConfig($file);
    }
}

$knownEhashes = array();
foreach ($pcs as $pc) {
    foreach ($pc['patch-entry'] as $pe) {
        $explpe = explode(" ", $pe);
        $type = $explpe[0];
        if ($type == "partial-priority") {
            continue;
        }

        $ckey = $explpe[1];
        $ckeysize = $explpe[2];
        $ekey = $explpe[3];
        $ekeysize = $explpe[4];
        if (!doesFileExist("data", $ekey, $product['cdndir'])) {
            echo "Missing patch-config referenced " . $type . " " . $ekey . " (from patch config " . $pc['original-filename'] . ")\n";
        }

        $remaining = array_slice($explpe, 6);
        for (
            $pi = 0; $pi < count($remaining);
        ) {
            $ekey = $remaining[$pi++];
            $ekeysize = $remaining[$pi++];
            $pkey = $remaining[$pi++];
            $pkeysize = $remaining[$pi++];
            $hasBuild = false;
            switch ($type) {
                case "encoding":
                    $hasBuild = in_array($ekey, $encodings);
                    break;
                case "install":
                    $hasBuild = in_array($ekey, $installs);
                    break;
                case "download":
                    $hasBuild = in_array($ekey, $downloads);
                    break;
                case "size":
                    $hasBuild = true;
                    break;
                default:
                    echo "Unknown type " . $type . "\n";
            }

            if (!$hasBuild) {
                if (in_array($ekey, $knownEhashes)) {
                    continue;
                }

                if (array_key_exists($pc['original-filename'], $patchconfigs)) {
                    $belongsToBuild = $patchconfigs[$pc['original-filename']];
                } else {
                    $belongsToBuild = "UNKNOWN";
                }

                $fileLoc = __DIR__ . "/../../tpr/wow/data/" . $ekey[0] . $ekey[1] . "/" . $ekey[2] . $ekey[3] . "/" . $ekey;
                if (file_exists($fileLoc)) {
                    $size = filesize($fileLoc);
                } else {
                    $size = 0;
                }

                if ($type == "install" && file_exists($fileLoc)) {
                    if (!file_exists("/tmp/install-" . $ekey . ".txt")) {
                        file_put_contents("/tmp/install-" . $ekey . ".txt", file_get_contents("https://wow.tools/casc/install/dump?hash=" . $ekey));
                    }

                    $json = json_decode(file_get_contents("/tmp/install-" . $ekey . ".txt"), true);
                    foreach ($json as $file) {
                        if (strtolower(substr($file['name'], 0, 3)) == "wow") {
                            echo $belongsToBuild . " unref'd patchconfig install " . $ekey . " has WoW exe: " . $file['name'] . " " . $file['contentHash'] . " " . $file['size'] . "\n";
                        }
                    }

                    $knownEhashes[] = $ekey;
                }
            }
        }
    }
}
