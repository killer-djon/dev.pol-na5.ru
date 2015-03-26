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

	if ( !isset($folderID) ) {
		$targetFolder = $DF_ID;
	}
	else {
		$targetFolder = $folderId;
	}
	switch ($btnIndex) {
		case 0:
					// check files size
					//
//PEAR::raiseError('Trace #1');					
					$objects = null;

					if ( $addMode == DD_CREATEARCHIVE_FILES )
						$objects = $documents;
					if ( $addMode == DD_CREATEARCHIVE_FOLDER )
						$objects = $folderId;

					$addsubfolders = 1;

					$fileNumber = 0;
					$count_of_size = 0;
					
					$bad_files = array();
					 
					$archiveSize = $dd_treeClass->prepareArchive( $currentUser, $addMode, $objects, $kernelStrings, $ddStrings, $addsubfolders, $fileNumber , $count_of_size, $bad_files);
					
					if ( PEAR::isError($archiveSize) ) {
						$errorStr = $archiveSize->getMessage();
						break;
					}
					
					$prepareArchive = true;
					$direct_start = false;
					if(empty($errorStr) && count($bad_files) == 0)
					{
						$direct_start = true;
					}
					
					$objects = null;
//PEAR::raiseError('Trace #7');
					if ( $addMode == DD_CREATEARCHIVE_FILES )
						$objects = $documents;
					if ( $addMode == DD_CREATEARCHIVE_FOLDER )
						$objects = $folderId;

					if ( !isset($addsubfolders) )
						$addsubfolders = 0;

					$fileNumber = 0;
					$archiveName = $dd_treeClass->createArchive( $currentUser, $addMode, $objects, $kernelStrings, $ddStrings, $addsubfolders, $fileNumber );
					if ( PEAR::isError($archiveName) ) {
						$errorStr = $archiveName->getMessage();
						break;
					}
					

//PEAR::raiseError('Trace #8');
					$archiveCreated = true;
					$filesAddedMessage = sprintf( $ddStrings['ca_filesadded_label'], $fileNumber );

					$archivePath = ( onWebAsystServer() ? "/tmp" : WBS_TEMP_DIR )."/".$archiveName;
					$fileSizeInBytes = filesize($archivePath);
					$fileSize = formatFileSizeStr( filesize($archivePath) );

					switch ($addMode) {
						case DD_CREATEARCHIVE_FILES : $namePrefix = "archive"; break;
						case DD_CREATEARCHIVE_FOLDER : 
//PEAR::raiseError('Trace #9');									
									$folderName = $dd_treeClass->getFolderInfo($folderId, $kernelStrings);
									if ( PEAR::isError($folderName) )
										{
										$errorStr = $folderName->getMessage();
										break;
										}
									$namePrefix = $folderName['DF_NAME']; 
									break;
						case DD_CREATEARCHIVE_ENTIRE : $namePrefix = "dd"; break;
					}
//PEAR::raiseError('Trace #10');
					if ($fileName) {
						if (mb_substr($fileName, -4) !== '.zip') {
							$fileName = sprintf("%s.zip", $fileName); 
						}
					} else {
						$fileName = sprintf( "%s%s.zip", $namePrefix, date('mdY') );
					}
					$archiveName = $archiveName;

					$edited = false;
					
						// Check required fields
					
					//
//PEAR::raiseError('Trace #3');
					$fileList = array();

					$srcPath = (onWebAsystServer() ? "/tmp" : WBS_TEMP_DIR ) ."/".$archiveName;

					// Check file name
					//
					$res = dd_checkFileName( $fileName, $ddStrings );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();
						$invalidField = 'fileName';
						break;
					}
					$fileObj = new dd_fileDescription();
					$fileObj->DL_FILENAME = prepareStrToStore( $fileName );
					$fileObj->DL_FILESIZE = $fileSizeInBytes;
					$fileObj->DL_DESC = prepareStrToStore( trim( $fileDescription ) );
					$fileObj->DL_MIMETYPE = 'application/x-zip-compressed';
					$fileObj->sourcePath = $srcPath;
					$fileList[] = $fileObj;
					

					$lastFile = null;
					$resultStatistics = null;
					$res = dd_addFiles( $fileList, $folderId, $currentUser, $kernelStrings, $ddStrings, $messageStack, $lastFile, $resultStatistics );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();
						break;
					}		
					$srcPath = (onWebAsystServer() ? "/tmp" : WBS_TEMP_DIR)."/".$archiveName;
					@unlink($srcPath);
				
	}
	 
	if ($errorStr) {
		print "{'success': false, errorStr: '$errorStr'}";
	} else {
		print "{'success': true}";
	}
	
?>