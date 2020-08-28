<?php
require_once(__DIR__ . "/../inc/header.php");

// Map old URL to new url for backwards compatibility
if(!empty($_GET['bc'])){
	$bcq = $pdo->prepare("SELECT description FROM wow_buildconfig WHERE hash = ?");
	$bcq->execute([$_GET['bc']]);
	$row = $bcq->fetch();

	if(!empty($row)){
		$build = parseBuildName($row['description'])['full'];
		$newurl = str_replace("bc=".$_GET['bc'], "build=".$build, $_SERVER['REQUEST_URI']);
		$newurl = str_replace(".db2", "", $newurl);
		echo "<meta http-equiv='refresh' content='0; url=https://wow.tools".$newurl."'>";
		die();
	}
}else if(!empty($_GET['dbc']) && strpos($_GET['dbc'], "db2") !== false){
	$newurl = str_replace(".db2", "", $_SERVER['REQUEST_URI']);
	echo "<meta http-equiv='refresh' content='0; url=https://wow.tools".$newurl."'>";
	die();
}

$tables = [];

foreach($pdo->query("SELECT * FROM wow_dbc_tables ORDER BY name ASC") as $dbc){
	$tables[$dbc['id']] = $dbc;
	if(!empty($_GET['dbc']) && strtolower($_GET['dbc']) == $dbc['name']) $currentDB = $dbc;
}

$locales = [
	["name" => "Korean", "value" => "koKR"],
	["name" => "French", "value" => "frFR"],
	["name" => "German", "value" => "deDE"],
	["name" => "Simplified Chinese", "value" => "zhCN"],
	["name" => "Spanish", "value" => "esES"],
	["name" => "Taiwanese Mandarin", "value" => "zhTW"],
	["name" => "English", "value" => "enGB"],
	["name" => "Mexican Spanish", "value" => "esMX"],
	["name" => "Russian", "value" => "ruRU"],
	["name" => "Brazilian Portugese", "value" => "ptBR"],
	["name" => "Italian", "value" => "itIT"],
	["name" => "Portugese", "value" => "ptPT"],
];

$dbFound = false;
?>
<link href="/dbc/css/dbc.css?v=<?=filemtime("/var/www/wow.tools/dbc/css/dbc.css")?>" rel="stylesheet">
<div class="container-fluid">
	<select id='fileFilter' class='form-control form-control-sm'>
		<option value="">Select a table</option>
		<?php foreach($tables as $table){ ?>
			<option value='<?=$table['name']?>' <? if(!empty($_GET['dbc']) && strtolower($_GET['dbc']) == $table['name']){ echo " SELECTED"; } ?>><?=$table['displayName']?></option>
		<?php }?>
	</select>
	<?php if(!empty($currentDB)){ ?>
		<form id='dbcform' action='/dbc/' method='GET'>
			<input type='hidden' name='dbc' value='<?=$_GET['dbc']?>'>
			<select id='buildFilter' name='build' class='form-control form-control-sm buildFilter'>
				<?php
				$vq = $pdo->prepare("SELECT * FROM wow_dbc_table_versions LEFT JOIN wow_builds ON wow_dbc_table_versions.versionid=wow_builds.id WHERE wow_dbc_table_versions.tableid = ?  AND wow_dbc_table_versions.hasDefinition = 1 ORDER BY version DESC");
				$vq->execute([$currentDB['id']]);
				$versions = $vq->fetchAll();
				foreach($versions as $row){
					?>
					<option value='<?=$row['version']?>'<? if(!empty($_GET['build']) && $row['version'] == $_GET['build']){ echo " SELECTED"; }?>><?=$row['version']?></option>
					<?php
				}
				?>
			</select>

			<select id='localeSelection' name='locale' class='form-control form-control-sm buildFilter'>
				<option value='' <? if(empty($_GET['locale']) || $_GET['locale'] == ""){ echo " SELECTED"; }?>>enUS (Default)</option>
				<?php foreach($locales as $locale){ ?>
					<option value='<?=$locale['value']?>' <? if(!empty($_GET['locale']) && $_GET['locale'] == $locale['value']){ echo " SELECTED"; }?>><?=$locale['name']?></option>
				<?php } ?>
			</select>

			<input type='submit' id='browseButton' class='form-control form-control-sm btn btn-sm btn-primary' value='Browse'>

			<a href='' id='downloadCSVButton' class='form-control form-control-sm btn btn-sm btn-secondary'><i class='fa fa-download'></i> CSV</a>

			<label class="btn btn-sm btn-info active" style='margin-left: 5px;'>
				<input type="checkbox" autocomplete="off" id="hotfixToggle" <?php if(!empty($_GET['hotfixes'])){?>CHECKED<?php } ?>> Use hotfixes?
			</label>
			<a style='vertical-align: top;' class='btn btn-secondary btn-sm' data-toggle='modal' href='' data-target='#settingsModal'>Settings</a>
		</form><br>
	<?php } ?>
		<div id='tableContainer'><br>
			<table id='dbtable' class="table table-striped table-bordered table-condensed" cellspacing="0" width="100%">
				<thead>
				</thead>
				<tbody>
					<tr><td style='text-align: center' id='loadingMessage'>Select a table in the dropdown above</td></tr>
				</tbody>
			</table>
		</div>
