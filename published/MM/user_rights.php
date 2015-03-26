<?php

class MM_FoldersTreeDescriptor extends  FoldersTreeDescriptor
{
	function MM_FoldersTreeDescriptor( )
	{
		$this->folderDescriptor = new treeFolderTableDescriptor( 'MMFOLDER', 'MMF_ID', 'MMF_NAME', 'MMF_ID_PARENT', 'MMF_STATUS' );
		$this->folderDescriptor = &$this->folderDescriptor;
		$this->documentDescriptor = new treeDocumentsTableDescriptor( 'MMMESSAGE', 'MMM_ID', 'MMM_USERID' );
		$this->documentDescriptor = &$this->documentDescriptor;
	}
}

$mm_TreeFoldersDescriptor = new MM_FoldersTreeDescriptor( );
$mm_TreeFoldersDescriptor = &$mm_TreeFoldersDescriptor;

$__ur_MMApp = new UR_RO_Container( 'MM', "app_name_long", 'MM' );
$__ur_MMApp = &$__ur_MMApp;
$UR_Manager->AddChild( $__ur_MMApp );

$__ur_MMScreens = new UR_RO_Container( UR_SCREENS, "app_available_pages_name", 'MM' );
$__ur_MMScreens = &$__ur_MMScreens;
$__ur_MMApp->AddChild( $__ur_MMScreens );

$__ur_MMScreens->AddChild( new UR_RO_Screen( "MM", "mm_screen_long_name", "mm_screen_short_name",  "mailmaster.php", 'MM' ) );

$__ur_Folders = new UR_RO_FoldersTree( $mm_TreeFoldersDescriptor, "FOLDERS", "app_available_functions_name", 'MM' );
$__ur_Folders = &$__ur_Folders;
$__ur_MMApp->AddChild( $__ur_Folders );

$__ur_Folders->AddChild( new UR_RO_RootFolder( "INBOX", "app_canaccessinbox_label", 'MM' ) );

$__ur_Folders->AddChild( new UR_RO_RootFolder( "ROOT", "app_treerootfolders_label", 'MM' ) );

unset($__ur_Folders);

?>
