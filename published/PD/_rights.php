<?php

$rights['PD'] = array(
	'APP_ID' => 'PD',
	'ORDER' => 50,
	'TITLE' => _s('Photos'),
	'SCREEN_ID' => 'CT',
	'SECTIONS' => array(
		array(		
			'ID' => 'FUNCTIONS',
			'TITLE' => _s("Available functions"),  
			'OBJECTS' => array(
				array('MANAGE_COLLECTIONS', _s("Can create and manage collections")),
				array('MODIFY_DESIGN', _s("Can modify public gallery design")),
			)
		),
		array(		
			'ID' => 'FOLDERS',
			'TITLE' => _s("Available actions with albums"),  
			'OBJECTS' => array(
				array('MANAGE_ALBUM', _s("Can manage albums (and see the list of all albums)")),
				array('ROOT', _s("Can create albums")),				
			)
		)		
	),
	'FOLDERS' => array(
		'TITLE' => _s("Available albums"),
		'ICONS' => 'folder',
		'TABLE' => 'PIXFOLDER',
		'ID' => 'PF_ID',
		'PARENT' => 'PF_ID_PARENT',
		'NAME' => 'PF_NAME',
		'ORDER' => 'PF_SORT DESC',
		'STATUS' => 'PF_STATUS',
	)
);

?>