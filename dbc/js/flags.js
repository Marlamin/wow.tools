// Flags are currently retrieved from TrinityCore repo, in a best case scenario these would come from DBD.
const itemSparseFlags0 = {
	0x1 : 'NO_PICKUP',
	0x2 : 'CONJURED', // Conjured item
	0x3 : 'HAS_LOOT', // Item can be right clicked to open for loot
	0x4 : 'HEROIC_TOOLTIP', // Makes green "Heroic" text appear on item
	0x10 : 'DEPRECATED', // Cannot equip or use
	0x20 : 'NO_USER_DESTROY', // Item can not be destroyed, except by using spell (item can be reagent for spell)
	0x40 : 'PLAYERCAST', // Item's spells are castable by players
	0x80 : 'NO_EQUIP_COOLDOWN', // No default 30 seconds cooldown when equipped
	0x100 : 'MULTI_LOOT_QUEST',
	0x200 : 'IS_WRAPPER', // Item can wrap other items
	0x400 : 'USES_RESOURCES',
	0x800 : 'MULTI_DROP', // Looting this item does not remove it from available loot
	0x1000 : 'ITEM_PURCHASE_RECORD', // Item can be returned to vendor for its original cost (extended cost)
	0x2000 : 'PETITION', // Item is guild or arena charter
	0x4000 : 'HAS_TEXT', // Only readable items have this (but not all)
	0x8000 : 'NO_DISENCHANT',
	0x10000 : 'REAL_DURATION',
	0x20000 : 'NO_CREATOR',
	0x40000 : 'IS_PROSPECTABLE', // Item can be prospected
	0x80000 : 'UNIQUE_EQUIPPABLE', // You can only equip one of these
	0x100000 : 'IGNORE_FOR_AURAS',
	0x200000 : 'IGNORE_DEFAULT_ARENA_RESTRICTIONS', // Item can be used during arena match
	0x400000 : 'NO_DURABILITY_LOSS', // Some Thrown weapons have it (and only Thrown) but not all
	0x800000 : 'USE_WHEN_SHAPESHIFTED', // Item can be used in shapeshift forms
	0x1000000 : 'HAS_QUEST_GLOW',
	0x2000000 : 'HIDE_UNUSABLE_RECIPE', // Profession recipes can only be looted if you meet requirements and don't already know it
	0x4000000 : 'NOT_USEABLE_IN_ARENA', // Item cannot be used in arena
	0x8000000 : 'IS_BOUND_TO_ACCOUNT', // Item binds to account and can be sent only to your own characters
	0x10000000 : 'NO_REAGENT_COST', // Spell is cast ignoring reagents
	0x20000000 : 'IS_MILLABLE', // Item can be milled
	0x40000000 : 'REPORT_TO_GUILD_CHAT',
	0x80000000 : 'NO_PROGRESSIVE_LOOT'
}

const itemSparseFlags1 = {
	0x1 : 'FACTION_HORDE',
	0x2 : 'FACTION_ALLIANCE',
	0x4 : 'DONT_IGNORE_BUY_PRICE', // when item uses extended cost, gold is also required
	0x8 : 'CLASSIFY_AS_CASTER',
	0x10 : 'CLASSIFY_AS_PHYSICAL',
	0x20 : 'EVERYONE_CAN_ROLL_NEED',
	0x40 : 'NO_TRADE_BIND_ON_ACQUIRE',
	0x80 : 'CAN_TRADE_BIND_ON_ACQUIRE',
	0x100 : 'CAN_ONLY_ROLL_GREED',
	0x200 : 'CASTER_WEAPON',
	0x400 : 'DELETE_ON_LOGIN',
	0x800 : 'INTERNAL_ITEM',
	0x1000 : 'NO_VENDOR_VALUE',
	0x2000 : 'SHOW_BEFORE_DISCOVERED',
	0x4000 : 'OVERRIDE_GOLD_COST',
	0x8000 : 'IGNORE_DEFAULT_RATED_BG_RESTRICTIONS',
	0x10000 : 'NOT_USABLE_IN_RATED_BG',
	0x20000 : 'BNET_ACCOUNT_TRADE_OK',
	0x40000 : 'CONFIRM_BEFORE_USE',
	0x80000 : 'REEVALUATE_BONDING_ON_TRANSFORM',
	0x100000 : 'NO_TRANSFORM_ON_CHARGE_DEPLETION',
	0x200000 : 'NO_ALTER_ITEM_VISUAL',
	0x400000 : 'NO_SOURCE_FOR_ITEM_VISUAL',
	0x800000 : 'IGNORE_QUALITY_FOR_ITEM_VISUAL_SOURCE',
	0x1000000 : 'NO_DURABILITY',
	0x2000000 : 'ROLE_TANK',
	0x4000000 : 'ROLE_HEALER',
	0x8000000 : 'ROLE_DAMAGE',
	0x10000000 : 'CAN_DROP_IN_CHALLENGE_MODE',
	0x20000000 : 'NEVER_STACK_IN_LOOT_UI',
	0x40000000 : 'DISENCHANT_TO_LOOT_TABLE',
	0x80000000 : 'USED_IN_A_TRADESKILL'
}

