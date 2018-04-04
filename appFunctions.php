<?php

/**
This is the main library for the OJSapp

//FUNCTIONS DEFINED IN THIS SCRIPT:
00) backupDataMapping
01) makeXmlDataMapping
02) saveDataMapping 
03) getDataMapping 
04) processFiles  --(getFiles) 
05) mapJournalSections
06) treatExportErrors
07) treatImportErrors
08) menu
09) mainMenu
10) migrateFiles
11) myExport
12) myImport
13) myMigrate
14) myMain  /////////////// THE MAIN FUNCTION //////////////// 

/// getters and setters functions  ////////////
15) getData
16) setData
///////////////////////////////////////////////

Developed in 2017 by Bernardo Amado
*/

include_once("helperFunctions.php");
include_once("db2xml.php");
include_once("xml2db.php");

// #00)
/**
creates a backup of the dataMappings.xml
*/
function backupDataMapping($pathToXml = "dataMappings", $filename = "dataMappings.xml") {
	
	$now = date("Y-m-d_H:i:s");
	$name = "";
	
	if (substr($filename, -4) === ".xml") { //the last 4 characters of $filename
		$name = substr($filename, 0, -4); 
		$now = date("Y-m-d_H:i:s");
		$name .= $now . ".xml";
	}
	else {
		$name = $filename . "." . $now;
	}
	
	$filenameFull = $pathToXml . "/" . $filename;
	$nameFull = $pathToXml . "/backups/" . $name;
	
	if (copy($filenameFull, $nameFull)) {
		return $nameFull;
	}
	
	return false;
}


// #01)
/**
creates a xml data mapping from the array journalDataMapping which has the dataMappings for one specific journal
the structure will be :
<mappings>
	<field>
		<mapping>
			<old></old>
			<new></new>
		</mapping>
		...
		...
		...
	</field>
	...
	...
	...
</mappings>
where 'field' is the actual name of each field
*/
function makeXmlDataMapping(&$xml, &$mappings_node, $journalDataMapping) {
	foreach ($journalDataMapping as $field => $mapping) {
		$field_node = $xml->createElement($field);
		foreach ($mapping as $old => $new) {
			$mapping_node = $xml->createElement("mapping");
			
			$old_node = $xml->createElement("old", $old);
			$new_node = $xml->createElement("new", $new);
			
			$mapping_node->appendChild($old_node);
			$mapping_node->appendChild($new_node);
			
			$field_node->appendChild($mapping_node);
		}
		
		$mappings_node->appendChild($field_node);
	}
}


// #02)
/**
save the dataMapping for the specified journal in the dataMappings xml file

return values:
 
 -1 if could not load the file
  0 if could not save the data mapping in an xml file
  1 if the xml file with the data mapping was saved successfully
  
*/
function saveDataMapping($dataMapping, $journalName, $journalMapping = null, $pathToXml = "./dataMappings", $xmlFilename = "dataMappings.xml") {
	
	$xml = new DOMDocument("1.0", "UTF-8");
	
	$filename = $pathToXml . "/" . $xmlFilename;
	
	if (!$xml->load($filename)) {
		echo "\nCould not load '$filename'.\n";
		return -1;
	}
	
	//save a backup copy 
	backupDataMapping(); //from this script function #00 
	
	//the data mapping  //////
	$mappings = $xml->createElement("mappings");
	//arrayToXml($xml, $mappings, $dataMapping, ["type" => "mappings"]); // from helperFunctions.php function #13
	makeXmlDataMapping($xml, $mappings, $dataMapping); //from this script function #01
	//////////////////////////
	
	$data_mappings = $xml->getElementsByTagName("data_mappings")->item(0);
	
	$data_list = $data_mappings->getElementsByTagName("data_mapping");
	$append = true;
	if ($data_list->length > 0) {
		foreach ($data_list as $old_data) {
			if ($old_data->getAttribute("journal") === $journalName) {
				//put the new data_mapping in the file
				$old_mappings = $old_data->getElementsByTagName("mappings")->item(0);
				$old_data->replaceChild($mappings, $old_mappings);
				//$data_mappings->replaceChild($data_mapping, $old_data);
				$append = false;
				break;
			}
		}//end of the foreach
	}
	
	if ($append) {
		$data_mapping = $xml->createElement("data_mapping");
		
		$data_mapping->setAttribute("journal", $journalName);
		
		//the journal mapping/////
		$journal = $xml->createElement("journal");
		arrayToXml($xml, $journal, $journalMapping, ["type" => "journal"]); //from helperFunctions.php function #13
		//////////////////////////
		
		$data_mapping->appendChild($journal);
		$data_mapping->appendChild($mappings);
		
		$data_mappings->appendChild($data_mapping);
	}
	
	if ($xml->save($filename)) {
		echo "\n'$filename' successfully saved!\n";
		return 1;
	}
	
	return 0;
} 


