	function showTooltip(el){
		if(document.getElementById("tooltipToggle")){
			if(!document.getElementById("tooltipToggle").checked){
				return;
			}
		}

		const bodyRect = document.body.getBoundingClientRect();
		const elementRect = el.getBoundingClientRect();

		let tooltipMarginLeft = el.offsetWidth;
		if((elementRect.x + elementRect.width + 400) > bodyRect.width){
			tooltipMarginLeft = bodyRect.width - (elementRect.x + elementRect.width + 400);
		}

		let localBuild = "";

		const tooltipType = el.dataset.tooltip;
		const tooltipTargetValue = el.dataset.id;
		if('build' in el.dataset){
			localBuild = el.dataset.build;
		}else{
			localBuild = build;
		}
		let tooltipHTML = "<div id='tooltip'><div class='tooltip-icon' style='display: none'><img src='https://wow.tools/casc/preview/chash?buildconfig=bf24b9d67a4a9c7cc0ce59d63df459a8&cdnconfig=2b5b60cdbcd07c5f88c23385069ead40&filename=interface%2Ficons%2Finv_misc_questionmark.blp&contenthash=45809010e72cafe336851539a9805b80'/></div><div class='tooltip-desc'>Generating tooltip..</div></div></div>";
		if(el.children.length == 0){
		// Replace with generated
		let tooltipDiv = document.createElement("div");
		tooltipDiv.innerHTML = tooltipHTML;
		tooltipDiv.style.position = "absolute";
		tooltipDiv.style.zIndex = 5;
		tooltipDiv.style.display = "block";
		tooltipDiv.style.marginLeft = tooltipMarginLeft + "px";
		tooltipDiv.classList.add('wt-tooltip');

		if(tooltipType == "spell" || tooltipType == "item"){
			tooltipDiv.querySelector(".tooltip-icon").style.display = 'block';
		}
		// Append to HTML
		el.appendChild(tooltipDiv);

		if(tooltipType == 'spell'){
			generateSpellTooltip(tooltipTargetValue, tooltipDiv);
		}else if(tooltipType == 'item'){
			generateItemTooltip(tooltipTargetValue, tooltipDiv, localBuild);
		}else if(tooltipType == 'creature'){
			generateCreatureTooltip(tooltipTargetValue, tooltipDiv);
		}else if(tooltipType == 'quest'){
			generateQuestTooltip(tooltipTargetValue, tooltipDiv);
		}else if(tooltipType == 'fk'){
			if((el.dataset.fk == "Map::ID" || tooltipTargetValue != 0) && tooltipTargetValue != -1){
				generateFKTooltip(el.dataset.fk, tooltipTargetValue, tooltipDiv);
			}else{
				hideTooltip(el);
			}
		}else{
			console.log("Unsupported tooltip type " + tooltipType);
			return;
		}
	}
}

function generateQuestTooltip(id, tooltip)
{
	console.log("Generating quest tooltip for " + id);

	let tooltipIcon = tooltip.querySelector(".tooltip-icon img");
	let tooltipDesc = tooltip.querySelector(".tooltip-desc");

	fetch("/db/quest_api.php?id=" + id, {cache: "force-cache"})
	.then(function (response) {
		return response.json();
	}).then(function (questEntry) {
		if(tooltipIcon == undefined || tooltipDesc == undefined){
			console.log("Tooltip closed before rendering finished, nevermind");
			return;
		}

		console.log(questEntry);
		if(questEntry["error"] !== undefined){
			tooltipDesc.innerHTML = "An error occured: " + questEntry["error"];
			return;
		}

		tooltipDesc.innerHTML = "<h2>" + questEntry["LogTitle"] + "</h2>";
		tooltipDesc.innerHTML += "<p class='yellow'>" + questEntry["QuestDescription"];
	}).catch(function (error) {
		console.log("An error occurred retrieving data to generate the tooltip: " + error);
		tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
	});
}

