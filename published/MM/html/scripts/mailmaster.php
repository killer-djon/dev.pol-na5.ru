<?php

	require_once '../../../common/html/includes/httpinit.php';
	require_once WBS_DIR . 'published/MM/mm.php';

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "MM";

	$processButtonTemplate = "javascript:processTextButton('%s', 'form')";

	define( 'EXPAND', 'expand' );
	define( 'COLLAPSE', 'collapse' );
	define( 'HIDE_FOLDER', 'hidefolders' );
	define( 'ACTION_DELETEFOLDER', 'DELETE' );
	define( 'ACTION_SHOWALLUSERS', 'SHOWALLUSERS' );
	define( 'ACTION_SHOWALLGROUPS', 'SHOWALLGROUPS' );

	pageUserAuthorization( $SCR_ID, $MM_APP_ID, false );

	$showAccountError = null;

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$mmStrings = $mm_loc_str[$language];
	$invalidField = null;
	$noAccessGranted = false;
	$folderChanged = false;
	$diskQuotaWarning = false;

	if ( !isset($searchString) )
		$lastSearchString = base64_decode(getAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_SEARCHSTRING', null, $readOnly ));
	else
		$lastSearchString = $searchString;

	if ( $searchString == "" )
		$searchString = null;

	if ( !isset( $prevSearchString ) )
		$prevSearchString = null;

	if ($searchString)
		setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_SEARCHSTRING', base64_encode($searchString), $kernelStrings, $readOnly );

	$replyMenu[$mmStrings['app_reply_to_sender_label']] = "javascript: getSendForm_2('reply')";
	$replyMenu[$mmStrings['app_reply_to_all_label']] = "javascript: getSendForm_2('reply_all')";

	$accounts = mm_getAccounts( $currentUser );
	if (PEAR::isError($accounts))
	{
	  $errorStr = $accounts->getMessage();
	  $fatalError= true;
	}
	elseif($accounts)
	{
		foreach($accounts as $acc)
		{
			if($acc['MMA_INTERNAL'])
				$acc['MMA_EMAIL'] .= '@'.$acc['MMA_DOMAIN'];
			$acc_out[$acc['MMA_EMAIL']] = $acc;
			$acc_keys[] = $acc['MMA_EMAIL'];
		}
		unset($accounts);
		sort($acc_keys);
		foreach($acc_keys as $key)
			$accounts[$key] = $acc_out[$key];
	}
	$checkMailInterval = 60;
	$connectLimit = 60;

	$sendingMessages = '';

	$folders = $mm_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
							$access, $hierarchy, $deletable, null,
							null, false, null, true, null, $curMMF_ID == TREE_AVAILABLE_FOLDERS );

	if(empty($curMMF_ID) && $action != 'virtualBox')
	{
		$inboxMode = getAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_INBOXMODE', null, $readOnly );
		if(empty($mailbox))
			$currentAccount = $mailbox = getAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_MAILBOX', null, $readOnly );
		else
			$currentAccount = $mailbox;
	}
	else
	{
		$inboxMode = $currentAccount = $mailbox = false;
		setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_INBOXMODE', $inboxMode, $kernelStrings, $readOnly );
		setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_MAILBOX', $mailbox, $kernelStrings, $readOnly );
	}

	if ( isset($action) )
		switch ( $action )
		{
			case ACTION_DELETEFOLDER :
					$targetMMF_ID = base64_decode($MMF_ID);

					$params = array();
					$params['U_ID'] = $currentUser;
					$params['kernelStrings'] = $kernelStrings;

					$res = $mm_treeClass->deleteFolder( $targetMMF_ID, $currentUser, $kernelStrings, false, "mm_onDeleteFolder", $params );
					if ( PEAR::isError($res) )
						$errorStr = $res->getMessage();

					break;

			case 'commonList':
					$currentAccount = $mailbox = false;
					$inboxMode = true;
					$statisticsMode = true;
					setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_MAILBOX', $mailbox, $kernelStrings, $readOnly );
					setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_INBOXMODE', $inboxMode, $kernelStrings, $readOnly );
					setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );
					$box = '';
					setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_VIRTUALFOLDER', $box, $kernelStrings, $readOnly );

					break;

			case 'msgList':
				if($accounts[$mailbox]['MMA_INTERNAL'])
				{
					$res = MailCommonDB::getDiskUsage(array('MMA_OWNER' => $DB_KEY, 'MMA_EMAIL' => $mailbox));
					if($res > 90)
						$diskQuotaWarning = true;
				}
			case 'msgRefresh':
					$currentAccount = $mailbox;
					$inboxMode = true;
					$statisticsMode = false;
					setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_MAILBOX', $mailbox, $kernelStrings, $readOnly );
					setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_INBOXMODE', $inboxMode, $kernelStrings, $readOnly );
					setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );
					$box = '';
					setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_VIRTUALFOLDER', $box, $kernelStrings, $readOnly );
					break;
		}

	// Determine active folder
	//

	if ( !isset( $curMMF_ID ) )
	{
		if(!$box)
			$box = getAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_VIRTUALFOLDER', null, $readOnly );

		if($action == 'virtualBox' || $box)
		{
			$curMMF_ID = 0;
			setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_VIRTUALFOLDER', $box, $kernelStrings, $readOnly );
			$mm_treeClass->setUserDefaultFolder( $currentUser, $curMMF_ID, $kernelStrings, $readOnly );

			$statisticsMode = false;
			setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );
		}
		else
		{
			$curMMF_ID = $mm_treeClass->getUserDefaultFolder( $currentUser, $kernelStrings, $readOnly );

			if ( ( $curMMF_ID == TREE_AVAILABLE_FOLDERS ) && !$currentAccount && !$box)
			{
				$statisticsMode = true;
				setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );
			}
		}
	}
	else
	{
		$box = '';
		setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_VIRTUALFOLDER', $box, $kernelStrings, $readOnly );
		$curMMF_ID = base64_decode($curMMF_ID);

		if ( ( $curMMF_ID != TREE_AVAILABLE_FOLDERS ) && !$currentAccount )
		{
			if ( $mm_treeClass->getIdentityFolderRights( $currentUser, $curMMF_ID, $kernelStrings ) == TREE_NOACCESS )
				$curMMF_ID = $mm_treeClass->getUserDefaultFolder( $currentUser, $kernelStrings, $readOnly );

			$mm_treeClass->setUserDefaultFolder( $currentUser, $curMMF_ID, $kernelStrings, $readOnly );
			$folderChanged = true;

			$statisticsMode = false;
			setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );

			if ( $curMMF_ID != TREE_AVAILABLE_FOLDERS )
				$mm_treeClass->expandPathToFolder( $curMMF_ID, $currentUser, $kernelStrings );
		} else {
			$statisticsMode = true;
			setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );

			$mm_treeClass->setUserDefaultFolder( $currentUser, $curMMF_ID, $kernelStrings, $readOnly );
		}
	}

	if ( isset($action) )
		switch ( $action )
		{
			case EXPAND :
			case COLLAPSE :
							$decodedID = base64_decode($MMF_ID);
							$mm_treeClass->setFolderCollapseValue( $currentUser, $decodedID, $action == COLLAPSE, $kernelStrings );

							if ( $action == COLLAPSE )
							{
								if ( $decodedID != TREE_AVAILABLE_FOLDERS )
								{
									if ( $mm_treeClass->isChildOf( $curMMF_ID, $decodedID, $kernelStrings ) )
										$curMMF_ID = $decodedID;
								}
								else
									$curMMF_ID = $decodedID;

								$mm_treeClass->setUserDefaultFolder( $currentUser, $curMMF_ID, $kernelStrings, $readOnly );
								$curMMF_ID = $mm_treeClass->getUserDefaultFolder( $currentUser, $kernelStrings, $readOnly );

								if ( ( $curMMF_ID == TREE_AVAILABLE_FOLDERS ) && !$currentAccount )
								{
									$statisticsMode = true;
									setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );

									$mm_treeClass->setUserDefaultFolder( $currentUser, $curMMF_ID, $kernelStrings, $readOnly );
								}
							}

							break;
			case HIDE_FOLDER :
							$foldersHidden = true;
							setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_FOLDERSHIDDEN', $foldersHidden, $kernelStrings, $readOnly );

							break;
		}

	$statisticsMode = getAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_STATISTICSMODE', null, $readOnly );

	if ( $prevSearchString != $searchString )
		$folderChanged = true;

	$btnIndex = getButtonIndex( array(
		'adddocbtn', 'copyfolderbtn', 'movefolderbtn', 'deletebtn', 'copybtn', 'movebtn',
		'foldersbtn', 'showFoldersBtn', 'viewbtn', 'setgridmodeview', 'setlistmodeview',
		'printbtn', 'sendbtn', 'fromtpl', 'addtplbtn', 'sendtestbtn', 'delbtn'
		), $_POST );

	$commonRedirParams = array();
	$commonRedirParams[OPENER] = base64_encode(PAGE_MM_MAILMASTER);

	$virtualFoldersTrans = array(
		'draftBox'=>$mmStrings['app_folder_draft_name'],
		'pendingBox'=>$mmStrings['app_folder_pending_name'],
		'sentBox'=>$mmStrings['app_folder_sent_name'],
		'templateBox'=>$mmStrings['app_folder_template_name']
	);


	switch ($btnIndex)
	{
		case 14 :
				$commonRedirParams['MMM_STATUS'] = MM_STATUS_TEMPLATE;

		case 0 :
				$commonRedirParams['MMF_ID'] = base64_encode($curMMF_ID);
				$commonRedirParams[ACTION] = ACTION_NEW;
				redirectBrowser( PAGE_MM_ADDMODMESSAGE, $commonRedirParams );
		case 1 :
				$commonRedirParams['operation'] = TREE_COPYFOLDER;
				$commonRedirParams['MMF_ID'] = base64_encode($curMMF_ID);
				redirectBrowser( PAGE_MM_COPYMOVE, $commonRedirParams );
		case 2 :
				$commonRedirParams['operation'] = TREE_MOVEFOLDER;
				$commonRedirParams['MMF_ID'] = base64_encode($curMMF_ID);
				redirectBrowser( PAGE_MM_COPYMOVE, $commonRedirParams );

		case 3 :

			if ( !isset($document) )
					break;

				$res = mm_deleteMessages( array_keys($document), $currentUser, $kernelStrings, $mmStrings );
				if ( PEAR::isError($res) )
					$errorStr = $res->getMessage();

				break;

		case 16 :

				$currentMID = array(str_replace('~', "\t", $currentMID));

				$res = mm_deleteMessages( $currentMID, $currentUser, $kernelStrings, $mmStrings );
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
					$commonRedirParams['MMF_ID'] = base64_encode($curMMF_ID);
				else
					$commonRedirParams['MMF_ID'] = null;

				redirectBrowser( PAGE_MM_COPYMOVE, $commonRedirParams );
		case 5 :

				if ( !isset($document) )
					break;

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$commonRedirParams['doclist'] = $documentList;
				$commonRedirParams['operation'] = TREE_MOVEDOC;

				if ( !strlen($searchString) )
					$commonRedirParams['MMF_ID'] = base64_encode($curMMF_ID);
				else
					$commonRedirParams['MMF_ID'] = null;

				$commonRedirParams['inboxMode'] = $inboxMode;
				$commonRedirParams['virtualFolder'] = $virtualFoldersTrans[$box];
				redirectBrowser( PAGE_MM_COPYMOVE, $commonRedirParams );
		case 6 :
				$searchString = null;
				setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_SEARCHSTRING', base64_encode($searchString), $kernelStrings, $readOnly );

				break;
		case 7 :
				$foldersHidden = false;
				setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_FOLDERSHIDDEN', $foldersHidden, $kernelStrings, $readOnly );

				break;
		case 8 :
				$commonRedirParams["searchString"] = $searchString;
				redirectBrowser( PAGE_MM_VIEW, $commonRedirParams );
		case 9 :
				mm_setViewOptions( $currentUser, null, MM_GRID_VIEW, null, null, null, $kernelStrings, $readOnly );
				break;
		case 10 :
				mm_setViewOptions( $currentUser, null, MM_LIST_VIEW, null, null, null, $kernelStrings, $readOnly );
				break;
		case 11 :
				if ( !isset($document) )
					$document = array();

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$commonRedirParams['doclist'] = $documentList;
				$commonRedirParams['MMF_ID'] = base64_encode($curMMF_ID);

				redirectBrowser( PAGE_MM_PRINT, $commonRedirParams );
		case 12:
				if ( !isset($document) )
					break;

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$commonRedirParams['doclist'] = $documentList;
				$commonRedirParams['MMF_ID'] = base64_encode($curMMF_ID);

				redirectBrowser( PAGE_MM_SEND, $commonRedirParams );

		case 15:
				if ( !isset($document) )
					break;

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$commonRedirParams['doclist'] = $documentList;
				$commonRedirParams['MMF_ID'] = base64_encode($curMMF_ID);

				redirectBrowser( PAGE_MM_TESTSEND, $commonRedirParams );

		case 13:
				$commonRedirParams['MMF_ID'] = base64_encode($curMMF_ID);
				redirectBrowser( PAGE_MM_FROMTPL, $commonRedirParams );

				break;

	}


	$msgTotal = 0;
	$newTotal = 0;
	$cache = new mm_cache();
	$acc_info = $cache->getAccountsInfo( $currentUser, $kernelStrings );
	if (PEAR::isError($acc_info))
	{
		$errorStr = $acc_info->getMessage();
		$acc_info = array();
		$fatalError= true;
	}
	$accountsInfo = array();
	if (!$acc_keys) {
		$acc_keys = array();
	}
	foreach($acc_keys as $key)
	{
		if(empty($acc_info[$key]))
			$accountsInfo[$key] = array('count'=>0, 'size'=>0, 'new'=>0);
		else
		{
			$accountsInfo[$key] = $acc_info[$key];
			$msgTotal += $acc_info[$key]['count'];
			$newTotal += $acc_info[$key]['new'];
		}

		$accountsInfo[$key]['id'] = $accounts[$key]['MMA_ID'];
		$accountsInfo[$key]['internal'] = $accounts[$key]['MMA_INTERNAL'];
		$accountsInfo[$key]['name'] = $accounts[$key]['MMA_NAME'];
	}

	$foldersHidden = getAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_FOLDERSHIDDEN', null, $readOnly );

	switch (true)
	{
		case true :

					// Load folder list
					//
					$access = null;
					$hierarchy = null;
					$deletable = null;
					$folders = $mm_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
															$access, $hierarchy, $deletable, null,
															null, false, null, true, null, $curMMF_ID == TREE_AVAILABLE_FOLDERS );
					if ( PEAR::isError($folders) )
					{
						$fatalError = true;
						$errorStr = $folders->getMessage();

						break;
					}

					// Prepare folder list to display
					//
					$collapsedFolders = $mm_treeClass->listCollapsedFolders( $currentUser );

					foreach ( $folders as $MMF_ID=>$folderData )
					{
						$encodedID = base64_encode($MMF_ID);
						$folderData->curMMF_ID = $encodedID;
						$folderData->curID = $encodedID;

						if ( $folderData->TYPE != TREE_AVAILABLE_FOLDERS )
						{
							if ( $folderData->TREE_ACCESS_RIGHTS != TREE_NOACCESS )
							{
								$params = array();
								$params['curMMF_ID'] = $encodedID;

								$folderData->ROW_URL = prepareURLStr( PAGE_MM_MAILMASTER, $params );
							}
						} else {
							$params = array();
							$params = array();
							$params['curMMF_ID'] = $encodedID;

							$folderData->ROW_URL = prepareURLStr( PAGE_MM_MAILMASTER, $params );
						}

						$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);

						$collapseParams = array();
						$collapseParams['MMF_ID'] = $encodedID;
						if ( isset($collapsedFolders[$MMF_ID]) )
							$collapseParams['action'] = EXPAND;
						else
							$collapseParams['action'] = COLLAPSE;

						$folderData->COLLAPSE_URL = prepareURLStr( PAGE_MM_MAILMASTER, $collapseParams );

						if ( $statisticsMode )
							$folderData->SHARED = $mm_treeClass->folderIsShared( $MMF_ID, $currentUser, $kernelStrings );

						$folders[$MMF_ID] = $folderData;
					}

					// Load current folder data
					//
					$folderData = $folders[$curMMF_ID];

					// Load catalog documents
					//
					$visibleColumns = null;
					$viewMode = null;
					$recordsPerPage = null;
					$showSharedPanel = null;
					$contentLimit = null;
					mm_getViewOptions( $currentUser, $visibleColumns, $viewMode, $recordsPerPage, $showSharedPanel, $contentLimit, $kernelStrings, $readOnly );
					$recordsPerPage = MESSAGES_PER_PAGE;


					if( $inboxMode )
					{
						$visibleColumns = array('MMM_PRIORITY','MMM_FROM','MMM_SUBJECT','MMM_DATETIME','ATTACHEDFILES','MMM_SIZE');
						$mm_columnNames['MMM_DATETIME'] = 'app_received_field';
					}
					elseif( !$curMMF_ID && $box == 'draftBox' )
					{
						$visibleColumns = array('MMM_PRIORITY','MMM_SUBJECT','MMM_DATETIME','ATTACHEDFILES','MMM_SIZE');
						$mm_columnNames['MMM_DATETIME'] = 'app_created_field';
					}
					elseif( !$curMMF_ID && $box == 'templateBox' )
						$visibleColumns = array('MMM_PRIORITY','MMM_SUBJECT','MMM_DATETIME','ATTACHEDFILES','MMM_SIZE');
					elseif( !$curMMF_ID && $box=='pendingBox' )
					{
						$visibleColumns = array('MMM_PRIORITY','MMM_TO','MMM_SUBJECT','MMM_DATETIME','ATTACHEDFILES','MMM_SIZE');
						$mm_columnNames['MMM_DATETIME'] = 'app_tosend_field';
					}
					elseif( !$curMMF_ID && $box=='sentBox' )
					{
						$visibleColumns = array('MMM_PRIORITY','MMM_TO','MMM_SUBJECT','MMM_DATETIME','ATTACHEDFILES','MMM_SIZE');
						$mm_columnNames['MMM_DATETIME'] = 'app_sent_field';
					}
					else
						$visibleColumns = array('MMM_PRIORITY','MMM_FROM','MMM_TO','MMM_SUBJECT','MMM_STATUS','MMM_DATETIME','ATTACHEDFILES','MMM_SIZE');


					if($currentAccount)
						$srt = 'INBOX_'.$currentAccount;
					elseif($box)
						$srt = 'VIRTUAL_'.$box;
					elseif($curMMF_ID)
						$srt = 'FOLDER_'.$curMMF_ID;
					else
						$srt = 'DEFAULT';

					if( empty($sorting) )
					{
						$sorting = getAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_SORTING_'.$srt, null, $readOnly );
						if( !strlen($sorting) )
							$sorting = "MMM_DATETIME desc";
						else
							$sorting = base64_decode( $sorting );
						$sortData = parseSortStr( $sorting );
						if( !in_array($sortData['field'], $visibleColumns) )
							$sorting = "MMM_DATETIME desc";
					} else {
						setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_SORTING_'.$srt, $sorting, $kernelStrings, $readOnly );
						$sorting = base64_decode( $sorting );
					}

					// Add pages support
					//
					if( !$inboxMode )
					{
						if( $box && !$curMMF_ID )
							$docCount = virtualFolderDocumentCount( $box, $currentUser, $kernelStrings );
						else
							$docCount = $mm_treeClass->folderDocumentCount( $curMMF_ID, $currentUser, $kernelStrings );
					}
					else
					{
						$docCount = $cache->getHeadersCountFromCache( $accounts[$mailbox]['MMA_EMAIL'] );
						if( PEAR::isError( $docCount ) )
						{
						  $fatalError= true;
							$errorStr = $docCount->getMessage();
						}
					}

					if( !isset($currentPage) || !strlen($currentPage) )
						$currentPage = 1;

					$showPageSelector = false;
					$pages = null;
					$pageCount = 0;
					$startIndex = 0;
					$count = 0;
					getQueryLimitValues( $docCount, $recordsPerPage, $showPageSelector, $currentPage, $pages, $pageCount, $startIndex, $count );
					$recordsPerPage = MESSAGES_PER_PAGE;

					$totalDocNum = $docCount;

					if( !$inboxMode )
					{
						if( $box && !$curMMF_ID )
							$notes = mm_listVirtualFolder( $box, $currentUser, $sorting, $kernelStrings, $startIndex, $count );
						else //if ( is_null( $searchString ) )
							$notes = $mm_treeClass->listFolderDocuments( $curMMF_ID, $currentUser, $sorting, $kernelStrings, null, false, $startIndex, $count, $folderData->TREE_ACCESS_RIGHTS );

						if (PEAR::isError($notes))
						{
							$errorStr = $notes->getMessage ();
							$notes = array();
							//$fatalError = true;
							break;
						}

						foreach( $notes as $key=>$value )
						{
							if(!$notes[$key]->TREE_ACCESS_RIGHTS)
							{
								$fid = $notes[$key]->MMF_ID;
								if(!empty($folders[$fid]))
									$notes[$key]->TREE_ACCESS_RIGHTS = $folders[$fid]->USER_RIGHTS;
								else
									$notes[$key]->TREE_ACCESS_RIGHTS = 7;
							}
							$notes[$key] = mm_processFileListEntry( $value );

							$notes[$key]->pendingError = false;
							if( $notes[$key]->MMM_STATUS == MM_STATUS_PENDING )
							{
								$str = $notes[$key]->MMM_DATETIME;
								global $dateDelimiters;
								$delimiter = $dateDelimiters[DATE_DISPLAY_FORMAT];
								if( validateGeneralDateStr( $str, $month, $day, $year, DATE_DISPLAY_FORMAT, $delimiter ) )
								{
									$ts = strtotime( sprintf( "%s-%s-%s %s", $year, $month, $day, substr( $str, -5 ) ) );
									if( ( $ts + 3600 ) < time() )
										$notes[$key]->pendingError = true;
								}
							}

						}

						$sendingMessages = array();
						if(($sending = mm_getSendingMessages()) && is_array($sending))
							foreach($sending as $id=>$stat)
								$sendingMessages[] = "'$id':'$stat'";
							$sendingMessages = join(',', $sendingMessages);
					}
					elseif(!$statisticsMode)
					{
						$notes = $cache->loadHeadersFromCache( $accounts[$mailbox]['MMA_EMAIL'], $sorting, $startIndex, $count );
						if ( PEAR::isError( $notes) )
						{
							$errorStr = $notes->getMessage();
							$notes = array();
							//$fatalError= true;

							$showAccountError = $currentAccount;
							$currentAccount = $mailbox = false;
							$inboxMode = true;
							$statisticsMode = true;
							setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_MAILBOX', $mailbox, $kernelStrings, $readOnly );
							setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_INBOXMODE', $inboxMode, $kernelStrings, $readOnly );
							setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );
						}
					}

					setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_CURRENTPAGE', $currentPage, $kernelStrings, $readOnly );

					foreach( $pages as $key => $value )
					{
						$params = array();
						$params[PAGES_CURRENT] = $value;
						$params[SORTING_COL] = base64_encode($sorting);
						$params['searchString'] = $searchString;

						$URL = prepareURLStr( PAGE_MM_MAILMASTER, $params );
						$pages[$key] = array( $value, $URL );
					}

					// Post-process note entries
					//
					foreach( $notes as $MMM_ID=>$data )
					{
						$params = array();
						$params[PAGES_CURRENT] = $currentPage;
						$params['searchString'] = $searchString;
						$params['MMM_ID'] = base64_encode($MMM_ID);

						$data->DESC_URL = prepareURLStr( PAGE_MM_ADDMODMESSAGE, $params );

						if ( $searchString != "" )
						{
							$params = array();
							$params['curMMF_ID'] = base64_encode( $data->MMF_ID );
							$params['searchString'] = null;

							$data->FOLDER_URL = prepareURLStr( PAGE_MM_MAILMASTER, $params );
						}
						$data->MMM_FROM = mm_getNameFromAddress( $data->MMM_FROM );
						$data->MMM_TO = mm_getNameFromAddress( $data->MMM_TO );
						if( !$data->MMM_TO )
						{
							if( PEAR::isError( $lists = mm_getLists( $U_ID, $kernelStrings, $mmStrings ) ) )
								$errorStr = $res->getMessage();
							else
							{
								$arr = explode( ',', $data->MMM_LISTS );
								$data->MMM_TO = $lists['CM_OT_LISTS'.base64_encode($arr[0])]['NAME'];
								if( $arr[1] )
									$data->MMM_TO .= ', ...';
							}
						}
						$notes[$MMM_ID] = $data;
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

					$folderUsers = $mm_treeClass->listFolderUsers( $curMMF_ID, $kernelStrings, LFU_GROUPSANDUSERS,
																	$currentUser, $userlistLimit, $grouplistLimit );

					$showAllParams = array();
					$showAllParams[PAGES_CURRENT] = $currentPage;
					$showAllParams[SORTING_COL] = base64_encode($sorting);
					$showAllParams['searchString'] = $searchString;

					if ( $folderUsers[LFU_USERSLIMITED] )
					{
						$userLimitStr = sprintf( $kernelStrings['app_userlisttotal_text'], $folderUsers[LFU_USERSTOTAL] );

						$showAllParams[ACTION] = ACTION_SHOWALLUSERS;
						$userShowAllLink = prepareURLStr( PAGE_MM_MAILMASTER, $showAllParams );
					}

					if ( $folderUsers[LFU_GROUPSLIMITED] )
					{
						$groupLimitStr = sprintf( $kernelStrings['app_grouplisttotal_text'], $folderUsers[LFU_GROUPSTOTAL] );

						$showAllParams[ACTION] = ACTION_SHOWALLGROUPS;
						$groupShowAllLink = prepareURLStr( PAGE_MM_MAILMASTER, $showAllParams );
					}

					// Prepare menus
					//
					$allowCopyFolder =  ( $folderData->TREE_ACCESS_RIGHTS > TREE_NOACCESS );
					$folderMenu = array();
					$encodedID = base64_encode($curMMF_ID);

					if ( $statisticsMode )
					{
						$canCreateAnyFolders = $mm_treeClass->canCreateFolders( $currentUser, $kernelStrings );
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
						$params[OPENER] = base64_encode(PAGE_MM_MAILMASTER);

						if ( !$statisticsMode )
							$params['MMF_ID_PARENT'] = $encodedID;
						else
							$params['MMF_ID_PARENT'] = base64_encode( TREE_ROOT_FOLDER );

						//$addURL = prepareURLStr( PAGE_MM_ADDMODFOLDER, $params );
						$addURL = "#||createFolder()";

						$folderMenu[$mmStrings['app_treeaddfolder_title']] = $addURL;
						$folderMenu[] = "-";
					} else {
						if ( !$statisticsMode )
							$folderMenu[$mmStrings['app_treeaddfolder_title']] = null;
						else
							$folderMenu[$mmStrings['app_treeaddfolder_title']] = "#||alertAddRoot()";
						$folderMenu[] = "-";
					}

					//$folderMenuCopyItem =  ( $allowCopyFolder ) ? sprintf( $processButtonTemplate, 'copyfolderbtn' ) : $folderMenu[$kernelStrings['app_treecopyfld_text']] = "#||alertCopy()";

					if ( !$statisticsMode )
					{
						if ( UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, TREE_READWRITEFOLDER ) )
						{
							$params = array();
							$params[ACTION] = ACTION_EDIT;
							$params[OPENER] = base64_encode(PAGE_MM_MAILMASTER);
							$params['MMF_ID'] = $encodedID;
							$params['MMF_ID_PARENT'] = base64_encode($folderData->MMF_ID_PARENT);

							$modifyURL = prepareURLStr( PAGE_MM_ADDMODFOLDER, $params );

							$folderMenu[$mmStrings['app_treemodfolder_title']] = $modifyURL;
							$folderMenu[] = "-";
							
							$folderMenu[$mmStrings['app_treerenamefolder_title']] = "#||renameFolder()";

							//$folderMenu[$kernelStrings['app_treecopyfld_text']] = $folderMenuCopyItem;
							if ( $folderData->ALLOW_MOVE )
								$folderMenu[$mmStrings['app_treemovefld_text']] = sprintf( $processButtonTemplate, 'movefolderbtn' );
							else
								$folderMenu[$mmStrings['app_treemovefld_text']] = "#||alertMove()";

							if ( $folderData->ALLOW_DELETE )
							{
								$params = array();
								$params[ACTION] = ACTION_DELETEFOLDER;
								$params['MMF_ID'] = $encodedID;

								//$deleteURL = prepareURLStr( PAGE_MM_MAILMASTER, $params );
								//$deleteURL .= "||confirmFolderDeletion()";
								$deleteURL = "#||deleteCurrentFolder()";

								$folderMenu[$mmStrings['app_treedelfolder_text']] = $deleteURL;
							} else
								$folderMenu[$mmStrings['app_treedelfolder_text']] = "#||alertDelete()";
						} else {
							$folderMenu[$mmStrings['app_treemodfolder_title']] = null;
							$folderMenu[] = "-";
							$folderMenu[$mmStrings['app_treerenamefolder_title']] = null;
							//$folderMenu[$kernelStrings['app_treecopyfld_text']] = $folderMenuCopyItem;
							$folderMenu[$mmStrings['app_treemovefld_text']] = null;
							$folderMenu[$mmStrings['app_treedelfolder_text']] = null;
						}
					}
					else
					{
						$folderMenu[$mmStrings['app_treemodfolder_title']] = null;
						$folderMenu[] = "-";
						//$folderMenu[$kernelStrings['app_treecopyfld_text']] = null;
						$folderMenu[$mmStrings['app_treemovefld_text']] = null;
						$folderMenu[$mmStrings['app_treedelfolder_text']] = null;
					}

					if (!checkUserAccessRights( $currentUser, "UNG", "UG", false))
						unset($folderMenu[$mmStrings['app_treemodfolder_title']]);

					if ( $inboxMode || !$curMMF_ID )
					{
						$folderMenu = array();
						$folderMenu[$mmStrings['app_treeaddfolder_title']] = null;
						$folderMenu[] = "-";
						$folderMenu[$mmStrings['app_treemodfolder_title']] = null;
						$folderMenu[] = "-";
						$folderMenu[$mmStrings['app_treerenamefolder_title']] = null;
						//$folderMenu[$kernelStrings['app_treecopyfld_text']] = null;
						$folderMenu[$mmStrings['app_treemovefld_text']] = null;
						$folderMenu[$mmStrings['app_treedelfolder_text']] = null;
					}

					$fileMenu = array();



					if ( ( ( UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, array( TREE_WRITEREAD, TREE_READWRITEFOLDER ) ) && is_null( $searchString ) && !$statisticsMode )
						|| !$curMMF_ID || ( $curMMF_ID == TREE_AVAILABLE_FOLDERS && !$inboxMode)  || $inboxMode ) )
					{
						$fileMenu[$mmStrings['mm_screen_addnote_menu']] = "javascript:getSendForm_2(0)";
						$fileMenu[$mmStrings['mm_screen_composetpl_menu']] = "javascript:getSelectTemplatePage()";
						$fileMenu[] = '-';
						$fileMenu[$mmStrings['mm_screen_addtpl_menu']] = "javascript:getSendForm_2('template')";
						$fileMenu[] = '-';
					} else {
						$fileMenu[$mmStrings['mm_screen_addnote_menu']] = null;
						$fileMenu[$mmStrings['mm_screen_composetpl_menu']] = null;
						$fileMenu[] = '-';
						$fileMenu[$mmStrings['mm_screen_addtpl_menu']] = null;
						$fileMenu[] = '-';
					}
					if ( ( UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, array( TREE_WRITEREAD, TREE_READWRITEFOLDER ) ) && !$statisticsMode && is_null($searchString) &&  $curMMF_ID ) || $box )
					{
						$fileMenu[$mmStrings['mm_screen_movenote_menu']] = sprintf( $processButtonTemplate, 'movebtn' )."||confirmMove()";
						$fileMenu[$mmStrings['mm_screen_deletenote_menu']] = "javascript:processTextButton_2('deletebtn', 'form')||confirmDeletion()";
					}
					else
					{
						$fileMenu[$mmStrings['mm_screen_movenote_menu']] = null;
						$fileMenu[$mmStrings['mm_screen_deletenote_menu']] = null;
					}

					// View menu
					//
					$viewMenu = array();

					$viewMenu[$mmStrings['mm_screen_custview_menu']] = sprintf( $processButtonTemplate, 'viewbtn' );

					// Other appearance settings
					//
					$params = array();
					$params[ACTION] = HIDE_FOLDER;
					$closeFoldersLink = prepareURLStr( PAGE_MM_MAILMASTER, $params );

					$hideLeftPanel = $foldersHidden || !is_null( $searchString );
					$hideLeftPanel = false;
					$showFolderSelector = $foldersHidden && is_null( $searchString );

					if ( $statisticsMode )
					{
						$curMMF_ID = TREE_AVAILABLE_FOLDERS;

						$folderNum = 0;
						$documentNum = 0;
						$summaryData = $mm_treeClass->getSummaryStatistics( $currentUser, $folderNum, $documentNum, $kernelStrings );
						if ( PEAR::isError($summaryData) )
						{
							$fatalError = true;
							$errorStr = $summaryData->getMessage();

							break;
						}

						if($inboxMode)
							$summaryStr = sprintf( $mmStrings['mm_screen_inbox_summary_note'], $msgTotal, count($accountsInfo) );
						else
							$summaryStr = sprintf( $mmStrings['mm_screen_summary_note'], $documentNum, $folderNum );
					}

					if ( !is_null( $searchString ) && $viewMode == MM_GRID_VIEW )
						$visibleColumns = array_merge( array( MM_COLUMN_FOLDER ), $visibleColumns );

					$columnKeys = array();
					foreach ( $visibleColumns as $key=>$columnName )
						$columnKeys[$columnName] = 1;

					$numVisibleColumns = count( $visibleColumns );
					if ( count($notes) )
						$numVisibleColumns++;

					// Check if folder is shared and format access rights page URL
					//

					if ( !$statisticsMode )
					{
						$folderIsShared = $mm_treeClass->folderIsShared( $curMMF_ID, $currentUser, $kernelStrings );
						$accessRightsURL = prepareURLStr( PAGE_MM_ACCESSRIGHTS, array( 'MMF_ID'=>base64_encode($curMMF_ID) ) );
					}

					// Read initial folder tree panel width
					//
					if ( isset($_COOKIE['splitterView'.$MM_APP_ID.$currentUser]) )
						$treePanelWidth = (int)$_COOKIE['splitterView'.$MM_APP_ID.$currentUser];
					else
						$treePanelWidth = 200;
					
					$treePanelHide = (@$_COOKIE['splitterVisible'.$MM_APP_ID.$currentUser] == "false");

	}

	$kernelStrings['app_treerightslegend_text'] = $mmStrings['app_treerightslegend_text'];

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $MM_APP_ID );

	if ($searchString != "")
		$preproc->assign( PAGE_TITLE, $mmStrings['mm_sreen_searchresult_title'] );
	else
		$preproc->assign( PAGE_TITLE, $mmStrings['mm_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_MM_MAILMASTER );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "mmStrings", $mmStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "tree_access_mode_long_names", $tree_access_mode_long_names );
	$preproc->assign( "genericLinkUnsorted", prepareURLStr( PAGE_MM_MAILMASTER, array('searchString'=>$searchString) ) );

	if ( !$fatalError )
	{
		$preproc->assign( "folders", $folders );
		$preproc->assign( "hierarchy", $hierarchy );
		$preproc->assign( "collapsedFolders", $collapsedFolders );
		$preproc->assign( "currentFolder", $curMMF_ID);
		$preproc->assign( "currentAccount", $currentAccount );

		if ( isset($searchString) )
			$preproc->assign( "searchString", $searchString );
		if ( isset($lastSearchString) )
			$preproc->assign( "lastSearchString", $lastSearchString );

		$preproc->assign( "noAccessGranted", $noAccessGranted );

		if ( !$noAccessGranted )
		{
			if($currentAccount)
				$curFolderName = $currentAccount;
			elseif($box)
				$curFolderName = $virtualFoldersTrans[$box];
			else
				$curFolderName = $folderData->MMF_NAME;

			$preproc->assign( "curFolderName", $curFolderName );

			$preproc->assign( "folderUsers", $folderUsers );

			foreach( $notes as $key=>$data )
				$notes[$key] = (array)$data;

			$preproc->assign( "notes", $notes );
			$preproc->assign( "numDocuments", $totalDocNum);
			$preproc->assign( "curFolderData", $folderData );
			$preproc->assign( "availableFolders", TREE_AVAILABLE_FOLDERS );

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
			$preproc->assign( "replyMenu", $replyMenu);
			$preproc->assign( "canTools", $canTools);

			$preproc->assign( "viewMode", $viewMode );
			$preproc->assign( "visibleColumns", $visibleColumns );
			$preproc->assign( "numVisibleColumns", $numVisibleColumns );
			$preproc->assign( "mm_columnNames", $mm_columnNames );
			$preproc->assign( "columnKeys", $columnKeys );
			$preproc->assign( "contentLimit", $contentLimit );

			$preproc->assign( "numNotes", count($notes) );
			$preproc->assign( "treePanelWidth", $treePanelWidth );
			$preproc->assign( "treePanelHide", $treePanelHide );

			$preproc->assign( "closeFoldersLink", $closeFoldersLink );
			$preproc->assign( "hideLeftPanel", $hideLeftPanel );
			$preproc->assign( "showFolderSelector", $showFolderSelector );
			$preproc->assign( "showSharedPanel", $showSharedPanel );

			$preproc->assign( "statisticsMode", $statisticsMode );
			$preproc->assign( "tree_access_mode_names", $tree_access_mode_names );
			$preproc->assign( "tree_access_mode_long_names", $tree_access_mode_long_names );

			$preproc->assign( "mm_statusStyle", $mm_statusStyle );

			if ( $statisticsMode )
				$preproc->assign( "summaryStr", $summaryStr );
			else
			{
				if ( is_null( $searchString ) )
					$preproc->assign( "thisFolderRights", $folderData->TREE_ACCESS_RIGHTS );

				$preproc->assign( "folderIsShared", $folderIsShared );
				$preproc->assign( "accessRightsURL", $accessRightsURL );
			}

			if ( isset($userShowAllLink) )
			{
				$preproc->assign( "userLimitStr", $userLimitStr );
				$preproc->assign( "userShowAllLink", $userShowAllLink );
			}

			if ( isset($groupShowAllLink) )
			{
				$preproc->assign( "groupLimitStr", $groupLimitStr );
				$preproc->assign( "groupShowAllLink", $groupShowAllLink );
			}
		}
		$preproc->assign( "accounts", $accounts );
		$preproc->assign( "accountsInfo", $accountsInfo );
		$preproc->assign( "newTotal", $newTotal );
		$preproc->assign( "checkMailInterval", $checkMailInterval );
		$preproc->assign( "connectLimit", $connectLimit );
		$preproc->assign( "inboxAccess", $mm_treeClass->getIdentityFolderRights( $currentUser, "INBOX", $kernelStrings ) );

		$preproc->assign( "virtualBox", $box );

		$preproc->assign( "diskQuotaWarning", $diskQuotaWarning );


		$preproc->assign( "showAccountError", $showAccountError );
		$preproc->assign( "accountPage", PAGE_MM_ADDMODACCOUNT );
/*
		$preproc->assign( "MM_RECEIVE_SIZE_LIMIT", MM_RECEIVE_SIZE_LIMIT );

		$preproc->assign( "MM_DISPLAY_SIZE_LIMIT",
			$mmStrings['mm_big_size_confirm_1'] . " "
			. ( MM_RECEIVE_SIZE_LIMIT / 1000000 ) . " "
			. $mmStrings['mm_big_size_confirm_2'] );
*/
/*
		if(onWebasystServer())
		{
			$viewFormURL = '/MM/2.0/viewform.php';
			$preproc->assign( 'searchPageURL', '/MM/2.0/search.php' );
			$preproc->assign( 'selectTemplateURL', '/MM/2.0/selecttemplate.php' );
		}
		else
		{
*/
		$viewFormURL = '../../2.0/viewform.php';
		$preproc->assign( 'searchPageURL', '../../2.0/search.php' );
		$preproc->assign( 'selectTemplateURL', '../../2.0/selecttemplate.php' );

		$mainPageURL = $_SERVER['REQUEST_URI'];

		$preproc->assign( "mainPageURL", $mainPageURL );
		$preproc->assign( "viewFormURL", $viewFormURL );

		if(onWebasystServer())
			$preproc->assign( "accountsMenuURL", prepareURLStr( PAGE_MM_SELECTMODACCOUNT, array() ) );
		else
			$preproc->assign( "accountsMenuURL", prepareURLStr( PAGE_MM_ADDMODACCOUNT . '?onServer=0', array() ) );
	}
	$preproc->assign( "inboxMode", $inboxMode );
	$preproc->assign( "mm_path", "../../2.0/" );

	$preproc->assign( "sendingMessages", $sendingMessages );


	$rightHeaderHeight = 0;
	if($inboxMode)
	{
		setAppUserCommonValue( $MM_APP_ID, $currentUser, 'CURRENT_FOLDER', base64_encode(0), $kernelStrings, $readOnly );
		if(!$statisticsMode)
			$rightHeaderLabel = '<b>' . $currentAccount . '</b> (' . $accounts[$currentAccount]['MMA_NAME'] . ')';
		else
			$rightHeaderLabel = '<b>' . $mmStrings['app_inbox_summary_title'] . '</b>';
		$rightHeaderHeight = 75;
	}
	else
	{
		setAppUserCommonValue( $MM_APP_ID, $currentUser, 'CURRENT_FOLDER', base64_encode($curMMF_ID), $kernelStrings, $readOnly );
		if(!$statisticsMode)
			$rightHeaderLabel = '<b>' . $curFolderName . '</b>';
		else
			$rightHeaderLabel = '<b>' . $mmStrings['app_treeavailflds_title'] . '</b>';
	}
	$preproc->assign( "rightHeaderLabel", $rightHeaderLabel );
	$preproc->assign( "rightHeaderHeight", $rightHeaderHeight );

	if( $action=='msgList' && !$ajaxAccess )
		$preproc->assign( "hideErrorBlock", 1 );
	else
		$preproc->assign( "hideErrorBlock", 0 );

	if($action == 'msgRefresh')
	{
		$preproc->display( "msglist.htm" );
		exit;
	}

	if(($action == 'fldRefresh') && !$fatalError)
	{
		$info_block = '';
		foreach($accountsInfo as $account=>$info)
			foreach($info as $key=>$val)
				$info_block .= $account . "\t" . $key . "\t" . $val . "\n";

		if($info_block)
			echo rtrim($info_block);

		exit;
	}

	if($preproc->get_template_vars('ajaxAccess'))
	{
		require_once  "../../../common/html/includes/ajax.php";
		$ajaxRes = array();

		$ajaxRes["toolbar"] = simple_ajax_get_toolbar("mm_toolbar.htm", $preproc);
		$ajaxRes["rightContent"] = $preproc->fetch( "mm_rightpanel.htm" );

		print simple_ajax_encode($ajaxRes);
		exit;
	}

	$preproc->display( 'mm_resizeable.htm' );

?>