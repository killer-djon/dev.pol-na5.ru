<?php

require_once WBS_DIR."kernel/class.cm_folderstreedescriptor.php";

$__ur_CMApp = new UR_RO_Container( CM_APP_ID, "app_name_long", CM_APP_ID );
$__ur_CMApp =&$__ur_CMApp;
$UR_Manager->AddChild( $__ur_CMApp );

$__ur_CMScreens = new UR_RO_Container( UR_SCREENS, "app_available_pages_name" );
$__ur_CMScreens = &$__ur_CMScreens;
$__ur_CMApp->AddChild( $__ur_CMScreens );

//	$__ur_tmp = &new UR_RO_Screen( "UC", "cman_page_title", "cman_page_title",  "contacts.php", CM_APP_ID );
	$__ur_CMScreens->AddChild( new UR_RO_Screen( "UC", "cman_page_title", "cman_page_title",  "../../index.php", CM_APP_ID ) );
	
	global  $databaseInfo;
	if(isset($databaseInfo) && isset($databaseInfo[HOST_DBSETTINGS]['OLD_TEMPLATE']) && $databaseInfo[HOST_DBSETTINGS]['OLD_TEMPLATE']){
		
		//$__ur_tmp = &new UR_RO_Screen( "SVC", "svc_page_title", "svc_page_title",  "cmservice.php", CM_APP_ID );
		$__ur_CMScreens->AddChild(new UR_RO_Screen( "SVC", "svc_page_title", "svc_page_title",  "cmservice.php", CM_APP_ID ) );
		
		//$__ur_tmp = &new UR_RO_Screen( "RP", "rp_page_title", "rp_page_title",  "reports.php", CM_APP_ID );
		$__ur_CMScreens->AddChild( new UR_RO_Screen( "RP", "rp_page_title", "rp_page_title",  "reports.php", CM_APP_ID ) );
	}
$__ur_CMFuncs = new UR_RO_Container( UR_FUNCTIONS, "app_available_functions_name" );
$__ur_CMFuncs = &$__ur_CMFuncs;
$__ur_CMApp->AddChild( $__ur_CMFuncs );

	//$__ur_tmp = &new UR_RO_Bool( "MANAGELISTS", "cm_canmanagelists_title", CM_APP_ID );
	$__ur_CMFuncs->AddChild( new UR_RO_Bool( "ADMIN", "cm_canmanagelists_title", CM_APP_ID ) );
	

$__ur_Folders = new UR_RO_FoldersTree( $cm_TreeFoldersDescriptor, "FOLDERS", "app_treefolders_text",  CM_APP_ID );
$__ur_Folders = &$__ur_Folders;
$__ur_CMApp->AddChild( $__ur_Folders );

	//$__ur_temp = &new UR_RO_RootFolder( "ROOT", "app_treerootfolders_label", CM_APP_ID  );
	$__ur_Folders->AddChild(new UR_RO_RootFolder( "ROOT", "app_treerootfolders_label", CM_APP_ID  ) );
	$__ur_Folders->AddChild(new UR_RO_RootFolder( "PRIVATE", "app_treerootfolders_label", CM_APP_ID  ) );

	unset($__ur_Folders);	

	$cm_groupClass->cm_groupClass( $cm_TreeFoldersDescriptor );

?>