<?php

/**
fetch all the data from a database table and return as an array
*/
function fetchTable(&$conn, $tableName) {
	$name = filter_var($tableName, FILTER_SANITIZE_STRING);
	try {
		$query = "SELECT * FROM $tableName";
		
		$stmt = $conn->prepare($query);
		if ($stmt->execute()) {
			$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $records;
		}
		else {
			echo "\nDid not execute the statement: $query\nThe error was " . $stmt->errorInfo() . "\n";
			return false;
		}
	}
	catch (PDOException $e) {
		echo "\nThe function fetchTable raised an Exception\n";
		echo "The arguments were: " .
		"\nconn: " . print_r($conn, true) .
		"\ntableName: $tableName which sanitized to $name\n\n";
		echo "The exception was: " . $e->getMessage();
	}
}

/**
Fetch the data form all the tables in collations
*/
function fetchData(&$conn, $collations) {
	
	$tables = array();
	
	foreach ($collations as $tableName => $collation) {
		if (strpos($collation, 'latin') !== false) {
			//the item collation is of type ISO-8859-1 or very similar
			$table = fetchTable($conn, $tableName);
			
			if (is_array($table)) {
				if (count($table) > 0) {
					$tables[$tableName] = $table;
				}
			}
		}
	}
	
	return $tables;
	
}


/**

*/
function translateData(&$tables) {
	
	require_once('helperFunctions.php'); // the function translateArray2utf8 is in this file
	
	$map = array();
	foreach ($tables as $name => $data) {
		$newData = $data;
		translateArray2utf8($newData);
		$map[$name] = array('fields' => array_keys($data), 'original' => $data, 'translated' => $newData);
	}
	
	return $map;
}


function updateTable(&$conn, $tableName, &$mappedTable) {
	$attributes = $mappedTable['fields'];
	
	$query = "UPDATE TABLE $tableName SET ";
	
	///////// fill the query ///////////////////////////
	$numAttributes = count($attributes); 
	for ($i = 0; $i < $numAtrributes - 1; $i++) {
		$attr = $attributes[$i];
		$query .= "$attr = :value_$attr, ";
	}
	
	// the last attribute in the query
	$attr = $attributes[$numAttributes - 1];
	$query .= "$attr = :value_$attr";
	
	///// the conditions /////////////
	$query .= " WHERE ";
	
	for ($i = 0; $i < $numAtrributes - 1; $i++) {
		$attr = $attributes[$i];
		$query .= "$attr = :cond_$attr, ";
	}
	
	// the last attribute in the query
	$attr = $attributes[$numAttributes - 1];
	$query .= "$attr = :cond_$attr";
	/////////////////  query filled  ////////////////////
	
	$stmt = $conn->prepare($query);
	
	$numRecords = count($mappedTable['translated']);
	
	for ($i = 0; $i < $numRecords; $i++) {
		//loop through each record set
		foreach ($attributes as $attr) {
			//loop through each attribute
			$fieldType = (is_int($mappedTable['translated'])) ? PDO::PARAM_INT : PDO::PARAM_STR;
			$stmt->bindParam(":value_$attr", $mappedTable['translated'][$i][$attr], $fieldType);
			$stmt->bindParam(":cond_$attr", $mappedTable['original'][$i][$attr], $fieldType);
		}
		
		if ($stmt->execute()) {
			// everything ok
		}
		else {
			echo "\nThere was an error: " . print_r($stmt->errorInfo(), true) . "\n";
			echo "\nOriginal: " . print_r($mappedTable['original'][$i]);
			echo "\nTranslated: " . print_r($mappedTable['translated'][$i]);
			$resp = readline('Continue even with this error? (y/N) : ');
			
			if (strtolower($resp) !== 'y' && strtolower($resp) !== 'yes') {
				return false;
			}
			else {
				"\nContinuing the update...\n";
			}
		}
	}
	
	return true;
	
}


function updateData(&$conn, $map) {
	
	echo "\n\n----------- Updating the data ------------ \n\n";
	
	foreach ($map as $tableName => $mappedTable) {
		if (!updateTable($conn, $tableName, $mappedTable)) {
			return false;
		}
	}
	
	return true;
}