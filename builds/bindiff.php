<?php
include(__DIR__ . "/../inc/config.php");
include(__DIR__ . "/../inc/header.php");

ini_set('memory_limit','1G');
?>
<div class="container-fluid">
	<?php
	$arr = $pdo->query("SELECT wow_versions.buildconfig, wow_buildconfig.description FROM wow_versions LEFT OUTER JOIN wow_buildconfig ON wow_versions.buildconfig=wow_buildconfig.hash ORDER BY wow_buildconfig.description DESC")->fetchAll();
	?>
	<p>Select a build to continue.</p>
	<form action='bindiff.php' method='GET' class='form-inline'>
		<div class='input-group'>
			<select class='form-control' name='oldbuild'>
				<?php foreach($arr as $row){?>
				<option value='<?=$row['buildconfig']?>'<?php if(!empty($_GET['oldbuild']) && $row['buildconfig'] == $_GET['oldbuild']){ echo " SELECTED"; }?>><?=$row['description']?></option>
				<?php }?>
			</select>
			<div class="input-group-append">
			<span class="input-group-text"> => </span>
			</div>
			<select class='form-control' name='newbuild'>
				<?php foreach($arr as $row){?>
				<option value='<?=$row['buildconfig']?>'<?php if(!empty($_GET['newbuild']) && $row['buildconfig'] == $_GET['newbuild']){ echo " SELECTED"; }?>><?=$row['description']?></option>
				<?php }?>
			</select>
		</div>
		<div class='input-group'>
			<select class='form-control' name='oldfile'>
			<?php
			if(!empty($_GET['oldbuild'])){
				$oldbuild = getVersionByBuildConfigHash($_GET['oldbuild']);

				if(empty($oldbuild)){
					die("Invalid buildconfig specified!");
				}

				$oldoutput = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll dumpinstall wow ".$oldbuild['buildconfig']['install_cdn']." | sort");

				$oldlines = explode("\n", $oldoutput);
				foreach($oldlines as $line){
					if(empty(trim($line))) continue;
					$split1 = explode(" (", $line);
					$split2 = explode (", ", $split1[1]);

					$filename = $split1[0];

					if(substr($filename, -4) != ".exe" && substr($filename, -4) != ".dll" && substr($filename, -17) != "World of Warcraft" && substr($filename, -11) != "MacOS\Agent") continue;

					$size = str_replace("size: ", "", $split2[0]);
					$md5 = str_replace("md5: ", "", $split2[1]);

					echo "<option value='".$filename."' ";
					if(!empty($_GET['oldfile']) && $filename == $_GET['oldfile']){ echo " SELECTED"; }
					echo ">".$filename."</option>";
				}
			}else{
				echo "<option value=''>Click submit to load files</option>";
			}
			?>
			</select>
			<div class="input-group-append">
			<span class="input-group-text"> => </span>
			</div>
			<select class='form-control' name='newfile'>
			<?php
			if(!empty($_GET['newbuild'])){
				$newbuild = getVersionByBuildConfigHash($_GET['newbuild']);

				if(empty($newbuild)){
					die("Invalid buildconfig specified!");
				}

				$newoutput = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll dumpinstall wow ".$newbuild['buildconfig']['install_cdn']." | sort");

				$newlines = explode("\n", $newoutput);
				foreach($newlines as $line){
					if(empty(trim($line))) continue;
					$split1 = explode(" (", $line);
					$split2 = explode (", ", $split1[1]);

					$filename = $split1[0];

					if(substr($filename, -4) != ".exe" && substr($filename, -4) != ".dll" && substr($filename, -17) != "World of Warcraft" && substr($filename, -11) != "MacOS\Agent") continue;

					$size = str_replace("size: ", "", $split2[0]);
					$md5 = str_replace("md5: ", "", $split2[1]);

					echo "<option value='".$filename."' ";
					if(!empty($_GET['newfile']) && $filename == $_GET['newfile']){ echo " SELECTED"; }
					echo ">".$filename."</option>";
				}
			}else{
				echo "<option value=''>Click submit to load files</option>";
			}
			?>
			</select>
	</div>
	<input type='submit' class='form-control btn btn-primary' style='margin-left: 10px;'>
	</form>

	<?php
	if(!empty($_GET['oldbuild']) && !empty($_GET['newbuild'])){
		$oldbuild = getVersionByBuildConfigHash($_GET['oldbuild']);
		$newbuild = getVersionByBuildConfigHash($_GET['newbuild']);

		if(empty($oldbuild) || empty($newbuild)){
			die("Invalid buildconfig specified!");
		}

		$oldoutput = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll dumpinstall wow ".$oldbuild['buildconfig']['install_cdn']." | sort");
		$newoutput = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll dumpinstall wow ".$newbuild['buildconfig']['install_cdn']." | sort");

		$oldlines = explode("\n", $oldoutput);
		$newlines = explode("\n", $newoutput);

		$oldsuccess = false;
		foreach($oldlines as $line){
			if(empty(trim($line))) continue;
			$split1 = explode(" (", $line);
			$split2 = explode (", ", $split1[1]);

			$oldfilename = $split1[0];

			if(!empty($_GET['oldfile']) && $_GET['oldfile'] == $oldfilename){
				$md5 = str_replace("md5: ", "", $split2[1]);

				$oldq = $pdo->prepare("SELECT cdnconfig FROM wow_versions WHERE buildconfig = ?");
				$oldq->execute([$oldbuild['buildconfig']['hash']]);
				$oldrow = $oldq->fetch();

				if(!file_exists("/tmp/".$oldfilename."_".$oldbuild['buildconfig']['description'])){
					$output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll extractfilebycontenthash wow ".escapeshellarg($oldbuild['buildconfig']['hash'])." ".escapeshellarg($oldrow['cdnconfig'])." ".escapeshellarg($md5)." ".escapeshellarg("/tmp/".$oldfilename."_".$oldbuild['buildconfig']['description'])." 2>&1");
				}

				if(!file_exists("/tmp/".$oldfilename."_".$oldbuild['buildconfig']['description'])){
					echo("Something went wrong during extraction.").$output;
				}else{
					echo "Success extracting " . $oldfilename."_".$oldbuild['buildconfig']['description'];
					$oldsuccess = true;
				}
				break;
			}
		}

		$newsuccess = false;

		foreach($newlines as $line){
			if(empty(trim($line))) continue;
			$split1 = explode(" (", $line);
			$split2 = explode (", ", $split1[1]);

			$newfilename = $split1[0];

			if(!empty($_GET['newfile']) && $_GET['newfile'] == $newfilename){
				$md5 = str_replace("md5: ", "", $split2[1]);

				$newq = $pdo->prepare("SELECT cdnconfig FROM wow_versions WHERE buildconfig = ?");
				$newq->execute([$newbuild['buildconfig']['hash']]);
				$newrow = $newq->fetch();

				if(!file_exists("/tmp/".$newfilename."_".$newbuild['buildconfig']['description'])){
					$output = shell_exec("cd /home/wow/buildbackup; /usr/bin/dotnet BuildBackup.dll extractfilebycontenthash wow ".escapeshellarg($newbuild['buildconfig']['hash'])." ".escapeshellarg($newrow['cdnconfig'])." ".escapeshellarg($md5)." ".escapeshellarg("/tmp/".$newfilename."_".$newbuild['buildconfig']['description'])." 2>&1");
				}

				if(!file_exists("/tmp/".$newfilename."_".$newbuild['buildconfig']['description'])){
					echo("Something went wrong during extraction.").$output;
				}else{
					echo "Success extracting " . $newfilename."_".$newbuild['buildconfig']['description'];
					$newsuccess = true;
				}

				break;
			}
		}

		if(!$oldsuccess || !$newsuccess) die("Not enough to diff!");

		$olddiff = shell_exec("strings ".escapeshellarg("/tmp/".$oldfilename."_".$oldbuild['buildconfig']['description'])." | sort -u > ".escapeshellarg("/tmp/".$oldfilename."_".$oldbuild['buildconfig']['description'].".txt"));
		$newdiff = shell_exec("strings ".escapeshellarg("/tmp/".$newfilename."_".$newbuild['buildconfig']['description'])." | sort -u > ".escapeshellarg("/tmp/".$newfilename."_".$newbuild['buildconfig']['description'].".txt"));

		$diff = shell_exec("git diff --no-index -U0 ".escapeshellarg("/tmp/".$oldfilename."_".$oldbuild['buildconfig']['description'].".txt")." ".escapeshellarg("/tmp/".$newfilename."_".$newbuild['buildconfig']['description'].".txt"));
		echo "<pre>";
		foreach(explode("\n", $diff) as $line){
			if(substr($line, 0, 3) == "@@ " || substr($line, 0, 3) == "+++" || substr($line, 0, 3) == "---" || substr($line, 0, 3) == "dif") continue;
			echo htmlentities($line)."\n";
		}
	}
	?>
</div>
<?php include "../inc/footer.php"; ?>