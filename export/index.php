<?php require_once("../inc/header.php"); ?>
<link href="/export/css/style.css?v=<?=filemtime(__DIR__ . "/css/style.css")?>" rel="stylesheet" type="text/css">
<div class='container-fluid'>
    <div class='row'>
        <div class='col-8 offset-2'>
            <h1>WoW.export</h1>
            <p class="lead">A complete rewrite of WoW Export Tools by <a href='https://twitter.com/kruithne' target='_BLANK'>Kruithne</a></p>
        </div>
    </div>
    <div class='row'>
        <div class='col-8 offset-2'>
            <h4>About</h4>
            <p>WoW.export is a remake of the old WoW OBJ Exporter. WoW.export can be used to export files from WoW to more generic file formats readable by other 3D applications so you can use them in machinima and other types of 3D fan art.</p>
        </div>
    </div>
    <div class='row'>
        <div class='col-8 offset-2'>
            <h4>Screenshots</h4>
            <div id="weCarousel" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img class="d-block w-100" src="/export/img/ss1.png" alt="First slide">
                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="/export/img/ss2.png" alt="Second slide">
                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="/export/img/ss3.png" alt="Third slide">
                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="/export/img/ss4.png" alt="Fourth slide">
                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="/export/img/ss5.png" alt="Fifth slide">
                    </div>
                </div>
                <a class="carousel-control-prev" href="#weCarousel" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#weCarousel" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>
        </div>
    </div
    <br>
    <br>

    <div class='row'>
        <div class='col-8 offset-2'>
            <div class='row'>
                <div class='col-4'>
                    <h4>Supported features</h4>
                    <ul>
                        <li>Supports Retail & Classic versions.</li>
                        <li>Online support to use the tool without WoW installed.</li>
                        <li>Built-in modelviewer for basic previews.</li>
                        <li>OBJ exporting for WoW terrain & models.</li>
                        <li>Sound/music/cinematic exporting.</li>
                        <li>Blender plug-in to import with buildings/doodads.</li>
                        <li>Built-in updater.</li>
                    </ul>
                </div>
                <div class='col-4'>
                    <h4>Planned features</h4>
                    <ul>
                        <li>Character model support.</li>
                        <li>Shadowlands character customization.</li>
                        <li>glTF exports (as alternative to FBX).</li>
                    </ul>
                </div>
                <div class='col-4'>
                    <h4>Won't be supported</h4>
                    <ul>
                        <li>FBX exports (non-open proprietary format).</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class='row'>
        <div class='col-8 offset-2'>
            <p>If you find any issues, please report them on the <a href='https://github.com/Kruithne/wow.export/issues' target='_BLANK'>issue tracker</a> and/or <a href='https://discord.gg/kC3EzAYBtf'>Discord</a>.
            </p>
            <div class='alert alert-danger'>
                <b>Important</b><br>- When setting up, please use a <b>NEW</b> export folder rather than one you used for WoW Export Tools (or other tools) so that in the case of bugs, we're not getting mixed up data.<br>
                - Reinstall the Blender plugin if you have the previous version installed
            </div>
        </div>
    </div>
    <div class='row'>
        <div class='col-8 offset-2'>
            <h4>Tutorial</h4>
            <p><a href='https://twitter.com/CountessBelvane' target='_BLANK'>Belvane</a> (who is the reason why the old exporter and by extension this one exist!) made a tutorial on how to install/use WoW.export as well as importing things into Blender. <a href='https://www.youtube.com/watch?v=ybcq2C93i2k' target='_BLANK'>Watch here.</a></p>
        </div>
    </div>
    <div class='row'>
        <div class='col-8 offset-2'>
            <h4>Download</h4>
            <p>First time installs only, the application has a built-in updater that notifies you when a new version is available.</p>
            <?php 
            $files = glob("download/win-x64/*.zip");
            $files = array_combine($files, array_map("filemtime", $files));
            arsort($files);
            $latestFile = str_replace("download/win-x64/", "", key($files));
            ?>
            <p><a href='https://wow.tools/export/download/win-x64/<?=$latestFile?>' class='btn btn-primary'><i class='fa fa-download'></i> Download <?=str_replace(["wow.export-", ".zip"], "", $latestFile)?></a></p>
            <p>
            <b>Changelog</b></i>
            <?php $changelog = htmlentities(file_get_contents("https://raw.githubusercontent.com/Kruithne/wow.export/master/CHANGELOG.md")); ?>
            <pre id='changelog'><?=$changelog?></pre>
            </p>
        </div>
    </div>
    <div class='row'>
        <div class='col-8 offset-2'>
            <h4>FAQ/Troubleshooting</h4>
            <b>When I import models into Blender many textures are not invisible</b><br>
            Set the Alpha to "None" on the material node to ignore the alpha channel on the texture <a href='https://marlamin.com/u/blender_DlC6CZ2sbR.png'>(image)</a>.
            <br><br>
            <b>WoW.export gives a "Unable to initialize CASC" error</b><br>
            This can have several causes, including, but not limited to:<br>
            - <i>(Local installation)</i> Broken/outdated WoW installations, try running a repair in Battle.net.<br>
            - <i>(CDN/online mode)</i> Network/CDN issues, if available, use a local installation instead. If not, try selecting a different region.<br>
            - If you are behind the not-so-great firewall of China, go to Settings and change the URL in "Data Table Definition Repository" to <kbd>https://wow.tools/dbd/%s.dbd</kbd> <a href='https://marlamin.com/u/wow.export_izgDo2vA7y.png'>(image)</a>.<br>
            <br>
            Other issue? Tell us on <a href='https://discord.gg/kC3EzAYBtf'>Discord</a>. Please share your runtime.log, you can find it by clicking <a href='https://marlamin.com/u/wow.export_AM3IlwOzTx.png'>here in WoW.export</a> or in <kbd>C:\Users\<i><u>[USER]</u></i>\AppData\Local\wow.export\User Data\Default\</kbd>.
        </div>
    </div>
</div>
<footer>
<div class="container text-muted">
    <p>You made it all the way to the bottom of the page, you're pretty cool!</p>
    <p style='font-size: 11px'>World of Warcraft and related trademarks are registered trademarks of Blizzard Entertainment whom this site is not affiliated with.</p>
</div>
</footer>
</body>
</html>