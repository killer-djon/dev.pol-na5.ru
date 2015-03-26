<?php

    define('NEW_CONTACT', 1);
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
	$metric = metric::getInstance();
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

	$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL, 'deletebtn'), $_POST );

	$admin = base64_decode($opener) == PAGE_QP_MANAGER;

	$qpBook = new qp_bookArray();

	switch ($btnIndex) {
		case 0:
					if ( isset($folderData['DF_NAME']) )
						$folderData['QPB_NAME'] = $folderData['DF_NAME'];

					$saveBtnPressed = true;

					if ( PEAR::isError( $ret = qp_checkTextID( $kernelStrings, $qpStrings, $folderData["QPB_TEXTID"], $action == ACTION_NEW ? "" : $folderData["QPB_ID"] ) ) )
					{
						$errorStr = $ret->getMessage();
						$invalidField = 'QPB_TEXTID';
						break;
					}

					$kernelStrings["app_treeinvfoldername_message"]= $qpStrings["app_treeinvfoldername_message"];
					$kernelStrings["app_treeinvfolderlenname_message"]= $qpStrings["app_treeinvfoldername_message"];

					$folderID = $qp_treeClass->addmodFolder( $action, $currentUser, $parentFolderID, prepareArrayToStore($folderData),
														$kernelStrings, $admin, null, null, true, false, null, $checkFolderName = false );

					if ( PEAR::isError( $folderID ) ) {
						$errorStr = $folderID->getMessage();

						if ( $folderID->getCode() == ERRCODE_INVALIDFIELD )
							$invalidField = $folderID->getUserInfo();

						if ( $invalidField == 'QPB_NAME' )
							$invalidField = 'DF_NAME';

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

					$callbackParams['U_ID'] = $currentUser;

					$folderData["QPB_PROPERTIES"] = base64_decode( $QPB_PROPERTIES );

					$folderData["QPB_STATUS"] = "0";

					$ret = $qpBook->loadFromArray( $folderData, $kernelStrings, 1, null );

					if ( PEAR::isError( $ret ) )
					{
						$errorStr = $ret->getMessage();
						echo $invalidField = $ret->getUserInfo();

						break;
					}

					$qpBook->QPB_ID = $folderID;

					$ret = $qpBook->saveEntry( ACTION_EDIT, $kernelStrings, $currentUser );
					if ( PEAR::isError($ret) )
					{
						$errorStr = $ret->getMessage();
						$invalidField = $ret->getUserInfo();
						break;
					}

					if ( $action == ACTION_NEW )
						setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_CURRENT_BOOK', base64_encode( $folderID ), $kernelStrings, $readOnly );
					$metric->addAction($DB_KEY, $currentUser, 'QP', 'ADDBOOK', 'ACCOUNT');
					redirectBrowser( base64_decode( $opener ), array() );

		case 2 :

				$QPB_ID = $folderData[QPB_ID];

				$res = qp_deleteBook( $currentUser, $QPB_ID, $kernelStrings );

				if ( PEAR::isError( $res ) )
				{
					$fatalError = true;
					$errorStr = $res->getMessage();
					break;
				}

				redirectBrowser( base64_decode( $opener ), array() );

		case 1 :
				redirectBrowser( base64_decode( $opener ), array() );
	}

	switch (true) {
		case true :
					$userID = ($admin) ? null : $currentUser;
					$minimalRights = ($admin) ? null : TREE_READWRITEFOLDER;

					if ( $action == ACTION_NEW )
					{
						$isRootUser = $qp_treeClass->isRootIdentity( $currentUser, $kernelStrings );

						if ( PEAR::isError( $isRootUser ) ) {
							$fatalError = true;
							$errorStr = $folders->getMessage();

							break;
						}

						if ( !$admin && !$isRootUser ) {
							$fatalError = true;
							$errorStr = $kernelStrings['app_treenocreatefldrights_message'];

							break;
						}

						if ( $admin || $isRootUser ) {
							$rootFolder = array();
							$rootFolder['OFFSET_STR'] = null;
							$rootFolder['NAME'] = $kernelStrings['app_treeroot_name'];
							$rootFolder['RIGHT'] = TREE_READWRITEFOLDER;
							$folders[TREE_ROOT_FOLDER] =(object)$rootFolder;
						}

						$folderData["QPB_PUBLISHED"] = 0;

					} else {
						$curQP_ID = base64_decode($QPB_ID);
						$thisFolderData = $qp_treeClass->getFolderInfo( $curQP_ID, $kernelStrings );

						if ( PEAR::isError( $thisFolderData ) )
						{
							$fatalError = true;
							$errorStr = $thisFolderData->getMessage();
							break;
						}

						$thisFolderName = $thisFolderData['QPB_NAME'];
					}

					if ( $action == ACTION_EDIT )
						if ( !isset($edited) ) {
							$curQPB_ID = base64_decode($QPB_ID);

							$rights = $qp_treeClass->getIdentityFolderRights( $currentUser, $curQPB_ID, $kernelStrings );

							if ( PEAR::isError($rights) )
							{
								$fatalError = true;
								$errorStr = $rights->getMessage();
								break;
							}

							if ( is_null( $QPB_ID ) || !UR_RightsObject::CheckMask( $rights, array( TREE_WRITEREAD, TREE_READWRITEFOLDER  )  ) )
							{
								$fatalError = true;
								$errorStr = $qpStrings['app_norights_book_message'];
								break;
							}

							$folderData = $qp_treeClass->getFolderInfo( $curQPB_ID, $kernelStrings );
							if ( PEAR::isError($folderData) ) {
								$fatalError = true;
								$errorStr = $folderData->getMessage();

								break;
							}
							$folderData['QPB_ID'] = $curQPB_ID;
						}
						else
							$folderData['QPB_PROPERTIES'] = base64_decode( $QPB_PROPERTIES );

					if ( !isset( $edited ) )
					{
						if ( $action == ACTION_EDIT )
						{
							$userAccessRights = array( UR_PATH=>$qp_treeClass->folderDescriptor->folder_rights_path, UR_ACTION=>UR_ACTION_EDITUSER, UR_OBJECTID=>$curQPB_ID, UR_FIELD=>"userAccessRights" );
							$groupAccessRights = array( UR_PATH=>$qp_treeClass->folderDescriptor->folder_rights_path, UR_ACTION=>UR_ACTION_EDITGROUP, UR_OBJECTID=>$curQPB_ID, UR_FIELD=>"groupAccessRights" );
						} else {
							$userAccessRights = array( UR_PATH=>$qp_treeClass->folderDescriptor->folder_rights_path, UR_ACTION=>UR_ACTION_EDITUSER, UR_OBJECTID=>null, UR_FIELD=>"userAccessRights", UR_COPYFROM=>$parentFolderID );
							$groupAccessRights = array( UR_PATH=>$qp_treeClass->folderDescriptor->folder_rights_path, UR_ACTION=>UR_ACTION_EDITGROUP, UR_OBJECTID=>null, UR_FIELD=>"groupAccessRights", UR_COPYFROM=>$parentFolderID );
						}
					}

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
										PT_FILE=>'amf_foldertab.htm',
										PT_CONTROL=>'folderData[DF_NAME]'
									);

					$tabs[] = array( PT_NAME=>$kernelStrings['app_treeusers_title'],
										PT_PAGE_ID=>'USERS',
										PT_FILE=>'amf_userstab.htm' );

					$tabs[] = array( PT_NAME=>$kernelStrings['app_treegroups_title'],
										PT_PAGE_ID=>'GROUP',
										PT_FILE=>'amf_grouptab.htm' );

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

	$title = ($action == ACTION_NEW) ? $qpStrings['qpb_screen_add_title'] : $qpStrings['qpb_screen_modify_title'];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_QP_ADDMODBOOK );
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

		$preproc->assign( "updateOnFolderChange", true );

		$preproc->assign( "tabs", $tabs );
	}

	$preproc->display( "addmodbook.htm" );
?>
