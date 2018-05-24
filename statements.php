<?php

//this is a file to store the statements


//set the queries
$queries = array();


////////////////  SELECT  /////////////////////////////////////////////////////////////////

/////////// user related queries ///////////////////
$queries['selectUsernameCount'] = array(
	'query' => 'SELECT COUNT(*) as count FROM users WHERE username = :selectUsernameCount_username'
	'params' => array('username' => ':selectUsernameCount_username')
);

$queries['selectUserByEmail'] = array(
	'query' => 'SELECT * FROM users WHERE email = :selectUserByEmail_email',
	'params' => array('email' => ':selectUserByEmail_email')
);

$queries['selectLastTenUsers'] = array(
	'query' => 'SELECT * FROM users ORDER BY user_id DESC LIMIT 10',
	'params' => null
);

$queries['selectUserById'] = array(
	'query' => 'SELECT * FROM users WHERE user_id = :selectUserById_userId'
	'params' => array('user_id' => ':selectUserById_userId')
);

$queries['selectUserSettings'] = array(
	'query' => 'SELECT * FROM user_settings WHERE user_id = :selectUserSettings_userId',
	'params' => array('user_id', ':selectUserSettings_userId')
);

$queries['selectUserRoles'] = array(
	'query' => 'SELECT * FROM roles WHERE journal_id = :selectUserRoles_journalId AND user_id = :selectUserRoles_userId',
	'params' => array(
		'journal_id' => ':selectUserRoles_journalId',
		'user_id' => ':selectUserRoles_userId'
	)
);

$queries['selectUserInterests'] = array(
	'query' => 'SELECT t.setting_value AS interest, u_int.controlled_vocab_entry_id AS controlled_vocab_entry_id 
		FROM user_interests AS u_int
		INNER JOIN controlled_vocab_entry_settings AS t
			ON u_int.controlled_vocab_entry_id = t.controlled_vocab_entry_id
		WHERE u_int.user_id = :selectUserInterests_userId',
		
	'params' => array('user_id' => ':selectUserInterests_userId')
);

$queries['getInterestControlledVocabId'] = array(
	'query' => 'SELECT * FROM controlled_vocabs WHERE symbolic = "interest"',
	'params' => null
);

$queries['selectLastControlledVocabEntry'] = array(
	'query' => 'SELECT * FROM controlled_vocab_entries ORDER BY controlled_vocab_entry_id DESC LIMIT 1',
	'params' => null
)


////////// article related queries //////////////////
$queries['selectPublishedArticles'] = array(
	'query' => 'SELECT art.*, pub_art.* 
		FROM published_articles AS pub_art
		INNER JOIN articles AS art
			ON art.article_id = pub_art.article_id
		WHERE art.journal_id = :selectPublishedArticles_journalId',
		
	'params' => array('journal_id' => ':selectPublishedArticles_journalId')
);

$queries['selectPublishedArticleBySetting'] = array(
	'query' => 'SELECT art.*, sett.*, pub.* 
		 FROM article_settings AS sett
		 INNER JOIN articles AS art
			ON art.article_id = sett.article_id
		 INNER JOIN published_articles AS pub
			ON pub.article_id = sett.article_id
		  WHERE art.journal_id = :selectPublishedArticleBySetting_journalId AND sett.locale = :selectPublishedArticleBySetting_locale AND
			   sett.setting_name = :selectPublishedArticleBySetting_settingName AND sett.setting_value = :selectPublishedArticleBySetting_settingValue',
		       
	'params' => array(
		'journal_id' => ':selectPublishedArticleBySetting_journalId',
		'locale' => ':selectPublishedArticleBySetting_locale',
		'setting_name' => ':selectPublishedArticleBySetting_settingName',
		'setting_value' => ':selectPublishedArticleBySetting_settingValue'
	)
	
);

$queries['countPublishedArticleBySetting'] = array(
	'query' => 'SELECT COUNT(*) AS count
		 FROM article_settings AS sett
		 INNER JOIN articles AS art
		 	ON art.article_id = sett.article_id
		 INNER JOIN published_articles AS pub
		 	ON pub.article_id = sett.article_id
		  WHERE art.journal_id = :countPublishedArticleBySetting_journalId AND sett.locale = :countPublishedArticleBySetting_locale AND
		       sett.setting_name = :countPublishedArticleBySetting_settingName AND sett.setting_value = :countPublishedArticleBySetting_settingValue',
		       
	'params' => array(
		'journal_id' => ':countPublishedArticleBySetting_journalId',
		'locale' => ':countPublishedArticleBySetting_locale',
		'setting_name' => ':countPublishedArticleBySetting_settingName',
		'setting_value' => ':countPublishedArticleBySetting_settingValue'
	)
);

$queries['selectUnpublishedArticles'] = array(
	'query' => 'SELECT * FROM articles WHERE article_id IN (
			SELECT article_id FROM articles WHERE article_id NOT IN (
				SELECT article_id FROM published_articles
			) AND journal_id = :selectUnpublishedArticles_journalId
		)',
		
	'params' => array('journal_id' => ':selectUnpublishedArticles_journalId')
);

$queries['selectArticles'] = array(
	'query' => 'SELECT art.*, pub_art.* 
		 FROM articles AS art
		 LEFT JOIN published_articles AS pub_art
			ON pub_art.article_id = art.article_id
		 WHERE art.journal_id = :selectArticles_journalId',
	
	'params' => array('journal_id' => ':selectArticles_journalId')
);

$queries['selectAuthors'] = array(
	'query' => 'SELECT * FROM authors WHERE submission_id = :selectAuthors_submissionId',
	'params' => array('submission_id' => ':selectAuthors_submissionId')
);

$queries['selectAuthorByEmail'] = array(
	'query' => 'SELECT * FROM authors WHERE email = :selectAuthorByEmail_email',
	'params' => array('email' => ':selectAuthorByEmail_email')
);

$queries['selectAuthorSettings'] = array(
	'query' => 'SELECT * FROM author_settings WHERE author_id = :selectAuthorSettings_authorId',
	'params' => array('author_id' => ':selectAuthorSettings_authorId')
);

$queries['selectLastAuthor'] = array(
	'query' => 'SELECT * FROM authors ORDER BY author_id DESC LIMIT 1',
	'params' => null
);

$queries['selectArticleSettings'] = array(
	'query' => 'SELECT * FROM article_settings WHERE article_id = :selectArticleSettings_articleId',
	'params' => array('article_id' => ':selectArticleSettings_articleId')
);

$queries['selectArticleFiles'] = array(
	'query' => 'SELECT * FROM article_files WHERE article_id = :selectArticleFiles_articleId',
	'params' => array('article_id' => ':selectArticleFiles_articleId')
);

