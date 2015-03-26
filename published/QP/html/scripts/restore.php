<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/QP/qp.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QP";

	pageUserAuthorization( $SCR_ID, $QP_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$qpStrings = $qp_loc_str[$language];
	$invalidField = null;
	$contactCount = 0;
	$saveBtnPressed = false;

	$rightMasks = array(
						 TREE_NOACCESS => array(0,0,0),
						 TREE_ONLYREAD => array(1,0,0),
						 TREE_WRITEREAD => array(1,1,0),
						 TREE_READWRITEFOLDER => array(1,1,1) );

	if ( !isset( $action ) )
		$action = ACTION_NEW;

	if ( !isset( $QPB_ID_PARENT ) )
		$QPB_ID_PARENT = TREE_ROOT_FOLDER;

	if ( !isset($parentFolderID) )
		$parentFolderID = base64_decode( $QPB_ID_PARENT );
	else
		$parentFolderID = base64_decode( $parentFolderID );

	if ( !isset( $opener ) )
		$opener = base64_encode( PAGE_QP_BOOKS );

	$parentFolderID = TREE_ROOT_FOLDER;

	$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL), $_POST );

	$admin = base64_decode($opener) == PAGE_QP_MANAGER;

	$qpBook = new qp_bookArray();

	switch ($btnIndex) {
		case 0:
					$saveBtnPressed = true;

					// Upload and analyze file
					//
					if ( $file['size'] == 0 )
					{
						$errorStr = $qpStrings['rst_selectfile_message'];
						$invalidField = 'file';
						break;
					}

					// Upload file to the temporary dir
					//
					$tmpFileName = uniqid( TMP_FILES_PREFIX );
					$destPath = WBS_TEMP_DIR."/".$tmpFileName;
					if ( !@move_uploaded_file( $file['tmp_name'], $destPath ) ) {
						$errorStr = $kernelStrings['iul_erruploading_message'];
						break;
					}

					$archiveInfo = qp_analyzeArchive( $destPath, $kernelStrings, $qpStrings );
					if ( PEAR::isError($archiveInfo) )
					{
						$errorStr = $archiveInfo->getMessage();
						break;
					}

					if ( PEAR::isError( $ret = qp_checkTextID( $kernelStrings, $qpStrings, $folderData["QPB_TEXTID"], "" ) ) )
					{
						$errorStr = $ret->getMessage();
						$invalidField = 'QPB_TEXTID';
						break;
					}

					$kernelStrings["app_treeinvfoldername_message"]= $qpStrings["app_treeinvfoldername_message"];
					$kernelStrings["app_treeinvfolderlenname_message"]= $qpStrings["app_treeinvfoldername_message"];

					$resultStats = array();

					$folderID = qp_restoreArchive( $currentUser, $folderData["QPB_TEXTID"], $file['name'], $destPath, $kernelStrings, $qpStrings, $resultStats );

					if ( PEAR::isError($folderID) ) {
						$errorStr = $folderID->getMessage();
						break;
					}

					$userAccessRights[UR_REAL_ID] = $folderID;
					$saveResult =  $UR_Manager->SaveItem( $userAccessRights );
					if ( PEAR::isError( $saveResult ) )
					{
						$errorStr = $saveResult->getMessage();
						break;
					}

					$groupAccessRights[UR_REAL_ID] = $folderID;
					$saveResult =  $UR_Manager->SaveItem( $groupAccessRights );
					if ( PEAR::isError( $saveResult ) )
					{
						$errorStr = $saveResult->getMessage();
						break;
					}

					redirectBrowser( PAGE_QP_QUICKPAGES, array() );
					break;

		case 1 :
					redirectBrowser( PAGE_QP_QUICKPAGES, array() );
	}

	switch (true) {
		case true :
					$archiveSupported = qp_archiveSupported();

					$userID = ($admin) ? null : $currentUser;
					$minimalRights = ($admin) ? null : TREE_READWRITEFOLDER;

					$isRootUser = $qp_treeClass->isRootIdentity( $currentUser, $kernelStrings );

					if ( PEAR::isError( $isRootUser ) ) {
						$fatalError = true;
						$errorStr = $folders->getMessage();

						break;
					}

					if ( !$admin && !$isRootUser )
					{
						$fatalError = true;
						$errorStr = $kernelStrings['app_treenocreatefldrights_message'];

						break;
					}

					$userAccessRights = array( UR_PATH=>$qp_treeClass->folderDescriptor->folder_rights_path, UR_ACTION=>UR_ACTION_EDITUSER, UR_OBJECTID=>null, UR_FIELD=>"userAccessRights", UR_COPYFROM=>$parentFolderID );
					$groupAccessRights = array( UR_PATH=>$qp_treeClass->folderDescriptor->folder_rights_path, UR_ACTION=>UR_ACTION_EDITGROUP, UR_OBJECTID=>null, UR_FIELD=>"groupAccessRights", UR_COPYFROM=>$parentFolderID );

					if ( $userAccessRights[UR_OBJECTID] == UR_SYS_ID && !isset( $userAccessRights[UR_REAL_ID] ) )
						$userAccessRights[UR_OBJECTID] = null;

					if ( $groupAccessRights[UR_OBJECTID] == UR_SYS_ID && !isset( $groupAccessRights[UR_REAL_ID] ) )
							$groupAccessRights[UR_OBJECTID] = null;

					$userAccessRightsHtml =  $UR_Manager->RenderItem( $userAccessRights );
					if ( PEAR::isError($userAccessRightsHtml))
					{
						$fatalError = true;
						$errorStr = $userAccessRightsHtml->getMessage();
					}

					$groupAccessRightsHtml =  $UR_Manager->RenderItem( $groupAccessRights );
					if ( PEAR::isError($groupAccessRightsHtml))
					{
						$fatalError = true;
						$errorStr = $groupAccessRightsHtml->getMessage();
					}

					// Prepare form tabs
					//
					$tabs = array();

					$tabs[] = array( PT_NAME=>$qpStrings['app_book_title'],
										PT_PAGE_ID=>'FOLDER',
										PT_FILE=>'amf_restoretab.htm',
										PT_CONTROL=>'folderData[DF_NAME]'
									);

					$tabs[] = array( PT_NAME=>$kernelStrings['app_treeusers_title'],
										PT_PAGE_ID=>'USERS',
										PT_FILE=>'amf_userstab.htm',
									);

					$tabs[] = array( PT_NAME=>$kernelStrings['app_treegroups_title'],
										PT_PAGE_ID=>'GROUP',
										PT_FILE=>'amf_grouptab.htm',
									);

					$oldTemplate = isset($databaseInfo[HOST_DBSETTINGS]['OLD_TEMPLATE']) && $databaseInfo[HOST_DBSETTINGS]['OLD_TEMPLATE'];

					if ( $oldTemplate )
						foreach( $tabs as $key=>$value )
						{
							$value[PT_PATH] = '../classic';
							$tabs[$key] = $value;
						}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QP_APP_ID );

	$preproc->assign( PAGE_TITLE, $qpStrings['rst_screen_title'] );
	$preproc->assign( FORM_LINK, PAGE_QP_RESTORE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( HELP_TOPIC, "modifyfolder.htm" );
	$preproc->assign( 'QPB_ID_PARENT', $QPB_ID_PARENT );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "admin", $admin );

	$preproc->assign( ACTION, $action );
	$preproc->assign( "qpStrings", $qpStrings );

	$preproc->assign( OPENER, $opener );

	$preproc->assign( "hideDelete", base64_decode( $opener ) == PAGE_QP_BOOKS ? false : true );

	$preproc->assign( "archiveSupported", $archiveSupported );

	if ( isset( $TAB ) )
		$preproc->assign( "activeTab", $TAB );


	if ( $action == ACTION_EDIT )
		$preproc->assign( "QPB_ID", $QPB_ID );

	if ( !$fatalError )
	{
		$preproc->assign( "userAccessRightsHtml", $userAccessRightsHtml );
		$preproc->assign( "groupAccessRightsHtml", $groupAccessRightsHtml );

		if ( isset($folderData['QPB_NAME']) )
			$folderData['DF_NAME'] = $folderData['QPB_NAME'];

		if ( $action == ACTION_EDIT )
			$preproc->assign( "thisFolderName", $thisFolderName );
		else
			$preproc->assign( "folders", $folders );

		if ( isset($folderData) )
		{
			$preproc->assign( "folderData", $folderData );
			$preproc->assign( "QPB_PROPERTIES", isset( $folderData["QPB_PROPERTIES"] ) ? base64_encode( $folderData["QPB_PROPERTIES"] ) : "" );
		}

		$preproc->assign( "parentFolderID", $parentFolderID );

		$preproc->assign( "settingsNum", count($folderUsers) );
		$preproc->assign( "folderUsers", $folderUsers );
		$preproc->assign( "folderGroups", $folderGroups );
		$preproc->assign( "userRightsCB", $userRightsCB );
		$preproc->assign( "updateOnFolderChange", true );

		$preproc->assign( "tabs", $tabs );
	}

	$preproc->display( "restore.htm" );
?>
