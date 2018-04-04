<?php
/**

This is a library with functions for getting data from the OJS database and store in a .xml file

FUNCTIONS DEFINED IN THIS SCRIPT

00) fetchUser
01) fetchSections
02) fetchUnpublishedArticles
03) fetchAnnouncements
04) fetchEmailTemplates
05) fetchGroups
06) fetchReviewForms
07) fetchCitations
08) fetchReferrals
09) fetchHistory
10) fetchPluginSettings
11) fetchSectionAndIssueOrders

Yet to fetch:

////////  User related ///////////
user_interests
controlled_vocab_entries
controlled_vocab_entry_settings
controlled_vocabs
//////////////////////////////////

///////  Article related /////////
event_log
event_log_settings
email_log
email_log_users

citations
citation_settings

referrals
referral_settings
//////////////////////////////////

//////// Journal related /////////
custom_issue_orders
custom_section_orders

plugin_settings
//////////////////////////////////

Developed in 2017 by Bernardo Amado
*/

include_once('helperFunctions.php');
include_once('appFunctions.php');


// #00)

function fetchUser($conn, $userId, $journal = null, $args = null) {
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php
	}
	
	$collations = null;
	$verbose = null;
	
	if (is_array($args)) {
		if (array_key_exists('collations', $args)) {
			$collations = $args['collations'];
		}
		if (array_key_exists('verbose', $args)) {
			$verbose = $args['verbose'];	
		}
	}
	
	
	$userSTMT = $conn->prepare('SELECT * FROM users WHERE user_id = :userId');
	$userSettingsSTMT = $conn->prepare('SELECT * FROM user_settings WHERE user_id = :userSettings_userId');
	$rolesSTMT = $conn->prepare('SELECT * FROM roles WHERE journal_id = :roles_journalId AND user_id = :roles_userId');
	$rolesSTMT->bindParam(':roles_journalId', $journal['journal_id'], PDO::PARAM_INT);
	
	
	/////////////  set the user info /////////////////////////////////////
					
	$errorOccurred = false;
	$error = array();
	$user = null;
	
	$userSTMT->bindParam(':userId', $userId, PDO::PARAM_INT);
	if ($userSTMT->execute()) {
		$user = $userSTMT->fetch(PDO::FETCH_ASSOC);
		
		processCollation($user, 'users', $collations);
		
		//fetching the user settings
		$userSettingsSTMT->bindParam(':userSettings_userId', $user['user_id'], PDO::PARAM_INT);
		if ($userSettingsSTMT->execute()) {
			$userSettings = array();
			while ($setting = $userSettingsSTMT->fetch(PDO::FETCH_ASSOC)) {
				array_push($userSettings, $setting);
			}
			
			processCollation($userSettings, 'user_settings', $collations);
			
			$user['settings'] = $userSettings;
		}// end of the if userSettingsSTMT executed
		else {
			$errorOccurred = true;
			$error['userSettingsError'] = $userSettingsSTMT->errorInfo();
		}
		
		//fetching the user roles for this journal
		$rolesSTMT->bindParam(':roles_userId', $user['user_id'], PDO::PARAM_INT);
		if ($rolesSTMT->execute()) {
			$roles = array();
			while ($role = $rolesSTMT->fetch(PDO::FETCH_ASSOC)) {
				array_push($roles, $role);
			}
			
			processCollation($roles, 'roles', $collations);
			
			$user['roles'] = $roles;
		}// end of the if rolesSTMT executed
		else {
			$errorOccurred = true;
			$error['rolesError'] = $rolesSTMT->errorInfo();
		}
		
		
		if ($errorOccurred) {
			$error['user'] = $user;
		}
		
		
		
	}// end of the if userSTMT executed
	else {
		$errorOccurred = true;
		$error['userError'] = $userSTMT->errorInfo();
	}
	
	return array('user' => $user, 'error' => $error, 'errorOccurred' => $errorOccurred);
	
}


// #01)

function fetchSections($conn, $journal = null, $args = null) {
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php
	}
	
	$collations = null;
	$verbose = null;
	
	if (is_array($args)) {
		if (array_key_exists('collations', $args)) {
			$collations = $args['collations'];
		}
		if (array_key_exists('verbose', $args)) {
			$verbose = $args['verbose'];	
		}
	}
	
	$sections = array();
	
	$sectionsSTMT = $conn->prepare('SELECT * FROM sections WHERE journal_id = :sections_journalId');
	$sectionSettingsSTMT = $conn->prepare("SELECT * FROM section_settings WHERE section_id = :sectionSettings_sectionId");
	
	$sectionsSTMT->bindParam(":sections_journalId", $journal["journal_id"], PDO::PARAM_INT);
	
	$errors = array(
		"sections" => array(),
		"section_settings" => array(),
	);
	
	if ($verbose) echo "\n\nFetching the sections...\n\n";
	
	if ($sectionsSTMT->execute()) {
		while ($section = $sectionsSTMT->fetch(PDO::FETCH_ASSOC)) {
			
			processCollation($section, 'sections', $collations);
			
			$abbrevs = array();
			$titles = array();
			
			//////////  section settings  ///////////////////////////////
			$settings = array();
			$sectionSettingsSTMT->bindParam(':sectionSettings_sectionId', $section['section_id'], PDO::PARAM_INT);
			
			if ($verbose) echo "\nFetching the section #" . $section['section_id'] . " settings ........ ";
			
			if ($sectionSettingsSTMT->execute()) {
				while ($setting = $sectionSettingsSTMT->fetch(PDO::FETCH_ASSOC)) {
					
					/*//getting the abbrevs and titles apart from the settings
					if ($setting["setting_name"] === "title" || $setting["setting_name"] === "abbrev") {
						
						$data = $title = array("locale" => $setting["locale"], "value" => $setting["setting_value"]);
						
						switch($setting["setting_name"]) {
							case "title": 
								array_push($titles, $data);
								break;
							
							case "abbrev":  
								array_push($abbrevs, $data);
								break;
						}//end of the switch
					}
					////////////////////////////////////////////////////////*/
					
					array_push($settings, $setting);
				}
				if ($verbose) echo "Ok\n";
			}
			else {
				if ($verbose) echo "Error\n";
				$error = array('section_id' => $section['section_id'], 'error' => $sectionSettingsSTMT->errorInfo());
				array_push($errors['section_settings'], $error);
			}
			
			processCollation($settings, 'section_settings', $collations);
			
			$section['settings'] = $settings;
			/////////////////////////////////////////////////////////////
			
			array_push($sections, $section);
		}
	}
	else {
		array_push($errors['sections'], $sectionsSTMT->errorInfo());
	}
	
	echo "\nFetched " . count($sections) . " sections.\n";
	
	return array('sections' => $sections, 'errors' => $errors);
}


