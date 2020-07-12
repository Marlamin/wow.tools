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

const spellAttributes0 = {
    0x00000002: 'REQ_AMMO', //  1 on next ranged
    0x00000004: 'ON_NEXT_SWING', //  2
    0x00000008: 'IS_REPLENISHMENT', //  3 not set in 3.0.3
    0x00000010: 'ABILITY', //  4 client puts 'ability' instead of 'spell' in game strings for these spells
    0x00000020: 'TRADESPELL', //  5 trade spells (recipes), will be added by client to a sublist of profession spell
    0x00000040: 'PASSIVE', //  6 Passive spell
    0x00000080: 'HIDDEN_CLIENTSIDE', //  7 Spells with this attribute are not visible in spellbook or aura bar
    0x00000100: 'HIDE_IN_COMBAT_LOG', //  8 This attribite controls whether spell appears in combat logs
    0x00000200: 'TARGET_MAINHAND_ITEM', //  9 Client automatically selects item from mainhand slot as a cast target
    0x00000400: 'ON_NEXT_SWING_2', // 10
    0x00001000: 'DAYTIME_ONLY', // 12 only useable at daytime, not set in 2.4.2
    0x00002000: 'NIGHT_ONLY', // 13 only useable at night, not set in 2.4.2
    0x00004000: 'INDOORS_ONLY', // 14 only useable indoors, not set in 2.4.2
    0x00008000: 'OUTDOORS_ONLY', // 15 Only useable outdoors.
    0x00010000: 'NOT_SHAPESHIFT', // 16 Not while shapeshifted
    0x00020000: 'ONLY_STEALTHED', // 17 Must be in stealth
    0x00040000: 'DONT_AFFECT_SHEATH_STATE', // 18 client won't hide unit weapons in sheath on cast/channel
    0x00080000: 'LEVEL_DAMAGE_CALCULATION', // 19 spelldamage depends on caster level
    0x00100000: 'STOP_ATTACK_TARGET', // 20 Stop attack after use this spell (and not begin attack if use)
    0x00200000: 'IMPOSSIBLE_DODGE_PARRY_BLOCK', // 21 Cannot be dodged/parried/blocked
    0x00400000: 'CAST_TRACK_TARGET', // 22 Client automatically forces player to face target when casting
    0x00800000: 'CASTABLE_WHILE_DEAD', // 23 castable while dead?
    0x01000000: 'CASTABLE_WHILE_MOUNTED', // 24 castable while mounted
    0x02000000: 'DISABLED_WHILE_ACTIVE', // 25 Activate and start cooldown after aura fade or remove summoned creature or go
    0x04000000: 'NEGATIVE_1', // 26 Many negative spells have this attr
    0x08000000: 'CASTABLE_WHILE_SITTING', // 27 castable while sitting
    0x10000000: 'CANT_USED_IN_COMBAT', // 28 Cannot be used in combat
    0x20000000: 'UNAFFECTED_BY_INVULNERABILITY', // 29 unaffected by invulnerability (hmm possible not...)
    0x40000000: 'HEARTBEAT_RESIST_CHECK', // 30 random chance the effect will end TODO: implement core support
    0x80000000: 'CANT_CANCEL'  // 31 positive aura can't be canceled
};

