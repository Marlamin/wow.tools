// Enums are currently retrieved from TrinityCore repo, in a best case scenario these would come from DBD..
const reputationLevels = {
    0: 'None/Hated',
    1: 'Hostile',
    2: 'Unfriendly',
    3: 'Neutral',
    4: 'Friendly',
    5: 'Honored',
    6: 'Revered',
    7: 'Exalted',
}

const expansionLevels = {
    0: 'Vanilla',
    1: 'TBC',
    2: 'WotLK',
    3: 'Cata',
    4: 'MoP',
    5: 'WoD',
    6: 'Legion',
    7: 'BfA',
    8: 'Shadowlands',
}

const mapTypes = {
    0: 'Normal',
    1: 'Instance',
    2: 'Raid',
    3: 'BG',
    4: 'Arena',
    5: 'Scenario',
}

const itemBonusTypes = {
    0: 'Unk',
    1: 'ItemLevel',
    2: 'StatModifier',
    3: 'QualityModifier',
    4: 'TitleModifier',
    5: 'NameModifier',
    6: 'Socket',
    7: 'Appearance',
    8: 'RequiredLevel',
    9: 'DisplayToastMethod',
    10: 'RepairCostMultiplier',
    11: 'ScalingStatDistribution',
    12: 'DisenchantLootID',
    13: 'ScalingStatDistributionFixed',
    14: 'ItemLevelCanIncrease',
    15: 'RandomEnchantment',
    16: 'Bonding',
    17: 'RelicType',
    18: 'OverrideRequiredLevel',
    19: 'AzeriteTierUnlockSetID',
    21: 'CanDisenchant',
    22: 'CanScrap',
    23: 'ItemEffectID',
    31: 'LegendaryName'
}

const criteriaTreeOperator = {
    0: 'SINGLE',
    1: 'SINGLE_NOT_COMPLETED',
    4: 'ALL',
    5: 'SUM_CHILDREN',
    6: 'MAX_CHILD',
    7: 'COUNT_DIRECT_CHILDREN',
    8: 'ANY',
    9: 'SUM_CHILDREN_WEIGHT'
}

const criteriaTreeOperatorFriendly = {
    0: 'Requires just one of:',
    1: 'SINGLE_NOT_COMPLETED',
    4: 'Requires all of:',
    5: 'Sum of:',
    6: 'MAX_CHILD',
    7: 'COUNT_DIRECT_CHILDREN',
    8: 'Requires any of:',
    9: 'SUM_CHILDREN_WEIGHT'
}

const modifierTreeOperator = {
    2: 'SingleTrue',
    3: 'SingleFalse',
    4: 'All',
    8: 'Some'
};

const criteriaAdditionalCondition = {
    0: 'NONE',
    1: 'SOURCE_DRUNK_VALUE',
    2: 'SOURCE_PLAYER_CONDITION',
    3: 'ITEM_LEVEL',
    4: 'TARGET_CREATURE_ENTRY',
    5: 'TARGET_MUST_BE_PLAYER',
    6: 'TARGET_MUST_BE_DEAD',
    7: 'TARGET_MUST_BE_ENEMY',
    8: 'SOURCE_HAS_AURA',
    9: 'SOURCE_HAS_AURA_TYPE',
    10: 'TARGET_HAS_AURA',
    11: 'TARGET_HAS_AURA_TYPE',
    12: 'SOURCE_AURA_STATE',
    13: 'TARGET_AURA_STATE',
    14: 'ITEM_QUALITY_MIN',
    15: 'ITEM_QUALITY_EQUALS',
    16: 'SOURCE_IS_ALIVE',
    17: 'SOURCE_AREA_OR_ZONE',
    18: 'TARGET_AREA_OR_ZONE',
    19: 'UNK_19',
    20: 'MAP_DIFFICULTY_OLD',
    21: 'TARGET_CREATURE_YIELDS_XP',
    22: 'SOURCE_LEVEL_ABOVE_TARGET',
    23: 'SOURCE_LEVEL_EQUAL_TARGET',
    24: 'ARENA_TYPE',
    25: 'SOURCE_RACE',
    26: 'SOURCE_CLASS',
    27: 'TARGET_RACE',
    28: 'TARGET_CLASS',
    29: 'MAX_GROUP_MEMBERS',
    30: 'TARGET_CREATURE_TYPE',
    31: 'TARGET_CREATURE_FAMILY',
    32: 'SOURCE_MAP',
    33: 'CLIENT_VERSION',
    34: 'BATTLE_PET_TEAM_LEVEL',
    35: 'NOT_IN_GROUP',
    36: 'IN_GROUP',
    37: 'MIN_PERSONAL_RATING',
    38: 'TITLE_BIT_INDEX',
    39: 'SOURCE_LEVEL',
    40: 'TARGET_LEVEL',
    41: 'SOURCE_ZONE',
    42: 'TARGET_ZONE',
    43: 'SOURCE_HEALTH_PCT_LOWER',
    44: 'SOURCE_HEALTH_PCT_GREATER',
    45: 'SOURCE_HEALTH_PCT_EQUAL',
    46: 'TARGET_HEALTH_PCT_LOWER',
    47: 'TARGET_HEALTH_PCT_GREATER',
    48: 'TARGET_HEALTH_PCT_EQUAL',
    49: 'SOURCE_HEALTH_LOWER',
    50: 'SOURCE_HEALTH_GREATER',
    51: 'SOURCE_HEALTH_EQUAL',
    52: 'TARGET_HEALTH_LOWER',
    53: 'TARGET_HEALTH_GREATER',
    54: 'TARGET_HEALTH_EQUAL',
    55: 'TARGET_PLAYER_CONDITION',
    56: 'MIN_ACHIEVEMENT_POINTS',
    57: 'IN_LFG_DUNGEON',
    58: 'IN_LFG_RANDOM_DUNGEON',
    59: 'IN_LFG_FIRST_RANDOM_DUNGEON',
    60: 'UNK_60',
    61: 'REQUIRES_GUILD_GROUP',
    62: 'GUILD_REPUTATION',
    63: 'RATED_BATTLEGROUND',
    64: 'RATED_BATTLEGROUND_RATING',
    65: 'PROJECT_RARITY',
    66: 'PROJECT_RACE',
    67: 'WORLD_STATE_EXPRESSION',
    68: 'MAP_DIFFICULTY',
    69: 'SOURCE_LEVEL_GREATER',
    70: 'TARGET_LEVEL_GREATER',
    71: 'SOURCE_LEVEL_LOWER',
    72: 'TARGET_LEVEL_LOWER',
    73: 'MODIFIER_TREE',
    74: 'SCENARIO_ID',
    75: 'THE_TILLERS_REPUTATION',
    76: 'PET_BATTLE_ACHIEVEMENT_POINTS',
    77: 'UNK_77',
    78: 'BATTLE_PET_FAMILY',
    79: 'BATTLE_PET_HEALTH_PCT',
    80: 'GUILD_GROUP_MEMBERS',
    81: 'BATTLE_PET_ENTRY',
    82: 'SCENARIO_STEP_INDEX',
    83: 'CHALLENGE_MODE_MEDAL',
    84: 'IS_ON_QUEST',
    85: 'EXALTED_WITH_FACTION',
    86: 'HAS_ACHIEVEMENT',
    87: 'HAS_ACHIEVEMENT_ON_CHARACTER',
    88: 'CLOUD_SERPENT_REPUTATION',
    89: 'BATTLE_PET_BREED_QUALITY_ID',
    90: 'PET_BATTLE_IS_PVP',
    91: 'BATTLE_PET_SPECIES',
    92: 'ACTIVE_EXPANSION',
    93: 'UNK_93',
    94: 'FRIENDSHIP_REP_REACTION',
    95: 'FACTION_STANDING',
    96: 'ITEM_CLASS_AND_SUBCLASS',
    97: 'SOURCE_SEX',
    98: 'SOURCE_NATIVE_SEX',
    99: 'SKILL',
    100: 'UNK_100',
    101: 'NORMAL_PHASE_SHIFT',
    102: 'IN_PHASE',
    103: 'NOT_IN_PHASE',
    104: 'HAS_SPELL',
    105: 'ITEM_COUNT',
    106: 'ACCOUNT_EXPANSION',
    107: 'SOURCE_HAS_AURA_LABEL',
    108: 'UNK_108',
    109: 'TIME_IN_RANGE',
    110: 'REWARDED_QUEST',
    111: 'COMPLETED_QUEST',
    112: 'COMPLETED_QUEST_OBJECTIVE',
    113: 'EXPLORED_AREA',
    114: 'ITEM_COUNT_INCLUDING_BANK',
    115: 'UNK_115',
    116: 'SOURCE_PVP_FACTION_INDEX',
    117: 'LFG_VALUE_EQUAL',
    118: 'LFG_VALUE_GREATER',
    119: 'CURRENCY_AMOUNT',
    120: 'UNK_120',
    121: 'CURRENCY_TRACKED_AMOUNT',
    122: 'MAP_INSTANCE_TYPE',
    123: 'MENTOR',
    124: 'UNK_124',
    125: 'UNK_125',
    126: 'GARRISON_LEVEL_ABOVE',
    127: 'GARRISON_FOLLOWERS_ABOVE_LEVEL',
    128: 'GARRISON_FOLLOWERS_ABOVE_QUALITY',
    129: 'GARRISON_FOLLOWER_ABOVE_LEVEL_WITH_ABILITY',
    130: 'GARRISON_FOLLOWER_ABOVE_LEVEL_WITH_TRAIT',
    131: 'GARRISON_FOLLOWER_WITH_ABILITY_IN_BUILDING',
    132: 'GARRISON_FOLLOWER_WITH_TRAIT_IN_BUILDING',
    133: 'GARRISON_FOLLOWER_ABOVE_LEVEL_IN_BUILDING',
    134: 'GARRISON_BUILDING_ABOVE_LEVEL',
    135: 'GARRISON_BLUEPRINT',
    136: 'UNK_136',
    137: 'UNK_137',
    138: 'UNK_138',
    139: 'UNK_139',
    140: 'GARRISON_BUILDING_INACTIVE',
    141: 'UNK_141',
    142: 'GARRISON_BUILDING_EQUAL_LEVEL',
    143: 'GARRISON_FOLLOWER_WITH_ABILITY',
    144: 'GARRISON_FOLLOWER_WITH_TRAIT',
    145: 'GARRISON_FOLLOWER_ABOVE_QUALITY_WOD',
    146: 'GARRISON_FOLLOWER_EQUAL_LEVEL',
    147: 'GARRISON_RARE_MISSION',
    148: 'UNK_148',
    149: 'GARRISON_BUILDING_LEVEL',
    150: 'UNK_150',
    151: 'BATTLE_PET_SPECIES_IN_TEAM',
    152: 'BATTLE_PET_FAMILY_IN_TEAM',
    153: 'UNK_153',
    154: 'UNK_154',
    155: 'UNK_155',
    156: 'UNK_156',
    157: 'GARRISON_FOLLOWER_ID',
    158: 'QUEST_OBJECTIVE_PROGRESS_EQUAL',
    159: 'QUEST_OBJECTIVE_PROGRESS_ABOVE',
    160: 'UNK_160',
    161: 'UNK_161',
    162: 'UNK_162',
    163: 'UNK_163',
    164: 'UNK_164',
    165: 'UNK_165',
    166: 'UNK_166',
    167: 'GARRISON_MISSION_TYPE',
    168: 'GARRISON_FOLLOWER_ABOVE_ITEM_LEVEL',
    169: 'GARRISON_FOLLOWERS_ABOVE_ITEM_LEVEL',
    170: 'GARRISON_LEVEL_EQUAL',
    171: 'GARRISON_GROUP_SIZE',
    172: 'UNK_172',
    173: 'TARGETING_CORPSE',
    174: 'UNK_174',
    175: 'GARRISON_FOLLOWERS_LEVEL_EQUAL',
    176: 'GARRISON_FOLLOWER_ID_IN_BUILDING',
    177: 'UNK_177',
    178: 'UNK_178',
    179: 'WORLD_PVP_AREA',
    180: 'NON_OWN_GARRISON',
    181: 'UNK_181',
    182: 'UNK_182',
    183: 'UNK_183',
    184: 'GARRISON_FOLLOWERS_ITEM_LEVEL_ABOVE',
    185: 'UNK_185',
    186: 'UNK_186',
    187: 'GARRISON_FOLLOWER_TYPE',
    188: 'UNK_188',
    189: 'UNK_189',
    190: 'UNK_190',
    191: 'UNK_191',
    192: 'UNK_192',
    193: 'HONOR_LEVEL',
    194: 'PRESTIGE_LEVEL',
    195: 'UNK_195',
    196: 'UNK_196',
    197: 'UNK_197',
    198: 'UNK_198',
    199: 'UNK_198',
    200: 'ITEM_MODIFIED_APPEARANCE',
    201: 'GARRISON_SELECTED_TALENT',
    202: 'GARRISON_RESEARCHED_TALENT',
    203: 'HAS_CHARACTER_RESTRICTIONS',
    204: 'UNK_204',
    205: 'UNK_205',
    206: 'QUEST_INFO_ID',
    207: 'GARRISON_RESEARCHING_TALENT',
    208: 'ARTIFACT_APPEARANCE_SET_USED',
    209: 'CURRENCY_AMOUNT_EQUAL',
    210: 'UNK_210',
    211: 'SCENARIO_TYPE',
    212: 'ACCOUNT_EXPANSION_EQUAL',
    213: 'UNK_213',
    214: 'UNK_214',
    215: 'UNK_215',
    216: 'CHALLENGE_MODE_MEDAL_2',
    217: 'UNK_217',
    218: 'UNK_218',
    219: 'UNK_219',
    220: 'UNK_220',
    221: 'UNK_221',
    222: 'UNK_222',
    223: 'UNK_223',
    224: 'UNK_224',
    225: 'UNK_225',
    226: 'USED_LEVEL_BOOST',
    227: 'USED_RACE_CHANGE',
    228: 'USED_FACTION_CHANGE',
    229: 'UNK_229',
    230: 'UNK_230',
    231: 'ACHIEVEMENT_GLOBALLY_INCOMPLETED',
    232: 'MAIN_HAND_VISIBLE_SUBCLASS',
    233: 'OFF_HAND_VISIBLE_SUBCLASS',
    234: 'PVP_TIER',
    235: 'AZERITE_ITEM_LEVEL',
    236: 'UNK_236',
    237: 'UNK_237',
    238: 'UNK_238',
    239: 'PVP_TIER_GREATER',
    240: 'UNK_240',
    241: 'UNK_241',
    242: 'UNK_242',
    243: 'UNK_243',
    244: 'UNK_244',
    245: 'IN_WAR_MODE',
    246: 'UNK_246',
    247: 'KEYSTONE_LEVEL',
    248: 'UNK_248',
    249: 'KEYSTONE_DUNGEON',
    250: 'UNK_250',
    251: 'PVP_SEASON',
    252: 'SOURCE_DISPLAY_RACE',
    253: 'TARGET_DISPLAY_RACE',
    254: 'FRIENDSHIP_REP_REACTION_EXACT',
    255: 'SOURCE_AURA_COUNT_EQUAL',
    256: 'TARGET_AURA_COUNT_EQUAL',
    257: 'SOURCE_AURA_COUNT_GREATER',
    258: 'TARGET_AURA_COUNT_GREATER',
    259: 'UNLOCKED_AZERITE_ESSENCE_RANK_LOWER',
    260: 'UNLOCKED_AZERITE_ESSENCE_RANK_EQUAL',
    261: 'UNLOCKED_AZERITE_ESSENCE_RANK_GREATER',
    262: 'SOURCE_HAS_AURA_EFFECT_INDEX',
    263: 'SOURCE_SPECIALIZATION_ROLE',
    264: 'SOURCE_LEVEL_120',
    265: 'UNK_265',
    266: 'SELECTED_AZERITE_ESSENCE_RANK_LOWER',
    267: 'SELECTED_AZERITE_ESSENCE_RANK_GREATER',
    268: 'CONTENT_TUNING',
    269: 'UNK_269',
    270: 'UNK_270',
    271: 'UNK_271',
    272: 'CONTENT_TUNING_2',
    273: 'UNK_273',
    274: 'UNK_274',
    275: 'UNK_275',
    276: 'UNK_276',
    277: 'UNK_277',
    278: 'UNK_278',
    279: 'UNK_279',
    280: 'MAP_OR_COSMETIC_MAP',
    281: 'UNK_281',
    282: 'HAS_ENTITLEMENT',
    283: 'HAS_QUEST_SESSION',
    284: 'UNK_284',
    285: 'UNK_285',
    286: 'UNK_286',
    287: 'UNK_287',
    288: 'UNK_288',
    289: 'UNK_289',
    290: 'UNK_290',
    291: 'UNK_291',
    298: 'UNK_298',
    299: 'UNK_299',
    300: 'UNK_300',
    301: 'UNK_301',
    302: 'UNK_302',
    303: 'RUNEFORGED_LEGENDARY_ABILITY',
    305: 'UNK_305', // Related to magic mask in ConditionalContentTuning
    306: 'ACHIEVEMENT_UNK',
    307: 'UNK_307',
    308: 'UNK_308'
};

