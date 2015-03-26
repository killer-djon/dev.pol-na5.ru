<?php

$rights['PM'] = array(
	'APP_ID' => 'PM',
	'ORDER' => 80,
	'TITLE' => _s('Projects'),
	'SCREEN_ID' => 'WL',
	'SECTIONS' => array(
		array(		
			'ID' => 'FUNCTIONS',
			'TITLE' => _s("Available functions"),  
			'OBJECTS' => array(
				array('CANPROJECTLIST', _s('Has access to project list')),
				array('CANMANAGECUSTOMERS', _s("Can manage customers")),
				array('CANREPORTS', _s("Can use reports")),				
			)
		),
	),
	'FOLDERS' => array(
		'TITLE' => _s("Available projects"),
		'TITLE_INHERIT' => _s('Project manager'),
		'ICONS' => 'folder',
		'TABLE' => 'PROJECT',
		'ID' => 'P_ID',
		'PARENT' => '"ROOT"',
		'NAME' => 'P_DESC',
		'ORDER' => 'P_DESC',
		'INHERIT' => 'U_ID_MANAGER = s:U_ID',
		'WHERE' => "P_ENDDATE IS NULL OR P_ENDDATE >= CURDATE()",
	)
);

?>