<?php

//this is a file to store the statements


//set the queries
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
		'password' => ':insertUser_password', 
		'salutation' => ':insertUser_salutation', 
		'first_name' => ':insertUser_firstName', 
		'middle_name' => ':insertUser_middleName', 
		'last_name' => ':insertUser_lastName', 
		'gender' => ':insertUser_gender', 
		'initials' => ':insertUser_initials', 
		'email' => ':insertUser_email', 
		'url' => ':insertUser_url', 
		'phone' => ':insertUser_phone', 
		'fax' => ':insertUser_fax',
		'mailing_address' => ':insertUser_mailingAddress', 
		'country' => ':insertUser_country', 
		'locales' => ':insertUser_locales', 
		'date_last_email' => ':insertUser_dateLastEmail',
		'date_registered' => ':insertUser_dateRegistered', 
		'date_validated' => ':insertUser_dateValidated', 
		'date_last_login' => ':insertUser_dateLastLogin', 
		'must_change_password' => ':insertUser_mustChangePassword', 
		'auth_id' => ':insertUser_authId', 
		'disabled' => ':insertUser_disabled', 
		'disabled_reason' => ':insertUser_disabledReason', 
		'auth_str' => ':insertUser_authStr', 
		'suffix' => ':insertUser_suffix', 
		'billing_address' => ':insertUser_billingAddress', 
		'inline_help' => ':insertUser_inlineHelp'
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

/**
this query does not insert the following fields:
	submission_file_id
	revised_file_id
	review_file_id
	editor_file_id
	
	the article_id field is auto_incremented, so it does not go in the query
*/
$queries['insertArticle'] = array(
	'query' => 'INSERT INTO articles (user_id, journal_id, section_id, language, comments_to_ed, date_submitted, last_modified, date_status_modified,
		status, submission_progress, current_round, pages, fast_tracked, hide_author, comments_status, locale, citations) 
		VALUES (:insertArticle_userId, :insertArticle_journalId, :insertArticle_sectionId, :insertArticle_language, :insertArticle_commentsToEd, :insertArticle_dateSubmitted,
		:insertArticle_lastModified, :insertArticle_dateStatusModified, :insertArticle_status, :insertArticle_submissionProgress, :insertArticle_currentRound, 
		:insertArticle_pages, :insertArticle_fastTracked, :insertArticle_hideAuthor, :insertArticle_commentsStatus, :insertArticle_locale, :insertArticle_citations)',
		
	'params' => array(
		'user_id' => ':insertArticle_userId',
		'journal_id' => ':insertArticle_journalId',
		'section_id' => ':insertArticle_sectionId',
		'language' => ':insertArticle_language',
		'comments_to_ed' => ':insertArticle_commentsToEd',
		'date_submitted' => ':insertArticle_dateSubmitted',
		'last_modified' => ':insertArticle_lastModified',
		'date_status_modified' => ':insertArticle_dateStatusModified',
		'status' => ':insertArticle_status',
		'submission_progress' => ':insertArticle_submissionProgress',
		'current_round' => ':insertArticle_currentRound',
		'pages' => ':insertArticle_pages',
		'fast_tracked' => ':insertArticle_fastTracked',
		'hide_author' => ':insertArticle_hideAuthor',
		'comments_status' => ':insertArticle_commentsStatus',
		'locale' => ':insertArticle_locale',
		'citations' => ':insertArticle_citations'
	)
);

$queries['fetchPublishedArticleBySetting'] = array(
	'query' => 'SELECT art.*, sett.*, pub.* 
		 FROM article_settings AS sett
		 INNER JOIN articles AS art
		 	ON art.article_id = sett.article_id
		 INNER JOIN published_articles AS pub
		 	ON pub.article_id = sett.article_id
		  WHERE art.journal_id = :publishedArticle_journalId AND sett.locale = :publishedArticle_locale AND
		       sett.setting_name = :publishedArticle_settingName AND sett.setting_value = :publishedArticle_settingValue',
		 
	'params' => array(
		'journal_id' => ':publishedArticle_journalId',
		'locale' => ':publishedArticle_locale',
		'setting_name' => ':publishedArticle_settingName',
		'setting_value' => ':publishedArticle_settingValue'
	)
	
);