const itemStatType = {
    0: 'MANA',
    1: 'HEALTH',
    2: 'ENDURANCE',
    3: 'AGILITY',
    4: 'STRENGTH',
    5: 'INTELLECT',
    6: 'SPIRIT_UNUSED',	// Removed in 7.3.0
    7: 'STAMINA',
    8: 'ENERGY',
    9: 'RAGE',
    10: 'FOCUS',
    11: 'WEAPON_SKILL_RATING_OBSOLETE',
    12: 'DEFENSE_SKILL_RATING',
    13: 'DODGE_RATING',
    14: 'PARRY_RATING',
    15: 'BLOCK_RATING',
    16: 'HIT_MELEE_RATING',
    17: 'HIT_RANGED_RATING',
    18: 'HIT_SPELL_RATING',
    19: 'CRIT_MELEE_RATING',
    20: 'CRIT_RANGED_RATING',
    21: 'CRIT_SPELL_RATING',
    22: 'CORRUPTION',
    23: 'CORRUPTION_RESISTANCE',
    24: 'MODIFIED_CRAFTING_STAT_1',
    25: 'MODIFIED_CRAFTING_STAT_2',
    26: 'CRIT_TAKEN_RANGED_RATING', // Removed
    27: 'CRIT_TAKEN_SPELL_RATING', // Removed
    28: 'HASTE_MELEE_RATING',	// Removed
    29: 'HASTE_RANGED_RATING',	// Removed
    30: 'HASTE_SPELL_RATING',	// Removed
    31: 'HIT_RATING',
    32: 'CRIT_RATING',
    33: 'HIT_TAKEN_RATING', // Removed
    34: 'CRIT_TAKEN_RATING', // Removed
    35: 'RESILIENCE_RATING',
    36: 'HASTE_RATING',
    37: 'EXPERTISE_RATING',
    38: 'ATTACK_POWER',
    39: 'RANGED_ATTACK_POWER',
    40: 'VERSATILITY',
    41: 'SPELL_HEALING_DONE',
    42: 'SPELL_DAMAGE_DONE',
    43: 'MANA_REGENERATION', // Removed
    44: 'ARMOR_PENETRATION_RATING', // Removed
    45: 'SPELL_POWER',
    46: 'HEALTH_REGEN',
    47: 'SPELL_PENETRATION',
    48: 'BLOCK_VALUE', // Removed
    49: 'MASTERY_RATING',
    50: 'EXTRA_ARMOR',
    51: 'FIRE_RESISTANCE',
    52: 'FROST_RESISTANCE',
    53: 'HOLY_RESISTANCE',
    54: 'SHADOW_RESISTANCE',
    55: 'NATURE_RESISTANCE',
    56: 'ARCANE_RESISTANCE',
    57: 'PVP_POWER',
    58: 'CR_AMPLIFY',		// Deprecated
    59: 'CR_MULTISTRIKE',	// Deprecated
    60: 'CR_READINESS',		// Deprecated
    61: 'CR_SPEED',
    62: 'CR_LIFESTEAL',
    63: 'CR_AVOIDANCE',
    64: 'CR_STURDINESS',
    // 65: 'CR_UNUSED_7',
    // 66: 'CR_CLEAVE',
    // 67: 'CR_UNUSED_9',
    // 68: 'CR_UNUSED_10',
    // 69: 'CR_UNUSED_11',
    // 70: 'CR_UNUSED_12',
    71: 'AGI_STR_INT',
    72: 'AGI_STR',
    73: 'AGI_INT',
    74: 'STR_INT'
};

const itemPrettyStatType = {
    0: 'Mana',
    1: 'Health',
    3: 'Agility',
    4: 'Strength',
    5: 'Intellect',
    6: 'Spirit',
    7: 'Stamina',
    12: 'Defense',
    13: 'Dodge',
    14: 'Parry',
    15: 'Block',
    16: 'Hit (Melee)',
    17: 'Hit (Ranged)',
    18: 'Hit (Spell)',
    19: 'Crit (Melee)',
    20: 'Crit (Ranged)',
    21: 'Crit (Spell)',
    22: 'Corruption',
    23: 'Corruption Resistance',
    24: 'Random Stat 1',
    25: 'Random Stat 2',
    26: 'Critical Strike Avoidance (Ranged)',
    27: 'Critical Strike Avoidance (Spell)',
    28: 'Haste (Melee)',
    29: 'Haste (Ranged)',
    30: 'Haste (Spell)',
    31: 'Hit',
    32: 'Critical Strike',
    33: 'Hit Avoidance',
    34: 'Critical Strike Avoidance',
    35: 'Resilience',
    36: 'Haste',
    37: 'Expertise',
    38: 'Attack Power',
    39: 'Attack Power (Ranged)',
    40: 'Versatility',
    41: 'Bonus Healing',
    42: 'Bonus Damage',
    43: 'Mana Regeneration',
    44: 'Armor Penetration',
    45: 'Spell Power',
    46: 'Health Regen',
    47: 'Spell Penetration',
    48: 'Block',
    49: 'Mastery',
    50: 'Bonus Armor',
    51: 'Fire Resistance',
    52: 'Frost Resistance',
    53: 'Holy Resistance',
    54: 'Shadow Resistance',
    55: 'Nature Resistance',
    56: 'Arcane Resistance',
    57: 'PvP Power',
    58: 'Amplify',
    59: 'Multistrike',
    60: 'Readiness',
    61: 'Speed',
    62: 'Lifesteal',
    63: 'Avoidance',
    64: 'Sturdiness',
    65: 'Unused (7)',
    66: 'Cleave',
    67: 'Versatility',
    68: 'Unused (10)',
    69: 'Unused (11)',
    70: 'Unused (12)',
    71: 'Agility | Strength | Intellect',
    72: 'Agility | Strength',
    73: 'Agility | Intellect',
    74: 'Strength | Intellect'
};

const spellEffectName = {
    1: 'INSTAKILL',
    2: 'SCHOOL_DAMAGE',
    3: 'DUMMY',
    4: 'PORTAL_TELEPORT',
    5: 'TELEPORT_UNITS_OLD',
    6: 'APPLY_AURA',
    7: 'ENVIRONMENTAL_DAMAGE',
    8: 'POWER_DRAIN',
    9: 'HEALTH_LEECH',
    10: 'HEAL',
    11: 'BIND',
    12: 'PORTAL',
    13: 'RITUAL_BASE',
    14: 'INCREASE_CURRENCY_CAP',
    15: 'RITUAL_ACTIVATE_PORTAL',
    16: 'QUEST_COMPLETE',
    17: 'WEAPON_DAMAGE_NOSCHOOL',
    18: 'RESURRECT',
    19: 'ADD_EXTRA_ATTACKS',
    20: 'DODGE',
    21: 'EVADE',
    22: 'PARRY',
    23: 'BLOCK',
    24: 'CREATE_ITEM',
    25: 'WEAPON',
    26: 'DEFENSE',
    27: 'PERSISTENT_AREA_AURA',
    28: 'SUMMON',
    29: 'LEAP',
    30: 'ENERGIZE',
    31: 'WEAPON_PERCENT_DAMAGE',
    32: 'TRIGGER_MISSILE',
    33: 'OPEN_LOCK',
    34: 'SUMMON_CHANGE_ITEM',
    35: 'APPLY_AREA_AURA_PARTY',
    36: 'LEARN_SPELL',
    37: 'SPELL_DEFENSE',
    38: 'DISPEL',
    39: 'LANGUAGE',
    40: 'DUAL_WIELD',
    41: 'JUMP',
    42: 'JUMP_DEST',
    43: 'TELEPORT_UNITS_FACE_CASTER',
    44: 'SKILL_STEP',
    45: 'PLAY_MOVIE',
    46: 'SPAWN',
    47: 'TRADE_SKILL',
    48: 'STEALTH',
    49: 'DETECT',
    50: 'TRANS_DOOR',
    51: 'FORCE_CRITICAL_HIT',
    52: 'SET_MAX_BATTLE_PET_COUNT',
    53: 'ENCHANT_ITEM',
    54: 'ENCHANT_ITEM_TEMPORARY',
    55: 'TAMECREATURE',
    56: 'SUMMON_PET',
    57: 'LEARN_PET_SPELL',
    58: 'WEAPON_DAMAGE',
    59: 'CREATE_RANDOM_ITEM',
    60: 'PROFICIENCY',
    61: 'SEND_EVENT',
    62: 'POWER_BURN',
    63: 'THREAT',
    64: 'TRIGGER_SPELL',
    65: 'APPLY_AREA_AURA_RAID',
    66: 'RECHARGE_ITEM',
    67: 'HEAL_MAX_HEALTH',
    68: 'INTERRUPT_CAST',
    69: 'DISTRACT',
    70: 'PULL',
    71: 'PICKPOCKET',
    72: 'ADD_FARSIGHT',
    73: 'UNTRAIN_TALENTS',
    74: 'APPLY_GLYPH',
    75: 'HEAL_MECHANICAL',
    76: 'SUMMON_OBJECT_WILD',
    77: 'SCRIPT_EFFECT',
    78: 'ATTACK',
    79: 'SANCTUARY',
    80: 'ADD_COMBO_POINTS',
    81: 'PUSH_ABILITY_TO_ACTION_BAR',
    82: 'BIND_SIGHT',
    83: 'DUEL',
    84: 'STUCK',
    85: 'SUMMON_PLAYER',
    86: 'ACTIVATE_OBJECT',
    87: 'GAMEOBJECT_DAMAGE',
    88: 'GAMEOBJECT_REPAIR',
    89: 'GAMEOBJECT_SET_DESTRUCTION_STATE',
    90: 'KILL_CREDIT',
    91: 'THREAT_ALL',
    92: 'ENCHANT_HELD_ITEM',
    93: 'FORCE_DESELECT',
    94: 'SELF_RESURRECT',
    95: 'SKINNING',
    96: 'CHARGE',
    97: 'CAST_BUTTON',
    98: 'KNOCK_BACK',
    99: 'DISENCHANT',
    100: 'INEBRIATE',
    101: 'FEED_PET',
    102: 'DISMISS_PET',
    103: 'REPUTATION',
    104: 'SUMMON_OBJECT_SLOT1',
    105: 'SURVEY',
    106: 'CHANGE_RAID_MARKER',
    107: 'SHOW_CORPSE_LOOT',
    108: 'DISPEL_MECHANIC',
    109: 'RESURRECT_PET',
    110: 'DESTROY_ALL_TOTEMS',
    111: 'DURABILITY_DAMAGE',
    114: 'ATTACK_ME',
    115: 'DURABILITY_DAMAGE_PCT',
    116: 'SKIN_PLAYER_CORPSE',
    117: 'SPIRIT_HEAL',
    118: 'SKILL',
    119: 'APPLY_AREA_AURA_PET',
    120: 'TELEPORT_GRAVEYARD',
    121: 'NORMALIZED_WEAPON_DMG',
    123: 'SEND_TAXI',
    124: 'PULL_TOWARDS',
    125: 'MODIFY_THREAT_PERCENT',
    126: 'STEAL_BENEFICIAL_BUFF',
    127: 'PROSPECTING',
    128: 'APPLY_AREA_AURA_FRIEND',
    129: 'APPLY_AREA_AURA_ENEMY',
    130: 'REDIRECT_THREAT',
    131: 'PLAY_SOUND',
    132: 'PLAY_MUSIC',
    133: 'UNLEARN_SPECIALIZATION',
    134: 'KILL_CREDIT2',
    135: 'CALL_PET',
    136: 'HEAL_PCT',
    137: 'ENERGIZE_PCT',
    138: 'LEAP_BACK',
    139: 'CLEAR_QUEST',
    140: 'FORCE_CAST',
    141: 'FORCE_CAST_WITH_VALUE',
    142: 'TRIGGER_SPELL_WITH_VALUE',
    143: 'APPLY_AREA_AURA_OWNER',
    144: 'KNOCK_BACK_DEST',
    145: 'PULL_TOWARDS_DEST',
    146: 'ACTIVATE_RUNE',
    147: 'QUEST_FAIL',
    148: 'TRIGGER_MISSILE_SPELL_WITH_VALUE',
    149: 'CHARGE_DEST',
    150: 'QUEST_START',
    151: 'TRIGGER_SPELL_2',
    152: 'SUMMON_RAF_FRIEND',
    153: 'CREATE_TAMED_PET',
    154: 'DISCOVER_TAXI',
    155: 'TITAN_GRIP',
    156: 'ENCHANT_ITEM_PRISMATIC',
    157: 'CREATE_LOOT',
    158: 'MILLING',
    159: 'ALLOW_RENAME_PET',
    160: 'FORCE_CAST_2',
    161: 'TALENT_SPEC_COUNT',
    162: 'TALENT_SPEC_SELECT',
    163: 'OBLITERATE_ITEM',
    164: 'REMOVE_AURA',
    165: 'DAMAGE_FROM_MAX_HEALTH_PCT',
    166: 'GIVE_CURRENCY',
    167: 'UPDATE_PLAYER_PHASE',
    168: 'ALLOW_CONTROL_PET',
    169: 'DESTROY_ITEM',
    170: 'UPDATE_ZONE_AURAS_AND_PHASES',
    172: 'RESURRECT_WITH_AURA',
    173: 'UNLOCK_GUILD_VAULT_TAB',
    174: 'APPLY_AURA_ON_PET',
    176: 'SANCTUARY_2',
    179: 'CREATE_AREATRIGGER',
    180: 'UPDATE_AREATRIGGER',
    181: 'REMOVE_TALENT',
    182: 'DESPAWN_AREATRIGGER',
    184: 'REPUTATION_2',
    187: 'RANDOMIZE_ARCHAEOLOGY_DIGSITES',
    189: 'LOOT',
    191: 'TELEPORT_TO_DIGSITE',
    192: 'UNCAGE_BATTLEPET',
    193: 'START_PET_BATTLE',
    198: 'PLAY_SCENE',
    200: 'HEAL_BATTLEPET_PCT',
    201: 'ENABLE_BATTLE_PETS',
    202: 'APPLY_AURA_ON_?',
    204: 'CHANGE_BATTLEPET_QUALITY',
    205: 'LAUNCH_QUEST_CHOICE',
    206: 'ALTER_ITEM',
    207: 'LAUNCH_QUEST_TASK',
    210: 'LEARN_GARRISON_BUILDING',
    211: 'LEARN_GARRISON_SPECIALIZATION',
    214: 'CREATE_GARRISON',
    215: 'UPGRADE_CHARACTER_SPELLS',
    216: 'CREATE_SHIPMENT',
    217: 'UPGRADE_GARRISON',
    219: 'CREATE_CONVERSATION',
    220: 'ADD_GARRISON_FOLLOWER',
    222: 'CREATE_HEIRLOOM_ITEM',
    223: 'CHANGE_ITEM_BONUSES',
    224: 'ACTIVATE_GARRISON_BUILDING',
    225: 'GRANT_BATTLEPET_LEVEL',
    227: 'TELEPORT_TO_LFG_DUNGEON',
    229: 'SET_FOLLOWER_QUALITY',
    230: 'INCREASE_FOLLOWER_ITEM_LEVEL',
    231: 'INCREASE_FOLLOWER_EXPERIENCE',
    232: 'REMOVE_PHASE',
    233: 'RANDOMIZE_FOLLOWER_ABILITIES',
    236: 'GIVE_EXPERIENCE',
    237: 'GIVE_RESTED_EXPERIENCE_BONUS',
    238: 'INCREASE_SKILL',
    239: 'END_GARRISON_BUILDING_CONSTRUCTION',
    240: 'GIVE_ARTIFACT_POWER',
    242: 'GIVE_ARTIFACT_POWER_NO_BONUS',
    243: 'APPLY_ENCHANT_ILLUSION',
    244: 'LEARN_FOLLOWER_ABILITY',
    245: 'UPGRADE_HEIRLOOM',
    246: 'FINISH_GARRISON_MISSION',
    247: 'ADD_GARRISON_MISSION',
    248: 'FINISH_SHIPMENT',
    249: 'FORCE_EQUIP_ITEM',
    250: 'TAKE_SCREENSHOT',
    251: 'SET_GARRISON_CACHE_SIZE',
    252: 'TELEPORT_UNITS',
    253: 'GIVE_HONOR',
    255: 'LEARN_TRANSMOG_SET',
    258: 'MODIFY_KEYSTONE',
    259: 'RESPEC_AZERITE_EMPOWERED_ITEM',
    260: 'SUMMON_STABLED_PET',
    261: 'SCRAP_ITEM',
    263: 'REPAIR_ITEM',
    264: 'REMOVE_GEM',
    265: 'LEARN_AZERITE_ESSENCE_POWER',
    268: 'APPLY_MOUNT_EQUIPMENT',
    269: 'UPGRADE_ITEM',
    271: 'APPLY_AREA_AURA_PARTY_NONRANDOM',
    // 272: '',
    // 273: '',
    276: 'ITEM_SPELL_SOMETHING',
    279: 'COVENANT_GARR_TALENT',
    // 281: '',
    // 283: '',
};

const soundkitSoundType = {
    0: 'Unused/Miscellaneous',
    1: 'Spells',
    2: 'UI',
    3: 'Footsteps',
    4: 'Weapons/Impact',
    6: 'Weapons/Miss',
    7: 'Greetings',
    8: 'Casting',
    9: 'Pick Up/Put Down',
    10: 'NPC Combat',
    12: 'Errors',
    13: 'Ambient FX',
    14: 'Objects',
    16: 'Death',
    17: 'NPC Greetings',
    18: 'Test/Temporary',
    19: 'Armor/Foley',
    20: 'Footsteps',
    21: 'Water/Character',
    22: 'Water/Liquid',
    23: 'Tradeskills',
    24: 'Misc. FX',
    25: 'Doodads',
    26: 'Spell Fizzle',
    27: 'NPC Loops',
    28: 'Zone Music',
    29: 'Emotes',
    30: 'Narration Music',
    31: 'Narration',
    50: 'Zone Ambience',
    52: 'Zone Emitters',
    53: 'Vehicle'
};

const charSectionType = {
    0: 'Skin',
    1: 'Face',
    2: 'FacialHair',
    3: 'Hair',
    4: 'Underwear',
    5: 'HDSkin',
    6: 'HDFace',
    7: 'HDFacialHair',
    8: 'HDHair',
    9: 'HDUnderwear',
    10: 'Custom1',
    11: 'HDCustom1',
    12: 'Custom2',
    13: 'HDCustom2',
    14: 'Custom3',
    15: 'HDCustom3'
}

const charSex = {
    0: 'Male',
    1: 'Female'
}

const uiMapType = {
    0: 'Cosmic',
    1: 'World',
    2: 'Continent',
    3: 'Zone',
    4: 'Dungeon',
    5: 'Micro',
    6: 'Orphan'
}

