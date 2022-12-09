document.addEventListener('mousemove', (e) => {
    if (!e.target.matches('[data-tooltip]')) {
        return;
    }

    tooltip2.call(this, e.target, e);
});

function tooltip2(el, event){
    if (document.getElementById("tooltipToggle")){
        if (!document.getElementById("tooltipToggle").checked){
            return;
        }
    }

    el.addEventListener("mouseout", hideTooltip2, el);
    el.addEventListener("click", hideTooltip2, el);

    const tooltipType = el.dataset.tooltip;
    const tooltipTargetValue = el.dataset.id;
    let tooltipDiv = document.getElementById("wtTooltip");
    // let defaultTooltipHTML = "<div id='tooltip'><div class='tooltip-icon' style='display: none'><img src='https://wow.tools/casc/preview/chash?buildconfig=" + SiteSettings.buildConfig + "&cdnconfig=" + SiteSettings.cdnConfig + "&filename=interface%2Ficons%2Finv_misc_questionmark.blp&contenthash=45809010e72cafe336851539a9805b80'/></div><div class='tooltip-desc'>Generating tooltip..</div></div></div>";
    let defaultTooltipHTML = "<div id='tooltip'><div class='tooltip-desc'>Generating tooltip..</div></div></div>";
    let needsRefresh = false;

    if (!tooltipDiv) {
        // Tooltip div does not exist yet, create!
        tooltipDiv = document.createElement("div");
        tooltipDiv.dataset.type = tooltipType;
        tooltipDiv.dataset.id = tooltipTargetValue;
        tooltipDiv.innerHTML = defaultTooltipHTML;
        tooltipDiv.style.position = "absolute";
        tooltipDiv.style.top = event.pageY + "px";

        tooltipDiv.style.left = event.pageX + "px";
        tooltipDiv.style.zIndex = 1100;
        tooltipDiv.style.display = "block";
        tooltipDiv.id = "wtTooltip";
        tooltipDiv.classList.add('wt-tooltip');

        if (tooltipType == "spell" || tooltipType == "item"){
            // tooltipDiv.querySelector(".tooltip-icon").style.display = 'block';
        }
        needsRefresh = true;
        document.body.appendChild(tooltipDiv);
    } else {
        tooltipDiv.style.display = "block";
        tooltipDiv.style.top = (event.pageY + 5) + "px";
        tooltipDiv.style.left = (event.pageX + 5) + "px";

        if (tooltipTargetValue != tooltipDiv.dataset.id || tooltipType != tooltipDiv.dataset.type){
            tooltipDiv.innerHTML = defaultTooltipHTML;
            tooltipDiv.dataset.type = tooltipType;
            tooltipDiv.dataset.id = tooltipTargetValue;

            if (tooltipType == "spell" || tooltipType == "item"){
                // tooltipDiv.querySelector(".tooltip-icon").style.display = 'block';
            }

            needsRefresh = true;
        }

        repositionTooltip(tooltipDiv);
    }

    if ((event.pageX + 400) > window.innerWidth) {
        tooltipDiv.style.left = ((event.pageX + 5) - 400) + "px";
    }

    if (needsRefresh){
        let localBuild = "";

        if ('build' in el.dataset){
            localBuild = el.dataset.build;
        } else if (build != undefined) {
            localBuild = build;
        } else {
            // TODO: Global site fallback build?
        }

        if (tooltipType == 'spell'){
            generateSpellTooltip(tooltipTargetValue, tooltipDiv, localBuild);
        } else if (tooltipType == 'item'){
            generateItemTooltip(tooltipTargetValue, tooltipDiv, localBuild);
        } else if (tooltipType == 'creature'){
            generateCreatureTooltip(tooltipTargetValue, tooltipDiv);
        } else if (tooltipType == 'quest'){
            generateQuestTooltip(tooltipTargetValue, tooltipDiv);
        } else if (tooltipType == 'fk'){
            generateFKTooltip(el.dataset.fk, tooltipTargetValue, tooltipDiv, localBuild);
        } else if (tooltipType == 'file'){
            generateFileTooltip(tooltipTargetValue, tooltipDiv);
        } else if (tooltipType == 'criteria'){
            generateCriteriaTooltip(tooltipTargetValue, tooltipDiv, localBuild);
        } else {
            console.log("Unsupported tooltip type " + tooltipType);
            return;
        }
    }
}

