<?php
require_once(__DIR__ . "/../inc/header.php");
?>
<div class='container-fluid'>
	<table class='table' id='hotfixTable'>
		<thead>
			<tr><th>Push ID</th><th>Table name</th><th>Record ID</th><th>Build</th><th>First seen at</th></tr>
		</thead>
		<tbody>

		</tbody>
	</table>
</div>
<link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/pagination/input.js" crossorigin="anonymous"></script>
<script type='text/javascript'>
	var table = $('#hotfixTable').DataTable({
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "/dbc/hotfix_api.php"
		},
		"pageLength": 25,
		"displayStart": 0,
		"autoWidth": true,
		"pagingType": "input",
		"orderMulti": false,
		"ordering": false,
		"searching": false,
		"language": { "search": "<a class='btn btn-dark btn-sm btn-outline-primary' href='#' onClick='toggleFilters()' style='margin-right: 10px'>Toggle filters</a> Search: _INPUT_ " },
		"search": { "search": "" }
	});

</script>
<?php
require_once(__DIR__ . "/../inc/footer.php");
?>