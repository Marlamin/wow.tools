<?php require_once("inc/header.php"); ?>
<div class='container-fluid' style='text-align: center'>
<h2 style='text-align: center'>An update on the future of WoW.tools</h2>
<p style='text-align: center'>
In December 2022, WoW.tools will be going (mostly) read-only. This means no new data will be added to the site and many features will lose the ability to use historical data, only being limited to the last build that was imported.<br>
Some features will be affected before then, more information can be found in the latest update (linked below).<br>For questions/discussion/concerns about this subject you can find a link to the Discord (specifically the #site-future channel) in the August update below.
</p>
<p><b>New: <a class='text-danger' href='https://docs.google.com/document/d/1My-iHd9JLqT_9DH97QGuJZseZ-vCohmDQHz_ZSKs_vc/edit'>August update</a></b><br><small>(older: <a href='https://docs.google.com/document/d/1Ex_A_RmDMxfk4YuX7eQ5U0GB5sJBkLlvvWiSCdc5wR0/edit?usp=sharing'>May 2022 update</a>, <a href='https://docs.google.com/document/d/1jGDkBVbgXNz8BrmWNXQDpAeW9zLkIAj387wn-Bw1s6c/edit' target='_BLANK'>April 2022 update</a>, <a href='https://docs.google.com/document/d/16NgzFhaIfxpgFvDzfzdjwf9UBGVn7Ky6FmNjCiCr_yU/edit' target='_BLANK'>March 2022 update</a>)</small>
</p>
<p>
Below are alternatives (or planned alternatives) for both users and devs. Am I missing something? Let me know in Discord!
</p>
<h2 style='text-align: center'>Alternatives (for users)</h2>
<div class='row'>
    <div class='col'>
        <h4>Files</h4>
        <a href='https://www.kruithne.net/wow.export/' target='_BLANK'>wow.export</a> (<abbr title='Click the top right menu and select "Browse Raw Client files"'>all files</abbr>)<br>
        <a href='https://github.com/WoW-Tools/CASCExplorer/releases' target='_BLANK'>CASCExplorer</a> (all files)<br>
        <a href='http://www.zezula.net/en/casc/main.html' target='_BLANK'>CascView</a> (all files)<br>
    </div>
    <div class='col'>
        <h4>Modelviewer</h4>
        Deamon's standalone viewer (planned)<br>
        <a href='https://wowmodelviewer.net/' target='_BLANK'>WoW Model Viewer</a> (M2 models)<br>
        <a href='https://www.kruithne.net/wow.export/' target='_BLANK'>wow.export</a> (Basic M2/WMO models)<br>
        <a href='https://wowdb.com' target='_BLANK'>WoWDB</a> (Creatures/Items)<br>
        <a href='https://wowhead.com' target='_BLANK'>Wowhead</a> (Creatures/Items)<br>
    </div>
    <div class='col'>
        <h4>DBC browser</h4>
        <a href='https://www.kruithne.net/wow.export/' target='_BLANK'>wow.export</a> (planned)<br>
        <a href='https://github.com/WowDevTools/WDBXEditor' target='_BLANK'>WDBX Editor</a> (possibly outdated)<br>
    </div>
    <div class='col'>
        <h4>DBC exports</h4>
        <a href='https://github.com/Marlamin/DBC2CSV' target='_BLANK'>DBC2CSV</a>
    </div>
    <div class='col'>
        <h4>Minimaps</h4>
        <a href='https://www.kruithne.net/wow.export/' target='_BLANK'>wow.export</a> (in map export tab)<br>
        <a href='https://www.wyrimaps.net/wow' target='_BLANK'>WyriMaps.net</a><br>
        <a href='https://worldofmapcraft.com/' target='_BLANK'>World of MapCraft</a> (outdated)
    </div>
    <div class='col'>
        <h4>Monitor</h4>
        <a href='https://blizztrack.com/' target='_BLANK'>BlizzTrack</a>
    </div>
</div>
<h2>Alternatives (for devs)</h2>
<div class='row'>
    <div class='col'>
        <h4>Listfile</h4>
        <a href='https://github.com/wowdev/wow-listfile/commits/master' target='_BLANK'>GitHub (mirror only for now)</a>
    </div>
    <div class='col'>
        <h4>Database definitions</h4>
        Will remain <a href='https://github.com/wowdev/WoWDBDefs/commits/master' target='_BLANK'>on GitHub</a>, <a href='https://github.com/wowdev/WoWDBDefs/blob/master/UPDATING.md' target='_BLANK'>auto-updating</a> needs to move
    </div>
    <div class='col'>
        <h4>DBC reading</h4>
        <a href='https://github.com/wowdev/DBCD' target='_BLANK'>DBCD</a> (C#)<br>
        <a href='https://github.com/erorus/db2' target='_BLANK'>Erorus' DB2 reader</a> (PHP)<br>
        <a href='https://www.townlong-yak.com/casc/dbc/' target='_BLANK'>LuaDBC</a> (Lua)<br>
    </div>
    <div class='col'>
        <h4>DBC export API</h4>
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
        <h4>World maps</h4>
        <a href='https://github.com/Marlamin/WorldMapCompiler/releases' target='_BLANK'>WorldMapCompiler</a>
    </div>
</div><br>
<h2 style='text-align: center'>Reasoning</h2>
<p>I could (and might still) write up a full post-mortem on this after this is all behind me, but know there's more than enough reason and thinking that have gone into this decision, most of the reasons are my fault alone while others not so much.
<br>I am willing to put <i>some</i> time into making the transition as less annoying for people as possible, hence the above list of alternatives. If there's anything you are particularly worried about missing or want to help with/take over, let me know in Discord/on Twitter.</p>
</div>
<?php require_once("inc/footer.php"); ?>