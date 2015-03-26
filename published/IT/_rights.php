<?php

$rights['IT'] = array(
	'APP_ID' => 'IT',
	'ORDER' => 90,
	'TITLE' => _s('Issue Tracker'),
	'SCREEN_ID' => 'IL',
	'SECTIONS' => array(
		array(		
			'ID' => 'FUNCTIONS',
			'TITLE' => _s("Available functions"),  
			'OBJECTS' => array(
				array('CANTOOLS', _s("Can manage workflow")),
				array('CANREPORTS', _s("Can use reports")),
			)
		),
		array(		
			'ID' => 'MESSAGES',
			'TITLE' => _s("Notifications"),  
			'OBJECTS' => array(
				array('ONISSUEASSIGNMENT', _s("Is notified on issue assignment")),
			)
		),
	),
);

?>