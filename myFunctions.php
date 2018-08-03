<?php

/**
find all the htmlentities in the given string and collect the following data about it:
entity: the entity itself, like &atilde;
start: the position where the entity's "&" is located, the start position of the entity 
end: the position where the entity's ";" is located, the end position of the entity 
substitute: the letter that the entity must be substituted by, like a would be the substitute of &atilde;

Returns an array with these data of all the entities found in the string
*/
function findEntities($str) {
	$entities = array();
	$offset = 0;
	while (($amperPos = strpos($str, '&', $offset)) !== false) {
		$smcolPos = strpos($str, ';', $amperPos);
		if ($smcolPos === false) {
			break;
		}
		$length = $smcolPos - $amperPos + 1;
		$entity = substr($str, $amperPos, $length);
		$substitute = substr($str, $amperPos + 1, 1);

		$entityData = array(
			'entity' => $entity, 
			'start' => $amperPos, 
			'end' => $smcolPos, 
			'substitute' => $substitute
		);

		array_push($entities, $entityData);

		$offset = $smcolPos;
	}

	return $entities;
}

/**
strips the entities found in the string, replacing it with the proper letter
*/
function stripEntities($str) {
	$newStr = '';
	$entitiesArray = findEntities($str);
	$index = 0;

	foreach($entitiesArray as $entityData) {
		//copy the part of the string until the entity start position
		$length = $entityData['start'] - $index;
		$newStr .= substr($str, $index, $length);	

		//replace the entity with the proper letter
		$entityLength = $entityData['end'] - $entityData['start'];
		$newStr .= $entityData['substitute'];

		//update the index to start the next iteration copying the string 
		//in the position just after the end of the entity
		$index = $entityData['end'] + 1;
	}
	
	//copy the rest of the string
	$newStr .= substr($str, $index);

	return $newStr;
}

/**
Returns the string with the special characters replaced by its htmlentity first letter
Example: "ã" which has htmlentity "&atilde" will be normalized to "a"
*/
function normalizeSpecialChars($str) {
	$withEntities = htmlentities($str);
	return stripEntities($withEntities);
}
