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
<div class="container-fluid">
	Select a DBC:
	<select id='fileBuildFilter' style='width: 225px; display: inline-block; margin-left: 5px;' class='form-control form-control-sm'>
		<option value="">Select a DBC</option>
		<? foreach($allowedtables as $table){ ?>
			<option value='<?=$table?>' <? if(!empty($_GET['dbc']) && $_GET['dbc'] == $table){ echo " SELECTED"; } ?>><?=$table?></option>
		<? }?>
	</select>
	<br>
	<? if(!empty($id)){ ?>
		<form action='/dbc/diff2.php?dbc' method='GET'>
			<input type='hidden' name='dbc' value='<?=$_GET['dbc']?>'>
			Select first build (older):
			<select id='oldbuild' name='old' style='width: 225px; display: inline-block; margin-left: 5px;'  class='form-control form-control-sm'>
				<?
				foreach($versions as $row){
					?>
					<option value='<?=$row['hash']?>'<? if(!empty($_GET['old']) && $row['hash'] == $_GET['old']){ echo " SELECTED"; }?>><?=$row['description']?></option>
					<?
				}
				?>
			</select><br>
			Select second build (newer):
			<select id='newbuild' name='new' style='width: 225px; display: inline-block; margin-left: 5px;' class='form-control form-control-sm'>
				<?
				foreach($versions as $row){?>
					<option value='<?=$row['hash']?>'<? if(!empty($_GET['new']) && $row['hash'] == $_GET['new']){ echo " SELECTED"; }?>><?=$row['description']?></option>
					<?
				}
				?>
			</select><br>
			<input type='submit' class='form-control form-control-sm btn btn-primary' style='width: 100px; display: inline-block; margin-left: 5px;'>
		</form>
		<?
	}
	?>
</div>
<table id="dbtable" class="table table-striped table-bordered table-condensed no-footer" style="width:100%">
	<thead>
		<tr>
			<th>AreaTableID</th>
			<th>CorpseMapID</th>
			<th>Corpse[0]</th>
			<th>Corpse[1]</th>
			<th>CosmeticParentMapID</th>
			<th>Directory</th>
			<th>ExpansionID</th>
			<th>Flags[0]</th>
			<th>Flags[1]</th>
			<th>ID</th>
			<th>InstanceType</th>
			<th>LoadingScreenID</th>
			<th>MapDescription0_lang</th>
			<th>MapDescription1_lang</th>
			<th>MapName_lang</th>
			<th>MapType</th>
			<th>MaxPlayers</th>
			<th>MinimapIconScale</th>
			<th>ParentMapID</th>
			<th>PvpLongDescription_lang</th>
			<th>PvpShortDescription_lang</th>
			<th>TimeOfDayOverride</th>
			<th>TimeOffset</th>
			<th>WindSettingsID</th>
		</tr>
	</thead>
</table>
<link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/pagination/input.js" crossorigin="anonymous"></script>
<script src="/js/diff_match_patch.js"></script>

<style>
	.added {
		background: var(--diff-added-color) !important;
		text-decoration: underline;
	}

	.removed {
		background: var(--diff-removed-color) !important;
		text-decoration: line-through;
	}
</style>
<script type='text/javascript'>
	function makeBuild(text){
		if(text == null){
			return "";
		}

		var rawdesc = text.replace("WOW-", "");
		var build  = rawdesc.substring(0, 5);

		var rawdesc = rawdesc.replace(build, "").replace("patch", "");
		var descexpl = rawdesc.split("_");

		return descexpl[0] + "." + build;
	}
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
				return '<ins style="background: var(--diff-added-color);">' + diff.currentvalue + '</ins>';
				case "Removed":
				return '<del style="background: var(--diff-removed-color);">' + diff.currentvalue + '</del>';
				case "Replaced":
				{
					if(!!Number(diff.previousvalue) && !!Number(diff.currentvalue)) {
						// for numbers return a fake diff to save on computation
						return '<del style="background: var(--diff-removed-color-color);">' + diff.previousvalue + '</del>' +
						'<ins style="background: var(--diff-added-color);">' + diff.currentvalue + '</ins>';
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
	var dataURL = "https://wow.tools/api/diff?name=<?=$dbcname?>&build1=" + oldBuild + "&build2=" + newBuild;
	$.ajax({
		"url": dataURL,
		"success": function ( json ) {
			$('#dbtable').DataTable({
				"data": json['data'],
				"iDisplayLength": 100,
				"bSort": false,
				"bFilter": false,
				"pagingType": "input",
				"columnDefs": [{
					"targets": "_all",
					"render": $.fn.dataTable.render.wowtools_diff_cells(),
					"defaultContent": ""
				}],
				"createdRow": $.fn.dataTable.render.wowtools_diff_rows()
			});
		},
		"dataType": "json"
	});
});
	<?php } ?>
	$('#fileBuildFilter').on( 'change', function () {
		if($(this).val() != ""){
			document.location = "https://wow.tools/dbc/diff2.php?dbc=" + $(this).val();
		}
	});
</script>
<? require_once("../inc/footer.php"); ?>