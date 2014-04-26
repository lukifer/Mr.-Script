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


// DONE

$db = array(
	'items'		=> $allItems,
	'monsters'	=> $allMonsters,
);

file_put_contents('db.json',		json_encode($db));
file_put_contents('db.pretty.json', json_encode($db, JSON_PRETTY_PRINT));



//file_put_contents('items.json', json_encode($unindexedArray));
//file_put_contents('items.pretty.json', json_encode($unindexedArray, JSON_PRETTY_PRINT));

//file_put_contents('items.id.json', json_encode($idIndexed));
//file_put_contents('items.id.pretty.json', json_encode($idIndexed, JSON_PRETTY_PRINT));

//file_put_contents('items.descid.json', json_encode($descIdIndexed));
//file_put_contents('items.descid.pretty.json', json_encode($descIdIndexed, JSON_PRETTY_PRINT));