$queries['selectLastArticleFile'] = array(
	'query' => 'SELECT * FROM article_files ORDER BY file_id DESC LIMIT 1',
	'params' => null
);

$queries['selectArticleSupplementaryFiles'] = array(
	'query' => 'SELECT * FROM article_supplementary_files WHERE article_id = :selectArticleSupplementaryFiles_articleId',
	'params' => array('article_id' => ':selectArticleSupplementaryFiles_articleId')
);

$queries['selectLastArticleSupplementaryFile'] = array(
	'query' => 'SELECT * FROM article_supplementary_files ORDER BY supp_id DESC LIMIT 1',
	'params' => null
);

$queries['selectArticleSuppFileSettings'] = array(
	'query' => 'SELECT * FROM article_supp_file_settings WHERE supp_id = :selectArticleSuppFileSettings_suppId',
	'params' => array('supp_id' => ':selectArticleSuppFileSettings_suppId')
);

$queries['selectArticleComments'] = array(
	'query' => 'SELECT * FROM article_comments WHERE article_id = :selectArticleComments_articleId',
	'params' => array('article_id' => ':selectArticleComments_articleId')
);

$queries['selectLastArticleComment'] = array(
	'query' => 'SELECT * FROM article_comments ORDER BY comment_id DESC LIMIT 1',
	'params' => null
);

$queries['selectLastArticleNote'] = array(
	'query' => 'SELECT * FROM article_notes ORDER BY note_id DESC LIMIT 1',
	'params' => null
);

$queries['selectArticleGalleys'] = array(
	'query' => 'SELECT * FROM article_galleys WHERE article_id = :selectArticleGalleys_articleId',
	'params' => array('article_id' => ':selectArticleGalleys_articleId')
);

$queries['selectLastArticleGalley'] = array(
	'query' => 'SELECT * FROM article_galleys ORDER BY galley_id DESC LIMIT 1',
	'params' => null
);

$queries['selectArticleGalleySettings'] = array(
	'query' => 'SELECT * FROM article_galley_settings WHERE galley_id = :selectArticleGalleySettings_galleyId',
	'params' => array('galley_id' => ':selectArticleGalleySettings_galleyId')
);

$queries['selectArticleXmlGalleys'] = array(
	'query' => 'SELECT * FROM article_xml_galleys WHERE galley_id = :selectArticleXmlGalleys_galleyId AND article_id = :selectArticleXmlGalleys_articleId',
	'params' => array(
		'galley_id' => ':selectArticleXmlGalleys_galleyId',
		'article_id' => ':selectArticleXmlGalleys_articleId'
	)
);

$queries['selectLastArticleXmlGalley'] = array(
	'query' => 'SELECT * FROM article_xml_galleys ORDER BY xml_galley_id DESC LIMIT 1',
	'params' => null
);

$queries['selectHtmlGalleyImages'] = array(
	'query' => 'SELECT * FROM article_html_galley_images WHERE galley_id = :selectHtmlGalleyImages_galleyId',
	'params' => array('galley_id' => ':selectHtmlGalleyImages_galleyId')
);

$queries['selectArticleSearchKeywordLists'] = array(
	'query' => 'SELECT * FROM article_search_keyword_list WHERE keyword_id = :selectArticleSearchKeywordLists_keywordId',
	'params' => array('keyword_id' => ':selectArticleSearchKeywordLists_keywordId')
);

$queries['selectLastArticleSearchKeywordList'] = array(
	'query' => 'SELECT * FROM article_search_keyword_list ORDER BY keyword_id DESC LIMIT 1',
	'params' => null
);

$queries['selectArticleSearchObjectKeywords'] = array(
	'query' => 'SELECT * FROM article_search_object_keywords WHERE object_id = :selectArticleSearchObjectKeywords_objectId'
	'params' => array('object_id' => ':selectArticleSearchObjectKeywords_objectId')
);

$queries['selectArticleSearchObjects'] = array(
	'query' => 'SELECT * FROM article_search_objects WHERE article_id = :selectArticleSearchObjects_articleId',
	'params' => array('article_id' => ':selectArticleSearchObjects_articleId')
);

$queries['selectLastArticleSearchObject'] = array(
	'query' => 'SELECT * FROM article_search_objects ORDER BY object_id DESC LIMIT 1',
	'params' => null
);

$queries['selectEditDecisions'] = array(
	'query' => 'SELECT * FROM edit_decisions WHERE article_id = :selectEditDecisions_articleId',
	'params' => array('article_id' => ':selectEditDecisions_articleId')
);

$queries['selectLastEditDecision'] = array(
	'query' => 'SELECT * FROM edit_decisions ORDER BY edit_decision_id DESC LIMIT 1',
	'params' => null
);

$queries['selectEditAssignments'] = array(
	'query' => 'SELECT * FROM edit_assignments WHERE article_id = :selectEditAssignments_articleId',
	'params' => array('article_id' => ':selectEditAssignments_articleId')
);

$queries['selectLastEditAssignment'] = array(
	'query' => 'SELECT * FROM edit_assignments ORDER BY edit_id DESC LIMIT 1',
	'params' => null
);

$queries['selectReviewAssignments'] = array(
	'query' => 'SELECT * FROM review_assignments WHERE submission_id = :selectReviewAssignments_submissionId',
	'params' => array('submission_id' => ':selectReviewAssignments_submissionId')
);

$queries['selectLastReviewAssignment'] = array(
	'query' => 'SELECT * FROM review_assignments ORDER BY review_id DESC LIMIT 1',
	'params' => null
);

$queries['selectReviewRounds'] = array(
	'query' => 'SELECT * FROM review_rounds WHERE submission_id = :selectReviewRounds_submissionId'
	'params' => array('submission_id' => ':selectReviewRounds_submissionId')
);

$queries['selectLastReviewRound'] = array(
	'query' => 'SELECT * FROM review_rounds ORDER BY review_round_id DESC LIMIT 1',
	'params' => null
);

//review_form_responses
$queries['selectReviewFormResponses'] = array(
	'query' => 'SELECT * FROM review_form_responses WHERE review_id = :selectReviewFormResponses_reviewId',
	'params' => array('review_id' => ':selectReviewFormResponses_reviewId')
);


////////// section related //////////////
$queries['selectSections'] = array(
	'query' => 'SELECT * FROM sections WHERE journal_id = :selectSections_journalId',
	'params' => array('journal_id' => ':selectSections_journalId')
);

$queries['selectSectionSettings'] = array(
	'query' => 'SELECT * FROM section_settings WHERE section_id = :selectSectionSettings_sectionId'
	'params' => array('section_id' => ':selectSectionSettings_sectionId')
);

