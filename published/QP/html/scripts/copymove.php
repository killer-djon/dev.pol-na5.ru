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

	if ( !isset($searchString) )
		$searchString = null;

	$btnIndex = getButtonIndex( array(), $_POST );

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL ), $_POST );

	$currentBookID = base64_decode( $currentBookID );
	$curQPF_ID = base64_decode( $QPF_ID );

	$pages = unserialize( base64_decode( $pagelist ) );

	switch (true)
	{
		case true :

					$rights = $qp_treeClass->getIdentityFolderRights( $currentUser, $currentBookID, $kernelStrings );

					if ( PEAR::isError($rights) )
					{
						$fatalError = true;
						$errorStr = $rights->getMessage();
						break;
					}

					if ( is_null( $currentBookID ) || !UR_RightsObject::CheckMask( $rights, TREE_READWRITEFOLDER ) )
					{
						$fatalError = true;
						$errorStr = $qpStrings['app_page_norights_message'];
						break;
					}

					$qp_pagesClass->currentBookID = $currentBookID;

					$access = null;
					$hierarchy = null;
					$deletable = null;
					$minimalRights = null;

					$folders = $qp_pagesClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false, $access, $hierarchy, $deletable, $minimalRights );

					if ( PEAR::isError($folders) )
					{
						$fatalError = true;
						$errorStr = $folders->getMessage();
						break;
					}

					foreach( $pages as $key=>$value )
					{
						$tmpQPF_ID = $value;

						if ( !in_array( $tmpQPF_ID, array_keys( $folders ) ) )
						{
							$fatalError = true;
							$errorStr = $qpStrings['app_page_norights_message'];
							break;
						}

						if ( isset( $folderData ) && !in_array( $folderData["QPF_ID"], array_keys( $folders ) ) && !in_array( $folderData["QPF_ID_PARENT"], array_keys( $folders ) ))
						{
							$fatalError = true;
							$errorStr = $qpStrings['app_page_norights_message'];
							break;
						}
					}
	}

	$commonRedirParams = array();
	$commonRedirParams['currentBookID'] = base64_encode( $currentBookID );
	$commonRedirParams['QPF_ID'] = base64_encode( $curQPF_ID );

	switch ($btnIndex) {
		case 0 :

				$showSelected = count( $pages );

				if ( 0 == $showSelected )
					redirectBrowser( PAGE_QP_QUICKPAGES, array() );

				$destQPF_ID = base64_decode($toQPF_ID);

				if ( $operation == TREE_COPYFOLDER || $operation == TREE_MOVEFOLDER )
					foreach( $pages as $key=>$value )
					{
						$srcQPF_ID = $value;

						$pagesAction = $operation == TREE_COPYFOLDER ? ACTION_NEW : ACTION_EDIT;

						$callbackParams = array( "qpStrings"=>$qpStrings, "kernelStrings"=>$kernelStrings, "currentBookID" => $currentBookID, 'action'=>$pagesAction );

						if ( $operation == TREE_MOVEFOLDER )
							$newQPF_ID = $qp_pagesClass->moveFolder( $srcQPF_ID, $destQPF_ID, $currentUser, $kernelStrings, null, null, "qp_onCreateMoveFolder", null, $callbackParams, null, false, false, false, false, TREE_FSTATUS_NORMAL, false, false );
						else
							$newQPF_ID = $qp_pagesClass->copyFolder( $srcQPF_ID, $destQPF_ID, $currentUser, $kernelStrings, null, null, "qp_onCreateFolder", $callbackParams, null, false, "qp_checkPermissionsCallback", false );

						if ( PEAR::isError($newQPF_ID) )
						{
							$errorStr = $newQPF_ID->getMessage();
							break 2;
						}
					}

					$qp_pagesClass->setUserDefaultFolder( $currentUser, $newQPF_ID, $kernelStrings );

					$commonRedirParams['QPF_ID'] = base64_encode( $newQPF_ID );
					redirectBrowser( PAGE_QP_ORGANIZE, $commonRedirParams );

				break;
		case 1 :
				redirectBrowser( PAGE_QP_ORGANIZE, $commonRedirParams );
	}


	switch (true) {
		case true :
					$showSelected = count( $pages );

					if ( 0 == $showSelected )
						redirectBrowser( PAGE_QP_QUICKPAGES, array() );

					foreach( $pages as $key=>$value )
						$pages[$key] = $value;

					if ( !isset($edited) )
						$accessInheritance = ACCESSINHERITANCE_COPY;

					if ( $operation == TREE_COPYFOLDER || $operation == TREE_MOVEFOLDER )
					{
						$minimalRights = TREE_READWRITEFOLDER;
						$showRootFolder = true;
					}

					$supressChildren = $operation == TREE_MOVEFOLDER || $operation == TREE_COPYFOLDER;
					$suppressParent = false;

					$qp_pagesClass->currentBookID = $currentBookID;

					$access = null;
					$hierarchy = null;
					$deletable = null;
					$supressID=null;

					$folders = $qp_pagesClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
															$access, $hierarchy, $deletable, $minimalRights, $supressID,
															$supressChildren, $suppressParent, $showRootFolder );
					if ( PEAR::isError($folders) ) {
						$fatalError = true;
						$errorStr = $folders->getMessage();

						break;
					}

					$pagesFrom = array();
					foreach ( $folders as $fQPF_ID=>$folderData )
					{
						$encodedID = base64_encode($fQPF_ID);
						$folderData->curID = $encodedID;

						if ( in_array( $fQPF_ID, $pages ) )
							$pagesFrom[$fQPF_ID] = $folderData;
					}

					foreach ( $folders as $fQPF_ID=>$folderData )
					{
						$encodedID = base64_encode($fQPF_ID);
						$folderData->curID = $encodedID;
						$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);

						$params = array();

						if ( $operation == TREE_MOVEFOLDER )
						{
							foreach( $pages as $key=>$value )
							{
								$currentid="";
								if ( qp_getParentId( $hierarchy, $value, $currentid ) && $currentid == $fQPF_ID )
								{
									$folderData->RIGHT = TREE_NOACCESS;
									continue;
								}

								if ( qp_isChildOf( $hierarchy, $value, $fQPF_ID ) )
									$folderData->RIGHT = TREE_NOACCESS;
							}
						}

						if ( $operation == TREE_MOVEFOLDER || $operation == TREE_COPYFOLDER )
							if ( $fQPF_ID == TREE_AVAILABLE_FOLDERS )
								$folderData->NAME = $kernelStrings['app_treeroot_name'];

						// Prevert file copy/move operations for current folder
						//
						if ( ($operation == TREE_COPYDOC || $operation == TREE_MOVEDOC) && $fQPF_ID == $QPF_ID )
							$folderData->RIGHT = -2;

						$folders[$fQPF_ID] = $folderData;
					}

					$docNum = null;
					if ( $operation == TREE_COPYDOC || $operation == TREE_MOVEDOC ) {
						$documents = unserialize( base64_decode( $doclist ) );
						$docNum = count( $documents );
					}

	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QP_APP_ID );

	$titles = array( TREE_COPYFOLDER=>'cm_screen_copypage_title', TREE_MOVEFOLDER=>'cm_screen_movepage_title' );
	$saveCaptions = array( TREE_COPYFOLDER=>'cm_screen_copy_btn', TREE_MOVEFOLDER=>'cm_screen_move_btn' );
	$toLabels = array( TREE_COPYFOLDER=>'cm_copyto_message', TREE_MOVEFOLDER=>'cm_moveto_message' );

	$title = $qpStrings[$titles[$operation]];
	$saveCaption = $qpStrings[$saveCaptions[$operation]];
	$toLabel = $qpStrings[$toLabels[$operation]];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_QP_COPYMOVE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "qpStrings", $qpStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "operation", $operation );
	$preproc->assign( "searchString", $searchString );

	$preproc->assign( "QPF_ID", $QPF_ID );
	$preproc->assign( 'currentBookID', base64_encode( $currentBookID ) );
	$preproc->assign( 'opener', $opener );
	$preproc->assign( "pagelist", $pagelist );

	if ( !$fatalError )
	{
		if ( $operation == TREE_COPYDOC || $operation == TREE_MOVEDOC )
			$preproc->assign( "doclist", $doclist );

		$preproc->assign( "showSelected", $showSelected );
		$preproc->assign( "folders", $folders );
		$preproc->assign( "folderCount", count($folders) );
		$preproc->assign( "hierarchy", $hierarchy );
		$preproc->assign( "saveCaption", $saveCaption );
		$preproc->assign( "toLabel", $toLabel );
		$preproc->assign( "docNum", $docNum );

		$preproc->assign( "pagesFrom", $pagesFrom );

		$preproc->assign( "accessInheritance", $accessInheritance );
	}

	$preproc->display( "copymove.htm" );
?>