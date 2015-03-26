<?php

$rights['QP'] = array(
	'APP_ID' => 'QP',
	'ORDER' => 110,
	'TITLE' => _s('Pages'),
	'SCREEN_ID' => 'QP',
	'SECTIONS' => array(
		array(		
			'ID' => 'FUNCTIONS',
			'TITLE' => _s("Available functions"),  
			'OBJECTS' => array(
				array('CANTOOLS', _s("Can use tools")),
				array('CANBOOKLIST', _s("Has access to book list")),
			)
		),
		array(		
			'ID' => 'FOLDERS',
			'TITLE' => _s("Available actions with books"),  
			'OBJECTS' => array(
				array('ROOT', _s("Can create books")),
			)
		)
	),
	'FOLDERS' => array(
		'TITLE' => _s("Available books"),
		'COMMENT' => "",
		'ICONS' => 'folder',
		'TABLE' => 'QPBOOK',
		'ID' => 'QPB_ID',
		'PARENT' => 'QPB_ID_PARENT',
		'NAME' => 'QPB_NAME',
		'STATUS' => 'QPB_STATUS',
		'ORDER' => 'QPB_NAME',
	)
);

?>