// #02)
/**
fetch the unpublished articles of the specified journal
*/
function fetchUnpublishedArticles($conn, $journal, $args = null) {
	
	$getKeywords = false;
	$verbose = false;
	$limitDate = '2015-01-01 00:00:00';
	$numArticles = 0;
	$numErrors = 0;
	$collations = null;
	
	if (is_array($args)) {
		if (array_key_exists('limitDate', $args)) {
			$limitDate = $args['limitDate'];
		}
		if (array_key_exists('getKeywords', $args)) {
			$getKeywords = $args['getKeywords'];
		}
		if (array_key_exists('verbose', $args)) {
			$verbose = $args['verbose'];	
		}
		if (array_key_exists('collations', $args)) {
			$collations = $args['collations'];
		}
	}
	
	$journalId = 0;
	
	if (is_array($journal)) {
		$journalId = filter_var($journal['journal_id'], FILTER_VALIDATE_INT);
	}
	else {
		return false;
	}
	
	$unpubArt = array();

	$stmt = $conn->prepare(
	'SELECT * FROM articles WHERE article_id IN (
		SELECT article_id FROM articles WHERE article_id NOT IN (
			SELECT article_id FROM published_articles
		) AND date_submitted > :limitDate AND journal_id = :journalId
	)');
	
	$stmt->bindParam(':journalId', $journalId, PDO::PARAM_INT);
	$stmt->bindParam(':limitDate', $limitDate, PDO::PARAM_STR);
	
	//////////////////// PART 1 /////////////////////////////////////////////
	//////// informations needed to identify the user and the section ///////
	$userSTMT = $conn->prepare('SELECT * FROM users WHERE user_id = :userId');
	$userSettingsSTMT = $conn->prepare('SELECT * FROM user_settings WHERE user_id = :userSettings_userId');
	$rolesSTMT = $conn->prepare('SELECT * FROM roles WHERE journal_id = :roles_journalId AND user_id = :roles_userId');
	$rolesSTMT->bindParam(':roles_journalId', $journalId, PDO::PARAM_INT);
	$sectionSTMT = $conn->prepare('SELECT section_id, setting_name, setting_value, locale FROM section_settings WHERE section_id = :sectionId AND setting_name IN ("title", "abbrev")');
	/////////////////////////////////////////////////////////////////////////
	
	$authorSTMT = $conn->prepare('SELECT * FROM authors WHERE submission_id = :author_submissionId');
	$authorSettingsSTMT = $conn->prepare('SELECT * FROM author_settings WHERE author_id = :authorSettings_authorId');
	
	/////////////////////  PART 2  //////////////////////////////////////////////////////////
	/////////////////////  the articles informations  ///////////////////////////////////////
	$articleSettingsSTMT = $conn->prepare('SELECT * FROM article_settings WHERE article_id = :settings_articleId');
	$articleFilesSTMT = $conn->prepare('SELECT * FROM article_files WHERE article_id = :files_articleId');
	$articleSuppFilesSTMT = $conn->prepare('SELECT * FROM article_supplementary_files WHERE article_id = :supp_files_articleId');
	$articleSuppFileSettingsSTMT = $conn->prepare('SELECT * FROM article_supp_file_settings WHERE supp_id = :suppId');
	//$articleNotesSTMT = $conn->prepare('SELECT * FROM article_notes WHERE article_id = :notes_articleId');
	$articleCommentsSTMT = $conn->prepare('SELECT * FROM article_comments WHERE article_id = :comments_articleId');
	/////////////////////////////////////////////////////////////////////////////////////////
	
	//////////////////  PART 3  /////////////////////////////////////////////////////////////
	/////////////////  the galleys informations  ////////////////////////////////////////////
	$articleGalleysSTMT = $conn->prepare('SELECT * FROM article_galleys WHERE article_id = :art_galleys_articleId');
	$articleGalleySettingsSTMT = $conn->prepare('SELECT * FROM article_galley_settings WHERE galley_id = :galleyId');
	$articleXmlGalleysSTMT = $conn->prepare('SELECT * FROM article_xml_galleys WHERE galley_id = :xml_galleyId AND article_id = :xml_galley_articleId');
	$articleHtmlGalleyImagesSTMT = $conn->prepare('SELECT * FROM article_html_galley_images WHERE galley_id = :html_image_galleyId');
	//////////////////////////////////////////////////////////////////////////////////////
	
	/////////////////  PART 4  //////////////////////////////////////////////////////////////
	/////////////////  the article search keywords  ////////////////////////////////////////
	$articleSearchKeywordListSTMT = $conn->prepare('SELECT * FROM article_search_keyword_list WHERE keyword_id = :keywordId');
	$articleSearchObjectKeywordsSTMT = $conn->prepare('SELECT * FROM article_search_object_keywords WHERE object_id = :objectId');
	$articleSearchObjectsSTMT = $conn->prepare('SELECT * FROM article_search_objects WHERE article_id = :search_objects_articleId');
	/////////////////////////////////////////////////////////////////////////////////////////
	
	////////////////  PART 5  //////////////////////////////////////////////////////////////
	//////////////// the edit decisions and assignments  ///////////////////////////////////
	$editDecisionsSTMT = $conn->prepare('SELECT * FROM edit_decisions WHERE article_id = :edit_decisions_articleId');
	$editAssignmentsSTMT = $conn->prepare('SELECT * FROM edit_assignments WHERE article_id = :edit_assignments_articleId');
	////////////////////////////////////////////////////////////////////////////////////////
	
	////////////////  PART 6 //////////////////////////////////////////////////////////////
	/////////////// the reviews  //////////////////////////////////////////////////////////
	$reviewAssignmentsSTMT = $conn->prepare('SELECT * FROM review_assignments WHERE submission_id = :revAssign_submissionId');
	$reviewRoundsSTMT = $conn->prepare('SELECT * FROM review_rounds WHERE submission_id = :revRounds_submissionId');
	$reviewFormResponsesSTMT = $conn->prepare('SELECT * FROM review_form_responses WHERE review_id = :rfResponses_reviewId');
	///////////////////////////////////////////////////////////////////////////////////////
	
	/*
	///////////////  PART 7  //////////////////////////////////////////////////////////////
	///////////// the logs ////////////////////////////////////////////////////////////////
	$eventLogsSTMT = $conn->prepare('SELECT * FROM event_log WHERE assoc_id = :eventLog_assocId'); //the assoc_id is the article_id
	$eventLogSettingsSTMT = $conn->prepare('SELECT * FROM event_log_settings WHERE log_id = :eventLogSettings_logId');
	$emailLogsSTMT = $conn->prepare('SELECT * FROM email_log WHERE assoc_id = :emailLog_assocId'); //the assoc_id is the article_id
	$emailLogUsersSTMT;
	///////////////////////////////////////////////////////////////////////////////////////
	*/
	
	$errors = array(
		'articles' => array(),
		'article_settings' => array(),
		'article_files' => array(),
		'article_supplementary_files' => array(),
		'article_comments' => array(),
		'article_galleys' => array(),
		'article_galley_settings' => array(),
		'article_xml_galleys' => array(),
		'article_html_galley_images' => array(),
		'article_search_objects' => array(),
		'article_search_object_keywords' => array(),
		'article_search_keyword_list' => array(),
		'edit_decisions' => array(),
		'edit_assignments' => array(),
		'review_assignments' => array(),
		'review_rounds' => array(),
		'review_form_responses' => array(),
		'authors' => array(),
		'users' => array(),
		'reviewers' => array(),
		'editors' => array(),
		'sections' => array()
	);
	
	if ($verbose) echo "\nFetching the unpublished articles...\n"; 
	
	//////////////  FETCHING THE ARTICLES  //////////////////////////////////////////////////////////////////////////////////
	if ($stmt->execute()) {
		
		while ($article = $stmt->fetch(PDO::FETCH_ASSOC)) {
			
			processCollation($article, 'articles', $collations);
			
			if ($verbose) echo "\nArticle #".$article['article_id'].":\n";
			
			
			/////////  PART 1  /////////////////////////////////////////////////////
			if ($verbose) echo 'fetching user info... ';
			
			/////////////  set the user info /////////////////////////////////////
					
			$errorOccurred = false;
			$error = array('article_id' => $article['article_id']);
			
			$userSTMT->bindParam(':userId', $article['user_id'], PDO::PARAM_INT);
			if ($userSTMT->execute()) {
				$user = $userSTMT->fetch(PDO::FETCH_ASSOC);
				
				if (!array_key_exists('user_id', $user) || $user['user_id'] === null) {
					continue;
					//if could not fetch the user should not fetch the article
				}
				
				processCollation($user, 'users', $collations);
				
				//fetching the user settings
				$userSettingsSTMT->bindParam(':userSettings_userId', $user['user_id'], PDO::PARAM_INT);
				if ($userSettingsSTMT->execute()) {
					$userSettings = array();
					while ($setting = $userSettingsSTMT->fetch(PDO::FETCH_ASSOC)) {
						array_push($userSettings, $setting);
					}
					
					processCollation($userSettings, 'user_settings', $collations);
					
					$user['settings'] = $userSettings;
				}// end of the if userSettingsSTMT executed
				else {
					$errorOccurred = true;
					$error['userSettingsError'] = $userSettingsSTMT->errorInfo();
				}
				
				//fetching the user roles for this journal
				$rolesSTMT->bindParam(':roles_userId', $user['user_id'], PDO::PARAM_INT);
				if ($rolesSTMT->execute()) {
					$roles = array();
					while ($role = $rolesSTMT->fetch(PDO::FETCH_ASSOC)) {
						array_push($roles, $role);
					}
					
					processCollation($roles, 'roles', $collations);
					
					$user['roles'] = $roles;
				}// end of the if rolesSTMT executed
				else {
					$errorOccurred = true;
					$error['rolesError'] = $rolesSTMT->errorInfo();
				}
				
				$article['user'] = $user;
				
				if ($errorOccurred) {
					$error['user'] = $user;
				}
				else if ($verbose) {
					echo "Ok\n";
				}
				
			}// end of the if userSTMT executed
			else {
				$errorOccurred = true;
				$error['userError'] = $userSTMT->errorInfo();
			}
			
			if ($errorOccurred) {
				if ($verbose) echo "Error\n";
				array_push($errors['users'], $error);
				$numErrors++;
			}
			/////// end of set the user info  ////////////////////////////////
	
			
			//set the section info
			if ($verbose) echo 'fetching section info... ';
			$sectionSTMT->bindParam(':sectionId', $article['section_id'], PDO::PARAM_INT);
			if ($sectionSTMT->execute()) {
				$section = array();
				$section['original_id'] = $article['section_id'];
				while ($sectionInfo = $sectionSTMT->fetch(PDO::FETCH_ASSOC)) {
					$locale = $sectionInfo['locale'];
					if (array_key_exists($locale, $section)) {
						$section[$locale][$sectionInfo['setting_name']] = $sectionInfo['setting_value'];	
					}
					else {
						$section[$locale] = array($sectionInfo['setting_name'] => $sectionInfo['setting_value']);
					}
				}
				$article['section'] = $section;
				if ($verbose) echo "Ok\n";
			}
			else {
				if ($verbose) echo "Error\n";
				$error = array('article_id' => $article['article_id'], 'section_id' => $article['section_id'], 'error' => $sectionSTMT->errorInfo());
				array_push($errors['sections'], $error);
				$numErrors++;
			}
			
			//set the authors info
			if ($verbose) echo 'fetching authors... ';
			$authors = array();
			$authorSTMT->bindParam(':author_submissionId', $article['article_id'], PDO::PARAM_INT);
			if ($authorSTMT->execute()) {
				while ($author = $authorSTMT->fetch(PDO::FETCH_ASSOC)) {
					
					processCollation($author, 'authors', $collations);
					
					$authorSettingsSTMT->bindParam(':authorSettings_authorId', $author['author_id'], PDO::PARAM_INT);
					if ($authorSettingsSTMT->execute()) {
						//fetching the author settings
						$authorSettings = array();
						while ($setting = $authorSettingsSTMT->fetch(PDO::FETCH_ASSOC)) {
							array_push($authorSettings, $setting);
						}
						processCollation($authorSettings, 'author_settings', $collations);
						$author['settings'] = $authorSettings;
					}
					array_push($authors, $author);
				}
				$article['authors'] = $authors;
				if ($verbose) echo "Ok\n";
			}
			else {
				if ($verbose) echo "Error\n";
				$error = array('article_id' => $article['article_id'], 'authors' => $authors, 'error' => $authorSTMT->errorInfo());
				array_push($errors['authors'], $error);
				$numErrors++;
			}
			///////////  END OF PART 1  /////////////////////////////////////////////
			
			
			///////////  PART 2  ////////////////////////////////////////////////////////
			if ($verbose) echo 'fetching info (settings, files, supplementary files, notes and comments)... '; 
			
			$errorOccurred = false;
			
			//set the article_settings 
			$articleSettingsSTMT->bindParam(':settings_articleId', $article['article_id'], PDO::PARAM_INT);
			if ($articleSettingsSTMT->execute()) {
				$articleSettings = array();
				while ($setting = $articleSettingsSTMT->fetch(PDO::FETCH_ASSOC)) {
					array_push($articleSettings, $setting);
				}
				
				processCollation($articleSettings, 'article_settings', $collations);
				
				$article['settings'] = $articleSettings;
			}
			else {
				$errorOccurred = true;
				$error = array('article_id' => $article['article_id'],  'error' => $articleSettingsSTMT->errorInfo());
				array_push($errors['article_settings'], $error);
				$numErrors++;
			}
			
			//set the article_files 
			$articleFilesSTMT->bindParam(':files_articleId', $article['article_id'], PDO::PARAM_INT);
			if ($articleFilesSTMT->execute()) {
				$articleFiles = array();
				while ($artFile = $articleFilesSTMT->fetch(PDO::FETCH_ASSOC)) {
					array_push($articleFiles, $artFile);
				}
				
				processCollation($articleFiles, 'article_files', $collations);
				
				$article['files'] = $articleFiles;
			}
			else {
				$errorOccurred = true;
				$error = array('article_id' => $article['article_id'],  'error' => $articleFilesSTMT->errorInfo());
				array_push($errors['article_files'], $error);
				$numErrors++;
			}
			
			
			//set the article_supplementary_files
			$articleSuppFilesSTMT->bindParam(':supp_files_articleId', $article['article_id'], PDO::PARAM_INT);
			if ($articleSuppFilesSTMT->execute()) {
				$articleSuppFiles = array();
				while($artSuppFile = $articleSuppFilesSTMT->fetch(PDO::FETCH_ASSOC)) {
					
					processCollation($artSuppFile, 'article_supplementary_files', $collations);
					
					///// set the article_supp_file_settings //////////////
					$articleSuppFileSettingsSTMT->bindParam(':suppId', $artSuppFile['supp_id'], PDO::PARAM_INT);
					if ($articleSuppFileSettingsSTMT->execute()) {
						$suppFileSettings = array();
						while($setting = $articleSuppFileSettingsSTMT->fetch(PDO::FETCH_ASSOC)) {
							array_push($suppFileSettings, $setting);
						}
						
						processCollation($suppFileSettings, 'article_supp_file_settings', $collations);
						
						$artSuppFile['settings'] = $suppFileSettings;
					}
					//////////////////////////////////////////////////////////////
					
					array_push($articleSuppFiles, $artSuppFile);
				}
				$article['supplementary_files'] = $articleSuppFiles;
			}
			else {
				$errorOccurred = true;
				$error = array('article_id' => $article['article_id'],  'error' => $articleSuppFilesSTMT->errorInfo());
				array_push($errors['article_supplementary_files'], $error);
				$numErrors++;
			}
			
			//set the article_comments
			$articleCommentsSTMT->bindParam(':comments_articleId', $article['article_id'], PDO::PARAM_INT);
			if ($articleCommentsSTMT->execute()) {
				$articleComments = array();
				while($artComment = $articleCommentsSTMT->fetch(PDO::FETCH_ASSOC)) {
					
					processCollation($artComment, 'article_comments', $collations);
					
					$userSTMT->bindParam(':userId', $artComment['author_id'], PDO::PARAM_INT);
					if ($userSTMT->execute()) {
						$author = $userSTMT->fetch(PDO::FETCH_ASSOC);
						
						processCollation($author, 'users', $collations);
						
						$artComment['author'] = $author;
					}
					array_push($articleComments, $artComment);
				}
				$article['comments'] = $articleComments;
			}
			else {
				$errorOccurred = true;
				$error = array('article_id' => $article['article_id'],  'error' => $articleCommentsSTMT->errorInfo());
				array_push($errors['article_comments'], $error);
				$numErrors++;
			}
			
			if ($verbose) echo ($errorOccurred) ? "Error\n" : "Ok\n";
			/////////// END OF PART 2  ////////////////////////////////////////////////////////////
			
			
			///////////  PART 3  //////////////////////////////////////////////////////////////////
			if ($verbose) echo 'fetching galleys info (galleys, settings, xml_galleys and html_galley_images) ... ';
			
			$errorOccurred = false;
			
			//set the article_galleys
			$articleGalleysSTMT->bindParam(':art_galleys_articleId', $article['article_id'], PDO::PARAM_INT);
			if ($articleGalleysSTMT->execute()) {
				$articleGalleys = array();
				while ($artGalley = $articleGalleysSTMT->fetch(PDO::FETCH_ASSOC)) {
					
					processCollation($artGalley, 'article_galleys', $collations);
					
					////////// set the article_galley_settings   ///////////////////
					$articleGalleySettingsSTMT->bindParam(':galleyId', $artGalley['galley_id'], PDO::PARAM_INT);
					if ($articleGalleySettingsSTMT->execute()) {
						$galleySettings = array();
						while($setting = $articleGalleySettingsSTMT->fetch(PDO::FETCH_ASSOC)) {
							array_push($galleySettings, $setting);
						}
						
						processCollation($galleySettings, 'article_galley_settings', $collations);
						
						$artGalley['settings'] = $galleySettings;
					}
					else {
						$errorOccurred = true;
						$error = array('article_id' => $article['article_id'], 'galley_id' => $artGalley['galley_id'], 'error' => $articleGalleySettingsSTMT->errorInfo());
						array_push($errors['article_galley_settings'], $error);
						$numErrors++;
					}
					////////////////////////////////////////////////////////////////
					
					///////// set the article_xml_galleys  /////////////////////////
					$articleXmlGalleysSTMT->bindParam(':xml_galleyId', $artGalley['galley_id'], PDO::PARAM_INT);
					$articleXmlGalleysSTMT->bindParam(':xml_galley_articleId', $article['article_id'], PDO::PARAM_INT);
					if ($articleXmlGalleysSTMT->execute()) {
						$xmlGalleys = array();
						while($galley = $articleXmlGalleysSTMT->fetch(PDO::FETCH_ASSOC)) {
							array_push($xmlGalleys, $galley);
						}
						
						processCollation($xmlGalleys, 'article_xml_galleys', $collations);
						
						$artGalley['xml_galleys'] = $xmlGalleys;
					}
					else {
						$errorOccurred = true;
						$error = array('article_id' => $article['article_id'], 'galley_id' => $artGalley['galley_id'], 'error' => $articleXmlGalleysSTMT->errorInfo());
						array_push($errors['article_xml_galleys'], $error);
						$numErrors++;
					}
					////////////////////////////////////////////////////////////////
					
					//////// set the article_html_galley_images  ////////////////////
					$articleHtmlGalleyImagesSTMT->bindParam(':html_image_galleyId', $artGalley['galley_id'], PDO::PARAM_INT);
					if ($articleHtmlGalleyImagesSTMT->execute()) {
						$htmlGalleyImages = array();
						while($image = $articleHtmlGalleyImagesSTMT->fetch(PDO::FETCH_ASSOC)) {
							array_push($htmlGalleyImages, $image);
						}
						
						//article_html_galley_images has all fields as integers
						
						$artGalley['html_galley_images'] = $htmlGalleyImages;
					}
					else {
						$errorOccurred = true;
						$error = array('article_id' => $article['article_id'], 'galley_id' => $artGalley['galley_id'], 'error' => $articleHtmlGalleyImagesSTMT->errorInfo());
						array_push($errors['article_html_galley_images'], $error);
						$numErrors++;
					}
					///////////////////////////////////////////////////////////////
					
					array_push($articleGalleys, $artGalley);
				}
				$article['galleys'] = $articleGalleys;
			}
			else {
				$errorOccurred = true;
				$error = array('article_id' => $article['article_id'], 'galley_id' => $artGalley['galley_id'], 'error' => $articleGalleysSTMT->errorInfo());
				array_push($errors['article_galleys'], $error);
				$numErrors++;
			}
			
			if ($verbose) echo ($errorOccurred) ? "Error\n" : "Ok\n";
			//////////  END OF PART 3  //////////////////////////////////////////////////////////////
			
			
			//////////  PART 4  /////////////////////////////////////////////////////////////////////
			if ($getKeywords) {
				
				$errorOccurred = false;
				
				if ($verbose) echo 'fetching the keywords... ';
				
				//set the article_search_objects
				$articleSearchObjectsSTMT->bindParam(':search_objects_articleId', $article['article_id'], PDO::PARAM_INT);
				if ($articleSearchObjectsSTMT->execute()) {
					$searchObjects = array();
					while($obj = $articleSearchObjectsSTMT->fetch(PDO::FETCH_ASSOC)) {
						
						//article_search_objects has all fields as integers
						
						//set the article_search_object_keywords ///////////////
						$articleSearchObjectKeywordsSTMT->bindParam(':objectId', $obj['object_id'], PDO::PARAM_INT);
						if ($articleSearchObjectKeywordsSTMT->execute()) {
							$searchObjectKeywords = array();
							while($keyword = $articleSearchObjectKeywordsSTMT->fetch(PDO::FETCH_ASSOC)) {
								
								//article_search_object_keywords has all the fields as integers
								
								//set the article_search_keyword_list//////
								$articleSearchKeywordListSTMT->bindParam(':keywordId', $keyword['keyword_id'], PDO::PARAM_INT);
								if ($articleSearchKeywordListSTMT->execute()) {
									$keywordList = $articleSearchKeywordListSTMT->fetch(PDO::FETCH_ASSOC);
									
									processCollation($keywordList, 'article_search_keyword_list', $collations);
									
									$keyword['keyword_list'] = $keywordList;
								}
								else {
									$errorOccurred = true;
									$error = array('article_id' => $article['article_id'], 'keyword' => $keyword, 'error' => $articleSearchKeywordListSTMT->errorInfo());
									array_push($errors['article_search_keyword_list'], $error);
									$numErrors++;
								}
								///////////////////////////////////////////
								
								array_push($searchObjectKeywords, $keyword);
							}
							$obj['keywords'] = $searchObjectKeywords;
						}
						else {
							$errorOccurred = true;
							$error = array('article_id' => $article['article_id'], 'object' => $object, 'error' => $articleSearchObjectKeywordsSTMT->errorInfo());
							array_push($errors['article_search_object_keywords'], $error);
							$numErrors++;
						}
						////////////////////////////////////////////////////////
						array_push($searchObjects, $obj);
					}
					$article['search_objects'] = $searchObjects;
				}
				else {
					$errorOccurred = true;
					$error = array('article_id' => $article['article_id'],  'error' => $articleSearchObjectsSTMT->errorInfo());
					array_push($errors['article_search_objects'], $error);
					$numErrors++;
				}
				
				if ($verbose) echo ($errorOccurred) ? "Error\n" : "Ok\n";
			}
			/////////  END OF PART 4  ///////////////////////////////////////////////////////////////
			
			
			/////////  PART 5  //////////////////////////////////////////////////////////////////////
			if ($verbose) echo 'fetching edit decisions and assignments... ';
			
			$errorOccurred = false;
			
			//set the edit_decisions
			$editDecisionsSTMT->bindParam(':edit_decisions_articleId', $article['article_id'], PDO::PARAM_INT);
			if ($editDecisionsSTMT->execute()) {
				$editDecisions = array();
				while($editDec = $editDecisionsSTMT->fetch(PDO::FETCH_ASSOC)) {
					
					//all edit_decisions fields are either integers or datetime (does not need to process collations)
					
					$userSTMT->bindParam(':userId', $editDec['editor_id'], PDO::PARAM_INT);
					if ($userSTMT->execute()) {
						$editor = $userSTMT->fetch(PDO::FETCH_ASSOC);
						
						processCollation($editor, 'users', $collations);
						
						$editDec['editor'] = $editor;
					}
					else {
						$errorOccurred = true;
						$error = array('article_id' => $article['article_id'], 'editor_id' => $editDec['editor_id'], 'error' => $userSTMT->errorInfo());
						array_push($errors['editors'], $error);
						$numErrors++;
					}
					
					array_push($editDecisions, $editDec);
				}
				$article['edit_decisions'] = $editDecisions;
			}
			else {
				$errorOccurred = true;
				$error = array('article_id' => $article['article_id'], 'error' => $editDecisionsSTMT->errorInfo());
				array_push($errors['edit_decisions'], $error);
				$numErrors++;
			}
			
			//set the edit_assignments
			$editAssignmentsSTMT->bindParam(':edit_assignments_articleId', $article['article_id'], PDO::PARAM_INT);
			if ($editAssignmentsSTMT->execute()) {
				$editAssignments = array();
				while($editAssign = $editAssignmentsSTMT->fetch(PDO::FETCH_ASSOC)) {
					
					//all edit_assignments fields are either integers or datetime (does not need to process collations)
					
					$userSTMT->bindParam(':userId', $editAssign['editor_id'], PDO::PARAM_INT);
					if ($userSTMT->execute()) {
						$editor = $userSTMT->fetch(PDO::FETCH_ASSOC);
						
						processCollation($editor, 'users', $collations);
						
						$editAssign['editor'] = $editor;
					}
					else {
						$errorOccurred = true;
						$error = array('article_id' => $article['article_id'], 'editor_id' => $editAssign['editor_id'], 'error' => $userSTMT->errorInfo());
						array_push($errors['editors'], $error);
						$numErrors++;
					}
					
					array_push($editAssignments, $editAssign);
				}
				$article['edit_assignments'] = $editAssignments;
			}
			else {
				$errorOccurred = true;
				$error = array('article_id' => $article['article_id'], 'error' => $editAssignmentsSTMT->errorInfo());
				array_push($errors['edit_decisions'], $error);
				$numErrors++;
			}
			
			if ($verbose) echo ($errorOccurred) ? "Error\n" : "Ok\n";
			/////////  END OF PART 5  ///////////////////////////////////////////////////////////////
			
			////////  PART 6 THE REVIEWS  ///////////////////////////////////////////////////////////////////////
			
			if ($verbose) echo "\nFetching the reviews (assignments, form responses and rounds) ...";
			
			$errorOccurred = false;
			
			$reviewAssignmentsSTMT->bindParam(":revAssign_submissionId", $article["article_id"], PDO::PARAM_INT);
			if ($reviewAssignmentsSTMT->execute()) {
				$reviewAssignments = array();
				while($reviewAssign = $reviewAssignmentsSTMT->fetch(PDO::FETCH_ASSOC)) {
					
					processCollation($reviewAssign, "review_assignments", $collations);
					
					$userSTMT->bindParam(":userId", $reviewAssign["reviewer_id"], PDO::PARAM_INT);
					if ($userSTMT->execute()) {
						$reviewer = $userSTMT->fetch(PDO::FETCH_ASSOC);
						
						processCollation($reviewer, "users", $collations);
						
						$reviewAssign["reviewer"] = $reviewer;
					}
					else {
						$errorOccurred = true;
						$error = array("article_id" => $article["article_id"], "reviewer_id" => $reviewAssign["reviewer_id"], "error" => $userSTMT->errorInfo());
						array_push($errors["reviewer"], $error);
						$numErrors++;
					}
					
					$reviewResponses = array();
					$reviewFormResponsesSTMT->bindParam(":rfResponses_reviewId", $reviewAssign["review_id"], PDO::PARAM_INT);
					
					if ($reviewFormResponsesSTMT->execute()) {
						while ($response = $reviewFormResponsesSTMT->fetch(PDO::FETCH_ASSOC)) {
							array_push($reviewResponses, $response);
						}
						
						processCollation($reviewResponses, "review_form_responses", $collations);
						
						$reviewAssign["review_form_responses"] = $reviewResponses;
					}
					else {
						$errorOccurred = true;
						$error = array("article_id" => $article["article_id"], "review_assignment" => $reviewAssign, "error" => $reviewFormResponses->errorInfo());
						array_push($errors["review_form_responses"], $error);
						$numErrors++;
					}
					
					array_push($reviewAssignments, $reviewAssign);
				}
				$article["review_assignments"] = $reviewAssignments;
			}
			else {
				$errorOccurred = true;
				$error = array("article_id" => $article["article_id"], "error" => $reviewAssignmentsSTMT->errorInfo());
				array_push($errors["review_assignments"], $error);
				$numErrors++;
			}
			
			// all fields in review_rounds are integers
			$reviewRoundsSTMT->bindParam(":revRounds_submissionId", $article["article_id"], PDO::PARAM_INT);
			if ($reviewRoundsSTMT->execute()) {
				$reviewRounds = array();
				while($reviewRound = $reviewRoundsSTMT->fetch(PDO::FETCH_ASSOC)) {
					array_push($reviewRounds, $reviewRound);
				}
				$article["review_rounds"] = $reviewRounds;
			}
			else {
				$errorOccurred = true;
				$error = array("article_id" => $article["article_id"], "error" => $reviewRoundsSTMT->errorInfo());
				array_push($errors["review_rounds"], $error);
				$numErrors++;
			}
			
			
			if ($verbose) echo ($errorOccurred) ? "Error\n" : "Ok\n";
			///////////  END OF PART 6  //////////////////////////////////////////////////////////////////////////
			
			//PUT ALL THE ARTICLE INFORMATIONS ON THE ARRAY OF UNPUBLISHED ARTICLES
			array_push($unpubArt, $article);
			$numArticles++;
		}
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	echo "\nFetched $numArticles unpublished articles.\n";
	
	return array("unpublished_articles" => $unpubArt, "numArticles" => $numArticles, "errors" => $errors, "numErrors" => $numErrors);
	
}
////////// END OF fetchUnpublishedArticles  ///////////////////////////////////////////


// #03)

function fetchAnnouncements($conn, $journal = null, $args = null) {
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php
	}
	
	$collations = null;
	$verbose = null;
	
	if (is_array($args)) {
		if (array_key_exists('collations', $args)) {
			$collations = $args['collations'];
		}
		if (array_key_exists('verbose', $args)) {
			$verbose = $args['verbose'];	
		}
	}
	
	$announcements = array();
	
	$errors = array(
		'announcements' => array(),
		'announcement_settings' => array()
	);
	
	$announcementsSTMT = $conn->prepare('SELECT * FROM announcements WHERE assoc_id = :journalId');
	$announcementSettingsSTMT = $conn->prepare('SELECT * FROM announcement_settings WHERE announcement_id = :announcementId');
	
	//do not get the announcements types because it is quite funky 
	
	$announcementsSTMT->bindParam(':journalId', $journal['journal_id'], PDO::PARAM_INT);
	
	if ($verbose) echo "\n\nFetching the announcements ...... ";
	
	if ($announcementsSTMT->execute()) {
		while ($announcement = $announcementsSTMT->fetch(PDO::FETCH_ASSOC)) {
			
			processCollation($announcement, 'announcements', $collations);
			
			//////////  announcement settings  ///////////////////////////////
			$settings = array();
			$announcementSettingsSTMT->bindParam(':announcementId', $announcement['announcement_id'], PDO::PARAM_INT);
			
			if ($verbose) echo "\n    fetching announcement #" . $announcement['announcement_id'] . " settings ....... ";
			
			if ($announcementSettingsSTMT->execute()) {
				while ($setting = $announcementSettingsSTMT->fetch(PDO::FETCH_ASSOC)) {
					array_push($settings, $setting);
				}
				if ($verbose) echo "Ok\n";
			}
			else {
				if ($verbose) echo "Error\n";
				$error = array('announcement_id' => $announcement['announcement_id'], 'error' => $announcementSettingsSTMT->errorInfo());
				array_push($errors['announcement_settings'], $error);
			}
			
			processCollation($settings, 'announcement_settings', $collations);
			
			$announcement['settings'] = $settings;
			/////////////////////////////////////////////////////////////
			
			array_push($announcements, $announcement);
		}
		
		if ($verbose) echo "Ok\n";
	}
	else {
		if ($verbose) echo "Error\n";
		array_push($errors['announcements'], $announcementsSTMT->errorInfo());
	}
	
	echo "\nFetched " . count($announcements) . " announcements.\n";
	
	return array('announcements' => $announcements, 'errors' => $errors);
}
///////// end of fetchAnnouncements  ///////////////////////////////


