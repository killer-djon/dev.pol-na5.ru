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

	$locStrings = $loc_str[$language];
	$kernelStrings = $loc_str[$language];
	$qpStrings = $qp_loc_str[$language];
	$invalidField = null;
	$metric = metric::getInstance();
	if ( !isset( $action ) )
		$action = ACTION_NEW;

	$currentBookID = base64_decode( $currentBookID );

	if ( isset( $QPF_ID ) )
		$curQPF_ID = base64_decode( $QPF_ID );

	if ( !isset($parentFolderID) )
		$parentFolderID = base64_decode( $QPF_ID_PARENT );
	else
		$parentFolderID = base64_decode( $parentFolderID );
	
	if ($action == ACTION_EDIT && empty($curQPF_ID)) {
		$curQPF_ID = $qp_pagesClass->getUserDefaultFolder( $currentUser, $kernelStrings, $readOnly, false );
		$QPF_ID = base64_encode($curQPF_ID);
	}
	
	if ($action == ACTION_NEW && empty($curQPF_ID) && empty($parentFolderID)) {
		$parentFolderID = $qp_pagesClass->getUserDefaultFolder( $currentUser, $kernelStrings, $readOnly, false );
	}
	

	$_SESSION['EDIT_BOOK'] = null;

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

					if ( $action != ACTION_NEW )
					{
						if ( !in_array( $curQPF_ID, array_keys( $folders ) ) )
						{
							$fatalError = true;
							$errorStr = $qpStrings['app_page_norights_message'];
							break;
						}
						
						if (!($parentFolderID))
							$parentFolderID = $folders[$curQPF_ID]->QPF_ID_PARENT;
						
						if ( $parentFolderID != TREE_ROOT_FOLDER && !in_array( $parentFolderID, array_keys( $folders ) ) )
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

	$btnIndex = getButtonIndex( array( BTN_ATTACH, BTN_SAVE, BTN_CANCEL, BTN_DELETEFILES, "deleteNoteBtn", 'saveclosebtn', 'copyimbtn', 'addfilesbtn' ), $_POST );
	if ($btnIndex === -1)
		$metric->addAction($DB_KEY, $currentUser, 'QP', 'ADDPAGE', 'ACCOUNT');

	switch ($btnIndex)
	{
		case 1 :
		case 3 :
		case 5 :
		case 7 :
					if ( $fatalError )
						break;

					if ( $btnIndex == 3 )
					{
						$pageFiles = base64_decode($PAGE_ATTACHED_FILES);
						$delFiles = base64_decode($PAGE_DELETED_FILES);

						if ( !isset($cbdeletenewfile) ) $cbdeletenewfile = array();
						if ( !isset($cbdeleterecordfile) ) $cbdeleterecordfile = array();

						$res = deleteAttachedFiles( base64_decode($RECORD_FILES), $delFiles, $pageFiles,
													$cbdeletenewfile, $cbdeleterecordfile, $locStrings );

						if ( PEAR::isError( $res ) ) {
							$errorStr =  $res->getMessage();

							break;
						}

						$PAGE_ATTACHED_FILES = base64_encode( $pageFiles );
						$PAGE_DELETED_FILES = base64_encode( $delFiles );
					}

					// Make note attachments list
					//
					$res = makeRecordAttachedFilesList( base64_decode($RECORD_FILES),
														base64_decode($PAGE_DELETED_FILES),
														base64_decode($PAGE_ATTACHED_FILES),
														$locStrings );

					if ( PEAR::isError( $res ) ) {
						$errorStr = $res->getMessage();
						break;
					}

					$folderData["QPB_ID"] = $currentBookID;
					$folderData["QPF_ATTACHMENT"] = base64_encode($res);
					$folderData["QPF_NAME"] = $folderData["QPF_TITLE"];

					if ( PEAR::isError( $ret = qp_checkTextID( $kernelStrings, $qpStrings, $folderData["QPF_TEXTID"], $currentBookID, $action == ACTION_NEW ? "" : $folderData["QPF_ID"] ) ) )
					{
						$errorStr = $ret->getMessage();
						$invalidField = 'QPF_TEXTID';
						break;
					}

					if ( !isset( $folderData["QPF_PUBLISHED"] ) )
						$folderData["QPF_PUBLISHED"] = 0;

					if ( $action == ACTION_NEW )
					{
						$SORT_ID = db_query_result( $qr_qp_maxSort, DB_FIRST, array( "QPF_ID_PARENT"=> $parentFolderID, "QPB_ID"=> $currentBookID ) );
						$folderData["QPF_SORT"] = $SORT_ID + 1;
					}

					$kernelStrings["app_treeinvfoldername_message"]= $qpStrings["app_treeinvfoldername_message"];
					$kernelStrings["app_treeinvfolderlenname_message"]= $qpStrings["app_treeinvfoldername_message"];

					$folderID = $qp_pagesClass->addmodFolder( $action, $currentUser, $parentFolderID, prepareArrayToStore($folderData),
															$kernelStrings, false, null, array('qpStrings'=>$qpStrings, 'action'=>$action), true, false, null, $checkFolderName = false, "qp_checkPermissionsCallback" );

					if ( PEAR::isError( $folderID ) )
					{
						$errorStr = $folderID->getMessage();

						if ( $folderID->getCode() == ERRCODE_INVALIDFIELD )
							$invalidField = $folderID->getUserInfo();

						break;
					}

					$folderData["QPF_ID"] = $folderID;

					$folderData["QPF_MODIFYUSERNAME"] = getUserName( $currentUser, true );
					$folderData["QPF_MODIFYDATETIME"] = convertToSqlDateTime( time() );

					if ( $action == ACTION_NEW )
						$folderData["QPF_UNIQID"] = qp_generateUniqueID( $currentUser, $folderID );

					$folderData["QPF_TEXT"] =  html2text( $folderData["QPF_CONTENT"], false );
					$res = db_query( $qr_qp_updateFolderBook, prepareArrayToStore( $folderData ) );

					if ( PEAR::isError($res) )
					{
						$errorStr = $kernelStrings[ERR_QUERYEXECUTING];
						$fatalError = true;
						break;
					}

					$callbackParams['U_ID'] = $currentUser;

					if ( $action == ACTION_NEW )
					{
						if ( !$admin )
						{
							$qp_pagesClass->setFolderCollapseValue( $currentUser, $parentFolderID, false, $kernelStrings );
							$qp_pagesClass->setUserDefaultFolder( $currentUser, $folderID, $kernelStrings );
						}
					}

					// Apply attachments
					//
					$attachmentsPath = qp_getNoteAttachmentsDir( $folderData["QPF_UNIQID"] );

					$res = applyPageAttachments( base64_decode($PAGE_ATTACHED_FILES),
												base64_decode($PAGE_DELETED_FILES),
												$attachmentsPath, $locStrings, $QP_APP_ID );

					if ( PEAR::isError($res) )
					{
						$messageStack[] = $res->getMessage();
						break;
					}

					$fileList = listAttachedFiles( base64_decode($PAGE_DELETED_FILES) );

					$_qpQuotaManager = new DiskQuotaManager();

					foreach( $fileList as $value )
					{
						$pInfo = pathinfo( $value["diskfilename"] );
						$destPath = sprintf( "%s/%s", $attachmentsPath, $value["diskfilename"] );

						$thumbToDelete = findThumbnailFile( $destPath, $ext );
						if ( file_exists($thumbToDelete) )
						{
							$_qpQuotaManager->AddDiskUsageRecord( SYS_USER_ID, $QP_APP_ID, -1*filesize($thumbToDelete) );
							@unlink($thumbToDelete);
						}
					}

					$_qpQuotaManager->Flush($kernelStrings);

					if ( $btnIndex == 5 )
						redirectBrowser( base64_decode($opener), array( 'curQPF_ID'=>base64_encode($folderID), 'currentBookID'=>base64_encode( $currentBookID ) ) );

					if ( $btnIndex == 7 )
					{

						$commonRedirParams = array();

						$commonRedirParams['opener'] = $opener;

						$commonRedirParams['QPF_ID'] = base64_encode( $curQPF_ID );
						$commonRedirParams['currentBookID'] = base64_encode( $currentBookID );
						$commonRedirParams['QPF_ID_PARENT'] = base64_encode( $parentFolderID );

						redirectBrowser( PAGE_QP_ADDIMAGES, $commonRedirParams );
					}

					if ( $action == ACTION_NEW )
					{
						$action = ACTION_EDIT;
						$curQPF_ID = $folderID;
						$QPF_ID = base64_encode( $folderID );
						$QPF_ID_PARENT = base64_encode( $parentFolderID );
					}

					$edited = false;

					break;


		case 2 :
					redirectBrowser( PAGE_QP_QUICKPAGES, array( "random"=>rand() ) );
					break;

		case 4 :
					if ( $fatalError )
						break;

					$params = array();
					$params['U_ID'] = $currentUser;
					$params['kernelStrings'] = $kernelStrings;

					$res = $qp_pagesClass->deleteFolder( $curQPF_ID, $currentUser, $kernelStrings, false, "qp_onDeleteFolder", $params );

					if ( PEAR::isError($res) )
					{
						$errorStr = $res->getMessage();
						break;
					}

					redirectBrowser( PAGE_QP_QUICKPAGES, array( "random"=>rand() ) );

					break;

		case 6 :
					if ( $fatalError )
						break;

					$fromQPF_ID = base64_decode( $copyIMGS_ID );

					if ( !in_array( $fromQPF_ID, array_keys( $folders ) ) )
						break;

					$fromData = $qp_pagesClass->getFolderInfo( $fromQPF_ID, $kernelStrings );

					if ( PEAR::isError( $fromData ) )
					{
						$fatalError = true;
						$errorStr = $folderData->getMessage();

						break;
					}

					$ret = qp_copyFiles( $fromData, $folderData );

					if ( PEAR::isError( $ret ) )
					{
						$fatalError = true;
						$errorStr = $ret->getMessage();

						break;
					}

					$folderData["QPB_ID"] = $currentBookID;
					$folderData["QPF_ATTACHMENT"] = base64_encode($ret);
					$folderData["QPF_NAME"] = $folderData["QPF_TITLE"];

					if ( !isset( $folderData["QPF_PUBLISHED"] ) )
						$folderData["QPF_PUBLISHED"] = 0;

					$folderID = $qp_pagesClass->addmodFolder( "edit", $currentUser, $parentFolderID, prepareArrayToStore($folderData),
															$kernelStrings, false, null, array('qpStrings'=>$qpStrings, 'action'=>ACTION_EDIT), true, false, null, $checkFolderName = false, "qp_checkPermissionsCallback" );

					if ( PEAR::isError( $folderID ) )
					{
						$errorStr = $folderID->getMessage();

						if ( $folderID->getCode() == ERRCODE_INVALIDFIELD )
							$invalidField = $folderID->getUserInfo();

						break;
					}

					$RECORD_FILES = base64_encode ( $ret );

					$fileList = listAttachedFiles( $ret );

					$attachmentsPath = qp_getNoteAttachmentsDir( $folderData["QPF_UNIQID"] );

					foreach( $fileList as $value )
					{
						$pInfo = pathinfo( $value["name"] );
						$ext = $pInfo['extension'];

						$destPath = sprintf( "%s/%s", $attachmentsPath, $value["name"] );

						$thumbPath = makeThumbnail( $destPath, $destPath, $ext, 96, $kernelStrings );

						if ( !PEAR::isError($thumbPath) )
						{
							if ( $thumbPath )
								$thumbGenerated = true;
						}
						else
						{
							$fatalError = true;
							$errorStr = $thumbPath->getMessage();
							break 2;
						}
					}

					break;
	}


	switch (true) {
		case true :
					$_SESSION['EDIT_BOOK'] = $currentBookID;

					if ( $fatalError )
						break;

					if ( $action == ACTION_NEW )
					{
						$rootFolder = array();
						$rootFolder['OFFSET_STR'] = null;
						$rootFolder['NAME'] = $kernelStrings['app_treeroot_name'];
						$rootFolder['RIGHT'] = TREE_READWRITEFOLDER;

						$folders = array_merge( array(TREE_ROOT_FOLDER => (object)$rootFolder), $folders );

						foreach ( $folders as $thisQPF_ID=>$curFolderData )
						{
							$encodedID = base64_encode($thisQPF_ID);
							$curFolderData->curQPB_ID = base64_encode( $currentBookID );
							$curFolderData->curID = $encodedID;
							$curFolderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $curFolderData->OFFSET_STR);
							$folders[$thisQPF_ID] = $curFolderData;
						}
					}

					$im_folders = array();
					foreach ( $folders as $thisQPF_ID=>$curFolderData )
					{
						if ( isset( $curQPF_ID ) && ( $thisQPF_ID == TREE_ROOT_FOLDER || $thisQPF_ID == $curQPF_ID ) )
							continue;

						$rFiles = isset( $curFolderData->QPF_ATTACHMENT ) ? isset( $curFolderData->QPF_ATTACHMENT ) : NULL;

						$rrFiles = makeAttachedFileList( base64_decode($rFiles),
												null,
												null,
												"cbdeletenewfile",
												"cbdeleterecordfile" );

						if ( count( $rrFiles ) == 0 )
							continue;

						$encodedID = base64_encode($thisQPF_ID);
						$curFolderData->curQPB_ID = base64_encode( $currentBookID );
						$curFolderData->curID = $encodedID;
						$curFolderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $curFolderData->OFFSET_STR);
						$curFolderData->NAME .= " (".count( $rrFiles ).")";
						$im_folders[$thisQPF_ID] = $curFolderData;

					}

					if ( !isset($edited) || !$edited )
					{
						$PAGE_ATTACHED_FILES = null;
						$PAGE_DELETED_FILES = null;

						if ( $action == ACTION_NEW )
						{
							$folderData["QPF_SORT"] = 0;

							$RECORD_FILES = null;
						}
						else
						{
							$curQPF_ID = base64_decode($QPF_ID);

							$folderData = $qp_pagesClass->getFolderInfo( $curQPF_ID, $kernelStrings );

							if ( PEAR::isError( $folderData ) )
							{
								$fatalError = true;
								$errorStr = $folderData->getMessage();

								break;
							}

							$thisFolderName = $folderData['QPF_TITLE'];
							$RECORD_FILES = $folderData["QPF_ATTACHMENT"];
						}
					}

					if ( ( !isset( $edited ) || $edited != 1 ) && $action == "new" )
					{
						$selectedFolder ="";
						if ( !qp_getParentId( $hierarchy, $parentFolderID, $selectedFolder ) )
							$selectedFolder = TREE_AVAILABLE_FOLDERS;

						$parentFolderID = $selectedFolder;
					}

	}

	//
	// Generating attached files lists
	//
	if ( !$fatalError )
	{
		$attachedFiles = makeAttachedFileList( base64_decode($RECORD_FILES),
												base64_decode($PAGE_DELETED_FILES),
												base64_decode($PAGE_ATTACHED_FILES),
												"cbdeletenewfile",
												"cbdeleterecordfile" );

		$oldFiles = makeAttachedFileList( base64_decode($RECORD_FILES),
												null,
												null,
												"cbdeletenewfile",
												"cbdeleterecordfile" );
		if ( !is_null( $attachedFiles ) && !is_null( $oldFiles ) )
			foreach( $attachedFiles as $key => $value )
				if ( in_array( $key, array_keys( $oldFiles ) ) )
				{
					$value["old"] = 1;
					$attachedFiles[$key] = $value;
				}

	}

	//
	// Page implementation
	//

	if ( isset( $folderData["QPF_UNIQID"] ) )
		$attachmentsPath = qp_getNoteAttachmentsDir( $folderData["QPF_UNIQID"] );

	$thumbPerms[] = array();

	if ( isset( $attachedFiles ) )
		foreach ( $attachedFiles as $key=>$value )
		{
			$diskfilename = base64_decode( $value["diskfilename"] );

			$imgPath = "../../../publicdata/$DB_KEY/attachments/qp/attachments/{$folderData['QPF_UNIQID']}/".$diskfilename;
			$value["URL"] = "<img src=".str_replace( " ", "%20", $imgPath ).">";
			$value["DIMS"] = getimagesize( $imgPath );

			$thumbParams = array();

			$pInfo = pathinfo( $diskfilename );

			$destPath = sprintf( "%s/%s", $attachmentsPath, $diskfilename );

			$thumbPerms[] = $destPath;

			$srcExt = null;
			$thumbParams['nocache'] = getThumbnailModifyDate( $destPath, 'win', $srcExt );
			$thumbParams['basefile'] = base64_encode( $destPath );
			$thumbParams['ext'] = base64_encode( $pInfo['extension'] );

			$value['THUMB_URL'] = prepareURLStr( PAGE_GETFILETHUMB, $thumbParams );

			$attachedFiles[$key] = $value;
		}

	$_SESSION['THUMBPERMS'] = $thumbPerms;

	$preproc = new php_preprocessor( $templateName, $locStrings, $language, $QP_APP_ID );

	$title = ( $action == ACTION_NEW ) ? $qpStrings['amn_screen_add_title'] : $qpStrings['amn_screen_modify_title'];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_QP_ADDMODPAGE."?".rand() );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( ACTION, $action );
	$preproc->assign( "qpStrings", $qpStrings );

	$preproc->assign( "HTML_AREA_FIELD", "folderData[QPF_CONTENT]" );
	$preproc->assign( "HTML_AREA_CONFIG", "full" );


	$preproc->assign( 'currentBookID', base64_encode( $currentBookID ) );
	$preproc->assign( "limitStr", nl2br(getUploadLimitInfoStr( $locStrings )) );

	$preproc->assign( "opener", $opener );

	if ( $action == ACTION_EDIT )
	{
		$preproc->assign( "QPF_ID", $QPF_ID );
		$preproc->assign( "QPF_ID_PARENT", $QPF_ID_PARENT );
		$preproc->assign( "hasItChilds", qp_hasItChilds( $hierarchy, $curQPF_ID ) );
	}
	else
		$preproc->assign( "parentFolderID", $parentFolderID );

	if ( !$fatalError )
	{
		$preproc->assign( PAGE_ATTACHED_FILES, $PAGE_ATTACHED_FILES );
		$preproc->assign( RECORD_FILES, $RECORD_FILES );
		$preproc->assign( PAGE_DELETED_FILES, $PAGE_DELETED_FILES );

		$preproc->assign( "attachedFiles", $attachedFiles );
		$preproc->assign( "im_folders", $im_folders );
		$preproc->assign( "show_im_folders", ( count( $im_folders ) != 0 ) );


		if ( $action == ACTION_EDIT )
			$preproc->assign( "thisFolderName", $thisFolderName );
		else
			$preproc->assign( "folders", $folders );

		if ( isset($folderData) ) {
			$fix = prepareArrayToDisplay( $folderData, array( "QPF_CONTENT" ), isset($edited) && $edited );
			$fix = str_replace("&", "&amp;", $fix);
			$preproc->assign( "folderData",  $fix);
		}

		if ( isset($searchString) )
			$preproc->assign( "searchString", $searchString );

		if ( isset($edited) )
			$preproc->assign( "edited", $edited );
	}

	$preproc->display( "addmodpage.htm" );
?>
