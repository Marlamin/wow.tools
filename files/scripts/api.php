<?
require_once("/var/www/wow.tools/inc/config.php");

if(!isset($_SESSION)){ session_start(); }

if(!empty($_GET['src']) && $_GET['src'] == "mv"){
	$mv = true;
}else{
	$mv = false;
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
	$query = "FROM wow_rootfiles";
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

$params = [];

if($_GET['search']['value'] == "unnamed") {
	$query .= " WHERE filename IS NULL";
}else if($_GET['search']['value'] == "encrypted") {
	$query .= " WHERE id IN (SELECT filedataid FROM wow_encrypted)";
}else if(substr($_GET['search']['value'], 0, 10) == "encrypted:"){
	$query .= " WHERE id IN (SELECT filedataid FROM wow_encrypted WHERE keyname = :search)";
	$params[':search'] = str_replace("encrypted:", "", $_GET['search']['value']);
}else if(substr($_GET['search']['value'], 0, 6) == "chash:"){
	$query = " FROM wow_rootfiles_chashes LEFT JOIN wow_rootfiles ON wow_rootfiles_chashes.filedataid=wow_rootfiles.id WHERE contenthash = :search";
	$params[':search'] = str_replace("chash:", "", $_GET['search']['value']);
}else if(substr($_GET['search']['value'], 0, 5) == "type:"){
	$query .= " WHERE type = :type";
	$params[':type'] = str_replace("type:", "", $_GET['search']['value']);
}else if(substr($_GET['search']['value'], 0, 5) == "skit:"){
	$query = " FROM `wowdata`.soundkitentry INNER JOIN `casc`.wow_rootfiles ON `wowdata`.soundkitentry.id=wow_rootfiles.id WHERE `wowdata`.soundkitentry.entry = :skitid";
	$params[':skitid'] = str_replace("skit:", "", $_GET['search']['value']);

}else{
	// Point slashes the correct way :)
	$_GET['search']['value'] = str_replace("\\", "/", trim($_GET['search']['value']));

	if(!empty($_GET['search']['value']) && $_GET['search']['value'][0] == '^'){
		$params[':search'] = substr($_GET['search']['value'], 1)."%";
	}else{
		$params[':search'] = "%".$_GET['search']['value']."%";
	}

	if($mv){
		$query .= " WHERE id = :search";

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

		$query .= " OR filename LIKE :search AND type IN ('".implode("','", $types)."')";

		if($_GET['showWMO'] == "true"){
			$query .= " AND filename NOT LIKE '%_lod1.wmo' AND filename NOT LIKE '%_lod2.wmo'";
		}
		if($_GET['showADT'] == "true"){
			$query .= " AND filename NOT LIKE '%_obj0.adt' AND filename NOT LIKE '%_obj1.adt' AND filename NOT LIKE '%_tex0.adt' AND filename NOT LIKE '%_tex1.adt' AND filename NOT LIKE '%_lod.adt'";
		}
	}else{
		$query .= " WHERE id LIKE :search
		OR lookup LIKE :search
		OR filename LIKE :search";
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
// $returndata['query'] = "SELECT * " . $query . $orderby . " LIMIT " . $start .", " . $length;
// $returndata['params'] = $params;
function str_replace_first($from, $to, $content)
{
    $from = '/'.preg_quote($from, '/').'/';
    return preg_replace($from, $to, $content, 1);
}

$searchCounter = 0;
if(substr_count($query, ':search') > 1){
	for($i = 0; $i < substr_count($query, ':search'); $i++){
		$query = str_replace_first(":search", " :sr" . $searchCounter, $query);
		$params[':sr' . $searchCounter] = $params[':search'];
		$searchCounter++;
	}
}

$returndata['query'] = $query;
$returndata['params'] = $params;
$numrowsq = $pdo->prepare("SELECT COUNT(1) " . $query);
$numrowsq->execute($params);
$dataq = $pdo->prepare("SELECT * " . $query . $orderby . " LIMIT " . $start .", " . $length);
$dataq->execute($params);

$returndata['draw'] = (int)$_GET['draw'];
$returndata['recordsFiltered'] = (int)$numrowsq->fetchColumn();
$returndata['recordsTotal'] = $pdo->query("SELECT count(id) FROM wow_rootfiles")->fetchColumn();


/*
if(!($returndata['recordsTotal'] = $memcached->get("files.total"))){
	$memcached->set("files.total", $returndata['recordsTotal']);
}
*/

$returndata['data'] = array();

$encq = $pdo->prepare("SELECT keyname FROM wow_encrypted WHERE filedataid = ?");
$soundkitq = $pdo->prepare("SELECT soundkitentry.entry as entry, soundkitname.name as name FROM `wowdata`.soundkitentry INNER JOIN `wowdata`.soundkitname ON soundkitentry.entry=`wowdata`.soundkitname.id WHERE soundkitentry.id = ?");
$cmdq = $pdo->prepare("SELECT id FROM `wowdata`.creaturemodeldata WHERE filedataid = ?");
$commentq = $pdo->prepare("SELECT comment, lastedited, users.username as username FROM wow_rootfiles_comments INNER JOIN users ON wow_rootfiles_comments.lasteditedby=users.id WHERE filedataid = ?");
$cdnq = $pdo->prepare("SELECT cdnconfig FROM wow_versions WHERE buildconfig = ?");
$subq = $pdo->prepare("SELECT wow_rootfiles_chashes.root_cdn, wow_rootfiles_chashes.contenthash, wow_buildconfig.hash as buildconfig, wow_buildconfig.description FROM wow_rootfiles_chashes LEFT JOIN wow_buildconfig on wow_buildconfig.root_cdn=wow_rootfiles_chashes.root_cdn WHERE filedataid = ? ORDER BY wow_buildconfig.description ASC");

while($row = $dataq->fetch()){
	$contenthashes = array();

	if(!$mv){
		// enc 0 = not encrypted, enc 1 = encrypted, unknown key, enc 2 = encrypted, known key
		$encq->execute([$row['id']]);
		$encr = $encq->fetch();
		if(!empty($encr)){
			$key = $encr['keyname'];
			if(array_key_exists($encr['keyname'], $keys)){
				if(!empty($keys[$encr['keyname']])){
					$enc = 2;
				}else{
					$enc = 1;
				}
			}else{
				$enc = 1;
			}
		}else{
			$enc = 0;
		}

		/* CROSS REFERENCES */
		$xrefs = array();

		// SoundKit
		$soundkitq->execute([$row['id']]);
		$soundkits = $soundkitq->fetchAll();
		if(count($soundkits)){
			$xrefs['soundkit'] = "<b>Part of SoundKit(s):</b><br>";
			foreach($soundkits as $soundkitrow){
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
			$subrow['key'] = $key;
		}

		// Mention firstseen if it is from first casc build
		if($subrow['description'] == "WOW-18125patch6.0.1_Beta"){
			$subrow['firstseen'] = $row['firstseen'];
		}

		$versions[] = $subrow;
	}

	$returndata['data'][] = array($row['id'], $row['filename'], $row['lookup'], array_reverse($versions), $row['type'], $xrefs, $comments);
}

echo json_encode($returndata);
?>