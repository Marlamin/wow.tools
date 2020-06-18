<?php require_once("../inc/header.php"); ?>
<?php if(empty($_GET['id'])){ die ("No ID given!"); } ?>
<?php $tutq = $pdo->prepare("SELECT * FROM tutorials WHERE id = ?"); $tutq->execute([$_GET['id']]); $tutorial = $tutq->fetch(PDO::FETCH_ASSOC); ?>
<?php

if(!empty($tutorial)){
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/1.9.0/showdown.min.js"></script>
<div class='container-fluid'>
<?php if(!empty($_SESSION['loggedin']) && $_SESSION['rank'] > 0){ ?><a href='/help/edit.php?id=<?=$_GET['id']?>' class='btn btn-primary'>Edit</a><br><br><?php } ?>
<h4><?=$tutorial['name']?></h4>
<div id='raw' style='display: none'><?=$tutorial['content']?></div>
<p id='target'></p>
</div>
<script type='text/javascript'>
	var converter = new showdown.Converter();
	var html = converter.makeHtml(document.getElementById("raw").innerHTML);
	document.getElementById("target").innerHTML = html;
</script>
<?php } ?>
<?php require_once("../inc/footer.php"); ?>