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
		$iq = $pdo->prepare("INSERT INTO wow_rootfiles (id, filename, verified) VALUES (?, ?, 0)");

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
				$log[] = "<b>WARNING!</b> Adding entirely new file <kbd>".$fname."</kbd> to new filedataid ".$fdid;
				$suggestedfiles[$fdid] = $fname;
				$iq->execute([$fdid, $fname]);
			}
		}

		foreach($suggestedfiles as $fdid => $fname){
			$uq->execute([$fname, $fdid]);
		}

		$statusq->execute(["approved", urldecode($_GET['approve'])]);

		if(count($suggestedfiles) > 0){
			file_get_contents("https://wow.tools/casc/root/diff_api_invalidate?t=" . strtotime("now"));
			$message = "Approved " . count($suggestedfiles) . " files.";
			$json = json_encode([ "username" => getUsernameByUserID($_SESSION['userid']), "content" => $message]);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $discordfilenames);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			curl_setopt($ch, CURLOPT_USERAGENT, "WoW.Tools Discord Integration");
			curl_setopt($ch, CURLOPT_HTTPHEADER, ["Length" => strlen($json), "Content-Type: application/json"]);
			$response = curl_exec($ch);
			curl_close($ch);
			flushQueryCache();
		}
	}

	if(!empty($_GET['decline'])){
		$statusq->execute(["declined", urldecode($_GET['decline'])]);
		$log = [];
		$log[] = "Declined files with submit time " .htmlentities(urldecode($_GET['decline']));
	}

	if(!empty($log)){
		echo "<div class='container-fluid'>";
		echo "<h4>Log</h4>";
		echo "<pre style='max-height: 500px; overflow-y: scroll'>";
		echo implode("\n", $log);
		echo "</pre>";
		echo "</div>";
	}
}

// $allFiles = $pdo->query("SELECT filename FROM wow_rootfiles")->fetchAll(PDO::FETCH_COLUMN);
?>
<div class="container-fluid">
	<?php if(!empty($_SESSION['loggedin']) && $_SESSION['rank'] > 0){?>
		<h3>Open filename submissions</h3>
		<table class='table table-striped table-condensed'>
			<?php
			$previousTime = '';
			$cq = $pdo->prepare("SELECT filename FROM wow_rootfiles WHERE id = ?");
			$suggestions = $pdo->query("SELECT * FROM wow_rootfiles_suggestions WHERE status = 'todo' ORDER BY submitted, filedataid DESC")->fetchAll();
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

					echo "<tr><td>".$row['filedataid']."</td>";
					// if(in_array($row['filename'], $allFiles)){
					// 	echo "<td>!!!! DUPLICATE !!!! ".$row['filename']."</td>";
					// }else{
						echo "<td>".$row['filename']."</td>";

					// }
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
	<?php } ?>
	<h3>Filenames submitted in the last 7 days</h3>
	<table class='table table-striped table-condensed'>
			<?php
			$previousTime = '';
			$filenames = $pdo->query("SELECT * FROM wow_rootfiles_suggestions WHERE submitted BETWEEN NOW() - INTERVAL 7 DAY AND NOW() ORDER BY submitted DESC")->fetchAll();
			if(count($filenames) > 0){
				echo "<thead><tr><th style='width: 100px'>User</th><th style='width: 100px'>Status</th><th style='width: 200px'>Submitted at</th><th>Files</th></tr></thead>";
				foreach($filenames as $row){
					if($previousTime != $row['submitted']){
						if($previousTime == ''){
							$endTag = "</table></pre></td></tr>";

						}else{
							$endTag = "</table></pre></td></tr>";
						}
						if($previousTime != '') echo $endTag;
						echo "<tr><td>".getUsernameByUserID($row['userid'])."</td><td>".$row['status']."</td><td>".$row['submitted']."</td><td><pre style='max-height: 200px; overflow-y: scroll; color: var(--text-color)'><table class='table table-minimal'><thead style='position: sticky; top: 0px;'><tr><th style='width: 200px;'>FileDataID</th><th>Suggested name</th></tr></thead>";
					}

					echo "<tr><td>".$row['filedataid']."</td><td>".$row['filename']."</td></tr>";
					$previousTime = $row['submitted'];
				}

				if($previousTime != ''){
					echo "</table></pre></td></tr>";
				}
			}

			?>
		</table>
</div>
<?php
include("../inc/footer.php");
?>