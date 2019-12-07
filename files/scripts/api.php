<?php
require_once("/var/www/wow.tools/inc/config.php");

header('Content-Type: application/json');
if(!isset($_SESSION)){ session_start(); }

if(!empty($_GET['src']) && $_GET['src'] == "mv"){
	$mv = true;
}else{
	$mv = false;
}

if(!empty($_GET['src']) && $_GET['src'] == "dbc"){
	$dbc = true;
}else{
	$dbc = false;
}

$keys = array();
$tactq = $pdo->query("SELECT id, keyname, keybytes FROM wow_tactkey");
while($tactrow = $tactq->fetch()){
	$keys[$tactrow['keyname']] = $tactrow['keybytes'];
}

if(isset($_GET['switchbuild'])){
	if(empty($_GET['switchbuild'])){
		$_SESSION['buildfilter'] = null;
		return;
	}else{
		if(strlen($_GET['switchbuild']) != 32 || !ctype_xdigit($_GET['switchbuild'])) die("Invalid contenthash!");
		$_SESSION['buildfilter'] = $_GET['switchbuild'];
	}
	die();
}

if(empty($_SESSION['buildfilter'])){
	$query = "FROM wow_rootfiles ";
} else {
	$qq = $pdo->prepare("SELECT id FROM wow_rootfiles_available_roots WHERE root8 = :root8id");
	$qq->bindValue(":root8id", hexdec(substr($_SESSION['buildfilter'], 0, 8)));
	$qq->execute();
	$qqr = $qq->fetch();
	if(empty($qqr)){
		die("invalid buildfilter");
	}else{
		$rootid = $qqr['id'];
	}

	$query = "FROM wow_rootfiles_available build JOIN wow_rootfiles ON build.root8id = ".$rootid." AND wow_rootfiles.id = build.filedataid";
}

$joinparams = [];
$clauseparams = [];
if(!empty($_GET['search']['value'])){
	$joins = [];
	$clauses = [];
	$criteria = array_filter( explode(",", $_GET['search']['value']), 'strlen' );

	$i = 0;
	foreach($criteria as &$c) {
		if($c == "unnamed") {
			array_push($clauses, " (wow_rootfiles.filename IS NULL) ");
		}else if($c == "communitynames") {
			array_push($clauses, " (wow_rootfiles.filename IS NOT NULL AND verified = 0) ");
		}else if($c == "unshipped") {
			array_push($clauses, " wow_rootfiles.id NOT IN (SELECT filedataid FROM wow_rootfiles_chashes) ");
		}else if($c == "encrypted") {
			array_push($clauses, " wow_rootfiles.id IN (SELECT filedataid FROM wow_encrypted) ");
		}else if(substr($c, 0, 10) == "encrypted:"){
			array_push($joins, " INNER JOIN wow_encrypted ON wow_rootfiles.id = wow_encrypted.filedataid AND keyname = ? ");
			$joinparams[] = str_replace("encrypted:", "", $c);
		}else if(substr($c, 0, 6) == "chash:"){
			array_push($joins, " JOIN wow_rootfiles_chashes ON wow_rootfiles_chashes.filedataid=wow_rootfiles.id AND contenthash = ? ");
			$joinparams[] = str_replace("chash:", "", $c);
		}else if(substr($c, 0, 5) == "type:"){
			array_push($clauses, " (type = ?) ");
			$clauseparams[] = str_replace("type:", "", $c);
		}else if(substr($c, 0, 5) == "skit:"){
			array_push($joins, " INNER JOIN `wowdata`.soundkitentry ON `wowdata`.soundkitentry.id=wow_rootfiles.id AND `wowdata`.soundkitentry.entry = ? ");
			$joinparams[] = str_replace("skit:", "", $c);
		} else {
			// Point slashes the correct way :)
			$c = str_replace("\\", "/", trim($c));
			$subquery = "";

			$search = "";
			if(!empty($c)){
				if($c[0] != '^'){
					$search .= "%";
				}

				$search .= str_replace(["^","$"], "", $c);

				if($c[strlen($c) - 1] != '$'){
					$search .= "%";
				}
			}

			if($mv){
				$subquery = "wow_rootfiles.id = ?";
				$clauseparams[] = $c."%";
				$types = array();
				if($_GET['showADT'] == "true"){
					$types[] = "adt";
				}
				if($_GET['showWMO'] == "true"){
					$types[] = "wmo";
				}
				if($_GET['showM2'] == "true"){
					$types[] = "m2";
				}
				if(!empty($c)){
					$subquery .= " OR wow_rootfiles.filename LIKE ? AND type IN ('".implode("','", $types)."')";
					$clauseparams[] = $search;
				}else{
					$subquery .= " OR type IN ('".implode("','", $types)."')";
				}
				if(!empty($c) && $_GET['showWMO'] == "true"){
					$subquery .= " AND wow_rootfiles.filename IS NOT NULL AND wow_rootfiles.filename NOT LIKE '%_lod1.wmo' AND wow_rootfiles.filename NOT LIKE '%_lod2.wmo'";
				}
				if($_GET['showADT'] == "true"){
					$subquery .= " AND wow_rootfiles.filename NOT LIKE '%_obj0.adt' AND wow_rootfiles.filename NOT LIKE '%_obj1.adt' AND wow_rootfiles.filename NOT LIKE '%_tex0.adt' AND wow_rootfiles.filename NOT LIKE '%_tex1.adt' AND wow_rootfiles.filename NOT LIKE '%_lod.adt'";
				}

				array_push($clauses, " (". $subquery . ")");

			}else if($dbc){
				array_push($clauses, " (wow_rootfiles.filename LIKE ? AND type = 'db2')");
				$clauseparams[] = $search;
			}else{
				$clauseparams[] = $search;
				$clauseparams[] = $search;
				$clauseparams[] = $search;
				array_push($clauses, " (wow_rootfiles.id LIKE ? OR lookup LIKE ? OR wow_rootfiles.filename LIKE ?) ");
			}
		}
		$i++;
	}

	$query .= implode(" ", $joins);
	if(count($clauses) > 0){
		$query .= " WHERE " . implode(" AND ", $clauses);
	}
}else{
	if($mv){
		$types = array();
		if($_GET['showADT'] == "true"){
			$types[] = "adt";
		}
		if($_GET['showWMO'] == "true"){
			$types[] = "wmo";
		}
		if($_GET['showM2'] == "true"){
			$types[] = "m2";
		}
		$query .= " WHERE type IN ('".implode("','", $types)."')";
		if(!empty($_GET['search']['value']) && $_GET['showWMO'] == "true"){
			$query .= " AND wow_rootfiles.filename NOT LIKE '%_lod1.wmo' AND wow_rootfiles.filename NOT LIKE '%_lod2.wmo'";
		}
		if($_GET['showADT'] == "true"){
			$query .= " AND wow_rootfiles.filename NOT LIKE '%_obj0.adt' AND wow_rootfiles.filename NOT LIKE '%_obj1.adt' AND wow_rootfiles.filename NOT LIKE '%_tex0.adt' AND wow_rootfiles.filename NOT LIKE '%_tex1.adt' AND wow_rootfiles.filename NOT LIKE '%_lod.adt'";
		}
	}
}

