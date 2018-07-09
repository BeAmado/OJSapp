<?php 

function fetchUserSettings(&$returnData, &$stmts, &$queries, &$collations, $userId, &$errors) {

	//fetching the user settings
	//$userSettingsSTMT->bindParam(':userSettings_userId', $user['user_id'], PDO::PARAM_INT);
//function bindStmtParam(&$stmts, &$queries, $statementName, $fieldName, $fieldValue, &$msg = null) {
	$msg = '';
	$bound = bindStmtParam($stmts, $queries, 'selectUserSettings', 'user_id', $userId, $msg); //from config.php
	if ($bound && executeStmt($stmts, 'selectUserSettings', $errors)) {
		$userSettings = array();
		while ($setting = $stmts['selectUserSettings']['stmt']->fetch(PDO::FETCH_ASSOC)) {
			array_push($userSettings, $setting);
		}
		
		processCollation($userSettings, 'user_settings', $collations);
		$returnData = $userSettings;
		return true;
	}

	//STILL TO FIGURE OUT WHAT TO DO WITH THE MSG

	return false;
}


function fetchUserRoles(&$returnData, &$stmts, &$queries, &$collations, $userId, &$errors) {

	//fetching the user settings
	$msg = '';
	$bound = bindStmtParam($stmts, $queries, 'selectUserRoles', 'user_id', $userId, $msg); //from config.php
	if ($bound && executeStmt($stmts, 'selectUserRoles', $errors)) {
		$userRoles = array();
		while ($setting = $stmts['selectUserRoles']['stmt']->fetch(PDO::FETCH_ASSOC)) {
			array_push($userRoles, $setting);
		}
		
		processCollation($userRoles, 'user_roles', $collations);
		$returnData = $userRoles;
		return true;
	}

	//STILL TO FIGURE OUT WHAT TO DO WITH THE MSG

	return false;
}


function fetchUserInterests(&$returnData, &$stmts, &$queries, &$collations, $userId, &$errors) {

	//fetching the user settings
	$msg = '';
	$bound = bindStmtParam($stmts, $queries, 'selectUserInterests', 'user_id', $userId, $msg); //from config.php
	if ($bound && executeStmt($stmts, 'selectUserInterests', $errors)) {
		$userInterests = array();
		if ($interests = $stmts['selectUserInterests']['stmt']->fetchAll(PDO::FETCH_ASSOC)) {
			$userInterests = $interests;
		}
		
		processCollation($userInterests, 'controlled_vocab_entry_settings', $collations);
		$returnData = $userInterests;
		return true;
	}

	//STILL TO FIGURE OUT WHAT TO DO WITH THE MSG

	return false;
}
