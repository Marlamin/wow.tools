<?
require_once("../inc/header.php");
?>
<div class='container-fluid'>
<table id='files' class="table table-striped table-bordered table-condensed" cellspacing="0" style='margin: auto; table-layout: fixed;' width="100%">
<thead>
<tr>
<th style='width: 120px;'>Timestamp</th>
<th style='width: 220px;'>URL</th>
<th style='max-width: calc(100% - calc(120px + 220px)); overflow: hidden'>Diff</th>
</tr>
</thead>
<tbody>
</tbody>
</table>
</div>
<link href="css/monitor.css?v=<?=filemtime($basedir."/monitor/css/monitor.css")?>" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/pagination/input.js" crossorigin="anonymous"></script>
<script type='text/javascript'>
	(function() {
		var searchHash = location.hash.substr(1),
		searchString = searchHash.substr(searchHash.indexOf('search=')).split('&')[0].split('=')[1];

		if(searchString != undefined && searchString.length > 0){
			searchString = decodeURIComponent(searchString);
		}

		var page = (parseInt(searchHash.substr(searchHash.indexOf('page=')).split('&')[0].split('=')[1], 10) || 1) - 1;

		var products = [{"name":"bvt","product":"bvt"},{"name":"WoW Vendor","product":"wowv"},{"name":"WoW Submission","product":"wowz"},{"name":"WoW Retail","product":"wow"},{"name":"WoW PTR","product":"wowt"},{"name":"WoW Event 3","product":"wowe3"},{"name":"WoW Event 2","product":"wowe2"},{"name":"WoW Event 1","product":"wowe1"},{"name":"WoW Dev","product":"wowdev"},{"name":"WoW Classic Demo (encrypted)","product":"wowdemo"},{"name":"WoW Classic Demo","product":"wow_classic"},{"name":"WoW Beta","product":"wow_beta"},{"name":"Warcraft III","product":"war3"},{"name":"Warcraft 3 Test","product":"w3t"},{"name":"Warcraft 3","product":"w3"},{"name":"Warcraft 2","product":"w2"},{"name":"Warcraft 1","product":"w1"},{"name":"Test","product":"test"},{"name":"StarCraft II Test","product":"s2t"},{"name":"StarCraft II Beta","product":"s2b"},{"name":"StarCraft II","product":"sc2"},{"name":"StarCraft II","product":"s2"},{"name":"StarCraft 1 Test","product":"s1t"},{"name":"StarCraft 1 A","product":"s1a"},{"name":"StarCraft 1","product":"s1"},{"name":"Overwatch comp 2","product":"proc2"},{"name":"Overwatch Vendor","product":"prov"},{"name":"Overwatch Tournament KR","product":"proc2_kr"},{"name":"Overwatch Tournament EU","product":"proc2_eu"},{"name":"Overwatch Tournament CN","product":"proc2_cn"},{"name":"Overwatch Tournament","product":"proc"},{"name":"Overwatch Test","product":"prot"},{"name":"Overwatch MS (?)","product":"proms"},{"name":"Overwatch Dev","product":"prodev"},{"name":"Overwatch Competitive 3","product":"proc3"},{"name":"Overwatch CR","product":"procr"},{"name":"Overwatch","product":"pro"},{"name":"HotS Tournament","product":"heroc"},{"name":"HotS Test","product":"herot"},{"name":"HotS Alpha","product":"bnt"},{"name":"HotS","product":"hero"},{"name":"Hearthstone Test","product":"hst"},{"name":"Hearthstone Retail","product":"hsb"},{"name":"Hearthstone Competitive","product":"hsc"},{"name":"Diablo III China Test","product":"d3cnt"},{"name":"Diablo III China","product":"d3cn"},{"name":"Diablo 3 Test","product":"d3t"},{"name":"Diablo 3 Beta","product":"d3b"},{"name":"Diablo 3","product":"d3"},{"name":"Diablo 2","product":"d2"},{"name":"Diablo 1","product":"d1"},{"name":"Destiny 2 PTR","product":"dst2t"},{"name":"Destiny 2 Internet Game Room","product":"dst2igr"},{"name":"Destiny 2 Event 1","product":"dst2e1"},{"name":"Destiny 2 Dev","product":"dst2dev"},{"name":"Destiny 2 Alpha","product":"dst2a"},{"name":"Destiny 2","product":"dst2"},{"name":"CoD: BO4 Vendor","product":"viperv1"},{"name":"CoD: BO4 Dev","product":"viperdev"},{"name":"CoD: BO4","product":"viper"},{"name":"Client (old)","product":"clnt"},{"name":"Catalog","product":"catalogs"},{"name":"Bootstrapper","product":"bts"},{"name":"Battle.net App","product":"bna"},{"name":"Battle.net Agent","product":"agent"},{"name":"Agent Test","product":"agent_test"}];

		var build = searchHash.substr(searchHash.indexOf('build=')).split('&')[0].split('=')[1];

		if(build != undefined && build.length > 0){
			$("#fileBuildFilter").val(build);
			$("#fileBuildFilter").trigger('change');
		}

		var sortCol = searchHash.substr(searchHash.indexOf('sort=')).split('&')[0].split('=')[1];
		var sortDesc = searchHash.substr(searchHash.indexOf('desc=')).split('&')[0].split('=')[1];

		if(!sortCol){
			sortCol = 0;
		}

		if(!sortDesc){
			sortDesc = "desc";
		}
		var previewTypes = ["ogg", "mp3", "blp", "wmo", "m2"];

		var table = $('#files').DataTable({
			"processing": true,
			"serverSide": true,
			"searching": true,
			"dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'p>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
			"ajax": "scripts/api.php",
			"pageLength": 5,
			"displayStart": page * 5,
			"autoWidth": false,
			"pagingType": "input",
			"orderMulti": false,
			"order": [[sortCol, sortDesc]],
			"lengthMenu": [[5, 10, 25, 50], [5, 10, 25, 50]],
			initComplete: function () {
				this.api().columns().every( function (col) {
					var column = this;
					if(col == 1){
						var select = $('<select style="max-width: 100%"><option value="">Product</option></select>')
						.appendTo( $(column.header()).empty() )
						.on( 'change', function () {
							var val = $.fn.dataTable.util.escapeRegex(
								$(this).val()
								);

							column
							.search( val ? '/'+val+'/' : '', true, false )
							.draw();
						} );

						products.forEach(function(product){
							select.append('<option value="'+ product.product +'">'+product.name+'</option>');
						});
					}
				} );
			},
			"columnDefs": [
			{
				"targets": [1,2],
				"orderable": false,
			}],
			"language": {"search": ""}
		});
	}());
</script>
<? require_once("../inc/footer.php"); ?>