const spellAttributes1 = {
    0x00000001: 'DISMISS_PET', //  0 for spells without this flag client doesn't allow to summon pet if caster has a pet
    0x00000002: 'DRAIN_ALL_POWER', //  1 use all power (Only paladin Lay of Hands and Bunyanize)
    0x00000004: 'CHANNELED_1', //  2 clientside checked? cancelable?
    0x00000008: 'CANT_BE_REDIRECTED', //  3
    0x00000020: 'NOT_BREAK_STEALTH', //  5 Not break stealth
    0x00000040: 'CHANNELED_2', //  6
    0x00000080: 'CANT_BE_REFLECTED', //  7
    0x00000100: 'CANT_TARGET_IN_COMBAT', //  8 can target only out of combat units
    0x00000200: 'MELEE_COMBAT_START', //  9 player starts melee combat after this spell is cast
    0x00000400: 'NO_THREAT', // 10 no generates threat on cast 100% (old NO_INITIAL_AGGRO)
    0x00001000: 'IS_PICKPOCKET', // 12 Pickpocket
    0x00002000: 'FARSIGHT', // 13 Client removes farsight on aura loss
    0x00004000: 'CHANNEL_TRACK_TARGET', // 14 Client automatically forces player to face target when channeling
    0x00008000: 'DISPEL_AURAS_ON_IMMUNITY', // 15 remove auras on immunity
    0x00010000: 'UNAFFECTED_BY_SCHOOL_IMMUNE', // 16 on immuniy
    0x00020000: 'UNAUTOCASTABLE_BY_PET', // 17
    0x00080000: 'CANT_TARGET_SELF', // 19
    0x00100000: 'REQ_COMBO_POINTS1', // 20 Req combo points on target
    0x00400000: 'REQ_COMBO_POINTS2', // 22 Req combo points on target
    0x01000000: 'IS_FISHING', // 24 only fishing spells
    0x10000000: 'DONT_DISPLAY_IN_AURA_BAR', // 28 client doesn't display these spells in aura bar
    0x20000000: 'CHANNEL_DISPLAY_SPELL_NAME', // 29 spell name is displayed in cast bar instead of 'channeling' text
    0x40000000: 'ENABLE_AT_DODGE', // 30 Overpower
};

const spellAttributes2 = {
    0x00000001: 'CAN_TARGET_DEAD', //  0 can target dead unit or corpse
    0x00000004: 'CAN_TARGET_NOT_IN_LOS', //  2 26368 4.0.1 dbc change
    0x00000010: 'DISPLAY_IN_STANCE_BAR', //  4 client displays icon in stance bar when learned, even if not shapeshift
    0x00000020: 'AUTOREPEAT_FLAG', //  5
    0x00000040: 'CANT_TARGET_TAPPED', //  6 target must be tapped by caster
    0x00000800: 'HEALTH_FUNNEL', // 11
    0x00002000: 'PRESERVE_ENCHANT_IN_ARENA', // 13 Items enchanted by spells with this flag preserve the enchant to arenas
    0x00010000: 'TAME_BEAST', // 16
    0x00020000: 'NOT_RESET_AUTO_ACTIONS', // 17 don't reset timers for melee autoattacks (swings) or ranged autoattacks (autoshoots)
    0x00040000: 'REQ_DEAD_PET', // 18 Only Revive pet and Heart of the Pheonix
    0x00080000: 'NOT_NEED_SHAPESHIFT', // 19 does not necessarly need shapeshift
    0x00200000: 'DAMAGE_REDUCED_SHIELD', // 21 for ice blocks, pala immunity buffs, priest absorb shields, but used also for other spells -> not sure!
    0x00800000: 'IS_ARCANE_CONCENTRATION', // 23 Only mage Arcane Concentration have this flag
    0x04000000: 'UNAFFECTED_BY_AURA_SCHOOL_IMMUNE', // 26 unaffected by school immunity
    0x10000000: 'IGNORE_ITEM_CHECK', // 28 Spell is cast without checking item requirements (charges/reagents/totem)
    0x20000000: 'CANT_CRIT', // 29 Spell can't crit
    0x40000000: 'TRIGGERED_CAN_TRIGGER_PROC', // 30 spell can trigger even if triggered
    0x80000000: 'FOOD_BUFF'  // 31 Food or Drink Buff (like Well Fed)
};

