<?
include("../inc/config.php");
// include("inc/functions.php");
ini_set('memory_limit','1G');
include("../inc/header.php");
?>
<div class="container-fluid">
	<?
	/*
	$arr = $pdo->query("SELECT ".$_SESSION['product']."_versions.buildconfig, ".$_SESSION['product']."_buildconfig.description FROM ".$_SESSION['product']."_versions LEFT OUTER JOIN ".$_SESSION['product']."_buildconfig ON ".$_SESSION['product']."_versions.buildconfig=".$_SESSION['product']."_buildconfig.hash ORDER BY ".$_SESSION['product']."_buildconfig.description")->fetchAll();
	<div class="alert alert-info" role="alert">
		<h4 class="alert-heading">Packages</h4>
		<p>I'm currently testing a script that compiles all of the install files for a certain build, OS and architecture and packages them up. You can try it out below.</p>
		<p class="mb-0">
			<form method='GET' action='/scripts/downloadBuild.php'>
				<select name='build'>
					<?foreach($arr as $row){?>
					<option value='<?=$row['buildconfig']?>'<? if($row['buildconfig'] == $_GET['build']){ echo " SELECTED"; }?>><?=$row['description']?></option>
					<?}?>
				</select>
				<select name='os'>
					<option value='Windows'>Windows</option>
					<option value='OSX'>OSX</option>
					<option value='Web'>Web</option>
				</select>
				<select name='arch'>
					<option value='x86_32'>x86_32</option>
					<option value='x86_64'>x86_64</option>
				</select>
				<input type='submit' value='Download'>
			</form>
			<br><b>Keep in mind this is highly experimental and there's a good chance that it will time out.</b>
		</p>
	</div>
	<h3>Downloading specific files manually</h3>
	<p>Select a build to continue.</p>
	<form action='extract.php' method='GET'>
		<select name='build'>
			<?foreach($arr as $row){?>
			<option value='<?=$row['buildconfig']?>'<? if($row['buildconfig'] == $_GET['build']){ echo " SELECTED"; }?>><?=$row['description']?></option>
			<?}?>
		</select>
		<input type='hidden' name='type' value='install'>
		<input type='submit'>
	</form>

	*/

	?>
<!-- <div class="alert alert-info" role="alert">
		<h4 class="alert-heading">Packages</h4>
		<p>Packages are currently disabled due to their popularity, they weren't really meant to be used by the general public. Sorry!</p>
</div> -->
	<?
	if(!empty($_GET['build'])){
		$build = getVersionByBuildConfigHash($_GET['build']);

		if(empty($build)){
			die("Invalid buildconfig specified!");
		}

		$output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll dumpinstall wow ".$build['buildconfig']['install_cdn']." | sort");

		echo "<h3>Install dump for ".$build['buildconfig']['description']."</h3>";

		$lines = explode("\n", $output);

		$buildn = substr($build['buildconfig']['description'], 4, 5);

		// 18125 - 18761
			// 1 = Arch
			// 2 = Locale
			// 3 = OS
		// 18764 - 20426
			// 1 = Arch
			// 2 = Category
			// 3 = Locale
			// 4 = OS
			// 5 = Region
		// 20438 - now
			// 1 = OS
			// 2 = Arch
			// 3 = Locale
			// 4 = Region
			// 5 = Category

		if($buildn >= 18125 && $buildn <= 18761){
			$headers = array(1 => "Arch", 2 => "Locale", 3 => "OS");
		}elseif($buildn >= 18764 && $buildn <= 20426){
			$headers = array(1 => "Arch", 2 => "Category", 3 => "Locale", 4 => "OS", 5 => "Region");
		}elseif($buildn >= 20438){
			$headers = array(1 => "OS", 2 => "Arch", 3 => "Locale", 4 => "Region", 5 => "Category");
		}

		?>

		<table class="table table-sm table-hover table-striped">
			<thead class="thead-inverse">
				<tr>
					<th>Filename</th>
					<th>Size</th>
					<? foreach($headers as $header){ ?>
					<th><?=$header?></th>
					<? } ?>
					<th>MD5</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?
				foreach($lines as $line){
					if(empty(trim($line))) continue;
					$split1 = explode(" (", $line);
					$split2 = explode (", ", $split1[1]);
					$filename = $split1[0];
					$size = str_replace("size: ", "", $split2[0]);
					$md5 = str_replace("md5: ", "", $split2[1]);
					$tagraw = str_replace("tags: ", "", $split2[2]);
					$tagraw = explode(",", $tagraw);
					$tags = array();
					foreach($tagraw as $tag){
						$exploded = explode("=", $tag);
						$tags[$exploded[0]][] = str_replace(")", "", $exploded[1]);
					}

					echo "<tr>";
					echo "<td>".$filename."</td>";
					echo "<td>".$size."</td>";
					if(!empty($headers)){
						foreach($headers as $key => $header){
							echo "<td>".implode(", ", $tags[$key])."</td>";
						}
					}

					echo "<td><span class='hash'>".$md5."</span></td>";
					echo "<td><a href='https://wow.tools/casc/file/chash?contenthash=".$md5."&buildconfig=".$build['buildconfig']['hash']."&cdnconfig=".$build['cdnconfig']['hash']."&filename=".basename(str_replace("\\", "//", $filename))."'>Download</a>";
					echo "</tr>";
				}
				?>
			</tbody>
		</table>
		<?
	}
	?>
</div>
<? include "../inc/footer.php"; ?>