<?php

require_once(__DIR__ . "/../inc/config.php");
header('Content-Type: application/json');

function peekDBCRow($name, $build, $col, $val, $useHotfix, $pushID = 0)
{
    $ch = curl_init();
    if (empty($pushID)) {
        curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:5000/api/peek/" . $name . "?build=" . urlencode($build) . "&col=" . $col . "&val=" . $val . "&useHotfixes=" . $useHotfix);
    } else {
        curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:5000/api/peek/" . $name . "?build=" . urlencode($build) . "&col=" . $col . "&val=" . $val . "&useHotfixes=" . $useHotfix . "&pushIDs=" . $pushID);
    }
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    if (!$data) {
        header('HTTP/1.1 500 Internal Server Error');
        die("cURL fail: " . print_r(curl_error($ch)) . "\n");
    }
    curl_close($ch);
    return $data;
}

if (!empty($_GET['cacheproxy']) && $_GET['cacheproxy'] == 1) {
    if (empty($_GET['dbc']) || empty($_GET['build']) || empty($_GET['col']) || empty($_GET['val']) || empty($_GET['useHotfixes'])) {
        die("Not enough parameters");
    }

    if (empty($_GET['pushID'])) {
        $_GET['pushID'] = 0;
    }

    $cacheName = "hotfix.cache." . $_GET['dbc'] . "." . $_GET['build'] . "." . $_GET['col'] . "." . $_GET['val'] . "." . $_GET['useHotfixes'] . "." . $_GET['pushID'];
    if (!($entry = $memcached->get($cacheName))) {
        $cacheHit = false;
        if (!empty($_GET['pushID'])) {
            $entry = peekDBCRow($_GET['dbc'], $_GET['build'], $_GET['col'], $_GET['val'], $_GET['useHotfixes'], $_GET['pushID']);
        } else {
            $entry = peekDBCRow($_GET['dbc'], $_GET['build'], $_GET['col'], $_GET['val'], $_GET['useHotfixes']);
        }
        if (!$memcached->set($cacheName, $entry)) {
            die("Failed to set memcache entry: " . $memcached->getResultMessage());
        }
    } else {
        $cacheHit = true;
    }

    $json = json_decode($entry, true);

    foreach ($json['values'] as $key => $value) {
        if ($key == "Expression") {
            $hrExp = new humanReadableWorldStateExpression();
            $wState = new worldStateExpression($value);
            $json['values'][$key] = $hrExp->stateToString($wState->state);
        }
    }

    $json['cacheHit'] = $cacheHit;
    echo json_encode($json);
    die();
}
$fullbuilds = $pdo->query("SELECT build, version FROM wow_builds")->fetchAll(PDO::FETCH_KEY_PAIR);
$buildsToID = $pdo->query("SELECT build, id FROM wow_builds")->fetchAll(PDO::FETCH_KEY_PAIR);
$tablesToID = $pdo->query("SELECT name, id FROM wow_dbc_tables")->fetchAll(PDO::FETCH_KEY_PAIR);
$loggedFixes = $pdo->query("SELECT pushID from wow_hotfixlogs")->fetchAll(PDO::FETCH_COLUMN);

$versionTableCache = [];
foreach ($pdo->query("SELECT versionid, tableid FROM wow_dbc_table_versions") as $tv) {
    $versionTableCache[$tv['versionid']][] = $tv['tableid'];
}

function isTableAvailableForBuild($table, $build)
{
    global $buildsToID;
    global $tablesToID;
    global $versionTableCache;

    if (array_key_exists(strtolower($table), $tablesToID)) {
        return in_array($tablesToID[strtolower($table)], $versionTableCache[$buildsToID[$build]]);
    } else {
        return false;
    }
}
$start = (int)filter_input(INPUT_GET, 'start', FILTER_SANITIZE_NUMBER_INT);
$length = (int)filter_input(INPUT_GET, 'length', FILTER_SANITIZE_NUMBER_INT);

if (empty($_GET['draw'])) {
    $_GET['draw'] = 0;
}

$returndata['draw'] = (int)$_GET['draw'];
$returndata['recordsTotal'] = $pdo->query("SELECT count(*) FROM wow_hotfixes")->fetchColumn();

if (empty($_GET['search']['value'])) {
    if (empty($_GET['since'])) {
        $dataq = $pdo->prepare("SELECT * FROM wow_hotfixes ORDER BY firstdetected DESC, pushID DESC, tableName DESC, recordID DESC LIMIT :start, :length");
    } else {
        $since = (int)filter_input(INPUT_GET, 'since', FILTER_SANITIZE_NUMBER_INT);

        $dataq = $pdo->prepare("SELECT * FROM wow_hotfixes WHERE firstdetected > FROM_UNIXTIME(:since) ORDER BY firstdetected DESC, pushID DESC, tableName DESC, recordID DESC LIMIT :start, :length");
        $dataq->bindValue(":since", $since);
    }
} else {
    if (substr($_GET['search']['value'], 0, 7) == "pushid:") {
        $dataq = $pdo->prepare("SELECT * FROM wow_hotfixes WHERE pushID = :pushID ORDER BY firstdetected DESC, pushID DESC, tableName DESC, recordID DESC LIMIT :start, :length");
        $dataq->bindValue(":pushID", str_replace("pushid:", "", $_GET['search']['value']));
    } else {
        $dataq = $pdo->prepare("SELECT * FROM wow_hotfixes WHERE pushID LIKE :pushID OR recordID LIKE :recordID OR tableName LIKE :tableName or build LIKE :build or firstdetected LIKE :firstDetected ORDER BY firstdetected DESC, pushID DESC, tableName DESC, recordID DESC LIMIT  :start, :length");
        $dataq->bindValue(":pushID", "%" . $_GET['search']['value'] . "%");
        $dataq->bindValue(":recordID", "%" . $_GET['search']['value'] . "%");
        $dataq->bindValue(":tableName", "%" . $_GET['search']['value'] . "%");
        $dataq->bindValue(":build", "%" . $_GET['search']['value'] . "%");
        $dataq->bindValue(":firstDetected", "%" . $_GET['search']['value'] . "%");
    }
}

$dataq->bindValue(":start", $start, PDO::PARAM_INT);
$dataq->bindValue(":length", $length, PDO::PARAM_INT);

$dataq->execute();

$returndata['data'] = array();
while ($row = $dataq->fetch()) {
    $returndata['data'][] = array($row['pushID'], $row['tableName'], $row['recordID'], $fullbuilds[$row['build']], $row['isValid'], $row['firstdetected'], isTableAvailableForBuild($row['tableName'], $row['build']), in_array($row['pushID'], $loggedFixes));
}
$returndata['recordsFiltered'] = count($returndata['data']);

echo json_encode($returndata);