const criteriaType = {
    0: 'KILL_CREATURE',								// creature::ID
    1: 'WIN_BG',									// map::ID
    2: 'UNK_2',										// researchproject::ID
    3: 'COMPLETE_ARCHAEOLOGY_PROJECTS',				// No FK
    4: 'SURVEY_GAMEOBJECT',							// gameobjects::ID
    5: 'REACH_LEVEL',								// No FK
    6: 'CLEAR_DIGSITE',								// No FK
    7: 'REACH_SKILL_LEVEL',							// skillline::ID
    8: 'COMPLETE_ACHIEVEMENT',						// achievement::ID
    9: 'COMPLETE_QUEST_COUNT',						// No FK
    10: 'COMPLETE_DAILY_QUEST_DAILY',				// No FK
    11: 'COMPLETE_QUESTS_IN_ZONE',					// areatable::ID
    12: 'CURRENCY',									// currencytypes::ID
    13: 'DAMAGE_DONE',								// No FK
    14: 'COMPLETE_DAILY_QUEST',						// No FK
    15: 'COMPLETE_BATTLEGROUND',					// map::ID
    16: 'DEATH_AT_MAP',								// map::ID
    17: 'DEATH',									// No FK
    18: 'DEATH_IN_DUNGEON',							// ????
    19: 'COMPLETE_RAID',							// ????
    20: 'KILLED_BY_CREATURE',						// creature::ID
    21: 'MANUAL_COMPLETE_CRITERIA',					// criteria::ID
    22: 'COMPLETE_CHALLENGE_MODE_GUILD',			// No FK
    23: 'KILLED_BY_PLAYER',							// No FK
    24: 'FALL_WITHOUT_DYING',						// No FK
    26: 'DEATHS_FROM',								// ????
    27: 'COMPLETE_QUEST',							// questv2:ID
    28: 'BE_SPELL_TARGET',							// spell::ID
    29: 'CAST_SPELL',								// spell::ID
    30: 'BG_OBJECTIVE_CAPTURE',						// pvpstat::ID
    31: 'HONORABLE_KILL_AT_AREA',					// areatable::ID
    32: 'WIN_ARENA',								// map::ID
    33: 'PLAY_ARENA',								// map::ID
    34: 'LEARN_SPELL',								// spell::ID
    35: 'HONORABLE_KILL',							// No FK
    36: 'OWN_ITEM',									// item::ID
    37: 'WIN_RATED_ARENA',							// No FK
    38: 'HIGHEST_TEAM_RATING',						// No FK
    39: 'HIGHEST_PERSONAL_RATING',					// No FK
    40: 'LEARN_SKILL_LEVEL',						// skilline::ID
    41: 'USE_ITEM',									// item::ID
    42: 'LOOT_ITEM',								// item::ID
    43: 'EXPLORE_AREA',								// areatable::ID
    44: 'OWN_RANK',									// No FK
    45: 'BUY_BANK_SLOT',							// No FK
    46: 'GAIN_REPUTATION',							// faction::ID
    47: 'GAIN_EXALTED_REPUTATION',					// No FK
    48: 'VISIT_BARBER_SHOP',						// No FK
    49: 'EQUIP_EPIC_ITEM',							// No FK
    50: 'ROLL_NEED_ON_LOOT',						// No FK
    51: 'ROLL_GREED_ON_LOOT',						// No FK
    52: 'HK_CLASS',									// chrclasses::ID
    53: 'HK_RACE',									// chrraces::ID
    54: 'DO_EMOTE',									// emotes::ID
    55: 'HEALING_DONE',								// No FK
    56: 'GET_KILLING_BLOWS',						// No FK
    57: 'EQUIP_ITEM',								// item::ID
    59: 'MONEY_FROM_VENDORS',						// No FK
    60: 'GOLD_SPENT_FOR_TALENTS',					// No FK
    61: 'NUMBER_OF_TALENT_RESETS',					// No FK
    62: 'MONEY_FROM_QUEST_REWARD',					// No FK
    63: 'GOLD_SPENT_FOR_TRAVELLING',				// No FK
    64: 'DEFEAT_CREATURE_GROUP',					// ????
    65: 'GOLD_SPENT_AT_BARBER',						// No FK
    66: 'GOLD_SPENT_FOR_MAIL',						// No FK
    67: 'LOOT_MONEY',								// No FK
    68: 'USE_GAMEOBJECT',							// gameobjects::ID
    69: 'BE_SPELL_TARGET2',							// spell::ID
    70: 'SPECIAL_PVP_KILL',							// No FK
    71: 'COMPLETE_CHALLENGE_MODE',					// map::ID
    72: 'FISH_IN_GAMEOBJECT',						// gameobjects::ID
    73: 'SEND_EVENT',								// ????
    74: 'ON_LOGIN',									// No FK
    75: 'LEARN_SKILLLINE_SPELLS',					// skillline::ID
    76: 'WIN_DUEL',									// No FK
    77: 'LOSE_DUEL',								// No FK
    78: 'KILL_CREATURE_TYPE',						// No FK
    79: 'COOK_RECIPES_GUILD',						// No FK
    80: 'GOLD_EARNED_BY_AUCTIONS',					// No FK
    81: 'EARN_PET_BATTLE_ACHIEVEMENT_POINTS',		// No FK
    82: 'CREATE_AUCTION',							// No FK
    83: 'HIGHEST_AUCTION_BID',						// No FK
    84: 'WON_AUCTIONS',								// No FK
    85: 'HIGHEST_AUCTION_SOLD',						// No FK
    86: 'HIGHEST_GOLD_VALUE_OWNED',					// No FK
    87: 'GAIN_REVERED_REPUTATION',					// No FK
    88: 'GAIN_HONORED_REPUTATION',					// No FK
    89: 'KNOWN_FACTIONS',							// No FK
    90: 'LOOT_EPIC_ITEM',							// No FK
    91: 'RECEIVE_EPIC_ITEM',						// No FK
    92: 'SEND_EVENT_SCENARIO',						// ????
    93: 'ROLL_NEED',								// No FK
    94: 'ROLL_GREED',								// No FK
    95: 'RELEASE_SPIRIT',							// No FK
    96: 'OWN_PET',									// creature::ID
    97: 'GARRISON_COMPLETE_DUNGEON_ENCOUNTER',		// dungeonencounter::ID
    101: 'HIGHEST_HIT_DEALT',						// No FK
    102: 'HIGHEST_HIT_RECEIVED',					// No FK
    103: 'TOTAL_DAMAGE_RECEIVED',					// No FK
    104: 'HIGHEST_HEAL_CAST',						// No FK
    105: 'TOTAL_HEALING_RECEIVED',					// No FK
    106: 'HIGHEST_HEALING_RECEIVED',				// No FK
    107: 'QUEST_ABANDONED',							// No FK
    108: 'FLIGHT_PATHS_TAKEN',						// No FK
    109: 'LOOT_TYPE',								// No FK
    110: 'CAST_SPELL2',								// spell::ID
    112: 'LEARN_SKILL_LINE',						// skillline::ID
    113: 'EARN_HONORABLE_KILL',						// No FK
    114: 'ACCEPTED_SUMMONINGS',						// No FK
    115: 'EARN_ACHIEVEMENT_POINTS',					// No FK
    118: 'COMPLETE_LFG_DUNGEON',					// No FK
    119: 'USE_LFD_TO_GROUP_WITH_PLAYERS',			// No FK
    120: 'LFG_VOTE_KICKS_INITIATED_BY_PLAYER',		// No FK
    121: 'LFG_VOTE_KICKS_NOT_INIT_BY_PLAYER',		// No FK
    122: 'BE_KICKED_FROM_LFG',						// No FK
    123: 'LFG_LEAVES',								// No FK
    124: 'SPENT_GOLD_GUILD_REPAIRS',				// No FK
    125: 'REACH_GUILD_LEVEL',						// No FK
    126: 'CRAFT_ITEMS_GUILD',						// No FK
    127: 'CATCH_FROM_POOL',							// No FK
    128: 'BUY_GUILD_BANK_SLOTS',					// No FK
    129: 'EARN_GUILD_ACHIEVEMENT_POINTS',			// No FK
    130: 'WIN_RATED_BATTLEGROUND',					// No FK
    132: 'REACH_BG_RATING',							// No FK
    133: 'BUY_GUILD_TABARD',						// No FK
    134: 'COMPLETE_QUESTS_GUILD',					// No FK
    135: 'HONORABLE_KILLS_GUILD',					// No FK
    136: 'KILL_CREATURE_TYPE_GUILD',				// No FK
    137: 'COUNT_OF_LFG_QUEUE_BOOSTS_BY_TANK',		// No FK
    138: 'COMPLETE_GUILD_CHALLENGE_TYPE',			// ????
    139: 'COMPLETE_GUILD_CHALLENGE',				// No FK
    145: 'LFR_DUNGEONS_COMPLETED',					// No FK
    146: 'LFR_LEAVES',								// No FK
    147: 'LFR_VOTE_KICKS_INITIATED_BY_PLAYER',		// No FK
    148: 'LFR_VOTE_KICKS_NOT_INIT_BY_PLAYER',		// No FK
    149: 'BE_KICKED_FROM_LFR',						// No FK
    150: 'COUNT_OF_LFR_QUEUE_BOOSTS_BY_TANK',		// No FK
    151: 'COMPLETE_SCENARIO_COUNT',					// No FK
    152: 'COMPLETE_SCENARIO',						// scenario::ID
    153: 'REACH_AREATRIGGER_WITH_ACTIONSET',		// ????
    155: 'OWN_BATTLE_PET',							// No FK
    156: 'OWN_BATTLE_PET_COUNT',					// No FK
    157: 'CAPTURE_BATTLE_PET',						// No FK
    158: 'WIN_PET_BATTLE',							// No FK
    160: 'LEVEL_BATTLE_PET',						// No FK
    161: 'CAPTURE_BATTLE_PET_CREDIT',				// No FK
    162: 'LEVEL_BATTLE_PET_CREDIT',					// No FK
    163: 'ENTER_AREA',								// areatable::ID
    164: 'LEAVE_AREA',								// areatable::ID
    165: 'COMPLETE_DUNGEON_ENCOUNTER',				// dungeonencounter::ID
    167: 'PLACE_GARRISON_BUILDING',					// garrbuilding::ID
    168: 'UPGRADE_GARRISON_BUILDING',				// No FK
    169: 'CONSTRUCT_GARRISON_BUILDING',				// garrbuilding::ID
    170: 'UPGRADE_GARRISON',						// No FK (GarrLevel)
    171: 'START_GARRISON_MISSION',					// No FK
    172: 'START_ORDER_HALL_MISSION',				// garrmission::ID
    173: 'COMPLETE_GARRISON_MISSION_COUNT',			// No FK
    174: 'COMPLETE_GARRISON_MISSION',				// garrmission::ID
    175: 'RECRUIT_GARRISON_FOLLOWER_COUNT',			// No FK
    176: 'RECRUIT_GARRISON_FOLLOWER',				// garrfollower::ID
    178: 'LEARN_GARRISON_BLUEPRINT_COUNT',			// No FK
    182: 'COMPLETE_GARRISON_SHIPMENT',				// No FK
    183: 'RAISE_GARRISON_FOLLOWER_ITEM_LEVEL',		// No FK
    184: 'RAISE_GARRISON_FOLLOWER_LEVEL',			// No FK
    185: 'OWN_TOY',									// ????
    186: 'OWN_TOY_COUNT',							// ????
    187: 'RECRUIT_GARRISON_FOLLOWER_WITH_QUALITY',	// No FK
    189: 'OWN_HEIRLOOMS',							// No FK
    190: 'ARTIFACT_POWER_EARNED',					// No FK
    191: 'ARTIFACT_TRAITS_UNLOCKED',				// No FK
    194: 'HONOR_LEVEL_REACHED',						// No FK
    195: 'PRESTIGE_REACHED',						// No FK
    196: 'HERITAGE_AT_LEVEL',						// No FK (Level Reached, points to Heritage achievements)
    197: 'COVENANT_SANCTUM_RANK_REACHED',			// No FK
    198: 'ORDER_HALL_TALENT_LEARNED',				// No FK
    199: 'APPEARANCE_UNLOCKED_BY_SLOT',				// No FK (Slot)
    200: 'ORDER_HALL_RECRUIT_TROOP',				// No FK
    202: 'RESEARCHED_GARRISON_TALENT',              // garrtalent::ID
    203: 'COMPLETE_WORLD_QUEST',					// No FK
    204: 'TRANSMOG_SET_RELATED',					// transmogset::ID
    205: 'TRANSMOG_SET_UNLOCKED',					// transmogset::ID (?)
    206: 'GAIN_PARAGON_REPUTATION',					// No FK
    207: 'EARN_HONOR_XP',							// No FK
    211: 'RELIC_TALENT_UNLOCKED',					// artifactpower::ID (?)
    213: 'REACH_ACCOUNT_HONOR_LEVEL',				// No FK (Honor Level Reached)
    214: 'HEART_OF_AZEROTH_ARTIFACT_POWER_EARNED',	// No FK
    215: 'HEART_OF_AZEROTH_LEVEL_REACHED',			// No FK (Neck Level Reached)
    216: 'MYTHIC_KEYSTONE_COMPLETED',				// No FK
    218: 'QUEST_COUNT_RELATED',						// No FK
    219: 'BOUGHT_ITEM_FROM_VENDOR',					// No FK
    220: 'SOLD_ITEM_TO_VENDOR',						// No FK
    225: 'TRAVELED_TO_AREA',						// areatable::ID
    228: 'CONDUIT_RELATED',							// No FK
    229: 'ANIMA_DEPOSITED'							// No FK
}

const componentSection = {
    0: 'ArmUpper',
    1: 'ArmLower',
    2: 'Hand',
    3: 'TorsoUpper',
    4: 'TorsoLower',
    5: 'LegUpper',
    6: 'LegLower',
    7: 'Foot',
    8: 'Accessory',
    9: 'ScalpUpper',
    10: 'ScalpLower',
    11: 'UNK0',
    12: 'Tattoo unk0',
    13: 'Tattoo unk1'
}

const geosetType = {
    0: 'Skin/Hair',
    1: 'Face 1',
    2: 'Face 2',
    3: 'Face 3',
    4: 'Gloves',
    5: 'Boots',
    6: 'Tail',
    7: 'Ears',
    8: 'Sleeves',
    9: 'Kneepads',
    10: 'Chest',
    11: 'Pants',
    12: 'Tabard',
    13: 'Trousers',
    14: 'DH Loincloth',
    15: 'Cloak',
    16: 'Mechagnome Chin',
    17: 'Eyeglows',
    18: 'Belt',
    19: 'Bone',
    20: 'Feet',
    22: 'Torso',
    23: 'Hand attachment',
    24: 'Head attachment',
    25: 'DH Blindfolds',
    29: 'Mechagnome Arms/Hands',
    30: 'Mechagnome Legs',
    31: 'Mechagnome Feet',
    32: 'Face',
    33: 'Eyes',
    34: 'Eyebrows',
    35: 'Earrings',
    36: 'Necklace',
    37: 'Headdress',
    38: 'Tails',
    39: 'Vines',
    40: 'Tusks'
}

const chrCustomizationReqType = {
    1: 'ClassReq'
}

const uiCustomizationType = {
    0: 'Skin',
    1: 'Face',
    2: 'Hair',
    3: 'HairColor',
    4: 'FacialHair',
    5: 'CustomOptionTattoo',
    6: 'CustomOptionHorn',
    7: 'CustomOptionFacewear',
    8: 'CustomOptionTattooColor'
}

