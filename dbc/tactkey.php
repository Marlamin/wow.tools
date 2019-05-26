<?
require_once("../inc/header.php");

$encrypted = [];
$tactkeys = [];

foreach($pdo->query("SELECT keyname, filedataid FROM wow_encrypted") as $row){
	$encrypted[$row['keyname']][] = $row['filedataid'];
}

foreach($pdo->query("SELECT * FROM wow_tactkey") as $row){
	$tactkeys[$row['keyname']] = $row;
}

$q = $pdo->query("SELECT wow_rootfiles_chashes.root_cdn, wow_rootfiles_chashes.contenthash, wow_buildconfig.description, wow_buildconfig.hash, wow_buildconfig.description, wow_versions.cdnconfig FROM wow_rootfiles_chashes JOIN wow_buildconfig ON wow_buildconfig.root_cdn=wow_rootfiles_chashes.root_cdn JOIN wow_versions ON wow_buildconfig.hash=wow_versions.buildconfig WHERE filedataid = 1302851 ORDER BY wow_buildconfig.description DESC");
$versions = array();
while($row = $q->fetch()){
	$rawdesc = str_replace("WOW-", "", $row['description']);
	$build = substr($rawdesc, 0, 5);
	$rawdesc = str_replace(array($build, "patch"), "", $rawdesc);
	$descexpl = explode("_", $rawdesc);
	$row['build'] = $descexpl[0].".".$build;
	if($build >= 25600){
		$versions[] = $row;
	}
}

?>
<div class="container-fluid" style='margin-top: 15px;'>
	<form>
		Select build:
		<select name='bc' id='buildFilter' style='width: 225px; display: inline-block; margin-left: 5px; margin-bottom: 5px;' class='form-control form-control-sm'>
			<?foreach($versions as $row){?>
				<option value='<?=$row['build']?>'<? if(!empty($_GET['bc']) && $row['hash'] == $_GET['bc']){ echo " SELECTED"; }?>><?=$row['description']?></option>
			<? } ?>
		</select>
	</form>
	<div id='output' style='font-family: "Courier New", monospace; white-space: pre;'>

	</div>
	<script type='text/javascript'>
		var tactkeys = <?=json_encode($tactkeys)?>;
		var encrypted = <?=json_encode($encrypted)?>;
		function loadBuild(build){
			var output = new Array();
			$("#output").html("");
			console.log("Loading " + build);

			$.ajax({
				url: "/api/data/tactkey/?build=" + build + "&draw=1&start=0&length=500",
				context: document.body
			}).done(function(tactkeydata) {
				var tactkeydb = new Array();
				console.log(tactkeydata);
				tactkeydata.data.forEach(function(tactkeyentry, tactkeykey){

					var fullkey = "";
					for(var i = 1; i < 17; i++){
						fullkey += "" + parseInt(tactkeyentry[i]).toString(16).padStart(2, '0').toUpperCase();
					}

					tactkeydb[tactkeyentry[0]] = fullkey;
				});

				$.ajax({
					url: "/api/data/tactkeylookup/?build=" + build + "&draw=1&start=0&length=500",
					context: document.body
				}).done(function(data) {
					data.data.forEach(function(entry, key){
						var lookup = "";
						var reversed = entry.reverse();
						for(var i = 0; i < 8; i++){
							lookup += parseInt(reversed[i]).toString(16).padStart(2, '0').toUpperCase();
						};

						entry.reverse();
						if(lookup in tactkeys){
							if(tactkeydb[entry[0]] != null){
								tactkeys[lookup]['keybytes'] = tactkeydb[entry[0]];
							}
							if(tactkeys[lookup]['keybytes'] == null || tactkeys[lookup]['keybytes'] == ""){
								tactkeys[lookup]['keybytes'] = "????????????????????????????????";
							}

							if(tactkeys[lookup]['added'] == null || tactkeys[lookup]['added'] == ""){
								tactkeys[lookup]['added'] = "                       ";
							}

							if(tactkeys[lookup]['description'] == null){
								if(lookup in encrypted){
									if(encrypted[lookup].length > 7){
										tactkeys[lookup]['description'] = "starts at fdid " + encrypted[lookup][0] + ", total of " + encrypted[lookup].length + " fdids";
									}else{
										tactkeys[lookup]['description'] = "fdid " + encrypted[lookup].join(', ');
									}
								}else{
									tactkeys[lookup]['description'] = "";
								}
							}

							output[parseInt(entry[0])] = " " + lookup + "  " + tactkeys[lookup]['keybytes'] + "  salsa20   " + entry[0].padEnd(3, ' ') + " " + tactkeys[lookup]['added'].padEnd(24, ' ') + "  " + tactkeys[lookup]['description'];
						}else{
							var keybytes = "????????????????????????????????";
							if(tactkeydb[entry[0]] != null){
								keybytes = tactkeydb[entry[0]];
							}

							if(lookup in encrypted){
								if(encrypted[lookup].length > 7){
									var desc  = "starts at fdid " + encrypted[lookup][0] + ", total of " + encrypted[lookup].length + " fdids";
								}else{
									var desc  = "fdid " + encrypted[lookup].join(', ');
								}
							}else{
								var desc = "";
							}
							output[parseInt(entry[0])] = " " + lookup + "  " + keybytes + "  salsa20   " + entry[0].padEnd(3, ' ') + "                           " + desc;
						}
					});

					for(var i = 0; i < Object.keys(tactkeys).length; i++){
						var values = Object.values(tactkeys)[i];
						if(!(values.id in output)){
							if(tactkeydb[values.id] != undefined){
								values.keybytes = tactkeydb[values.id];
							}else if(values.keybytes == null || values.keybytes == ""){
								values.keybytes = "????????????????????????????????";
							}

							if(values.added == null || values.added == ""){
								values.added = "                       ";
							}

							output[values.id] = " " + values.keyname + "  " + values.keybytes + "  salsa20   " + values.id.toString().padEnd(3, ' ') + " " + values.added.padEnd(24, ' ') + "  " + values.description;
						}
					}

					output.forEach(function(line){
						$("#output").append(line + "\n");
					});
				});
			});


		}

		loadBuild($("#buildFilter").val());

		$('#buildFilter').on('change', function() {
			loadBuild(this.value);
		});
	</script>
</div>
<?
require_once("../inc/footer.php");