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
		case 41:
		case 43:
			$groupInfo['project'] = "Classic";
			break;
		default:
			$groupInfo['project'] = "Mainline";
	}

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
<p>Realm status for every known US/EU/TEST realm, updated every 5 minutes. Still WIP!</p>

<?php
foreach($groupedRealms as $region => $realms){
?>
<h1 id="<?=$region?>"><?=$region?></h1>
<table class='table table-sm table-striped'>
<thead><tr><th style='width: 80px'></th><th style='width: 300px'>Name</th><th style='width: 50px;'>Type</th><th style='width: 80px'>Population</th><th>Server version</th><th>Last seen in realmlist at (UTC+1)</th></tr></thead>
<?php
	foreach($realms as $realm){
		$realm['status'] == 1 ? $status = "<span style='color: green'><i class='fa fa-arrow-circle-up'></i></span> Up" : $status = "<span style='color: red;'><i class='fa fa-arrow-circle-down'></i></span> Down";
		echo "<tr><td>".$status."</td><td>".$realm['group']['region']."-".$realm['name']."</td><td></td><td></td><td>".$realm['version']."</td><td>".$realm['lastseen']." (".time_elapsed_string($realm['lastseen']).")</td></tr>";
	}
?>
</table>

<?php
}
?>
</div>
<?php include("../inc/footer.php"); ?>