// #04)

function fetchEmailTemplates($conn, $journal = null, $args = null) {
	
	echo "\n\nTHE FUNCTION fetchEmailTemplates DOES NOT DO ANYTHING \n\n";
	/*if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php
	}
	
	$collations = null;
	$verbose = null;
	
	if (is_array($args)) {
		if (array_key_exists("collations", $args)) {
			$collations = $args["collations"];
		}
		if (array_key_exists("verbose", $args)) {
			$verbose = $args["verbose"];	
		}
	}
	
	$emailTemplates = array();
	
	$emailTemplatesSTMT = $conn->prepare("SELECT * FROM email_templates WHERE assoc_id = :journalId");
	$emailTemplatesDataSTMT = $conn->prepare("SELECT * FROM email_templates_data WHERE email_key = :emailKey AND assoc_id = :assocId");
	
	$emailTemplatesSTMT->bindParam(":journalId", $journal["journal_id"], PDO::PARAM_INT);
	
	if ($verbose) echo "\n\nFetching the email templates...\n\n";
	
	if ($emailTemplatesSTMT->execute()) {
		while ($emailTemplate = $emailTemplatesSTMT->fetch(PDO::FETCH_ASSOC)) {
			
			processCollation($emailTemplate, "email_templates", $collations);
			
			//////////  get the email_template_data  ///////////////////////////////
			$templateData = array();
			$emailTemplatesDataSTMT->bindParam(":assocId", $emailTemplate["assoc_id"], PDO::PARAM_INT);
			$emailTemplatesDataSTMT->bindParam(":emailKey", $emailTemplate["email_key"], PDO::PARAM_STR);
			
			if ($emailTemplatesDataSTMT->execute()) {
				while ($data = $emailTemplatesDataSTMT->fetch(PDO::FETCH_ASSOC)) {
					array_push($templateData, $data);
				}
			}
			
			processCollation($templateData, "email_templates_data", $collations);
			
			$emailTemplate["template_data"] = array("data" => $templateData);
			/////////////////////////////////////////////////////////////
			print_r($emailTemplate);
			array_push($emailTemplates, $emailTemplate);
		}
	}
	else {
		print_r($emailTemplatesSTMT->errorInfo());
	}
	
	echo "\nFetched " . count($emailTemplates) . " email templates.\n";
	
	return $emailTemplates;*/
}
////////////////// end of fetchEmailTemplates //////////////////////////////////////////////


