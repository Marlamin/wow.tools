<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}
require_once("../../inc/config.php");

function makeTiles2019($targetdir, $mapname, $version, $build)
{
    if (is_dir($targetdir)) {
        if (count(glob($targetdir . "*")) == 0) {
            echo "[" . $version . "] [" . $mapname . "] No files found in target directory! Compilation might have failed in the past!\n";
        } else {
            //echo "[".$version."] [".$mapname."] Tiles already exist, skipping..\n";
            return;
        }
    } else {
        mkdir($targetdir, 0777, true);
    }

    $config['map'] = "/home/wow/minimaps/png/" . $version . "/" . $mapname . ".png";

    if (!file_exists($config['map'])) {
        echo "[" . $version . "] [" . $mapname . "] File not found on disk! Trying to find one with same filename but different casing..\n";
        foreach (glob("/home/wow/minimaps/png/" . $version . "/*.png") as $file) {
            $cleanedfile = str_replace("/home/wow/minimaps/png/" . $version . "/", "", $file);
            $cleanedfile = str_replace(".png", "", $cleanedfile);
            if (strtolower($cleanedfile) == strtolower($mapname)) {
                echo "[" . $version . "] [" . $mapname . "] Found match in file " . $file . ", using that instead!\n";
                $config['map'] = $file;
            }
        }
    }

    echo "[" . $version . "] [" . $mapname . "] Generating tiles..";

    if ($build > 26706) {
        $maxZoom = 8;
    } else {
        $maxZoom = 7;
    }

    exec("cd /home/wow/minimaps/cut; /usr/bin/dotnet WoWTools.MinimapCut.dll " . escapeshellarg($config['map']) . " " . escapeshellarg($targetdir) . " " . escapeshellarg($maxZoom));
    echo "..done!\n";
}

if (empty($argv[1])) {
    die("Need buildconfig hash or build as argument");
}

if (!empty($argv[2]) && $argv[2] == "true") {
    $regenerate = true;
} else {
    $regenerate = false;
}

if (strlen($argv[1]) == 32) {
    // CASC
    $build = getVersionByBuildConfigHash($argv[1]);
    if (empty($build)) {
        die("Could not find build!");
    }

    if (empty($build['buildconfig']['description'])) {
        die("Empty build description!");
    }

    $rawdesc = str_replace("WOW-", "", $build['buildconfig']['description']);
    $buildnum = substr($rawdesc, 0, 5);
    $rawdesc = str_replace(array($buildnum, "patch"), "", $rawdesc);
    $descexpl = explode("_", $rawdesc);
    $outdir = $descexpl[0] . "." . $buildnum;

    echo "Extracting tiles..\n";

    if ($regenerate) {
        echo "Removing raw directory..\n";
        shell_exec("rm -rf /home/wow/minimaps/raw/" . $outdir);
    }

    echo "Creating raw directory..\n";
    if (file_exists("/home/wow/minimaps/raw/" . $outdir)) {
        echo "Raw directory already exists, skipping extraction..\n";
    } else {
        shell_exec("mkdir -p /home/wow/minimaps/raw/" . $outdir);
        echo "Extracting tiles..\n";
        $extractionoutput = shell_exec("cd /home/wow/minimaps/extract; /usr/bin/dotnet WoWTools.MinimapExtract.dll " . __DIR__ . "/../../tpr/wow/ " . escapeshellarg($build['buildconfig']['hash']) . " " . escapeshellarg($build['cdnconfig']['hash']) . " " . escapeshellarg("/home/wow/minimaps/raw/" . $outdir));
        print_r($extractionoutput);
    }
} else {
    // Pre-CASC
    $buildexpl = explode(".", $argv[1]);
    if (count($buildexpl) != 4) {
        die("Invalid buildconfig or pre-CASC build: " . $argv[1]);
    }
    $buildnum = $buildexpl[3];
    $outdir = $argv[1];
}

if ($regenerate) {
    echo "Removing output directory..\n";
    shell_exec("rm -rf /home/wow/minimaps/png/" . $outdir);
}