$queries['selectSectionTitlesAndAbbrevs'] = array(
	'query' => 'SELECT section_id, setting_name, setting_value, locale 
	FROM section_settings 
	WHERE section_id = :selectSectionTitlesAndAbbrevs_sectionId AND setting_name IN ("title", "abbrev")',
	
	'params' => array('section_id' => ':selectSectionTitlesAndAbbrevs_sectionId')
);


////// announcements related //////////////
$queries['selectAnnouncements'] = array(
	'query' => 'SELECT * FROM announcements WHERE assoc_id = :selectAnnouncements_journalId',
	'params' => array('assoc_id' => ':selectAnnouncements_journalId')
);

$queries['selectAnnouncementSettings'] = array(
	'query' => 'SELECT * FROM announcement_settings WHERE announcement_id = :selectAnnouncementSettings_announcementId',
	'params' => array('announcement_id' => ':selectAnnouncementSettings_announcementId')
);


//////// groups related  ////////////////////
$queries['selectGroups'] = array(
	'query' => 'SELECT * FROM groups WHERE assoc_id = :selectGroups_journalId',
	'params' => array('assoc_id' => ':selectGroups_journalId')
);

$queries['selectGroupSettings'] = array(
	'query' => 'SELECT * FROM group_settings WHERE group_id = :selectGroupSettings_groupId',
	'params' => array('group_id' => ':selectGroupSettings_groupId')
);

$queries['selectGroupMemberships'] = array(
	'query' => 'SELECT * FROM group_memberships WHERE group_id = :selectGroupMemberships_groupId'
	'params' => array('group_id' => ':selectGroupMemberships_groupId')
);


//////// review_forms related //////////////
$queries['selectReviewForms'] = array(
	'query' => 'SELECT * FROM review_forms WHERE assoc_id = :selectReviewForms_assocId',
	'params' => array('assoc_id' => ':selectReviewForms_assocId')
);

$queries['selectLastReviewForm'] = array(
	'query' => 'SELECT * FROM review_forms ORDER BY review_form_id DESC LIMIT 1',
	'params' => null
);

$queries['selectReviewFormSettings'] = array(
	'query' => 'SELECT * FROM review_form_settings WHERE review_form_id = :selectReviewFormSettings_reviewFormId',
	'params' => array('review_form_id' => ':selectReviewFormSettings_reviewFormId')
);

$queries['selectReviewFormElements'] = array(
	'query' => 'SELECT * FROM review_form_elements WHERE review_form_id = :selectReviewFormElements_reviewFormId',
	'params' => array('review_form_id' => ':selectReviewFormElements_reviewFormId')
);

$queries['selectLastReviewFormElement'] = array(
	'query' => 'SELECT * FROM review_form_elements ORDER BY review_form_element_id DESC LIMIT 1',
	'params' => null
);

$queries['selectReviewFormElementSettings'] = array(
	'query' => 'SELECT * FROM review_form_element_settings WHERE review_form_element_id = :selectReviewFormElementSettings_reviewFormElementId',
	'params' => array('review_form_element_id' => ':selectReviewFormElementSettings_reviewFormElementId')
);

//the review_form_response queries are at the final of the article related select queries



/////////////  issues related  //////////////////////
$queries['selectIssues'] = array(
	'query' => 'SELECT * FROM issues WHERE journal_id = :selectIssues_journalId',
	'params' => array('journal_id' => ':selectIssues_journalId')
);

$queries['selectIssueSettings'] = array(
	'query' => 'SELECT * FROM issue_settings WHERE issue_id = :selectIssueSettings_issueId',
	'params' => array('issue_id' => ':selectIssueSettings_issueId')
);
	
$queries['selectCustomIssueOrders'] = array(
	'query' => 'SELECT * FROM custom_issue_orders WHERE journal_id = :selectCustomIssueOrders_journalId AND issue_id = :selectCustomIssueOrders_issueId',
	'params' => array(
		'journal_id' => ':selectCustomIssueOrders_journalId',
		'issue_id' => ':selectCustomIssueOrders_issueId'
	)
);

$queries['selectCustomSectionOrders'] = array(
	'query' => 'SELECT * FROM custom_section_orders WHERE issue_id = :selectCustomSectionOrders_issueId',
	'params' => array('issue_id' => ':selectCustomSectionOrders_issueId')
);


////////// plugin_settings ////////////
$queries['selectPluginSettings'] = array(
	'query' => 'SELECT * FROM plugin_settings WHERE journal_id = :selectPluginSettings_journalId',
	'params' => array('journal_id' => ':selectPluginSettings_journalId')
);


////////// event_log /////////////////

function createSelectEventLogsQuery(&$queriesArray, $articleIdsArray) {
	
	require_once('helperFunctions.php');
	$ids = getArticleIdsSTR($articleIds); //from helperFunctions.php
	
	$queriesArray['selectEventLogs'] = array('query' => 'SELECT * FROM event_log WHERE assoc_id IN ' . $ids, 'params' => null);
}

$queries['selectLastEventLog'] = array(
	'query' => 'SELECT * FROM event_log ORDER BY log_id DESC LIMIT 1',
	'params' => null
);

$queries['selectEventLogSettings'] = array(
	'query' => 'SELECT * FROM event_log_settings WHERE log_id = :selectEventLogSettings_logId',
	'params' => array('log_id' => ':selectEventLogSettings_logId')
);

////////// email_log /////////////////////

function createSelectEmailLogsQuery(&$queriesArray, $articleIdsArray) {
	
	require_once('helperFunctions.php');
	$ids = getArticleIdsSTR($articleIds); //from helperFunctions.php
	
	$queriesArray['selectEventLogs'] = array('query' => 'SELECT * FROM email_log WHERE assoc_id IN ' . $ids, 'params' => null);
}

$queries['selectLastEmailLog'] = array(
	'query' => 'SELECT * FROM email_log ORDER BY log_id DESC LIMIT 1',
	'params' => null
);

$queries['selectEmailLogUsers'] = array(
	'query' => 'SELECT * FROM email_log_users WHERE email_log_id = :selectEmailLogUsers_emailLogId',
	'params' => array('email_log_id' => ':selectEmailLogUsers_emailLogId')
);


//////////////////////// END OF SELECT ////////////////////////////////////////////////////////////////////////



/////////////////////// INSERT /////////////////////////////////////////////////////////////////////////////////

///////// user related /////////////
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

$queries['insertUserSetting'] = array(
	'query' => 'INSERT INTO user_settings (user_id, locale, setting_name, setting_value, setting_type, assoc_id, assoc_type)
			VALUES (:insertUserSetting_userId, :insertUserSetting_locale, :insertUserSetting_settingName, :insertUserSetting_settingValue, :insertUserSetting_settingType,
			:insertUserSetting_assocId, :insertUserSetting_assocType)',
			
	'params' => array(
		'user_id' => ':insertUserSetting_userId',
		'locale' => ':insertUserSetting_locale',
		'setting_name' => ':insertUserSetting_settingName',
		'setting_value' => ':insertUserSetting_settingValue',
		'setting_type' => ':insertUserSetting_settingType',
		'assoc_id' => ':insertUserSetting_assocId',
		'assoc_type' => ':insertUserSetting_assocType'
	)
);

