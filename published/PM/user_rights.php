<?php

require_once "pm_rightsmanager.php";

$__ur_PMApp = new UR_RO_Container( "PM", "app_name_long", "PM" );
$__ur_PMApp = &$__ur_PMApp;
$UR_Manager->AddChild( $__ur_PMApp );

$__ur_PMScreens = new UR_RO_Container( UR_SCREENS, "app_available_pages_name", "PM" );
$__ur_PMScreens = &$__ur_PMScreens;
$__ur_PMApp->AddChild( $__ur_PMScreens );

	//$__ur_tmp = &new UR_RO_Screen( "WL", "pm_screen_long_name", "pm_screen_short_name", "worklist.php", "PM" );
	$__ur_PMScreens->AddChild( new UR_RO_Screen( "WL", "pm_screen_long_name", "pm_screen_short_name", "worklist.php", "PM" ) );

	/*$__ur_tmp = &new UR_RO_Screen( "CL", "cl_screen_long_name", "cl_screen_short_name", "customerlist.php", "PM" );
	$__ur_PMScreens->AddChild( $__ur_tmp );

	$__ur_tmp = &new UR_RO_Screen( "PL", "pl_screen_long_name", "pl_screen_short_name", "projectlist.php", "PM" );
	$__ur_PMScreens->AddChild( $__ur_tmp );

	$__ur_tmp = &new UR_RO_Screen( "PS", "prs_screen_long_name", "prs_screen_short_name", "projectstatistics.php", "PM" );
	$__ur_PMScreens->AddChild( $__ur_tmp );
	*/
	
$__ur_PMFuncs = new UR_RO_Container( UR_FUNCTIONS, "app_available_functions_name" );
$__ur_PMFuncs = &$__ur_PMFuncs;
$__ur_PMApp->AddChild( $__ur_PMFuncs );

//	$__ur_tmp = &new UR_RO_Bool( APP_CANTOOLS_RIGHTS, "app_cantools_label", 'PM' );
//	$__ur_PMFuncs->AddChild( $__ur_tmp );
	
	//$__ur_tmp = &new UR_RO_Bool( "CANPROJECTLIST", "app_canprojectlist_label", 'PM' );
	$__ur_PMFuncs->AddChild( new UR_RO_Bool( "CANPROJECTLIST", "app_canprojectlist_label", 'PM' ) );
	
//	$__ur_tmp = &new UR_RO_Bool( "CANADDPROJECT", "app_canaddproject_label", 'PM' );
//	$__ur_PMFuncs->AddChild( $__ur_tmp );
	
//	$__ur_tmp = &new UR_RO_Bool( "CANMANAGECUSTOMERS", "app_canmanagecust_label", 'PM' );
	$__ur_PMFuncs->AddChild( new UR_RO_Bool( "CANMANAGECUSTOMERS", "app_canmanagecust_label", 'PM' ) );
	
//	$__ur_tmp = &new UR_RO_Bool( APP_CANREPORTS_RIGHTS, "app_canreports_label", 'PM' );
	$__ur_PMFuncs->AddChild( new UR_RO_Bool( APP_CANREPORTS_RIGHTS, "app_canreports_label", 'PM' ) );

global $PMRightsManager;
unset($PMRightsManager);
$PMRightsManager = new pm_rightsManager( "PROJECTS", "app_projectrights_title", "PM" );
$PMRightsManager = &$PMRightsManager;
$__ur_PMApp->AddChild( $PMRightsManager );

?>