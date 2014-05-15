<?php // Run as a shell script: php dbgen.php

// Requires the following files from http://sourceforge.net/p/kolmafia/code/HEAD/tree/src/data/
//
// - items.txt
// - monsters.txt
// - restores.txt



function matchy($pattern, $subject)
{
	preg_match($pattern, $subject, $matches);
	return isset($matches[1]) ? $matches[1] : false;
}


// RESTORES

$restores = array();

$file = fopen("data/restores.txt", "r");
if($file) while (($line = fgets($file)) !== false)
{
	if($line === "" || $line[0] === '#') continue;
	$line = explode("\t", trim($line));

	if($line[1] != 'item') continue;

	$restore = array();

	if(!empty($line[3]))
		$restore['hp'] = $line[2].','.$line[3];
	if(!empty($line[5]))
		$restore['mp'] = $line[4].','.$line[5];

	if(!empty($restore))
		$restores[$line[0]] = $restore;
}
fclose($file);



// ITEMS

$allItems = array();
$idIndexed = array();
$descIdIndexed = array();

$file = fopen("data/items.txt", "r");
if($file) while (($line = fgets($file)) !== false)
{
	if($line === "" || $line[0] === '#') continue;
	$line = explode("\t", trim($line));

	if(@empty($line[1])) continue;

	$useableTypes = array('usable', 'multiple', 'reusable', 'hp', 'mp', 'hpmp');
	$equippableTypes = array('hat', 'pants', 'shirt', 'weapon', 'offhand', 'accessory');

	$types = explode(', ', $line[4]);
	$use = count(array_intersect($types, $useableTypes)) ? 1 : 0;
	$equip = count(array_intersect($types, $equippableTypes)) ? 1 : 0;

	$item = array(
		"id"		=> $line[0],
		"descid"	=> $line[2],
		"name"		=> $line[1],
		"image"		=> $line[3],
		"type"		=> explode(', ', $line[4]),
		"autosell"	=> $line[6],
		"use"		=> $use,
		"equip"		=> $equip,
	);

	if(isset($restores[$item['name']]['hp']))
	{
		$item['hp'] = $restores[$item['name']]['hp'];
	}
	if(isset($restores[$item['name']]['mp']))
	{
		$item['mp'] = $restores[$item['name']]['mp'];
	}

	$allItems		[]					= $item;
	$idIndexed		[$item['id']]		= $item;
	$descIdIndexed	[$item['descid']]	= $item;
}
fclose($file);



// MONSTERS

$allMonsters = array();

$file = fopen("data/monsters.txt", "r");
if($file) while (($line = fgets($file)) !== false)
{
	if($line === "" || $line[0] === '#') continue;
	$line = explode("\t", trim($line));

	if(@empty($line[0])) continue;

	$deets = $line[2];
	$atk	= matchy('/Atk:\s([0-9\-]+)/',	$deets);
	$def	= matchy('/Def:\s([0-9\-]+)/',	$deets);
	$hp		= matchy('/HP:\s([0-9\-]+)/',	$deets);
	$init	= matchy('/Init:\s([0-9\-]+)/',	$deets);
	$type	= matchy('/\sP:\s([a-z\-]+)/',	$deets);
	$elem	= matchy('/\sE:\s([a-z]+)/',	$deets);
	$meat	= matchy('/Meat:\s([0-9\-]+)/',	$deets);
	$boss	= matchy('/(BOSS)/',			$deets);

	$monster = array(
		"name"		=> $line[0],
		"image"		=> $line[1],
		"atk"		=> $atk,
		"def"		=> $def,
		"hp"		=> $hp,
		"init"		=> $init,
		"type"		=> $type,
		"elem"		=> $elem,
		"meat"		=> $meat ? $meat : 0,
		"boss"		=> $boss ? true : false,
		"items"		=> array_slice($line, 3),
	);

	$allMonsters[] = $monster;
}
fclose($file);


// FAMILIARS

$allFamiliars = array();