$orderby = '';
if(!empty($_GET['order'])){
	$orderby .= " ORDER BY ";
	switch($_GET['order'][0]['column']){
		case 0:
		$orderby .= "wow_rootfiles.id";
		break;
		case 1:
		$orderby .= "wow_rootfiles.filename";
		break;
		case 2:
		$orderby .= "wow_rootfiles.lookup";
		break;
		case 3:
		$orderby .= "wow_rootfiles.firstseen";
		break;
		case 4:
		$orderby .= "wow_rootfiles.type";
		break;
	}

	switch($_GET['order'][0]['dir']){
		case "asc":
		$orderby .= " ASC";
		break;
		case "desc":
		$orderby .= " DESC";
		break;
	}
}

$start = (int)filter_input( INPUT_GET, 'start', FILTER_SANITIZE_NUMBER_INT );
$length = (int)filter_input( INPUT_GET, 'length', FILTER_SANITIZE_NUMBER_INT );

$params = array_merge($joinparams, $clauseparams);

$profiling = false;
if($profiling){
	$pdo->exec('set @@session.profiling_history_size = 100;');
	$pdo->exec('set profiling=1');
}

try{
	$qmd5 = md5($query.implode('|',$params));
	$returndata['rfq'] = $query;
	if(!($returndata['recordsFiltered'] = $memcached->get("query." . $qmd5))){
		$numrowsq = $pdo->prepare("SELECT COUNT(wow_rootfiles.id) " . $query);
		$numrowsq->execute($params);
		$returndata['recordsFiltered'] = (int)$numrowsq->fetchColumn();
		if(!$memcached->set("query." . $qmd5, $returndata['recordsFiltered'])){
			$returndata['mc1error'] = $memcached->getResultMessage();
		}
	}

	$dataq = $pdo->prepare("SELECT wow_rootfiles.* " . $query . $orderby . " LIMIT " . $start .", " . $length);
	$dataq->execute($params);
}catch(Exception $e){
	$returndata['data'] = [];
	// echo "<pre>";
	// print_r($e);
	// echo "</pre>";
	$returndata['query'] = $query;
	$returndata['params'] = $params;
	$returndata['error'] = "I'm currently working on this functionality right now and broke it. Hopefully back soon. <3";
	echo json_encode($returndata);
	die();
}

if(empty($_GET['draw']))
	$_GET['draw'] = 0;

$returndata['draw'] = (int)$_GET['draw'];

if(!($returndata['recordsTotal'] = $memcached->get("files.total"))){
	$returndata['recordsTotal'] = $pdo->query("SELECT count(id) FROM wow_rootfiles")->fetchColumn();
	if(!$memcached->set("files.total", $returndata['recordsTotal'])){
		$returndata['mc2error'] = $memcached->getResultMessage();
	}
}

$returndata['data'] = array();

