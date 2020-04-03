<?php require_once("inc/header.php"); ?>
<div class='container-fluid'>
	<h3>Welcome to WoW.tools!</h3>
	<p>
		WoW.tools is a collection of several tools that interact with/display data from World of Warcraft. Please keep in mind these tools are meant for those with basic technical/datamining skills. For the basics on datamining, check out <a href='https://docs.google.com/document/d/1y1fHaZ1PrLvUTNM8crz081b4t8_pXjOYPxnagc-Y94c/' target='_BLANK'>this Google doc</a>.
	</p>
	<div class='row'>
		<div class='col-md-4'>
			<?php $updatedago = strtotime("now") - $memcached->get("github.commits.lastupdated"); ?>
			<h4>Recent updates</h4>
			<table class='table table-condensed table-striped table-hover' style='width: 100%'>
			<thead><tr><th>Description <small style='float: right'>Updates every 5 minutes, last updated <?=$updatedago?> seconds ago</small></th><th>&nbsp;</th></tr></thead>
			<?php
				if(!$memcached->get("github.commits.json")){
					require_once("scripts/updateGitHistory.php");
				}

				$commits = json_decode($memcached->get("github.commits.json"));
				foreach($commits as $commit){
					echo "<tr><td>[".$commit->repo."] <a target='_BLANK' href='".$commit->url."'>".$commit->message."</a></td><td><span class='text-muted'><b>".date("Y-m-d H:i:s", $commit->timestamp)."</b></span></td></tr>";
				}
			?>
			</table>
		</div>
		<div class='col-md-4'>
			<h4>Current WoW versions per branch</h4>
			<table class='table table-condensed table-striped table-hover' style='width: 100%'>
				<thead><tr><th>Name</th><th>Version</th><th>Build time (PT)</th></tr></thead>
				<?php
				$productq = $pdo->query("SELECT id, name FROM ngdp_urls WHERE url LIKE '%wow%versions' ORDER BY ID ASC");
				while($row = $productq->fetch(PDO::FETCH_ASSOC)){
					$histq = $pdo->prepare("SELECT newvalue, timestamp FROM ngdp_history WHERE url_id = ? AND event = 'valuechange' ORDER BY ID DESC LIMIT 1");
					$histq->execute([$row['id']]);

					$highestBuild = 0;
					$highestBuildName = "<i>Unknown</i>";
					$buildTime = "<i>Unknown</i>";

					$histr = $histq->fetch(PDO::FETCH_ASSOC);
					if(!empty($histr)){
						$bc = parseBPSV(explode("\n", $histr['newvalue']));
						foreach($bc as $bcregion){
							if($bcregion['BuildId'] > $highestBuild){
								$highestBuild = $bcregion['BuildId'];
								$highestBuildName = $bcregion['VersionsName'];
								$highestConfig = $bcregion['BuildConfig'];
								$build = getBuildConfigByBuildConfigHash($bcregion['BuildConfig']);
								if(!empty($build['builton'])){
									$buildTime = $build['builton'];
								}
							}
						}
					}

					echo "<tr><td>".str_replace(" Versions", "", $row['name'])."</td><td>" . $highestBuildName."</td><td>".$buildTime."</td></tr>";
				}
				?>
			</table>
		</div>
		<div class='col-md-4'>
			<h4>Latest detected hotfixes</h4>
			<table class='table table-condensed table-striped table-hover' style='width: 100%'>
			<thead><tr><th>Tables</th><th>Record count</th><th>Push ID</th><th>Detected on</th></tr></thead>
			<?php
			$hotfixes = $pdo->query("SELECT GROUP_CONCAT(DISTINCT(tableName)) as tables, COUNT(recordID) as rowCount, pushID, firstdetected FROM wow_hotfixes GROUP BY pushID ORDER BY firstdetected DESC, pushID DESC LIMIT 0,5")->fetchAll();
			foreach($hotfixes as $hotfix){
				echo "<tr><td>".implode('<br>', explode(',', $hotfix['tables']))."</td><td>".$hotfix['rowCount']."</td><td>".$hotfix['pushID']."</td><td>".$hotfix['firstdetected']."</td></tr>";
			}
			?>
			</table>
			<h4>Latest filename additions</h4>
			<table class='table table-condensed table-striped table-hover' style='width: 100%'>
			<thead><tr><th>Amount</th><th>User</th><th>Submitted on</th><th>Status</th></tr></thead>
			<?php
			$suggestions = $pdo->query("SELECT userid, DATE_FORMAT( submitted, \"%M %d\" ) as submitday, status, COUNT(*) as count FROM wow_rootfiles_suggestions GROUP BY userid, status, DATE_FORMAT( submitted, \"%M %d\" ) ORDER BY submitted DESC LIMIT 0,5")->fetchAll();
			$i = 0;
			foreach($suggestions as $row){
				if($i > 5)
					continue;

				echo "<tr><td>".$row['count']." files</td><td>".getUsernameByUserID($row['userid'])."</td><td>".$row['submitday']."</td><td>".$row['status']."</td></tr>";

				$i++;
			}
			?>
			</table>
		</div>
	</div>
</div>
<?php require_once("inc/footer.php"); ?>