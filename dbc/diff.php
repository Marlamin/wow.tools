<?php
if(!empty($_GET['embed'])){
	require_once("/var/www/wow.tools/inc/config.php");
}else{
	require_once("../inc/header.php");
}

// Map old URL to new url for backwards compatibility
if(!empty($_GET['old']) && strlen($_GET['old']) == 32 || !empty($_GET['new']) && strlen($_GET['new']) == 32){
	$bcq = $pdo->prepare("SELECT description FROM wow_buildconfig WHERE hash = ?");

	$bcq->execute([$_GET['old']]);
	$oldrow = $bcq->fetch();

	$bcq->execute([$_GET['new']]);
	$newrow = $bcq->fetch();

	if(!empty($oldrow) && !empty($newrow)){
		$oldbuild = parseBuildName($oldrow['description'])['full'];
		$newbuild = parseBuildName($newrow['description'])['full'];
		$newurl = str_replace($_GET['old'], $oldbuild, $_SERVER['REQUEST_URI']);
		$newurl = str_replace($_GET['new'], $newbuild, $newurl);
		$newurl = str_replace(".db2", "", $newurl);
		echo "<meta http-equiv='refresh' content='0; url=https://wow.tools".$newurl."'>";
		die();
	}
}

$tables = [];

foreach($pdo->query("SELECT * FROM wow_dbc_tables ORDER BY name ASC") as $dbc){
	$tables[$dbc['id']] = $dbc;
	if(!empty($_GET['dbc']) && $_GET['dbc'] == $dbc['name']) $currentDB = $dbc;
}

$canDiff = false;
if(!empty($currentDB) && !empty($_GET['old']) && !empty($_GET['new'])){
	$canDiff = true;
}
?>
<link href="/dbc/css/dbc.css?v=<?=filemtime("/var/www/wow.tools/dbc/css/dbc.css")?>" rel="stylesheet">
<div class="container-fluid">
	<select id='fileFilter' class='form-control form-control-sm'>
		<option value="">Select a table</option>
		<?php foreach($tables as $table){ ?>
			<option value='<?=$table['name']?>' <? if(!empty($_GET['dbc']) && $_GET['dbc'] == $table['name']){ echo " SELECTED"; } ?>><?=$table['displayName']?></option>
		<?php }?>
	</select>
	<?php if(!empty($currentDB)){ ?>
		<form id='dbcform' action='/dbc/diff.php' method='GET'>
			<input type='hidden' name='dbc' value='<?=$_GET['dbc']?>'>
			<label for='oldbuild' class='' style='float: left; padding-left: 15px;'>Old </label>
			<select id='oldbuild' name='old' class='form-control form-control-sm buildFilter'>
				<?php
				$vq = $pdo->prepare("SELECT * FROM wow_dbc_table_versions LEFT JOIN wow_builds ON wow_dbc_table_versions.versionid=wow_builds.id WHERE wow_dbc_table_versions.tableid = ? AND wow_dbc_table_versions.hasDefinition = 1 ORDER BY version DESC");
				$vq->execute([$currentDB['id']]);
				$versions = $vq->fetchAll();
				foreach($versions as $row){
					?>
					<option value='<?=$row['version']?>'<?php if(!empty($_GET['old']) && $row['version'] == $_GET['old']){ echo " SELECTED"; }?>><?=$row['version']?></option>
					<?php
				}
				?>
			</select>
			<label for='newbuild' class='' style='float: left; padding-left: 15px;'> New </label>
			<select id='newbuild' name='new' class='form-control form-control-sm buildFilter'>
				<?php
				foreach($versions as $row){?>
					<option value='<?=$row['version']?>'<?php if(!empty($_GET['new']) && $row['version'] == $_GET['new']){ echo " SELECTED"; }?>><?=$row['version']?></option>
					<?php
				}
				?>
			</select>
			<input type='submit' id='browseButton' class='form-control form-control-sm btn btn-sm btn-primary' value='Diff'>
		</form><br>
		<?php
	}
	?>
	<div id='tableContainer'></div>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/pagination/input.js" crossorigin="anonymous"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>