// #05)

function fetchGroups($conn, $journal = null, $args = null) {
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php
	}
	
	$collations = null;
	$verbose = null;
	
	if (is_array($args)) {
		if (array_key_exists('collations', $args)) {
			$collations = $args['collations'];
		}
		if (array_key_exists('verbose', $args)) {
			$verbose = $args['verbose'];	
		}
	}
	
	$groups = array();
	
	$errors = array(
		'users' => array(),
		'groups' => array(),
		'group_settings' => array(),
		'group_memberships' => array()
	);
	
	//////////// STATEMENTS ///////////////////////////////////////////
	
	/////////////// group statements  ////////////////////////////////////////
	$groupsSTMT = $conn->prepare('SELECT * FROM groups WHERE assoc_id = :journalId');
	$groupsSTMT->bindParam(':journalId', $journal['journal_id'], PDO::PARAM_INT);
	$groupSettingsSTMT = $conn->prepare('SELECT * FROM group_settings WHERE group_id = :settings_groupId');
	$groupMembershipsSTMT = $conn->prepare('SELECT * FROM group_memberships WHERE group_id = :memberships_groupId');
	/////////////////////////////////////////////////////////////////////////////
	
	////////////////////// statements to get information to identify the user ///////////////////////////////////
	$userSTMT = $conn->prepare('SELECT * FROM users WHERE user_id = :userId');
	$userSettingsSTMT = $conn->prepare('SELECT * FROM user_settings WHERE user_id = :userSettings_userId');
	$rolesSTMT = $conn->prepare('SELECT * FROM roles WHERE journal_id = :roles_journalId AND user_id = :roles_userId');
	$rolesSTMT->bindParam(':roles_journalId', $journalId, PDO::PARAM_INT);
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	
	if ($verbose) echo "\n\nFetching the groups ...... ";
	
	if ($groupsSTMT->execute()) {
		while ($group = $groupsSTMT->fetch(PDO::FETCH_ASSOC)) {
			
			processCollation($group, 'groups', $collations);
			
			//////////  group settings  ///////////////////////////////
			$settings = array();
			$groupSettingsSTMT->bindParam(':settings_groupId', $group['group_id'], PDO::PARAM_INT);
			
			if ($verbose) echo "\n    fetching the group #" . $group['group_id'] . " settings ............ ";
			
			if ($groupSettingsSTMT->execute()) {
				while ($setting = $groupSettingsSTMT->fetch(PDO::FETCH_ASSOC)) {
					array_push($settings, $setting);
				}
				if ($verbose) echo "Ok\n";
			}
			else {
				if ($verbose) echo "Error\n";
				$error = array('group_id' => $group['group_id'], 'error' => $groupSettingsSTMT->errorInfo());
				array_push($errors['group_settings'], $error);
			}
			
			processCollation($settings, 'group_settings', $collations);
			
			$group['settings'] = $settings;
			/////////////////////////////////////////////////////////////
			
			/////////  group memberships  ///////////////////////////////
			$memberships = array();
			$groupMembershipsSTMT->bindParam(':memberships_groupId', $group['group_id'], PDO::PARAM_INT);
			
			if ($verbose) echo "\n    fetching the group #" . $group['group_id'] . " memberships .......... ";
			
			if ($groupMembershipsSTMT->execute()) {
				while ($membership = $groupMembershipsSTMT->fetch(PDO::FETCH_ASSOC)) {
					
					if ($verbose) echo "        fetching user #" . $membership['user_id'] . " info ....... ";
					
					/////////////  set the user info /////////////////////////////////////
					
					$errorOccurred = false;
					$error = array('group_id' => $membership['group_id']);
					
					$userSTMT->bindParam(':userId', $membership['user_id'], PDO::PARAM_INT);
					if ($userSTMT->execute()) {
						$user = $userSTMT->fetch(PDO::FETCH_ASSOC);
						
						processCollation($user, 'users', $collations);
						
						//fetching the user settings
						$userSettingsSTMT->bindParam(':userSettings_userId', $user['user_id'], PDO::PARAM_INT);
						if ($userSettingsSTMT->execute()) {
							$userSettings = array();
							while ($setting = $userSettingsSTMT->fetch(PDO::FETCH_ASSOC)) {
								array_push($userSettings, $setting);
							}
							
							processCollation($userSettings, 'user_settings', $collations);
							
							$user['settings'] = $userSettings;
						}// end of the if userSettingsSTMT executed
						else {
							$errorOccurred = true;
							$error['userSettingsError'] = $userSettingsSTMT->errorInfo();
						}
						
						//fetching the user roles for this journal
						$rolesSTMT->bindParam(':roles_userId', $user['user_id'], PDO::PARAM_INT);
						if ($rolesSTMT->execute()) {
							$roles = array();
							while ($role = $rolesSTMT->fetch(PDO::FETCH_ASSOC)) {
								array_push($roles, $role);
							}
							
							processCollation($roles, 'roles', $collations);
							
							$user['roles'] = $roles;
						}// end of the if rolesSTMT executed
						else {
							$errorOccurred = true;
							$error['rolesError'] = $rolesSTMT->errorInfo();
						}
						
						$membership['user'] = $user;
						
						if ($errorOccurred) {
							$error['user'] = $user;
						}
						
					}// end of the if userSTMT executed
					else {
						$errorOccurred = true;
						$error['userError'] = $userSTMT->errorInfo();
					}
					
					if ($errorOccurred) {
						if ($verbose) echo "Error\n";
						array_push($errors['users'], $error);
						$numErrors++;
					}
					else if ($verbose) {
						echo "Ok\n";
					}
					/////// end of set the user info  ////////////////////////////////
					
					array_push($memberships, $membership);
				}
			}
			else {
				$error = array('group_id' => $grp['group_id'], 'error' => $groupMembershipsSTMT->errorInfo());
				array_push($errors['group_memberships'], $error);
			}
			
			processCollation($memberships, 'group_memberships', $collations);
			
			$group['memberships'] = $memberships;
			
			/////////////////////////////////////////////////////////////
			
			array_push($groups, $group);
		}
		
	}// end of the if groupsSTMT executed
	else {
		if ($verbose) echo "Error\n";
		array_push($errors['groups'], $groupsSTMT->errorInfo());
	}
	
	echo "\nFetched " . count($groups) . " groups.\n";
	
	return array('groups' => $groups, 'errors' => $errors);
}
//////////////// end of fetchGroups  ///////////////////////////////