$file = fopen("data/familiars.txt", "r");
if($file) while (($line = fgets($file)) !== false)
{
	if($line === "" || $line[0] === '#') continue;
	$line = explode("\t", trim($line));

	if(@empty($line[0]) || @empty($line[1])) continue;

	$familiar = array(
		"id"		=> $line[0],
		"name"		=> $line[1],
		"image"		=> $line[2],
		"type"		=> explode(', ', $line[3]),
		"larva"		=> $line[4],
		"item"		=> $line[5],
		"arena"		=> $line[6].','.$line[7].','.$line[8].','.$line[9],
	);

	# Mafia doesn't include Throne/Bjorn data :(

	switch($familiar["name"])
	{
		case "Mosquito":
		case "Pair of Stomping Boots":
			$familiar["bjorn"] = "Weapon Damage +20%"; break;
		case "Grimstone Golem":
			$familiar["bjorn"] = "-5% Combat Frequency"; break;
		case "Grim Brother":
			$familiar["bjorn"] = "+5% Combat Frequency"; break;
		case "Pottery Barn Owl":
			$familiar["bjorn"] = "+10 Hot Damage"; break;
		case "El Vibrato Megadrone":
			$familiar["bjorn"] = "+10 Monster Level"; break;
		case "Spirit Hobo":
			$familiar["bjorn"] = "+15% Booze Drops"; break;
		case "Gluttonous Green Ghost":
			$familiar["bjorn"] = "+15% Food Drops"; break;
		case "Li'l Xenomorph":
		case "Feral Kobold":
			$familiar["bjorn"] = "+15% Item Drops"; break;
		case "Reassembled Blackbird":
		case "Reconstituted Crow":
		case "Oily Woim":
			$familiar["bjorn"] = "+10% Item Drops"; break;
		case "Slimeling":
			$familiar["bjorn"] = "+15% Weapon Drops"; break;
		case "Blood-Faced Volleyball":
		case "Cymbal-Playing Monkey":
		case "Hovering Skull":
		case "Jill-O-Lantern":
		case "Mariachi Chihuahua":
		case "Nervous Tick":
			$familiar["bjorn"] = "+2 Moxie Stats"; break;
		case "Chauvinist Pig":
		case "Grinning Turtle":
		case "Hunchbacked Minion":
		case "Killer Bee":
		case "Baby Mutant Rattlesnake":
			$familiar["bjorn"] = "+2 Muscle Stats"; break;
		case "Cheshire Bat":
		case "Dramatic Hedgehog":
		case "Hovering Sombrero":
		case "Pygmy Bugbear Shaman":
		case "Reanimated Reanimator":
		case "Sugar Fruit Fairy":
		case "Uniclops":
			$familiar["bjorn"] = "+2 Mysticality Stats"; break;
		case "Frumious Bandersnatch":
		case "Jack-in-the-Box":
		case "Purse Rat":
			$familiar["bjorn"] = "+2 Stats"; break;
		case "Frozen Gravy Fairy":
			$familiar["bjorn"] = "+20 Cold Damage"; break;
		case "Flaming Gravy Fairy":
		case "Mutant Fire Ant":
			$familiar["bjorn"] = "+20 Hot Damage"; break;
		case "Sleazy Gravy Fairy":
			$familiar["bjorn"] = "+20 Sleaze Damage"; break;
		case "Reagnimated Gnome":
			$familiar["bjorn"] = "+15 Spooky Damage"; break;
		case "Spooky Gravy Fairy":
			$familiar["bjorn"] = "+20 Spooky Damage"; break;
		case "Stinky Gravy Fairy":
			$familiar["bjorn"] = "+20 Stench Damage"; break;
		case "Attention-Deficit Demon":
		case "Casagnova Gnome":
		case "Coffee Pixie":
		case "Dancing Frog":
		case "Grouper Groupie":
		case "Hand Turkey":
		case "Jitterbug":
		case "Leprechaun":
		case "Mutant Cactus Bud":
		case "Obtuse Angel":
		case "Piano Cat":
		case "Psychedelic Bear":
		case "Hippo Ballerina":
			$familiar["bjorn"] = "+20% Meat"; break;
		case "Hobo Monkey":
		case "Knob Goblin Organ Grinder":
		case "Happy Medium":
			$familiar["bjorn"] = "+25% Meat"; break;
		case "Smiling Rat":
			$familiar["bjorn"] = "+3 Stats"; break;
		case "Animated Macaroni Duck":
		case "Autonomous Disco Ball":
		case "Barrrnacle":
		case "Gelatinous Cubeling":
		case "Ghost Pickle on a Stick":
		case "Misshapen Animal Skeleton":
		case "Pair of Ragged Claws":
		case "Penguin Goodfella":
		case "Spooky Pirate Skeleton":
			$familiar["bjorn"] = "+5 Familiar Weight"; break;
		case "Artistic Goth Kid":
		case "Cocoabo":
		case "Crimbo P. R. E. S. S. I. E.":
		case "Fuzzy Dice":
		case "Hanukkimbo Dreidl":
		case "He-Boulder":
		case "Mini-Hipster":
		case "Ninja Pirate Zombie Robot":
		case "Personal Raincloud":
		case "RoboGoose":
		case "Robot Reindeer":
		case "Steam-Powered Cheerleader":
		case "Stocking Mimic":
		case "Tickle-Me Emilio":
			$familiar["bjorn"] = "Attributes +10"; break;
		case "Wild Hare":
			$familiar["bjorn"] = "5-6 HP, 3-4 MP"; break;
		case "BRICKO chick":
		case "Mini-Adventurer":
		case "Nanorhino":
			$familiar["bjorn"] = "Attributes +10%"; break;
		case "Black Cat":
		case "O.A.F.":
			$familiar["bjorn"] = "Attributes -10"; break;
		case "Baby Bugged Bugbear":
			$familiar["bjorn"] = "Attributes -10, +2 Stats"; break;
		case "Cotton Candy Carnie":
		case "Cuddlefish":
		case "Emo Squid":
		case "Evil Teddy Bear":
		case "Feather Boa Constrictor":
		case "Levitating Potato":
		case "Mini-Skulldozer":
		case "Origami Towel Crane":
		case "Squamous Gibberer":
		case "Syncopated Turtle":
		case "Teddy Bear":
		case "Teddy Borg":
		case "Temporal Riftlet":
		case "Untamed Turtle":
			$familiar["bjorn"] = "Combat Init +20%"; break;
		case "Angry Jung Man":
		case "Astral Badger":
		case "Baby Sandworm":
		case "Blavious Kloop":
		case "Bloovian Groose":
		case "Green Pixie":
		case "Llama Lama":
		case "Unconscious Collective":
			$familiar["bjorn"] = "Max HP/MP +10"; break;
		case "Warbear Drone":
			$familiar["bjorn"] = "Max HP/MP +15"; break;
		case "Adorable Space Buddy":
			$familiar["bjorn"] = "Max HP/MP +30"; break;
		case "Clockwork Grapefruit":
		case "Nosy Nose":
		case "Ninja Snowflake":
		case "Sabre-Toothed Lime":
			$familiar["bjorn"] = "Moxie +15%"; break;
		case "Angry Goat":
		case "Imitation Crab":
		case "MagiMechTech MicroMechaMech":
		case "Stab Bat":
		case "Wereturtle":
		case "Wind-up Chattering Teeth":
			$familiar["bjorn"] = "Muscle +15%"; break;
		case "Grue":
		case "Inflatable Dodecapede":
		case "Ragamuffin Imp":
		case "Scary Death Orb":
		case "Snowy Owl":
			$familiar["bjorn"] = "Mysticality +15%"; break;
		case "Adorable Seal Larva":
		case "Ancient Yuletide Troll":
		case "Dandy Lion":
		case "Ghuol Whelp":
		case "Mutant Gila Monster":
		case "Sweet Nutcracker":
			$familiar["bjorn"] = "Regen 2-8 HP & MP"; break;
		case "Flaming Face":
			$familiar["bjorn"] = "Serious Cold Resistance"; break;
		case "Bulky Buddy Box":
		case "Exotic Parrot":
		case "Holiday Log":
		case "Pet Rock":
		case "Toothsome Rock":
			$familiar["bjorn"] = "So-So Resistance"; break;
		case "Baby Yeti":
		case "Dataspider":
		case "Howling Balloon Monkey":
		case "Midget Clownfish":
		case "Rogue Program":
		case "Snow Angel":
		case "Star Starfish":
		case "Underworld Bonsai":
		case "Whirling Maple Leaf":
		case "Wizard Action Figure":
			$familiar["bjorn"] = "Spell Damage +10%"; break;
		case "Magic Dragonfish":
		case "Pet Cheezling":
		case "Rock Lobster":
			$familiar["bjorn"] = "Spell Damage +15%"; break;
		case "Mechanical Songbird":
			$familiar["bjorn"] = "Spell Damage +20%"; break;
		case "Baby Gravy Fairy":
		case "Crimbo Elf":
		case "Urchin Urchin":
			$familiar["bjorn"] = "Weapon Damage +10"; break;
		case "Jumpsuited Hound Dog":
			$familiar["bjorn"] = "Weapon Damage +20"; break;
		case "Peppermint Rhino":
			$familiar["bjorn"] = "Weapon Damage +10%"; break;
		case "Mosquito":
		case "Pair of Stomping Boots":
			$familiar["bjorn"] = "Weapon Damage +20%"; break;
		default:
			$familiar["bjorn"] = ""; break;
	}

	# Bjorn/Throne 2ndary FX
	switch($familiar["name"])
	{
		case "":
			$familiar["bjorn2"] = ""; break;


		case "Grimstone Golem":
			$familiar["bjorn2"] = "Grimstone mask 1/day"; break;
		case "Grim Brother":
			$familiar["bjorn2"] = "Grim fairy tale 2/day"; break;
		case "Pottery Barn Owl":
			$familiar["bjorn2"] = "Volcanic ash"; break;
		case "El Vibrato Megadrone":
		case "Rock Lobster":
			$familiar["bjorn2"] = "10-15 MP / 3 rounds"; break;
		case "Oily Woim":
		case "Feral Kobold":
		case "Spirit Hobo":
		case "Hand Turkey":
		case "Leprechaun":
		case "Knob Goblin Organ Grinder":
		case "Happy Medium":
		case "He-Boulder":
		case "Nanorhino":
		case "Temporal Riftlet":
		case "Wereturtle":
			$familiar["bjorn2"] = "5-15 Meat / 3 rounds"; break;
		case "Gluttonous Green Ghost":
			$familiar["bjorn2"] = "Random bean burrito"; break;
		case "Li'l Xenomorph":
			$familiar["bjorn2"] = "Lunar isotope"; break;
		case "Reassembled Blackbird":
		case "Reconstituted Crow":
			$familiar["bjorn2"] = "blackberry"; break;
		case "Slimeling":
			$familiar["bjorn2"] = "10% Damage"; break;
		case "Blood-Faced Volleyball":
			$familiar["bjorn2"] = "15-20 Spooky Damage"; break;
		case "Cymbal-Playing Monkey":
			$familiar["bjorn2"] = "5-25 Damage"; break;
		case "Hovering Skull":
		case "Chauvinist Pig":
		case "Grouper Groupie":
		case "Teddy Borg":
			$familiar["bjorn2"] = "15-25 Sleaze Damage"; break;
		case "Jill-O-Lantern":
		case "Nervous Tick":
		case "Killer Bee":
		case "Cheshire Bat":
		case "Ghost Pickle on a Stick":
		case "Magic Dragonfish":
			$familiar["bjorn2"] = "8-10 HP / 3 rounds"; break;
		case "Mariachi Chihuahua":
			$familiar["bjorn2"] = "Staggers, 50%"; break;
		case "Grinning Turtle":
		case "Hovering Sombrero":
			$familiar["bjorn2"] = "15-25 Spooky Damage"; break;
		case "Hunchbacked Minion":
			$familiar["bjorn2"] = "Skeleton bone, disembodied brain"; break;
		case "Baby Mutant Rattlesnake":
		case "Mutant Fire Ant":
		case "Mutant Cactus Bud":
			$familiar["bjorn2"] = "15-25 poison"; break;
		case "Dramatic Hedgehog":
			$familiar["bjorn2"] = "Delevels, heals 2-3 HP"; break;
		case "Pygmy Bugbear Shaman":
			$familiar["bjorn2"] = "Delevels 3-5, 5-6 HP, 3-4 MP / 3 rounds"; break;
		case "Reanimated Reanimator":
			$familiar["bjorn2"] = "Hot wing, broken skull"; break;
		case "Sugar Fruit Fairy":
			$familiar["bjorn2"] = "15-25 Damage / 33%"; break;
		case "Uniclops":
			$familiar["bjorn2"] = "15-25 Damage / 30%"; break;
		case "Frumious Bandersnatch":
			$familiar["bjorn2"] = "2-4 Prismatic Damage x 5 / 33%"; break;
		case "Jack-in-the-Box":
		case "Reagnimated Gnome":
		case "Dancing Frog":
		case "Psychedelic Bear":
		case "Crimbo P. R. E. S. S. I. E.":
		case "Hanukkimbo Dreidl":
		case "Clockwork Grapefruit":
		case "Sabre-Toothed Lime":
		case "Ragamuffin Imp":
		case "Dandy Lion":
			$familiar["bjorn2"] = "Delevels 3-5"; break;
		case "Purse Rat":
			$familiar["bjorn2"] = "40-50 Damage / 30%"; break;
		case "Frozen Gravy Fairy":
			$familiar["bjorn2"] = "Cold nuggets"; break;
		case "Flaming Gravy Fairy":
			$familiar["bjorn2"] = "Hot nuggets"; break;
		case "Sleazy Gravy Fairy":
			$familiar["bjorn2"] = "Sleaze nuggets"; break;
		case "Spooky Gravy Fairy":
			$familiar["bjorn2"] = "Spooky nuggets"; break;
		case "Stinky Gravy Fairy":
			$familiar["bjorn2"] = "Stench nuggets"; break;
		case "Attention-Deficit Demon":
			$familiar["bjorn2"] = "Chorizo brownies, white chocolate and tomato pizza, carob chunk noodles"; break;
		case "Casagnova Gnome":
			$familiar["bjorn2"] = "14-20 HP, 8-14 MP / 1st round"; break;
		case "Coffee Pixie":
		case "Obtuse Angel":
		case "Origami Towel Crane":
		case "Ninja Snowflake":
		case "Scary Death Orb":
		case "Mosquito":
		case "Llama Lama":
			$familiar["bjorn2"] = "5-6 MP / 3 rounds"; break;
		case "Jitterbug":
			$familiar["bjorn2"] = "15-20 Stench Damage, 8-10 HP / 3 rounds"; break;
		case "Piano Cat":
			$familiar["bjorn2"] = "Beertini, papaya slung, salty slug, tomato daiquiri"; break;
		case "Hippo Ballerina":
			$familiar["bjorn2"] = "Delevels / 33%"; break;
		case "Baby Yeti":
		case "Snow Angel":
			$familiar["bjorn2"] = "Delevels"; break;
		case "Hobo Monkey":
			$familiar["bjorn2"] = "15-25 Hot Damage, delevels 3-5 / 3 rounds"; break;
		case "Smiling Rat":
			$familiar["bjorn2"] = "15-20 Spooky Damage, delevels 3-5"; break;
		case "Animated Macaroni Duck":
			$familiar["bjorn2"] = "20-50 Damage"; break;
		case "Autonomous Disco Ball":
			$familiar["bjorn2"] = "Delevels 5-9"; break;
		case "Barrrnacle":
			$familiar["bjorn2"] = "10-30 Damage, 30%"; break;
		case "Gelatinous Cubeling":
			$familiar["bjorn2"] = "Damage"; break;
		case "Misshapen Animal Skeleton":
			$familiar["bjorn2"] = "5-15 Meat, 20-40 Spooky Damage / 3 rounds"; break;
		case "Pair of Ragged Claws":
		case "Spooky Pirate Skeleton":
		case "Fuzzy Dice":
		case "RoboGoose":
		case "Tickle-Me Emilio":
		case "Evil Teddy Bear":
		case "Syncopated Turtle":
		case "MagiMechTech MicroMechaMech":
		case "Midget Clownfish":
			$familiar["bjorn2"] = "Staggers / 30%"; break;
		case "Penguin Goodfella":
		case "Urchin Urchin":
		case "Exotic Parrot":
			$familiar["bjorn2"] = "15-30 Damage / 30%"; break;
		case "Artistic Goth Kid":
		case "Mini-Hipster":
			$familiar["bjorn2"] = "10-20 HP, 3-4 MP, staggers"; break;
		case "Cocoabo":
			$familiar["bjorn2"] = "white chocolate chips"; break;
		case "Ninja Pirate Zombie Robot":
			$familiar["bjorn2"] = "10-15 HP, 8-10 MP / 3 rounds"; break;
		case "Personal Raincloud":
		case "Feather Boa Constrictor":
		case "Levitating Potato":
		case "Snowy Owl":
		case "Baby Gravy Fairy":
		case "Peppermint Rhino":
		case "Wild Hare":
			$familiar["bjorn2"] = "5-6 HP, 3-4 MP / 3 rounds"; break;
		case "Robot Reindeer":
		case "Sweet Nutcracker":
		case "Ancient Yuletide Troll":
			$familiar["bjorn2"] = "Candy cane, eggnog, fruitcake, or gingerbread bugbear / 30%"; break;
		case "Steam-Powered Cheerleader":
		case "Mutant Gila Monster":
			$familiar["bjorn2"] = "Delevels 8-10"; break;
		case "Stocking Mimic":
		case "Crimbo Elf":
			$familiar["bjorn2"] = "Angry Farmer candy, Cold Hots candy, Rock Pops, Tasty Fun Good rice candy, Wint-O-Fresh mint / 30%"; break;
		case "BRICKO chick":
			$familiar["bjorn2"] = "BRICKO brick / 1 per fight"; break;
		case "Mini-Adventurer":
			$familiar["bjorn2"] = "15-30 Meat / 3 rounds"; break;
		case "Black Cat":
			$familiar["bjorn2"] = "Lose 30-40 HP, 20-30 Meat"; break;
		case "O.A.F.":
			$familiar["bjorn2"] = "+10 Monster Level"; break;
		case "Baby Bugged Bugbear":
			$familiar["bjorn2"] = "?"; break;
		case "Cotton Candy Carnie":
			$familiar["bjorn2"] = "Cotton candy pinch / 1 per fight"; break;
		case "Cuddlefish":
			$familiar["bjorn2"] = "15-30 Sleaze Damage / 30%"; break;
		case "Emo Squid":
		case "Nosy Nose":
			$familiar["bjorn2"] = "Staggers"; break;
		case "Mini-Skulldozer":
			$familiar["bjorn2"] = "Delevels / 50%"; break;
		case "Squamous Gibberer":
			$familiar["bjorn2"] = "15-30 Spooky Damage / 33%"; break;
		case "Teddy Bear":
			$familiar["bjorn2"] = "15-25 Sleaze Damage, delevels 3-5"; break;
		case "Untamed Turtle":
			$familiar["bjorn2"] = "Snailmail bits, turtlemail bits, turtle wax"; break;
		case "Angry Jung Man":
		case "Unconscious Collective":
			$familiar["bjorn2"] = "Delevels opponent 3-6"; break;
		case "Blavious Kloop":
		case "Bloovian Groose":
			$familiar["bjorn2"] = "Delevels opponent 3-6 / 30%"; break;
		case "Astral Badger":
			$familiar["bjorn2"] = "Spooky mushrooms, Knob mushrooms, Knoll mushrooms; delevels 5-9"; break;
		case "Baby Sandworm":
			$familiar["bjorn2"] = "Delevels 3-5 / 50%"; break;
		case "Green Pixie":
			$familiar["bjorn2"] = "bottles of tequila / 20%"; break;
		case "Warbear Drone":
			$familiar["bjorn2"] = "warbear whosit"; break;
		case "Adorable Space Buddy":
			$familiar["bjorn2"] = "10-12 MP / 3 rounds"; break;
		case "Angry Goat":
			$familiar["bjorn2"] = "Goat cheese pizza"; break;
		case "Imitation Crab":
			$familiar["bjorn2"] = "15-20 Damage x 2 / 33%"; break;
		case "Stab Bat":
			$familiar["bjorn2"] = "20-30 Damage, 10-15 HP loss"; break;
		case "Wind-up Chattering Teeth":
			$familiar["bjorn2"] = "15-20 Stench Damage / 30%"; break;
		case "Grue":
			$familiar["bjorn2"] = "20-25 Spooky Damage / 33%"; break;
		case "Inflatable Dodecapede":
			$familiar["bjorn2"] = "Blocks attacks"; break;
		case "Adorable Seal Larva":
			$familiar["bjorn2"] = "Random nugget / 1 per fight, 35%"; break;
		case "Ghuol Whelp":
			$familiar["bjorn2"] = "15-25 Stench Damage"; break;
		case "Flaming Face":
			$familiar["bjorn2"] = "15-30 Hot Damage"; break;
		case "Holiday Log":
			$familiar["bjorn2"] = "30-50 Damage"; break;
		case "Pet Rock":
		case "Toothsome Rock":
			$familiar["bjorn2"] = "20-50 Damage / 30%"; break;
		case "Dataspider":
			$familiar["bjorn2"] = "3-5 MP / 3 rounds"; break;
		case "Howling Balloon Monkey":
			$familiar["bjorn2"] = "10-15 Stench Damage 10-15 Sleaze Damage, 30%"; break;
		case "Rogue Program":
			$familiar["bjorn2"] = "15-20 Damage"; break;
		case "Star Starfish":
			$familiar["bjorn2"] = "MP/4 Damage, max 100 / 30%"; break;
		case "Underworld Bonsai":
			$familiar["bjorn2"] = "10-20 Spooky Damage, staggers"; break;
		case "Whirling Maple Leaf":
			$familiar["bjorn2"] = "15-25 Cold Damage / 25%"; break;
		case "Wizard Action Figure":
			$familiar["bjorn2"] = "5-6 MP"; break;
		case "Pet Cheezling":
			$familiar["bjorn2"] = "5-15 HP, 2-8 MP / 3 rounds"; break;
		case "Mechanical Songbird":
			$familiar["bjorn2"] = "5-10 HP"; break;
		case "Jumpsuited Hound Dog":
			$familiar["bjorn2"] = "4-10 HP, 2-8 MP / 3 rounds"; break;
		case "Pair of Stomping Boots":
			$familiar["bjorn2"] = "20-30 Damage"; break;
		default:
			$familiar["bjorn2"] = ""; break;
	}

	$allFamiliars[] = $familiar;
}
fclose($file);



// DONE

$db = array(
	'items'		=> $allItems,
	'monsters'	=> $allMonsters,
	'familiars'	=> $allFamiliars,
);

file_put_contents('db.json',		json_encode($db));
file_put_contents('db.pretty.json', json_encode($db, JSON_PRETTY_PRINT));



//file_put_contents('items.json', json_encode($unindexedArray));
//file_put_contents('items.pretty.json', json_encode($unindexedArray, JSON_PRETTY_PRINT));

//file_put_contents('items.id.json', json_encode($idIndexed));
//file_put_contents('items.id.pretty.json', json_encode($idIndexed, JSON_PRETTY_PRINT));

//file_put_contents('items.descid.json', json_encode($descIdIndexed));
//file_put_contents('items.descid.pretty.json', json_encode($descIdIndexed, JSON_PRETTY_PRINT));
