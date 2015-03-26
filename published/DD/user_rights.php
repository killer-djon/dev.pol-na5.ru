<?php

class DD_FoldersTreeDescriptor extends FoldersTreeDescriptor
{
	function DD_FoldersTreeDescriptor( )
	{
		$this->folderDescriptor = new treeFolderTableDescriptor( 'DOCFOLDER', 'DF_ID', 'DF_NAME', 'DF_ID_PARENT', 'DF_STATUS' );
		$this->folderDescriptor = &$this->folderDescriptor;
		$this->folderDescriptor->folder_order_by_str = "DF_SPECIALSTATUS ASC,DF_NAME ASC";
		$this->folderDescriptor->folder_specialstatus_field = "DF_SPECIALSTATUS";
		$this->documentDescriptor = new treeDocumentsTableDescriptor( 'DOCLIST', 'DL_ID', 'DL_STATUSINT', 'DL_MODIFYUSERNAME' );
		$this->documentDescriptor = &$this->documentDescriptor;
	}
}

global $dd_TreeFoldersDescriptor;
$dd_TreeFoldersDescriptor = new DD_FoldersTreeDescriptor( );
$dd_TreeFoldersDescriptor = &$dd_TreeFoldersDescriptor;

$__ur_DDApp = new UR_RO_Container( "DD", "app_name_long", "DD" );
$__ur_DDApp = &$__ur_DDApp;
$UR_Manager->AddChild( $__ur_DDApp );

$__ur_DDScreens = new UR_RO_Container( UR_SCREENS, "app_available_pages_name" );
$__ur_DDScreens = &$__ur_DDScreens;
$__ur_DDApp->AddChild( $__ur_DDScreens );

	$__ur_DDScreens->AddChild( new UR_RO_Screen( "CT", "dd_screen_long_name", "dd_screen_short_name", "../../2.0/backend.php", "DD" ) );


$__ur_DDFuncs = new UR_RO_Container( UR_FUNCTIONS, "app_available_functions_name" );
$__ur_DDFuncs = &$__ur_DDFuncs;
$__ur_DDApp->AddChild( $__ur_DDFuncs );

//	$__ur_tmp = &new UR_RO_Bool( APP_CANTOOLS_RIGHTS, "app_cantools_label", 'DD' );
	$__ur_DDFuncs->AddChild( new UR_RO_Bool( APP_CANTOOLS_RIGHTS, "app_cantools_label", 'DD' ) );
	
//	$__ur_tmp = &new UR_RO_Bool( APP_CANREPORTS_RIGHTS, "app_canreports_label", 'DD' );
	$__ur_DDFuncs->AddChild( new UR_RO_Bool( APP_CANREPORTS_RIGHTS, "app_canreports_label", 'DD' ) );
	
//	$__ur_tmp = &new UR_RO_Bool( APP_CANWIDGETS_RIGHTS, "app_canwidgets_label", 'DD' );
	$__ur_DDFuncs->AddChild( new UR_RO_Bool( APP_CANWIDGETS_RIGHTS, "app_canwidgets_label", 'DD' ) );


$__ur_DDMessages = new UR_RO_Container( UR_MESSAGES, "app_notifications_section_name", "DD" );
$__ur_DDMessages = &$__ur_DDMessages;
$__ur_DDApp->AddChild( $__ur_DDMessages );

//	$__ur_tmp = &new UR_RO_Bool( "ONFOLDERUPDATE", "notify_onfolderupdate_name", "DD" );
	$__ur_DDMessages->AddChild( new UR_RO_Bool( "ONFOLDERUPDATE", "notify_onfolderupdate_name", "DD" ) );

$__ur_Folders = new UR_RO_FoldersTree( $dd_TreeFoldersDescriptor, "FOLDERS", "app_treefolders_text", "DD" );
$__ur_Folders = &$__ur_Folders;
$__ur_DDApp->AddChild( $__ur_Folders );

//	$__ur_temp = &new UR_RO_RootFolder( "ROOT", "app_treerootfolders_label", "DD" );
	$__ur_Folders->AddChild( new UR_RO_RootFolder( "ROOT", "app_treerootfolders_label", "DD" ) );

//	$__ur_temp = &new UR_RO_Bool( "VIEWSHARES", "app_treeflduserscb_label", "DD" );
	$__ur_Folders->AddChild( new UR_RO_Bool( "VIEWSHARES", "app_treeflduserscb_label", "DD" ) );
	
unset($__ur_Folders);

?>