<?php 

//###### USE A CONSTANT THAT STORES THE OJSapp base directory
require_once('config.php');

function fetchUserSettings(&$returnData, &$stmts, &$queries, &$collations, $userId, &$errors) {

	if (!array_key_exists('selectUserSettings', $stmts)) {
		//load the selectUserSettings prepared statement
		createStatement($conn, $stmts, 'selectUserSettings', $queries); //from config.php
	}

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

/**
the roles table only have integer values, so there's no need to process the collations
*/
function fetchUserRoles(&$returnData, &$stmts, &$queries, $userId, &$errors) {

	if (!array_key_exists('selectUserRoles', $stmts)) {
		//load the selectUserRoles prepared statement
		createStatement($conn, $stmts, 'selectUserRoles', $queries); //from config.php
	}

	$msg = '';
	$bound = bindStmtParam($stmts, $queries, 'selectUserRoles', 'user_id', $userId, $msg); //from config.php
	if ($bound && executeStmt($stmts, 'selectUserRoles', $errors)) {
		$userRoles = array();
		while ($setting = $stmts['selectUserRoles']['stmt']->fetch(PDO::FETCH_ASSOC)) {
			array_push($userRoles, $setting);
		}
		
		$returnData = $userRoles;
		return true;
	}

	if (!$bound) {
		if (!array_key_exists('fetchUserRoles', $errors)) $errors['fetchUserRoles'] = array();
		$error = array('user_id' => $userId, 'error' => $msg);
		array_push($errors['fetchUserRoles'], $error);
	}

	return false;
}


function fetchUserInterests(&$returnData, &$stmts, &$queries, &$collations, $userId, &$errors) {

	if (!array_key_exists('selectUserInterests', $stmts)) {
		//load the selectUserInterests prepared statement
		createStatement($conn, $stmts, 'selectUserInterests', $queries); //from config.php
	}

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

function fetchUser(&$conn, $userId, &$queries, &$stmts, $journal = null, &$args = null) {
	
	if ($journal === null) {
 		//### use base directory
		require_once('helperFunctions.php');
		$journal = chooseJournal($conn); //from helperFunctions.php
	}
	
	//variables declaration
	$collations = null;
	$verbose = false;
	$errors = array();
	$bound = false;
	$msg = '';
	$user = array();
	
	if (is_array($args)) {
		if (array_key_exists('collations', $args)) {
			$collations =& $args['collations'];
		}
		if (array_key_exists('verbose', $args)) {
			$verbose =& $args['verbose'];	
		}
		if (array_key_exists('errors', $args)) {
			$errors =& $args['errors'];
		}
	}

	if (!array_key_exists('selectUserById', $stmts)) {
		//load the selectUserById prepared statement
		createStatement($conn, $stmts, 'selectUserById', $queries); //from config.php
	}
	
	/////////////  set the user info /////////////////////////////////////
					
	$bound = bindStmtParam($stmts, $queries, 'selectUserById', 'user_id', $userId, $msg); // from config.php

	if ($bound && executeStmt($stmts, 'selectUserById', $errors)) {
		if ($user = $stmts['selectUserById']['stmt']->fetch(PDO::FETCH_ASSOC)) {
			processCollation($user, 'users', $collations);
			
			//fetching the user settings
			$userSettings = array(); 
			$fetched = fetchUserSettings($userSettings, $stmts, $queries, $collations, $user['user_id'], $errors);
			if ($fetched && !empty($userSettings)) $user['settings'] = $userSettings;
			
			//fetching the user roles for this journal
			$userRoles = array(); 
			$fetched = fetchUserRoles($userRoles, $stmts, $queries, $collations, $user['user_id'], $errors);
			if ($fetched && !empty($userRoles)) $user['roles'] = $userRoles;

			//fetching the user interests
			$userInterests = array(); 
			$fetched = fetchUserInterests($userInterests, $stmts, $queries, $collations, $user['user_id'], $errors);
			if ($fetched && !empty($userInterests)) $user['interests'] = $userInterests;
		
		}//end of the if user was fetched
		
	}// end of the if statement  executed

	//if the user_id parameter was not bound the error will be saved as a message in the variable $msg
	
	//unsetting the local variable references (aliases)
	unset($errors);
	unset($verbose);
	unset($collations);

	return array('user' => $user, 'error' => $msg);
	
}