const spellAttributes3 = {
    0x00000008: 'BLOCKABLE_SPELL', //  3 Only dmg class melee in 3.1.3
    0x00000010: 'IGNORE_RESURRECTION_TIMER', //  4 you don't have to wait to be resurrected with these spells
    0x00000080: 'STACK_FOR_DIFF_CASTERS', //  7 separate stack for every caster
    0x00000100: 'ONLY_TARGET_PLAYERS', //  8 can only target players
    0x00000200: 'TRIGGERED_CAN_TRIGGER_PROC_2', //  9 triggered from effect?
    0x00000400: 'MAIN_HAND', // 10 Main hand weapon required
    0x00000800: 'BATTLEGROUND', // 11 Can only be cast in battleground
    0x00001000: 'ONLY_TARGET_GHOSTS', // 12
    0x00002000: 'DONT_DISPLAY_CHANNEL_BAR', // 13 Clientside attribute - will not display channeling bar
    0x00004000: 'IS_HONORLESS_TARGET', // 14 "Honorless Target" only this spells have this flag
    0x00010000: 'CANT_TRIGGER_PROC', // 16 confirmed with many patchnotes
    0x00020000: 'NO_INITIAL_AGGRO', // 17 Soothe Animal, 39758, Mind Soothe
    0x00040000: 'IGNORE_HIT_RESULT', // 18 Spell should always hit its target
    0x00080000: 'DISABLE_PROC', // 19 during aura proc no spells can trigger (20178, 20375)
    0x00100000: 'DEATH_PERSISTENT', // 20 Death persistent spells
    0x00400000: 'REQ_WAND', // 22 Req wand
    0x01000000: 'REQ_OFFHAND', // 24 Req offhand weapon
    0x02000000: 'TREAT_AS_PERIODIC', // 25 Makes the spell appear as periodic in client combat logs - used by spells that trigger another spell on each tick
    0x04000000: 'CAN_PROC_WITH_TRIGGERED', // 26 auras with this attribute can proc from triggered spell casts with SPELL_ATTR3_TRIGGERED_CAN_TRIGGER_PROC_2 (67736 + 52999)
    0x08000000: 'DRAIN_SOUL', // 27 only drain soul has this flag
    0x20000000: 'NO_DONE_BONUS', // 29 Ignore caster spellpower and done damage mods?  client doesn't apply spellmods for those spells
    0x40000000: 'DONT_DISPLAY_RANGE', // 30 client doesn't display range in tooltip for those spells
};

const spellAttributes4 = {
    0x00000001: 'IGNORE_RESISTANCES', //  0 spells with this attribute will completely ignore the target's resistance (these spells can't be resisted)
    0x00000002: 'PROC_ONLY_ON_CASTER', //  1 proc only on effects with TARGET_UNIT_CASTER?
    0x00000040: 'NOT_STEALABLE', //  6 although such auras might be dispellable, they cannot be stolen
    0x00000080: 'CAN_CAST_WHILE_CASTING', //  7 Can be cast while another cast is in progress - see CanCastWhileCasting(SpellRec const*,CGUnit_C *,int &)
    0x00000100: 'FIXED_DAMAGE', //  8 Ignores resilience and any (except mechanic related) damage or % damage taken auras on target.
    0x00000200: 'TRIGGER_ACTIVATE', //  9 initially disabled / trigger activate from event (Execute, Riposte, Deep Freeze end other)
    0x00000400: 'SPELL_VS_EXTEND_COST', // 10 Rogue Shiv have this flag
    0x00002000: 'COMBAT_LOG_NO_CASTER', // 13 No caster object is sent to client combat log
    0x00004000: 'DAMAGE_DOESNT_BREAK_AURAS', // 14 doesn't break auras by damage from these spells
    0x00010000: 'NOT_USABLE_IN_ARENA_OR_RATED_BG', // 16 Cannot be used in both Arenas or Rated Battlegrounds
    0x00020000: 'USABLE_IN_ARENA', // 17
    0x00040000: 'AREA_TARGET_CHAIN', // 18 (NYI)hits area targets one after another instead of all at once
    0x00100000: 'NOT_CHECK_SELFCAST_POWER', // 20 supersedes message "More powerful spell applied" for self casts.
    0x02000000: 'IS_PET_SCALING', // 25 pet scaling auras
    0x04000000: 'CAST_ONLY_IN_OUTLAND', // 26 Can only be used in Outland.
};

