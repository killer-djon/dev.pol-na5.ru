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



	switch (true) {
		case true :
				
				$fileData = dd_getDocumentData( $DL_ID, $kernelStrings );
				$targetFolder = $DF_ID = $fileData->DF_ID;
				$diskFileName = $fileData->DL_DISKFILENAME;
				if( $fileData->DL_STATUSINT == TREE_DLSTATUS_NORMAL ) {
					$destPath = dd_getFolderDir( $fileData->DF_ID )."/".$diskFileName;
				}
				elseif ( $fileData->DL_STATUSINT == TREE_DLSTATUS_DELETED ) {
					$destPath = dd_recycledDir()."/".$diskFileName;
				}
				
				$archiveInfo = $dd_treeClass->analyzeArchive( $destPath, $kernelStrings, $ddStrings );
				if ( PEAR::isError($archiveInfo) ) {
					$errorStr = $archiveInfo->getMessage();
					break;
				}

					// Get folder rights
				//
				$rights = $dd_treeClass->getIdentityFolderRights( $currentUser, $targetFolder, $kernelStrings );
				if ( PEAR::isError($rights) ) {
					$fatalError = true;
					$errorStr = $rights->getMessage();

					break;
				}				
				$hasFolderRights = UR_RightsObject::CheckMask( $rights, TREE_READWRITEFOLDER );
				if ($archiveInfo['folders'] && !$hasFolderRights) {
					$fatalError = true;
					$errorStr = $ddStrings['ua_nofolderrights_message'];
					break;
				}
				
				$filesInfo = sprintf( $ddStrings['ua_filefolders_label'], $fileData->DL_FILENAME, $archiveInfo['files'], $archiveInfo['folders'] );
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
				if ( $archiveInfo['images'] ) {
					$createThumbs = true;
				} else {
					$createThumbs = false;
				}
				$fileLoaded = true;


				
				// Extract files
				//
				//$extractSubdirs = true;
				$thumbnailEnabled = readApplicationSettingValue( $DD_APP_ID, DD_THUMBNAILSTATE, DD_THUMBENABLED, $kernelStrings );
				$createThumbs = $createThumbs && $thumbnailEnabled;

				$archiveInfo = $archiveInfo;
				$fileName = $fileData->DL_FILENAME;
				$endPath = $destPath;

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

					// Load folder name
					//
					$curFolderData = $dd_treeClass->getFolderInfo( $targetFolder, $kernelStrings );
					if ( PEAR::isError($curFolderData) ) {
						$fatalError = true;
						$errorStr = $curFolderData->getMessage();

						break;
					}

					$folderName = $curFolderData['DF_NAME'];

					$archiveSupported = dd_archiveSupported();

					// Set page defaults
					//
					if ( !isset($edited) ) {
						$extractSubdirs = true;
						$fileLoaded = false;
					}
	}

	if ($errorStr) {
		print "{'success': false, errorStr: '$errorStr'}";
	} else {
		print "{'success': true}";
	}
?>