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
	$("#fkModalContent").html("<b>Lookup into table " + splitLocation[0].toLowerCase() + " on col '" + splitLocation[1] + "' value '" + value + "'</b><br><br><table id='fktable' class='table table-condensed table-striped'>");
	$.ajax({
		"url": "/dbc/api/header/" + splitLocation[0].toLowerCase() + "?build=" + $("#buildFilter").val(),
		"success": function(headerjson) {
			console.log(headerjson);
			$.ajax({
				"url": "/dbc/api/peek/" + splitLocation[0].toLowerCase() + "?build=" + $("#buildFilter").val() + "&col=" + splitLocation[1] + "&val=" + value,
				"success": function(json) {
					json.values.forEach(function (value) {
						if(value.item1 in headerjson.fks){
							if(headerjson.fks[value.item1] == "SoundEntries::ID" && parseInt($("#buildFilter").val()[0]) > 6){
								$("#fktable").append("<tr><td>" + value.item1 + "</td><td><a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' onclick='openFKModal(" + value.item2 + ", \"SoundKit::ID\")'>" + value.item2 + "</a></td></tr>");
							}else{
								$("#fktable").append("<tr><td>" + value.item1 + "</td><td><a style='padding-top: 0px; padding-bottom: 0px; cursor: pointer; border-bottom: 1px dotted;' onclick='openFKModal(" + value.item2 + ", \"" + headerjson.fks[value.item1] + "\")'>" + value.item2 + "</a></td></tr>");
							}
						}else{
							$("#fktable").append("<tr><td>" + value.item1 + "</td><td>" + value.item2 + "</td></tr>");
						}
					});
					var numRecordsIntoPage = json.offset - Math.floor((json.offset - 1) / 25) * 25;
					var page = Math.floor(((json.offset - 1) / 25) + 1);
					$("#fkModalContent").append("<a target=\"_BLANK\" href=\"/dbc/?dbc=" + splitLocation[0].replace(".db2", "").toLowerCase() + "&build=" + $("#buildFilter").val() + "#page=" + page + "&row=" + numRecordsIntoPage + "\" class=\"btn btn-primary\">Jump to record</a>");
				}
			});
		}
	});
}


