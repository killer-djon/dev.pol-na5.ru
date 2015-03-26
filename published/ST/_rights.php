<?php

$rights['ST'] = array(
	'APP_ID' => 'ST',
	'ORDER' => 210,
	'TITLE' => _s('Help Desk'),
	'SCREEN_ID' => 'RL',
	'SECTIONS' => array(
		array(
			'ID' => 'MESSAGES',
			'TITLE' => _s("Notifications"), 
			'OBJECTS' => array(
				array('ASSIGNED', _s("Is notified on request assignment")) 
			),
		),
		array(
			'ID' => 'FUNCTIONS',
			'TITLE' => _s("Access level"), 
			'OBJECTS' => array(
				array('ADMIN', _s("Administrator")) 
			),
		),
		
	)
);

?>