const spellAttributes5 = {
    0x00000001: 'CAN_CHANNEL_WHEN_MOVING', //  0 available casting channel spell when moving
    0x00000002: 'NO_REAGENT_WHILE_PREP', //  1 not need reagents if UNIT_FLAG_PREPARATION
    0x00000008: 'USABLE_WHILE_STUNNED', //  3 usable while stunned
    0x00000020: 'SINGLE_TARGET_SPELL', //  5 Only one target can be apply at a time
    0x00000200: 'START_PERIODIC_AT_APPLY', //  9 begin periodic tick at aura apply
    0x00000400: 'HIDE_DURATION', // 10 do not send duration to client
    0x00000800: 'ALLOW_TARGET_OF_TARGET_AS_TARGET', // 11 (NYI) uses target's target as target if original target not valid (intervene for example)
    0x00002000: 'HASTE_AFFECT_DURATION', // 13 haste effects decrease duration of this
    0x00020000: 'USABLE_WHILE_FEARED', // 17 usable while feared
    0x00040000: 'USABLE_WHILE_CONFUSED', // 18 usable while confused
    0x00080000: 'DONT_TURN_DURING_CAST', // 19 Blocks caster's turning when casting (client does not automatically turn caster's model to face UNIT_FIELD_TARGET)
    0x08000000: 'DONT_SHOW_AURA_IF_SELF_CAST', // 27 Auras with this attribute are not visible on units that are the caster
    0x10000000: 'DONT_SHOW_AURA_IF_NOT_SELF_CAST', // 28 Auras with this attribute are not visible on units that are not the caster
};

const spellAttributes6 = {
    0x00000001: 'DONT_DISPLAY_COOLDOWN', //  0 client doesn't display cooldown in tooltip for these spells
    0x00000002: 'ONLY_IN_ARENA', //  1 only usable in arena
    0x00000004: 'IGNORE_CASTER_AURAS', //  2
    0x00000008: 'ASSIST_IGNORE_IMMUNE_FLAG', //  3 skips checking UNIT_FLAG_IMMUNE_TO_PC and UNIT_FLAG_IMMUNE_TO_NPC flags on assist
    0x00000040: 'USE_SPELL_CAST_EVENT', //  6 Auras with this attribute trigger SPELL_CAST combat log event instead of SPELL_AURA_START (clientside attribute)
    0x00000100: 'CANT_TARGET_CROWD_CONTROLLED', //  8
    0x00000400: 'CAN_TARGET_POSSESSED_FRIENDS', // 10 NYI!
    0x00000800: 'NOT_IN_RAID_INSTANCE', // 11 not usable in raid instance
    0x00001000: 'CASTABLE_WHILE_ON_VEHICLE', // 12 castable while caster is on vehicle
    0x00002000: 'CAN_TARGET_INVISIBLE', // 13 ignore visibility requirement for spell target (phases, invisibility, etc.)
    0x00040000: 'CAST_BY_CHARMER', // 18 client won't allow to cast these spells when unit is not possessed && charmer of caster will be original caster
    0x00100000: 'ONLY_VISIBLE_TO_CASTER', // 20 Auras with this attribute are only visible to their caster (or pet's owner)
    0x00200000: 'CLIENT_UI_TARGET_EFFECTS', // 21 it's only client-side attribute
    0x01000000: 'CAN_TARGET_UNTARGETABLE', // 24
    0x02000000: 'NOT_RESET_SWING_IF_INSTANT', // 25 Exorcism, Flash of Light
    0x20000000: 'NO_DONE_PCT_DAMAGE_MODS', // 29 ignores done percent damage mods?
    0x80000000: 'IGNORE_CATEGORY_COOLDOWN_MODS'  // 31 Spells with this attribute skip applying modifiers to category cooldowns
};

