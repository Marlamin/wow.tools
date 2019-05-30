function makeBuild(text){
	if(text == null){
		return "";
	}

	var rawdesc = text.replace("WOW-", "");
	var build  = rawdesc.substring(0, 5);

	var rawdesc = rawdesc.replace(build, "").replace("patch", "");
	var descexpl = rawdesc.split("_");

	return descexpl[0] + "." + build;
}

function getFKCols(headers, fks){
	var fkCols = [];
	headers.forEach(function(header, index){
		Object.keys(fks).forEach(function(key) {
			if(key == header){
				fkCols[index] = fks[key];
			}
		});
	});
	return fkCols;
}

function openFKModal(value, location){
	console.log("Opening FK link to " + location + " (build " +  $("#buildFilter").val() + ") with value " + value);
	var splitLocation = location.split("::");
	var url = "/api/peek/" + splitLocation[0].toLowerCase() + "?build=" + $("#buildFilter").val() + "&col=" + splitLocation[1] + "&val=" + value;
	$("#fkModalContent").html("<b>Lookup into table " + splitLocation[0].toLowerCase() + " on col '" + splitLocation[1] + "' value '" + value + "'</b><br><br><table id='fktable' class='table table-condensed table-striped'>");
	$.ajax({
		"url": url,
		"success": function(json) {
			json.values.forEach(function (value) {
				$("#fktable").append("<tr><td>" + value.item1 + "</td><td>" + value.item2 + "</td></tr>");
			});
			var numRecordsIntoPage = json.offset - Math.floor((json.offset - 1) / 25) * 25;
			var page = Math.floor(((json.offset - 1) / 25) + 1);
			$("#fkModalContent").append("<a target=\"_BLANK\" href=\"/dbc/?dbc=" + splitLocation[0].replace(".db2", "").toLowerCase() + ".db2&bc=" + $("#buildFilter").val() + "#page=" + page + "&row=" + numRecordsIntoPage + "\" class=\"btn btn-primary\">Jump to record</a>");
		}
	});
}
