<?php require_once("inc/header.php"); ?>
<div class='container-fluid'>
<h2>Persisting the WoW.tools archive</h2>
<p>WoW.tools started as an archival project for CDN data for all WoW builds starting at 6.0. With the changes on the site <small>(see <a href='/2022.php' target='_BLANK'>2022</a> page)</small> files from older WoW builds will become inaccessible.</p>
<p>Throughout the years I also started collecting other information such as patch server information and information from the game such as hotfixes and other cache data. That will be available on this page for download as well at some point.
<h3>CDN data</h3>
<p>For more information on the structure and file formats of CDN files, check out the WoWDev wiki page for it <a href='https://wowdev.wiki/TACT' target='_BLANK'>here</a>.</p>
<h5>Downloading</h5>
<p>CDN data is stored in exactly the same directory structure as it is on official CDNs, e.g. <kbd>tpr/wow/data/12/34/1234567890abcdef1234567890abcdef</kbd> (example hash).</p> 
<p>If you are interested in mirroring the CDN archive, drop me a line on marlamin@marlamin.com.</p>
<p><i>I am still working on making this available publicly in a way complacent with Cloudflare's Terms of Use. More information on that as soon as I've figured it out.</i></p>
<h5>Integrity</h5>
<p>Integrity of the archive is not verified to be 100% intact, I wish it were but this has been a hobby project for me and there have been problems throughout the years.</p>
<h5>Size</h5>
<p>CDN data is, as of October 29th 2022 around 1.8TB total, but keep in mind there will still be a few 10.0 builds.</p> 
<pre>
449G    ./patch
70M     ./config
1.3T    ./data
</pre>
</p>
<h5>How to extract files from CDN data</h5>
<p><i>Coming in late December/January.</i></p>
<h3>Caches and hotfixes</h3>
<p><i>Coming in late December/January.</i></p>
<h3>Database backup</h3>
<p><i>Coming in late December/January.</i></p>
<h3>Community mirrors</h3>
<p>List of 3rd-party mirrors of WoW.tools data:</p>
<ul>
    <li><b><a href='https://files.gw2archive.eu/wow.tools/' target='_BLANK'>GW2Archive.eu</a></b>: Limited to DBCs, DB2s, game tables and interface files.</li>
</ul>
</div>
<?php require_once("inc/footer.php"); ?>