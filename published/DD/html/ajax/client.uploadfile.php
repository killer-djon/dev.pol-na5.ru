<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$error = null;
	$SCR_ID = "CT";

	pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );
	
	//
	// Page variables setup
	//
	
	$access = null;
	$hierarchy = null;
	$deletable = null;
	$statisticsMode = false;
	/*$folders = $dd_treeClass->listFolders( $currentUser, "ROOT", $kernelStrings, 0, false,
															$access, $hierarchy, $deletable,
															null, null, false, null, true, null, $statisticsMode );
	
	$folderID = null;
	$keys = array_keys($hierarchy["AVAILABLEFOLDERS"]);
	foreach ($keys as $cKey) {
		$cFolder = $folders[$cKey];
		if ($cFolder->RIGHT >= 7) {
			$folderID = $cFolder->DF_ID;
			break;
		}
	}*/
	
	do {
	
		if (!$folderID) {
			$error = PEAR::raiseError("Empty folder id");
			break;
		}
			
		$kernelStrings = $loc_str[$language];
		$ddStrings = $dd_loc_str[$language];
		$invalidField = null;
		$contactCount = 0;
		
		$name = $_FILES['file']['name'];
		if ( strlen($name) ) {
			if ( $_FILES['file']['size'] != 0 ) {

				$tmpFileName = uniqid( TMP_FILES_PREFIX );
				$destPath = WBS_TEMP_DIR."/".$tmpFileName;
				$srcName =  $_FILES['file']['tmp_name'];
				
				if ( !@move_uploaded_file( $srcName, $destPath ) )
				{
					$messageStack[] = sprintf ( $ddStrings['add_screen_upload_error'], $_FILES['file']['name'], $ddStrings['app_copyerr_message'] );
				} else {
					$fileObj = new dd_fileDescription();
					$fileObj->DL_FILENAME = getFileBaseName($name);
					$fileObj->DL_FILESIZE = $_FILES['file']['size'];
					$fileObj->DL_DESC = prepareStrToStore( trim( $descriptions ) );
					$fileObj->DL_MIMETYPE = $_FILES['file']['type'];
					$fileObj->sourcePath = $destPath;
					$fileList[] = $fileObj;
				}
			}
			else
			{
				$messageStack[] = sprintf ( $ddStrings['add_screen_zero_size'], $_FILES['file']['name'], $ddStrings[$dd_uploadErrors[$_FILES['file']['error']]] );
			}
		}
		
		$curDF_ID = $folderID;
		$lastFile = null;
		$resultStatistics = null;
		$existingFileOperation = DD_SKIP_FILES;
					
		$res = dd_addFiles( $fileList, $curDF_ID, $currentUser, $kernelStrings, $ddStrings, $messageStack, $lastFile, $resultStatistics, true, $existingFileOperation );
		if (PEAR::isError($error = $res))
			break;
	} while (false);	
	
	
	if (PEAR::isError($error)) {
		$ajaxRes["success"] = false;
		$ajaxRes["errorStr"] = $error->getMessage ();
	} else {
		$ajaxRes["success"] = true;
	}	
	
	print $json->encode ($ajaxRes);	
?>