echo "Creating output directory..\n";
if (file_exists("/home/wow/minimaps/png/" . $outdir)) {
    echo "Output directory already exists, skipping compilation..\n";
} else {
    shell_exec("mkdir -p /home/wow/minimaps/png/" . $outdir);
    echo "Compiling maps..\n";

    if (is_dir("/home/wow/minimaps/raw/" . $outdir . "/World/Minimaps/")) {
        $rawDir = "/home/wow/minimaps/raw/" . $outdir . "/World/Minimaps/";
    } elseif (is_dir("/home/wow/minimaps/raw/" . $outdir . "/world/minimaps/")) {
        $rawDir = "/home/wow/minimaps/raw/" . $outdir . "/world/minimaps/";
    } else {
        die("Unable to find right casing for raw minimap directory!");
    }
    foreach (glob($rawDir . "*", GLOB_ONLYDIR) as $directory) {
        $mapname = str_replace($rawDir, "", $directory);
        echo "Compiling map " . $mapname . "\n";
        $res = 256;

        // Ensure classic compatibility
        if ($buildnum > 26707 && substr($descexpl[0], 0, 1) >= 8) {
            $res = 512;
        }

        $compilationoutput = shell_exec("cd /home/wow/minimaps/compile; /usr/bin/dotnet WoWTools.MinimapCompile.dll " . escapeshellarg($rawDir . $mapname) . " " . escapeshellarg("/home/wow/minimaps/png/" . $outdir . "/" . $mapname . ".png") . " " . escapeshellarg($res));
        if (!file_exists("/home/wow/minimaps/png/" . $outdir . "/" . $mapname . ".png")) {
            echo "Compilation failed:" . print_r($compilationoutput, true);
        }
    }
}

echo "Updating database..\n";

if ($regenerate) {
    echo "Removing existing entries for this version..\n";

    $versionid = getOrCreateVersionID($outdir);
    if (empty($versionid)) {
        die("Unable to get/create version id!");
    }

    $vdelq = $pdo->prepare("DELETE FROM wow_maps_versions WHERE versionid = ?");
    $vdelq->execute([$versionid]);

    $cdelq = $pdo->prepare("DELETE FROM wow_maps_config WHERE versionid = ?");
    $cdelq->execute([$versionid]);
}

$mapCache = [];
foreach ($pdo->query("SELECT id, internal FROM wow_maps_maps") as $map) {
    $mapCache[strtolower($map['internal'])] = $map['id'];
}

$versionMapCache = [];
foreach ($pdo->query("SELECT map_id, versionid FROM wow_maps_versions") as $mapVersion) {
    $versionMapCache[$mapVersion['versionid']][] = $mapVersion['map_id'];
}

$createMapQ = $pdo->prepare("INSERT INTO wow_maps_maps (name, internal, firstseen) VALUES (?, ?, ?)");
$createMapVersionQ = $pdo->prepare("INSERT INTO wow_maps_versions (map_id, versionid, md5) VALUES (?, ?, ?)");
$createConfigQ = $pdo->prepare("INSERT INTO wow_maps_config (versionid, mapid, resx, resy, zoom, minzoom, maxzoom) VALUES (?, ?, ?, ?, 5, 2, ?)");
$checkConfigQ = $pdo->prepare("SELECT * FROM wow_maps_config WHERE versionid = ? AND mapid = ?");
foreach (glob("/home/wow/minimaps/png/*") as $dir) {
    $version = str_replace("/home/wow/minimaps/png/", "", $dir);
    $versionex = explode(".", $version);
    $versionid = getOrCreateVersionID($version);
    echo "Version: " . $versionex[0] . "." . $versionex[1] . "." . $versionex[2] . "." . $versionex[3] . " (" . $versionid . ")\n";
    foreach (glob($dir . "/*.png") as $map) {
        $mapname = str_replace(array($dir . "/", ".png"), "", $map);

        // Skip maps named WMO, remnants remain in older extracts
        if ($mapname == "wmo" || $mapname == "WMO") {
            continue;
        }

        if (!array_key_exists(strtolower($mapname), $mapCache)) {
            echo "[" . $version . "] [" . $mapname . "] Map is not yet known, creating ID..\n";
            $createMapQ->execute([$mapname, $mapname, $buildnum]);
            $mapCache[strtolower($mapname)] = $pdo->lastInsertId();
            echo "[" . $version . "] [" . $mapname . "] Created ID: " . $mapCache[strtolower($mapname)] . "\n";
        }

        $mapid = $mapCache[strtolower($mapname)];

        if (!array_key_exists($versionid, $versionMapCache) || !in_array($mapid, $versionMapCache[$versionid])) {
            echo "[" . $version . "] [" . $mapname . "] Map is unknown in version " . $version . " (" . $versionid . "), adding..\n";
            $md5 = md5_file($map);

            $targetdir = "/home/wow/minimaps/tiles/test/" . $mapid . "/" . $md5 . "/";
            makeTiles2019($targetdir, $mapname, $version, $buildnum);

            $createMapVersionQ->execute([$mapid, $versionid, $md5]);
            $versionMapCache[$versionid][] = $mapid;
        }

        $checkConfigQ->execute([$versionid, $mapid]);

        if (empty($checkConfigQ->fetch(PDO::FETCH_ASSOC))) {
            echo "[" . $version . "] [" . $mapname . "] Config unknown for map " . $mapname . " in version " . $version . " (" . $versionid . "), adding..\n";
            $sizes = getimagesize($map);

            if ($buildnum > 26706) {
                $maxZoom = 8;
            } else {
                $maxZoom = 7;
            }

            $createConfigQ->execute([$versionid, $mapid, $sizes[0], $sizes[1], $maxZoom]);
        }
    }
}
