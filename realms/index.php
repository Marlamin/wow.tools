<?php
require_once("../inc/header.php");

// https://stackoverflow.com/questions/1416697/converting-timestamp-to-time-ago-in-php-e-g-1-day-ago-2-days-ago
function time_elapsed_string($datetime, $full = false) {
	$now = new DateTime;
	$ago = new DateTime($datetime);
	$diff = $now->diff($ago);

	$diff->w = floor($diff->d / 7);
	$diff->d -= $diff->w * 7;

	$string = array(
		'y' => 'year',
		'm' => 'month',
		'w' => 'week',
		'd' => 'day',
		'h' => 'hour',
		'i' => 'minute',
		's' => 'second',
	);
	foreach ($string as $k => &$v) {
		if ($diff->$k) {
			$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
		} else {
			unset($string[$k]);
		}
	}

	if (!$full) $string = array_slice($string, 0, 1);
	return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function getStatus($status){
	switch($status){
		case 0:
		return "<span class='badge badge-danger'><i class='fa fa-arrow-circle-down'></i> Down</span>";
		case 1:
		return "<span class='badge badge-success'><i class='fa fa-arrow-circle-up'></i> Up</span>";
		default:
		return "<span class='badge badge-dark'><i class='fa fa-arrow-circle-up'></i> Unknown</span>";
	}
}

function getPopulation($population){
	switch($population){
		case 0:
		return "<span class='badge badge-secondary'>Offline</span>";
		case 1:
		return "<span class='badge badge-success'>Low</span>";
		case 2:
		return "<span class='badge badge-warning'>Medium</span>";
		case 3:
		return "<span class='badge badge-danger'>High</span>";
		case 4:
		return "<span class='badge badge-light'>New</span>";
		case 5:
		return "<span class='badge badge-primary'>Recommended</span>";
		case 6:
		return "<span class='badge badge-dark'>Full</span>";
		case 7:
		return "<span class='badge badge-dark'>Locked</span>";
		default:
		return "<span class='badge badge-dark'>Unknown</span>";
	}
}

function getRealmType($type){
	switch($type){
		case 1:
		return "<span class='badge badge-success'>PvE</span>";
		case 2:
		return "<span class='badge badge-warning'>PvP</span>";
		case 7:
		return "<span class='badge badge-info'>RP</span>";
		case 9:
		return "<span class='badge badge-danger'>RP-PvP</span>";
		case 12:
		return "<span class='badge badge-primary'>Arena</span>";
		case 14:
		return "<span class='badge badge-primary'>Dungeon</span>";
		default:
		return "<span class='badge badge-dark'>Unknown (".$type.")</span>";
	}
}

function getGroupInfo($group){
	$groupInfo = [];
	$expl = explode(".", $group);
	$explid = explode("-", $expl[1]);

	switch($explid[0]){
		case 1:
		case 2:
		case 3:
		case 4:
		case 5:
		$groupInfo['project'] = "Retail";
		break;
		case 21:
		case 22:
		case 23:
		case 24:
		case 25:
		case 26:
		$groupInfo['project'] = "Arena Tournament";
		break;
		case 40:
		$groupInfo['project'] = "Classic PTR";
		break;
		case 41:
		case 42:
		case 43:
		case 44:
		case 45:
		$groupInfo['project'] = "Classic Retail";
		break;
		case 50:
		$groupInfo['project'] = "Public Test Realm";
		break;
		case 60:
		$groupInfo['project'] = "8.x Alpha/Beta";
		break;
		case 65:
		$groupInfo['project'] = "9.x (Take-home employee-only?) Alpha";
		break;
		case 71:
		case 72:
		case 73:
		case 74:
		case 75:
		case 76:
		$groupInfo['project'] = "Dungeon Tournament";
		break;
		default:
		$groupInfo['project'] = "Unknown (".$explid[0].")";
	}

	$groupInfo["id"] = $explid[0];
	$groupInfo["region"] = strtoupper($expl[0]);
	$groupInfo["desc"] = $groupInfo['project'];
	return $groupInfo;
}

$groupedRealms = [];
foreach($pdo->query("SELECT * FROM wow_realms ORDER BY version DESC, name, id ASC") as $realm){
	$groupInfo = getGroupInfo($realm['groupname']);
	if(!array_key_exists($groupInfo['region'], $groupedRealms))
		$groupedRealms[$groupInfo['region']] = [];

	$groupedRealms[$groupInfo["region"]][$groupInfo['desc']][$realm['id']] = $realm;
	$groupedRealms[$groupInfo["region"]][$groupInfo['desc']][$realm['id']]['group'] = $groupInfo;
}
?>
<div class="container-fluid">
	<p>Realm status for every known US/EU/TEST realm, updated every 5 minutes. Basic realm status is not retrieved from the Official API allowing for more realms (including PTR/Beta) to be listed as well. Still WIP, some values (such as population) might not be up to date.</p>
	<div class='alert alert-warning'><h4>Shadowlands Alpha realm status</h4>
		It appears Blizzard fixed WoW.tools being able to monitor the Shadowlands Alpha realm so it will appear as <?=getStatus(0)?> for now. Until Alpha/Beta flagging starts up, shown status will be wrong.
	</div>
	<?php
	?>
	<ul class="nav nav-pills" role="tablist">
		<?php
		$firstNav = true;
		foreach($groupedRealms as $region => $categories){
			?>
			<li class="nav-item">
				<a class="nav-link <?php if($firstNav){?>active<?} $firstNav = false; ?>" data-toggle="tab" href="#region<?=$region?>" role="tab"><?=$region?></a>
			</li>&nbsp;
			<?php
		}
		?>
	</ul>
	<div class="tab-content" style='margin-top: 5px'>
		<?php
		$firstTab = true;
		foreach($groupedRealms as $region => $categories){
			$firstNav = true;
			?>
			<div class="tab-pane <?php if($firstTab){?>show active<?php $firstTab = false; } ?>" id="region<?=$region?>" role="tabpanel">
				<ul class="nav nav-pills" role="tablist">
					<?php foreach($categories as $category => $realms){ ?>
						<li class="nav-item">
							<a class="nav-link <?php if($firstNav){?>active<?} $firstNav = false; ?>" data-toggle="tab" href="#group<?=$realms[key($realms)]['group']['id']?>" role="tab">
								<?=$category?> <span class="badge badge-light"><?=count($realms)?> realms</span>
							</a>
						</li>&nbsp;
					<?php } ?>
				</ul>
				<div class="tab-content">
					<?php $firstTab = true; ?>
					<?php foreach($categories as $category => $realms){ ?>
						<div class="tab-pane <?php if($firstTab){?>show active<?php $firstTab = false; } ?>" id="group<?=$realms[key($realms)]['group']['id']?>" role="tabpanel">
							<table class='table table-sm table-striped'>
								<thead><tr><th style='width: 80px'></th><th style='width: 300px'>Name</th><th style='width: 50px;'>Type</th><th style='width: 80px'>Population</th><th>Server version</th><th>Last seen in realmlist at (UTC+1)</th></tr></thead>
								<?php
								foreach($realms as $realm){
									echo "<tr>";
									echo "<td>".getStatus($realm['status'])."</td>";
									echo "<td>".$realm['name']."</td>";
									echo "<td>".getRealmType($realm['type'])."</td>";
									echo "<td>".getPopulation($realm['population'])."</td>";
									echo "<td>".$realm['version']."</td>";
									echo "<td>".$realm['lastseen']." (".time_elapsed_string($realm['lastseen']).")</td>";
									echo "</tr>";
								}
								?>
							</table>
						</div>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
<?php include("../inc/footer.php"); ?>
