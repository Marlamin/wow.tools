<?if(!empty($_GET['versionCheck'])){ echo "1.2.3.0"; die();}?>
<?php

require_once("../inc/header.php"); ?>
<div class='container-fluid'>
<h3>WoW.tools Uploader</h3>
<p>
Blizzard has started using hotfixes more and more over the years and are now becoming a vital part of WoW's architecture, as such, sites like WoWDB and Wowhead rely on the submissions of user data to fill their databases.<br>
Support for hotfixes is added throughout WoW.tools as well, but to keep the data up to date I need a good amount of users regularly uploading hotfixes.<br>
<b>This is where you can help!</b> I've put together an application that keeps an eye on WoW's hotfix/cache files and uploads them after you close WoW.<br><br>
<img style='width: 400px' src='https://marlamin.com/u/WoWTools.Uploader_EGPYOOi8iO.png'>
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
    <li>Download <a href='/pub/WoWTools.Uploader.v1.2.3.0.zip'>this ZIP</a> (version 1.2.3.0)</li>
    <li>Extract it in a place it can safely stay (no installer... yet)</li>
    <li>Run WoWTools.Uploader.exe once to configure it</li>
    <li>Enter your personal API token: <span class='badge badge-secondary hash'><?=$token?></span>, if you don't fill this in the uploader will give 'Unauthorized' errors!<br><i><small>Your WoW.tools UserID will be saved with the uploaded files to identify users sending in malformed/private server hotfixes.</small></i></li>
    <li>Enter your WoW directory if it's not already filled in</li>
    <li>Press check to check if the directory is correct</li>
    <li>Choose whether or not you want the uploader to run at startup. This is recommended as it'll upload automatically after WoW closes.</li>
    <li>If you want to do so, you can choose to upload in-game data gathered by the WoWDB Profiler/Wowhead Looter addon.<br><i><small>This data contains your character name and realm. While not used by WoW.tools, this is still included as the uploader does not modify any files.</small></i></li>
    <li>Press Save, the app will restart and move to tray</li>
</ol>

<h4>Updates</h4>
<p>To update an existing installation, simply download the above ZIP file and extract it over your current installation.</p>

<h4>Manual uploads</h4>
<p>To do manual uploads, you can right click the icon and choose which WoW client you want to upload data for. <kbd>wow</kbd> is retail WoW, <kbd>wowt</kbd> is PTR and so forth.</p>

<h4>Bug reports/feature requests></h4>
<p>Bug reports/feature requests are very welcome via the regular channels listed at the bottom of <a href='https://wow.tools/faq.php'>the FAQ</a>, I've only tested the application with a handful of people so there might still be issues.</p>

<h4>Open source</h4>
<p>The hotfix uploader is open-source and can be found <a href='https://github.com/Marlamin/WoWTools.Hotfixes/tree/master/WoWTools.Uploader.NET48' target='_BLANK'>on GitHub</a>.</p>

</p>
</div>
<?php require_once("../inc/footer.php"); ?>