const spellAttributes7 = {
    0x00000002: 'IGNORE_DURATION_MODS', //  1 Duration is not affected by duration modifiers
    0x00000004: 'REACTIVATE_AT_RESURRECT', //  2 Paladin's auras and 65607 only.
    0x00000008: 'IS_CHEAT_SPELL', //  3 Cannot cast if caster doesn't have UnitFlag2 & UNIT_FLAG2_ALLOW_CHEAT_SPELLS
    0x00000020: 'SUMMON_TOTEM', //  5 Only Shaman totems.
    0x00000040: 'NO_PUSHBACK_ON_DAMAGE', //  6 Does not cause spell pushback on damage
    0x00000100: 'HORDE_ONLY', //  8 Teleports, mounts and other spells.
    0x00000200: 'ALLIANCE_ONLY', //  9 Teleports, mounts and other spells.
    0x00000400: 'DISPEL_CHARGES', // 10 Dispel and Spellsteal individual charges instead of whole aura.
    0x00000800: 'INTERRUPT_ONLY_NONPLAYER', // 11 Only non-player casts interrupt, though Feral Charge - Bear has it.
    0x00001000: 'SILENCE_ONLY_NONPLAYER', // 12 Not set in 3.2.2a.
    0x00010000: 'CAN_RESTORE_SECONDARY_POWER', // 16 These spells can replenish a powertype, which is not the current powertype.
    0x00040000: 'HAS_CHARGE_EFFECT', // 18 Only spells that have Charge among effects.
    0x00080000: 'ZONE_TELEPORT', // 19 Teleports to specific zones.
    0x10000000: 'CONSOLIDATED_RAID_BUFF', // 28 May be collapsed in raid buff frame (clientside attribute)
    0x80000000: 'CLIENT_INDICATOR'
};

const spellAttributes8 = {
    0x00000001: 'CANT_MISS', //  0
    0x00000100: 'AFFECT_PARTY_AND_RAID', //  8 Nearly all spells have "all party and raid" in description
    0x00000200: 'DONT_RESET_PERIODIC_TIMER', //  9 Periodic auras with this flag keep old periodic timer when refreshing at close to one tick remaining (kind of anti DoT clipping)
    0x00000400: 'NAME_CHANGED_DURING_TRANSFORM', // 10 according to wowhead comments, name changes, title remains
    0x00001000: 'AURA_SEND_AMOUNT', // 12 Aura must have flag AFLAG_ANY_EFFECT_AMOUNT_SENT to send amount
    0x00008000: 'WATER_MOUNT', // 15 only one River Boat used in Thousand Needles
    0x00040000: 'REMEMBER_SPELLS', // 18 at some point in time, these auras remember spells and allow to cast them later
    0x00080000: 'USE_COMBO_POINTS_ON_ANY_TARGET', // 19 allows to consume combo points from dead targets
    0x00100000: 'ARMOR_SPECIALIZATION', // 20
    0x00800000: 'BATTLE_RESURRECTION', // 23 Used to limit the Amount of Resurrections in Boss Encounters
    0x01000000: 'HEALING_SPELL', // 24
    0x04000000: 'RAID_MARKER', // 26 probably spell no need learn to cast
    0x10000000: 'NOT_IN_BG_OR_ARENA', // 28 not allow to cast or deactivate currently active effect, not sure about Fast Track
    0x20000000: 'MASTERY_SPECIALIZATION', // 29
    0x80000000: 'ATTACK_IGNORE_IMMUNE_TO_PC_FLAG'  // 31 Do not check UNIT_FLAG_IMMUNE_TO_PC in IsValidAttackTarget
};

const spellAttributes9 = {
    0x00000004: 'RESTRICTED_FLIGHT_AREA', //  2 Dalaran and Wintergrasp flight area auras have it
    0x00000010: 'SPECIAL_DELAY_CALCULATION', //  4
    0x00000020: 'SUMMON_PLAYER_TOTEM', //  5
    0x00000100: 'AIMED_SHOT', //  8
    0x00000200: 'NOT_USABLE_IN_ARENA', //  9 Cannot be used in arenas
    0x00002000: 'SLAM', // 13
    0x00004000: 'USABLE_IN_RATED_BATTLEGROUNDS', // 14 Can be used in Rated Battlegrounds
};

