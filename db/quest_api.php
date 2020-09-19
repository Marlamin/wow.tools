<?php

require_once("../inc/config.php");

if (!empty($_GET['id'])) {
    $q = $pdo->prepare("SELECT json FROM wowdata.quests WHERE id = ?");
    $q->execute([$_GET['id']]);

    header("Content-Type: application/json");

    $row = $q->fetch(PDO::FETCH_ASSOC);
    if (empty($row)) {
        die(json_encode(["error" => "Quest not found!"]));
    }

    $quest = json_decode($row['json'], true);

    echo json_encode($quest);

    die();
}

$query = "FROM wowdata.quests ";

if (!empty($_GET['search']['value'])) {
    $query .= " WHERE id LIKE :search1 OR name LIKE :search2";
    $search = "%" . $_GET['search']['value'] . "%";
}

$orderby = '';
if (!empty($_GET['order'])) {
    $orderby .= " ORDER BY ";
    switch ($_GET['order'][0]['column']) {
        case 0:
            $orderby .= "quests.id";
            break;
        case 1:
            $orderby .= "quests.name";
            break;
        case 2:
            $orderby .= "quests.firstseenbuild";
            break;
        case 3:
            $orderby .= "quests.lastupdatedbuild";
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

$numrowsq->execute();
$dataq->execute();

$returndata['draw'] = (int)$_GET['draw'];
$returndata['recordsFiltered'] = (int)$numrowsq->fetchColumn();
$returndata['recordsTotal'] = $pdo->query("SELECT count(id) FROM wowdata.quests")->fetchColumn();
$returndata['data'] = array();

foreach ($dataq->fetchAll() as $row) {
    $returndata['data'][] = array($row['id'], $row['name'], $row['firstseenbuild'], $row['lastupdatedbuild']);
}

echo json_encode($returndata);
