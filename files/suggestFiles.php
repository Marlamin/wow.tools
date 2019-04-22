<? require_once("../inc/header.php");

$filelimit = 20000;

$cq = $pdo->prepare("SELECT * FROM wow_rootfiles WHERE id = ? AND verified = 0");
$uq = $pdo->prepare("UPDATE wow_rootfiles SET filename = ? WHERE id = ?");
if(!empty($_POST['files'])){
	echo "<pre style='color: var(--text-color)'>";

	$ipwhitelist = array("2001:984:beb9:1:38bc:4175:9976:5254", "81.107.172.160");
	// Temporary password check
	if(empty($_POST['password']) || $_POST['password'] != "sillyblizzard820"){
		if(!in_array($_SERVER['REMOTE_ADDR'], $ipwhitelist)){
			die("Incorrect password");
		}
	}

	$files = explode("\n", $_POST['files']);

	if(count($files) > $filelimit){
		die("There currently is a limit of <b>".$filelimit." files</b> per request. You entered ".count($files)." files.");
	}

	foreach($files as $file){
		if(empty($file))
			continue;

		$split = explode(";", $file);
		$fdid = $split[0];
		$fname = strtolower(str_replace("\\", "/", trim($split[1])));
		$cq->execute([$fdid]);
		$row = $cq->fetch();
		if(empty($row['id'])){
			// Nothing at all
		}else if(empty($row['filename'])){
			// No filename currently set
			echo "Adding <kbd>".$fname."</kbd> to ".$row['id']." (".$row['type'].")\n";
			if(!empty($_POST['write']) && $_POST['write'] == 'on'){
				$uq->execute([$fname, $fdid]);
			}
		}else if($row['filename'] == $fname){
			echo "Skipping <kbd>".$fname."</kbd>, same as <kbd>".$row['filename']."</kbd> (".$row['id'].",".$row['type'].")\n";
		}else{
			// Filename currently set. Overwrite?
			echo "Overriding <kbd>".$row['filename']."</kbd> (".$row['id'].",".$row['type'].") with <kbd>".$fname."</kbd>\n";
			if(!empty($_POST['write']) && $_POST['write'] == 'on'){
				$uq->execute([$fname, $fdid]);
			}
		}
	}

	flushQueryCache();

	echo "</pre>";
}
?>
<div class="container-fluid">
	<div class='alert alert-danger'>
		Page is still in progress. A password is currently required to submit files.
	</div>
	<p>Enter files in the textbox below to suggest filenames for the community listfile. Each line must start with a filedataid, followed by the <kbd>;</kbd> and then the suggested filename.<br><b>Please note:</b> When this page becomes public, all submitted files will have to be checked by a moderator before being added to the listfile to prevent purposefully incorrect filenames being added to the system. For now, input is protected with a password.</p>
	<p>Example:<br><kbd>2961114;world/expansion07/doodads/dungeon/doodads/8du_mechagon_anvil01.m2</kbd><br><kbd>2961119;world/expansion07/doodads/dungeon/doodads/8du_mechagon_anvil0100.skin</kbd></p>
	<div class='alert alert-warning'>A maximum of <b><?=$filelimit?> files</b> per request is allowed.</div>
	<form method='post' action='suggestFiles.php'>
		<input type='password' name='password' class='form-control form-control-sm' placeholder='Dev password'>
		<label for='writeBox'>Write entries to DB?</label> <input id='writeBox' type='checkbox' name='write'>
		<br>
		<textarea name='files' rows='15' cols='200'></textarea>
		<br>
		<input class='btn btn-success' type='submit' value='Submit'>
	</form>
</div>

<? include("../inc/footer.php"); ?>