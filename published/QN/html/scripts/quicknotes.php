<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/QN/qn.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QN";

	$processButtonTemplate = "javascript:processTextButton('%s', 'form')";

	define( 'EXPAND', 'expand' );
	define( 'COLLAPSE', 'collapse' );
	define( 'HIDE_FOLDER', 'hidefolders' );
	define( 'ACTION_DELETEFOLDER', 'DELETE' );
	define( 'ACTION_SHOWALLUSERS', 'SHOWALLUSERS' );
	define( 'ACTION_SHOWALLGROUPS', 'SHOWALLGROUPS' );

	pageUserAuthorization( $SCR_ID, $QN_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$qnStrings = $qn_loc_str[$language];
	$invalidField = null;
	$noAccessGranted = false;
	$folderChanged = false;

	
	if ( !isset($searchString) )
		$lastSearchString = base64_decode(getAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_SEARCHSTRING', null, $readOnly ));
	else
		$lastSearchString = $searchString;
	
	if ( $searchString == "" )
		$searchString = null;

	if ( !isset( $prevSearchString ) )
		$prevSearchString = null;

	if ($searchString)
		setAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_SEARCHSTRING', base64_encode($searchString), $kernelStrings, $readOnly );
	
	$canTools = checkUserFunctionsRights( $currentUser, $QN_APP_ID, APP_CANTOOLS_RIGHTS, $kernelStrings );
	$toolsMenu[$qnStrings["qnt_screen_long_name"]] = prepareURLStr(PAGE_QN_TPLLIST, array() );

	if ( isset($action) )
		switch ( $action ) {
			case ACTION_DELETEFOLDER :
							$targetQNF_ID = base64_decode($QNF_ID);

							$params = array();
							$params['U_ID'] = $currentUser;
							$params['kernelStrings'] = $kernelStrings;

							$res = $qn_treeClass->deleteFolder( $targetQNF_ID, $currentUser, $kernelStrings, false, "qn_onDeleteFolder", $params );
							if ( PEAR::isError($res) )
								$errorStr = $res->getMessage();

							break;
		}

	// Determine active folder
	//
	if ( !isset( $curQNF_ID ) ) {
		$curQNF_ID = $qn_treeClass->getUserDefaultFolder( $currentUser, $kernelStrings, $readOnly );

		if ( $curQNF_ID == TREE_AVAILABLE_FOLDERS ) {
			$statisticsMode = true;
			setAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );
		}
	} else {
			$curQNF_ID = base64_decode($curQNF_ID);

			if ( $curQNF_ID != TREE_AVAILABLE_FOLDERS ) {

				if ( $qn_treeClass->getIdentityFolderRights( $currentUser, $curQNF_ID, $kernelStrings ) == TREE_NOACCESS )
					$curQNF_ID = $qn_treeClass->getUserDefaultFolder( $currentUser, $kernelStrings, $readOnly );

				$qn_treeClass->setUserDefaultFolder( $currentUser, $curQNF_ID, $kernelStrings, $readOnly );
				$folderChanged = true;

				$statisticsMode = false;
				setAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );

				if ( $curQNF_ID != TREE_AVAILABLE_FOLDERS )
					$qn_treeClass->expandPathToFolder( $curQNF_ID, $currentUser, $kernelStrings );
			} else {
				$statisticsMode = true;
				setAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );

				$qn_treeClass->setUserDefaultFolder( $currentUser, $curQNF_ID, $kernelStrings, $readOnly );
			}
		}

	if ( isset($action) )
		switch ( $action ) {
			case EXPAND :
			case COLLAPSE :
							$decodedID = base64_decode($QNF_ID);
							$qn_treeClass->setFolderCollapseValue( $currentUser, $decodedID, $action == COLLAPSE, $kernelStrings );

							if ( $action == COLLAPSE ) {
								if ( $decodedID != TREE_AVAILABLE_FOLDERS ) {
									if ( $qn_treeClass->isChildOf( $curQNF_ID, $decodedID, $kernelStrings ) )
										$curQNF_ID = $decodedID;
								} else
									$curQNF_ID = $decodedID;

								$qn_treeClass->setUserDefaultFolder( $currentUser, $curQNF_ID, $kernelStrings, $readOnly );
								$curQNF_ID = $qn_treeClass->getUserDefaultFolder( $currentUser, $kernelStrings, $readOnly );

								if ( $curQNF_ID == TREE_AVAILABLE_FOLDERS ) {
									$statisticsMode = true;
									setAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );

									$qn_treeClass->setUserDefaultFolder( $currentUser, $curQNF_ID, $kernelStrings, $readOnly );
								}
							}

							break;
			case HIDE_FOLDER :
							$foldersHidden = true;
							setAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_FOLDERSHIDDEN', $foldersHidden, $kernelStrings, $readOnly );

							break;
		}

	$statisticsMode = getAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_STATISTICSMODE', null, $readOnly );

	if ( $prevSearchString != $searchString )
		$folderChanged = true;

	$btnIndex = getButtonIndex( array(	'adddocbtn', 'copyfolderbtn', 'movefolderbtn',
										'deletebtn', 'copybtn', 'movebtn', 'foldersbtn',
										'showFoldersBtn', 'viewbtn', 'setgridmodeview',
										'setlistmodeview', 'printbtn' ), $_POST );

	$commonRedirParams = array();
	$commonRedirParams[OPENER] = base64_encode(PAGE_QN_QUICKNOTES);

	switch ($btnIndex) {
		case 0 :
				$commonRedirParams['QNF_ID'] = base64_encode($curQNF_ID);
				$commonRedirParams[ACTION] = ACTION_NEW;
				redirectBrowser( PAGE_QN_ADDMODNOTE, $commonRedirParams );
		case 1 :
				$commonRedirParams['operation'] = TREE_COPYFOLDER;
				$commonRedirParams['QNF_ID'] = base64_encode($curQNF_ID);
				redirectBrowser( PAGE_QN_COPYMOVE, $commonRedirParams );
		case 2 :
				$commonRedirParams['operation'] = TREE_MOVEFOLDER;
				$commonRedirParams['QNF_ID'] = base64_encode($curQNF_ID);
				redirectBrowser( PAGE_QN_COPYMOVE, $commonRedirParams );

		case 3 :
				if ( !isset($document) )
					break;

				$res = qn_deleteNotes( array_keys($document), $currentUser, $kernelStrings, $qnStrings );
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
					$commonRedirParams['QNF_ID'] = base64_encode($curQNF_ID);
				else
					$commonRedirParams['QNF_ID'] = null;

				redirectBrowser( PAGE_QN_COPYMOVE, $commonRedirParams );
		case 5 :
				if ( !isset($document) )
					break;

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$commonRedirParams['doclist'] = $documentList;
				$commonRedirParams['operation'] = TREE_MOVEDOC;

				if ( !strlen($searchString) )
					$commonRedirParams['QNF_ID'] = base64_encode($curQNF_ID);
				else
					$commonRedirParams['QNF_ID'] = null;

				redirectBrowser( PAGE_QN_COPYMOVE, $commonRedirParams );
		case 6 :
				$searchString = null;
				setAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_SEARCHSTRING', base64_encode($searchString), $kernelStrings, $readOnly );

				break;
		case 7 :
				$foldersHidden = false;
				setAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_FOLDERSHIDDEN', $foldersHidden, $kernelStrings, $readOnly );

				break;
		case 8 :
				$commonRedirParams["searchString"] = $searchString;
				redirectBrowser( PAGE_QN_VIEW, $commonRedirParams );
		case 9 :
				qn_setViewOptions( $currentUser, null, QN_GRID_VIEW, null, null, null, $kernelStrings, $readOnly );
				break;
		case 10 :
				qn_setViewOptions( $currentUser, null, QN_LIST_VIEW, null, null, null, $kernelStrings, $readOnly );
				break;
		case 11 :
				if ( !isset($document) )
					$document = array();

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$commonRedirParams['doclist'] = $documentList;
				$commonRedirParams['QNF_ID'] = base64_encode($curQNF_ID);

				redirectBrowser( PAGE_QN_PRINT, $commonRedirParams );
	}

	$foldersHidden = getAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_FOLDERSHIDDEN', null, $readOnly );
	$foldersHidden = false;

	switch (true) {
		case true :

					// Load folder list
					//
					$access = null;
					$hierarchy = null;
					$deletable = null;
					$folders = $qn_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
															$access, $hierarchy, $deletable, null,
															null, false, null, true, null, $curQNF_ID == TREE_AVAILABLE_FOLDERS );
					if ( PEAR::isError($folders) ) {
						$fatalError = true;
						$errorStr = $folders->getMessage();

						break;
					}

					// Prepare folder list to display
					//
					$collapsedFolders = $qn_treeClass->listCollapsedFolders( $currentUser );

					foreach ( $folders as $QNF_ID=>$folderData ) {
						$encodedID = base64_encode($QNF_ID);
						$folderData->curQNF_ID = $encodedID;
						$folderData->curID = $encodedID;

						if ( $folderData->TYPE != TREE_AVAILABLE_FOLDERS ) {
							if ( $folderData->TREE_ACCESS_RIGHTS != TREE_NOACCESS ) {
								$params = array();
								$params['curQNF_ID'] = $encodedID;

								$folderData->ROW_URL = prepareURLStr( PAGE_QN_QUICKNOTES, $params );
							}
						} else {
							$params = array();
							$params = array();
							$params['curQNF_ID'] = $encodedID;

							$folderData->ROW_URL = prepareURLStr( PAGE_QN_QUICKNOTES, $params );
						}

						$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);

						$collapseParams = array();
						$collapseParams['QNF_ID'] = $encodedID;
						if ( isset($collapsedFolders[$QNF_ID]) )
							$collapseParams['action'] = EXPAND;
						else
							$collapseParams['action'] = COLLAPSE;

						$folderData->COLLAPSE_URL = prepareURLStr( PAGE_QN_QUICKNOTES, $collapseParams );

						if ( $statisticsMode )
							$folderData->SHARED = $qn_treeClass->folderIsShared( $QNF_ID, $currentUser, $kernelStrings );

						$folders[$QNF_ID] = $folderData;
					}

					if ( is_null($curQNF_ID) ) {
						$noAccessGranted = true;
						break;
					}

					// Load current folder data
					//
					$folderData = $folders[$curQNF_ID];

					// Load catalog documents
					//
					$visibleColumns = null;
					$viewMode = null;
					$recordsPerPage = null;
					$showSharedPanel = null;
					$contentLimit = null;
					qn_getViewOptions( $currentUser, $visibleColumns, $viewMode, $recordsPerPage, $showSharedPanel, $contentLimit, $kernelStrings, $readOnly );

					if ( !strlen($contentLimit) )
						$contentLimit = 0;


					if ( !isset($sorting) ) {
						$sorting = getAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_DOCUMENT_SORTING', null, $readOnly );
						if ( !strlen($sorting) )
							$sorting = "QN_SUBJECT asc";
						else
							$sorting = base64_decode( $sorting );

						$sortData = parseSortStr( $sorting );
						if ( $sortData['field'] == 'QNF_NAME' && $searchString == "" || ( !in_array($sortData['field'], $visibleColumns) ) ) {
							$sorting = "QN_SUBJECT asc";
							setAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_DOCUMENT_SORTING', base64_encode($sorting), $kernelStrings, $readOnly );
						}
					} else {
						setAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_DOCUMENT_SORTING', $sorting, $kernelStrings, $readOnly );

						$sorting = base64_decode( $sorting );
					}

					// Add pages support
					//
					$docCount = $qn_treeClass->folderDocumentCount( $curQNF_ID, $currentUser, $kernelStrings );

					if ( !isset($currentPage) || !strlen($currentPage) ) {
						if ( !$folderChanged )
							$currentPage = getAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_CURRENTPAGE', null, $readOnly );
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
						$notes = $qn_treeClass->listFolderDocuments( $curQNF_ID, $currentUser, $sorting, $kernelStrings, null, false,  $startIndex, $count, $folderData->TREE_ACCESS_RIGHTS );
						$totalFilesNum = count($notes);
					}
					else
					{
						$notes = qn_searchNotes( $searchString, $currentUser, $sorting, $kernelStrings );
						$totalFilesNum = count($notes);
						$showPageSelector = false;
						$pages = null;
						$pageCount = 0;
						$notes = addPagesSupport( $notes, $recordsPerPage, $showPageSelector, $currentPage, $pages, $pageCount );
					}

					foreach( $notes as $key=>$value )
						$notes[$key] = qn_processFileListEntry( $value );

					setAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_CURRENTPAGE', $currentPage, $kernelStrings, $readOnly );

					foreach( $pages as $key => $value ) {
						$params = array();
						$params[PAGES_CURRENT] = $value;
						$params[SORTING_COL] = base64_encode($sorting);
						$params['searchString'] = $searchString;

						$URL = prepareURLStr( PAGE_QN_QUICKNOTES, $params );
						$pages[$key] = array( $value, $URL );
					}

					// Post-process note entries
					//
					foreach( $notes as $QN_ID=>$data ) {
						$params = array();
						$params[PAGES_CURRENT] = $currentPage;
						$params['searchString'] = $searchString;
						$params['QN_ID'] = base64_encode($QN_ID);

						$data->DESC_URL = prepareURLStr( PAGE_QN_ADDMODNOTE, $params );

						if ( $searchString != "" ) {
							$params = array();
							$params['curQNF_ID'] = base64_encode( $data->QNF_ID );
							$params['searchString'] = null;

							$data->FOLDER_URL = prepareURLStr( PAGE_QN_QUICKNOTES, $params );
						}

						if ( is_null( $searchString ) )
							$data->TREE_ACCESS_RIGHTS = $folderData->TREE_ACCESS_RIGHTS;

						$notes[$QN_ID] = $data;
					}

					// Load folder users
					//
					if ( isset($action) && $action == ACTION_SHOWALLUSERS )
						$userlistLimit = null;
					else
						$userlistLimit = TREEDOC_MAXVIEWUSERS;

					if ( isset($action) && $action == ACTION_SHOWALLGROUPS )
						$grouplistLimit = null;
					else
						$grouplistLimit = TREEDOC_MAXVIEWUSERS;

					$folderUsers = $qn_treeClass->listFolderUsers( $curQNF_ID, $kernelStrings, LFU_GROUPSANDUSERS,
																	$currentUser, $userlistLimit, $grouplistLimit );

					$showAllParams = array();
					$showAllParams[PAGES_CURRENT] = $currentPage;
					$showAllParams[SORTING_COL] = base64_encode($sorting);
					$showAllParams['searchString'] = $searchString;

					if ( $folderUsers[LFU_USERSLIMITED] )
					{
						$userLimitStr = sprintf( $kernelStrings['app_userlisttotal_text'], $folderUsers[LFU_USERSTOTAL] );

						$showAllParams[ACTION] = ACTION_SHOWALLUSERS;
						$userShowAllLink = prepareURLStr( PAGE_DD_CATALOG, $showAllParams );
					}

					if ( $folderUsers[LFU_GROUPSLIMITED] )
					{
						$groupLimitStr = sprintf( $kernelStrings['app_grouplisttotal_text'], $folderUsers[LFU_GROUPSTOTAL] );

						$showAllParams[ACTION] = ACTION_SHOWALLGROUPS;
						$groupShowAllLink = prepareURLStr( PAGE_DD_CATALOG, $showAllParams );
					}

					// Prepare menus
					//
					$allowCopyFolder =  ( $folderData->TREE_ACCESS_RIGHTS ^ TREE_ONLYREAD ) != TREE_NOACCESS;
					$folderMenu = array();
					$encodedID = base64_encode($curQNF_ID);

					if ( $statisticsMode )
					{
						$canCreateAnyFolders = $qn_treeClass->canCreateFolders( $currentUser, $kernelStrings );
						if ( PEAR::isError($canCreateAnyFolders) )
						{
							$errorStr = $canCreateAnyFolders->getMessage();
							$fatalError = true;

							break;
						}

						if ( $canCreateAnyFolders )
							$folderData->TREE_ACCESS_RIGHTS = TREE_READWRITEFOLDER;
					}

					if ( UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, TREE_READWRITEFOLDER ) )
					{
						$params = array();
						$params[ACTION] = ACTION_NEW;
						$params[OPENER] = base64_encode(PAGE_QN_QUICKNOTES);

						if ( !$statisticsMode )
							$params['QNF_ID_PARENT'] = $encodedID;
						else
							$params['QNF_ID_PARENT'] = base64_encode( TREE_ROOT_FOLDER );

						//$addURL = prepareURLStr( PAGE_QN_ADDMODFOLDER, $params );
						$addURL = "#||createFolder()";

						$folderMenu[$kernelStrings['app_treeaddfolder_title']] = $addURL;
					} else {
						if ( !$statisticsMode )
							$folderMenu[$kernelStrings['app_treeaddfolder_title']] = null;
						else
							$folderMenu[$kernelStrings['app_treeaddfolder_title']] = "#||alertAddRoot()";
					}
					$folderMenu[] = "-";
					
					$folderMenuCopyItem =  ( $allowCopyFolder ) ? sprintf( $processButtonTemplate, 'copyfolderbtn' ) : $folderMenu[$kernelStrings['app_treecopyfld_text']] = "#||alertCopy()";
					
					if ( !$statisticsMode )
					{
						if ( UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, TREE_READWRITEFOLDER ) )
						{
							$params = array();
							$params[ACTION] = ACTION_EDIT;
							$params[OPENER] = base64_encode(PAGE_QN_QUICKNOTES);
							$params['QNF_ID'] = $encodedID;
							$params['QNF_ID_PARENT'] = base64_encode($folderData->QNF_ID_PARENT);

							$modifyURL = prepareURLStr( PAGE_QN_ADDMODFOLDER, $params );

							$folderMenu[$kernelStrings['app_treemodfolder_title']] = $modifyURL;
							$folderMenu[] = "-";
							
							$folderMenu[$kernelStrings['app_treerenamefolder_title']] = "#||renameFolder()";
							$folderMenuCopyItem =  ( $allowCopyFolder ) ? sprintf( $processButtonTemplate, 'copyfolderbtn' ) : $folderMenu[$kernelStrings['app_treecopyfld_text']] = "#||alertCopy()";
							
							if ( $folderData->ALLOW_MOVE )
								$folderMenu[$kernelStrings['app_treemovefld_text']] = sprintf( $processButtonTemplate, 'movefolderbtn' );
							else
								$folderMenu[$kernelStrings['app_treemovefld_text']] = "#||alertMove()";
							
							if ( $folderData->ALLOW_DELETE ) {
								$params = array();
								$params[ACTION] = ACTION_DELETEFOLDER;
								$params['QNF_ID'] = $encodedID;

								//$deleteURL = prepareURLStr( PAGE_QN_QUICKNOTES, $params );
								//$deleteURL .= "||confirmFolderDeletion()";
								$deleteURL = "#||deleteCurrentFolder()";

								$folderMenu[$kernelStrings['app_treedelfolder_text']] = $deleteURL;
							} else
								$folderMenu[$kernelStrings['app_treedelfolder_text']] = "#||alertDelete()";
						} else {
							$folderMenu[$kernelStrings['app_treemodfolder_title']] = null;
							$folderMenu[] = "-";
							$folderMenu[$kernelStrings['app_treerenamefolder_title']] = null;
							$folderMenu[$kernelStrings['app_treemovefld_text']] = null;
							$folderMenu[$kernelStrings['app_treedelfolder_text']] = null;
						}
					} else {
						$folderMenu[$kernelStrings['app_treemodfolder_title']] = null;
						$folderMenu[] = "-";
						$folderMenu[$kernelStrings['app_treerenamefolder_title']] = null;
						$folderMenu[$kernelStrings['app_treecopyfld_text']] = null;
						$folderMenu[$kernelStrings['app_treemovefld_text']] = null;
						$folderMenu[$kernelStrings['app_treedelfolder_text']] = null;
					}
					
					if (!checkUserAccessRights( $currentUser, "UNG", "UG", false))
						unset($folderMenu[$kernelStrings['app_treemodfolder_title']]);

					$fileMenu = array();

					if ( UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, TREE_WRITEREAD )  && is_null($searchString) && !$statisticsMode ) {
						$fileMenu[$qnStrings['qn_screen_addnote_menu']] = sprintf( $processButtonTemplate, 'adddocbtn' );
					} else
						$fileMenu[$qnStrings['qn_screen_addnote_menu']] = null;

					if ( (!$statisticsMode) || !is_null($searchString) )
						$fileMenu[$qnStrings['qn_screen_copynote_menu']] = sprintf( $processButtonTemplate, 'copybtn' )."||confirmCopy()";
					else
						$fileMenu[$qnStrings['qn_screen_copynote_menu']] = null;

					if ( ( UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, TREE_WRITEREAD ) && !$statisticsMode) || !is_null($searchString) ) {
						$fileMenu[$qnStrings['qn_screen_movenote_menu']] = sprintf( $processButtonTemplate, 'movebtn' )."||confirmMove()";
						$fileMenu[$qnStrings['qn_screen_deletenote_menu']] = sprintf( $processButtonTemplate, 'deletebtn' )."||confirmDeletion()";
					} else {
						$fileMenu[$qnStrings['qn_screen_movenote_menu']] = null;
						$fileMenu[$qnStrings['qn_screen_deletenote_menu']] = null;
					}

					$fileMenu[null] = '-';
					$fileMenu[$qnStrings['qn_screen_print_menu']] = sprintf( $processButtonTemplate, 'printbtn' );

					// View menu
					//
					$viewMenu = array();

					$checked = ($viewMode == QN_GRID_VIEW) ? "checked" : "unchecked";
					$viewMenu[$qnStrings['qn_screen_grid_menu']] = sprintf( $processAjaxButtonTemplate, 'setgridmodeview' )."||null||$checked";

					$checked = ($viewMode == QN_LIST_VIEW) ? "checked" : "unchecked";
					$viewMenu[$qnStrings['qn_screen_list_menu']] = sprintf( $processAjaxButtonTemplate, 'setlistmodeview' )."||null||$checked";
					$viewMenu[$qnStrings['qn_screen_custview_menu']] = sprintf( $processButtonTemplate, 'viewbtn' );

					// Other appearance settings
					//
					$params = array();
					$params[ACTION] = HIDE_FOLDER;
					$closeFoldersLink = prepareURLStr( PAGE_QN_QUICKNOTES, $params );

					$hideLeftPanel = $foldersHidden || !is_null( $searchString );
					$hideLeftPanel = false;					
					//$showFolderSelector = $foldersHidden && is_null( $searchString );
					$showFolderSelector = false;

					if ( $statisticsMode ) {
						$curQNF_ID = TREE_AVAILABLE_FOLDERS;

						$folderNum = 0;
						$documentNum = 0;
						$summaryData = $qn_treeClass->getSummaryStatistics( $currentUser, $folderNum, $documentNum, $kernelStrings );
						if ( PEAR::isError($summaryData) ) {
							$fatalError = true;
							$errorStr = $summaryData->getMessage();

							break;
						}

						$summaryStr = sprintf( $qnStrings['qn_screen_summary_note'], $documentNum, $folderNum );
					}

					if ( !is_null( $searchString ) && $viewMode == QN_GRID_VIEW )
						$visibleColumns = array_merge( array( QN_COLUMN_SUBJECT, QN_COLUMN_FOLDER ), $visibleColumns );
					else
						$visibleColumns = array_merge( array( QN_COLUMN_SUBJECT ), $visibleColumns );

					$columnKeys = array();
					foreach ( $visibleColumns as $key=>$columnName )
						$columnKeys[$columnName] = 1;

					$numVisibleColumns = count( $visibleColumns );
					if ( count($notes) )
						$numVisibleColumns++;

					// Check if folder is shared and format access rights page URL
					//
					if ( !$statisticsMode ) {
						$folderIsShared = $qn_treeClass->folderIsShared( $curQNF_ID, $currentUser, $kernelStrings );
						$accessRightsURL = prepareURLStr( PAGE_QN_ACCESSRIGHTS, array( 'QNF_ID'=>base64_encode($curQNF_ID) ) );
					}

					// Read initial folder tree panel width
					//
					if ( isset($_COOKIE['splitterView'.$QN_APP_ID.$currentUser]) )
						$treePanelWidth = (int)$_COOKIE['splitterView'.$QN_APP_ID.$currentUser];
					else
						$treePanelWidth = 200;
					$treePanelHide = (@$_COOKIE['splitterVisible'.$QN_APP_ID.$currentUser] == "false");
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QN_APP_ID );

	if ($searchString != "")
		$preproc->assign( PAGE_TITLE, $qnStrings['qn_sreen_searchresult_title'] );
	else
		$preproc->assign( PAGE_TITLE, $qnStrings['qn_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_QN_QUICKNOTES );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "qnStrings", $qnStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "tree_access_mode_long_names", $tree_access_mode_long_names );
	$preproc->assign( "genericLinkUnsorted", prepareURLStr( PAGE_QN_QUICKNOTES, array('searchString'=>$searchString) ) );

	if ( !$fatalError ) {
		$preproc->assign( "folders", $folders );
		$preproc->assign( "hierarchy", $hierarchy );
		$preproc->assign( "collapsedFolders", $collapsedFolders );
		$preproc->assign( "currentFolder", $curQNF_ID );

		if ( isset($searchString) )
			$preproc->assign( "searchString", $searchString );
		if ( isset($lastSearchString) )
			$preproc->assign( "lastSearchString", $lastSearchString );

		$preproc->assign( "noAccessGranted", $noAccessGranted );
		if ( !$noAccessGranted ) {
			$preproc->assign( "curFolderName", $folderData->QNF_NAME );
			$preproc->assign( "folderUsers", $folderUsers );

			foreach( $notes as $key=>$data )
				$notes[$key] = (array)$data;

			$preproc->assign( "notes", $notes );
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
			$preproc->assign( "toolsMenu", $toolsMenu);
			$preproc->assign( "canTools", $canTools);

			$preproc->assign( "viewMode", $viewMode );
			$preproc->assign( "visibleColumns", $visibleColumns );
			$preproc->assign( "numVisibleColumns", $numVisibleColumns );
			$preproc->assign( "qn_columnNames", $qn_columnNames );
			$preproc->assign( "columnKeys", $columnKeys );
			$preproc->assign( "contentLimit", $contentLimit );

			$preproc->assign( "numNotes", count($notes) );
			$preproc->assign( "treePanelWidth", $treePanelWidth );
			$preproc->assign( "treePanelHide", $treePanelHide );

			$preproc->assign( "closeFoldersLink", $closeFoldersLink );
			$preproc->assign( "hideLeftPanel", $hideLeftPanel );
			$preproc->assign( "showFolderSelector", $showFolderSelector );
			$preproc->assign( "showSharedPanel", $showSharedPanel );

			$preproc->assign( "availableFolders", TREE_AVAILABLE_FOLDERS );
			$preproc->assign( "statisticsMode", $statisticsMode );
			$preproc->assign( "tree_access_mode_names", $tree_access_mode_names );
			$preproc->assign( "tree_access_mode_long_names", $tree_access_mode_long_names );

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
		require_once( "../../../common/html/includes/ajax.php" );
		$ajaxRes = array ();
		$ajaxRes["toolbar"] = simple_ajax_get_toolbar ("qn_toolbar.htm", $preproc);
		$ajaxRes["rightContent"] = $preproc->fetch( "qn_rightpanel.htm" );
		print simple_ajax_encode($ajaxRes);
		exit;
	}
	$preproc->display( "quicknotes_resizable.htm" );
?>
