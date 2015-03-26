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

	if ( isset( $newQPF_ID ) )
		$curQPF_ID = base64_decode( $newQPF_ID );

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

					if ( is_null( $currentBookID ) || !UR_RightsObject::CheckMask( $rights, array( TREE_WRITEREAD, TREE_READWRITEFOLDER  )  ) )
					{
						$fatalError = true;
						$errorStr = $qpStrings['app_norights_book_message'];
						break;
					}

					$qp_pagesClass->currentBookID = $currentBookID;

					$access = null;
					$hierarchy = null;
					$deletable = null;
					$minimalRights = null;
					$suppress_ID = null;
					$suppressIDChildren = false;
					$suppressParent = null;
					$addavailableFolders = true;

					$folders = $qp_pagesClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false, $access, $hierarchy, $deletable, $minimalRights, $suppress_ID, $suppressIDChildren,
								$suppressParent, $addavailableFolders );

					if ( PEAR::isError($folders) )
					{
						$fatalError = true;
						$errorStr = $folders->getMessage();
						break;
					}

					if ( !in_array( $curQPF_ID, array_keys( $folders ) ) )
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

	$btnIndex = getButtonIndex( array(	'movebtn', 'copybtn',
										'deletebtn', 'orderbtn', 'cancelbtn', 'publbtn', 'unpublbtn' ), $_POST, false );

	if ( !isset($selectedpage) )
		$selectedpage = array();

	if ( !isset($pagesIDs) )
		$pagesIDs = array();

	$pageList = base64_encode( serialize( $pagesIDs ) );

	$commonRedirParams = array();

	$commonRedirParams[OPENER] = base64_encode(PAGE_QP_QUICKPAGES);
	$commonRedirParams['QPF_ID'] = base64_encode( $curQPF_ID );
	$commonRedirParams['currentBookID'] = base64_encode( $currentBookID );

	$commonRedirParams['pagelist'] = $pageList;

	if ( !$fatalError )
	{
		switch ($btnIndex)
		{

			case 'copybtn' :
					$commonRedirParams['operation'] = TREE_COPYFOLDER;
					redirectBrowser( PAGE_QP_COPYMOVE, $commonRedirParams );
					break;

			case 'movebtn' :
					$commonRedirParams['operation'] = TREE_MOVEFOLDER;
					redirectBrowser( PAGE_QP_COPYMOVE, $commonRedirParams );
					break;

			case 'deletebtn' :

					if ( !count( $pagesIDs ) )
						break;

					foreach ( $pagesIDs as $key=>$value )
					{
						$targetQNF_ID = $value;

						if ( !in_array( $targetQNF_ID, array_keys( $folders ) ) )
						{
							$fatalError = true;
							$errorStr = $qpStrings['app_page_norights_message'];
							break;
						}

						$params = array();
						$params['U_ID'] = $currentUser;
						$params['kernelStrings'] = $kernelStrings;

						$res = $qp_pagesClass->deleteFolder( $targetQNF_ID, $currentUser, $kernelStrings, false, "qp_onDeleteFolder", $params );
						if ( PEAR::isError($res) )
							$errorStr = $res->getMessage();

						unset( $folders[$targetQNF_ID] );
					}

					break;

			case 'publbtn' :
			case 'unpublbtn' :

					if ( !count( $pagesIDs ) )
						break;

					foreach ( $pagesIDs as $key=>$value )
					{
						if ( !in_array( $value, array_keys( $folders ) ) )
							break;

						if ( PEAR::isError( qp_changePagePublishState( $value, ( $btnIndex == "publbtn" ? 1 : 0 ) ) ) )
						{
							$errorStr = $kernelStrings[ERR_QUERYEXECUTING];
							$fatalError = true;
							break;
						}
					}

					$access = null;
					$hierarchy = null;
					$deletable = null;
					$minimalRights = null;
					$suppress_ID = null;
					$suppressIDChildren = false;
					$suppressParent = null;
					$addavailableFolders = true;

					$folders = $qp_pagesClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false, $access, $hierarchy, $deletable, $minimalRights, $suppress_ID, $suppressIDChildren,
								$suppressParent, $addavailableFolders );

					if ( PEAR::isError($folders) )
					{
						$fatalError = true;
						$errorStr = $folders->getMessage();
						break;
					}

					break;

			case 'orderbtn' :

					if ( !count( $pagesIDs ) )
					{
						redirectBrowser( PAGE_QP_QUICKPAGES, $commonRedirParams );
						break;
					}

					foreach ( $pagesIDs as $key=>$value )
					{
						if ( !in_array( $value, array_keys( $folders ) ) )
							break;

						$folderData = array( "QPF_ID"=>$value, "QPF_SORT"=>$key );

						$res = db_query( $qr_qp_updateSortPage, $folderData );

						if ( PEAR::isError($res) )
						{
							echo $errorStr = $kernelStrings[ERR_QUERYEXECUTING];
							$fatalError = true;
							break;
						}
					}

					$access = null;
					$hierarchy = null;
					$deletable = null;
					$minimalRights = null;
					$suppress_ID = null;
					$suppressIDChildren = false;
					$suppressParent = null;
					$addavailableFolders = true;

					$folders = $qp_pagesClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false, $access, $hierarchy, $deletable, $minimalRights, $suppress_ID, $suppressIDChildren,
								$suppressParent, $addavailableFolders );

					if ( PEAR::isError($folders) )
					{
						$fatalError = true;
						$errorStr = $folders->getMessage();
						break;
					}

					redirectBrowser( PAGE_QP_QUICKPAGES, $commonRedirParams );
					break;

			case 'cancelbtn' :
					redirectBrowser( PAGE_QP_QUICKPAGES, $commonRedirParams );
					break;

		}
	}

	switch (true)
	{
		case true :

					if ( !isset($edited) )
						$accessInheritance = ACCESSINHERITANCE_COPY;

					$selectedFolder = $curQPF_ID;

					if ( !isset( $newQPF_ID ) )
					{
						$selectedFolder ="";
						if ( !qp_getParentId( $hierarchy, $curQPF_ID, $selectedFolder ) )
							$selectedFolder = TREE_AVAILABLE_FOLDERS;

						$curQPF_ID = $selectedFolder;
					}

					// there it makes two folders lists to put one into listbox and other to show in sort list

					$foldersIDs = array();
					$foldersNames = array();

					$tempID = $curQPF_ID;
					if ( $curQPF_ID == TREE_AVAILABLE_FOLDERS )
						$tempID = TREE_ROOT_FOLDER;

					foreach ( $folders as $fQPF_ID=>$folderData )
					{
						if ( $folderData->TYPE == TREE_AVAILABLE_FOLDERS )
							$folderData->NAME = $kernelStrings["app_treeroot_name"];
						else
							$folderData->NAME = $folderData->NAME . ( !$folderData->QPF_PUBLISHED ?  " (".$qpStrings["qp_screen_notpublished_title"].") "  : "" );

						$encodedID = base64_encode($fQPF_ID);
						$folderData->curID = $encodedID;
						$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);

						$folders[$fQPF_ID] = $folderData;

						if ( $folderData->QPF_ID_PARENT == $tempID && $folderData->TYPE != TREE_AVAILABLE_FOLDERS )
						{
							$foldersIDs[] = $fQPF_ID;
							$foldersNames[] = $folderData->NAME; // ." (". ( !$folderData->published ? $qpStrings["qp_notpublished_title"]  : "" ).") ";
						}

						if ( !qp_hasItChilds( $hierarchy, $fQPF_ID ) )
							unset( $folders[$fQPF_ID] );
					}

	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QP_APP_ID );

	$preproc->assign( PAGE_TITLE, $qpStrings['qpo_screen_title'] );
	$preproc->assign( FORM_LINK, PAGE_QP_ORGANIZE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "qpStrings", $qpStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );

	$preproc->assign( "searchString", $searchString );

	$preproc->assign( "QPF_ID", $QPF_ID );
	$preproc->assign( 'currentBookID', base64_encode( $currentBookID ) );

	if ( !$fatalError )
	{
		$preproc->assign( "folders", $folders );
		$preproc->assign( "folderCount", count($folders) );
		$preproc->assign( "hierarchy", $hierarchy );

		$preproc->assign( "selectedFolder", $selectedFolder );

		$preproc->assign( "accessInheritance", $accessInheritance );

		$preproc->assign( "sortCount", count( $foldersNames ) );
		$preproc->assign( "pagesIDs", $foldersIDs );
		$preproc->assign( "pagesNames", $foldersNames );
	}

	$preproc->display( "organize.htm" );
?>