function generateCreatureTooltip(id, tooltip)
{
	console.log("Generating creature tooltip for " + id);

	let tooltipIcon = tooltip.querySelector(".tooltip-icon img");
	let tooltipDesc = tooltip.querySelector(".tooltip-desc");

	fetch("/db/creature_api.php?id=" + id, {cache: "force-cache"})
	.then(function (response) {
		return response.json();
	}).then(function (creatureEntry) {
		if(tooltipIcon == undefined || tooltipDesc == undefined){
			console.log("Tooltip closed before rendering finished, nevermind");
			return;
		}
		if(creatureEntry["error"] !== undefined){
			tooltipDesc.innerHTML = "An error occured: " + creatureEntry["error"];
			return;
		}

		tooltipDesc.innerHTML = "<h2>" + creatureEntry["Name[0]"] + "</h2>";
		tooltipDesc.innerHTML += "<p class='yellow'>Type: " + creatureEntry["CreatureType"];
		// TODO: Portrait
		// fetch("/dbc/api/peek/creaturedisplayinfo?build=" + build + "&col=ID&val=" + creatureEntry["CreatureDisplayInfoID[0]"], {cache: "force-cache"})
		// .then(function (response) {
		// 	return response.json();
		// }).then(function (cdiEntry) {
		// 	if(Object.keys(cdiEntry).length === 0){
		// 		tooltipDesc.innerHTML = "An error occured: Creature Display Info not found";
		// 		return;
		// 	}
		// 	console.log("cdi", cdiEntry);
		// 	// tooltipIcon.src = 'https://wow.tools/casc/preview/fdid?buildconfig=e4ec55573724aa18e5908a157526d3ca&cdnconfig=efce24b3df56fbc182d3e97249cadf76&filename=icon.blp&filedataid=' + spellMiscEntry["SpellIconFileDataID"];
		// }).catch(function (error) {
		// 	console.log("An error occurred retrieving data to generate the tooltip: " + error);
		// 	tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
		// });
		// tooltipIcon.src = 'https://wow.tools/casc/preview/fdid?buildconfig=e4ec55573724aa18e5908a157526d3ca&cdnconfig=efce24b3df56fbc182d3e97249cadf76&filename=icon.blp&filedataid=' + spellMiscEntry["SpellIconFileDataID"];
	}).catch(function (error) {
		console.log("An error occurred retrieving data to generate the tooltip: " + error);
		tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
	});
}