// #06)

function fetchReviewForms($conn, $journal = null, $args = null) {
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php
	}
	
	$collations = null;
	$verbose = null;
	
	if (is_array($args)) {
		if (array_key_exists('collations', $args)) {
			$collations = $args['collations'];
		}
		if (array_key_exists('verbose', $args)) {
			$verbose = $args['verbose'];	
		}
	}
	
	$reviewForms = array();
	
	$errors = array(
		'review_forms' => array(),
		'review_form_settings' => array(),
		'review_form_elements' => array(),
		'review_form_element_settings' => array()
	);
	
	$reviewFormsSTMT = $conn->prepare('SELECT * FROM review_forms WHERE assoc_id = :revForms_assocId');
	$reviewFormSettingsSTMT = $conn->prepare('SELECT * FROM review_form_settings WHERE review_form_id = :rfSettings_reviewFormId');
	$revFormElementsSTMT = $conn->prepare('SELECT * FROM review_form_elements WHERE review_form_id = :rfElements_reviewFormId');
	$revFormElementSettingsSTMT = $conn->prepare('SELECT * FROM review_form_element_settings WHERE review_form_element_id = :rfElemSettings_reviewFormElementId');
	
	$reviewFormsSTMT->bindParam(':revForms_assocId', $journal['journal_id'], PDO::PARAM_INT);
	
	if ($verbose) echo "\n\nFetching the review forms ...... ";
	
	if ($reviewFormsSTMT->execute()) {
		while ($reviewForm = $reviewFormsSTMT->fetch(PDO::FETCH_ASSOC)) {
			
			processCollation($reviewForm, 'review_forms', $collations);
			
			//////////  review_form_settings  ///////////////////////////////
			$settings = array();
			$reviewFormSettingsSTMT->bindParam(':rfSettings_reviewFormId', $reviewForm['review_form_id'], PDO::PARAM_INT);
			
			if ($verbose) echo "\n    fetching review form #" . $reviewForm['review_form_id'] . " settings ....... ";
			
			if ($reviewFormSettingsSTMT->execute()) {
				while ($setting = $reviewFormSettingsSTMT->fetch(PDO::FETCH_ASSOC)) {
					array_push($settings, $setting);
				}
				if ($verbose) echo "Ok\n";
			}
			else {
				if ($verbose) echo "Error\n";
				$error = array('review_form_id' => $reviewForm['review_form_id'], 'error' => $reviewFormSettingsSTMT->errorInfo());
				array_push($errors['review_form_settings'], $error);
			}
			
			processCollation($settings, 'review_form_settings', $collations);
			
			$reviewForm['settings'] = $settings;
			/////////////////////////////////////////////////////////////
			
			
			
			//////////// review_form_elements  //////////////////////////
			
			$revFormElements = array();
			$revFormElementsSTMT->bindParam(':rfElements_reviewFormId', $reviewForm['review_form_id'], PDO::PARAM_INT);
			
			if ($verbose) echo "     fetching the review form elements:\n";
			
			if ($revFormElementsSTMT->execute()) {
				while ($element = $revFormElementsSTMT->fetch(PDO::FETCH_ASSOC)) {
					
					processCollation($element, 'review_form_elements', $collations);
					
					///////// get the review form element settings ////////////////////
					$elementSettings = array();
					$revFormElementSettingsSTMT->bindParam(':rfElemSettings_reviewFormElementId', $element['review_form_element_id'], PDO::PARAM_INT);
					
					if ($verbose) echo "        fetching review form element #" . $element['review_form_element_id'] . " settings ......";;
					if ($revFormElementSettingsSTMT->execute()) {
						while($setting = $revFormElementSettingsSTMT->fetch(PDO::FETCH_ASSOC)) {
							array_push($elementSettings, $setting);
						}
						if ($verbose) echo "Ok\n";
					}
					else {
						if ($verbose) echo "Error\n";
						$error = array('review_form_element' => $element, 'error' => $revFormElementSettingsSTMT->errorInfo());
						array_push($errors['review_form_element_settings'], $error);
					}
					
					processCollation($elementSettings, 'review_form_element_settings', $collations);
					
					$element['settings'] = $elementSettings;
					///////////////////////////////////////////////////////////////////
					
					array_push($revFormElements, $element);
				}
			}
			else {
				if ($verbose) echo "Error\n";
				$error = array('review_form' => $reviewForm, 'error' => $revFormElementsSTMT->errorInfo());
				array_push($errors['review_form_elements'], $error);
			}
			
			$reviewForm['elements'] = $revFormElements;
			
			/////////////////////////////////////////////////////////////
			
			array_push($reviewForms, $reviewForm);
		}
		
		
	}
	else {
		if ($verbose) echo "Error\n";
		array_push($errors['review_forms'], $reviewFormsSTMT->errorInfo());
	}
	
	echo "\nFetched " . count($reviewForms) . " reviewForms.\n";
	
	return array('review_forms' => $reviewForms, 'errors' => $errors);
}


