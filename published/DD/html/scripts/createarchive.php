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

	if ( !isset($folderID) )
		$targetFolder = base64_decode($DF_ID);
	else
		$targetFolder = $folderID;

	$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL, "savearchivebtn", "closebtn", "proceedbtn"), $_POST );
	

	
	switch ($btnIndex) {
		case 0:
					// check files size
					//
					
					$objects = null;

					if ( $addMode == DD_CREATEARCHIVE_FILES )
						$objects = unserialize( base64_decode($doclist) );
					if ( $addMode == DD_CREATEARCHIVE_FOLDER )
						$objects = $folderID;

					if ( !isset($addsubfolders) )
						$addsubfolders = 0;

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
					if(empty($errorStr) and count($bad_files) == 0)
						{
						$direct_start = true;
						}
					count($bad_files);	
					break;
		case 1 :
					redirectBrowser( PAGE_DD_CATALOG, array() );
		case 2 :
					// Check required fields
					//
					$requiredFields = array( 'targetID', 'fileName' );
					if ( PEAR::isError( $invalidField = findEmptyField($_POST, $requiredFields) ) ) {
						$errorStr = $kernelStrings[ERR_REQUIREDFIELDS];
						$invalidField = $invalidField->getUserInfo();

						break;
					}

					$fileList = array();

					$srcPath = WBS_TEMP_DIR."/".base64_decode($archiveName);

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
					$res = dd_addFiles( $fileList, $targetID, $currentUser, $kernelStrings, $ddStrings, $messageStack, $lastFile, $resultStatistics );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();
						break;
					}

					redirectBrowser( PAGE_DD_CATALOG, array() );
		case 3 :
					$srcPath = WBS_TEMP_DIR."/".base64_decode($archiveName);
					@unlink($srcPath);
					redirectBrowser( PAGE_DD_CATALOG, array() );
		case 4:
					$objects = null;

					if ( $addMode == DD_CREATEARCHIVE_FILES )
						$objects = unserialize( base64_decode($doclist) );
					if ( $addMode == DD_CREATEARCHIVE_FOLDER )
						$objects = $folderID;

					if ( !isset($addsubfolders) )
						$addsubfolders = 0;

					$fileNumber = 0;
					$archiveName = $dd_treeClass->createArchive( $currentUser, $addMode, $objects, $kernelStrings, $ddStrings, $addsubfolders, $fileNumber );
					if ( PEAR::isError($archiveName) ) {
						$errorStr = $archiveName->getMessage();
						break;
					}

					$archiveCreated = true;
					$filesAddedMessage = sprintf( $ddStrings['ca_filesadded_label'], $fileNumber );

					$archivePath = WBS_TEMP_DIR."/".$archiveName;
					$fileSizeInBytes = filesize($archivePath);
					$fileSize = formatFileSizeStr( filesize($archivePath) );

					switch ($addMode) {
						case DD_CREATEARCHIVE_FILES : $namePrefix = "archive"; break;
						case DD_CREATEARCHIVE_FOLDER : 
									
									$folderName = $dd_treeClass->getFolderInfo($folderID, $kernelStrings);
									if ( PEAR::isError($folderName) )
										{
										$errorStr = $folderName->getMessage();
										break;
										}
									$namePrefix = $folderName['DF_NAME']; 
									break;
						case DD_CREATEARCHIVE_ENTIRE : $namePrefix = "dd"; break;
					}

					$fileName = sprintf( "%s%s.zip", $namePrefix, date('mdY') );
					$archiveName = base64_encode($archiveName);

					$downloadArchiveLink = prepareURLStr( PAGE_DD_GETARCHIVE, array('file'=>$archiveName, 'fileName'=>base64_encode($fileName)) );
					$edited = false;

					break;
	}

	switch (true) {
		case true : 
					if ( isset($archiveCreated) )
						$minimalRights = array( TREE_ONLYREAD, TREE_WRITEREAD );
					else
						$minimalRights = null;

					$access = null;
					$hierarchy = null;
					$deletable = null;
					$folders = $dd_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false, 
														$access, $hierarchy, $deletable, $minimalRights, null,
														false, null );
					if ( PEAR::isError($folders) ) {
						$errorStr = $folders->getMessage();
						$fatalError = true;
						break;
					}

					foreach ( $folders as $fDF_ID=>$folderData ) {
						$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);
						$folderData->curID = $fDF_ID;
						$folders[$fDF_ID] = $folderData;
					}

					$documents = unserialize( base64_decode( $doclist ) );
					$selectedDocsNum = count($documents);

					if ( !isset($edited) )
						if ( $selectedDocsNum )
							$addMode = DD_CREATEARCHIVE_FILES;
						else
							if ( $targetFolder != TREE_AVAILABLE_FOLDERS )
								$addMode = DD_CREATEARCHIVE_FOLDER;
							else
								$addMode = DD_CREATEARCHIVE_ENTIRE;

					if ( !isset($addsubfolders) )
						$addsubfolders = 0;

					$archiveSupported = dd_archiveSupported();
					
					if ($prepareArchive and $count_of_size > DD_LIMIT_DOWNLOAD_SIZE) 
						{
							$direct_start = false;
							$accessDenied = true;
						}
					else	
						{
						// calculation time for archive
						//
						$accessDenied = false;
						} 
					
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$preproc->assign( PAGE_TITLE, $ddStrings['ca_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_DD_CREATEARCHIVE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "ddStrings", $ddStrings );
	$preproc->assign( "DF_ID", $DF_ID );

	if ( !$fatalError ) {
		$preproc->assign( "hierarchy", $hierarchy );
		$preproc->assign( "folders", $folders );
		$preproc->assign( "doclist", $doclist );
		$preproc->assign( "folderID", $targetFolder );
		$preproc->assign( "selectedDocsNum", $selectedDocsNum );
		$preproc->assign( "addsubfolders", $addsubfolders );
		$preproc->assign( "archiveSupported", $archiveSupported );
		$preproc->assign( "addMode", $addMode );
		$preproc->assign( "fileNumber", $fileNumber );
		
		$preproc->assign( "filesizelimit", formatFileSizeStr(DD_FILE_LIMIT) );
		$preproc->assign( "allowSize", formatFileSizeStr(DD_LIMIT_DOWNLOAD_SIZE) );
		$preproc->assign( "activeFile", session_id() );
		
		$preproc->assign( "ca_creating_note", sprintf($ddStrings['ca_creating_note'],formatFileSizeStr(DD_FILE_LIMIT),formatFileSizeStr(DD_LIMIT_DOWNLOAD_SIZE)) );
		
		if ( isset($archiveCreated) ) {
			$preproc->assign( "archiveCreated", $archiveCreated );
			if ( $archiveCreated ) {
				$preproc->assign( "archiveName", $archiveName );
				$preproc->assign( "fileSize", $fileSize );
				
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
		elseif($prepareArchive)
			{
			$preproc->assign( "bad_files", $bad_files );
			$preproc->assign( "direct_start", $direct_start );
			$preproc->assign( "prepareArchive", $prepareArchive );
			$preproc->assign( "archiveSize", formatFileSizeStr($count_of_size)  );
			$preproc->assign( "accessDenied", $accessDenied );
			
			}
		
	}

	if ($preproc->get_template_vars('ajaxAccess')) {
		 
		require_once( "../../../common/html/includes/ajax.php" );
		$preproc->assign( 'ajax', 1 );
		$ajaxRes = array ();
		$ajaxRes["mainContent"] = $preproc->fetch( "createarchive_process.htm" );
		print simple_ajax_encode($ajaxRes);
		exit;
	}
	
	$preproc->display( "createarchive.htm" ); 
?>