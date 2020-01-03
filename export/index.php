<?php require_once("../inc/header.php"); ?>
<link href="/export/css/style.css?v=<?=filemtime(__DIR__ . "/css/style.css")?>" rel="stylesheet" type="text/css">
<div class='container-fluid'>
	<div class='row'>
		<div class='col-8 offset-2'>
			<h1>WoW.export public beta</h1>
			<p class="lead">A complete rewrite of <a href='https://marlam.in/obj/'>WoW Export Tools</a> now available for public beta testing</p>
		</div>
	</div>
	<div class='row'>
		<div class='col-8 offset-2'>
			<h4>About</h4>
			<p>The old exporter was hard to maintain and rather unstable when doing certain things (e.g, baking terrain textures). After being repeatedly frustrated by modifying things in that exporter, <a href='https://twitter.com/kruithne' target='_BLANK'>Kruithne</a> set out to build a modern, more maintainable/future-proof and generally better alternative to the currently available version of WoW Export Tools.</p>
		</div>
	</div>
	<div class='row'>
		<div class='col-8 offset-2'>
			<h4>Features</h4>
			<p>All the features you know and love from the old exporter are present, new features include:</p>
			<ul>
				<li>New look based on WoW.tools</li>
				<li>Built-in updater</li>
				<li>Modelviewer with better controls</li>
				<li>Better sound/music player</li>
			</ul>
				Features from the old exporter still in development:
				<ul>
					<li>Terrain foliage exports</li>
					<li>Unnamed models/textures in file list</li>
				</ul>
		</div>
	</div>
	<div class='row'>
		<div class='col-8 offset-2'>
			<h4>Beta</h4>
			<p>We feel that this version is now feature-complete enough to start testing it publicly. Please report issues on the <a href='https://github.com/Kruithne/wow.export/issues' target='_BLANK'>issue tracker</a> or <a href='https://discord.gg/52mHpxC'>Discord</a>.
			</p>
			<div class='alert alert-danger'>
				<b>Important</b><br>- When setting up, please use a <b>NEW</b> export folder rather than one you used for WoW Export Tools (or other tools) so that in the case of bugs, we're not getting mixed up data.<br>
				- Reinstall the Blender plugin if you have the previous version installed
			</div>
		</div>
	</div>
	<div class='row'>
		<div class='col-8 offset-2'>
			<h4>Download</h4>
			<p>First time installs only, the application has a built-in updater that notifies you when a new version is available.</p>
			<p><a href='https://wow.tools/export/download/win-x64/wow.export-0.1.5.zip' class='btn btn-primary'>Download</a></p>
			<p>
			<b>Changelog</b>
			<pre><b>0.1.5</b> (03-01-2020)
- Fix WMO-only Blender import doodad rotations

<b>0.1.4</b> (03-01-2020)
- Fix issue that prevented encryption keys from properly updating
- Fix BLTE offset issue that caused partialDecrypt blocks to infinite loop which could cause hangs when listing maps

<b>0.1.3</b> (02-01-2020)
- Initial public beta</pre>
			</p>
		</div>
	</div>
</div>
<?php require_once("../inc/footer.php"); ?>