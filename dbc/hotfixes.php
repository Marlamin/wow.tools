<?php
require_once(__DIR__ . "/../inc/header.php");

$dbcs = [];
foreach($pdo->query("SELECT * FROM wow_dbc_tables") as $dbc){
	$dbcs[$dbc['name']] = $dbc;
}
?>
<div class='container-fluid'>
<table class='table'>
<thead>
	<tr><th>Push ID</th><th>Table name</th><th>Record ID</th><th>Build</th><th>First seen at</th></tr>
<?php
foreach($pdo->query("SELECT * FROM wow_hotfixes ORDER BY firstdetected DESC, pushID DESC") as $hotfix){
?>
<tr><td><?=$hotfix['pushID']?></td><td><?=$hotfix['tableName']?></td><td><?=$hotfix['recordID']?></td><td><?=$hotfix['build']?></td><td><?=$hotfix['firstdetected']?></td></tr>
<? } ?>
</table>
</div>
<script type='text/javascript'>

</script>
<?php
require_once(__DIR__ . "/../inc/footer.php");
?>