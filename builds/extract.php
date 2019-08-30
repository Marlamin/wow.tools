<?php
include("../inc/config.php");
include("../inc/header.php");
?>
<div class="container-fluid">
	<?php
	if(!empty($_GET['build'])){
		$build = getVersionByBuildConfigHash($_GET['build']);

		if(empty($build)){
			die("Invalid buildconfig specified!");
		}

		echo "<h3>Install dump for ".$build['buildconfig']['description']."</h3>";

		$buildn = substr($build['buildconfig']['description'], 4, 5);

		// 18125 - 18761
			// 1 = Arch
			// 2 = Locale
			// 3 = OS
		// 18764 - 20426
			// 1 = Arch
			// 2 = Category
			// 3 = Locale
			// 4 = OS
			// 5 = Region
		// 20438 - now
			// 1 = OS
			// 2 = Arch
			// 3 = Locale
			// 4 = Region
			// 5 = Category

		if($buildn >= 18125 && $buildn <= 18761){
			$headers = array(1 => "Arch", 2 => "Locale", 3 => "OS");
		}elseif($buildn >= 18764 && $buildn <= 20426){
			$headers = array(1 => "Arch", 2 => "Category", 3 => "Locale", 4 => "OS", 5 => "Region");
		}elseif($buildn >= 20438){
			$headers = array(1 => "OS", 2 => "Arch", 3 => "Locale", 4 => "Region", 5 => "Category");
		}

		?>

		<table id='install' class="table table-sm table-hover table-striped">
			<thead class="thead-inverse">
				<tr>
					<th>Filename</th>
					<th>Size</th>
					<?php foreach($headers as $header){ ?>
					<th><?=$header?></th>
					<?php } ?>
					<th>MD5</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>

		<script type='text/javascript'>
			$.getJSON("https://wow.tools/casc/install/dump?hash=<?=$build['buildconfig']['install_cdn']?>", function( data ) {
				var items = [];
				$.each( data, function( key, val ) {
					var row = "<tr><td>" + val.name + "</td><td>" + val.size + "</td>";

					var prevTag = 0;

					val.tags.forEach(function(tag) {
						var splitTag = tag.split('=');

						if(prevTag != splitTag[0]){
							row += "</td><td>";
						}

						row += splitTag[1] + " ";

						prevTag = splitTag[0];
					});

					row += "<td><span class='hash'>" + val.contentHash + "</span></td><td><a href='https://wow.tools/casc/file/chash?contenthash=" + val.contentHash + "&buildconfig=<?=$build['buildconfig']['hash']?>&cdnconfig=<?=$build['cdnconfig']['hash']?>&filename=" + val.name.split('\\').reverse()[0] + "'>Download</a></td></tr>";
					$('#install tbody').append(row);
				});
			});
		</script>
		<?php
	}
	?>
</div>
<?php include "../inc/footer.php"; ?>