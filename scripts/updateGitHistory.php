<?php
require_once(__DIR__ . "/../inc/config.php");

if(!$memcached->get("github.commits.json") || strtotime("-4 minutes") > $memcached->get("github.commits.lastupdated")){
	$commits = [];

	$i = 0;
	$res = githubRequest("repos/marlamin/wow.tools/commits");
	foreach($res as $commit){
		$commits[] = array("repo" => "Website", "message" => $commit['commit']['message'], "author" => $commit['author']['login'], "timestamp" => strtotime($commit['commit']['author']['date']), "url" => $commit['html_url']);
		$i++;
		if($i > 10) break;
	}

	$i = 0;
	$res = githubRequest("repos/marlamin/casctoolhost/commits");
	foreach($res as $commit){
		$commits[] = array("repo" => "File backend","message" => $commit['commit']['message'], "author" => $commit['author']['login'], "timestamp" => strtotime($commit['commit']['author']['date']), "url" => $commit['html_url']);
		$i++;
		if($i > 10) break;
	}

	$i = 0;
	$res = githubRequest("repos/marlamin/dbcdumphost/commits");
	foreach($res as $commit){
		$commits[] = array("repo" => "DBC backend","message" => $commit['commit']['message'], "author" => $commit['author']['login'], "timestamp" => strtotime($commit['commit']['author']['date']), "url" => $commit['html_url']);
		$i++;
		if($i > 10) break;
	}

	$i = 0;
	$res = githubRequest("repos/wowdev/wowdbdefs/commits");
	foreach($res as $commit){
		$commits[] = array("repo" => "DBC definitions", "message" => $commit['commit']['message'], "author" => $commit['author']['login'], "timestamp" => strtotime($commit['commit']['author']['date']), "url" => $commit['html_url']);
		$i++;
		if($i > 10) break;
	}

	$i = 0;
	$res = githubRequest("repos/marlamin/wowtools.minimaps/commits");
	foreach($res as $commit){
		$commits[] = array("repo" => "Minimap backend", "message" => $commit['commit']['message'], "author" => $commit['author']['login'], "timestamp" => strtotime($commit['commit']['author']['date']), "url" => $commit['html_url']);
		$i++;
		if($i > 10) break;
	}

	usort($commits, "compareTimestamp");
	$memcached->set("github.commits.json", json_encode(array_slice($commits, 0, 10)));
	$memcached->set("github.commits.lastupdated", strtotime("now"));
}