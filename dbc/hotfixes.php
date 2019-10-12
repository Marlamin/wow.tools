<?php
require_once("../inc/header.php");
?>
<div class='container-fluid'>
<table class='table'>
<thead>
	<tr><th>Push ID</th><th>Table name</th><th>Record ID</th><th>Build</th></tr>
<?php
foreach($pdo->query("SELECT * FROM wow_hotfixes ORDER BY pushID DESC") as $hotfix){
?>
<tr><td><?=$hotfix['pushID']?></td><td><?=$hotfix['build']?></td><td><?=$hotfix['tableName']?></td><td><?=$hotfix['build']?></td></tr>
<? } ?>
</table>
</div>
<script type='text/javascript'>

</script>
<?php
require_once("../inc/footer.php");
?>