const effectAuraType = {
    0: 'NONE',
    1: 'BIND_SIGHT',
    2: 'MOD_POSSESS',
    3: 'PERIODIC_DAMAGE',
    4: 'DUMMY',
    5: 'MOD_CONFUSE',
    6: 'MOD_CHARM',
    7: 'MOD_FEAR',
    8: 'PERIODIC_HEAL',
    9: 'MOD_ATTACKSPEED',
    10: 'MOD_THREAT',
    11: 'MOD_TAUNT',
    12: 'MOD_STUN',
    13: 'MOD_DAMAGE_DONE',
    14: 'MOD_DAMAGE_TAKEN',
    15: 'DAMAGE_SHIELD',
    16: 'MOD_STEALTH',
    17: 'MOD_STEALTH_DETECT',
    18: 'MOD_INVISIBILITY',
    19: 'MOD_INVISIBILITY_DETECT',
    20: 'OBS_MOD_HEALTH',
    21: 'OBS_MOD_POWER',
    22: 'MOD_RESISTANCE',
    23: 'PERIODIC_TRIGGER_SPELL',
    24: 'PERIODIC_ENERGIZE',
    25: 'MOD_PACIFY',
    26: 'MOD_ROOT',
    27: 'MOD_SILENCE',
    28: 'REFLECT_SPELLS',
    29: 'MOD_STAT',
    30: 'MOD_SKILL',
    31: 'MOD_INCREASE_SPEED',
    32: 'MOD_INCREASE_MOUNTED_SPEED',
    33: 'MOD_DECREASE_SPEED',
    34: 'MOD_INCREASE_HEALTH',
    35: 'MOD_INCREASE_ENERGY',
    36: 'MOD_SHAPESHIFT',
    37: 'EFFECT_IMMUNITY',
    38: 'STATE_IMMUNITY',
    39: 'SCHOOL_IMMUNITY',
    40: 'DAMAGE_IMMUNITY',
    41: 'DISPEL_IMMUNITY',
    42: 'PROC_TRIGGER_SPELL',
    43: 'PROC_TRIGGER_DAMAGE',
    44: 'TRACK_CREATURES',
    45: 'TRACK_RESOURCES',
    // 46: '46',
    47: 'MOD_PARRY_PERCENT',
    // 48: '48',
    49: 'MOD_DODGE_PERCENT',
    50: 'MOD_CRITICAL_HEALING_AMOUNT',
    51: 'MOD_BLOCK_PERCENT',
    52: 'MOD_WEAPON_CRIT_PERCENT',
    53: 'PERIODIC_LEECH',
    54: 'MOD_HIT_CHANCE',
    55: 'MOD_SPELL_HIT_CHANCE',
    56: 'TRANSFORM',
    57: 'MOD_SPELL_CRIT_CHANCE',
    58: 'MOD_INCREASE_SWIM_SPEED',
    59: 'MOD_DAMAGE_DONE_CREATURE',
    60: 'MOD_PACIFY_SILENCE',
    61: 'MOD_SCALE',
    62: 'PERIODIC_HEALTH_FUNNEL',
    63: 'MOD_ADDITIONAL_POWER_COST',
    64: 'PERIODIC_MANA_LEECH',
    65: 'MOD_CASTING_SPEED_NOT_STACK',
    66: 'FEIGN_DEATH',
    67: 'MOD_DISARM',
    68: 'MOD_STALKED',
    69: 'SCHOOL_ABSORB',
    70: 'EXTRA_ATTACKS',
    71: 'MOD_SPELL_CRIT_CHANCE_SCHOOL',
    72: 'MOD_POWER_COST_SCHOOL_PCT',
    73: 'MOD_POWER_COST_SCHOOL',
    74: 'REFLECT_SPELLS_SCHOOL',
    75: 'MOD_LANGUAGE',
    76: 'FAR_SIGHT',
    77: 'MECHANIC_IMMUNITY',
    78: 'MOUNTED',
    79: 'MOD_DAMAGE_PERCENT_DONE',
    80: 'MOD_PERCENT_STAT',
    81: 'SPLIT_DAMAGE_PCT',
    82: 'WATER_BREATHING',
    83: 'MOD_BASE_RESISTANCE',
    84: 'MOD_REGEN',
    85: 'MOD_POWER_REGEN',
    86: 'CHANNEL_DEATH_ITEM',
    87: 'MOD_DAMAGE_PERCENT_TAKEN',
    88: 'MOD_HEALTH_REGEN_PERCENT',
    89: 'PERIODIC_DAMAGE_PERCENT',
    // 90: '90',
    91: 'MOD_DETECT_RANGE',
    92: 'PREVENTS_FLEEING',
    93: 'MOD_UNATTACKABLE',
    94: 'INTERRUPT_REGEN',
    95: 'GHOST',
    96: 'SPELL_MAGNET',
    97: 'MANA_SHIELD',
    98: 'MOD_SKILL_TALENT',
    99: 'MOD_ATTACK_POWER',
    100: 'AURAS_VISIBLE',
    101: 'MOD_RESISTANCE_PCT',
    102: 'MOD_MELEE_ATTACK_POWER_VERSUS',
    103: 'MOD_TOTAL_THREAT',
    104: 'WATER_WALK',
    105: 'FEATHER_FALL',
    106: 'HOVER',
    107: 'ADD_FLAT_MODIFIER',
    108: 'ADD_PCT_MODIFIER',
    109: 'ADD_TARGET_TRIGGER',
    110: 'MOD_POWER_REGEN_PERCENT',
    111: 'ADD_CASTER_HIT_TRIGGER',
    112: 'OVERRIDE_CLASS_SCRIPTS',
    113: 'MOD_RANGED_DAMAGE_TAKEN',
    114: 'MOD_RANGED_DAMAGE_TAKEN_PCT',
    115: 'MOD_HEALING',
    116: 'MOD_REGEN_DURING_COMBAT',
    117: 'MOD_MECHANIC_RESISTANCE',
    118: 'MOD_HEALING_PCT',
    119: 'PVP_TALENTS',
    120: 'UNTRACKABLE',
    121: 'EMPATHY',
    122: 'MOD_OFFHAND_DAMAGE_PCT',
    123: 'MOD_TARGET_RESISTANCE',
    124: 'MOD_RANGED_ATTACK_POWER',
    125: 'MOD_MELEE_DAMAGE_TAKEN',
    126: 'MOD_MELEE_DAMAGE_TAKEN_PCT',
    127: 'RANGED_ATTACK_POWER_ATTACKER_BONUS',
    128: 'MOD_POSSESS_PET',
    129: 'MOD_SPEED_ALWAYS',
    130: 'MOD_MOUNTED_SPEED_ALWAYS',
    131: 'MOD_RANGED_ATTACK_POWER_VERSUS',
    132: 'MOD_INCREASE_ENERGY_PERCENT',
    133: 'MOD_INCREASE_HEALTH_PERCENT',
    134: 'MOD_MANA_REGEN_INTERRUPT',
    135: 'MOD_HEALING_DONE',
    136: 'MOD_HEALING_DONE_PERCENT',
    137: 'MOD_TOTAL_STAT_PERCENTAGE',
    138: 'MOD_MELEE_HASTE',
    139: 'FORCE_REACTION',
    140: 'MOD_RANGED_HASTE',
    // 141: '141',
    142: 'MOD_BASE_RESISTANCE_PCT',
    143: 'MOD_RESISTANCE_EXCLUSIVE',
    144: 'SAFE_FALL',
    145: 'MOD_PET_TALENT_POINTS',
    146: 'ALLOW_TAME_PET_TYPE',
    147: 'MECHANIC_IMMUNITY_MASK',
    148: 'RETAIN_COMBO_POINTS',
    149: 'REDUCE_PUSHBACK',
    150: 'MOD_SHIELD_BLOCKVALUE_PCT',
    151: 'TRACK_STEALTHED',
    152: 'MOD_DETECTED_RANGE',
    // 153: '153',
    154: 'MOD_STEALTH_LEVEL',
    155: 'MOD_WATER_BREATHING',
    156: 'MOD_REPUTATION_GAIN',
    157: 'PET_DAMAGE_MULTI',
    158: 'MOD_SHIELD_BLOCKVALUE',
    159: 'NO_PVP_CREDIT',
    // 160: '160',
    161: 'MOD_HEALTH_REGEN_IN_COMBAT',
    162: 'POWER_BURN',
    163: 'MOD_CRIT_DAMAGE_BONUS',
    // 164: '164',
    165: 'MELEE_ATTACK_POWER_ATTACKER_BONUS',
    166: 'MOD_ATTACK_POWER_PCT',
    167: 'MOD_RANGED_ATTACK_POWER_PCT',
    168: 'MOD_DAMAGE_DONE_VERSUS',
    // 169: '169',
    170: 'DETECT_AMORE',
    171: 'MOD_SPEED_NOT_STACK',
    172: 'MOD_MOUNTED_SPEED_NOT_STACK',
    // 173: '173',
    174: 'MOD_SPELL_DAMAGE_OF_STAT_PERCENT',
    175: 'MOD_SPELL_HEALING_OF_STAT_PERCENT',
    176: 'SPIRIT_OF_REDEMPTION',
    177: 'AOE_CHARM',
    // 178: '178',
    179: 'MOD_ATTACKER_SPELL_CRIT_CHANCE',
    180: 'MOD_FLAT_SPELL_DAMAGE_VERSUS',
    // 181: '181',
    182: 'MOD_RESISTANCE_OF_STAT_PERCENT',
    183: 'MOD_CRITICAL_THREAT',
    184: 'MOD_ATTACKER_MELEE_HIT_CHANCE',
    185: 'MOD_ATTACKER_RANGED_HIT_CHANCE',
    186: 'MOD_ATTACKER_SPELL_HIT_CHANCE',
    187: 'MOD_ATTACKER_MELEE_CRIT_CHANCE',
    188: 'MOD_ATTACKER_RANGED_CRIT_CHANCE',
    189: 'MOD_RATING',
    190: 'MOD_FACTION_REPUTATION_GAIN',
    191: 'USE_NORMAL_MOVEMENT_SPEED',
    192: 'MOD_MELEE_RANGED_HASTE',
    193: 'MELEE_SLOW',
    194: 'MOD_TARGET_ABSORB_SCHOOL',
    195: 'MOD_TARGET_ABILITY_ABSORB_SCHOOL',
    196: 'MOD_COOLDOWN',
    197: 'MOD_ATTACKER_SPELL_AND_WEAPON_CRIT_CHANCE',
    // 198: '198',
    // 199: '199',
    200: 'MOD_XP_PCT',
    201: 'FLY',
    202: 'IGNORE_COMBAT_RESULT',
    203: 'MOD_ATTACKER_MELEE_CRIT_DAMAGE',
    204: 'MOD_ATTACKER_RANGED_CRIT_DAMAGE',
    205: 'MOD_SCHOOL_CRIT_DMG_TAKEN',
    206: 'MOD_INCREASE_VEHICLE_FLIGHT_SPEED',
    207: 'MOD_INCREASE_MOUNTED_FLIGHT_SPEED',
    208: 'MOD_INCREASE_FLIGHT_SPEED',
    209: 'MOD_MOUNTED_FLIGHT_SPEED_ALWAYS',
    210: 'MOD_VEHICLE_SPEED_ALWAYS',
    211: 'MOD_FLIGHT_SPEED_NOT_STACK',
    // 212: '212',
    213: 'MOD_RAGE_FROM_DAMAGE_DEALT',
    // 214: '214',
    215: 'ARENA_PREPARATION',
    216: 'HASTE_SPELLS',
    217: 'MOD_MELEE_HASTE_2',
    218: 'HASTE_RANGED',
    219: 'MOD_MANA_REGEN_FROM_STAT',
    220: 'MOD_RATING_FROM_STAT',
    221: 'MOD_DETAUNT',
    // 222: '222',
    223: 'RAID_PROC_FROM_CHARGE',
    224: 'GAIN_TALENT',
    225: 'RAID_PROC_FROM_CHARGE_WITH_VALUE',
    226: 'PERIODIC_DUMMY',
    227: 'PERIODIC_TRIGGER_SPELL_WITH_VALUE',
    228: 'DETECT_STEALTH',
    229: 'MOD_AOE_DAMAGE_AVOIDANCE',
    230: 'MOD_MAX_HEALTH',
    231: 'PROC_TRIGGER_SPELL_WITH_VALUE',
    232: 'MECHANIC_DURATION_MOD',
    233: 'CHANGE_MODEL_FOR_ALL_HUMANOIDS',
    234: 'MECHANIC_DURATION_MOD_NOT_STACK',
    235: 'MOD_DISPEL_RESIST',
    236: 'CONTROL_VEHICLE',
    237: 'MOD_SPELL_DAMAGE_OF_ATTACK_POWER',
    238: 'MOD_SPELL_HEALING_OF_ATTACK_POWER',
    239: 'MOD_SCALE_2',
    240: 'MOD_EXPERTISE',
    241: 'FORCE_MOVE_FORWARD',
    242: 'MOD_SPELL_DAMAGE_FROM_HEALING',
    243: 'MOD_FACTION',
    244: 'COMPREHEND_LANGUAGE',
    245: 'MOD_AURA_DURATION_BY_DISPEL',
    246: 'MOD_AURA_DURATION_BY_DISPEL_NOT_STACK',
    247: 'CLONE_CASTER',
    248: 'MOD_COMBAT_RESULT_CHANCE',
    249: 'CONVERT_RUNE',
    250: 'MOD_INCREASE_HEALTH_2',
    251: 'MOD_ENEMY_DODGE',
    252: 'MOD_SPEED_SLOW_ALL',
    253: 'MOD_BLOCK_CRIT_CHANCE',
    254: 'MOD_DISARM_OFFHAND',
    255: 'MOD_MECHANIC_DAMAGE_TAKEN_PERCENT',
    256: 'NO_REAGENT_USE',
    257: 'MOD_TARGET_RESIST_BY_SPELL_CLASS',
    258: 'OVERRIDE_SUMMONED_OBJECT',
    // 259: '259',
    260: 'SCREEN_EFFECT',
    261: 'PHASE',
    262: 'ABILITY_IGNORE_AURASTATE',
    263: 'ALLOW_ONLY_ABILITY',
    // 264: '264',
    // 265: '265',
    // 266: '266',
    267: 'MOD_IMMUNE_AURA_APPLY_SCHOOL',
    // 268: '268',
    269: 'MOD_IGNORE_TARGET_RESIST',
    270: 'SCHOOL_MASK_DAMAGE_FROM_CASTER',
    271: 'MOD_SPELL_DAMAGE_FROM_CASTER',
    272: 'IGNORE_MELEE_RESET',
    273: 'X_RAY',
    // 274: '274',
    275: 'MOD_IGNORE_SHAPESHIFT',
    276: 'MOD_DAMAGE_DONE_FOR_MECHANIC',
    // 277: '277',
    278: 'MOD_DISARM_RANGED',
    279: 'INITIALIZE_IMAGES',
    // 280: '280',
    281: 'MOD_HONOR_GAIN_PCT',
    282: 'MOD_BASE_HEALTH_PCT',
    283: 'MOD_HEALING_RECEIVED',
    284: 'LINKED',
    285: 'MOD_ATTACK_POWER_OF_ARMOR',
    286: 'ABILITY_PERIODIC_CRIT',
    287: 'DEFLECT_SPELLS',
    288: 'IGNORE_HIT_DIRECTION',
    289: 'PREVENT_DURABILITY_LOSS',
    290: 'MOD_CRIT_PCT',
    291: 'MOD_XP_QUEST_PCT',
    292: 'OPEN_STABLE',
    293: 'OVERRIDE_SPELLS',
    294: 'PREVENT_REGENERATE_POWER',
    // 295: '295',
    296: 'SET_VEHICLE_ID',
    297: 'BLOCK_SPELL_FAMILY',
    298: 'STRANGULATE',
    // 299: '299',
    300: 'SHARE_DAMAGE_PCT',
    301: 'SCHOOL_HEAL_ABSORB',
    // 302: '302',
    303: 'MOD_DAMAGE_DONE_VERSUS_AURASTATE',
    304: 'MOD_FAKE_INEBRIATE',
    305: 'MOD_MINIMUM_SPEED',
    // 306: '306',
    307: 'HEAL_ABSORB_TEST',
    308: 'MOD_CRIT_CHANCE_FOR_CASTER',
    309: 'MOD_RESILIENCE',
    310: 'MOD_CREATURE_AOE_DAMAGE_AVOIDANCE',
    // 311: '311',
    312: 'ANIM_REPLACEMENT_SET',
    // 313: '313',
    314: 'PREVENT_RESURRECTION',
    315: 'UNDERWATER_WALKING',
    316: 'PERIODIC_HASTE',
    317: 'MOD_SPELL_POWER_PCT',
    318: 'MASTERY',
    319: 'MOD_MELEE_HASTE_3',
    320: 'MOD_RANGED_HASTE_2',
    321: 'MOD_NO_ACTIONS',
    322: 'INTERFERE_TARGETTING',
    // 323: '323',
    // 324: '324',
    325: 'LEARN_PVP_TALENT',
    326: 'PHASE_GROUP',
    // 327: '327',
    328: 'PROC_ON_POWER_AMOUNT',
    329: 'MOD_RUNE_REGEN_SPEED',
    330: 'CAST_WHILE_WALKING',
    331: 'FORCE_WEATHER',
    332: 'OVERRIDE_ACTIONBAR_SPELLS',
    333: 'OVERRIDE_ACTIONBAR_SPELLS_TRIGGERED',
    334: 'MOD_BLIND',
    // 335: '335',
    336: 'MOD_FLYING_RESTRICTIONS',
    337: 'MOD_VENDOR_ITEMS_PRICES',
    338: 'MOD_DURABILITY_LOSS',
    339: 'INCREASE_SKILL_GAIN_CHANCE',
    340: 'MOD_RESURRECTED_HEALTH_BY_GUILD_MEMBER',
    341: 'MOD_SPELL_CATEGORY_COOLDOWN',
    342: 'MOD_MELEE_RANGED_HASTE_2',
    343: 'MOD_MELEE_DAMAGE_FROM_CASTER',
    344: 'MOD_AUTOATTACK_DAMAGE',
    345: 'BYPASS_ARMOR_FOR_CASTER',
    346: 'ENABLE_ALT_POWER',
    347: 'MOD_SPELL_COOLDOWN_BY_HASTE',
    348: 'DEPOSIT_BONUS_MONEY_IN_GUILD_BANK_ON_LOOT',
    349: 'MOD_CURRENCY_GAIN',
    350: 'MOD_GATHERING_ITEMS_GAINED_PERCENT',
    // 351: '351',
    // 352: '352',
    353: 'MOD_CAMOUFLAGE',
    // 354: '354',
    355: 'MOD_CASTING_SPEED',
    // 356: '356',
    357: 'ENABLE_BOSS1_UNIT_FRAME',
    358: 'WORGEN_ALTERED_FORM',
    // 359: '359',
    360: 'PROC_TRIGGER_SPELL_COPY',
    361: 'OVERRIDE_AUTOATTACK_WITH_MELEE_SPELL',
    // 362: '362',
    363: 'MOD_NEXT_SPELL',
    // 364: '364',
    365: 'MAX_FAR_CLIP_PLANE',
    366: 'OVERRIDE_SPELL_POWER_BY_AP_PCT',
    367: 'OVERRIDE_AUTOATTACK_WITH_RANGED_SPELL',
    // 368: '368',
    369: 'ENABLE_POWER_BAR_TIMER',
    370: 'SET_FAIR_FAR_CLIP',
    // 371: '371',
    // 372: '372',
    373: 'MOD_SPEED_NO_CONTROL',
    374: 'MOD_FALL_DAMAGE_PCT',
    // 375: '375',
    376: 'MOD_CURRENCY_GAIN_FROM_SOURCE',
    377: 'CAST_WHILE_WALKING_2',
    // 378: '378',
    379: 'MOD_MANA_REGEN_PCT',
    380: 'MOD_GLOBAL_COOLDOWN_BY_HASTE',
    // 381: '381',
    382: 'MOD_PET_STAT_PCT',
    383: 'IGNORE_SPELL_COOLDOWN',
    // 384: '384',
    385: 'CHANCE_OVERRIDE_AUTOATTACK_WITH_SPELL_ON_SELF',
    // 386: '386',
    // 387: '387',
    388: 'MOD_TAXI_FLIGHT_SPEED',
    // 389: '389',
    // 390: '390',
    // 391: '391',
    // 392: '392',
    // 393: '393',
    394: 'SHOW_CONFIRMATION_PROMPT',
    395: 'AREA_TRIGGER',
    396: 'PROC_ON_POWER_AMOUNT_2',
    // 397: '397',
    // 398: '398',
    // 399: '399',
    400: 'MOD_SKILL_2',
    // 401: '401',
    402: 'MOD_POWER_DISPLAY',
    403: 'OVERRIDE_SPELL_VISUAL',
    404: 'OVERRIDE_ATTACK_POWER_BY_SP_PCT',
    405: 'MOD_RATING_PCT',
    406: 'KEYBOUND_OVERRIDE',
    407: 'MOD_FEAR_2',
    // 408: '408',
    409: 'CAN_TURN_WHILE_FALLING',
    // 410: '410',
    411: 'MOD_MAX_CHARGES',
    // 412: '412',
    // 413: '413',
    // 414: '414',
    // 415: '415',
    416: 'MOD_COOLDOWN_BY_HASTE_REGEN',
    417: 'MOD_GLOBAL_COOLDOWN_BY_HASTE_REGEN',
    418: 'MOD_MAX_POWER',
    419: 'MOD_BASE_MANA_PCT',
    420: 'MOD_BATTLE_PET_XP_PCT',
    421: 'MOD_ABSORB_EFFECTS_AMOUNT_PCT',
    // 422: '422',
    // 423: '423',
    // 424: '424',
    // 425: '425',
    // 426: '426',
    427: 'SCALE_PLAYER_LEVEL',
    428: 'LINKED_SUMMON',
    // 429: '429',
    430: 'PLAY_SCENE',
    431: 'MOD_OVERRIDE_ZONE_PVP_TYPE',
    // 432: '432', // UNUSED IN 9.0.1.34199
    // 433: '433', // UNUSED IN 9.0.1.34199
    // 434: '434', // Attacking nearby units (players/NPCs) of same faction?
    // 435: '435', // UNUSED IN 9.0.1.34199
    436: 'MOD_ENVIRONMENTAL_DAMAGE_TAKEN',
    437: 'MOD_MINIMUM_SPEED_RATE',
    438: 'PRELOAD_PHASE',
    // 439: '439', // UNUSED IN 9.0.1.34199
    440: 'MOD_MULTISTRIKE_DAMAGE',
    441: 'MOD_MULTISTRIKE_CHANCE',
    442: 'MOD_READINESS',
    443: 'MOD_LEECH',
    // 444: '444', // UNUSED IN 9.0.1.34199
    // 445: '445', // UNUSED IN 9.0.1.34199
    // 446: '446', // UNUSED IN 9.0.1.34199
    447: 'MOD_XP_FROM_CREATURE_TYPE',
    // 448: '448', // Related to PvP rules
    // 449: '449', // UNUSED IN 9.0.1.34199
    // 450: '450', // Only used in Character Upgrade Spell Tier (156747)
    451: 'OVERRIDE_PET_SPECS',
    // 452: '452', // UNUSED IN 9.0.1.34199
    453: 'CHARGE_RECOVERY_MOD',
    454: 'CHARGE_RECOVERY_MULTIPLIER',
    455: 'MOD_ROOT_2', // Related to being immobilized/rooted
    456: 'CHARGE_RECOVERY_AFFECTED_BY_HASTE',
    457: 'CHARGE_RECOVERY_AFFECTED_BY_HASTE_REGEN',
    458: 'IGNORE_DUAL_WIELD_HIT_PENALTY',
    459: 'IGNORE_MOVEMENT_FORCES',
    460: 'RESET_COOLDOWNS_ON_DUEL_START',
    // 461: '461', // UNUSED IN 9.0.1.34199
    462: 'MOD_HEALING_AND_ABSORB_FROM_CASTER',
    463: 'CONVERT_CRIT_RATING_PCT_TO_PARRY_RATING',
    464: 'MOD_ATTACK_POWER_OF_BONUS_ARMOR',
    465: 'MOD_BONUS_ARMOR',
    466: 'MOD_BONUS_ARMOR_PCT',
    467: 'MOD_STAT_BONUS_PCT',
    468: 'TRIGGER_SPELL_ON_HEALTH_BELOW_PCT',
    469: 'SHOW_CONFIRMATION_PROMPT_WITH_DIFFICULTY',
    // 470: '470', // Used in spell	209618 (Expedite), EffectBasePointsF of 100, EffectMiscValue[0] of 174 or 182
    471: 'MOD_VERSATILITY',
    // 472: '472', // FIXATE?
    473: 'PREVENT_DURABILITY_LOSS_FROM_COMBAT',
    // 474: '474', // "Upgrades", some of these removed in 8.3.0 => 9.0.1 for spell 170733? Needs ID mapping.
    475: 'ALLOW_USING_GAMEOBJECTS_WHILE_MOUNTED',
    476: 'MOD_CURRENCY_GAIN_LOOTED_PCT',
    // 477: '477', // Only set on scaling for "testing purposes" spells
    // 478: '478', // UNUSED IN 9.0.1.34199
    // 479: '479', // Set to nothing, 1 or 31
    // 480: '480', // UNUSED IN 9.0.1.34199
    481: 'CONVERT_CONSUMED_RUNE',
    // 482: '482', // Only used in S.E.L.F.I.E spells, always set to 120
    483: 'SUPPRESS_TRANSFORMS',
    // 484: '484', // INTERRUPTABLE_BY_SPELL
    485: 'MOD_MOVEMENT_FORCE_MAGNITUDE',
    // 486: '486', // OBSCURED?
    // 487: '487', // 12 spells, possibly SpellVisual* related?
    // 488: '488', // Frozen effect? Paused anim?? (195289 + movement spells)
    // 489: '489', // DISABLE_LANGUAGE? Only used in mercenary spells
    // 490: '490', // Only used in mercenary spells (193863/193864)
    // 491: '491', // SET_REPUTATION?
    // 492: '492', // UNUSED IN 9.0.1.34199
    // 493: '493', // SUMMON_ADDITIONAL_PET?
}

