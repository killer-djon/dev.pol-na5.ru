<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "CT";

	$processButtonTemplate = "javascript:processTextButton('%s', 'form')";

	define( 'EXPAND', 'expand' );
	define( 'COLLAPSE', 'collapse' );
	define( 'HIDE_FOLDER', 'hidefolders' );
	define( 'ACTION_DELETEFOLDER', 'DELETE' );
	define( 'ACTION_SHOWALLUSERS', 'SHOWALLUSERS' );
	define( 'ACTION_SHOWALLGROUPS', 'SHOWALLGROUPS' );

	pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$ddStrings = $dd_loc_str[$language];
	$invalidField = null;
	$contactCount = 0;
	$noAccessGranted = false;
	$folderChanged = false;
	$popupMessage = null;

	if ( !isset($searchString) )
		$searchString = base64_decode(getAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_SEARCHSTRING', null, $readOnly ));

	if ( $searchString == "" )
		$searchString = null;

	if ( !isset( $prevSearchString ) )
		$prevSearchString = null;

	setAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_SEARCHSTRING', base64_encode($searchString), $kernelStrings, $readOnly );

	if ( isset($action) )
		switch ( $action ) {
			case ACTION_DELETEFOLDER :
							$targetDF_ID = base64_decode($DF_ID);

							$res = $dd_treeClass->recycleFolder( $targetDF_ID, $currentUser, $kernelStrings, $ddStrings,
																false, "dd_onDeleteFolder", array('ddStrings'=>$ddStrings, 'kernelStrings'=>$kernelStrings, 'U_ID'=>$currentUser), false );
							if ( PEAR::isError($res) )
								$errorStr = $res->getMessage();

							break;
		}

	// Determine active folder
	//
	if ( !isset( $curDF_ID ) ) {
		$curDF_ID = $dd_treeClass->getUserDefaultFolder( $currentUser, $kernelStrings, $readOnly );

		if ( $curDF_ID == TREE_AVAILABLE_FOLDERS ) {
			$statisticsMode = true;
			setAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );
		}
	} else {
			$curDF_ID = base64_decode($curDF_ID);

			if ( $curDF_ID != TREE_AVAILABLE_FOLDERS ) {

				if ( $dd_treeClass->getIdentityFolderRights( $currentUser, $curDF_ID, $kernelStrings ) == TREE_NOACCESS )
					$curDF_ID = $dd_treeClass->getUserDefaultFolder( $currentUser, $kernelStrings, $readOnly );

				if ( $dd_treeClass->getFolderStatus( $curDF_ID, $kernelStrings ) == TREE_FSTATUS_DELETED )
					$curDF_ID = $dd_treeClass->getUserDefaultFolder( $currentUser, $kernelStrings, $readOnly );

				$dd_treeClass->setUserDefaultFolder( $currentUser, $curDF_ID, $kernelStrings, $readOnly );
				$folderChanged = true;

				$statisticsMode = false;
				setAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );

				if ( $curDF_ID != TREE_AVAILABLE_FOLDERS )
					$dd_treeClass->expandPathToFolder( $curDF_ID, $currentUser, $kernelStrings );
			} else {
				$statisticsMode = true;
				setAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );

				$dd_treeClass->setUserDefaultFolder( $currentUser, $curDF_ID, $kernelStrings, $readOnly );
			}
		}

	if ( isset($action) )
		switch ( $action ) {
			case EXPAND :
			case COLLAPSE :
							$decodedID = base64_decode($DF_ID);
							$dd_treeClass->setFolderCollapseValue( $currentUser, $decodedID, $action == COLLAPSE, $kernelStrings );

							if ( $action == COLLAPSE ) {
								if ( $decodedID != TREE_AVAILABLE_FOLDERS ) {
									if ( $dd_treeClass->isChildOf( $curDF_ID, $decodedID, $kernelStrings ) )
										$curDF_ID = $decodedID;
								} else
									$curDF_ID = $decodedID;

								$dd_treeClass->setUserDefaultFolder( $currentUser, $curDF_ID, $kernelStrings, $readOnly );
								$curDF_ID = $dd_treeClass->getUserDefaultFolder( $currentUser, $kernelStrings, $readOnly );

								if ( $curDF_ID == TREE_AVAILABLE_FOLDERS ) {
									$statisticsMode = true;
									setAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );

									$dd_treeClass->setUserDefaultFolder( $currentUser, $curDF_ID, $kernelStrings, $readOnly );
								}
							}

							break;
			case HIDE_FOLDER :
							$foldersHidden = true;
							setAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_FOLDERSHIDDEN', $foldersHidden, $kernelStrings, $readOnly );

							break;
		}

	$statisticsMode = getAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_STATISTICSMODE', null, $readOnly );

	if ( $prevSearchString != $searchString )
		$folderChanged = true;

	$btnIndex = getButtonIndex( array(	'addfilebtn', 'deletebtn', 'restorebtn', 'removebtn',
										'copybtn', 'movebtn', 'foldersbtn', 'copyfolderbtn',
										'movefolderbtn' , 'viewbtn', 'showFoldersBtn',
										'setgridmodeview', 'setlistmodeview', 'setthumblistmodeview',
										'setthumbtilemodeview', 'uploadarchive', 'createarchive',
										'sendemailbtn', 'checkinbtn', 'checkoutbtn', 'propagaterightsbtn' ), $_POST );

	$commonRedirParams = array();
	$commonRedirParams[OPENER] = base64_encode(PAGE_DD_CATALOG);

	switch ($btnIndex) {
		case 0 :
				$commonRedirParams['DF_ID'] = base64_encode($curDF_ID);
				redirectBrowser( PAGE_DD_ADDFILE, $commonRedirParams );

		case 1 :
				if ( !isset($document) )
					break;

				$res = dd_deleteRestoreDocuments( array_keys($document), DD_DELETEDOC, $currentUser, $kernelStrings, $ddStrings );
				if ( PEAR::isError($res) )
					$popupMessage = $res->getMessage();

				break;

		case 2 :
				if ( !isset($document) )
					break;

				$documentList = base64_encode( serialize( array_keys($document) ) );

				$commonRedirParams['doclist'] = $documentList;
				redirectBrowser( PAGE_DD_RESTORE, $commonRedirParams );

				break;
		case 3 :
				if ( !isset($document) )
					break;

				$res = dd_removeDocuments( array_keys($document), $currentUser, $kernelStrings, $ddStrings );
				if ( PEAR::isError($res) )
					$errorStr = $res->getMessage();

				break;
		case 4 :
				if ( !isset($document) )
					break;

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$commonRedirParams['doclist'] = $documentList;
				$commonRedirParams['operation'] = TREE_COPYDOC;

				if ( !strlen($searchString) )
					$commonRedirParams['DF_ID'] = base64_encode($curDF_ID);
				else
					$commonRedirParams['DF_ID'] = null;

				redirectBrowser( PAGE_DD_COPYMOVE, $commonRedirParams );
		case 5 :
				if ( !isset($document) )
					break;

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$commonRedirParams['doclist'] = $documentList;
				$commonRedirParams['operation'] = TREE_MOVEDOC;

				if ( !strlen($searchString) )
					$commonRedirParams['DF_ID'] = base64_encode($curDF_ID);
				else
					$commonRedirParams['DF_ID'] = null;

				redirectBrowser( PAGE_DD_COPYMOVE, $commonRedirParams );
		case 6 :
				$searchString = null;
				$currentPage = 1;
				setAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_SEARCHSTRING', base64_encode($searchString), $kernelStrings, $readOnly );

				break;
		case 7 :
				$commonRedirParams['operation'] = TREE_COPYFOLDER;
				$commonRedirParams['DF_ID'] = base64_encode($curDF_ID);
				redirectBrowser( PAGE_DD_COPYMOVE, $commonRedirParams );
		case 8 :
				$commonRedirParams['operation'] = TREE_MOVEFOLDER;
				$commonRedirParams['DF_ID'] = base64_encode($curDF_ID);
				redirectBrowser( PAGE_DD_COPYMOVE, $commonRedirParams );
		case 9 :
				$commonRedirParams['DF_ID'] = base64_encode($curDF_ID);
				redirectBrowser( PAGE_DD_VIEW, $commonRedirParams );
		case 10 :
				$foldersHidden = false;
				setAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_FOLDERSHIDDEN', $foldersHidden, $kernelStrings, $readOnly );

				break;
		case 11 :
				dd_setViewOptions( $currentUser, null, DD_GRID_VIEW, null, null, null, null, null, $curDF_ID, $kernelStrings, $readOnly );
				break;
		case 12 :
				dd_setViewOptions( $currentUser, null, DD_LIST_VIEW, null, null, null, null, null, $curDF_ID, $kernelStrings, $readOnly );
				break;
		case 13 :
				dd_setViewOptions( $currentUser, null, DD_THUMBLIST_VIEW, null, null, null, null, null, $curDF_ID, $kernelStrings, $readOnly );
				break;
		case 14 :
				dd_setViewOptions( $currentUser, null, DD_THUMBTILE_VIEW, null, null, null, null, null, $curDF_ID, $kernelStrings, $readOnly );
				break;
		case 15 :
				// Upload archive
				//
				$commonRedirParams['DF_ID'] = base64_encode($curDF_ID);
				redirectBrowser( PAGE_DD_UPLOADARCHIVE, $commonRedirParams );
		case 16 :
				// Create archive
				//
				if ( !isset($document) )
					$document = array();

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$commonRedirParams['doclist'] = $documentList;
				$commonRedirParams['DF_ID'] = base64_encode($curDF_ID);

				redirectBrowser( PAGE_DD_CREATEARCHIVE, $commonRedirParams, "", false, false, true );
		case 17 :
				// Send email
				//
				if ( !isset($document) )
					$document = array();

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$commonRedirParams['doclist'] = $documentList;

				redirectBrowser( PAGE_DD_SENDEMAIL, $commonRedirParams, "", false, false, true );
		case 18 :
				// Check in
				//
				if ( !isset($document) )
					$document = array();

				foreach ( $document as $key=>$value ) {
					$res = dd_changeFileCheckStatus( $currentUser, $key, DD_CHECK_IN, $kernelStrings );

					if ( !$res ) {
						$popupMessage = $ddStrings['dd_screen_checkinrighs_message'];
					}
				}

				break;
		case 19:
				// Check out
				//
				if ( !isset($document) )
					$document = array();

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$commonRedirParams['doclist'] = $documentList;

				redirectBrowser( PAGE_DD_CHECKOUT, $commonRedirParams, "", false, false, true );
		case 20:
				// Propagate access rights
				//
				$commonRedirParams['DF_ID'] = base64_encode($curDF_ID);
				redirectBrowser( PAGE_DD_PROPAGATEACCESSRIGHTS, $commonRedirParams );
	}

	$foldersHidden = getAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_FOLDERSHIDDEN', null, $readOnly );

	switch (true) {
		case true :
					$versionControlEnabled = dd_versionControlEnabled($kernelStrings);

					// Load folder list
					//
					$access = null;
					$hierarchy = null;
					$deletable = null;
					$folders = $dd_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
															$access, $hierarchy, $deletable,
															null, null, false, null, true, null, $statisticsMode );

					if ( PEAR::isError($folders) ) {
						$fatalError = true;
						$errorStr = $folders->getMessage();

						break;
					}

					// Prepare folder list to display
					//
					$collapsedFolders = $dd_treeClass->listCollapsedFolders( $currentUser );

					foreach ( $folders as $DF_ID=>$folderData ) {
						$encodedID = base64_encode($DF_ID);
						$folderData->curDF_ID = $encodedID;
						$folderData->curID = $encodedID;

						if ( $folderData->TYPE != TREE_AVAILABLE_FOLDERS ) {
							if ( $folderData->TREE_ACCESS_RIGHTS != TREE_NOACCESS ) {
								$params = array();
								$params['curDF_ID'] = $encodedID;

								$folderData->ROW_URL = prepareURLStr( PAGE_DD_CATALOG, $params );
							}
						} else {
							$params = array();
							$params = array();
							$params['curDF_ID'] = $encodedID;

							$folderData->ROW_URL = prepareURLStr( PAGE_DD_CATALOG, $params );
						}

						$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);

						$collapseParams = array();
						$collapseParams['DF_ID'] = $encodedID;
						if ( isset($collapsedFolders[$DF_ID]) )
							$collapseParams['action'] = EXPAND;
						else
							$collapseParams['action'] = COLLAPSE;

						$folderData->COLLAPSE_URL = prepareURLStr( PAGE_DD_CATALOG, $collapseParams );

						if ( $statisticsMode )
							$folderData->SHARED = $dd_treeClass->folderIsShared( $DF_ID, $currentUser, $kernelStrings );

						$folders[$DF_ID] = $folderData;
					}

					if ( is_null($curDF_ID) ) {
						$noAccessGranted = true;
						break;
					}

					// Load current folder data
					//
					$folderData = $folders[$curDF_ID];

					// Check if thumbnail generation is enabled
					//
					$thumbnailEnabled = readApplicationSettingValue( $DD_APP_ID, DD_THUMBNAILSTATE, DD_THUMBENABLED, $kernelStrings );
					$thumbnailEnabled = $thumbnailEnabled == DD_THUMBENABLED;

					// Load catalog documents
					//
					$visibleColumns = null;
					$viewMode = null;
					$recordsPerPage = null;
					$showSharedPanel = null;
					$displayIcons = null;
					$folderViewMode = null;
					$restrictDescLen = null;
					dd_getViewOptions( $currentUser, $visibleColumns, $viewMode, $recordsPerPage,
						$showSharedPanel, $displayIcons, $folderViewMode, $restrictDescLen,
						$curDF_ID, $kernelStrings, $readOnly );

					if ( !$thumbnailEnabled )
						if ( $viewMode == DD_THUMBLIST_VIEW || $viewMode == DD_THUMBTILE_VIEW )
							$viewMode = DD_LIST_VIEW;

					if ( $viewMode != DD_GRID_VIEW )
						$visibleColumns = $dd_listModeColumns;

					$isRecycledDir = $curDF_ID == TREE_RECYCLED_FOLDER;
					if ($isRecycledDir) {
						$visibleColumns[] = DD_COLUMN_DELETED;

						$dd_columnNames[DD_COLUMN_DELETED] = 'sv_screen_deletedcol_title';
					}

					if ( !isset($sorting) ) {
						$sorting = getAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_DOCUMENT_SORTING', null, $readOnly );
						if ( !strlen($sorting) )
							$sorting = "DL_FILENAME asc";
						else
							$sorting = base64_decode( $sorting );

						$sortData = parseSortStr( $sorting );
						if ( $sortData['field'] == 'DF_NAME' && $searchString == "" || ( !in_array($sortData['field'], $visibleColumns) ) ) {
							$sorting = "DL_FILENAME asc";
							setAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_DOCUMENT_SORTING', base64_encode($sorting), $kernelStrings, $readOnly );
						}
					} else {
						setAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_DOCUMENT_SORTING', $sorting, $kernelStrings, $readOnly );

						$sorting = base64_decode( $sorting );
					}

					// Add pages support
					//
					$docCount = $dd_treeClass->folderDocumentCount( $curDF_ID, $currentUser, $kernelStrings );

					if ( !isset($currentPage) || !strlen($currentPage) ) {
						if ( !$folderChanged )
							$currentPage = getAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_CURRENTPAGE', null, $readOnly );
						else
							$currentPage = 1;

						if ( !strlen($currentPage) )
							$currentPage = 1;
					}

					if ( is_null( $searchString ) ) {
						$showPageSelector = false;
						$pages = null;
						$pageCount = 0;
						$startIndex = 0;
						$count = 0;
						getQueryLimitValues( $docCount, $recordsPerPage, $showPageSelector, $currentPage, $pages, $pageCount, $startIndex, $count );
					}

					if ( is_null( $searchString ) )
					{
						$files = $dd_treeClass->listFolderDocuments( $curDF_ID, $currentUser, $sorting, $kernelStrings, null, false, $startIndex, $count, $folderData->TREE_ACCESS_RIGHTS );
						$totalFilesNum = $docCount;
					}
					else
					{
						$files = dd_searchFiles( prepareStrToStore($searchString), $currentUser, $sorting, $kernelStrings );
						$totalFilesNum = count($files);
						$showPageSelector = false;
						$pages = null;
						$pageCount = 0;
						$files = addPagesSupport( $files, $recordsPerPage, $showPageSelector, $currentPage, $pages, $pageCount );
					}

					foreach( $files as $key=>$value ) {
						if ( $versionControlEnabled ) {
							if ( $value->DL_CHECKSTATUS == DD_CHECK_OUT ) {
								$checkoutDate = convertToDisplayDateTime($value->DL_CHECKDATETIME, false, true, true );

								$checkedUserName = dd_getUserName($value->DL_CHECKUSERID);

								$value->CHECKOUTMESSAGE = sprintf( "%s %s %s", $ddStrings['add_screen_checkout_label'], $checkedUserName, $checkoutDate );
							}
						}

						$files[$key] = dd_processFileListEntry( $value );
					}

					setAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_CURRENTPAGE', $currentPage, $kernelStrings, $readOnly );

					foreach( $pages as $key => $value ) {
						$params = array();
						$params[PAGES_CURRENT] = $value;
						$params[SORTING_COL] = base64_encode($sorting);
						$params['searchString'] = $searchString;

						$URL = prepareURLStr( PAGE_DD_CATALOG, $params );
						$pages[$key] = array( $value, $URL );
					}

					// Post-process file entries
					//
					$thumbPerms = array();

					foreach( $files as $DL_ID=>$data ) {
						$params = array();
						$params[PAGES_CURRENT] = $currentPage;
						//$params['searchString'] = $searchString;
						$params['DL_ID'] = base64_encode($DL_ID);

						$data->DESC_URL = prepareURLStr( PAGE_DD_FILEPROPERTIES, $params );

						if ( $searchString != "" ) {
							$params = array();
							$params['curDF_ID'] = base64_encode( $data->DF_ID );
							$params['searchString'] = null;

							$data->FOLDER_URL = prepareURLStr( PAGE_DD_CATALOG, $params );
						}

						if ( is_null( $searchString ) )
							$data->TREE_ACCESS_RIGHTS = $folderData->TREE_ACCESS_RIGHTS;

						// Add session thumbnail view permissions
						//
						if( $data->DL_STATUSINT == TREE_DLSTATUS_NORMAL )
							$attachmentPath = dd_getFolderDir( $data->DF_ID )."/".$data->DL_DISKFILENAME;
						elseif ( $data->DL_STATUSINT == TREE_DLSTATUS_DELETED )
							$attachmentPath = dd_recycledDir()."/".$data->DL_DISKFILENAME;

						$thumbPerms[] = $attachmentPath;

						$thumbParams = array();
						$srcExt = null;
						$thumbParams['nocache'] = getThumbnailModifyDate( $attachmentPath, 'win', $srcExt );
						$thumbParams['basefile'] = base64_encode( $attachmentPath );
						$thumbParams['ext'] = base64_encode( $data->DL_FILETYPE );

						$data->THUMB_URL = prepareURLStr( PAGE_GETFILETHUMB, $thumbParams );

						$data->VIEW_DESC_URL = prepareURLStr( PAGE_DD_FILEPROPERTIES, array('DL_ID'=>base64_encode($DL_ID)) );

						$files[$DL_ID] = $data;
					}

					$_SESSION['THUMBPERMS'] = $thumbPerms;

					// Prepare menus
					//
					$allowCopyFolder = UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, array(TREE_ONLYREAD, TREE_WRITEREAD, TREE_READWRITEFOLDER) );
					$folderMenu = array();
					$encodedID = base64_encode($curDF_ID);

					if ( $statisticsMode ) {
						$canCreateAnyFolders = $dd_treeClass->canCreateFolders( $currentUser, $kernelStrings );
						if ( PEAR::isError($canCreateAnyFolders) ) {
							$errorStr = $canCreateAnyFolders->getMessage();
							$fatalError = true;

							break;
						}

						if ( $canCreateAnyFolders )
							$folderData->TREE_ACCESS_RIGHTS = TREE_READWRITEFOLDER;
					}

					if ( UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, TREE_READWRITEFOLDER ) || $isRecycledDir ) {
						$params = array();
						$params[ACTION] = ACTION_NEW;
						$params[OPENER] = base64_encode(PAGE_DD_CATALOG);

						if ( !$statisticsMode )
							$params['DF_ID_PARENT'] = $encodedID;
						else
							$params['DF_ID_PARENT'] = base64_encode( TREE_ROOT_FOLDER );

						$addURL = prepareURLStr( PAGE_DD_ADDMODFOLDER, $params );

						$folderMenu[$kernelStrings['app_treeaddfolder_title']] = $addURL;
					} else
						if ( !$statisticsMode )
							$folderMenu[$kernelStrings['app_treeaddfolder_title']] = null;
						else
							$folderMenu[$kernelStrings['app_treeaddfolder_title']] = "#||alertAddRoot()";

					if ( !$statisticsMode && !$isRecycledDir ) {
						if ( $allowCopyFolder )
							$folderMenu[$kernelStrings['app_treecopyfld_text']] = sprintf( $processButtonTemplate, 'copyfolderbtn' );
						else
							$folderMenu[$kernelStrings['app_treecopyfld_text']] = "#||alertCopy()";
					} else
						$folderMenu[$kernelStrings['app_treecopyfld_text']] = null;

					if ( !$statisticsMode && !$isRecycledDir ) {
						if ( UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, TREE_READWRITEFOLDER ) ) {
							if ( $folderData->ALLOW_MOVE )
								$folderMenu[$kernelStrings['app_treemovefld_text']] = sprintf( $processButtonTemplate, 'movefolderbtn' );
							else
								$folderMenu[$kernelStrings['app_treemovefld_text']] = "#||alertMove()";

							$params = array();
							$params[ACTION] = ACTION_EDIT;
							$params[OPENER] = base64_encode(PAGE_DD_CATALOG);
							$params['DF_ID'] = $encodedID;
							$params['DF_ID_PARENT'] = base64_encode($folderData->DF_ID_PARENT);

							$modifyURL = prepareURLStr( PAGE_DD_ADDMODFOLDER, $params );

							$folderMenu[$kernelStrings['app_treemodfolder_title']] = $modifyURL . "||addToHref(this,'DF_ID', $('curDF_ID').value)";;

							if ( $folderData->ALLOW_DELETE ) {
								$params = array();
								$params[ACTION] = ACTION_DELETEFOLDER;
								$params['DF_ID'] = $encodedID;

								$deleteURL = prepareURLStr( PAGE_DD_CATALOG, $params );
								$deleteURL .= "||confirmFolderDeletion()";

								$folderMenu[$kernelStrings['app_treedelfolder_text']] = $deleteURL;
							} else
								$folderMenu[$kernelStrings['app_treedelfolder_text']] = "#||alertDelete()";

							$folderMenu[] = '-';
							$folderMenu[$ddStrings['dd_screen_propagate_menu']] = sprintf( $processButtonTemplate, 'propagaterightsbtn' );
						} else {
							$folderMenu[$kernelStrings['app_treemovefld_text']] = null;
							$folderMenu[$kernelStrings['app_treemodfolder_title']] = null;
							$folderMenu[$kernelStrings['app_treedelfolder_text']] = null;
							$folderMenu[] = '-';
							$folderMenu[$ddStrings['dd_screen_propagate_menu']] = null;
						}
					} else {
						$folderMenu[$kernelStrings['app_treemovefld_text']] = null;
						$folderMenu[$kernelStrings['app_treemodfolder_title']] = null;
						$folderMenu[$kernelStrings['app_treedelfolder_text']] = null;
						$folderMenu[] = '-';
						$folderMenu[$ddStrings['dd_screen_propagate_menu']] = null;
					}

					$fileMenu = array();

					if ( UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, array( TREE_WRITEREAD, TREE_READWRITEFOLDER) ) && is_null($searchString) && !$statisticsMode && !$isRecycledDir ) {
						$fileMenu[$ddStrings['dd_screen_addfiles_menu']] = sprintf( $processButtonTemplate, 'addfilebtn' );
					} else
						$fileMenu[$ddStrings['dd_screen_addfiles_menu']] = null;

					if ( (!$statisticsMode && !$isRecycledDir) || !is_null($searchString) )
						$fileMenu[$ddStrings['dd_screen_copyfiles_menu']] = sprintf( $processButtonTemplate, 'copybtn' )."||confirmCopy()";
					else
						$fileMenu[$ddStrings['dd_screen_copyfiles_menu']] = null;

					if ( (UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, array(TREE_WRITEREAD, TREE_READWRITEFOLDER) ) && !$statisticsMode) || !is_null($searchString) ) {
						if ( !$isRecycledDir || !is_null($searchString) ) {
							$fileMenu[$ddStrings['dd_screen_movefiles_menu']] = sprintf( $processButtonTemplate, 'movebtn' )."||confirmMove()";
							$fileMenu[$ddStrings['dd_screen_deletefiles_menu']] = sprintf( $processButtonTemplate, 'deletebtn' )."||confirmDeletion()";
						} else {
							$fileMenu[$ddStrings['dd_screen_movefiles_menu']] = null;
							$fileMenu[$ddStrings['dd_screen_deletefiles_menu']] = sprintf( $processButtonTemplate, 'removebtn' )."||confirmDeletion()";
						}
					} else {
						$fileMenu[$ddStrings['dd_screen_movefiles_menu']] = null;
						$fileMenu[$ddStrings['dd_screen_deletefiles_menu']] = null;
					}

					if ( !$statisticsMode && $isRecycledDir && is_null($searchString) )
						$fileMenu[$ddStrings['dd_screen_restore_menu']] = sprintf( $processButtonTemplate, 'restorebtn' )."||confirmRestore()";

					$fileMenu['-'] = '-';

					if ( UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, array(TREE_WRITEREAD, TREE_READWRITEFOLDER) ) && !$statisticsMode && !$isRecycledDir && is_null($searchString) ) {
						$fileMenu[$ddStrings['dd_screen_uploadarchive_menu']] = sprintf( $processButtonTemplate, 'uploadarchive' );
					} else
						$fileMenu[$ddStrings['dd_screen_uploadarchive_menu']] = null;

					$fileMenu[$ddStrings['dd_screen_createarchive_menu']] = sprintf( $processButtonTemplate, 'createarchive' );

					$fileMenu[] = '-';

					if ( !$statisticsMode )
						$fileMenu[$ddStrings['dd_screen_sendemail_menu']] = sprintf( $processButtonTemplate, 'sendemailbtn' )."||checkEmailList()";
					else
						$fileMenu[$ddStrings['dd_screen_sendemail_menu']] = null;

					$fileMenu[] = '-';

					if ( $versionControlEnabled && !$isRecycledDir && UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, array(TREE_WRITEREAD, TREE_READWRITEFOLDER) ) && !$statisticsMode ) {
						$fileMenu[$ddStrings['dd_screen_checkout_menu']] = sprintf( $processButtonTemplate, 'checkoutbtn' )."||confirmCheckOut()";
						$fileMenu[$ddStrings['dd_screen_checkin_menu']] = sprintf( $processButtonTemplate, 'checkinbtn' )."||confirmCheckIn()";
					} else {
						$fileMenu[$ddStrings['dd_screen_checkout_menu']] = null;
						$fileMenu[$ddStrings['dd_screen_checkin_menu']] = null;
					}

					// View menu
					//
					$viewMenu = array();

					$checked = ($viewMode == DD_GRID_VIEW) ? "checked" : "unchecked";
					$viewMenu[$ddStrings['dd_screen_grid_menu']] = sprintf( $processButtonTemplate, 'setgridmodeview' )."||null||$checked";

					$checked = ($viewMode == DD_LIST_VIEW) ? "checked" : "unchecked";
					$viewMenu[$ddStrings['dd_screen_list_menu']] = sprintf( $processButtonTemplate, 'setlistmodeview' )."||null||$checked";

					$checked = ($viewMode == DD_THUMBLIST_VIEW) ? "checked" : "unchecked";
					$cmd = $thumbnailEnabled ? sprintf( $processButtonTemplate, 'setthumblistmodeview' )."||null||$checked" : null;
					$viewMenu[$ddStrings['dd_screen_thumbnaillist_menu']] = $cmd;

					$checked = ($viewMode == DD_THUMBTILE_VIEW) ? "checked" : "unchecked";
					$cmd = $thumbnailEnabled ? sprintf( $processButtonTemplate, 'setthumbtilemodeview' )."||null||$checked" : null;
					$viewMenu[$ddStrings['dd_screen_thumbnailtile_menu']] = $cmd;

					$viewMenu[$ddStrings['dd_screen_custview_menu']] = sprintf( $processButtonTemplate, 'viewbtn' );

					// Other appearance settings
					//
					$params = array();
					$params[ACTION] = HIDE_FOLDER;
					$closeFoldersLink = prepareURLStr( PAGE_DD_CATALOG, $params );

					$hideLeftPanel = $foldersHidden || !is_null( $searchString );
					$showFolderSelector = $foldersHidden && is_null( $searchString );

					if ( $statisticsMode ) {
						$curDF_ID = TREE_AVAILABLE_FOLDERS;
						$folderNum = 0;
						$documentNum = 0;
						$summaryData = $dd_treeClass->getSummaryStatistics( $currentUser, $folderNum, $documentNum, $kernelStrings );
						if ( PEAR::isError($summaryData) ) {
							$fatalError = true;
							$errorStr = $summaryData->getMessage();

							break;
						}

						$summaryStr = sprintf( $ddStrings['dd_screen_summary_note'], $documentNum, $folderNum );
					}

					// Check if folder is shared and format access rights page URL
					//
					if ( !$statisticsMode ) {
						$folderIsShared = $dd_treeClass->folderIsShared( $curDF_ID, $currentUser, $kernelStrings );
						$accessRightsURL = prepareURLStr( PAGE_DD_ACCESSRIGHTS, array( 'DF_ID'=>base64_encode($curDF_ID) ) );
					}

					// Read initial folder tree panel width
					//
					if ( isset($_COOKIE['splitterView'.$DD_APP_ID.$currentUser]) )
						$treePanelWidth = (int)$_COOKIE['splitterView'.$DD_APP_ID.$currentUser];
					else
						$treePanelWidth = 200;
	}
	
	$canTools = checkUserFunctionsRights( $currentUser, $DD_APP_ID, APP_CANTOOLS_RIGHTS, $kernelStrings );
	$canReports = checkUserFunctionsRights( $currentUser, $DD_APP_ID, APP_CANREPORTS_RIGHTS, $kernelStrings );
	
	$toolsMenu[$ddStrings['dd_screen_versioncontrol_menu']] = prepareURLStr(PAGE_DD_RECYCLED, array ("actionName" => "showVersionControl"));
	$toolsMenu[$ddStrings['dd_screen_toolsemail_menu']] = prepareURLStr(PAGE_DD_RECYCLED, array ("actionName" => "showEmailSettings"));
	$toolsMenu[$ddStrings['dd_screen_thumbnail_menu']] = prepareURLStr(PAGE_DD_RECYCLED, array ("actionName" => "showThumbnails"));
	$toolsMenu[] = '-';
	$toolsMenu[$ddStrings['dd_screen_recyclebin_menu']] = prepareURLStr(PAGE_DD_RECYCLED);
	
	$reportsMenu[$ddStrings['rep_spacebyusers_title']] = prepareURLStr(PAGE_DD_REP_SPACEBYUSERS, array ());
	$reportsMenu[$ddStrings['rep_recentuploads_title']] = prepareURLStr(PAGE_DD_REP_RECENTUPLOADS, array ());
	$reportsMenu[$ddStrings['rep_folderssummary_title']] = prepareURLStr(PAGE_DD_REP_FOLDERSSUMMARY, array ());
	$reportsMenu[$ddStrings['rep_filetypestats_title']] = prepareURLStr(PAGE_DD_REP_FILETYPESSTATS, array ());
	$reportsMenu[$ddStrings['rep_frequpd_title']] = prepareURLStr(PAGE_DD_REP_FREQUPLFILES, array ());
	
	
	
	

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	if ($searchString != "")
		$preproc->assign( PAGE_TITLE, $ddStrings['dd_sreen_searchresult_title'] );
	else
		$preproc->assign( PAGE_TITLE, $ddStrings['dd_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_DD_CATALOG );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "ddStrings", $ddStrings );
	$preproc->assign( "popupMessage", $popupMessage );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "tree_access_mode_long_names", $tree_access_mode_long_names );
	$preproc->assign( "genericLinkUnsorted", prepareURLStr( PAGE_DD_CATALOG, array('searchString'=>$searchString) ) );
	$preproc->assign( HELP_TOPIC, "catalog.htm");

	if ( !$fatalError ) {
		$preproc->assign( "folders", $folders );
		$preproc->assign( "hierarchy", $hierarchy );
		$preproc->assign( "collapsedFolders", $collapsedFolders );
		$preproc->assign( "currentFolder", $curDF_ID );
		$preproc->assign( "curDF_ID", base64_encode($curDF_ID) );

		if ( isset($searchString) )
			$preproc->assign( "searchString", $searchString );

		$preproc->assign( "noAccessGranted", $noAccessGranted );
		if ( !$noAccessGranted ) {
			$preproc->assign( "curFolderName", $folderData->DF_NAME );

			foreach( $files as $key=>$file )
				$files[$key] = (array)$file;

			$preproc->assign( "files", $files );
			$preproc->assign( "numDocuments", $totalFilesNum );
			$preproc->assign( "curFolderData", $folderData );

			$preproc->assign( PAGES_SHOW, $showPageSelector );
			$preproc->assign( PAGES_PAGELIST, $pages );
			$preproc->assign( PAGES_CURRENT, $currentPage );
			$preproc->assign( PAGES_NUM, $pageCount );
			$preproc->assign( PAGES_CURRENT, $currentPage );
			$preproc->assign( SORTING_COL, $sorting );

			$preproc->assign( "folderMenu", $folderMenu );
			$preproc->assign( "fileMenu", $fileMenu );
			$preproc->assign( "viewMenu", $viewMenu );
			
			$preproc->assign( "canTools", $canTools );
			$preproc->assign( "canReports", $canReports );
			$preproc->assign( "toolsMenu", $toolsMenu );
			$preproc->assign( "reportsMenu", $reportsMenu );

			$preproc->assign( "treePanelWidth", $treePanelWidth );
			$preproc->assign( "versionControlEnabled", $versionControlEnabled );

			$preproc->assign( "visibleColumns", $visibleColumns );
			$preproc->assign( "viewMode", $viewMode );
			$preproc->assign( "dd_columnNames", $dd_columnNames );
			$preproc->assign( "numColumns", count($visibleColumns)+2 );
			$preproc->assign( "numVisibleColumns", count($visibleColumns) );
			$preproc->assign( "descriptionVisible", in_array(DD_COLUMN_DESC, $visibleColumns) );

			$preproc->assign( "numFiles", count($files) );

			$preproc->assign( "closeFoldersLink", $closeFoldersLink );
			$preproc->assign( "hideLeftPanel", $hideLeftPanel );
			$preproc->assign( "showFolderSelector", $showFolderSelector );
			$preproc->assign( "showSharedPanel", $showSharedPanel );
			$preproc->assign( "displayIcons", $displayIcons );
			$preproc->assign( "restrictDescLen", $restrictDescLen );

			$preproc->assign( "statisticsMode", $statisticsMode );
			$preproc->assign( "tree_access_mode_names", $tree_access_mode_names );
			$preproc->assign( "tree_access_mode_long_names", $tree_access_mode_long_names );

			$preproc->assign( "hideBottomPanel", $isRecycledDir );

			if ( $statisticsMode )
				$preproc->assign( "summaryStr", $summaryStr );
			else {
				if ( is_null( $searchString ) )
					$preproc->assign( "thisFolderRights", $folderData->TREE_ACCESS_RIGHTS );
				$preproc->assign( "folderIsShared", $folderIsShared );
				$preproc->assign( "accessRightsURL", $accessRightsURL );
			}

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
		//$preproc->assign("hideLeftPanel", true);
		$tb = "";
		$tb .= "<div id='SubToolbar'><div>" . $preproc->get_template_vars(PAGE_TITLE) . "</div></div>";
		$tb .= "			<div id='ToolbarIn' style='height: 35px'><ul>\n";
		$params = array("smarty_include_tpl_file" => "dd_toolbar.htm", 'smarty_include_vars' => $preproc->get_template_vars());
		ob_start();
		$preproc->_smarty_include($params);
		$tb .= ob_get_clean();		
		$tb .= "			</ul></div>\n";
		
		
		print $preproc->fetch( "catalog_rightpanel.htm" ) . "--AJAXTOOLBAR--" . $tb;
		exit;
	}
	$preproc->display( "catalog_resizable.htm" );
?>