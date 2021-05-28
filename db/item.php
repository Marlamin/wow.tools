<?php
require_once("../inc/header.php");
?>
<link href="/dbc/css/dbc.css?v=<?=filemtime("/var/www/wow.tools/dbc/css/dbc.css")?>" rel="stylesheet">
<div class="container-fluid">
    <div class='row'>
        <div class='col-md-12'>    
            <h3 id='itemsearchname'></h3>
            <div id='itemInformation'>

            </div>
        </div>
    </div> 
</div>
<style type='text/css'>
tr.selected{
    background-color: #8bc34aa1 !important;
}
</style>
<script type="text/javascript" src="/js/main.js?v=<?=filemtime("/var/www/wow.tools/js/main.js")?>"></script>
<script type="text/javascript" src="/js/tooltips.js?v=<?=filemtime("/var/www/wow.tools/js/tooltips.js")?>"></script>
<script src="/files/js/files.js?v=<?=filemtime("/var/www/wow.tools/files/js/files.js")?>" crossorigin="anonymous"></script>
<script src="/dbc/js/dbc.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/dbc.js")?>"></script>
<script src="/dbc/js/enums.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/enums.js")?>"></script>
<script src="/dbc/js/flags.js?v=<?=filemtime("/var/www/wow.tools/dbc/js/flags.js")?>"></script>
<script type='text/javascript'>
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });

    const searchParams = new URL(window.location).searchParams;

    let itemID = 0;
    if(searchParams.has("itemID")){
        itemID = Number(searchParams.get("itemID"));
    }

    let givenItemBonusListIDs = [];
    if(searchParams.has("bonusIDs")){
        givenItemBonusListIDs = searchParams.get("bonusIDs").split(',').map(function (stringNum) { return parseInt(stringNum, 10); });
    }

    let embeddedMode = false;
    if(searchParams.has("embed")){
        embeddedMode = true;
    }

    if(embeddedMode){
    }

    if(itemID != 0){
        loadItem(itemID);
    }

    async function loadItem(id){
        document.getElementById("itemsearchname").innerHTML = "<i class='fa fa-spinner fa-spin'></i>";
        document.getElementById("itemInformation").innerHTML = "";

        document.getElementById("itemInformation").innerHTML = "<br><br><br><ul style='clear: both' class='nav nav-tabs' id='itemTabs'></ul><div class='tab-content' id='itemTabContent'></div>";
        try{
            const tooltipResponse = await fetch("/dbc/api/tooltip/item/" + id + "?build=" + SiteSettings.buildName);
            const tooltipJson = await tooltipResponse.json();
            loadTooltip(id, tooltipJson);
        }catch(e){
            console.log("Error loading tooltip", e);
        }

        genItemBonusTab(id);
        document.getElementById("itemsearchname").innerHTML = "";
    }

    async function getItemBonusesByBonusListID(bonusListID){
        const itemBonusResponse = await fetch("/dbc/api/find/ItemBonus/?build=" + SiteSettings.buildName + "&col=ParentItemBonusListID&val=" + bonusListID);
        const itemBonusJson = await itemBonusResponse.json();
        return itemBonusJson;
    }

    async function getItemBonusesByTreeID(treeID){
        const itemBonusTreeNodeResponse = await fetch("/dbc/api/find/ItemBonusTreeNode/?build=" + SiteSettings.buildName + "&col=ParentItemBonusTreeID&val=" + treeID);
        const itemBonusTreeNodeJson = await itemBonusTreeNodeResponse.json();

        let itemBonusListIDs = [];

        for (const itemBonusTreeNodeRow of itemBonusTreeNodeJson){
            // console.log(itemBonusTreeNodeRow);

            if(itemBonusTreeNodeRow.ChildItemBonusTreeID != 0){
                let subItemBonuses = await getItemBonusesByTreeID(itemBonusTreeNodeRow.ChildItemBonusTreeID);
                if(subItemBonuses.length > 0){
                    itemBonusListIDs.push(...subItemBonuses);
                }
            }

            if(itemBonusTreeNodeRow.ChildItemBonusListID != 0){
                itemBonusListIDs.push(Number(itemBonusTreeNodeRow.ChildItemBonusListID));
                // let itemBonuses = await getItemBonusesByBonusListID(itemBonusTreeNodeRow.ChildItemBonusListID);
            }
        }

        return itemBonusListIDs;
    }

    async function genItemBonusTab(itemID){
        console.log(itemID);
        const itemXBonusTreeResponse = await fetch("/dbc/api/find/ItemXBonusTree/?build=" + SiteSettings.buildName + "&col=ItemID&val=" + itemID);
        const itemXBonusTreeJson = await itemXBonusTreeResponse.json();

        let itemBonusListIDs = givenItemBonusListIDs;
        
        console.log("Given bonus list IDs in URL", givenItemBonusListIDs);

        for(const itemXBonusTreeRow of itemXBonusTreeJson){
            console.log(itemXBonusTreeRow);
            let itemBonusListIDsFromTree = await getItemBonusesByTreeID(itemXBonusTreeRow.ItemBonusTreeID);

            console.log("Item bonus lists retrieved from tree " + itemXBonusTreeRow.ItemBonusTreeID, itemBonusListIDsFromTree);

            itemBonusListIDs.push(...itemBonusListIDsFromTree);
        }

        console.log("ItemBonusListIDs", itemBonusListIDs);

        let contents = "<div class='tab-pane fade show active' id='itembonus' role='tabpanel' aria-labelledby='itembonus-tab'><table class='table table-sm table-striped' style='clear: both'>";

        for(const itemBonusListID of new Set(itemBonusListIDs)){
            const itemBonuses = await getItemBonusesByBonusListID(itemBonusListID);
            contents += "<tr><td style='width: 250px'>ItemBonusListID " + itemBonusListID + "</td><td><table class='table table-condensed table-hover subtable' style='width: 100%; font-size: 12px'>";
            for(const itemBonus of itemBonuses.sort(function(a, b) {return parseInt(a.OrderIndex) - parseInt(b.OrderIndex)})){
                console.log(itemBonus);
                let itemBonusType = "Unknown bonus type";
                if(itemBonus.Type in itemBonusTypes){
                    itemBonusType = itemBonusTypes[itemBonus.Type];
                }
                contents += "<tr>"
                contents += "<td style='width: 100px'>" + itemBonus.ID + "</td>";
                contents += "<td>" + itemBonus.Type + " (" + itemBonusType + ")</td>";
                for(let i = 0; i < 4; i++){

                    let itemBonusValueHTML = itemBonus['Value[' + i + ']'];

                    if(conditionalEnums.has("itembonus.Value[" + i + "]")){
                        const conditionalEnum = conditionalEnums.get("itembonus.Value[" + i + "]");
                        conditionalEnum.forEach(function(conditionalEnumEntry) {
                            let condition = conditionalEnumEntry[0].split('=');
                            let conditionTarget = condition[0].split('.');
                            let conditionValue = condition[1];
                            let resultEnum = conditionalEnumEntry[1];

                            if (conditionTarget[1] in itemBonus) {
                                if (itemBonus[conditionTarget[1]] == conditionValue) {
                                    var enumVal = getEnumVal(resultEnum, itemBonus["Value[" + i + "]"]);
                                    if (itemBonus["Value[" + i  +"]"] == '0' && enumVal == "Unk") {
                                        itemBonusValueHTML = itemBonus["Value[" + i + "]"];
                                    } else {
                                        itemBonusValueHTML = itemBonus["Value[" + i + "]"] +" <i>(" + enumVal + ")</i>";
                                    }
                                }
                            }
                        });
                    }

                    contents += "<td style='width: 300px;'>" + itemBonusValueHTML + "</td>";

                }
                contents += "</tr>";
            }
            contents += "</table></td></tr>";
        }

        contents += "</table></div>";
        document.getElementById('itemTabs').insertAdjacentHTML("beforeend", "<li class='nav-item '><a class='nav-link active' id='itembonus-tab' data-toggle='tab' href='#itembonus' role='tab'>ItemBonus</a></li>");
        document.getElementById("itemTabContent").insertAdjacentHTML("beforeend", contents);
    }

    // function genSpellEffectsTab(spellEffectEntries){
    //     if(spellEffectEntries.length == 0)
    //         return;

    //     document.getElementById('itemTabs').insertAdjacentHTML("beforeend", "<li class='nav-item'><a class='nav-link' id='effects-tab' data-toggle='tab' href='#effects' role='tab'>Effects</a></li>");

    //     let contents = "<div class='tab-pane fade show' id='effects' role='tabpanel' aria-labelledby='effects-tab'><table class='table table-sm table-striped' style='clear: both'>";

    //     let effectIndex = 0;
    //     spellEffectEntries.forEach(effectEntry => {
    //         Object.keys(effectEntry).forEach(element => {
    //             if(element == "ID" || element == "SpellID" || effectEntry[element] == 0)
    //                 return;

    //             contents += "<tr><td>Effect " + effectEntry['EffectIndex'] + " " + element + "</td><td>" + effectEntry[element] + "</td></tr>";

    //             effectIndex++;
    //         });
    //     });
        
    //     contents += "</table></div>";

    //     document.getElementById("itemTabContent").insertAdjacentHTML("beforeend", contents);
    // }

    // function genSpellTable(spellEntry, spellMiscEntry){
    //     document.getElementById('itemTabs').insertAdjacentHTML("beforeend", "<li class='nav-item'><a class='nav-link active' id='base-tab' data-toggle='tab' href='#base' role='tab'>Base</a></li>");

    //     let contents = "<div class='tab-pane fade show active' id='base' role='tabpanel' aria-labelledby='base-tab'><table class='table table-sm table-striped' style='clear: both'>";

    //     if (spellEntry['NameSubtext_lang'] != ""){
    //         contents += "<tr><td>Name subtext (raw)</td><td>" + spellEntry['NameSubtext_lang'] + "</td></tr>";
    //     }

    //     if (spellEntry['Description_lang'] != ""){
    //         contents += "<tr><td>Description (raw)</td><td>" + spellEntry['Description_lang'] + "</td></tr>";
    //     }

    //     if (spellEntry['AuraDescription_lang'] != ""){
    //         contents += "<tr><td>Aura desc (raw)</td><td>" + spellEntry['AuraDescription_lang'] + "</td></tr>";
    //     }

    //     Object.keys(spellMiscEntry).forEach(element => {
    //         if(element == "ID" || element == "SpellID" || spellMiscEntry[element] == 0)
    //             return;

    //         if(element.substr(0, 10) == "Attributes"){
    //             var usedFlags = getFlagDescriptions("spellmisc", element, spellMiscEntry[element]);
    //             var flagTable = fancyFlagTable(usedFlags);
    //             contents += "<tr><td>" + element + "</td><td>" + spellMiscEntry[element] + "<br><br>" + flagTable + "</td></tr>";
    //         }else{
    //             contents += "<tr><td>" + element + "</td><td>" + spellMiscEntry[element] + "</td></tr>";
    //         }
    //     });
        
    //     contents += "</table></div>";

    //     document.getElementById("itemTabContent").insertAdjacentHTML("beforeend", contents);
    // }

    function loadTooltip(id, data){
         let defaultTooltipHTML = "<div id='tooltip'><div class='tooltip-icon' style='display: none'><img src='https://wow.tools/casc/preview/chash?buildconfig=" + SiteSettings.buildConfig + "&cdnconfig=" + SiteSettings.cdnConfig + "&filename=interface%2Ficons%2Finv_misc_questionmark.blp&contenthash=45809010e72cafe336851539a9805b80'/></div><div class='tooltip-desc'>Generating tooltip..</div></div></div>";

        tooltipDiv = document.createElement("div");
        tooltipDiv.dataset.type = 'item';
        tooltipDiv.dataset.id = id;
        tooltipDiv.innerHTML = defaultTooltipHTML;
        tooltipDiv.style.position = "relative";
        tooltipDiv.style.display = "block";
        tooltipDiv.id = "wtTooltip";
        tooltipDiv.classList.add('wt-tooltip');
        
        document.getElementById("itemInformation").insertAdjacentElement("afterBegin", tooltipDiv);
        const tooltipIcon = tooltipDiv.querySelector(".tooltip-icon img");
        const tooltipDesc = tooltipDiv.querySelector(".tooltip-desc");
        tooltipDesc.innerHTML = "<h2>" + data["name"] + "</h2>";
        if (data["description"] != null){
            tooltipDesc.innerHTML += "<p class='yellow'>" + data["description"].replace("\n", "<br><br>");
        }
        tooltipDiv.querySelector(".tooltip-icon").style.display = 'block';
        tooltipIcon.src = 'https://wow.tools/casc/preview/fdid?buildconfig=' + SiteSettings.buildConfig + '&cdnconfig=' + SiteSettings.cdnConfig + '&filename=icon.blp&filedataid=' + data["iconFileDataID"];
    }
</script>
<?php if(empty($_GET['embed'])){ require_once("../inc/footer.php"); } ?>