const spellAttributes10 = {
    0x00000010: 'WATER_SPOUT', //  4
    0x00000080: 'TELEPORT_PLAYER', //  7 4 Teleport Player spells
    0x00000800: 'HERB_GATHERING_MINING', // 11 Only Herb Gathering and Mining
    0x00001000: 'USE_SPELL_BASE_LEVEL_FOR_SCALING', // 12
    0x20000000: 'MOUNT_IS_NOT_ACCOUNT_WIDE', // 29 This mount is stored per-character
};

const spellAttributes11 = {
    0x00000004: 'SCALES_WITH_ITEM_LEVEL', //  2
    0x00000020: 'SPELL_ATTR11_ABSORB_ENVIRONMENTAL_DAMAGE', //  5
    0x00000080: 'SPELL_ATTR11_RANK_IGNORES_CASTER_LEVEL', //  7 Spell_C_GetSpellRank returns SpellLevels->MaxLevel * 5 instead of std::min(SpellLevels->MaxLevel, caster->Level) * 5
    0x00010000: 'NOT_USABLE_IN_CHALLENGE_MODE', // 16

};

const spellAttributes12 = {
    0x01000000: 'IS_GARRISON_BUFF', // 24
    0x08000000: 'IS_READINESS_SPELL', // 27
};

const spellAttributes13 = {
    0x00040000: 'ACTIVATES_REQUIRED_SHAPESHIFT', // 18
};

const inventoryTypeMask = {
	0x2: 'Head',
	0x4: 'Neck',
	0x8: 'Shoulder',
	0x10: 'Body',
	0x20: 'Chest',
	0x40: 'Waist',
	0x80: 'Legs',
	0x100: 'Feet',
	0x200: 'Wrist',
	0x400: 'Hand',
	0x800: 'Finger',
	0x1000: 'Trinket',
	0x2000: 'Main Hand',
	0x4000: 'Off Hand',
	0x8000: 'Ranged',
	0x10000: 'Cloak',
	0x20000: '2H Weapon',
	0x40000: 'Bag',
	0x80000: 'Tabard',
	0x100000: 'Robe',
	0x200000: 'Weapon Main Hand',
	0x400000: 'Weapon Off Hand',
	0x800000: 'Holdable',
	0x1000000: 'Ammo',
	0x2000000: 'Thrown',
	0x4000000: 'Ranged Right',
	0x8000000: 'Quiver',
	0x10000000: 'Relic'
}


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

flagMap.set("spellmisc.Attributes[0]", spellAttributes0);
flagMap.set("spellmisc.Attributes[1]", spellAttributes1);
flagMap.set("spellmisc.Attributes[2]", spellAttributes2);
flagMap.set("spellmisc.Attributes[3]", spellAttributes3);
flagMap.set("spellmisc.Attributes[4]", spellAttributes4);
flagMap.set("spellmisc.Attributes[5]", spellAttributes5);
flagMap.set("spellmisc.Attributes[6]", spellAttributes6);
flagMap.set("spellmisc.Attributes[7]", spellAttributes7);
flagMap.set("spellmisc.Attributes[8]", spellAttributes8);
flagMap.set("spellmisc.Attributes[9]", spellAttributes9);
flagMap.set("spellmisc.Attributes[10]", spellAttributes10);
flagMap.set("spellmisc.Attributes[11]", spellAttributes11);
flagMap.set("spellmisc.Attributes[12]", spellAttributes12);
flagMap.set("spellmisc.Attributes[13]", spellAttributes13);

flagMap.set("runeforgelegendaryability.InventoryTypeMask", inventoryTypeMask);
// Conditional flags
let conditionalFlags = new Map();
conditionalFlags.set("chrcustomizationreq.ReqValue",
	[
		['chrcustomizationreq.ReqType=1', classMask],
	]
);