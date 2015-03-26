<?php

$rights['QN'] = array(
	'APP_ID' => 'QN',
	'ORDER' => 100,
	'TITLE' => _s('Notes'),
	'SCREEN_ID' => 'QN',
	'SECTIONS' => array(
		array(		
			'ID' => 'FUNCTIONS',
			'TITLE' => _s("Available functions"),  
			'OBJECTS' => array(
				array('CANTOOLS', _s("Can use tools")),
			)
		),
		array(		
			'ID' => 'FOLDERS',
			'TITLE' => _s("Available actions with folders"),  
			'OBJECTS' => array(
				array('ROOT', _s("Can create root folders")),
				array('VIEWSHARES', _s("Can see other users' permissions"))
			)
		)
	),
	'FOLDERS' => array(
		'TITLE' => _s("Available folders"),
		'ICONS' => 'folder',
		'TABLE' => 'QNFOLDER',
		'ID' => 'QNF_ID',
		'PARENT' => 'QNF_ID_PARENT',
		'NAME' => 'QNF_NAME',
		'STATUS' => 'QNF_STATUS',
		'ORDER' => 'QNF_NAME',
	)
);

?>