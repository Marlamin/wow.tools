<?php
require_once(__DIR__ . "/../inc/config.php");
header('Content-Type: application/json');

$start = (int)filter_input( INPUT_GET, 'start', FILTER_SANITIZE_NUMBER_INT );
$length = (int)filter_input( INPUT_GET, 'length', FILTER_SANITIZE_NUMBER_INT );

if(empty($_GET['draw']))
	$_GET['draw'] = 0;

$returndata['draw'] = (int)$_GET['draw'];
$returndata['recordsTotal'] = $pdo->query("SELECT count(*) FROM wow_hotfixes")->fetchColumn();
$returndata['recordsFiltered'] = $returndata['recordsTotal'];

$dataq = $pdo->prepare("SELECT * FROM wow_hotfixes ORDER BY firstdetected DESC, pushID DESC LIMIT " . $start .", " . $length);
$dataq->execute();

$returndata['data'] = array();
while($row = $dataq->fetch()){
	$returndata['data'][] = array($row['pushID'], $row['tableName'], $row['recordID'], $row['build'], $row['firstdetected']);
}

echo json_encode($returndata);
?>