$queries['insertUserRole'] = array(
	'query' => 'INSERT INTO roles (journal_id, user_id, role_id) 
	VALUES (:insertUserRole_journalId, :insertUserRole_userId, :insertUserRole_roleId)',
	
	'params' => array(
		'journal_id' => ':insertUserRole_journalId',
		'user_id' => ':insertUserRole_userId',
		'role_id' => ':insertUserRole_roleId'
	)
);

$queries['insertControlledVocabEntry'] = array(
	'query' => 'INSERT INTO controlled_vocab_entries (controlled_vocab_id, seq) 
	VALUES (:insertControlledVocabEntry_controlledVocabId, :insertControlledVocabEntry_seq)'
	
	'params' => array(
		'controlled_vocab_id' => ':insertControlledVocabEntry_controlledVocabId',
		'seq' => ':insertControlledVocabEntry_seq'
	)
);

$queries['insertControlledVocabEntrySetting'] = array(
	'query' => 'INSERT INTO controlled_vocab_entry_settings (controlled_vocab_entry_id, locale, setting_name, setting_value, setting_type)
	VALUES (:insertControlledVocabEntrySetting_controlledVocabEntryId, :insertControlledVocabEntrySetting_locale,
	:insertControlledVocabEntrySetting_settingName, :insertControlledVocabEntrySetting_settingValue, :insertControlledVocabEntrySetting_settingType)',
	
	'params' => array(
		'controlled_vocab_entry_id' => ':insertControlledVocabEntrySetting_controlledVocabEntryId',
		'locale' => ':insertControlledVocabEntrySetting_locale',
		'setting_name' => ':insertControlledVocabEntrySetting_settingName',
		'setting_value' => ':insertControlledVocabEntrySetting_settingValue',
		'setting_type' => ':insertControlledVocabEntrySetting_settingType'
	)
);

$queries['insertUserInterest'] = array(
	'query' => 'INSERT INTO user_interests (controlled_vocab_entry_id, user_id) 
	VALUES (:insertUserInterest_controlledVocabEntryId, :insertUserInterest_userId)',
	
	'params' => array(
		'controlled_vocab_entry_id' => ':insertUserInterest_controlledVocabEntryId',
		'user_id' => ':insertUserInterest_userId'
	)
);


////////// article related //////////////
$queries['insertArticle'] = array(
	'query' => 'INSERT INTO articles (user_id, journal_id, section_id, language, comments_to_ed, date_submitted, last_modified, date_status_modified,
		status, submission_progress, current_round, pages, fast_tracked, hide_author, comments_status, locale, citations) 
		VALUES (:insertArticle_userId, :insertArticle_journalId, :insertArticle_sectionId, :insertArticle_language, :insertArticle_commentsToEd, :insertArticle_dateSubmitted, 
		:insertArticle_lastModified, :insertArticle_dateStatusModified, :insertArticle_status, :insertArticle_submissionProgress, :insertArticle_currentRound, :insertArticle_pages, 
		:insertArticle_fastTracked, :insertArticle_hideAuthor, :insertArticle_commentsStatus, :insertArticle_locale, :insertArticle_citations)'
		
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

$queries['insertArticleSetting'] = array(
	'query' => 'INSERT INTO article_settings (article_id, locale, setting_name, setting_value, setting_type) VALUES 
		(:insertArticleSetting_articleId, :insertArticleSetting_locale, :insertArticleSetting_settingName, :insertArticleSetting_settingValue, :insertArticleSetting_settingType)',
		
	'params' => array(
		'article_id' => ':insertArticleSetting_articleId',
		'locale' => ':insertArticleSetting_locale',
		'setting_name' => ':insertArticleSetting_settingName',
		'setting_value' => ':insertArticleSetting_settingValue',
		'setting_type' => ':insertArticleSetting_settingType'
	)
);

$queries['insertAuthor'] = array(
	'query' => 'INSERT INTO authors (submission_id, primary_contact, seq, first_name, middle_name, last_name, country, email, url, suffix) 
		VALUES (:insertAuthor_submissionId, :insertAuthor_primaryContact, :insertAuthor_seq, :insertAuthor_firstName, :insertAuthor_middleName, 
		:insertAuthor_lastName, :insertAuthor_country, :insertAuthor_email, :insertAuthor_url, :insertAuthor_suffix)',
		
	'params' => array(
		'submission_id' => ':insertAuthor_submissionId',
		'primary_contact' => ':insertAuthor_primaryContact',
		'seq' => ':insertAuthor_seq',
		'first_name' => ':insertAuthor_firstName',
		'middle_name' => ':insertAuthor_middleName',
		'last_name' => ':insertAuthor_lastName',
		'country' => ':insertAuthor_country',
		'email' => ':insertAuthor_email',
		'url' => ':insertAuthor_url',
		'suffix' => ':insertAuthor_suffix'
	)
);

$queries['insertAuthorSetting'] = array(
	'query' => 'INSERT INTO author_settings (author_id, locale, setting_name, setting_value, setting_type) VALUES (:insertAuthorSettings_authorId,
		:insertAuthorSettings_locale, :insertAuthorSettings_settingName, :insertAuthorSettings_settingValue, :insertAuthorSettings_settingType)',
		
	'params' => array(
		'author_id' => ':insertAuthorSettings_authorId',
		'locale' => ':insertAuthorSettings_locale',
		'setting_name' => ':insertAuthorSettings_settingName',
		'setting_value' => ':insertAuthorSettings_settingValue',
		'setting_type' => ':insertAuthorSettings_settingType'
	)
);


$queries['insertArticleFile'] = array(
	'query' => 'INSERT INTO article_files (revision, source_revision, article_id, file_name, file_type, file_size, original_file_name, file_stage, viewable,
		date_uploaded, date_modified, round, assoc_id) VALUES (:insertArticleFile_revision, :insertArticleFile_sourceRevision, :insertArticleFile_articleId, 
		:insertArticleFile_fileName, :insertArticleFile_fileType, :insertArticleFile_fileSize, :insertArticleFile_originalFileName, :insertArticleFile_fileStage, 
		:insertArticleFile_viewable, :insertArticleFile_dateUploaded, :insertArticleFile_dateModified, :insertArticleFile_round, :insertArticleFile_assocId)',
		
	'params' => array(
		'revision' => ':insertArticleFile_revision',
		'source_revision' => ':insertArticleFile_sourceRevision',
		'article_id' => ':insertArticleFile_articleId',
		'file_name' => ':insertArticleFile_fileName',
		'file_type' => ':insertArticleFile_fileType',
		'file_size' => ':insertArticleFile_fileSize',
		'original_file_name' => ':insertArticleFile_originalFileName',
		'file_stage' => ':insertArticleFile_fileStage',
		'viewable' => ':insertArticleFile_viewable',
		'date_uploaded' => ':insertArticleFile_dateUploaded',
		'date_modified' => ':insertArticleFile_dateModified',
		'round' => ':insertArticleFile_round',
		'assoc_id' => ':insertArticleFile_assocId'
	)
);

