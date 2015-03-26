<?php
	
if (!isset($PMRightsManager)) {
	require_once "it_pm_rightsmanager.php";
	$PMRightsManager = new ITPMRightsManager ();
	define ("PM_DISABLED", true);
} else {
	define ("PM_DISABLED", false);
}

$__ur_ITApp = new UR_RO_Container( "IT", "app_name_long", "IT" );
$__ur_ITApp = &$__ur_ITApp;
$UR_Manager->AddChild( $__ur_ITApp );

$__ur_ITScreens = new UR_RO_Container( UR_SCREENS, "app_available_pages_name", "IT" );
$__ur_ITScreens = &$__ur_ITScreens;
$__ur_ITApp->AddChild( $__ur_ITScreens );

	//$__ur_tmp = &new UR_RO_Screen( "IL", "il_screen_long_name", "il_screen_short_name", "issuelist.php", "IT" );
	$__ur_ITScreens->AddChild( new UR_RO_Screen( "IL", "il_screen_long_name", "il_screen_short_name", "issuelist.php", "IT" ) );

	/*$__ur_tmp = &new UR_RO_Screen( "FA", "fa_screen_long_name", "fa_screen_short_name", "fileattachment.php", "IT" );
	$__ur_ITScreens->AddChild( $__ur_tmp );

	$__ur_tmp = &new UR_RO_Screen( "ISM", "iss_screen_long_name", "iss_screen_short_name", "statistics.php", "IT" );
	$__ur_ITScreens->AddChild( $__ur_tmp );

	$__ur_tmp = &new UR_RO_Screen( "TL", "tl_screen_long_name", "tl_screen_short_name", "templatelist.php", "IT" );
	$__ur_ITScreens->AddChild( $__ur_tmp );*/
	
$__ur_ITFuncs = new UR_RO_Container( UR_FUNCTIONS, "app_available_functions_name" );
$__ur_ITFuncs = &$__ur_ITFuncs;
$__ur_ITApp->AddChild( $__ur_ITFuncs );

	//$__ur_tmp = &new UR_RO_Bool( APP_CANTOOLS_RIGHTS, "app_cantools_label", 'IT' );
	$__ur_ITFuncs->AddChild( new UR_RO_Bool( APP_CANTOOLS_RIGHTS, "app_cantools_label", 'IT' ) );
	
	//$__ur_tmp = &new UR_RO_Bool( APP_CANREPORTS_RIGHTS, "app_canreports_label", 'IT' );
	$__ur_ITFuncs->AddChild( new UR_RO_Bool( APP_CANREPORTS_RIGHTS, "app_canreports_label", 'IT' ) );


$__ur_ITMessages = new UR_RO_Container( UR_MESSAGES, "app_notifications_section_name", "IT" );
$__ur_ITMessages = &$__ur_ITMessages;
$__ur_ITApp->AddChild( $__ur_ITMessages );

	//$__ur_tmp = &new UR_RO_Bool( "ONISSUEASSIGNMENT", "app_onissueassignment_name", "IT" );
	$__ur_ITMessages->AddChild( new UR_RO_Bool( "ONISSUEASSIGNMENT", "app_onissueassignment_name", "IT" ) );

?>