</div>
<div class="modal" id="settingsModal" tabindex="-1" role="dialog" aria-labelledby="settingsModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="settingsModalLabel">Settings</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="settingsModalContent">
				<input type="checkbox" autocomplete="off" id="tooltipToggle" CHECKED> <label for='tooltipToggle'>Enable tooltips?</label><br>
				<input type="checkbox" autocomplete="off" id="alwaysEnableFilters" CHECKED> <label for='alwaysEnableFilters'>Always show filters?</label><br>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" onclick="saveSettings();" data-dismiss="modal">Save</button>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="fkModal" tabindex="-1" role="dialog" aria-labelledby="fkModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="fkModalLabel">Foreign key lookup</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="fkModalContent">
				<i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="moreInfoModal" tabindex="-1" role="dialog" aria-labelledby="moreInfoModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="moreInfoModalLabel">More information</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="moreInfoModalContent">
				<i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="previewModalLabel">Preview</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="previewModalContent">
				<i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="chashModal" tabindex="-1" role="dialog" aria-labelledby="chashModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="chashModalLabel">Content hash lookup</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="chashModalContent">
				<i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="flagModal" tabindex="-1" role="dialog" aria-labelledby="flagModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="flagModalLabel">Flag viewer</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="flagModalContent">
				<i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>
