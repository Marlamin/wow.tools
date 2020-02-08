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

function openFKModal(value, location, build){
	const wowDBMap = new Map();
	wowDBMap.set("spell", "https://www.wowdb.com/spells/");
	wowDBMap.set("item", "https://www.wowdb.com/items/");
	wowDBMap.set("itemsparse", "https://www.wowdb.com/items/");

	const wowheadMap = new Map();
	wowheadMap.set("spell", "https://www.wowhead.com/spell=");
	wowheadMap.set("item", "https://www.wowhead.com/item=");
	wowheadMap.set("itemsparse", "https://www.wowhead.com/item=");

	console.log("Opening FK link to " + location + " (build " +  build + ") with value " + value);

	const splitLocation = location.split("::");
	const db = splitLocation[0].toLowerCase();
	const col = splitLocation[1];

	$("#fkModalContent").html("<b>Lookup into table " + db + " on col '" + col + "' value '" + value + "'</b><br><br><table id='fktable' class='table table-condensed table-striped'>");
	$.ajax({
		"url": "/dbc/api/header/" + db + "?build=" + build,
		"success": function(headerjson) {
			$.ajax({
				"url": "/dbc/api/peek/" + db + "?build=" + build + "&col=" + col + "&val=" + value,
				"success": function(json) {
					Object.keys(json.values).forEach(function (key) {
						var val = json.values[key];
						if(key in headerjson.fks){
							if(headerjson.fks[key] == "SoundEntries::ID" && parseInt(build[0]) > 6){
								$("#fktable").append("<tr><td>" + key + "</td><td><a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' onclick='openFKModal(" + val + ", \"SoundKit::ID\")'>" + val + "</a></td></tr>");
							}else{
								$("#fktable").append("<tr><td>" + key + "</td><td><a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' onclick='openFKModal(" + val + ", \"" + headerjson.fks[key] + "\")'>" + val + "</a></td></tr>");
							}
						}else{
							$("#fktable").append("<tr><td>" + key + "</td><td>" + val + "</td></tr>");
						}
					});

					var numRecordsIntoPage = json.offset - Math.floor((json.offset - 1) / 25) * 25;
					var page = Math.floor(((json.offset - 1) / 25) + 1);
					$("#fkModalContent").append(" <a target=\"_BLANK\" href=\"/dbc/?dbc=" + splitLocation[0].replace(".db2", "").toLowerCase() + "&build=" + build + "#page=" + page + "&row=" + numRecordsIntoPage + "\" class=\"btn btn-primary\">Jump to record</a>");
				}
			});
		}
	});

	if(wowDBMap.has(db)){
		$("#fkModalContent").append(" <a target='_BLANK' href='" + wowDBMap.get(db) + value + "' class='btn btn-warning'>View on WoWDB</a>");
	}

	if(wowheadMap.has(db)){
		$("#fkModalContent").append(" <a target='_BLANK' href='" + wowheadMap.get(db) + value + "' class='btn btn-warning'>View on Wowhead</a>");
	}
}

function getFlagDescriptions(db, field, value){
	let usedFlags = Array();
	const targetFlags = flagMap.get(db + '.' + field);
	if(targetFlags !== undefined){
		Object.keys(targetFlags).forEach(function (flag) {
			if(value & targetFlags[flag]){
				usedFlags.push(flag);
			}
		});
	}
	return usedFlags;
}