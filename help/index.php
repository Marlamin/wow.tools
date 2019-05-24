<?php require_once("../inc/header.php"); ?>
<div class='container-fluid'>
<p>
Welcome to the help part of the site. This page will host an increasing amount of manuals/guides/tutorials on using some of the tools on the site.<br>
Please let me know if you have any suggestions!
</p>
<div class='row'>
<div class='col-md-4'>
<h4>Generic</h4>
<p>Tutorials covering things that utilize multiple parts of the site</p>
<ul><?php foreach($pdo->query("SELECT * FROM tutorials WHERE category = 'generic'") as $tutorial){ ?>
	<li><a href='/help/view.php?id=<?=$tutorial['id']?>'><?=$tutorial['name']?></a></li>
<?php } ?>
</ul>
</div>
</div>
</div>
<?php require_once("../inc/footer.php"); ?>