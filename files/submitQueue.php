<?php
require_once("../inc/header.php");

if(!empty($_SESSION['loggedin']) && $_SESSION['rank'] > 0){
	$statusq = $pdo->prepare("UPDATE wow_rootfiles_suggestions SET status = ? WHERE submitted = ?");
	if(!empty($_GET['approve'])){

		$kfq = $pdo->query("SELECT id, filename FROM wow_rootfiles WHERE verified = 0")->fetchAll();
		foreach($kfq as $row){
			$knownfiles[$row['id']] = $row['filename'];
		}

		$uq = $pdo->prepare("UPDATE wow_rootfiles SET filename = ? WHERE id = ? AND verified = 0");

		$date = urldecode($_GET['approve']);

		$addq = $pdo->prepare("SELECT filedataid, filename FROM wow_rootfiles_suggestions WHERE submitted = ?");
		$addq->execute([$date]);

		$log = [];
		$suggestedfiles = [];

		foreach($addq->fetchAll(PDO::FETCH_ASSOC) as $file){
			$fdid = $file['filedataid'];
			$fname = $file['filename'];
			if(array_key_exists($fdid, $knownfiles)){
				if(empty($knownfiles[$fdid])){
					// No filename currently set
					$log[] = "Adding <kbd>".$fname."</kbd> to ".$fdid;
					$suggestedfiles[$fdid] = $fname;
				}else if($knownfiles[$fdid] != $fname){
					// Submitted filename differs from current filename
					$log[] = "Overriding <kbd>".$knownfiles[$fdid]."</kbd> (".$fdid.") with <kbd>".$fname."</kbd>";
					$suggestedfiles[$fdid] = $fname;
				}else{
					// Submitted filename is the same
					$log[] = "Skipping <kbd>".$fname."</kbd>, same as <kbd>".$knownfiles[$fdid]."</kbd> (".$fdid.")";
				}
			}else{
				// File does not exist
				$log[] = "<b>WARNING!</b> FileDataID " . $fdid . " does not exist or is a file with a bruteforcable lookup!";
			}
		}

		foreach($suggestedfiles as $fdid => $fname){
			$uq->execute([$fname, $fdid]);
		}

		$statusq->execute(["approved", urldecode($_GET['approve'])]);

		$message = "Approved " . count($suggestedfiles) . " files.";
		$json = json_encode([ "username" => getUsernameByUserID($_SESSION['userid']), "content" => $message]);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $discordfilenames);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_USERAGENT, "WoW.Tools Discord Integration");
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Length" => strlen($json), "Content-Type" => "application/json"]);
		$response = curl_exec($ch);
		curl_close($ch);

		echo "<div class='container-fluid'>";
		echo "<h4>Log</h4>";
		echo "<pre style='max-height: 500px; overflow-y: scroll'>";
		echo implode("\n", $log);
		echo "</pre>";
		echo "</div>";
	}

	if(!empty($_GET['decline'])){
		$statusq->execute(["declined", urldecode($_GET['decline'])]);
		header("Location: /files/submitQueue.php");
		die();
	}
}
?>
<div class="container-fluid">
	<?php if(empty($_SESSION['loggedin']) || $_SESSION['rank'] == 0){?>
		<div class='alert alert-danger'>
			You need to be logged in as a moderator to submit filenames.
		</div>
	<? }else{ ?>
		<table class='table table-striped table-condensed'>
			<?php
			$previousTime = '';
			$cq = $pdo->prepare("SELECT filename FROM wow_rootfiles WHERE id = ?");
			$suggestions = $pdo->query("SELECT * FROM wow_rootfiles_suggestions WHERE status = 'todo'")->fetchAll();
			if(count($suggestions) == 0){
				echo "There are currently no submitted files waiting for approval.";
			}else{
				echo "<thead><tr><th style='width: 100px'>User</th><th style='width: 200px'>Submitted at</th><th>Files</th><th style='width: 100px'>&nbsp;</th><th style='width: 100px'>&nbsp;</th></tr></thead>";
				foreach($suggestions as $row){
					if($previousTime != $row['submitted']){
						if($previousTime == ''){
							$endTag = "</table></pre></td><td><a href='?approve=".urlencode($row['submitted'])."' class='btn btn-sm btn-outline-success'>Approve</a></td><td><a href='?decline=".urlencode($row['submitted'])."' class='btn btn-sm btn-outline-danger'>Decline</a></td></tr>";

						}else{
							$endTag = "</table></pre></td><td><a href='?approve=".urlencode($previousTime)."' class='btn btn-sm btn-outline-success'>Approve</a></td><td><a href='?decline=".urlencode($previousTime)."' class='btn btn-sm btn-outline-danger'>Decline</a></td></tr>";
						}
						if($previousTime != '') echo $endTag;
						echo "<tr><td>".getUsernameByUserID($row['userid'])."</td><td>".$row['submitted']."</td><td><pre style='max-height: 200px; overflow-y: scroll; color: var(--text-color)'><table class='table table-minimal'><thead style='position: sticky; top: 0px;'><tr><th>FileDataID</th><th>Suggested name</th><th>Current name (if set)</th></tr></thead>";
					}

					echo "<tr><td>".$row['filedataid']."</td><td>".$row['filename']."</td>";
					$cq->execute([$row['filedataid']]);
					$cr = $cq->fetch();
					if(!empty($cr['filename'])){
						echo "<td>".$cr['filename']."</td>";
					}
					echo "</tr>";
					$previousTime = $row['submitted'];
				}

				if($previousTime != ''){
					echo "</table></pre></td><td><a href='?approve=".urlencode($row['submitted'])."' class='btn btn-sm btn-outline-success'>Approve</a></td><td><a href='?decline=".urlencode($row['submitted'])."' class='btn btn-sm btn-outline-danger'>Decline</a></td></tr>";
				}
			}

			?>
		</table>
	<? } ?>
</div>
<?php
include("../inc/footer.php");
?>