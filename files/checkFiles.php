<? require_once("../inc/header.php");

$filelimit = 20000;

if(!empty($_POST['files'])){
	$validfiles = array();
	$invalidfiles = array();
	$files = explode("\n", $_POST['files']);

	if(count($files) > $filelimit){
		die("There currently is a limit of <b>".$filelimit." files</b> per request. You entered ".count($files)." files.");
	}

	$tmpfname = tempnam("/tmp", "bnetlistfile");
	$tmpfile = fopen($tmpfname, "w");

	foreach($files as $file){
		if(empty(trim($file))) continue;
		if(strpos($file, " (in: ") !== false){
			$expl = explode(" (in: ", $file);
			$file = $expl[0];
		}

		$file = trim($file);
		$file = strtolower(str_replace("\\", "/", $file));

		fwrite($tmpfile, $file."\n");
	}

	fclose($tmpfile);

	$cmd = "cd /home/wow/buildbackup; /usr/bin/dotnet /home/wow/buildbackup/BuildBackup.dll calchashlistfile ".escapeshellarg($tmpfname);
	$output = explode("\n", shell_exec($cmd));

	$qt = $pdo->prepare("SELECT filename FROM wow_rootfiles WHERE lookup = ?");
	$addq = $pdo->prepare("UPDATE wow_rootfiles SET filename = ? WHERE lookup = ?");
	$numadded = 0;
	foreach($output as $line){
		$expl = explode(" = ", trim($line));
		if(count($expl) < 2) continue;

		$qt->execute([$expl[1]]);
		$row = $qt->fetch();

		if(!empty($row)){
			if(empty($row['filename'])){
				$addq->execute([$expl[0], $expl[1]]);
				echo "Added ".$expl[0]." (".$expl[1].")<br>";
				$numadded++;
			}
			$validfiles[] = $expl[0];
		}else{
			$invalidfiles[] = $expl[0];
		}
	}

	unlink($tmpfname);

	// Log credit
	if($numadded > 0){
		$lq = $pdo->prepare("INSERT INTO wow_namelog (userid, userip, numadded) VALUES (:id, :ip, :numadded)");

		if(!empty($_SESSION['userid'])){
			$lq->bindParam(':id', $_SESSION['userid'], PDO::PARAM_INT);
		}else{
			$lq->bindValue(':id', null, PDO::PARAM_INT);
		}

		if(!empty($_SERVER['REMOTE_ADDR'])){
			$lq->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
		}else{
			$lq->bindValue(':ip', "127.0.0.1");
		}

		$lq->bindParam(':numadded', $numadded);
		$lq->execute();
	}
}
?>
<div class="container-fluid">
<?
if(!empty($validfiles) || !empty($invalidfiles)){
	if(count($validfiles) > 0){
		echo "<h3>Valid files</h3>";
		echo "<pre>";
		foreach($validfiles as $validfile){
			echo $validfile."\n";
		}
		echo "</pre>";
	}

	if(count($invalidfiles) > 0){
		echo "<h3>Invalid files</h3>";
		echo "<pre>";
		foreach($invalidfiles as $invalidfile){
			echo $invalidfile."\n";
		}
		echo "</pre>";
	}
	include("inc/footer.php");
	die();
}
?>
	<p>Enter files in the textbox below to check if they exist! If they exist, they are added to the game files page.</p>
<? if(empty($_SESSION['userid'])){ ?>
	<div class='alert alert-info'>If you log in when adding filenames, the number of files you added will be tracked for credits in <a href='//github.com/wowdev/wow-listfile' target='_BLANK'>automatic commits (every 30 minutes) to GitHub</a>.</div>
<? }else{ ?>
	<div class='alert alert-info'>You are logged in, the number of files you added will be tracked for credits in <a href='//github.com/wowdev/wow-listfile' target='_BLANK'>automatic commits (every 30 minutes) to GitHub</a>. If you don't want your username credited in the commits, log out when adding filenames.</div>
<? } ?>
	<div class='alert alert-warning'>A maximum of <b><?=$filelimit?> files</b> per request is allowed.</div>
	<form method='post' action='checkFiles.php'>
		<textarea name='files' rows='15' cols='200'></textarea>
		<br>
		<input type='submit' value='Check'>
	</form>
</div>
<? include("inc/footer.php"); ?>