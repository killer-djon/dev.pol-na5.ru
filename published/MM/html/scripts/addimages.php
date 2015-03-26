<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/MM/mm.php" );
	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "MM";

	pageUserAuthorization( $SCR_ID, $MM_APP_ID, false );

	//
	// Page variables setup
	//

	$locStrings = $loc_str[$language];
	$kernelStrings = $loc_str[$language];
	$mmStrings = $mm_loc_str[$language];
	$invalidField = null;

	$curMMM_ID = $MMM_ID;
	$curMMF_ID = base64_decode( $MMF_ID );

	$btnIndex = getButtonIndex( array( 'attachbtn', 'copyimbtn', 'cancelbtn' ), $_POST, false );

	$commonRedirParams = array();

	$commonRedirParams['MMM_ID'] = $curMMM_ID;
	$commonRedirParams['MMF_ID'] = $MMF_ID;

	$commonRedirParams[ACTION] = ACTION_EDIT;
	$commonRedirParams[OPENER] = $opener;

	$messageStack = array();

	switch( true )
	{
		case true:

					$message = new mm_message( $mm_message_data_schema );

					if ( PEAR::isError($ret = $message->loadEntry($MMM_ID, $kernelStrings, $mmStrings ) ) )
					{
						$fatalError = true;
						$errorStr = $ret->getMessage();
						break;
					}

					$messageData = $message->getValuesArray();

					if ( $messageData['MMF_ID'] != $curMMF_ID )
					{
						$fatalError = true;
						$errorStr = $mmStrings['app_access_violation_error'];
						break;
					}

					$folderInfo = $mm_treeClass->getFolderInfo( $curMMF_ID , $kernelStrings );
					if ( PEAR::isError($folderInfo) ) {
						$fatalError = true;
						$errorStr = $folderInfo->getMessage();

						break;
					}

					$rights = $mm_treeClass->getIdentityFolderRights( $currentUser, $curMMF_ID, $kernelStrings );
					if ( PEAR::isError($rights) )
					{
						$fatalError = true;
						$errorStr = $rights->getMessage();

						break;
					}

					if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) )
					{
						$fatalError = true;
						$errorStr = $mmStrings['app_access_violation_error'];

						break;
					}
	}

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
																	WBS_TEMP_DIR, $kernelStrings, true, "mm" );

									if ( PEAR::isError( $res ) )
									{
										$messageStack[] = $res->getMessage();
										break;
									}
									$PAGE_ATTACHED_FILES = base64_encode($res);
								}
								else
									$messageStack[] = sprintf ( $mmStrings['add_screen_upload_error'], $_FILES['files']['name'][$fileIndex], $mmStrings[$mm_uploadErrors[$_FILES['files']['error'][$fileIndex]]] );
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

					$message->MMM_IMAGES = base64_encode($res);

					$MM_ND = $message->updateImageAttachment( $currentUser, $kernelStrings, $mmStrings );

					if ( PEAR::isError( $MM_ND ) )
					{
						$messageStack[] = $MM_ND->getMessage();
						break;
					}

					// Apply attachments
					//
					$attachmentsPath = mm_getNoteAttachmentsDir( $MM_ND, MM_IMAGES );

					$res = applyPageAttachments( base64_decode($PAGE_ATTACHED_FILES),
												base64_decode($PAGE_DELETED_FILES),
												$attachmentsPath, $locStrings, $MM_APP_ID );

					if ( PEAR::isError($res) )
					{
						$messageStack[] = $res->getMessage();
						break;
					}

					$fileList = listAttachedFiles( base64_decode($PAGE_ATTACHED_FILES) );

					$_mmQuotaManager = new DiskQuotaManager();

					$TotalUsedSpace = $_mmQuotaManager->GetUsedSpaceTotal( $kernelStrings );

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

						$TotalUsedSpace += $_mmQuotaManager->GetSpaceUsageAdded();

						// Check if the user disk space quota is not exceeded
						//
						if ( $_mmQuotaManager->SystemQuotaExceeded($TotalUsedSpace + $fileSize) )
						{
							$_mmQuotaManager->Flush( $kernelStrings );
							$errorObj = $_mmQuotaManager->ThrowNoSpaceError( $kernelStrings );

							$messageStack[] = $errorObj->getMessage();
						}

						$_mmQuotaManager->AddDiskUsageRecord( SYS_USER_ID, $MM_APP_ID, $fileSize );
					}
					$_mmQuotaManager->Flush( $kernelStrings );

					if ( count( $messageStack ) )
						break;

		case 'cancelbtn' :
					redirectBrowser( PAGE_MM_ADDMODMESSAGE, $commonRedirParams );
					break;
	}


	switch (true)
	{
		case true :
					if ( $fatalError || count( $messageStack ) )
						break;

					$PAGE_ATTACHED_FILES = null;
					$PAGE_DELETED_FILES = null;

					$RECORD_FILES = $messageData["MMM_IMAGES"];

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

	$preproc = new php_preprocessor( $templateName, $locStrings, $language, $MM_APP_ID );

	$preproc->assign( PAGE_TITLE, $mmStrings['ai_screen_addimage_title'] );
	$preproc->assign( FORM_LINK, PAGE_MM_ADDIMAGES );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	$preproc->assign( "mmStrings", $mmStrings );
	$preproc->assign( "messageData", $messageData );

	$preproc->assign( "limitStr", nl2br(getUploadLimitInfoStr( $locStrings )) );

	$preproc->assign( "opener", $opener );

	$preproc->assign( "MMM_ID", $MMM_ID );
	$preproc->assign( "MMF_ID", $MMF_ID );

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