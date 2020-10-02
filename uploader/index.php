<?if(!empty($_GET['versionCheck'])){ echo "1.2.2.0"; die();}?>
<?php

require_once("../inc/header.php"); ?>
<div class='container-fluid'>
<h3>WoW.tools Uploader</h3>
<p>
Blizzard has started using hotfixes more and more over the years and are now becoming a vital part of WoW's architecture.<br>
As such, sites like MMO-Champion and Wowhead rely on the submissions of user data to fill their databases.<br>
Support for hotfixes is added throughout WoW.tools as well, but to keep the data up to date I need a good amount of users regularly uploading hotfixes.<br><br>
<b>This is where you can help!</b> I've put together an application that keeps an eye on WoW's hotfix file and uploads it after you close WoW. These files do not contain personal information or anything like that.<br><br>
<img style='width: 400px' src='https://marlamin.com/u/WoWTools.Uploader_2020-04-15_17-00-16-3O5D14QB.png'>
<?php
if (empty($_SESSION['loggedin'])) {
    $token = "<span class='text-danger'>You need to log in to WoW.tools to retrieve your personal API token!</span>";
} else {
    $checkq = $pdo->prepare("SELECT apitoken FROM users WHERE username = ?");
    $checkq->execute([$_SESSION['user']]);
    $token = $checkq->fetchColumn();

    if (empty($token)) {
        // User needs token!
        $token = bin2hex(random_bytes(16));
        $settokenq = $pdo->prepare("UPDATE users SET apitoken = ? WHERE username = ?");
        $settokenq->execute([$token, $_SESSION['user']]);
    }
}
?>

<h4>How to install/run the uploader:</h4>
<ol>
    <li>Download <a href='/pub/WoWTools.Uploader.v1.2.2.0.zip'>this zip</a> (version 1.2.2.0)</li>
    <li>Extract it in a place it can safely stay (no installer... yet)</li>
    <li>Run WoWTools.Uploader.exe once to configure it</li>
    <!-- <li>The API token is already filled in with a standard one for now, but if you want your own token to be be used, use this one: <span class='badge badge-secondary hash'><?=$token?></span></li> -->
    <li>Enter your personal API token: <span class='badge badge-secondary hash'><?=$token?></span>, if you don't fill this in the uploader will give 'Unauthorized' errors!<br><i><small>Your WoW.tools UserID will be saved with the uploaded hotfix file to identify users sending in malformed/private server hotfixes. This is hopefully a temporary measure and only I can map user IDs to users.</small></i></li>
    <li>Enter your WoW directory if it's not already filled in</li>
    <li>Press check to check if the directory is correct</li>
    <li>Press Save, the app will restart and move to tray</li>
</ol>

Bug reports/feature requests are very welcome via the regular channels listed at the bottom of <a href='https://wow.tools/faq.php'>the FAQ</a>, I've only tested the application with a handful of people so there might still be issues.


<br><br><a href='https://github.com/Marlamin/WoWTools.Hotfixes/tree/master/WoWTools.Uploader.NET48' target='_BLANK'>The hotfix uploader is open-source and can be found on GitHub.</a>

</p>
</div>
<?php require_once("../inc/footer.php"); ?>