/**
fetch the citations of the articles passed by the argument $articleIds
which is an array with the ids of the articles to fetch their citations

returns an array with the citations
*/
function fetchCitations() {
	echo "\n\n\nTHIS FUNCTION DOES NOT DO ANYTHING YET!\n\n\n";
}

/**
fetch the article referrals
*/
function fetchReferrals() {
	echo "\n\n\nTHIS FUNCTION DOES NOT DO ANYTHING YET\n\n\n";
}


function fetchEventLogs($conn, $articleIdsSTR, &$dataMapping, $journal = null, $args = null) {
	if (!is_string($articleIdsSTR)) {
		return false;
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php
	}
	
	$collations = null;
	$verbose = null;
	
	if (is_array($args)) {
		if (array_key_exists('collations', $args)) {
			$collations = $args['collations'];
		}
		if (array_key_exists('verbose', $args)) {
			$verbose = $args['verbose'];	
		}
	}
	
	$ids = filter_var($articleIdsSTR, FILTER_SANITIZE_STRING);
	
	if ($ids === false) {
		echo "\n\nInvalid ids passed to fetchEventLogs: '$articleIdsSTR'\n\n";
		return false;
	}
	
	$eventLogSTMT = $conn->prepare('SELECT * FROM event_log WHERE assoc_id IN ' . $ids);
	$eventLogSettingsSTMT = $conn->prepare('SELECT * FROM event_log_settings WHERE log_id = :settings_logId');
	
	$errors = array(
		'event_log' => array(),
		'event_log_settings' => array()
	);
	
	$eventLogs = array();
	
	///////////// fetching the event_logs ////////////////////////////////////////////////
	if ($verbose) echo "\n\nFetching the event logs .......";
	if ($eventLogSTMT->execute()) {
		echo "\n\n";
		while ($eventLog = $eventLogSTMT->fetch(PDO::FETCH_ASSOC)) {
			
			processCollation($eventLog, 'event_log', $collations);
			
			/////// checking if it needs to fetch the user data //////////////
			$fetchUserData = false;
			if (is_array($dataMapping)) {
				if (!array_key_exists('user_id', $dataMapping)) {
					$fetchUserData = true;
				}
				else if (!array_key_exists($eventLog['user_id'], $dataMapping['user_id'])) {
					// the sender id is not in the dataMapping
					// which means the user was not yet imported
					$fetchUserData = true;
				}
			}
			else {
				$fetchUserData = true;
			}
			/////////////////////////////////////////////////////////////////////
			
			if ($fetchUserData) {
				
				if ($verbose) echo "\n    fetching the user #" . $eventLog['user_id'] . " data ..........";
				
				$data = fetchUser($conn, $eventLog['user_id'], $journal, $args);
				if ($data['errorOccurred']) {
					if ($verbose) {
						echo "Error\n";
						$errors['event_log'] = array('event_log' => $eventLog, 'error' => $data['error']);
					}
				}
				else {
					if ($verbose) echo "Ok\n";
				}
				$eventLog['user'] = $data['user'];
			}
			/////////////// end of fetching the user data  ////////////////////////
			
			///// fetch the event_log_settings
			$eventLogSettingsSTMT->bindParam(':settings_logId', $eventLog['log_id'], PDO::PARAM_INT);
			
			if ($verbose) echo '    fetching the event_log #' . $eventLog['log_id'] . ' settings .....';
			
			if ($eventLogSettingsSTMT->execute()) {
				
				$settings = array();
				
				while ($setting = $eventLogSettingsSTMT->fetch(PDO::FETCH_ASSOC)) {
					processCollation($setting, 'event_log_settings', $collations);
					array_push($settings, $setting);
				}
				
				$eventLog['settings'] = $settings;
				if ($verbose) echo "OK\n";
				
			}
			else {
				if ($verbose) echo "Error\n";
				$error = array('event_log' => $eventLog, 'error' => $eventLogSettingsSTMT->errorInfo());
				array_push($errors['event_log_settings'], $error);
			}
			
			array_push($eventLogs, $eventLog);
			
		}//end of the while to fetch the event_log
		
	}
	else {
		if ($verbose) echo "Error\n\n";
		$error = array('error' => $eventLogSTMT->errorInfo());
		array_push($errors['event_log'], $error);
	}
	////////// end of fetching the event_logs //////////////////////////////////////////////
	
	echo "\nFetched " . count($eventLogs) . " event logs.\n";
	
	return array('event_logs' => $eventLogs, 'errors' => $errors);
	
}


