<?php

function fetchSectionSettings(&$returnData, &$stmts, &$queries, &$collations, $sectionId, &$errors) {
	
	//fetching the section settings
	$msg = '';
	$bound = bindStmtParam($stmts, $queries, 'selectSectionSettings', 'section_id', $sectionId, $msg); //from config.php
	if ($bound && executeStmt($stmts, 'selectSectionSettings', $errors)) {
		$sectionSettings = array();
		while ($setting = $stmts['selectSectionSettings']['stmt']->fetch(PDO::FETCH_ASSOC)) {
			array_push($sectionSettings, $setting);
		}
		
		processCollation($sectionSettings, 'section_settings', $collations);
		$returnData = $sectionSettings;
		return true;
	}

	if (!$bound) {
		if (!array_key_exists('fetchSectionSettings', $errors)) $errors['fetchSectionSettings'] = array(); 
		$error = array('section_id' => $sectionId, 'error' => $msg);
		array_push($errors['fetchSectionSettings'], $error);
	}

	return false;
}

// #01)

function fetchSections(&$conn, &$queries, &$stmts, $journal = null, &$args = null) {
	if ($journal === null) {
		require_once(BASE_DIR . '/helperFunctions.php');
		$journal = chooseJournal($conn); //from helperFunctions.php
	}
	
	//variable declarations
	$collations = null;
	$verbose = null;
	$sections = array();
	$msg = '';
	$errors = array(
		'sections' => array(),
		'section_settings' => array(),
	);

	
	if (is_array($args)) {
		if (array_key_exists('collations', $args)) {
			$collations =& $args['collations'];
		}
		if (array_key_exists('verbose', $args)) {
			$verbose =& $args['verbose'];	
		}
	}
	
	if (!array_key_exists('selectSections', $stmts)) {
		//load the selectSections prepared statement
		createStatement($conn, $stmts, 'selectSections', $queries);
	}

	$bound = bindStmtParam($stmts, $queries, 'selectSections', 'journal_id', $journal['journal_id'], $msg);
	
	if ($bound && executeStmt($stmts, 'selectSections', $errors)) {
	if ($verbose) echo "\n\nFetching the sections...\n\n";
		while ($section = $stmts['selectSections']['stmt']->fetch(PDO::FETCH_ASSOC)) {
			
			processCollation($section, 'sections', $collations);
			
			$abbrevs = array();
			$titles = array();
			
			//////////  section settings  ///////////////////////////////
			$sectionSettings = array();
			//$sectionSettingsSTMT->bindParam(':sectionSettings_sectionId', $section['section_id'], PDO::PARAM_INT);
			$fetched = fetchSectionSettings(
				$sectionSettings, $stmts, $queries, $collations, $section['section_id'], $errors
			);
			if ($fetched && !empty($sectionSettings)) $section['settings'] = $sectionSettings;

			//if ($verbose) echo "\nFetching the section #" . $section['section_id'] . " settings ........ ";
			
			/*if ($sectionSettingsSTMT->execute()) {
				while ($setting = $sectionSettingsSTMT->fetch(PDO::FETCH_ASSOC)) {
					array_push($settings, $setting);
				}
				if ($verbose) echo "Ok\n";
			}
			else {
				if ($verbose) echo "Error\n";
				$error = array('section_id' => $section['section_id'], 'error' => $sectionSettingsSTMT->errorInfo());
				array_push($errors['section_settings'], $error);
			}*/
			
			array_push($sections, $section);
		}
	}
	else {
		array_push($errors['sections'], $sectionsSTMT->errorInfo());
	}
	
	echo "\nFetched " . count($sections) . " sections.\n";

	//unsetting the local reference variables
	unset($collations);
	unset($verbose);
	
	return array('sections' => $sections, 'errors' => $errors);
}


