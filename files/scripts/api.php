<?php

require_once("/var/www/wow.tools/inc/config.php");
header('Content-Type: application/json');

if (!empty($_GET['tree']) && isset($_GET['depth'])) {
    $treeQ = $pdo->prepare("SELECT DISTINCT(SUBSTRING_INDEX(filename, '/', :depth)) as entry, filename FROM wow_rootfiles WHERE filename LIKE :start AND filename LIKE :filter GROUP BY entry ASC");

    $treeQ->bindParam(":depth", $_GET['depth']);

    if (empty($_GET['start'])) {
        $treeQ->bindValue(":start", "%");
    } else {
        $treeQ->bindValue(":start", $_GET['start'] . "/%");
    }

    if (empty($_GET['filter'])) {
        $treeQ->bindValue(":filter", "%");
    } else {
        $treeQ->bindValue(":filter", "%" . $_GET['filter'] . "%");
    }

    $treeQ->execute();
    echo json_encode($treeQ->fetchAll(PDO::FETCH_ASSOC));
    die();
}

$profiling = false;
if ($profiling) {
    $pdo->exec('set @@session.profiling_history_size = 300;');
    $pdo->exec('set profiling=1');
    $returndata['profiletimings'][] = microtime(true);
}

if (!isset($_SESSION)) {
    // session_start();
}

if (!empty($_GET['src']) && $_GET['src'] == "mv") {
    $mv = true;
} else {
    $mv = false;
}

if (!empty($_GET['src']) && $_GET['src'] == "dbc") {
    $dbc = true;
} else {
    $dbc = false;
}

$keys = array();
$tactq = $pdo->query("SELECT id, keyname, keybytes FROM wow_tactkey");
while ($tactrow = $tactq->fetch()) {
    $keys[$tactrow['keyname']] = $tactrow['keybytes'];
}

if (isset($_GET['switchbuild'])) {
    if (empty($_GET['switchbuild'])) {
        session_start();
        $_SESSION['buildfilterid'] = null;
        session_write_close();
        return;
    } else {
        if (strlen($_GET['switchbuild']) != 32 || !ctype_xdigit($_GET['switchbuild'])) {
            die("Invalid contenthash!");
        }
        $selectBuildFilterQ = $pdo->prepare("SELECT id FROM wow_buildconfig WHERE root_cdn = ? GROUP BY root ORDER BY id ASC");
        $selectBuildFilterQ->execute([$_GET['switchbuild']]);
        $filteredBuildID = $selectBuildFilterQ->fetchColumn();
        if (!empty($filteredBuildID)) {
            session_start();
            $_SESSION['buildfilterid'] = $filteredBuildID;
            session_write_close();
        }
    }
    die();
}

$query = "FROM wow_rootfiles ";


$joinparams = [];
$clauseparams = [];
$clauses = [];
$joins = [];

if (!empty($_SESSION['buildfilterid']) && !$mv && !$dbc) {
    $query .= "JOIN wow_rootfiles_builds_erorus ON ORD(MID(wow_rootfiles_builds_erorus.files, 1 + FLOOR(wow_rootfiles.id / 8), 1)) & (1 << (wow_rootfiles.id % 8)) ";
    array_push($clauses, " wow_rootfiles_builds_erorus.build = ? ");
    $clauseparams[] = $_SESSION['buildfilterid'];
}