<script src="/dbc/js/dbc.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/dbc.js")?>"></script>
<script src="/js/diff_match_patch.js?v=<?=filemtime("/var/www/wow.tools/js/diff_match_patch.js")?>"></script>
<script type='text/javascript'>
$(function() {
	$('#fileFilter').select2();
});
	<?php if($canDiff){ ?>
		$(function() {

	var oldBuild = $("#oldbuild option:selected").text();
	var newBuild = $("#newbuild option:selected").text();
	var dataURL = "/api/diff?name=<?=$currentDB['name']?>&build1=" + oldBuild + "&build2=" + newBuild;
	var header1URL = "/api/header/<?=$currentDB['name']?>/?build=" + oldBuild;
	var header2URL = "/api/header/<?=$currentDB['name']?>/?build=" + newBuild;

	$.when($.getJSON(header1URL), $.getJSON(header2URL)).then(function (resp1, resp2) {
	    //this callback will be fired once all ajax calls have finished.
		if(resp1[0]['error'] != null){
			alert("An error occured on the server:\n" + resp1[0]['error']);
		}

		if(resp2[0]['error'] != null){
			alert("An error occured on the server:\n" + resp2[0]['error']);
		}
	    var fields = [...new Set([].concat(...resp1[0].headers, ...resp2[0].headers))];
	    var tableHeaders = "";
	    $.each(fields, function(i, val){
	    	tableHeaders += "<th>" + val + "</th>";
	    });

	    $("#tableContainer").empty();
	    $("#tableContainer").append('<table id="dbtable" class="table table-striped table-bordered table-condensed" cellspacing="0" width="100%"><thead><tr>' + tableHeaders + '</tr></thead></table>');
	    $.ajax({
	    	"url": dataURL,
	    	"success": function ( json ) {
	    		$('#dbtable').DataTable({
	    			"data": json['data'],
	    			"pageLength": 25,
	    			"ordering": false,
	    			"bFilter": false,
	    			"pagingType": "input",
	    			"columnDefs": [{
	    				"targets": "_all",
	    				"render":
	    						/*
								Overrides cell rendering in particular the cell's value if there is an applicable diff
								- for Added/Removed, this applies a flat +/- diff snippet
								- for Replaced this applies a html snippet containing diff information
									- for numbers this is a flat '-x+y', for text diff_match_patch is used
									*/
						 function (data, type, row, meta) {

							// grab the formatted field name
							var field = meta.settings.aoColumns[meta.col].sTitle;

							//! USE THIS
							// if an array split out the field and ordinal
							//var match = /^(.*)\[(\d+)\]$/.exec(field);
							var match = false;

							// assign the cell value
							data = match ? row.row[match[1]][match[2]] : row.row[field];

							// only apply on the initial display event for replaced rows that have a diff
							if(type !== 'display' || row.op !== "Replaced" || row.diff === null)
								return data;

							// find and apply the specific diff for this field
							// if no diff is found then return the default data value
							var diff = row.diff.find(x => x.property == field);
							if(!diff) {
								return data;
							}

							// apply the diff html information
							switch(diff.op) {

								case "Added":
								return '<ins class="diff-added">' + diff.currentvalue + '</ins>';
								case "Removed":
								return '<del class="diff-removed">' + diff.currentvalue + '</del>';
								case "Replaced":
								{
									if(!isNaN(diff.previousvalue) && !isNaN(diff.currentvalue)) {
										// for numbers return a fake diff to save on computation
										return '<del class="diff-removed">' + diff.previousvalue + '</del> &rarr; ' +
										'<ins class="diff-added">' + diff.currentvalue + '</ins>';
									} else {
										// for text use diff_match_patch to compute a real diff
										var dmp = new diff_match_patch();
										var dmp_diff = dmp.diff_main(diff.previousvalue, diff.currentvalue);
										dmp.diff_cleanupSemantic(dmp_diff);
										return dmp.diff_prettyHtml(dmp_diff);
									}
								}
							}

							return data;
						},
	    				"defaultContent": ""
	    			}],
	    			"language": {
	    				"emptyTable": "No differences were found"
	    			},
	    			"createdRow":
						/* Overrides row rendering for Added/Removed rows */
						function(ele, row, rowIndex) {
							if(row.op == "Added" || row.op == "Removed") {
								$(ele).addClass(row.op.toLowerCase()); // apply the formatting class
							}
						}
	    		});
	    	},
	    	"dataType": "json"
	    });
	});
});
	<?php } ?>
	$('#fileFilter').on( 'change', function () {
		if($(this).val() != ""){
			document.location = "/dbc/diff.php?dbc=" + $(this).val();
		}
	});
</script>
<?php if(empty($_GET['embed'])){ require_once("../inc/footer.php");} ?>