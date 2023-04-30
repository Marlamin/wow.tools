<?php require_once("inc/header.php"); ?>
<div class='container-fluid' style='text-align: center'>
<p style='text-align: center'>
WoW.tools is now mostly read-only. This means no new builds or other data will be added/processed.<br>
</p>
<p><b>Latest: <a class='text-danger' href='https://docs.google.com/document/d/1xoJxdiSb4qYZyVkqCS8QR9S2c5HUAtffbiQs3gB-3yw'>December 2022 update</a></b><br><small>(older updates: <a href='https://docs.google.com/document/d/1jMTwBemRyScqXKJ4NK51krMQ2wLQpqH-cP1B8RwwUFc'>November</a>, <a href='https://docs.google.com/document/d/1XBQyRsgtnMLsxmJDFQFwJNxAJ0QmGm50qPK8uTIT3Ig'>October</a>, <a href='https://docs.google.com/document/d/1My-iHd9JLqT_9DH97QGuJZseZ-vCohmDQHz_ZSKs_vc/edit'>August</a>, <a href='https://docs.google.com/document/d/1Ex_A_RmDMxfk4YuX7eQ5U0GB5sJBkLlvvWiSCdc5wR0/edit?usp=sharing'>May</a>, <a href='https://docs.google.com/document/d/1jGDkBVbgXNz8BrmWNXQDpAeW9zLkIAj387wn-Bw1s6c/edit' target='_BLANK'>April</a>, <a href='https://docs.google.com/document/d/16NgzFhaIfxpgFvDzfzdjwf9UBGVn7Ky6FmNjCiCr_yU/edit' target='_BLANK'>March</a>)</small>
</p>
<p>
Below are alternatives (or planned alternatives) for both users and devs.
</p>
<hr>
<h2 style='text-align: center'>Alternatives (for users)</h2>
<div class='row'>
    <div class='col'>
        <h4>Files</h4>
        <a href='https://wago.tools/files' target='_BLANK'>wago.tools</a><br>
        <a href='https://github.com/Marlamin/wow.tools.local/blob/main/README.md' target='_BLANK'>Local WoW.tools</a><br>
        <a href='https://www.kruithne.net/wow.export/' target='_BLANK'>wow.export</a> (<abbr title='Click the top right menu and select "Browse Raw Client files"'>Hover for help</abbr>)<br>
        <a href='https://github.com/WoW-Tools/CASCExplorer/releases' target='_BLANK'>CASCExplorer</a><br>
        <a href='http://www.zezula.net/en/casc/main.html' target='_BLANK'>CascView</a><br>
    </div>
    <div class='col'>
        <h4>Modelviewer</h4>
        Deamon's standalone viewer (planned)<br>
        <a href='https://github.com/Marlamin/wow.tools.local/blob/main/README.md' target='_BLANK'>Local WoW.tools</a><br>
        <a href='https://wowmodelviewer.net/' target='_BLANK'>WoW Model Viewer</a> (M2 models)<br>
        <a href='https://www.kruithne.net/wow.export/' target='_BLANK'>wow.export</a> (Basic M2/WMO models)<br>
        <a href='https://wowdb.com' target='_BLANK'>WoWDB</a> (Creatures/Items)<br>
        <a href='https://wowhead.com' target='_BLANK'>Wowhead</a> (Creatures/Items)<br>
    </div>
    <div class='col'>
        <h4>DBC browser</h4>
        <b><a href='https://wago.tools/db2' target='_BLANK'>wago.tools</a></b><br>
        <a href='https://github.com/Marlamin/wow.tools.local/blob/main/README.md' target='_BLANK'>Local WoW.tools</a><br>
        <a href='https://www.kruithne.net/wow.export/' target='_BLANK'>wow.export</a> (planned)<br>
    </div>
    <div class='col'>
        <h4>DBC exporting</h4>
        <a href='https://wago.tools/files' target='_BLANK'>wago.tools</a><br>
        <a href='https://github.com/Marlamin/wow.tools.local/blob/main/README.md' target='_BLANK'>Local WoW.tools</a><br>
        <a href='https://github.com/Marlamin/DBC2CSV' target='_BLANK'>DBC2CSV</a><i> (see <a href='https://docs.google.com/document/d/1jMTwBemRyScqXKJ4NK51krMQ2wLQpqH-cP1B8RwwUFc'>November update</a> for tutorial)</i>
    </div>
    <div class='col'>
        <h4>Minimap viewing</h4>
        <a href='https://www.kruithne.net/wow.export/' target='_BLANK'>wow.export</a> (in map export tab)<br>
        <a href='https://www.wyrimaps.net/wow' target='_BLANK'>WyriMaps.net</a><br>
    </div>
    <div class='col'>
        <h4>Monitor</h4>
        <a href='https://blizztrack.com/' target='_BLANK'>BlizzTrack</a><br>
        <a href='https://twitter.com/algalon_ghost'>@algalon_ghost on Twitter</a>
    </div>
</div>
<hr>
<h2>Alternatives (for devs)</h2>
<div class='row'>
    <div class='col'>
        <h4>Listfile</h4>
        <a href='https://github.com/wowdev/wow-listfile/commits/master' target='_BLANK'>GitHub</a>
    </div>
    <div class='col'>
        <h4>Database definitions</h4>
        <a href='https://github.com/wowdev/WoWDBDefs/commits/master' target='_BLANK'>GitHub</a>
    </div>
    <div class='col'>
        <h4>DBC reading</h4>
        <a href='https://github.com/wowdev/DBCD' target='_BLANK'>DBCD</a> (C#)<br>
        <a href='https://github.com/erorus/db2' target='_BLANK'>Erorus' DB2 reader</a> (PHP)<br>
        <a href='https://www.townlong-yak.com/casc/dbc/' target='_BLANK'>LuaDBC</a> (Lua)<br>
    </div>
    <div class='col'>
        <h4>DBC export API</h4>
        <a href='https://wago.tools/db2/' target='_BLANK'>wago.tools</a> (append /csv to URLs)<br>
        <a href='https://github.com/Marlamin/DBC2CSV' target='_BLANK'>DBC2CSV</a>
    </div>
    <div class='col'>
        <h4>Builds API</h4>
        <a href='https://blizztrack.com/docs'>BlizzTrack API</a><br>
        DIY: <a href='https://wowdev.wiki/Ribbit' target='_BLANK'>Ribbit</a>
    </div>
    <div class='col'>
        <h4>Cache data</h4>
        <a href='https://github.com/MMOSimca/SaneWDBReader'>SaneWDBReader</a><br>
    </div>
    <div class='col'>
        <h4>Maps</h4>
        <a href='https://wago.tools/maps/worldmap' target='_BLANK'>wago.tools</a> (world maps only)<br>
        <a href='https://github.com/Marlamin/WoWTools.Minimaps' target='_BLANK'>WoW.tools minimap tools</a> (minimaps only)<br>
        <a href='https://github.com/Marlamin/WorldMapCompiler/releases' target='_BLANK'>WorldMapCompiler</a> (world maps only)
    </div>
</div>
<hr>
<p style='text-align: center'>
If any alternatives are missing, e-mail me at marlamin@marlamin.com. Thanks for using and supporting WoW.tools all these years, it's been a blast. ♥</p>
</div>
<?php require_once("inc/footer.php"); ?>