$queries['insertArticleRevisedFile'] = array(
	'query' => 'INSERT INTO article_files 
		(file_id, revision, source_revision, article_id, file_name, file_type, file_size, original_file_name, file_stage, viewable, date_uploaded, date_modified, round, assoc_id) 
		VALUES (:insertArticleRevisedFile_fileId, :insertArticleRevisedFile_revision, :insertArticleRevisedFile_sourceRevision, :insertArticleRevisedFile_articleId, 
		:insertArticleRevisedFile_fileName, :insertArticleRevisedFile_fileType, :insertArticleRevisedFile_fileSize, :insertArticleRevisedFile_originalFileName, 
		:insertArticleRevisedFile_fileStage, :insertArticleRevisedFile_viewable, :insertArticleRevisedFile_dateUploaded, :insertArticleRevisedFile_dateModified, 
		:insertArticleRevisedFile_round, :insertArticleRevisedFile_assocId)',
		
	'params' => array(
		'file_id' => ':insertArticleRevisedFile_fileId',
		'revision' => ':insertArticleRevisedFile_revision',
		'article_id' => ':insertArticleRevisedFile_articleId',
		'file_name' => ':insertArticleRevisedFile_fileName',
		'file_type' => ':insertArticleRevisedFile_fileType',
		'file_size' => ':insertArticleRevisedFile_fileSize',
		'original_file_name' => ':insertArticleRevisedFile_originalFileName',
		'file_stage' => ':insertArticleRevisedFile_fileStage',
		'viewable' => ':insertArticleRevisedFile_viewable',
		'date_uploaded' => ':insertArticleRevisedFile_dateUploaded',
		'date_modified' => ':insertArticleRevisedFile_dateModified',
		'round' => ':insertArticleRevisedFile_round',
		'assoc_id' => ':insertArticleRevisedFile_assocId'
	)
);

$queries['insertArticleSupplementaryFile'] = array(
	'query' => 'INSERT INTO article_supplementary_files (file_id, article_id, type, language, date_created, show_reviewers, date_submitted, seq, remote_url) 
		VALUES (:insertArticleSupplementaryFile_fileId, :insertArticleSupplementaryFile_articleId, :insertArticleSupplementaryFile_type, 
		:insertArticleSupplementaryFile_language, :insertArticleSupplementaryFile_dateCreated, :insertArticleSupplementaryFile_showReviewers, 
		:insertArticleSupplementaryFile_dateSubmitted, :insertArticleSupplementaryFile_seq, :insertArticleSupplementaryFile_remoteUrl)'
		
	'params' => array(
		'file_id' => ':insertArticleSupplementaryFile_fileId',
		'article_id' => ':insertArticleSupplementaryFile_articleId',
		'type' => ':insertArticleSupplementaryFile_type',
		'language' => 'insertArticleSupplementaryFile_language',
		'date_created' => ':insertArticleSupplementaryFile_dateCreated',
		'show_reviewers' => ':insertArticleSupplementaryFile_showReviewers',
		'date_submitted' => ':insertArticleSupplementaryFile_dateSubmitted',
		'seq' => ':insertArticleSupplementaryFile_seq',
		'remote_url' => ':insertArticleSupplementaryFile_remoteUrl'
	)
);

$queries['insertArticleSuppFileSetting'] = array(
	'query' => 'INSERT INTO article_supp_file_settings (supp_id, locale, setting_name, setting_value, setting_type) VALUES (:insertArticleSuppFileSetting_suppId,
		:insertArticleSuppFileSetting_locale, :insertArticleSuppFileSetting_settingName, :insertArticleSuppFileSetting_settingValue, :insertArticleSuppFileSetting_settingType)'
		
	'params' => array(
		'supp_id' => ':insertArticleSuppFileSetting_suppId',
		'locale' => ':insertArticleSuppFileSetting_locale',
		'setting_name' => ':insertArticleSuppFileSetting_settingName',
		'setting_value' => ':insertArticleSuppFileSetting_settingValue',
		'setting_type' => ':insertArticleSuppFileSetting_settingType'
	)
);

$queries['insertArticleNote'] = array(
	'query' => 'INSERT INTO article_notes (article_id, user_id, date_created, date_modified, title, note, file_id) VALUES (:insertArticleNote_articleId, :insertArticleNote_userId,
		:insertArticleNote_dateCreated, :insertArticleNote_dateModified, :insertArticleNote_title, :insertArticleNote_note, :insertArticleNote_fileId)',
	
	'params' => array(
		'article_id' => ':insertArticleNote_articleId',
		'user_id' => ':insertArticleNote_userId',
		'date_created' => ':insertArticleNote_dateCreated',
		'date_modified' => ':insertArticleNote_dateModified',
		'title' => ':insertArticleNote_title',
		'note' => ':insertArticleNote_note',
		'file_id' => ':insertArticleNote_fileId'
	)
);

$queries['insertArticleComment'] = array(
	'query' => 'INSERT INTO article_comments (comment_type, role_id, article_id, assoc_id, author_id, comment_title, comments, date_posted, date_modified, viewable) 
		VALUES (:insertArticleComment_commentType, :insertArticleComment_roleId, :insertArticleComment_articleId, :insertArticleComment_assocId, :insertArticleComment_authorId, 
		:insertArticleComment_commentTitle, :insertArticleComment_comments, :insertArticleComment_datePosted, :insertArticleComment_dateModified, :insertArticleComment_viewable)',
		
	'params' => array(
		'comment_type' => ':insertArticleComment_commentType',
		'role_id' => ':insertArticleComment_roleId',
		'article_id' => ':insertArticleComment_articleId',
		'assoc_id' => ':insertArticleComment_assocId',
		'author_id' => ':insertArticleComment_authorId',
		'comment_title' => ':insertArticleComment_commentTitle',
		'comments' => ':insertArticleComment_comments',
		'date_posted' => ':insertArticleComment_datePosted',
		'date_modified' => ':insertArticleComment_dateModified',
		'viewable' => ':insertArticleComment_viewable'
	)
);

