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

	$currentBookID = base64_decode( $currentBookID );
	$curQPF_ID = base64_decode( $QPF_ID );
	$parentFolderID = base64_decode( $QPF_ID_PARENT );

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

					if ( !in_array( $curQPF_ID, array_keys( $folders ) ) )
					{
						$fatalError = true;
						$errorStr = $qpStrings['app_page_norights_message']."0";;
						break;
					}

					if ( $parentFolderID != TREE_ROOT_FOLDER && !in_array( $parentFolderID, array_keys( $folders ) ) )
					{
						$fatalError = true;
						$errorStr = $qpStrings['app_page_norights_message']."1";
						break;
					}

					$folderData = $qp_pagesClass->getFolderInfo( $curQPF_ID, $kernelStrings );

					if ( PEAR::isError( $folderData ) )
					{
						$fatalError = true;
						$errorStr = $folderData->getMessage();

						break;
					}

	}

	$btnIndex = getButtonIndex( array( 'attachbtn', 'copyimbtn', 'cancelbtn' ), $_POST, false );

	$commonRedirParams = array();

	$commonRedirParams['QPF_ID'] = base64_encode( $curQPF_ID );
	$commonRedirParams['QPF_ID_PARENT'] = base64_encode( $parentFolderID );
	$commonRedirParams['currentBookID'] = base64_encode( $currentBookID );

	$commonRedirParams[ACTION] = ACTION_EDIT;
	$commonRedirParams[OPENER] = $opener;

	$messageStack = array();

	switch ($btnIndex)
	{
		case 'attachbtn' :

					if ( $fatalError || !isset($finished) )
						break;

					$exts = array( "jpeg", "jpg", "png", "gif" );

					// Move uploaded files to the temporary directory and prepare file list
					//

					$fileList = array();
					foreach( $_FILES['files']['name'] as $fileIndex=>$name )
						if ( strlen($name) )
						{
							$pInfo = pathinfo( $name );

							if ( in_array( strtolower( $pInfo['extension'] ), $exts ) )
							{
								if ( $_FILES['files']['size'][$fileIndex] != 0 && $_FILES['files']['size'][$fileIndex] != 0 )
								{
									$tmpfile = array();

									$tmpfile['name'] = $_FILES['files']['name'][$fileIndex];
									$tmpfile['type'] = $_FILES['files']['type'][$fileIndex];
									$tmpfile['tmp_name'] = $_FILES['files']['tmp_name'][$fileIndex];
									$tmpfile['error'] = $_FILES['files']['error'][$fileIndex];
									$tmpfile['size'] = $_FILES['files']['size'][$fileIndex];

									$res = add_moveAttachedFile( $tmpfile,
																	base64_decode($PAGE_ATTACHED_FILES),
																	WBS_TEMP_DIR, $locStrings, true, "qp" );

									if ( PEAR::isError( $res ) )
									{
										$messageStack[] = $res->getMessage();
										break;
									}
									$PAGE_ATTACHED_FILES = base64_encode($res);
								}
								else
									$messageStack[] = sprintf ( $qpStrings['add_screen_upload_error'], $_FILES['files']['name'][$fileIndex], $qpStrings[$qp_uploadErrors[$_FILES['files']['error'][$fileIndex]]] );
							}
						}

					// Make note attachments list
					//
					$res = makeRecordAttachedFilesList( base64_decode($RECORD_FILES),
														base64_decode($PAGE_DELETED_FILES),
														base64_decode($PAGE_ATTACHED_FILES),
														$locStrings );

					if ( PEAR::isError( $res ) )
					{
						$messageStack[] = $res->getMessage();
						break;
					}

					$folderData["QPF_ATTACHMENT"] = base64_encode($res);

					$res = db_query( $qr_qp_updateFolderBookAttachment, prepareArrayToStore( $folderData ) );

					if ( PEAR::isError($res) )
					{
						$messageStack[] = $kernelStrings[ERR_QUERYEXECUTING];
						break;
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

					$fileList = listAttachedFiles( base64_decode($PAGE_ATTACHED_FILES) );

					$_qpQuotaManager = new DiskQuotaManager();

					$TotalUsedSpace = $_qpQuotaManager->GetUsedSpaceTotal( $kernelStrings );

					if ( PEAR::isError($TotalUsedSpace) )
					{
						$messageStack[] = $TotalUsedSpace->getMessage();
						break;
					}

					foreach( $fileList as $value )
					{
						$pInfo = pathinfo( $value["diskfilename"] );
						$ext = $pInfo['extension'];

						$destPath = sprintf( "%s/%s", $attachmentsPath, $value["diskfilename"] );

						$thumbPath = makeThumbnail( $destPath, $destPath, $ext, 96, $kernelStrings );

						if ( PEAR::isError($thumbPath) )
							$messageStack[] = $thumbPath->getMessage();

						$fileSize = filesize( $thumbPath );

						$TotalUsedSpace += $_qpQuotaManager->GetSpaceUsageAdded();

						// Check if the user disk space quota is not exceeded
						//
						if ( $_qpQuotaManager->SystemQuotaExceeded($TotalUsedSpace + $fileSize) )
						{
							$_qpQuotaManager->Flush( $kernelStrings );
							$errorObj = $_qpQuotaManager->ThrowNoSpaceError( $kernelStrings );
							$messageStack[] = $errorObj->getMessage();
						}

						$_qpQuotaManager->AddDiskUsageRecord( SYS_USER_ID, $QP_APP_ID, $fileSize );
					}
					$_qpQuotaManager->Flush( $kernelStrings );

				if ( count( $messageStack ) )
					break;

		case 'cancelbtn' :
					redirectBrowser( PAGE_QP_ADDMODPAGE, $commonRedirParams );
					break;
	}


	switch (true) {
		case true :
					if ( $fatalError || count( $messageStack ) )
						break;

					$PAGE_ATTACHED_FILES = null;
					$PAGE_DELETED_FILES = null;

					$RECORD_FILES = $folderData["QPF_ATTACHMENT"];

	}

	//
	// Generating attached files lists
	//
	if ( !$fatalError && count( $messageStack ) == 0 )
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


	$preproc = new php_preprocessor( $templateName, $locStrings, $language, $QP_APP_ID );

	$preproc->assign( PAGE_TITLE, $qpStrings['ai_screen_addimage_title'] );
	$preproc->assign( FORM_LINK, PAGE_QP_ADDIMAGES );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	$preproc->assign( "qpStrings", $qpStrings );
	$preproc->assign( "folderData", $folderData );

	$preproc->assign( 'currentBookID', base64_encode( $currentBookID ) );
	$preproc->assign( "limitStr", nl2br(getUploadLimitInfoStr( $locStrings )) );

	$preproc->assign( "opener", $opener );

	$preproc->assign( "QPF_ID", $QPF_ID );
	$preproc->assign( "QPF_ID_PARENT", $QPF_ID_PARENT );

	$showMessages = 0;
	if ( isset($messageStack) && count( $messageStack ) )
	{
		$preproc->assign( "messageStack", implode( "<br>", $messageStack ) );
		$showMessages = 1;
	}
	$preproc->assign( "showMessages", $showMessages );

	if ( !$fatalError )
	{
		$preproc->assign( PAGE_ATTACHED_FILES, $PAGE_ATTACHED_FILES );
		$preproc->assign( RECORD_FILES, $RECORD_FILES );
		$preproc->assign( PAGE_DELETED_FILES, $PAGE_DELETED_FILES );

		$preproc->assign( "attachedFiles", $attachedFiles );

		if ( isset($searchString) )
			$preproc->assign( "searchString", $searchString );

		if ( isset($edited) )
			$preproc->assign( "edited", $edited );

		$fileSections = array();

		for ( $i = 0; $i <= 99; $i++ )
			$fileSections[] = $i;

		$preproc->assign( "fileSections", $fileSections );
		$preproc->assign( "sectionCount", count($fileSections) );

	}

	$preproc->display( "addimages.htm" );
?>