function fetchEmailLogs($conn, $articleIdsSTR, &$dataMapping, $journal = null, $args = null) {
	
	if (!is_string($articleIdsSTR)) {
		return false;
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php
	}
	
	$collations = null;
	$verbose = null;
	
	if (is_array($args)) {
		if (array_key_exists('collations', $args)) {
			$collations = $args['collations'];
		}
		if (array_key_exists('verbose', $args)) {
			$verbose = $args['verbose'];	
		}
	}
	
	$ids = filter_var($articleIdsSTR, FILTER_SANITIZE_STRING);
	
	if ($ids === false) {
		echo "\n\nInvalid ids passed to fetchEventLogs: '$articleIdsSTR'\n\n";
		return false;
	}
	
	$emailLogSTMT = $conn->prepare('SELECT * FROM email_log WHERE assoc_id IN ' . $ids);
	$emailLogUsersSTMT = $conn->prepare('SELECT * FROM email_log_users WHERE email_log_id = :emailLog_logId');
	
	$errors = array(
		'email_log' => array(),
		'email_log_users' => array()
	);
	
	$emailLogs = array();
	$showErrors = false;
	
	///////// fetching the email_logs  /////////////////////////////////////////////////////
	
	if ($verbose) echo "\nFetching the email_logs:\n";
	if ($emailLogSTMT->execute()) {
		
		//fetchUser($conn, $userId, $journal = null, $args = null)
		
		while ($emailLog = $emailLogSTMT->fetch(PDO::FETCH_ASSOC)) {
			
			processCollation($emailLog, 'email_log', $collations);
			
			/////////// fetching the sender data if it was not yet imported ////////
			
			$senderId = $emailLog['sender_id'];
			
			/////// checking if it needs to fetch the sender data //////////////
			$fetchUserData = false;
			if (is_array($dataMapping)) {
				if (!array_key_exists('user_id', $dataMapping)) {
					$fetchUserData = true;
				}
				else if (!array_key_exists($senderId, $dataMapping['user_id'])) {
					// the sender id is not in the dataMapping
					// which means the user was not yet imported
					$fetchUserData = true;
				}
			}
			else {
				$fetchUserData = true;
			}
			/////////////////////////////////////////////////////////////////////
			
			if ($fetchUserData) {
				
				if ($verbose) echo "\n    fetching the email log_id #" . $emailLog['log_id'] . " sender data ..........";
				
				$data = fetchUser($conn, $senderId, $journal, $args);
				if ($data['errorOccurred']) {
					if ($verbose) {
						echo "Error\n";
						$errors['email_log'] = array('emailLog' => $emailLog, 'error' => $data['error']);
						$showErrors = true;
					}
				}
				else {
					if ($verbose) echo "Ok\n";
				}
				$emailLog['sender'] = $data['user'];
			}
			
			/////////////// end of fetching the sender data  ////////////////////////
			
			$emailLogUsersSTMT->bindParam(':emailLog_logId', $emailLog['log_id'], PDO::PARAM_INT);
			
			if ($verbose) echo '    fetching the email_log_users for the email log_id #' . $emailLog['log_id'] . ' ............';
			if ($emailLogUsersSTMT->execute()){
				$emailLogUsers = array();
				
				if ($verbose) echo "Ok\n";
				
				while ($emailLogUser = $emailLogUsersSTMT->fetch(PDO::FETCH_ASSOC)) {
					/////// checking if it needs to fetch the user data //////////////
					$fetchUserData = false;
					if (is_array($dataMapping)) {
						if (!array_key_exists('user_id', $dataMapping)) {
							$fetchUserData = true;
						}
						else if (!array_key_exists($senderId, $dataMapping['user_id'])) {
							// the sender id is not in the dataMapping
							// which means the user was not yet imported
							$fetchUserData = true;
						}
					}
					else {
						$fetchUserData = true;
					}
					/////////////////////////////////////////////////////////////////////
					
					if ($fetchUserData) {
						
						if ($verbose) echo "\n    fetching the user #" . $emailLogUsers['user_id'] . " data ..........";
						
						$data = fetchUser($conn, $senderId, $journal, $args);
						if ($data['errorOccurred']) {
							if ($verbose) {
								echo "Error\n";
								$errors['email_log_users'] = array('emailLogUsers' => $emailLogUsers, 'error' => $data['error']);
								$showErrors = true;
							}
						}
						else {
							if ($verbose) echo "Ok\n";
						}
						$emailLogUser['user'] = $data['user'];
					}
					/////////////// end of fetching the user data  ////////////////////////
					
					echo "        fetched email_log_id #" . $emailLogUser['email_log_id'] . " with user_id #" . $emailLogUsers['user_id'] . "\n";
					
					array_push($emailLogUsers, $emailLogUser);
				}
				// end of the while to fetch each email_log_users record
				
				$emailLog['email_log_users'] = $emailLogUsers;
				
			}//end of the if email_log_users executed
			else {
				echo "Error\n";
				$error = array('email_log' => $emailLog, 'error' => $emailLogUsersSTMT->errorInfo());
				array_push($errors['email_log_users'], $error);
				$showErrors = true;
			}
			
			array_push($emailLogs, $emailLog);
			
		}
		//end of the while to fetch the email_logs
	}
	else {
		if ($verbose) echo "Error\n\n";
		$error = array('error' => $emailLogSTMT->errorInfo());
		array_push($errors['email_log'], $error);
		$showErrors = true;
	}
	///////// end of fetching the email_logs  //////////////////////////////////////////////
	
	echo "\nFetched " . count($emailLogs) . " email logs.\n";
	
	if ($showErrors) {
		echo "\nThe errors were:\n" . print_r($errors, true) . "\n";
	}
	
	return array('email_logs' => $emailLogs, 'errors' => $errors);
}