const spellVisualKitEffectType = {
    1: 'SpellProceduralEffectID',
    2: 'SpellVisualKitModelAttachID',
    3: 'CameraEffectID',
    4: 'CameraEffectID2',
    5: 'SoundKitID',
    6: 'SpellVisualAnimID',
    7: 'ShadowyEffectID',
    8: 'SpellEffectEmissionID',
    9: 'OutlineEffectID',
    10: 'UnitSoundType', // NOT soundkitSoundType!!!
    11: 'DissolveEffectID',
    12: 'EdgeGlowEffectID',
    13: 'BeamEffectID',
    14: 'ClientSceneEffectID',
    15: 'CloneEffectID', // Unused
    16: 'GradientEffectID',
    17: 'BarrageEffectID',
    18: 'RopeEffectID',
    19: 'SpellVisualScreenEffectID',
}

const spellLabelName = {
    // 12: '12',
    16: 'Player (???)',
    17: 'Mage',
    18: 'Priest',
    19: 'Warlock',
    20: 'Rogue',
    21: 'Druid',
    22: 'Monk',
    23: 'Hunter',
    24: 'Shaman',
    25: 'Warrior',
    26: 'Paladin',
    27: 'Death Knight',
    // 28: '28',
    // 29: '29',
    // 30: '30',
    // 31: '31',
    // 38: '38',
    // 40: '40',
    // 42: '42',
    // 52: '52',
    // 59: '59',
    // 60: '60',
    // 61: '61',
    // 62: '62',
    // 63: '63',
    // 64: '64',
    66: 'Demon Hunter',
    // 67: '67',
    // 68: '68',
    // 69: '69',
    // 71: '71',
    // 72: '72',
    // 73: '73',
    // 74: '74',
    // 75: '75',
    // 76: '76',
    // 77: '77',
    // 78: '78',
    // 79: '79',
    // 80: '80',
    // 81: '81',
    // 82: '82',
    // 83: '83',
    // 84: '84',
    // 85: '85',
    // 86: '86',
    // 87: '87',
    // 88: '88',
    // 89: '89',
    // 90: '90',
    // 91: '91',
    // 92: '92',
    // 93: '93',
    // 94: '94',
    // 95: '95',
    // 96: '96',
    // 97: '97',
    // 98: '98',
    // 99: '99',
    // 100: '100',
    // 101: '101',
    // 102: '102',
    // 103: '103',
    // 104: '104',
    // 105: '105',
    // 106: '106',
    // 107: '107',
    // 108: '108',
    // 109: '109',
    // 110: '110',
    // 111: '111',
    // 112: '112',
    // 113: '113',
    // 114: '114',
    // 115: '115',
    // 117: '117',
    // 118: '118',
    // 119: '119',
    // 120: '120',
    // 122: '122',
    // 123: '123',
    // 124: '124',
    // 125: '125',
    // 127: '127',
    // 128: '128',
    // 129: '129',
    // 130: '130',
    // 132: '132',
    // 134: '134',
    // 136: '136',
    // 137: '137',
    // 139: '139',
    // 140: '140',
    // 141: '141',
    // 142: '142',
    // 143: '143',
    // 144: '144',
    // 145: '145',
    // 146: '146',
    // 147: '147',
    // 151: '151',
    // 152: '152',
    // 153: '153',
    // 154: '154',
    // 155: '155',
    // 156: '156',
    // 157: '157',
    // 158: '158',
    // 159: '159',
    // 160: '160',
    // 161: '161',
    // 162: '162',
    // 163: '163',
    // 164: '164',
    // 165: '165',
    // 166: '166',
    // 167: '167',
    // 168: '168',
    // 170: '170',
    // 171: '171',
    // 172: '172',
    // 173: '173',
    // 174: '174',
    // 175: '175',
    // 176: '176',
    // 177: '177',
    // 178: '178',
    // 179: '179',
    // 180: '180',
    // 181: '181',
    // 182: '182',
    // 183: '183',
    // 184: '184',
    // 186: '186',
    // 187: '187',
    // 188: '188',
    // 189: '189',
    // 192: '192',
    // 193: '193',
    // 194: '194',
    // 197: '197',
    // 199: '199',
    // 201: '201',
    // 202: '202',
    // 203: '203',
    // 205: '205',
    // 207: '207',
    // 208: '208',
    // 209: '209',
    // 219: '219',
    // 228: '228',
    // 229: '229',
    // 230: '230',
    // 231: '231',
    // 232: '232',
    // 237: '237',
    // 242: '242',
    // 243: '243',
    // 247: '247',
    // 248: '248',
    // 249: '249',
    // 265: '265',
    // 275: '275',
    // 276: '276',
    // 277: '277',
    // 281: '281',
    // 282: '282',
    // 283: '283',
    // 284: '284',
    // 285: '285',
    // 286: '286',
    // 287: '287',
    // 288: '288',
    // 289: '289',
    // 291: '291',
    // 292: '292',
    // 293: '293',
    // 294: '294',
    // 295: '295',
    // 296: '296',
    // 297: '297',
    // 298: '298',
    // 299: '299',
    // 300: '300',
    // 301: '301',
    // 302: '302',
    // 303: '303',
    // 308: '308',
    // 309: '309',
    // 311: '311',
    // 312: '312',
    // 313: '313',
    // 319: '319',
    // 320: '320',
    // 322: '322',
    // 323: '323',
    // 325: '325',
    // 326: '326',
    // 327: '327',
    // 328: '328',
    // 329: '329',
    // 330: '330',
    // 331: '331',
    // 332: '332',
    // 333: '333',
    // 354: '354',
    // 359: '359',
    // 363: '363',
    // 364: '364',
    // 372: '372',
    // 373: '373',
    // 374: '374',
    // 375: '375',
    // 376: '376',
    // 378: '378',
    // 382: '382',
    // 383: '383',
    // 384: '384',
    // 385: '385',
    // 386: '386',
    // 387: '387',
    // 389: '389',
    // 391: '391',
    // 394: '394',
    // 396: '396',
    // 397: '397',
    // 399: '399',
    // 410: '410',
    // 411: '411',
    // 412: '412',
    // 413: '413',
    // 414: '414',
    // 415: '415',
    // 416: '416',
    // 417: '417',
    // 418: '418',
    // 420: '420',
    // 421: '421',
    // 422: '422',
    // 423: '423',
    // 424: '424',
    // 428: '428',
    // 429: '429',
    // 549: '549',
    // 563: '563',
    // 564: '564',
    // 565: '565',
    // 566: '566',
    // 569: '569',
    // 575: '575',
    // 577: '577',
    // 579: '579',
    // 580: '580',
    // 581: '581',
    585: 'Testimony spells',
    // 586: '586',
    // 587: '587',
    // 588: '588',
    // 590: '590',
    // 592: '592',
    // 599: '599',
    // 600: '600',
    // 602: '602',
    // 609: '609',
    // 611: '611',
    // 612: '612',
    // 613: '613',
    // 614: '614',
    615: 'Timewarp, Heroism, Drums etc',
    // 616: '616', // Hardcoded check
    // 617: '617',
    // 621: '621',
    // 623: '623',
    627: 'Torghast',
    // 629: '629',
    630: 'Anima Power',
    // 634: '634',
    // 638: '638',
    // 640: '640',
    // 641: '641',
    // 643: '643',
    // 644: '644',
    // 646: '646',
    // 647: '647',
    648: 'Hardened Azerite',
    // 649: '649',
    // 650: '650',
    // 651: '651',
    // 652: '652',
    // 653: '653',
    // 654: '654',
    // 655: '655',
    // 656: '656',
    // 657: '657',
    // 658: '658',
    // 659: '659',
    // 660: '660',
    // 661: '661',
    // 662: '662',
    // 663: '663',
    // 664: '664',
    // 665: '665',
    // 666: '666',
    // 667: '667', // PvP Hardcoded
    // 668: '668', // PvP Hardcoded
    // 669: '669', // PvP Hardcoded
    // 670: '670', // PvP Hardcoded
    // 671: '671',
    672: 'Empowered Null Barrier',
    673: 'Null Barrier',
    674: 'Null Barriers',
    675: 'Engine of X modifiers',
    676: 'Various conversation/phase spells',
    677: 'Azerite Spike',
    678: 'Unwavering Wards',
    679: 'Unwavering Ward',
    680: 'Guardian Shells',
    681: 'The Ever-Rising Tide',
    682: 'Overcharge Mana',
    683: 'Quickening',
    // 685: '685',
    // 686: '686',
    // 687: '687',
    // 688: '688',
    // 689: '689',
    // 690: '690',
    // 691: '691',
    // 692: '692',
    // 693: '693',
    // 694: '694',
    // 695: '695',
    // 698: '698',
    // 699: '699',
    // 700: '700',
    // 701: '701',
    // 702: '702',
    // 704: '704',
    // 709: '709',
    // 710: '710',
    // 711: '711',
    // 712: '712',
    // 713: '713',
    // 714: '714',
    // 715: '715',
    // 716: '716',
    // 717: '717',
    // 718: '718',
    // 719: '719',
    // 720: '720',
    // 721: '721',
    // 722: '722',
    // 723: '723',
    // 726: '726',
    // 731: '731',
    // 732: '732',
    // 733: '733',
    // 734: '734',
    // 735: '735',
    // 737: '737',
    // 738: '738',
    // 739: '739',
    // 740: '740',
    // 741: '741',
    // 742: '742',
    // 743: '743',
    // 744: '744',
    // 745: '745',
    // 746: '746', // Hardcoded check
    // 747: '747',
    // 748: '748',
    // 749: '749',
    // 750: '750',
    // 751: '751',
    // 752: '752',
    // 753: '753',
    // 754: '754',
    // 755: '755',
    756: 'Purification Protocol',
    // 757: '757',
    // 758: '758',
    // 759: '759',
    // 760: '760',
    // 768: '768',
    // 769: '769',
    // 770: '770',
    // 771: '771',
    // 773: '773',
    // 774: '774',
    // 777: '777',
    // 778: '778',
    // 779: '779',
    // 780: '780',
    // 781: '781',
    // 782: '782',
    // 783: '783',
    // 784: '784',
    // 785: '785',
    // 786: '786',
    // 795: '795',
    // 796: '796',
    // 797: '797',
    // 802: '802',
    // 803: '803',
    // 804: '804',
    // 805: '805',
    // 806: '806',
    // 807: '807',
    // 810: '810',
    // 811: '811',
    // 813: '813',
    // 814: '814',
    // 817: '817',
    // 818: '818',
    // 819: '819',
    // 820: '820',
    // 822: '822',
    // 823: '823',
    // 824: '824',
    825: 'Shroud of Resolve Rank auras',
    826: 'Azerite Essence - Worldvein Resonance',
    827: 'Gift/Servant of N\'Zoth',
    828: 'Covenant PH Abilities?',
    829: 'Receive Covenant Ability',
    830: 'Kyrian (Generic)',
    831: 'Kyrian (Deathknight)',
    832: 'Kyrian (Hunter)',
    833: 'Kyrian (Mage)',
    834: 'Kyrian (Paladin)',
    835: 'Covenant (Rogue)',
    837: 'Kyrian (Warlock)',
    838: 'Kyrian (Priest)',
    839: 'Kyrian (Warrior)',
    840: 'Kyrian (Warrior 2?)',
    841: 'Kyrian (Demon Hunter)',
    842: 'Kyrian (Rogue)',
    844: 'Kyrian (Monk)',
    845: 'Monk related',
    846: 'Kyrian (Hunter 2?)',
    847: 'Vision Madnesses',
    851: 'Vision Sanity Restoration',
    853: 'Kyrian (Priest)',
    854: 'Servant of N\'Zoth 2',
    856: 'Cyst related (dungeon/raid mechanic?)',
    858: 'Shroud of Resolve',
    859: 'Shroud of Resolve, again',
    860: 'Shroud of Resolve, but again',
    861: 'Venthyr (Warrior)',
    862: 'Muffinus messing around',
    863: 'High Noon (Druid Azerite)',
    869: 'Venthyr? (Rogue poisons?)',
    870: 'Crippling Poison (Rogue)',
    871: '[DNT] Immune To Bolster (Affix)',
    874: 'Find Weakness (?) (Rogue)',
    877: 'Eye of the Jailer Tiers',
    // 881: '881', // Many spells
    884: 'Torghast Chests',
    885: 'Felstorm/Beast Cleave (?)',
    887: 'Searing Bolt (?)',
    888: 'Venthyr (Shaman)',
    889: 'Fast Heal (?)',
    890: 'Night Fae (Generic/PH?)',
    891: 'Arcanic Pulse Detector (Torghast)',
    892: 'Alter Time',
    893: 'Alter Time 2',
    895: 'Clearcasting (Mage)',
    // 897: '897',
    // 905: '905',
    // 907: '907',
    // 908: '908',
    // 909: '909',
    // 910: '910',
    // 911: '911',
    // 912: '912',
    913: 'Kevin\'s Keyring (Soulbind)',
    914: 'Volatile Solvents',
    915: 'Ardenweald Garden',
    918: 'Hearth Kidneystone',
    919: 'Souls related (Torghast)',
    // 922: '922',
    // 923: '923',
    924: 'Ambient Sound States (All)',
    925: 'Ambient Sound States (Overrides)',
    926: 'Ambient Sound States (Nearby Threat)',
    927: 'Steward abilities/states',
    928: 'Covenant (Mage)',
    // 930: '930',
    931: 'New but old spells',
    932: 'Mage barriers?',
    933: 'Heroism (etc) exhaustions',
    934: 'Kyrian (Priest 2)',
    935: 'Disciplinary Command (Mage)',
    936: 'Ascended Nova (Mage)',
    937: 'Ascended Nova (Mage 2)',
    938: 'Brain Freeze (Mage)',
    939: 'Clearcasting 2 (Mage)',
    940: 'Hex',
    948: 'Warrior shouts',
    // 951: '951',
    952: 'Warrior cooldowns',
    954: 'Priest heals',
    958: 'Flasks',
    959: 'Well Fed(s)',
    960: 'Sinful Revelations', // (Priest? Pala?)
    961: 'Runes (DK)', // Frost?
    962: 'Runes 2 (DK)', // Unholy?
    963: 'Runes 3 (DK)', // Blood???
    965: 'Temple of Kotmogu Holding Artifact',
    966: 'Necrolord (Warlock)',
    967: 'Necrolord (Warlock 2)',
    968: 'Night Fae (Warlock)',
    969: '9.0 crafting related',
    970: 'Venthyr (Warlock)',
    971: '9.0 cooking',
    972: '9.0 crafting related (2)',
    973: 'Hold your ground 9.0',
    974: '9.0 enchanting',
    975: '9.0 inscription',
    976: '9.0 covenant',
    977: 'Wasteland Propriety soulbind',
    978: 'Kyrian Shaman',
    979: 'Denathrius abilities',
    980: 'Necrolord Shaman',
    981: 'Night Fae Shaman',
    982: 'Pelagos abilities',
    983: 'Chain Harvest (Venthyr)',
    984: 'Ancient Aftershock (Kyrian)',
    // 991: '',
    // 992: '',
    993: 'Path of Wisdom gifts',
    // 999: '',
    1003: 'Warrior (unk 9.0)',
    1025: 'Maw 9.0',
    1027: 'Necrolord Hunter',
    1032: 'Necrolord Hunter 2',
    1033: 'Night Fae Hunter',
    1034: 'Night Fae Hunter 2',
    1035: 'Venthyr Hunter',
    1036: 'Kleia skills',
    1038: 'Windfury Totem',
    1043: 'Blizzard',
    1044: '"Pick up item x" spells',

}