const itemSparseFlags2 = {
	0x1 : 'DONT_DESTROY_ON_QUEST_ACCEPT',
	0x2 : 'ITEM_CAN_BE_UPGRADED',
	0x4 : 'UPGRADE_FROM_ITEM_OVERRIDES_DROP_UPGRADE',
	0x8 : 'ALWAYS_FFA_IN_LOOT',
	0x10 : 'HIDE_UPGRADE_LEVELS_IF_NOT_UPGRADED',
	0x20 : 'UPDATE_INTERACTIONS',
	0x40 : 'UPDATE_DOESNT_LEAVE_PROGRESSIVE_WIN_HISTORY',
	0x80 : 'IGNORE_ITEM_HISTORY_TRACKER',
	0x100 : 'IGNORE_ITEM_LEVEL_CAP_IN_PVP',
	0x200 : 'DISPLAY_AS_HEIRLOOM', // Item appears as having heirloom quality ingame regardless of its real quality (does not affect stat calculation)
	0x400 : 'SKIP_USE_CHECK_ON_PICKUP',
	0x800 : 'OBSOLETE',
	0x1000 : 'DONT_DISPLAY_IN_GUILD_NEWS', // Item is not included in the guild news panel
	0x2000 : 'PVP_TOURNAMENT_GEAR',
	0x4000 : 'REQUIRES_STACK_CHANGE_LOG',
	0x8000 : 'UNUSED_FLAG',
	0x10000 : 'HIDE_NAME_SUFFIX',
	0x20000 : 'PUSH_LOOT',
	0x40000 : 'DONT_REPORT_LOOT_LOG_TO_PARTY',
	0x80000 : 'ALWAYS_ALLOW_DUAL_WIELD',
	0x100000 : 'OBLITERATABLE',
	0x200000 : 'ACTS_AS_TRANSMOG_HIDDEN_VISUAL_OPTION',
	0x400000 : 'EXPIRE_ON_WEEKLY_RESET',
	0x800000 : 'DOESNT_SHOW_UP_IN_TRANSMOG_UNTIL_COLLECTED',
	0x1000000 : 'CAN_STORE_ENCHANTS',
	0x2000000 : 'HIDE_QUEST_ITEM_FROM_OBJECT_TOOLTIP',
	0x4000000 : 'DO_NOT_TOAST',
	0x8000000 : 'IGNORE_CREATION_CONTEXT_FOR_PROGRESSIVE_WIN_HISTORY',
	0x10000000 : 'FORCE_ALL_SPECS_FOR_ITEM_HISTORY',
	0x20000000 : 'SAVE_ON_CONSUME',
	0x40000000 : 'CONTAINER_SAVES_PLAYER_DATA',
	0x80000000 : 'NO_VOID_STORAGE'
}

const itemSparseFlags3 = {
	0x1 : 'HANDLE_ON_USE_EFFECT_IMMEDIATELY',
	0x2 : 'ALWAYS_SHOW_ITEM_LEVEL_IN_TOOLTIP',
	0x4 : 'SHOWS_GENERATION_WITH_RANDOM_STATS',
	0x8 : 'ACTIVATE_ON_EQUIP_EFFECTS_WHEN_TRANSMOGRIFIED',
	0x10 : 'ENFORCE_TRANSMOG_WITH_CHILD_ITEM',
	0x20 : 'SCRAPABLE',
	0x40 : 'BYPASS_REP_REQUIREMENTS_FOR_TRANSMOG',
	0x80 : 'DISPLAY_ONLY_ON_DEFINED_RACES',
	0x100 : 'REGULATED_COMMODITY',
	0x200 : 'CREATE_LOOT_IMMEDIATELY',
	0x400 : 'GENERATE_LOOT_SPEC_ITEM'
}


const classMask = {
	0x1 : 'WARRIOR',
	0x2 : 'PALADIN',
	0x4 : 'HUNTER',
	0x8 : 'ROGUE',
	0x10 : 'PRIEST',
	0x20 : 'DEATH_KNIGHT',
	0x40 : 'SHAMAN',
	0x80 : 'MAGE',
	0x100 : 'WARLOCK',
	0x200 : 'MONK',
	0x400 : 'DRUID',
	0x800 : 'DEMON_HUNTER',
}

