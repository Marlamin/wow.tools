<?php

require_once(__DIR__ . "/../../inc/config.php");

function hasCDNDir($product)
{
    global $pdo;
    $q = $pdo->prepare("SELECT cdndir FROM ngdp_products WHERE program = ?");
    $q->execute([$product]);

    if (empty($q->fetch())) {
        return false;
    } else {
        return true;
    }
}

function buildURL($product, $type, $value)
{
    global $pdo;

    $cdn = "http://blzddist1-a.akamaihd.net/";
    $q = $pdo->prepare("SELECT cdndir FROM ngdp_products WHERE program = ?");
    $q->execute([$product]);
    $cdndir = $q->fetch()['cdndir'];
    if (empty($cdndir)) {
        return false;
    } else {
        if (empty($cdndir) || empty($value)) {
            return false;
        } else {
            if ($type == "config") {
                return $cdn . $cdndir . "/config/" . $value[0] . $value[1] . "/" . $value[2] . $value[3] . "/" . $value;
            } elseif ($type == "data") {
                return $cdn . $cdndir . "/data/" . $value[0] . $value[1] . "/" . $value[2] . $value[3] . "/" . $value;
            } elseif ($type == "tpr/configs/data") {
                return $cdn . $type . "/" . $value[0] . $value[1] . "/" . $value[2] . $value[3] . "/" . $value;
            }
        }
    }
}

if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_GET['draw']) || !isset($_GET['order'][0]['column'])) {
    http_response_code(400);
    die();
}

$query = "FROM ngdp_history INNER JOIN ngdp_urls on ngdp_urls.id=ngdp_history.url_id";

if (!empty($_SESSION['rank'])) {
    if (!empty($_GET['columns'][1]['search']['value'])) {
        $query .= " WHERE event = 'valuechange' AND ngdp_urls.url LIKE :prodSearch";
        $prodSearch = "%" . $_GET['columns'][1]['search']['value'] . "%";
    } else {
        $query .= " WHERE event = 'valuechange'";
    }
} else {
    if (!empty($_GET['columns'][1]['search']['value']) && strpos($_GET['columns'][1]['search']['value'], "wow") !== false) {
        $query .= " WHERE event = 'valuechange' AND ngdp_urls.url LIKE :prodSearch";
        $prodSearch = "%" . $_GET['columns'][1]['search']['value'] . "%";
    } else {
        $query .= " WHERE event = 'valuechange' AND ngdp_urls.url LIKE '%wow%'";
    }
}


if (!empty($_GET['search']['value'])) {
    $query .= " AND CONCAT_WS(' ', ngdp_history.oldvalue, ngdp_history.newvalue) LIKE :search";
    $search = "%" . $_GET['search']['value'] . "%";
}

$orderby = '';
if (!empty($_GET['order'])) {
    $orderby .= " ORDER BY ";
    switch ($_GET['order'][0]['column']) {
        case 0:
            $orderby .= "ngdp_history.timestamp";
            break;
        case 1:
            $orderby .= "ngdp_history.url_id";
            break;
        case 2:
        // no sorting by diff, yet
            $orderby .= "ngdp_history.timestamp";
            break;
    }

    switch ($_GET['order'][0]['dir']) {
        case "asc":
            $orderby .= " ASC";
            break;
        case "desc":
            $orderby .= " DESC";
            break;
    }
}

$start = (int)filter_input(INPUT_GET, 'start', FILTER_SANITIZE_NUMBER_INT);
$length = (int)filter_input(INPUT_GET, 'length', FILTER_SANITIZE_NUMBER_INT);

$numrowsq = $pdo->prepare("SELECT COUNT(1) " . $query);
$dataq = $pdo->prepare("SELECT * " . $query . $orderby . " LIMIT " . $start . ", " . $length);

if (!empty($prodSearch)) {
    $numrowsq->bindParam(":prodSearch", $prodSearch);
    $dataq->bindParam(":prodSearch", $prodSearch);
}

if (!empty($search)) {
    $numrowsq->bindParam(":search", $search);
    $dataq->bindParam(":search", $search);
}
$numrowsq->execute();
$dataq->execute();

$returndata['draw'] = (int)$_GET['draw'];
$returndata['recordsFiltered'] = (int)$numrowsq->fetchColumn();
$returndata['recordsTotal'] = $pdo->query("SELECT count(id) FROM ngdp_history WHERE event='valuechange'")->fetchColumn();
$returndata['data'] = array();

foreach ($dataq->fetchAll() as $row) {
    $urlex = explode("/", $row['url']);
    $product = $urlex[3];

    if (substr($row['url'], -4, 4) == "game" || substr($row['url'], -7, 7) == "install") {
        $before = json_decode(utf8_encode($row['oldvalue']), true);
        $after = json_decode(utf8_encode($row['newvalue']), true);
    } else {
        $before = parseBPSV(explode("\n", $row['oldvalue']));
        $after = parseBPSV(explode("\n", $row['newvalue']));
    }

    if (empty($before)) {
        $before = [];
    }

    if (empty($after)) {
        $after = [];
    }

    $diffs = CompareArrays::Diff($before, $after);

    if (empty($diffs)) {
        $difftext = "No changes found -- likely only a sequence number increase";
    } else {
        $diffs = CompareArrays::Flatten($diffs);

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

            $showUrl = false;

            if (hasCDNDir($product)) {
                if (strpos($name, "BuildConfig") !== false || strpos($name, "CDNConfig") !== false) {
                    $showUrl = true;
                    $oldurl = buildURL($product, "config", $diff->OldValue);
                    $newurl = buildURL($product, "config", $diff->NewValue);
                } elseif (strpos($name, "ProductConfig") !== false) {
                    $showUrl = true;
                    if (!empty($diff->OldValue)) {
                        $oldurl = buildURL($product, "tpr/configs/data", $diff->OldValue);
                    } else {
                        $oldurl = '#';
                    }
                    if (!empty($diff->NewValue)) {
                        $newurl = buildURL($product, "tpr/configs/data", $diff->NewValue);
                    } else {
                        $newurl = '#';
                    }
                }
            }

            if ($showUrl) {
                $difftext .= "<tr><td><i class='fa fa-" . $icon . "'></i></td><td>" . $name . "</td><td><a href='" . $oldurl . "' target='_BLANK'>" . $diff->OldValue . "</a></td><td><a href='" . $newurl . "' target='_BLANK'>" . $diff->NewValue . "</a></td><td><a style='cursor: pointer' data-toggle='modal' data-target='#previewModal' onClick='fillDiffModal(\"" . str_replace("http://blzddist1-a.akamaihd.net/", "", $oldurl) . "\", \"" . str_replace("http://blzddist1-a.akamaihd.net/", "", $newurl) . "\")'>Preview</a>";
            } else {
                $difftext .= "<tr><td><i class='fa fa-" . $icon . "'></i></td><td>" . $name . "</td><td>" . $diff->OldValue . "</td><td>" . $diff->NewValue . "</td><td></td></tr>";
            }
        }
    }


    $difftext .= "</table>";

    $row['diff'] = print_r($diffs, true);


    $returndata['data'][] = array($row['timestamp'], $row['name'] . " (" . $product . ")", "" . $difftext . "");
}

echo json_encode($returndata);
