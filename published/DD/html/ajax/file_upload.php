<?php
	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	}
	session_start();

	if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) {
		header("HTTP/1.1 500 File Upload Error");
		if (isset($_FILES["Filedata"])) {
			echo $_FILES["Filedata"]["error"];
		}
		exit(0);
	}
	
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );
	$metric = metric::getInstance();
	if (empty($currentUser)) $currentUser = $GLOBALS['HTTP_SESSION_VARS']['wbs_username'];
	$metric->addAction($DB_KEY, $currentUser, 'DD', 'UPLOAD-FLASH', 'ACCOUNT', $_FILES['Filedata']['size']);
	
	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$error = null;
	$SCR_ID = "CT";
	
	pageUserAuthorization( $SCR_ID, $DD_APP_ID, true, true);
	$folderID = @$_POST["folderID"];
	if (!$folderID) {
		header("HTTP/1.1 500 File Upload Error");
		exit(0);
	}
	/*if (!$folderID)
		$folderID = $dd_treeClass->getUserDefaultFolder( $currentUser, $kernelStrings, $readOnly );*/
	
	
	//
	// Page variables setup
	//
	
	$access = null;
	$hierarchy = null;
	$deletable = null;
	$statisticsMode = false;
	
	do {
	
		if (!$folderID) {
			$error = PEAR::raiseError("Empty folder id");
			break;
		}
			
		$kernelStrings = $loc_str[$language];
		$ddStrings = $dd_loc_str[$language];
		$invalidField = null;
		$contactCount = 0;
		
		setAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_UPLOADMETHOD', "FLASHFORM", $kernelStrings, $readOnly = false);
		
		$name = $_FILES['Filedata']['name'];
		if ( strlen($name) ) {
			if ( $_FILES['Filedata']['size'] != 0 ) {

				$tmpFileName = uniqid( TMP_FILES_PREFIX );
				$destPath = WBS_TEMP_DIR."/".$tmpFileName;
				$srcName =  $_FILES['Filedata']['tmp_name'];
				
				if ( !@move_uploaded_file( $srcName, $destPath ) )
				{
					$error = PEAR::raiseError ("", 500);
				} else {
					$fileObj = new dd_fileDescription();
					$fileObj->DL_FILENAME = getFileBaseName(stripslashes($_POST['Filename']? $_POST['Filename'] : $name));
					$fileObj->DL_FILESIZE = $_FILES['Filedata']['size'];
					$fileObj->DL_DESC = prepareStrToStore( trim( $descriptions ) );
//					$fileObj->DL_MIMETYPE = $_FILES['Filedata']['type'];
					$fileObj->DL_MIMETYPE = ($_FILES['Filedata']['type'] == 'application/octet-stream') ? 'application/force-download' : $_FILES['Filedata']['type'];
					$fileObj->sourcePath = $destPath;
					$fileList[] = $fileObj;
				}
			}
		}

		$curDF_ID = $folderID;
		$lastFile = null;
		$resultStatistics = null;
		$existingFileOperation = DD_REPLACE_FILES;
					
		$res = dd_addFiles( $fileList, $curDF_ID, $currentUser, $kernelStrings, $ddStrings, $messageStack, $lastFile, $resultStatistics, true, $existingFileOperation, $removeFilesAfterCopy = true, $fromWidget = false, $fromFlash = true );
		if (PEAR::isError($error = $res))
			break;
	} while (false);	 
	if (PEAR::isError($error)) {
		$code = $error->getUserInfo();
		if (!$code)
			$code = 500;
		header("HTTP/1.1 $code File Upload Error");
		exit;
	}
	else
		print "OK";
?>