<script src="/files/js/files.js" crossorigin="anonymous"></script>
<script src="/dbc/js/dbc.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/dbc.js")?>"></script>
<script src="/dbc/js/flags.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/flags.js")?>"></script>
<script src="/dbc/js/enums.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/enums.js")?>"></script>
<script type='text/javascript'>
	var Settings =
	{
	    filtersAlwaysEnabled: false,
	    filtersCurrentlyEnabled: false,
	    enableTooltips: true
	}

	function saveSettings(){
	    if(document.getElementById("tooltipToggle").checked){
	        localStorage.setItem('settings[tooltipToggle]', '1');
	    }else{
	        localStorage.setItem('settings[tooltipToggle]', '0');
	    }

	    if(document.getElementById("alwaysEnableFilters").checked){
	        localStorage.setItem('settings[alwaysEnableFilters]', '1');
	    }else{
	        localStorage.setItem('settings[alwaysEnableFilters]', '0');
	    }
	}

	function loadSettings(){
	    /* Enable tooltips? */
	    var tooltipToggle = localStorage.getItem('settings[tooltipToggle]');
	    if(tooltipToggle){
	        if(tooltipToggle== "1"){
	            Settings.enableTooltips = true;
	        }else{
	            Settings.enableTooltips = false;
	        }
	    }

	    document.getElementById("tooltipToggle").checked = Settings.enableTooltips;

	    /* Filters always enabled? */
	    var alwaysEnableFilters = localStorage.getItem('settings[alwaysEnableFilters]');
	    if(alwaysEnableFilters){
	        if(alwaysEnableFilters== "1"){
	            Settings.filtersAlwaysEnabled = true;
	        }else{
	            Settings.filtersAlwaysEnabled = false;
	        }
	    }

	    document.getElementById("alwaysEnableFilters").checked = Settings.filtersAlwaysEnabled;
	}

	loadSettings();

	function toggleFilters(){
		console.log("Toggling filters from " + Settings.filtersCurrentlyEnabled + " to " + !Settings.filtersCurrentlyEnabled);
		if(!Settings.filtersCurrentlyEnabled){
			$("#tableContainer thead tr").clone(true).appendTo("#tableContainer thead");
			$("#tableContainer thead tr:eq(1) th").each( function (i) {
				var title = $(this).text();
				$(this).html( '<input type="text"/>' );

				$( 'input', this ).on( 'keyup change', function () {
					if ( $('#dbtable').DataTable().column(i).search() !== this.value ) {
						$('#dbtable').DataTable().column(i).search(this.value).draw();
					}
				} );

				$('input', this).on('click', function(e) {
					e.stopPropagation();
				});
			} );
			Settings.filtersCurrentlyEnabled = true;
		}else{
			$("#tableContainer thead tr:eq(1)").remove();
			Settings.filtersCurrentlyEnabled = false;
		}
	}

	function buildURL(currentParams){
		let url = "https://wow.tools/dbc/";

		if(currentParams["dbc"]){
			url += "?dbc=" + currentParams["dbc"];
		}

		if(currentParams["build"]){
			url += "&build=" + currentParams["build"];
		}

		if(currentParams["locale"]){
			url += "&locale=" + currentParams["locale"];
		}

		if(currentParams["hotfixes"]){
			url += "&hotfixes=" + currentParams["hotfixes"];
		}

		return url;
	}
	let build;
	(function() {
		$('#fileFilter').select2();
		let vars = {};
		let parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
			if(value.includes('#')){
				const splitString = value.split('#');
				vars[key] = splitString[0];
			}else{
				vars[key] = value;
			}
		});

		let currentParams = [];

		if(vars["dbc"] == null){
			currentParams["dbc"] = "";
		}else{
			currentParams["dbc"] = vars["dbc"].replace(".db2", "").toLowerCase().split('#')[0];
		}

		if(vars["locale"] == null){
			currentParams["locale"] = "";
		}else{
			currentParams["locale"] = vars["locale"];
		}

		if($('#buildFilter').val() != undefined && $('#buildFilter').val() != ''){
			currentParams["build"] = $('#buildFilter').val();
		}

		currentParams["hotfixes"] = false;
		if(vars["hotfixes"] == "true"){
			currentParams["hotfixes"] = true;
		}

		$('#fileFilter').on( 'change', function () {
			if($(this).val() != ""){
				currentParams["dbc"] = $(this).val();
				document.location = buildURL(currentParams);
			}
		});

		$('#buildFilter').on('change', function(){
			currentParams["build"] = $('#buildFilter').val();
			document.location = buildURL(currentParams);
		});

		$('#localeSelection').on('change', function(){
			currentParams["locale"] = $('#localeSelection').val();
			document.location = buildURL(currentParams);
		});

		$('#hotfixToggle').on('change', function(){
			if(document.getElementById('hotfixToggle').checked){
				currentParams["hotfixes"] = true;
			}else{
				currentParams["hotfixes"] = false;
			}
			document.location = buildURL(currentParams);
		});

		if(!currentParams["dbc"] || !currentParams["build"]){
			// Don't bother doing anything else if no DBC is selected
			return;
		}

		build = currentParams["build"];

		document.getElementById('downloadCSVButton').href = buildURL(currentParams).replace("/dbc/?dbc=", "/dbc/api/export/?name=");

		$("#loadingMessage").html("Loading..");

		let apiArgs = currentParams["dbc"] + "/?build=" + currentParams["build"];

		if(currentParams["locale"] != ""){
			 apiArgs += "&locale=" + currentParams["locale"];
		}

		if(currentParams["hotfixes"]){
			apiArgs += "&useHotfixes=true";
			document.getElementById('downloadCSVButton').href = document.getElementById('downloadCSVButton').href.replace("&hotfixes=", "&useHotfixes=");
		}

		let tableHeaders = "";
		let idHeader = 0;

		$.ajax({
			"url": "/dbc/api/header/" + apiArgs,
			"success": function(json) {
				if(json['error'] != null){
					if(json['error'] == "No valid definition found for this layouthash or build!"){
						json['error'] += "\n\nPlease open an issue on the WoWDBDefs repository with the DBC name and selected version on GitHub to request a definition for this build.\n\nhttps://github.com/wowdev/WoWDBDefs";
					}
					$("#loadingMessage").html("<div class='alert '><b>Whoops, something exploded while loading this DBC</b><br>It is possible this is due to maintenance or an issue with reading the DBC file itself. Please try again later or report the below error (together with the table name and version) in Discord if it persists. Thanks!</p><p style='margin: 5px;'><kbd>" + json['error'] + "</kbd></p></div>");
					return;
				}
				let allCols = [];
				$.each(json['headers'], function(i, val){
					tableHeaders += "<th style='white-space: nowrap' ";
					if(val in json['comments']){
						tableHeaders += "title='" + json['comments'][val] +"' class='colHasComment'>";
					}else{
						tableHeaders += ">";
					}

					if(val in json['relationsToColumns']){
						tableHeaders += " <i class='fa fa-reply' style='font-size: 10px' title='The following tables point to this column: " + json['relationsToColumns'][val].join(", ") + "'></i> ";
					}

					tableHeaders += val;

					if(val.startsWith("Field_")){
						tableHeaders += " <i class='fa fa-question' style='color: red; font-size: 12px' title='This column is not yet named'></i> ";
					}else if(json['unverifieds'].includes(val)){
						tableHeaders += " <i class='fa fa-question' style='font-size: 12px' title='This column name is not verified to be 100% accurate'></i> ";
					}

					if(val in json['fks']){
						tableHeaders += " <i class='fa fa-share' style='font-size: 10px' title='This column points to " + json['fks'][val] + "'></i> ";
					}

					tableHeaders += "</th>";

					if(val == "ID"){
						idHeader = i;
					}
					allCols.push(i);
				});

				const fkCols = getFKCols(json['headers'], json['fks']);
				$("#tableContainer").empty();
				$("#tableContainer").append('<table id="dbtable" class="table table-striped table-bordered table-condensed" cellspacing="0" width="100%"><thead><tr>' + tableHeaders + '</tr></thead></table>');

				let searchHash = location.hash.substr(1),
				searchString = searchHash.substr(searchHash.indexOf('search=')).split('&')[0].split('=')[1];

				if(searchString != undefined && searchString.length > 0){
					searchString = decodeURIComponent(searchString);
				}

				const page = (parseInt(searchHash.substr(searchHash.indexOf('page=')).split('&')[0].split('=')[1], 10) || 1) - 1;
				let highlightRow = parseInt(searchHash.substr(searchHash.indexOf('row=')).split('&')[0].split('=')[1], 10) - 1;
				$.fn.dataTable.ext.errMode = 'none';
				$('#dbtable').on( 'error.dt', function ( e, settings, techNote, message ) {
					console.log( 'An error occurred: ', message );
				});
				var table = $('#dbtable').DataTable({
					"processing": true,
					"serverSide": true,
					"ajax": {
						url: "/dbc/api/data/" + apiArgs,
						type: "POST",
						beforeSend: function() {
							if (table && table.hasOwnProperty('settings')) {
								// table.settings()[0].jqXHR.abort();
							}
						},
						"data": function( result ) {
							return result;
						}
					},
					"pageLength": 25,
					"displayStart": page * 25,
					"autoWidth": true,
					"pagingType": "input",
					"orderMulti": false,
					"ordering": true,
					"order": [], // Sets default order to nothing (as returned by backend)
					"language": { "search": "<a class='btn btn-dark btn-sm btn-outline-primary' href='#' onClick='toggleFilters()' style='margin-right: 10px'>Toggle filters</a> Search: _INPUT_ " },
					"search": { "search": searchString },
					"columnDefs": [
					{
						"targets": allCols,
						"render": function ( data, type, full, meta ) {
							let returnVar = full[meta.col];
							const columnWithTable = currentParams["dbc"] + '.' + json["headers"][meta.col];

							if(meta.col in fkCols){
								if(fkCols[meta.col] == "FileData::ID"){
									returnVar = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' data-toggle='modal' data-target='#moreInfoModal' onclick='fillModal(" + full[meta.col] + ")'>" + full[meta.col] + "</a>";
								}else if(fkCols[meta.col] == "SoundEntries::ID" && parseInt(currentParams["build"][0]) > 6){
									returnVar = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' data-toggle='modal' data-target='#fkModal' onclick='openFKModal(" + full[meta.col] + ", \"SoundKit::ID\",\"" + $("#buildFilter").val() + "\")'>" + full[meta.col] + "</a>";
								}else if(fkCols[meta.col] == "Item::ID" && full[meta.col] > 0){
									returnVar = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' data-tooltip='item' data-id='" + full[meta.col] + "' ontouchstart='showTooltip(this)' ontouchend='hideTooltip(this)' onmouseover='showTooltip(this)' onmouseout='hideTooltip(this)' data-toggle='modal' data-target='#fkModal' onclick='openFKModal(" + full[meta.col] + ", \"" + fkCols[meta.col] + "\", \"" + $("#buildFilter").val() + "\")'>" + full[meta.col] + "</a>";
								}else if(fkCols[meta.col] == "QuestV2::ID" && full[meta.col] > 0){
									returnVar = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' data-tooltip='quest' data-id='" + full[meta.col] + "' ontouchstart='showTooltip(this)' ontouchend='hideTooltip(this)' onmouseover='showTooltip(this)' onmouseout='hideTooltip(this)' data-toggle='modal' data-target='#fkModal' onclick='openFKModal(" + full[meta.col] + ", \"" + fkCols[meta.col] + "\", \"" + $("#buildFilter").val() + "\")'>" + full[meta.col] + "</a>";
								}else if(fkCols[meta.col] == "Creature::ID" && full[meta.col] > 0){
									returnVar = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' data-tooltip='creature' data-id='" + full[meta.col] + "' ontouchstart='showTooltip(this)' ontouchend='hideTooltip(this)' onmouseover='showTooltip(this)' onmouseout='hideTooltip(this)' data-toggle='modal' data-target='#fkModal' onclick='openFKModal(" + full[meta.col] + ", \"" + fkCols[meta.col] + "\", \"" + $("#buildFilter").val() + "\")'>" + full[meta.col] + "</a>";
								}else if(fkCols[meta.col] == "Spell::ID" && full[meta.col] > 0){
									returnVar = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' data-tooltip='spell' data-id='" + full[meta.col] + "' ontouchstart='showTooltip(this)' ontouchend='hideTooltip(this)' onmouseover='showTooltip(this)' onmouseout='hideTooltip(this)' data-toggle='modal' data-target='#fkModal' onclick='openFKModal(" + full[meta.col] + ", \"" + fkCols[meta.col] + "\", \"" + $("#buildFilter").val() + "\")'>" + full[meta.col] + "</a>";
								}else{
									returnVar = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' data-tooltip='fk' data-id='" + full[meta.col] + "' data-fk='" + fkCols[meta.col] + "' data-toggle='modal' data-target='#fkModal' ontouchstart='showTooltip(this)' ontouchend='hideTooltip(this)' onmouseover='showTooltip(this)' onmouseout='hideTooltip(this)' onclick='openFKModal(" + full[meta.col] + ", \"" + fkCols[meta.col] + "\", \"" + $("#buildFilter").val() + "\")'>" + full[meta.col] + "</a>";
								}
							}else if(json["headers"][meta.col].startsWith("Flags") || flagMap.has(columnWithTable)){
								returnVar = "<span style='padding-top: 0px; padding-bottom: 0px; cursor: help; border-bottom: 1px dotted;' data-trigger='hover' data-container='body' data-html='true' data-toggle='popover' data-content='" + fancyFlagTable(getFlagDescriptions(currentParams["dbc"], json["headers"][meta.col], full[meta.col])) + "'>0x" + dec2hex(full[meta.col]) + "</span>";
							}else if(columnWithTable == "item.ID"){
								returnVar = "<span style='padding-top: 0px; padding-bottom: 0px; cursor: help; border-bottom: 1px dotted;' data-tooltip='item' data-id='" + full[meta.col] + "' ontouchstart='showTooltip(this)' ontouchend='hideTooltip(this)' onmouseover='showTooltip(this)' onmouseout='hideTooltip(this)'>" + full[meta.col] + "</span>";
							}else if(columnWithTable == "spell.ID"){
								returnVar = "<span style='padding-top: 0px; padding-bottom: 0px; cursor: help; border-bottom: 1px dotted;' data-tooltip='spell' data-id='" + full[meta.col] + "' ontouchstart='showTooltip(this)' ontouchend='hideTooltip(this)' onmouseover='showTooltip(this)' onmouseout='hideTooltip(this)'>" + full[meta.col] + "</span>";
							}

							if(enumMap.has(columnWithTable)){
								var enumVal = getEnum(vars["dbc"].toLowerCase(), json["headers"][meta.col], full[meta.col]);
								if(full[meta.col] == '0' && enumVal == "Unk"){
									// returnVar += full[meta.col];
								}else{
									returnVar += " <i>(" + enumVal + ")</i>";
								}
							}

							if(conditionalFKs.has(columnWithTable)){
								let conditionalFK = conditionalFKs.get(columnWithTable);
								conditionalFK.forEach(function(conditionalFKEntry){
									let condition = conditionalFKEntry[0].split('=');
									let conditionTarget = condition[0].split('.');
									let conditionValue = condition[1];
									let resultTarget = conditionalFKEntry[1];

									let colTarget = json["headers"].indexOf(conditionTarget[1]);

									// Col target found?
									if(colTarget > -1){
										if(full[colTarget] == conditionValue){
											returnVar = "<a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' data-toggle='modal' data-target='#fkModal' onclick='openFKModal(" + full[meta.col] + ", \"" + resultTarget + "\", \"" + $("#buildFilter").val() + "\")'>" + full[meta.col] + "</a>";
										}
									}
								});
							}

							if(conditionalEnums.has(columnWithTable)){
								let conditionalEnum = conditionalEnums.get(columnWithTable);
								conditionalEnum.forEach(function(conditionalEnumEntry){
									let condition = conditionalEnumEntry[0].split('=');
									let conditionTarget = condition[0].split('.');
									let conditionValue = condition[1];
									let resultEnum = conditionalEnumEntry[1];

									let colTarget = json["headers"].indexOf(conditionTarget[1]);

									// Col target found?
									if(colTarget > -1){
										if(full[colTarget] == conditionValue){
											var enumVal = getEnumVal(resultEnum, full[meta.col]);
											if(full[meta.col] == '0' && enumVal == "Unk"){
												returnVar = full[meta.col];
											}else{
												returnVar = full[meta.col] + " <i>(" + enumVal + ")</i>";
											}
										}
									}
								});
							}

							if(conditionalFlags.has(columnWithTable)){
								let conditionalFlag = conditionalFlags.get(columnWithTable);
								conditionalFlag.forEach(function(conditionalFlagEntry){
									let condition = conditionalFlagEntry[0].split('=');
									let conditionTarget = condition[0].split('.');
									let conditionValue = condition[1];
									let resultFlag = conditionalFlagEntry[1];

									let colTarget = json["headers"].indexOf(conditionTarget[1]);

									// Col target found?
									if(colTarget > -1){
										if(full[colTarget] == conditionValue){
											returnVar = "<span style='padding-top: 0px; padding-bottom: 0px; cursor: help; border-bottom: 1px dotted;' data-trigger='hover' data-container='body' data-html='true' data-toggle='popover' data-content='" + getFlagDescriptions(currentParams["dbc"], json["headers"][meta.col], full[meta.col], resultFlag).join(",<br> ") + "'>0x" + dec2hex(full[meta.col]) + "</span>";
										}
									}
								});
							}

							if(colorFields.includes(columnWithTable)){
								returnVar = "<div style='display: inline-block; border: 2px solid black; height: 19px; width: 19px; background-color: " + BGRA2RGBA(full[meta.col]) + "'>&nbsp;</div> " + full[meta.col];
							}

							return returnVar;
						}
					}],
					"createdRow": function( row, data, dataIndex ) {
						if(dataIndex == highlightRow){
							$(row).addClass('highlight');
							highlightRow = -1;
						}
					},
				});

				$('#dbtable').on ('init.dt', function () {
					if(Settings.filtersAlwaysEnabled){ toggleFilters(); };
				});

				$('#dbtable').on( 'draw.dt', function () {
					window.history.pushState('dbc', 'WoW.Tools | Database browser', buildURL(currentParams));

					let currentPage = $('#dbtable').DataTable().page() + 1;
					let hashPart = "page=" + currentPage;

					const currentSearch = encodeURIComponent($("#dbtable_filter label input").val());
					if(currentSearch != ""){
						hashPart += "&search=" + currentSearch;
					}

					window.location.hash = hashPart;

					$('.popover').remove();

					$("[data-toggle=popover]").popover({sanitize: false});
				});

			},
			"dataType": "json"
		});
	}());
</script>
<?php require_once(__DIR__ . "/../inc/footer.php"); ?>