<?php

$rights['DD'] = array(
	'APP_ID' => 'DD',
	'ORDER' => 40,
	'TITLE' => _s('Files'),
	'SCREEN_ID' => 'CT',
	'SECTIONS' => array(
		array(
			'ID' => 'FUNCTIONS',
			'TITLE' => _s('Available functions'),
			'OBJECTS' => array(
				array('CANTOOLS', _s("Has access to recycle bin and settings")),
				array('CANREPORTS', _s("Can use reports")),
				array('CANWIDGETS',	_s("Can manage widgets"))			
			)
		),
		array(
			'ID' => 'MESSAGES',
			'TITLE' => _s("Notifications"), 
			'OBJECTS' => array(
				array('ONFOLDERUPDATE', _s("Is notified on folder update")) 
			),
		),
		
		array(		
			'ID' => 'FOLDERS',
			'TITLE' => _s("Available actions with folders"),  
			'OBJECTS' => array(
				array('ROOT', _s("Can create root folders"))
			)
		),
		array(
			'ID' => 'QUOTA',
			'TITLE' => _s('Available storage space'),
			'OBJECTS' => array(
			),		
		),
		
	),
	'FOLDERS' => array(
		'TITLE' => _s("Available folders"),
		'TITLE_INHERIT' => _s('inherited from projects'),
		'ICONS' => 'folder',
		'TABLE' => 'DOCFOLDER',
		'ID' => 'DF_ID',
		'PARENT' => 'DF_ID_PARENT',
		'NAME' => 'DF_NAME',
		'STATUS' => 'DF_STATUS',
		'INHERIT' => 'DF_SPECIALSTATUS > 0',
		'ORDER' => 'DF_SPECIALSTATUS, DF_NAME',
		'COMMENT' => 1 
	)
);

?>