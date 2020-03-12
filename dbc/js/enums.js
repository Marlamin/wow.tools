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
	20: 'Unk',
	21: 'CanDisenchant',
	22: 'CanScrap',
	23: 'ItemEffectID',
}

let enumMap = new Map();
enumMap.set("map.ExpansionID", expansionLevels);
enumMap.set("map.InstanceType", mapTypes);
enumMap.set("playercondition.MinReputation[0]", reputationLevels);
enumMap.set("itembonus.Type", itemBonusTypes);