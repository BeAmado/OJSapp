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



