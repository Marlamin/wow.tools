<?php
if(php_sapi_name() != "cli") die("This script cannot be run outside of CLI.");
require_once("../../inc/config.php");

$q = $pdo->prepare("UPDATE wow_dbc_tables SET displayName = :displayname WHERE name = :name");

foreach(glob("/home/wow/dbd/WoWDBDefs/definitions/*.dbd") as $dbd){
	$tablename = str_replace(".dbd", "", basename($dbd));
	echo $tablename."\n";

	$q->execute([$tablename, strtolower($tablename)]);
}
?>