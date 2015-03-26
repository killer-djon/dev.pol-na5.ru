<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "CT";

	pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$ddStrings = $dd_loc_str[$language];
	$invalidField = null;
	$operationFinished = false;
	$error = false;

	$targetFolder = base64_decode($DF_ID);

	$btnIndex = getButtonIndex( array( "uploadbtn", BTN_SAVE, BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 :
				// Upload and analyze file
				//
				if ( $file['size'] == 0 ) {
					$errorStr = $ddStrings['ua_selectfile_message'];
					$invalidField = 'file';
					break;
				}

				// Upload file to the temporary dir
				//
				$tmpFileName = uniqid( TMP_FILES_PREFIX );
				$destPath = WBS_TEMP_DIR."/".$tmpFileName;
				if ( !@move_uploaded_file( $file['tmp_name'], $destPath ) ) {
					$errorStr = $kernelStrings['iul_erruploading_message'];
					
					break;
				}

				$archiveInfo = $dd_treeClass->analyzeArchive( $destPath, $kernelStrings, $ddStrings );
				if ( PEAR::isError($archiveInfo) ) {
					$errorStr = $archiveInfo->getMessage();
					break;
				}

				$filesInfo = sprintf( $ddStrings['ua_filefolders_label'], $file['name'], $archiveInfo['files'], $archiveInfo['folders'] );
				$extractSubdirs = $archiveInfo['folders'] ? 1 : 0;
				$requiredSpaceLabel = sprintf( $ddStrings['ua_requiredspace_label'], formatFileSizeStr( $archiveInfo['totalSize'] ) );

				$dataPath = sprintf( WBS_ATTACHMENTS_DIR );
				$fileCount = 0;
				$attachmentsSize = 0;

				$QuotaManager = new DiskQuotaManager();
				$spaceAvaliable = $QuotaManager->GetAvailableSpace( $currentUser, $DD_APP_ID, $kernelStrings );

				if ( !is_null($spaceAvaliable) ) {
					if ( $spaceAvaliable < $archiveInfo['totalSize'] )
						$notEnoughSpaceMessage = $ddStrings['ua_nospacerequired_note'];

					$availableSpaceLabel = sprintf( $ddStrings['ua_availablespace_label'], formatFileSizeStr( $spaceAvaliable ) );
				}

				$imagesLabel = sprintf( $ddStrings['ua_imagesfound_label'], $archiveInfo['images'] );
				if ( $archiveInfo['images'] )
					$createThumbs = true;

				$origFileName = base64_encode($file['name']);
				$destPath = base64_encode($destPath);
				$fileLoaded = true;

				break;
		case 1 :
				// Extract files
				//
				if ( !isset($extractSubdirs) )
					$extractSubdirs = 0;

				if ( !isset($createThumbs) )
					$createThumbs = 0;

				$archiveInfo = unserialize( base64_decode($archiveInfo) );
				$fileName = base64_decode($origFileName);
				$endPath = base64_decode($destPath);

				$versionControlEnabled = dd_versionControlEnabled($kernelStrings);

				if ( !$versionControlEnabled )
					$existingFileOperation = DD_REPLACE_FILES;

				$resultStats = array();
				$res = $dd_treeClass->uploadArchive( $currentUser, $targetFolder, $fileName, $endPath, $extractSubdirs, $createThumbs, $kernelStrings, $ddStrings, $resultStats, $existingFileOperation );
				
				$operationFinished = true;
				if ( PEAR::isError($res) ) {
					$statusText = $res->getMessage();
					$error = sprintf( $ddStrings['ua_partialupload_message'], $fileName );

					break;
				}
				$statusText = sprintf( $ddStrings['ua_arciveuploaded_message'], $fileName );

				break;
		case 2 : 
				redirectBrowser( PAGE_DD_CATALOG, array() );
	}

	switch (true) {
		case true :
					// Load folder name
					//
					$curFolderData = $dd_treeClass->getFolderInfo( $targetFolder, $kernelStrings );
					if ( PEAR::isError($curFolderData) ) {
						$fatalError = true;
						$errorStr = $curFolderData->getMessage();

						break;
					}

					$folderName = $curFolderData['DF_NAME'];

					// Get folder rights
					//
					$rights = $dd_treeClass->getIdentityFolderRights( $currentUser, $targetFolder, $kernelStrings );
					if ( PEAR::isError($rights) ) {
						$fatalError = true;
						$errorStr = $rights->getMessage();

						break;
					}

					$archiveSupported = dd_archiveSupported();

					// Set page defaults
					//
					if ( !isset($edited) ) {
						$extractSubdirs = true;
						$fileLoaded = false;
					}

					$hasFolderRights = UR_RightsObject::CheckMask( $rights, TREE_READWRITEFOLDER );

					// Load thumbnails creation status
					//
					$thumbnailEnabled = readApplicationSettingValue( $DD_APP_ID, DD_THUMBNAILSTATE, DD_THUMBENABLED, $kernelStrings );

					$versionControlEnabled = dd_versionControlEnabled($kernelStrings);
					if ( $versionControlEnabled )
						$existingFileOperation = DD_REPLACE_FILES;
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$preproc->assign( PAGE_TITLE, $ddStrings['ua_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_DD_UPLOADARCHIVE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "ddStrings", $ddStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "DF_ID", $DF_ID );

	if ( !$fatalError ) {
		$preproc->assign( "folderName", $folderName );
		$preproc->assign( "hasFolderRights", $hasFolderRights );
		$preproc->assign( "archiveSupported", $archiveSupported );
		$preproc->assign( "fileLoaded", $fileLoaded );
		$preproc->assign( "thumbnailEnabled", $thumbnailEnabled );
		$preproc->assign( "versionControlEnabled", $versionControlEnabled );
		if ($versionControlEnabled)
			$preproc->assign( "existingFileOperation", $existingFileOperation );

		if ( $fileLoaded && !$operationFinished ) {
			$preproc->assign( "extractSubdirs", $extractSubdirs );
			$preproc->assign( "destPath", $destPath );
			$preproc->assign( "archiveInfo", $archiveInfo ); 
			$preproc->assign( "filesInfo", $filesInfo );
			$preproc->assign( "imagesLabel", $imagesLabel );
			$preproc->assign( "origFileName", $origFileName );

			if ( isset($notEnoughSpaceMessage) )
				$preproc->assign( "notEnoughSpaceMessage", $notEnoughSpaceMessage );

			if ( isset($createThumbs) )
				$preproc->assign( "createThumbs", $createThumbs );

			$preproc->assign( "archiveInfo_packed", base64_encode(serialize($archiveInfo)) );

			$preproc->assign( "requiredSpaceLabel", $requiredSpaceLabel );
			if ( isset($availableSpaceLabel) )
				$preproc->assign( "availableSpaceLabel", $availableSpaceLabel );
		}
		if ( $operationFinished ) {
			$preproc->assign( "operationFinished", $operationFinished );
			if ( isset($availableSpaceLabel) )
				$preproc->assign( "availableSpaceLabel", $availableSpaceLabel );
			$preproc->assign( "resultStats", $resultStats );
			$preproc->assign( "archiveInfo", $archiveInfo );
			$preproc->assign( "createThumbs", $createThumbs );
			$preproc->assign( "error", $error );

			$preproc->assign( "statusText", $statusText );
		}
	}

	$preproc->display( "uploadarchive.htm" );
?>