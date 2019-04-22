<?
require_once("../inc/header.php");

function downloadCSV($file, $build, $outfile){
	$fp = fopen($outfile, 'w+');
	$url = 'http://localhost:5000/api/export/?name=' . str_replace(".db2", "", $file) . '&build=' . $build;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$exec = curl_exec($ch);
	curl_close($ch);
	fclose($fp);

	if($exec){
		return true;
	}else{
		return false;
	}
}

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
	}
}

if(!empty($id) && !in_array($_GET['dbc'], $allowedtables)){
	die("Invalid DBC!");
}

if(!empty($id) && !empty($_GET['old']) && !empty($_GET['new'])){
	if(strlen($_GET['old']) != 32 || !ctype_xdigit($_GET['old'])) die("Invalid old buildconfig!");
	if(strlen($_GET['new']) != 32 || !ctype_xdigit($_GET['new'])) die("Invalid new buildconfig!");

	foreach($versions as $version){
		if($version['hash'] == $_GET['old'] || $version['hash'] == $_GET['new']){
			if($version['hash'] == $_GET['old']){
				$oldcsv = tempnam("/tmp", "dbcdiff");
				downloadCSV($_GET['dbc'], $version['build'], $oldcsv);
			}

			if($version['hash'] == $_GET['new']){
				$newcsv = tempnam("/tmp", "dbcdiff");
				downloadCSV($_GET['dbc'], $version['build'], $newcsv);
			}
		}
	}

	$cmd = "/usr/bin/git diff --no-index ".escapeshellarg($oldcsv)." ".escapeshellarg($newcsv)." | grep -v wow.tools";
	$diff = shell_exec($cmd);
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
		<form action='/dbc/diff.php?dbc' method='GET'>
			<input type='hidden' name='dbc' value='<?=$_GET['dbc']?>'>
			Select first build (older):
			<select name='old' style='width: 225px; display: inline-block; margin-left: 5px;'  class='form-control form-control-sm'>
				<?
				foreach($versions as $row){
					?>
					<option value='<?=$row['hash']?>'<? if(!empty($_GET['old']) && $row['hash'] == $_GET['old']){ echo " SELECTED"; }?>><?=$row['description']?></option>
					<?
				}
				?>
			</select><br>
			Select second build (newer):
			<select name='new' style='width: 225px; display: inline-block; margin-left: 5px;' class='form-control form-control-sm'>
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
	if(!empty($id) && !empty($_GET['old']) && !empty($_GET['new'])){
		if(!empty($diff)){
			echo "<pre style='color: var(--text-color)'><code class='diff'>".$diff."</code></pre>";
		}else{
			echo "Either file contents did not change between builds, or something went wrong with extraction/conversion/diffing (errors should be above). DBC dumping is still a WIP and relies on incomplete definitions. Sorry!";
			if (file_get_contents($oldcsv) == file_get_contents($newcsv)){
				echo "<pre><code>".file_get_contents($oldcsv)."</code></pre>";
			}
		}
	}


	unlink($oldcsv);
	unlink($newcsv);
	?>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/default.min.css">
	<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js"></script>
	<script type='text/javascript'>
		$('#fileBuildFilter').on( 'change', function () {
			if($(this).val() != ""){
				document.location = "https://wow.tools/dbc/diff.php?dbc=" + $(this).val();
			}
		});

		hljs.initHighlightingOnLoad();
	</script>
</div>
<? require_once("../inc/footer.php"); ?>