function generateItemTooltip(id, tooltip, build){
	console.log("Generating item tooltip for " + id);

	let tooltipIcon = tooltip.querySelector(".tooltip-icon img");
	let tooltipDesc = tooltip.querySelector(".tooltip-desc");

	Promise.all([
		fetch("/dbc/api/tooltip/item/" + id + "?build=" + build),
		])
	.then(function (responses) {
		return Promise.all(responses.map(function (response) {
			if(tooltipIcon == undefined || tooltipDesc == undefined){
				console.log("Tooltip closed before rendering finished, nevermind");
				return;
			}
			return response.json();
		})).catch(function (error) {
			console.log("An error occurred retrieving data to generate the tooltip: " + error);
			tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
		});
	}).then(function (data) {
		if(tooltipIcon == undefined || tooltipDesc == undefined){
			console.log("Tooltip closed before rendering finished, nevermind");
			return;
		}

		const calcData = data[0]; // Calculated on server

		let tooltipTable = "<table class='tooltip-table'><tr><td><h2 class='q" + calcData["overallQualityID"] + "'>" + calcData["name"] + "</h2></td><td class='right'><img src='/img/exp/" + calcData["expansionID"] + ".png'></td></tr>";
		if(calcData["itemLevel"] != 0) tooltipTable += "<tr><td class='yellow'>Item Level " + calcData["itemLevel"] + "</td></tr>";
		tooltipTable += "<tr><td>" + inventoryTypeEnum[calcData["inventoryType"]] + "</td><td class='right'>" + itemSubClass[calcData['classID']][calcData['subClassID']] + "</td></tr>";

		if(calcData["classID"] == 2 && calcData["hasSparse"] == true){
			tooltipTable += "<tr><td><span class='mindmg'>" + calcData["minDamage"] + "</span> - <span class='maxdmg'>" + calcData["maxDamage"] + "</span> Damage</td><td class='right'>Speed <span class='speed'>" + calcData["speed"] + "</span></td></tr>";
			tooltipTable += "<tr><td>(<span class='dps'>" + calcData["dps"] + "</span> damage per second)</td></tr>";
		}

		if(calcData["itemEffects"] != undefined){
			for(let i = 0; i < calcData["itemEffects"].length; i++){
				let itemEffect = calcData["itemEffects"][i];
				tooltipTable += "<tr><td colspan='2'>" + itemEffectTriggerType[itemEffect["triggerType"]] + ": ";
				// if(itemEffect["spell"]["name"] != ""){
				// 	tooltipTable += " <b>" + itemEffect["spell"]["name"] + "</b>";
				// }

				if(itemEffect["spell"]["description"] != null){
					tooltipTable += " " + itemEffect["spell"]["description"];
				}else{
					tooltipTable += " SpellID #" + itemEffect["spell"]["spellID"];
				}

				tooltipTable += "</td></tr>";
			}
		}

		let hasStats = false;
		if(calcData["hasSparse"] == true && calcData["stats"] != null && calcData["stats"].length > 0){
			hasStats = true;
			for(let statIndex = 0; statIndex < calcData["stats"].length; statIndex++){
				var stat = calcData["stats"][statIndex];

				if(stat["isCombatRating"]){
					tooltipTable += "<tr><td class='q2'>+" + stat["value"] + " " + itemPrettyStatType[stat["statTypeID"]] + "</td></tr>";
				}else{
					tooltipTable += "<tr><td>+" + stat["value"] + " " + itemPrettyStatType[stat["statTypeID"]] + "</td></tr>";
				}
			}
		}

		if(calcData["requiredLevel"] > 1) { tooltipTable += "<tr><td>Requires Level " + calcData["requiredLevel"] + "</td></tr>"; }

		if(calcData["flavorText"] != null && calcData["flavorText"] != ""){
			tooltipTable += "<tr><td class='yellow'>\"" + calcData["flavorText"] + "\"</td></tr>";
		}

		if(hasStats){
			tooltipTable += "<tr><td class='yellow'><i>Still WIP, stats might be inaccurate.</i></td></tr>";
		}

		tooltipTable += "</table>";

		tooltipDesc.innerHTML = tooltipTable;

		if(calcData["iconFileDataID"] != 0){
			tooltipIcon.src = 'https://wow.tools/casc/preview/fdid?buildconfig=e4ec55573724aa18e5908a157526d3ca&cdnconfig=efce24b3df56fbc182d3e97249cadf76&filename=icon.blp&filedataid=' + calcData["iconFileDataID"];
		}
	}).catch(function (error) {
		console.log("An error occurred retrieving data to generate the tooltip: " + error);
		tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
	});
}

function generateSpellTooltip(id, tooltip)
{
	console.log("Generating spell tooltip for " + id);

	let tooltipIcon = tooltip.querySelector(".tooltip-icon img");
	let tooltipDesc = tooltip.querySelector(".tooltip-desc");

	Promise.all([
		fetch("/dbc/api/tooltip/spell/" + id + "?build=" + build),
		])
	.then(function (responses) {
		return Promise.all(responses.map(function (response) {
			if(tooltipIcon == undefined || tooltipDesc == undefined){
				console.log("Tooltip closed before rendering finished, nevermind");
				return;
			}
			return response.json();
		})).catch(function (error) {
			console.log("An error occurred retrieving data to generate the tooltip: " + error);
			tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
		});
	}).then(function (data) {
		if(tooltipIcon == undefined || tooltipDesc == undefined){
			console.log("Tooltip closed before rendering finished, nevermind");
			return;
		}

		console.log(data);

		const calcData = data[0];

		tooltipDesc.innerHTML = "<h2>" + calcData["name"] + "</h2>";
		tooltipDesc.innerHTML += "<p class='yellow'>" + calcData["description"];
		tooltipIcon.src = 'https://wow.tools/casc/preview/fdid?buildconfig=e4ec55573724aa18e5908a157526d3ca&cdnconfig=efce24b3df56fbc182d3e97249cadf76&filename=icon.blp&filedataid=' + calcData["iconFileDataID"];
	}).catch(function (error) {
		console.log("An error occurred retrieving data to generate the tooltip: " + error);
		tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
	});
}