// #03)
/**
get the dataMapping for the specified journal, from the $dataMappingXml file

return values:

 -1 if could not open the file
  0 if there is no data_mapping in the file
  $dataMapping array if found and null otherwise

*/
function getDataMapping($journalName, $dataMappingXml = "dataMappings.xml", $pathToXml = "./dataMappings") {
	
	$xml = new DOMDocument("1.0", "UTF-8");
	
	//$filename = $pathToXml . $dataMappingXml;
	$filename = $pathToXml . "/" . $dataMappingXml;
	
	if (!$xml->load($filename)) {
		echo "\nCould not open '$filename'.\n";
		return -1;
	}
	
	$data_mappings = $xml->getElementsByTagName("data_mapping");
	
	if ($data_mappings->length > 0) {
		foreach ($data_mappings as $data_mapping) {
			if ($data_mapping->getAttribute("journal") === strtolower($journalName)) {
				//$dataMapping = xmlToArray($data_mapping, true); //from helperFunctions.php function #14
				
				$dataMapping = array();
				
				/////////////////////// the journal mapping //////////////////////////////////
				$journal_mapping = $data_mapping->getElementsByTagName("journal")->item(0);
				
				$id_node = $journal_mapping->getElementsByTagName("id")->item(0);
				$journalOldId = $id_node->getElementsByTagName("old")->item(0)->nodeValue;
				$journalNewId = $id_node->getElementsByTagName("new")->item(0)->nodeValue;
				$dataMapping["journal_id"] = array($journalOldId => $journalNewId);
				
				$path_node = $journal_mapping->getElementsByTagName("path")->item(0);
				$journalOldPath = $path_node->getElementsByTagName("old")->item(0)->nodeValue;
				$journalNewPath = $path_node->getElementsByTagName("new")->item(0)->nodeValue;
				$dataMapping["journal_path"] = array($journalOldPath => $journalNewPath);
				///////////////////////////////////////////////////////////////////////////////
				
				
				//////////////////////  the data mappings  ////////////////////////////////////
				$mappings_node = $data_mapping->getElementsByTagName("mappings")->item(0);
				
				foreach ($mappings_node->childNodes as $field_node) {
					
					$fieldName = $field_node->tagName;
					$fieldMapping = array(); // array to store the mappings of the field
					
					$mapping_nodes = $field_node->getElementsByTagName("mapping"); //the mappings for the field
					
					// loop to map the old field value to the new field value
					foreach ($mapping_nodes as $mapping_node) {
						$old = $mapping_node->getElementsByTagName("old")->item(0)->nodeValue;
						$new = $mapping_node->getElementsByTagName("new")->item(0)->nodeValue;
						$fieldMapping[$old] = $new;
					}
					
					$dataMapping[$fieldName] = $fieldMapping;
				}
				///////////////////////////////////////////////////////////////////////////////
				
				return $dataMapping;
			}
		}//end of the foreach data_mapping
	}
	else {
		echo "\nThere is no data mapping in '$filename'.\n";
		return 0;
	}
	
	return null;
}


// #04)
/**
get the files from the old journal and copy them to the new journal files updating their names
return values:
   0 if all went well
   1 if could not know the journal_id
*/
function processFiles($filesDirOld, $filesDirNew, &$dataMapping, &$copied, &$errors) {
	$fileCopyErrors = array();
	$copiedFiles = array();
	$filesTranslation = array(
		"PB" => "public",
		"AT" => "attachment",
		"SP" => "supp",
		"CE" => "submission/copyedit",
		"SM" => "submission/original",
		"RV" => "submission/review",
		"ED" => "submission/editor",
		"LE" => "submission/layout"
	);
	$files = scandir($filesDirOld);
	
	//echo "\n inside process files \n";
	
	////// get the journalId /////////
	
	//echo "\ngetting the journal id\n";
	
	$values = array_values($dataMapping["journal_id"]);
	if (count($values) !== 1) {
		//do not know the journal_id
		echo "\nDon't know the journal_id to copy the files to the good location\n";
		print_r($dataMapping);
		return 1;
		
	}
	
	$journalId = $values[0];
	
	//////////////////////////////////
	
	foreach ($files as $file) {
		
		if ($file === "." || $file === "..") {
			//DO NOTHING
			
		}
		else if (is_dir("$filesDirOld/$file")) {
			processFiles("$filesDirOld/$file", $filesDirNew, $dataMapping, $copied, $errors); //recursive call
		}
		else if (array_key_exists($file, $dataMapping["file_name"])){
			
			$fileNewName = $dataMapping["file_name"][$file];
			$words = explode("-", $fileNewName);
			if (sizeof($words) === 4) {
				$type = substr($words[3], 0, 2);
				$articleId = $words[0];
				$folder = $filesTranslation[$type];
				$dirOk = false;
				$dir = "$filesDirNew/journals/$journalId/articles/$articleId/$folder";
				
				if (!file_exists($dir)) {
					if (mkdir($dir, 0777, true)) {
						$dirOk = true;
					}
					else {
						$fileCopyErrors[$file] = "Couldn't create the directory $dir";
					}
				}
				else {
					$dirOk = true;
				}
				
				if ($dirOk) {
					echo "\nCopying $file to $fileNewName .......... ";
					if (copy("$filesDirOld/$file", "$dir/$fileNewName")) {
						$copiedFiles[$file] = "$dir/$fileNewName";
						echo "OK\n";
						$copied++;
					}
					else {
						$fileCopyErrors[$file] = "Couldn't copy the file to $dir/$fileNewName";
						echo "Failed\n";
					}
				}
			}// end of if sizeof(words) === 4
			else {
				//TRATAR MELHOR
				echo "\nFilename $file is not standard.\n"; 
			}
			
		}//end of the if file_name in dataMappings
		else {
			//TRATAR MELHOR 
			//echo "\n$file is not in dataMappings.\n";
		}
	}//end of the foreach
	//$errors["copyFiles"] = $fileCopyErrors;
	
	if (!empty($fileCopyErrors)) {
		array_push($errors, $fileCopyErrors);
	}
	
	return 0;
}



