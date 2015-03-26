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

	if ( !isset( $action ) )
		$action = ACTION_EDIT;

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

	$btnIndex = getButtonIndex( array( BTN_ATTACH, BTN_SAVE, BTN_CANCEL, BTN_DELETEFILES, "deleteNoteBtn", 'saveclosebtn', 'copyimbtn', 'addfilesbtn' ), $_POST );

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

					$themeData["QPT_ID"] = $QPT_ID;

					$themeData["QPT_ATTACHMENT"] = base64_encode($res);

					$res = qp_updateThemeTopData( $themeData, $kernelStrings );

					if ( PEAR::isError( $res ) )
					{
						$errorStr = $folderID->getMessage();
						break;
					}

					$attachmentsPath = qp_getNoteAttachmentsDir( $themeData["QPT_UNIQID"] );

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

					if ( $btnIndex == 5 || $btnIndex == 1 )
						redirectBrowser( PAGE_QP_ADDMODTHEME, array( "QPT_ID"=>$QPT_ID ) );


					if ( $btnIndex == 7 )
					{
						$commonRedirParams = array();
						$commonRedirParams['QPT_ID'] = $QPT_ID;

						redirectBrowser( PAGE_QP_THMADDIMAGES, $commonRedirParams );
					}

					$edited = false;

					break;


		case 2 :
					redirectBrowser( PAGE_QP_ADDMODTHEME, array( "QPT_ID"=>$QPT_ID ) );
					break;

	}


	switch (true) {
		case true :
					if ( $fatalError )
						break;

					if ( !isset( $edited ) )
					{
						$themeData = $theme;
						$PAGE_ATTACHED_FILES = null;
						$PAGE_DELETED_FILES = null;
						$RECORD_FILES = $themeData["QPT_ATTACHMENT"];
					}

					$qpVars = array();
					$qpVars['%BOOKNAME%'] = $qpStrings['qpt_variable_bookname'];

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

	if ( isset( $themeData["QPT_UNIQID"] ) )
		$attachmentsPath = qp_getNoteAttachmentsDir( $themeData["QPT_UNIQID"] );

	$thumbPerms[] = array();

	if ( isset( $attachedFiles ) )
		foreach ( $attachedFiles as $key=>$value )
		{
			$diskfilename = base64_decode( $value["diskfilename"] );

			$imgPath = "../../../publicdata/$DB_KEY/attachments/qp/attachments/{$themeData['QPT_UNIQID']}/".$diskfilename;
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

	$title = $qpStrings['qpt_screen_topmodify_title'];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_QP_EDITFRAME );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( ACTION, $action );
	$preproc->assign( "qpStrings", $qpStrings );

	$preproc->assign( "HTML_AREA_FIELD", "themeData[QPT_HEADER]" );
	$preproc->assign( "HTML_AREA_CONFIG", "full" );

	$preproc->assign( "qpVars", $qpVars );

	$preproc->assign( "limitStr", nl2br(getUploadLimitInfoStr( $locStrings )) );

	$preproc->assign( "QPT_ID", $QPT_ID );

	if ( !$fatalError )
	{
		$preproc->assign( PAGE_ATTACHED_FILES, $PAGE_ATTACHED_FILES );
		$preproc->assign( RECORD_FILES, $RECORD_FILES );
		$preproc->assign( PAGE_DELETED_FILES, $PAGE_DELETED_FILES );

		$preproc->assign( "attachedFiles", $attachedFiles );

		if ( isset( $themeData ) )
		{
			$themeData["QPT_HEADER"] = stripSlashes( $themeData["QPT_HEADER"] );

			$preproc->assign( "themeData", prepareArrayToDisplay( $themeData, array( "QPT_HEADER" ), isset($edited) && $edited ) );
		}
	}

	$preproc->display( "editframe.htm" );
?>