/**
this function fetches the article history which includes the articles event and email logs
*/
function fetchArticlesHistory($conn, $articlesIds, $journal = null, $args = null) {
	
	/*if (!is_array($articleIds)) {
		exit("\nArticles ids are not in an array\n\n");
	}*/
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php
	}
	
	$dataMapping = getDataMapping($journal['path']); //from appFunctions.php
	
	if (!array_key_exists('article_id', $dataMapping)) {
		echo "\n\nThere is not any article data mapped yet\n\n";
		echo "The data mapping:\n" . print_r($dataMapping, true) . "\n";
		return false;
	}
	
	$articleIds = array();
	foreach ($dataMapping['article_id'] as $oldId => $newId) {
		array_push($articleIds, $oldId);
	}
	
	$collations = null;
	$verbose = null;
	
	if (is_array($args)) {
		if (array_key_exists('collations', $args)) {
			$collations = $args['collations'];
		}
		if (array_key_exists('verbose', $args)) {
			$verbose = $args['verbose'];	
		}
	}
	
	$totalArticles = count($articleIds);
	
	$articleIdsSTR = '(';
	for ($i = 0; $i < $totalArticles - 1; $i++) {
		$articleIdsSTR .= $articleIds[$i] . ', ';
	}
	$articleIdsSTR .= $articleIds[$totalArticles - 1] . ')';
	
	$eventLogData = fetchEventLogs($conn, $articleIdsSTR, $dataMapping, $journal, $args);
	
	$emailLogData = fetchEmailLogs($conn, $articleIdsSTR, $dataMapping, $journal, $args);
	
	$errors = array('email_log_errors' => $emailLogData['errors'], 'event_log_errors' => $eventLogData['errors']);
	
	$history = array('email_logs' => $emailLogData['email_logs'], 'event_logs' => $eventLogData['event_logs']);
	
	return array('articles_history' => $history, 'errors' => $errors);
}


/**
fetch the journal plugin settings
*/
function fetchPluginSettings($conn, $journalId) {
	
}


/**
fetch the tables custom_issue_orders and custom_section_orders for the journal specified by journalId
*/
function fetchSectionAndIssueOrders($conn, $journalId) {
	
}