if (!empty($_GET['search']['value'])) {
    $criteria = array_filter(explode(",", $_GET['search']['value']), 'strlen');

    $i = 0;
    foreach ($criteria as &$c) {
        $c = strtolower($c);
        if ($c == "unnamed") {
            array_push($clauses, " (wow_rootfiles.filename IS NULL) ");
        } elseif ($c == "communitynames") {
            array_push($clauses, " (wow_rootfiles.filename IS NOT NULL AND verified = 0) ");
        } elseif ($c == "unverified") {
            array_push($clauses, " (wow_rootfiles.lookup != '' AND verified = 0) ");
        } elseif ($c == "unshipped") {
            array_push($clauses, " wow_rootfiles.id NOT IN (SELECT filedataid FROM wow_rootfiles_chashes) ");
        } elseif ($c == "encrypted") {
            if (in_array("unkkey", $criteria)) {
                array_push($clauses, " wow_rootfiles.id IN (SELECT filedataid FROM wow_encrypted WHERE keyname NOT IN (SELECT keyname FROM wow_tactkey WHERE keybytes IS NOT NULL) AND active = 1) ");
                unset($criteria[array_search("unkkey", $criteria)]);
            } else if (in_array("haskey", $criteria)){
                array_push($clauses, " wow_rootfiles.id IN (SELECT filedataid FROM wow_encrypted WHERE keyname IN (SELECT keyname FROM wow_tactkey WHERE keybytes IS NOT NULL) AND active = 1) ");
                unset($criteria[array_search("haskey", $criteria)]);
            } else {
                array_push($clauses, " wow_rootfiles.id IN (SELECT filedataid FROM wow_encrypted WHERE active = 1) ");
            }
        } elseif (substr($c, 0, 10) == "encrypted:") {
            array_push($joins, " INNER JOIN wow_encrypted ON wow_rootfiles.id = wow_encrypted.filedataid AND keyname = ? ");
            $joinparams[] = str_replace("encrypted:", "", $c);
        } elseif (substr($c, 0, 6) == "chash:") {
            array_push($joins, " JOIN wow_rootfiles_chashes ON wow_rootfiles_chashes.filedataid=wow_rootfiles.id AND contenthash = ? ");
            $joinparams[] = str_replace("chash:", "", $c);
        } elseif (substr($c, 0, 5) == "fdid:") {
            array_push($clauses, " (wow_rootfiles.id = ?) ");
            $clauseparams[] = str_replace("fdid:", "", $c);
        } elseif (substr($c, 0, 5) == "type:") {
            array_push($clauses, " (type = ?) ");
            $clauseparams[] = str_replace("type:", "", $c);
        } elseif (substr($c, 0, 5) == "skit:") {
            array_push($joins, " INNER JOIN `wowdata`.soundkitentry ON `wowdata`.soundkitentry.id=wow_rootfiles.id AND `wowdata`.soundkitentry.entry = ? ");
            $joinparams[] = str_replace("skit:", "", $c);
        } elseif (substr($c, 0, 6) == "range:") {
            $explRange = explode("-", str_replace("range:", "", $c));
            if (count($explRange) == 2) {
                array_push($clauses, " (wow_rootfiles.ID BETWEEN ? AND ?) ");
                $clauseparams[] = $explRange[0];
                $clauseparams[] = $explRange[1];
            }
        } else if (substr($c, 0, 3) == "vo:") {
            array_push($joins, " INNER JOIN `wowdata`.soundkitentry ON `wowdata`.soundkitentry.id=wow_rootfiles.id AND `wowdata`.soundkitentry.entry IN (SELECT COALESCE(NULLIF(SoundKit0, 0), NULLIF(SoundKit1, 0)) AS value FROM `wowdata`.broadcasttext WHERE Text LIKE ? OR Text1 LIKE ?)");
            $joinparams[] = "%" . str_replace("vo:", "", $c) . "%";
            $joinparams[] = "%" . str_replace("vo:", "", $c) . "%";
        } else {
            // Point slashes the correct way :)
            $c = trim($c);
            $subquery = "";

            $search = "";
            if (!empty($c)) {
                if ($c[0] != '^') {
                    $search .= "%";
                }

                $search .= str_replace(["^","$"], "", $c);

                if ($c[strlen($c) - 1] != '$') {
                    $search .= "%";
                }
            }

            if ($mv) {
                $subquery = "wow_rootfiles.id = ?";
                $clauseparams[] = $c . "%";
                $types = array();
                if ($_GET['showADT'] == "true") {
                    $types[] = "adt";
                }
                if ($_GET['showWMO'] == "true") {
                    $types[] = "wmo";
                }
                if ($_GET['showM2'] == "true") {
                    $types[] = "m2";
                }
                if (!empty($c)) {
                    $subquery .= " OR wow_rootfiles.filename LIKE ? AND type IN ('" . implode("','", $types) . "')";
                    $clauseparams[] = $search;
                } else {
                    $subquery .= " OR type IN ('" . implode("','", $types) . "')";
                }
                if (!empty($c) && $_GET['showWMO'] == "true") {
                    $subquery .= " AND wow_rootfiles.filename IS NOT NULL AND wow_rootfiles.filename NOT LIKE '%_lod1.wmo' AND wow_rootfiles.filename NOT LIKE '%_lod2.wmo'";
                }
                if ($_GET['showADT'] == "true") {
                    $subquery .= " AND wow_rootfiles.filename NOT LIKE '%_obj0.adt' AND wow_rootfiles.filename NOT LIKE '%_obj1.adt' AND wow_rootfiles.filename NOT LIKE '%_tex0.adt' AND wow_rootfiles.filename NOT LIKE '%_tex1.adt' AND wow_rootfiles.filename NOT LIKE '%_lod.adt'";
                }

                array_push($clauses, " (" . $subquery . ")");
            } elseif ($dbc) {
                array_push($clauses, " (wow_rootfiles.filename LIKE ? AND type = 'db2')");
                $clauseparams[] = $search;
            } else {
                $clauseparams[] = $search;
                $clauseparams[] = $search;
                $clauseparams[] = $search;
                array_push($clauses, " (wow_rootfiles.id LIKE ? OR lookup LIKE ? OR wow_rootfiles.filename LIKE ?) ");
            }
        }
        $i++;
    }
} else {
    if ($mv) {
        $types = array();
        if ($_GET['showADT'] == "true") {
            $types[] = "adt";
        }
        if ($_GET['showWMO'] == "true") {
            $types[] = "wmo";
        }
        if ($_GET['showM2'] == "true") {
            $types[] = "m2";
        }
        $query .= " WHERE type IN ('" . implode("','", $types) . "')";
        if (!empty($_GET['search']['value']) && $_GET['showWMO'] == "true") {
            $query .= " AND wow_rootfiles.filename NOT LIKE '%_lod1.wmo' AND wow_rootfiles.filename NOT LIKE '%_lod2.wmo'";
        }
        if ($_GET['showADT'] == "true") {
            $query .= " AND wow_rootfiles.filename NOT LIKE '%_obj0.adt' AND wow_rootfiles.filename NOT LIKE '%_obj1.adt' AND wow_rootfiles.filename NOT LIKE '%_tex0.adt' AND wow_rootfiles.filename NOT LIKE '%_tex1.adt' AND wow_rootfiles.filename NOT LIKE '%_lod.adt'";
        }
    }
}

