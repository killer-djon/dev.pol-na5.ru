<?php

    define('NEW_CONTACT', 1);	
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "CT";

	pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );
	if ( $fatalError ) {
		$fatalError = false;
		$errorStr = null;
		$SCR_ID = "DB";

		pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );
	}
		
	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$ddStrings = $dd_loc_str[$language];
	$invalidField = null;
	$contactCount = 0;
	$saveBtnPressed = false;

	if ( !isset($parentFolderID) )
		$parentFolderID = base64_decode( $DF_ID_PARENT );
	else
		$parentFolderID = base64_decode( $parentFolderID );

	$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL, 'inheritbtn'), $_POST );

	$admin = base64_decode($opener) == PAGE_DD_DIRECTORYBUILDER;
	$reloadParent = false;

	switch ($btnIndex) {
		case 0:
					$saveBtnPressed = true;

					$callbackParams = array( 'ddStrings'=>$ddStrings, 'kernelStrings'=>$kernelStrings );

					$folderID = $dd_treeClass->addmodFolder( $action, $currentUser, $parentFolderID, prepareArrayToStore($folderData),
														$kernelStrings, $admin, 'dd_onCreateFolder', $callbackParams, true, true );
					if ( PEAR::isError( $folderID ) ) {
						$errorStr = $folderID->getMessage();

						if ( $folderID->getCode() == ERRCODE_INVALIDFIELD )
							$invalidField = $folderID->getUserInfo();

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
						dd_sendNewFolderMessage( $folderID, $callbackParams );

						if ( !$admin ) {
							$dd_treeClass->setFolderCollapseValue( $currentUser, $parentFolderID, false, $kernelStrings );
							$dd_treeClass->setUserDefaultFolder( $currentUser, $folderID, $kernelStrings );
						}
					}
					
					if (!empty($needPropogate)) {
						$res = $dd_treeClass->PropagateRightsRecursive( $currentUser, $folderID, $kernelStrings, $ddStrings );
						if ( PEAR::isError($res) ) {
							$errorStr = $res->getMessage();

							break;
						}
					}
					$reloadParent = true;
					break;
					//redirectBrowser( base64_decode($opener), array( 'curDF_ID'=>base64_encode($folderID), "afterSave" => 1 ) );
		case 1 :
				print "<script>window.parent.closeSubframe();</script>";
				exit;
					//redirectBrowser( base64_decode($opener), array() );
		case 2 :
					$folderUsers = $dd_treeClass->listFolderUsersRights( $parentFolderID, $kernelStrings );
					$folderGroups = $dd_treeClass->listFolderGroupsRights( $parentFolderID, $kernelStrings );

					break;
	}

	switch (true) {
		case true :
					$userID = ($admin) ? null : $currentUser;
					$minimalRights = ($admin) ? null : array(TREE_ONLYREAD, TREE_WRITEREAD, TREE_READWRITEFOLDER);
					$minimalRights = ($admin) ? 0 : TREE_READWRITEFOLDER;

					if ( $action == ACTION_NEW ) {
						$isRootUser = $dd_treeClass->isRootIdentity( $currentUser, $kernelStrings );

						$access = null;
						$hierarchy = null;
						$deletable = null;
						$folders = $dd_treeClass->listFolders( $userID, TREE_ROOT_FOLDER, $kernelStrings, 0, false, $access, $hierarchy, $deletable, $minimalRights );
						if ( PEAR::isError($folders) ) {
							$fatalError = true;
							$errorStr = $folders->getMessage();

							break;
						}
						
						if ( !$admin && !count($folders) && !$isRootUser ) {
							$fatalError = true;
							$errorStr = $kernelStrings['app_treenocreatefldrights_message'];

							break;
						}

						if ( $admin || $isRootUser ) {
							$rootFolder = array();
							$rootFolder['OFFSET_STR'] = null;
							$rootFolder['NAME'] = $kernelStrings['app_treeroot_name'];
							$rootFolder[TREE_ACCESS_RIGHTS] = TREE_READWRITEFOLDER;
							$folders = array_merge( array(TREE_ROOT_FOLDER => (object)$rootFolder), $folders );
						}
						
						foreach ( $folders as $thisDF_ID=>$curFolderData ) {
							$encodedID = base64_encode($thisDF_ID);
							$curFolderData->curDF_ID = $encodedID;
							$curFolderData->curID = $encodedID;
							$curFolderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $curFolderData->OFFSET_STR);
							if ( !$admin )
								if ( !UR_RightsObject::CheckMask( $curFolderData->TREE_ACCESS_RIGHTS, TREE_READWRITEFOLDER ) )
									$curFolderData->TREE_ACCESS_RIGHTS = TREE_NOACCESS;

							$folders[$thisDF_ID] = $curFolderData;
						}

						if ( !isset($edited) ) {
							$findFolder = false;
							if ( $admin )
								$findFolder = false;
							else
								$findFolder = !isset($folders[$parentFolderID]) || !UR_RightsObject::CheckMask( $folders[$parentFolderID]->TREE_ACCESS_RIGHTS, TREE_READWRITEFOLDER );

							if ( $findFolder )
								foreach( $folders as $key=>$data ) {
									if ( $data->TREE_ACCESS_RIGHTS >= TREE_READWRITEFOLDER ) {
										$parentFolderID = $key;
										break;
									}
								}
						}
					} else {
						$curDF_ID = base64_decode($DF_ID);
						$thisFolderData = $dd_treeClass->getFolderInfo( $curDF_ID, $kernelStrings );
						$thisFolderName = $thisFolderData['DF_NAME'];
						
						// Check if folder have subfolders
						//
						$minimalRights = array(TREE_ONLYREAD, TREE_WRITEREAD, TREE_READWRITEFOLDER);

						$access = null;
						$hierarchy = null;
						$deletable = null;
						$folders = $dd_treeClass->listFolders( $currentUser, $curDF_ID, $kernelStrings, 0, false, 
												$access, $hierarchy, $deletable, $minimalRights );

						if ( PEAR::isError($folders) ) {
							$fatalError = true;
							$errorStr = $folders->getMessage();

							break;
						}

						if ( !count($folders) )
							$noFolders = true;
					}

					if ( $action == ACTION_EDIT )
						if ( !isset($edited) ) {
							$curDF_ID = base64_decode($DF_ID);

							$folderData = $dd_treeClass->getFolderInfo( $curDF_ID, $kernelStrings );
							if ( PEAR::isError($folderData) ) {
								$fatalError = true;
								$errorStr = $folderData->getMessage();

								break;
							}
							$folderData['DF_ID'] = $curDF_ID;
						}

					if ( !isset( $edited ) )
					{
						if ( $action == ACTION_EDIT )
						{
							$specialStatus = $folderData["DF_SPECIALSTATUS"];
							/*$userAccessRights = array( UR_PATH=>$dd_treeClass->folderDescriptor->folder_rights_path, UR_ACTION=>UR_ACTION_EDITUSER, UR_OBJECTID=>$curDF_ID, UR_FIELD=>"userAccessRights");
							$groupAccessRights = array( UR_PATH=>$dd_treeClass->folderDescriptor->folder_rights_path, UR_ACTION=>UR_ACTION_EDITGROUP, UR_OBJECTID=>$curDF_ID, UR_FIELD=>"groupAccessRights");*/
							$userAccessRights = array( UR_PATH=>$dd_treeClass->folderDescriptor->folder_rights_path, UR_ACTION=>UR_ACTION_EDITUSER, UR_OBJECTID=>$curDF_ID, UR_FIELD=>"userAccessRights", UR_SPECIALSTATUS => $specialStatus );
							$groupAccessRights = array( UR_PATH=>$dd_treeClass->folderDescriptor->folder_rights_path, UR_ACTION=>UR_ACTION_EDITGROUP, UR_OBJECTID=>$curDF_ID, UR_FIELD=>"groupAccessRights", UR_SPECIALSTATUS => $specialStatus );
						} else {
							$userAccessRights = array( UR_PATH=>$dd_treeClass->folderDescriptor->folder_rights_path, UR_ACTION=>UR_ACTION_EDITUSER, UR_OBJECTID=>null, UR_FIELD=>"userAccessRights", UR_COPYFROM=>$parentFolderID );
							$groupAccessRights = array( UR_PATH=>$dd_treeClass->folderDescriptor->folder_rights_path, UR_ACTION=>UR_ACTION_EDITGROUP, UR_OBJECTID=>null, UR_FIELD=>"groupAccessRights", UR_COPYFROM=>$parentFolderID );
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

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$title = ($action == ACTION_NEW) ? $kernelStrings['app_treeaddfolder_title'] : $kernelStrings['app_treemodfolder_title'];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_DD_ADDMODFOLDER );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( HELP_TOPIC, "modifyfolder.htm" );
	$preproc->assign( 'DF_ID_PARENT', $DF_ID_PARENT );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "admin", $admin );

	$preproc->assign( ACTION, $action );
	$preproc->assign( "ddStrings", $ddStrings );
	if (empty($opener))
		$opener = base64_encode(getenv("REQUEST_URI"));
	$preproc->assign( OPENER, $opener );

	if ( $action == ACTION_EDIT ) {
		$preproc->assign( "DF_ID", $DF_ID );
		$preproc->assign( "canPropogate", !$noFolders );
		if (!empty($afterSave)) {
			$preproc->assign( "messageStr", $kernelStrings["app_changes_applied_message"]);	
		}
	}

	if ( !$fatalError )
	{
		$preproc->assign( "userAccessRightsHtml", $userAccessRightsHtml );
		$preproc->assign( "groupAccessRightsHtml", $groupAccessRightsHtml );

		if ( $action == ACTION_EDIT )
			$preproc->assign( "thisFolderName", $thisFolderName );
		else
			$preproc->assign( "folders", $folders );

		if ( isset($folderData) )
			$preproc->assign( "folderData", $folderData );

		if ( isset($DF_ID) && strlen($DF_ID) )
			$preproc->assign( "folderID", base64_decode($DF_ID) );

		$preproc->assign( "parentFolderID", $parentFolderID );

		$preproc->assign( "updateOnFolderChange", true );
		$preproc->assign( "reloadParent", $reloadParent);

		$preproc->assign( "tabs", $tabs );
	}

	$preproc->display( "addmodfolder.htm" );
?>