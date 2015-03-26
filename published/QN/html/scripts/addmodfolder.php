<?php

    define('NEW_CONTACT', 1);
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/QN/qn.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QN";

	pageUserAuthorization( $SCR_ID, $QN_APP_ID, false );
	if ( $fatalError ) {
		$fatalError = false;
		$errorStr = null;
		$SCR_ID = "QNM";

		pageUserAuthorization( $SCR_ID, $QN_APP_ID, false );
	}

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$qnStrings = $qn_loc_str[$language];
	$invalidField = null;
	$contactCount = 0;
	$saveBtnPressed = false;

	if ( !isset($parentFolderID) )
		$parentFolderID = base64_decode( $QNF_ID_PARENT );
	else
		$parentFolderID = base64_decode( $parentFolderID );

	$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL), $_POST );

	$admin = base64_decode($opener) == PAGE_QN_MANAGER;

	switch ($btnIndex) {
		case 0:
					if ( isset($folderData['DF_NAME']) )
						$folderData['QNF_NAME'] = $folderData['DF_NAME'];

					$saveBtnPressed = true;

					$folderID = $qn_treeClass->addmodFolder( $action, $currentUser, $parentFolderID, prepareArrayToStore($folderData),
														$kernelStrings, $admin );
					if ( PEAR::isError( $folderID ) ) {
						$errorStr = $folderID->getMessage();

						if ( $folderID->getCode() == ERRCODE_INVALIDFIELD )
							$invalidField = $folderID->getUserInfo();

						if ( $invalidField == 'QNF_NAME' )
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

					if ( $action == ACTION_NEW ) {
						if ( !$admin ) {
							$qn_treeClass->setFolderCollapseValue( $currentUser, $parentFolderID, false, $kernelStrings );
							$qn_treeClass->setUserDefaultFolder( $currentUser, $folderID, $kernelStrings );
						}
					}

					redirectBrowser( base64_decode($opener), array( 'curQNF_ID'=>base64_encode($folderID) ) );
		case 1 :
					redirectBrowser( base64_decode($opener), array() );
	}

	switch (true) {
		case true :
					$userID = ($admin) ? null : $currentUser;
					$minimalRights = ($admin) ? 0 : TREE_READWRITEFOLDER;

					if ( $action == ACTION_NEW )
					{
						$isRootUser = $qn_treeClass->isRootIdentity( $currentUser, $kernelStrings );

						$access = null;
						$hierarchy = null;
						$deletable = null;

						$folders = $qn_treeClass->listFolders( $userID, TREE_ROOT_FOLDER, $kernelStrings, 0, false, $access, $hierarchy, $deletable, $minimalRights );

						if ( PEAR::isError($folders) )
						{
							$fatalError = true;
							$errorStr = $folders->getMessage();

							break;
						}

						if ( $admin || $isRootUser )
						{
							$rootFolder = array();
							$rootFolder['OFFSET_STR'] = null;
							$rootFolder['NAME'] = $kernelStrings['app_treeroot_name'];
							$rootFolder['RIGHT'] = TREE_READWRITEFOLDER;
							$folders = array_merge( array(TREE_ROOT_FOLDER => (object)$rootFolder), $folders );
						}

						if ( !$admin && !count($folders) && !$isRootUser )
						{
							$fatalError = true;
							$errorStr = $kernelStrings['app_treenocreatefldrights_message'];

							break;
						}

						foreach ( $folders as $thisQNF_ID=>$curFolderData )
						{
							$encodedID = base64_encode($thisQNF_ID);
							$curFolderData->curQNF_ID = $encodedID;
							$curFolderData->curID = $encodedID;
							$curFolderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $curFolderData->OFFSET_STR);

							if ( !$admin )
								if ( !UR_RightsObject::CheckMask( $curFolderData->RIGHT, TREE_READWRITEFOLDER ) )
									$curFolderData->RIGHT = TREE_NOACCESS;

							$folders[$thisQNF_ID] = $curFolderData;
						}

						if ( !isset($edited) )
						{
							$findFolder = false;
							if ( $admin )
								$findFolder = false;
							else
								$findFolder = !isset($folders[$parentFolderID]) || !UR_RightsObject::CheckMask( $folders[$parentFolderID]->TREE_ACCESS_RIGHTS, TREE_READWRITEFOLDER );

							if ( $findFolder )
								foreach( $folders as $key=>$data )
								{
									if ( UR_RightsObject::CheckMask( $data->RIGHT, TREE_READWRITEFOLDER ) )
									{
										$parentFolderID = $key;
										break;
									}
								}
						}

					}
					else
					{
 						$curQN_ID = base64_decode($QNF_ID);
						$thisFolderData = $qn_treeClass->getFolderInfo( $curQN_ID, $kernelStrings );

						if ( PEAR::isError( $thisFolderData ) )
						{
							$fatalError = true;
							$errorStr = $thisFolderData->getMessage();
							break;
						}

						$thisFolderName = $thisFolderData['QNF_NAME'];
					}

					if ( $action == ACTION_EDIT )
						if ( !isset($edited) ) {
							$curQNF_ID = base64_decode($QNF_ID);

							$folderData = $qn_treeClass->getFolderInfo( $curQNF_ID, $kernelStrings );
							if ( PEAR::isError($folderData) ) {
								$fatalError = true;
								$errorStr = $folderData->getMessage();

								break;
							}
							$folderData['QNF_ID'] = $curQNF_ID;
						}

					if ( !isset( $edited ) )
					{
						if ( $action == ACTION_EDIT )
						{
							$userAccessRights = array( UR_PATH=>$qn_treeClass->folderDescriptor->folder_rights_path, UR_ACTION=>UR_ACTION_EDITUSER, UR_OBJECTID=>$curQNF_ID, UR_FIELD=>"userAccessRights" );
							$groupAccessRights = array( UR_PATH=>$qn_treeClass->folderDescriptor->folder_rights_path, UR_ACTION=>UR_ACTION_EDITGROUP, UR_OBJECTID=>$curQNF_ID, UR_FIELD=>"groupAccessRights" );
						} else {
							$userAccessRights = array( UR_PATH=>$qn_treeClass->folderDescriptor->folder_rights_path, UR_ACTION=>UR_ACTION_EDITUSER, UR_OBJECTID=>null, UR_FIELD=>"userAccessRights", UR_COPYFROM=>$parentFolderID );
							$groupAccessRights = array( UR_PATH=>$qn_treeClass->folderDescriptor->folder_rights_path, UR_ACTION=>UR_ACTION_EDITGROUP, UR_OBJECTID=>null, UR_FIELD=>"groupAccessRights", UR_COPYFROM=>$parentFolderID );
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

					/*$tabs[] = array( PT_NAME=>$kernelStrings['app_treefolder_text'],
										PT_PAGE_ID=>'FOLDER',
										PT_FILE=>'amf_foldertab.htm',
										PT_CONTROL=>'folderData[DF_NAME]' );*/
					$tabs[] = array( PT_NAME=>$kernelStrings['app_treeusers_title'],
										PT_PAGE_ID=>'USERS',
										PT_FILE=>'amf_userstab.htm' );
					$tabs[] = array( PT_NAME=>$kernelStrings['app_treegroups_title'],
										PT_PAGE_ID=>'GROUP',
										PT_FILE=>'amf_grouptab.htm' );
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QN_APP_ID );

	$title = ($action == ACTION_NEW) ? $kernelStrings['app_treeaddfolder_title'] : $kernelStrings['app_treemodfolder_title'];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_QN_ADDMODFOLDER );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( HELP_TOPIC, "modifyfolder.htm" );
	$preproc->assign( 'QNF_ID_PARENT', $QNF_ID_PARENT );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "admin", $admin );

	$preproc->assign( ACTION, $action );
	$preproc->assign( "qnStrings", $qnStrings );
	$preproc->assign( OPENER, $opener );

	if ( $action == ACTION_EDIT )
		$preproc->assign( "QNF_ID", $QNF_ID );

	if ( !$fatalError )
	{
		$preproc->assign( "userAccessRightsHtml", $userAccessRightsHtml );
		$preproc->assign( "groupAccessRightsHtml", $groupAccessRightsHtml );


		if ( isset($folderData['QNF_NAME']) )
			$folderData['DF_NAME'] = $folderData['QNF_NAME'];

		if ( $action == ACTION_EDIT )
			$preproc->assign( "thisFolderName", $thisFolderName );
		else
			$preproc->assign( "folders", $folders );

		if ( isset($folderData) )
			$preproc->assign( "folderData", $folderData );

		if ( isset($QNF_ID) && strlen($QNF_ID) )
			$preproc->assign( "folderID", base64_decode($QNF_ID) );

		$preproc->assign( "parentFolderID", $parentFolderID );

		$preproc->assign( "updateOnFolderChange", true );

		$preproc->assign( "tabs", $tabs );
	}

	$preproc->display( "addmodfolder.htm" );
?>