<?php

class PD_FoldersTreeDescriptor extends FoldersTreeDescriptor
{
	function PD_FoldersTreeDescriptor( )
	{
		$this->folderDescriptor = new treeFolderTableDescriptor( 'PIXFOLDER', 'PF_ID', 'PF_NAME', 'PF_ID_PARENT', 'PF_STATUS' );
		$this->folderDescriptor = &$this->folderDescriptor;
		$this->documentDescriptor = new treeDocumentsTableDescriptor( 'PIXLIST', 'PL_ID', 'PL_STATUSINT', 'PL_MODIFYUSERNAME' );
		$this->documentDescriptor = &$this->documentDescriptor;
	}
}

global $pd_TreeFoldersDescriptor;
$pd_TreeFoldersDescriptor = new PD_FoldersTreeDescriptor( );
$pd_TreeFoldersDescriptor = &$pd_TreeFoldersDescriptor;

$__ur_PDApp = new UR_RO_Container( "PD", "app_name_long", "PD" );
$__ur_PDApp = &$__ur_PDApp;
$UR_Manager->AddChild( $__ur_PDApp );

$__ur_PDScreens = new UR_RO_Container( UR_SCREENS, "app_available_pages_name" );
$__ur_PDScreens = &$__ur_PDScreens;
$__ur_PDApp->AddChild( $__ur_PDScreens );

	$__ur_PDScreens->AddChild( new UR_RO_Screen( "CT", "pd_screen_long_name", "pd_screen_short_name", "../../backend.php", "PD" ) );
    

	
//=========PD2==========
$__ur_Funcs = new UR_RO_Container( UR_FUNCTIONS, "app_available_functions_name" );
$__ur_Funcs = &$__ur_Funcs;
$__ur_PDApp->AddChild( $__ur_Funcs );

	$__ur_Funcs->AddChild( new UR_RO_Bool( 'MANAGE_COLLECTIONS', "app_pd_manage_collections", 'PD' ) );
	
	$__ur_Funcs->AddChild( new UR_RO_Bool( 'MODIFY_DESIGN', "app_pd_modify_design", 'PD' ) );
//======================
	

$__ur_Folders = new UR_RO_FoldersTree( $pd_TreeFoldersDescriptor, "FOLDERS", "app_treefolders_text", "PD" );
$__ur_Folders = &$__ur_Folders;
$__ur_PDApp->AddChild( $__ur_Folders );

	//$__ur_temp = &new UR_RO_RootFolder( "ROOT", "app_treerootfolders_label", "PD" );
	$__ur_Folders->AddChild( new UR_RO_RootFolder( "ROOT", "app_treerootfolders_label", "PD" ) );
	
//=======PD2============	
	$__ur_Folders->AddChild( new UR_RO_RootFolder( "MANAGE_ALBUM", "app_pd_manage_albums", "PD" ) );
//======================	


	//$__ur_temp = &new UR_RO_Bool( "VIEWSHARES", "app_treeflduserscb_label", "PD" );
	//$__ur_Folders->AddChild( $__ur_temp );
unset($__ur_Folders);

?>