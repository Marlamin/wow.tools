<?
require_once("../inc/header.php");

echo "<div class='container-fluid'>Coming soon!</div>";

require_once("../inc/footer.php");
die();

function downloadCSV($file, $build){

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
				/* OLD */
				$olddb2 = "/home/wow/dbcs/".$version['build']."/dbfilesclient/".$_GET['dbc'];
				$oldcsv = "/var/www/bnet.marlam.in/temp/".$_GET['dbc'].".".$version['contenthash'].".csv";
				if(!file_exists($olddb2)){
					$oldoutputextract = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll extractfilebycontenthash wow ".escapeshellarg($version['hash'])." ".escapeshellarg($version['cdnconfig'])." ".escapeshellarg($version['contenthash'])." ".escapeshellarg($olddb2)." 2>&1");
				}

				$oldoutputdump = shell_exec("cd /home/wow/dbcdump; /usr/bin/dotnet DBCDump.dll ".escapeshellarg($olddb2)." ".escapeshellarg($oldcsv)." 2>&1");

				/* NEW */
				$oldcsv = tempnam("/tmp", "dbcdiff");


			}

			if($version['hash'] == $_GET['new']){
				$newdb2 = "/home/wow/dbcs/".$version['build']."/dbfilesclient/".$_GET['dbc'];
				$newcsv = "/var/www/bnet.marlam.in/temp/".$_GET['dbc'].".".$version['contenthash'].".csv";
				if(!file_exists($newdb2)){
					$newoutputextract = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll extractfilebycontenthash wow ".escapeshellarg($version['hash'])." ".escapeshellarg($version['cdnconfig'])." ".escapeshellarg($version['contenthash'])." ".escapeshellarg($newdb2)." 2>&1");
				}

				$newoutputdump = shell_exec("cd /home/wow/dbcdump; /usr/bin/dotnet DBCDump.dll ".escapeshellarg($newdb2)." ".escapeshellarg($newcsv). " 2>&1");
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
		<form action='dbcdiff.php?dbc' method='GET'>
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
		echo "<pre>\n";
		if(!empty($oldoutputextract)){
			echo "<b>Extraction output (old)</b>\n";
			print_r($oldoutputextract);
		}

		if(!empty($oldoutputdump)){
			echo "<b>Dump to CSV output (old)</b>\n";
			print_r($oldoutputdump);
		}

		if(!empty($newoutputextract)){
			echo "<b>Extraction output (new)</b>\n";
			print_r($newoutputextract);
		}

		if(!empty($newoutputdump)){
			echo "<b>Dump to CSV output (new)</b>\n";
			print_r($newoutputdump);
		}
		echo "</pre>";

		if(!empty($diff)){
			echo "<pre><code class='diff'>".$diff."</code></pre>";
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
				document.location = "https://bnet.marlam.in/dbcdiff.php?dbc=" + $(this).val();
			}
		});

		hljs.initHighlightingOnLoad();
	</script>
</div>
<? require_once("../inc/footer.php"); ?>