<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "CT";

	set_time_limit( 3600 );

	pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );

	$sortStrRedirect = array( 'DL_FILENAME'=>'DF_NAME',
								'DL_FILETYPE'=>'DF_NAME',
								'DL_FILESIZE'=>'DF_NAME',
								'DL_UPLOADDATETIME'=>'DF_CREATEDATETIME',
								'DL_UPLOADUSERNAME'=>'DF_CREATEUSERNAME',
								'DL_MODIFYDATETIME'=>'DF_MODIFYDATETIME',
								'DL_MODIFYUSERNAME'=>'DF_MODIFYUSERNAME' );

	function compareFolderSize( $a, $b )
	{
		global $sortOrder;

		if ($a->DF_SIZE == $b->DF_SIZE) return 0;

		if ( $sortOrder == 'asc' )
			return ($a->DF_SIZE < $b->DF_SIZE) ? -1 : 1;
		else
			return ($a->DF_SIZE < $b->DF_SIZE) ? 1 : -1;
	}

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

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$ddStrings = $dd_loc_str[$language];
	$invalidField = null;
	$contactCount = 0;
	$thumbnailsGenerated = false;
	$duplicateFilesFound = false;
	$zohoeditStatusChanged = false;
	
	if (!empty($_GET["actionName"]))
		$_POST[$_GET["actionName"]] = 1;

	$btnIndex = getButtonIndex( array( "restorebtn", "deletebtn", "showFiles", "showThumbnails", "disablethumbs", "enablethumbs", "updatethumbs", "showVersionControl", "enableversioncontrol", "disableversioncontrol", "changeversionoverride", "showEmailSettings", "changeemailsettings", "showFtpFolder", "copyftp_btn", "cancelbtn", "showZohoedit", "setZohoedit" ), $_POST );
	
	$commonRedirParams = array();
	$commonRedirParams[OPENER] = base64_encode(PAGE_DD_RECYCLED);

	$_ftpFolder =  new dd_ftpFolder( $DB_KEY );

	if ( !isset( $curScreen ) )
		$curScreen=0;

	switch (true)
	{
		case true :
					if ( !$_ftpFolder->isExists() && $curScreen == 4 )
					{
						$curScreen=0;
						break;
					}

					if ( $curScreen != 4 )
						break;

					if ( !isset($masterStep) )
						$masterStep = 0;

					$versionControlEnabled = dd_versionControlEnabled($kernelStrings);
	}


	switch ($btnIndex) {
		case 0 :
				if ( !isset($document) && !isset($folder) )
					break;

				if ( !isset($document) )
					$document = array();

				if ( !isset($folder) )
					$folder = array();

				$documentList = base64_encode( serialize( array_keys($document) ) );
				$folderList = base64_encode( serialize( $folder ) );

				$commonRedirParams['doclist'] = $documentList;
				$commonRedirParams['folderlist'] = $folderList;

				redirectBrowser( PAGE_DD_RESTORE, $commonRedirParams, "#" );

				break;
		case 1 :
				if ( !isset($document) && !isset($folder) )
					break;

				if ( !isset($document) )
					$document = array();

				if ( !isset($folder) )
					$folder = array();

				if ( count($folder) ) {
					foreach( $folder as $key=>$value ) {
						$res = $dd_treeClass->deleteFolder( $key, $currentUser, $kernelStrings, true,
															"dd_onDeleteFolder", array('ddStrings'=>$ddStrings, 'kernelStrings'=>$kernelStrings, 'U_ID'=>$currentUser), false );
						if ( PEAR::isError($res) )
							$errorStr = $res->getMessage();
					}
				}

				if ( count($document) ) {
					$res = dd_removeDocuments( array_keys($document), $currentUser, $kernelStrings, $ddStrings, true );
					if ( PEAR::isError($res) )
						$errorStr = $res->getMessage();
				}

				break;
		case 2 :
				$curScreen = 0;

				break;
		case 3 :
				$curScreen = 1;

				break;

		case 4 :
				$res = dd_disableThumbnails( $kernelStrings, $ddStrings );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();

					break;
				}

				break;
		case 5 :
				$res = dd_enableThumbnails( $kernelStrings, $ddStrings, $messageStack = array() );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();

					break;
				}

				break;
		case 6 :
				// Update thumbnails
				//
				$filesProcessed = dd_updateThumbnails( $kernelStrings, $ddStrings, $messageStack = array() );
				if ( PEAR::isError($filesProcessed) ) {
					$errorStr = $filesProcessed->getMessage();

					break;
				}

				$thumbnailsGenerated = true;

				break;
		case 7 :
				$curScreen = 2;

				break;
		case 8 :
				$duplicates = dd_listDuplicateFlies( $kernelStrings );
				if ( $duplicates ) {
					foreach( $duplicates as $DF_ID=>$folderFiles ) {
						foreach ( $folderFiles['files'] as $fileName=>$fileList ) {
							foreach ( $fileList as $key=>$fileData ) {
								$fileData = (object)$fileData;
								$fileData = dd_processFileListEntry( $fileData );
								$fileList[$key] = $fileData;
							}
							$folderFiles['files'][$fileName] = $fileList;
						}

						$duplicates[$DF_ID] = $folderFiles;
					}

					$duplicateFilesFound = true;
					break;
				}

				$res = dd_enableVersionControl( $kernelStrings, $ddStrings );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();

					break;
				}

				break;
		case 9 :
				writeApplicationSettingValue( $DD_APP_ID, DD_VERSIONCONTROLSTATE, DD_VCDISABLED, $kernelStrings );

				break;
		case 10 :
				redirectBrowser( PAGE_DD_SETVEROVERRIDEPARAMS, array() );

				break;
		case 11 :
				$curScreen = 3;

				break;
		case 12 :
				redirectBrowser( PAGE_DD_SETEMAILPARAMS, array() );

				break;

		case 13:	$curScreen = 4;

				break;

		case 14:
				if ( !isset($finished) )
					break;

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

				if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) )
				{
					$fatalError = true;
					$errorStr = $ddStrings['add_screen_norights_message'];
					break;
				}

				if ( $masterStep == 0 )
				{

					$ftp_files = $_ftpFolder->GetFiles();

					$fileList = array();
					$messageStack = array();

					foreach( $ftpfile as $fileIndex=>$val )
					{
						if ( isset( $ftp_files[$fileIndex]  ) )
						{
							$fileObj = new dd_fileDescription();
							$fileObj->DL_FILENAME = $ftp_files[$fileIndex]["NAME"];
							$fileObj->DL_FILESIZE = $ftp_files[$fileIndex]["SIZE"];
							$fileObj->DL_DESC = "";
							$fileObj->DL_MIMETYPE = $ftp_files[$fileIndex]["MIMETYPE"];
							$fileObj->sourcePath = $ftp_files[$fileIndex]["FULLNAME"];
							$fileList[] = $fileObj;
						}
						else
							$messageStack[] = sprintf ( $ddStrings['sv_ftp_upload_error'], base64_decode( $fileIndex ), "" );
					}

					if ( count($messageStack) )
						break;

					if ( $versionControlEnabled )
					{
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
							foreach ( $existingFiles as $key=>$value )
							{
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

					foreach ( $existingFiles as $key=>$value )
					{
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

				$ddStrings['add_screen_upload_success'] = $ddStrings['sv_ftp_add_screen_upload_success'];
				$res = dd_addFiles( $fileList, $curDF_ID, $currentUser, $kernelStrings, $ddStrings, $messageStack, $lastFile, $resultStatistics, true, $existingFileOperation, $removeFilesAfterCopy = false );

				if ( PEAR::isError($res) )
				{
					$messageStack[] = sprintf( $ddStrings['add_screen_upload_info'], $lastFile, $res->getMessage() );
					break;
				}

				if ( count( $messageStack ) )
					break;

				$masterStep = 0;
				break;

			case 15:

				$masterStep = 0;
				break;
			case 16:
				$zohoeditEnabled = dd_zohoeditEnabled($kernelStrings);
				$curScreen = 5;
				break;
			case 17:
				$zohoeditValue = $enableZohoedit ? DD_ZOHOENABLED : DD_ZOHODISABLED;
				$zohoeditEnabled = $enableZohoedit;
				$zohokeyValue = trim($zohokey);
				if ($zohoeditEnabled) {
					if (!$zohokeyValue) {
						$errorStr = $ddStrings['sv_zohoeditKey_warning'];
						break;
					} elseif (preg_match('/[^0-9a-z]+/i', $zohokeyValue)) {
						$errorStr = $ddStrings['sv_zohoeditKey_invalid_value'];
						break;
					} else {
						writeApplicationSettingValue( $DD_APP_ID, DD_ZOHOSECRETKEY, $zohokeyValue, $kernelStrings );
					}
				}
				writeApplicationSettingValue( $DD_APP_ID, DD_ZOHOEDITSTATE, $zohoeditValue, $kernelStrings );
				$messageStr = $zohoeditEnabled ? $ddStrings["sv_zohoeditstatusenabled_label"] : $ddStrings["sv_zohoeditstatusdisabled_label"];
				$zohoeditStatusChanged = true;
				break;
	}
	

	switch (true) {
		case true :
					if ( !isset($sorting) )
					{
						$sorting = getAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_RECYCLED_SORTING', null );
						if ( !strlen($sorting) )
							$sorting = "DL_FILENAME asc";
						else
							$sorting = base64_decode( $sorting );
					}
					else
					{
						setAppUserCommonValue( $DD_APP_ID, $currentUser, 'DD_RECYCLED_SORTING', $sorting, $kernelStrings );
						$sorting = base64_decode( $sorting );
					}

					// Load files
					//
					$files = $dd_treeClass->listFolderDocuments( TREE_RECYCLED_FOLDER, $currentUser, $sorting, $kernelStrings, "dd_processFileListEntry", true );
					if ( PEAR::isError($files) ) {
						$errorStr = $files->getMessage();
						$fatalError = true;

						break;
					}

					foreach ( $files as $DL_ID=>$data ) {
						$files[$DL_ID] = (array)$data;
					}

					// Load folders
					//
					$sortingData = parseSortStr($sorting);
					$field = $sortStrRedirect[$sortingData['field']];

					$sortStr = $field." ".$sortingData['order'];

					$folders = $dd_treeClass->listFoldersPlain( TREE_FSTATUS_DELETED, $sortStr, $kernelStrings );
					if ( PEAR::isError($folders) )
					{
						$errorStr = $folders->getMessage();
						$fatalError = true;

						break;
					}

					foreach( $folders as $key=>$value )
					{
						$value->DF_CREATEDATETIME = convertToDisplayDate( $value->DF_CREATEDATETIME, true );
						$value->DF_MODIFYDATETIME = convertToDisplayDate( $value->DF_MODIFYDATETIME, true );

						$folderDir = dd_getFolderDir($key);
						$space = dd_directorySize($folderDir);

						$value->DF_SIZE = $space;

						$folders[$key] = $value;
					}

					if ($sortingData['field'] == 'DL_FILESIZE')
					{
						$sortOrder  = $sortingData['order'];
						uasort( $folders, 'compareFolderSize' );
					}

					foreach( $folders as $key=>$value )
					{
						$value->DF_SIZE = formatFileSizeStr( $value->DF_SIZE );

						$folders[$key] = $value;
					}

					$totalItemCount = count($folders) + count($files);

					if ( !isset($edited) && !isset($curScreen) )
						$curScreen = 0;

					// Prepare tabs
					//
					$tabs = array();

					$checked = ($curScreen == 0) ? "checked" : "unchecked";
					$tabs[] = array( 'caption'=>$ddStrings['sv_screen_recycle_label'], 'link'=>sprintf( $processButtonTemplate, 'showFiles' )."||$checked" );

					$checked = ($curScreen == 1) ? "checked" : "unchecked";
					$tabs[] = array( 'caption'=>$ddStrings['sv_screen_thumbnail_title'], 'link'=>sprintf( $processButtonTemplate, 'showThumbnails' )."||$checked" );

					$checked = ($curScreen == 2) ? "checked" : "unchecked";
					$tabs[] = array( 'caption'=>$ddStrings['sv_versioncontrol_tab'], 'link'=>sprintf( $processButtonTemplate, 'showVersionControl' )."||$checked" );

					$checked = ($curScreen == 3) ? "checked" : "unchecked";
					$tabs[] = array( 'caption'=>$ddStrings['sv_email_tab'], 'link'=>sprintf( $processButtonTemplate, 'showEmailSettings' )."||$checked" );

					if ( $_ftpFolder->isExists() )
					{
						$checked = ($curScreen == 4) ? "checked" : "unchecked";
						$tabs[] = array( 'caption'=>$ddStrings['sv_ftp_tab'], 'link'=>sprintf( $processButtonTemplate, 'showFtpFolder' )."||$checked" );
					}

					// Load thumbnail generation system state
					//
					$thumbnailEnabled = readApplicationSettingValue( $DD_APP_ID, DD_THUMBNAILSTATE, DD_THUMBENABLED, $kernelStrings );
					if ( PEAR::isError($thumbnailEnabled) ) {
						$errorStr = $thumbnailEnabled->getMessage();
						$fatalError = true;

						break;
					}

					$thumbnailsSupported = function_exists('gd_info');

					$totalImageNum = dd_getTotalImagesNum( $kernelStrings );
					if ( PEAR::isError($totalImageNum) ) {
						$errorStr = $totalImageNum->getMessage();
						$fatalError = true;

						break;
					}

					// Load version control status
					//
					$versionControlEnabled = dd_versionControlEnabled($kernelStrings);
					if ( $versionControlEnabled ) {
						$versionOverrideEnabled = false;
						$maxVersionNum = 0;
						dd_getVersionOverrideParams( $versionOverrideEnabled, $maxVersionNum, $kernelStrings );
					}

					// Load email settings
					//
					$emailMode = null;
					$emailName=  null;
					$emailAddress = null;
					dd_getEmailSettingsParams( $emailMode, $emailName, $emailAddress, $kernelStrings );

					if ( $curScreen == 4 )
					{
						$ftp_files = $_ftpFolder->GetFiles();

						$supressChildren = false;
						$minimalRights = array( TREE_WRITEREAD, TREE_READWRITEFOLDER );
						$supressID = null;
						$showRootFolder = false;
						$suppressParent = false;

						$access = null;
						$hierarchy = null;
						$deletable = null;

						$folders = $dd_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
																$access, $hierarchy, $deletable, $minimalRights, $supressID,
																$supressChildren, $suppressParent, $showRootFolder );
						if ( PEAR::isError($folders) ) {
							$fatalError = true;
							$errorStr = $folders->getMessage();

							break;
						}

						foreach ( $folders as $fDF_ID=>$folderData )
						{
							$encodedID = base64_encode($fDF_ID);
							$folderData->curID = $encodedID;
							$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);

							$params = array();

							$folders[$fDF_ID] = $folderData;
						}
					}
	}
	
	switch ($curScreen) {
		case 0: $pageTitle = $ddStrings["sv_screen_recycle_label"]; ; break;
		case 1: $pageTitle = $ddStrings["sv_thumbnails_tab"]; break;
		case 2: $pageTitle = $ddStrings["sv_versioncontrol_tab"]; break;
		case 3: $pageTitle = $ddStrings["sv_email_tab"]; break;
		case 4: $pageTitle = $ddStrings["sv_ftp_tab"]; break;
		case 5: $pageTitle = $ddStrings["sv_onlineedit_tab"]; break;
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$preproc->assign( PAGE_TITLE, $pageTitle );
	$preproc->assign( FORM_LINK, PAGE_DD_RECYCLED );
	$preproc->assign( INVALID_FIELD, $invalidField );
	if (isset($_GET['from']) && $_GET['from'] == 'online' && !$errorStr)
		$preproc->assign( ERROR_STR, $ddStrings['sv_zohoeditKey_message'] );
	else 
		$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "ddStrings", $ddStrings );
	$preproc->assign( "genericLinkUnsorted", prepareURLStr( PAGE_DD_RECYCLED, array() ) );

	$showMessages = 0;
	if ( isset($messageStack) && count( $messageStack ) )
	{
		$preproc->assign( "messageStack", implode( "<br>", $messageStack ) );
		$showMessages = 1;
	}
	$preproc->assign( "showMessages", $showMessages );

	if ( !$fatalError ) {
		$preproc->assign( SORTING_COL, $sorting );

		$preproc->assign( "files", $files );
		$preproc->assign( "folders", $folders );
		$preproc->assign( "tabs", $tabs );
		$preproc->assign( "curScreen", $curScreen );

		$preproc->assign( "thumbnailEnabled", $thumbnailEnabled );
		$preproc->assign( "thumbnailsGenerated", $thumbnailsGenerated );
		$preproc->assign( "totalItemCount", $totalItemCount );
		$preproc->assign( "folderCount", count($folders) );

		$preproc->assign( "thumbnailsSupported", $thumbnailsSupported );
		$preproc->assign( "totalImageNum", $totalImageNum );

		if ( $duplicateFilesFound ) {
			$preproc->assign( "duplicateFilesFound", $duplicateFilesFound );
			$preproc->assign( "duplicates", $duplicates );
		}

		if ( $thumbnailsGenerated ) {
			$preproc->assign( "messageStack", $messageStack );
			$preproc->assign( "filesProcessed", $filesProcessed );
		}

		$preproc->assign( "versionControlEnabled", $versionControlEnabled );

		if ( $versionControlEnabled ) {
			$preproc->assign( "versionOverrideEnabled", $versionOverrideEnabled );
			$preproc->assign( "maxVersionNum", $maxVersionNum );
		}

		if ( isset( $ftp_files ) )
		{
			$preproc->assign( "ftp_files", $ftp_files );
			$preproc->assign( "num_ftp_files", count( $ftp_files ) );
		}

		if ( isset( $masterStep ) )
			$preproc->assign( "masterStep", $masterStep );

		if ( isset( $masterStep ) && $masterStep == 1 )
		{
			$preproc->assign( "fileList", $fileList );
			if ( isset($existingFiles) )
				$preproc->assign( "existingFiles", $existingFiles );
			$preproc->assign( "fileListPacked", $fileListPacked );
			$preproc->assign( "existingFilesPacked", $existingFilesPacked );
			$preproc->assign( "existingFileOperation", $existingFileOperation );
			$preproc->assign( "DF_ID", $DF_ID );
		}
		
		if (!isset ($zohoeditEnabled))
			$zohoeditEnabled = dd_zohoeditEnabled($kernelStrings);
		if (isset($zohoeditEnabled)) {
			$preproc->assign("zohoeditEnabled", $zohoeditEnabled);
			$preproc->assign("zohokey", dd_getzohoKey($kernelStrings));
			$preproc->assign("zohoeditStatusChanged", $zohoeditStatusChanged);
		}
		if (isset($messageStr))
			$preproc->assign("messageStr", $messageStr);


		$preproc->assign( "emailMode", $emailMode );
		$preproc->assign( "emailName", $emailName );
		$preproc->assign( "emailAddress", $emailAddress );
	}

	$preproc->display( "recycledbin.htm" );
?>