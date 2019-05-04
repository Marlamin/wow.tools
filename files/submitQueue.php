<?php
require_once("../inc/header.php");
?>
<div class="container-fluid">
	<?php if(empty($_SESSION['loggedin']) || $_SESSION['rank'] == 0){?>
		<div class='alert alert-danger'>
			You need to be logged in as a moderator to submit filenames.
		</div>
	<? }else{ ?>
		<table class='table table-striped table-condensed'>
			<thead><tr><th style='width: 100px'>User</th><th style='width: 200px'>Submitted at</th><th>Files</th><th style='width: 100px'>&nbsp;</th><th style='width: 100px'>&nbsp;</th></tr></thead>
			<?php
			$previousTime = '';
			$cq = $pdo->prepare("SELECT filename FROM wow_rootfiles WHERE id = ?");
			$suggestions = $pdo->query("SELECT * FROM wow_rootfiles_suggestions WHERE status = 'todo'")->fetchAll();
			foreach($suggestions as $row){
				$endTag = "</table></pre></td><td><a href='#' class='btn btn-sm btn-outline-success'>Approve</a></td><td><a href='#' class='btn btn-sm btn-outline-danger'>Decline</a></td></tr>";
				if($previousTime != $row['submitted']){
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
			echo $endTag;
			?>
		</table>
	<? } ?>
</div>
<?php
include("../inc/footer.php");
?>