$queries['insertArticleGalley'] = array(
	'query' => 'INSERT INTO article_galleys (locale, article_id, file_id, label, html_galley, style_file_id, seq, remote_url) 
		VALUES (:insertArticleGalley_locale, :insertArticleGalley_articleId, :insertArticleGalley_fileId, :insertArticleGalley_label, 
		:insertArticleGalley_htmlGalley, :insertArticleGalley_styleFileId, :insertArticleGalley_seq, :insertArticleGalley_remoteUrl)',
		
	'params' => array(
		'locale' => ':insertArticleGalley_locale',
		'article_id' => ':insertArticleGalley_articleId',
		'file_id' => ':insertArticleGalley_fileId',
		'label' => ':insertArticleGalley_label',
		'html_galley' => ':insertArticleGalley_htmlGalley',
		'style_file_id' => ':insertArticleGalley_styleFileId'
		'seq' => ':insertArticleGalley_seq',
		'remote_url' => ':insertArticleGalley_remoteUrl'
	)
);

$queries['insertArticleGalleySetting'] = array(
	'query' => 'INSERT INTO article_galley_settings (galley_id, locale, setting_name, setting_value, setting_type) VALUES (:insertArticleGalleySetting_galleyId,
		:insertArticleGalleySetting_locale, :insertArticleGalleySetting_settingName, :insertArticleGalleySetting_settingValue, :insertArticleGalleySetting_settingType)',
		
	'params' => array(
		'galley_id' => ':insertArticleGalleySetting_galleyId',
		'locale' => ':insertArticleGalleySetting_locale',
		'setting_name' => ':insertArticleGalleySetting_settingName',
		'setting_value' => ':insertArticleGalleySetting_settingValue',
		'setting_type' => ':insertArticleGalleySetting_settingType'
	)
);

$queries['insertArticleXmlGalley'] = array(
	'query' => 'INSERT INTO article_xml_galleys (galley_id, article_id, label, galley_type, views) VALUES (:insertArticleXmlGalley_galleyId, 
		:insertArticleXmlGalley_articleId, :insertArticleXmlGalley_label, :insertArticleXmlGalley_galleyType, :insertArticleXmlGalley_views)'
		
	'params' => array(
		'galley_id' => ':insertArticleXmlGalley_galleyId',
		'article_id' => ':insertArticleXmlGalley_articleId',
		'label' => ':insertArticleXmlGalley_label',
		'galley_type' => ':insertArticleXmlGalley_galleyType',
		'views' => ':insertArticleXmlGalley_views'
	)
);

$queries['insertArticleHtmlGalleyImage'] = array(
	'query' => 'INSERT INTO article_html_galley_images (galley_id, file_id) VALUES (:insertArticleHtmlGalleyImage_galleyId, :insertArticleHtmlGalleyImage_fileId)',
	'params' => array(
		'galley_id' => ':insertArticleHtmlGalleyImage_galleyId',
		'file_id' => ':insertArticleHtmlGalleyImage_fileId'
	)
);

$queries['insertArticleSearchKeywordList'] = array(
	'query' => 'INSERT INTO article_search_keyword_list (keyword_text) VALUES (:insertArticleSearchKeywordList_keywordText)',
	'params' => array('keyword_text' => ':insertArticleSearchKeywordList_keywordText')
);

$queries['insertArticleSearchObjectKeyword'] = array(
	'query' => 'INSERT INTO article_search_object_keywords (object_id, keyword_id, pos) VALUES (:insertArticleSearchObjectKeyword_objectId,
		:insertArticleSearchObjectKeyword_keywordId, :insertArticleSearchObjectKeyword_pos)'
		
	'params' => array(
		'object_id' => ':insertArticleSearchObjectKeyword_objectId',
		'keyword_id' => ':insertArticleSearchObjectKeyword_keywordId',
		'pos' => ':insertArticleSearchObjectKeyword_pos'
	)
);

$queries['insertArticleSearchObject'] = array(
	'query' => 'INSERT INTO article_search_objects (article_id, type, assoc_id) 
		VALUES (:insertArticleSearchObject_articleId, :insertArticleSearchObject_type, :insertArticleSearchObject_assocId)'
		
	'params' => array(
		'article_id' => ':insertArticleSearchObject_articleId',
		'type' => ':insertArticleSearchObject_type',
		'assoc_id' => ':insertArticleSearchObject_assocId'
	)
);

$queries['insertEditDecision'] = array(
	'query' => 'INSERT INTO edit_decisions (article_id, round, editor_id, decision, date_decided) VALUES (:insertEditDecision_articleId, 
		:insertEditDecision_round, :insertEditDecision_editorId, :insertEditDecision_decision, :insertEditDecision_dateDecided)'
		
	'params' => array(
		'article_id' => ':insertEditDecision_articleId',
		'round' => ':insertEditDecision_round',
		'editor_id' => ':insertEditDecision_editorId',
		'decision' => ':insertEditDecision_decision',
		'date_decided' => ':insertEditDecision_dateDecided'
	)
);

$queries['insertEditAssignment'] = array(
	'query' => 'INSERT INTO edit_assignments (article_id, editor_id, can_edit, can_review, date_assigned, date_notified, date_underway) 
		VALUES (:insertEditAssignment_articleId, :insertEditAssignment_editorId, :insertEditAssignment_canEdit, :insertEditAssignment_canReview, 
		:insertEditAssignment_dateAssigned, :insertEditAssignment_dateNotified, :insertEditAssignment_dateUnderway)',
		
	'params' => array(
		'article_id' => ':insertEditAssignment_articleId',
		'editor_id' => ':insertEditAssignment_editorId',
		'can_edit' => ':insertEditAssignment_canEdit',
		'can_review' => ':insertEditAssignment_canReview',
		'date_assigned' => ':insertEditAssignment_dateAssigned',
		'date_notified' => ':insertEditAssignment_dateNotified',
		'date_underway' => ':insertEditAssignment_dateUnderway'
	)
);

$queries['insertReviewRound'] = array(
	'query' => 'INSERT INTO review_rounds (submission_id, stage_id, round, review_revision, status) VALUES (:insertReviewRound_submissionId,
		:insertReviewRound_stageId, :insertReviewRound_round, :insertReviewRound_reviewRevision, :insertReviewRound_status)',
		
	'params' => array(
		'submission_id' => ':insertReviewRound_submissionId',
		'stage_id' => ':insertReviewRound_stageId',
		'round' => ':insertReviewRound_round',
		'review_revision' => ':insertReviewRound_reviewRevision',
		'status' => ':insertReviewRound_status'
	)
);

