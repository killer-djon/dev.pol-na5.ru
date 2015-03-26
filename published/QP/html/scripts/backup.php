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

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL, 'closebtn' ), $_POST );

	$currentBookID = base64_decode( $currentBookID );

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

					if ( is_null( $currentBookID ) )
					{
						$fatalError = true;
						$errorStr = $qpStrings['app_page_norights_message'];
						break;
					}

					$bookData = $qp_treeClass->getFolderInfo( $currentBookID, $kernelStrings );

					if ( PEAR::isError($bookData) )
					{
						die( $errorStr = $res->getMessage() );
						break;
					}

					$qp_pagesClass->currentBookID = $currentBookID;

	}

	switch ($btnIndex) {

		case 0:
					$archiveName = $qp_pagesClass->createArchive( $currentUser, $bookData, $kernelStrings, $qpStrings, $fileNumber=0 );

					if ( PEAR::isError($archiveName) ) {
						$errorStr = $archiveName->getMessage();
						break;
					}

					$archiveCreated = true;
					$filesAddedMessage = sprintf( $qpStrings['bak_filesadded_label'], $fileNumber );

					$archivePath = WBS_TEMP_DIR."/".$archiveName;
					$fileSizeInBytes = filesize($archivePath);
					$fileSize = formatFileSizeStr( filesize($archivePath) );

					$namePrefix = "qpbak_".$bookData["QPB_TEXTID"];

					$fileName = sprintf( "%s%s.zip", $namePrefix, date('mdY') );
					$archiveName = base64_encode($archiveName);

					$downloadArchiveLink = prepareURLStr( PAGE_QP_GETARCHIVE, array('file'=>$archiveName, 'fileName'=>base64_encode($fileName)) );
					$edited = false;

					break;

		case 1 :
		case 2 :

					redirectBrowser( PAGE_QP_QUICKPAGES, array() );

	}


	switch (true) {
		case true :

					$folders = $qp_pagesClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false, $access = null, $hierarchy = null, $deletable = null, $minimalRights = null );

					if ( PEAR::isError($folders) )
					{
						$fatalError = true;
						$errorStr = $folders->getMessage();
						break;
					}

					$archiveSupported = qp_archiveSupported();
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QP_APP_ID );

	$preproc->assign( PAGE_TITLE, $qpStrings["bak_screen_title"] );
	$preproc->assign( FORM_LINK, PAGE_QP_BACKUP );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "qpStrings", $qpStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );

	$preproc->assign( 'currentBookID', base64_encode( $currentBookID ) );

	if ( !$fatalError )
	{
		$preproc->assign( "folders", $folders );
		$preproc->assign( "folderCount", count($folders) );
		$preproc->assign( "bookData", $bookData );
		$preproc->assign( "archiveSupported", $archiveSupported );

		$preproc->assign( "hierarchy", $hierarchy );

		if ( isset($archiveCreated) )
		{
			$preproc->assign( "archiveCreated", $archiveCreated );
			if ( $archiveCreated ) {
				$preproc->assign( "archiveName", $archiveName );
				$preproc->assign( "fileSize", $fileSize );
				$preproc->assign( "fileNumber", $fileNumber );
				$preproc->assign( "filesAddedMessage", $filesAddedMessage );
				$preproc->assign( "downloadArchiveLink", $downloadArchiveLink );
				$preproc->assign( "fileName", $fileName );
				$preproc->assign( "fileSizeInBytes", $fileSizeInBytes );
				if ( isset($edited) )
					$preproc->assign( "edited", $edited );

				if ( isset($fileDescription) )
					$preproc->assign( "fileDescription", $fileDescription );

				if ( isset($targetID) )
					$preproc->assign( "targetID", $targetID );
			}
		}

	}

	$preproc->display( "backup.htm" );
?>