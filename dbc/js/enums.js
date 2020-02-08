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

let enumMap = new Map();
enumMap.set("map.ExpansionID", expansionLevels);
enumMap.set("map.InstanceType", mapTypes);
enumMap.set("playercondition.MinReputation[0]", reputationLevels);