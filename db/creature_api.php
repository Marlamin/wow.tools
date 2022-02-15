<?php

require_once("../inc/config.php");

if (!empty($_GET['type']) && $_GET['type'] == "bycdi" && !empty($_GET['id'])) {
    $q = $pdo->prepare('SELECT id, name, json FROM wowdata.creatures WHERE json LIKE ?');
    $q->execute(["%CreatureDisplayInfoID[_]\":\"" . $_GET['id'] . "%"]);

    header("Content-Type: application/json");

    $res = [];
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        $res[] = array("id" => $row['id'], "name" => $row['name'], json_decode($row['json'], true));
    }

    echo json_encode($res);

    die();
}

if (!empty($_GET['id'])) {
    $q = $pdo->prepare("SELECT json FROM wowdata.creatures WHERE id = ?");
    $q->execute([$_GET['id']]);

    header("Content-Type: application/json");

    $row = $q->fetch(PDO::FETCH_ASSOC);
    if (empty($row)) {
        die(json_encode(["error" => "Creature not found!"]));
    }

    $creature = json_decode($row['json'], true);

    if (!empty($creature['CreatureDisplayInfoID[0]'])) {
        $cdi = $pdo->prepare("SELECT filedataid FROM wowdata.creaturemodeldata WHERE id IN (SELECT ModelID FROM wowdata.creaturedisplayinfo WHERE ID = ?)");
        $cdi->execute([$creature['CreatureDisplayInfoID[0]']]);
        $cdirow = $cdi->fetch(PDO::FETCH_ASSOC);
        if (!empty($cdirow)) {
            $creature['filedataid'] = $cdirow['filedataid'];
        }
    }

    echo json_encode($creature);

    die();
}

$query = "FROM wowdata.creatures ";

if (!empty($_GET['search']['value'])) {
    if (substr($_GET['search']['value'], 0, 8) == "addedin:") {
        $searchBuild = str_replace("addedin:", "", $_GET['search']['value']);
        if (is_numeric($searchBuild)) {
            $query .= " WHERE firstseenbuild = " . $searchBuild;
        }
    } elseif (substr($_GET['search']['value'], 0, 6) == "field:") {
        $searchJSON = str_replace("field:", "", trim($_GET['search']['value']));
        $searchExploded = explode("=", $searchJSON);
        if (count($searchExploded) == 2) {
            $query .= " WHERE JSON_CONTAINS(json, :jsonVal, :jsonKey)";
            $jsonSearch = [];
            $jsonSearch['key'] = "$." . $searchExploded[0];
            $jsonSearch['value'] = "\"" . $searchExploded[1] . "\"";
        }
    } else {
        $query .= " WHERE id LIKE :search1 OR name LIKE :search2";
        $search = "%" . $_GET['search']['value'] . "%";
    }
}

$orderby = '';
if (!empty($_GET['order'])) {
    $orderby .= " ORDER BY ";
    switch ($_GET['order'][0]['column']) {
        case 0:
            $orderby .= "creatures.id";
            break;
        case 1:
            $orderby .= "creatures.name";
            break;
        case 2:
            $orderby .= "creatures.firstseenbuild";
            break;
        case 3:
            $orderby .= "creatures.lastupdatedbuild";
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

if (!empty($search)) {
    $numrowsq->bindParam(":search1", $search);
    $numrowsq->bindParam(":search2", $search);
    $dataq->bindParam(":search1", $search);
    $dataq->bindParam(":search2", $search);
}

if (!empty($jsonSearch)) {
    $numrowsq->bindParam(":jsonKey", $jsonSearch['key']);
    $numrowsq->bindParam(":jsonVal", $jsonSearch['value']);
    $dataq->bindParam(":jsonKey", $jsonSearch['key']);
    $dataq->bindParam(":jsonVal", $jsonSearch['value']);
}

$dataq->execute();
$numrowsq->execute();

if(isset($_GET['draw'])){
    $returndata['draw'] = (int)$_GET['draw'];
}

$returndata['recordsFiltered'] = (int)$numrowsq->fetchColumn();
$returndata['recordsTotal'] = $pdo->query("SELECT count(id) FROM wowdata.creatures")->fetchColumn();
$returndata['data'] = array();

foreach ($dataq->fetchAll() as $row) {
    $returndata['data'][] = array($row['id'], $row['name'], $row['firstseenbuild'], $row['lastupdatedbuild']);
}

echo json_encode($returndata);
