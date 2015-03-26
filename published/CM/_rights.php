<?php

$rights['CM'] = array(
	'APP_ID' => 'CM',
	'ORDER' => 60,
	'TITLE' => _s('Contacts'),
	'SCREEN_ID' => 'UC',
	'SECTIONS' => array(
		array(
			'ID' => 'FOLDERS',
			'TITLE' => _s('Available functions'),
			'OBJECTS' => array(
				array('ROOT', _s("Can create root folders")),				
				array('PRIVATE', _s('Has private folder')),
			)
		),
	),
	'FOLDERS' => array(
		'TITLE' => _s("Available folders"),
		'ICONS' => 'folder',
		'TABLE' => 'CFOLDER',
		'ID' => 'CF_ID',
		'PARENT' => 'CF_ID_PARENT',
		'NAME' => 'CF_NAME',
		'STATUS' => 'CF_STATUS',
		'ORDER' => 'NAME'
	)
);

?>