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
			$groupInfo['project'] = "Tournament";
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
			$groupInfo['project'] = "Alpha";
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
	$groupInfo["desc"] = $groupInfo["region"] . " - " . $groupInfo['project'];
	return $groupInfo;
}

$groupedRealms = [];
foreach($pdo->query("SELECT * FROM wow_realms ORDER BY version DESC, name, id ASC") as $realm){
	$groupInfo = getGroupInfo($realm['groupname']);
	if(!array_key_exists($groupInfo['desc'], $groupedRealms))
		$groupedRealms[$groupInfo['desc']] = [];

	$groupedRealms[$groupInfo['desc']][$realm['id']] = $realm;
	$groupedRealms[$groupInfo['desc']][$realm['id']]['group'] = $groupInfo;
}
?>
<div class="container-fluid">
<p>Realm status for every known US/EU/TEST realm, updated every 5 minutes. Basic realm status is not retrieved from the Official API allowing for more realms (including PTR/Beta) to be listed as well. Still WIP!</p>
<?php
?>
<ul class="nav nav-tabs" role="tablist">
<?php
$firstNav = true;
foreach($groupedRealms as $region => $realms){
?>
<li class="nav-item">
	<a class="nav-link <?php if($firstNav){?>active<?} $firstNav = false; ?>" data-toggle="tab" href="#group<?=$realms[key($realms)]['group']['id']?>" role="tab"><?=$region?></a>
</li>
<?php
}
?>
</ul>
<div class="tab-content">
<?php

$firstTab = true;
foreach($groupedRealms as $region => $realms){
?>
<div class="tab-pane <?php if($firstTab){?>show active<?php $firstTab = false; } ?>" id="group<?=$realms[key($realms)]['group']['id']?>" role="tabpanel">
<h1><?=$region?></h1>
<table class='table table-sm table-striped'>
<thead><tr><th style='width: 80px'></th><th style='width: 300px'>Name</th><th style='width: 50px;'>Type</th><th style='width: 80px'>Population</th><th>Server version</th><th>Last seen in realmlist at (UTC+1)</th></tr></thead>
<?php
	foreach($realms as $realm){
		$realm['status'] == 1 ? $status = "<span style='color: green'><i class='fa fa-arrow-circle-up'></i></span> Up" : $status = "<span style='color: red;'><i class='fa fa-arrow-circle-down'></i></span> Down";
		echo "<tr><td>".$status."</td><td>".$realm['group']['region']."-".$realm['name']."</td><td></td><td></td><td>".$realm['version']."</td><td>".$realm['lastseen']." (".time_elapsed_string($realm['lastseen']).")</td></tr>";
	}
?>
</table>
</div>
<?php
}
?>
</div>
<?php include("../inc/footer.php"); ?>
