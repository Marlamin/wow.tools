<?php
require_once("../inc/config.php");

if(!empty($_GET['id'])){
	$q = $pdo->prepare("SELECT json FROM wowdata.creatures WHERE id = ?");
	$q->execute([$_GET['id']]);

	header("Content-Type: application/json");

	$row = $q->fetch(PDO::FETCH_ASSOC);
	if(empty($row)){
		die(json_encode(["error" => "Creature not found!"]));
	}

	$creature = json_decode($row['json'], true);

	if(!empty($creature['CreatureDisplayInfoID[0]'])){
		$cdi = $pdo->prepare("SELECT filedataid FROM wowdata.creaturemodeldata WHERE id IN (SELECT ModelID FROM wowdata.creaturedisplayinfo WHERE ID = ?)");
		$cdi->execute([$creature['CreatureDisplayInfoID[0]']]);
		$cdirow = $cdi->fetch(PDO::FETCH_ASSOC);
		if(!empty($cdirow)){
			$creature['filedataid'] = $cdirow['filedataid'];
		}
	}

	echo json_encode($creature);

	die();
}

$query = "FROM wowdata.creatures ";

if(!empty($_GET['search']['value'])){
	$query .= " WHERE id LIKE :search1 OR name LIKE :search2";
	$search = "%". $_GET['search']['value'] . "%";
}

$orderby = '';
if(!empty($_GET['order'])){
	$orderby .= " ORDER BY ";
	switch($_GET['order'][0]['column']){
		case 0:
		$orderby .= "creatures.id";
		break;
		case 1:
		$orderby .= "creatures.name";
		break;
	}

	switch($_GET['order'][0]['dir']){
		case "asc":
		$orderby .= " ASC";
		break;
		case "desc":
		$orderby .= " DESC";
		break;
	}
}

$start = (int)filter_input( INPUT_GET, 'start', FILTER_SANITIZE_NUMBER_INT );
$length = (int)filter_input( INPUT_GET, 'length', FILTER_SANITIZE_NUMBER_INT );

$numrowsq = $pdo->prepare("SELECT COUNT(1) " . $query);
$dataq = $pdo->prepare("SELECT id, name " . $query . $orderby . " LIMIT " . $start .", " . $length);

if(!empty($search)){
	$numrowsq->bindParam(":search1", $search);
	$numrowsq->bindParam(":search2", $search);
	$dataq->bindParam(":search1", $search);
	$dataq->bindParam(":search2", $search);
}

$numrowsq->execute();
$dataq->execute();

$returndata['draw'] = (int)$_GET['draw'];
$returndata['recordsFiltered'] = (int)$numrowsq->fetchColumn();
$returndata['recordsTotal'] = $pdo->query("SELECT count(id) FROM wowdata.creatures")->fetchColumn();
$returndata['data'] = array();

foreach($dataq->fetchAll() as $row){
	$returndata['data'][] = array($row['id'], $row['name']);
}

echo json_encode($returndata);
?>