$queries['insertReviewAssignment'] = array(
	'query' => 'INSERT INTO review_assignments (submission_id, reviewer_id, competing_interests, regret_message, recommendation, date_assigned, date_notified, date_confirmed, 
		date_completed, date_acknowledged, date_due, last_modified, reminder_was_automatic, declined, replaced, cancelled, reviewer_file_id, date_rated,
		date_reminded, quality, review_round_id, stage_id, review_method, round, step, review_form_id, unconsidered) 
		VALUES (:insertReviewAssignment_submissionId, :insertReviewAssignment_reviewerId, :insertReviewAssignment_competingInterests, :insertReviewAssignment_regretMessage, 
		:insertReviewAssignment_recommendation, :insertReviewAssignment_dateAssigned, :insertReviewAssignment_dateNotified, :insertReviewAssignment_dateConfirmed, 
		:insertReviewAssignment_dateCompleted, :insertReviewAssignment_dateAcknowledged, :insertReviewAssignment_dateDue, :insertReviewAssignment_lastModified, 
		:insertReviewAssignment_reminderAuto, :insertReviewAssignment_declined, :insertReviewAssignment_replaced, :insertReviewAssignment_cancelled, 
		:insertReviewAssignment_reviewerFileId, :insertReviewAssignment_dateRated, :insertReviewAssignment_dateReminded, :insertReviewAssignment_quality, 
		:insertReviewAssignment_reviewRoundId, :insertReviewAssignment_stageId, :insertReviewAssignment_reviewMethod, :insertReviewAssignment_round, 
		:insertReviewAssignment_step, :insertReviewAssignment_reviewFormId, :insertReviewAssignment_unconsidered)',
		
	'params' => array(
		'submission_id' => ':insertReviewAssignment_submissionId',
		'reviewer_id' => ':insertReviewAssignment_reviewerId',
		'competing_interests' => ':insertReviewAssignment_competingInterests',
		'regret_message' => ':insertReviewAssignment_regretMessage'
		'recommendation' => ':insertReviewAssignment_recommendation',
		'date_assigned' => ':insertReviewAssignment_dateAssigned',
		'date_notified' => ':insertReviewAssignment_dateNotified',
		'date_confirmed' => ':insertReviewAssignment_dateConfirmed',
		'date_completed' => ':insertReviewAssignment_dateCompleted',
		'date_acknowledged' => ':insertReviewAssignment_dateAcknowledged',
		'date_due' => ':insertReviewAssignment_dateDue',
		'last_modified' => ':insertReviewAssignment_lastModified',
		'reminder_was_automatic' => ':insertReviewAssignment_reminderAuto',
		'declined' => ':insertReviewAssignment_declined',
		'replaced' => ':insertReviewAssignment_replaced',
		'cancelled' => ':insertReviewAssignment_cancelled',
		'reviewer_file_id' => ':insertReviewAssignment_reviewerFileId',
		'date_rated' => ':insertReviewAssignment_dateRated',
		'date_reminded' => ':insertReviewAssignment_dateReminded',
		'quality' =. ':insertReviewAssignment_quality',
		'review_round_id' => ':insertReviewAssignment_reviewRoundId',
		'stage_id' => ':insertReviewAssignment_stageId',
		'review_method' => ':insertReviewAssignment_reviewMethod',
		'round' => ':insertReviewAssignment_round',
		'step' => ':insertReviewAssignment_step',
		'review_form_id' => ':insertReviewAssignment_reviewFormId',
		'unconsidered' => ':insertReviewAssignment_unconsidered'
	)
);

$queries['insertReviewFormResponse'] = array(
	'query' => 'INSERT INTO review_form_responses (review_form_element_id, review_id, response_type, response_value) 
		VALUES (:insertReviewFormResponse_reviewFormElementId, :insertReviewFormResponse_reviewId, :insertReviewFormResponse_responseType, :insertReviewFormResponse_reponseValue)',
	
	'params' => array(
		'review_form_element_id' => ':insertReviewFormResponse_reviewFormElementId',
		'review_id' => ':insertReviewFormResponse_reviewId',
		'response_type' => ':insertReviewFormResponse_responseType',
		'response_value' => ':insertReviewFormResponse_reponseValue'
	)
);


//////////////////////////// END OF INSERT  /////////////////////////////////////////////////////////////////////////



///////////////////////////// UPDATE ////////////////////////////////////////////////////////////////////////////////
/////////// user related /////////////
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


/////////// article related ///////////
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
		submission_file_id = :updateArticle_filesIds_submissionFileId, revised_file_id = :updateArticle_filesIds_revisedFileId, 
		review_file_id = :updateArticle_filesIds_reviewFileId, editor_file_id = :updateArticle_filesIds_editorFileId 
		WHERE article_id = :updateArticle_filesIds_articleId',
		
	'params' => array(
		'submission_file_id' => ':updateArticle_filesIds_submissionFileId',
		'revised_file_id' => ':updateArticle_filesIds_revisedFileId', 
		'review_file_id' => ':updateArticle_filesIds_reviewFileId', 
		'editor_file_id' => ':updateArticle_filesIds_editorFileId', 
		'article_id' => ':updateArticle_filesIds_articleId'
	)
);

$queries['updateArticleSetting'] = array(
	'query' => 'UPDATE article_settings 
		SET setting_value = :updateArticleSetting_settingValue, setting_type = :updateArticleSetting_settingType
		WHERE article_id = :updateArticleSetting_articleId AND locale = :updateArticleSetting_locale AND setting_name = :updateArticleSetting_settingName',
		
	'params' => array(
		'article_id' => ':updateArticleSetting_articleId',
		'locale' => ':updateArticleSetting_locale',
		'setting_name' => ':updateArticleSetting_settingName',
		'setting_value' => ':updateArticleSetting_settingValue',
		'setting_type' => ':updateArticleSetting_settingType'
	)
);

$queries['updateArticleFile'] = array(
	'query' => 'UPDATE article_files 
		SET source_revision = :updateArticleFile_sourceRevision, file_stage = :updateArticleFile_fileStage , viewable = :updateArticleFile_viewable, 
			date_uploaded = :updateArticleFile_dateUploaded, date_modified = :updateArticleFile_dateModified, round = :updateArticleFile_round, assoc_id = :updateArticleFile_assocId
		WHERE file_id = :updateArticleFile_fileId AND revision = :updateArticleFile_revision',
		
	'params' => array(
		'source_revision' => ':updateArticleFile_sourceRevision',
		'file_stage' => ':updateArticleFile_fileStage',
		'viewable' => ':updateArticleFile_viewable',
		'date_uploaded' => ':updateArticleFile_dateUploaded',
		'date_modified' => ':updateArticleFile_dateModified',
		'round' => ':updateArticleFile_round',
		'assoc_id' => ':updateArticleFile_assocId',
		'file_id' => ':updateArticleFile_fileId',
		'revision' => ':updateArticleFile_revision'
	)
);

