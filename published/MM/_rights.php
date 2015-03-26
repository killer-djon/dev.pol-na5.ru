<?php

$rights['MM'] = array(
	'APP_ID' => 'MM',
	'ORDER' => 70,
	'TITLE' => _s('Mail'),
	'SCREEN_ID' => 'MM',
	'SECTIONS' => array(
		array(		
			'ID' => 'FOLDERS',
			'TITLE' => _s("Available functions"),  
			'OBJECTS' => array(
				array('INBOX', _s('Can access "Inbox"')),
				array('ROOT', _s("Can create root folders"))
			)
		)
	),
	'FOLDERS' => array(
		'TITLE' => _s("Available folders"),
		'ICONS' => 'folder',
		'TABLE' => 'MMFOLDER',
		'ID' => 'MMF_ID',
		'PARENT' => 'MMF_ID_PARENT',
		'NAME' => 'MMF_NAME',
		'STATUS' => 'MMF_STATUS',
		'ORDER' => 'MMF_NAME',
	)
);

?>