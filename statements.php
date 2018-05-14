<?php

//this is a file to store the statements

$queries = array();
	
$queries['insertUser'] = array( 
	'query' =>'INSERT INTO users (username, password, salutation, first_name, middle_name, last_name, gender, initials, email, url, phone, fax, mailing_address,
country, locales, date_last_email, date_registered, date_validated, date_last_login, must_change_password, auth_id, disabled, disabled_reason, auth_str, suffix, billing_address, 
inline_help) VALUES (:insertUser_username, :insertUser_password, :insertUser_salutation, :insertUser_firstName, :insertUser_middleName, :insertUser_lastName, :insertUser_gender, 
:insertUser_initials, :insertUser_email, :insertUser_url, :insertUser_phone, :insertUser_fax, :insertUser_mailingAddress, :insertUser_country, :insertUser_locales, 
:insertUser_dateLastEmail, :insertUser_dateRegistered, :insertUser_dateValidated, :insertUser_dateLastLogin, :insertUser_mustChangePassword, :insertUser_authId, :insertUser_disabled, 
:insertUser_disabledReason, :insertUser_authStr, :insertUser_suffix, :insertUser_billingAddress, :insertUser_inlineHelp)',

	'params' => array(
		'username' => ':insertUser_username', 
		'password' => ':insertUser_password', 'salutation' => ':insertUser_salutation', 'first_name' => ':insertUser_firstName', 
		'middle_name' => ':insertUser_middleName', 'last_name' => ':insertUser_lastName', 'gender' => ':insertUser_gender', 'initials' => ':insertUser_initials', 
		'email' => ':insertUser_email', 'url' => ':insertUser_url', 'phone' => ':insertUser_phone', 'fax' => ':insertUser_fax',
		'mailing_address' => ':insertUser_mailingAddress', 'country' => ':insertUser_country', 'locales' => ':insertUser_locales', 'date_last_email' => ':insertUser_dateLastEmail',
		'date_registered' => ':insertUser_dateRegistered', 'date_validated' => ':insertUser_dateValidated', 'date_last_login' => ':insertUser_dateLastLogin', 
		'must_change_password' => ':insertUser_mustChangePassword', 'auth_id' => ':insertUser_authId', 
		'disabled' => ':insertUser_disabled', 'disabled_reason' => ':insertUser_disabledReason', 'auth_str' => ':insertUser_authStr', 'suffix' => ':insertUser_suffix', 
		'billing_address' => ':insertUser_billingAddress', 'inline_help' => ':insertUser_inlineHelp'
	)
);

$queries['checkUsername'] = array(
	'query' => 'SELECT COUNT(*) as count FROM users WHERE username = :checkUsername_username'
	'params' => array('username' => ':checkUsername_username')
);

$queries['selectUserByEmail'] = array(
	'query' => 'SELECT * FROM users WHERE email = :selectUserByEmail_email',
	'params' => array('email' => ':selectUserByEmail_email')
);

$queries['lastUsers'] = array(
	'query' => 'SELECT * FROM users ORDER BY user_id DESC LIMIT 10',
	'params' => null
);

$queries['insertUserSetting'] = array(
	'query' => 'INSERT INTO user_settings (user_id, locale, setting_name, setting_value, setting_type, assoc_id, assoc_type)
			VALUES (:userSetting_userId, :userSetting_locale, :userSetting_settingName, :userSetting_settingValue, :userSetting_settingType,
			:userSetting_assocId, :userSetting_assocType)',
			
	'params' => array(
		'user_id' => ':userSetting_userId',
		'locale' => ':userSetting_locale',
		'setting_name' => ':userSetting_settingName',
		'setting_value' => ':userSetting_settingValue',
		'setting_type' => ':userSetting_settingType',
		'assoc_id' => ':userSetting_assocId',
		'assoc_type' => ':userSetting_assocType'
	)
);

$statements = array();
$statements['insertUser'] = 