function getFlagDescriptions(db, field, value){
	// Flags are retrieved from TrinityCore repo, in a best case scenario these would come from DBD.
	var itemSparseFlags0 = {
		NO_PICKUP 							: 0x1,
		CONJURED							: 0x2, // Conjured item
		HAS_LOOT							: 0x3, // Item can be right clicked to open for loot
		HEROIC_TOOLTIP						: 0x4, // Makes green "Heroic" text appear on item
		DEPRECATED							: 0x10, // Cannot equip or use
		NO_USER_DESTROY						: 0x20, // Item can not be destroyed, except by using spell (item can be reagent for spell)
		PLAYERCAST							: 0x40, // Item's spells are castable by players
		NO_EQUIP_COOLDOWN					: 0x80, // No default 30 seconds cooldown when equipped
		MULTI_LOOT_QUEST					: 0x100,
		IS_WRAPPER							: 0x200, // Item can wrap other items
		USES_RESOURCES						: 0x400,
		MULTI_DROP							: 0x800, // Looting this item does not remove it from available loot
		ITEM_PURCHASE_RECORD				: 0x1000, // Item can be returned to vendor for its original cost (extended cost)
		PETITION							: 0x2000, // Item is guild or arena charter
		HAS_TEXT							: 0x4000, // Only readable items have this (but not all)
		NO_DISENCHANT						: 0x8000,
		REAL_DURATION						: 0x10000,
		NO_CREATOR							: 0x20000,
		IS_PROSPECTABLE						: 0x40000, // Item can be prospected
		UNIQUE_EQUIPPABLE					: 0x80000, // You can only equip one of these
		IGNORE_FOR_AURAS					: 0x100000,
		IGNORE_DEFAULT_ARENA_RESTRICTIONS	: 0x200000, // Item can be used during arena match
		NO_DURABILITY_LOSS					: 0x400000, // Some Thrown weapons have it (and only Thrown) but not all
		USE_WHEN_SHAPESHIFTED				: 0x800000, // Item can be used in shapeshift forms
		HAS_QUEST_GLOW						: 0x1000000,
		HIDE_UNUSABLE_RECIPE				: 0x2000000, // Profession recipes can only be looted if you meet requirements and don't already know it
		NOT_USEABLE_IN_ARENA				: 0x4000000, // Item cannot be used in arena
		IS_BOUND_TO_ACCOUNT					: 0x8000000, // Item binds to account and can be sent only to your own characters
		NO_REAGENT_COST						: 0x10000000, // Spell is cast ignoring reagents
		IS_MILLABLE							: 0x20000000, // Item can be milled
		REPORT_TO_GUILD_CHAT				: 0x40000000,
		NO_PROGRESSIVE_LOOT					: 0x80000000
	}

	var itemSparseFlags1 = {
		FACTION_HORDE									: 0x1,
		FACTION_ALLIANCE								: 0x2,
		DONT_IGNORE_BUY_PRICE							: 0x4, // when item uses extended cost, gold is also required
		CLASSIFY_AS_CASTER								: 0x8,
		CLASSIFY_AS_PHYSICAL							: 0x10,
		EVERYONE_CAN_ROLL_NEED							: 0x20,
		NO_TRADE_BIND_ON_ACQUIRE						: 0x40,
		CAN_TRADE_BIND_ON_ACQUIRE						: 0x80,
		CAN_ONLY_ROLL_GREED								: 0x100,
		CASTER_WEAPON									: 0x200,
		DELETE_ON_LOGIN									: 0x400,
		INTERNAL_ITEM									: 0x800,
		NO_VENDOR_VALUE									: 0x1000,
		SHOW_BEFORE_DISCOVERED							: 0x2000,
		OVERRIDE_GOLD_COST								: 0x4000,
		IGNORE_DEFAULT_RATED_BG_RESTRICTIONS			: 0x8000,
		NOT_USABLE_IN_RATED_BG							: 0x10000,
		BNET_ACCOUNT_TRADE_OK							: 0x20000,
		CONFIRM_BEFORE_USE								: 0x40000,
		REEVALUATE_BONDING_ON_TRANSFORM					: 0x80000,
		NO_TRANSFORM_ON_CHARGE_DEPLETION				: 0x100000,
		NO_ALTER_ITEM_VISUAL							: 0x200000,
		NO_SOURCE_FOR_ITEM_VISUAL						: 0x400000,
		IGNORE_QUALITY_FOR_ITEM_VISUAL_SOURCE			: 0x800000,
		NO_DURABILITY									: 0x1000000,
		ROLE_TANK										: 0x2000000,
		ROLE_HEALER										: 0x4000000,
		ROLE_DAMAGE										: 0x8000000,
		CAN_DROP_IN_CHALLENGE_MODE						: 0x10000000,
		NEVER_STACK_IN_LOOT_UI							: 0x20000000,
		DISENCHANT_TO_LOOT_TABLE						: 0x40000000,
		USED_IN_A_TRADESKILL							: 0x80000000
	}

	var itemSparseFlags2 = {
    	DONT_DESTROY_ON_QUEST_ACCEPT                         : 0x1,
    	ITEM_CAN_BE_UPGRADED                                 : 0x2,
    	UPGRADE_FROM_ITEM_OVERRIDES_DROP_UPGRADE             : 0x4,
    	ALWAYS_FFA_IN_LOOT                                   : 0x8,
    	HIDE_UPGRADE_LEVELS_IF_NOT_UPGRADED                  : 0x10,
    	UPDATE_INTERACTIONS                                  : 0x20,
    	UPDATE_DOESNT_LEAVE_PROGRESSIVE_WIN_HISTORY          : 0x40,
    	IGNORE_ITEM_HISTORY_TRACKER                          : 0x80,
    	IGNORE_ITEM_LEVEL_CAP_IN_PVP                         : 0x100,
    	DISPLAY_AS_HEIRLOOM                                  : 0x200, // Item appears as having heirloom quality ingame regardless of its real quality (does not affect stat calculation)
    	SKIP_USE_CHECK_ON_PICKUP                             : 0x400,
    	OBSOLETE                                             : 0x800,
    	DONT_DISPLAY_IN_GUILD_NEWS                           : 0x1000, // Item is not included in the guild news panel
    	PVP_TOURNAMENT_GEAR                                  : 0x2000,
    	REQUIRES_STACK_CHANGE_LOG                            : 0x4000,
    	UNUSED_FLAG                                          : 0x8000,
    	HIDE_NAME_SUFFIX                                     : 0x10000,
    	PUSH_LOOT                                            : 0x20000,
    	DONT_REPORT_LOOT_LOG_TO_PARTY                        : 0x40000,
    	ALWAYS_ALLOW_DUAL_WIELD                              : 0x80000,
    	OBLITERATABLE                                        : 0x100000,
    	ACTS_AS_TRANSMOG_HIDDEN_VISUAL_OPTION                : 0x200000,
    	EXPIRE_ON_WEEKLY_RESET                               : 0x400000,
    	DOESNT_SHOW_UP_IN_TRANSMOG_UNTIL_COLLECTED           : 0x800000,
    	CAN_STORE_ENCHANTS                                   : 0x1000000,
    	HIDE_QUEST_ITEM_FROM_OBJECT_TOOLTIP                  : 0x2000000,
    	DO_NOT_TOAST                                         : 0x4000000,
    	IGNORE_CREATION_CONTEXT_FOR_PROGRESSIVE_WIN_HISTORY  : 0x8000000,
    	FORCE_ALL_SPECS_FOR_ITEM_HISTORY                     : 0x10000000,
    	SAVE_ON_CONSUME                                      : 0x20000000,
    	CONTAINER_SAVES_PLAYER_DATA                          : 0x40000000,
    	NO_VOID_STORAGE                                      : 0x80000000
	}

	var itemSparseFlags3 = {
		HANDLE_ON_USE_EFFECT_IMMEDIATELY                 : 0x1,
		ALWAYS_SHOW_ITEM_LEVEL_IN_TOOLTIP                : 0x2,
		SHOWS_GENERATION_WITH_RANDOM_STATS               : 0x4,
		ACTIVATE_ON_EQUIP_EFFECTS_WHEN_TRANSMOGRIFIED    : 0x8,
		ENFORCE_TRANSMOG_WITH_CHILD_ITEM                 : 0x10,
		SCRAPABLE                                        : 0x20,
		BYPASS_REP_REQUIREMENTS_FOR_TRANSMOG             : 0x40,
		DISPLAY_ONLY_ON_DEFINED_RACES                    : 0x80,
		REGULATED_COMMODITY                              : 0x100,
		CREATE_LOOT_IMMEDIATELY                          : 0x200,
		GENERATE_LOOT_SPEC_ITEM                          : 0x400
	}

	let targetFlags = {};
	switch(db){
		case "itemsparse":
		switch(field){
			case "Flags[0]":
			targetFlags = itemSparseFlags0;
			break;
			case "Flags[1]":
			targetFlags = itemSparseFlags1;
			break;
			case "Flags[2]":
			targetFlags = itemSparseFlags2;
			break;
			case "Flags[3]":
			targetFlags = itemSparseFlags3;
			break;
		}
	}

	let usedFlags = Array();
	Object.keys(targetFlags).forEach(function (flag) {
		if(value & targetFlags[flag]){
			usedFlags.push(flag);
		}
	});

	return usedFlags;
}