const unitConditionVariable = {
    8: 'CAN_ASSIST',
    9: 'CAN_ATTACK',
    16: 'COMBO_POINTS',
    24: 'DAMAGE_SCHOOL0_PERCENT',
    25: 'DAMAGE_SCHOOL1_PERCENT',
    26: 'DAMAGE_SCHOOL2_PERCENT',
    27: 'DAMAGE_SCHOOL3_PERCENT',
    28: 'DAMAGE_SCHOOL4_PERCENT',
    29: 'DAMAGE_SCHOOL5_PERCENT',
    30: 'DAMAGE_SCHOOL6_PERCENT',
    37: 'NPC_NUM_MELEE_ATTACKERS',
    40: 'IS_IN_MELEE_RANGE',
    41: 'PURSUIT_TIME',
    42: 'HARMFUL_AURA_CANCELLED_BY_DAMAGE',
    45: 'NUM_FRIENDS',
    46: 'THREAT_SCHOOL0_PERCENT',
    47: 'THREAT_SCHOOL1_PERCENT',
    48: 'THREAT_SCHOOL2_PERCENT',
    49: 'THREAT_SCHOOL3_PERCENT',
    50: 'THREAT_SCHOOL4_PERCENT',
    51: 'THREAT_SCHOOL5_PERCENT',
    52: 'THREAT_SCHOOL6_PERCENT',
    53: 'IS_INTERRUPTIBLE',
    55: 'NPC_NUM_RANGED_ATTACKERS',
    56: 'CREATURE_TYPE',
    57: 'IN_MELEE_RANGE',
    60: 'SPELL_KNOWN',
    62: 'IS_AREA_IMMUNE',
    64: 'DAMAGE_MAGIC_PERCENT',
    65: 'DAMAGE_PERCENT',
    66: 'THREAT_MAGIC_PERCENT',
    67: 'THREAT_PERCENT',
    69: 'HAS_TOTEM1',
    70: 'HAS_TOTEM2',
    71: 'HAS_TOTEM3',
    72: 'HAS_TOTEM4',
    73: 'HAS_TOTEM5',
    75: 'HAS_STRING_ID',
    76: 'HAS_AURA',
    77: 'REACTION_HOSTILE',
    78: 'CHAR_SPECIALIZATION_???',
    79: 'ROLE_IS_TANK',
    80: 'CHAR_SPECIALIZATION_???',
    81: 'ROLE_IS_HEALER',
    84: 'PATH_FAIL_COUNT',
    86: 'HAS_LABEL'
}

const unitConditionOperator = {
    1: 'EQUAL TO',
    2: 'NOT EQUAL TO',
    3: 'LESS THAN',
    4: 'LESS THAN OR EQUAL TO',
    5: 'GREATER THAN',
    6: 'GREATER THAN OR EQUAL TO',
}

const spellClassSet = {
    3: 'Mage',
    4: 'Warrior',
    5: 'Warlock',
    6: 'Priest',
    7: 'Druid',
    8: 'Rogue',
    9: 'Hunter',
    10: 'Paladin',
    11: 'Shaman',
    15: 'Death Knight',
    53: 'Monk',
    107: 'Demon Hunter',
}


// ChrModelID is already an enum that will end up at a race/gender but this is just a quick way
const tempChrModelIDEnum = {
    1: 'Human Male',
    2: 'Human Female',
    3: 'Orc Male',
    4: 'Orc Female',
    5: 'Dwarf Male',
    6: 'Dwarf Female',
    7: 'Night Elf Male',
    8: 'Night Elf Female',
    9: 'Scourge Male',
    10: 'Scourge Female',
    11: 'Tauren Male',
    12: 'Tauren Female',
    13: 'Gnome Male',
    14: 'Gnome Female',
    15: 'Troll Male',
    16: 'Troll Female',
    17: 'Goblin Male',
    18: 'Goblin Female',
    19: 'Blood Elf Male',
    20: 'Blood Elf Female',
    21: 'Draenei Male',
    22: 'Draenei Female',
    23: 'Fel Orc Male',
    24: 'Fel Orc Female',
    25: 'Naga Male',
    26: 'Naga Female',
    27: 'Broken Male',
    28: 'Broken Female',
    29: 'Skeleton Male',
    30: 'Skeleton (Fe)male',
    31: 'Vrykul Male',
    32: 'Vrykul (Fe)male',
    33: 'Tuskarr Male',
    34: 'Tuskarr Fe(male)',
    35: 'Forest Troll Male',
    36: 'Forest Troll (Fe)male',
    37: 'Taunka Male',
    38: 'Taunka (Fe)male',
    39: 'Northrend Skeleton Male',
    40: 'Northrend Skeleton (Fe)male',
    41: 'Ice Troll Male',
    42: 'Ice Troll (Fe)male',
    43: 'Worgen Male',
    44: 'Worgen Female',
    45: 'Gilnean Male',
    46: 'Gilnean Female',
    47: 'Pandaren Male',
    48: 'Pandaren Female',
    53: 'Nightborne Male',
    54: 'Nightborne Female',
    55: 'Highmountain Tauren Male',
    56: 'Highmountain Tauren Female',
    57: 'Void Elf Male',
    58: 'Void Elf Female',
    59: 'Lightforged Draenei Male',
    60: 'Lightforged Draenei Female',
    61: 'Zandalari Male',
    62: 'Zandalari Female',
    63: 'Kul Tiran Male',
    64: 'Kul Tiran Female',
    65: 'Thin Human Male',
    66: 'Thin Human (Fe)male',
    67: 'Dark Iron Dwarf Male',
    68: 'Dark Iron Dwarf Female',
    69: 'Vulpera Male',
    70: 'Vulpera Female',
    71: 'Mag\'har Orc Male',
    72: 'Mag\'har Orc Female',
    73: 'Mechagnome Male',
    74: 'Mechagnome Female',
}

const tempChrRaceIDEnum = {
    1: 'Human',
    2: 'Orc',
    3: 'Dwarf',
    4: 'Night Elf',
    5: 'Scourge',
    6: 'Tauren',
    7: 'Gnome',
    8: 'Troll',
    9: 'Goblin',
    10: 'Blood Elf',
    11: 'Draenei',
    12: 'Fel Orc',
    13: 'Naga',
    14: 'Broken',
    15: 'Skeleton',
    16: 'Vrykul',
    17: 'Tuskarr',
    18: 'Forest Troll',
    19: 'Taunka',
    20: 'Northrend Skeleton',
    21: 'Ice Troll',
    22: 'Worgen',
    23: 'Gilnean',
    24: 'Pandaren (Neutral)',
    25: 'Pandaren (Alliance)',
    26: 'Pandaren (Horde)',
    27: 'Nightborne',
    28: 'Highmountain Tauren',
    29: 'Void Elf',
    30: 'Lightforged Draenei',
    31: 'Zandalari Troll',
    32: 'Kul Tiran',
    33: 'Thin Human',
    34: 'Dark Iron Dwarf',
    35: 'Vulpera',
    36: 'Mag\'har Orc',
    37: 'Mechagnome'
}

const challengeModeItemBonusOverrideType = {
    0: 'Mythic+',
    1: 'PvP'
}

const textureType = {
    0: 'InFile',
    1: 'Skin',
    2: 'Object Skin',
    3: 'Weapon Blade',
    4: 'Weapon Handle',
    5: '(OBSOLETE) Environment',
    6: 'Hair',
    7: '(OBSOLETE) Facial Hair',
    8: 'Skin Extra',
    9: 'UI Skin',
    10: 'Tauren Mane',
    11: 'Monster Skin 1',
    12: 'Monster Skin 2',
    13: 'Monster Skin 3',
    14: 'Item Icon',
    15: 'Guild BG Color',
    16: 'Guild Emblem Color',
    17: 'Guild Border Color',
    18: 'Guild Emblem',
    19: 'Eyes',
    20: 'Accessory',
    21: 'Secondary Skin',
    22: 'Secondary Hair',
}

const chrModelMaterialSkinType = {
    0: 'Primary Skin',
    1: 'Secondary Skin',
}

const inventoryTypeEnum = {
    0: 'Non-equippable',
    1: 'Head',
    2: 'Neck',
    3: 'Shoulder',
    4: 'Shirt',
    5: 'Chest',
    6: 'Waist',
    7: 'Legs',
    8: 'Feet',
    9: 'Wrist',
    10: 'Hands',
    11: 'Finger',
    12: 'Trinket',
    13: 'One-Hand',
    14: 'Off Hand',
    15: 'Ranged',
    16: 'Back',
    17: 'Two-Hand',
    18: 'Bag',
    19: 'Tabard',
    20: 'Chest',
    21: 'Main Hand',
    22: 'Off Hand',
    23: 'Held in Off-hand',
    24: 'Ammo',
    25: 'Thrown',
    26: 'Ranged',
    27: 'Quiver',
    28: 'Relic'
}

// From ItemClass table -- by ClassID (not ID)
const itemClassEnum = {
    0: 'Consumable',
    1: 'Container',
    2: 'Weapon',
    3: 'Gem',
    4: 'Armor',
    5: 'Reagent',
    6: 'Projectile',
    7: 'Tradeskill',
    8: 'Item Enhancement',
    9: 'Recipe',
    10: 'Money (OBSOLETE)',
    11: 'Quiver',
    12: 'Quest',
    13: 'Key',
    14: 'Permanent (OBSOLETE)',
    15: 'Miscellaneous',
    16: 'Glyph',
    17: 'Battle Pets',
    18: 'WoW Token'
}

let itemSubClass = [];
itemSubClass[0] = {
    0: 'Explosives and Devices',
    1: 'Potion',
    2: 'Elixir',
    3: 'Flask',
    4: 'Scroll (OBSOLETE)',
    5: 'Food & Drink',
    6: 'Item Enhancement (OBSOLETE)',
    7: 'Bandage',
    8: 'Other',
    9: 'Vantus Rune'
}

itemSubClass[1] = {
    0: 'Bag',
    1: 'Soul Bag',
    2: 'Herb Bag',
    3: 'Enchanting Bag',
    4: 'Engineering Bag',
    5: 'Gem Bag',
    6: 'Mining Bag',
    7: 'Leatherworking Bag',
    8: 'Inscription Bag',
    9: 'Tackle Box',
    10: 'Cooking Bag'
}

itemSubClass[2] = {
    0: 'Axe',
    1: 'Axe', //2H
    2: 'Bow',
    3: 'Gun',
    4: 'Mace',
    5: 'Mace', //2H
    6: 'Polearm',
    7: 'Sword',
    8: 'Sword', //2H
    9: 'Warglaives',
    10: 'Staff',
    11: 'Bear Claws',
    12: 'Cat Claws',
    13: 'Fist Weapon',
    14: 'Miscellaneous',
    15: 'Dagger',
    16: 'Thrown',
    17: 'Spear',
    18: 'Crossbow',
    19: 'Wand',
    20: 'Fishing Pole'
}

itemSubClass[3] = {
    0: 'Intellect',
    1: 'Agility',
    2: 'Strength',
    3: 'Stamina',
    4: 'Spirit',
    5: 'Critical Strike',
    6: 'Mastery',
    7: 'Haste',
    8: 'Versatility',
    9: 'Other',
    10: 'Multiple Stats',
    11: 'Artifact Relic'
}

itemSubClass[4] = {
    0: 'Miscellaneous',
    1: 'Cloth',
    2: 'Leather',
    3: 'Mail',
    4: 'Plate',
    5: 'Cosmetic',
    6: 'Shield',
    7: 'Libram',
    8: 'Idol',
    9: 'Totem',
    10: 'Sigil',
    11: 'Relic'
}

itemSubClass[5] = {
    0: 'Reagent',
    1: 'Keystone',
    2: 'Context Token'
}

itemSubClass[6] = {
    0: 'Wand(OBSOLETE)',
    1: 'Bolt(OBSOLETE)',
    2: 'Arrow',
    3: 'Bullet',
    4: 'Thrown(OBSOLETE)'
}

itemSubClass[7] = {
    0: 'Trade Goods (OBSOLETE)',
    1: 'Parts',
    2: 'Explosives (OBSOLETE)',
    3: 'Devices (OBSOLETE)',
    4: 'Jewelcrafting',
    5: 'Cloth',
    6: 'Leather',
    7: 'Metal & Stone',
    8: 'Cooking',
    9: 'Herb',
    10: 'Elemental',
    11: 'Other',
    12: 'Enchanting',
    13: 'Materials (OBSOLETE)',
    14: 'Item Enchantment (OBSOLETE)',
    15: 'Weapon Enchantment - Obsolete',
    16: 'Inscription',
    17: 'Explosives and Devices (OBSOLETE)',
    18: 'Optional Reagents'
}

itemSubClass[8] = {
    0: 'Head',
    1: 'Neck',
    2: 'Shoulder',
    3: 'Cloak',
    4: 'Chest',
    5: 'Wrist',
    6: 'Hands',
    7: 'Waist',
    8: 'Legs',
    9: 'Feet',
    10: 'Finger',
    11: 'Weapon',
    12: 'Two-Handed Weapon',
    13: 'Shield/Off-hand',
    14: 'Misc'
}

itemSubClass[9] = {
    0: 'Book',
    1: 'Leatherworking',
    2: 'Tailoring',
    3: 'Engineering',
    4: 'Blacksmithing',
    5: 'Cooking',
    6: 'Alchemy',
    7: 'First Aid',
    8: 'Enchanting',
    9: 'Fishing',
    10: 'Jewelcrafting',
    11: 'Inscription'
}

itemSubClass[10] = {
    0: 'Money(OBSOLETE)',
}

itemSubClass[11] = {
    0: 'Quiver(OBSOLETE)',
    1: 'Bolt(OBSOLETE)',
    2: 'Quiver',
    3: 'Ammo Pouch'
}

itemSubClass[12] = {
    0: 'Quest'
}

itemSubClass[13] = {
    0: 'Key',
    1: 'Lockpick'
}

itemSubClass[14] = {
    0: 'Permanent'
}

itemSubClass[15] = {
    0: 'Junk',
    1: 'Reagent',
    2: 'Companion Pets',
    3: 'Holiday',
    4: 'Other',
    5: 'Mount',
    6: 'Mount Equipment'
}

itemSubClass[16] = {
    1: 'Warrior',
    2: 'Paladin',
    3: 'Hunter',
    4: 'Rogue',
    5: 'Priest',
    6: 'Death Knight',
    7: 'Shaman',
    8: 'Mage',
    9: 'Warlock',
    10: 'Monk',
    11: 'Druid',
    12: 'Demon Hunter'
}

itemSubClass[17] = {
    0: 'BattlePet'
}

itemSubClass[18] = {
    0: 'WoW Token'
}

const itemEffectTriggerType = {
    0: 'Use',
    1: 'On Equip',
    2: 'Chance on Hit',
    // 3: 'UNKNOWN', // Only on 23442
    4: 'Soulstone',
    5: 'While carrying',
    6: 'Learn Spell',
    7: 'When obtained',
}

const uiMapSystem = {
    0: 'World',
    1: 'Taxi',
    2: 'Adventure',
    3: 'Minimap'
}

