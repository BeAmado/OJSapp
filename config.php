<?php

/**
FUNCTIONS DEFINED IN THIS SCRIPT
/*
01) isSettings
02) isValidId
03) isFileId


THIS SCRIPT DEFINES THE VARIABLE $tables FOR USE IN THE ENTIRE APP

Developed in 2017-2018 by Bernardo Amado

*/

// #01)
/**
returns true is $type is something like *settings
*/
function isSettings($type) {
	if (strlen($type) > 8) {
		if (substr($type, -8) === 'settings') {
			return true;
		}
	}
	return false;
}

// #02)
/**
returns true is $type is something like *_id
*/
function isValidId($type) {
	if (strlen($type) > 3) {
		if (substr($type, -3) === '_id') {
			return true;
		}
	}
	return false;
}


// #03)
/**
returns true is $type is something like *file_id
*/
function isFileId($type) {
	if (strlen($type) >= 7) {
		if (substr($type, -7) === 'file_id') {
			return true;
		}
	}
	return false;
}


//the variable tables stores the properties of the OJS database tables that might be used in the app
$tables = array();

$tables['articles'] = array(
	'attributes' => array('article_id', 'user_id', 'journal_id', 'section_id', 'language', 'comments_to_ed', 'date_submitted', 'last_modified', 
		'date_status_modified', 'status', 'submission_progress', 'current_round', 'submission_file_id', 'revised_file_id', 'review_file_id', 'editor_file_id', 
		'pages', 'fast_tracked', 'hide_author','comments_status', 'locale', 'citations'),
	'primary_keys' => array('article_id'),
	'foreign_keys' => array('user_id', 'journal_id', 'section_id'),
	'properties' => array(
		'article_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'user_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'journal_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'section_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'language' => array('type' => 'varchar(10)', 'null' => 'yes', 'key' => '', 'default' => 'en', 'extra' => ''),
		'comments_to_ed' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_submitted' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'last_modified' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_status_modified' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'status' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 1, 'extra' => ''),
		'submission_progress' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 1, 'extra' => ''),
		'current_round' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 1, 'extra' => ''),
		'submission_file_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'revised_file_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'review_file_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'editor_file_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'pages' => array('type' => 'varchar(255)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'fast_tracked' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'hide_author' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'comments_status' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'locale' => array('type' => 'varchar(5)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'citations' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);

$tables['article_settings'] = array(
	'attributes' => array('article_id', 'locale', 'setting_name', 'setting_value', 'setting_type'),
	'primary_keys' => array('article_id', 'locale', 'setting_name'),
	'foreign_keys' => array(),
	'properties' => array(
		'article_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'setting_name' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => 'pri', 'default' => 'setting name', 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => '', 'extra' => '')
	)
);

$tables['article_comments'] = array(
	'attributes' => array('comment_id', 'comment_type', 'role_id', 'article_id', 'assoc_id', 'author_id', 'commment_title', 
		'comments', 'date_posted', 'date_modified', 'viewable'),
	'primary_keys' => array('comment_id'),
	'foreign_keys' => array('article_id'),
	'properties' => array(
		'comment_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'comment_type' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'role_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'article_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'assoc_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'author_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'comment_title' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => '', 'default' => 'Titulo do comentario', 'extra' => ''),
		'comments' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_posted' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_modified' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'viewable' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => '')
	)

);



