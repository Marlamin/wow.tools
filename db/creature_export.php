<?php
require_once("../inc/config.php");
ini_set('memory_limit', '1G');
header("Content-type: application/json; charset=utf-8");

$allJson = $pdo->query("SELECT id, name, JSON_EXTRACT(json, \"$.HPMultiplier\") as HPMult FROM wowdata.creatures");
$creatureArr = [];

while($row = $allJson->fetch(PDO::FETCH_ASSOC)){
    $row['HPMult'] = floatval(str_replace("\"","", $row['HPMult']));
    $creatureArr[] = $row;
}

echo json_encode($creatureArr);