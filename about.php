<?php require_once("inc/header.php"); ?>
<div class='container-fluid'>
<h3>Privacy Policy</h3>
<h5>Logs</h5>
<p>
All requests are logged to monitor for (and prevent) abuse. Logged information includes IP address, the page you visited and with what browser you did so. The site also uses Google Analytics (with anonymized IPs).
</p>
<h5>Accounts</h5>
If you create an account on the site, the following information is stored:
<ul>
    <li>Username</li>
    <li>A salted hash of your password (generated with bcrypt)</li>
    <li>E-mail (for account recovery)</li>
</ul>
<h3>Open source</h3>
The site and the tools powering the site are open source.
<ul>
<li><a href='https://github.com/Marlamin/wow.tools/' target='_BLANK'>wow.tools</a> (PHP) - Website code.</li>
<li><a href='https://github.com/Marlamin/wow.tools.api/' target='_BLANK'>wow.tools.api</a> (C#) - API powering some DBC/tooltip stuff.</li>
<li><a href='https://github.com/Marlamin/WoWFormatTest/tree/master/WoWJsonDumper' target='_BLANK'>WoWJsonDumper</a> (C#) - Utility powering "raw" M2 dumps.</li>
<li><a href='https://github.com/Marlamin/BuildBackup/' target='_BLANK'>BuildBackup</a> (C#) - Makes backups of builds on Blizzard's CDN.</li>
<li><a href='https://github.com/Marlamin/CASCToolHost/' target='_BLANK'>CASCToolHost</a> (C#) - Powers listfile generation, file downloads and previews.</li>
<li><a href='https://github.com/Marlamin/DBCDumpHost/' target='_BLANK'>DBCDumpHost</a> (C#) - Powers database file browsing, searching and lookups for the modelviewer. Uses <a href='https://github.com/wowdev/DBCD' target='_BLANK'>DBCD</a> (C#) for DBC/DB2 reading.</li>
<li><a href='https://github.com/Marlamin/WoWTools.Hotfixes/' target='_BLANK'>Hotfix uploader (+dumper)</a> (C#) - Automated hotfix uploading as well as tools for dumping them.</li>
<li><a href='https://github.com/Marlamin/WoWTools.Minimaps/' target='_BLANK'>Minimap tools</a> (C#) - Minimap extraction and compilation tools.</li>
<li><a href='https://github.com/Marlamin/WorldMapCompiler/' target='_BLANK'>WorldMapCompiler</a> (C#) - Compiles in-game world maps into single images.</li>
<li><a href='https://github.com/wowdev/WoWDBDefs/' target='_BLANK'>WoWDBDefs</a> - Definitions for database files.</li>
</ul>
<h4>Third party</h4>
<ul>
    <li><a href='https://github.com/Kruithne/node-bufo' target='_BLANK'>Bufo</a> and <a href='https://github.com/Kruithne/js-blp/' target='_BLANK'>js-blp</a> by Kruithne for client-side BLP conversion</li>
    <li>Expansion icons by Wowpedia & schlumpf</li>
</ul>
<h3>Legal stuff</h3>
<p>WoW and as such all content from WoW is copyrighted and owned by Blizzard. This site is neither endorsed or affiliated with Blizzard or any of its partners.</p>
</div>
<?php require_once("inc/footer.php"); ?>