const garrAbilityAction = {
    0: 'COUNTER_MECHANIC',
    1: 'SOLO_MISSION',
    2: 'MOD_SUCCESS_CHANCE',
    3: 'MOD_TRAVEL_TIME',
    4: 'MOD_XP',
    5: 'FRIENDLY_RACE',
    6: 'LONG_MISSION',
    7: 'SHORT_MISSION',
    8: 'MOD_CURRENCY',
    9: 'LONG_TRAVEL',
    10: 'SHORT_TRAVEL',
    11: 'MOD_BIAS',
    12: 'PROFESSION',
    13: 'MOD_BRONZE_LOOT_CHANCE',
    14: 'MOD_SILVER_LOOT_CHANCE',
    15: 'MOD_GOLD_LOOT_CHANCE',
    16: 'MOD_ALL_LOOT_CHANCE',
    17: 'MOD_MISSION_TIME',
    18: 'MENTORING',
    19: 'MOD_GOLD',
    20: 'PREVENT_DEATH',
    21: 'TREASURE_ON_MISSION_SUCCESS',
    22: 'FRIENDLY_CLASS',
    23: 'ADVANTAGE_MECHANIC',
    24: 'MOD_SUCCESS_PER_DURABILITY',
    25: 'MOD_SUCCESS_DURABILITY_IN_RANGE',
    26: 'FRIENDLY_FOLLOWER',
    27: 'KILL_TROOPS',
    28: 'MOD_DURABILITY_COST',
    29: 'MOD_BONUS_LOOT_CHANCE',
    30: 'MOD_XP_FLAT',
    31: 'MOD_ITEMLEVEL',
    32: 'MOD_STARTING_DURABILITY',
    33: 'UNIQUE_TROOPS',
    34: 'MOD_CLASSSPEC_LIMIT',
    35: 'TROOP_RESURRECTION',
    36: 'MOD_COST_BY_RACE',
    37: 'REWARD_ON_WORLD_QUEST_COMPLETE',
    38: 'MOD_SUCCESS_BY_MISSIONS_IN_PROGRESS',
    39: 'MOD_MISSION_COST',
    40: 'MOD_SUCCESS_IF_RARE_MISSION',
    41: 'SOLO_CHAMPION',
}

const garrAbilityTargetType = {
    0: 'None',
    1: 'Self',
    2: 'Party',
    3: 'Race',
    4: 'Class',
    5: 'Gender',
    6: 'Profession',
    7: 'NotSelf',
    8: 'NotRace',
    9: 'NotClass',
    10: 'NotProfession'
}

const mawPowerRarity = {
    1: 'Common',
    2: 'Uncommon',
    3: 'Rare',
    4: 'Epic'
}

const spellVisualEffectNames = {
    0: "FileDataID",            // Use value from SpellVisualEffectName::ModelFileDataID
    1: "Item",                  // Item::ID
    2: "CreatureDisplayInfo",   // CreatureDisplayInfo::ID
    // 3: "",
    // 4: "",
    // 5: "",
    // 6: "",
    // 7: "",
    // 8: "",
    // 9: "",
    // 10: ""
}

const itemQuality = {
    0: 'Poor',
    1: 'Common',
    2: 'Uncommon',
    3: 'Rare',
    4: 'Epic',
    5: 'Legendary',
    6: 'Artifact',
    7: 'Heirloom',
    8: 'WoW Token'
}

const spellItemEnchantmentEffect = {
    1: 'Proc',
    2: 'Damage',
    3: 'Buff',
    4: 'Armor',
    5: 'Stat',
    6: 'Totem',
    7: 'Use Spell',
    8: 'Prismatic socket'
}

const itemContext = {
    1: "Normal Dungeon",
    2: "Heroic Dungeon",
    3: "Normal Raid",
    4: "LFR",
    5: "Heroic Raid",
    6: "Mythic Raid",
    7: "Unranked PvP 1",
    8: "Ranked PvP 1",
    9: "Normal Scenario",
    10: "Heroic Scenario",
    11: "Quest Reward",
    12: "Store",
    13: "Tradeskill",
    14: "Vendor",
    15: "Black Market",
    16: "Mythic Keystone Challenge 1",
    17: "Dungeon level up 1",
    18: "Dungeon level up 2",
    19: "Dungeon level up 3",
    20: "Dungeon level up 4",
    21: "None (forced)",
    22: "Timewalking",
    23: "Mythic Dungeon",
    24: "PvP (honor reward)",
    25: "World Quest 1",
    26: "World Quest 2",
    27: "World Quest 3",
    28: "World Quest 4",
    29: "World Quest 5",
    30: "World Quest 6",
    31: "Mission Reward 1",
    32: "Mission Reward 2",
    33: "Mythic Keystone Challenge 2",
    34: "Mythic Keystone Challenge 3",
    35: "Mythic Keystone Challenge Jackpot",
    36: "World Quest 7",
    37: "World Quest 8",
    38: "Ranked PvP 2",
    39: "Ranked PvP 3",
    40: "Ranked PvP 4",
    41: "Unranked PvP 2",
    42: "World Quest 9",
    43: "World Quest 10",
    44: "Ranked PvP 5",
    45: "Ranked PvP 6",
    46: "Ranked PvP 7",
    47: "Unranked PvP 3",
    48: "Unranked PvP 4",
    49: "Unranked PvP 5",
    50: "Unranked PvP 6",
    51: "Unranked PvP 7",
    52: "Ranked PvP 8",
    53: "World Quest 11",
    54: "World Quest 12",
    55: "World Quest 13",
    56: "Ranked PvP Jackpot",
    57: "Tournament Realm",
    58: "Relinquished",
    59: "Legendary Forge",
    60: "Quest Bonus Loot",
    61: "Character Boost 1",
    62: "Character Boost 2",
    63: "Legendary Crafting 1",
    64: "Legendary Crafting 2",
    65: "Legendary Crafting 3",
    66: "Legendary Crafting 4",
    67: "Legendary Crafting 5",
    68: "Legendary Crafting 6",
    69: "Legendary Crafting 7",
    70: "Legendary Crafting 8",
    71: "Legendary Crafting 9",
    72: "Weekly Rewards (additional)",
    73: "Weekly Rewards (concession)",
    74: "World Quest Jackpot",
    75: "New Character",
    76: "Warmode",
    77: "PvP Brawl 1",
    78: "PvP Brawl 2",
    79: "Torghast",
    80: "Corpse Recovery",
    81: "World Boss",
    82: "Normal Raid (extended)",
    83: "LFR (extended)",
    84: "Heroic Raid (extended)",
    85: "Mythic Raid (extended)",
}

const environmentalDamageType = {
    0: 'FATIGUE',
    1: 'DROWNING',
    2: 'FALLING',
    3: 'LAVA',
    4: 'SLIME',
    5: 'FIRE',
}

const garrAutoCombatantRole = {
    0: 'NONE',
    1: 'MELEE',
    2: 'RANGED_PHYSICAL',
    3: 'RANGED_MAGIC',
    4: 'HEAL_SUPPORT',
    5: 'TANK',
}

const garrAutoSpellEffectType = {
    0: 'NONE',
    1: 'DAMAGE',
    2: 'HEAL',
    3: 'DAMAGE_PCT',
    4: 'HEAL_PCT',
    5: 'DOT',
    6: 'HOT',
    7: 'DOT_PCT',
    8: 'HOT_PCT',
    9: 'TAUNT',
    10: 'DETAUNT',
    11: 'MOD_DAMAGE_DONE',
    12: 'MOD_DAMAGE_DONE_PCT',
    13: 'MOD_DAMAGE_TAKEN',
    14: 'MOD_DAMAGE_TAKEN_PCT',
    15: 'DEAL_DAMAGE_TO_ATTACKER',
    16: 'DEAL_DAMAGE_TO_ATTACKER_PCT',
    17: 'INCREASE_MAX_HEALTH',
    18: 'INCREASE_MAX_HEALTH_PCT',
    19: 'MOD_DAMAGE_DONE_PCT_OF_FLAT',
    20: 'MOD_DAMAGE_TAKEN_PCT_OF_FLAT',
}

const garrAutoSpellTarget = {
    0: 'NONE',
    1: 'SELF',
    2: 'ADJACENT_FRIENDLY',
    3: 'ADJACENT_HOSTILE',
    4: 'RANGED_FRIENDLY',
    5: 'RANGED_HOSTILE',
    6: 'ALL_FRIENDLIES',
    7: 'ALL_HOSTILES',
    8: 'ALL_ADJACENT_FRIENDLIES',
    9: 'ALL_ADJACENT_HOSTILES',
    10: 'CONE_FRIENDLIES',
    11: 'CONE_HOSTILES',
    12: 'LINE_FRIENDLIES',
    13: 'LINE_HOSTILES',
    14: 'ALL_FRONT_ROW_FRIENDLIES',
    15: 'ALL_FRONT_ROW_HOSTILES',
    16: 'ALL_BACK_ROW_FRIENDLIES',
    17: 'ALL_BACK_ROW_HOSTILES',
    18: 'ALL_TARGETS',
    19: 'RANDOM_TARGET',
    20: 'RANDOM_ALLY',
    21: 'RANDOM_ENEMY',
    22: 'ALL_FRIENDLIES_BUT_SELF',
}

const garrBuildingType = {
    0: 'NONE',
    1: 'MINE',
    2: 'FARM',
    3: 'BARN',
    4: 'LUMBER_MILL',
    5: 'INN',
    6: 'TRADING_POST',
    7: 'PET_MENAGERIE',
    8: 'BARRACKS',
    9: 'SHIPYARD',
    10: 'ARMORY',
    11: 'STABLE',
    12: 'ACADEMY',
    13: 'MAGE_TOWER',
    14: 'SALAVAGE_YARD',
    15: 'STOREHOUSE',
    16: 'ALCHEMY',
    17: 'BLACKSMITH',
    18: 'ENCHANTING',
    19: 'ENGINEERING',
    20: 'INSCRIPTION',
    21: 'JEWELCRAFTING',
    22: 'LEATHERWORKING',
    23: 'TAILORING',
    24: 'FISHING',
    25: 'SPARRING_ARENA',
    26: 'WORKSHOP',
}

const garrFollowerItemSlot = {
    0: 'MAINHAND',
    1: 'OFFHAND',
    2: 'ARMOR'
}

const garrFollowerQuality = {
    1: 'COMMON',
    2: 'UNCOMMON',
    3: 'RARE',
    4: 'EPIC',
    5: 'LEGENDARY',
    6: 'TITLE',
}

const garrMechanicCategory = {
    0: 'ENVIRONMENT',
    1: 'ENEMY_RACE',
    2: 'ENCOUNTER'
}

const garrSpecType = {
    0: 'REDUCE_TRAVEL_TIME',
    1: 'STABLE_EXTRA_MOUNTS',
    2: 'RECALL_FOLLOWERS',
    3: 'GENERATE_ITEM_RECURRING',
    4: 'RECOVER_FOLLOWER',
    5: 'INCREASED_HEALTH',
    6: 'FOLLOWER_DISCOVERY_CHANCE_INCREASE',
    7: 'INCREASE_GATHERING_RATE',
    8: 'MENAGERIE_EXTRA_PETS',
    9: 'COST_MULTIPLIER',
}

const garrTalentCostType = {
    0: 'INITIAL',
    1: 'RESPEC',
    2: 'MAKE_PERMANENT',
    3: 'TREE_RESET',
}

const itemSlot = {
    0: 'HEAD',
    1: 'SHOULDER',
    2: 'SHIRT',
    3: 'ARMOR',
    4: 'WAIST',
    5: 'LEGS',
    6: 'FEET',
    7: 'WRIST',
    8: 'HAND',
    9: 'TABARD',
    10: 'CAPE',
    11: 'QUIVER'
}

const uiWidgetScale = {
    0: '100',
    1: '90',
    2: '80',
    3: '70',
    4: '60',
    5: '50'
}

const questTagType = {
    0: 'TAG',
    1: 'PROFESSION',
    2: 'NORMAL',
    3: 'PVP',
    4: 'PET_BATTLE',
    5: 'BOUNTY',
    6: 'DUNGEON',
    7: 'INVASION',
    8: 'RAID',
    9: 'CONTRIBUTION',
    10: 'RATED_REWARD',
    11: 'INVASION_WRAPPER',
    12: 'FACTION_ASSAULT',
    13: 'ISLANDS',
    14: 'THREAT',
    15: 'COVENANT_CALLING'
}

const questObjectiveType = {
    0: 'KILL',
    1: 'COLLECT',
    2: 'INTERACT_DOODAD',
    3: 'INTERACT_UNIT',
    4: 'GET_CURRENCY',
    5: 'LEARN_SPELL',
    6: 'FACTION_MIN',
    7: 'FACTION_MAX',
    8: 'PAY_MONEY',
    9: 'KILL_PLAYERS',
    10: 'AREA_TRIGGER_DEPRECATED',
    11: 'DEFEAT_BATTLEPET_NPC',
    12: 'DEFEAT_BATTLEPET',
    13: 'DEFEAT_BATTLEPET_PVP',
    14: 'CRITERIA_TREE',
    15: 'PROGRESS_BAR',
    16: 'REACH_CURRENCY',
    17: 'INCREASE_CURRENCY',
    18: 'AREA_TRIGGER_ENTER',
    19: 'AREA_TRIGGER_EXIT'
}

const transmogSourceTypeEnum = {
    0: 'NONE',
    1: 'JOURNAL_ENCOUNTER',
    2: 'QUEST',
    3: 'VENDOR',
    4: 'WORLD_DROP',
    5: 'HIDDEN_UNTIL_COLLECTED',
    6: 'CANT_COLLECT',
    7: 'ACHIEVEMENT',
    8: 'PROFESSION',
    9: 'NOT_VALID_FOR_TRANSMOG'
}

const itemModification = {
    0: 'TRANSMOGRIFY_ITEM_MODIFIED_APPEARANCE_ID_SPEC_ALL',
    1: 'TRANSMOGRIFY_ITEM_MODIFIED_APPEARANCE_ID_SPEC_0',
    2: 'INCREMENT_LEVEL_OBSOLETE',
    3: 'BATTLE_PET_SPECIES',
    4: 'BATTLE_PET_BREED',
    5: 'BATTLE_PET_LEVEL',
    6: 'BATTLE_PET_CREATUREDISPLAYID',
    7: 'TRANSMOGRIFY_OVERRIDE_ENCHANT_VISUAL_ID_SPEC_ALL',
    8: 'ARTIFACT_APPEARANCE_ID',
    9: 'TIMEWALKER_LEVEL',
    10: 'TRANSMOGRIFY_OVERRIDE_ENCHANT_VISUAL_ID_SPEC_0',
    11: 'TRANSMOGRIFY_ITEM_MODIFIED_APPEARANCE_ID_SPEC_1',
    12: 'TRANSMOGRIFY_OVERRIDE_ENCHANT_VISUAL_ID_SPEC_1',
    13: 'TRANSMOGRIFY_ITEM_MODIFIED_APPEARANCE_ID_SPEC_2',
    14: 'TRANSMOGRIFY_OVERRIDE_ENCHANT_VISUAL_ID_SPEC_2',
    15: 'TRANSMOGRIFY_ITEM_MODIFIED_APPEARANCE_ID_SPEC_3',
    16: 'TRANSMOGRIFY_OVERRIDE_ENCHANT_VISUAL_ID_SPEC_3',
    17: 'KEYSTONE_MAP_CHALLENGE_MODE_ID',
    18: 'KEYSTONE_POWER_LEVEL',
    19: 'KEYSTONE_AFFIX0',
    20: 'KEYSTONE_AFFIX01',
    21: 'KEYSTONE_AFFIX02',
    22: 'KEYSTONE_AFFIX03',
    23: 'LEGION_ARTIFACT_KNOWLEDGE_OBSOLETE',
    24: 'ARTIFACT_TIER',
    25: 'TRANSMOGRIFY_ITEM_MODIFIED_APPEARANCE_ID_SPEC_4',
    26: 'PVP_RATING',
    27: 'TRANSMOGRIFY_OVERRIDE_ENCHANT_VISUAL_ID_SPEC_4',
    28: 'CONTENT_TUNING_ID',
    29: 'CHANGE_MODIFIED_CRAFTING_STAT_1',
    30: 'CHANGE_MODIFIED_CRAFTING_STAT_2',
    31: 'TRANSMOGRIFY_SECONDARY_ITEM_MODIFIED_APPEARANCE_ID_SPEC_ALL',
    32: 'TRANSMOGRIFY_SECONDARY_ITEM_MODIFIED_APPEARANCE_ID_SPEC_0',
    33: 'TRANSMOGRIFY_SECONDARY_ITEM_MODIFIED_APPEARANCE_ID_SPEC_1',
    34: 'TRANSMOGRIFY_SECONDARY_ITEM_MODIFIED_APPEARANCE_ID_SPEC_2',
    35: 'TRANSMOGRIFY_SECONDARY_ITEM_MODIFIED_APPEARANCE_ID_SPEC_3',
    36: 'TRANSMOGRIFY_SECONDARY_ITEM_MODIFIED_APPEARANCE_ID_SPEC_4',
    37: 'SOULBIND_CONDUIT_RANK'
}