// #05
/**
maps the journal sections and put in the dataMappings.xml
Returns the section mapping as an array

this function is necessary because the sections data mapping is a bit different 

*/
function mapJournalSections($sectionsFilename, $conn = null, $journal = null) {
	
	if ($conn === null) {
		echo "\n-------- Connection with the database to map the sections -----------\n";
		echo "\n";
		$conn = askAndConnect(); //from helperFunctions.php function #03
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	$xml = new DOMDocument("1.0", "UTF-8");
	
	if (@$xml->loadHTMLFile($sectionsFilename)) {
		//use the loadHTMLFile to be able to decode the htmlentities
		//the @ in before the variable name is to suppress the warnings
		//everything ok
	}
	else {
		echo "\nCould not load the file '$sectionsFilename'\n";
		return 2;
	}
	
	echo "\nMapping journal sections...\n";
	
	$sections = $xml->getElementsByTagName("sections")->item(0);
	
	$journalOldPath = $sections->getAttribute("journal_original_path");
	$journalOldId = $sections->getAttribute("journal_original_id");
	
	$settings = $sections->getElementsByTagName("setting");
	
	$sectionMapping = getDataMapping($journalOldPath);
	
	if (!is_array($sectionMapping)) {
		$sectionMapping = array();
	}
	
	if (!array_key_exists("journal_id", $sectionMapping)) $sectionMapping["journal_id"] = array($journalOldId => $journal["journal_id"]);
	
	if (!array_key_exists("section_id", $sectionMapping)) $sectionMapping["section_id"] = array();
	
	foreach ($settings as $setting) {
		
		$name = $setting->getElementsByTagName("setting_name")->item(0)->nodeValue;
		
		if ($name === "abbrev") {
			
			$abbrev = $setting->getElementsByTagName("setting_value")->item(0)->nodeValue;
			$sectionOldId = $setting->getElementsByTagName("section_id")->item(0)->nodeValue;
			
			if (!array_key_exists($sectionOldId, $sectionMapping["section_id"])) {
				$section = getSectionByAbbrev($conn, $journal["journal_id"], $abbrev); // from xml2db.php function #02.5
				if (is_array($section)) {
					$sectionMapping["section_id"][$sectionOldId] = $section["section_id"];
				}
				
			}// closing the if section_id not in data mappings
		}//closing the if name === abbrev	
	}//end of the foreach setting
	
	
	return $sectionMapping;
	
}



// #06)
/**
function to treat the error genereating in the EXPORTATION process
*/

function treatExportErrors($result, $type = null) {
	
	$stop = false;
	
	switch($result) {
		case -2:{
			echo "\nUser decided to stop the program.\n";
			$stop = true;
		} break;
		
		case -1:
			echo "The application could not fetch the data to export.\n";
			break;
			
		case 0:
			echo "The application could not save the .xml file with the data to export.\n";
			break;
			
		case null:
			echo "The exportation function returned null\n";
			break;
			
		default:
			echo "Unknown return value for the exportation function: '$result'\n";
			$stop = true;
	}
	
	if (!$stop) {
		$resp = readline("\n\nContinue the execution even with the errors? (y/N): ");
		$responseYes = strtolower($resp) === "y" && strtolower($resp) === "yes";
	}
	
	if (!$responseYes || $stop) {
		exit("\n\n-----------  Application halt  ----------------\n\n");
	}
}

// #07)
/**
function to treat the error genereating in the IMPORTATION process
*/

function treatImportErrors($result, &$conn, $type = null) {
	
	$stop = false;
	$responseYes = false;
	
	if ($result < 0) {
		//occurred some problem while setting the data
		
		switch ($result) {
			case -1:
				echo "\nOccurred some problem(s) during the importation.\n";
				break;
				
			case -2: {
				echo "\nDid not load the .xml file.\n";
				$stop = true;
			} break;
				
			case -3:
				echo "\nImported everything but did not save the data mappings (CAN'T RETRIEVE THAT DATA AFTERWARDS).\n";
				break;
				
			case -4: {
				echo "\nUser decided to stop the program.\n";
				$stop = true;
			} break;
			
			case null:
				echo "\nThe importation result was null.\n";
				break;
				
			default:
				echo "\nUnknown return value for the importation result: '$result'\n";
				$stop = true;
		}
		
		if (!$stop) {
			$resp = readline("\n\nContinue the importation even with the errors? (y/N): ");
			$responseYes = strtolower($resp) === "y" || strtolower($resp) === "yes";
		}
		
		if (!$responseYes || $stop) {
			echo "\n\nRolling back the transaction ....... ";
			$conn->rollBack();
			echo "OK\n";
			exit("\n\n-----------  Application halt  ----------------\n\n");
		}
	}
}

// #08)
/**
function that returns an array with the tables collations for the specified db_name
*/
function getCollations(&$conn, $db_name) {
	
	$collations = array();
	
	//get the collations to know which tables need to transform the characters to utf-8
	$stmt = $conn->prepare("SELECT TABLE_SCHEMA, TABLE_NAME, TABLE_COLLATION FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=:db_name");
	
	if ($db_name === null) {
		exit("\n\ndb_name must not be null appFunctions.php function getCollations\n\n");
	}
	
	$stmt->bindParam(":db_name", $db_name, PDO::PARAM_STR);
	
	if ($stmt->execute()) {
		while ($info = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$collations[$info["TABLE_NAME"]] = $info["TABLE_COLLATION"];
		}
	}
	else {
		exit("\n\nCould not retrive the tables collations. (in appFunction.php function myExport)\n\n");
	}
	
	$stmt = null;
	
	return $collations;
}


// #09)
/**
functions that displays the main menu and returns the selected option
*/
function mainMenu($options) {
	
	echo "\n  Main menu:\n\n";
	
	foreach ($options as $key => $value) {
		echo "    $key - $value \n";
	}
	
	echo "\n";
	
	$opt = readline("Enter the desired option: ");
	
	if (array_key_exists($opt, $options)) {
		// confirm the chosen option
		$confirm = strtolower(readline("You chose '" . $options[$opt] . "'. Do you confirm this option? (Y/n) : "));
		if ($confirm === "n" || $confirm === "no") {
			return mainMenu($options); //call again the same menu
		}
	}
	else {
		echo "\nInvalid option!\n\n";
		return mainMenu($options); //call again the same menu
	}
	
	return $opt;
}



// #10)
/**
function to copy the article files from the old ojs to the new installation processing their names
arguments:
	journal -> is used to get the data mapping for the journal
	conn -> is used in order to get the journal, is necessary only if journal is null, 
*/
function migrateFiles($journal = null, $conn = null) {
	
	if ($journal === null) {
		if ($conn === null) {
			echo "\n-------- Connection with the database to migrate the files -----------\n";
			echo "\n";
			$conn = askAndConnect(); //from helperFunctions.php function #03
		}
		
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	echo "\n\nFILES MIGRATION\n\n";
	
	$filesOld = readline("Enter the location of the files_dir for the OLD OJS instalation: ");
	$filesNew = readline("Enter the location of the files_dir for the NEW OJS instalation: ");
	$copiedFiles = 0; // the number of copied files
	$fileErrors = array(); // array to store the errors while copying the files
	
	$dataMapping = getDataMapping($journal["path"]); // from this script function #03
	
	if (is_array($dataMapping)) {
		processFiles($filesOld, $filesNew, $dataMapping, $copiedFiles, $fileErrors); // from this script function #04
	}
	else {
		//error when trying to get the data mapping
		echo "\n\nERROR: Could not copy the files to the new files directory.\n";
		return 1;
	}
	
	
	if (!empty($fileErrors)) {
		echo "\nErrors while copying:\n";
		print_r($fileErrors);
	}
	
	$errorCount = count($fileErrors);
	
	echo "\nNumber of copied files: $copiedFiles\n";
	echo "\nNumber of errors: $errorCount\n";
	
	return $errorCount;
}


// #11)
/**
exports the data and puts the name of the xml file created in the array $arr
$options is an array with the keys sections, unpublished_articles and announcements
set to either true or false, marking which ones to be exported

returns the number of problems encountered during the execution of the function

*/
function myExport($options, &$arr, $conn = null, $journal = null, $args = null) {
	
	$numberOfProblems = 0;
	$db_name = null;
	$collations = null;
	
	if (is_array($args)) {
		if (array_key_exists("db_name", $args)) {
			$db_name = $args["db_name"];
		}
		
		if (array_key_exists("collations", $args)) {
			$collations = $args["collations"];
		}
	}
	
	if ($conn === null) {
		echo "\n-------- Connection with the database to export data -----------\n";
		echo "\n";
		//$conn = askAndConnect(); //from helperFunctions.php function #03
		
		
		$connData = AskConnectData(); // from helperFunctions.php function #02
		$db_name = $connData["db"]; //saving the db_name to use later
		
		$conn = myConnect($connData["host"], $connData["user"], $connData["pass"], $connData["db"]); // from helperFunctions.php function #01
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	if ($collations === null) $collations = getCollations($conn, $db_name);
	
	foreach ($options as $printableType => $export) { 
	if ($export) {
		//////// replace empty spaces (' ') with underlines ('_') /////////////
		$type = str_replace(" ", "_", $printableType);
		
		echo "\nExporting the journal $printableType...\n";
		
		$result = getData($type, $conn, $journal, $collations); // from this script function #07
	
		if (is_string($result)) {
			//echo "Success!!!\n";
			$arr[$type] = $result;
		}
		else {
			$numberOfProblems++; //increment the number of problems indicating that a problem occurred
			
			//treat the error acused by getAnnouncements
			treatExportErrors($result);
		}
	}// end of the if export
	}// end of the foreach options
	
	return $numberOfProblems;
}



// #12)
/**
imports the data from the xml file with the unpublished articles and/or sections
the names of the xml files are passed in the array $xmlFiles
option is an array with the selected items to import as true

returns the number of problems encountered during the execution of the function

uses mysql transaction

*/
function myImport($options, &$xmlFiles, &$conn = null, $journal = null, $args= null) {
	
	$numberOfProblems = 0;
	$db_name = null;
	$tables_info = null;
	$migrate_files = false;
	$saveDataMappingXml = false;
	
	if (is_array($args)) {
		if (array_key_exists("db_name", $args)) {
			$db_name = $args["db_name"];
		}
		
		if (array_key_exists("tables_info", $args)) {
			$tables_info = $args["tables_info"];
		}
	}
	
	if ($conn === null) {
		echo "\n-------- Connection with the database to import data -----------\n\n";
		
		$connData = AskConnectData(); // from helperFunctions.php function #02
		if ($db_name === null) $db_name = $connData["db"]; //saving the db_name to use later
		
		$conn = myConnect($connData["host"], $connData["user"], $connData["pass"], $connData["db"]); // from helperFunctions.php function #01
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	if ($db_name === null) {
		exit("\ndb_name null when in myImport type $type\n");
		$db_name = readline("Enter the name of the database to which the data must be imported: ");
	}
	
	if ($tables_info === null) {
		$tables_info = getTablesInfo($conn, $db_name); // from helperFunctions.php function #26
	}
	
	if (is_array($tables_info)) { 
		foreach ($tables_info as $info) {
			if ($info["ENGINE"] !== "InnoDB") {
				echo "\n\nTable " . $info["TABLE_NAME"] . " is not InnoDB\n\n";
				return 1; //indicates numberOfProblems = 1
			}
		}
	}
	else {
		echo "\nThe tables informations were not passed as an array\n";
		return 1; //indicates numberOfProblems = 1
	}
	
	
	try {
	
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		if (!$conn->inTransaction()) {
			$conn->beginTransaction();
		}
		
		$commit = true;
		
		$journalOldId = null;
		$journalOldPath = null;
		
		//// loop through each option and whether import it or not //////////////
		foreach ($options as $printableType => $import) { 
		if ($import) {
			//////// replace empty spaces (' ') with underlines ('_') /////////////
			$type = str_replace(" ", "_", $printableType);
			
			echo "\nImporting the journal $printableType...\n";
			
			// the xml filename for the announcements must be passed to setSections
			//this piece of code check if the filename is in the xmlFiles array, and sets the name if it's not there already
			if (!array_key_exists($type, $xmlFiles)) {
				$dataFilename = readline("Enter the name of the file where the $printableType are stored: ");
				if (substr($dataFilename, -1, 1) === " ") { // if the last character is an empty space
					$dataFilename = substr($dataFilename, 0, -1); //remove the last character of the string
				}
				
				$xmlFiles[$type] = $dataFilename;
			}
			/////////////  end of the check and set filename  //////////////////////////////////////////////////
			
			$result = setData($type, $xmlFiles, $conn, $journal, $dataMapping);
			// if everything went well result will be 1
			
			if ($result === 1) {
				$saveDataMappingXml = true;
				if ($type === "unpublished_articles") {
					$migrate_files = true;
				}
			}
			
			treatImportErrors($result, $conn);
			
			/////// this data will be used later to map the journal  ///////////
			if ($journalOldId === null || $journalOldPath === null) {
				
				$dataXml = new DOMDocument("1.0", "UTF-8");
				@$dataXml->loadHTMLFile($xmlFiles[$type]);
				
				$data_node = $dataXml->getElementsByTagName($type)->item(0);
				$journalOldPath = $data_node->getAttribute("journal_original_path");
				$journalOldId = $data_node->getAttribute("journal_original_id");
			}
			////////////////////////////////////////////////////////////////////
			
		
		}// end of the if import
		}// end of the foreach options
		
		if ($saveDataMappingXml) {
			$dataMappingSaved = false;
		
			if ($journalOldId !== null && $journalOldPath !== null) {
				
				$journalMapping = array(
					"path" => array("old" => $journalOldPath, "new" => $journal["path"]), 
					"id" => array("old" => $journalOldId, "new" => $journal["journal_id"])
				);
				
				$dataMappingSaved = saveDataMapping($dataMapping, $journal["path"], $journalMapping);  //from this script function #02
			}
			else {
				echo "\n\nCOULD NOT GET THE JOURNAL'S OLD id AND path\n\n";
			}
			
			if (!$dataMappingSaved) {
				treatImportErrors(-3, $conn); // -3 is the code for not saving the data mapping
				// if the user wants the program might stop here and the transaction will be rolled back
			}
			
			if ($commit){
				echo "\nCommitting changes to the database......";
				$conn->commit();
				echo "OK\n";
			}
			
			if ($migrate_files) {
				$numberOfProblems += migrateFiles($journal, $conn); // from this script function #11
			}
			
		}
		else {
			echo "\nNothing was imported\n";
		}
	
	} // end of the try block
	
	catch (PDOException $e) {
		echo "\n\n ########## FAILURE ########### \n";
		echo "\nException reached: " . $e->getMessage();
		
		echo "\n\nRolling back the transaction ........ ";
		
		$conn->rollBack();
		
		echo "Ok\n";
		
		exit("\n\n-----------  Application halt  ----------------\n\n");
	}
	
	return $numberOfProblems;
}
//end of the function myImport


// #13)
function myMigrate($options, &$xmlFiles, $conn = null, $journal = null, $args = null) {
	
	$numProblems = myExport($options, $xmlFiles, $conn, $journal, $args);
	if ($numProblems === 0) {
		// will only import the data if the data exportation ran without problems
		myImport($options, $xmlFiles, $conn, $journal, $args);
	}
	
}



// #14)
/**
the function that actually executes the app

like in C it returns 0 if everything went ok
*/
function myMain() {
	echo "\n\n---------------------------------------------------------------\n\n";
	echo "This is an app to help migrate data that the OJS importExport does not.\n";
	
	$actions = array(
		1 => 'export',
		2 => 'import',
		3 => 'migrate',
		4 => 'copy files',
		5 => 'correct charset'
	);
	
	$index = mainMenu($actions); // use a the mainMenu function to choose the action
	
	$action = $actions[$index];
	
	$options = array(
		'review_forms' => false, 
		'sections' => false, 
		'unpublished articles' => false, 
		'announcements' => false, 
		'groups' => false,
		'articles_history' => false
	);
	
	if ($action !== 'copy files' && $action !== 'correct charset') {
	foreach ($options as $type => &$value) {
		$resp = readline("Do you want to $action the $type? (y/N) : ");
		if (strtolower($resp) === "y" || strtolower($resp) === "yes") {
			$value = true;
		}
	}
	}
	
	///////// VARIABLES NEEDED TO PERFORM THE ACTIONS //////////////////
	
	$xmlFiles = array("data_mappings" => "dataMappings.xml"); 
	
	////////////////////////////////////////////////////////////////////
	
	switch($action){
		
		case 'export': 
			myExport($options, $xmlFiles);
			break;
			
		case 'import': 
			myImport($options, $xmlFiles);
			break;
		
		case 'migrate': 
			myMigrate($options, $xmlFiles);
			break;
		
		case 'copy files': 
			migrateFiles();
			break;
			
		case 'correct charset':
			correctCharset();
			break;
		
		default: 
			echo "\n\nBye bye!\n\n----------------------------------------------------------------------\n";
	
	}// end of the switch $options
	
	return 0; //classic C return for the main function
	
}// end of the main

///////////////// THESE FUNCTIONS GERENALIZE THE GETTERS AND SETTERS  ///////////////////////////////

// #15)
/**

return values:
 -3 if the type passed as an argument is not one of the preselected
 -2 if the user decided to stop the program
 -1 if did not fetch the unpublished articles
  0 if did not save the xml with the unpublished articles
  the 'xml filename' if saved the unpublished articles in the xml file successfully

*/
function getData($type, $conn = null, $journal = null, $collations) {
	
	if ($conn === null) {
		echo "\n-------- Connection with the database to export data -----------\n";
		echo "\n";
		$conn = askAndConnect(); //from helperFunctions.php function #03
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	//////// replace underlines ('_') with empty spaces (' ')/////////////
	$printableType = str_replace('_', ' ', $type);
	
	//////// replace empty spaces (' ') with underlines ('_') /////////////
	$type = str_replace(' ', '_', $type);
	
	$verbose = false;
	$getKeywords = false;
	$returnedData = null;
	
	$resp = readline('Do you want the system to emit messages of each step? (y/N) : ');
	if ($resp === 'y' || $resp === 'Y') {
		$verbose = true;
	}
	
	if ($type === 'unpublished_articles') {
		$resp = readline('Do you want to export the keywords? (y/N) : ');
		if ($resp === 'y' || $resp === 'Y') {
			$getKeywords = true;
		}
	}
	
	$args = array();
	$args['collations'] = $collations;
	$args['getKeywords'] = $getKeywords;
	$args['verbose'] = $verbose;
	
	
	
	switch($type) {
		
		case 'review_forms':
			$returnedData = fetchReviewForms($conn, $journal, $args);
			break;
		
		case 'sections':
			$returnedData = fetchSections($conn, $journal, $args);
			break;
			
		case 'unpublished_articles':
			$returnedData = fetchUnpublishedArticles($conn, $journal, $args);
			break;
			
		case 'announcements':
			$returnedData = fetchAnnouncements($conn, $journal, $args);
			break;
			
		case 'email_templates':
			$returnedData = fetchEmailTemplates($conn, $journal, $args);
			break;
			
		case 'groups':
			$returnedData = fetchGroups($conn, $journal, $args);
			break;
			
		case 'articles_history':
			$returnedData = fetchArticlesHistory($conn, $articlesIds, $journal, $args);
			break;
			
		default:
			echo "\nUnknown type '$type'\n";
			return -3;
	}
	
	$numErrors = countErrors($returnedData['errors']); // from helperFunctions.php function #21
	
	if ($numErrors > 0) {
		echo "\nThere were errors while fetching the $printableType:\n";
		print_r($returnedData["errors"]);
		
		$resp = readline("\n\nContinue the execution even with the errors? (y/N): ");
		
		if (strtolower($resp) !== "y" && strtolower($resp) !== "yes") {
			return -2;
		}
		
	}
	
	$data = $returnedData[$type];
	
	if (!is_array($data)) {
		echo "\nCould not fetch $type.\n";
		return -1;
	}
	
	$xml = new DOMDocument("1.0", "UTF-8");
	
	$data_node = $xml->getElementsByTagName("data")->item(0);
	
	$dumpArgs = array();
	$dumpArgs["addRootNode"] = true;
	$dumpArgs["rootNode"] = $type;
	$dumpArgs["journal"] = $journal;
	$dumpArgs["type"] = $type;
	
	arrayToXml($xml, $xml, $data, $dumpArgs); //from helperFunctions.php function #13
	
	$filename = $journal["path"] . "_$type.xml";
	
	if (saveMyXml($xml, $filename, false)) { //from helperFunctions.php function #04
		return $filename;
	}
	
	return 0;
	
}

// #16) 
/**
read a .xml file and put in the database the data that are not already there

return value:
	 1 -> Everything went ok and imported at least one data record
	 0 -> Everything went ok but did not import any data record
	-1 -> Occurred some problem(s) while inserting the data
	-2 -> Could not load the .xml file with the data
	-3 -> Imported everything but did not save the data mapping NOT USED
	-4 -> The user decided to stop the importation
	-5 -> Unknown type
*/
function setData($type, $xmlFiles, $conn = null, $journal = null, &$dataMapping) {
	
	$dataFilename = null;
	$mappingsFilename = null;
	$returnedData = null;
	$saveDataMappingXml = false;
	
	//////// replace underlines ('_') with empty spaces (' ')/////////////
	$printableType = str_replace('_', ' ', $type);
	
	//////// replace empty spaces (' ') with underlines ('_') /////////////
	$type = str_replace(' ', '_', $type);
	
	if (is_array($xmlFiles)) {
		if (array_key_exists($type, $xmlFiles)) {
			$dataFilename = $xmlFiles[$type];
		}
		if (array_key_exists('data_mappings', $xmlFiles)) {
			$mappingsFilename = $xmlFiles['data_mappings'];
		}
	}
	
	if ($conn === null) {
		echo "\n-------- Connection with the database to import data -----------\n";
		echo "\n";
		$conn = askAndConnect(); //from helperFunctions.php function #03
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	$mappingsXml = new DOMDocument('1.0', 'UTF-8');
	$dataXml = new DOMDocument('1.0', 'UTF-8');
	
	//if (!$dataXml->load($dataFilename)) {
	if (@$dataXml->loadHTMLFile($dataFilename)) {
		//use the loadHTMLFile to be able to decode the htmlentities
		//the @ in before the variable name is to suppress the warnings
		//everything ok
	}
	else {
		echo "\nCould not load the xml file '$dataFilename' for the $type.\n";
		return false;
	}
	
	if (!is_array($dataMapping)) {
		$dataMapping = getDataMapping($journal['path'], $mappingsFilename); //from this script function #03
		if (!is_array($dataMapping)) {
			if ($type === 'sections') {
				$dataMapping = mapJournalSections($sectionsFilename, $conn, $journal);
			}
			else {
				$dataMapping = array();
			}
		}
	}
	
	switch($type) {
		//the insert functions are in the script xml2db.php
		
		case 'review_forms':
			$returnedData = insertReviewForms($dataXml, $conn, $dataMapping, $journal['journal_id']);
			break;
		
		case 'sections':
			$returnedData = insertSections($dataXml, $conn, $dataMapping, $journal['journal_id']);
			break;
			
		case 'unpublished_articles':
			$returnedData = insertUnpublishedArticles($dataXml, $conn, $dataMapping, $journal); // the journal path is used also
			break;
			
		case 'announcements':
			$returnedData = insertAnnouncements($dataXml, $conn, $dataMapping, $journal['journal_id']);
			break;
			
		case 'email_templates':
			$returnedData = insertEmailTemplates($dataXml, $conn, $dataMapping, $journal['journal_id']);
			break;
			
		case 'groups':
			$returnedData = insertGroups($dataXml, $conn, $dataMapping, $journal['journal_id']);
			break;
			
		case 'articles_history':
			echo "\nTHE OPTION articles_history DOES NOT WORK FOR IMPORTATION YET\n";
			//$returnedData = insertArticlesHistory($dataXml, $conn, $dataMapping, $journal['journal_id']);
			break;
			
		default:
			echo "\nUnknown type '$type'\n";
			return -5;
	}
	
	
	if (!is_array($returnedData)) { 
		//ocurred some problem the insert function
		return -1; //TRATAR MELHOR
	}
	
	
	$numErrors = countErrors($returnedData["errors"]); //from helperFunction function #21
	
	if ($numErrors > 0) {
		echo "\nErrors:\n";
		print_r($returnedData["errors"]);
		
		echo "\nNumber of errors: $numErrors\n";
		
		$resp = readline("The changes are not yet committed. Continue with the importation? (y/N): ");
		
		if (strtolower($resp) !== "y" &&  strtolower($resp) !== "yes") {
			return -4;
			//exit("\n\n----------- Haulting the application ----------\n\n");
		}
	}
	else {
		echo "\nThere were no errors while inserting the $printableType\n";
	}
	
	if (array_key_exists("insertedUsers", $returnedData)) {
		$insertedUsers = $returnedData["insertedUsers"];
	
		if (count($insertedUsers) > 0) {
			
			echo "\nImported " . count($insertedUsers) . " new users.\n";
			$saveDataMappingXml = true;
			
			$newUsersXml = new DOMDocument("1.0", "UTF-8");
			
			$extraArgs = array();
			$extraArgs["addRootNode"] = true;
			$extraArgs["rootNode"] = "new_users";
			$extraArgs["journal"] = $journal;
			$extraArgs["type"] = "user";
			
			arrayToXml($newUsersXml, $newUsersXml, $insertedUsers, $extraArgs); // from helperFunctions function #13
			
			$usersFilename = $journal["path"] . "_newUsers.xml";
			
			echo "\nSaving the newly imported users in a .xml file:\n";
			saveMyXml($newUsersXml, $usersFilename, false); // from helperFunctions function #04 
		}
		else {
			echo "\nDid not import any new user.\n";
		}
	}
	
	if (is_array($returnedData["numInsertedRecords"])) {
		foreach ($returnedData["numInsertedRecords"] as $item => $number) {
			if ($number > 0) {
				$saveDataMappingXml = true;
				echo "\nImported $number new $item";
			}
			else {
				echo "\nDid not import any new $item";
			}
		}
	}
	else if ($returnedData["numInsertedRecords"] > 0) {
		
		//only save the dataMappings if at least one article or new user was inserted
		
		echo "\nImported " . $returnedData["numInsertedRecords"] . " new $printableType.";
		$saveDataMappingXml = true;
		
	}
	else {
		echo "\nDid not import any new $PrintableType.\n";
	}
	
	if ($saveDataMappingXml) {
		return 1;
	}
	
	return 0;
	
}


// #17)
function correctCharset($conn = null, $collations = null, $db_name = null) {
	require_once('correctCharset.php');
	if ($conn === null) {
		echo "\n-------- Connection with the database to correct the charset -----------\n";
		echo "\n";
		//$conn = askAndConnect(); //from helperFunctions.php function #03
		
		
		$connData = AskConnectData(); // from helperFunctions.php function #02
		$db_name = $connData["db"]; //saving the db_name to use later
		
		$conn = myConnect($connData["host"], $connData["user"], $connData["pass"], $connData["db"]); // from helperFunctions.php function #01
	}
	
	//if ($collations === null) $collations = getCollations($conn, $db_name);
	$collations = array('article_settings' => 'latin1', 'journal_settings' => 'latin1');
	
	echo "\n\nFetching the tables ... ";
	
	$tablesData = fetchData($conn, $collations);
	
	//print_r($tablesData['journal_settings'][1]); exit();
	
	if (is_array($tablesData)) {
		echo "Ok\n";
	}
	else {
		exit("Error\nCould not fetch all the tables\n");
	}
	
	echo "\n\nFixing the encoding and mapping the old to the new ... ";
	$map = translateData($tablesData);
	
	if (is_array($map)) {
		echo "Ok\n";
	}
	else {
		exit("Error\nCould not form the map\n");
	}
	
	////////////////////// TRANSACTION TO UPDATE THE DATABASE /////////////////////////
	
	try {
		echo "\n\n  ############# BEGINNING THE TRANSACTION TO UPDATE THE DATABASE  ################## \n\n";
		$conn->beginTransaction();
		
		if (updateData($conn, $map)) {
			$resp = readline('Do you wish to commit the changes to the database? (y/N) : ');
			if (strtolower($resp) === 'y' || strtolower($resp) === 'yes') {
				$conn->commit();
			}
			else {
				echo "\n\nRolling back the transaction ........ ";
				$conn->rollBack();
				echo "Ok\n";
			}
		}
		
	}
	catch (PDOException $e) {
		echo "\n\n ########## FAILURE ########### \n";
		echo "\nException reached: " . $e->getMessage();
		
		echo "\n\nRolling back the transaction ........ ";
		
		$conn->rollBack();
		
		echo "Ok\n";
		
		exit("\n\n-----------  Application halt  ----------------\n\n");
	}
	///////////////////////////////////////////////////////////////////////////////////
	
	echo "\n\nEnd of the charset translation to utf-8\n\n";
}