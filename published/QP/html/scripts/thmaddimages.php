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

	switch (true)
	{
		case true :

					if ( !session_is_registered( "propArray" ) )
					{
						$errorStr = $qpStrings["qpt_wrong_theme_message"];
						$fatalError=true;
						break;
					}

					$theme = qp_getTheme( $currentUser, $QPT_ID, $kernelStrings );

					if ( PEAR::isError( $theme ) )
					{
						$errorStr = $res->getMessage();
						$fatalError=true;
						break;
					}

					if ( is_null( $theme ) )
					{
						$errorStr = $qpStrings["qpt_wrong_theme_message"];
						$fatalError=true;
						break;
					}


	}

	$btnIndex = getButtonIndex( array( 'attachbtn', 'cancelbtn' ), $_POST, false );

	$commonRedirParams = array();

	$commonRedirParams['QPT_ID'] = $QPT_ID;
	$commonRedirParams[ACTION] = ACTION_EDIT;

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
									$messageStack[] = sprintf ( $qpStrings['add_screen_upload_error'], $_FILES['files']['name'][$fileIndex], $ddStrings[$dd_uploadErrors[$_FILES['files']['error'][$fileIndex]]] );
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

					$themeData["QPT_ID"] = $QPT_ID;
					$themeData["QPT_ATTACHMENT"] = base64_encode($res);

					$res = db_query( $qr_qp_updateThemeAttachment, prepareArrayToStore( $themeData ) );

					if ( PEAR::isError($res) )
					{
						$messageStack[] = $kernelStrings[ERR_QUERYEXECUTING];
						break;
					}

					// Apply attachments
					//
					$attachmentsPath = qp_getNoteAttachmentsDir( $themeData["QPT_UNIQID"] );

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
					redirectBrowser( PAGE_QP_EDITFRAME, $commonRedirParams );
					break;
	}


	switch (true) {
		case true :
					if ( $fatalError || count( $messageStack ) )
						break;

					$PAGE_ATTACHED_FILES = null;
					$PAGE_DELETED_FILES = null;

					if ( !isset( $edited ) )
						$themeData = $theme;

					$RECORD_FILES = $themeData["QPT_ATTACHMENT"];

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

	$thumbPerms[] = array();

	$preproc = new php_preprocessor( $templateName, $locStrings, $language, $QP_APP_ID );

	$preproc->assign( PAGE_TITLE, $qpStrings['ai_screen_addimage_title'] );
	$preproc->assign( FORM_LINK, PAGE_QP_THMADDIMAGES );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	$preproc->assign( "qpStrings", $qpStrings );

	$preproc->assign( "themeData", $themeData );

	$preproc->assign( "limitStr", nl2br(getUploadLimitInfoStr( $locStrings )) );

	$preproc->assign( "QPT_ID", $QPT_ID );

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

	$preproc->display( "thmaddimages.htm" );
?>