$tables['article_files'] = array(
	'attributes' => array('file_id', 'revision', 'source_file_id', 'source_revision', 'article_id', 'file_name', 'file_type', 'file_size',
		'original_file_name','file_stage', 'viewable', 'date_uploaded', 'date_modified', 'round', 'assoc_id'),
	'primary_keys' => array('file_id', 'revision'),
	'foreign_keys' => array('article_id'),
	'properties' => array(
		'file_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'revision' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
		'source_file_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'source_revision' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'article_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'file_name' => array('type' => 'varchar(90)', 'null' => 'no', 'key' => '', 'default' => 'file name', 'extra' => ''),
		'file_type' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => '', 'default' => 'file type', 'extra' => ''),
		'file_size' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'original_file_name' => array('type' => 'varchar(127)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'file_stage' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'viewable' => array('type' => 'tinyint(4)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_uploaded' => array('type' => 'datetime', 'null' => 'no', 'key' => '', 'default' => '1990-01-01 00:00:00', 'extra' => ''),
		'date_modified' => array('type' => 'datetime', 'null' => 'no', 'key' => '', 'default' => '1990-01-01 00:00:00', 'extra' => ''),
		'round' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'assoc_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);


$tables['article_supplementary_files'] = array(
	'attributes' => array('supp_id', 'file_id', 'article_id', 'type', 'language', 'date_created', 'show_reviewers', 'date_submitted', 'seq', 'remote_url'),
	'primary_keys' => array('supp_id'),
	'foreign_keys' => array('file_id', 'article_id'),
	'properties' => array(
		'supp_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'file_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'article_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'type' => array('type' => 'varchar(255)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'language' => array('type' => 'varchar(10)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_created' => array('type' => 'date', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'show_reviewers' => array('type' => 'tinyint(4)', 'null' => 'yes', 'key' => '', 'default' => 0, 'extra' => ''),
		'date_submitted' => array('type' => 'datetime', 'null' => 'no', 'key' => '', 'default' => '1990-01-01 00:00:00', 'extra' => ''),
		'seq' => array('type' => 'double', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'remote_url' => array('type' => 'varchar(255)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);

$tables['article_supp_file_settings'] = array(
	'attributes' => array('supp_id', 'locale', 'setting_name', 'setting_value', 'setting_type'),
	'primary_keys' => array('supp_id', 'locale', 'setting_name'),
	'foreign_keys' => array(),
	'properties' => array(
		'supp_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'setting_name' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => null, 'extra' => '')
	)
);


$tables['article_galleys'] = array(
	'attributes' => array('galley_id', 'locale', 'article_id', 'file_id', 'label', 'html_galley', 'style_file_id', 'seq', 'remote_url'),
	'primary_keys' => array('galley_id'),
	'foreign_keys' => array('article_id', 'file_id', 'style_file_id'),
	'properties' => array(
		'galley_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'locale' => array('type' => 'varchar(5)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'article_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'file_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'label' => array('type' => 'varchar(32)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'html_galley' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'style_file_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'seq' => array('type' => 'double', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'remote_url' => array('type' => 'varchar(255)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);

$tables['article_galley_settings'] = array(
	'attributes' => array('galley_id', 'locale', 'setting_name', 'setting_value', 'setting_type'),
	'primary_keys' => array('galley_id', 'locale', 'setting_name'),
	'foreign_keys' => array(),
	'properties' => array(
		'galley_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'setting_name' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => '-type-', 'extra' => '')
	)

);


$tables['article_html_galley_images'] = array(
	'attributes' => array('galley_id', 'file_id'),
	'primary_keys' => array('galley_id', 'file_id'),
	'foreign_keys' => array(),
	'properties' => array(
		'galley_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'file_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => '')
	)

);


$tables['article_xml_galleys'] = array(
	'attributes' => array('xml_galley_id', 'galley_id', 'article_id', 'label', 'galley_type', 'views'),
	'primary_keys' => array('xml_galley_id'),
	'foreign_keys' => array('galley_id', 'article_id'),
	'properties' => array(
		'xml_galley_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'galley_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'article_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'label' => array('type' => 'varchar(32)', 'null' => 'no', 'key' => '', 'default' => '--label--', 'extra' => ''),
		'galley_type' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'views' => array('type' => 'int(11)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => '')
	)

);

	
$tables['article_search_objects'] = array(
	'attributes' => array('object_id', 'article_id', 'type', 'assoc_id'),
	'primary_keys' => array('object_id'),
	'foreign_keys' => array('article_id'),
	'properties' => array(
		'object_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'article_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'type' => array('type' => 'int(11)', 'null' => 'no', 'key' => '', 'default' => 1, 'extra' => ''),
		'assoc_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);


$tables['article_search_object_keywords'] = array(
	'attributes' => array('object_id', 'keyword_id', 'pos'),
	'primary_keys' => array('object_id', 'pos'),
	'foreign_keys' => array('keyword_id'),
	'properties' => array(
		'object_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
		'keyword_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'pos' => array('type' => 'int(11)', 'null' => 'no', 'key' => 'pri', 'default' => 10, 'extra' => '')
	)
);


$tables['article_search_keyword_list'] = array(
	'attributes' => array('keyword_id', 'keyword_text'),
	'primary_keys' => array('keyword_id'),
	'foreign_keys' => array(),
	'properties' => array(
		'keyword_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'keyword_text' => array('type' => 'varchar(60)', 'null' => 'no', 'key' => 'uni', 'default' => 0, 'extra' => '')
	)
);


$tables['authors'] = array(
	'attributes' => array('author_id', 'submission_id', 'primary_contact', 'seq', 'first_name', 'middle_name', 'last_name', 'country', 'email', 'url', 'user_group_id', 'suffix'),
	'primary_keys' => array('author_id'),
	'foreign_keys' => array('submission_id'),
	'properties' => array(
		'author_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => 'auto_increment'),
		'submission_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => null, 'extra' => ''),
		'primary_contact' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'seq' => array('type' => 'double', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'first_name' => array('type' => 'varchar(40)', 'null' => 'no', 'key' => '', 'default' => '--first_name--', 'extra' => ''),
		'middle_name' => array('type' => 'varchar(40)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'last_name' => array('type' => 'varchar(90)', 'null' => 'no', 'key' => '', 'default' => '--last_name--', 'extra' => ''),
		'country' => array('type' => 'varchar(90)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'email' => array('type' => 'varchar(90)', 'null' => 'no', 'key' => '', 'default' => '--email--', 'extra' => ''),
		'url' => array('type' => 'varchar(255)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'user_group_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'suffix' => array('type' => 'varchar(40)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);

$tables['author_settings'] = array(
	'attributes' => array('author_id', 'locale', 'setting_name', 'setting_value', 'setting_type'),
	'primary_keys' => array('author_id', 'locale', 'setting_name'),
	'foreign_keys' => array(),
	'properties' => array(
		'author_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'setting_name' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => null, 'extra' => '')
	)
);


$tables['edit_assignments'] = array(
	'attributes' => array('edit_id', 'article_id', 'editor_id', 'can_edit', 'can_review', 'date_assigned', 'date_notified', 'date_underway'),
	'primary_keys' => array('edit_id'),
	'foreign_keys' => array('article_id', 'editor_id'),
	'properties' => array(
		'edit_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'article_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'editor_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'can_edit' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 1, 'extra' => ''),
		'can_review' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 1, 'extra' => ''),
		'date_assigned' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_notified' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_underway' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);


$tables['edit_decisions'] = array(
	'attributes' => array('edit_decision_id', 'article_id', 'round', 'editor_id', 'decision', 'date_decided'),
	'primary_keys' => array('edit_decision_id'),
	'foreign_keys' => array('article_id', 'editor_id'),
	'properties' => array(
		'edit_decision_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'article_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'editor_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'round' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 1, 'extra' => ''),
		'decision' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 1, 'extra' => ''),
		'date_decided' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);


$tables['review_assignments'] = array(
	'attributes' => array('review_id', 'submission_id', 'reviewer_id', 'competing_interests', 'recommendation', 'date_assigned', 'date_notified', 'date_confirmed',
		'date_completed', 'date_acknowledged', 'date_due', 'last_modified', 'reminder_was_automatic', 'declined', 'replaced', 'cancelled', 'reviewer_file_id', 'date_rated', 
		'date_reminded', 'quality', 'round', 'review_form_id', 'regret_message', 'date_response_due', 'review_method', 'step', 'review_round_id', 'stage_id', 'unconsidered'),
	'primary_keys' => array('review_id'),
	'foreign_keys' => array('submission_id', 'reviewer_id', 'review_form_id', 'reviewer_file_id', 'review_round_id'),
	'properties' => array(
		'review_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'submission_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'reviewer_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'competing_interests' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'recommendation' => array('type' => 'tinyint(4)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_assigned' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_notified' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_confirmed' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_completed' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_acknowledged' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_due' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'last_modified' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'reminder_was_automatic' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'declined' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'replaced' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'cancelled' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'reviewer_file_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_rated' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_reminded' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'quality' => array('type' => 'tinyint(4)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'round' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 1, 'extra' => ''),
		'review_form_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => 'mul', 'default' => null, 'extra' => ''),
		'regret_message' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_response_due' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'review_method' => array('type' => 'tinyint(20)', 'null' => 'no', 'key' => '', 'default' => 1, 'extra' => ''),
		'step' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 1, 'extra' => ''),
		'review_round_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'stage_id' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 1, 'extra' => ''),
		'unconsidered' => array('type' => 'tinyint(4)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);


$tables['review_rounds'] = array(
	'attributes' => array('submission_id', 'round', 'review_revision', 'status', 'review_round_id', 'stage_id'),
	'primary_keys' => array('review_round_id'),
	'foreign_keys' => array('submission_id'),
	'properties' => array(
		'review_round_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'submission_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'round' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 1, 'extra' => ''),
		'review_revision' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'status' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'stage_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);


$tables['review_forms'] = array(
	'attributes' => array('review_form_id', 'assoc_id', 'seq', 'is_active', 'assoc_type'),
	'primary_keys' => array('review_form_id'),
	'foreign_keys' => array(),
	'properties' => array(
		'review_form_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'assoc_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'seq' => array('type' => 'double', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'is_active' => array('type' => 'tinyint(4)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'assoc_type' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);


$tables['review_form_settings'] = array(
	'attributes' => array('review_form_id', 'locale', 'setting_name', 'setting_value', 'setting_type'),
	'primary_keys' => array('review_form_id', 'locale', 'setting_name'),
	'foreign_keys' => array(),
	'properties' => array(
		'review_form_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'setting_name' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => '-type-', 'extra' => '')
	)
);


$tables['review_form_elements'] = array(
	'attributes' => array('review_form_element_id', 'review_form_id', 'seq', 'element_type', 'required', 'included'),
	'primary_keys' => array('review_form_element_id'),
	'foreign_keys' => array('review_form_id'),
	'properties' => array(
		'review_form_element_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => 'auto_increment'),
		'review_form_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => null, 'extra' => ''),
		'seq' => array('type' => 'double', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'element_type' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'required' => array('type' => 'tinyint(4)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'included' => array('type' => 'tinyint(4)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);

$tables['review_form_element_settings'] = array(
	'attributes' => array('review_form_element_id', 'locale', 'setting_name', 'setting_value', 'setting_type'),
	'primary_keys' => array('review_form_element_id', 'locale', 'setting_name'),
	'foreign_keys' => array(),
	'properties' => array(
		'review_form_element_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'setting_name' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => null, 'extra' => '')
	)
);


$tables['review_form_responses'] = array(
	'attributes' => array('review_form_element_id', 'review_id', 'response_type', 'response_value'),
	'primary_keys' => array(),
	'foreign_keys' => array('review_form_element_id', 'review_id'),
	'properties' => array(
		'review_form_element_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => 'auto_increment'),
		'review_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'response_type' => array('type' => 'varchar(6)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'response_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);


$tables['roles'] = array(
	'attributes' => array('journal_id', 'user_id', 'role_id'),
	'primary_keys' => array('journal_id', 'user_id', 'role_id'),
	'foreign_keys' => array('journal_id', 'user_id'),
	'properties' => array(
		'article_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
		'user_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
		'role_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 65536, 'extra' => '')
	)
);

	
$tables['users'] = array(
	'attributes' => array('user_id', 'username', 'password', 'salutation', 'first_name', 'middle_name', 'last_name', 'gender', 'initials', 'email', 'url', 'phone', 'fax', 
		'mailing_address', 'country', 'locales', 'date_last_email', 'date_registered', 'date_validated', 'date_last_login', 'must_change_password', 'auth_id', 'disabled', 
		'disabled_reason', 'auth_str', 'suffix', 'billing_address', 'inline_help'),
	'primary_keys' => array('user_id'),
	'foreign_keys' => array(),
	'properties' => array(
		'user_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => 'auto_increment'),
		'username' => array('type' => 'varchar(32)', 'null' => 'no', 'key' => 'uni', 'default' => null, 'extra' => ''),
		'password' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => '', 'default' => null, 'extra' => ''),
		'salutation' => array('type' => 'varchar(40)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'first_name' => array('type' => 'varchar(40)', 'null' => 'no', 'key' => '', 'default' => '', 'extra' => ''),
		'middle_name' => array('type' => 'varchar(40)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'last_name' => array('type' => 'varchar(90)', 'null' => 'no', 'key' => '', 'default' => '', 'extra' => ''),
		'gender' => array('type' => 'varchar(1)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'initials' => array('type' => 'varchar(5)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'email' => array('type' => 'varchar(90)', 'null' => 'no', 'key' => 'uni', 'default' => null, 'extra' => ''),
		'url' => array('type' => 'varchar(255)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'phone' => array('type' => 'varchar(24)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'fax' => array('type' => 'varchar(24)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);

$tables['user_settings'] = array(
	'attributes' => array('user_id', 'locale', 'setting_name', 'setting_value', 'setting_type', 'assoc_id', 'assoc_type'),
	'primary_keys' => array(),
	'foreign_keys' => array('user_id'),
	'properties' => array(
		'user_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => '', 'default' => '', 'extra' => ''),
		'setting_name' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => '', 'default' => 'setting name', 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => '', 'extra' => ''),
		'assoc_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => 0, 'extra' => ''),
		'assoc_type' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => 0, 'extra' => '')
	)
);


$tables['controlled_vocabs'] = array(
	'attributes' => array('controlled_vocab_id', 'symbolic', 'assoc_type', 'assoc_id'),
	'primary_keys' => array('controlled_vocab_id'),
	'foreign_keys' => array(),
	'properties' => array(
		'controlled_vocab_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'symbolic' => array('type' => 'varchar(64)', 'null' => 'no', 'key' => 'mul', 'default' => '', 'extra' => ''),
		'assoc_type' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'assoc_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => '')
	)
);


$tables['controlled_vocab_entries'] = array(
	'attributes' => array('controlled_vocab_entry_id', 'controlled_vocab_id', 'seq'),
	'primary_keys' => array('controlled_vocab_entry_id'),
	'foreign_keys' => array('controlled_vocab_id'),
	'properties' => array(
		'controlled_vocab_entry_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'controlled_vocab_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'seq' => array('type' => 'double', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);


$tables['controlled_vocab_entry_settings'] = array(
	'attributes' => array('controlled_vocab_entry_id', 'locale', 'setting_name', 'setting_value', 'setting_type'),
	'primary_keys' => array('controlled_vocab_entry_id', 'locale', 'setting_name'),
	'foreign_keys' => array(),
	'properties' => array(
		'controlled_vocab_entry_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'setting_name' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => null, 'extra' => '')
	)
);


$tables['user_interests'] = array(
	'attributes' => array('user_id', 'controlled_vocab_entry_id'),
	'primary_keys' => array('user_id', 'controlled_vocab_entry_id'),
	'foreign_keys' => array(),
	'properties' => array(
		'user_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
		'controlled_vocab_entry_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
	)
);



$tables['sections'] = array(
	'attributes' => array('section_id', 'journal_id', 'review_form_id', 'seq', 'editor_restricted', 'meta_indexed', 'meta_reviewed', 'abstracts_not_required',
'hide_title', 'hide_author', 'hide_about', 'disable_comments', 'abstract_word_count'),
	'primary_keys' => array('section_id'),
	'foreign_keys' => array('journal_id', 'review_form_id'),
	'properties' => array(
		'section_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => 'auto_increment'),
		'journal_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => null, 'extra' => ''),
		'review_form_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'seq' => array('type' => 'double', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'editor_restricted' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'meta_indexed' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'meta_reviewed' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 1, 'extra' => ''),
		'abstracts_not_required' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'hide_title' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'hide_author' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'hide_about' => array('type' => 'tinyint(4)', 'null' => 'yes', 'key' => '', 'default' => 0, 'extra' => ''),
		'disable_comments' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'abstract_word_count' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);

$tables['section_settings'] = array(
	'attributes' => array('section_id', 'locale', 'setting_name', 'setting_value', 'setting_type'),
	'primary_keys' => array('section_id', 'locale', 'setting_name'),
	'foreign_keys' => array(),
	'properties' => array(
		'section_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'setting_name' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => null, 'extra' => '')
	)
);

$tables['announcements'] = array(
	'attributes' => array('announcement_id', 'assoc_id', 'type_id', 'date_expire', 'date_posted', 'assoc_type'),
	'primary_keys' => array('announcement_id'),
	'foreign_keys' => array('assoc_id'), // the assoc_id is the id of the journal the announcement is from
// the ojs system does not mark it as a foreign key but it should logically be, since id should be the same as the primary key of the journal from the table journals
	'properties' => array(
		'announcement_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'assoc_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'assoc_type' => array('type' => 'smallint(6)', 'null' => 'yes', 'key' => 'mul', 'default' => null, 'extra' => ''),
		'type_id' => array('type' => 'bigint(20)', 'null' => 'yes' , 'key' => '', 'default' => null, 'extra' => ''),
		'date_posted' => array('type' => 'datetime', 'null' => 'no' , 'key' => '', 'default' => null, 'extra' => ''),
		'date_expire' => array('type' => 'datetime', 'null' => 'yes' , 'key' => '', 'default' => null, 'extra' => '')
	)
);

$tables['announcement_settings'] = array(
	'attributes' => array('announcement_id', 'locale', 'setting_name', 'setting_value', 'setting_type'),
	'primary_keys' => array('announcement_id', 'locale', 'setting_name'),
	'foreign_keys' => array(),
	'properties' => array(
		'announcement_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'setting_name' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => null, 'extra' => '')
	)
);

$tables['announcement_types'] = array(
	'attributes' => array('type_id', 'assoc_id', 'assoc_type'),
	'primary_keys' => array('type_id'),
	'foreign_keys' => array(), 
	'properties' => array(
		'type_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'assoc_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'assoc_type' => array('type' => 'smallint(6)', 'null' => 'yes', 'key' => 'mul', 'default' => null, 'extra' => '')
	)
);

$tables['announcement_type_settings'] = array(
	'attributes' => array('type_id', 'locale', 'setting_name', 'setting_value', 'setting_type'),
	'primary_keys' => array('type_id', 'locale', 'setting_name'),
	'foreign_keys' => array(),
	'properties' => array(
		'type_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'setting_name' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => null, 'extra' => '')
	)
);


$tables['groups'] = array(
	'attributes' => array('group_id', 'context', 'assoc_id', 'assoc_type', 'about_displayed', 'seq', 'publish_email'),
	'primary_keys' => array('group_id'),
	'foreign_keys' => array(),
	'properties' => array(
		'group_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'context' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'assoc_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => 0, 'extra' => ''),
		'assoc_type' => array('type' => 'smallint(6)', 'null' => 'yes', 'key' => 'mul', 'default' => null, 'extra' => ''),
		'about_displayed' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'seq' => array('type' => 'double', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'publish_email' => array('type' => 'smallint(6)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);

$tables['group_settings'] = array(
	'attributes' => array('group_id', 'locale', 'setting_name', 'setting_value', 'setting_type'),
	'primary_keys' => array('group_id', 'locale', 'setting_name'),
	'foreign_keys' => array(),
	'properties' => array(
		'group_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'setting_name' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => null, 'extra' => ''),
	)
);

$tables['group_memberships'] = array(
	'attributes' => array('group_id', 'user_id', 'about_displayed', 'seq'),
	'primary_keys' => array('group_id', 'user_id'),
	'foreign_keys' => array(),
	'properties' => array(
		'group_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'user_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
		'about_displayed' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'seq' => array('type' => 'double', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => '')
	)
);

$tables['email_templates'] = array(
	'attributes' => array('email_id', 'email_key', 'assoc_id', 'enabled', 'assoc_type'),
	'primary_keys' => array('email_id'),
	'foreign_keys' => array('assoc_id'), // the id of the journal
	'properties' => array(
		'email_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'email_key' => array('type' => 'varchar(64)', 'null' => 'no', 'key' => 'mul', 'default' => 'not_the_mama', 'extra' => ''),
		'assoc_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => 0, 'extra' => ''),
		'enabled' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 1, 'extra' => ''),
		'assoc_type' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => 'mul', 'default' => 0, 'extra' => '')
	)
);

$tables['email_templates_data'] = array(
	'attributes' => array('email_key', 'locale', 'assoc_id', 'assoc_type', 'subject', 'body'),
	'primary_keys' => array(),
	'foreign_keys' => array('email_id', 'assoc_id'), // assoc_id is the id of the journal
	'properties' => array(
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => '', 'default' => 'en_US', 'extra' => ''),
		'email_key' => array('type' => 'varchar(64)', 'null' => 'no', 'key' => 'mul', 'default' => 'not_the_mama', 'extra' => ''),
		'assoc_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => 0, 'extra' => ''),
		'assoc_type' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => 0, 'extra' => ''),
		'subject' => array('type' => 'varchar(120)', 'null' => 'no', 'key' => '', 'default' => 'not_the_mama', 'extra' => ''),
		'body' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);



$tables['event_log'] = array(
	'attributes' => array('log_id', 'assoc_type', 'assoc_id', 'user_id', 'date_logged', 'ip_address', 'event_type', 'message', 'is_translated'),
	'primary_keys' => array('log_id'),
	'foreign_keys' => array('assoc_id', 'user_id'), // assoc id the id of the article
	'properties' => array(
		'log_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'assoc_type' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => 'mul', 'default' => 'null', 'extra' => ''),
		'assoc_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'user_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'date_logged' => array('type' => 'datetime', 'null' => 'no', 'key' => '', 'default' => '2018-01-01 13:00:00', 'extra' => ''),
		'ip_address' => array('type' => 'varchar(39)', 'null' => 'no', 'key' => '', 'default' => 'no ip', 'extra' => ''),
		'event_type' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'message' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'is_translated' => array('type' => 'tinyint(4)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);

$tables['event_log_settings'] = array(
	'attributes' => array('log_id', 'setting_name', 'setting_value', 'setting_type'),
	'primary_keys' => array('log_id', 'setting_name'),
	'foreign_keys' => array(),
	'properties' => array(
		'log_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'setting_name' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => null, 'extra' => '')
	)
);

$tables['email_log'] = array(
	'attributes' => array('log_id', 'assoc_type', 'assoc_id', 'sender_id', 'date_sent', 'ip_address', 'event_type', 'from_address', 'recipients',  'cc_recipients',
'bcc_recipients', 'subject', 'body'),
	'primary_keys' => array('log_id'),
	'foreign_keys' => array('assoc_id', 'sender_id'), // assoc id the id of the article
	'properties' => array(
		'log_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'assoc_type' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => 'mul', 'default' => null, 'extra' => ''),
		'assoc_id' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'sender_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'date_sent' => array('type' => 'datetime', 'null' => 'no', 'key' => '', 'default' => '2018-01-01 13:00:00', 'extra' => ''),
		'ip_address' => array('type' => 'varchar(39)', 'null' => 'no', 'key' => '', 'default' => 'no ip', 'extra' => ''),
		'event_type' => array('type' => 'bigint(20)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'from_address' => array('type' => 'varchar(255)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'recipients' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'cc_recipients' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'bcc_recipients' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'subject' => array('type' => 'varchar(255)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'body' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);

$tables['email_log_users'] = array(
	'attributes' => array('email_log_id', 'user_id'),
	'primary_keys' => array('email_log_id', 'user_id'),
	'foreign_keys' => array(),
	'properties' => array(
		'email_log_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
		'user_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => '')
	)
);


$tables['citations'] = array(
	'attributes' => array('citation_id', 'assoc_type', 'assoc_id', 'citation_state', 'raw_citation', 'seq', 'lock_id'),
	'primary_keys' => array('citation_id'),
	'foreign_keys' => array('assoc_id'), //assoc_id is the id of the article
	'properties' => array(
		'citation_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'assoc_type' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'assoc_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'citation_state' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'raw_citation' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'seq' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'lock_id' => array('type' => 'varchar(23)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);

$tables['citation_settings'] = array(
	'attributes' => array('citation_id', 'locale', 'setting_name', 'setting_value', 'setting_type'),
	'primary_keys' => array('citation_id', 'locale', 'setting_name'),
	'foreign_keys' => array(),
	'properties' => array(
		'citation_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'setting_name' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => null, 'extra' => '')
	)
);


$tables['referrals'] = array(
	'attributes' => array('referral_id', 'article_id', 'status', 'url', 'date_added', 'link_count'),
	'primary_keys' => array('referral_id'),
	'foreign_keys' => array('article_id'),
	'properties' => array(
		'referral_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'article_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'status' => array('type' => 'smallint(6)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'url' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => '', 'default' => 'empty url', 'extra' => ''),
		'date_added' => array('type' => 'datetime', 'null' => 'no', 'key' => '', 'default' => '2018-01-01 13:00:00', 'extra' => ''),
		'link_count' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => '')
	)
);

$tables['referral_settings'] = array(
	'attributes' => array('referral_id', 'locale', 'setting_name', 'setting_value', 'setting_type'),
	'primary_keys' => array('referral_id', 'locale', 'setting_name'),
	'foreign_keys' => array(),
	'properties' => array(
		'referral_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'setting_name' => array('type' => 'varchar(255)', 'null' => 'no', 'key' => 'pri', 'default' => 'defaultSettingName', 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => 'def', 'extra' => '')
	)
);


$tables['plugin_settings'] = array(
	'attributes' => array('plugin_name', 'locale', 'journal_id', 'setting_name', 'setting_value', 'setting_type'),
	'primary_keys' => array('plugin_name', 'locale', 'journal_id', 'setting_name'),
	'foreign_keys' => array(),
	'properties' => array(
		'plugin_name' => array('type' => 'varchar(80)', 'null' => 'no', 'key' => 'pri', 'default' => 'defaultPluginName', 'extra' => ''),
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'journal_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
		'setting_name' => array('type' => 'varchar(80)', 'null' => 'no', 'key' => 'pri', 'default' => null, 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => 'def', 'extra' => '')
	)
);


$tables['custom_issue_orders'] = array(
	'attributes' => array('issue_id', 'journal_id', 'seq'),
	'primary_keys' => array('issue_id'),
	'foreign_keys' => array('journal_id'),
	'properties' => array(
		'issue_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
		'journal_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'seq' => array('type' => 'double', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
	)
);


$tables['custom_section_orders'] = array(
	'attributes' => array('issue_id', 'section_id', 'seq'),
	'primary_keys' => array('issue_id', 'section_id'),
	'foreign_keys' => array(),
	'properties' => array(
		'issue_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
		'section_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
		'seq' => array('type' => 'double', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
	)
);


$tables['issues'] = array(
	'attributes' => array('issue_id', 'journal_id', 'volume', 'number', 'year', 'published', 'current', 'date_published', 'date_notified', 'access_status', 
	'open_access_date', 'show_volume', 'show_number', 'show_year', 'show_title', 'style_file_name', 'original_style_file_name', 'last_modified'),
	'primary_keys' => array('issue_id'),
	'foreign_keys' => array('journal_id'),
	'properties' => array(
		'issue_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => 'auto_increment'),
		'journal_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'mul', 'default' => 0, 'extra' => ''),
		'volume' => array('type' => 'smallint(6)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'number' => array('type' => 'varchar(10)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'year' => array('type' => 'smallint(6)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'published' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'current' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'date_published' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'date_notified' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'access_status' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 1, 'extra' => ''),
		'open_access_date' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'show_volume' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'show_number' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'show_year' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'show_title' => array('type' => 'tinyint(4)', 'null' => 'no', 'key' => '', 'default' => 0, 'extra' => ''),
		'style_file_name' => array('type' => 'varchar(90)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'original_style_file_name' => array('type' => 'varchar(255)', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'last_modified' => array('type' => 'datetime', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => '')
	)
);

$tables['issue_settings'] = array(
	'attributes' => array('locale', 'issue_id', 'setting_name', 'setting_value', 'setting_type'),
	'primary_keys' => array('locale', 'issue_id', 'setting_name'),
	'foreign_keys' => array(),
	'properties' => array(
		'locale' => array('type' => 'varchar(5)', 'null' => 'no', 'key' => 'pri', 'default' => '', 'extra' => ''),
		'issue_id' => array('type' => 'bigint(20)', 'null' => 'no', 'key' => 'pri', 'default' => 0, 'extra' => ''),
		'setting_name' => array('type' => 'varchar(80)', 'null' => 'no', 'key' => 'pri', 'default' => 'defaultSettingName', 'extra' => ''),
		'setting_value' => array('type' => 'text', 'null' => 'yes', 'key' => '', 'default' => null, 'extra' => ''),
		'setting_type' => array('type' => 'varchar(6)', 'null' => 'no', 'key' => '', 'default' => 'def', 'extra' => '')
	)
);



$idFields = array();
foreach ($tables as $type => $arr) {
	
	if (!array_key_exists('primary_keys', $arr)) {
		print_r($arr);
		exit("$type does not have the array primary_keys");
	}
	foreach ($arr['primary_keys'] as $pk) {
		if (!in_array($pk, $idFields) && isValidId($pk) && !isSettings($type)) {
			array_push($idFields, $pk);
		}
	}
	
	
	if (!array_key_exists('foreign_keys', $arr)) {
		print_r($arr);
		exit("$type does not have the array foreign_keys");
	}
	foreach ($arr['foreign_keys'] as $fk) {
		if (!in_array($fk, $idFields) && isValidId($fk) && !isSettings($type)) {
			array_push($idFields, $fk);
		}
	}
	
	
	if (!array_key_exists('attributes', $arr)) {
		print_r($arr);
		exit("$type does not have the array attributes");
	}
	foreach ($arr['attributes'] as $attr) {
		if (!in_array($attr, $idFields) && isFileId($attr) && !isSettings($type)) {
			array_push($idFields, $attr);
		}
	}
	
}


$updateFields = $idFields;

//array_push($updateFields, 'comment_author_id');
array_push($updateFields, 'file_name');
array_push($updateFields, 'original_file_name');


//set the queries
$queries = array();


////////////////  SELECT  /////////////////////////////////////////////////////////////////

/////////// user related queries ///////////////////
$queries['selectUsernameCount'] = array(
	'query' => 'SELECT COUNT(*) as count FROM users WHERE username = :selectUsernameCount_username',
	'params' => array(
		'username' => array('name' => ':selectUsernameCount_username', 'type' => PDO::PARAM_STR)
	)
);

$queries['selectUserByEmail'] = array(
	'query' => 'SELECT * FROM users WHERE email = :selectUserByEmail_email',
	'params' => array(
		'email' => array('name' => ':selectUserByEmail_email', 'type' => PDO::PARAM_STR)
	)
);

$queries['selectLastTenUsers'] = array(
	'query' => 'SELECT * FROM users ORDER BY user_id DESC LIMIT 10',
	'params' => null
);

$queries['selectUserById'] = array(
	'query' => 'SELECT * FROM users WHERE user_id = :selectUserById_userId'
	'params' => array(
		'user_id' => 'name' => ':selectUserById_userId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectUserSettings'] = array(
	'query' => 'SELECT * FROM user_settings WHERE user_id = :selectUserSettings_userId',
	'params' => array(
		'user_id' => array('name' => ':selectUserSettings_userId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectUserRoles'] = array(
	'query' => 'SELECT * FROM roles WHERE journal_id = :selectUserRoles_journalId AND user_id = :selectUserRoles_userId',
	'params' => array(
		'journal_id' => array('name' => ':selectUserRoles_journalId', 'type' => PDO::PARAM_INT),
		'user_id' => array('name' => ':selectUserRoles_userId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectUserInterests'] = array(
	'query' => 'SELECT t.setting_value AS interest, u_int.controlled_vocab_entry_id AS controlled_vocab_entry_id 
		FROM user_interests AS u_int
		INNER JOIN controlled_vocab_entry_settings AS t
			ON u_int.controlled_vocab_entry_id = t.controlled_vocab_entry_id
		WHERE u_int.user_id = :selectUserInterests_userId',
		
	'params' => array(
		'user_id' => array('name' => ':selectUserInterests_userId', 'type' => PDO::PARAM_INT)
	)
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
		
	'params' => array(
		'journal_id' => array('name' => ':selectPublishedArticles_journalId', 'type' => PDO::PARAM_INT)
	)
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
		'journal_id' => array('name' => ':selectPublishedArticleBySetting_journalId', 'type' => PDO::PARAM_INT),
		'locale' => array('name' => ':selectPublishedArticleBySetting_locale', 'type' => PDO::PARAM_STR),
		'setting_name' => array('name' => ':selectPublishedArticleBySetting_settingName', 'type' => PDO::PARAM_STR),
		'setting_value' => array('name' => ':selectPublishedArticleBySetting_settingValue', 'type' => PDO::PARAM_STR)
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
		'journal_id' => array('name' => ':countPublishedArticleBySetting_journalId', 'type' => PDO::PARAM_INT),
		'locale' => array('name' => ':countPublishedArticleBySetting_locale', 'type' => PDO::PARAM_STR),
		'setting_name' => array('name' => ':countPublishedArticleBySetting_settingName', 'type' => PDO::PARAM_STR),
		'setting_value' => array('name' => ':countPublishedArticleBySetting_settingValue', 'type' => PDO::PARAM_STR)
	)
);

$queries['selectUnpublishedArticles'] = array(
	'query' => 'SELECT * FROM articles WHERE article_id IN (
			SELECT article_id FROM articles WHERE article_id NOT IN (
				SELECT article_id FROM published_articles
			) AND journal_id = :selectUnpublishedArticles_journalId
		)',
		
	'params' => array(
		'journal_id' => array('name' => ':selectUnpublishedArticles_journalId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectArticles'] = array(
	'query' => 'SELECT art.*, pub_art.* 
		 FROM articles AS art
		 LEFT JOIN published_articles AS pub_art
			ON pub_art.article_id = art.article_id
		 WHERE art.journal_id = :selectArticles_journalId',
	
	'params' => array(
		'journal_id' => array('name' => ':selectArticles_journalId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectAuthors'] = array(
	'query' => 'SELECT * FROM authors WHERE submission_id = :selectAuthors_submissionId',
	'params' => array(
		'submission_id' => array('name' => ':selectAuthors_submissionId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectAuthorByEmail'] = array(
	'query' => 'SELECT * FROM authors WHERE email = :selectAuthorByEmail_email',
	'params' => array(
		'email' => array('name' => ':selectAuthorByEmail_email', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectAuthorSettings'] = array(
	'query' => 'SELECT * FROM author_settings WHERE author_id = :selectAuthorSettings_authorId',
	'params' => array(
		'author_id' => array('name' => ':selectAuthorSettings_authorId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectLastAuthor'] = array(
	'query' => 'SELECT * FROM authors ORDER BY author_id DESC LIMIT 1',
	'params' => null
);

$queries['selectArticleSettings'] = array(
	'query' => 'SELECT * FROM article_settings WHERE article_id = :selectArticleSettings_articleId',
	'params' => array(
		'article_id' => array('name' => ':selectArticleSettings_articleId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectArticleFiles'] = array(
	'query' => 'SELECT * FROM article_files WHERE article_id = :selectArticleFiles_articleId',
	'params' => array(
		'article_id' => array('name' => ':selectArticleFiles_articleId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectLastArticleFile'] = array(
	'query' => 'SELECT * FROM article_files ORDER BY file_id DESC LIMIT 1',
	'params' => null
);

$queries['selectArticleSupplementaryFiles'] = array(
	'query' => 'SELECT * FROM article_supplementary_files WHERE article_id = :selectArticleSupplementaryFiles_articleId',
	'params' => array(
		'article_id' => array('name' => ':selectArticleSupplementaryFiles_articleId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectLastArticleSupplementaryFile'] = array(
	'query' => 'SELECT * FROM article_supplementary_files ORDER BY supp_id DESC LIMIT 1',
	'params' => null
);

$queries['selectArticleSuppFileSettings'] = array(
	'query' => 'SELECT * FROM article_supp_file_settings WHERE supp_id = :selectArticleSuppFileSettings_suppId',
	'params' => array(
		'supp_id' => array('name' => ':selectArticleSuppFileSettings_suppId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectArticleComments'] = array(
	'query' => 'SELECT * FROM article_comments WHERE article_id = :selectArticleComments_articleId',
	'params' => array(
		'article_id' => array('name' => ':selectArticleComments_articleId', 'type' => PDO::PARAM_INT)
	)
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
	'params' => array(
		'article_id' => array('name' => ':selectArticleGalleys_articleId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectLastArticleGalley'] = array(
	'query' => 'SELECT * FROM article_galleys ORDER BY galley_id DESC LIMIT 1',
	'params' => null
);

$queries['selectArticleGalleySettings'] = array(
	'query' => 'SELECT * FROM article_galley_settings WHERE galley_id = :selectArticleGalleySettings_galleyId',
	'params' => array(
		'galley_id' => array('name' => ':selectArticleGalleySettings_galleyId', 'type' => PDO::PARAM_INT)
	)
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
	'params' => array(
		'galley_id' => array('name' => ':selectHtmlGalleyImages_galleyId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectArticleSearchKeywordLists'] = array(
	'query' => 'SELECT * FROM article_search_keyword_list WHERE keyword_id = :selectArticleSearchKeywordLists_keywordId',
	'params' => array(
		'keyword_id' => array('name' => ':selectArticleSearchKeywordLists_keywordId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectLastArticleSearchKeywordList'] = array(
	'query' => 'SELECT * FROM article_search_keyword_list ORDER BY keyword_id DESC LIMIT 1',
	'params' => null
);

$queries['selectArticleSearchObjectKeywords'] = array(
	'query' => 'SELECT * FROM article_search_object_keywords WHERE object_id = :selectArticleSearchObjectKeywords_objectId'
	'params' => array(
		'object_id' => array('name' => ':selectArticleSearchObjectKeywords_objectId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectArticleSearchObjects'] = array(
	'query' => 'SELECT * FROM article_search_objects WHERE article_id = :selectArticleSearchObjects_articleId',
	'params' => array(
		'article_id' => array('name' => ':selectArticleSearchObjects_articleId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectLastArticleSearchObject'] = array(
	'query' => 'SELECT * FROM article_search_objects ORDER BY object_id DESC LIMIT 1',
	'params' => null
);

$queries['selectEditDecisions'] = array(
	'query' => 'SELECT * FROM edit_decisions WHERE article_id = :selectEditDecisions_articleId',
	'params' => array(
		'article_id' => array('name' => ':selectEditDecisions_articleId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectLastEditDecision'] = array(
	'query' => 'SELECT * FROM edit_decisions ORDER BY edit_decision_id DESC LIMIT 1',
	'params' => null
);

$queries['selectEditAssignments'] = array(
	'query' => 'SELECT * FROM edit_assignments WHERE article_id = :selectEditAssignments_articleId',
	'params' => array(
		'article_id' => array('name' => ':selectEditAssignments_articleId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectLastEditAssignment'] = array(
	'query' => 'SELECT * FROM edit_assignments ORDER BY edit_id DESC LIMIT 1',
	'params' => null
);

$queries['selectReviewAssignments'] = array(
	'query' => 'SELECT * FROM review_assignments WHERE submission_id = :selectReviewAssignments_submissionId',
	'params' => array(
		'submission_id' => array('name' => ':selectReviewAssignments_submissionId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectLastReviewAssignment'] = array(
	'query' => 'SELECT * FROM review_assignments ORDER BY review_id DESC LIMIT 1',
	'params' => null
);

$queries['selectReviewRounds'] = array(
	'query' => 'SELECT * FROM review_rounds WHERE submission_id = :selectReviewRounds_submissionId'
	'params' => array(
		'submission_id' => array('name' => ':selectReviewRounds_submissionId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectLastReviewRound'] = array(
	'query' => 'SELECT * FROM review_rounds ORDER BY review_round_id DESC LIMIT 1',
	'params' => null
);

//review_form_responses
$queries['selectReviewFormResponses'] = array(
	'query' => 'SELECT * FROM review_form_responses WHERE review_id = :selectReviewFormResponses_reviewId',
	'params' => array(
		'review_id' => array('name' => ':selectReviewFormResponses_reviewId', 'type' => PDO::PARAM_INT)
	)
);


////////// section related //////////////
$queries['selectSections'] = array(
	'query' => 'SELECT * FROM sections WHERE journal_id = :selectSections_journalId',
	'params' => array(
		'journal_id' => array('name' => ':selectSections_journalId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectSectionSettings'] = array(
	'query' => 'SELECT * FROM section_settings WHERE section_id = :selectSectionSettings_sectionId'
	'params' => array(
		'section_id' => array('name' => ':selectSectionSettings_sectionId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectSectionTitlesAndAbbrevs'] = array(
	'query' => 'SELECT section_id, setting_name, setting_value, locale 
	FROM section_settings 
	WHERE section_id = :selectSectionTitlesAndAbbrevs_sectionId AND setting_name IN ("title", "abbrev")',
	
	'params' => array(
		'section_id' => array('name' => ':selectSectionTitlesAndAbbrevs_sectionId', 'type' => PDO::PARAM_INT)
	)
);


////// announcements related //////////////
$queries['selectAnnouncements'] = array(
	'query' => 'SELECT * FROM announcements WHERE assoc_id = :selectAnnouncements_journalId',
	'params' => array(
		'assoc_id' => array('name' => ':selectAnnouncements_journalId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectAnnouncementSettings'] = array(
	'query' => 'SELECT * FROM announcement_settings WHERE announcement_id = :selectAnnouncementSettings_announcementId',
	'params' => array(
		'announcement_id' => array('name' => ':selectAnnouncementSettings_announcementId', 'type' => PDO::PARAM_INT)
	)
);


//////// groups related  ////////////////////
$queries['selectGroups'] = array(
	'query' => 'SELECT * FROM groups WHERE assoc_id = :selectGroups_journalId',
	'params' => array(
		'assoc_id' => array('name' => ':selectGroups_journalId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectGroupSettings'] = array(
	'query' => 'SELECT * FROM group_settings WHERE group_id = :selectGroupSettings_groupId',
	'params' => array(
		'group_id' => array('name' => ':selectGroupSettings_groupId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectGroupMemberships'] = array(
	'query' => 'SELECT * FROM group_memberships WHERE group_id = :selectGroupMemberships_groupId'
	'params' => array(
		'group_id' => array('name' => ':selectGroupMemberships_groupId', 'type' => PDO::PARAM_INT)
	)
);


//////// review_forms related //////////////
$queries['selectReviewForms'] = array(
	'query' => 'SELECT * FROM review_forms WHERE assoc_id = :selectReviewForms_assocId',
	'params' => array(
		'assoc_id' => array('name' => ':selectReviewForms_assocId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectLastReviewForm'] = array(
	'query' => 'SELECT * FROM review_forms ORDER BY review_form_id DESC LIMIT 1',
	'params' => null
);

$queries['selectReviewFormSettings'] = array(
	'query' => 'SELECT * FROM review_form_settings WHERE review_form_id = :selectReviewFormSettings_reviewFormId',
	'params' => array(
		'review_form_id' => array('name' => ':selectReviewFormSettings_reviewFormId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectReviewFormElements'] = array(
	'query' => 'SELECT * FROM review_form_elements WHERE review_form_id = :selectReviewFormElements_reviewFormId',
	'params' => array(
		'review_form_id' => array('name' => ':selectReviewFormElements_reviewFormId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectLastReviewFormElement'] = array(
	'query' => 'SELECT * FROM review_form_elements ORDER BY review_form_element_id DESC LIMIT 1',
	'params' => null
);

$queries['selectReviewFormElementSettings'] = array(
	'query' => 'SELECT * FROM review_form_element_settings WHERE review_form_element_id = :selectReviewFormElementSettings_reviewFormElementId',
	'params' => array(
		'review_form_element_id' => array('name' => ':selectReviewFormElementSettings_reviewFormElementId', 'type' => PDO::PARAM_INT)
	)
);

//the review_form_response queries are at the final of the article related select queries



/////////////  issues related  //////////////////////
$queries['selectIssues'] = array(
	'query' => 'SELECT * FROM issues WHERE journal_id = :selectIssues_journalId',
	'params' => array(
		'journal_id' => array('name' => ':selectIssues_journalId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectIssueSettings'] = array(
	'query' => 'SELECT * FROM issue_settings WHERE issue_id = :selectIssueSettings_issueId',
	'params' => array(
		'issue_id' => array('name' => ':selectIssueSettings_issueId', 'type' => PDO::PARAM_INT)
	)
);
	
$queries['selectCustomIssueOrders'] = array(
	'query' => 'SELECT * FROM custom_issue_orders WHERE journal_id = :selectCustomIssueOrders_journalId AND issue_id = :selectCustomIssueOrders_issueId',
	'params' => array(
		'journal_id' => array('name' => ':selectCustomIssueOrders_journalId', 'type' => PDO::PARAM_INT),
		'issue_id' => array('name' => ':selectCustomIssueOrders_issueId', 'type' => PDO::PARAM_INT)
	)
);

$queries['selectCustomSectionOrders'] = array(
	'query' => 'SELECT * FROM custom_section_orders WHERE issue_id = :selectCustomSectionOrders_issueId',
	'params' => array(
		'issue_id' => array('name' => ':selectCustomSectionOrders_issueId', 'type' => PDO::PARAM_INT)
	)
);


////////// plugin_settings ////////////
$queries['selectPluginSettings'] = array(
	'query' => 'SELECT * FROM plugin_settings WHERE journal_id = :selectPluginSettings_journalId',
	'params' => array(
		'journal_id' => array('name' => ':selectPluginSettings_journalId', 'type' => PDO::PARAM_INT)
	)
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
	'params' => array(
		'log_id' => array('name' => ':selectEventLogSettings_logId', 'type' => PDO::PARAM_INT)
	)
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
	'params' => array(
		'email_log_id' => array('name' => ':selectEmailLogUsers_emailLogId', 'type' => PDO::PARAM_INT)
	)
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
		'username' => array('name' => ':insertUser_username', 'type' => PDO::PARAM_STR), 
		'password' => array('name' => ':insertUser_password', 'type' => PDO::PARAM_STR), 
		'salutation' => array('name' => ':insertUser_salutation', 'type' => PDO::PARAM_STR), 
		'first_name' => array('name' => ':insertUser_firstName', 'type' => PDO::PARAM_STR), 
		'middle_name' => array('name' => ':insertUser_middleName', 'type' => PDO::PARAM_STR), 
		'last_name' => array('name' => ':insertUser_lastName', 'type' => PDO::PARAM_STR), 
		'gender' => array('name' => ':insertUser_gender', 'type' => PDO::PARAM_STR), 
		'initials' => array('name' => ':insertUser_initials', 'type' => PDO::PARAM_STR), 
		'email' => array('name' => ':insertUser_email', 'type' => PDO::PARAM_STR), 
		'url' => array('name' => ':insertUser_url', 'type' => PDO::PARAM_STR), 
		'phone' => array('name' => ':insertUser_phone', 'type' => PDO::PARAM_STR), 
		'fax' => array('name' => ':insertUser_fax', 'type' => PDO::PARAM_STR),
		'mailing_address' => array('name' => ':insertUser_mailingAddress', 'type' => PDO::PARAM_STR), 
		'country' => array('name' => ':insertUser_country', 'type' => PDO::PARAM_STR), 
		'locales' => array('name' => ':insertUser_locales', 'type' => PDO::PARAM_STR), 
		'date_last_email' => array('name' => ':insertUser_dateLastEmail', 'type' => PDO::PARAM_STR),
		'date_registered' => array('name' => ':insertUser_dateRegistered', 'type' => PDO::PARAM_STR), 
		'date_validated' => array('name' => ':insertUser_dateValidated', 'type' => PDO::PARAM_STR), 
		'date_last_login' => array('name' => ':insertUser_dateLastLogin', 'type' => PDO::PARAM_STR), 
		'must_change_password' => array('name' => ':insertUser_mustChangePassword', 'type' => PDO::PARAM_INT), 
		'auth_id' => array('name' => ':insertUser_authId', 'type' => PDO::PARAM_INT), 
		'disabled' => array('name' => ':insertUser_disabled', 'type' => PDO::PARAM_INT), 
		'disabled_reason' => array('name' => ':insertUser_disabledReason', 'type' => PDO::PARAM_STR), 
		'auth_str' => array('name' => ':insertUser_authStr', 'type' => PDO::PARAM_STR), 
		'suffix' => array('name' => ':insertUser_suffix', 'type' => PDO::PARAM_STR), 
		'billing_address' => array('name' => ':insertUser_billingAddress', 'type' => PDO::PARAM_STR), 
		'inline_help' => array('name' => ':insertUser_inlineHelp', 'type' => PDO::PARAM_INT)
	)
);

$queries['insertUserSetting'] = array(
	'query' => 'INSERT INTO user_settings (user_id, locale, setting_name, setting_value, setting_type, assoc_id, assoc_type)
			VALUES (:insertUserSetting_userId, :insertUserSetting_locale, :insertUserSetting_settingName, :insertUserSetting_settingValue, :insertUserSetting_settingType,
			:insertUserSetting_assocId, :insertUserSetting_assocType)',
			
	'params' => array(
		'user_id' => array('name' => ':insertUserSetting_userId', 'type' => PDO::PARAM_INT),
		'locale' => array('name' => ':insertUserSetting_locale', 'type' => PDO::PARAM_STR),
		'setting_name' => array('name' => ':insertUserSetting_settingName', 'type' => PDO::PARAM_STR),
		'setting_value' => array('name' => ':insertUserSetting_settingValue', 'type' => PDO::PARAM_STR),
		'setting_type' => array('name' => ':insertUserSetting_settingType', 'type' => PDO::PARAM_STR),
		'assoc_id' => array('name' => ':insertUserSetting_assocId', 'type' => PDO::PARAM_INT),
		'assoc_type' => array('name' => ':insertUserSetting_assocType', 'type' => PDO::PARAM_INT)
	)
);

$queries['insertUserRole'] = array(
	'query' => 'INSERT INTO roles (journal_id, user_id, role_id) 
	VALUES (:insertUserRole_journalId, :insertUserRole_userId, :insertUserRole_roleId)',
	
	'params' => array(
		'journal_id' => array('name' => ':insertUserRole_journalId', 'type' => PDO::PARAM_INT),
		'user_id' => array('name' => ':insertUserRole_userId', 'type' => PDO::PARAM_INT),
		'role_id' => array('name' => ':insertUserRole_roleId', 'type' => PDO::PARAM_INT)
	)
);

$queries['insertControlledVocabEntry'] = array(
	'query' => 'INSERT INTO controlled_vocab_entries (controlled_vocab_id, seq) 
	VALUES (:insertControlledVocabEntry_controlledVocabId, :insertControlledVocabEntry_seq)'
	
	'params' => array(
		'controlled_vocab_id' => array('name' => ':insertControlledVocabEntry_controlledVocabId', 'type' => PDO::PARAM_INT),
		'seq' => array('name' => ':insertControlledVocabEntry_seq', 'type' => PDO::PARAM_STR)
	)
);

$queries['insertControlledVocabEntrySetting'] = array(
	'query' => 'INSERT INTO controlled_vocab_entry_settings (controlled_vocab_entry_id, locale, setting_name, setting_value, setting_type)
	VALUES (:insertControlledVocabEntrySetting_controlledVocabEntryId, :insertControlledVocabEntrySetting_locale,
	:insertControlledVocabEntrySetting_settingName, :insertControlledVocabEntrySetting_settingValue, :insertControlledVocabEntrySetting_settingType)',
	
	'params' => array(
		'controlled_vocab_entry_id' => array('name' => ':insertControlledVocabEntrySetting_controlledVocabEntryId', 'type' => PDO::PARAM_INT),
		'locale' => array('name' => ':insertControlledVocabEntrySetting_locale', 'type' => PDO::PARAM_STR),
		'setting_name' => array('name' => ':insertControlledVocabEntrySetting_settingName', 'type' => PDO::PARAM_STR),
		'setting_value' => array('name' => ':insertControlledVocabEntrySetting_settingValue', 'type' => PDO::PARAM_STR),
		'setting_type' => array('name' => ':insertControlledVocabEntrySetting_settingType', 'type' => PDO::PARAM_STR)
	)
);

$queries['insertUserInterest'] = array(
	'query' => 'INSERT INTO user_interests (controlled_vocab_entry_id, user_id) 
	VALUES (:insertUserInterest_controlledVocabEntryId, :insertUserInterest_userId)',
	
	'params' => array(
		'controlled_vocab_entry_id' => array('name' => ':insertUserInterest_controlledVocabEntryId', 'type' => PDO::PARAM_INT),
		'user_id' => array('name' => ':insertUserInterest_userId', 'type' => PDO::PARAM_INT)
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
		'user_id' => array('name' => ':insertArticle_userId', 'type' => PDO::PARAM_INT),
		'journal_id' => array('name' => ':insertArticle_journalId', 'type' => PDO::PARAM_INT),
		'section_id' => array('name' => ':insertArticle_sectionId', 'type' => PDO::PARAM_INT),
		'language' => array('name' => ':insertArticle_language', 'type' => PDO::PARAM_STR),
		'comments_to_ed' => array('name' => ':insertArticle_commentsToEd', 'type' => PDO::PARAM_STR),
		'date_submitted' => array('name' => ':insertArticle_dateSubmitted', 'type' => PDO::PARAM_STR),
		'last_modified' => array('name' => ':insertArticle_lastModified', 'type' => PDO::PARAM_STR),
		'date_status_modified' => array('name' => ':insertArticle_dateStatusModified', 'type' => PDO::PARAM_STR),
		'status' => array('name' => ':insertArticle_status', 'type' => PDO::PARAM_INT),
		'submission_progress' => array('name' => ':insertArticle_submissionProgress', 'type' => PDO::PARAM_INT),
		'current_round' => array('name' => ':insertArticle_currentRound', 'type' => PDO::PARAM_INT),
		'pages' => array('name' => ':insertArticle_pages', 'type' => PDO::PARAM_STR),
		'fast_tracked' => array('name' => ':insertArticle_fastTracked', 'type' => PDO::PARAM_INT),
		'hide_author' => array('name' => ':insertArticle_hideAuthor', 'type' => PDO::PARAM_INT),
		'comments_status' => array('name' => ':insertArticle_commentsStatus', 'type' => PDO::PARAM_INT),
		'locale' => array('name' => ':insertArticle_locale', 'type' => PDO::PARAM_STR),
		'citations' => array('name' => ':insertArticle_citations', 'type' => PDO::PARAM_STR)
	)
);

$queries['insertArticleSetting'] = array(
	'query' => 'INSERT INTO article_settings (article_id, locale, setting_name, setting_value, setting_type) VALUES 
		(:insertArticleSetting_articleId, :insertArticleSetting_locale, :insertArticleSetting_settingName, :insertArticleSetting_settingValue, :insertArticleSetting_settingType)',
		
	'params' => array(
		'article_id' => array('name' => ':insertArticleSetting_articleId', 'type' => PDO::PARAM_INT),
		'locale' => array('name' => ':insertArticleSetting_locale', 'type' => PDO::PARAM_STR),
		'setting_name' => array('name' => ':insertArticleSetting_settingName', 'type' => PDO::PARAM_STR),
		'setting_value' => array('name' => ':insertArticleSetting_settingValue', 'type' => PDO::PARAM_STR),
		'setting_type' => array('name' => ':insertArticleSetting_settingType', 'type' => PDO::PARAM_STR)
	)
);

$queries['insertAuthor'] = array(
	'query' => 'INSERT INTO authors (submission_id, primary_contact, seq, first_name, middle_name, last_name, country, email, url, suffix) 
		VALUES (:insertAuthor_submissionId, :insertAuthor_primaryContact, :insertAuthor_seq, :insertAuthor_firstName, :insertAuthor_middleName, 
		:insertAuthor_lastName, :insertAuthor_country, :insertAuthor_email, :insertAuthor_url, :insertAuthor_suffix)',
		
	'params' => array(
		'submission_id' => array('name' => ':insertAuthor_submissionId', 'type' => PDO::PARAM_INT),
		'primary_contact' => array('name' => ':insertAuthor_primaryContact', 'type' => PDO::PARAM_INT),
		'seq' => array('name' => ':insertAuthor_seq', 'type' => PDO::PARAM_STR),
		'first_name' => array('name' => ':insertAuthor_firstName', 'type' => PDO::PARAM_STR),
		'middle_name' => array('name' => ':insertAuthor_middleName', 'type' => PDO::PARAM_STR),
		'last_name' => array('name' => ':insertAuthor_lastName', 'type' => PDO::PARAM_STR),
		'country' => array('name' => ':insertAuthor_country', 'type' => PDO::PARAM_STR),
		'email' => array('name' => ':insertAuthor_email', 'type' => PDO::PARAM_STR),
		'url' => array('name' => ':insertAuthor_url', 'type' => PDO::PARAM_STR),
		'suffix' => array('name' => ':insertAuthor_suffix', 'type' => PDO::PARAM_STR)
	)
);

$queries['insertAuthorSetting'] = array(
	'query' => 'INSERT INTO author_settings (author_id, locale, setting_name, setting_value, setting_type) VALUES (:insertAuthorSettings_authorId,
		:insertAuthorSettings_locale, :insertAuthorSettings_settingName, :insertAuthorSettings_settingValue, :insertAuthorSettings_settingType)',
		
	'params' => array(
		'author_id' => array('name' => ':insertAuthorSettings_authorId', 'type' => PDO::PARAM_INT),
		'locale' => array('name' => ':insertAuthorSettings_locale', 'type' => PDO::PARAM_STR),
		'setting_name' => array('name' => ':insertAuthorSettings_settingName', 'type' => PDO::PARAM_STR),
		'setting_value' => array('name' => ':insertAuthorSettings_settingValue', 'type' => PDO::PARAM_STR),
		'setting_type' => array('name' => ':insertAuthorSettings_settingType', 'type' => PDO::PARAM_STR)
	)
);


$queries['insertArticleFile'] = array(
	'query' => 'INSERT INTO article_files (revision, source_revision, article_id, file_name, file_type, file_size, original_file_name, file_stage, viewable,
		date_uploaded, date_modified, round, assoc_id) VALUES (:insertArticleFile_revision, :insertArticleFile_sourceRevision, :insertArticleFile_articleId, 
		:insertArticleFile_fileName, :insertArticleFile_fileType, :insertArticleFile_fileSize, :insertArticleFile_originalFileName, :insertArticleFile_fileStage, 
		:insertArticleFile_viewable, :insertArticleFile_dateUploaded, :insertArticleFile_dateModified, :insertArticleFile_round, :insertArticleFile_assocId)',
		
	'params' => array(
		'revision' => array('name' => ':insertArticleFile_revision', 'type' => PDO::PARAM_INT),
		'source_revision' => array('name' => ':insertArticleFile_sourceRevision', 'type' => PDO::PARAM_INT),
		'article_id' => array('name' => ':insertArticleFile_articleId', 'type' => PDO::PARAM_INT),
		'file_name' => array('name' => ':insertArticleFile_fileName', 'type' => PDO::PARAM_STR),
		'file_type' => array('name' => ':insertArticleFile_fileType', 'type' => PDO::PARAM_STR),
		'file_size' => array('name' => ':insertArticleFile_fileSize', 'type' => PDO::PARAM_INT),
		'original_file_name' => array('name' => ':insertArticleFile_originalFileName', 'type' => PDO::PARAM_STR),
		'file_stage' => array('name' => ':insertArticleFile_fileStage', 'type' => PDO::PARAM_INT),
		'viewable' => array('name' => ':insertArticleFile_viewable', 'type' => PDO::PARAM_INT),
		'date_uploaded' => array('name' => ':insertArticleFile_dateUploaded', 'type' => PDO::PARAM_STR),
		'date_modified' => array('name' => ':insertArticleFile_dateModified', 'type' => PDO::PARAM_STR),
		'round' => array('name' => ':insertArticleFile_round', 'type' => PDO::PARAM_INT),
		'assoc_id' => array('name' => ':insertArticleFile_assocId', 'type' => PDO::PARAM_INT)
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
		'file_id' => array('name' => ':insertArticleRevisedFile_fileId', 'type' => PDO::PARAM_INT),
		'revision' => array('name' => ':insertArticleRevisedFile_revision', 'type' => PDO::PARAM_INT),
		'article_id' => array('name' => ':insertArticleRevisedFile_articleId', 'type' => PDO::PARAM_INT),
		'file_name' => array('name' => ':insertArticleRevisedFile_fileName', 'type' => PDO::PARAM_STR),
		'file_type' => array('name' => ':insertArticleRevisedFile_fileType', 'type' => PDO::PARAM_STR),
		'file_size' => array('name' => ':insertArticleRevisedFile_fileSize', 'type' => PDO::PARAM_INT),
		'original_file_name' => array('name' => ':insertArticleRevisedFile_originalFileName', 'type' => PDO::PARAM_STR),
		'file_stage' => array('name' => ':insertArticleRevisedFile_fileStage', 'type' => PDO::PARAM_INT),
		'viewable' => array('name' => ':insertArticleRevisedFile_viewable', 'type' => PDO::PARAM_INT),
		'date_uploaded' => array('name' => ':insertArticleRevisedFile_dateUploaded', 'type' => PDO::PARAM_STR),
		'date_modified' => array('name' => ':insertArticleRevisedFile_dateModified', 'type' => PDO::PARAM_STR),
		'round' => array('name' => ':insertArticleRevisedFile_round', 'type' => PDO::PARAM_INT),
		'assoc_id' => array('name' => ':insertArticleRevisedFile_assocId', 'type' => PDO::PARAM_INT)
	)
);

$queries['insertArticleSupplementaryFile'] = array(
	'query' => 'INSERT INTO article_supplementary_files (file_id, article_id, type, language, date_created, show_reviewers, date_submitted, seq, remote_url) 
		VALUES (:insertArticleSupplementaryFile_fileId, :insertArticleSupplementaryFile_articleId, :insertArticleSupplementaryFile_type, 
		:insertArticleSupplementaryFile_language, :insertArticleSupplementaryFile_dateCreated, :insertArticleSupplementaryFile_showReviewers, 
		:insertArticleSupplementaryFile_dateSubmitted, :insertArticleSupplementaryFile_seq, :insertArticleSupplementaryFile_remoteUrl)'
		
	'params' => array(
		'file_id' => array('name' => ':insertArticleSupplementaryFile_fileId', 'type' => PDO::PARAM_INT),
		'article_id' => array('name' => ':insertArticleSupplementaryFile_articleId', 'type' => PDO::PARAM_INT),
		'type' => array('name' => ':insertArticleSupplementaryFile_type', 'type' => PDO::PARAM_STR),
		'language' => array('name' => 'insertArticleSupplementaryFile_language', 'type' => PDO::PARAM_STR),
		'date_created' => array('name' => ':insertArticleSupplementaryFile_dateCreated', 'type' => PDO::PARAM_STR),
		'show_reviewers' => array('name' => ':insertArticleSupplementaryFile_showReviewers', 'type' => PDO::PARAM_INT),
		'date_submitted' => array('name' => ':insertArticleSupplementaryFile_dateSubmitted', 'type' => PDO::PARAM_STR),
		'seq' => array('name' => ':insertArticleSupplementaryFile_seq', 'type' => PDO::PARAM_STR),
		'remote_url' => array('name' => ':insertArticleSupplementaryFile_remoteUrl', 'type' => PDO::PARAM_STR)
	)
);

$queries['insertArticleSuppFileSetting'] = array(
	'query' => 'INSERT INTO article_supp_file_settings (supp_id, locale, setting_name, setting_value, setting_type) VALUES (:insertArticleSuppFileSetting_suppId,
		:insertArticleSuppFileSetting_locale, :insertArticleSuppFileSetting_settingName, :insertArticleSuppFileSetting_settingValue, :insertArticleSuppFileSetting_settingType)'
		
	'params' => array(
		'supp_id' => array('name' => ':insertArticleSuppFileSetting_suppId', 'type' => PDO::PARAM_INT),
		'locale' => array('name' => ':insertArticleSuppFileSetting_locale', 'type' => PDO::PARAM_STR),
		'setting_name' => array('name' => ':insertArticleSuppFileSetting_settingName', 'type' => PDO::PARAM_STR),
		'setting_value' => array('name' => ':insertArticleSuppFileSetting_settingValue', 'type' => PDO::PARAM_STR),
		'setting_type' => array('name' => ':insertArticleSuppFileSetting_settingType', 'type' => PDO::PARAM_STR)
	)
);

$queries['insertArticleNote'] = array(
	'query' => 'INSERT INTO article_notes (article_id, user_id, date_created, date_modified, title, note, file_id) VALUES (:insertArticleNote_articleId, :insertArticleNote_userId,
		:insertArticleNote_dateCreated, :insertArticleNote_dateModified, :insertArticleNote_title, :insertArticleNote_note, :insertArticleNote_fileId)',
	
	'params' => array(
		'article_id' => array('name' => ':insertArticleNote_articleId', 'type' => PDO::PARAM_INT),
		'user_id' => array('name' => ':insertArticleNote_userId', 'type' => PDO::PARAM_INT),
		'date_created' => array('name' => ':insertArticleNote_dateCreated', 'type' => PDO::PARAM_STR),
		'date_modified' => array('name' => ':insertArticleNote_dateModified', 'type' => PDO::PARAM_STR),
		'title' => array('name' => ':insertArticleNote_title', 'type' => PDO::PARAM_STR),
		'note' => array('name' => ':insertArticleNote_note', 'type' => PDO::PARAM_STR),
		'file_id' => array('name' => ':insertArticleNote_fileId', 'type' => PDO::PARAM_INT)
	)
);

$queries['insertArticleComment'] = array(
	'query' => 'INSERT INTO article_comments (comment_type, role_id, article_id, assoc_id, author_id, comment_title, comments, date_posted, date_modified, viewable) 
		VALUES (:insertArticleComment_commentType, :insertArticleComment_roleId, :insertArticleComment_articleId, :insertArticleComment_assocId, :insertArticleComment_authorId, 
		:insertArticleComment_commentTitle, :insertArticleComment_comments, :insertArticleComment_datePosted, :insertArticleComment_dateModified, :insertArticleComment_viewable)',
		
	'params' => array(
		'comment_type' => array('name' => ':insertArticleComment_commentType', 'type' => PDO::PARAM_INT),
		'role_id' => array('name' => ':insertArticleComment_roleId', 'type' => PDO::PARAM_INT),
		'article_id' => array('name' => ':insertArticleComment_articleId', 'type' => PDO::PARAM_INT),
		'assoc_id' => array('name' => ':insertArticleComment_assocId', 'type' => PDO::PARAM_INT),
		'author_id' => array('name' => ':insertArticleComment_authorId', 'type' => PDO::PARAM_INT),
		'comment_title' => array('name' => ':insertArticleComment_commentTitle', 'type' => PDO::PARAM_STR),
		'comments' => array('name' => ':insertArticleComment_comments', 'type' => PDO::PARAM_STR),
		'date_posted' => array('name' => ':insertArticleComment_datePosted', 'type' => PDO::PARAM_STR),
		'date_modified' => array('name' => ':insertArticleComment_dateModified', 'type' => PDO::PARAM_STR),
		'viewable' => array('name' => ':insertArticleComment_viewable', 'type' => PDO::PARAM_INT)
	)
);

$queries['insertArticleGalley'] = array(
	'query' => 'INSERT INTO article_galleys (locale, article_id, file_id, label, html_galley, style_file_id, seq, remote_url) 
		VALUES (:insertArticleGalley_locale, :insertArticleGalley_articleId, :insertArticleGalley_fileId, :insertArticleGalley_label, 
		:insertArticleGalley_htmlGalley, :insertArticleGalley_styleFileId, :insertArticleGalley_seq, :insertArticleGalley_remoteUrl)',
		
	'params' => array(
		'locale' => array('name' => ':insertArticleGalley_locale', 'type' => PDO::PARAM_STR),
		'article_id' => array('name' => ':insertArticleGalley_articleId', 'type' => PDO::PARAM_INT),
		'file_id' => array('name' => ':insertArticleGalley_fileId', 'type' => PDO::PARAM_INT),
		'label' => array('name' => ':insertArticleGalley_label', 'type' => PDO::PARAM_STR),
		'html_galley' => array('name' => ':insertArticleGalley_htmlGalley', 'type' => PDO::PARAM_INT),
		'style_file_id' => array('name' => ':insertArticleGalley_styleFileId', 'type' => PDO::PARAM_INT)
		'seq' => array('name' => ':insertArticleGalley_seq', 'type' => PDO::PARAM_STR),
		'remote_url' => array('name' => ':insertArticleGalley_remoteUrl', 'type' => PDO::PARAM_STR)
	)
);

$queries['insertArticleGalleySetting'] = array(
	'query' => 'INSERT INTO article_galley_settings (galley_id, locale, setting_name, setting_value, setting_type) VALUES (:insertArticleGalleySetting_galleyId,
		:insertArticleGalleySetting_locale, :insertArticleGalleySetting_settingName, :insertArticleGalleySetting_settingValue, :insertArticleGalleySetting_settingType)',
		
	'params' => array(
		'galley_id' => array('name' => ':insertArticleGalleySetting_galleyId', 'type' => PDO::PARAM_INT),
		'locale' => array('name' => ':insertArticleGalleySetting_locale', 'type' => PDO::PARAM_STR),
		'setting_name' => array('name' => ':insertArticleGalleySetting_settingName', 'type' => PDO::PARAM_STR),
		'setting_value' => array('name' => ':insertArticleGalleySetting_settingValue', 'type' => PDO::PARAM_STR),
		'setting_type' => array('name' => ':insertArticleGalleySetting_settingType', 'type' => PDO::PARAM_STR)
	)
);

$queries['insertArticleXmlGalley'] = array(
	'query' => 'INSERT INTO article_xml_galleys (galley_id, article_id, label, galley_type, views) VALUES (:insertArticleXmlGalley_galleyId, 
		:insertArticleXmlGalley_articleId, :insertArticleXmlGalley_label, :insertArticleXmlGalley_galleyType, :insertArticleXmlGalley_views)'
		
	'params' => array(
		'galley_id' => array('name' => ':insertArticleXmlGalley_galleyId', 'type' => PDO::PARAM_INT),
		'article_id' => array('name' => ':insertArticleXmlGalley_articleId', 'type' => PDO::PARAM_INT),
		'label' => array('name' => ':insertArticleXmlGalley_label', 'type' => PDO::PARAM_STR),
		'galley_type' => array('name' => ':insertArticleXmlGalley_galleyType', 'type' => PDO::PARAM_STR),
		'views' => array('name' => ':insertArticleXmlGalley_views', 'type' => PDO::PARAM_INT)
	)
);

$queries['insertArticleHtmlGalleyImage'] = array(
	'query' => 'INSERT INTO article_html_galley_images (galley_id, file_id) VALUES (:insertArticleHtmlGalleyImage_galleyId, :insertArticleHtmlGalleyImage_fileId)',
	'params' => array(
		'galley_id' => array('name' => ':insertArticleHtmlGalleyImage_galleyId', 'type' => PDO::PARAM_INT),
		'file_id' => array('name' => ':insertArticleHtmlGalleyImage_fileId', 'type' => PDO::PARAM_INT)
	)
);

$queries['insertArticleSearchKeywordList'] = array(
	'query' => 'INSERT INTO article_search_keyword_list (keyword_text) VALUES (:insertArticleSearchKeywordList_keywordText)',
	'params' => array(
		'keyword_text' => array('name' => ':insertArticleSearchKeywordList_keywordText', 'type' => PDO::PARAM_INT)
	)
);

$queries['insertArticleSearchObjectKeyword'] = array(
	'query' => 'INSERT INTO article_search_object_keywords (object_id, keyword_id, pos) VALUES (:insertArticleSearchObjectKeyword_objectId,
		:insertArticleSearchObjectKeyword_keywordId, :insertArticleSearchObjectKeyword_pos)'
		
	'params' => array(
		'object_id' => array('name' => ':insertArticleSearchObjectKeyword_objectId', 'type' => PDO::PARAM_INT),
		'keyword_id' => array('name' => ':insertArticleSearchObjectKeyword_keywordId', 'type' => PDO::PARAM_INT),
		'pos' => array('name' => ':insertArticleSearchObjectKeyword_pos', 'type' => PDO::PARAM_INT)
	)
);

$queries['insertArticleSearchObject'] = array(
	'query' => 'INSERT INTO article_search_objects (article_id, type, assoc_id) 
		VALUES (:insertArticleSearchObject_articleId, :insertArticleSearchObject_type, :insertArticleSearchObject_assocId)'
		
	'params' => array(
		'article_id' => array('name' => ':insertArticleSearchObject_articleId', 'type' => PDO::PARAM_INT),
		'type' => array('name' => ':insertArticleSearchObject_type', 'type' => PDO::PARAM_INT),
		'assoc_id' => array('name' => ':insertArticleSearchObject_assocId', 'type' => PDO::PARAM_INT)
	)
);

$queries['insertEditDecision'] = array(
	'query' => 'INSERT INTO edit_decisions (article_id, round, editor_id, decision, date_decided) VALUES (:insertEditDecision_articleId, 
		:insertEditDecision_round, :insertEditDecision_editorId, :insertEditDecision_decision, :insertEditDecision_dateDecided)'
		
	'params' => array(
		'article_id' => array('name' => ':insertEditDecision_articleId', 'type' => PDO::PARAM_INT),
		'round' => array('name' => ':insertEditDecision_round', 'type' => PDO::PARAM_INT),
		'editor_id' => array('name' => ':insertEditDecision_editorId', 'type' => PDO::PARAM_INT),
		'decision' => array('name' => ':insertEditDecision_decision', 'type' => PDO::PARAM_INT),
		'date_decided' => array('name' => ':insertEditDecision_dateDecided', 'type' => PDO::PARAM_STR)
	)
);

$queries['insertEditAssignment'] = array(
	'query' => 'INSERT INTO edit_assignments (article_id, editor_id, can_edit, can_review, date_assigned, date_notified, date_underway) 
		VALUES (:insertEditAssignment_articleId, :insertEditAssignment_editorId, :insertEditAssignment_canEdit, :insertEditAssignment_canReview, 
		:insertEditAssignment_dateAssigned, :insertEditAssignment_dateNotified, :insertEditAssignment_dateUnderway)',
		
	'params' => array(
		'article_id' => array('name' => ':insertEditAssignment_articleId', 'type' => PDO::PARAM_INT),
		'editor_id' => array('name' => ':insertEditAssignment_editorId', 'type' => PDO::PARAM_INT),
		'can_edit' => array('name' => ':insertEditAssignment_canEdit', 'type' => PDO::PARAM_INT),
		'can_review' => array('name' => ':insertEditAssignment_canReview', 'type' => PDO::PARAM_INT),
		'date_assigned' => array('name' => ':insertEditAssignment_dateAssigned', 'type' => PDO::PARAM_STR),
		'date_notified' => array('name' => ':insertEditAssignment_dateNotified', 'type' => PDO::PARAM_STR),
		'date_underway' => array('name' => ':insertEditAssignment_dateUnderway', 'type' => PDO::PARAM_STR)
	)
);

$queries['insertReviewRound'] = array(
	'query' => 'INSERT INTO review_rounds (submission_id, stage_id, round, review_revision, status) VALUES (:insertReviewRound_submissionId,
		:insertReviewRound_stageId, :insertReviewRound_round, :insertReviewRound_reviewRevision, :insertReviewRound_status)',
		
	'params' => array(
		'submission_id' => array('name' => ':insertReviewRound_submissionId', 'type' => PDO::PARAM_INT),
		'stage_id' => array('name' => ':insertReviewRound_stageId', 'type' => PDO::PARAM_INT),
		'round' => array('name' => ':insertReviewRound_round', 'type' => PDO::PARAM_INT),
		'review_revision' => array('name' => ':insertReviewRound_reviewRevision', 'type' => PDO::PARAM_INT),
		'status' => array('name' => ':insertReviewRound_status', 'type' => PDO::PARAM_INT)
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
		'submission_id' => array('name' => ':insertReviewAssignment_submissionId', 'type' => PDO::PARAM_INT),
		'reviewer_id' => array('name' => ':insertReviewAssignment_reviewerId', 'type' => PDO::PARAM_INT),
		'competing_interests' => array('name' => ':insertReviewAssignment_competingInterests', 'type' => PDO::PARAM_STR),
		'regret_message' => array('name' => ':insertReviewAssignment_regretMessage', 'type' => PDO::PARAM_STR)
		'recommendation' => array('name' => ':insertReviewAssignment_recommendation', 'type' => PDO::PARAM_INT),
		'date_assigned' => array('name' => ':insertReviewAssignment_dateAssigned', 'type' => PDO::PARAM_STR),
		'date_notified' => array('name' => ':insertReviewAssignment_dateNotified', 'type' => PDO::PARAM_STR),
		'date_confirmed' => array('name' => ':insertReviewAssignment_dateConfirmed', 'type' => PDO::PARAM_STR),
		'date_completed' => array('name' => ':insertReviewAssignment_dateCompleted', 'type' => PDO::PARAM_STR),
		'date_acknowledged' => array('name' => ':insertReviewAssignment_dateAcknowledged', 'type' => PDO::PARAM_STR),
		'date_due' => array('name' => ':insertReviewAssignment_dateDue', 'type' => PDO::PARAM_STR),
		'last_modified' => array('name' => ':insertReviewAssignment_lastModified', 'type' => PDO::PARAM_STR),
		'reminder_was_automatic' => array('name' => ':insertReviewAssignment_reminderAuto', 'type' => PDO::PARAM_INT),
		'declined' => array('name' => ':insertReviewAssignment_declined', 'type' => PDO::PARAM_INT),
		'replaced' => array('name' => ':insertReviewAssignment_replaced', 'type' => PDO::PARAM_INT),
		'cancelled' => array('name' => ':insertReviewAssignment_cancelled', 'type' => PDO::PARAM_INT),
		'reviewer_file_id' => array('name' => ':insertReviewAssignment_reviewerFileId', 'type' => PDO::PARAM_INT),
		'date_rated' => array('name' => ':insertReviewAssignment_dateRated', 'type' => PDO::PARAM_STR),
		'date_reminded' => array('name' => ':insertReviewAssignment_dateReminded', 'type' => PDO::PARAM_STR),
		'quality' =. array('name' => ':insertReviewAssignment_quality', 'type' => PDO::PARAM_INT),
		'review_round_id' => array('name' => ':insertReviewAssignment_reviewRoundId', 'type' => PDO::PARAM_INT),
		'stage_id' => array('name' => ':insertReviewAssignment_stageId', 'type' => PDO::PARAM_INT),
		'review_method' => array('name' => ':insertReviewAssignment_reviewMethod', 'type' => PDO::PARAM_INT),
		'round' => array('name' => ':insertReviewAssignment_round', 'type' => PDO::PARAM_INT),
		'step' => array('name' => ':insertReviewAssignment_step', 'type' => PDO::PARAM_INT),
		'review_form_id' => array('name' => ':insertReviewAssignment_reviewFormId', 'type' => PDO::PARAM_INT),
		'unconsidered' => array('name' => ':insertReviewAssignment_unconsidered', 'type' => PDO::PARAM_INT)
	)
);

$queries['insertReviewFormResponse'] = array(
	'query' => 'INSERT INTO review_form_responses (review_form_element_id, review_id, response_type, response_value) 
		VALUES (:insertReviewFormResponse_reviewFormElementId, :insertReviewFormResponse_reviewId, :insertReviewFormResponse_responseType, :insertReviewFormResponse_reponseValue)',
	
	'params' => array(
		'review_form_element_id' => array('name' => ':insertReviewFormResponse_reviewFormElementId', 'type' => PDO::PARAM_INT),
		'review_id' => array('name' => ':insertReviewFormResponse_reviewId', 'type' => PDO::PARAM_INT),
		'response_type' => array('name' => ':insertReviewFormResponse_responseType', 'type' => PDO::PARAM_STR),
		'response_value' => array('name' => ':insertReviewFormResponse_reponseValue', 'type' => PDO::PARAM_STR)
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
		'date_last_email' => array('name' => ':updateUserDates_dateLastEmail', 'type' => PDO::PARAM_STR), 
		'date_registered' => array('name' => ':updateUserDates_dateRegistered', 'type' => PDO::PARAM_STR), 
		'date_validated' => array('name' => ':updateUserDates_dateValidated', 'type' => PDO::PARAM_STR), 
		'date_last_login' => array('name' => ':updateUserDates_dateLastLogin', 'type' => PDO::PARAM_STR),
		'user_id' => array('name' => ':updateUserDates_userId', 'type' => PDO::PARAM_INT)
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

//set the statements
$statements = array();

function createStatement(&$conn, &$stmts, $statementName) {
	
	global $queries;

	if (!array_key_exists($statementName, $queries)) {
		return false;
	}
	
	if (!array_key_exists($statementName, $stmts)) {
		$stmts[$statementName] = $conn->prepare($queries[$statementName]['query']);
	}
	
	return true;
}

/**
binds the value to the 'name' field of the prepared statement 'stmt'
*/
function bindStmtParam(&$stmt, $name, $value) {

}
