<?php
require_once("../inc/header.php");

$versionCacheByID = [];
foreach($pdo->query("SELECT id, version FROM wow_builds") as $version){
	$versionCacheByID[$version['id']] = $version['version'];
}

$tableCacheByID = [];
foreach($pdo->query("SELECT id, displayname FROM wow_dbc_tables") as $table){
	$tableCacheByID[$table['id']] = $table['displayname'];
}

$versionTableCache = [];
foreach($pdo->query("SELECT versionid, tableid FROM wow_dbc_table_versions") as $tv){
	$versionTableCache[$tv['versionid']][] = $tv['tableid'];
}
?>
<div class='container-fluid'>
	<div class='row'>
		<div class='col-md-6'>
			Current amount of versions (with or without definitions): <?=count($versionCacheByID)?>
			<table id='dbdstatsbymissingver' class='table table-condensed table-striped'>
				<thead><tr><th>Version</th><th>Tables w/o def</th></tr></thead>
				<?php
				foreach($pdo->query("SELECT versionid, COUNT(*) as noDefCount FROM wow_dbc_table_versions WHERE hasDefinition = 0 GROUP BY versionid ORDER BY noDefCount DESC") as $noDef){
					echo "<tr><td>".$versionCacheByID[$noDef['versionid']]."</td><td>".$noDef['noDefCount']."</td></tr>";
				}
				?>
			</table>
		</div>
		<div class='col-md-6'>
			Current amount of tables (with or without definitions): <?=count($tableCacheByID)?>
			<table id='dbdstatsbymissingtable' class='table table-condensed table-striped'>
				<thead><tr><th>Table</th><th>Versions w/o def</th></tr></thead>
				<?php
				foreach($pdo->query("SELECT tableid, COUNT(*) as noDefCount FROM wow_dbc_table_versions WHERE hasDefinition = 0 GROUP BY tableid ORDER BY noDefCount DESC") as $noDef){
					echo "<tr><td>".$tableCacheByID[$noDef['tableid']]."</td><td>".$noDef['noDefCount']."</td></tr>";
				}
				?>
			</table>
		</div>
	</div>
</div>