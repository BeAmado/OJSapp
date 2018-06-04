<?php
/**
This is a library for getting data from .xml files and put in the OJS database

FUNCTIONS IN DEFINED IN THIS SCRIPT:

01) processUsers
02) getSectionById
	02.1) getSectionByAbbrev
	02.2) getSectionBySetting
	02.3) getAbbrevsAndTitles
	02.4) sectionExists
03) getSetting
04) updateSections
05) insertSections
06) insertUnpublishedArticles
07) insertAnnouncements
08) insertEmailTemplates (better not use yet)
09) insertGroups
10) insertReviewForms
11) insertArticlesHistory
12) insertPluginSettings
13) insertIssueOrders
14) insertCitationsAndReferrals

#############################################################################
FUNCTIONS TO UPDATE DATA ALREADY IMPORTED TO GENERATE THE CORRECT STATISTICS

updatePublishedArticles
updateIssues
updateUsers
#############################################################################

Developed in 2017-2018 by Bernardo Amado

*/

include_once('helperFunctions.php');


// #01)
/**
function description here
*/
function processUser(&$user, $elem, &$dataMapping, &$errors, &$insertedUsers, &$stmts, &$conn) {
	
	$userOk = false;
	
	$userSTMT = &$stmts['userSTMT'];
	$checkUsernameSTMT = &$stmts['checkUsernameSTMT'];
	$insertUserSTMT = &$stmts['insertUserSTMT'];
	$lastUsersSTMT = &$stmts['lastUsersSTMT'];
	
	if (!array_key_exists('insertUserSettingSTMT', $stmts)) {
		$stmts['insertUserSettingSTMT'] = $conn->prepare(
			'INSERT INTO user_settings (user_id, locale, setting_name, setting_value, setting_type, assoc_id, assoc_type)
			VALUES (:userSetting_userId, :userSetting_locale, :userSetting_settingName, :userSetting_settingValue, :userSetting_settingType,
			:userSetting_assocId, :userSetting_assocType)'
		);
	}
	
	if (!array_key_exists('insertUserRoleSTMT', $stmts)) {
		$stmts['insertUserRoleSTMT'] = $conn->prepare(
			'INSERT INTO roles (journal_id, user_id, role_id)
			VALUES (:userRole_journalId, :userRole_userId, :userRole_roleId)'
		);
	}
	
	if (!array_key_exists('insertUserInterestSTMT', $stmts)) {
		$stmts['insertUserInterestSTMT'] = $conn->prepare(
			'INSERT INTO user_interests (controlled_vocab_entry_id, user_id) VALUES (:insertInterest_controlledVocabEntryId, :insertInterest_userId)'
		);
	}
	
	$insertUserSettingSTMT = &$stmts['insertUserSettingSTMT'];
	$insertUserRoleSTMT = &$stmts['insertUserRoleSTMT'];
	$insertUserInterestSTMT = &$stmts['insertUserInterestSTMT'];
	
	$type = $elem['type'];
	$data = $elem['data'];
	
	$error = array();
	
	global $tables; // from the config.php script
	
	$keys = $tables[$type]['primary_keys'];
	if (empty($keys)) {
		$keys = $tables[$type]['foreign_keys'];
	}
	
	foreach ($keys as $key) {
		$error[$key] = $data[$key]; 
	}
	
	$userSTMT->bindParam(':user_email', $user['email'], PDO::PARAM_STR);
	
	if ($userSTMT->execute()) {
		//echo "\nExecuted the userSTMT\n";
		if ($userInfo = $userSTMT->fetch(PDO::FETCH_ASSOC)) {
			$userOk = true;
			$user['user_new_id'] = $userInfo['user_id'];
		}
		else {
			//user is not registered, so need to be inserted in the database
			
			echo "TRYING TO INSERT THE USER \n";
			
			validateData('user', $user); //from helperFunctions.php
			$usernameUsed = true;
			$executed = true;
			$number = 2;
			$user['new_username'] = $user['username'];
			
			//check if need to change the username
			while ($usernameUsed) {
				
				$checkUsernameSTMT->bindParam(':checkUsername', $user['new_username'], PDO::PARAM_STR);
				
				if ($checkUsernameSTMT->execute()) {
					$res = $checkUsernameSTMT->fetch(PDO::FETCH_ASSOC);
					
					if ($res['count'] == 0) {
						$usernameUsed = false;
					}
					else {
						// the username is already in use
						// if the last characters are numbers increment the 2-digit number
						$last2Characters = substr($user['new_username'], -2, 2);
						if (is_numeric($last2Characters)) {
							$number = (int) $last2Characters;
							$number++;
							$str = substr($user['new_username'], 0, -2);
							$user['new_username'] = $str . "$number";
						}
						else {
							// if the last character is a number increment it, otherwise put the number 2 at the final
							$lastCharacter = substr($user['new_username'], -1, 1);
							if (is_numeric($lastCharacter)) {
								$number = (int) $lastCharacter;
								$number++;
								$str = substr($user['new_username'], 0, -1);
								$user['new_username'] = $str . "$number";
							}
							else {
								$user['new_username'] .= "$number";
							}
						}
					}
				}
				else { //Did not execute checkUsernameSTMT
					$error['checkUsernameSTMT'] = array('user' => $user, 'error' => $checkUsernameSTMT->errorInfo());
					break;
				}						
			}
			
			echo "\n\nThe user is : " . print_r($user, true) . "\n\n";
			
			$arr = array();
			$arr['data'] = $user;
			$arr['params'] = array(
				array('name' => ':insertUser_username', 'attr' => 'new_username', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_password', 'attr' => 'password', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_salutation', 'attr' => 'salutation', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_firstName', 'attr' => 'first_name', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_middleName', 'attr' => 'middle_name', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_lastName', 'attr' => 'last_name', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_gender', 'attr' => 'gender', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_initials', 'attr' => 'initials', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_email', 'attr' => 'email', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_url', 'attr' => 'url', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_phone', 'attr' => 'phone', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_fax', 'attr' => 'fax', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_mailingAddress', 'attr' => 'mailing_address', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_country', 'attr' => 'country', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_locales', 'attr' => 'locales', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_dateLastEmail', 'attr' => 'date_last_email', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_dateRegistered', 'attr' => 'date_registered', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_dateValidated', 'attr' => 'date_validated', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_dateLastLogin', 'attr' => 'date_last_login', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_mustChangePassword', 'attr' => 'must_change_password', 'type' => PDO::PARAM_INT),
				array('name' => ':insertUser_authId', 'attr' => 'auth_id', 'type' => PDO::PARAM_INT),
				array('name' => ':insertUser_disabled', 'attr' => 'disabled', 'type' => PDO::PARAM_INT),
				array('name' => ':insertUser_disabledReason', 'attr' => 'disabled_reason', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_authStr', 'attr' => 'auth_str', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_suffix', 'attr' => 'suffix', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_billingAddress', 'attr' => 'billing_address', 'type' => PDO::PARAM_STR),
				array('name' => ':insertUser_inlineHelp', 'attr' => 'inline_help', 'type' => PDO::PARAM_STR)
				
			);
			
			echo "\ninserting user " . $user['first_name'] . " " . $user['middle_name'] . " " . $user['last_name'] . " ............ ";
			
			if (myExecute('insert', 'user', $arr, $insertUserSTMT, $errors)) { //from helperFunctions.php
				echo "OK\n";
				$args = array();
				$args['compare'] = 'almost all';
				$args['notCompare'] = 'new_username';
				
				if (getNewId('user', $lastUsersSTMT, $user, $dataMapping, $errors, $args)) { //from helperFunctions.php
					echo "    user new id = " . $user['user_new_id'] . "\n\n";
					$userOk = true;
					// save the user on the insertedUsers array
					array_push($insertedUsers, $user);
				}
			}
			else {
				echo "Failed\n";
			}
		}//end of the else that inserts the user in the database
		
		//put the user new id in the dataMapping
		if (array_key_exists('user_new_id', $user) && !array_key_exists($user['user_id'], $dataMapping['user_id'])) {
			$dataMapping['user_id'][$user['user_id']] = $user['user_new_id'];
		}
		
	}
	else {
		//Did not execute the userSTMT
		$error['userSTMT'] = ['user' => $user, 'error' => $userSTMT->errorInfo()];
	}
	
	unset($userSTMT);
	unset($checkUsernameSTMT);
	unset($insertUserSTMT);
	unset($lastUsersSTMT);
	unset($insertUserSettingSTMT);
	unset($insertUserRoleSTMT);
	unset($insertUserInterestSTMT);
	
	if (array_key_exists('userSTMT', $error) || array_key_exists('checkUsernameSTMT', $error)) {
		array_push($errors, $error);
	}
	
	return $userOk;
}


// #02)
/**
this function returns the section with its settings if the section_id is found
returns false otherwise
*/
function getSectionById($conn, $sectionId) {
	$getSection = $conn->prepare('SELECT * FROM section WHERE section_id = :sectionId');
	$getSection->bindParam(':sectionId', $sectionId, PDO::PARAM_INT);
	
	$getSettings = $conn->prepare('SELECT * FROM section_settings WHERE section_id = :settings_sectionId');
	$getSettings->bindParam(':settings_sectionId', $sectionId, PDO::PARAM_INT);
	
	if ($getSection->execute()) { // I
	if ($section = $getSection->fetch(PDO::FETCH_ASSOC)) { // II
		if ($getSettings->execute()) { // III
		$settings = array();
		
		while ($setting = $getSettings->fetch(PDO::FETCH_ASSOC)) {
			array_push($settings, $setting);
		}
		
		if (!empty($settings)) {
			$section['settings'] = $settings;
		}
		}// III - closing the if getSettings execute
		
		return $section;
		
	}// II - closing the if getSection fetch
	}// I - closing the if getSection execute
	
	return false;
}


// #02.1)
/**
description here
*/
function getSectionByAbbrev($conn, $journalId, $abbrev, $locale, $fetchSettings = false) {
	
	$getSection = $conn->prepare('SELECT * FROM sections WHERE section_id = :sectionId');
	$getSettings = $conn->prepare('SELECT * FROM section_settings WHERE section_id = :settings_sectionId');
	
	
	$abbrevSTMT = $conn->prepare('SELECT * FROM section_settings WHERE 
		setting_name="abbrev" AND setting_value=:abbrev AND locale=:locale AND section_id IN (
			SELECT section_id FROM sections WHERE journal_id=:journalId
		)');
	
	$abbrevSTMT->bindParam(':abbrev', $abbrev, PDO::PARAM_STR);
	$abbrevSTMT->bindParam(':locale', $locale, PDO::PARAM_STR);
	$abbrevSTMT->bindParam(':journalId', $journalId, PDO::PARAM_STR);
	
	if ($abbrevSTMT->execute()) { // I	
	if ($fetched = $abbrevSTMT->fetch(PDO::FETCH_ASSOC)) { // II 
		   
  		$getSection->bindParam(':sectionId', $fetched['section_id'], PDO::PARAM_INT);
		if ($getSection->execute()) { // III
		if ($section = $getSection->fetch(PDO::FETCH_ASSOC)) { // IV
			
			if ($fetchSettings) {
				$settings = array();
				$getSettings->bindParam(':settings_sectionId', $section['section_id'], PDO::PARAM_INT);
				
				if ($getSettings->execute()) { // V 
				while ($setting = $getSettings->fetch(PDO::FETCH_ASSOC)) {
					array_push($settings, $setting);
				}
				} // V - closing the if getSettings execute
				else {
					echo "\nDid not execute the getSettings\n";
				}
				
				$section['settings'] = $settings;
			}
			
			/*echo "\nsection returned by getSectionAbbrev: '; print_r($section);*/
			
			return $section;
			
		}// IV - closing the if getSection fetch
		/*else {
			echo "\ngetSection did not fetch\n";
		}*/
		}// III - closing the if getSection execute
		else {
			return array('error' => 'Did not execute the getSection', 'errorInfo' => $getSection->errorInfo());
		}
		
	}// II - closing the if abbrevSTMT fetch
	/*else {
		echo "\nabbrevSTMT did not fetch\n";
	}*/
	}// I - closing the if abbrevSTMT execute
	else {
		return array('error' => 'Did not execute the abbrevSTMT', 'errorInfo' => $abbrevSTMT->errorInfo());
	}
	
	return false;
}


// #02.2)
/**
description here
*/
function getSectionBySetting($conn, $journalId, $locale, $settingName, $settingValue, $fetchSettings = false) {
	
	$getSection = $conn->prepare('SELECT * FROM sections WHERE section_id = :sectionId');
	$getSettings = $conn->prepare('SELECT * FROM section_settings WHERE section_id = :settings_sectionId');
	
	
	$stmt = $conn->prepare('SELECT * FROM section_settings WHERE 
		locale=:locale AND setting_name=:settingName AND setting_value=:settingValue AND section_id IN (
			SELECT section_id FROM sections WHERE journal_id=:journalId
		)');
	
	$stmt->bindParam(':locale', $locale, PDO::PARAM_STR);
	$stmt->bindParam(':settingName', $settingName, PDO::PARAM_STR);
	$stmt->bindParam(':settingValue', $settingValue, PDO::PARAM_STR);
	$stmt->bindParam(':journalId', $journalId, PDO::PARAM_STR);
	
	if ($stmt->execute()) { // I	
	if ($fetched = $stmt->fetch(PDO::FETCH_ASSOC)) { // II                                             
		   
  		$getSection->bindParam(':sectionId', $fetched['section_id'], PDO::PARAM_INT);
		if ($getSection->execute()) { // III
		if ($section = $getSection->fetch(PDO::FETCH_ASSOC)) { // IV
			
			if ($fetchSettings) {
				$settings = array();
				$getSettings->bindParam(':settings_sectionId', $section['section_id'], PDO::PARAM_INT);
				
				if ($getSettings->execute()) { // V 
				while ($setting = $getSettings->fetch(PDO::FETCH_ASSOC)) {
					array_push($settings, $setting);
				}
				} // V - closing the if getSettings execute
				else {
					return array('error' => 'Did not execute the getSettings', 'errorInfo' => $getSettings->errorInfo());
				}
				
				$section['settings'] = $settings;
			}
			
			return $section;
			
		}// IV - closing the if getSection fetch
		/*else {
			echo "\ngetSection did not fetch\n";
		}*/
		}// III - closing the if getSection execute
		else {
			$error = 'Did not execute the getSection';
			return array('error' => $error, 'errorInfo' => $getSection->errorInfo());
		}
		
	}// II - closing the if stmt fetch
	/*else {
		echo "\nstmt did not fetch\n";
	}*/
	}// I - closing the if stmt execute
	else {
		return array('error' => 'Did not execute the stmt', 'errorInfo' => $stmt->errorInfo());
	}
	
	return false;
}


// #02.3)
/**
function to get the abbrevs and titles of section from the array $section
*/
function getAbbrevsAndTitles($section) {
	if (!array_key_exists('settings', $section)) {
		return false;
	}
	
	$titles = array();
	$abbrevs = array();
	
	foreach ($section['settings'] as $setting) {
	if ($setting['setting_name'] === 'title' || $setting['setting_name'] === 'abbrev') {
		
		switch($setting['setting_name']) {
			case 'title': 
				$titles[$setting['locale']] =  $setting['setting_value'];
				break;
			
			case 'abbrev':  
				$abbrevs[$setting['locale']] =  $setting['setting_value'];
				break;
		}//end of the switch
		
	}// end of the if setting_name is title or abbrev
	}// end of the foreach section setting
	
	return array('titles' => $titles, 'abbrevs' => $abbrevs);
}


// #02.4) 
/**
test if a section already exist in the database

if the section exists returns an array with the section and some messages regarding
whether the section data is the same as the one being searched

returns false if the section does not exist
*/
function sectionExists($conn, $journalId, $section) {
	$abbrevsAndTitles = getAbbrevsAndTitles($section); //from this script function #02.3
	$abbrevs = $abbrevsAndTitles['abbrevs'];
	$titles = $abbrevsAndTitles['titles'];
	$found = false;
	$messages = array();
	$fetchedSection = null;
	
	//search the section by abbrev
	foreach ($abbrevs as $locale => $abbrev) {
		$fetchedSection = getSectionBySetting($conn, $journalId, $locale, 'abbrev', $abbrev, true); // from this script function #02.2
		
		if ($fetchedSection) {
			//compare the abbrevs and titles of the fetched section with the one we are trying to check if exists
			$fetchedData = getAbbrevsAndTitles($fetchedSection); //from this script function #02.3
			$fetchedAbbrevs = $fetchedData['abbrevs'];
			$fetchedTitles = $fetchedData['titles'];
			
			if (count($abbrevs) === count($fetchedAbbrevs)) {
				//loop through the fetched abbrev data to compare
				foreach ($fetchedAbbrevs as $fetchedLocale => $fetchedAbbrev) {
					if ($abbrevs[$fetchedLocale] !== $fetchedAbbrev) {
						// as the abbrevs are not equal, the section exists but the data are not the same
						$msg = "The section abbrev with locale '$fetchedLocale' stored in the database is '$fetchedAbbrev' while the one looking to be inserted is '" .
						$abbrevs[$fetchedLocale] . "'" ;
						array_push($messages, $msg);
					}
				}
				
				//loop through the fetched title data to compare
				foreach ($fetchedTitles as $fetchedLocale => $fetchedTitle) {
					if ($titles[$fetchedLocale] !== $fetchedTitle) {
						// as the titles are not equal, the section exists but the data are not the same
						$msg = "The section title with locale '$fetchedLocale' stored in the database is '$fetchedTitle' while the one looking to be inserted is '" .
						$titles[$fetchedLocale] . "'" ;
						array_push($messages, $msg);
					}
				}
			}
			else {
				//the section exists but the data are not the same
				 $msg = 'The stored section has ' . count($fetchedAbbrevs) . ' abbrevs while the one looking to be inserted has ' . count($abbrevs);
				array_push($messages, $msg);
			}
			
			$found = true;
			break;
			
		}//end of the if fetchedSection
	}//end of the foreach abbrevs
	
	if (!$found) {
	//search the section by title
	foreach ($titles as $locale => $title) {
		$fetchedSection = getSectionBySetting($conn, $journalId, $locale, 'title', $title, true); // from this script function #02.2
		
		if ($fetchedSection) {
			//compare the titles and titles of the fetched section with the one we are trying to check if exists
			$fetchedData = getAbbrevsAndTitles($fetchedSection); //from this script function #02.3
			$fetchedAbbrevs = $fetchedData['abbrevs'];
			$fetchedTitles = $fetchedData['titles'];
			
			if (count($titles) === count($fetchedTitles)) {
				//loop through the fetched abbrev data to compare
				foreach ($fetchedAbbrevs as $fetchedLocale => $fetchedAbbrev) {
					if ($abbrevs[$fetchedLocale] !== $fetchedAbbrev) {
						// as the titles are not equal, the section exists but the data are not the same
						$msg = "The section abbrev with locale '$locale' stored in the database is '$fetchedAbbrev' while the one looking to be inserted is '" .
						$abbrevs[$fetchedLocale] . "'" ;
						array_push($messages, $msg);
					}
				}
				
				//loop through the fetched title data to compare
				foreach ($fetchedTitles as $fetchedLocale => $fetchedTitle) {
					if ($titles[$fetchedLocale] !== $fetchedTitle) {
						// as the titles are not equal, the section exists but the data are not the same
						$msg = "The section title with locale '$locale' stored in the database is '$fetchedTitle' while the one looking to be inserted is '" .
						$titles[$fetchedLocale] . "'" ;
						array_push($messages, $msg);
					}
				}
			}
			else {
				//the section exists but the data are not the same
				$msg = 'The stored section has ' . count($fetchedTitles) . ' titles while the one looking to be inserted has ' . count($titles);
				array_push($messages, $msg);
			}
			
			break;
			
		}//end of the if fetchedSection
	}//end of the foreach titles
	} // end of the if not found
	
	if ($fetchedSection === null || $fetchedSection === false) {
		return false; // the section does not exist
	}
	
	return array('section' => $fetchedSection, 'messages' => $messages);
}


// #03)
/**
function to know wheater or not the element has the specified setting
arguments:
    $element: is an array with the data from e.g a section or an article
    $setting: is an array with the setting data
    
returns setting data from the element or false if the the element does not have it
*/
function getSetting($element, $setting) {
	
	if (array_key_exists('settings', $element)) {
		foreach ($element['settings'] as $elementSetting) {
			if ($setting['setting_name'] === $elementSetting['setting_name'] && $setting['locale'] === $elementSetting['locale']) {
				return $elementSetting;
			}
		}
	}
	
	return false;
}

// #04)
/**
function to update or insert new data to the section already in the database
*/
function updateSection($conn, &$section, $sectionNewId, &$dataMapping) {
	
	//search if the section_id exists
	$searchSectionSTMT = $conn->prepare('SELECT COUNT(*) AS count FROM sections WHERE section_id = :search_sectionId');
	
	$searchSectionSTMT->bindParam(':search_sectionId', $sectionNewId);
	if ($searchSectionSTMT->execute()) {
		if ($searchSectionSTMT->fetchColumn() == 0) {
			return array(
				'result' => 'error', 
				'message' => 'Did not find the section #' . $sectionNewId,
				'section' => $section
			);
		}
	}
	else {
		//did not execute the searchSectionSTMT
		return array(
			'result' => 'error',
			'message' => 'Did not execute the statement to search for the section',
			'errorInfo' => $searchSectionSTMT->errorInfo(),
			'sectionNewId' => $sectionNewId
		);
	}
	
	
	validateData('section', $section);
	
	$reviewFormNewId = null;
	$results = array('section' => null, 'section_settings' => null);
	
	if ($section['review_form_id'] !== 0 && $section['review_form_id'] !== null) {
		if (array_key_exists($section['review_form_id'], $dataMapping['review_form_id'])) {
			$reviewFormNewId = $dataMapping['review_form_id'][$section['review_form_id']];
		}
		else if ($section['review_form_id'] === 0){
			$reviewFormNewId = 0;
		}
	}
	
	
	$updateSectionSTMT = $conn->prepare('UPDATE sections SET review_form_id = :updateSection_reviewFormId, seq = :updateSection_seq, 
		editor_restricted = :updateSection_editorRestricted, meta_indexed = :updateSection_metaIndexed, meta_reviewed = :updateSection_metaReviewed, 
		abstracts_not_required = :updateSection_abstractsNotRequired, hide_title = :updateSection_hideTitle, hide_author = :updateSection_hideAuthor, 
		hide_about = :updateSection_hideAbout, disable_comments = :updateSection_disableComments, abstract_word_count = :updateSection_abstractWordCount
		WHERE section_id = :updateSection_sectionId');
	
	$updateSectionSTMT->bindParam(':updateSection_reviewFormId', $reviewFormNewId, PDO::PARAM_INT);
	$updateSectionSTMT->bindParam(':updateSection_seq', $section['seq']);
	$updateSectionSTMT->bindParam(':updateSection_editorRestricted', $section['editor_restricted'], PDO::PARAM_INT);
	$updateSectionSTMT->bindParam(':updateSection_metaIndexed', $section['meta_indexed'], PDO::PARAM_INT);
	$updateSectionSTMT->bindParam(':updateSection_metaReviewed', $section['meta_reviewed'], PDO::PARAM_INT);
	$updateSectionSTMT->bindParam(':updateSection_abstractsNotRequired', $section['abstracts_not_required'], PDO::PARAM_INT);
	$updateSectionSTMT->bindParam(':updateSection_hideTitle', $section['hide_title'], PDO::PARAM_INT);
	$updateSectionSTMT->bindParam(':updateSection_hideAuthor', $section['hide_author'], PDO::PARAM_INT);
	$updateSectionSTMT->bindParam(':updateSection_hideAbout', $section['hide_about'], PDO::PARAM_INT);
	$updateSectionSTMT->bindParam(':updateSection_disableComments', $section['disable_comments'], PDO::PARAM_INT);
	$updateSectionSTMT->bindParam(':updateSection_abstractWordCount', $section['abstract_word_count'], PDO::PARAM_INT);
	$updateSectionSTMT->bindParam(':updateSection_sectionId', $sectionNewId, PDO::PARAM_INT);
	
	$message = '';
	$result = '';
	$errorInfo = null;
	$data = $section;
	
	$numSectionErrors = 0;
	$numSettingErrors = 0;
	$numSectionUpdates = 0;
	$numSectionSettingUpdates = 0;
	$numSectionSettingInsertions = 0;
	
	if ($updateSectionSTMT->execute()) {
		if ($updateSectionSTMT->rowCount() > 0) {
			$result = 'success';
			$message = 'Successfully updated the section #' . $sectionNewId;
			$numSectionUpdates++;
		}
		else {
			$result = 'notUpdate';
			$message = 'The section #' . $sectionNewId . ' is already up to date';
		}
	}
	else {
		$result = 'error';
		$message = 'Did not execute the statement to update the section #' . $sectionNewId;
		$errorInfo = $updateSectionSTMT->errorInfo();
		$numSectionErrors++;
	}
	
	$results['section'] = array(
		'result' => $result, 
		'message' => $message, 
		'errorInfo' => $errorInfo, 
		'data' => $section
	);
	
	if (array_key_exists('settings', $section)) { 
	if (!empty($section['settings'])) {
		
		$results['section_settings'] = array();
		
		$selectSectionSettingSTMT = $conn->prepare('SELECT * FROM section_settings 
		WHERE section_id = :selectSetting_sectionId AND locale = :selectSetting_locale AND setting_name = :selectSetting_settingName');
		
		$updateSectionSettingSTMT = $conn->prepare('UPDATE section_settings SET setting_value = :updateSetting_settingValue, setting_type = :updateSetting_settingType
		WHERE section_id = :updateSetting_sectionId AND locale = :updateSetting_locale AND setting_name = :updateSetting_settingName');
		
		$insertSectionSettingSTMT = $conn->prepare('INSERT INTO section_settings (section_id, locale, setting_name, setting_value, setting_type) VALUES (
		:insertSetting_sectionId, :insertSetting_locale, :insertSetting_settingName, :insertSetting_settingValue, :insertSetting_settingType)');
		
		$selectSectionSettingSTMT->bindParam(':selectSetting_sectionId', $sectionNewId, PDO::PARAM_INT);
		$updateSectionSettingSTMT->bindParam(':updateSetting_sectionId', $sectionNewId, PDO::PARAM_INT);
		$insertSectionSettingSTMT->bindParam(':insertSetting_sectionId', $sectionNewId, PDO::PARAM_INT);
		
		foreach ($section['settings'] as $setting) {
			
			$message = '';
			$result = '';
			$errorInfo = null;
			
			if ($setting['setting_value'] !== '' && $setting['setting_value'] !== null) {
				
				//fill the selectSectionSettingSTMT
				$selectSectionSettingSTMT->bindParam(':selectSetting_locale', $setting['locale'], PDO::PARAM_STR);
				$selectSectionSettingSTMT->bindParam(':selectSetting_settingName', $setting['setting_name'], PDO::PARAM_STR);
				
				if ($selectSectionSettingSTMT->execute()) {
					if ($dbSectionSetting = $selectSectionSettingSTMT->fetch(PDO::FETCH_ASSOC)) {
						
						if ($setting['setting_value'] !== $dbSectionSetting['setting_value']) {
							//update the setting
							$updateSectionSettingSTMT->bindParam(':updateSetting_locale', $dbSectionSetting['locale'], PDO::PARAM_STR);
							$updateSectionSettingSTMT->bindParam(':updateSetting_settingName', $dbSectionSetting['setting_name'], PDO::PARAM_STR);
							$updateSectionSettingSTMT->bindParam(':updateSetting_settingValue', $setting['setting_value'], PDO::PARAM_STR);
							$updateSectionSettingSTMT->bindParam(':updateSetting_settingType', $setting['setting_type'], PDO::PARAM_STR);
							
							if ($updateSectionSettingSTMT->execute()) {
								if ($updateSectionSettingSTMT->rowCount() > 0) {
									$result = 'success';
									$message = 'Successfully updated the section #' . $sectionNewId . ' ' . $setting['setting_name'];
									$numSectionSettingUpdates++;
								}
								else {
									$result = 'error';
									$message = 'Did not update the section #' . $sectionNewId . ' ' . $setting['setting_name'];
									$numSettingErrors++;
								}
							}
							else {
								$result = 'error';
								$message = 'Did not execute the statement to update the section setting';
								$errorInfo = $updateSectionSettingSTMT->errorInfo();
								$numSettingErrors++;
							}
						}
						//else {
						//    do not need to update
						//}
						
					}//end of the if fetchedSection
					
					else { // the section setting does not exist
						//insert the setting
						$insertSectionSettingSTMT->bindParam(':insertSetting_locale', $setting['locale'], PDO::PARAM_STR);
						$insertSectionSettingSTMT->bindParam(':insertSetting_settingName', $setting['setting_name'], PDO::PARAM_STR);
						$insertSectionSettingSTMT->bindParam(':insertSetting_settingValue', $setting['setting_value'], PDO::PARAM_STR);
						$insertSectionSettingSTMT->bindParam(':insertSetting_settingType', $setting['setting_type'], PDO::PARAM_STR);
						
						if ($insertSectionSettingSTMT->execute()) {
							if ($insertSectionSettingSTMT->rowCount() > 0) {
								$result = 'success';
								$message = 'Successfully inserted the section #' . $sectionNewId . ' ' . $setting['setting_name'];
								$numSectionSettingInsertions++;
							}
							else {
								$result = 'error';
								$message = 'Did not insert the section #' . $sectionNewId . ' ' . $setting['setting_name'];
								$numSettingErrors++;
							}
						}
						else {
							$result = 'error';
							$message = 'Did not execute the statement to insert the section setting';
							$errorInfo = $insertSectionSettingSTMT->errorInfo();
							$numSettingErrors++;
						}
					}
					
				}//end of the if selectSectionSetting executed
				else {
					$result = 'error';
					$message = 'Did not execute the statement to select the section #' . $sectionNewId . ' ' . $setting['setting_name'];
					$errorInfo = $selectSectionSettingSTMT->errorInfo();
					$numSettingErrors++;
				}
				
				$resultArray = array(
					'result' => $result, 
					'message' => $message, 
					'errorInfo' => $errorInfo, 
					'data' => $setting
				);
				array_push($results['section_settings'], $resultArray);
			
			}// closing the if setting value not null nor empty string
		}//end fo the foreach section settings
		
	}//closing the if section settings not empty	
	}//closing the if section settings exists
	
	$results['numSettingErrors'] = $numSettingErrors;
	$results['numSectionErrors'] = $numSectionErrors;
	$results['totalErrors'] = $numSectionErrors + $numSettingErrors;
	
	$results['numUpdates'] = array('section' => $numSectionUpdates, 'section_settings' => $numSectionSettingUpdates);
	$results['numInsertions'] = array('section_settings' => $numSectionSettingInsertions);
	
	return $results;
	
}
//end of updateSection


// #05)
function insertSections(&$xml, $conn, &$dataMapping, $journalNewId, $args = null) {
	
	$limit = 10;
	
	if (is_array($args)) {
		if (array_key_exists('limit', $args)) {
			$limit = $args['limit'];
		}
	}
	
	if (!array_key_exists('section_id', $dataMapping)) {
		$dataMapping['section_id'] = array();
	}
	if (!array_key_exists('review_form_id', $dataMapping)) {
		$dataMapping['review_form_id'] = array();
	}
	if (!array_key_exists('review_form_element_id', $dataMapping)) {
		$dataMapping['review_form_element_id'] = array();
	}
	
	///////  THE STATEMENTS  ////////////////////////////////////////////////////////////
	
	$getSectionsSTMT = $conn->prepare('SELECT * FROM sections WHERE journal_id = :getSections_journalId');
	$getSectionSettingsSTMT = $conn->prepare('SELECT * FROM section_settings WHERE section_id = :getSectionSettings_sectionId');
	
	// sections
	$insertSectionSTMT = $conn->prepare('INSERT INTO sections (journal_id, review_form_id, seq, editor_restricted, meta_indexed, meta_reviewed, abstracts_not_required, 
	hide_title, hide_author, hide_about, disable_comments, abstract_word_count) VALUES (:section_journalId, :section_reviewFormId, :section_seq, :section_editorRestricted, 
	:section_metaIndexed, :section_metaReviewed, :section_abstractsNotRequired, :section_hideTitle, :section_hideAuthor, :section_hideAbout, :section_disableComments, 
	:section_abstractWordCount)');
	
	$lastSectionsSTMT = $conn->prepare("SELECT * FROM sections ORDER BY section_id DESC LIMIT $limit");
	
	$insertSectionSettingsSTMT = $conn->prepare('INSERT INTO section_settings (section_id, locale, setting_name, setting_value, setting_type) VALUES (:sectionSettings_sectionId,
	:sectionSettings_locale, :sectionSettings_settingName, :sectionSettings_settingValue, :sectionSettings_settingType)');
	
	$sections_node = null;
	
	if ($xml->nodeName === 'sections') {
		$sections_node = $xml;
	}
	else {
		$sections_node = $xml->getElementsByTagName('sections')->item(0);
	}
	
	$errors = array(
		'section' => array('insert' => array(), 'update' => array()),
		'section_settings' => array('insert' => array(), 'update' => array()),
	);
	
	$sections = xmlToArray($sections_node, true); //from helperFunctions.php
	
	$numInsertions = array('sections' => 0, 'section_settings' => 0);
	$numUpdates = array('sections' => 0, 'section_settings' => 0);
	
	$updatesLog = array();
	
	foreach ($sections as &$section) {
		
		if (array_key_exists($section['section_id'], $dataMapping['section_id'])) {
			$sectionId = $section['section_id'];
			$mappedId = $dataMapping['section_id'][$sectionId];
			
			if ($mappedId === null || $mappedId === 0 || $mappedId == '') {
				//the section was not imported
			}
			else {
				echo "\nThe section #" . $section['section_id'] . " was already imported.\n";
				echo "Its new id is '$mappedId' \n";
				
				//updating the section
				echo '    updating the section ......... ';
				$updateResults = updateSection($conn, $section, $mappedId, $dataMapping);
				echo $updateResults['section']['result'] . "\n";
				
				array_push($updatesLog, $updateResults); //store the updateResults in a log of the updates
				
				if ($updateResults['totalErrors'] > 0) { //log the errors if there were any
					
					//if there was an error in the section update step
					if ($updateResults['numSectionErrors'] > 0) {
						array_push($errors['section']['update'], $updateResults['section']);
					}
					
					//if there were errors in the section_settings update step
					if ($updateResults['numSettingErrors'] > 0) {
						foreach ($updateResults['section_settings'] as $result) {
							if ($result['result'] === 'error') {
								array_push($errors['section_settings']['update'], $result);
							}
						}// end of the foreach updateResults
					}//end of the if numSettingErrors > 0
					
				}//end of the if totalErrors > 0
				
				
				$numInsertions['section_settings'] += $updateResults['numInsertions']['section_settings'];
				$numUpdates['section_settings'] += $updateResults['numUpdates']['section_settings'];
				$numUpdates['section'] += $updateResults['numUpdates']['section'];
				
				continue; // go to the next section
			}
		}
		
		///////////////  TEST IF THE SECTION ALREADY EXISTS IN THE JOURNAL ///////////////////
		if ($data = sectionExists($conn, $journalNewId, $section)) {
			$messages = $data['messages'];
			if (count($messages) > 0) {
				echo "\nThe following messages were generated: " . print_r($messages, true) . "\n";
				$resp = readline('Do you wish to continue the sections importation? (y/N) : ');
				
				if (strtolower($resp) !== 'y' && strtolower($resp) !== 'yes') {
					return false;
				}
				
			}
			
			if (array_key_exists('section', $data)) {
				$fetchedSection = $data['section'];
			
				if ($fetchedSection) {
					//map the section
					if (is_array($fetchedSection)) { if (array_key_exists('section_id', $fetchedSection)) {
						
						//mapping the section_id
						$dataMapping['section_id'][$section['section_id']] = $fetchedSection['section_id'];

						///////////////// updating the section ////////////////////////////////////
						echo '    updating the section #' . $fetchedSection['section_id']. ' ......... ';
						$updateResults = updateSection($conn, $section, $fetchedSection['section_id'], $dataMapping);
						echo $updateResults['section']['result'] . "\n";
						
						array_push($updatesLog, $updateResults); //store the updateResults in a log of the updates
						
						if ($updateResults['totalErrors'] > 0) { //log the errors if there were any
							
							//if there was an error in the section update step
							if ($updateResults['numSectionErrors'] > 0) {
								array_push($errors['section']['update'], $updateResults['section']);
							}
							
							//if there were errors in the section_settings update step
							if ($updateResults['numSettingErrors'] > 0) {
								foreach ($updateResults['section_settings'] as $result) {
									if ($result['result'] === 'error') {
										array_push($errors['section_settings']['update'], $result);
									}
								}// end of the foreach updateResults
							}//end of the if numSettingErrors > 0
							
						}//end of the if totalErrors > 0
						
						
						$numInsertions['section_settings'] += $updateResults['numInsertions']['section_settings'];
						$numUpdates['section_settings'] += $updateResults['numUpdates']['section_settings'];
						$numUpdates['section'] += $updateResults['numUpdates']['section'];
						
						/////////////////// end of updating the section ////////////////////////////
						
					}}
					
					continue; // go to the next section
					
				}//end of the if fetchedSection
			}//end of the if key exists section
			
		}
		///////////////////////////////////////////////////////////////////////////////////////
		
		/// from now on is just the regular section insertion 
		
		$section['journal_new_id'] = $journalNewId;
		
		validateData('section', $section);
	
		$section['review_form_new_id'] = null;
		
		if ($section['review_form_id'] !== null && array_key_exists($section['review_form_id'], $dataMapping['review_form_id'])) {
			$section['review_form_new_id'] = $dataMapping['review_form_id'][$section['review_form_id']];
		}
		
		$sectionOk = false;
		
		echo 'inserting section #'. $section['section_id'] . ' .........'; 
		
		$arr = array();
		$arr['data'] = $section;
		$arr['params'] = array(
			array('name' => ':section_journalId', 'attr' => 'journal_new_id', 'type' => PDO::PARAM_INT),
			array('name' => ':section_reviewFormId', 'attr' => 'review_form_new_id', 'type' => PDO::PARAM_INT),
			array('name' => ':section_seq', 'attr' => 'seq'),
			array('name' => ':section_editorRestricted', 'attr' => 'editor_restricted', 'type' => PDO::PARAM_INT),
			array('name' => ':section_metaIndexed', 'attr' => 'meta_indexed', 'type' => PDO::PARAM_INT),
			array('name' => ':section_metaReviewed', 'attr' => 'meta_reviewed', 'type' => PDO::PARAM_INT),
			array('name' => ':section_abstractsNotRequired', 'attr' => 'abstracts_not_required', 'type' => PDO::PARAM_INT),
			array('name' => ':section_hideTitle', 'attr' => 'hide_title', 'type' => PDO::PARAM_INT),
			array('name' => ':section_hideAuthor', 'attr' => 'hide_author', 'type' => PDO::PARAM_INT),
			array('name' => ':section_hideAbout', 'attr' => 'hide_about', 'type' => PDO::PARAM_INT),
			array('name' => ':section_disableComments', 'attr' => 'disable_comments', 'type' => PDO::PARAM_INT),
			array('name' => ':section_abstractWordCount', 'attr' => 'abstract_word_count', 'type' => PDO::PARAM_INT)
		);
		
		if (myExecute('insert', 'section', $arr, $insertSectionSTMT, $errors)) { //from helperFunctions.php
			echo "Ok\n";
			$numInsertions['sections']++;
			if (getNewId('section', $lastSectionsSTMT, $section, $dataMapping, $errors)) { //from helperFunctions.php
				echo "    new id = " . $section['section_new_id'] . "\n\n";
				$sectionOk = true;
			}
		}
		else {
			echo "Failed\n";
		}
		
		if ($sectionOk) {
			if (array_key_exists('settings', $section)) {
			foreach ($section['settings'] as &$setting) {
				validateData('section_settings', $setting); //from helperFunctions.php
			
				$setting['section_new_id'] = $section['section_new_id'];
				echo '    inserting '. $setting['setting_name'] . ' with locale ' . $setting['locale'] . ' .........'; 
				
				$arr = array();
				$arr['data'] = $setting;
				$arr['params'] = array(
					array('name' => ':sectionSettings_sectionId', 'attr' => 'section_new_id', 'type' => PDO::PARAM_INT),
					array('name' => ':sectionSettings_locale', 'attr' => 'locale', 'type' => PDO::PARAM_STR),
					array('name' => ':sectionSettings_settingName', 'attr' => 'setting_name', 'type' => PDO::PARAM_STR),
					array('name' => ':sectionSettings_settingValue', 'attr' => 'setting_value', 'type' => PDO::PARAM_STR),
					array('name' => ':sectionSettings_settingType', 'attr' => 'setting_type', 'type' => PDO::PARAM_STR)
				);
				
				if (myExecute('insert', 'section_settings', $arr, $insertSectionSettingsSTMT, $errors)) { //from helperFunctions.php
					echo "Ok\n";
					$numInsertions['section_settings']++;
				}
				else {
					echo "Failed\n";
				}
			}//end of foreach section_setting
			unset($setting);
			}//closing the if section_settings exist
		}//closing the if sectionOk
		
	}//end of foreach section
	unset($section);
	
	
	$returnData = array(
		'errors' => $errors, 
		'numInsertedRecords' => $numInsertions, 
		'numUpdatedRecords' => $numUpdates
	);

	return $returnData;
	
}


// #05.99)
function updatePublishedArticle(&$article, $conn, &$dataMapping, $journalNewId, $args = null) {
	$title = null;
	//get the article title to search in the database
	if (array_key_exists('title', $article)) {
		$title = $article['title'];
	}
	else if (array_key_exists('settings', $article)) {
		foreach ($article['settings'] as $setting) {
			if ($setting['setting_name'] === 'title') {
				$title = $setting['setting_value'];
			}
		}
	}
	
	if ($title === null) {
		return false;
	}
	
	$stmt = $conn->prepare(
		'SELECT set.*, art.*, pub_art.* 
		 FROM article_settings AS set
		 INNER JOIN articles AS art 
		 	ON art.article_id = set.article_id
		 INNER JOIN published_articles AS pub_art
		 	ON pub_art.article_id = art.article_id
		 WHERE 
		 	set.setting_name = "title" AND 
		 	set.setting_value = :settingValue AND
		 	art.journal_id = :journalId'
	);
	
	$stmt->bindParam(':settingValue', $title, PDO::PARAM_STR);
	$stmt->bindParam(':journalId', $journalNewId, PDO::PARAM_INT);
	
	$updateArticleSTMT = $conn->prepare(
		'UPDATE articles SET date_submitted = :dateSubmitted, last_modified = :lastModified, date_status_modified = :dateStatusModified
		 WHERE article_id = :articleId'
	);
	
	if ($stmt->execute()) {
		if ($fetchedArticle = $stmt->fetch(PDO::FETCH_ASSOC)) {
			
			//map the article_id 
			if (!array_key_exists($article['article_id'], $dataMapping['article_id'])) {
				$dataMapping['article_id'][$article['article_id']] = $fetchedArticle['article_id'];
			}
			
			//map the published_article_id
			if (!array_key_exists('published_article_id', $dataMapping)) {
				$dataMapping['published_article_id'] = array();
			}
			
			if (!array_key_exists($article['published_article_id'], $dataMapping['published_article_id'])) {
				$dataMapping['published_article_id'][$article['published_article_id']] = $fetchedArticle['published_article_id'];
			}
			
			//map the issue_id
			if (!array_key_exists('issue_id', $dataMapping)) {
				$dataMapping['issue_id'] = array();
			}
			
			if (!array_key_exists($article['issue_id'], $dataMapping['issue_id'])) {
				$dataMapping['issue_id'][$article['issue_id']] = $fetchedArticle['issue_id'];
			}
			
			
			$updateArticleSTMT->bindParam(':dateSubmitted', $article['date_submitted'], PDO::PARAM_STR);
			$updateArticleSTMT->bindParam(':lastModified', $article['last_modified'], PDO::PARAM_STR);
			$updateArticleSTMT->bindParam(':dateStatusModified', $article['date_status_modified'], PDO::PARAM_STR);
			$updateArticleSTMT->bindParam(':articleId', $fetchedArticle['article_id'], PDO::PARAM_INT);
			
			if ($updateArticleSTMT->execute()) {
				return true;
			}
			else {
				//updateArticleSTMT did not execute
				//treat the error
			}
		}
	}
	else {
		//did not execute the statement to fetch the article
		//treat the error
	}
	
}// end of the function updateArticle

// #06)
function insertArticles(&$xml, $conn, &$dataMapping, $journal, $type = 'both', $args = null) {
	
	$journalNewPath = null;
	$journalNewId = null;
	$limit = 10;
	
	if (is_array($args)) {
		if (array_key_exists('journalNewPath', $args)) {
			$journalNewPath = $args['journalNewPath'];
		}
		if (array_key_exists('journalNewId', $args)) {
			$journalNewId = $args['journalNewId'];
		}
		if (array_key_exists('rowsLimit', $args)) {
			$limit = (int) $args['rowsLimit'];
		}
	}
	
	if (is_array($journal)) {
		if (array_key_exists('path', $journal)) {
			$journalNewPath = $journal['path'];
		}
		
		if(array_key_exists('journal_id', $journal)) {
			$journalNewId = $journal['journal_id'];
		}
	}
	
	if ($journalNewPath === null) {
		echo "\nThe journal path was not in journal:\n";
		print_r($journal);
		return -1;
	}
	if ($journalNewId === null) {
		echo "\nThe journal id was not in journal:\n";
		print_r($journal);
		return -1;
	}
	
	$sectionSTMT = $conn->prepare(
		'SELECT * FROM sections WHERE section_id IN (
			SELECT section_id FROM section_settings WHERE setting_name = "title" AND setting_value = :section_title AND locale = :section_locale
		) AND journal_id = :section_journalId ');
	
	$userSTMT = $conn->prepare('SELECT * FROM users WHERE email = :user_email');
	
	$checkUsernameSTMT = $conn->prepare('SELECT COUNT(*) as count FROM users WHERE username = :checkUsername');
	
	$insertUserSTMT = $conn->prepare('INSERT INTO users (username, password, salutation, first_name, middle_name, last_name, gender, initials, email, url, phone, fax, mailing_address,
country, locales, date_last_email, date_registered, date_validated, date_last_login, must_change_password, auth_id, disabled, disabled_reason, auth_str, suffix, billing_address, 
inline_help) VALUES (:insertUser_username, :insertUser_password, :insertUser_salutation, :insertUser_firstName, :insertUser_middleName, :insertUser_lastName, :insertUser_gender, 
:insertUser_initials, :insertUser_email, :insertUser_url, :insertUser_phone, :insertUser_fax, :insertUser_mailingAddress, :insertUser_country, :insertUser_locales, 
:insertUser_dateLastEmail, :insertUser_dateRegistered, :insertUser_dateValidated, :insertUser_dateLastLogin, :insertUser_mustChangePassword, :insertUser_authId, :insertUser_disabled, 
:insertUser_disabledReason, :insertUser_authStr, :insertUser_suffix, :insertUser_billingAddress, :insertUser_inlineHelp)');
	
	$lastUsersSTMT = $conn->prepare("SELECT * FROM users ORDER BY user_id DESC LIMIT $limit");
	
	$userStatements = array('userSTMT' => &$userSTMT, 'checkUsernameSTMT' => &$checkUsernameSTMT, 'insertUserSTMT' => &$insertUserSTMT, 'lastUsersSTMT' => &$lastUsersSTMT);
	
	///// article info////////////////////////////////
	
	$insertArticleSTMT = $conn->prepare('INSERT INTO articles (user_id, journal_id, section_id, language, comments_to_ed, date_submitted, last_modified, date_status_modified,
		status, submission_progress, current_round, pages, fast_tracked, hide_author, comments_status, locale, citations) 
		VALUES (:userId, :journalId, :sectionId, :language, :commentsToEd, :dateSubmitted, :lastModified, :dateStatusModified,
		:status, :submissionProgress, :currentRound, :pages, :fastTracked, :hideAuthor, :commentsStatus, :locale, :citations)');
	
	$updtArticleSTMT = $conn->prepare(
		'UPDATE articles
		SET language = :updtArticle_language, comments_to_ed = :updtArticle_commentsToEd, date_submitted = :updtArticle_dateSubmitted, last_modified = :updtArticle_lastModified, 
		date_status_modified = :updtArticle_dateStatusModified, status = :updtArticle_status, submission_progress = :updtArticle_submissionProgress, 
		current_round = :updtArticle_currentRound, pages = :updtArticle_pages, fast_tracked = :updtArticle_fastTracked, hide_author = :updtArticle_hideAuthor, 
		comments_status = :updtArticle_commentsStatus, locale = :updtArticle_locale, citations = :updtArticle_citations
		WHERE article_id = :updtArticle_articleId'
	);
	
	$updateArticleDatesSTMT = $conn->prepare('UPDATE articles SET
		date_status_modified = :updateArticleDates_dateStatusModified, date_submitted = :updateArticleDates_dateSubmitted,
		last_modified = :updateArticleDates_lastModified WHERE article_id = :updateArticleDates_articleId');
	
	$fetchPublishedArticleBySettingSTMT = $conn->prepare(
		'SELECT art.*, sett.*, pub.* 
		 FROM article_settings AS sett
		 INNER JOIN articles AS art
		 	ON art.article_id = sett.article_id
		 INNER JOIN published_articles AS pub
		 	ON pub.article_id = sett.article_id
		  WHERE art.journal_id = :publishedArticle_journalId AND sett.locale = :publishedArticle_locale AND
		       sett.setting_name = :publishedArticle_settingName AND sett.setting_value = :publishedArticle_settingValue'
	);
	
	$countPublishedArticlesSTMT = $conn->prepare(
		'SELECT COUNT(*) AS count
		 FROM article_settings AS sett
		 INNER JOIN articles AS art
		 	ON art.article_id = sett.article_id
		 INNER JOIN published_articles AS pub
		 	ON pub.article_id = sett.article_id
		  WHERE art.journal_id = :countPublishedArticle_journalId AND sett.locale = :countPublishedArticle_locale AND
		       sett.setting_name = :countPublishedArticle_settingName AND sett.setting_value = :countPublishedArticle_settingValue'
	);
	
	$checkArticleSTMT = $conn->prepare('SELECT * FROM articles WHERE article_id = :checkArticle_articleId');
	
	//get the inserted article_id
	$lastArticlesSTMT = $conn->prepare("SELECT * FROM articles ORDER BY article_id DESC LIMIT $limit"); 
	
	$insertArticleSettingsSTMT = $conn->prepare('INSERT INTO article_settings (article_id, locale, setting_name, setting_value, setting_type) VALUES (:articleSettings_articleId,
		:articleSettings_locale, :articleSettings_settingName, :articleSettings_settingValue, :articleSettings_settingType)');
	
	$checkArticleSettingSTMT = $conn->prepare('SELECT COUNT(*) AS count FROM article_settings WHERE 
		article_id = :checkArticleSetting_articleId AND locale = :checkArticleSetting_locale AND setting_name = :checkArticleSetting_settingName');
	
	$updtArticleSettingSTMT = $conn->prepare('UPDATE article_settings 
		SET setting_value = :updtArticleSetting_settingValue, setting_type = :updtArticleSetting_settingType
		WHERE article_id = :updtArticleSetting_articleId AND locale = :updtArticleSetting_locale AND setting_name = :updtArticleSetting_settingName');
	
	//////////////  the article authors  ///////////////////////////////////////////////////////////////
	//see if the author is already registered
	$getAuthorSTMT = $conn->prepare('SELECT * FROM authors WHERE email = :getAuthor_email '); 
	
	$insertAuthorSTMT = $conn->prepare('INSERT INTO authors (submission_id, primary_contact, seq, first_name, middle_name, last_name, country,
		email, url, suffix) VALUES (:author_submissionId, :author_primaryContact, :author_seq, :author_firstName, :author_middleName, :author_lastName, :author_country,
		:author_email, :author_url, :author_suffix)');
	
	$insertAuthorSettingsSTMT = $conn->prepare('INSERT INTO author_settings (author_id, locale, setting_name, setting_value, setting_type) VALUES (:authorSettings_authorId,
		:authorSettings_locale, :authorSettings_settingName, :authorSettings_settingValue, :authorSettings_settingType)');
	
	$lastAuthorsSTMT = $conn->prepare("SELECT * FROM authors ORDER BY author_id DESC LIMIT $limit"); 
	////////////////////////////////////////////////////////////////////////////////////////////////////
	
	$insertArticleFileSTMT = $conn->prepare('INSERT INTO article_files (revision, source_revision, article_id, file_name, file_type, file_size, original_file_name, file_stage, 
		viewable, date_uploaded, date_modified, round, assoc_id) VALUES (:file_revision, :file_sourceRevision, :file_articleId, :file_fileName, :file_fileType, :file_fileSize, 
		:file_originalFileName, :file_fileStage, :file_viewable, :file_dateUploaded, :file_dateModified, :file_round, :file_assocId)');
	
	$lastArticleFilesSTMT = $conn->prepare("SELECT * FROM article_files ORDER BY file_id DESC LIMIT $limit");
	
	$insertArticleFileRevisedSTMT = $conn->prepare('INSERT INTO article_files 
		(file_id, revision, source_revision, article_id, file_name, file_type, file_size, 
		original_file_name, file_stage, viewable, date_uploaded, date_modified, round, assoc_id) 
		VALUES (:fileRev_fileId, :fileRev_revision, :fileRev_sourceRevision, :fileRev_articleId, :fileRev_fileName, :fileRev_fileType, :fileRev_fileSize, 
		:fileRev_originalFileName, :fileRev_fileStage, :fileRev_viewable, :fileRev_dateUploaded, :fileRev_dateModified, :fileRev_round, :fileRev_assocId)');
	
	$checkRevisedFileSTMT = $conn->prepare('SELECT * FROM article_files WHERE file_id = :checkRev_fileId AND revision = :checkRev_revision');
	
	$updtArticleFileSTMT = $conn->prepare(
		'UPDATE article_files 
		SET source_revision = :updtArticleFile_sourceRevision, file_stage = :updtArticleFile_fileStage , viewable = :updtArticleFile_viewable, 
			date_uploaded = :updtArticleFile_dateUploaded, date_modified = :updtArticleFile_dateModified, round = :updtArticleFile_round, assoc_id = :updtArticleFile_assocId
		WHERE file_id = :updtArticleFile_fileId AND revision = :updtArticleFile_revision');
	
	//article_supplementary_file  ///////
	$insertArticleSuppFileSTMT = $conn->prepare('INSERT INTO article_supplementary_files (file_id, article_id, type, language, date_created, show_reviewers, date_submitted, seq, 
		remote_url) VALUES (:supp_fileId, :supp_articleId, :supp_type, :supp_language, :supp_dateCreated, :supp_showReviewers, :supp_dateSubmitted, :supp_seq, :supp_remoteUrl)');
	
	$lastArticleSuppFilesSTMT = $conn->prepare("SELECT * FROM article_supplementary_files ORDER BY supp_id DESC LIMIT $limit");
	
	$insertArticleSuppFileSettingSTMT = $conn->prepare('INSERT INTO article_supp_file_settings (supp_id, locale, setting_name, setting_value, setting_type) VALUES (:suppSettings_suppId,
		:suppSettings_locale, :suppSettings_settingName, :suppSettings_settingValue, :suppSettings_settingType)');
	
	$insertArticleNoteSTMT = $conn->prepare('INSERT INTO article_notes (article_id, user_id, date_created, date_modified, title, note, file_id) VALUES (:note_articleId, :note_userId,
		:note_dateCreated, :note_dateModified, :note_title, :note_note, :note_fileId)');
	
	$lastArticleNotesSTMT = $conn->prepare("SELECT * FROM article_notes ORDER BY note_id DESC LIMIT $limit");
	
	$insertArticleCommentSTMT = $conn->prepare('INSERT INTO article_comments (comment_type, role_id, article_id, assoc_id, author_id, comment_title, comments, date_posted, date_modified,
		viewable) VALUES (:comment_commentType, :comment_roleId, :comment_articleId, :comment_assocId, :comment_authorId, :comment_commentTitle, :comment_comments, :comment_datePosted,
		:comment_dateModified, :comment_viewable)');
	
	$lastArticleCommentsSTMT = $conn->prepare("SELECT * FROM article_comments ORDER BY comment_id DESC LIMIT $limit");
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////
	
	
	//////////  galleys /////////////////////////////////////////////////////////////////////////
	$insertArticleGalleySTMT = $conn->prepare('INSERT INTO article_galleys (locale, article_id, file_id, label, html_galley, style_file_id, seq, remote_url) VALUES (:articleGalley_locale,
		:articleGalley_articleId, :articleGalley_fileId, :articleGalley_label, :articleGalley_htmlGalley, :articleGalley_styleFileId, :articleGalley_seq, :articleGalley_remoteUrl)');
	
	$lastArticleGalleysSTMT = $conn->prepare("SELECT * FROM article_galleys ORDER BY galley_id DESC LIMIT $limit");
	
	$insertArticleGalleySettingSTMT = $conn->prepare('INSERT INTO article_galley_settings (galley_id, locale, setting_name, setting_value, setting_type) VALUES (:galleySetting_galleyId,
		:galleySetting_locale, :galleySetting_settingName, :galleySetting_settingValue, :galleySetting_settingType)');
	
	$insertArticleXmlGalleySTMT = $conn->prepare('INSERT INTO article_xml_galleys (galley_id, article_id, label, galley_type, views) VALUES (:xmlGalley_galleyId, :xmlGalley_articleId,
		:xmlGalley_label, :xmlGalley_galleyType, :xmlGalley_views)');
	
	$lastArticleXmlGalleysSTMT = $conn->prepare("SELECT * FROM article_xml_galleys ORDER BY xml_galley_id DESC LIMIT $limit");
	
	$insertArticleHtmlGalleyImageSTMT = $conn->prepare('INSERT INTO article_html_galley_images (galley_id, file_id) VALUES (:galleyImage_galleyId, :galleyImage_fileId)');
	/////////////////////////////////////////////////////////////////////////////////////////////////////
	
	
	/////////////////  the article search keywords  ////////////////////////////////////////
	$getKeywordSTMT = $conn->prepare('SELECT * FROM article_search_keyword_list WHERE keyword_text = :getKeyword_keywordText');
	$insertArticleSearchKeywordListSTMT = $conn->prepare('INSERT INTO article_search_keyword_list (keyword_text) VALUES (:keywordList_keywordText)');
	
	$lastArticleSearchKeywordListsSTMT = $conn->prepare("SELECT * FROM article_search_keyword_list ORDER BY keyword_id DESC LIMIT $limit");
	
	$insertArticleSearchObjectKeywordSTMT = $conn->prepare('INSERT INTO article_search_object_keywords (object_id, keyword_id, pos) VALUES (:objectKeyword_objectId,
		:objectKeyword_keywordId, :objectKeyword_pos)');
	$insertArticleSearchObjectSTMT = $conn->prepare('INSERT INTO article_search_objects (article_id, type, assoc_id) VALUES (:searchObj_articleId, :searchObj_type, :searchObj_assocId)');
	
	$lastArticleSearchObjectsSTMT = $conn->prepare("SELECT * FROM article_search_objects ORDER BY object_id DESC LIMIT $limit");
	/////////////////////////////////////////////////////////////////////////////////////////
	
	//////////////// the edit decisions and assignments  ///////////////////////////////////
	$insertEditDecisionSTMT = $conn->prepare('INSERT INTO edit_decisions (article_id, round, editor_id, decision, date_decided) VALUES (:editDecision_articleId, :editDecision_round,
		:editDecision_editorId, :editDecision_decision, :editDecision_dateDecided)');
	
	$lastEditDecisionsSTMT = $conn->prepare("SELECT * FROM edit_decisions ORDER BY edit_decision_id DESC LIMIT $limit");
	
	$insertEditAssignmentSTMT = $conn->prepare('INSERT INTO edit_assignments (article_id, editor_id, can_edit, can_review, date_assigned, date_notified, date_underway) VALUES (
		:editAssign_articleId, :editAssign_editorId, :editAssign_canEdit, :editAssign_canReview, :editAssign_dateAssigned, :editAssign_dateNotified, :editAssign_dateUnderway)');
	
	$lastEditAssignmentsSTMT = $conn->prepare("SELECT * FROM edit_assignments ORDER BY edit_id DESC LIMIT $limit");
	////////////////////////////////////////////////////////////////////////////////////////
	
	///////////////  the review rounds, assignments and responses  //////////////////////////////////////
	
	$insertReviewRoundSTMT = $conn->prepare('INSERT INTO review_rounds (submission_id, stage_id, round, review_revision, status) VALUES (:revRound_submissionId,
		:revRound_stageId, :revRound_round, :revRound_reviewRevision, :revRound_status)');
	
	$updtRevRoundSTMT = $conn->prepare('UPDATE review_rounds SET 
		stage_id = :updtRevRound_stageId, round = :updtRevRound_round, 
		review_revision = :updtRevRound_reviewRevision, status = :updtRevRound_status
		WHERE review_round_id = :updtRevRound_reviewRoundId');
	
	$checkRevRoundSTMT = $conn->prepare('SELECT * FROM review_rounds WHERE review_round_id = :checkRevRound_reviewRoundId');
	
	$lastReviewRoundsSTMT = $conn->prepare("SELECT * FROM review_rounds ORDER BY review_round_id DESC LIMIT $limit");
	
	$insertReviewAssignmentSTMT = $conn->prepare('INSERT INTO review_assignments (submission_id, reviewer_id, competing_interests, regret_message, recommendation, date_assigned,
	date_notified, date_confirmed, date_completed, date_acknowledged, date_due, last_modified, reminder_was_automatic, declined, replaced, cancelled, reviewer_file_id, date_rated,
	date_reminded, quality, review_round_id, stage_id, review_method, round, step, review_form_id, unconsidered) VALUES (:revAssign_submissionId, :revAssign_reviewerId,
	:revAssign_competingInterests, :revAssign_regretMessage, :revAssign_recommendation, :revAssign_dateAssigned, :revAssign_dateNotified, :revAssign_dateConfirmed, 
	:revAssign_dateCompleted, :revAssign_dateAcknowledged, :revAssign_dateDue, :revAssign_lastModified, :revAssign_reminderAuto, :revAssign_declined, :revAssign_replaced,
	:revAssign_cancelled, :revAssign_reviewerFileId, :revAssign_dateRated, :revAssign_dateReminded, :revAssign_quality, :revAssign_reviewRoundId, :revAssign_stageId, 
	:revAssign_reviewMethod, :revAssign_round, :revAssign_step, :revAssign_reviewFormId, :revAssign_unconsidered)');
	
	$updtRevAssignSTMT = $conn->prepare('UPDATE review_assignments SET
	competing_interests = :updtRevAssign_competingInterests, regret_message = :updtRevAssign_regretMessage, recommendation = :updtRevAssign_recommendation, 
	date_assigned = :updtRevAssign_dateAssigned, date_notified = :updtRevAssign_dateNotified, date_confirmed = :updtRevAssign_dateConfirmed, 
	date_completed = :updtRevAssign_dateCompleted, date_acknowledged = :updtRevAssign_dateAcknowledged, date_due = :updtRevAssign_dateDue, 
	last_modified = :updtRevAssign_lastModified, reminder_was_automatic = :updtRevAssign_reminderWasAutomatic, declined = :updtRevAssign_declined, 
	replaced = :updtRevAssign_replaced, cancelled = :updtRevAssign_cancelled, reviewer_file_id = :updtRevAssign_reviewerFileId, 
	date_rated = :updtRevAssign_dateRated, date_reminded = :updtRevAssign_dateReminded, quality = :updtRevAssign_quality, 
	review_round_id = :updtRevAssign_reviewRoundId, stage_id = :updtRevAssign_stageId, review_method = :updtRevAssign_reviewMethod, 
	round = :updtRevAssign_round, step = :updtRevAssign_step, review_form_id = :updtRevAssign_reviewFormId, unconsidered = :updtRevAssign_unconsidered
	WHERE review_id = :updtRevAssign_reviewId');
	
	$checkRevAssignSTMT = $conn->prepare('SELECT * FROM review_assignments WHERE review_id = :checkRevAssign_reviewId');
	
	$lastReviewAssignmentsSTMT = $conn->prepare("SELECT * FROM review_assignments ORDER BY review_id DESC LIMIT $limit");
	
	$insertReviewFormResponseSTMT = $conn->prepare('INSERT INTO review_form_responses (review_form_element_id, review_id, response_type, response_value) 
	VALUES (:response_reviewFormElementId, :reponse_reviewId, :response_responseType, :response_reponseValue)');
	
	$updtReviewFormResponseSTMT = $conn->prepare('UPDATE review_form_responses 
		SET response_type = :updtRevFormResp_responseType, response_value = :updtRevFormResp_reponseValue
		WHERE review_form_element_id = :updtRevFormResp_reviewFormElementId AND review_id = :updtRevFormResp_reviewId');
	
	$checkRevFormRespSTMT = $conn->prepare('SELECT * FROM review_form_responses WHERE 
		review_form_element_id = :checkRevFormResp_reviewFormElementId AND review_id = :checkRevFormResp_reviewId');
	
	////////////////////////////////////////////////////////////////////////////////////////////////////
	
	$selectArticleFilesSTMT = $conn->prepare('SELECT * FROM article_files WHERE article_id = :selectArticleFiles_articleId');
	
	$articlesXml = null;
	
	if (strpos($xml->nodeName, 'articles') !== false) {
		$articlesXml = $xml;
	}
	else {
		switch ($type) {
			case 'published':
				$articlesXml = $xml->getElementsByTagName('published_articles')->item(0);
				break;
				
			case 'unpublished':
				$articlesXml = $xml->getElementsByTagName('unpublished_articles')->item(0);
				break;
				
			default:
				$articlesXml = $xml->getElementsByTagName('articles')->item(0);
				break;
		}
			
	}
	
	$articles = xmlToArray($articlesXml, true); //array with the articles
	
	$insertedUsers = array();
	
	if ($dataMapping === null) {
		$dataMapping = array();
	}
	
	if (!array_key_exists('journal_id', $dataMapping)) {
		$journalOldId = $articlesXml->getAttribute('journal_original_id');
		$dataMapping['journal_id'] = array($journalOldId => $journalNewId);
	}
	
	if (!array_key_exists('article_id', $dataMapping))  $dataMapping['article_id'] = array();
	if (!array_key_exists('author_id', $dataMapping)) $dataMapping['author_id'] = array();
	if (!array_key_exists('comment_id', $dataMapping)) $dataMapping['comment_id'] = array();
	if (!array_key_exists('comment_author_id', $dataMapping)) $dataMapping['comment_author_id'] = array();
	if (!array_key_exists('editor_id', $dataMapping)) $dataMapping['editor_id'] = array();
	if (!array_key_exists('edit_id', $dataMapping)) $dataMapping['edit_id'] = array();
	if (!array_key_exists('edit_decision_id', $dataMapping)) $dataMapping['edit_decision_id'] = array();
	if (!array_key_exists('file_id', $dataMapping)) $dataMapping['file_id'] = array();
	if (!array_key_exists('galley_id', $dataMapping)) $dataMapping['galley_id'] = array();
	if (!array_key_exists('keyword_id', $dataMapping)) $dataMapping['keyword_id'] = array();
	if (!array_key_exists('object_id', $dataMapping)) $dataMapping['object_id'] = array();
	if (!array_key_exists('review_id', $dataMapping)) $dataMapping['review_id'] = array();
	if (!array_key_exists('reviewer_id', $dataMapping)) $dataMapping['reviewer_id'] = array();
	if (!array_key_exists('review_form_id', $dataMapping)) $dataMapping['review_form_id'] = array();
	if (!array_key_exists('review_form_element_id', $dataMapping)) $dataMapping['review_form_element_id'] = array();
	if (!array_key_exists('review_round_id', $dataMapping)) $dataMapping['review_round_id'] = array();
	if (!array_key_exists('section_id', $dataMapping)) $dataMapping['section_id'] = array();
	if (!array_key_exists('supp_id', $dataMapping)) $dataMapping['supp_id'] = array();
	if (!array_key_exists('user_id', $dataMapping)) $dataMapping['user_id'] = array();
	if (!array_key_exists('xml_galley_id', $dataMapping)) $dataMapping['xml_galley_id'] = array();
	if (!array_key_exists('file_name', $dataMapping)) $dataMapping['file_name'] = array();
	if (!array_key_exists('original_file_name', $dataMapping)) $dataMapping['original_file_name'] = array();
	if (!array_key_exists('published_article_id', $dataMapping)) $dataMapping['published_article_id'] = array();
		
	$errors = array(
		'article' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'article_settings' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'article_file' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'article_supplementary_file' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'article_supp_file_settings' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'article_note' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'article_comment' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'article_galley' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'article_galley_settings' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'article_xml_galley' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'article_html_galley_image' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'article_search_object' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'article_search_object_keyword' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'article_search_keyword_list' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'edit_assignment' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'edit_decision' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'author' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'author_settings' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'review_assignment' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'review_form_response' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'review_round' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'user' => array('insert' => array(), 'update' => array(), 'check' => array())
	);	
	
	$numInsertions = array(
		'articles' => 0,
		'article_settings' => 0,
		'article_files' => 0,
		'article_supplementary_files' => 0,
		'article_supp_file_settings' => 0,
		'article_notes' => 0,
		'article_comments' => 0,
		'article_galleys' => 0,
		'article_galley_settings' => 0,
		'article_xml_galleys' => 0,
		'article_html_galley_images' => 0,
		'article_search_objects' => 0,
		'article_search_object_keywords' => 0,
		'article_search_keyword_list' => 0,
		'edit_assignments' => 0,
		'edit_decisions' => 0,
		'authors' => 0,
		'author_settings' => 0,
		'review_assignments' => 0,
		'review_form_responses' => 0,
		'review_rounds' => 0,
	);  
	
	$numUpdates = array(
		'articles' => 0,
		'article_files' => 0,
		'article_supplementary_files' => 0,
		'review_assignments' => 0,
		'review_form_responses' => 0,
		'review_rounds' => 0
	);
	
	
	///////////////////  BEGINNING OF THE INSERT STAGE  //////////////////////////////////////////////////////////////////////////
	
	//loop through every article to insert the preliminary data in the database
	//and map the id's in the old database to the id's in the new one
	foreach($articles as &$article) {
		
		// set if the article is published or unpublished
		$published = false;
		if (array_key_exists('published_article_id', $article)) {
			$published = true;
		}
		
		$articleAlreadyImported = false;
		$articleOk = false;
		
		if (array_key_exists($article['article_id'], $dataMapping['article_id'])) {
			
			$article['article_new_id'] = $dataMapping['article_id'][$article['article_id']];
			$articleAlreadyImported = true;
			
			echo "\nThe article #" . $article['article_id'] . " was already imported.\n";
			echo "    Its new id is #" . $article['article_new_id'] . "\n";
			
			$articleOk = true;
		}
		
		//validateData('article', $article); //from helperFunctions.php
		
		$article['journal_new_id'] = $journalNewId;
		
		$userOk = false;
		$sectionOk = false;
		
		
		$articleFileOk = false;
		$articleSuppFileOk = false;
		
		$articleSettings = null;
		$articleFile = null;
		$articleSuppFile = null;
		
		if ($published) {
			
			if (array_key_exists($article['published_article_id'], $dataMapping['published_article_id'])) {
				$article['DNU'] = true; // means do not update the article
			}
			else {
				// update the published articles and map the published_article_id
				
				$error = array(
					'published_article_id' => $article['published_article_id'], 
					'article_id' => $article['article_id']
				);
			
				//search the published article by title
				$titles = null;
				if (array_key_exists('settings', $article)) { if (!empty($article['settings'])) {
					
					$titles = getArticleTitles($article['settings']); // from helperFunctions.php function #32
					
				}// end of the setting not empty
				}// end of the if article settings exist
				
				if ($titles === null) {
					//article has no setting
					$error['error'] = 'The article has no setting';
				}
				
				$fetchedTheArticle = false;
				$fetchedArticle = null;
				
				$settingName = 'title';
				
				$fetchPublishedArticleBySettingSTMT->bindParam(':publishedArticle_journalId', $journalNewId, PDO::PARAM_INT);
				$fetchPublishedArticleBySettingSTMT->bindParam(':publishedArticle_settingName', $settingName, PDO::PARAM_STR);
				$countPublishedArticlesSTMT->bindParam(':countPublishedArticle_journalId', $journalNewId, PDO::PARAM_INT);
				$countPublishedArticlesSTMT->bindParam(':countPublishedArticle_settingName', $settingName, PDO::PARAM_STR);
				
				foreach ($titles as $title) {
					
					
					$countPublishedArticlesSTMT->bindParam(':countPublishedArticle_locale', $title['locale'], PDO::PARAM_STR);
					$countPublishedArticlesSTMT->bindParam(':countPublishedArticle_settingValue', $title['title'], PDO::PARAM_STR);
					
					if ($countPublishedArticlesSTMT->execute()) {
						$numMatches = $countPublishedArticlesSTMT->fetchColumn();
						
						if ($numMatches == 1) {
							//found the right one
							
							$fetchPublishedArticleBySettingSTMT->bindParam(':publishedArticle_locale', $title['locale'], PDO::PARAM_STR);
							$fetchPublishedArticleBySettingSTMT->bindParam(':publishedArticle_settingValue', $title['title'], PDO::PARAM_STR);
							if ($fetchPublishedArticleBySettingSTMT->execute()) {
								if ($fetchedArticle = $fetchPublishedArticleBySettingSTMT->fetch(PDO::FETCH_ASSOC)) {
									$fetchedTheArticle = true;
								}
							}
							else {
								//the fetchPublishedArticleBySettingSTMT did not execute
								$error['error'] = 'The fetchPublishedArticleBySettingSTMT did not execute';
								$error['errorInfo'] = $fetchPublishedArticleBySettingSTMT->errorInfo();
							}
							
							break; // breaking out of the foreach titles
							
						}
						else if ($numMatches > 1) {
							//found multiple articles with that title
							$error['error'] = 'Found ' . $numMatches . ' articles with title "' . $title['title'] . '"';
						}
						else if ($numMatches == 0) {
							//that is probably an error of charset
							//if not, then something is really wrong
							$error['error'] = 'Did not find the article with title "' . $title['title'] . '" in the database';
						}
						
					}//end of the if countPublishedArticlesSTMT executed
					else {
						$error['error'] = 'countPublishedArticlesSTMT did not execute';
						$error['errorInfo'] = $countPublishedArticlesSTMT->errorInfo();
					}
					
					
				}//end of the foreach titles
				
				if ($fetchedTheArticle) {
					//update the article 
					
					if (!array_key_exists('issue_id', $dataMapping)) $dataMapping['issue_id'] = array();
					if (!array_key_exists($article['issue_id'], $dataMapping['issue_id'])) {
						$dataMapping['issue_id'][$article['issue_id']] = $fetchedArticle['issue_id'];
					}
					
					if (!array_key_exists($article['published_article_id'], $dataMapping['published_article_id'])) {
						$dataMapping['published_article_id'][$article['published_article_id']] = $fetchedArticle['published_article_id'];
					}
					
					if (!array_key_exists($article['article_id'], $dataMapping['article_id'])) {
						$dataMapping['article_id'][$article['article_id']] = $fetchedArticle['article_id'];
					}
					
					$article['article_new_id'] = $fetchedArticle['article_id'];
					
					$articleOk = true;
					$updateArticle = false;
						
						//update the article if at least one of the file_ids is not mapped, which would mean the file_id changed
						
					if ($article['submission_file_id'] != null && $article['submission_file_id'] != '') {
						if (!array_key_exists($fetchedArticle['submission_file_id'], $dataMapping['file_id'])) {
							$updateArticle = true;
						}
					}
					if ($article['revised_file_id'] != null && $article['revised_file_id'] != '') {
						if (!array_key_exists($fetchedArticle['revised_file_id'], $dataMapping['file_id'])) {
							$updateArticle = true;
						}
					}
					if ($article['review_file_id'] != null && $article['review_file_id'] != '') {
						if (!array_key_exists($fetchedArticle['review_file_id'], $dataMapping['file_id'])) {
							$updateArticle = true;
						}
					}
					if ($article['editor_file_id'] != null && $article['editor_file_id'] != '') {
						if (!array_key_exists($fetchedArticle['editor_file_id'], $dataMapping['file_id'])) {
							$updateArticle = true;
						}
					}
						
					$args = array();
					$args['compare'] = 'almost all';
					$args['notCompare'] = ['submission_file_id', 'revised_file_id', 'review_file_id', 'editor_file_id'];
					
					if (same2($article, $fetchedArticle, $args) > 0) { //from helperFunctions.php function #16
						
						if (!$updateArticle) {
							$article['DNU'] = true; // DNU means Do Not Update
						}
						
					}//end of the if same2
					else { //update the article
						
						validateData('article', $article);
						
						$arr = array();
						$arr['data'] = $article;
						$arr['params'] = array(
							array('name' => ':updtArticle_language', 'attr' => 'language', 'type' => PDO::PARAM_STR),
							array('name' => ':updtArticle_commentsToEd', 'attr' => 'comments_to_ed', 'type' => PDO::PARAM_STR),
							array('name' => ':updtArticle_dateSubmitted', 'attr' => 'date_submitted', 'type' => PDO::PARAM_STR),
							array('name' => ':updtArticle_lastModified', 'attr' => 'last_modified', 'type' => PDO::PARAM_STR),
							array('name' => ':updtArticle_dateStatusModified', 'attr' => 'date_status_modified', 'type' => PDO::PARAM_STR),
							array('name' => ':updtArticle_status', 'attr' => 'status', 'type' => PDO::PARAM_INT),
							array('name' => ':updtArticle_submissionProgress', 'attr' => 'submission_progress', 'type' => PDO::PARAM_INT),
							array('name' => ':updtArticle_currentRound', 'attr' => 'current_round', 'type' => PDO::PARAM_INT),
							array('name' => ':updtArticle_pages', 'attr' => 'pages', 'type' => PDO::PARAM_STR),
							array('name' => ':updtArticle_fastTracked', 'attr' => 'fast_tracked', 'type' => PDO::PARAM_INT),
							array('name' => ':updtArticle_hideAuthor', 'attr' => 'hide_author', 'type' => PDO::PARAM_INT),
							array('name' => ':updtArticle_commentsStatus', 'attr' => 'comments_status', 'type' => PDO::PARAM_INT),
							array('name' => ':updtArticle_locale', 'attr' => 'locale', 'type' => PDO::PARAM_STR),
							array('name' => ':updtArticle_citations', 'attr' => 'citations', 'type' => PDO::PARAM_STR),
							array('name' => ':updtArticle_articleId', 'attr' => 'article_new_id', 'type' => PDO::PARAM_INT),
						);
						
						echo "\nupdating article #" . $article['article_new_id'] . " ......... "; 
						
						if (myExecute('update', 'article', $arr, $updtArticleSTMT, $errors)) { //from helperFunctions.php
							echo "Ok\n";
							$numUpdates['articles']++;
						}
						else {
							echo "Failed\n";
							$articleOk = false;
						}
						
					}
					
					
				}//end of the if fetchedTheArticle
				else if (!array_key_exists('error', $error)){
					//did not fetch the article
					$error['error'] = 'Did not fetch the article';
				}
				
				if (array_key_exists('error', $error)) {
					array_push($errors['article']['update'], $error);
				}
				
				//// end of the block to update the published article ////////////////
			}//end of the else pub_id not in dataMapping
			
			//////////// end of the block to process the published articles /////////////
			
		}// end of the if published
		
		else if (!$articleAlreadyImported){ // the article is unpublished //////////////////////////////////////////////////
		
		$error = array('article_id' => $article['article_id']);
		
		if ($article['user']['username'] === null || !array_key_exists('username', $article['user'])) {
			$error['userError'] = 'The username is abscent.';
		}
		else {
			// get the user that owns the article to identify on the new journal  ///////////////////
			if (is_array($article['user'])) {
				$userOk = processUser($article['user'], array('type' => 'article', 'data' => $article), $dataMapping, 
					$errors, $insertedUsers, $userStatements, $conn);
			}
		}
		
		
		
		// get the section new id //////////////////////////////////////
		
		if (array_key_exists($article['section_id'], $dataMapping['section_id'])) {
			$article['section_new_id'] = (int) $dataMapping['section_id'][$article['section_id']];
			$sectionOk = true;
		}
		else {
			//the section_id is not yet on dataMapping
			$section = $article['section'];
			
			$locales = array_keys($section);
			
			// if original_id is one one the keys remove it from the array $locales
			$pos = array_search('original_id', $locales);
			
			if ($pos !== false) {
				array_splice($locales, $pos, 1); //remove the element at position $pos
			}
			///////////////////////////////////////////////////////////////////////
			
			foreach ($locales as $locale) {
				if (array_key_exists('title', $section[$locale])) {
					$sectionSTMT->bindParam(':section_title', $section[$locale]['title'], PDO::PARAM_STR);
					$sectionSTMT->bindParam(':section_locale', $locale, PDO::PARAM_STR);
					break;
				}
			}
			
			$sectionSTMT->bindParam(':section_journalId', $journalNewId, PDO::PARAM_INT);
			
			if ($sectionSTMT->execute()) {
				$sectionInfo = $sectionSTMT->fetch(PDO::FETCH_ASSOC);
				$article['section_new_id'] = (int) $sectionInfo['section_id'];
				$sectionOk = true;
				
				$section['new_id'] = $sectionInfo['section_id'];
				
				if (!array_key_exists($article['section_id'], $dataMapping['section_id'])) {
					$dataMapping['section_id'][$article['section_id']] = $article['section_new_id'];
				}
				
			}
			else {
				//sectionSTMT did not execute
				$error['sectionSTMT'] = ['section' => $section, 'error' => $sectionSTMT->errorInfo()];
				$sectionOk = false;
			}
		}
		
		// end of get the section new id  ///////////////////////////////////
		
		if ($sectionOk && $userOk) {
			
			$article['user_new_id'] = $article['user']['user_new_id'];
			
			validateData('article', $article); //from helperFunctions.php
			
			if (array_key_exists($article['article_id'], $dataMapping['article_id'])) {
				$article['article_new_id'] = $dataMapping['article_id'][$article['article_id']];
				
				//check if need to update the article
				$checkArticleSTMT->bindParam(':checkArticle_articleId', $article['article_new_id'], PDO::PARAM_INT);
				
				$checkError = array('article' => $article);
				
				if ($checkArticleSTMT->execute()) {
					
					if ($fetchedArticle = $checkArticleSTMT->fetch(PDO::FETCH_ASSOC)) {
						
						$updateArticle = false;
						
						//update the article if at least one of the file_ids is not mapped, which would mean the file_id changed
						
						if ($article['submission_file_id'] != null && $article['submission_file_id'] != '') {
							if (!array_key_exists($fetchedArticle['submission_file_id'], $dataMapping['file_id'])) {
								$updateArticle = true;
							}
						}
						if ($article['revised_file_id'] != null && $article['revised_file_id'] != '') {
							if (!array_key_exists($fetchedArticle['revised_file_id'], $dataMapping['file_id'])) {
								$updateArticle = true;
							}
						}
						if ($article['review_file_id'] != null && $article['review_file_id'] != '') {
							if (!array_key_exists($fetchedArticle['review_file_id'], $dataMapping['file_id'])) {
								$updateArticle = true;
							}
						}
						if ($article['editor_file_id'] != null && $article['editor_file_id'] != '') {
							if (!array_key_exists($fetchedArticle['editor_file_id'], $dataMapping['file_id'])) {
								$updateArticle = true;
							}
						}
							
						$args = array();
						$args['compare'] = 'almost all';
						$args['notCompare'] = ['submission_file_id', 'revised_file_id', 'review_file_id', 'editor_file_id'];
						
						if (same2($article, $fetchedArticle, $args) > 0) { //from helperFunctions.php function #16
							
							if (!$updateArticle) {
								$article['DNU'] = true; // DNU means Do Not Update
							}
							
						}//end of the if same2
						else { //update the article
							
							
							$arr = array();
							$arr['data'] = $article;
							$arr['params'] = array(
								array('name' => ':updtArticle_language', 'attr' => 'language', 'type' => PDO::PARAM_STR),
								array('name' => ':updtArticle_commentsToEd', 'attr' => 'comments_to_ed', 'type' => PDO::PARAM_STR),
								array('name' => ':updtArticle_dateSubmitted', 'attr' => 'date_submitted', 'type' => PDO::PARAM_STR),
								array('name' => ':updtArticle_lastModified', 'attr' => 'last_modified', 'type' => PDO::PARAM_STR),
								array('name' => ':updtArticle_dateStatusModified', 'attr' => 'date_status_modified', 'type' => PDO::PARAM_STR),
								array('name' => ':updtArticle_status', 'attr' => 'status', 'type' => PDO::PARAM_INT),
								array('name' => ':updtArticle_submissionProgress', 'attr' => 'submission_progress', 'type' => PDO::PARAM_INT),
								array('name' => ':updtArticle_currentRound', 'attr' => 'current_round', 'type' => PDO::PARAM_INT),
								array('name' => ':updtArticle_pages', 'attr' => 'pages', 'type' => PDO::PARAM_STR),
								array('name' => ':updtArticle_fastTracked', 'attr' => 'fast_tracked', 'type' => PDO::PARAM_INT),
								array('name' => ':updtArticle_hideAuthor', 'attr' => 'hide_author', 'type' => PDO::PARAM_INT),
								array('name' => ':updtArticle_commentsStatus', 'attr' => 'comments_status', 'type' => PDO::PARAM_INT),
								array('name' => ':updtArticle_locale', 'attr' => 'locale', 'type' => PDO::PARAM_STR),
								array('name' => ':updtArticle_citations', 'attr' => 'citations', 'type' => PDO::PARAM_STR),
								array('name' => ':updtArticle_articleId', 'attr' => 'article_new_id', 'type' => PDO::PARAM_INT),
							);
							
							echo "\nupdating article #" . $article['article_new_id'] . " ......... "; 
							
							if (myExecute('update', 'article', $arr, $updtArticleSTMT, $errors)) { //from helperFunctions.php
								echo "Ok\n";
								$numUpdates['articles']++;
							}
							else {
								echo "Failed\n";
								$articleOk = false;
							}
							
						}
					}//end of the if fetched the article
					else {
						// did not fetch the article
						$checkError['error'] = 'Did not fetch the article';
					}
					
				}//end of the if checkArticleSTMT executed
				else {
					//checkArticleSTMT did not execute
					$checkError['error'] = 'The checkArticleSTMT did not execute';
					$checkError['stmtError'] = $checkArticleSTMT->errorInfo();
				}
				
				if (array_key_exists('error', $checkError)) {
					array_push($errors['article']['check'], $checkError);
				}
				
			}
			else {// insert the article
				
				$arr = array();
				$arr['data'] = $article;
				$arr['params'] = array(
					array('name' => ':userId', 'attr' => 'user_new_id', 'type' => PDO::PARAM_INT),
					array('name' => ':journalId', 'attr' => 'journal_new_id', 'type' => PDO::PARAM_INT),
					array('name' => ':sectionId', 'attr' => 'section_new_id', 'type' => PDO::PARAM_INT),
					array('name' => ':language', 'attr' => 'language', 'type' => PDO::PARAM_STR),
					array('name' => ':commentsToEd', 'attr' => 'comments_to_ed', 'type' => PDO::PARAM_STR),
					array('name' => ':dateSubmitted', 'attr' => 'date_submitted', 'type' => PDO::PARAM_STR),
					array('name' => ':lastModified', 'attr' => 'last_modified', 'type' => PDO::PARAM_STR),
					array('name' => ':dateStatusModified', 'attr' => 'date_status_modified', 'type' => PDO::PARAM_STR),
					array('name' => ':status', 'attr' => 'status', 'type' => PDO::PARAM_INT),
					array('name' => ':submissionProgress', 'attr' => 'submission_progress', 'type' => PDO::PARAM_INT),
					array('name' => ':currentRound', 'attr' => 'current_round', 'type' => PDO::PARAM_INT),
					array('name' => ':pages', 'attr' => 'pages', 'type' => PDO::PARAM_STR),
					array('name' => ':fastTracked', 'attr' => 'fast_tracked', 'type' => PDO::PARAM_INT),
					array('name' => ':hideAuthor', 'attr' => 'hide_author', 'type' => PDO::PARAM_INT),
					array('name' => ':commentsStatus', 'attr' => 'comments_status', 'type' => PDO::PARAM_INT),
					array('name' => ':locale', 'attr' => 'locale', 'type' => PDO::PARAM_STR),
					array('name' => ':citations', 'attr' => 'citations', 'type' => PDO::PARAM_STR)
				);
				
				
				echo "\ninserting article #" . $article['article_id'] . " ......... "; 
				
				if (myExecute('insert', 'article', $arr, $insertArticleSTMT, $errors)) { //from helperFunctions.php
					echo "Ok\n";
					$numInsertions['articles']++;
					
					$args = array();
					$args['compare'] = 'almost all';
					$args['notCompare'] = ['submission_file_id', 'revised_file_id', 'review_file_id', 'editor_file_id'];
					
					if (getNewId('article', $lastArticlesSTMT, $article, $dataMapping, $errors, $args)) { //from helperFunctions.php
						echo "article new id = " . $article['article_new_id'] . "\n";
						$articleOk = true;
					}
					
				}
				else {
					echo "Failed\n";
				}
				
			}//end of the block to insert the article
			
		}// end of the if sectionOk and UserOk
		else {
			array_push($errors['article']['insert'], $error);
		}
		
		
		/////////end of the unpublished_articles ////////////
		}
		
		
		//if the article has been correctly inserted
		if ($articleOk) {
			
			//insert the article_settings /////////////////////////
			if (!$published && array_key_exists('settings', $article)) { if (!empty($article['settings']) && $article['settings'] != null) {
			echo "\ninserting article_settings:\n";
			
			//insert each article setting
			foreach($article['settings'] as &$articleSetting) { 
				
				validateData('article_settings', $articleSetting); //from helperFunctions.php
				
				$articleSetting['article_new_id'] = $article['article_new_id'];
				
				//check if the articleSetting exists and if need to be updated
				$checkArticleSettingSTMT->bindParam(':checkArticleSetting_articleId', $articleSetting['article_new_id'], PDO::PARAM_INT);
				$checkArticleSettingSTMT->bindParam(':checkArticleSetting_locale', $articleSetting['locale'], PDO::PARAM_STR);
				$checkArticleSettingSTMT->bindParam(':checkArticleSetting_settingName', $articleSetting['setting_name'], PDO::PARAM_STR);
				
				$checkError = array('article_setting' => $articleSetting);
				
				if ($checkArticleSettingSTMT->execute()) {
					if ($checkArticleSettingSTMT->fetchColumn() > 0) {
						// update the article_setting //////////////////
						
						echo '    updating '. $articleSetting['setting_name'] . ' with locale ' . $articleSetting['locale'] . ' .........'; 
				
						$arr = array();
						$arr['data'] = $articleSetting;
						$arr['params'] = array(
							array('name' => ':updtArticleSetting_articleId', 'attr' => 'article_new_id', 'type' => PDO::PARAM_INT),
							array('name' => ':updtArticleSetting_locale', 'attr' => 'locale', 'type' => PDO::PARAM_STR),
							array('name' => ':updtArticleSetting_settingName', 'attr' => 'setting_name', 'type' => PDO::PARAM_STR),
							array('name' => ':updtArticleSetting_settingValue', 'attr' => 'setting_value', 'type' => PDO::PARAM_STR),
							array('name' => ':updtArticleSetting_settingType', 'attr' => 'setting_type', 'type' => PDO::PARAM_STR)
						);
						
						
						if (myExecute('update', 'article_settings', $arr, $updtArticleSettingSTMT, $errors)) { //from helperFunctions.php
							echo " Ok\n";
							$numUpdates['article_settings']++;
						}
						else {
							echo " Up to date\n";
						}
						
					}// end of the if to update the article_setting ////
					else {
						//the setting does not exist
						/////////////// insert the article_setting /////////////////////////////////////
						echo '    inserting '. $articleSetting['setting_name'] . ' with locale ' . $articleSetting['locale'] . ' .........'; 
				
						$arr = array();
						$arr['data'] = $articleSetting;
						$arr['params'] = array(
							array('name' => ':articleSettings_articleId', 'attr' => 'article_new_id', 'type' => PDO::PARAM_INT),
							array('name' => ':articleSettings_locale', 'attr' => 'locale', 'type' => PDO::PARAM_STR),
							array('name' => ':articleSettings_settingName', 'attr' => 'setting_name', 'type' => PDO::PARAM_STR),
							array('name' => ':articleSettings_settingValue', 'attr' => 'setting_value', 'type' => PDO::PARAM_STR),
							array('name' => ':articleSettings_settingType', 'attr' => 'setting_type', 'type' => PDO::PARAM_STR)
						);
						
						if (myExecute('insert', 'article_settings', $arr, $insertArticleSettingsSTMT, $errors)) { //from helperFunctions.php
							echo "Ok\n";
							$numInsertions['article_settings']++;
						}
						else {
							echo "Failed\n";
						}
					}//////// end of the else block to insert the article_setting ////////////////////
				}
				else {
					//checkArticleSettingSTMT did not execute
					$checkError['error'] = 'The checkArticleSettingSTMT did not execute';
					$checkError['stmtError'] = $checkArticleSettingSTMT->errorInfo();
				}
				
				if (array_key_exists('error', $checkError)) {
					array_push($errors['article']['check'], $checkError);
				}
				
			}//end of foreach article settings
			unset($articleSetting);
			}// closing the if count settings > 0
			}//closing the if article settings exists
			
			////////////// end of insert article_settings  //////////////////////
			
			
			/////////////// insert author ///////////////////////////////////////
			if (!$published && array_key_exists('authors', $article)) { if (!empty($article['authors'])) {
			echo "\ninserting authors:\n";
			foreach ($article['authors'] as $author) {
				
				if (array_key_exists($author['author_id'], $dataMapping['author_id'])) {
					echo "\nThe author #" . $author['author_id'] . " was already imported.";
					echo "\nIts new id is " . $dataMapping['author_id'][$author['author_id']] . "\n";
					continue; // go to the next author
				}
				
				$authorOk = false;
				$author['submission_new_id'] = $article['article_new_id'];
				
				validateData('author', $author); //from helperFunctions.php
				
				$arr = array();
				$arr['data'] = $author;
				$arr['params'] = array(
					array('name' => ':author_submissionId', 'attr' => 'submission_new_id', 'type' => PDO::PARAM_INT),
					array('name' => ':author_primaryContact', 'attr' => 'primary_contact', 'type' => PDO::PARAM_INT),
					array('name' => ':author_seq', 'attr' => 'seq'),
					array('name' => ':author_firstName', 'attr' => 'first_name', 'type' => PDO::PARAM_STR),
					array('name' => ':author_middleName', 'attr' => 'middle_name', 'type' => PDO::PARAM_STR),
					array('name' => ':author_lastName', 'attr' => 'last_name', 'type' => PDO::PARAM_STR),
					array('name' => ':author_country', 'attr' => 'country', 'type' => PDO::PARAM_STR),
					array('name' => ':author_email', 'attr' => 'email', 'type' => PDO::PARAM_STR),
					array('name' => ':author_url', 'attr' => 'url', 'type' => PDO::PARAM_STR),
					array('name' => ':author_suffix', 'attr' => 'suffix', 'type' => PDO::PARAM_STR),
				);
				
				echo "\ninserting author #" . $author['author_id'] . " ......... "; 
			
				if (myExecute('insert', 'author', $arr, $insertAuthorSTMT, $errors)) { //from helperFunctions.php
					echo "Ok\n";
					
					$numInsertions['authors']++;
					
					$args = array();
					$args['compare'] = ['email' => 'email'];
					
					if (getNewId('author', $lastAuthorsSTMT, $author, $dataMapping, $errors, $args)) { //from helperFunctions.php
						echo "author new id = " . $author['author_new_id'] . "\n";
						$authorOk = true;
					}
					
				}
				else {
					echo "Failed\n";
				}
				
			}//end of foreach author
			
			}//end fo the if not published and authors exist
			}//end of the if authors not empty
			
			//// end of the insert author ///////////////////////////
			
			
			// insert article_files ////////////////////////////////
			if (array_key_exists('files', $article)) {  if (!empty($article['files']) && $article['files'] != null) {
				
			$fetchedArticleFiles = null;
				
			if ($published) {
				$selectArticleFilesSTMT->bindParam('selectArticleFiles_articleId', $article['article_new_id'], PDO::PARAM_INT);
				if ($selectArticleFilesSTMT->execute()) {
					$fetchedArticleFiles = $selectArticleFilesSTMT->fetchAll(PDO::FETCH_ASSOC);
				}
				else {
					//TREAT THE ERROR
				}
			}
				
			echo "\ninserting article_files:\n";
			
			//insert each article_file
			foreach ($article['files'] as &$articleFile) {
				
				validateData('article_file', $articleFile); //from helperFunctions.php
				
				$articleIdOk = false;
				
				if (array_key_exists($articleFile['article_id'], $dataMapping['article_id'])) {
					$articleFile['article_new_id'] = $dataMapping['article_id'][$articleFile['article_id']];
					$articleIdOk = true;
				}
				
				if ($articleIdOk) {
			
					if (array_key_exists($articleFile['file_id'], $dataMapping['file_id'])) {
						//check if the article_file needs to be updated or even inserted if the revision is > 1
						
						$articleFile['file_new_id'] = $dataMapping['file_id'][$articleFile['file_id']];
						
						$checkRevisedFileSTMT->bindParam(':checkRev_fileId', $articleFile['file_new_id'], PDO::PARAM_INT);
						$checkRevisedFileSTMT->bindParam(':checkRev_revision', $articleFile['revision'], PDO::PARAM_INT);
						
						
						$checkError = array('article_file' => $articleFile);
						
						if ($checkRevisedFileSTMT->execute()) {
							
							if ($fetchedArticleFile = $checkRevisedFileSTMT->fetch(PDO::FETCH_ASSOC)) {
								//check if needs to update
								
								$args = array();
								$args['compare'] = 'almost all';
								$args['notCompare'] = ['file_name','original_file_name', 'source_file_id'];
								
								
								if (same2($articleFile, $fetchedArticleFile, $args) > 0) { // from helperFunctions.php function #16
									// does not need to update
								}
								else { // update the article_file
									
									$arr = array();
									$arr['data'] = $articleFile;
									$arr['params'] = array(
										array('name' => ':updtArticleFile_sourceRevision', 'attr' => 'source_revision', 'type' => PDO::PARAM_INT),
										array('name' => ':updtArticleFile_fileStage', 'attr' => 'file_stage', 'type' => PDO::PARAM_INT),
										array('name' => ':updtArticleFile_viewable', 'attr' => 'viewable', 'type' => PDO::PARAM_INT),
										array('name' => ':updtArticleFile_dateUploaded', 'attr' => 'date_uploaded', 'type' => PDO::PARAM_STR),
										array('name' => ':updtArticleFile_dateModified', 'attr' => 'date_modified', 'type' => PDO::PARAM_STR),
										array('name' => ':updtArticleFile_round', 'attr' => 'round', 'type' => PDO::PARAM_INT),
										array('name' => ':updtArticleFile_assocId', 'attr' => 'assoc_id', 'type' => PDO::PARAM_INT),
										array('name' => ':updtArticleFile_fileId', 'attr' => 'file_new_id', 'type' => PDO::PARAM_INT),
										array('name' => ':updtArticleFile_revision', 'attr' => 'revision', 'type' => PDO::PARAM_INT)
									);
									
									echo '    updating article file #' . $articleFile['file_new_id']. ' revision ' . $articleFile['revision'] .'............ ';
									
									if (myExecute('update', 'article_file', $arr, $updtArticleFileSTMT, $errors)) { //from helperFunctions.php
										echo "Ok\n";
										$numUpdates['article_files']++;
									}
									else {
										echo "Failed\n";
									}
									
								}// end of the update article_file block
								
								$articleFile['DNU'] = true; // DNU means Do Not Update
								
							}// end of the if fetchedArticleFile
							else if ( ((int)$articleFile['revision']) > 1){
								// insert the revised file
								
								$arr = array();
								$arr['data'] = $articleFile;
								$arr['params'] = array(
									array('name' => ':fileRev_fileId', 'attr' => 'file_new_id', 'type' => PDO::PARAM_INT),
									array('name' => ':fileRev_revision', 'attr' => 'revision', 'type' => PDO::PARAM_INT),
									array('name' => ':fileRev_sourceRevision', 'attr' => 'source_revision', 'type' => PDO::PARAM_INT),
									array('name' => ':fileRev_articleId', 'attr' => 'article_new_id', 'type' => PDO::PARAM_INT),
									array('name' => ':fileRev_fileName', 'attr' => 'file_name', 'type' => PDO::PARAM_STR),
									array('name' => ':fileRev_fileType', 'attr' => 'file_type', 'type' => PDO::PARAM_STR),
									array('name' => ':fileRev_originalFileName', 'attr' => 'original_file_name', 'type' => PDO::PARAM_STR),
									array('name' => ':fileRev_fileSize', 'attr' => 'file_size', 'type' => PDO::PARAM_INT),
									array('name' => ':fileRev_fileStage', 'attr' => 'file_stage', 'type' => PDO::PARAM_INT),
									array('name' => ':fileRev_viewable', 'attr' => 'viewable', 'type' => PDO::PARAM_INT),
									array('name' => ':fileRev_dateUploaded', 'attr' => 'date_uploaded', 'type' => PDO::PARAM_STR),
									array('name' => ':fileRev_dateModified', 'attr' => 'date_modified', 'type' => PDO::PARAM_STR),
									array('name' => ':fileRev_round', 'attr' => 'round', 'type' => PDO::PARAM_INT),
									array('name' => ':fileRev_assocId', 'attr' => 'assoc_id', 'type' => PDO::PARAM_INT)
								);
								
								echo '    inserting article file #' . $articleFile['file_new_id']. ' revision ' . $articleFile['revision'] .' ............ ';
								
								if (myExecute('insert', 'article_file', $arr, $insertArticleFileRevisedSTMT, $errors)) { //from helperFunctions.php
									echo "Ok\n";
									$numInsertions['article_files']++;
								}
								else {
									echo "Failed\n";
								}
								
							}// end of the if to insert the revised file
							else {
								$checkError['error'] = 'Did not fetch the article file which is not a revised file';
							}
							
						}// end of the if checkRevisedFileSTMT executed
						else {
							//checkRevisedFileSTMT did not execute
							$checkError['error'] = 'The checkRevisedFileSTMT did not execute';
							$checkError['stmtError'] = $checkRevisedFileSTMT->errorInfo();
						}
						
						if (array_key_exists('error', $checkError)) {
							array_push($errors['article_file']['check'], $checkError);
						}
						
					}//end of the if file_id is mapped
					else if ($published) {
						
						//WORKING ON NOW
						
						//check if the corresponding files already exists
		//the PB.pdf must exist and some other like the RV.doc or ED.doc might exist
		
						$matchedFile = null;
						
						//look for the same original_file_name and termination of file_name
						
						//search for types: AT, SP, CE, SM, PB, RV, ED, LE
						if (strpos($articleFile['file_name'])) echo 'whoa there'!;
						
							//OBS! original_file_name might have types too like Sp, Pb, ...
						//BEWARE OF THAT AND PROBABLY BEST TO MAKE A FUNCTION TO TREAT IT
						
						//search for a match in the fetchedArticleFiles
						foreach ($fetchedArticleFiles as $fetchedFile) {
							
							if (substr())
							
							$sameOriginalName = $fetchedFile['original_file_name'] == $articleFile['original_file_name'];
							$sameSize = $fetchedFile['file_size'] == $articleFile['file_size'];
							$sameFileTermination = 
							if ()
						}
						
						
						
					}
					else { //insert the article_file
						
						$arr = array();
						$arr['data'] = $articleFile;
						$arr['params'] = array(
							array('name' => ':file_revision', 'attr' => 'revision', 'type' => PDO::PARAM_INT),
							array('name' => ':file_sourceRevision', 'attr' => 'source_revision', 'type' => PDO::PARAM_INT),
							array('name' => ':file_articleId', 'attr' => 'article_new_id', 'type' => PDO::PARAM_INT),
							array('name' => ':file_fileName', 'attr' => 'file_name', 'type' => PDO::PARAM_STR),
							array('name' => ':file_fileType', 'attr' => 'file_type', 'type' => PDO::PARAM_STR),
							array('name' => ':file_originalFileName', 'attr' => 'original_file_name', 'type' => PDO::PARAM_STR),
							array('name' => ':file_fileSize', 'attr' => 'file_size', 'type' => PDO::PARAM_INT),
							array('name' => ':file_fileStage', 'attr' => 'file_stage', 'type' => PDO::PARAM_INT),
							array('name' => ':file_viewable', 'attr' => 'viewable', 'type' => PDO::PARAM_INT),
							array('name' => ':file_dateUploaded', 'attr' => 'date_uploaded', 'type' => PDO::PARAM_STR),
							array('name' => ':file_dateModified', 'attr' => 'date_modified', 'type' => PDO::PARAM_STR),
							array('name' => ':file_round', 'attr' => 'round', 'type' => PDO::PARAM_INT),
							array('name' => ':file_assocId', 'attr' => 'assoc_id', 'type' => PDO::PARAM_INT)
						);
						
						echo '    inserting article file #' . $articleFile['file_id']. '............ ';
						
						if (myExecute('insert', 'article_file', $arr, $insertArticleFileSTMT, $errors)) { //from helperFunctions.php
							echo "Ok\n";
							
							$numInsertions['article_files']++;
							
							$args = array();
							$args['compare'] = 'almost all';
							$args['notCompare'] = ['file_name','original_file_name', 'source_file_id'];
							
							if (getNewId('article_file', $lastArticleFilesSTMT, $articleFile, $dataMapping, $errors, $args)) { //from helperFunctions.php
								echo "    file new id = " . $articleFile['file_new_id'] . "\n\n";
								$articleFileOk = true;
							}
						}
						else {
							echo "Failed\n";
						}
						
					}//end of the file insertion block
					
					
				}
				else {
					$error = ['file_id' => $articleFile['file_id'], 'error' => 'article_id '. $articleFile['article_id'] . ' not found on dataMappings.'];
					array_push($errors['article_file']['insert'], $error);
				}
				
			}//end of foreach article_files
			unset($articleFile);
			}//closing the if article files is empty
			}//closing the if article files exist
			
			/////////////////// end of insert article_files /////////////////////////
			
			
			/////////////// insert article_supplementary_files ///////////////////////
			if (!$published && array_key_exists('supplementary_files', $article)) {  if (!empty($article['supplementary_files']) && $article['supplementary_files'] != null) {
			echo "inserting article_supplementary_files:\n";
			foreach ($article['supplementary_files'] as &$articleSuppFile) {
				
				if (array_key_exists($articleSuppFile['supp_id'], $dataMapping['supp_id'])) {
					echo "\nThe article supplementary file #" . $articleSuppFile['supp_id'] . " was already imported.";
					echo "\nIts new id is " . $dataMapping['supp_id'][$articleSuppFile['supp_id']] . "\n";
					continue; // go to the next article supplementary file
				}
				
				validateData('article_supplementary_file', $articleSuppFile); //from helperFunctions.php
				
				$articleSuppFileOk = false;
				$fileIdOk = false;
				$articleIdOk = false;
				if (array_key_exists($articleSuppFile['file_id'], $dataMapping['file_id'])) {
					$articleSuppFile['file_new_id'] = $dataMapping['file_id'][$articleSuppFile['file_id']];
					$fileIdOk = true;
				}
				if (array_key_exists($articleSuppFile['article_id'], $dataMapping['article_id'])) {
					$articleSuppFile['article_new_id'] = $dataMapping['article_id'][$articleSuppFile['article_id']];
					$articleIdOk = true;
				}
				
				if ($fileIdOk && $articleIdOk) {
					
					$arr = array();
					$arr['data'] = $articleSuppFile;
					$arr['params'] = array(
						array('name' => ':supp_fileId', 'attr' => 'file_new_id', 'type' => PDO::PARAM_INT),
						array('name' => ':supp_articleId', 'attr' => 'article_new_id', 'type' => PDO::PARAM_INT),
						array('name' => ':supp_type', 'attr' => 'type', 'type' => PDO::PARAM_STR),
						array('name' => ':supp_language', 'attr' => 'language', 'type' => PDO::PARAM_STR),
						array('name' => ':supp_showReviewers', 'attr' => 'show_reviewers', 'type' => PDO::PARAM_INT),
						array('name' => ':supp_dateCreated', 'attr' => 'date_created', 'type' => PDO::PARAM_STR),
						array('name' => ':supp_dateSubmitted', 'attr' => 'date_submitted', 'type' => PDO::PARAM_STR),
						array('name' => ':supp_seq', 'attr' => 'seq'),
						array('name' => ':supp_remoteUrl', 'attr' => 'remote_url', 'type' => PDO::PARAM_STR)
					);
					
					echo '    inserting article supplemetary file #' . $articleSuppFile['supp_id']. '............ ';
					
					if (myExecute('insert', 'article_supplementary_file', $arr, $insertArticleSuppFileSTMT, $errors)) { //from helperFunctions.php
						echo "Ok\n";
						
						$numInsertions['article_supplementary_files']++;
						
						if (getNewId('article_supplementary_file', $lastArticleSuppFilesSTMT, $articleSuppFile, $dataMapping, $errors)) { //from helperFunctions.php
							echo "    new id = " . $articleSuppFile['supp_new_id'] . "\n\n";
							$articleSuppFileOk = true;
						}
					}
					else {
						echo "Failed\n";
					}
				}
				else {
					if (!$articleIdOk) {
						$error = ['supp_id' => $articleSuppFile['supp_id'], 'error' => 'article_id '. $articleSuppFile['article_id'] . ' not found on dataMappings.'];
						array_push($errors['article_supplementary_file']['insert'], $error);
					}
					if (!$fileIdOk) {
						$error = ['file_id' => $articleSuppFile['file_id'], 'error' => 'file_id '. $articleSuppFile['file_id'] . ' not found on dataMappings.'];
						array_push($errors['article_supplementary_file']['insert'], $error);
					}
				}
				
				
				if ($articleSuppFileOk) {
				
				/////////////// insert the article_supplementary_file_settings /////////////////////////
					if (array_key_exists('settings', $articleSuppFile)) { if (!empty($articleSuppFile['settings'])) {
					echo "\ninserting article_settings:\n";
					
					//insert each article setting
					foreach($articleSuppFile['settings'] as &$setting) {
						
						validateData('article_supp_file_settings', $setting); //from helperFunctions.php
						
						$setting['supp_new_id'] = $articleSuppFile['supp_new_id'];
						echo '    inserting '. $setting['setting_name'] . ' with locale ' . $setting['locale'] . ' .........'; 
						
						$arr = array();
						$arr['data'] = $setting;
						$arr['params'] = array(
							array('name' => ':suppSettings_suppId', 'attr' => 'supp_new_id', 'type' => PDO::PARAM_INT),
							array('name' => ':suppSettings_locale', 'attr' => 'locale', 'type' => PDO::PARAM_STR),
							array('name' => ':suppSettings_settingName', 'attr' => 'setting_name', 'type' => PDO::PARAM_STR),
							array('name' => ':suppSettings_settingValue', 'attr' => 'setting_value', 'type' => PDO::PARAM_STR),
							array('name' => ':suppSettings_settingType', 'attr' => 'setting_type', 'type' => PDO::PARAM_STR)
						);
						
						if (myExecute('insert', 'article_supp_file_settings', $arr, $insertArticleSuppFileSettingSTMT, $errors)) { //from helperFunctions.php
							echo "Ok\n";
							$numInsertions['article_supp_file_settings']++;
						}
						else {
							echo "Failed\n";
						}
					}//end of the foreach setting
					unset($setting);
					}//closing the if articleSuppFile['settings'] is empty
					}//closing the if articleSuppFile settings exist
					
				///////////////////end of insert article_settings  //////////////////////
				
				}// end of the if articleSuppFileOk
				
			} //end of foreach article supplementary file
			unset($articleSuppFile);
			}//closing the if supplementary files is empty
			}//closing the if supplementary_files exist
			
			///////////////// end of insert article_supplementary_files ////////////////////////
		
		
			/////////////////////// insert article comments  ///////////////////////////////////
			if (!$published && array_key_exists('comments', $article)) { if (!empty($article['comments'])) {
			echo "\ninserting article comments...\n";
			
			foreach ($article['comments'] as &$articleComment) {
				
				if (array_key_exists($articleComment['comment_id'], $dataMapping['comment_id'])) {
					echo "\nThe article_comment #" . $articleComment['comment_id'] . " was already imported.\n";
					echo "Its new id is " . $dataMapping['comment_id'][$articleComment['comment_id']];
					continue; // go to the next article_comment
				}
				
				validateData('article_comment', $articleComment); //from helperFunctions.php
				
				$articleIdOk = false;
				$authorIdOk = false;
				$fileIdOk = false;
				$articleCommentOk = false;
				
				if (array_key_exists($articleComment['article_id'], $dataMapping['article_id'])) {
					$articleComment['article_new_id'] = $dataMapping['article_id'][$articleComment['article_id']];
					$articleIdOk = true;
				}
				
				if (array_key_exists($articleComment['author_id'], $dataMapping['comment_author_id'])) {
					$articleComment['author_new_id'] = $dataMapping['comment_author_id'][$articleComment['author_id']];
					$authorIdOk = true;
				}
				else if (array_key_exists($articleComment['author_id'], $dataMapping['user_id'])) {
					$dataMapping['comment_author_id'][$articleComment['author_id']] = $dataMapping['user_id'][$articleComment['author_id']];
					$articleComment['author_new_id'] = $dataMapping['user_id'][$articleComment['author_id']];
					$authorIdOk = true;
				}
				else if (is_array($articleComment['author'])){
					
					$authorIdOk = processUser($articleComment['author'], array('type' => 'article_comment', 'data' => $articleComment), $dataMapping, $errors, 
						$insertedUsers, $userStatements, $conn);
					
					if ($authorIdOk) {
						
						$articleComment['author_new_id'] = $articleComment['author']['user_new_id'];
						
						if (!array_key_exists($articleComment['author_id'], $dataMapping['comment_author_id'])) {
							$dataMapping['comment_author_id'][$articleComment['author_id']] = $articleComment['author_new_id'];
						}
						
					}
					
				}
				
				if ($articleIdOk && $authorIdOk) {
					
					$arr = array();
					$arr['data'] = $articleComment;
					$arr['params'] = array(
						array('name' => ':comment_commentType', 'attr' => 'comment_type', 'type' => PDO::PARAM_INT),
						array('name' => ':comment_roleId', 'attr' => 'role_id', 'type' => PDO::PARAM_INT),
						array('name' => ':comment_articleId', 'attr' => 'article_new_id', 'type' => PDO::PARAM_INT),
						array('name' => ':comment_assocId', 'attr' => 'assoc_id', 'type' => PDO::PARAM_INT),
						array('name' => ':comment_authorId', 'attr' => 'author_new_id', 'type' => PDO::PARAM_INT),
						array('name' => ':comment_commentTitle', 'attr' => 'comment_title', 'type' => PDO::PARAM_STR),
						array('name' => ':comment_comments', 'attr' => 'comments', 'type' => PDO::PARAM_STR),
						array('name' => ':comment_datePosted', 'attr' => 'date_posted', 'type' => PDO::PARAM_STR),
						array('name' => ':comment_dateModified', 'attr' => 'date_modified', 'type' => PDO::PARAM_STR),
						array('name' => ':comment_viewable', 'attr' => 'viewable', 'type' => PDO::PARAM_INT)
					);
					
					echo '    inserting article comment #' . $articleComment['comment_id']. '............ ';
					
					if (myExecute('insert', 'article_comment', $arr, $insertArticleCommentSTMT, $errors)) { //from helperFunctions.php
						echo "Ok\n";
						
						$numInsertions['article_comments']++;
						
						if (getNewId('article_comment', $lastArticleCommentsSTMT, $articleComment, $dataMapping, $errors)) { //from helperFunctions.php
							echo "    new id = " . $articleComment['comment_new_id'] . "\n\n";
							$articleCommentOk = true;
						}
					}
					else {
						echo "Failed\n";
					}
					
				}
				else {
					if (!$articleIdOk) {
						$error = ['comment_id' => $articleComment['comment_id'], 'error' => 'article_id '. $articleComment['article_id'] . ' not found on dataMappings.'];
						array_push($errors['article_comment']['insert'], $error);
					}
					if (!$authorIdOk) {
						$error = ['comment_id' => $articleComment['comment_id'], 'error' => 'author_id '. $articleComment['author_id'] . ' not found on dataMappings.'];
						array_push($errors['article_comment']['insert'], $error);
					}
				}
			}//end of the foreach article comments
			unset($articleComment);
			}//closing the if article comment is empty
			}//closing the if article comments exists
			
			//////////////////  end of insert article comments  ////////////////////////////////////////
			
			
			///////////////////// insert article galleys /////////////////////////////////////
			if (!$published && array_key_exists('galleys', $article)) { if (!empty($article['galleys'])) {
			echo "\ninserting article galleys...\n";
			
			foreach ($article['galleys'] as &$articleGalley) {
				
				if (array_key_exists($articleGalley['galley_id'], $dataMapping['galley_id'])) {
					echo "\nThe article galley #" . $articleGalley['galley_id'] . " was already imported.\n";
					echo "Its new id is " . $dataMapping['galley_id'][$articleGalley['galley_id']];
					continue; // go to the next article galley
				}
				
				validateData('article_galley', $articleGalley); //from helperFunctions.php
				
				$articleGalleyOk = false;
				
				$articleIdOk = false;
				$fileIdOk = false;
				$styleFileIdOk = false;
				
				$error = ['galley_id' => $articleGalley['galley_id']];
				
				if (array_key_exists($articleGalley['article_id'], $dataMapping['article_id'])) {
					$articleGalley['article_new_id'] = $dataMapping['article_id'][$articleGalley['article_id']];
					$articleIdOk = true;
				}
				else {
					$error['article_id'] = ['article_id' => $articleGalley['article_id'], 'error' => 'article_id not in dataMapping'];
				}
				
				if (array_key_exists($articleGalley['file_id'], $dataMapping['file_id'])) {
					$articleGalley['file_new_id'] = $dataMapping['file_id'][$articleGalley['file_id']];
					$fileIdOk = true;
				}
				else {
					$error['file_id'] = ['file_id' => $articleGalley['file_id'], 'error' => 'file_id not in dataMapping'];
				}
				
				if ($articleGalley['style_file_id'] === null) {
					$articleGalley['style_file_new_id'] = null;
					$styleFileIdOk = true;
				}
				else if (array_key_exists($articleGalley['style_file_id'], $dataMapping['file_id'])) {
					$dataMapping['style_file_id'][$articleGalley['style_file_id']] = $dataMapping['file_id'];
				}
				else {
					$error['style_file_id'] = ['style_file_id' => $articleGalley['style_file_id'], 'error' => 'style_file_id not in dataMapping'];
				}
				
				
				
				if ($articleIdOk && $fileIdOk && $styleFileIdOk) {
					
					$arr = array();
					$arr['data'] = $articleGalley;
					$arr['params'] = array(
						array('name' => ':articleGalley_locale', 'attr' => 'locale', 'type' => PDO::PARAM_STR),
						array('name' => ':articleGalley_articleId', 'attr' => 'article_new_id', 'type' => PDO::PARAM_INT),
						array('name' => ':articleGalley_fileId', 'attr' => 'file_new_id', 'type' => PDO::PARAM_INT),
						array('name' => ':articleGalley_label', 'attr' => 'label', 'type' => PDO::PARAM_STR),
						array('name' => ':articleGalley_htmlGalley', 'attr' => 'html_galley', 'type' => PDO::PARAM_INT),
						array('name' => ':articleGalley_styleFileId', 'attr' => 'style_file_new_id', 'type' => PDO::PARAM_INT),
						array('name' => ':articleGalley_seq', 'attr' => 'seq'),
						array('name' => ':articleGalley_remoteUrl', 'attr' => 'remote_url', 'type' => PDO::PARAM_STR)
					);
					
					echo '    inserting article galley #' . $articleGalley['galley_id']. '............ ';
					
					if (myExecute('insert', 'article_galley', $arr, $insertArticleGalleySTMT, $errors)) { //from helperFunctions.php
						echo "Ok\n";
						
						$numInsertions['article_galleys']++;
						
						if (getNewId('article_galley', $lastArticleGalleysSTMT, $articleGalley, $dataMapping, $errors)) { //from helperFunctions.php
							echo "    new id = " . $articleGalley['galley_new_id'] . "\n\n";
							$articleGalleyOk = true;
						}
					}
					else {
						echo "Failed\n";
					}
				}
				else {
					array_push($errors['article_galley']['insert'], $error);
				}
				
				if ($articleGalleyOk) {
					
					//////////////// insert the article_galley_settings /////////////////////////
					if (array_key_exists('settings', $articleGalley)) { if (!empty($articleGalley['settings'])) {
					echo "\ninserting article_galley_settings:\n";
					
					//insert each article galley setting
					foreach($articleGalley['settings'] as &$setting) {
						
						validateData('article_galley_settings', $setting); //from helperFunctions.php 
						
						$setting['galley_new_id'] = $articleGalley['galley_new_id'];
						
						echo '    inserting '. $setting['setting_name'] . ' with locale ' . $setting['locale'] . ' .........'; 
						
						
						$arr = array();
						$arr['data'] = $setting;
						$arr['params'] = array(
							array('name' => ':galleySetting_galleyId', 'attr' => 'galley_new_id', 'type' => PDO::PARAM_INT),
							array('name' => ':galleySetting_locale', 'attr' => 'locale', 'type' => PDO::PARAM_STR),
							array('name' => ':galleySetting_settingName', 'attr' => 'setting_name', 'type' => PDO::PARAM_STR),
							array('name' => ':galleySetting_settingValue', 'attr' => 'setting_value', 'type' => PDO::PARAM_STR),
							array('name' => ':galleySetting_settingType', 'attr' => 'setting_type', 'type' => PDO::PARAM_STR)
						);
						
						if (myExecute('insert', 'article_galley_settings', $arr, $insertArticleGalleySettingSTMT, $errors)) { //from helperFunctions.php
							echo "Ok\n";
							$numInsertions['article_galley_settings']++;
						}
						else {
							echo "Failed\n";
						}
					}
					unset($setting);
					}//closing the if articleGalley settings is empty
					}//closing the if articleGalley settings exists
					
				//////////// end of insert article_galley_settings  //////////////////////
				
				///////////// insert article_xml_galleys /////////////////////////
					if (array_key_exists('xml_galley', $articleGalley)) { if (!empty($articleGalley['xml_galley'])) {
					echo "\ninserting article_xml_galleys:\n";
					
					//insert each article xml galley
					foreach($articleGalley['xml_galley'] as &$xmlGalley) {
						
						validateData('article_xml_galley', $xmlGalley); //from helperFunctions.php
						
						$xmlGalley['galley_new_id'] = $articleGalley['galley_new_id'];
						
						$articleIdOk = false;
						$xmlGalleyOk = false;
						
						if (array_key_exists($xmlGalley['article_id'], $dataMapping['article_id'])) {
							$xmlGalley['article_new_id'] = $dataMapping['article_id'][$xmlGalley['article_id']];
							$articleIdOk = true;
						}
						
						if ($articleIdOk) {
						
							echo '    inserting xml_galley #'. $xmlGalley['xml_galley_id'] . ' .........'; 
							
							$arr = array();
							$arr['data'] = $xmlGalley;
							$arr['params'] = array(
								array('name' => ':xmlGalley_galleyId', 'attr' => 'galley_new_id', 'type' => PDO::PARAM_INT),
								array('name' => ':xmlGalley_articleId', 'attr' => 'article_new_id', 'type' => PDO::PARAM_INT),
								array('name' => ':xmlGalley_label', 'attr' => 'label', 'type' => PDO::PARAM_STR),
								array('name' => ':xmlGalley_galleyType', 'attr' => 'galley_type', 'type' => PDO::PARAM_STR),
								array('name' => ':xmlGalley_views', 'attr' => 'views', 'type' => PDO::PARAM_INT)
							);
							
							if (myExecute('insert', 'article_xml_galley', $arr, $insertArticleXmlGalleySTMT, $errors)) { //from helperFunctions.php
								echo "Ok\n";
								
								$numInsertions['article_xml_galleys']++;
								
								if (getNewId('article_xml_galley', $lastArticleXmlGalleysSTMT, $xmlGalley, $dataMapping, $errors)) { //from helperFunctions.php
									echo "    new id = " . $articleGalley['xml_galley_new_id'] . "\n\n";
									$xmlGalleyOk = true;
								}
							}
							else {
								echo "Failed\n";
							}
						}
						else {
							$error = array('xml_galley_id' => $xmlGalley['xml_galley_id'], 'error' => 'article_id '. $xmlGalley['article_id'] . ' not found on dataMappings.');
							array_push($errors['article_xml_galley']['insert'], $error);
						}
					}//end of the foreach xml_galley
					unset($xmlGalley);
					}//closing the if xml galley is empty
					}//closing the if xml galley exist
					
				//////////////// end of insert article_xml_galleys  //////////////////////
				
				/////////////// insert the article_html_galley_images /////////////////////////
			
					if (array_key_exists('html_galley_images', $articleGalley)) { if (!empty($articleGalley['html_galley_images']) && $articleGalley['html_galley_images'] != null) {
				
					echo "\ninserting article_html_galley_images:\n";
					
					//insert each article_html_galley_image
					foreach($articleGalley['html_galley_images'] as &$galleyImage) {
						
						validateData('article_html_galley_image', $galleyImage); //from helperFunctions.php
						
						$galleyImage['galley_new_id'] = $articleGalley['galley_new_id'];
						
						$fileIdOk = false;
						
						if (array_key_exists($galleyImage['file_id'], $dataMapping['file_id'])) {
							$galleyImage['file_new_id'] = $dataMapping['file_id'][$galleyImage['file_id']];
							$fileIdOk = true;
						}
						
						if ($fileIdOk) {
							echo '    inserting galley #'. $galleyImage['galley_id'] . ' file #' . $galleyImage['file_id'] . ' .........'; 
							
							$arr = array();
							$arr['data'] = $setting;
							$arr['params'] = array(
								array('name' => ':galleyImage_galleyId', 'attr' => 'galley_new_id', 'type' => PDO::PARAM_INT),
								array('name' => ':galleyImage_fileId', 'attr' => 'file_new_id', 'type' => PDO::PARAM_INT)
							);
							
							if (myExecute('insert', 'article_html_galley_image', $arr, $insertArticleHtmlGalleyImageSTMT, $errors)) { //from helperFunctions.php
								echo "Ok\n";
								
								$numInsertions['article_html_galley_images']++;
								
							}
							else {
								echo "Failed\n";
							}
						}
						else {
							$error = array(
								'galley_id' => $galleyImage['galley_id'], 
								'file_id' => $galleyImage['file_id'], 
								'error' => 'file_id '. $galleyImage['file_id'] . ' not found on dataMappings.'
							);
							array_push($errors['article_html_galley_image']['insert'], $error);
						}
						
					}
					unset($galleyImage);
					}//closing the if html galley images is empty
					}//closing the if article html galley image exists
					
				/////////////// end of insert article_html_galley_images  //////////////////////
				
				}//closing the if articleGalleyOk
				
			}//end of the foreach article galleys
			unset($articleGalley);
			}//closing the if article galley is empty
			}//closing the if article galley exists
			
			
			//////////// end of insert article galleys /////////////////
			
			
			///////////////  insert edit decisions  //////////////////
			if (array_key_exists('edit_decisions', $article)) { if (!empty($article['edit_decisions']) && $article['edit_decisions'] != null) {
			echo "\ninserting edit_decisions:\n";
			foreach ($article['edit_decisions'] as &$editDecision) {
						
				if (array_key_exists($editDecision['edit_decision_id'], $dataMapping['edit_decision_id'])) {
					echo "\nThe edit decision #" . $editDecision['edit_decision_id'] . " was already imported\n";
					echo "Its new id is " . $dataMapping['edit_decision_id'][$editDecision['edit_decision_id']];
					continue; // go to the next edit decision
				}
				
				$articleIdOk = false;
				$editorIdOk = false;
				$editDecisionOk = false;
				
				validateData('edit_decision', $editDecision); //from helperFunctions.php
				
				$error = ['edit_decision_id' => $editDecision['edit_decision_id']];
				
				if (array_key_exists($editDecision['article_id'], $dataMapping['article_id'])) {
					$editDecision['article_new_id'] = $dataMapping['article_id'][$editDecision['article_id']];
					$articleIdOk = true;
				}
				else {
					$error['article_id'] = ['article_id' => $editDecision['article_id'], 'error' => 'article_id not found on dataMappings.'];
				}
				
				if (array_key_exists($editDecision['editor_id'], $dataMapping['editor_id'])) {
					$editDecision['editor_new_id'] = $dataMapping['editor_id'][$editDecision['editor_id']];
					$editorIdOk = true;
				}
				else if (array_key_exists($editDecision['editor_id'], $dataMapping['user_id'])) {
					$dataMapping['editor_id'][$editDecision['editor_id']] = $dataMapping['user_id'][$editDecision['editor_id']];
					$editDecision['editor_new_id'] = $dataMapping['editor_id'][$editDecision['editor_id']];
					$editorIdOk = true;
				}
				else if (is_array($editDecision['editor'])) {
					$editorIdOk = processUser($editDecision['editor'], array('type' => 'edit_decision', 'data' => $editDecision), $dataMapping, 
						$errors, $insertedUsers, $userStatements, $conn);
					
					if ($editorIdOk) {
						
						$editDecision['editor_new_id'] = $editDecision['editor']['user_new_id'];
						
						if (!array_key_exists($editDecision['editor_id'], $dataMapping['editor_id'])) {
							$dataMapping['editor_id'][$editDecision['editor_id']] = $editDecision['editor_new_id'];
						}
						
					}
					else {
						$error['editor_id'] = array('editor_id' => $editDecision['editor_id'], 'error' => 'processUser returned false.');
					}
				}
				
				if ($editorIdOk && $articleIdOk) {
					echo '    inserting edit decision #'. $editDecision['edit_decision_id'] . ' .........'; 
					
					$arr = array();
					$arr['data'] = $editDecision;
					$arr['params'] = array(
						array('name' => ':editDecision_articleId', 'attr' => 'article_new_id', 'type' => PDO::PARAM_INT),
						array('name' => ':editDecision_round', 'attr' => 'round', 'type' => PDO::PARAM_INT),
						array('name' => ':editDecision_editorId', 'attr' => 'editor_new_id', 'type' => PDO::PARAM_INT),
						array('name' => ':editDecision_decision', 'attr' => 'decision', 'type' => PDO::PARAM_INT),
						array('name' => ':editDecision_dateDecided', 'attr' => 'date_decided', 'type' => PDO::PARAM_STR)
					);
					
					if (myExecute('insert', 'edit_decision', $arr, $insertEditDecisionSTMT, $errors)) { //from helperFunctions.php
						echo "Ok\n";
						
						$numInsertions['edit_decisions']++; // increment the number of inserted edit_decisions
						
						if (getNewId('edit_decision', $lastEditDecisionsSTMT, $editDecision, $dataMapping, $errors)) { //from helperFunctions.php
							echo "    new id = " . $editDecision['edit_decision_new_id'] . "\n\n";
							$editDecisionOk = true;
						}
					}
					else {
						echo "Failed\n";
					}
				}
				else {
					array_push($errors['edit_decision']['insert'], $error);
				}
			}//end of the foreach edit_decision
			unset($editDecision);
			}//closing the if edit_decision is empty
			}//closing the if edit_decision exist
			
			//////////////// end of insert edit decisions  /////////////
			
			
			////////////////// insert edit_assignments  //////////////////
			if (array_key_exists('edit_assignments', $article)) { if (!empty($article['edit_assignments']) && $article['edit_assignments'] != null) {
			echo "\ninserting edit_assignments:\n";
			foreach ($article['edit_assignments'] as &$editAssignment) {
				
				if (array_key_exists($editAssignment['edit_id'], $dataMapping['edit_id'])) {
					echo "\nThe edit assignment #" . $editAssignment['edit_id'] . " was already imported\n";
					echo "Its new id is " . $dataMapping['edit_id'][$editAssignment['edit_id']];
					continue; // go to the next edit decision
				}
				
				validateData('edit_assignment', $editAssignment); //from helperFunctions.php
						
				$articleIdOk = false;
				$editorIdOk = false;
				$editAssignmentOk = false;
				
				$error = ['edit_id' => $editAssignment['edit_id']];
				
				if (array_key_exists($editAssignment['article_id'], $dataMapping['article_id'])) {
					$editAssignment['article_new_id'] = $dataMapping['article_id'][$editAssignment['article_id']];
					$articleIdOk = true;
				}
				else {
					$error['article_id'] = ['article_id' => $editAssignment['article_id'], 'error' => 'article_id not found on dataMappings.'];
				}
				if (array_key_exists($editAssignment['editor_id'], $dataMapping['editor_id'])) {
					$editAssignment['editor_new_id'] = $dataMapping['editor_id'][$editAssignment['editor_id']];
					$editorIdOk = true;
				}
				else if (array_key_exists($editAssignment['editor_id'], $dataMapping['user_id'])) {
					$dataMapping['editor_id'][$editAssignment['editor_id']] = $dataMapping['user_id'][$editAssignment['editor_id']];
					$editAssignment['editor_new_id'] = $dataMapping['editor_id'][$editAssignment['editor_id']];
					$editorIdOk = true;
				}	
				else if (is_array($editAssignment['editor'])) {
					
					$editorIdOk = processUser($editAssignment['editor'], array('type' => 'edit_assignment', 'data' => $editAssignment), $dataMapping, 
						$errors, $insertedUsers, $userStatements, $conn);
					
					if ($editorIdOk) {
						
						$editAssignment['editor_new_id'] = $editAssignment['editor']['user_new_id'];
						
						if (!array_key_exists($editAssignment['editor_id'], $dataMapping['editor_id'])) {
							$dataMapping['editor_id'][$editAssignment['editor_id']] = $editAssignment['editor_new_id'];
						}
						
					}
					else {
						$error['editor_id'] = array('editor_id' => $editAssignment['editor_id'], 'error' => 'processUser returned false.');
					}
				}
				
				if ($editorIdOk && $articleIdOk) {
					echo '    inserting edit assignment #'. $editAssignment['edit_id'] . ' .........'; 
					
					$arr = array();
					$arr['data'] = $editAssignment;
					$arr['params'] = array(
						array('name' => ':editAssign_articleId', 'attr' => 'article_new_id', 'type' => PDO::PARAM_INT),
						array('name' => ':editAssign_canEdit', 'attr' => 'can_edit', 'type' => PDO::PARAM_INT),
						array('name' => ':editAssign_editorId', 'attr' => 'editor_new_id', 'type' => PDO::PARAM_INT),
						array('name' => ':editAssign_canReview', 'attr' => 'can_review', 'type' => PDO::PARAM_INT),
						array('name' => ':editAssign_dateAssigned', 'attr' => 'date_assigned', 'type' => PDO::PARAM_STR),
						array('name' => ':editAssign_dateNotified', 'attr' => 'date_notified', 'type' => PDO::PARAM_STR),
						array('name' => ':editAssign_dateUnderway', 'attr' => 'date_underway', 'type' => PDO::PARAM_STR)
					);
					
					if (myExecute('insert', 'edit_assignment', $arr, $insertEditAssignmentSTMT, $errors)) { //from helperFunctions.php
						echo "Ok\n";
						
						$numInsertions['edit_assignments']++; // increment the number of inserted edit_assignments
						
						if (getNewId('edit_assignment', $lastEditAssignmentsSTMT, $editAssignment, $dataMapping, $errors)) { //from helperFunctions.php
							echo "    new id = " . $editAssignment['edit_new_id'] . "\n\n";
							$editAssignmentOk = true;
						}
					}
					else {
						echo "Failed\n";
					}
				}
				else {
					array_push($errors['edit_assignment']['insert'], $error);
				}
			}//end of the foreach edit_assignments
			unset($editAssignment);
			}//closing the if edit_assignments is empty
			}//closing the if edit_assignments exist
			
			/////////// end of insert edit assignments  //////////
			
			////////  INSERT THE KEYWORDS  /////////////////////////////
			
			if (array_key_exists('search_objects', $article)) { if (!empty($article['search_objects'])) {
			
			echo "\ninserting article_search_objects:\n";
				
			foreach ($article['search_objects'] as &$searchObj) {
				
				if (array_key_exists($searchObj['object_id'], $dataMapping['object_id'])) {
					echo "\nThe search object #" . $searchObj['object_id'] . " was already imported\n";
					echo "    Its new id is " . $dataMapping['object_id'][$searchObj['object_id']];
					continue; // go to the next edit decision
				}
				
				$searchObjOk = false;
				
				$searchObj['article_new_id'] = $article['article_new_id'];
				
				//validating the data 
				validateData('article_search_object', $searchObj); //from helperFunctions.php
				
				echo '    inserting article_search_object #'. $searchObj['object_id'] . ' .........'; 
					
				$arr = array();
				$arr['data'] = $searchObj;
				$arr['params'] = array(
					array('name' => ':searchObj_articleId', 'attr' => 'article_new_id', 'type' => PDO::PARAM_INT),
					array('name' => ':searchObj_type', 'attr' => 'type', 'type' => PDO::PARAM_INT),
					array('name' => ':searchObj_assocId', 'attr' => 'assoc_id', 'type' => PDO::PARAM_INT)
				);
				
				if (myExecute('insert', 'article_search_object', $arr, $insertArticleSearchObjectSTMT, $errors)) { //from helperFunctions.php
					echo "Ok\n";
					
					$numInsertions['article_search_objects']++; // increment the number of inserted article_search_objects
					
					if (getNewId('article_search_object', $lastArticleSearchObjectsSTMT, $searchObj, $dataMapping, $errors)) { //from helperFunctions.php
						echo "    new id = " . $searchObj['object_new_id'] . "\n\n";
						$searchObjOk = true;
					}
				}
				else {
					echo "Failed\n";
				}
				
				if ($searchObjOk) {
					
					if (array_key_exists('keywords', $searchObj)) { if (!empty($searchObj['keywords'])) {
					foreach ($searchObj['keywords'] as &$searchObjKeyword) {
						
						$objectIdOk = false;
						$keywordIdOk = false;
						
						$error = ['object_id' => $searchObj['object_id']];
						
						if (array_key_exists('keyword_list', $searchObjKeyword)) { if (!empty($searchObjKeyword['keyword_list'])) {
							$keyword = &$searchObjKeyword['keyword_list'];
							//check if the keyword is already in dataMapping
							if (array_key_exists($keyword['keyword_id'], $dataMapping['keyword_id'])) {
								$keyword['keyword_new_id'] = $dataMapping['keyword_id'][$keyword['keyword_id']];
								//$keywordIdOk = true;
							}
							else {
								//see if the keyword already exists in the database
								$getKeywordSTMT->bindParam(':getKeyword_keywordText', $keyword['keyword_text'], PDO::PARAM_STR);
								if ($getKeywordSTMT->execute()) {
									if ($kw = $getKeywordSTMT->fetch(PDO::FETCH_ASSOC)) {
										$keyword['keyword_new_id'] = $kw['keyword_id'];
									}
									else {
										//the keyword doesn't exist in the database, must insert it then
										echo "\n        inserting keyword ". $keyword['keyword_text'] . " ........."; 
										
										$arr = array();
										$arr['data'] = $keyword;
										$arr['params'] = array(
											array('name' => ':keywordList_keywordText', 'attr' => 'keyword_text', 'type' => PDO::PARAM_STR)
										);
										
										if (myExecute('insert', 'article_search_keyword_list', $arr, $insertArticleSearchKeywordListSTMT, $errors)) { //from helperFunctions.php
											echo "Ok\n";
											
											$numInsertions['article_search_keyword_list']++;
											
											if (getNewId('article_search_keyword_list', $lastArticleSearchKeywordListsSTMT, $keyword, $dataMapping, $errors)) { //from helperFunctions.php
												echo "        new id = " . $keyword['keyword_new_id'] . "\n\n";
												//$keywordIdOk = true;
											}
										}
										else {
											echo "Failed\n";
										}
									}
								}
								else {
									//DIDN'T EXECUTE THE GET KEYWORD STATEMENT
									
									$error['keyword'] = ['keyword_id' => $keyword['keyword_id'], 'error' => $getKeywordSTMT->errorInfo()];
								}
							}//end of the else keyword in dataMapping
							
							//NOW THE KEYWORD HAS A NEW ID SO WE'RE READY TO INSERT THE article_search_object_keyword
							
							if (array_key_exists('object_new_id', $searchObj)) {
								$searchObjKeyword['object_new_id'] = $searchObj['object_new_id'];
								$objectIdOk = true;
							}
							else {
								$error['object_new_id'] = ['error' => 'search_object has no object_new_id'];
							}
							
							if (array_key_exists('keyword_new_id', $keyword)) {
								$searchObjKeyword['keyword_new_id'] = $keyword['keyword_new_id'];
								$keywordIdOk = true;
							}
							
							unset($keyword);
								
							if ($objectIdOk && $keywordIdOk) {
								echo '        inserting object_id #' . $searchObjKeyword['object_new_id'] . ', keyword_id #' . 
								$searchObjKeyword['keyword_new_id'] . ' at position ' . $searchObjKeyword['pos'] . '.................';
									
								$arr = array();
								$arr['data'] = $searchObjKeyword;
								$arr['params'] = array(
									array('name' => ':objectKeyword_objectId', 'attr' => 'object_new_id', 'type' => PDO::PARAM_INT),
									array('name' => ':objectKeyword_keywordId', 'attr' => 'keyword_new_id', 'type' => PDO::PARAM_INT),
									array('name' => ':objectKeyword_pos', 'attr' => 'pos', 'type' => PDO::PARAM_INT)
								);
								
								if (myExecute('insert', 'article_search_object_keyword', $arr, $insertArticleSearchObjectKeywordSTMT, $errors)) {  //from helperFunctions.php
									echo "Ok\n";
									
									$numInsertions['article_search_object_keywords']++;
									
								}
								else {
									echo "Failed\n";
								}
							}
							else {
								array_push($errors['article_search_object_keyword']['insert'], $error);
							}
						
						}//closing the if keyword_list not empty
						}//closing the if keyword_list exists
						
					}
					//end of foreach keyword
					unset($searchObjKeyword);
					}// closing the if keywords is not empty
					}//closing the if keywords exists
					
				}//closing the if searchObjOk
				
			}
			//end of foreach search_objects
			unset($searchObj);
			}//closing the if search_objects not empty
			}//closing the if search_objects exist
			
			//////  END OF INSERT THE KEYWORDS  ////////////////////////
			
			
			//////  INSERT THE REVIEWS  ////////////////////////////////
			
			//////////////// insert the review_rounds //////////////////////
			
			if (array_key_exists('review_rounds', $article)) { if (!empty($article['review_rounds']) && $article['review_rounds'] != null) {
			
			echo "inserting the review_rounds:\n";
			foreach ($article['review_rounds'] as &$revRound) {
				
				$revRound['submission_new_id'] = $article['article_new_id'];
				validateData('review_round', $revRound); //from helperFunctions.php
				
				if (array_key_exists($revRound['review_round_id'], $dataMapping['review_round_id'])) {
					$revRound['review_round_new_id'] = $dataMapping['review_round_id'][$revRound['review_round_id']];
					
					echo "\n    The review_round #" . $revRound['review_round_id'] . " was already imported.\n";
					echo "        Its new id is #" . $revRound['review_round_new_id'] . "\n";
					
					//check if needs to update the review_round
					$checkRevRoundSTMT->bindParam(':checkRevRound_reviewRoundId', $revRound['review_round_new_id']);
					
					$checkError = array('review_round' => $revRound);
					
					if ($checkRevRoundSTMT->execute()) {
						if ($fetchedRevRound = $checkRevRoundSTMT->fetch(PDO::FETCH_ASSOC)) {
							$compareArgs = array('type' => 'review_round');
							if (same2($fetchedRevRound, $revRound) > 0) { //from helperFunctions.php function #16
								// does not need to update
								echo "    It is already up to date\n";
							}
							else { //////// update the review_round /////////////////////////
								
								echo '    updating review_round #' . $revRound['review_round_new_id'] . ' .................';
				
								$arr = array();
								$arr['data'] = $revRound;
								$arr['params'] = array(
									array('name' => ':updtRevRound_reviewRoundId', 'attr' => 'review_round_new_id', 'type' => PDO::PARAM_INT),
									array('name' => ':updtRevRound_stageId', 'attr' => 'stage_id', 'type' => PDO::PARAM_INT),
									array('name' => ':updtRevRound_round', 'attr' => 'round', 'type' => PDO::PARAM_INT),
									array('name' => ':updtRevRound_reviewRevision', 'attr' => 'review_revision', 'type' => PDO::PARAM_INT),
									array('name' => ':updtRevRound_status', 'attr' => 'status', 'type' => PDO::PARAM_INT)
								);
								
								if (myExecute('update', 'review_round', $arr, $updtRevRoundSTMT, $errors)) {  //from helperFunctions.php
									echo "Ok\n";
									
									$numUpdates['review_rounds']++;
									
								}
								else {
									echo "Failed\n";
								}
								
							}//// end of the else block to update the review_round //////////
						}
						else {
							// did not fetch the review round
							$checkError['error'] = 'Did not fetch the review_round';
						}
					}
					else {
						// did not execute the checkRevRoundSTMT
						$checkError['error'] = 'The checkRevRoundSTMT did not execute';
						$checkError['stmtError'] = $checkRevRoundSTMT->errorInfo();
					}
					
					if (array_key_exists('error', $checkError)) {
						array_push($errors['review_round']['check'], $checkError);
					}
					
				}//end of the if the review_roundId is in the dataMapping
				else { /// insert the review_round ////////////////////////
					echo '    inserting review_round #' . $revRound['review_round_id'] . ' .................';
				
					$arr = array();
					$arr['data'] = $revRound;
					$arr['params'] = array(
						array('name' => ':revRound_submissionId', 'attr' => 'submission_new_id', 'type' => PDO::PARAM_INT),
						array('name' => ':revRound_stageId', 'attr' => 'stage_id', 'type' => PDO::PARAM_INT),
						array('name' => ':revRound_round', 'attr' => 'round', 'type' => PDO::PARAM_INT),
						array('name' => ':revRound_reviewRevision', 'attr' => 'review_revision', 'type' => PDO::PARAM_INT),
						array('name' => ':revRound_status', 'attr' => 'status', 'type' => PDO::PARAM_INT)
					);
					
					if (myExecute('insert', 'review_round', $arr, $insertReviewRoundSTMT, $errors)) {  //from helperFunctions.php
						echo "Ok\n";
						
						$numInsertions['review_rounds']++;
						
						if (getNewId('review_round', $lastReviewRoundsSTMT, $revRound, $dataMapping, $errors)) { //from helperFunctions.php
							echo "        review round new id = " . $revRound['review_round_new_id'] . "\n";
							//$reviewIdOk = true;
						}
					}
					else {
						echo "Failed\n";
					}
				} //// end of the else block to insert the review_round
				
			}//end of the foreach review_round
			unset($revRound);
			}//closing the if review_rounds is empty
			}//closing the if review_rounds exist
			
			///////////////// end of insert the review_rounds  //////////////
			
			
			
			////////////// insert the review_assignments /////////////////
			if (array_key_exists('review_assignments', $article)) { if (!empty($article['review_assignments']) && $article['review_assignments'] != null) { 
			//echo "\nInsert the review assignments:\n";
			
			foreach ($article['review_assignments'] as &$revAssign) {
				
				$submissionIdOk = false;
				$reviewerIdOk = false;
				$reviewFormIdOk = false;
				$reviewerFileIdOk = false;
				$reviewRoundIdOk = false;
				
				$reviewIdOk = false; // variable to control if the review_form_response can be inserted
				
				validateData('review_assignment', $revAssign); //from helperFunctions.php
				
				$error = ['review_id' => $revAssign['review_id']];
				
				$revAssign['submission_new_id'] = $article['article_new_id'];
				
				////////////////// check the reviewer_file_id /////////////////////////////////////////////////////////////////////////////////////
				if ($revAssign['reviewer_file_id'] === null) {
					$reviewerFileIdOk = true;
				}
				else {
					if (array_key_exists($revAssign['reviewer_file_id'], $dataMapping['file_id'])) {
						$revAssign['reviewer_file_new_id'] = $dataMapping['file_id'][$revAssign['reviewer_file_id']];
						$reviewerFileIdOk = true;
					}
					else {
						$error['reviewer_file_id'] = ['reviewer_file_id' => $revAssign['reviewer_file_id'], 'error' => 'this reviewer_file_id not in the dataMapping.'];
						//echo "\nreviewer_file_id not ok\n";
					}
				}
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				
				/////////////////// check the reviewer_id //////////////////////////////////////////////////////////////////////////////////////////////
				if (array_key_exists($revAssign['reviewer_id'], $dataMapping['reviewer_id'])) {
					$revAssign['reviewer_new_id'] = $dataMapping['reviewer_id'][$revAssign['reviewer_id']];
					$reviewerIdOk = true;
				}
				else if (array_key_exists($revAssign['reviewer_id'], $dataMapping['user_id'])) {
					$dataMapping['reviewer_id'][$revAssign['reviewer_id']] = $dataMapping['user_id'][$revAssign['reviewer_id']];
					$revAssign['reviewer_new_id'] = $dataMapping['reviewer_id'][$revAssign['reviewer_id']];
					$reviewerIdOk = true;
				}
				else if (is_array($revAssign['reviewer'])) {
					$reviewerIdOk = processUser($revAssign['reviewer'], array('type' => 'review_assignment', 'data' => $revAssign), $dataMapping, 
						$errors, $insertedUsers, $userStatements, $conn);
					
					if ($reviewerIdOk) {
						$revAssign['reviewer_new_id'] = $revAssign['reviewer']['user_new_id'];
						if (!array_key_exists($revAssign['reviewer_id'], $dataMapping['reviewer_id'])) {
							$dataMapping['reviewer_id'][$revAssign['reviewer_id']] = $revAssign['reviewer_new_id'];
						}
					}
					else {
						$error['reviewer_id'] = array('reviewer_id' => $revAssign['reviewer_id'], 'error' => 'processUser returned false');
						//echo "\nreviewer_id not ok\n";
					}
				}
				//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				
				/////////////////// check the review_form_id /////////////////////////////////////////////////////
				if ($revAssign['review_form_id'] === null) {
					$revAssign['review_form_new_id'] = null;
					$reviewFormIdOk = true;
				}
				else if (array_key_exists($revAssign['review_form_id'], $dataMapping['review_form_id'])) {
					$revAssign['review_form_new_id'] = $dataMapping['review_form_id'][$revAssign['review_form_id']];
					$reviewFormIdOk = true;
				}
				else {
					$error['review_form_id'] = array('review_form_id' => $revAssign['review_form_id'], 'error' => 'this review_form_id not in the dataMapping.');
					//echo "\nreview_form_id not ok\n";
				}
				///////////////////////////////////////////////////////////////////////////////////////////////////
				
				//////////// check the review_round_id  /////////////////////////////////////
				if ($revAssign['review_round_id'] === null) {
					$revAssign['review_round_new_id'] = null;
					$reviewRoundIdOk = true;
				}
				else if (array_key_exists($revAssign['review_round_id'], $dataMapping['review_round_id'])) {
					$revAssign['review_round_new_id'] = $dataMapping['review_round_id'][$revAssign['review_round_id']];
					$reviewRoundIdOk = true;
				}
				else if ($revAssign['review_round_id'] == 0 || $revAssign['review_round_id'] === null) {
					$revAssign['review_round_new_id'] = $revAssign['review_round_id'];
					$reviewRoundIdOk = true;
				}
				else {
					$error['review_round_id'] = array('review_round_id' => $revAssign['review_round_id'], 'error' => 'this review_round_id is not in the dataMapping.');
				}
				/////////////////////////////////////////////////////////////////////////////
				
				if ($reviewerIdOk && $reviewFormIdOk && $reviewerFileIdOk && $reviewRoundIdOk) {
					
					if (array_key_exists($revAssign['review_id'], $dataMapping['review_id'])) {
						$reviewIdOk = true;
						$revAssign['review_new_id'] = $dataMapping['review_id'][$revAssign['review_id']];
						
						echo "\n    The review_assignment #" . $revAssign['review_id'] . " was already imported.\n";
						echo "        Its new id is #" . $revAssign['review_new_id'] . " \n";
						
						// the review assignment was imported, so we need to check if it needs to be updated
						$checkError = array('review_assignment' => $revAssign);
						
						$checkRevAssignSTMT->bindParam(':checkRevAssign_reviewId', $revAssign['review_new_id']);
						if ($checkRevAssignSTMT->execute()) {
							if ($fetchedRevAssign = $checkRevAssignSTMT->fetch(PDO::FETCH_ASSOC)) {
								$compareArgs = array('type' => 'review_assignment');
								if (same2($revAssign, $fetchedRevAssign, $compareArgs) > 0) { // from helperFunctions.php function #16
									$revAssign['DNU'] = true;
									echo "    It is already up to date\n";
								}
								else {
									//update the review assignment
									
									$arr = array();
									$arr['data'] = $revAssign;
									$arr['params'] = array(
										array('name' => ':updtRevAssign_competingInterests', 'attr' => 'competing_interests', 'type' => PDO::PARAM_STR),
										array('name' => ':updtRevAssign_regretMessage', 'attr' => 'regret_message', 'type' => PDO::PARAM_STR),
										array('name' => ':updtRevAssign_recommendation', 'attr' => 'recommendation', 'type' => PDO::PARAM_STR),
										array('name' => ':updtRevAssign_dateAssigned', 'attr' => 'date_assigned', 'type' => PDO::PARAM_STR),
										array('name' => ':updtRevAssign_dateNotified', 'attr' => 'date_notified', 'type' => PDO::PARAM_STR),
										array('name' => ':updtRevAssign_dateConfirmed', 'attr' => 'date_confirmed', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_dateCompleted', 'attr' => 'date_completed', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_dateAcknowledged', 'attr' => 'date_acknowledged', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_dateDue', 'attr' => 'date_due', 'type' => PDO::PARAM_STR),
										array('name' => ':updtRevAssign_lastModified', 'attr' => 'last_modified', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_reminderWasAutomatic', 'attr' => 'reminder_was_automatic', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_declined', 'attr' => 'declined', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_replaced', 'attr' => 'replaced', 'type' => PDO::PARAM_STR),
										array('name' => ':updtRevAssign_cancelled', 'attr' => 'cancelled', 'type' => PDO::PARAM_STR),
										array('name' => ':updtRevAssign_reviewerFileId', 'attr' => 'reviewer_file_new_id', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_dateRated', 'attr' => 'date_rated', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_dateReminded', 'attr' => 'date_reminded', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_quality', 'attr' => 'quality', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_reviewRoundId', 'attr' => 'review_round_new_id', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_stageId', 'attr' => 'stage_id', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_reviewMethod', 'attr' => 'review_method', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_round', 'attr' => 'round', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_step', 'attr' => 'step', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_reviewFormId', 'attr' => 'review_form_new_id', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_unconsidered', 'attr' => 'unconsidered', 'type' => PDO::PARAM_INT),
										array('name' => ':updtRevAssign_reviewId', 'attr' => 'review_new_id', 'type' => PDO::PARAM_INT)
									);
														
									
									echo '    updating review_assignment #' . $revAssign['review_new_id'] . ' ......... '; 
									
									if (myExecute('update', 'review_assignment', $arr, $updtRevAssignSTMT, $errors)) { //from helperFunctions.php
										echo "Ok\n";
										
										$numUpdates['review_assignments']++;
										
									}
									else {
										echo "Failed\n";
									}
									
								}// end of the else block to update the review assignment
							}//end of the if fetched the review assignment	
							else {
								// did not fetch any review assignment from the database
								$checkError['error'] = 'Did not fetch the review_assignment';
							}
						}//end of the if checkRevAssignSTMT executed
						else {
							// the checkRevAssignSTMT did not execute
							$checkError['error'] = 'The checkRevAssignSTMT did not execute';
							$checkError['stmtError'] = $checkRevAssignSTMT->errorInfo();
						}
						
						if (array_key_exists('error', $checkError)) {
							array_push($errors['review_assignment']['check'], $checkError);
						}
						
					}//end of the if the review assignment was already imported
					else {
						// insert the review assignment
						$arr = array();
						$arr['data'] = $revAssign;
						$arr['params'] = array(
							array('name' => ':revAssign_submissionId', 'attr' => 'submission_new_id', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_reviewerId', 'attr' => 'reviewer_new_id', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_competingInterests', 'attr' => 'competing_interests', 'type' => PDO::PARAM_STR),
							array('name' => ':revAssign_regretMessage', 'attr' => 'regret_message', 'type' => PDO::PARAM_STR),
							array('name' => ':revAssign_recommendation', 'attr' => 'recommendation', 'type' => PDO::PARAM_STR),
							array('name' => ':revAssign_dateAssigned', 'attr' => 'date_assigned', 'type' => PDO::PARAM_STR),
							array('name' => ':revAssign_dateNotified', 'attr' => 'date_notified', 'type' => PDO::PARAM_STR),
							array('name' => ':revAssign_dateConfirmed', 'attr' => 'date_confirmed', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_dateCompleted', 'attr' => 'date_completed', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_dateAcknowledged', 'attr' => 'date_acknowledged', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_dateDue', 'attr' => 'date_due', 'type' => PDO::PARAM_STR),
							array('name' => ':revAssign_lastModified', 'attr' => 'last_modified', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_reminderAuto', 'attr' => 'reminder_was_automatic', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_declined', 'attr' => 'declined', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_replaced', 'attr' => 'replaced', 'type' => PDO::PARAM_STR),
							array('name' => ':revAssign_cancelled', 'attr' => 'cancelled', 'type' => PDO::PARAM_STR),
							array('name' => ':revAssign_reviewerFileId', 'attr' => 'reviewer_file_new_id', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_dateRated', 'attr' => 'date_rated', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_dateReminded', 'attr' => 'date_reminded', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_quality', 'attr' => 'quality', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_reviewRoundId', 'attr' => 'review_round_id', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_stageId', 'attr' => 'stage_id', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_reviewMethod', 'attr' => 'review_method', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_round', 'attr' => 'round', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_step', 'attr' => 'step', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_reviewFormId', 'attr' => 'review_form_new_id', 'type' => PDO::PARAM_INT),
							array('name' => ':revAssign_unconsidered', 'attr' => 'unconsidered', 'type' => PDO::PARAM_INT)
						);
						
						
						echo "\n    inserting review_assignment #" . $revAssign['review_id'] . " ......... "; 
						
						if (myExecute('insert', 'review_assignment', $arr, $insertReviewAssignmentSTMT, $errors)) { //from helperFunctions.php
							echo "Ok\n";
							
							$numInsertions['review_assignments'];
							
							if (getNewId('review_assignment', $lastReviewAssignmentsSTMT, $revAssign, $dataMapping, $errors)) { //from helperFunctions.php
								echo "        review new id = " . $revAssign['review_new_id'] . "\n";
								$reviewIdOk = true;
							}
							
						}
						else {
							echo "Failed\n";
						}
					}//end of the else block to insert the review_assignment
					
				}
				else {
					//some of the ids are not ok
					array_push($errors['review_assignment']['insert'], $error); //put the error in the review_assignments insert part
				}
				
				if ($reviewIdOk) {
					//insert the review_form_responses //////////////////////
					if (array_key_exists('review_form_responses', $revAssign)) { if (!empty($revAssign['review_form_responses']) && $revAssign['review_form_responses'] != null) {
						
					echo "    inserting review_form_responses:\n";
						
					foreach ($revAssign['review_form_responses'] as &$revFormResponse) {
						
						$revFormResponse['review_new_id'] = $revAssign['review_new_id'];
						
						validateData('review_form_response', $revFormResponse); //from helperFunctions.php
						
						$reviewFormElementIdOk = false;
						
						if (array_key_exists($revFormResponse['review_form_element_id'], $dataMapping['review_form_element_id'])) {
							$revFormResponse['review_form_element_new_id'] = $dataMapping['review_form_element_id'][$revFormResponse['review_form_element_id']];
							$reviewFormElementIdOk = true;
						}
						else {
							//put it in the errors array
						}
						
						if ($reviewFormElementIdOk) {
							
							//check if the review_form_response alreay exists and if needs to be updated
							$checkRevFormRespSTMT->bindParam(':checkRevFormResp_reviewFormElementId', $revFormResponse['review_form_element_new_id'], PDO::PARAM_INT);
							$checkRevFormRespSTMT->bindParam(':checkRevFormResp_reviewId', $revFormResponse['review_new_id'], PDO::PARAM_INT);
							
							$checkError = array('review_form_response' => $revFormResponse);
							
							if ($checkRevFormRespSTMT->execute()) {
								if ($fetchedRevFormResponse = $checkRevFormRespSTMT->fetch(PDO::FETCH_ASSOC)) {
									
									$compareArgs = array('type' => 'review_form_response');
									if (same2($fetchedRevFormResponse, $revFormResponse) > 0) { //from helperFunctions.php function #16
										//the review_form_response doe not need to be updated
										echo "\n    the review_form_response with review_id #" . $revFormResponse['review_new_id'] . " and review_form_element_id #" .
										$revFormResponse['review_form_element_new_id'] . " is already up to date.\n";
									}
									else { /////// update the review_form_response  ////////////////////////////////////////////////
										echo "\n";
										echo '    updating the review_form_response with review_id #' . $revFormResponse['review_new_id'] . ' and review_form_element_id #' .
										$revFormResponse['review_form_element_new_id'] . ' ............';
									
										$arr = array();
										$arr['data'] = $revFormResponse;
										$arr['params'] = array(
											array('name' => ':updtRevFormResp_reviewFormElementId', 'attr' => 'review_form_element_new_id', 'type' => PDO::PARAM_INT),
											array('name' => ':updtRevFormResp_reviewId', 'attr' => 'review_new_id', 'type' => PDO::PARAM_INT),
											array('name' => ':updtRevFormResp_responseType', 'attr' => 'response_type', 'type' => PDO::PARAM_STR),
											array('name' => ':updtRevFormResp_reponseValue', 'attr' => 'response_value', 'type' => PDO::PARAM_STR)
										);
										
										if (myExecute('update', 'review_form_response', $arr, $updtReviewFormResponseSTMT, $errors)) {  //from helperFunctions.php
											echo "Ok\n";
											
											$numUpdates['review_form_responses']++;
											
										}
										else {
											echo "Failed\n";
										}
										
										
									}// end of the else block to update the review_form_response ///////////////////////////////////
									
								}//end of the if fetched the review_form_response
								else { ///////////////////  insert the review_form_response ///////////////////////////
									echo '        inserting review_form_response with review_form_element_id #' . $revFormResponse['review_form_element_new_id'] . ' and review_id #' . 
									$revFormResponse['review_new_id'] . ' .................';
									
									validateData('review_form_response', $revFormResponse); //from helperFunctions.php
									
									$arr = array();
									$arr['data'] = $revFormResponse;
									$arr['params'] = array(
										array('name' => ':response_reviewFormElementId', 'attr' => 'review_form_element_new_id', 'type' => PDO::PARAM_INT),
										array('name' => ':reponse_reviewId', 'attr' => 'review_new_id', 'type' => PDO::PARAM_INT),
										array('name' => ':response_responseType', 'attr' => 'response_type', 'type' => PDO::PARAM_STR),
										array('name' => ':response_reponseValue', 'attr' => 'response_value', 'type' => PDO::PARAM_STR)
									);
									
									if (myExecute('insert', 'review_form_response', $arr, $insertReviewFormResponseSTMT, $errors)) {  //from helperFunctions.php
										echo "Ok\n";
										
										$numInsertions['review_form_responses']++;
										
									}
									else {
										echo "Failed\n";
									}
								}////////////// end of the else block to insert the review_form_response //////////////
							}
							else {
								$checkError['error'] = 'The checkRevFormResponseSTMT did not execute';
								$checkError['stmtError'] = $checkRevFormResponseSTMT->errorInfo();
							}
							
							if (array_key_exists('error', $checkError)) {
								array_push($errors['review_form_response']['check'], $checkError);
							}
							
							
						}//end of the if reviewFormElementIdOk
						
					}//end of the foreach review_form_response
					unset($revFormResponse);
					}//closing the if review_form_responses is empty
					}//closing the if review_form_responses exist
					
					//////// end of insert review_form_responses ///////////////
				}//closing the if reviewIdOk
				
			}//end of the foreach review_assignments
			unset($revAssign);
			}// closing the if review_assignments is empty
			}//closing the if review_assignments exists
				
			//////////////// end of insert the review_assignments ///////////////
			
			/////  END OF INSERT THE REVIEWS  //////////////////////////
			
		}//end of the if articleOk
		
		else {
			//article not ok
			// the getNewId function already flagged the error
		}
	}//end of foreach articles
	unset($article);
	
	
	//////////////////////  END OF THE INSERT STAGE  ////////////////////////////////////////////////////////////////////////////////
	
	if ($numInsertions['articles'] > 0) {
	///////////////////// BEGINNING OF THE UPDATE STAGE  ///////////////////////////////////////////////////////////////////////////
	
	$updateArticleSTMT = $conn->prepare('UPDATE articles SET submission_file_id = :updateArticle_submissionFileId, revised_file_id = :updateArticle_revisedFileId, 
		review_file_id = :updateArticle_reviewFileId, editor_file_id = :updateArticle_editorFileId WHERE article_id = :updateArticle_articleId');
	
	$updateArticleFileSTMT = $conn->prepare('UPDATE article_files SET source_file_id = :updateFile_sourceFileId, file_name = :updateFile_fileName, 
		original_file_name = :updateFile_originalFileName WHERE file_id = :updateFile_fileId AND revision = :updateFile_revision');
	
	//loop through every article to update data to the correct ones in the database
	foreach($articles as &$article) {
		
		if (array_key_exists('DNU', $article)) { if ($article['DNU']) {
			//DNU means Do Not Update
			continue; // go to the next article
		}}
		
		//updatting article
		$article['submission_file_new_id'] = null;
		$article['revised_file_new_id'] = null;
		$article['review_file_new_id'] = null;
		$article['editor_file_new_id'] = null;
		
		$updateArticle = true;
		
		if ($article['submission_file_id'] !== null && $article['submission_file_id'] !== '' && array_key_exists($article['submission_file_id'], $dataMapping['file_id'])) {
			$article['submission_file_new_id'] = $dataMapping['file_id'][$article['submission_file_id']];
		}
		if ($article['revised_file_id'] !== null && $article['revised_file_id'] !== '' && array_key_exists($article['revised_file_id'], $dataMapping['file_id'])) {
			$article['revised_file_new_id'] = $dataMapping['file_id'][$article['revised_file_id']];
		}
		if ($article['review_file_id'] !== null && $article['review_file_id'] !== '' && array_key_exists($article['review_file_id'], $dataMapping['file_id'])) {
			$article['review_file_new_id'] = $dataMapping['file_id'][$article['review_file_id']];
		}
		if ($article['editor_file_id'] !== null && $article['editor_file_id'] !== '' && array_key_exists($article['editor_file_id'], $dataMapping['file_id'])) {
			$article['editor_file_new_id'] = $dataMapping['file_id'][$article['editor_file_id']];
		}
		
		if ($article['submission_file_new_id'] === null && $article['revised_file_new_id'] === null && $article['review_file_new_id'] === null && $article['editor_file_new_id'] === null) {
			$updateArticle = false;
		}
		
		if (!array_key_exists('article_new_id', $article)) {
			$updateArticle = false;
		}
		
		if ($updateArticle) {
			$arr = array();
			$arr['data'] = $article;
			$arr['params'] = array(
				array('name' => ':updateArticle_submissionFileId', 'attr' => 'submission_file_new_id', 'type' => PDO::PARAM_INT),
				array('name' => ':updateArticle_revisedFileId', 'attr' => 'revised_file_new_id', 'type' => PDO::PARAM_INT),
				array('name' => ':updateArticle_reviewFileId', 'attr' => 'review_file_new_id', 'type' => PDO::PARAM_INT),
				array('name' => ':updateArticle_editorFileId', 'attr' => 'editor_file_new_id', 'type' => PDO::PARAM_INT),
				array('name' => ':updateArticle_articleId', 'attr' => 'article_new_id', 'type' => PDO::PARAM_INT)
			);
			
			echo "\nupdating article #" . $article['article_new_id'] . " ......... "; 
			
			if (myExecute('update', 'article', $arr, $updateArticleSTMT, $errors)) { //from helperFunctions.php
				echo "Ok\n";
				
				$numUpdates['articles']++;
				
			}
			else {
				echo "Failed\n";
			}
		}// closing the if updateArticle
		
		//updating article_files
		if (array_key_exists('files', $article)) { if (!empty($article['files']) && $article['files'] != null) {
		foreach ($article['files'] as &$articleFile) {
			
			if (array_key_exists('DNU', $articleFile)) { if ($articleFile['DNU']) {
				//DNU means Do Not Update
				continue; // go to the next article file
			}}
			
			if (!array_key_exists('article_new_id', $articleFile)) {
				$articleFile['article_new_id'] = $article['article_new_id'];
			}
			
			$articleFile['source_file_new_id'] = null;
			$articleFile['file_new_name'] = null;
			$articleFile['original_file_new_name'] = null;
			$updateFile = true;
			
			if ($articleFile['source_file_id'] !== null && $articleFile['source_file_id'] !== '' && array_key_exists($articleFile['source_file_id'], $dataMapping['file_id'])) {
				$articleFile['source_file_new_id'] = $dataMapping['file_id'][$articleFile['source_file_id']];
			}
			if (isStandardName($articleFile['file_name'])) {
				$msg = '';
				$articleFile['file_new_name'] = setNewName($articleFile['file_name'], $dataMapping, $msg);
				if ($articleFile['file_new_name'] !== null) {
					$dataMapping['file_name'][$articleFile['file_name']] = $articleFile['file_new_name'];
				}
				else {
					$updateFile = false;
					if (array_key_exists($articleFile['article_new_id'], $errors['article_file']['update'])) {
						$errors['article_file']['update'][$articleFile['article_new_id']]['file_name'] = $msg;
					}
					else {
						$errors['article_file']['update'][$articleFile['article_new_id']] = array();
						$errors['article_file']['update'][$articleFile['article_new_id']]['file_name'] = $msg;
					}
				}
			}
			else {
				$updateFile = false;
				$articleFile['file_new_name'] = $articleFile['file_name'];
				if (array_key_exists($articleFile['article_new_id'], $errors['article_file']['update'])) {
					$errors['article_file']['update'][$articleFile['article_new_id']]['file_name'] = 'file_name ' . $articleFile['file_name'] . ' is not standard.';
				}
				else {
					$errors['article_file']['update'][$articleFile['article_new_id']] = array();
					$errors['article_file']['update'][$articleFile['article_new_id']]['file_name'] = 'file_name ' . $articleFile['file_name'] . ' is not standard.';
				}
			}
			
			if (isStandardName($articleFile['original_file_name'])) {
				$msg = '';
				$articleFile['original_file_new_name'] = setNewName($articleFile['original_file_name'], $dataMapping, $msg);
				if ($articleFile['original_file_new_name'] !== null) {
					$dataMapping['original_file_name'][$articleFile['original_file_name']] = $articleFile['original_file_new_name'];
				}
				else {
					$updateFile = false;
					if (array_key_exists($articleFile['article_new_id'], $errors['article_file']['update'])) {
						$errors['article_file']['update'][$articleFile['article_new_id']]['original_file_name'] = $msg;
					}
					else {
						$errors['article_file']['update'][$articleFile['article_new_id']] = array();
						$errors['article_file']['update'][$articleFile['article_new_id']]['original_file_name'] = $msg;
					}
				}
			}
			
			if (!array_key_exists('file_new_id', $articleFile)) {
				$updateFile = false;
			}
			
			if ($updateFile) {
				$arr = array();
				$arr['data'] = $articleFile;
				$arr['params'] = array(
					array('name' => ':updateFile_sourceFileId', 'attr' => 'source_file_new_id', 'type' => PDO::PARAM_INT),
					array('name' => ':updateFile_fileName', 'attr' => 'file_new_name', 'type' => PDO::PARAM_STR),
					array('name' => ':updateFile_originalFileName', 'attr' => 'original_file_new_name', 'type' => PDO::PARAM_STR),
					array('name' => ':updateFile_fileId', 'attr' => 'file_new_id', 'type' => PDO::PARAM_INT),
					array('name' => ':updateFile_revision', 'attr' => 'revision', 'type' => PDO::PARAM_INT)
				);
				
				echo "\nupdating file #" . $articleFile['file_new_id'] . " ......... "; 
				
				if (myExecute('update', 'article_file', $arr, $updateArticleFileSTMT, $errors)) { //from helperFunctions.php
					echo "Ok\n";
					
					$numUpdates['article_files']++;
				}
				else {
					echo "Failed\n";
				}
			}//closing the if updateFile
		}//end of foreach article file
		unset($articleFile);
		}//closing the if article_files is empty
		}//closing the if article_files exist
		
	}//end of the foreach articles
	unset($article);
	
	////////////////////  END OF THE UPDATE STAGE  /////////////////////////////////////////////////////////////////////////////////
	
	}// closing the if numInsertions['articles'] > 0
	
	$returnData = array(
		'errors' => $errors, 
		'insertedUsers' => $insertedUsers, 
		'numInsertedRecords' => $numInsertions, 
		'numUpdatedRecords' => $numUpdates
	);
	
	return $returnData;
}
//////// END OF insertUnpublishedArticles  ////////////////////////////////////////////////////////


// #07)

function insertAnnouncements(&$xml, $conn, &$dataMapping, $journalNewId, $args = null) {
	$limit = 10;
	
	if (is_array($args)) {
		if (array_key_exists('limit', $args)) {
			$limit = $args['limit'];
		}
	}
	
	if (!array_key_exists('announcement_id', $dataMapping)) {
		$dataMapping['announcement_id'] = array();
	}
	
	
	///////  THE STATEMENTS  ////////////////////////////////////////////////////////////
	
	//$getAnnouncementsSTMT = $conn->prepare('SELECT * FROM announcements WHERE journal_id = :getAnnouncements_journalId');
	//$getAnnouncementSettingsSTMT = $conn->prepare('SELECT * FROM announcement_settings WHERE announcement_id = :getAnnouncementSettings_announcementId');
	
	// announcements
	$insertAnnouncementSTMT = $conn->prepare(
		'INSERT INTO announcements (assoc_id, type_id, date_expire, date_posted, assoc_type) 
		 VALUES (:assocId, :typeId, :dateExpire, :datePosted, :assocType)'
	);
	
	$lastAnnouncementsSTMT = $conn->prepare("SELECT * FROM announcements ORDER BY announcement_id DESC LIMIT $limit");
	
	$insertAnnouncementSettingsSTMT = $conn->prepare(
		'INSERT INTO announcement_settings (announcement_id, locale, setting_name, setting_value, setting_type) 
		 VALUES (:announcementId, :locale, :settingName, :settingValue, :settingType)'
	);
	
	//////////////////////////////////////////////////////////////////////////////////////
	
	$announcements_node = null;
	
	if ($xml->nodeName === 'announcements') {
		$announcements_node = $xml;
	}
	else {
		$announcements_node = $xml->getElementsByTagName('announcements')->item(0);
	}
	
	$errors = array(
		'announcement' => array(
			'insert' => array(), 
			'update' => array()
		),
		'announcement_settings' => array(
			'insert' => array(), 
			'update' => array()
		)
	);
	
	$announcements = xmlToArray($announcements_node, true); //from helperFunctions.php
	
	$numInsertedAnnouncements = 0;
	
	foreach ($announcements as &$ann) {
		
		if (array_key_exists($ann['announcement_id'], $dataMapping['announcement_id'])) {
			echo "\nannouncement #" . $ann['announcement_id'] . " was already imported.\n";
			continue; // go to the next announcement
		}
		
		$announcementOk = false;
		$ann['assoc_new_id'] = $journalNewId;
		
		validateData('announcement', $ann); //from helperFunctions.php
		
		$arr = array();
		$arr['data'] = $ann;
		$arr['params'] = array(
			array('name' => ':assocId', 'attr' => 'assoc_new_id', 'type' => PDO::PARAM_INT),
			array('name' => ':typeId', 'attr' => 'type_id', 'type' => PDO::PARAM_INT),
			array('name' => ':dateExpire', 'attr' => 'date_expire', 'type' => PDO::PARAM_STR),
			array('name' => ':datePosted', 'attr' => 'date_posted', 'type' => PDO::PARAM_STR),
			array('name' => ':assocType', 'attr' => 'assoc_type', 'type' => PDO::PARAM_INT)
		);
		
		echo '    inserting announcement #' . $ann['announcement_id']. '............ ';
		
		if (myExecute('insert', 'announcement', $arr, $insertAnnouncementSTMT, $errors)) { //from helperFunctions.php
			echo "Ok\n";
			if (getNewId('announcement', $lastAnnouncementsSTMT, $ann, $dataMapping, $errors)) { //from helperFunctions.php
				echo "    new id = " . $ann['announcement_new_id'] . "\n\n";
				$announcementOk = true;
				$numInsertedAnnouncements++;
			}
		}
		else {
			echo "Failed\n";
		}
		
		if ($announcementOk) {
			//insert the announcement settings
			if (array_key_exists('settings', $ann)) { if (!empty($ann['settings']) && $ann['settings'] != null) {
			
				foreach ($ann['settings'] as $setting) {
					validateData('announcement_settings', $setting); 
					
					$setting['announcement_new_id'] = $ann['announcement_new_id'];
					echo '    inserting '. $setting['setting_name'] . ' with locale ' . $setting['locale'] . ' .........';
					
					$arr = array();
					$arr['data'] = $setting;
					$arr['params'] = array(
						array('name' => ':announcementId', 'attr' => 'announcement_new_id', 'type' => PDO::PARAM_INT),
						array('name' => ':locale', 'attr' => 'locale', 'type' => PDO::PARAM_STR),
						array('name' => ':settingName', 'attr' => 'setting_name', 'type' => PDO::PARAM_STR),
						array('name' => ':settingValue', 'attr' => 'setting_value', 'type' => PDO::PARAM_STR),
						array('name' => ':settingType', 'attr' => 'setting_type', 'type' => PDO::PARAM_STR)
					);
					
					if (myExecute('insert', 'announcement_settings', $arr, $insertAnnouncementSettingsSTMT, $errors)) { //from helperFunctions.php
						echo "Ok\n";
					}
					else {
						echo "Failed\n";
					}
				}//end of the foreach setting
				
			}}// end of the 2 ifs to see if announcement_settings exist
			
			///////////// end of insert announcement settings  //////////////////////
			
		}//end of the if announcementOk
	}//end of the foreach announcement	
	unset($ann);
	
	$returnData = array();
	$returnData['errors'] = $errors;
	$returnData['numInsertedRecords'] = array('announcements' => $numInsertedAnnouncements);
	
	return $returnData;
	
}
/////////////////// end of innsertAnnouncements  ////////////////////////////////

// #08) 

function insertEmailTemplates() {
	echo "\n\nTHE FUNCTION insertEmailTemplates DOES NOT DO ANYTHING\n\n";
}
/////////////////// end of insertEmailTemplates  /////////////////////////////////



// #09)

function insertGroups(&$xml, $conn, &$dataMapping, $journalNewId, $args = null) {
	$limit = 10;
	
	if (is_array($args)) {
		if (array_key_exists('limit', $args)) {
			$limit = $args['limit'];
		}
	}
	
	if (!array_key_exists('group_id', $dataMapping)) {
		$dataMapping['group_id'] = array();
	}
	
	if (!array_key_exists('user_id', $dataMapping)) {
		$dataMapping['user_id'] = array();
	}
	
	
	///////  THE STATEMENTS  ////////////////////////////////////////////////////////////
	
	// user statements ////////////////
	
	$userSTMT = $conn->prepare('SELECT * FROM users WHERE email = :user_email');
	
	$checkUsernameSTMT = $conn->prepare('SELECT COUNT(*) as count FROM users WHERE username = :checkUsername');
	
	$insertUserSTMT = $conn->prepare('INSERT INTO users (username, password, salutation, first_name, middle_name, last_name, gender, initials, email, url, phone, fax, mailing_address,
country, locales, date_last_email, date_registered, date_validated, date_last_login, must_change_password, auth_id, disabled, disabled_reason, auth_str, suffix, billing_address, 
inline_help) VALUES (:insertUser_username, :insertUser_password, :insertUser_salutation, :insertUser_firstName, :insertUser_middleName, :insertUser_lastName, :insertUser_gender, 
:insertUser_initials, :insertUser_email, :insertUser_url, :insertUser_phone, :insertUser_fax, :insertUser_mailingAddress, :insertUser_country, :insertUser_locales, 
:insertUser_dateLastEmail, :insertUser_dateRegistered, :insertUser_dateValidated, :insertUser_dateLastLogin, :insertUser_mustChangePassword, :insertUser_authId, :insertUser_disabled, 
:insertUser_disabledReason, :insertUser_authStr, :insertUser_suffix, :insertUser_billingAddress, :insertUser_inlineHelp)');
	
	$lastUsersSTMT = $conn->prepare("SELECT * FROM users ORDER BY user_id DESC LIMIT $limit");
	
	$insertUserSettingSTMT = $conn->prepare(
		'INSERT INTO user_settings (user_id, locale, setting_name, setting_value, setting_type, assoc_id, assoc_type)
		VALUES (:userSetting_userId, :userSetting_locale, :userSetting_settingName, :userSetting_settingValue, :userSetting_settingType,
		:userSetting_assocId, :userSetting_assocType)'
	);
	
	$insertUserRoleSTMT = $conn->prepare(
		'INSERT INTO roles (journal_id, user_id, role_id)
		VALUES (:userRole_journalId, :userRole_userId, :userRole_roleId)'
	);
	
	$insertUserInterestSTMT = $conn->prepare('INSERT INTO user_interests');
	
	$userStatements = array('userSTMT' => &$userSTMT, 'checkUsernameSTMT' => &$checkUsernameSTMT, 'insertUserSTMT' => &$insertUserSTMT, 'lastUsersSTMT' => &$lastUsersSTMT);
	
	///////////////////////////////////
	
	//$getGroupsSTMT = $conn->prepare('SELECT * FROM groups WHERE journal_id = :getGroups_journalId');
	//$getGroupSettingsSTMT = $conn->prepare('SELECT * FROM group_settings WHERE group_id = :getGroupSettings_groupId');
	
	// groups
	$insertGroupSTMT = $conn->prepare(
		'INSERT INTO groups (context, assoc_id, assoc_type, about_displayed, seq, publish_email) 
		 VALUES (:context, :assocId, :assocType, :groups_aboutDisplayed, :groups_seq, :publishEmail)'
	);
	
	$lastGroupsSTMT = $conn->prepare("SELECT * FROM groups ORDER BY group_id DESC LIMIT $limit");
	
	$insertGroupSettingsSTMT = $conn->prepare(
		'INSERT INTO group_settings (group_id, locale, setting_name, setting_value, setting_type) 
		 VALUES (:settings_groupId, :locale, :settingName, :settingValue, :settingType)'
	);
	
	$insertGroupMembershipSTMT = $conn->prepare(
		'INSERT INTO group_memberships (group_id, user_id, about_displayed, seq) 
		 VALUES (:memberships_groupId, :userId, :memberships_aboutDisplayed, :memberships_seq)'
	);
	
	$checkMembershipSTMT = $conn->prepare('SELECT * FROM group_memberships WHERE user_id = :check_userId AND group_id = :check_groupId');
	
	//////////////////////////////////////////////////////////////////////////////////////
	
	$groups_node = null;
	
	if ($xml->nodeName === 'groups') {
		$groups_node = $xml;
	}
	else {
		$groups_node = $xml->getElementsByTagName('groups')->item(0);
	}
	
	$errors = array(
		'group' => array(
			'insert' => array(), 
			'update' => array()
		),
		'group_settings' => array(
			'insert' => array(), 
			'update' => array()
		),
		'group_membership' => array(
			'insert' => array(), 
			'update' => array()
		),
		'user' => array(
			'insert' => array(),
			'update' => array()
		)
	);
	
	$groups = xmlToArray($groups_node, true); //from helperFunctions.php
	
	$numInsertedGroups = 0;
	$insertedUsers = array();
	
	foreach ($groups as &$grp) {
		
		$groupExists = false;
		
		if (array_key_exists($grp['group_id'], $dataMapping['group_id'])) {
			//echo "\ngroup #" . $grp['group_id'] . " was already imported.\n";
			//continue; // go to the next group
			$groupExists = true;
			$grp['group_new_id'] = $dataMapping['group_id'][$grp['group_id']];
		}
		
		$groupOk = true;
		$grp['assoc_new_id'] = $journalNewId;
		
		validateData('group', $grp); //from helperFunctions.php
		
		if (!$groupExists) {
			$arr = array();
			$arr['data'] = $grp;
			$arr['params'] = array(
				array('name' => ':context', 'attr' => 'context', 'type' => PDO::PARAM_INT),
				array('name' => ':assocId', 'attr' => 'assoc_new_id', 'type' => PDO::PARAM_INT),
				array('name' => ':assocType', 'attr' => 'assoc_type', 'type' => PDO::PARAM_INT),
				array('name' => ':groups_aboutDisplayed', 'attr' => 'about_displayed', 'type' => PDO::PARAM_INT),
				array('name' => ':groups_seq', 'attr' => 'seq'),
				array('name' => ':publishEmail', 'attr' => 'publish_email', 'type' => PDO::PARAM_INT),
				
			);
			
			echo '    inserting group #' . $grp['group_id']. '............ ';
			
			if (myExecute('insert', 'group', $arr, $insertGroupSTMT, $errors)) { //from helperFunctions.php
				echo "Ok\n";
				if (getNewId('group', $lastGroupsSTMT, $grp, $dataMapping, $errors)) { //from helperFunctions.php
					echo "    new id = " . $grp['group_new_id'] . "\n\n";
					$groupOk = true;
					$numInsertedGroups++;
				}
				else {
					$groupOk = false;
				}
			}
			else {
				echo "Failed\n";
			}
		}// end of the if !$groupExists
		
		
		if ($groupOk) {
			
			////// insert the group settings //////////////////////////////////
			
			if (array_key_exists('settings', $grp) && !$groupExists) { if (!empty($grp['settings']) && $grp['settings'] != null) {
			
				foreach ($grp['settings'] as &$setting) {
					validateData('group_settings', $setting); 
					
					$setting['group_new_id'] = $grp['group_new_id'];
					echo '    inserting '. $setting['setting_name'] . ' with locale ' . $setting['locale'] . ' .........';
					
					$arr = array();
					$arr['data'] = $setting;
					$arr['params'] = array(
						array('name' => ':settings_groupId', 'attr' => 'group_new_id', 'type' => PDO::PARAM_INT),
						array('name' => ':locale', 'attr' => 'locale', 'type' => PDO::PARAM_STR),
						array('name' => ':settingName', 'attr' => 'setting_name', 'type' => PDO::PARAM_STR),
						array('name' => ':settingValue', 'attr' => 'setting_value', 'type' => PDO::PARAM_STR),
						array('name' => ':settingType', 'attr' => 'setting_type', 'type' => PDO::PARAM_STR)
					);
					
					if (myExecute('insert', 'group_settings', $arr, $insertGroupSettingsSTMT, $errors)) { //from helperFunctions.php
						echo "Ok\n";
					}
					else {
						echo "Failed\n";
					}
				}//end of the foreach setting
				unset($setting);
			}}// end of the 2 ifs to see if group_settings exist
			
			///////////// end of insert group settings  //////////////////////
			
			
			////// insert the group memberships //////////////////////////////////
			
			if (array_key_exists('memberships', $grp)) { if (!empty($grp['memberships']) && $grp['memberships'] != null) {
			
				foreach ($grp['memberships'] as &$membership) {
					
					$userOk = false;
					$user = null;
					
					//////////////////// process user data ///////////////////////
				
					// check if the user is registered in the new journal
					if (array_key_exists($membership['user_id'], $dataMapping['user_id'])) {
						$membership['user_new_id'] = $dataMapping['user_id'][$membership['user_id']];
						$userOk = true;
					}
					else if (is_array($membership['user'])) {
						//$user = $membership['user'];
						$userOk = processUser($membership['user'], array('type' => 'group_membership', 'data' => $membership), $dataMapping,
							$errors, $insertedUsers, $userStatements, $conn);
						
						$membership['user_new_id'] = $membership['user']['user_new_id'];
					}
					//////////////////////////////////////////////////////////////
					
					if ($userOk) {
						validateData('group_membership', $membership); 
					
						$membership['group_new_id'] = $grp['group_new_id'];
						
						//echo "\nThe user new id in the membership is : " . $user['user_new_id'] . "\n\n";
						
						
						//$membership['user_new_id'] = $user['user_new_id'];
						
						$membershipExists = false;
						
						//////////// check the membership  /////////////////////
						
						$checkMembershipSTMT->bindParam(':check_userId', $membership['user_new_id'], PDO::PARAM_INT);
						$checkMembershipSTMT->bindParam(':check_groupId', $membership['group_new_id'], PDO::PARAM_INT);
						
						if ($checkMembershipSTMT->execute()) {
							if ($checkedMembership = $checkMembershipSTMT->fetch(PDO::FETCH_ASSOC)) {
								$membershipExists = true;
							}
						}
						else {
							$error = array('membership' => $membership, 'error' => $checkMembershipSTMT->errorInfo());
							array_push($errors['group_membership']['insert'], $error);
						}
						
						////////////////////////////////////////////////////////
						
						if (!$membershipExists) {
						
							$arr = array();
							$arr['data'] = $membership;
							$arr['params'] = array(
								array('name' => ':memberships_groupId', 'attr' => 'group_new_id', 'type' => PDO::PARAM_INT),
								array('name' => ':userId', 'attr' => 'user_new_id', 'type' => PDO::PARAM_INT),
								array('name' => ':memberships_aboutDisplayed', 'attr' => 'about_displayed', 'type' => PDO::PARAM_STR),
								array('name' => ':memberships_seq', 'attr' => 'seq')
							);
							
							echo "\nInserting user #" . $membership['user_new_id'] . " as a member of the group #" . $membership['group_new_id'] . " ...........";
							
							if (myExecute('insert', 'group_membership', $arr, $insertGroupMembershipSTMT, $errors)) { //from helperFunctions.php
								echo "Ok\n";
							}
							else {
								echo "Failed\n";
							}
						}// end of the if !$membershipExists
					}// end of the if userOk
					else {
						$error = array('group_id' => $grp['group_id'], 'user' => $membership['user'], 'error' => 'error while processing the user');
						array_push($errors['group_membership'], $error);
					}
					
				}//end of the foreach membership
				unset($membership);
			}}// end of the 2 ifs to see if group_memberships exist
			
			///////////// end of insert group memberships  //////////////////////
			
		}//end of the if groupOk
	}//end of the foreach group	
	unset($grp);
	
	$returnData = array();
	$returnData['errors'] = $errors;
	$returnData['insertedUsers'] = $insertedUsers;
	$returnData['numInsertedRecords'] = array('groups' => $numInsertedGroups);
	
	return $returnData;
	
}
////////////////  end of insertGroups  /////////////////////////////////////


// #10)
function insertReviewForms(&$xml, $conn, &$dataMapping, $journalNewId, $args = null) {
	$limit = 10;
	
	if (is_array($args)) {
		if (array_key_exists('limit', $args)) {
			$limit = $args['limit'];
		}
	}
	
	if (!array_key_exists('review_form_id', $dataMapping)) {
		$dataMapping['review_form_id'] = array();
	}
	
	if (!array_key_exists('review_form_element_id', $dataMapping)) {
		$dataMapping['review_form_element_id'] = array();
	}
	
	
	///////  THE STATEMENTS  ////////////////////////////////////////////////////////////
	
	// review_forms
	$insertReviewFormSTMT = $conn->prepare(
		'INSERT INTO review_forms (assoc_id, seq, is_active, assoc_type) 
		 VALUES (:assocId, :seq, :isActive, :assocType)'
	);
	
	$lastReviewFormsSTMT = $conn->prepare("SELECT * FROM review_forms ORDER BY review_form_id DESC LIMIT $limit");
	
	$insertReviewFormSettingsSTMT = $conn->prepare(
		'INSERT INTO review_form_settings (review_form_id, locale, setting_name, setting_value, setting_type) 
		 VALUES (:rfSettings_reviewFormId, :rfSettings_locale, :rfSettings_settingName, :rfSettings_settingValue, :rfSettings_settingType)'
	);
	
	$checkReviewFormSTMT = $conn->prepare('SELECT * FROM review_forms WHERE review_form_id = :checkRevForm_reviewFormId');
	
	$updtReviewFormSTMT = $conn->prepare('UPDATE review_forms SET 
		seq = :updtReviewForm_seq, is_active = :updtReviewForm_isActive
		WHERE review_form_id = :updtReviewForm_reviewFormId'
	);
	
	// review_form_elements
	$insertReviewFormElementSTMT = $conn->prepare(
		'INSERT INTO review_form_elements (review_form_id, seq, element_type, required, included)
		 VALUES (:rfElement_reviewFormId, :rfElement_seq, :rfElement_elementType, :rfElement_required, :rfElement_included)'
	);
	
	$lastReviewFormElementsSTMT = $conn->prepare("SELECT * FROM review_form_elements ORDER BY review_form_element_id DESC LIMIT $limit");
	
	$insertReviewFormElementSettingsSTMT = $conn->prepare(
		'INSERT INTO review_form_element_settings (review_form_element_id, locale, setting_name, setting_value, setting_type) 
		 VALUES (:rfElemSettings_reviewFormElementId, :rfElemSettings_locale, :rfElemSettings_settingName, :rfElemSettings_settingValue, :rfElemSettings_settingType)'
	);
	
	$checkRevFormElemSTMT = $conn->prepare('SELECT * FROM review_form_elements WHERE review_form_element_id = :checkRevFormElem_reviewFormElementId');
	
	$updtRevFormElemSTMT = $conn->prepare(
		'UPDATE review_form_elements SET
		seq = :updtRevFormElem_seq, element_type = :updtRevFormElem_elementType,
		required = :updtRevFormElem_required, included = :updtRevFormElem_included
		WHERE review_form_element = :updtRevFormElem_reviewFormElementId'
	);
	
	//////////////////////////////////////////////////////////////////////////////////////
	
	$review_forms_node = null;
	
	if ($xml->nodeName === 'review_forms') {
		$review_forms_node = $xml;
	}
	else {
		$review_forms_node = $xml->getElementsByTagName('review_forms')->item(0);
	}
	
	$errors = array(
		'review_form' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'review_form_settings' => array('insert' => array(), 'update' => array()),
		'review_form_element' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'review_form_element_settings' => array('insert' => array(), 'update' => array() )
	);
	
	$reviewForms = xmlToArray($review_forms_node, true); //from helperFunctions.php
	
	$numInsertions = array(
		'review_forms' => 0,
		'review_form_settings' => 0,
		'review_form_elements' => 0,
		'review_form_element_settings' => 0
	);
	
	$numUpdates = array(
		'review_forms' => 0, 
		'review_form_elements' => 0
	);
	
	foreach ($reviewForms as &$revForm) {
		
		$reviewFormOk = false;
		validateData('review_form', $revForm); //from helperFunctions.php
		
		if (array_key_exists($revForm['review_form_id'], $dataMapping['review_form_id'])) {
			
			$reviewFormOk = true;
			$revForm['review_form_new_id'] = $dataMapping['review_form_id'][$revForm['review_form_id']];
			echo "\nThe review_form #" . $revForm['review_form_id'] . " was already imported.\n";
			echo "Its new id is #" . $revForm['review_form_new_id'] . "\n";
			
			$checkError = array('review_form' => $revForm);
			
			//check if need to update the review_form
			$checkReviewFormSTMT->bindParam(':checkRevForm_reviewFormId', $revForm['review_form_new_id'], PDO::PARAM_INT);
			if ($checkReviewFormSTMT->execute()) {
				
				if ($fetchedRevForm = $checkReviewFormSTMT->fetch(PDO::FETCH_ASSOC)) {
					$compareArgs = array('type' => 'review_form');
					if (same2($fetchedRevForm, $revForm, $compareArgs) > 0) { // from helperFunctions.php function #16
						// do not need to update
						echo "It is already up to date\n";
					}
					else { ///// update the review_form /////////////////////////
						
						$arr = array();
						$arr['data'] = $revForm;
						$arr['params'] = array(
							array('name' => ':updtReviewForm_reviewFormId', 'attr' => 'review_form_new_id', 'type' => PDO::PARAM_INT),
							array('name' => ':updtReviewForm_seq', 'attr' => 'seq'),
							array('name' => ':updtReviewForm_isActive', 'attr' => 'is_active', 'type' => PDO::PARAM_INT)
						);
						
						
						echo '    updating review_form #' . $revForm['review_form_new_id']. '............ ';
						
						if (myExecute('update', 'review_form', $arr, $updtReviewFormSTMT, $errors)) { //from helperFunctions.php
							echo "Ok\n";
							$numUpdates['review_forms']++;
						}
						else {
							echo "Failed\n";
						}
						
					}//// end of the else block to update the review_form ///////
					
					continue; // go to the next review_form
				}
				else {
					// did not fetch the review_form
					$checkError['error'] = 'Did not fetch the review_form';
				}
				
			}//end of the if checkReviewFormSTMT executed
			else {
				// the checkReviewFormSTMT did not execute
				$checkError['error'] = 'The checkReviewFormSTMT did not execute';
				$checkError['stmtError'] = $checkReviewFormSTMT->errorInfo();
			}
			
			if (array_key_exists('error', $checkError)) {
				array_push($errors['review_form']['check'], $checkError);
			}
			
		}// end of the if review_form_id in the dataMapping
		else { ///// insert the review_form
			$reviewFormOk = false;
			$revForm['assoc_new_id'] = $journalNewId;
			
			$arr = array();
			$arr['data'] = $revForm;
			$arr['params'] = array(
				array('name' => ':assocId', 'attr' => 'assoc_new_id', 'type' => PDO::PARAM_INT),
				array('name' => ':seq', 'attr' => 'seq'),
				array('name' => ':isActive', 'attr' => 'is_active', 'type' => PDO::PARAM_INT),
				array('name' => ':assocType', 'attr' => 'assoc_type', 'type' => PDO::PARAM_INT)
			);
			
			echo '    inserting review_form #' . $revForm['review_form_id']. '............ ';
			
			if (myExecute('insert', 'review_form', $arr, $insertReviewFormSTMT, $errors)) { //from helperFunctions.php
				echo "Ok\n";
				$numInsertions['review_forms']++;
				if (getNewId('review_form', $lastReviewFormsSTMT, $revForm, $dataMapping, $errors)) { //from helperFunctions.php
					echo "    new id = " . $revForm['review_form_new_id'] . "\n\n";
					$reviewFormOk = true;
				}
			}
			else {
				echo "Failed\n";
			}
		}///// end of the else block to insert the review_form
		
		
		
		if ($reviewFormOk) {
			////////// insert the review_form settings ///////////////////////////////
			if (array_key_exists('settings', $revForm)) { if (!empty($revForm['settings']) && $revForm['settings'] != null) {
			
				foreach ($revForm['settings'] as &$setting) {
					validateData('review_form_settings', $setting); 
					
					$setting['review_form_new_id'] = $revForm['review_form_new_id'];
					echo '        inserting '. $setting['setting_name'] . ' with locale ' . $setting['locale'] . ' .........';
					
					$arr = array();
					$arr['data'] = $setting;
					$arr['params'] = array(
						array('name' => ':rfSettings_reviewFormId', 'attr' => 'review_form_new_id', 'type' => PDO::PARAM_INT),
						array('name' => ':rfSettings_locale', 'attr' => 'locale', 'type' => PDO::PARAM_STR),
						array('name' => ':rfSettings_settingName', 'attr' => 'setting_name', 'type' => PDO::PARAM_STR),
						array('name' => ':rfSettings_settingValue', 'attr' => 'setting_value', 'type' => PDO::PARAM_STR),
						array('name' => ':rfSettings_settingType', 'attr' => 'setting_type', 'type' => PDO::PARAM_STR)
					);
					
					if (myExecute('insert', 'review_form_settings', $arr, $insertReviewFormSettingsSTMT, $errors)) { //from helperFunctions.php
						echo "Ok\n";
						$numInsertions['review_form_settings']++;
					}
					else {
						echo "Failed\n";
					}
				}//end of the foreach setting
				
				unset($setting);
				
			}}// end of the 2 ifs to see if review_form_settings exist
			
			///////////// end of insert review_form settings  //////////////////////
			
			
			//////////  insert the review form elements  ///////////////////////////
			
			if (array_key_exists('elements', $revForm)) { if (!empty($revForm['elements']) && $revForm['elements'] != null) {
			
			foreach ($revForm['elements'] as &$element) {
				
				$reviewFormElementOk = false;
				
				validateData('review_form_element', $element); 
				
				$element['review_form_new_id'] = $revForm['review_form_new_id'];
				
				if (array_key_exists($element['review_form_element_id'], $dataMapping['review_form_element_id'])) {
					
					$element['review_form_element_new_id'] = $dataMapping['review_form_element_id'][$element['review_form_element_id']];
					
					echo "\nThe review_form_element #" . $element['review_form_element_id'] . " was already imported\n";
					echo "\nIts new id is #" . $element['review_form_element_id'] . "\n" ;
					
					
					//check if needs to update the review_form_element /////////
					$checkRevFormElemSTMT->bindParam(':checkRevFormElem_reviewFormElementId', $element['review_form_element_new_id'], PDO::PARAM_INT);
					
					$checkError = array('review_form_element' => $element);
					
					if ($checkRevFormElemSTMT->execute()) {
						if ($fetchedElement = $checkRevFormElemSTMT->fetch(PDO::FETCH_ASSOC)) {
							$compareArgs = array('type' => 'review_form_element');
							if (same2($fetchedElement, $element, $compareArgs) > 0) { //from helperFunctions.php function #16
								//does not need to update
								echo "It is already up to date\n";
								
							}
							else { ////// update the review_form_element  /////////////////////////////
								
								echo '    updating review form element #'. $element['review_form_element_new_id'] . ' .........';
					
								$arr = array();
								$arr['data'] = $element;
								$arr['params'] = array(
									array('name' => ':updtRevFormElem_reviewFormElementId', 'attr' => 'review_form_element_new_id', 'type' => PDO::PARAM_INT),
									array('name' => ':updtRevFormElem_seq', 'attr' => 'seq'),
									array('name' => ':updtRevFormElem_required', 'attr' => 'required', 'type' => PDO::PARAM_INT),
									array('name' => ':updtRevFormElem_included', 'attr' => 'included', 'type' => PDO::PARAM_INT),
									array('name' => ':updtRevFormElem_elementType', 'attr' => 'element_type', 'type' => PDO::PARAM_INT)
								);
					
								if (myExecute('update', 'review_form_element', $arr, $updtRevFormElementSTMT, $errors)) { //from helperFunctions.php
									echo "Ok\n";
									$numUpdates['review_form_elements']++;
								}
								else {
									echo "Failed\n";
								}
								
							}/// end of the else block to update the review_form_element //////////////
							
							continue; // go to the next review_form_element
						}
						else {
							//did not fetch the review_form_element
							$checkError['error'] = 'Did not fetch the review_form_element';
						}
					}
					else {
						// did not execute the checkRevFormElemSTMT
						$checkError['error'] = 'The checkRevFormElemSTMT did not execute';
						$checkError['stmtError'] = $checkRevFormElemSTMT->errorInfo();
					}
					
					if (array_key_exists('error', $checkError)) {
						array_push($errors['review_form_element']['check'], $checkError);
					}
	
				}//end of the if review_form_element_id in the dataMapping
				else { ////////// insert the review_form_element //////////////////////////
					
					echo '    inserting review form element #'. $element['review_form_element_id'] . ' .........';
					
					$arr = array();
					$arr['data'] = $element;
					$arr['params'] = array(
						array('name' => ':rfElement_reviewFormId', 'attr' => 'review_form_new_id', 'type' => PDO::PARAM_INT),
						array('name' => ':rfElement_seq', 'attr' => 'seq'),
						array('name' => ':rfElement_required', 'attr' => 'required', 'type' => PDO::PARAM_INT),
						array('name' => ':rfElement_included', 'attr' => 'included', 'type' => PDO::PARAM_INT),
						array('name' => ':rfElement_elementType', 'attr' => 'element_type', 'type' => PDO::PARAM_INT)
					);
		
					if (myExecute('insert', 'review_form_element', $arr, $insertReviewFormElementSTMT, $errors)) { //from helperFunctions.php
						echo "Ok\n";
						$numInsertions['review_form_elements']++;
						if (getNewId('review_form_element', $lastReviewFormElementsSTMT, $element, $dataMapping, $errors)) { //from helperFunctions.php
							echo "    new id = " . $element['review_form_element_new_id'] . "\n\n";
							$reviewFormElementOk = true;
						}
					}
					else {
						echo "Failed\n";
					}
				
				}//// end of the else block to insert the review_form_element  ////////////
				
				if ($reviewFormElementOk) {
					////////// insert the review_form settings ///////////////////////////////
					if (array_key_exists('settings', $element)) { if (!empty($element['settings']) && $element['settings'] != null) {
					
					foreach ($element['settings'] as &$setting) {
						validateData('review_form_element_settings', $setting); 
						
						$setting['review_form_element_new_id'] = $element['review_form_element_new_id'];
						echo '        inserting '. $setting['setting_name'] . ' with locale ' . $setting['locale'] . ' .........';
						
						$arr = array();
						$arr['data'] = $setting;
						$arr['params'] = array(
							array('name' => ':rfElemSettings_reviewFormElementId', 'attr' => 'review_form_element_new_id', 'type' => PDO::PARAM_INT),
							array('name' => ':rfElemSettings_locale', 'attr' => 'locale', 'type' => PDO::PARAM_STR),
							array('name' => ':rfElemSettings_settingName', 'attr' => 'setting_name', 'type' => PDO::PARAM_STR),
							array('name' => ':rfElemSettings_settingValue', 'attr' => 'setting_value', 'type' => PDO::PARAM_STR),
							array('name' => ':rfElemSettings_settingType', 'attr' => 'setting_type', 'type' => PDO::PARAM_STR)
						);
						
						if (myExecute('insert', 'review_form_element_settings', $arr, $insertReviewFormElementSettingsSTMT, $errors)) { //from helperFunctions.php
							echo "Ok\n";
							$numInsertions['review_form_element_settings']++;
						}
						else {
							echo "Failed\n";
						}
					}//end of the foreach setting
					
					unset($setting);
						
					}}// end of the 2 ifs to see if review_form_element settings exist
					
					///////////// end of insert review_form_element settings  //////////////////////
				}
			}//end of the foreach element
			
			unset($element);
				
			}}// end of the 2 ifs to see if review_form_elements exist
			
			
			//////// end of insert the review form elements ////////////////////////
			
		}//end of the if reviewFormOk
	}//end of the foreach reviewForm	
	unset($revForm);
	
	$returnData = array('errors' => $errors, 'numInsertedRecords' => $numInsertions, 'numUpdatedRecords' => $numUpdates);
	/*$returnData['errors'] = $errors;
	$returnData['numInsertedRecords'] = array('reviewForms' => $numInsertedReviewForms);*/
	
	return $returnData;
	
}

/////////////////////  end of insertReviewForms  ////////////////////////////////////


// #11)
/**
insert the articles history, which is comprised of event_logs and email_logs
*/
function insertArticlesHistory(&$xml, $conn, &$dataMapping, $journalNewId, $args = null) {
	
	//exit("\n\nNOT FUNCTIONAL\n\n");
	
	
	$limit = 10;
	
	if (is_array($args)) {
		if (array_key_exists('limit', $args)) {
			$limit = $args['limit'];
		}
	}
	
	if (!array_key_exists('event_log_id', $dataMapping)) {
		$dataMapping['event_log_id'] = array();
	}
	
	if (!array_key_exists('email_log_id', $dataMapping)) {
		$dataMapping['email_log_id'] = array();
	}
	
	
	///////  THE STATEMENTS  ////////////////////////////////////////////////////////////
	
	// user statements //////////////////////
	
	$userSTMT = $conn->prepare('SELECT * FROM users WHERE email = :user_email');
	
	$checkUsernameSTMT = $conn->prepare('SELECT COUNT(*) as count FROM users WHERE username = :checkUsername');
	
	$insertUserSTMT = $conn->prepare('INSERT INTO users (username, password, salutation, first_name, middle_name, last_name, gender, initials, email, url, phone, fax, mailing_address,
country, locales, date_last_email, date_registered, date_validated, date_last_login, must_change_password, auth_id, disabled, disabled_reason, auth_str, suffix, billing_address, 
inline_help) VALUES (:insertUser_username, :insertUser_password, :insertUser_salutation, :insertUser_firstName, :insertUser_middleName, :insertUser_lastName, :insertUser_gender, 
:insertUser_initials, :insertUser_email, :insertUser_url, :insertUser_phone, :insertUser_fax, :insertUser_mailingAddress, :insertUser_country, :insertUser_locales, 
:insertUser_dateLastEmail, :insertUser_dateRegistered, :insertUser_dateValidated, :insertUser_dateLastLogin, :insertUser_mustChangePassword, :insertUser_authId, :insertUser_disabled, 
:insertUser_disabledReason, :insertUser_authStr, :insertUser_suffix, :insertUser_billingAddress, :insertUser_inlineHelp)');
	
	$lastUsersSTMT = $conn->prepare("SELECT * FROM users ORDER BY user_id DESC LIMIT $limit");
	
	$userStatements = array('userSTMT' => &$userSTMT, 'checkUsernameSTMT' => &$checkUsernameSTMT, 'insertUserSTMT' => &$insertUserSTMT, 'lastUsersSTMT' => &$lastUsersSTMT);
	
	/////////////////////////////////////////
	
	// event_log ///////////
	$insertEventLogSTMT = $conn->prepare(
		'INSERT INTO event_log (assoc_type, assoc_id, user_id, date_logged, ip_address, event_type, message, is_translated) 
		 VALUES (:eventLog_assocType, :eventLog_assocId, :userId, :dateLogged, :eventLog_ipAddress, :eventLog_eventType, :message, :isTranslated)'
	);
	
	$lastEventLogsSTMT = $conn->prepare("SELECT * FROM event_log ORDER BY log_id DESC LIMIT $limit");
	
	$insertEventLogSettingsSTMT = $conn->prepare(
		'INSERT INTO event_log_settings (log_id, setting_name, setting_value, setting_type) 
		 VALUES (:eventLogId, :settingName, :settingValue, :settingType)'
	);
	//////////////////////////

	// email_logs ///////////
	$insertEmailLogSTMT = $conn->prepare(
		'INSERT INTO email_log (assoc_type, assoc_id, sender_id, date_sent, ip_address, event_type, from_address, 
		 recipients, cc_recipients, bcc_recipients, subject, body)
		 VALUES (:emailLog_assocType, :emailLog_assocId, :senderId, :dateSent, :emailLog_ipAddress, :eventLog_eventType, :fromAddress,
		 :recipients, :ccRecipients, :bccRecipients, :subject, :body)'
	);
	
	$lastEmailLogsSTMT = $conn->prepare("SELECT * FROM email_log ORDER BY log_id DESC LIMIT $limit");

	
	$insertEmailLogUsersSTMT = $conn->prepare(
		'INSERT INTO email_log_users (email_log_id, user_id) 
		 VALUES (:emailLogId, :emailLogUsers_userId)'
	);
	
	//////////////////////////////////////////////////////////////////////////////////////
	
	$email_logs_node = $xml->getElementsByTagName('email_logs')->item(0);
	$event_logs_node = $xml->getElementsByTagName('event_logs')->item(0);
	
	$errors = array(
		'event_log' => array('insert' => array(), 'update' => array()),
		'event_log_settings' => array('insert' => array(), 'update' => array()),
		'email_log' => array('insert' => array(), 'update' => array()),
		'email_log_users' => array('insert' => array(), 'update' => array() )
	);
	
	$insertedUsers = array();
	
	//////// INSERTING THE EMAIL LOGS  /////////////////////////////////////
	
	$emailLogs = xmlToArray($email_logs_node, true); //from helperFunctions.php
	
	$numInsertedEmailLogs = 0;
	
	foreach ($emailLogs as &$emailLog) {
		if (array_key_exists($emailLog['log_id'], $dataMapping['email_log_id'])) {
			echo "\nemail_log #" . $emailLog['log_id'] . " was already imported.\n";
			continue; // go to the next review_form
		}
		
		//echo "\n\nThe sender is:\n" . print_r($emailLog['sender'], true) . "\n\n"; 
		
		$emailLogOk = false;
		$assocIdOk = false;
		$senderIdOk = false;
		
		$error = array();
		
		/////////////// checking data integrity ///////////////////////////////////////////
		if (array_key_exists($emailLog['assoc_id'], $dataMapping['article_id'])) {
			$emailLog['assoc_new_id'] = $dataMapping['article_id'][$emailLog['assoc_id']];
			$assocIdOk = true;
		}
		else {
			$error['assocIdError'] = 'The email log assoc_id #' . $emailLog['assoc_id'] . ' , which is an article_id, is not in the dataMapping.'; 
		}
		
		//$sender = null;
		
		if (array_key_exists($emailLog['sender_id'], $dataMapping['user_id'])) {
			$emailLog['sender_new_id'] = $dataMapping['user_id'][$emailLog['sender_id']];
			$senderIdOk = true;
		}
		else if (is_array($emailLog['sender'])){
			
			//echo "Passing the sender: \n" . print_r($emailLog['sender'], true) . "\n to processUser\n\n";
			
			$senderIdOk = processUser($emailLog['sender'], array('type' => 'email_log', 'data' => $emailLog), $dataMapping, 
				$errors, $insertedUsers, $userStatements, $conn);
			
			$emailLog['sender_new_id'] = $emailLog['sender']['user_new_id'];
		}
		/////////////////////////////////////////////////////////////////////////////////////
		
		if ($assocIdOk && $senderIdOk) { //if data integrity is ok
			
			$emailLog['body'] = $emailLog['email_body'];
			unset($emailLog['email_body']);
			
			validateData('email_log', $emailLog); //from helperFunctions.php
			
			/*echo "\n\nThe email_log is: \n";
			var_dump($emailLog);
			echo "\n\n";*/
			
			$arr = array();
			$arr['data'] = $emailLog;
			$arr['params'] = array(
				array('name' => ':emailLog_assocType', 'attr' => 'assoc_type', 'type' => PDO::PARAM_INT),
				array('name' => ':emailLog_assocId', 'attr' => 'assoc_new_id', 'type' => PDO::PARAM_INT),
				array('name' => ':senderId', 'attr' => 'sender_new_id', 'type' => PDO::PARAM_INT),
				array('name' => ':dateSent', 'attr' => 'date_sent', 'type' => PDO::PARAM_STR),
				array('name' => ':emailLog_ipAddress', 'attr' => 'ip_address', 'type' => PDO::PARAM_STR),
				array('name' => ':eventLog_eventType', 'attr' => 'event_type', 'type' => PDO::PARAM_INT),
				array('name' => ':fromAddress', 'attr' => 'from_address', 'type' => PDO::PARAM_STR),
				array('name' => ':recipients', 'attr' => 'recipients', 'type' => PDO::PARAM_STR	),
				array('name' => ':ccRecipients', 'attr' => 'cc_recipients', 'type' => PDO::PARAM_STR),
				array('name' => ':bccRecipients', 'attr' => 'bcc_recipients', 'type' => PDO::PARAM_STR),
				array('name' => ':subject', 'attr' => 'subject', 'type' => PDO::PARAM_STR),
				array('name' => ':body', 'attr' => 'body', 'type' => PDO::PARAM_STR)
			);
			
			echo '    inserting email_log #' . $emailLog['log_id']. '............ ';
			
			if (myExecute('insert', 'email_log', $arr, $insertEmailLogSTMT, $errors)) { //from helperFunctions.php
				echo "Ok\n";
				if (getNewId('email_log', $lastEmailLogsSTMT, $emailLog, $dataMapping, $errors)) { //from helperFunctions.php
					echo "    new id = " . $emailLog['log_new_id'] . "\n\n";
					$emailLogOk = true;
					$numInsertedEmailLogs++;
				}
			}
			else {
				echo "Failed\n";
			}
		}// end of the if assocIdOk and senderIdOk
		else { // the data integrity is not ok
			if (!$senderIdOk) {
				$error['senderIdError'] = 'The sender_id #' . $emailLog['sender_id'] . ' , which is a user_id, is not in the dataMappings.';
			}
			
			$error['email_log_id'] = $emailLog['log_id'];
			
			array_push($errors['email_log']['insert'], $error);
		}
		
		
		if ($emailLogOk) {
			// insert the email_log_users
			if (array_key_exists('email_log_users', $emailLog)) { if (!empty($emailLog['email_log_users'])) {
				
			echo "\nInserting email_log_users for the email_log_id #" . $emailLog['log_id'] . " :\n";
				
			foreach ($emailLog['email_log_users'] as &$emailLogUser) {
				
				$emailLogIdOk = false;
				$userIdOk = false;
				$error = array();
				
				//////////////////// checking data integrity //////////////////////////////////////
				if (array_key_exists($emailLogUser['email_log_id'], $dataMapping['email_log_id'])) {
					$emailLogUser['email_log_new_id'] = $dataMapping['email_log_id'][$emailLogUser['email_log_id']];
					$emailLogIdOk = true;
				}
				else {
					$error['emailLogIdError'] = 'The email_log_id #' . $emailLogUser['email_log_id'] . ' is not in the dataMapping.';
				}
				
				if (array_key_exists($emailLogUser['user_id'], $dataMapping['user_id'])) {
					$emailLogUser['user_new_id'] = $dataMapping['user_id'][$emailLogUser['user_id']];
					$userIdOk = true;
				}
				else if (is_array($emailLogUsers['user'])){
					$userIdOk = processUser($emailLogUser['user'], array('type' => 'email_log_users', 'data' => $emailLogUser), $dataMapping, 
						$errors, $insertedUsers, $userStatements, $conn);
				}
				/////////////////////////////////////////////////////////////////////////////////////
				
				if ($userIdOk && $emailLogIdOk) { 
					
					echo '        inserting email_log_user with user_id #' . $emailLogUser['user_id'] . ' .........';
					
					$arr = array();
					$arr['data'] = $emailLogUser;
					$arr['params'] = array(
						array('name' => ':emailLogId', 'attr' => 'email_log_new_id', 'type' => PDO::PARAM_STR),
						array('name' => ':emailLogUsers_userId', 'attr' => 'user_new_id', 'type' => PDO::PARAM_STR)
					);
					
					if (myExecute('insert', 'email_log_users', $arr, $insertEmailLogUsersSTMT, $errors)) { //from helperFunctions.php
						echo "Ok\n";
					}
					else {
						echo "Failed\n";
					}
					
				}
				else {
					$error['email_log_user'] = $emailLogUser;
					array_push($errors['email_log_users']['insert'], $error);
				}
				
			}//end of the foreach email_log_user
			unset($emailLogUser);
			}//end of the if email_log_users is not empty
			}//end of the if email_log_users exists
		}// end of the if emailLogOk
		
	}// end of the foreach emailLog
	unset($emailLog);
	
	//////// END OF INSERTING THE EMAIL LOGS ///////////////////////////////

	
	
	////////  INSERTING THE EVENT LOGS  ////////////////////////////////////
	
	$eventLogs = xmlToArray($event_logs_node, true); //from helperFunctions.php
	$numInsertedEventLogs = 0;
	
	echo "\n\nInserting the event logs:\n";
	
	foreach ($eventLogs as &$eventLog) {
		if (array_key_exists($eventLog['log_id'], $dataMapping['event_log_id'])) {
			echo "\nevent_log #" . $eventLog['log_id'] . " was already imported.\n";
			continue; // go to the next review_form
		}
		
		$eventLogOk = false;
		$assocIdOk = false;
		$userIdOk = false;
		
		$error = array();
		
		//////////////////// checking data integrity /////////////////////////////////////////
		if (array_key_exists($eventLog['assoc_id'], $dataMapping['article_id'])) {
			$eventLog['assoc_new_id'] = $dataMapping['article_id'][$eventLog['assoc_id']];
			$assocIdOk = true;
		}
		else {
			$error['assocIdError'] = 'The event log assoc_id #' . $eventLog['assoc_id'] . ' , which is an article_id, is not in the dataMapping.'; 
		}
		
		if (array_key_exists($eventLog['user_id'], $dataMapping['user_id'])) {
			$eventLog['user_new_id'] = $dataMapping['user_id'][$eventLog['user_id']];
			$userIdOk = true;
		}
		else if (is_array($eventLog['user'])){
			$userIdOk = processUser($eventLog['user'], array('type' => 'event_log', 'data' => $eventLog), $dataMapping, 
				$errors, $insertedUsers, $userStatements, $conn);
			
			$eventLog['user_new_id'] = $eventLog['user']['user_new_id'];
		}
		////////////////////////////////////////////////////////////////////////////////////////
		
		if ($assocIdOk && $userIdOk) { //if the data integrity is ok
			validateData('event_log', $eventLog); //from helperFunctions.php
			
			$arr = array();
			$arr['data'] = $eventLog;
			$arr['params'] = array(
				array('name' => ':eventLog_assocType', 'attr' => 'assoc_type', 'type' => PDO::PARAM_INT),
				array('name' => ':eventLog_assocId', 'attr' => 'assoc_new_id', 'type' => PDO::PARAM_INT),
				array('name' => ':userId', 'attr' => 'user_new_id', 'type' => PDO::PARAM_INT),
				array('name' => ':dateLogged', 'attr' => 'date_logged', 'type' => PDO::PARAM_STR),
				array('name' => ':eventLog_ipAddress', 'attr' => 'ip_address', 'type' => PDO::PARAM_STR),
				array('name' => ':eventLog_eventType', 'attr' => 'event_type', 'type' => PDO::PARAM_INT),
				array('name' => ':message', 'attr' => 'message', 'type' => PDO::PARAM_STR),
				array('name' => ':isTranslated', 'attr' => 'is_translated', 'type' => PDO::PARAM_INT)
			);
			
			echo '    inserting event_log #' . $eventLog['log_id']. '............ ';
			
			if (myExecute('insert', 'event_log', $arr, $insertEventLogSTMT, $errors)) { //from helperFunctions.php
				echo "Ok\n";
				if (getNewId('event_log', $lastEventLogsSTMT, $eventLog, $dataMapping, $errors)) { //from helperFunctions.php
					echo "    new id = " . $eventLog['log_new_id'] . "\n\n";
					$eventLogOk = true;
					$numInsertedEventLogs++;
				}
			}
			else {
				echo "Failed\n";
			}
		}//end of the if assocIdOk and userIdOk
		else { //the data had no integrity
			if (!$userIdOk) {
				$error['userIdError'] = array(
					'user' => $eventLog['user'], 
					'error' => 'The user_id #' . $eventLog['user_id'] . ' is not in the dataMappings.'
				);
			}
			
			$error['event_log_id'] = $eventLog['log_id'];
			
			array_push($errors['event_log']['insert'], $error);
		}
		
		if ($eventLogOk) {
			if (array_key_exists('settings', $eventLog)) { if (!empty($eventLog['settings'])) {
			foreach ($eventLog['settings'] as &$setting) {
				
				/*
				$insertEventLogSettingsSTMT = $conn->prepare(
		'INSERT INTO review_form_settings (log_id, setting_name, setting_value, setting_type) 
		 VALUES (:eventLogId, :settingName, :settingValue, :settingType)'
	);
				*/
				
				validateData('event_log_settings', $setting); 
						
				$setting['log_new_id'] = $eventLog['log_new_id'];
				echo '        inserting '. $setting['setting_name']  . ' .........';
				
				$arr = array();
				$arr['data'] = $setting;
				$arr['params'] = array(
					array('name' => ':eventLogId', 'attr' => 'log_new_id', 'type' => PDO::PARAM_INT),
					array('name' => ':settingName', 'attr' => 'setting_name', 'type' => PDO::PARAM_STR),
					array('name' => ':settingValue', 'attr' => 'setting_value', 'type' => PDO::PARAM_STR),
					array('name' => ':settingType', 'attr' => 'setting_type', 'type' => PDO::PARAM_STR)
				);
				
				if (myExecute('insert', 'event_log_settings', $arr, $insertEventLogSettingsSTMT, $errors)) { //from helperFunctions.php
					echo "Ok\n";
				}
				else {
					echo "Failed\n";
				}
				
			}//end of the foreach setting
			unset($setting);	
				
			}//end of the if eventLog settings is not empty
			}//end of the if settings exist
		}// end of the if eventLogOk
		
	}//end of the foreach eventLog
	unset($eventLog);
	
	///////  END OF INSERTING THE EVENT LOGS  //////////////////////////////
	
	$returnData = array();
	$returnData['errors'] = $errors;
	$returnData['insertedUsers'] = $insertedUsers;
	$returnData['numInsertedRecords'] = array('event_logs' => $numInsertedEventLogs, 'email_logs' => $numInsertedEmailLogs);
	
	return $returnData;
	
}
//end of insertArticleHistory


// #12) 
function insertPluginSettings(&$xml, $conn, &$dataMapping, $journalNewId, $args = null) {
	//echo "\n\nTHE FUNCTION insertPluginSettings DOES NOT DO ANYTHING YET\n\n";
	$limit = 10;
	
	if (is_array($args)) {
		if (array_key_exists('limit', $args)) {
			$limit = $args['limit'];
		}
	}
	
	///////  THE STATEMENTS  ////////////////////////////////////////////////////////////
	$checkPluginSTMT = $conn->prepare(
		'SELECT * FROM plugin_settings WHERE 
		 plugin_name = :check_pluginName AND locale = :check_locale AND
		 journal_id = :check_journalId AND setting_name = :check_settingName'
	);
	
	$insertPluginSettingsSTMT = $conn->prepare('INSERT INTO plugin_settings (plugin_name, locale, journal_id, setting_name, setting_value, setting_type)
		VALUES (:insert_pluginName, :insert_locale, :insert_journalId, :insert_settingName, :insert_settingValue, :insert_settingType)');
	
	$updatePluginSettingsSTMT = $conn->prepare(
		'UPDATE plugin_settings 
		 SET setting_value = :update_settingValue, setting_type = :update_settingType 
		 WHERE plugin_name = :update_pluginName AND locale = :update_locale AND
		 journal_id = :update_journalId AND setting_name = :update_settingName'
	);
	
	$checkPluginSTMT->bindParam(':check_journalId', $journalNewId, PDO::PARAM_INT);
	$insertPluginSettingsSTMT->bindParam(':insert_journalId', $journalNewId, PDO::PARAM_INT);
	$updatePluginSettingsSTMT->bindParam(':update_journalId', $journalNewId, PDO::PARAM_INT);
	
	/////////////////////////////////////////////////////////////////////////////////////
	
	$errors = array(
		'plugin_settings' => array('insert' => array(), 'update' => array(), 'check' => array())
	);
	
	$plugin_settings_node = $xml->getElementsByTagName('plugin_settings')->item(0);
	
	$pluginSettings = xmlToArray($plugin_settings_node, true); //from helperFunctions.php
	
	$numUpdates = 0;
	$numInsertions = 0;
	
	foreach ($pluginSettings as &$plugin) {
		
		$plugin['journal_new_id'] = $journalNewId;
		
		validateData('plugin_settings', $plugin); //from helperFunctions.php
		
		$pluginSettingExists = false;
		/////////// check if the plugin setting exists //////////////////////////
		$checkPluginSTMT->bindParam(':check_pluginName', $plugin['plugin_name'], PDO::PARAM_STR);
		$checkPluginSTMT->bindParam(':check_locale', $plugin['locale'], PDO::PARAM_STR);
		$checkPluginSTMT->bindParam(':check_settingName', $plugin['setting_name'], PDO::PARAM_STR);
		
		echo "\nChecking if the plugin '" . $plugin['plugin_name'] . 
		"' setting '" . $plugin['setting_name'] .
		"' with locale '" . $plugin['locale'] .  "' exists: ";
		
		if ($checkPluginSTMT->execute()) {
			if ($checkedPlugin = $checkPluginSTMT->fetch(PDO::FETCH_ASSOC)) {
				$pluginSettingExists = true;
				echo "Yes\n";
				/////////////// update the plugin setting if necessary //////////////////////
				
				if ($checkedPlugin['setting_value'] != $plugin['setting_value']) {
					//update the plugin setting value
					$arr = array();
					$arr['data'] = $plugin;
					$arr['params'] = array(
						array('name' => ':update_settingValue', 'attr' => 'setting_value', 'type' => PDO::PARAM_STR),
						array('name' => ':update_settingType', 'attr' => 'setting_type', 'type' => PDO::PARAM_STR),
						array('name' => ':update_pluginName', 'attr' => 'plugin_name', 'type' => PDO::PARAM_STR),
						array('name' => ':update_locale', 'attr' => 'locale', 'type' => PDO::PARAM_STR),
						array('name' => ':update_settingName', 'attr' => 'setting_name', 'type' => PDO::PARAM_STR)
					);
					
					echo '    updating plugin setting ............ ';
					
					if (myExecute('update', 'plugin_settings', $arr, $updatePluginSettingsSTMT, $errors)) { //from helperFunctions.php
						echo "Ok\n\n";
						$numUpdates++;
					}
					else {
						echo "Failed\n\n";
					}
				}
				else {
					echo "    this plugin setting is already up to date\n\n";
				}
				
				/////////////////////////////////////////////////////////////////////////////
				
			}// end of the if to fetch the plugin setting
			else {
				echo "No\n"; // the plugin setting does not exist yet
			}
		}//end of the if checkPluginSTMT executed
		else {
			$error = array('plugin_setting' => $plugin, 'error' => $checkPluginSTMT->errorInfo());
			array_push($errors['plugin_settings']['check'], $error);
			echo "Error\n";
		}
		
		///////////// end of the check if the plugin exists ///////////////////////////
		
		if (!$pluginSettingExists) {
			/////////////// insert the plugin setting //////////////////////////
			$arr = array();
			$arr['data'] = $plugin;
			$arr['params'] = array(
				array('name' => ':insert_pluginName', 'attr' => 'plugin_name', 'type' => PDO::PARAM_STR),
				array('name' => ':insert_locale', 'attr' => 'locale', 'type' => PDO::PARAM_STR),
				array('name' => ':insert_settingName', 'attr' => 'setting_name', 'type' => PDO::PARAM_STR),
				array('name' => ':insert_settingValue', 'attr' => 'setting_value', 'type' => PDO::PARAM_STR),
				array('name' => ':insert_settingType', 'attr' => 'setting_type', 'type' => PDO::PARAM_STR),
			);
			
			echo '    inserting the plugin setting ............ ';
			
			if (myExecute('insert', 'plugin_settings', $arr, $insertPluginSettingsSTMT, $errors)) { //from helperFunctions.php
				echo "Ok\n\n";
				$numInsertions++;
			}
			else {
				echo "Failed\n\n";
			}
			///////////// end of insert the plugin setting /////////////////////
		}
		
	}//end of the foreach pluginSettings
	unset($plugin);
	
	$returnData = array();
	$returnData['errors'] = $errors;
	$returnData['numInsertedRecords'] = array('plugin_settings' => $numInsertions);
	$returnData['numUpdatedRecords'] = array('plugin_settings' => $numUpdates);
	
	return $returnData;
	
}
//end of insertPluginSettings


// #13)
function insertIssueOrders(&$xml, $conn, &$dataMapping, $journal, $args = null) {
	
	$limit = 10;
	
	if (is_array($args)) {
		if (array_key_exists('limit', $args)) {
			$limit = $args['limit'];
		}
	}
	
	///////  THE STATEMENTS  ////////////////////////////////////////////////////////////
	////// custom_issue_orders /////////////////////////////////////////
	$checkIssueOrderSTMT = $conn->prepare(
		'SELECT * FROM custom_issue_orders 
		 WHERE issue_id = :checkIssue_issueId'
	);
	
	$insertCustomIssueOrderSTMT = $conn->prepare(
		'INSERT INTO custom_issue_orders (issue_id, journal_id, seq) VALUES (:insertIssue_issueId, :insertIssue_journalId, :insertIssue_seq)'
	);
	
	$updateCustomIssueOrderSTMT = $conn->prepare(
		'UPDATE custom_issue_orders 
		 SET seq = :updateIssue_seq 
		 WHERE issue_id = :updateIssue_issueId'
	);
	
	$insertCustomIssueOrderSTMT->bindParam(':insertIssue_journalId', $journalNewId, PDO::PARAM_INT);
	
	/////////// custom_section_orders //////////////////////////////////////////
	$checkSectionOrderSTMT = $conn->prepare(
		'SELECT * FROM custom_section_orders 
		 WHERE issue_id = :checkSection_issueId AND section_id = :checkSection_sectionId'
	);
	
	$insertCustomSectionOrderSTMT = $conn->prepare(
		'INSERT INTO custom_section_orders (issue_id, section_id, seq) VALUES (:insertSection_issueId, :insertSection_sectionId, :insertSection_seq)'
	);
	
	$updateCustomSectionOrderSTMT = $conn->prepare(
		'UPDATE custom_section_orders
		 SET seq = :updateSection_seq
		 WHERE issue_id = :updateSection_issueId AND section_id = :updateSection_sectionId'
	);
	
	////////////////////////////////////////////////////////////////////////////
	
	$errors = array(
		'custom_issue_order' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'custom_section_order' => array('insert' => array(), 'update' => array(), 'check' => array()),
		'mapping' => array('check' => array())
	);
	
	$issue_orders_node = $xml->getElementsByTagName('issue_orders')->item(0);
	
	$issueOrders = xmlToArray($issue_orders_node, true); //from helperFunctions.php
	
	///////// first of all the issues must be mapped ////////////////////////////
	$mappedIssues = 0;
	$issuesTotal = count($issueOrders);
	
	$returnedData = mapIssueIds($conn, $dataMapping, $issueOrders, $journal); //from helperFunctions function #29
	if (is_array($returnedData)) {
		$mappedIssues = $returnedData['numbers']['newlyMapped'] + $returnedData['numbers']['alreadyMapped'];
	}
	
	if ($mappedIssues > 0) {
		if ($mappedIssues < $issuesTotal) {
			$error = array(
				'error' => "From the $issuesTotal issues only $mappedIssues were mapped", 
				'mappingNumbers' => $returnedData['numbers'], 
				'mappingErrors' => $returnedData['errors']
			);
			array_push($errors['mapping']['check'], $error);
		}
	}
	else {
		$error = array('error' => 'None of the issues were mapped', 'mappingErrors' => $returnedData['errors']);
		array_push($errors['mapping']['check'], $error);
	}
	/////////////////////////////////////////////////////////////////////////////
	
	$numIssueOrderUpdates = 0;
	$numIssueOrderInsertions = 0;
	$numSectionOrderUpdates = 0;
	$numSectionOrderInsertions = 0;
	
	$journalNewId = $journal['journal_id'];
	
	foreach ($issueOrders as &$issue) {
		
		//var_dump($issue); exit();
		
		$issue['journal_new_id'] = $journalNewId;
		
		validateData('issue', $issue); //from helperFunctions.php
		
		$issueIdOk = false;
		
		//////////////////// checking data integrity /////////////////////////////////////////
		if (array_key_exists($issue['issue_id'], $dataMapping['issue_id'])) {
			$issue['issue_new_id'] = $dataMapping['issue_id'][$issue['issue_id']];
			$issueIdOk = true;
		}
		////////////////////////////////////////////////////////////////////////////////////////
		
		if ($issueIdOk) {
			
			///////////////////////  insert the custom_issue_orders  ////////////////////////////////////////////////
			$insertCustomIssueOrder = true;
			
			$checkIssueOrderSTMT->bindParam(':checkIssue_issueId', $issue['issue_new_id'], PDO::PARAM_INT);
		
			echo "\nChecking if the issue #'" . $issue['issue_new_id'] . " is ordered: ";
			
			if ($checkIssueOrderSTMT->execute()) {
				if ($checkedIssueOrder = $checkIssueOrderSTMT->fetch(PDO::FETCH_ASSOC)) {
					$insertCustomIssueOrder = false;
					echo "Yes\n\n";
					
				}// end of the if to fetch the custom_issue_order
				else {
					echo "No\n"; // the plugin setting does not exist yet
				}
			}//end of the if checkIssueOrderSTMT executed
			else {
				$error = array('issue' => $issue, 'error' => $checkIssueOrderSTMT->errorInfo());
				array_push($errors['custom_issue_order']['check'], $error);
				echo "Error\n\n";
			}
			
			if ($insertCustomIssueOrder) {
			
			if (array_key_exists('custom_issue_orders', $issue)) { if (!empty($issue['custom_issue_orders'])) {
			foreach ($issue['custom_issue_orders'] as &$customIssueOrder) {
				
				$customIssueOrder['issue_new_id'] = $issue['issue_new_id'];
				//$customIssueOrder['journal_new_id'] = $journalNewId;
				
				$arr = array();
				$arr['data'] = $customIssueOrder;
				$arr['params'] = array(
					array('name' => ':insertIssue_issueId', 'attr' => 'issue_new_id', 'type' => PDO::PARAM_INT),
					// do not need to set the journal_id as it has already been set after the statement declaration
					// and will be the same for every statement execution 
					array('name' => ':insertIssue_seq', 'attr' => 'seq') // is type double
				);
				
				echo '    inserting the custom issue order ............ ';
				
				if (myExecute('insert', 'custom_issue_order', $arr, $insertCustomIssueOrderSTMT, $errors)) { //from helperFunctions.php
					echo "Ok\n\n";
					$numIssueOrderInsertions++;
				}
				else {
					echo "Failed\n\n";
				}
				
			}//end of the foreach custom_issue_order
			unset($customIssueOrder);
			}//end of the if custom_issue_orders not empty
			}//end of the if custom_issue_orders exist
			
			}//end of the if insertCustomIssueOrder
			
			///////////////////////  end of insert the custom_issue_orders  //////////////////////////////////////////
			
			
			/////////////////////  insert the custom_section_orders  /////////////////////////////////////////////////
			
			if (array_key_exists('custom_section_orders', $issue)) { if (!empty($issue['custom_section_orders'])) {
			foreach ($issue['custom_section_orders'] as &$customSectionOrder) {
				
				$customSectionOrder['issue_new_id'] = $issue['issue_new_id'];
	
				$sectionIdOk = false;
				
				//////////////////// checking data integrity /////////////////////////////////////////
				if (array_key_exists($customSectionOrder['section_id'], $dataMapping['section_id'])) {
					$customSectionOrder['section_new_id'] = $dataMapping['section_id'][$customSectionOrder['section_id']];
					$sectionIdOk = true;
				}
				//////////////////////////////////////////////////////////////////////////////////////
				
				if ($sectionIdOk) {
					//////////////  check if the section is already ordered in this issue //////////////////
					$insertCustomSectionOrder = true;
					
					$checkSectionOrderSTMT->bindParam(':checkSection_sectionId', $customSectionOrder['section_new_id'], PDO::PARAM_INT);
					$checkSectionOrderSTMT->bindParam(':checkSection_issueId', $customSectionOrder['issue_new_id'], PDO::PARAM_INT);
					
					echo 'Checking if the issue #' . $issue['issue_new_id'] . ' section #' . $customSectionOrder['section_new_id'] . ' is already ordered: ';
					
					if ($checkSectionOrderSTMT->execute()) {
						if ($checkedSectionOrder = $checkSectionOrderSTMT->fetch(PDO::FETCH_ASSOC)) {
							echo "Yes\n\n";
							$insertCustomSectionOrder = false;
						}
						else {
							echo "No\n";
						}
					}
					else {
						$error = array('custom_section_order' => $customsectionOrder, 'error' => $checkSectionOrderSTMT->errorInfo());
						array_push($errors['custom_section_order']['check'], $error);
						echo "Error\n\n";
					}
					////////////////////////////////////////////////////////////////////////////////////////
					
					if ($insertCustomSectionOrder) {
					///////// insert the custom_section_order //////////////////////////////////////////////
					
						$arr = array();
						$arr['data'] = $customSectionOrder;
						$arr['params'] = array(
							array('name' => ':insertSection_issueId', 'attr' => 'issue_new_id', 'type' => PDO::PARAM_INT),
							array('name' => ':insertSection_sectionId', 'attr' => 'section_new_id', 'type' => PDO::PARAM_INT),
							array('name' => ':insertSection_seq', 'attr' => 'seq') // is type double
						);
						
						echo '    inserting the custom section order ............ ';
						
						if (myExecute('insert', 'custom_section_order', $arr, $insertCustomSectionOrderSTMT, $errors)) { //from helperFunctions.php
							echo "Ok\n\n";
							$numSectionOrderInsertions++;
						}
						else {
							echo "Failed\n\n";
						}
					
					////////////////////////////////////////////////////////////////////////////////////////
					}//end of the if insertCustomSectionOrder
					
				}//end of the if sectionIdOk
				else {
					$error = array('custom_section_order' => $customsectionOrder, 'error' => 'The section id is not in the dataMappings');
					array_push($errors['custom_section_order']['insert'], $error);
				}
				
			}//end of the foreach custom_section_order
			unset($customSectionOrder);	
			}//end of the if not empty custom_section_orders
			}//end of the custom_section_orders exist 
			
			
			//////////////////  end of the insert custom_section_orders  /////////////////////////////////////////////
			
		}//end of the if issueIdOk
		else {
			$error = array('issue_id' => $issue['issue_id'], 'error' => 'The issue_id #' . $issue['issue_id'] . ' is not in the dataMappings');
			array_push($errors['custom_issue_order']['check'], $error);
			echo "Error\n\n";
		}
		
	}//end of the foreach issueOrders
	unset($issue);
	
	$returnData = array();
	$returnData['errors'] = $errors;
	$returnData['numInsertedRecords'] = array('custom_issue_orders' => $numIssueOrderInsertions, 'custom_section_orders' => $numSectionOrderInsertions);
	$returnData['numUpdatedRecords'] = array('custom_issue_orders' => $numIssueOrderUpdates, 'custom_section_orders' => $numSectionOrderUpdates);
	
	return $returnData;
}
//end of insertIssueOrders


// #14)
function insertCitationsAndReferrals(&$xml, $conn, &$dataMapping, $journalNewId, $args = null) {
	//echo "\n\nTHE FUNCTION insertCitationsAndReferrals DOES NOT DO ANYTHING YET\n\n";
	$limit = 10;
	
	if (is_array($args)) {
		if (array_key_exists('limit', $args)) {
			$limit = $args['limit'];
		}
	}
	
	if (!array_key_exists('event_log_id', $dataMapping)) {
		$dataMapping['event_log_id'] = array();
	}
	
	if (!array_key_exists('email_log_id', $dataMapping)) {
		$dataMapping['email_log_id'] = array();
	}
	
	
	///////  THE STATEMENTS  ////////////////////////////////////////////////////////////
}
// end of insertCitationsAndReferrals

//#15
function updateUserRegistrationDates(&$xml, $conn, &$dataMapping) {
	
	
	if (!array_key_exists('user_id', $dataMapping)) {
		$dataMapping['user_id'] = array();
	}
	
	$user_registration_dates_node = null;
	
	if (strpos($xml->nodeName, 'articles') !== false) {
		$user_registration_dates_node = $xml;
	}
	else {
		$user_registration_dates_node = $xml->getElementsByTagName('user_registration_dates')->item(0);
	}
	
	$registrations = xmlToArray($user_registration_dates_node, true); //from helperFunctions.php
	
	$selectUserByEmailSTMT = $conn->prepare('SELECT user_id, email FROM users WHERE email = :email');
	
	$updateUserRegistrationDateSTMT = $conn->prepare(
		'UPDATE users 
		 SET date_registered = :updateUserRegistrationDate_dateRegistered 
		 WHERE user_id = :updateUserRegistrationDate_userId'
	);
	
	$numUpdates = 0;
	$errors = array(
		'users' => array('updates' => array(), 'check' => array())
	);
	
	echo "\n\nUpdating the users registrations dates: \n";
	
	foreach ($registrations as $reg) {
		
		//fetch the user_id
		$selectUserByEmailSTMT->bindParam(':email', $reg['email'], PDO::PARAM_STR);
		
		$fetchedUser = null;
		
		if ($selectUserByEmailSTMT->execute()) {
			if ($fetchedUser = $selectUserByEmailSTMT->fetch(PDO::FETCH_ASSOC)) {
				
				
				///// map the user_id //////
				if (!array_key_exists($reg['user_id'], $dataMapping)) {
					$dataMapping['user_id'][$reg['user_id']] = $fetchedUser['user_id'];
				}
				////////////////////////////
				
				$updateUserRegistrationDateSTMT->bindParam(':updateUserRegistrationDate_userId', $fetchedUser['user_id'], PDO::PARAM_INT);
			}
			else {
				continue; // go to the user
			}
		}
		else { //the selectUserByEmailSTMT did not execute
			$error = array(
				'registration' => $reg, 
				'error' => 'the statement to select user by email did not execute', 
				'errorInfo' => $selectUserByEmailSTMT->errorInfo()
			);
			array_push($errors['users']['check'], $error);
			continue; // go to the next user
		}
		
		$updateUserRegistrationDateSTMT->bindParam(':updateUserRegistrationDate_dateRegistered', $reg['date_registered'], PDO::PARAM_STR);
		
		
		if ($updateUserRegistrationDateSTMT->execute()) {
			if ($updateUserRegistrationDateSTMT->rowCount() == 1) {
				$numUpdates++;
			}
		}
		else {
			$error = array(
				'registration' => $reg, 
				'error' => 'the statement to update user registration did not execute', 
				'errorInfo' => $updateUserRegistrationDateSTMT->errorInfo()
			);
			array_push($errors['users']['update'], $error);
		}
	}
	
	return array('errors' => $errors, 'numUpdatedRecords' => $numUpdates);
	
}