$queries['updateArticleFile_namesAndSourceId'] = array(
	'query' => 'UPDATE article_files SET source_file_id = :updateArticleFile_namesAndSourceIds_sourceFileId, 
		file_name = :updateArticleFile_namesAndSourceIds_fileName, original_file_name = :updateArticleFile_namesAndSourceIds_originalFileName 
		WHERE file_id = :updateArticleFile_namesAndSourceIds_fileId AND revision = :updateArticleFile_namesAndSourceIds_revision',
		
	'params' => array(
		'source_file_id' => ':updateArticleFile_namesAndSourceIds_sourceFileId', 
		'file_name' => ':updateArticleFile_namesAndSourceIds_fileName', 
		'original_file_name' => ':updateArticleFile_namesAndSourceIds_originalFileName',
		'file_id' => ':updateArticleFile_namesAndSourceIds_fileId',
		'revision' => ':updateArticleFile_namesAndSourceIds_revision'
	)
);

$queries['updateReviewRound'] = array(
	'query' => 'UPDATE review_rounds SET 
		stage_id = :updateReviewRound_stageId, round = :updateReviewRound_round, 
		review_revision = :updateReviewRound_reviewRevision, status = :updateReviewRound_status
		WHERE review_round_id = :updateReviewRound_reviewRoundId',
		
	'params' => array(
		'stage_id' => ':updateReviewRound_stageId', 
		'round' => ':updateReviewRound_round', 
		'review_revision' => ':updateReviewRound_reviewRevision', 
		'status' => ':updateReviewRound_status'
		'review_round_id' => ':updateReviewRound_reviewRoundId'
	)
);

$queries['updateReviewAssignment'] = array(
	'query' => 'UPDATE review_assignments SET
		competing_interests = :updateReviewAssignment_competingInterests, regret_message = :updateReviewAssignment_regretMessage, 
		recommendation = :updateReviewAssignment_recommendation, date_assigned = :updateReviewAssignment_dateAssigned, 
		date_notified = :updateReviewAssignment_dateNotified, date_confirmed = :updateReviewAssignment_dateConfirmed, 
		date_completed = :updateReviewAssignment_dateCompleted, date_acknowledged = :updateReviewAssignment_dateAcknowledged, 
		date_due = :updateReviewAssignment_dateDue, last_modified = :updateReviewAssignment_lastModified, reminder_was_automatic = :updateReviewAssignment_reminderWasAutomatic, 
		declined = :updateReviewAssignment_declined, replaced = :updateReviewAssignment_replaced, cancelled = :updateReviewAssignment_cancelled, 
		reviewer_file_id = :updateReviewAssignment_reviewerFileId, date_rated = :updateReviewAssignment_dateRated, date_reminded = :updateReviewAssignment_dateReminded, 
		quality = :updateReviewAssignment_quality, review_round_id = :updateReviewAssignment_reviewRoundId, stage_id = :updateReviewAssignment_stageId,
		review_method = :updateReviewAssignment_reviewMethod, round = :updateReviewAssignment_round, step = :updateReviewAssignment_step, 
		review_form_id = :updateReviewAssignment_reviewFormId, unconsidered = :updateReviewAssignment_unconsidered
		WHERE review_id = :updateReviewAssignment_reviewId',
	
	'params' => array(
		'competing_interests' => ':updateReviewAssignment_competingInterests',
		'regret_message' => ':updateReviewAssignment_regretMessage', 
		'recommendation' => ':updateReviewAssignment_recommendation',
		'date_assigned' => ':updateReviewAssignment_dateAssigned', 
		'date_notified' => ':updateReviewAssignment_dateNotified', 
		'date_confirmed' => ':updateReviewAssignment_dateConfirmed', 
		'date_completed' => ':updateReviewAssignment_dateCompleted', 
		'date_acknowledged' => ':updateReviewAssignment_dateAcknowledged', 
		'date_due' => ':updateReviewAssignment_dateDue', 
		'last_modified' => ':updateReviewAssignment_lastModified', 
		'reminder_was_automatic' => ':updateReviewAssignment_reminderWasAutomatic', 
		'declined' => ':updateReviewAssignment_declined', 
		'replaced' => ':updateReviewAssignment_replaced',
		'cancelled' => ':updateReviewAssignment_cancelled', 
		'reviewer_file_id' => ':updateReviewAssignment_reviewerFileId',
		'date_rated' => ':updateReviewAssignment_dateRated', 
		'date_reminded' => ':updateReviewAssignment_dateReminded', 
		'quality' => ':updateReviewAssignment_quality', 
		'review_round_id' => ':updateReviewAssignment_reviewRoundId', 
		'stage_id' => ':updateReviewAssignment_stageId',
		'review_method' => ':updateReviewAssignment_reviewMethod', 
		'round' => ':updateReviewAssignment_round', 
		'step' => ':updateReviewAssignment_step', 
		'review_form_id' => ':updateReviewAssignment_reviewFormId', 
		'unconsidered' => ':updateReviewAssignment_unconsidered',
		'review_id' => ':updateReviewAssignment_reviewId'
	)
);


////// review_forms related //////
$queries['updateReviewForm'] = array(
	'query' => 'UPDATE review_forms SET 
		seq = :updateReviewForm_seq, is_active = :updateReviewForm_isActive
		WHERE review_form_id = :updateReviewForm_reviewFormId',
		
	'params' => array(
		'seq' => ':updateReviewForm_seq',
		'is_active' => ':updateReviewForm_isActive',
		'review_form_id' => ':updateReviewForm_reviewFormId' 
	)
);

$queries['updateReviewFormElement'] = array (
	'query' => 'UPDATE review_form_elements SET
		seq = :updateReviewFormElement_seq, element_type = :updateReviewFormElement_elementType,
		required = :updateReviewFormElement_required, included = :updateReviewFormElement_included
		WHERE review_form_element = :updateReviewFormElement_reviewFormElementId',
		
	'params' => array(
		'seq' => ':updateReviewFormElement_seq', 
		'element_type' => ':updateReviewFormElement_elementType',
		'required' => ':updateReviewFormElement_required', 
		'included' => ':updateReviewFormElement_included'
		'review_form_element' => ':updateReviewFormElement_reviewFormElementId'
	)
);


//////////////////////////////// END OF UPDATE /////////////////////////////////////////////////////////////

// $conn has to be set
if (!isset($conn)) {
	//set the connection
}

//set the statements
$statements = array();

function createStatement(&$conn, &$stmts, $statementName, &$queriesArray) {
	
	if (!array_key_exists($statementName, $queriesArray)) {
		return false;
	}
	
	if (!array_key_exists($statementName, $stmts)) {
		$stmts[$statementName] = $conn->prepare($queriesArray[$statementName]['query']);
	}
	
	return true;
}

