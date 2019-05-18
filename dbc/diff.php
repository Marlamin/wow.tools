<?php
require_once("../inc/header.php");

$query = $pdo->query("SELECT id, filename FROM wow_rootfiles WHERE filename LIKE 'dbfilesclient%.db2'");

$allowedtables = array();
while($row = $query->fetch()){
	$allowedtables[] = str_replace("dbfilesclient/", "", $row['filename']);
	if(!empty($_GET['dbc']) && "dbfilesclient/".$_GET['dbc'] == $row['filename']){
		$id = $row['id'];
	}
}

if(!empty($id)){
	$query = $pdo->prepare("SELECT wow_rootfiles_chashes.root_cdn, wow_rootfiles_chashes.contenthash, wow_buildconfig.description, wow_buildconfig.hash, wow_buildconfig.description, wow_versions.cdnconfig FROM wow_rootfiles_chashes JOIN wow_buildconfig ON wow_buildconfig.root_cdn=wow_rootfiles_chashes.root_cdn JOIN wow_versions ON wow_buildconfig.hash=wow_versions.buildconfig WHERE filedataid = ? ORDER BY wow_buildconfig.description DESC");
	$query->execute([$id]);
	$versions = array();
	while($row = $query->fetch()){
		$rawdesc = str_replace("WOW-", "", $row['description']);
		$build = substr($rawdesc, 0, 5);
		$rawdesc = str_replace(array($build, "patch"), "", $rawdesc);
		$descexpl = explode("_", $rawdesc);
		$row['build'] = $descexpl[0].".".$build;
		if($build >= 25600){
			$versions[] = $row;
		}

		if(!empty($_GET['old']) && !empty($_GET['new'])){
			if(strlen($_GET['old']) != 32 || !ctype_xdigit($_GET['old'])) die("Invalid old buildconfig!");
			if(strlen($_GET['new']) != 32 || !ctype_xdigit($_GET['new'])) die("Invalid new buildconfig!");
			foreach($versions as $version){
				if($version['hash'] == $_GET['old']){
					$oldbuild = $version['build'];
				}

				if($version['hash'] == $_GET['new']){
					$newbuild = $version['build'];
				}
			}
		}
	}
}

if(!empty($id) && !in_array($_GET['dbc'], $allowedtables)){
	die("Invalid DBC!");
}

$canDiff = false;
if(!empty($id) && !empty($oldbuild) && !empty($newbuild)){
	$dbcname = str_replace(".db2", "", $_GET['dbc']);
	$canDiff = true;
}

?>
<link href="/dbc/css/dbc.css?v=<?=filemtime("/var/www/wow.tools/dbc/css/dbc.css")?>" rel="stylesheet">
<div class="container-fluid">
	<select id='fileFilter' class='form-control form-control-sm'>
		<option value="">Select a table</option>
		<? foreach($allowedtables as $table){ ?>
			<option value='<?=$table?>' <? if(!empty($_GET['dbc']) && $_GET['dbc'] == $table){ echo " SELECTED"; } ?>><?=$table?></option>
		<? }?>
	</select>
	<? if(!empty($id)){ ?>
		<form id='dbcform' action='/dbc/diff.php' method='GET'>
			<input type='hidden' name='dbc' value='<?=$_GET['dbc']?>'>
			<label for='oldbuild' class='' style='float: left; padding-left: 15px;'>Old </label>
			<select id='oldbuild' name='old' class='form-control form-control-sm buildFilter'>
				<?
				foreach($versions as $row){
					?>
					<option value='<?=$row['hash']?>'<? if(!empty($_GET['old']) && $row['hash'] == $_GET['old']){ echo " SELECTED"; }?>><?=$row['description']?></option>
					<?
				}
				?>
			</select>
			<label for='newbuild' class='' style='float: left; padding-left: 15px;'> New </label>
			<select id='newbuild' name='new' class='form-control form-control-sm buildFilter'>
				<?
				foreach($versions as $row){?>
					<option value='<?=$row['hash']?>'<? if(!empty($_GET['new']) && $row['hash'] == $_GET['new']){ echo " SELECTED"; }?>><?=$row['description']?></option>
					<?
				}
				?>
			</select>
			<input type='submit' id='browseButton' class='form-control form-control-sm btn btn-sm btn-primary' value='Diff'>
		</form><br>
		<?
	}
	?>
	<div id='tableContainer'></div>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/pagination/input.js" crossorigin="anonymous"></script>
<script src="/dbc/js/dbc.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/dbc.js")?>"></script>
<script src="/js/diff_match_patch.js?v=<?=filemtime("/var/www/wow.tools/js/diff_match_patch.js")?>"></script>
<script type='text/javascript'>
	<?php if($canDiff){ ?>

		$(function() {

	/*
		Overrides cell rendering in particular the cell's value if there is an applicable diff
		- for Added/Removed, this applies a flat +/- diff snippet
		- for Replaced this applies a html snippet containing diff information
			- for numbers this is a flat '-x+y', for text diff_match_patch is used
			*/
			$.fn.dataTable.render.wowtools_diff_cells = function() {
				return function (data, type, row, meta) {

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
						return '<del class="diff-removed">' + diff.previousvalue + '</del> 🡆 ' +
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
		};
	};

	/* Overrides row rendering for Added/Removed rows */
	$.fn.dataTable.render.wowtools_diff_rows = function() {
		return function(ele, row, rowIndex) {
			if(row.op == "Added" || row.op == "Removed") {
				$(ele).addClass(row.op.toLowerCase()); // apply the formatting class
			}
		};
	};

	var oldBuild = makeBuild($("#oldbuild option:selected").text());
	var newBuild = makeBuild($("#newbuild option:selected").text());
	var dataURL = "/api/diff?name=<?=$dbcname?>&build1=" + oldBuild + "&build2=" + newBuild;
	var header1URL = "/api/header/<?=$dbcname?>/?build=" + oldBuild;
	var header2URL = "/api/header/<?=$dbcname?>/?build=" + newBuild;

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
	    				"render": $.fn.dataTable.render.wowtools_diff_cells(),
	    				"defaultContent": ""
	    			}],
	    			"language": {
	    				"emptyTable": "No differences were found"
	    			},
	    			"createdRow": $.fn.dataTable.render.wowtools_diff_rows()
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
<? require_once("../inc/footer.php"); ?>