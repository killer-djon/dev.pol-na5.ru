<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/QP/qp.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QP";

	$processButtonTemplate = "javascript:processTextButton('%s', 'form')";

	define( 'EXPAND', 'expand' );
	define( 'COLLAPSE', 'collapse' );
	define( 'HIDE_FOLDER', 'hidefolders' );
	define( 'ACTION_DELETEFOLDER', 'DELETE' );

	pageUserAuthorization( $SCR_ID, $QP_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$qpStrings = $qp_loc_str[$language];
	$invalidField = null;
	$noAccessGranted = false;
	$folderChanged = false;
	$readOnly = false;
	$noBooks = false;

	if ( !isset($searchString) )
		$searchString = base64_decode(getAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_SEARCHSTRING', null, $readOnly ));

	if ( $searchString == "" )
		$searchString = null;

	if ( !isset( $prevSearchString ) )
		$prevSearchString = null;
	
	$canTools = checkUserFunctionsRights( $currentUser, "QP", APP_CANTOOLS_RIGHTS, $kernelStrings );
	$toolsMenu[$qpStrings["qpt_screen_long_name"]] = prepareURLStr(PAGE_QP_THEMES, array() );
	
	setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_SEARCHSTRING', base64_encode($searchString), $kernelStrings, $readOnly );

	$btnIndex = getButtonIndex( array( 'organizebtn', 'printbtn', 'bookbtn', 'showFoldersBtn', 'pagedeletebtn', 'previewbtn', 'bookdeletebtn', 'foldersbtn', 'publbtn', 'unpublbtn', 'copybtn' ), $_POST, false );

	switch ($btnIndex)
	{

			// Jump to selected book

			case 'bookbtn' :

					if ( !isset( $newCurrentBookID ) )
						break;
					setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_CURRENT_BOOK', base64_encode( $newCurrentBookID ), $kernelStrings, $readOnly );

					break;
	}

	switch (true)
	{
		case true :
					if ( isset( $currentBookID ) )
						$currentBookID = base64_decode( $currentBookID );

					// Load books list
					//

					$access = null;
					$hierarchy = null;
					$deletable = null;

					$books = $qp_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
															$access, $hierarchy, $deletable );

					if ( PEAR::isError($books) )
					{
						$fatalError = true;
						$errorStr = $books->getMessage();
						break;
					}

					if ( count( $books ) == 0 )
					{
						$fatalError = true;
						$noBooks = true;
						
						if ( $qp_treeClass->isRootIdentity( $currentUser, $kernelStrings ) )
							$addCreateButton = true;

						$errorStr = $qpStrings["qp_screen_nobooks_error"];
						break;
					}

					// if DeleteBook menu selected perform book deletion

					if ( isset( $currentBookID ) && $btnIndex == 'bookdeletebtn' && in_array( $currentBookID, array_keys($books) ) )
					{
						$bookToDel = $books[$currentBookID];

						if (  UR_RightsObject::CheckMask( $bookToDel->TREE_ACCESS_RIGHTS, TREE_READWRITEFOLDER ) )
						{
							$res = qp_deleteBook( $currentUser, $currentBookID, $kernelStrings );

							if ( PEAR::isError( $res ) )
							{
								$fatalError = true;
								$errorStr = $res->getMessage();
								break;
							}


							$access = null;
							$hierarchy = null;
							$deletable = null;

							$books = $qp_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
															$access, $hierarchy, $deletable );

							if ( PEAR::isError($books) )
							{
								$fatalError = true;
								$errorStr = $books->getMessage();
								break;
							}

							if ( count( $books ) == 0 )
							{
								$fatalError = true;
								$noBooks = true;

								$errorStr = $qpStrings["qp_screen_nobooks_error"];

								if ( $qp_treeClass->isRootIdentity( $currentUser, $kernelStrings ) )
									$addCreateButton = true;

								break;
							}
						}
					}


					// Dermine current BookId
					//
					if ( ( !isset( $currentBookID ) || $currentBookID=="" ) || isset( $newCurrentBookID ) )
					{
						$currentBookID = getAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_CURRENT_BOOK', null, $readOnly );

						if ( !strlen( $currentBookID ) )
						{
							$keys = array_keys( $books );
							$currentBookID = $keys[0];
							setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_CURRENT_BOOK', base64_encode( $currentBookID ), $kernelStrings, $readOnly );
						}
						else
							$currentBookID = base64_decode( $currentBookID );
					}
					else
						setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_CURRENT_BOOK', base64_encode( $currentBookID ), $kernelStrings, $readOnly );

					if ( !in_array( $currentBookID, array_keys($books) ) )
					{
						$keys = array_keys( $books );
						$currentBookID = $keys[0];
						setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_CURRENT_BOOK', base64_encode( $currentBookID ), $kernelStrings, $readOnly );
					}

					$bookData = $books[$currentBookID];
					
					if (onWebasystServer() && $DB_KEY == "WEBASYST") 
						$toolsMenu["Generate Help"] = prepareURLStr("generatehelp.php", array("currentBookID" => base64_encode( $currentBookID )) );
	}

	// Determine active folder
	//

	if ( isset( $curBOOK_ID ) )
	{
		$curQPF_ID = qp_getID( "page", $curBOOK_ID, $currentBookID );

		if ( PEAR::isError( $curQPF_ID ) || is_null( $curQPF_ID ) )
			unset( $curQPF_ID );
		else
			$curQPF_ID = base64_encode( $curQPF_ID );
	}

	if ( !isset( $curQPF_ID ) )
	{
		$curQPF_ID = $qp_pagesClass->getUserDefaultFolder( $currentUser, $kernelStrings, $readOnly, false );

		if ( $curQPF_ID == TREE_AVAILABLE_FOLDERS )
		{
			$statisticsMode = true;
			setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );
		}
		else
			$statisticsMode = false;
	}
	else
	{
			$curQPF_ID = base64_decode($curQPF_ID);

			if ( $curQPF_ID != TREE_AVAILABLE_FOLDERS )
			{

				if ( $qp_pagesClass->getIdentityFolderRights( $currentUser, $curQPF_ID, $kernelStrings ) == TREE_NOACCESS )
					$curQPF_ID = $qp_pagesClass->getUserDefaultFolder( $currentUser, $kernelStrings, $readOnly, false );

				$qp_pagesClass->setUserDefaultFolder( $currentUser, $curQPF_ID, $kernelStrings, $readOnly );
				$folderChanged = true;

				$statisticsMode = false;
				setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );

				if ( $curQPF_ID != TREE_AVAILABLE_FOLDERS )
					$qp_pagesClass->expandPathToFolder( $curQPF_ID, $currentUser, $kernelStrings );
			}
			else
			{
				$statisticsMode = true;
				setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );

				$qp_pagesClass->setUserDefaultFolder( $currentUser, $curQPF_ID, $kernelStrings, $readOnly );
			}
	}

	if ( isset($action) && !$fatalError )
		switch ( $action )
		{
			case EXPAND :
			case COLLAPSE :
							$decodedID = base64_decode($QPF_ID);
							$qp_pagesClass->setFolderCollapseValue( $currentUser, $decodedID, $action == COLLAPSE, $kernelStrings );

							if ( $action == COLLAPSE ) {
								if ( $decodedID != TREE_AVAILABLE_FOLDERS ) {
									if ( $qp_pagesClass->isChildOf( $curQPF_ID, $decodedID, $kernelStrings ) )
										$curQPF_ID = $decodedID;
								} else
									$curQPF_ID = $decodedID;

								$qp_pagesClass->setUserDefaultFolder( $currentUser, $curQPF_ID, $kernelStrings, $readOnly );
								$curQPF_ID = $qp_pagesClass->getUserDefaultFolder( $currentUser, $kernelStrings, $readOnly, false );

								if ( $curQPF_ID == TREE_AVAILABLE_FOLDERS )
								{
									$statisticsMode = true;
									setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );

									$qp_pagesClass->setUserDefaultFolder( $currentUser, $curQPF_ID, $kernelStrings, $readOnly );
								}
							}

							break;
			case HIDE_FOLDER :
							$foldersHidden = true;
							setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_FOLDERSHIDDEN', $foldersHidden, $kernelStrings, $readOnly );

							break;
		}

	$statisticsMode = getAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_STATISTICSMODE', null, $readOnly );

	if ( $prevSearchString != $searchString )
		$folderChanged = true;

	$commonRedirParams = array();

	$commonRedirParams[OPENER] = base64_encode(PAGE_QP_QUICKPAGES);
	$commonRedirParams['QPF_ID'] = base64_encode( $curQPF_ID );

	if ( isset( $currentBookID ) )
		$commonRedirParams['currentBookID'] = base64_encode( $currentBookID );

	if ( !$fatalError )
	{
		switch ($btnIndex)
		{

			case 'organizebtn' :

					if ( UR_RightsObject::CheckMask( $bookData->TREE_ACCESS_RIGHTS, array( TREE_WRITEREAD, TREE_READWRITEFOLDER ) ) )
						redirectBrowser( PAGE_QP_ORGANIZE, $commonRedirParams );

					break;

			case 'printbtn' :

					if ( !isset($document) )
						$document = array();

					$documentList = base64_encode( serialize( array_keys($document) ) );
					$commonRedirParams['doclist'] = $documentList;
					$commonRedirParams['QPF_ID'] = base64_encode($curQPF_ID);

					redirectBrowser( PAGE_QP_PRINT, $commonRedirParams );

			case 'showFoldersBtn' :

					$foldersHidden = false;
					setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_FOLDERSHIDDEN', $foldersHidden, $kernelStrings, $readOnly );

					break;

			case 'foldersbtn' :

					$searchString=null;
					setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_SEARCHSTRING', base64_encode($searchString), $kernelStrings, $readOnly );

					break;


			case 'pagedeletebtn':

				if ( $curQPF_ID != TREE_AVAILABLE_FOLDERS )
				{
					if (  UR_RightsObject::CheckMask( $bookData->TREE_ACCESS_RIGHTS, array( TREE_WRITEREAD, TREE_READWRITEFOLDER ) ) )
					{
						$params = array();
						$params['U_ID'] = $currentUser;
						$params['kernelStrings'] = $kernelStrings;

						$res = $qp_pagesClass->deleteFolder( $curQPF_ID, $currentUser, $kernelStrings, false, "qp_onDeleteFolder", $params );

						if ( PEAR::isError($res) )
							$errorStr = $res->getMessage();

						$curQPF_ID = TREE_AVAILABLE_FOLDERS;
						$qp_pagesClass->setUserDefaultFolder( $currentUser, $curQPF_ID, $kernelStrings, $readOnly );

						$folderChanged = true;
						$statisticsMode = true;

						setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );
					}
				}

			break;

			case 'previewbtn':

				$commonRedirParams["type"] = $btnIndex;
				redirectBrowser( PAGE_QP_PREVIEW, $commonRedirParams );
				break;


			case 'copybtn':

				if ( UR_RightsObject::CheckMask( $bookData->TREE_ACCESS_RIGHTS, array( TREE_WRITEREAD, TREE_READWRITEFOLDER ) ) )
				{
					$folderData = $qp_pagesClass->getFolderInfo( $curQPF_ID, $kernelStrings );

					if ( PEAR::isError( $folderData ) )
					{
						$errorStr = $folderData->getMessage();
						break;
					}

					$callbackParams = array( "qpStrings"=>$qpStrings, "kernelStrings"=>$kernelStrings, "currentBookID" => $currentBookID, 'action'=>ACTION_NEW );

					$newQPF_ID = $qp_pagesClass->copyFolder( $curQPF_ID, $folderData["QPF_ID_PARENT"], $currentUser, $kernelStrings, null, null, "qp_onCreateFolder", $callbackParams, null, false, "qp_checkPermissionsCallback", false, false );

					if ( PEAR::isError($newQPF_ID) )
					{
						$errorStr = $newQPF_ID->getMessage();
						break;
					}

					$qp_pagesClass->setUserDefaultFolder( $currentUser, $newQPF_ID, $kernelStrings );

					$params = array();
					$params[ACTION] = ACTION_EDIT;
					$params[OPENER] = base64_encode( PAGE_QP_QUICKPAGES );
					$params['QPF_ID'] = base64_encode( $newQPF_ID );
					$params['QPF_ID_PARENT'] = base64_encode( $folderData["QPF_ID_PARENT"] );
					$params["currentBookID"] = base64_encode( $currentBookID );

					redirectBrowser( PAGE_QP_ADDMODPAGE, $params );
				}

				break;

			case 'publbtn' :
			case 'unpublbtn' :

				if ( PEAR::isError( qp_changePagePublishState( $curQPF_ID, ( $btnIndex == "publbtn" ? 1 : 0 ) ) ) )
				{
						$errorStr = $kernelStrings[ERR_QUERYEXECUTING];
						$fatalError = true;
						break;
				}
				break;

		}
	}

	$foldersHidden = getAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_FOLDERSHIDDEN', null, $readOnly );

	switch (true) {
		case true :
					if ( $fatalError )
						break;

					$bookData = $books[$currentBookID];

					if ( is_null( $searchString ) )
					{
						// Load folder list
						//
						$qp_pagesClass->currentBookID = $currentBookID;

						$access = null;
						$hierarchy = null;
						$deletable = null;
						$addavailableFoldersP = false;

						$folders = $qp_pagesClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
																$access, $hierarchy, $deletable, null,
																null, false, null, $addavailableFoldersP, null, false, $curQPF_ID == TREE_AVAILABLE_FOLDERS );

						if ( PEAR::isError($folders) )
						{
							$fatalError = true;
							$errorStr = $folders->getMessage();
							break;
						}

						if ( !count( $folders ) )
						{
							$curQPF_ID = TREE_AVAILABLE_FOLDERS;
							$statisticsMode = true;
							setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );
						}
						else if ( !in_array( $curQPF_ID, array_keys( $folders ) ) || $curQPF_ID == TREE_AVAILABLE_FOLDERS || $statisticsMode )
						{
							$ids = array_keys( $folders );
							$curQPF_ID = $ids[0];
							$statisticsMode = false;
							setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );
						}

						$qp_pagesClass->setUserDefaultFolder( $currentUser, $curQPF_ID, $kernelStrings, $readOnly );

						// Prepare folder list to display
						//
						$collapsedFolders = $qp_pagesClass->listCollapsedFolders( $currentUser );

						foreach ( $folders as $QPF_ID=>$folderData )
						{
							$encodedID = base64_encode($QPF_ID);

							$folderData->curQPF_ID = $encodedID;
							$folderData->curQPB_ID = base64_encode( $currentBookID );

							$folderData->curID = $encodedID;

							$folderData->TREE_ACCESS_RIGHTS = intval( $bookData->TREE_ACCESS_RIGHTS );

							if ( $folderData->TYPE != TREE_AVAILABLE_FOLDERS )
							{
								if ( $folderData->TREE_ACCESS_RIGHTS != TREE_NOACCESS )
								{
									$params = array();
									$params['curQPF_ID'] = $encodedID;
									$params['currentBookID'] = base64_encode( $currentBookID );

									$folderData->ROW_URL = prepareURLStr( PAGE_QP_QUICKPAGES, $params );
								}
							}
							else
							{
								$params = array();
								$params['curQPF_ID'] = $encodedID;

								$folderData->ROW_URL = prepareURLStr( PAGE_QP_QUICKPAGES, $params );
								$folderData->NAME = $bookData->NAME;
							}

							$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);

							$collapseParams = array();

							$collapseParams['QPF_ID'] = $encodedID;

							if ( isset($collapsedFolders[$QPF_ID]) )
								$collapseParams['action'] = EXPAND;
							else
								$collapseParams['action'] = COLLAPSE;

							$folderData->COLLAPSE_URL = prepareURLStr( PAGE_QP_QUICKPAGES, $collapseParams );
							if ($folderData->QPF_PUBLISHED == false)
								$folderData->ICON_CLS = "book-unpublished";

							$folders[$QPF_ID] = $folderData;
						}

						if ( is_null($curQPF_ID) )
						{
							$noAccessGranted = true;
							break;
						}

						// Load current folder data
						//
						$folderData = $qp_pagesClass->getFolderInfo( $curQPF_ID, $kernelStrings );
						if ( PEAR::isError( $folderData ) )
						{
							$folderData = array();
							$folderData["QPF_CONTENT"] = "";
							$folderData["QPF_MODIFYDATETIME"] ="";
						}

						$folderData["QPF_CONTENT"] = preg_replace( '/(<[^>]*?="{0,1})([^">]*)pageid:([^">]*?[^>]*>)/u', '$1'.'$2'."quickpages.php?currentBookID=".base64_encode( $currentBookID )."&curBOOK_ID=".'$3', $folderData["QPF_CONTENT"] );
					}
					else
					{
						$public=false;
						$spages = qp_searchPages( $public, $searchString, $currentBookID, $DB_KEY, $kernelStrings );

						$showPageSelector = false;
						$pages = null;
						$pageCount = 0;

						$pagesFound = sprintf( $qpStrings["app_pagesfound_title"], count( $spages ) );

						$spages = addPagesSupport( $spages, 30, $showPageSelector, $currentPage, $pages, $pageCount );
					}

					if ( $bookData->QPB_PUBLISHED )
					{
						$themeID = $bookData->QPB_THEME;

						$useDefault = false;
						if ( !is_null( $themeID ) && !PEAR::isError( $theme = qp_getTheme( $currentUser, $themeID, $kernelStrings ) ) )
						{
							if ( is_null( $theme ) || $theme["QPT_SHARED"] == 0 )
								$useDefault = true;
							else
								$themeName = $theme["QPT_NAME"];
						}
						else
							$useDefault = true;

						if ( $useDefault )
							$themeName = $qpStrings["app_systemtheme_text"];

					}

					// Prepare menus
					//
					$folderMenu = array();
					$encodedID = base64_encode($curQPF_ID);

					$params = array();
					$params[OPENER] = base64_encode(PAGE_QP_QUICKPAGES);
					$params["QPB_ID"] = base64_encode( $currentBookID );
					$bookPropURL = prepareURLStr( PAGE_QP_JUMPBOOK, $params );

					$bookMenu[$qpStrings['qp_screen_jumpbook_menu']] = $bookPropURL;
					$bookMenu[] = "-";
					$canBooklist = checkUserFunctionsRights( $currentUser, "QP", "CANBOOKLIST", $kernelStrings );
					$bookMenu[$qpStrings['qp_screen_booklist_menu']] = $canBooklist ? PAGE_QP_BOOKS : "";

					$isBookRootUser = $qp_treeClass->isRootIdentity( $currentUser, $kernelStrings );

					$params = array();
					$params[ACTION] = ACTION_NEW;
					$params[OPENER] = base64_encode(PAGE_QP_QUICKPAGES);
					$bookPropURL = prepareURLStr( PAGE_QP_ADDMODBOOK, $params );

					if ( $isBookRootUser )
						$bookMenu[$qpStrings['qp_screen_createbook_menu']] = $bookPropURL;
					else
						$bookMenu[$qpStrings['qp_screen_createbook_menu']] = null;

					$params = array();
					$params[ACTION] = ACTION_EDIT;
					$params[OPENER] = base64_encode(PAGE_QP_QUICKPAGES);
					$params["QPB_ID"] = base64_encode( $currentBookID );
					$params["TAB"] = "USERS";

					$bookPropURL = prepareURLStr( PAGE_QP_ADDMODBOOK, $params );

					if (  UR_RightsObject::CheckMask( $bookData->TREE_ACCESS_RIGHTS, TREE_READWRITEFOLDER )  )
					{
						$bookMenu[$qpStrings['qp_screen_deletebook_menu']] = sprintf( $processButtonTemplate, 'bookdeletebtn' )."||confirmBookDeletion()";
						$bookMenu[] = "-";
						$bookMenu[$qpStrings['qp_screen_accessbook_menu']] = $bookPropURL;
						$bookMenu[] = "-";

						unset( $params["TAB"] );
						$bookPropURL = prepareURLStr( PAGE_QP_ADDMODBOOK, $params );

						$bookMenu[$qpStrings['qp_screen_bookprop_menu']] = $bookPropURL;
					}
					else
					{
						$bookMenu[$qpStrings['qp_screen_deletebook_menu']] = null;
						$bookMenu[] = "-";
						$bookMenu[$qpStrings['qp_screen_accessbook_menu']] = null;
						$bookMenu[] = "-";
						$bookMenu[$qpStrings['qp_screen_bookprop_menu']] = null;
					}

					$bookMenu[] = "-";

					$params = array();
					$params[OPENER] = base64_encode(PAGE_QP_QUICKPAGES);
					$params["currentBookID"] = base64_encode( $currentBookID );

					if (  UR_RightsObject::CheckMask( $bookData->TREE_ACCESS_RIGHTS, array( TREE_WRITEREAD, TREE_READWRITEFOLDER ) ) )
						$bookMenu[$qpStrings['qp_screen_publish_menu']] = prepareURLStr( PAGE_QP_PUBLISH, $params );
					else
						$bookMenu[$qpStrings['qp_screen_publish_menu']] = null;

					$bookMenu[] = "-";

					$bookMenu[$qpStrings['qp_screen_backup_menu']] = prepareURLStr( PAGE_QP_BACKUP, $params );

					if ( $isBookRootUser )
						$bookMenu[$qpStrings['qp_screen_restore_menu']] = prepareURLStr( PAGE_QP_RESTORE, $params );
					else
						$bookMenu[$qpStrings['qp_screen_restore_menu']] = null;

					if (  UR_RightsObject::CheckMask( $bookData->TREE_ACCESS_RIGHTS, array( TREE_WRITEREAD, TREE_READWRITEFOLDER ) ) )
					{
						$params = array();
						$params[ACTION] = ACTION_NEW;
						$params[OPENER] = base64_encode(PAGE_QP_QUICKPAGES);
						$params["currentBookID"] = base64_encode( $currentBookID );

						/*if ( !$statisticsMode )
							$params['QPF_ID_PARENT'] = $encodedID;
						else
							$params['QPF_ID_PARENT'] = base64_encode( TREE_ROOT_FOLDER );*/

						$addURL = prepareURLStr( PAGE_QP_ADDMODPAGE, $params );

						$folderMenu[$qpStrings['qp_screen_treeaddpage_menu']] = $addURL;
					} else
						$folderMenu[$qpStrings['qp_screen_treeaddpage_menu']] = null;

					if ( !$statisticsMode && is_null( $searchString ) )
					{
						if (  UR_RightsObject::CheckMask( $bookData->TREE_ACCESS_RIGHTS, array( TREE_WRITEREAD, TREE_READWRITEFOLDER ) ) )
						{
							$params = array();
							$params[ACTION] = ACTION_EDIT;
							$params[OPENER] = base64_encode(PAGE_QP_QUICKPAGES);
							//$params['QPF_ID'] = $encodedID;
							//$params['QPF_ID_PARENT'] = base64_encode($folderData["QPF_ID_PARENT"]);
							$params["currentBookID"] = base64_encode( $currentBookID );

							$modifyURL = prepareURLStr( PAGE_QP_ADDMODPAGE, $params );

							$folderMenu[$qpStrings['qp_screen_treemodpage_menu']] = $modifyURL;
							$folderMenu[$qpStrings['qp_screen_copy_menu']] = sprintf( $processButtonTemplate, 'copybtn' );
							$folderMenu[$qpStrings['qp_screen_treedelpage_menu']] = sprintf( $processButtonTemplate, 'pagedeletebtn' )."||confirmDeletion()";
							$folderMenu[] = '-';
							$folderMenu[$qpStrings['qp_screen_treeorganize_menu']] = sprintf( $processButtonTemplate, 'organizebtn' );
							$folderMenu[] = '-';

							if ( $folderData["QPF_PUBLISHED"] )
								$folderMenu["<span id='pubBtn'>" . $qpStrings['qpo_mark_unpub_btn'] . "</span>"] = sprintf( $processButtonTemplate, 'unpublbtn' );
							else
								$folderMenu["<span id='pubBtn'>" . $qpStrings['qpo_mark_pub_btn']  . "</span>"] = sprintf( $processButtonTemplate, 'publbtn' );

						}
						else
						{
							$folderMenu[$qpStrings['qp_screen_treemodpage_menu']] = null;
							$folderMenu[$qpStrings['qp_screen_copy_menu']] = null;
							$folderMenu[$qpStrings['qp_screen_treedelpage_menu']] = null;
							$folderMenu[] = '-';
							$folderMenu[$qpStrings['qp_screen_treeorganize_menu']] = null;
							$folderMenu[] = '-';

							if ( isset( $folderData["QPF_PUBLISHED"] ) && $folderData["QPF_PUBLISHED"] )
								$folderMenu[$qpStrings['qpo_mark_unpub_btn']] = null;
							else
								$folderMenu[$qpStrings['qpo_mark_pub_btn']] = null;
						}
					} else {
						$folderMenu[$qpStrings['qp_screen_treemodpage_menu']] = null;
						$folderMenu[$qpStrings['qp_screen_treedelpage_menu']] = null;
						$folderMenu[] = '-';
						$folderMenu[$qpStrings['qp_screen_treeorganize_menu']] = null;
					}


					// Other appearance settings
					//
					$params = array();
					$params[ACTION] = HIDE_FOLDER;
					$closeFoldersLink = prepareURLStr( PAGE_QP_QUICKPAGES, $params );

					$hideLeftPanel = $foldersHidden || !is_null( $searchString );
					$showFolderSelector = $foldersHidden && is_null( $searchString );

					if ( $statisticsMode )
						$curQPF_ID = TREE_AVAILABLE_FOLDERS;

					// Read initial folder tree panel width
					//
					if ( isset($_COOKIE['splitterView'.$QP_APP_ID.$currentUser]) )
						$treePanelWidth = (int)$_COOKIE['splitterView'.$QP_APP_ID.$currentUser];
					else
						$treePanelWidth = 200;
					$treePanelHide = (@$_COOKIE['splitterVisible'.$QP_APP_ID.$currentUser] == "false");

				$url = "DB_KEY=".base64_encode( $DB_KEY )."&BookID=".$bookData->QPB_TEXTID;

				$urlBook = qp_getBookURL( ).$url;

	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QP_APP_ID );

	if ($searchString != "")
		$preproc->assign( PAGE_TITLE, $qpStrings['app_screen_search_result_title'] );
	else
		$preproc->assign( PAGE_TITLE, $qpStrings['qp_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_QP_QUICKPAGES );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "qpStrings", $qpStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "tree_access_mode_long_names", $tree_access_mode_long_names );
	$preproc->assign( "genericLinkUnsorted", prepareURLStr( PAGE_QP_QUICKPAGES, array('searchString'=>$searchString) ) );

	$preproc->assign( "curAPP_ID", $QP_APP_ID );

	if ( $noBooks )
	{
		$preproc->assign( "noBooks", true );
		$preproc->assign( ERROR_STR, "" );

		if ( isset( $addCreateButton ) )
		{
			$preproc->assign( "addCreateButton", $addCreateButton );

			$params = array();
			$params[ACTION] = ACTION_NEW;
			$params[OPENER] = base64_encode(PAGE_QP_QUICKPAGES);
			$bookPropURL = prepareURLStr( PAGE_QP_ADDMODBOOK, $params );
			$bookMenu[$qpStrings['qp_screen_createbook_menu']] = $bookPropURL;
			$bookMenu[$qpStrings['qp_screen_restore_menu']] = prepareURLStr( PAGE_QP_RESTORE, $params );
		}
		else
		{
			$bookMenu[$qpStrings['qp_screen_createbook_menu']] = null;
			$bookMenu[$qpStrings['qp_screen_restore_menu']] = null;
		}
		$canBooklist = checkUserFunctionsRights( $currentUser, "QP", "CANBOOKLIST", $kernelStrings );
		$bookMenu[$qpStrings['qp_screen_booklist_menu']] = $canBooklist ? PAGE_QP_BOOKS : "";
					
		$preproc->assign( "bookMenu", $bookMenu );

	}

	$preproc->assign( "toolsMenu", $toolsMenu);
	$preproc->assign( "canTools", $canTools);
	if ( !$fatalError )
	{
		if ( isset($searchString) )
			$preproc->assign( "searchString", $searchString );
		
		$preproc->assign( "noAccessGranted", $noAccessGranted );

		if ( !$noAccessGranted )
		{

			$preproc->assign( "urlBook", $urlBook );

			if ( !isset($searchString) )
			{
				$preproc->assign( "folders", $folders );
				$preproc->assign( "hierarchy", $hierarchy );
				$preproc->assign( "collapsedFolders", $collapsedFolders );
				$preproc->assign( "currentFolder", $curQPF_ID );
				$preproc->assign( "curFolderData", $folderData );

				$preproc->assign( "hasItChilds", qp_hasItChilds( $hierarchy, $curQPF_ID ) );

				$fd = (array) $folderData;
				$fd["ModifiedDateTime"] = convertToDisplayDateTime( $fd["QPF_MODIFYDATETIME"], false, true, true );
				$preproc->assign( "folderData", $fd );
			}
			else
			{
				$preproc->assign( PAGES_SHOW, $showPageSelector );
				$preproc->assign( PAGES_PAGELIST, $pages );
				$preproc->assign( PAGES_CURRENT, $currentPage );
				$preproc->assign( PAGES_NUM, $pageCount );
				$preproc->assign( PAGES_CURRENT, $currentPage );
				$preproc->assign( "searchResult", $spages );
				$preproc->assign( "searchCount", count( $spages ) );
				$preproc->assign( "pagesFound", $pagesFound );
			}

			$preproc->assign( "currentBookID", base64_encode( $currentBookID ) );
			$preproc->assign( "bookData", (array)$bookData );

			if ( isset( $folderData["QPF_NAME"] ) )
				$preproc->assign( "curFolderName", $folderData["QPF_NAME"] );

			if ( isset( $themeName ) )
				$preproc->assign( "themeName", $themeName );

			$preproc->assign( "folderMenu", $folderMenu );
			$preproc->assign( "bookMenu", $bookMenu );
			
			$preproc->assign( "treePanelWidth", $treePanelWidth );
			$preproc->assign( "treePanelHide", $treePanelHide );

			$preproc->assign( "closeFoldersLink", $closeFoldersLink );
			$preproc->assign( "hideLeftPanel", $hideLeftPanel );
			$preproc->assign( "showFolderSelector", $showFolderSelector );

			$preproc->assign( "statisticsMode", $statisticsMode );
			$preproc->assign( "tree_access_mode_names", $tree_access_mode_names );
			$preproc->assign( "tree_access_mode_long_names", $tree_access_mode_long_names );

			if ( isset($userShowAllLink) ) {
				$preproc->assign( "userLimitStr", $userLimitStr );
				$preproc->assign( "userShowAllLink", $userShowAllLink );
			}

			if ( isset($groupShowAllLink) ) {
				$preproc->assign( "groupLimitStr", $groupLimitStr );
				$preproc->assign( "groupShowAllLink", $groupShowAllLink );
			}
		}
	}
	
	if ($preproc->get_template_vars('ajaxAccess')) {
		require_once( "../../../common/html/includes/ajax.php" );
		$ajaxRes = array ();
		$ajaxRes["toolbar"] = simple_ajax_get_toolbar ("qp_toolbar.htm", $preproc);
		$ajaxRes["rightContent"] = $preproc->fetch( "quickpages_rightpanel.htm" );
		print simple_ajax_encode($ajaxRes);
		exit;
	}
	
	$preproc->display( "quickpages_resizable.htm" );
?>