<?php
require_once("../inc/header.php");
if(empty($_SESSION['loggedin']) || $_SESSION['rank'] == 0){
	die("<div class='alert alert-danger'>You need to be logged in as a moderator to add/edit tutorials.</div>");
}

if(!empty($_POST)){
	if(!empty($_POST['id'])){
		// Edit
		$tutuq = $pdo->prepare("UPDATE tutorials SET name = :name, content = :content WHERE id = :id");
		$tutuq->bindParam(":name", $_POST['name']);
		$tutuq->bindParam(":content", $_POST['content']);
		$tutuq->bindParam(":id", $_POST['id']);
		$tutuq->execute();
	}else{
		// Add
		$tutiq = $pdo->prepare("INSERT INTO tutorials (name, content) VALUES (?, ?)");
		$tutiq->execute([$_POST['name'], $_POST['content']]);
		echo "<meta http-equiv='refresh' content='0; URL=/help/edit.php?id=" . $pdo->lastInsertId() ."'>";
		die();
	}
}

if(!empty($_GET['id'])) {
	$tutq = $pdo->prepare("SELECT * FROM tutorials WHERE id = ?");
	$tutq->execute([$_GET['id']]);
	$tutorial = $tutq->fetch(PDO::FETCH_ASSOC);
}
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/1.9.0/showdown.min.js"></script>
<div class='container-fluid'>
	<div class='row'>
		<div class='col-md-6'>
			<form action='/help/edit.php<?php if(!empty($tutorial['id'])){ echo "?id=".$tutorial['id']; }?>' method='POST'>
				<?php if(!empty($_GET['id'])){ ?><input type='hidden' name='id' value='<?=$tutorial['id']?>'><?php } ?>
				<label for='name'>Title: </label> <input class='form-control' id='name' type='text' name='name' value='<?php if(!empty($tutorial['name'])){ echo $tutorial['name']; } ?>'><br>
				<textarea id="source" rows="25" cols="100" name='content'><?php if(!empty($tutorial['content'])){ echo $tutorial['content']; }?></textarea><br>
				<input type='submit' class='btn btn-success'>
			</form>
		</div>
		<div class='col-md-6'>
			<div id="target"></div>
		</div>
	</div>
</div>
<script type='text/javascript'>
	$("#source").on('input', function (){
		updatePreview();
	});

	updatePreview();

	function updatePreview(){
		var source = document.getElementById('source');
		var target = document.getElementById('target');
		var converter = new showdown.Converter();
		var html = converter.makeHtml(source.value);
		target.innerHTML = html;
	}
</script>
<?php require_once("../inc/footer.php"); ?>