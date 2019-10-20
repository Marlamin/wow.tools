<?php require_once("inc/header.php"); ?>
<div class='container-fluid'>
<h3>WoW.tools Uploader</h3>
<p>
Blizzard has started using hotfixes more and more over the years and are now becoming a vital part of WoW's architecture.<br>
As such, sites like MMO-Champion and WoWHead rely on the submissions of user data to fill their databases.<br>
I'm currently working on supporting hotfixes throughout WoW.tools as well, but for this I need a good amount of hotfixes to start testing with.<br><br>
<b>This is where you can help!</b> I've put together an application that keeps an eye on WoW's hotfix file and uploads it after you close WoW.<br>

<?php
if(empty($_SESSION['loggedin'])){
	$token = "<span class='text-danger'>You need to log in to WoW.tools to retrieve your personal API token!</span>";
}else{
	$checkq = $pdo->prepare("SELECT apitoken FROM users WHERE username = ?");
	$checkq->execute([$_SESSION['user']]);
	$token = $checkq->fetchColumn();

	if(empty($token)){
		// User needs token!
		$token = bin2hex(random_bytes(16));
		$settokenq = $pdo->prepare("UPDATE users SET apitoken = ? WHERE username = ?");
		$settokenq->execute([$token, $_SESSION['user']]);
	}
}
?>

<h4>How to install/run the uploader:</h4>
<ol>
	<li>Download <a href='/pub/WoWTools.Uploader.NET48_v1.1.zip'>this zip</a></li>
	<li>Extract it in a place it can safely stay (no installer... yet)</li>
	<li>Run WoWTools.Uploader.exe once to configure it</li>
	<li>Enter your personal API token: <span class='hash'><?=$token?></span>, if you don't fill this in the uploader will give 'Unauthorized' errors!<br>Your WoW.tools UserID will be saved with the uploaded hotfix file to identify users sending in malformed hotfixes. This is hopefully a temporary measure.</li>
	<li>Enter your WoW directory if it's not already filled in</li>
	<li>Press check to check if the directory is correct</li>
	<li>Press Save, the app will restart and move to tray</li>
</ol>

Bug reports/feature requests are very welcome via the regular channels listed at the bottom of <a href='https://wow.tools/faq.php'>the FAQ</a>, I've only tested the application on my machine so it might be broken for you. Please let me know if it is.

</p>
</div>
<?php require_once("inc/footer.php"); ?>