$query .= implode(" ", $joins);
if (count($clauses) > 0) {
    $query .= " WHERE " . implode(" AND ", $clauses);
}

$orderby = '';
if (!empty($_GET['order'])) {
    $orderby .= " ORDER BY ";
    switch ($_GET['order'][0]['column']) {
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

    switch ($_GET['order'][0]['dir']) {
        case "asc":
            $orderby .= " ASC";
            break;
        case "desc":
            $orderby .= " DESC";
            break;
    }
}

$start = (int)filter_input(INPUT_GET, 'start', FILTER_SANITIZE_NUMBER_INT);
$length = (int)filter_input(INPUT_GET, 'length', FILTER_SANITIZE_NUMBER_INT);

$params = array_merge($joinparams, $clauseparams);

try {
    $qmd5 = md5($query . implode('|', $params));
    $returndata['rfq'] = $query;
    if (!($returndata['recordsFiltered'] = $memcached->get("query." . $qmd5))) {
        $returndata['rfcachehit'] = false;
        $numrowsq = $pdo->prepare("SELECT COUNT(wow_rootfiles.id) " . $query);
        $numrowsq->execute($params);
        $returndata['recordsFiltered'] = (int)$numrowsq->fetchColumn();
        if (!$memcached->set("query." . $qmd5, $returndata['recordsFiltered'])) {
            $returndata['mc1error'] = $memcached->getResultMessage();
        }
    } else {
        $returndata['rfcachehit'] = true;
    }

    $returndata['fullq'] = "SELECT wow_rootfiles.* " . $query . $orderby . " LIMIT " . $start . ", " . $length;
    $dataq = $pdo->prepare("SELECT wow_rootfiles.* " . $query . $orderby . " LIMIT " . $start . ", " . $length);
    $dataq->execute($params);
} catch (Exception $e) {
    $returndata['data'] = [];
    $returndata['query'] = $query;
    $returndata['params'] = $params;
    $returndata['error'] = "I'm currently working on this functionality right now and broke it. Hopefully back soon. <3";
    echo json_encode($returndata);
    die();
}


if (empty($_GET['draw'])) {
    http_response_code(400);
    die();
}

$returndata['draw'] = (int)$_GET['draw'];

if (!($returndata['recordsTotal'] = $memcached->get("files.total"))) {
    $returndata['rtcachehit'] = false;
    $returndata['recordsTotal'] = $pdo->query("SELECT count(id) FROM wow_rootfiles")->fetchColumn();
    if (!$memcached->set("files.total", $returndata['recordsTotal'])) {
        $returndata['mc2error'] = $memcached->getResultMessage();
    }
} else {
    $returndata['rtcachehit'] = true;
}

$returndata['data'] = array();

$encq = $pdo->prepare("SELECT keyname FROM wow_encrypted WHERE filedataid = ? AND active = 1");

if (!$mv && !$dbc) {
    $soundkitq = $pdo->prepare("SELECT soundkitentry.id as id, soundkitentry.entry as entry, soundkitname.name as name FROM `wowdata`.soundkitentry LEFT JOIN `wowdata`.soundkitname ON soundkitentry.entry=`wowdata`.soundkitname.id WHERE soundkitentry.id = ?");
    $cmdq = $pdo->prepare("SELECT id FROM `wowdata`.creaturemodeldata WHERE filedataid = ?");
    $mfdq = $pdo->prepare("SELECT ModelResourcesID FROM `wowdata`.modelfiledata WHERE FileDataID = ?");
    $tfdq = $pdo->prepare("SELECT MaterialResourcesID FROM `wowdata`.texturefiledata WHERE FileDataID = ?");
    $commentq = $pdo->prepare("SELECT comment, lastedited, users.username as username FROM wow_rootfiles_comments INNER JOIN users ON wow_rootfiles_comments.lasteditedby=users.id WHERE filedataid = ?");
    $bctxtq = $pdo->prepare("SELECT `Text`,Text1 FROM `wowdata`.broadcasttext WHERE SoundKit0 = ? OR SoundKit1 = ?");
}

$cdnq = $pdo->prepare("SELECT cdnconfig FROM wow_versions WHERE buildconfig = ?");
$subq = $pdo->prepare("SELECT wow_rootfiles_chashes.root_cdn, wow_rootfiles_chashes.contenthash, wow_buildconfig.hash as buildconfig, wow_buildconfig.description FROM wow_rootfiles_chashes LEFT JOIN wow_buildconfig on wow_buildconfig.root_cdn=wow_rootfiles_chashes.root_cdn WHERE filedataid = ? ORDER BY wow_buildconfig.description ASC");
while ($row = $dataq->fetch()) {
    $contenthashes = array();
    $cfname = "";
    if ($row['verified'] == 0) {
        $cfname = $row['filename'];
        $row['filename'] = null;
    }
    if (!$mv && !$dbc) {
        // enc 0 = not encrypted, enc 1 = encrypted, unknown key, enc 2 = encrypted, known key, enc 3 = encrypted with multiple keys, some known
        $encq->execute([$row['id']]);

        $encryptedKeyCount = 0;
        $encryptedAvailableKeys = 0;
        $enc = 0;
        $usedkeys = [];
        foreach ($encq->fetchAll(PDO::FETCH_ASSOC) as $encr) {
            $encryptedKeyCount++;
            $usedkeys[] = $encr['keyname'];
            if (array_key_exists($encr['keyname'], $keys)) {
                if (!empty($keys[$encr['keyname']])) {
                    $encryptedAvailableKeys++;
                }
            }
        }

        if ($encryptedKeyCount > 0) {
            if ($encryptedKeyCount == $encryptedAvailableKeys) {
                $enc = 2;
            } else {
                if ($encryptedKeyCount > 1 && $encryptedAvailableKeys > 1) {
                    $enc = 3;
                } else {
                    $enc = 1;
                }
            }
        }

        /* CROSS REFERENCES */
        $xrefs = array();

        // SoundKit
        $soundkitq->execute([$row['id']]);
        $soundkits = $soundkitq->fetchAll();
        if (count($soundkits) > 0) {
            $usedKits = [];
            foreach ($soundkits as $soundkitrow) {
                $kitDesc = $soundkitrow['entry'];
                if (!empty($soundkitrow['name'])) {
                    $kitDesc .= " (" . htmlentities($soundkitrow['name'], ENT_QUOTES) . ")";
                }

                $bctxtq->execute([$soundkitrow['entry'], $soundkitrow['entry']]);
                $bctxts = $bctxtq->fetchAll();
                if (count($bctxts) > 0) {
                    if (!empty($bctxts[0]['Text'])) {
                        $kitDesc .= ": " . htmlentities($bctxts[0]['Text'], ENT_QUOTES);
                    } else if (!empty($bctxts[0]['Text1'])) {
                        $kitDesc .= ": " . htmlentities($bctxts[0]['Text1'], ENT_QUOTES);
                    }
                }
                $usedKits[] = $kitDesc;
            }
            
            $xrefs['soundkit'] = "<b>Part of SoundKit(s):</b> " . implode(", ", $usedKits);
        }

        // Creature Model Data
        $cmdq->execute([$row['id']]);
        $cmdr = $cmdq->fetch();
        if (!empty($cmdr)) {
            $xrefs['cmd'] = "<b>CreatureModelData ID:</b> " . $cmdr['id'] . "<br>";
        }

        // TextureFileData
        $tfdq->execute([$row['id']]);
        $tfdr = $tfdq->fetch();
        if (!empty($tfdr)) {
            $xrefs['tfd'] = "<b>MaterialResourcesID:</b> " . $tfdr['MaterialResourcesID'] . "<br>";
        }

        // ModelFileData
        $mfdq->execute([$row['id']]);
        $mfdr = $mfdq->fetch();
        if (!empty($mfdr)) {
            $xrefs['mfd'] = "<b>ModelResourcesID:</b> " . $mfdr['ModelResourcesID'] . "<br>";
        }

        // Comments
        $commentq->execute([$row['id']]);
        $comments = $commentq->fetchAll();
        if (count($comments) > 0) {
            for ($i = 0; $i < count($comments); $i++) {
                $comments[$i]['username'] = htmlentities($comments[$i]['username'], ENT_QUOTES);
                $comments[$i]['comment'] = htmlentities($comments[$i]['comment'], ENT_QUOTES);
            }
        } else {
            $comments = "";
        }
    } else {
        $enc = 0;
        $xrefs = array();
        $comments = "";
    }

    $versions = array();

    $subq->execute([$row['id']]);

    foreach ($subq->fetchAll() as $subrow) {
        $cdnq->execute([$subrow['buildconfig']]);
        $subrow['cdnconfig'] = $cdnq->fetchColumn();

        if (in_array($subrow['contenthash'], $contenthashes)) {
            continue;
        } else {
            $contenthashes[] = $subrow['contenthash'];
        }

        $subrow['enc'] = $enc;
        if ($enc > 0) {
            $subrow['key'] = implode(", ", $usedkeys);
        }

        // Mention firstseen if it is from first casc build
        if ($subrow['description'] == "WOW-18125patch6.0.1_Beta") {
            $subrow['firstseen'] = $row['firstseen'];
        }

        $parsedBuild = parseBuildName($subrow['description']);

        $subrow['description'] = $parsedBuild['full'];
        $subrow['branch'] = $parsedBuild['branch'];
        
        $versions[] = $subrow;
    }

    $returndata['data'][] = array($row['id'], $row['filename'], $row['lookup'], array_reverse($versions), $row['type'], $xrefs, $comments, $cfname);
}

if ($profiling) {
    $profileq = $pdo->query('show profiles');
    $returndata['profiling'] = $profileq->fetchAll(PDO::FETCH_ASSOC);
    $totalDuration = 0;
    foreach ($returndata['profiling'] as $profile) {
        $totalDuration += $profile['Duration'];
    }
    $returndata['profiletotalquerytime'] = $totalDuration;
    $returndata['profiletimings'][] = microtime(true);
    $pdo->exec('set profiling=0');
}

echo json_encode($returndata);
