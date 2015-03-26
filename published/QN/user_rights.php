<?php

class QN_FoldersTreeDescriptor extends  FoldersTreeDescriptor
{
	function QN_FoldersTreeDescriptor( )
	{
		$this->folderDescriptor = new treeFolderTableDescriptor( 'QNFOLDER', 'QNF_ID', 'QNF_NAME', 'QNF_ID_PARENT', 'QNF_STATUS' );
		$this->folderDescriptor = &$this->folderDescriptor;
		$this->documentDescriptor = new treeDocumentsTableDescriptor( 'QUICKNOTES', 'QN_ID', 'QN_STATUS', 'QN_MODIFYUSERNAME' );
		$this->documentDescriptor = &$this->documentDescriptor;
	}
}

$qn_TreeFoldersDescriptor = new QN_FoldersTreeDescriptor( );
$qn_TreeFoldersDescriptor = &$qn_TreeFoldersDescriptor;

$__ur_QNApp = new UR_RO_Container( "QN", "app_name_long", "QN" );
$__ur_QNApp = &$__ur_QNApp;
$UR_Manager->AddChild( $__ur_QNApp );

$__ur_QNScreens = new UR_RO_Container( UR_SCREENS, "app_available_pages_name", "QN"  );
$__ur_QNScreens = &$__ur_QNScreens;
$__ur_QNApp->AddChild( $__ur_QNScreens );

//	$__ur_tmp = &new UR_RO_Screen( "QN", "qn_screen_long_name", "qn_screen_short_name",  "quicknotes.php", "QN" );
	$__ur_QNScreens->AddChild( new UR_RO_Screen( "QN", "qn_screen_long_name", "qn_screen_short_name",  "quicknotes.php", "QN" ) );

	//$__ur_tmp = &new UR_RO_Screen( "QNT", "qnt_screen_long_name", "qnt_screen_short_name",  "quicknotestemplates.php", "QN" );
	//$__ur_QNScreens->AddChild( $__ur_tmp );s
	
$__ur_QNFuncs = new UR_RO_Container( UR_FUNCTIONS, "app_available_functions_name" );
$__ur_QNFuncs = &$__ur_QNFuncs;
$__ur_QNApp->AddChild( $__ur_QNFuncs );

//	$__ur_tmp = &new UR_RO_Bool( APP_CANTOOLS_RIGHTS, "app_cantools_label", 'QN' );
	$__ur_QNFuncs->AddChild( new UR_RO_Bool( APP_CANTOOLS_RIGHTS, "app_cantools_label", 'QN' ) );

$__ur_Folders = new UR_RO_FoldersTree( $qn_TreeFoldersDescriptor, "FOLDERS", "app_treefolders_text",  "QN" );
$__ur_Folders = &$__ur_Folders;
$__ur_QNApp->AddChild( $__ur_Folders );

//	$__ur_temp = &new UR_RO_RootFolder( "ROOT", "app_treerootfolders_label", "QN"  );
	$__ur_Folders->AddChild( new UR_RO_RootFolder( "ROOT", "app_treerootfolders_label", "QN"  ) );

	//$__ur_temp = &new UR_RO_Bool( "VIEWSHARES", "app_treeflduserscb_label", "QN"  );
	$__ur_Folders->AddChild( new UR_RO_Bool( "VIEWSHARES", "app_treeflduserscb_label", "QN"  ) );
unset($__ur_Folders);
?>
