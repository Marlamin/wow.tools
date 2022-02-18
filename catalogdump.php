<?php
include("inc/header.php");

$arr = $pdo->query("SELECT * FROM catalogs_buildconfig ORDER BY description DESC")->fetchAll();
?>
<div class='container-fluid'>
	<form action='catalogdump.php' method='GET'>
		<select name='build'>
			<?php foreach($arr as $row){?>
			<option value='<?=$row['hash']?>'<? if(!empty($_GET['build']) && $row['hash'] == $_GET['build']){ echo " SELECTED"; }?>><?=$row['description']?></option>
			<?php }?>
		</select>
		<input type='submit'>
	</form>
	<?php
	if(!empty($_GET['build'])){
		foreach($arr as $row){
			if($row['hash'] == $_GET['build']){
				$build = $row;
				break;
			}
		}
	}

	if(empty($build)) die("No valid build selected");

	echo "<pre>";
	// Merge fragment catalogs into main catalogs
	$json = json_decode(file_get_contents("/var/www/wow.tools/tpr/catalogs/data/" . $build['root_cdn'][0] . $build['root_cdn'][1] . "/" . $build['root_cdn'][2] . $build['root_cdn'][3] . "/" . $build['root_cdn']), true);
	foreach($json['fragments'] as $fragment){
		if(doesFileExist("data", $fragment['hash'], "catalogs")){
			$fragmentjson = json_decode(file_get_contents("/var/www/wow.tools/tpr/catalogs/data/" . $fragment['hash'][0] . $fragment['hash'][1] . "/" . $fragment['hash'][2] . $fragment['hash'][3] . "/" . $fragment['hash']), true);
			$fragment['content'] = $fragmentjson;
		}
	}

	if(!empty($json['files']['default'])){
		$curr = current($json['files']['default']);
		if(doesFileExist("data", $curr['hash'], "catalogs")){
			$resourcejson = json_decode(file_get_contents("/var/www/wow.tools/tpr/catalogs/data/" . $curr['hash'][0] . $curr['hash'][1] . "/" . $curr['hash'][2] . $curr['hash'][3] . "/" . $curr['hash']), true);
			$json['files']['default']['content'] = $resourcejson;
		}
	}

	print_r($json);

	?>
</div>
<?php include "inc/footer.php"; ?>
