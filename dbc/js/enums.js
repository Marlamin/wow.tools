const reputationLevels = {
	0: 'None/Hated',
	1: 'Hostile',
	2: 'Unfriendly',
	3: 'Neutral',
	4: 'Friendly',
	5: 'Honored',
	6: 'Reverted',
	7: 'Exalted',
}

let enumMap = new Map();
enumMap.set("playercondition.MinReputation[0]", reputationLevels);