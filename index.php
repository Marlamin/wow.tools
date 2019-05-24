<?php require_once("inc/header.php"); ?>
<div class='container-fluid'>
	<h3>Welcome to WoW.tools!</h3>
	<p>
		Having had many different sites for many of the tools I work on, I decided to move them all under one roof. This is what WoW.tools is, a collection for all those tools under one roof, allowing for better integration between the tools and costing me less time to keep everything up-to-date separately. Keep in mind many of the tools are still only meant for the technical/datamining minded amongst you, some stuff might not be friendly to beginners at this stage. I plan on adding tutorials/guides on how to get started doing some basic stuff.
	</p>
	<div class='row'>
		<div class='col-md-6'>
			<h4>Recent updates</h4>
			<b>22-05-2019</b>
			<ul>
				<li>
					<b>Build diffs</b>
					<ul>
						<li>WIP version of new build diff page is now available: <a href='https://wow.tools/builds/diff_new.php?from=54b3dc4ced90d45071f72a05fecfd063&to=eb9dc13f6f32a1b4992b61d6217dd6ab'>give it a try for the latest 8.2 build here!</a></li>
					</ul>
				</li>
			</ul>
			<b>18-05-2019</b>
			<ul>
				<li>
					<b>DBCs</b>
					<ul>
						<li><a href='https://wow.tools/dbc/diff.php?dbc=map.db2&old=b82ac0499b1a56cfc8559e485f183799&new=54b3dc4ced90d45071f72a05fecfd063'>New DBC diffs!</a></li>
					</ul>
				</li>
			</ul>
			<b>12-05-2019</b>
			<ul>
				<li>
					<b>Homepage</b>
					<ul>
						<li>Add recent updates</li>
						<li>Add current WoW version per branch</li>
					</ul>
				</li>
			</ul>
			<b>11-05-2019</b>
			<ul>
				<li>
					<b>Files</b>
					<ul>
						<li>Change background color for community named files to purple</li>
					</ul>
				</li>
			</ul>
		</div>
		<div class='col-md-6'>
			<h4>Current WoW versions per branch</h4>
			<table class='table table-condensed table-striped table-hover' style='width: 450px;'>
				<thead><tr><th>Name</th><th>Version</th><th>Build time (PT)</th></tr></thead>
				<?php
				$productq = $pdo->query("SELECT id, name FROM ngdp_urls WHERE url LIKE '%wow%versions' ORDER BY ID ASC");
				while($row = $productq->fetch(PDO::FETCH_ASSOC)){
					$histq = $pdo->prepare("SELECT newvalue, timestamp FROM ngdp_history WHERE url_id = ? AND event = 'valuechange' ORDER BY ID DESC LIMIT 1");
					$histq->execute([$row['id']]);
					$histr = $histq->fetch(PDO::FETCH_ASSOC);
					$bc = parseBPSV(explode("\n", $histr['newvalue']));
					$highestBuild = 0;
					$highestBuildName = "<i>Unknown</i>";
					$buildTime = "<i>Unknown</i>";
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

					echo "<tr><td>".str_replace(" Versions", "", $row['name'])."</td><td>" . $highestBuildName."</td><td>".$buildTime."</td></tr>";
				}
				?>
			</table>
		</div>
	</div>
</div>
<?php require_once("inc/footer.php"); ?>