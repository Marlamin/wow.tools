<?php require_once(__DIR__ . "/config.php");?><!DOCTYPE html>
<html>
<head>
	<title><?=prettyTitle($_SERVER['REQUEST_URI'])?></title>
	<?=generateMeta($_SERVER['REQUEST_URI'])?>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<link rel="icon" type="image/png" href="/img/cogw.png" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
	<link href="/css/style.css?v=<?=filemtime("/var/www/wow.tools/css/style.css")?>" rel="stylesheet">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/js/main.js?v=<?=filemtime("/var/www/wow.tools/js/main.js")?>"></script>
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
		ga('set', 'anonymizeIp', true);
		ga('create', 'UA-8420950-6', 'auto');
		ga('send', 'pageview');
	</script>
</head>
<body>
	<nav class="navbar navbar-expand-lg">
		<a class="navbar-brand" href="/">
			<div id='logo'>
				<div id='cog'>&nbsp;</div>
				<div id='nocog'><img src='/img/w.svg'><img src='/img/w.svg'><span>.tools</span></div>
			</div>
		</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			<i class='fa fa-bars'></i>
		</button>
		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav mr-auto mt-2 mt-md-0">
				<?php $page = basename($_SERVER["SCRIPT_FILENAME"], '.php'); ?>
				<li class="nav-item<?php if($page == "files"){ echo " active"; } ?>">
					<a class="nav-link" href="/files/"><i class="fa fa-files-o fa-lg" aria-hidden="true"></i> Files</a>
				</li>
				<li class="nav-item<?php if($page == "dbc"){ echo " active"; } ?> dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="navDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="fa fa-table fa-lg" aria-hidden="true"></i> Database
					</a>
					<div class="dropdown-menu" aria-labelledby="navDropdown">
						<a class="dropdown-item" href="/dbc/">Browse table</a>
						<a class="dropdown-item" href="/dbc/diff.php">Compare tables</a>
					</div>
				</li>
				<li class="nav-item<?php if($page == "mv"){ echo " active"; } ?>">
					<a class="nav-link" href="/mv/"><i class="fa fa-cube fa-lg" aria-hidden="true"></i> Models</a>
				</li>
				<li class="nav-item<?php if($page == "maps"){ echo " active"; } ?>">
					<a class="nav-link" href="/maps/"><i class="fa fa-map-o fa-lg" aria-hidden="true"></i> Maps</a>
				</li>
				<li class="nav-item<?php if($page == "monitor"){ echo " active"; } ?>">
					<a class="nav-link" href="/monitor/"><i class="fa fa-search fa-lg" aria-hidden="true"></i> Monitor</a>
				</li>
				<li class="nav-item<?php if($page == "mirror"){ echo " active"; } ?>">
					<a class="nav-link" href="/builds/"><i class="fa fa-hdd-o fa-lg" aria-hidden="true"></i> Builds</a>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="navDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="fa fa-cog fa-lg" aria-hidden="true"></i> Experimental
					</a>
					<div class="dropdown-menu" aria-labelledby="navDropdown">
						<a class="dropdown-item" href="/realms/">Realm status</a>
						<a class="dropdown-item" href="/dbc/hotfixes.php">Hotfix diffs</a>
					</div>
				</li>
			</ul>
			<form class="form-inline my-md-2 my-lg-0">
				<button id="themeToggle" type="button" class="btn btn-sm btn-outline-secondary" data-toggle="button">
					Toggle theme
				</button>&nbsp;
				<?php if(empty($_SESSION['loggedin']) || (!empty($_GET['p']) && $_GET['p'] == "logout")){ ?>
					<a href='/user.php?p=login' class='btn btn-sm align-middle btn-outline-success'>Log in</a>
				<?php }else{ ?>
					<a href='/user.php?p=logout' class='btn btn-sm align-middle btn-outline-danger'>Log out</a>
				<?php } ?>
			</form>
		</div>
	</nav>
