<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/QN/qn.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QN";

	pageUserAuthorization( $SCR_ID, $QN_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$qnStrings = $qn_loc_str[$language];
	$invalidField = null;
	$contactCount = 0;

	$rightMasks = array( TREE_ONLYREAD => array(1,0,0),
						 TREE_WRITEREAD => array(1,1,0),
						 TREE_READWRITEFOLDER => array(1,1,1) );

	switch (true) {
		case true : 
					$curU_ID = base64_decode($U_ID);

					$access = null;
					$hierarchy = null;
					$deletable = null;
					$folders = $qn_treeClass->listFolders( null, TREE_ROOT_FOLDER, $kernelStrings, 0, false, $access, $hierarchy, $deletable );
					if ( PEAR::isError($folders) ) {
						$fatalError = true;
						$errorStr = $folders->getMessage();

						break;
					}

					$userName = getUserName( $curU_ID, true );

					if ( !isset($edited) ) {
						$visibleColumnsIDs = null;
						$viewMode = null;
						$recordsPerPage = null;
						$showSharedPanel = null;
						$contentLimit = null;
						qn_getViewOptions( $curU_ID, $visibleColumnsIDs, $viewMode, $recordsPerPage, $showSharedPanel, $contentLimit, $kernelStrings );

						if ( $action == ACTION_EDIT ) {
							$userRights = $qn_treeClass->listIdentityRights( $kernelStrings, $curU_ID );
							if ( PEAR::isError($userRights) ) {
								$fatalError = true;
								$errorStr = $folders->getMessage();

								break;
							}

							$canCreateRoot = $qn_treeClass->isRootIdentity( $curU_ID, $kernelStrings );
							if ( PEAR::isError($canCreateRoot) ) {
								$fatalError = true;
								$errorStr = $canCreateRoot->getMessage();

								break;
							}
						} else {
							$nonDDUsers = $qn_treeClass->listNotAssignedUsers( $kernelStrings );
							if ( PEAR::isError($nonDDUsers) ) {
								$fatalError = true;
								$errorStr = $nonDDUsers->getMessage();

								break;
							}

							$canCreateRoot = false;
							$userRights = array();
						}

						$userRightsCB = array();

						foreach( $folders as $QNF_ID=>$folderData ) {
							if ( isset( $userRights[$curU_ID] ) ) {
								$rights = $userRights[$curU_ID]['RIGHTS'];

								if ( isset($rights[$QNF_ID]) ) {
									$userRightsCB[$QNF_ID] = $rightMasks[$rights[$QNF_ID]];

									continue;
								}
							}

							$userRightsCB[$QNF_ID] = null;
						}
					}
	}

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 :
				if ( !isset($userRightsCB) )
					$userRightsCB = array();

				$folder_rights = array();

				foreach( $userRightsCB as $QNF_ID=>$data ) {
					$rights = null;

					if ( isset($data[2]) && $data[2] )
						$rights = TREE_READWRITEFOLDER;
					else
						if ( isset($data[1]) && $data[1] )
							$rights = TREE_WRITEREAD;
						else
							if ( isset($data[0]) && $data[0] )
								$rights = TREE_ONLYREAD;

					$folder_rights[$QNF_ID] = $rights;
				}

				$curU_ID = ($action == ACTION_NEW) ? $selectedU_ID : $curU_ID;

				if ( isset($canCreateRoot) && $canCreateRoot )
					$folder_rights[TREE_ROOT_FOLDER] = TREE_READWRITEFOLDER;

				$res = $qn_treeClass->setIdentityRights( $curU_ID, IDT_USER, $folder_rights, $kernelStrings, true );
				if ( PEAR::isError($res) ) {
					$fatalError = true;
					$errorStr = $res->getMessage();

					break;
				}

				if ( !isset($showSharedPanel) )
					$showSharedPanel = 0;

				qn_setViewOptions( $curU_ID, null, null, null, $showSharedPanel, null, $kernelStrings );
		case 1 :
				redirectBrowser( PAGE_QN_MANAGER, array( "firstIndex"=>$firstIndex ) );
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QN_APP_ID );

	$title = ($action == ACTION_NEW) ? $kernelStrings['app_treeadduser_title'] : $kernelStrings['app_treemoduser_title'];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_QN_USERRIGHTS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( 'U_ID', $U_ID );
	$preproc->assign( 'firstIndex', $firstIndex );
	$preproc->assign( "qnStrings", $qnStrings );
	$preproc->assign( ACTION, $action );
	$preproc->assign( "kernelStrings", $kernelStrings );

	if ( !$fatalError ) {		
		$preproc->assign( "hierarchy", $hierarchy );
		$preproc->assign( "folders", $folders );
		$preproc->assign( "userRightsCB", $userRightsCB );
		$preproc->assign( "settingsNum", count($folders) );
		$preproc->assign( "showSharedPanel", $showSharedPanel );

		if ( !isset($canCreateRoot) )
			$canCreateRoot = false;

		$preproc->assign( "canCreateRoot", $canCreateRoot );

		if ( $action == ACTION_NEW ) {
			$preproc->assign( "newUsersIDs", array_keys($nonDDUsers) );
			$preproc->assign( "newUsersNames", array_values($nonDDUsers) );
			$preproc->assign( "nonDDUserCount", count($nonDDUsers) );

			if ( isset($selectedU_ID) )
				$preproc->assign( "selectedU_ID", $selectedU_ID );
		} else {
			$preproc->assign( "userName", $userName );
		}
	}

	$preproc->display( "userrights.htm" );
?>