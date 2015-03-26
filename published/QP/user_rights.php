<?php

class QP_FoldersBookTreeDescriptor extends FoldersTreeDescriptor
{
	function QP_FoldersBookTreeDescriptor( )
	{
		$this->folderDescriptor = new treeFolderTableDescriptor( 'QPBOOK', 'QPB_ID', 'QPB_NAME', 'QPB_ID_PARENT', 'QPB_STATUS' );
		$this->folderDescriptor = &$this->folderDescriptor;
		$this->documentDescriptor = null;
	}
}

$qp_BookTreeFoldersDescriptor = new QP_FoldersBookTreeDescriptor( );
$qp_BookTreeFoldersDescriptor = &$qp_BookTreeFoldersDescriptor;

$__ur_QPApp = new UR_RO_Container( "QP", "app_name_long", "QP" );
$__ur_QPApp = &$__ur_QPApp;
$UR_Manager->AddChild( $__ur_QPApp );

$__ur_QPScreens = new UR_RO_Container( UR_SCREENS, "app_available_pages_name", "QP" );
$__ur_QPScreens = &$__ur_QPScreens;
$__ur_QPApp->AddChild( $__ur_QPScreens );

	//$__ur_tmp = &new UR_RO_Screen( "QP", "qp_screen_long_name", "qp_screen_short_name",  "quickpages.php", "QP" );
	$__ur_QPScreens->AddChild( new UR_RO_Screen( "QP", "qp_screen_long_name", "qp_screen_short_name",  "quickpages.php", "QP" ) );

	/*
	$__ur_tmp = &new UR_RO_Screen( "QPB", "qpb_screen_long_name", "qpb_screen_short_name",  "qpbooks.php", "QP" );
	$__ur_QPScreens->AddChild( $__ur_tmp );

	$__ur_tmp = &new UR_RO_Screen( "QPT", "qpt_screen_long_name", "qpt_screen_short_name",  "qpthemes.php", "QP" );
	$__ur_QPScreens->AddChild( $__ur_tmp );
	*/
	
$__ur_QPFuncs = new UR_RO_Container( UR_FUNCTIONS, "app_available_functions_name" );
$__ur_QPFuncs = &$__ur_QPFuncs;
$__ur_QPApp->AddChild( $__ur_QPFuncs );

	//$__ur_tmp = &new UR_RO_Bool( APP_CANTOOLS_RIGHTS, "app_cantools_label", 'QP' );
	$__ur_QPFuncs->AddChild( new UR_RO_Bool( APP_CANTOOLS_RIGHTS, "app_cantools_label", 'QP' ) );
	
	//$__ur_tmp = &new UR_RO_Bool( "CANBOOKLIST", "app_hasaccessbooklist_label", 'QP' );
	$__ur_QPFuncs->AddChild( new UR_RO_Bool( "CANBOOKLIST", "app_hasaccessbooklist_label", 'QP' ) );


$__ur_Folders = new UR_RO_FoldersTree( $qp_BookTreeFoldersDescriptor, "FOLDERS", "app_treefolders_text",  "QP" );
$__ur_Folders = &$__ur_Folders;
$__ur_QPApp->AddChild( $__ur_Folders );

//	$__ur_temp = &new UR_RO_RootFolder( "ROOT", "app_treerootfolders_label", "QP"  );
	$__ur_Folders->AddChild( new UR_RO_RootFolder( "ROOT", "app_treerootfolders_label", "QP"  ) );
	
	unset($__ur_Folders);

?>