const achievementFlags = {
	0x1 : 'COUNTER',
	0x2 : 'HIDDEN',
	0x4 : 'PLAY_NO_VISUAL',
	0x8 : 'SUM',
	0x10 : 'MAX_USED',
	0x20 : 'REQ_COUNT',
	0x40 : 'AVERAGE',
	0x80 : 'PROGRESS_BAR',
	0x100 : 'REALM_FIRST_REACH',
	0x200 : 'REALM_FIRST_KILL',
	0x400 : 'UNK3',
	0x800 : 'HIDE_INCOMPLETE',
	0x1000 : 'SHOW_IN_GUILD_NEWS',
	0x2000 : 'SHOW_IN_GUILD_HEADER',
	0x4000 : 'GUILD',
	0x8000 : 'SHOW_GUILD_MEMBERS',
	0x10000 : 'SHOW_CRITERIA_MEMBERS',
	0x20000 : 'ACCOUNT_WIDE',
	0x40000 : 'UNK5',
	0x80000 : 'HIDE_ZERO_COUNTER',
	0x100000 : 'TRACKING_FLAG',
}

const charSectionFlags = {
	0x1 : 'CHAR',
	0x2 : 'BARBERSHOP',
	0x4 : 'DEATHKNIGHT',
	0x8 : 'NPCSKIN',
	0x10 : 'SKIN',
	0x20 : 'DEMONHUNTER',
	0x40 : 'DEMONHUNTERFACE',
	0x80 : 'DHBLINDFOLDS',
	0x100 : 'SILHOUETTE',
	0x200 : 'VOIDELF',
	0x400 : 'HAS_CONDITION?'
}

const chrRacesFlags = {
	0x1 : 'NOT_PLAYABLE',
	0x2 : 'BARE_FEET',
	0x4 : 'CAN_MOUNT',
	0x8 : 'PLAYABLE_MAYBE',
	0x80 : 'DISALLOW_LOW_RES',
	0x100 : 'GOBLIN_RACIAL',
	0x200 : 'CREATIONUNK',
	0x400 : 'SELECTIONUNK',
	0x10000 : 'SKINISHAIRUNK',
}

const taxiNodeFlags = {
	0x1 : 'ALLIANCE',
	0x2 : 'HORDE',
	0x10 : 'USE_FAVORITE_MOUNT'
}

const difficultyFlags = {
	0x1: 'HEROIC',
	0x2: 'DEFAULT',
	0x4: 'CAN_SELECT',
	0x8: 'CHALLENGE_MODE',
	0x20: 'LEGACY',
	0x40: 'DISPLAY_HEROIC',
	0x80: 'DISPLAY_MYTHIC'
}

const emoteFlags = {

}

const mapFlags = {
	0x100: 'CAN_TOGGLE_DIFFICULTY',
	0x8000: 'FLEX_LOCKING',
	0x4000000: 'GARRISON'
}

const soundkitFlags = {
	0x0001: 'UNK1',
	0x0020: 'NO_DUPLICATES',
	0x0200: 'LOOPING',
	0x0400: 'VARY_PITCH',
	0x0800: 'VARY_VOLUME'
};

const globalstringsFlags ={
	0x1: 'FRAMEXML',
	0x2: 'GLUEXML'
};


window.flagMap = new Map();

flagMap.set("achievement.Flags", achievementFlags);

flagMap.set("itemsparse.Flags[0]", itemSparseFlags0);
flagMap.set("itemsparse.Flags[1]", itemSparseFlags1);
flagMap.set("itemsparse.Flags[2]", itemSparseFlags2);
flagMap.set("itemsparse.Flags[3]", itemSparseFlags3);

flagMap.set("playercondition.ClassMask", classMask);

flagMap.set("charsections.Flags", charSectionFlags);

flagMap.set("chrraces.Flags", chrRacesFlags);

flagMap.set("taxinodes.Flags", taxiNodeFlags);

flagMap.set("difficulty.Flags", difficultyFlags);

flagMap.set("emotes.EmoteFlags", emoteFlags);

flagMap.set("map.Flags[0]", mapFlags);

flagMap.set("soundkit.Flags", soundkitFlags);

flagMap.set("globalstrings.Flags", globalstringsFlags);

// Conditional flags
let conditionalFlags = new Map();
conditionalFlags.set("chrcustomizationreq.ReqValue",
	[
		['chrcustomizationreq.ReqType=1', classMask],
	]
);