// Regular enums
let enumMap = new Map();
enumMap.set("map.ExpansionID", expansionLevels);
enumMap.set("map.InstanceType", mapTypes);
enumMap.set("difficulty.InstanceType", mapTypes);
enumMap.set("playercondition.MinReputation[0]", reputationLevels);
enumMap.set("itembonus.Type", itemBonusTypes);
enumMap.set("criteriatree.Operator", criteriaTreeOperator);
enumMap.set("criteria.Type", criteriaType);
enumMap.set("modifiertree.Operator", modifierTreeOperator);
enumMap.set("modifiertree.Type", criteriaAdditionalCondition);
enumMap.set("spelleffect.Effect", spellEffectName);
enumMap.set("spelleffect.EffectAura", effectAuraType);
enumMap.set("charsections.BaseSection", charSectionType);
enumMap.set("charsections.SexID", charSex);
enumMap.set("charsectioncondition.BaseSection", charSectionType);
enumMap.set("charsectioncondition.Sex", charSex);
enumMap.set("uimap.Type", uiMapType);
enumMap.set("soundkit.SoundType", soundkitSoundType);
enumMap.set("itemdisplayinfomaterialres.ComponentSection", componentSection);
enumMap.set("charcomponenttexturesections.SectionType", componentSection);
enumMap.set("chrcustomization.ComponentSection[0]", componentSection);
enumMap.set("chrcustomization.ComponentSection[1]", componentSection);
enumMap.set("chrcustomization.ComponentSection[2]", componentSection);
enumMap.set("charhairgeosets.GeosetType", geosetType);
enumMap.set("chrcustomizationgeoset.GeosetType", geosetType);
enumMap.set("chrcustomizationskinnedmodel.GeosetType", geosetType);
enumMap.set("chrcustomizationreq.ReqType", chrCustomizationReqType);
enumMap.set("chrcustomization.UiCustomizationType", uiCustomizationType);
enumMap.set("spellvisualkiteffect.EffectType", spellVisualKitEffectType);
enumMap.set("spelllabel.LabelID", spellLabelName);
enumMap.set("spellclassoptions.SpellClassSet", spellClassSet);
enumMap.set("challengemodeitembonusoverride.Type", challengeModeItemBonusOverrideType);
enumMap.set("item.InventoryType", inventoryTypeEnum);
enumMap.set("item.ClassID", itemClassEnum);
enumMap.set("uimap.System", uiMapSystem);
enumMap.set("garrabilityeffect.AbilityAction", garrAbilityAction);
enumMap.set("garrabilityeffect.AbilityTargetType", garrAbilityTargetType);
enumMap.set("chrmodel.BaseRaceChrModelID", tempChrModelIDEnum);
enumMap.set("chrcustomizationoption.ChrModelID", tempChrModelIDEnum);
enumMap.set("chrracexchrmodel.ChrModelID", tempChrModelIDEnum);
enumMap.set("chrmodeltexturelayer.TextureType", textureType);
enumMap.set("chrmodelmaterial.TextureType", textureType);
enumMap.set("chrmodelmaterial.SkinType", chrModelMaterialSkinType);
enumMap.set("itemeffect.TriggerType", itemEffectTriggerType);
enumMap.set("mawpower.MawPowerRarityID", mawPowerRarity);
enumMap.set("spellvisualeffectname.Type", spellVisualEffectNames);
enumMap.set("spellitemenchantment.Effect[0]", spellItemEnchantmentEffect);
enumMap.set("spellitemenchantment.Effect[1]", spellItemEnchantmentEffect);
enumMap.set("spellitemenchantment.Effect[2]", spellItemEnchantmentEffect);
enumMap.set("itembonustreenode.ItemContext", itemContext);
enumMap.set("environmentaldamage.EnumID", environmentalDamageType);
enumMap.set("garrautocombatant.Role", garrAutoCombatantRole);
enumMap.set("garrautospelleffect.EffectType", garrAutoSpellEffectType);
enumMap.set("garrautospelleffect.Targets", garrAutoSpellTarget);
enumMap.set("garrbuilding.BuildingType", garrBuildingType);
enumMap.set("garrfollitemsetmember.ItemSlot", garrFollowerItemSlot);
enumMap.set("garrfollowerquality.Quality", garrFollowerQuality);
enumMap.set("garrmechanictype.Category", garrMechanicCategory);
enumMap.set("garrspecialization.BuildingType", garrBuildingType);
enumMap.set("garrspecialization.SpecType", garrSpecType);
enumMap.set("garrtalentcost.CostType", garrTalentCostType);
enumMap.set("npcmodelitemslotdisplayinfo.ItemSlot", itemSlot);
enumMap.set("uiwidgetvisualization.WidgetScale", uiWidgetScale);
enumMap.set("questinfo.Type", questTagType);
enumMap.set("questobjective.Type", questObjectiveType);
enumMap.set("itemmodifiedappearance.TransmogSourceTypeEnum", transmogSourceTypeEnum);

/* Race IDs */
enumMap.set("chrracexchrmodel.ChrRacesID", tempChrRaceIDEnum);
enumMap.set("charvariations.RaceID", tempChrRaceIDEnum);
enumMap.set("gluescreenemote.RaceID", tempChrRaceIDEnum);
enumMap.set("chrraceracialability.ChrRacesID", tempChrRaceIDEnum);
enumMap.set("chrcustomizationconversion.ChrRacesID", tempChrRaceIDEnum);
enumMap.set("soundcharactermacrolines.Race", tempChrRaceIDEnum);
enumMap.set("charstartkit.ChrRacesID", tempChrRaceIDEnum);
enumMap.set("helmetgeosetdata.RaceID", tempChrRaceIDEnum);
enumMap.set("characterfacialhairstyles.RaceID", tempChrRaceIDEnum);
enumMap.set("charbaseinfo.RaceID", tempChrRaceIDEnum);
enumMap.set("helmetanimscaling.RaceID", tempChrRaceIDEnum);
enumMap.set("uicamfbacktransmogchrrace.ChrRaceID", tempChrRaceIDEnum);
enumMap.set("chrcustomization.RaceID", tempChrRaceIDEnum);
enumMap.set("namegen.RaceID", tempChrRaceIDEnum);
enumMap.set("charstartoutfit.RaceID", tempChrRaceIDEnum);
enumMap.set("alliedrace.RaceID", tempChrRaceIDEnum);
enumMap.set("emotestextsound.RaceID", tempChrRaceIDEnum);
enumMap.set("charsections.RaceID", tempChrRaceIDEnum);
enumMap.set("barbershopstyle.Race", tempChrRaceIDEnum);
enumMap.set("creaturedisplayinfoextra.DisplayRaceID", tempChrRaceIDEnum);
enumMap.set("charhairgeosets.RaceID", tempChrRaceIDEnum);
enumMap.set("chrraces.UnalteredVisualRaceID", tempChrRaceIDEnum);
enumMap.set("chrraces.NeutralRaceID", tempChrRaceIDEnum);

for (let i = 0; i < 8; i++){
    enumMap.set("unitcondition.Variable[" + i + "]", unitConditionVariable);
    enumMap.set("unitcondition.Op[" + i + "]", unitConditionOperator);
}

// Conditional enums
let conditionalEnums = new Map();
conditionalEnums.set("itembonus.Value[0]",
    [
        ['itembonus.Type=2', itemStatType]
    ]
);

conditionalEnums.set("item.SubclassID",
    [
        ['item.ClassID=0',  itemSubClass[0]],
        ['item.ClassID=1',  itemSubClass[1]],
        ['item.ClassID=2',  itemSubClass[2]],
        ['item.ClassID=3',  itemSubClass[3]],
        ['item.ClassID=4',  itemSubClass[4]],
        ['item.ClassID=5',  itemSubClass[5]],
        ['item.ClassID=6',  itemSubClass[6]],
        ['item.ClassID=7',  itemSubClass[7]],
        ['item.ClassID=8',  itemSubClass[8]],
        ['item.ClassID=9',  itemSubClass[9]],
        ['item.ClassID=10', itemSubClass[10]],
        ['item.ClassID=11', itemSubClass[11]],
        ['item.ClassID=12', itemSubClass[12]],
        ['item.ClassID=13', itemSubClass[13]],
        ['item.ClassID=14', itemSubClass[14]],
        ['item.ClassID=15', itemSubClass[15]],
        ['item.ClassID=16', itemSubClass[16]],
        ['item.ClassID=17', itemSubClass[17]],
        ['item.ClassID=18', itemSubClass[18]]
    ]
);

conditionalEnums.set("modifiertree.Asset",
    [
        ['modifiertree.Type=14', itemQuality],
        ['modifiertree.Type=15', itemQuality]
    ]
);

for (let i = 0; i < 3; i++){
    conditionalEnums.set("spellitemenchantment.EffectArg[" + i + "]",
        [
            ['spellitemenchantment.Effect[' + i + ']=5', itemStatType]
        ]
    );
}


// Conditional FKs (move to sep file?)
let conditionalFKs = new Map();
conditionalFKs.set("itembonus.Value[0]",
    [
        ['itembonus.Type=5','itemnamedescription::ID'],
        ['itembonus.Type=19','azeritetierunlockset::ID'],
        ['itembonus.Type=23','itemeffect::ID'],
        ['itembonus.Type=31','itemnamedescription::ID']
    ]
);

conditionalFKs.set("spelleffect.EffectMiscValue[0]",
    [
        ['spelleffect.EffectAura=56','creature::ID'],
        ['spelleffect.EffectAura=78','creature::ID'],
        ['spelleffect.Effect=28','creature::ID'],
        ['spelleffect.Effect=90','creature::ID'],
        ['spelleffect.Effect=131','soundkit::ID'],
        ['spelleffect.Effect=132','soundkit::ID'],
        ['spelleffect.Effect=134','creature::ID'],
        ['spelleffect.Effect=279','garrtalent::ID'],
    ]
);

conditionalFKs.set("spelleffect.EffectMiscValue[1]",
    [
        ['spelleffect.Effect=28','summonproperties::ID'],
    ]
);

conditionalFKs.set("criteria.Asset",
    [
        ['criteria.Type=0', 'creature::ID'],
        ['criteria.Type=2', 'researchproject::ID'],
        ['criteria.Type=1', 'map::ID'],
        ['criteria.Type=4', 'gameobjects::ID'],
        ['criteria.Type=7', 'skillline::ID'],
        ['criteria.Type=8', 'achievement::ID'],
        ['criteria.Type=11', 'areatable::ID'],
        ['criteria.Type=12', 'currencytypes::ID'],
        ['criteria.Type=15', 'map::ID'],
        ['criteria.Type=16', 'map::ID'],
        ['criteria.Type=20', 'creature::ID'],
        ['criteria.Type=21', 'criteria::ID'],
        ['criteria.Type=27', 'questv2::ID'],
        ['criteria.Type=28', 'spell::ID'],
        ['criteria.Type=29', 'spell::ID'],
        ['criteria.Type=30', 'pvpstat::ID'],
        ['criteria.Type=31', 'areatable::ID'],
        ['criteria.Type=32', 'map::ID'],
        ['criteria.Type=33', 'map::ID'],
        ['criteria.Type=34', 'spell::ID'],
        ['criteria.Type=36', 'item::ID'],
        ['criteria.Type=40', 'skilline::ID'],
        ['criteria.Type=41', 'item::ID'],
        ['criteria.Type=42', 'item::ID'],
        ['criteria.Type=43', 'areatable::ID'],
        ['criteria.Type=46', 'faction::ID'],
        ['criteria.Type=52', 'chrclasses::ID'],
        ['criteria.Type=53', 'chrraces::ID'],
        ['criteria.Type=54', 'emotes::ID'],
        ['criteria.Type=57', 'item::ID'],
        ['criteria.Type=58', 'questsort::ID'],
        ['criteria.Type=68', 'gameobjects::ID'],
        ['criteria.Type=69', 'spell::ID'],
        ['criteria.Type=71', 'map::ID'],
        ['criteria.Type=72', 'gameobjects::ID'],
        ['criteria.Type=75', 'skillline::ID'],
        ['criteria.Type=96', 'creature::ID'],
        ['criteria.Type=97', 'dungeonencounter::ID'],
        ['criteria.Type=110', 'spell::ID'],
        ['criteria.Type=112', 'skillline::ID'],
        ['criteria.Type=152', 'scenario::ID'],
        ['criteria.Type=163', 'areatable::ID'],
        ['criteria.Type=164', 'areatable::ID'],
        ['criteria.Type=165', 'dungeonencounter::ID'],
        ['criteria.Type=167', 'garrbuilding::ID'],
        ['criteria.Type=169', 'garrbuilding::ID'],
        ['criteria.Type=172', 'garrmission::ID'],
        ['criteria.Type=174', 'garrmission::ID'],
        ['criteria.Type=176', 'garrfollower::ID'],
        ['criteria.Type=202', 'garrtalent::ID'],
        ['criteria.Type=204', 'transmogset::ID'],
        ['criteria.Type=205', 'transmogset::ID'],
        ['criteria.Type=211', 'artifactpower::ID'],
        ['criteria.Type=225', 'areatable::ID'],
    ]
);

conditionalFKs.set("spellvisualkiteffect.Effect",
    [
        ['spellvisualkiteffect.EffectType=1','spellproceduraleffect::ID'],
        ['spellvisualkiteffect.EffectType=2','spellvisualkitmodelattach::ID'],
        ['spellvisualkiteffect.EffectType=3','cameraeffect::ID'],
        ['spellvisualkiteffect.EffectType=4','cameraeffect::ID'],
        ['spellvisualkiteffect.EffectType=5','soundkit::ID'],
        ['spellvisualkiteffect.EffectType=6','spellvisualanim::ID'],
        ['spellvisualkiteffect.EffectType=7','shadowyeffect::ID'],
        ['spellvisualkiteffect.EffectType=8','spelleffectemission::ID'],
        ['spellvisualkiteffect.EffectType=9','outlineeffect::ID'],
        ['spellvisualkiteffect.EffectType=11','dissolveeffect::ID'],
        ['spellvisualkiteffect.EffectType=12','edgegloweffect::ID'],
        ['spellvisualkiteffect.EffectType=13','beameffect::ID'],
        ['spellvisualkiteffect.EffectType=14','clientsceneeffect::ID'],
        ['spellvisualkiteffect.EffectType=15','cloneeffect::ID'],
        ['spellvisualkiteffect.EffectType=16','gradienteffect::ID'],
        ['spellvisualkiteffect.EffectType=17','barrageeffect::ID'],
        ['spellvisualkiteffect.EffectType=18','ropeeffect::ID'],
        ['spellvisualkiteffect.EffectType=19','spellvisualscreeneffect::ID'],
    ]
);

conditionalFKs.set("modifiertree.Asset",
    [
        ['modifiertree.Type=2','playercondition::ID'],
        ['modifiertree.Type=4','creature::ID'],
        ['modifiertree.Type=8','spell::ID'],
        ['modifiertree.Type=10','spell::ID'],
        ['modifiertree.Type=17','areatable::ID'],
        ['modifiertree.Type=18','areatable::ID'],
        ['modifiertree.Type=25','chrraces::ID'],
        ['modifiertree.Type=26','chrclasses::ID'],
        ['modifiertree.Type=27','chrraces::ID'],
        ['modifiertree.Type=28','chrclasses::ID'],
        ['modifiertree.Type=30','creaturetype::ID'],
        ['modifiertree.Type=31','creaturefamily::ID'],
        ['modifiertree.Type=32','map::ID'],
        ['modifiertree.Type=40','areatable::ID'],
        ['modifiertree.Type=41','areatable::ID'],
        ['modifiertree.Type=55','playercondition::ID'],
        ['modifiertree.Type=67','worldstateexpression::ID'],
        ['modifiertree.Type=68','mapdifficulty::ID'],
        ['modifiertree.Type=73','modifiertree::ID'],
        ['modifiertree.Type=74','scenario::ID'],
        ['modifiertree.Type=81','creature::ID'],
        ['modifiertree.Type=84','questv2::ID'],
        ['modifiertree.Type=85','faction::ID'],
        ['modifiertree.Type=87','achievement::ID'],
        ['modifiertree.Type=105','item::ID'],
        ['modifiertree.Type=107','spelllabel::LabelID'],
        ['modifiertree.Type=110','questv2::ID'],
        ['modifiertree.Type=111','questv2::ID'],
        ['modifiertree.Type=174','questv2::ID'],
        ['modifiertree.Type=202','garrtalent::ID'],
        ['modifiertree.Type=268','contenttuning::ID'],
        ['modifiertree.Type=272','contenttuning::ID'],
        ['modifiertree.Type=280','map::ID'],
        ['modifiertree.Type=303','runeforgelegendaryability::ID'],
        ['modifiertree.Type=306','achievement::ID'],
    ]
);

conditionalFKs.set("spellvisualeffectname.GenericID",
    [
        ['spellvisualeffectname.Type=1', 'item::ID'],
        ['spellvisualeffectname.Type=2', 'creaturedisplayinfo::ID']
    ]
);

conditionalFKs.set("questobjective.ObjectID",
    [
        ['questobjective.Type=0', 'creature::ID'],
        ['questobjective.Type=1', 'item::ID'],
        ['questobjective.Type=2', 'gameobjects::ID'],
        ['questobjective.Type=3', 'creature::ID'],
        ['questobjective.Type=4', 'gameobjects::ID'],
        ['questobjective.Type=11', 'creature::ID'],
        ['questobjective.Type=12', 'battlepetspecies::ID'],
        ['questobjective.Type=14', 'criteriatree::ID'],
        ['questobjective.Type=17', 'currencytypes::ID'],
        ['questobjective.Type=19', 'areatrigger::ID'],
        ['questobjective.Type=20', 'areatrigger::ID'],
    ]
);

for (let i = 0; i < 8; i++){
    conditionalFKs.set("unitcondition.Value[" + i + "]",
        [
            ['unitcondition.Variable[' + i + ']=76','spell::ID'],
        ]
    );
}

conditionalFKs.set("spellproceduraleffect.Value[0]",
    [
        ['spellproceduraleffect.Type=9', 'spellvisualkitareamodel::ID'],
        ['spellproceduraleffect.Type=26', 'spellchaineffects::ID'],
        ['spellproceduraleffect.Type=30', 'spellvisualcoloreffect::ID'],
    ]
);  

/* Colors */
let colorFields = new Array();
colorFields.push("chrcustomizationchoice.Color");
colorFields.push("lightdata.DirectColor");
colorFields.push("lightdata.AmbientColor");
colorFields.push("lightdata.SkyTopColor");
colorFields.push("lightdata.SkyMiddleColor");
colorFields.push("lightdata.SkyBand1Color");
colorFields.push("lightdata.SkyBand2Color");
colorFields.push("lightdata.SkySmogColor");
colorFields.push("lightdata.SkyFogColor");
colorFields.push("lightdata.SunColor");
colorFields.push("lightdata.CloudSunColor");
colorFields.push("lightdata.CloudEmissiveColor");
colorFields.push("lightdata.CloudLayer1AmbientColor");
colorFields.push("lightdata.CloudLayer2AmbientColor");
colorFields.push("lightdata.OceanCloseColor");
colorFields.push("lightdata.OceanFarColor");
colorFields.push("lightdata.RiverCloseColor");
colorFields.push("lightdata.RiverFarColor");
colorFields.push("lightdata.ShadowOpacity");
colorFields.push("weather.OverrideColor");
colorFields.push("highlightcolor.StartColor");
colorFields.push("highlightcolor.MidColor");
colorFields.push("highlightcolor.EndColor");
colorFields.push("lightning.FlashColor");
colorFields.push("lightning.BoltColor");
colorFields.push("liquidtype.MinimapStaticCol");
colorFields.push("itemnamedescription.Color");

/* Dates */
let dateFields = new Array();

for (let i = 0; i < 26; i++){
    dateFields.push("holidays.Date[" + i + "]");
}