//queries for update
$queries['updateUserDates'] = array(
	'query' => 'UPDATE users SET 
		date_last_email = :updateUserDates_dateLastEmail, date_registered = :updateUserDates_dateRegistered, 
		date_validated = :updateUserDates_dateValidated, date_last_login = :updateUserDates_dateLastLogin
		WHERE user_id = :updateUserDates_userId',
		
	'params' => array(
		'date_last_email' => ':updateUserDates_dateLastEmail', 
		'date_registered' => ':updateUserDates_dateRegistered', 
		'date_validated' => ':updateUserDates_dateValidated', 
		'date_last_login' => ':updateUserDates_dateLastLogin',
		'user_id' => ':updateUserDates_userId'
	)
);

/**
does not update the following fields:
	article_id, since it is the primary key
	journal_id
	section_id
	user_id
	submission_file_id
	revised_file_id
	review_file_id
	editor_file_id
	
*/
$queries['updateArticle'] = array(
	'query' => 'UPDATE articles
		SET language = :updateArticle_language, comments_to_ed = :updateArticle_commentsToEd, date_submitted = :updateArticle_dateSubmitted, last_modified = :updateArticle_lastModified, 
		date_status_modified = :updateArticle_dateStatusModified, status = :updateArticle_status, submission_progress = :updateArticle_submissionProgress, 
		current_round = :updateArticle_currentRound, pages = :updateArticle_pages, fast_tracked = :updateArticle_fastTracked, hide_author = :updateArticle_hideAuthor, 
		comments_status = :updateArticle_commentsStatus, locale = :updateArticle_locale, citations = :updateArticle_citations
		WHERE article_id = :updateArticle_articleId',
		
	'params' => array(
		'language' => ':updateArticle_language',
		'comments_to_ed' => ':updateArticle_commentsToEd',
		'date_submitted' => ':updateArticle_dateSubmitted',
		'last_modified' => ':updateArticle_lastModified',
		'date_status_modified' => ':updateArticle_dateStatusModified',
		'status' => ':updateArticle_status',
		'submission_progress' => ':updateArticle_submissionProgress', 
		'current_round' => ':updateArticle_currentRound', 
		'pages' => ':updateArticle_pages', 
		'fast_tracked' => ':updateArticle_fastTracked', 
		'hide_author' => ':updateArticle_hideAuthor', 
		'comments_status' => ':updateArticle_commentsStatus', 
		'locale' => ':updateArticle_locale', 
		'citations' => ':updateArticle_citations'
		'article_id' => ':updateArticle_articleId'
	)
);


$queries['updateArticleDates'] = array(
	'query' => 'UPDATE articles SET
		date_status_modified = :updateArticleDates_dateStatusModified, date_submitted = :updateArticleDates_dateSubmitted,
		last_modified = :updateArticleDates_lastModified WHERE article_id = :updateArticleDates_articleId',
		
	'params' => array(
		'date_status_modified' => ':updateArticleDates_dateStatusModified',
		'date_submitted' => ':updateArticleDates_dateSubmitted',
		'last_modified' => ':updateArticleDates_lastModified',
		'article_id' => ':updateArticleDates_articleId'
	)
);


$queries['updateArticle_filesIds'] = array(
	'query' => 'UPDATE articles SET 
		submission_file_id = :updateFilesIds_submissionFileId, revised_file_id = :updateFilesIds_revisedFileId, 
		review_file_id = :updateFilesIds_reviewFileId, editor_file_id = :updateFilesIds_editorFileId 
		WHERE article_id = :updateFilesIds_articleId',
		
	'params' => array(
		'submission_file_id' => ':updateFilesIds_submissionFileId',
		'revised_file_id' => ':updateFilesIds_revisedFileId', 
		'review_file_id' => ':updateFilesIds_reviewFileId', 
		'editor_file_id' => ':updateFilesIds_editorFileId', 
		'article_id' => ':updateFilesIds_articleId'
	)
);

$queries['updateArticleFile_namesAndSourceId'] = array(
	'query' => 'UPDATE article_files SET source_file_id = :updateFile_sourceFileId, file_name = :updateFile_fileName, 
		original_file_name = :updateFile_originalFileName WHERE file_id = :updateFile_fileId AND revision = :updateFile_revision',
		
	'params' => array(
		'source_file_id' => ':updateFile_sourceFileId', 
		'file_name' => ':updateFile_fileName', 
		'original_file_name' => ':updateFile_originalFileName',
		'file_id' => ':updateFile_fileId',
		'revision' => ':updateFile_revision'
	)
);

// $conn has to be set
if (!isset($conn)) {
	//set the connection
}

//set the statements
$statements = array();
$statements['insertUser'] = $conn->prepare($queries['insertUser']['query']);