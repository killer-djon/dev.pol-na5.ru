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
	$contactCount = 0;

	function getInputFileByname( $fileList, $fileName, &$index )
	{
		$fileName = strtoupper( trim($fileName) );

		foreach ( $fileList as $key=>$fileData ) {
			$curFileName = strtoupper( trim($fileData->DL_FILENAME) );

			if ( $fileName == $curFileName ) {
				$index = $key;
				return $fileData;
			}
		}

		return null;
	}

	switch (true) {
		case true :
					$curDF_ID = base64_decode( $DF_ID );

					$folderInfo = $dd_treeClass->getFolderInfo( $curDF_ID, $kernelStrings );
					if ( PEAR::isError($folderInfo) ) {
						$fatalError = true;
						$errorStr = $folderInfo->getMessage();

						break;
					}

					$rights = $dd_treeClass->getIdentityFolderRights( $currentUser, $curDF_ID, $kernelStrings );
					if ( PEAR::isError($rights) ) {
						$fatalError = true;
						$errorStr = $rights->getMessage();

						break;
					}

					if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) ) {
						$fatalError = true;
						$errorStr = $ddStrings['add_screen_norights_message'];

						break;
					}

					if ( !isset($masterStep) )
						$masterStep = 0;

					$versionControlEnabled = dd_versionControlEnabled($kernelStrings);
	}

	$btnIndex = getButtonIndex( array( BTN_CANCEL, "addfilebtn" ), $_POST );

	switch ($btnIndex) {
		case 1 :
				if ( !isset($finished) )
					break;
		
				setAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_UPLOADMETHOD', "PAGE", $kernelStrings, $readOnly = false);

				if ( $masterStep == 0 ) {
					// Move uploaded files to the temporary directory and prepare file list
					//
					$fileList = array();
					$messageStack = array();

					foreach( $_FILES['files']['name'] as $fileIndex=>$name )
						if ( strlen($name) ) {
							if ( $_FILES['files']['size'][$fileIndex] != 0 ) {

								$tmpFileName = uniqid( TMP_FILES_PREFIX );
								$destPath = WBS_TEMP_DIR."/".$tmpFileName;
								$srcName =  $_FILES['files']['tmp_name'][$fileIndex];

								if ( !@move_uploaded_file( $srcName, $destPath ) )
								{
									$messageStack[] = sprintf ( $ddStrings['add_screen_upload_error'], $_FILES['files']['name'][$fileIndex], $ddStrings['app_copyerr_message'] );

									break;
								}
								$fileObj = new dd_fileDescription();
								$fileObj->DL_FILENAME = getFileBaseName($name);
								$fileObj->DL_FILESIZE = $_FILES['files']['size'][$fileIndex];
								$fileObj->DL_DESC = prepareStrToStore( trim( $descriptions[$fileIndex] ) );
								$fileObj->DL_MIMETYPE = $_FILES['files']['type'][$fileIndex];
								$fileObj->sourcePath = $destPath;
								$fileList[] = $fileObj;
							}
							else
							{
								$messageStack[] = sprintf ( $ddStrings['add_screen_zero_size'], $_FILES['files']['name'][$fileIndex], $ddStrings[$dd_uploadErrors[$_FILES['files']['error'][$fileIndex]]] );
							}
						}

					if ( count($messageStack) )
						break;

					if ( $versionControlEnabled ) {

						// Check if any files already exists
						//
						$filesFound = false;
						$existingFiles = dd_checkFilesExistence( $fileList, $curDF_ID, $currentUser, $kernelStrings, $ddStrings, $filesFound );
						if ( PEAR::isError($existingFiles) ) {
							$errorStr = $existingFiles->getMessage();
							break;
						}

						if ( $filesFound ) {
							// Prepare file list to display
							//
							foreach ( $existingFiles as $key=>$value ) {
								$inputFileData = getInputFileByname( $fileList, $value['DL_FILENAME'], $fileIndex );

								$value['DL_CHECKDATETIME'] = convertToDisplayDateTime($value['DL_CHECKDATETIME'], false, true, true );
								$value['DL_CHECKUSERID'] = dd_getUserName($value['DL_CHECKUSERID']);

								if ( !is_null($inputFileData) )
									$value['DL_VERSIONCOMMENT'] = substr($inputFileData->DL_DESC, 0, 255);
								else
									$value['DL_VERSIONCOMMENT'] = null;

								$existingFiles[$key] = $value;
							}

							$fileListPacked = base64_encode( serialize($fileList) );
							$existingFilesPacked = base64_encode( serialize($existingFiles) );
							$masterStep = 1;

							$existingFileOperation = DD_REPLACE_FILES;
							break;
						}
					}
				}

				if ( $masterStep == 1 )
				{
					$fileList = unserialize( base64_decode($fileListPacked) );
					$existingFiles = unserialize( base64_decode($existingFilesPacked) );

					foreach ( $existingFiles as $key=>$value ) {
						$inputFileInfo = getInputFileByname( $fileList, $value['DL_FILENAME'], $fileIndex );

						if ( array_key_exists($key, $DL_VERSIONCOMMENT) )
							$inputFileInfo->DL_VERSIONCOMMENT = prepareStrToStore($DL_VERSIONCOMMENT[$key]);

						$fileList[$fileIndex] = $inputFileInfo;
					}

					$messageStack = array();
				} else
					$existingFileOperation = DD_SKIP_FILES;

				$lastFile = null;
				$resultStatistics = null;
				
				$metric = metric::getInstance();
				$fTotal = sizeof($fileList);
				for($_i=0;$_i<$fTotal;$_i++)
					$fLength+=$fileList[$_i]->DL_FILESIZE;
				$metric->addAction($DB_KEY, $currentUser, 'DD', 'UPLOAD-STD', 'ACCOUNT', $fLength);

				$res = dd_addFiles( $fileList, $curDF_ID, $currentUser, $kernelStrings, $ddStrings, $messageStack, $lastFile, $resultStatistics, true, $existingFileOperation );
				if ( PEAR::isError($res) ) {
					$message = sprintf( $ddStrings['add_screen_upload_info'], $lastFile, $res->getMessage() );
					if ( substr($message, strlen($message)-1) !== '.' )
						$message .= '.';
					$messageStack[] = $message;
					break;
				}

				if ( count( $messageStack ) )
					break;

		case 0 :
				if ( isset($fileListPacked) ) {
					$fileList = unserialize( base64_decode($fileListPacked) );

					foreach ( $fileList as $fileDescription )
						if ( file_exists($fileDescription->sourcePath) )
							@unlink($fileDescription->sourcePath);
				}

				redirectBrowser( PAGE_DD_CATALOG, array() );
	}
	$newUploaderLink = prepareURLStr( PAGE_DD_CATALOG, array ("uploadFiles" => true) );

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$showMessages = 0;
	if ( isset($messageStack) && count( $messageStack ) )
	{
		$preproc->assign( "messageStack", implode( "<br>", $messageStack ) );
		$showMessages = 1;
	}
	$preproc->assign( "showMessages", $showMessages );

	$preproc->assign( PAGE_TITLE, $ddStrings['add_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_DD_ADDFILE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( 'DF_ID', $DF_ID );
	$preproc->assign( "ddStrings", $ddStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "newUploaderLink", $newUploaderLink);

	if ( !$fatalError ) {
		$preproc->assign( "folderInfo", $folderInfo );

		$fileSections = array();

		for ( $i = 0; $i <= 99; $i++ ) $fileSections[] = $i;
		$preproc->assign( "fileSections", $fileSections );

		$preproc->assign( "sectionCount", count($fileSections) );

		$preproc->assign( "limitStr", nl2br( getUploadLimitInfoStr($kernelStrings, true) ) );

		$preproc->assign( "masterStep", $masterStep );

		if ( $masterStep == 1 ) {
			$preproc->assign( "fileList", $fileList );
			if ( isset($existingFiles) )
				$preproc->assign( "existingFiles", $existingFiles );
			$preproc->assign( "fileListPacked", $fileListPacked );
			$preproc->assign( "existingFilesPacked", $existingFilesPacked );
			$preproc->assign( "existingFileOperation", $existingFileOperation );
		}

	}

	$preproc->display( "addfile.htm" );
?>
