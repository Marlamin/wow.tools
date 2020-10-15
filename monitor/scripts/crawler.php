<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

require_once(__DIR__ . "/../../inc/config.php");
ini_set('memory_limit', '2048M');
$ngdpurls[] = array("name" => "Versions", "url" => "http://us.patch.battle.net:1119/%program%/versions");
$ngdpurls[] = array("name" => "CDNs", "url" => "http://us.patch.battle.net:1119/%program%/cdns");
$ngdpurls[] = array("name" => "BGDL", "url" => "http://us.patch.battle.net:1119/%program%/bgdl");
$ngdpurls[] = array("name" => "Install", "url" => "http://us.patch.battle.net:1119/%program%/blob/install");
$ngdpurls[] = array("name" => "Blobs", "url" => "http://us.patch.battle.net:1119/%program%/blobs");
$DEBUG = false;

function MessageDiscord($product, $message)
{
    global $discord;
    global $pdo;
    $channelToUse = $discord['not-wow'];
    foreach ($discord as $discordChannel) {
        if (in_array($product, $discordChannel['products'])) {
            $channelToUse = $discordChannel;
        }
    }

    $uq = $pdo->prepare("SELECT name FROM ngdp_products WHERE program = ?");
    $uq->execute([$product]);
    $name = $uq->fetch(PDO::FETCH_COLUMN);
    if (empty($name)) {
        $username = "Unknown";
    } else {
        $username = $name;
    }

    $json = json_encode([ "username" => $username, "content" => $message]);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $channelToUse['url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_USERAGENT, "Blizzard Monitor Discord Integration");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Length: " . strlen($json), "Content-Type: application/json"]);
    $response = curl_exec($ch);
    curl_close($ch);
}

function getIDByURL($url)
{

    global $pdo;
    $urlq = $pdo->prepare("SELECT id FROM ngdp_urls WHERE url = ?");
    $urlq->execute([$url]);
    $urls = $urlq->fetchAll();
    if (count($urls) > 0) {
        $row = $urls[0];
        return $row['id'];
    } else {
        return false;
    }
}

function getUrlHistory($id)
{

    global $pdo;
    $ret = array();
    $res = $pdo->prepare("SELECT newvalue FROM ngdp_history WHERE url_id = ? AND event = 'valuechange' ORDER BY `timestamp` DESC LIMIT 1");
    $res->execute([$id]);
    $lastval = $res->fetch();
    if (!empty($lastval)) {
        $ret['lastcontent'] = $lastval['newvalue'];
    } else {
        $ret['lastcontent'] = null;
    }

    $res = $pdo->prepare("SELECT newvalue FROM ngdp_history WHERE url_id = ? AND event = 'statuschange' ORDER BY `timestamp` DESC LIMIT 1");
    $res->execute([$id]);
    $laststatus = $res->fetch();
    if (!empty($laststatus)) {
        $ret['laststatus'] = $laststatus['newvalue'];
    } else {
        $ret['laststatus'] = 0;
    }

    return $ret;
}

function parseNGDPcontentToArray($content)
{

    $lines = explode("\n", $content);
    $ngdp = array();
    $header = [];
    foreach ($lines as $num => $line) {
        $cols = explode("|", $line);
        if ($num == 0) {
            foreach ($cols as $col) {
                $innercol = explode("!", $col);
                $header[] = $innercol[0];
            }
        } else {
            if (empty(trim($cols[0]))) {
                continue;
            }
            foreach ($cols as $colnum => $col) {
                $ngdp[$num][$header[$colnum]] = $col;
            }
        }
    }
    return $ngdp;
}

function diffNGDParrays($old, $new)
{

    $msg = "";
    foreach ($old as $oldindex => $oldline) {
        foreach ($oldline as $oldcolindex => $oldcol) {
            if ($new[$oldindex][$oldcolindex] != $oldcol) {
                if (!empty($new[$oldindex]['Region'])) {
                    $region = $new[$oldindex]['Region'];
                } elseif (!empty($new[$oldindex]['Name'])) {
                    $region = $new[$oldindex]['Name'];
                } else {
                    $region = 'ERR';
                }

                if ($region[0] == "#") {
                    continue;
                }

                if (strlen($new[$oldindex][$oldcolindex]) == 32 || strlen($oldcol) == 32) {
                    $msg .= "(" . $region . ") " . $oldcolindex . ": " . substr($oldcol, 0, 7) . " → " . substr($new[$oldindex][$oldcolindex], 0, 7) . "\n";
                } else {
                    $msg .= "(" . $region . ") " . $oldcolindex . ": " . $oldcol . " → " . $new[$oldindex][$oldcolindex] . "!\n";
                }
            }
        }
    }

    return $msg;
}

$badhttpcodes = array("0", "404");
foreach ($pdo->query("SELECT * FROM ngdp_products") as $prog) {
    foreach ($ngdpurls as $url) {
        $url['url'] = str_replace("%program%", $prog['program'], $url['url']);
        $id = getIDByURL($url['url']);
        if (!$id) {
            echo "need to insert: " . $prog['name'];
            $iq = $pdo->prepare("INSERT INTO ngdp_urls (name, type, url) VALUES (? , 'ngdp', ?)");
            $name = $prog['name'] . " " . $url['name'];
            $iq->execute([$name, $url['url']]);
            telegramSendMessage("Now tracking " . $name);
        }
    }
}

$histstatq = $pdo->prepare("INSERT INTO ngdp_history (url_id, event, oldvalue, newvalue) VALUES (?, 'statuschange', ?, ?)");
$histvalq = $pdo->prepare("INSERT INTO ngdp_history (url_id, event, oldvalue, newvalue) VALUES (?, 'valuechange', ?, ?)");
$checkq = $pdo->prepare("SELECT * FROM ngdp_history WHERE url_id = ? AND event = 'valuechange' AND oldvalue = ? AND newvalue = ?");
foreach ($pdo->query("SELECT * FROM ngdp_urls WHERE enabled = 1") as $row) {
    $history = getUrlHistory($row['id']);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $row['url'] . "?cb=" . time());
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $content = curl_exec($ch);
    $httpcode =  curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (empty($history['laststatus'])) {
        $history['laststatus'] = '0';
    }

    if ($history['laststatus'] != $httpcode) {
        if (($history['laststatus'] == 0 || $httpcode == 0) || ($history['laststatus'] == 200 && $httpcode == 404) || ($httpcode == 200 && $history['laststatus'] == 404)) {
        // bad cdn
        } else {
            $histstatq->execute([$row['id'], $history['laststatus'], $httpcode]);
            telegramSendMessage($row['url'] . " (" . $row['name'] . ") status: " . $history['laststatus'] . " → " . $httpcode . "\nhttps://wow.tools/monitor/");
        }
    }

    if (!in_array($httpcode, $badhttpcodes)) {
        //Valid HTTP code, lets check contents!
        if (!empty($content) && $history['lastcontent'] != $content) {
            $checkq->execute([$row['id'], $history['lastcontent'], $content]);
            if (count($checkq->fetchAll()) == 0) {
                $histvalq->execute([$row['id'], $history['lastcontent'], $content]);
                if (!json_decode($content)) {
                    $msg = diffNGDParrays(parseNGDPcontentToArray($history['lastcontent']), parseNGDPcontentToArray($content));
                    $product = explode("/", str_replace("http://us.patch.battle.net:1119/", "", $row['url']));
                    MessageDiscord($product[0], $msg);
                    telegramSendMessage(str_replace("http://us.patch.battle.net:1119/", "", $row['url']) . "\n```\n" . $msg . "```\n https://wow.tools/monitor/");
                } else {
                    telegramSendMessage("Change detected: " . $row['url'] . "\nhttps://wow.tools/monitor/");
                }
            }
        }
    }
}