$encq = $pdo->prepare("SELECT keyname FROM wow_encrypted WHERE filedataid = ?");
$soundkitq = $pdo->prepare("SELECT soundkitentry.id as id, soundkitentry.entry as entry, soundkitname.name as name FROM `wowdata`.soundkitentry LEFT JOIN `wowdata`.soundkitname ON soundkitentry.entry=`wowdata`.soundkitname.id WHERE soundkitentry.id = ?");
$cmdq = $pdo->prepare("SELECT id FROM `wowdata`.creaturemodeldata WHERE filedataid = ?");
$commentq = $pdo->prepare("SELECT comment, lastedited, users.username as username FROM wow_rootfiles_comments INNER JOIN users ON wow_rootfiles_comments.lasteditedby=users.id WHERE filedataid = ?");
$cdnq = $pdo->prepare("SELECT cdnconfig FROM wow_versions WHERE buildconfig = ?");
$subq = $pdo->prepare("SELECT wow_rootfiles_chashes.root_cdn, wow_rootfiles_chashes.contenthash, wow_buildconfig.hash as buildconfig, wow_buildconfig.description FROM wow_rootfiles_chashes LEFT JOIN wow_buildconfig on wow_buildconfig.root_cdn=wow_rootfiles_chashes.root_cdn WHERE filedataid = ? ORDER BY wow_buildconfig.description ASC");

while($row = $dataq->fetch()){
	$contenthashes = array();
	$cfname = "";
	if($row['verified'] == 0){
		$cfname = $row['filename'];
		$row['filename'] = null;
	}
	if(!$mv && !$dbc){
		// enc 0 = not encrypted, enc 1 = encrypted, unknown key, enc 2 = encrypted, known key, enc 3 = encrypted with multiple keys, some known
		$encq->execute([$row['id']]);

		$encryptedKeyCount = 0;
		$encryptedAvailableKeys = 0;
		$enc = 0;
		$usedkeys = [];
		foreach($encq->fetchAll(PDO::FETCH_ASSOC) as $encr){
			$encryptedKeyCount++;
			$usedkeys[] = $encr['keyname'];
			if(array_key_exists($encr['keyname'], $keys)){
				if(!empty($keys[$encr['keyname']])){
					$encryptedAvailableKeys++;
				}
			}
		}

		if($encryptedKeyCount > 0){
			if($encryptedKeyCount == $encryptedAvailableKeys){
				$enc = 2;
			}else{
				if($encryptedKeyCount > 1 && $encryptedAvailableKeys > 1){
					$enc = 3;
				}else{
					$enc = 1;
				}
			}
		}

		/* CROSS REFERENCES */
		$xrefs = array();

		// SoundKit
		$soundkitq->execute([$row['id']]);
		$soundkits = $soundkitq->fetchAll();
		if(count($soundkits) > 0){
			$xrefs['soundkit'] = "<b>Part of SoundKit(s):</b><br>";
			foreach($soundkits as $soundkitrow){
				if(empty($soundkitrow['name'])){
					$soundkitrow['name'] = "Unknown";
				}
				$xrefs['soundkit'] .= $soundkitrow['entry'] . " (" .htmlentities($soundkitrow['name'], ENT_QUOTES) . ")<br>";
			}
		}

		// Creature Model Data
		$cmdq->execute([$row['id']]);
		$cmdr = $cmdq->fetch();
		if(!empty($cmdr)){
			$xrefs['cmd'] = "<b>CreatureModelData ID:</b> ".$cmdr['id']."<br>";
		}

		// Comments
		$commentq->execute([$row['id']]);
		$comments = $commentq->fetchAll();
		if(count($comments) > 0){
			for($i = 0; $i < count($comments); $i++){
				$comments[$i]['username'] = htmlentities($comments[$i]['username'], ENT_QUOTES);
				$comments[$i]['comment'] = htmlentities($comments[$i]['comment'], ENT_QUOTES);
			}
		}else{
			$comments = "";
		}
	}else{
		$enc = 0;
		$xrefs = array();
		$comments = "";
	}

	$versions = array();

	$subq->execute([$row['id']]);

	foreach($subq->fetchAll() as $subrow){
		$cdnq->execute([$subrow['buildconfig']]);
		$subrow['cdnconfig'] = $cdnq->fetchColumn();

		if(in_array($subrow['contenthash'], $contenthashes)){
			continue;
		}else{
			$contenthashes[] = $subrow['contenthash'];
		}

		$subrow['enc'] = $enc;
		if($enc > 0){
			$subrow['key'] = implode(", ", $usedkeys);
		}

		// Mention firstseen if it is from first casc build
		if($subrow['description'] == "WOW-18125patch6.0.1_Beta"){
			$subrow['firstseen'] = $row['firstseen'];
		}

		$versions[] = $subrow;
	}

	$returndata['data'][] = array($row['id'], $row['filename'], $row['lookup'], array_reverse($versions), $row['type'], $xrefs, $comments, $cfname);
}

if($profiling){
	$profileq = $pdo->query('show profiles');
	$returndata['profiling'] = $profileq->fetchAll(PDO::FETCH_ASSOC);
	$pdo->exec('set profiling=0');
}

echo json_encode($returndata);
?>