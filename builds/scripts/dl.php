<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

include(__DIR__ . "/../../inc/config.php");
$res = $pdo->query("SELECT * FROM missingfiles ORDER BY type, url ASC");
$cdns[] = "http://blzddist1-a.akamaihd.net/";
$cdns[] = "http://cdn.blizzard.com/";
$cdns[] = "http://level3.blizzard.com/";
$cdns[] = "http://dist.blizzard.com.edgesuite.net/";
$cdns[] = "http://blizzard.dl.llnw.net/";
$cdns[] = "http://client01.pdl.wow.battlenet.com.cn/";
$cdns[] = "http://client04.pdl.wow.battlenet.com.cn/";
$cdns[] = "http://client02.pdl.wow.battlenet.com.cn/";
$cdns[] = "http://client03.pdl.wow.battlenet.com.cn/";
$cdns[] = "http://blzddistkr1-a.akamaihd.net/";
$cdns[] = "http://blizzard.nefficient.co.kr/";

$dq = $pdo->prepare("DELETE FROM missingfiles WHERE url = ?");
$uq = $pdo->prepare("UPDATE missingfiles SET triedcdn = 1 WHERE url = ?");
while ($row = $res->fetch()) {
    // Check CDNs for file once
    if ($row['triedcdn'] == 0) {
        foreach ($cdns as $cdn) {
            echo "Trying " . $cdn . "\n";
            if (!doesLocalFileExist($row['url'])) {
                exec("cd /var/www/wow.tools/; wget -c -x -nvH -t 1 " . $cdn . $row['url']);
            }

            if (doesLocalFileExist($row['url'])) {
                echo "File " . $row['url'] . " (" . $row['type'] . ") succesfully retrieved from " . $cdn . "!\n";
                $dq->execute([$row['url']]);
                break;
            }
        }
        if (!doesLocalFileExist($row['url'])) {
            echo "File " . $row['url'] . " (" . $row['type'] . ") not found! :(\n";
            $uq->execute([$row['url']]);
        }
    }

    if (doesLocalFileExist($row['url'])) {
        echo "File " . $row['url'] . " (" . $row['type'] . ") was found before script was run! YAY! \n";
        $dq->execute([$row['url']]);
    }
}
function doesLocalFileExist($url)
{
    if (file_exists("/var/www/wow.tools/" . $url)) {
        return true;
    } else {
        return false;
    }
}
