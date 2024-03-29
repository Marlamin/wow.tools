<?php

require_once(__DIR__ . "/../inc/config.php");

if (!$memcached->get("github.commits.json") || strtotime("-4 minutes") > $memcached->get("github.commits.lastupdated")) {
    $commits = [];

    $repos = [
        ["name" => "Website", "url" => "marlamin/wow.tools"],
        ["name" => "File service", "url" => "marlamin/casctoolhost"],
        ["name" => "Database service", "url" => "marlamin/dbcdumphost"],
        ["name" => "Database definitions", "url" => "wowdev/wowdbdefs"],
        ["name" => "Minimap tools", "url" => "marlamin/wowtools.minimaps"],
        ["name" => "Cache/Hotfix tools", "url" => "marlamin/wowtools.hotfixes"],
        ["name" => "API", "url" => "marlamin/wow.tools.api"],
        ["name" => "Docker (WIP)", "url" => "marlamin/wow.tools-docker"],
    ];

    foreach ($repos as $repo) {
        $i = 0;
        $res = githubRequest("repos/" . $repo['url'] . "/commits");
        foreach ($res as $commit) {
            $commits[] = array("repo" => $repo['name'], "message" => $commit['commit']['message'], "timestamp" => strtotime($commit['commit']['author']['date']), "url" => $commit['html_url']);
            $i++;
            if ($i > 7) {
                break;
            }
        }
    }

    usort($commits, "compareTimestamp");
    $memcached->set("github.commits.json", json_encode(array_slice($commits, 0, 15)));
    $memcached->set("github.commits.lastupdated", strtotime("now"));
}