function hideTooltip2(){
    if (document.getElementById("keepTooltips")){
        if (document.getElementById("keepTooltips").checked){
            return;
        }
    }

    if (document.getElementById("wtTooltip")){
        document.getElementById("wtTooltip").style.display = "none";
    }

    return true;
}

function generateQuestTooltip(id, tooltip)
{
    console.log("Generating quest tooltip for " + id);

    // const tooltipIcon = tooltip.querySelector(".tooltip-icon img");
    const tooltipDesc = tooltip.querySelector(".tooltip-desc");

    fetch("/db/quest_api.php?id=" + id, {cache: "force-cache"})
        .then(function (response) {
            return response.json();
        }).then(function (questEntry) {
            if (tooltipDesc == undefined){
                console.log("Tooltip closed before rendering finished, nevermind");
                return;
            }

            console.log(questEntry);
            if (questEntry["error"] !== undefined){
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

function generateCriteriaTooltip(id, tooltip, build)
{
    console.log("Generating criteria tooltip for " + id);

    // const tooltipIcon = tooltip.querySelector(".tooltip-icon img");
    const tooltipDesc = tooltip.querySelector(".tooltip-desc");

    fetch("/dbc/experiments/criteriaExplorer.php?api=1&id=" + id, {cache: "force-cache"})
        .then(function (response) {
            return response.json();
        }).then(function (criteriaEntry) {
            if (tooltipDesc == undefined){
                console.log("Tooltip closed before rendering finished, nevermind");
                return;
            }

            console.log(criteriaEntry);
            if (criteriaEntry["error"] !== undefined){
                tooltipDesc.innerHTML = "An error occured: " + criteriaEntry["error"];
                return;
            }

            let description = criteriaEntry["Description_lang"];
            if (description == ""){
                description = "CriteriaTree ID " + criteriaEntry["ID"];
            }
            tooltipDesc.innerHTML = "<h2>" + criteriaEntry["Description_lang"] + "</h2>";
            tooltipDesc.innerHTML += "<p class='yellow'>" + criteriaTreeOperatorFriendly[criteriaEntry["Operator"]] + "</p>";

            criteriaEntry["Children"].forEach(function(child){
                tooltipDesc.innerHTML += child["Description_lang"] + "<br>";
            })

        }).catch(function (error) {
            console.log("An error occurred retrieving data to generate the tooltip: " + error);
            tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
        });
}

function generateCreatureTooltip(id, tooltip)
{
    console.log("Generating creature tooltip for " + id);

    // const tooltipIcon = tooltip.querySelector(".tooltip-icon img");
    const tooltipDesc = tooltip.querySelector(".tooltip-desc");

    fetch("/db/creature_api.php?id=" + id, {cache: "force-cache"})
        .then(function (response) {
            return response.json();
        }).then(function (creatureEntry) {
            if (tooltipDesc == undefined){
                console.log("Tooltip closed before rendering finished, nevermind");
                return;
            }
            if (creatureEntry["error"] !== undefined){
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

    // const tooltipIcon = tooltip.querySelector(".tooltip-icon img");
    const tooltipDesc = tooltip.querySelector(".tooltip-desc");

    Promise.all([
        fetch("https://api.wow.tools/api/tooltip/item/" + id + "?build=" + build),
    ])
        .then(function (responses) {
            return Promise.all(responses.map(function (response) {
                if (tooltipDesc == undefined){
                    console.log("Tooltip closed before rendering finished, nevermind");
                    return;
                }
                return response.json();
            })).catch(function (error) {
                console.log("An error occurred retrieving data to generate the tooltip: " + error);
                tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
            });
        }).then(function (data) {
            if (tooltipDesc == undefined){
                console.log("Tooltip closed before rendering finished, nevermind");
                return;
            }

            const calcData = data[0]; // Calculated on server

            let tooltipTable = "<table class='tooltip-table'><tr><td><h2 class='q" + calcData["overallQualityID"] + "'>" + calcData["name"] + "</h2></td><td class='right'><img src='/img/exp/" + calcData["expansionID"] + ".png'></td></tr>";
            if (calcData["itemLevel"] != 0) tooltipTable += "<tr><td class='yellow'>Item Level " + calcData["itemLevel"] + "</td></tr>";
            tooltipTable += "<tr><td>" + inventoryTypeEnum[calcData["inventoryType"]] + "</td><td class='right'>" + itemSubClass[calcData['classID']][calcData['subClassID']] + "</td></tr>";

            if (calcData["classID"] == 2 && calcData["hasSparse"] == true){
                tooltipTable += "<tr><td><span class='mindmg'>" + calcData["minDamage"] + "</span> - <span class='maxdmg'>" + calcData["maxDamage"] + "</span> Damage</td><td class='right'>Speed <span class='speed'>" + calcData["speed"] + "</span></td></tr>";
                tooltipTable += "<tr><td>(<span class='dps'>" + calcData["dps"] + "</span> damage per second)</td></tr>";
            }

            if (calcData["itemEffects"] != undefined){
                for (let i = 0; i < calcData["itemEffects"].length; i++){
                    let itemEffect = calcData["itemEffects"][i];
                    tooltipTable += "<tr><td colspan='2'>" + itemEffectTriggerType[itemEffect["triggerType"]] + ": ";
                    // if(itemEffect["spell"]["name"] != ""){
                    // 	tooltipTable += " <b>" + itemEffect["spell"]["name"] + "</b>";
                    // }

                    if (itemEffect["spell"]["description"] != null){
                        tooltipTable += "<span id='spelldesc-" + itemEffect["spell"]["spellID"] + "'>" + itemEffect["spell"]["description"] + "</span>";
                        fetch("https://api.wow.tools/api/tooltip/spell/" + itemEffect["spell"]["spellID"] + "?build=" + build + "&itemID=" + id)
                            .then(function (spellResponse) {
                                return spellResponse.json();
                            }).then(function (data) {
                                var spellDescHolder = document.getElementById("spelldesc-" + data["spellID"]);
                                if (data["description"] != null){
                                    if (spellDescHolder){
                                        spellDescHolder.innerHTML = data["description"].replace("\n", "<br><br>");
                                    }
                                }else{
                                    if (spellDescHolder){
                                        spellDescHolder.innerHTML = "No description for spell " + data["spellID"];
                                    }
                                }
                            }).catch(function (error) {
                                console.log("An error occurred retrieving data to generate spell description: " + error);
                            });

                    } else {
                        tooltipTable += " SpellID #" + itemEffect["spell"]["spellID"];
                    }

                    tooltipTable += "</td></tr>";
                }
            }

            let hasStats = false;
            if (calcData["hasSparse"] == true && calcData["stats"] != null && calcData["stats"].length > 0){
                hasStats = true;
                for (let statIndex = 0; statIndex < calcData["stats"].length; statIndex++){
                    var stat = calcData["stats"][statIndex];

                    if (stat["isCombatRating"]){
                        tooltipTable += "<tr><td class='q2'>+" + stat["value"] + " " + itemPrettyStatType[stat["statTypeID"]] + "</td></tr>";
                    } else {
                        tooltipTable += "<tr><td>+" + stat["value"] + " " + itemPrettyStatType[stat["statTypeID"]] + "</td></tr>";
                    }
                }
            }

            if (calcData["requiredLevel"] > 1) { tooltipTable += "<tr><td>Requires Level " + calcData["requiredLevel"] + "</td></tr>"; }

            if (calcData["flavorText"] != null && calcData["flavorText"] != ""){
                tooltipTable += "<tr><td class='yellow'>\"" + calcData["flavorText"] + "\"</td></tr>";
            }

            if (hasStats){
                tooltipTable += "<tr><td class='yellow'><i>Still WIP, stats might be inaccurate.</i></td></tr>";
            }

            tooltipTable += "</table>";

            tooltipDesc.innerHTML = tooltipTable;

            if (calcData["iconFileDataID"] != 0){
                // tooltipIcon.src = 'https://wow.tools/casc/preview/fdid?buildconfig=' + SiteSettings.buildConfig + '&cdnconfig=' + SiteSettings.cdnConfig + '&filename=icon.blp&filedataid=' + calcData["iconFileDataID"];
            }

            repositionTooltip(tooltip);
        }).catch(function (error) {
            console.log("An error occurred retrieving data to generate the tooltip: " + error);
            tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
        });
}

function generateSpellTooltip(id, tooltip, build)
{
    console.log("Generating spell tooltip for " + id);

    // const tooltipIcon = tooltip.querySelector(".tooltip-icon img");
    const tooltipDesc = tooltip.querySelector(".tooltip-desc");

    Promise.all([
        fetch("https://api.wow.tools/api/tooltip/spell/" + id + "?build=" + build),
    ])
        .then(function (responses) {
            return Promise.all(responses.map(function (response) {
                if (tooltipDesc == undefined){
                    console.log("Tooltip closed before rendering finished, nevermind");
                    return;
                }
                return response.json();
            })).catch(function (error) {
                console.log("An error occurred retrieving data to generate the tooltip: " + error);
                tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
            });
        }).then(function (data) {
            if (tooltipDesc == undefined){
                console.log("Tooltip closed before rendering finished, nevermind");
                return;
            }

            const calcData = data[0];

            if (calcData["name"] == null){
                calcData["name"] = "Unknown spell";
                calcData["description"] = "It is possible this spell was added through hotfixes or is entirely unavailable in the client.";
            }

            tooltipDesc.innerHTML = "<h2>" + calcData["name"] + "</h2>";
            if (calcData["description"] != null){
                tooltipDesc.innerHTML += "<p class='yellow'>" + calcData["description"].replace("\n", "<br><br>");
            }
            // tooltipIcon.src = 'https://wow.tools/casc/preview/fdid?buildconfig=' + SiteSettings.buildConfig + '&cdnconfig=' + SiteSettings.cdnConfig + '&filename=icon.blp&filedataid=' + calcData["iconFileDataID"];
        }).catch(function (error) {
            console.log("An error occurred retrieving data to generate the tooltip: " + error);
            tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
        });
}

function generateFKTooltip(targetFK, value, tooltip, build)
{
    console.log("Generating foreign key tooltip for " + value);

    const collapsedFKs = ["playercondition::id", "holidays::id", "spellchaineffects::id", "spellvisual::id", "soundkitadvanced::id"];

    // const tooltipIcon = tooltip.querySelector(".tooltip-icon img");
    const tooltipDesc = tooltip.querySelector(".tooltip-desc");

    const explodedTargetFK = targetFK.split("::");
    const table = explodedTargetFK[0].toLowerCase();
    let col = explodedTargetFK[1];

    if (col == "id")
        col = "ID";

    Promise.all([
        fetch("/dbc/api/peek/" + table + "?build=" + build + "&col=" + col + "&val=" + value),
    ])
        .then(function (responses) {
            return Promise.all(responses.map(function (response) {
                if (tooltipDesc == undefined){
                    console.log("Tooltip closed before rendering finished, nevermind");
                    return;
                }
                return response.json();
            })).catch(function (error) {
                console.log("An error occurred retrieving data to generate the tooltip: " + error);
                tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
            });
        }).then(function (data) {
            if (tooltipDesc == undefined){
                console.log("Tooltip closed before rendering finished, nevermind");
                return;
            }

            const json = data[0];
            let tooltipTable = "<table class='tooltip-table'><tr><td colspan='2'><h2 class='q2'>" + targetFK + " value " + value +"</h2></td></tr>";

            if (!json || Object.keys(json.values).length == 0){
                if (table == "creature" && col == "ID"){
                    generateCreatureTooltip(value, tooltip);
                }

                tooltipTable += "<tr><td colspan='2'>Row not available in client DB</td><td>";
            }

            Object.keys(json.values).forEach(function (key) {
                const val = json.values[key];

                if (collapsedFKs.includes(targetFK.toLowerCase()) && (val == 0 || val == -1)){
                    return;
                }

                tooltipTable += "<tr><td>" + key + "</td><td>";

                if (key.startsWith("Flags") || flagMap.has(table + "." + key)){
                    tooltipTable += "0x" + dec2hex(val);
                } else if (targetFK == "PlayerCondition::ID" && key.endsWith("Logic")){
                    tooltipTable += val + " (" + parseLogic(val) + ")";
                } else {
                    tooltipTable += val;
                }
                if (enumMap.has(table + "." + key)){
                    var enumVal = getEnum(table, key, val);
                    if (val == '0' && enumVal == "Unk"){
                        // returnVar += full[meta.col];
                    } else {
                        tooltipTable += " <i>(" + enumVal + ")</i>";
                    }
                }

                tooltipTable += "</td></tr>"
            });

            tooltipTable += "</table>";

            if (collapsedFKs.includes(targetFK.toLowerCase())) {
                tooltipTable += "<p class='yellow'>(Empty/0 values hidden for this table)</p>";
            }

            tooltipDesc.innerHTML = tooltipTable;

            repositionTooltip(tooltip);
        }).catch(function (error) {
            console.log("An error occurred retrieving data to generate the tooltip: " + error);
            tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
        });
}

function generateFileTooltip(id, tooltip)
{
    console.log("Generating file tooltip for " + id);

    // const tooltipIcon = tooltip.querySelector(".tooltip-icon img");
    const tooltipDesc = tooltip.querySelector(".tooltip-desc");

    Promise.all([
        fetch("https://api.wow.tools/files/" + id),
    ])
        .then(function (responses) {
            return Promise.all(responses.map(function (response) {
                if (tooltipDesc == undefined){
                    console.log("Tooltip closed before rendering finished, nevermind");
                    return;
                }
                return response.json();
            })).catch(function (error) {
                console.log("An error occurred retrieving data to generate the tooltip: " + error);
                tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
            });
        }).then(function (data) {
            if (tooltipDesc == undefined){
                console.log("Tooltip closed before rendering finished, nevermind");
                return;
            }

            console.log(data);

            const calcData = data[0];

            let tooltipTable = "<table class='tooltip-table'><tr><td colspan='2'><h2 class='q2'>FileDataID " + calcData["fileDataID"] + "</h2></td></tr>";
            if (calcData["filename"] != null){
                tooltipTable += "<tr><td>Filename</td><td>" + calcData["filename"];
                if (calcData["isOfficialFilename"] == true){
                    tooltipTable += " <img src='/img/blizz.png'>";
                }
                tooltipTable += "</td></tr>";
            } else {
                tooltipTable += "<tr><td>Filename</td><td>Unknown</td></tr>";
            }

            // if (calcData["type"] != null && calcData["type"] == "blp"){
            //     tooltipTable += "<tr><td colspan='2'><img class='tooltip-preview' src='https://wow.tools/casc/preview/fdid?buildconfig=" + SiteSettings.buildConfig + "&cdnconfig=" + SiteSettings.cdnConfig + "&filename=inlinepreview.blp&filedataid=" + calcData["fileDataID"] + "'></td></tr>";
            // }

            tooltipDesc.innerHTML = tooltipTable;
        }).catch(function (error) {
            console.log("An error occurred retrieving data to generate the tooltip: " + error);
            tooltipDesc.innerHTML = "An error occured generating the tooltip: " + error;
        });
}

function repositionTooltip(tooltip){
    const tooltipRect = tooltip.getBoundingClientRect();
    if (tooltipRect.bottom > window.innerHeight){
        tooltip.style.top =  (window.innerHeight - (tooltipRect.bottom - tooltipRect.top) - 5) + "px";

    }
}

function hideTooltip(el){
    return;
    if (document.getElementById("keepTooltips")){
        if (document.getElementById("keepTooltips").checked){
            return;
        }
    }

    if (el.children.length > 0){
        for (let i = 0; i < el.children.length; i++){
            if (el.children[i].classList.contains('wt-tooltip')){
                el.removeChild(el.children[i]);
            }
        }
    }
}