function generateFKTooltip(targetFK, value, tooltip)
{
	console.log("Generating foreign key tooltip for " + value);

	let tooltipIcon = tooltip.querySelector(".tooltip-icon img");
	let tooltipDesc = tooltip.querySelector(".tooltip-desc");

	const explodedTargetFK = targetFK.split("::");
	const table = explodedTargetFK[0].toLowerCase();
	const col = explodedTargetFK[1];

	Promise.all([
		fetch("/dbc/api/peek/" + table + "?build=" + build + "&col=" + col + "&val=" + value),
	])
	.then(function (responses) {
		return Promise.all(responses.map(function (response) {
			if(tooltipIcon == undefined || tooltipDesc == undefined){
				console.log("Tooltip closed before rendering finished, nevermind");
				return;
			}
			return response.json();
		})).catch(function (error) {
			console.log("An error occurred retrieving data to generate the tooltip: " + error);
			tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
		});
	}).then(function (data) {
		if(tooltipIcon == undefined || tooltipDesc == undefined){
			console.log("Tooltip closed before rendering finished, nevermind");
			return;
		}

		const json = data[0];
		let tooltipTable = "<table class='tooltip-table'><tr><td colspan='2'><h2 class='q2'>" + targetFK + " value " + value +"</h2></td></tr>";

		if(!json || Object.keys(json.values).length == 0){
			tooltipTable += "<tr><td colspan='2'>Row not available in client</td><td>";
		}

		Object.keys(json.values).forEach(function (key) {
			const val = json.values[key];
			tooltipTable += "<tr><td>" + key + "</td><td>";

			if(key.startsWith("Flags") || flagMap.has(table + "." + key)){
				tooltipTable += "0x" + dec2hex(val);
			}else{
				tooltipTable += val;
			}
			if(enumMap.has(table + "." + key)){
				var enumVal = getEnum(table, key, val);
				if(val == '0' && enumVal == "Unk"){
					// returnVar += full[meta.col];
				}else{
					tooltipTable += " <i>(" + enumVal + ")</i>";
				}
			}

			 tooltipTable += "</td></tr>"
		});
		tooltipTable += "</table>";

		tooltipDesc.innerHTML = tooltipTable;

		repositionTooltip(tooltip);
	}).catch(function (error) {
		console.log("An error occurred retrieving data to generate the tooltip: " + error);
		tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
	});
}

function repositionTooltip(tooltip){
		const bodyRect = document.body.getBoundingClientRect();

		if(tooltip.parentElement == null)
			return;

		const parentRect = tooltip.parentElement.getBoundingClientRect();
		const elementRect = tooltip.getBoundingClientRect();

		let leftCutOff = false;
		let bottomCutOff = false;

		let tooltipMarginLeft = tooltip.parentElement.offsetWidth;
		if((parentRect.x + parentRect.width + 400) > bodyRect.width){
			tooltipMarginLeft = bodyRect.width - (parentRect.x + parentRect.width + 400);

			leftCutOff = true;
		}

		let tooltipMarginTop = 10;
		if(elementRect.bottom > bodyRect.bottom || elementRect.bottom > window.innerHeight){
			if(bodyRect.bottom > window.innerHeight){
				tooltipMarginTop = bodyRect.bottom - elementRect.bottom - (bodyRect.bottom - window.innerHeight + 25);
			}else{
				tooltipMarginTop = bodyRect.bottom - elementRect.bottom - 5;
			}

			bottomCutOff = true;
		}

		if(leftCutOff && bottomCutOff){
			tooltipMarginLeft = -425;
		}

		tooltip.style.marginLeft = tooltipMarginLeft + "px";
		tooltip.style.marginTop = tooltipMarginTop + "px";
}

function hideTooltip(el){
	if(document.getElementById("keepTooltips")){
		if(document.getElementById("keepTooltips").checked){
			return;
		}
	}

	if(el.children.length > 0){
		for(let i = 0; i < el.children.length; i++){
			if(el.children[i].classList.contains('wt-tooltip')){
				el.removeChild(el.children[i]);
			}
		}
	}
}