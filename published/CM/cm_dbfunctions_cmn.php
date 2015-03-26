<?php

	//
	// Contact Manager DMBS-independent application functions
	//

	function cm_getViewOptions( $U_ID, $folderID, &$visibleColumns, &$viewMode, &$sorting, &$recordsPerPage, &$showSharedPanel, &$imgFieldsViewMode,
								&$folderViewMode, &$listViewImage, &$kernelStrings, $useCookies, $actualColumns, $objectType = CM_OT_FOLDERS )
	//
	//	Returns view options for specified user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$folderID - contact folder ID
	//			$visibleColumns - array of visible columns
	//			$viewMode - view mode (CM_GRID_VIEW, CM_LIST_VIEW)
	//			$sorting - sorting column
	//			$recordsPerPage - number of files on one page
	//			$showSharedPanel - show shared panel in Document Depot window
	//			$imgFieldsViewMode - image fields view mode
	//			$folderViewMode - folder view mode
	//			$listViewImage - list view image ID
	//			$kernelStrings - Kernel localization strings
	//			$useCookies - use cookies instead of database
	//			$actualColumns - return actual visible columns list stored in DB, regardless current view mode
	//			$objectType - Contact Manager object type
	//
	//		Returns null
	//
	{
		global $CM_APP_ID;
		global $cm_defaultColumnSet;
		global $cm_listColumnSet;
		global $UR_Manager;

		$typeDescription = getContactTypeDescription( CONTACT_BASIC_TYPE, LANG_ENG, $kernelStrings, false );
		$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDescription, $kernelStrings, true );

		$visibleColumns = getAppUserCommonValue( $CM_APP_ID, $U_ID, 'CM_VISIBLECOLUMNS', null, $useCookies );

		if ( $visibleColumns === 0 || !strlen($visibleColumns) && $visibleColumns != UL_NOCOLUMNS )
			$visibleColumns = $cm_defaultColumnSet;
		else
			if ( $visibleColumns != UL_NOCOLUMNS )
				$visibleColumns = explode( ",", $visibleColumns );
			else
				$visibleColumns = array();

		$folderViewMode = getAppUserCommonValue( $CM_APP_ID, $U_ID, 'CM_FOLDEVIEWMODE', null, $useCookies );
		if ( !strlen($folderViewMode) )
			$folderViewMode = CM_FLDVIEW_LOCAL;

		$viewMode = null;
		cm_getLocalViewSettings( $U_ID, $folderID, $viewMode, $objectType );

		//$viewMode = getAppUserCommonValue( $CM_APP_ID, $U_ID, 'CM_VIEWMODE', null, $useCookies );

		if ( !strlen($viewMode) )
			$viewMode = CM_GRID_VIEW;

		if ( $viewMode == CM_LIST_VIEW && !$actualColumns )
			$visibleColumns = $cm_listColumnSet;

		$existingColumns = array();
		foreach ( $visibleColumns as $col_id ) {
			if ( array_key_exists($col_id, $fieldsPlainDesc) )
				$existingColumns[] = $col_id;
		}

		$visibleColumns = $existingColumns;

		$listViewImage = getAppUserCommonValue( $CM_APP_ID, $U_ID, 'CM_LISTVIEWIMG', null, $useCookies );
		if ( !strlen($listViewImage) )
			$listViewImage = CM_LISTVIEW_NOIMG;

		$recordsPerPage = getAppUserCommonValue( $CM_APP_ID, $U_ID, 'CM_RECORDPERPAGE', null, $useCookies );
		if ( !strlen($recordsPerPage) )
			$recordsPerPage = 30;

		$imgFieldsViewMode = getAppUserCommonValue( $CM_APP_ID, $U_ID, 'CM_IMAGEVIEWMODE', null, $useCookies );
		if ( !strlen($imgFieldsViewMode) )
			$imgFieldsViewMode = CM_IMAGESVIEW_THUMBNAILS;

		$showSharedPanel = $UR_Manager->GetUserRightValue( $U_ID, "/ROOT/CM/FOLDERS/VIEWSHARES" ) == UR_BOOL_TRUE;

		$sorting = getAppUserCommonValue( $CM_APP_ID, $U_ID, 'CM_CM_SORTING'.$objectType, null, $useCookies );
		if ( !strlen($sorting) )
			$sorting = sprintf( "%s asc", CONTACT_NAMEFIELD );
		else {
			$sortData = parseSortStr($sorting);

			if ( !in_array($sortData['field'], $visibleColumns) && $sortData['field'] != 'CF_NAME' && $sortData['field'] != 'U_ID' )
				$sorting = sprintf( "%s asc", CONTACT_NAMEFIELD );
		}

		return null;
	}

	function cm_getLocalViewSettings( $U_ID, $folderID, &$viewMode, $objectType )
	//
	// Returns folder view settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$folderID - folder identifier
	//			$viewMode - view mode (DD_GRID_VIEW, DD_LIST_VIEW, DD_THUMBLIST_VIEW)
	//			$objectType - Contact Manager object type
	//
	//		Returns null or PEAR_Error
	//
	{
		global $CM_APP_ID;

		if ( is_null($folderID) )
			return null;

		$folderID = ($objectType == CM_OT_FOLDERS) ? $folderID : $objectType.$folderID;

		$folderViewMode = getAppUserCommonValue( $CM_APP_ID, $U_ID, 'CM_FOLDEVIEWMODE', null, false );
		if ( !strlen($folderViewMode) )
			$folderViewMode = CM_FLDVIEW_LOCAL;
					
		if ($folderViewMode == CM_FLDVIEW_LOCAL) {
			$viewMode = getAppUserCommonValue($CM_APP_ID, $U_ID, "FOLDERSVIEW_".$folderID);
		} else {
			$viewMode = getAppUserCommonValue($CM_APP_ID, $U_ID, "FOLDERSVIEW");
		}
		
		
		return null;
	}

	function cm_setFolderViewSettings( $U_ID, $folderID, $viewMode, &$kernelStrings, $objectType )
	//
	// Sets folder view settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$folderID - folder identifier
	//			$viewMode - folder view mode (grid, list)
	//			$kernelStrings - Kernel localization strings
	//			$objectType - Contact Manager object type
	//
	//
	{
		global $CM_APP_ID;

		$folderViewMode = getAppUserCommonValue( $CM_APP_ID, $U_ID, 'CM_FOLDEVIEWMODE', null, false );
		if ( !strlen($folderViewMode) )
			$folderViewMode = CM_FLDVIEW_LOCAL;

		// Set local view settings
		//
		cm_applyLocalViewSettings( $U_ID, $folderID, $folderViewMode, $viewMode, $kernelStrings, $objectType );
	}

	function cm_applyLocalViewSettings( $U_ID, $folderID, $folderViewMode, $viewMode, &$kernelStrings, $objectType )
	//
	// Applies local view settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$folderID - folder identifier
	//			$folderViewMode - folder view mode (CM_FLDVIEW_GLOBAL, CM_FLDVIEW_LOCAL)
	//			$viewMode - view mode (CM_GRID_VIEW, CM_LIST_VIEW)
	//			$kernelStrings - Kernel localization strings
	//			$objectType - Contact Manager object type
	//
	//		Returns null or PEAR_Error
	//
	{
		global $CM_APP_ID;
		global $cm_groupClass;

		

		$folderID = ($objectType == CM_OT_FOLDERS) ? $folderID : $objectType.$folderID;

		// Apply settings to the $folderID folder
		//
		if ( $folderViewMode == CM_FLDVIEW_LOCAL ) {
			if ( !is_null($folderID) ) {
				setAppUserCommonValue($CM_APP_ID, $U_ID, "FOLDERSVIEW_".$folderID, $viewMode, $kernelStrings);
			}
		}

		// Apply settings to all other folders
		//
		if ( $folderViewMode == CM_FLDVIEW_GLOBAL ) {
				setAppUserCommonValue($CM_APP_ID, $U_ID, "FOLDERSVIEW", $viewMode, $kernelStrings);
		}

		return null;
	}

	function cm_setObjectTypeViewSettings( &$folders, &$xpath, &$dom, &$foldersViewNode, $viewMode, $objectType )
	//
	// Saves the view settings for the Contact Manager object type. Helper function for the cm_applyLocalViewSettings function
	//
	{
		foreach ( $folders as $ID=>$data ) {
			$ID = ($objectType == CM_OT_FOLDERS) ? $ID : $objectType.$ID;

			$folderElement = &xpath_eval( $xpath, "FOLDER[@ID='$ID']", $foldersViewNode );

			// Create element for folder if it doesn't exists already
			//
			if ( !count($folderElement->nodeset) ) {
				$folderNode = @create_addElement( $dom, $foldersViewNode, 'FOLDER' );
				$folderNode->set_attribute( 'ID', $ID );
			} else
				$folderNode = $folderElement->nodeset[0];

			if ( !is_null($viewMode) )
				$folderNode->set_attribute( 'VIEWMODE', $viewMode );
		}
	}

	function cm_deleteFolderVewSettings( $folderID, &$kernelStrings )
	//
	// Deletes folder local view settings
	//
	//		Parameters:
	//			$folderID - folder identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $cm_groupClass;

		// Get folder user list
		//
		$users = $cm_groupClass->listFolderUsers( $folderID, $kernelStrings );
		if ( PEAR::isError($users) )
			return $users;

		// Delete view settings for each user
		//
		foreach ( $users as $key=>$value )
			cm_deleteUserFolderViewSettings( $key, $folderID, $kernelStrings );

		return null;
	}

	function cm_deleteUserFolderViewSettings( $U_ID, $folderID, &$kernelStrings )
	//
	// Deletes user folder view settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$folderID - folder identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $CM_APP_ID;

		$settingsElement = getUserSettingsRoot( $U_ID, $dom );
		if ( !$settingsElement )
			return $kernelStrings[ERR_XML];

		$result = array();

		$appNode = getElementByTagname( $settingsElement, $CM_APP_ID );
		if ( !$appNode )
			return null;

		$xpath = xpath_new_context($dom);

		$foldersViewNode = getElementByTagname( $appNode, 'FOLDERSVIEW' );
		if ( !$foldersViewNode )
			return null;

		$folderElement = &xpath_eval( $xpath, "FOLDER[@ID='$folderID']", $foldersViewNode );
		if ( !$folderElement || !count($folderElement->nodeset)  )
			return null;

		$folder = $folderElement->nodeset[0];
		$folder->unlink_node();

		$res = saveUserSettingsDOM( $U_ID, $dom, $settingsElement, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		return null;
	}

	function cm_onAfterCopyMoveContact( &$kernelStrings, $U_ID, $contactData, $operation, $params )
	//
	//	Completes contact copy/move process
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$contactData - contact data, record from ADDRESSBOOK table as array
	//			$operation - operation: TREE_COPYDOC, TREE_MOVEDOC
	//			$params - other parameters array
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_cm_insertContact;
		global $qr_cm_updatConcactLocation;
		global $_cmQuotaManager;
		global $CM_APP_ID;

		extract( $params );

		if ( $operation == TREE_COPYDOC ) {
			$contactData['C_MODIFYUSERNAME'] = getUserName( $U_ID, true );
			$contactData['C_MODIFYDATETIME'] = convertToSqlDateTime( time() );
			$contactData['C_STATUS'] = TREE_DLSTATUS_NORMAL;

			// Copy image field files
			//
			$filesPath = getContactsAttachmentsDir();

			foreach ( $fieldsPlainDesc as $fieldId=>$fieldData ) {
				if ( $fieldData[CONTACT_FIELD_TYPE] == CONTACT_FT_IMAGE && isset($contactData[$fieldId]) ) {
					$imgProperties = getContactImageFieldPropertieis( $contactData[$fieldId] );

					if ( strlen(base64_decode($imgProperties[CONTACT_IMGF_DISKFILENAME])) ) {
						$destFileName = uniqid(CONTACT_IMG_FILEPREFIX);

						$srcFilePath = $filesPath."/".base64_decode($imgProperties[CONTACT_IMGF_DISKFILENAME]);
						$destFilePath = $filesPath."/".$destFileName;

						$fileSize = 0;
						if ( file_exists($srcFilePath) ) {
							$TotalUsedSpace += $_cmQuotaManager->GetSpaceUsageAdded();
							$fileSize = filesize($srcFilePath);

							// Check if the user disk space quota is not exceeded
							//
							if ( $_cmQuotaManager->SystemQuotaExceeded($TotalUsedSpace + $fileSize) )
								return $_cmQuotaManager->ThrowNoSpaceError( $kernelStrings );
						}

						if ( @copy($srcFilePath, $destFilePath) )
							$_cmQuotaManager->AddDiskUsageRecord( SYS_USER_ID, $CM_APP_ID, $fileSize );

						$ext = null;
						$srcThumbFile = findThumbnailFile( $srcFilePath, $ext);
						if ( $srcThumbFile ) {
							$destThumbFile = $destFilePath.".$ext";

							if ( @copy($srcThumbFile, $destThumbFile) ) {
								$fileSize = filesize($destThumbFile);
								$_cmQuotaManager->AddDiskUsageRecord( SYS_USER_ID, $CM_APP_ID, $fileSize );
							}
						}

						$imgProperties[CONTACT_IMGF_FILENAME] = base64_encode( $imgProperties[CONTACT_IMGF_FILENAME] );
						$imgProperties[CONTACT_IMGF_DISKFILENAME] = base64_encode( $destFileName );
					}

					$contactData[$fieldId] = $imgProperties;
				} elseif ( $fieldData[CONTACT_FIELD_TYPE] == CONTACT_FT_DATE ) {
					// Convert date fields to display format
					//
					$contactData[$fieldId] = convertToDisplayDateNT($contactData[$fieldId]);
				}
			}

			$res = addmodContact( $contactData,$contactData['CF_ID'], ACTION_NEW, $kernelStrings, false, true );
			if ( PEAR::isError($res) )
				return $res;
		} else {
			$contactData['C_MODIFYUSERNAME'] = getUserName( $U_ID, true );
			$contactData['C_MODIFYDATETIME'] = convertToSqlDateTime( time() );

			$res = db_query( $qr_cm_updatConcactLocation, $contactData );
			if ( PEAR::isError($res) )
				return $res;
		}

		return null;
	}
	
	function cm_onCreateFolder ($CF_ID, $params) {
		global $qr_cm_updateFolderWidgets;
		extract ($params);
		if (isset($deletedFolderData) && is_array($deletedFolderData)) {
			$wgUpdateParams = array ("OLD_FOLDER_ID" =>  $deletedFolderData["CF_ID"], "NEW_FOLDER_ID" => $CF_ID);
			$res = db_query( $qr_cm_updateFolderWidgets, $wgUpdateParams );
		}
		
		return $null;
	}


	function cm_restoreUserContact( $CF_ID, $C_ID, &$kernelStrings )
	//
	// Restores user contact
	//
	//		Parameters:
	//			$C_ID - contact identofier
	//			$CF_ID - folder identifier
	//			$kernelStrings - kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_cm_updateContactFolder;

		$U_ID = getContactUser( $C_ID, $kernelStrings );
		if ( PEAR::isError($U_ID) )
			return $U_ID;

		if ( is_null($U_ID) )
			return;

		$res = setUserActivityStatus( $U_ID, RS_ACTIVE, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		$params = array( 'C_ID'=>$C_ID, 'CF_ID'=>$CF_ID );
		$res = db_query( $qr_cm_updateContactFolder, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $res;
	}

	function cm_deleteContacts( $docList, $U_ID, &$kernelStrings, $cmStrings, $language, $stopOnError = true )
	//
	// Deletes multiple contacts
	//
	//		Parameters:
	//			$docList - list of contacts to delete
	//			$U_ID - user identifier
	//			$kernelStrings - kernel localization strings
	//			$cmStrings - Contact Manager localization strings
	//			$language - user language
	//			$stopOnError - stop execution on error
	//
	//		Returns null or PEAR_Error
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;

		// Load type description
		//
		$typeDesc = getContactTypeDescription( CONTACT_BASIC_TYPE, LANG_ENG, $kernelStrings, false );
		if ( PEAR::isError($typeDesc) )
			return $typeDesc;

		// Obtain columns descriptions as a plain array
		//
		$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDesc, $kernelStrings, true );

		foreach ( $docList as $C_ID ) {
			$res = aa_deleteContact( $C_ID, $U_ID, $kernelStrings, $cmStrings, $language, $fieldsPlainDesc );

			if ( PEAR::isError($res) && $stopOnError ) {
				return PEAR::raiseError( $res->getMessage(),
											ERRCODE_APPLICATION_ERR,
											$_PEAR_default_error_mode,
											$_PEAR_default_error_options,
											$C_ID );
			}
		}

		return null;
	}

	function cm_onDeleteFolder( $CF_ID, $params )
	//
	// Callback function, on folder delete
	//
	//		Parameters:
	//			$CF_ID - folder identifier
	//			$params - other parameters array
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_cm_resetContactFolder;
		global $qr_cm_selectFolderUsers;

		extract($params);

		// Delete folder users
		//
		$qr = db_query( $qr_cm_selectFolderUsers, array( 'CF_ID'=>$CF_ID ) );

		while ( $contactData = db_fetch_array($qr) ) {
			$Contact = new Contact( $kernelStrings, $language, null, null, false );

			$res = $Contact->revokeUserPrivileges( $contactData['C_ID'], $kernelStrings, $U_ID );
			if ( PEAR::isError($res) )
				return $res;
		}

		db_free_result( $qr );

		cm_deleteFolderVewSettings( $CF_ID, $kernelStrings );

		return null;
	}

	function cm_getTypeDescriptionDOM( $type, &$kernelStrings )
	//
	// Returns contact type settings DOM
	//
	//		Parameters:
	//			$type - contact type identifier
	//			$kernelStrings - Kernel localization data
	//
	//		Returns DOM object reference or PEAR_Error
	//
	//
	{
		global $qr_selectcontacttype;

		// Load type description
		//
		$folderData = db_query_result( $qr_selectcontacttype, DB_ARRAY, array('CT_ID'=>$type) );
		if ( PEAR::isError($folderData) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( !strlen($folderData['CT_ID']) )
			return PEAR::raiseError( $kernelStrings['app_conttypenotfound_message'], ERRCODE_APPLICATION_ERR );

		$result = array();

		$typeSettings = $folderData['CT_SETTINGS'];
		if ( !strlen($typeSettings) )
			return PEAR::raiseError( $kernelStrings['app_invconttype_message'] );

		$dom = @domxml_open_mem( $typeSettings );
		if ( !$dom )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		return $dom;
	}

	function cm_setTypeDescription( $type, &$dom, &$kernelStrings )
	//
	// Sets contact type settings XML
	//
	//		Parameters:
	//			$type - contact type identifier
	//			$dom - DOM XML object reference
	//			$kernelStrings - Kernel localization data
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_cm_updateContactTypeSettings;

		$settings = $dom->dump_mem();

		$params = array();
		$params['CT_ID'] = $type;
		$params['CT_SETTINGS'] = $settings;

		$res = db_query( $qr_cm_updateContactTypeSettings, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function cm_fieldExists( $fieldName, &$kernelStrings, $ignoreName = null )
	//
	// Checks if field name exists
	//
	//		Parameters:
	//			$fieldName - field name to text
	//			$kernelStrings - Kernel localization strings
	//			$ignoreName - ignore this field name
	//
	//		Returns boolean or PEAR_Error
	//
	{
		global $qr_cm_selectContactColumns;

		$qr = db_query( $qr_cm_selectContactColumns, array() );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$found = false;
		while ( $row = db_fetch_array( $qr, DB_FETCHMODE_ORDERED ) )
			if ( $row[0] == $fieldName && $ignoreName != $row[0] ) {
				$found = true;
				break;
			}

		db_free_result( $qr );

		return $found;
	}

	function cm_deleteImageFieldData( $type, $fieldName, &$kernelStrings )
	//
	// Deletes data assigned with the image field
	//
	//		Parameters:
	//			$type - contact type identifier
	//			$fieldName - database field name
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_cm_selectContactsData;

		$filesPath = getContactsAttachmentsDir();

		// Load type description
		//
		$typeDesc = getContactTypeDescription( $type, LANG_ENG, $kernelStrings, false );
		if ( PEAR::isError($typeDesc) )
			return $typeDesc;

		// Obtain columns descriptions as a plain array
		//
		$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDesc, $kernelStrings, true );
		if ( PEAR::isError($fieldsPlainDesc) )
			return $fieldsPlainDesc;

		$qr = db_query( $qr_cm_selectContactsData );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		while ( $contactData = db_fetch_array($qr) ) {
			$contactData = applyContactTypeDescription( $contactData, array(), $fieldsPlainDesc, $kernelStrings, UL_LIST_VIEW );

			if ( isset($contactData[$fieldName]) ) {
				$imgProperties = $contactData[$fieldName];
				$diskFileName = $imgProperties[CONTACT_IMGF_DISKFILENAME];

				if ( strlen($diskFileName) ) {
					$srcFilePath = $filesPath."/".base64_decode($diskFileName);
					if ( @file_exists($srcFilePath) )
						@unlink($srcFilePath);

					$ext = null;
					$srcThumbFile = findThumbnailFile( $srcFilePath, $ext );
					if ( @file_exists($srcThumbFile) )
						@unlink($srcThumbFile);
				}
			}
		}

		db_free_result($qr);

		return null;
	}

	function cm_deleteDbField( $type, $fieldType, $fieldName, &$kernelStrings )
	//
	// Deletes database field
	//
	//		Parameters:
	//			$type - contact type identifier
	//			$fieldType - field type
	//			$fieldName - field name to text
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_cm_deleteField;

		if ( !strlen($fieldName) )
			return null;

		$res = cm_fieldExists( $fieldName, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		if ( !$res )
			return null;

		// Delete field data
		//
		switch ( $fieldType ) {
			case CONTACT_FT_IMAGE :
					$res = cm_deleteImageFieldData( $type, $fieldName, $kernelStrings );
					if ( PEAR::isError($res) )
						return $res;

					break;
		}

		// Delete database field
		//
		$sql = sprintf( $qr_cm_deleteField, $fieldName );
		$res = db_query( $sql, array() );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function cm_generateUniqueFieldName( $sourceFieldName, &$kernelStrings, $level = null, $ignoreName = null )
	//
	// Generates unique field name, based on source field name
	//
	//		Parameters:
	//			$sourceFieldName - source field name
	//			$kernelStrings - Kernel localization strings
	//			$level - start name level
	//			$ignoreName - ignore this field name
	//
	//		Returns string or PEAR_Error
	//
	{
		$testName = is_null($level) ? $sourceFieldName : $sourceFieldName.$level;

		if ( cm_fieldExists( $testName, $kernelStrings, $ignoreName ) ) {
			$level += 1;
			$res = cm_generateUniqueFieldName( $sourceFieldName, $kernelStrings, $level, $ignoreName );
			if ( PEAR::isError($res) )
				return $res;

			return $res;
		}

		return $testName;
	}

	function cm_createDBField( $fieldName, $type, &$kernelStrings )
	//
	// Creates field in the Contact table
	//
	//		Parameters:
	//			$fieldName - field name
	//			$type - field type
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_cm_createField;
		global $cm_contactFieldMySQLType;

		$type = $cm_contactFieldMySQLType[$type];

		$sql = sprintf( $qr_cm_createField, $fieldName, $type  );
		$res = db_query( $sql, array() );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
	}

	function cm_alterDBField( $oldFieldName, $fieldName, $type, &$kernelStrings )
	//
	// Alters field in the Contact table
	//
	//		Parameters:
	//			$oldFieldName - old field name
	//			$fieldName - field name
	//			$type - field type
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_cm_alterField;
		global $cm_contactFieldMySQLType;

		$type = $cm_contactFieldMySQLType[$type];

		$sql = sprintf( $qr_cm_alterField, $oldFieldName, $fieldName, $type );
		$res = db_query( $sql, array() );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
	}


	function cm_parseFieldName( &$xpath, &$dom, &$field, $nameNodeName )
	//
	// Returns field name description as array
	//
	//		Parameters:
	//			$xpath - xpath object reference
	//			$dom - DOM XML object reference
	//			$field - field element reference
	//			$nameNodeName - name of the element node
	//
	//		Returns array
	//
	{
		$nameData = array();

		$nameElement = xpath_eval( $xpath, $nameNodeName, $field );
		if ( !count($nameElement->nodeset) )
			return $nameData;

		$nameElement = $nameElement->nodeset[0];

		$langElements = xpath_eval( $xpath, "child::*", $nameElement );
		foreach( $langElements->nodeset as $langElement )
			$nameData[$langElement->tagname()] = $langElement->get_attribute( CONTACT_NAMEVALUE );

		return $nameData;
	}

	function cm_getFieldDescription( &$xpath, &$dom, &$field, &$kernelStrings )
	//
	// Returns field description as array
	//
	//		Parameters:
	//			$xpath - xpath object reference
	//			$dom - DOM XML object reference
	//			$field - field element reference
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		$result = array();

		// Load field array
		//
		$result = getAttributeValues( $field );

		// Load field name elements
		//
		$longNameElement = xpath_eval( $xpath, CONTACT_FIELDGROUP_LONGNAME, $field );

		if ( count($longNameElement->nodeset) ) {
			$result[CONTACT_FIELDGROUP_LONGNAME] = cm_parseFieldName( $xpath, $dom, $field, CONTACT_FIELDGROUP_LONGNAME );
			$result[CONTACT_FIELDGROUP_SHORTNAME] = cm_parseFieldName( $xpath, $dom, $field, CONTACT_FIELDGROUP_SHORTNAME );
		}

		return $result;
	}

	function cm_getSectionDescription( &$xpath, &$dom, &$section, &$kernelStrings )
	//
	// Returns section description as array
	//
	//		Parameters:
	//			$xpath - xpath object reference
	//			$dom - DOM XML object reference
	//			$section - section element reference
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		$result = array();

		// Load section attributes array
		//
		$result = getAttributeValues( $section );

		// Load section name elements
		//
		$longNameElement = xpath_eval( $xpath, CONTACT_FIELDGROUP_LONGNAME, $section );

		if ( count($longNameElement->nodeset) ) {
			$result[CONTACT_FIELDGROUP_LONGNAME] = cm_parseFieldName( $xpath, $dom, $section, CONTACT_FIELDGROUP_LONGNAME );
			$result[CONTACT_FIELDGROUP_SHORTNAME] = cm_parseFieldName( $xpath, $dom, $section, CONTACT_FIELDGROUP_SHORTNAME );
		}

		// Load section fields into CONTACT_FIELDS element
		//
		$result[CONTACT_FIELDS] = array();

		$sectionFields = xpath_eval( $xpath, CONTACT_FIELD, $section );
		foreach( $sectionFields->nodeset as $fieldElement )
			$result[CONTACT_FIELDS][] = cm_getFieldDescription( $xpath, $dom, $fieldElement, $kernelStrings );

		return $result;
	}


	function cm_createFieldDescription( &$dom, &$parent, $fieldData )
	//
	// Creates XML field description, based on array data
	//
	//		Parameters:
	//			$dom - DOM XML object reference
	//			$parent - parent object reference
	//			$fieldData - field data as array
	//
	//		Returns field object
	//
	{
		// Create new field element
		//
		$field = @create_addElement( $dom, $parent, CONTACT_FIELD );

		// Fill field attribites
		//
		$field->set_attribute( CONTACT_FIELDID, $fieldData[CONTACT_FIELDID] );

		foreach( $fieldData as $key=>$value )
			if ( !is_array($value) && !is_object($value) )
				$field->set_attribute( $key, $value );

		// Create field name elements
		//
		if ( is_array($fieldData[CONTACT_FIELDGROUP_LONGNAME]) )
			cm_createFieldNameElement( $dom, $field, CONTACT_FIELDGROUP_LONGNAME, $fieldData[CONTACT_FIELDGROUP_LONGNAME] );
		else
			$field->set_attribute( CONTACT_FIELDGROUP_LONGNAME, $fieldData[CONTACT_FIELDGROUP_LONGNAME] );

		if ( is_array($fieldData[CONTACT_FIELDGROUP_SHORTNAME]) )
			cm_createFieldNameElement( $dom, $field, CONTACT_FIELDGROUP_SHORTNAME, $fieldData[CONTACT_FIELDGROUP_SHORTNAME] );
		else
			$field->set_attribute( CONTACT_FIELDGROUP_SHORTNAME, $fieldData[CONTACT_FIELDGROUP_SHORTNAME] );

		return $field;
	}

	function cm_createSectionDescription( &$dom, &$parent, $sectionData, &$section )
	//
	// Creates XML section description, based on array data
	//
	//		Parameters:
	//			$dom - DOM XML object reference
	//			$parent - parent object reference
	//			$sectionData - section data as array
	//			$section - optional section object
	//
	//		Returns field object
	//
	{
		// Create new section element
		//
		if ( is_null($section) )
			$section = @create_addElement( $dom, $parent, CONTACT_GROUP );

		// Fill section attribites
		//
		$section->set_attribute( CONTACT_GROUPID, $sectionData[CONTACT_GROUPID] );

		// Create section name elements
		//
		if ( is_array($sectionData[CONTACT_FIELDGROUP_LONGNAME]) )
			cm_createFieldNameElement( $dom, $section, CONTACT_FIELDGROUP_LONGNAME, $sectionData[CONTACT_FIELDGROUP_LONGNAME] );
		else
			$section->set_attribute( CONTACT_FIELDGROUP_LONGNAME, $sectionData[CONTACT_FIELDGROUP_LONGNAME] );

		if ( is_array($sectionData[CONTACT_FIELDGROUP_SHORTNAME]) )
			cm_createFieldNameElement( $dom, $section, CONTACT_FIELDGROUP_SHORTNAME, $sectionData[CONTACT_FIELDGROUP_SHORTNAME] );
		else
			$section->set_attribute( CONTACT_FIELDGROUP_SHORTNAME, $sectionData[CONTACT_FIELDGROUP_SHORTNAME] );

		// Create section fields
		//
		if ( isset($sectionData[CONTACT_FIELDS]) ) {
			foreach( $sectionData[CONTACT_FIELDS] as $sectionField )
				cm_createFieldDescription( $dom, $section, $sectionField );
		}

		return $section;
	}

	function cm_setFieldPosition( &$xpath, &$dom, &$field, &$section, $targetSection, $targetFieldPosition, &$kernelStrings )
	//
	// Sets field position
	//
	//		Parameters:
	//			$xpath - xpath object reference
	//			$dom - DOM XML object reference
	//			$field - field object reference
	//			$section - field section object reference
	//			$targetSection - section to put field in
	//			$targetFieldPosition - field identifier in the targer section, to put the field after
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		// Load field description into array
		//
		$sourceFieldDesc = cm_getFieldDescription( $xpath, $dom, $field, $kernelStrings );

		// Unset field node in the source section
		//
		$field->unlink_node();

		//
		// Load all fields of the source section into array
		//

		// Find target section object
		//
		$targetSection = xpath_eval( $xpath, "/TYPE/FIELDGROUP[@ID='$targetSection']" );

		if ( !count($targetSection->nodeset) )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$targetSection = $targetSection->nodeset[0];

		// Load target section fields and unlink them
		//
		$sectionFields = &xpath_eval( $xpath, CONTACT_FIELD, $targetSection );

		$targetFields = array();
		$targetFieldIndexes = array();
		foreach ( $sectionFields->nodeset as $fieldIndex=>$fieldData ) {
			$targetFields[] = cm_getFieldDescription( $xpath, $dom, $fieldData, $kernelStrings );
			$targetFieldIndexes[] = $fieldIndex;
			$fieldData->unlink_node();
		}

		//
		// Populate target section fields
		//

		// Create new field as first section field, if $targetFieldPosition is null
		//
		if ( is_null($targetFieldPosition) )
			cm_createFieldDescription( $dom, $targetSection, $sourceFieldDesc );

		// Add target section fields fields
		//
		foreach ( $targetFields as $targetFieldDesc ) {

			cm_createFieldDescription( $dom, $targetSection, $targetFieldDesc );

			if ( $targetFieldDesc[CONTACT_FIELDID] == $targetFieldPosition ) {
				// Add new source field after target field
				//
				cm_createFieldDescription( $dom, $targetSection, $sourceFieldDesc );
			}
		}

		return null;
	}

	function cm_setSectionPosition( &$xpath, &$dom, &$root, &$section, $targetSectionPosition, &$kernelStrings )
	//
	// Sets field position
	//
	//		Parameters:
	//			$xpath - xpath object reference
	//			$dom - DOM XML object reference
	//			$root - root object reference
	//			$section - section object reference
	//			$targetSectionPosition - section identifier, to put the section after
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		// Load section description into array
		//
		$sourceSectionDesc = cm_getSectionDescription( $xpath, $dom, $section, $kernelStrings );

		// Unlink section node
		//
		$section->unlink_node();

		// Load sections into array and unlink them
		//
		$sections = &xpath_eval( $xpath, CONTACT_GROUP, $root );

		$sectionArr = array();
		foreach ( $sections->nodeset as $sectionData ) {
			$sectionArr[] = cm_getSectionDescription( $xpath, $dom, $sectionData, $kernelStrings );
			$sectionData->unlink_node();
		}

		//
		// Populate sections
		//

		// Create new section as topmost section, if $targetSectionPosition is null
		//
		if ( is_null($targetSectionPosition) ) {
			$sc = null;
			cm_createSectionDescription( $dom, $root, $sourceSectionDesc, $sc );
		}

		// Add target section fields fields
		//
		foreach ( $sectionArr as $sectionDesc ) {
			$sc = null;
			cm_createSectionDescription( $dom, $root, $sectionDesc, $sc );

			if ( $sectionDesc[CONTACT_GROUPID] == $targetSectionPosition ) {
				// Add new source section after target section
				//
				$sc = null;
				cm_createSectionDescription( $dom, $root, $sourceSectionDesc, $sc );
			}
		}

		return null;
	}

	function cm_addModField( $type, $action, $fieldData, $fieldSection, $fieldPosition, &$kernelStrings, $cmStrings )
	//
	// Adds or modifies field description
	//
	//		Parameters:
	//			$type - contact type identifier
	//			$action - form action
	//			$fieldData - field data
	//			$fieldSection - field section
	//			$fieldPosition - field position
	//			$kernelStrings - Kernel localization data
	//			$cmStrings - Contact Manager localization data
	//
	//		Returns null or PEAR_Error
	//
	{
		$fieldData = trimArrayData( $fieldData );

		// Check required fields
		//
		$nameFields = array( CONTACT_FIELDGROUP_LONGNAME );

		foreach ( $nameFields as $nameField ) {
			// Check if field name is not empty
			//
			if ( PEAR::isError( $invalidField = findEmptyField( $fieldData[$nameField], array(LANG_ENG)) ) ) {
				$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];
				$invalidField->userinfo = sprintf( '[%s][%s]', $nameField, LANG_ENG );

				return $invalidField;
			}
		}

		$engLongName = $fieldData[CONTACT_FIELDGROUP_LONGNAME][LANG_ENG];

		$nameFields = array( CONTACT_FIELDGROUP_LONGNAME, CONTACT_FIELDGROUP_SHORTNAME );
		foreach ( $nameFields as $nameField ) {
			// Check if field name contais allowed symbols only
			//
			if ( PEAR::isError( $invalidField = checkFieldInvalidSymbols( $fieldData[$nameField], array(LANG_ENG), CM_FIELDSYMBOLS) ) ) {
				$invalidField->message = $kernelStrings['app_invfieldchars_message'];
				$invalidField->userinfo = sprintf( '[%s][%s]', $nameField, LANG_ENG );

				return $invalidField;
			}

			foreach ( $fieldData[$nameField] as $lang_id=>$value )
				$fieldData[$nameField][$lang_id] = base64_encode($value);
		}

		// Process type descriptions
		//
		if ( $fieldData[CONTACT_FIELD_TYPE] == CONTACT_FT_MENU ) {
			if ( !strlen($fieldData[CONTACT_MENU]) ) {
				$err = PEAR::raiseError( $cmStrings['amf_emptymenu_message'], ERRCODE_APPLICATION_ERR );
				$err->userinfo = sprintf( '[%s]', CONTACT_MENU );
				return $err;
			}
		}

		if ( $fieldData[CONTACT_FIELD_TYPE] == CONTACT_FT_NUMERIC ) {
			$res = checkIntegerFields( $fieldData, array(CONTACT_DECPLACES), $kernelStrings );
			$res->userinfo = sprintf( '[%s]', CONTACT_DECPLACES );
			if ( PEAR::isError($res) )
				return $res;
		}

		if ( $fieldData[CONTACT_FIELD_TYPE] != CONTACT_FT_TEXT ) {
			$fieldData[CONTACT_MAXLEN] = null;
		} else {
			$res = checkIntegerFields( $fieldData, array(CONTACT_MAXLEN), $kernelStrings );
			$res->userinfo = sprintf( '[%s]', CONTACT_MAXLEN );
			if ( PEAR::isError($res) )
				return $res;
		}

		// Check field constraints
		//
		if ( $fieldData[CONTACT_FIELDUNIQUE] && $fieldData[CONTACT_FIELD_TYPE] == CONTACT_FT_IMAGE )
			return PEAR::raiseError( $cmStrings['amf_imgunique_message'], ERRCODE_APPLICATION_ERR );

		if ( $fieldData[CONTACT_FIELD_TYPE] != CONTACT_FT_MENU )
			$fieldData[CONTACT_MENU] = null;

		// Implode menu value
		//
		$fieldData[CONTACT_MENU] = str_replace( "\r\n", "\n", $fieldData[CONTACT_MENU] );
		$fieldData[CONTACT_MENU] = str_replace( "\n", CONTACT_MENU_SEPARATOR, $fieldData[CONTACT_MENU] );
		$fieldData[CONTACT_MENU] = base64_encode( $fieldData[CONTACT_MENU] );

		// Load type description
		//
		$dom = &cm_getTypeDescriptionDOM( $type, $kernelStrings );
		if ( PEAR::isError($dom) )
			return $dom;

		$xpath = xpath_new_context($dom);

		// Find section element
		//
		$section = xpath_eval( $xpath, "/TYPE/FIELDGROUP[@ID='$fieldSection']" );

		if ( !count($section->nodeset) )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$section = $section->nodeset[0];

		if ( $action == ACTION_NEW ) {
			// Generate field ID
			//
			$fieldID = cm_getNameLatinSymbols( $engLongName );
			$fieldID = CM_FIELD_PREFIX.strtoupper($fieldID);

			// Generate database field name
			//
			$fieldID = cm_generateUniqueFieldName( $fieldID, $kernelStrings );
			if ( PEAR::isError($fieldID) )
				return $fieldID;

			// Create new field element
			//
			$fieldData[CONTACT_FIELDID] = $fieldID;
			$fieldData[CONTACT_DBFIELD] = $fieldID;

			$field = &cm_createFieldDescription( $dom, $section, $fieldData );

			// Set field position
			//
			$res = cm_setFieldPosition( $xpath, $dom, $field, $section, $fieldSection, $fieldPosition, $kernelStrings );
			if ( PEAR::isError($res) )
				return $res;

			// Create database field
			//
			$res = cm_createDBField( $fieldID, $fieldData[CONTACT_FIELD_TYPE], $kernelStrings );
			if ( PEAR::isError($res) )
				return $res;

			// Dump settings
			//
			$res = cm_setTypeDescription( $type, $dom, $kernelStrings );

			if ( PEAR::isError($res) )
				return $res;
		} else {
			// Find field object
			//
			$targetFieldID = $fieldData[CONTACT_FIELDID];
			$field = xpath_eval( $xpath, "/TYPE/FIELDGROUP/FIELD[@ID='$targetFieldID']" );

			if ( !count($field->nodeset) )
				return PEAR::raiseError( $kernelStrings[ERR_XML] );

			$field = $field->nodeset[0];

			// Generate field ID
			//
			$prevFieldID = $field->get_attribute(CONTACT_DBFIELD);

			if ( substr($prevFieldID, 0, strlen(CM_FIELD_PREFIX)) != CM_FIELD_PREFIX ) {
				// Do not override ID for pre-installed fields
				//
				$fieldID = $prevFieldID;
				$fieldID = $prevFieldID;
			} else {
				$fieldID = cm_getNameLatinSymbols( $engLongName );
				$fieldID = CM_FIELD_PREFIX.strtoupper($fieldID);
			}

			// Load field data into array
			//
			$fieldDescription = cm_getFieldDescription( $xpath, $dom, $field, $kernelStrings );;

			// Unlink field node
			//
			$field->unlink_node();

			// Generate database field name
			//
			$fieldID = cm_generateUniqueFieldName( $fieldID, $kernelStrings, null, $fieldDescription[CONTACT_DBFIELD] );
			if ( PEAR::isError($fieldID) )
				return $fieldID;

			// Set new database ID
			//
			$fieldData[CONTACT_DBFIELD] = $fieldID;
			$fieldData[CONTACT_FIELDID] = $fieldID;

			// Copy some attributes from old field description
			//
			if ( isset($fieldDescription[CONTACT_REQUIRED_GROUP]) )
				$fieldData[CONTACT_REQUIRED_GROUP] = $fieldDescription[CONTACT_REQUIRED_GROUP];

			// Create new field element
			//
			$field = &cm_createFieldDescription( $dom, $section, $fieldData );

			// Set field position
			//
			$res = cm_setFieldPosition( $xpath, $dom, $field, $section, $fieldSection, $fieldPosition, $kernelStrings );
			if ( PEAR::isError($res) )
				return $res;

			// Alter database field
			//
			$res = cm_alterDBField( $fieldDescription[CONTACT_DBFIELD], $fieldID, $fieldData[CONTACT_FIELD_TYPE], $kernelStrings );
			if ( PEAR::isError($res) )
				return $res;

			// Dump settings
			//
			$res = cm_setTypeDescription( $type, $dom, $kernelStrings );

			if ( PEAR::isError($res) )
				return $res;
		}

		return null;
	}

	function cm_loadFieldDescription( $type, $fieldID, $sectionID, &$fieldPosition, &$kernelStrings )
	//
	// Helper function for add/modify field screen. Loads field data into array
	//
	//		Parameters:
	//			$type - contact type identifier
	//			$fieldID - field identifier
	//			$sectionID - section identifier
	//			$fieldPosition - field position
	//			$kernelStrings - Kernel strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $wbs_languages;
		global $loc_str;

		// Load type description
		//
		$dom = &cm_getTypeDescriptionDOM( $type, $kernelStrings );
		if ( PEAR::isError($dom) )
			return $dom;

		$xpath = xpath_new_context($dom);

		// Find section element
		//
		$section = xpath_eval( $xpath, "/TYPE/FIELDGROUP[@ID='$sectionID']" );

		if ( !count($section->nodeset) )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$section = $section->nodeset[0];

		// Find field element
		//
		$field = xpath_eval( $xpath, "FIELD[@ID='$fieldID']", $section );

		if ( !count($field->nodeset) )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$field = $field->nodeset[0];

		// Load field description
		//
		$fieldDescription = cm_getFieldDescription( $xpath, $dom, $field, $kernelStrings );

		// Populate name fields
		//
		if ( !is_array($fieldDescription[CONTACT_FIELDGROUP_LONGNAME]) ) {
			$longNameIndex = $fieldDescription[CONTACT_FIELDGROUP_LONGNAME];
			$shortNameIndex = $fieldDescription[CONTACT_FIELDGROUP_SHORTNAME];

			$fieldDescription[CONTACT_FIELDGROUP_LONGNAME] = array();
			$fieldDescription[CONTACT_FIELDGROUP_SHORTNAME] = array();

			foreach ( $wbs_languages as $lang_id=>$lang_data ) {
				$langStrings = $loc_str[$lang_id];

				if (isset($langStrings[$longNameIndex]))
					$fieldDescription[CONTACT_FIELDGROUP_LONGNAME][$lang_id] = $langStrings[$longNameIndex];
				else
					$fieldDescription[CONTACT_FIELDGROUP_LONGNAME][$lang_id] = null;

				if (isset($langStrings[$shortNameIndex]))
					$fieldDescription[CONTACT_FIELDGROUP_SHORTNAME][$lang_id] = $langStrings[$shortNameIndex];
				else
					$fieldDescription[CONTACT_FIELDGROUP_SHORTNAME][$lang_id] = null;
			}
		} else {
			foreach ( $wbs_languages as $lang_id=>$lang_data ) {
				if ( isset( $fieldDescription[CONTACT_FIELDGROUP_SHORTNAME][$lang_id] ) )
					$fieldDescription[CONTACT_FIELDGROUP_SHORTNAME][$lang_id] = base64_decode($fieldDescription[CONTACT_FIELDGROUP_SHORTNAME][$lang_id]);

				if ( isset( $fieldDescription[CONTACT_FIELDGROUP_LONGNAME][$lang_id] ) )
					$fieldDescription[CONTACT_FIELDGROUP_LONGNAME][$lang_id] = base64_decode($fieldDescription[CONTACT_FIELDGROUP_LONGNAME][$lang_id]);
			}
		}

		// Unpack menu value
		//
		if ( isset($fieldDescription[CONTACT_MENU]) && strlen($fieldDescription[CONTACT_MENU]) ) {
			$fieldDescription[CONTACT_MENU] = base64_decode($fieldDescription[CONTACT_MENU]);
			$fieldDescription[CONTACT_MENU] = str_replace( CONTACT_MENU_SEPARATOR, "\n", $fieldDescription[CONTACT_MENU] );
		}

		// Find field position
		//
		$fields = xpath_eval( $xpath, "FIELD", $section );

		$prevField = null;
		foreach( $fields->nodeset as $fieldData ) {
			if ( $fieldData->get_attribute(CONTACT_FIELDID) == $fieldID )
				break;

			$prevField = $fieldData;
		}

		if ( is_null($prevField) )
			$fieldPosition = CM_SECTIONQUALIFIER.$section->get_attribute(CONTACT_GROUPID);
		else
			$fieldPosition = sprintf( "%s|%s", $section->get_attribute(CONTACT_GROUPID), $prevField->get_attribute(CONTACT_FIELDID) );

		return $fieldDescription;
	}

	function cm_deleteContactField( $type, $fieldID, &$kernelStrings )
	//
	// Deletes field
	//
	//		Parameters:
	//			$type - contact type identifier
	//			$fieldID - field identifier
	//			$kernelStrings - Kernel strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $contactMandotoryFields;

		// Load type description
		//
		$dom = &cm_getTypeDescriptionDOM( $type, $kernelStrings );
		if ( PEAR::isError($dom) )
			return $dom;

		$xpath = xpath_new_context($dom);

		// Find field section
		//
		$section = xpath_eval( $xpath, "parent::node()/TYPE/FIELDGROUP[child::FIELD[@ID='$fieldID']]" );
		if ( !count($section->nodeset) )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$section = $section->nodeset[0];

		// Protect mandatory fields
		//
		if ( in_array($fieldID, $contactMandotoryFields) )
			return null;

		// Find field element
		//
		$field = xpath_eval( $xpath, "/TYPE/FIELDGROUP/FIELD[@ID='$fieldID']" );

		if ( !count($field->nodeset) )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$field = $field->nodeset[0];


		// Delete table field
		//
		$fieldType = $field->get_attribute(CONTACT_FIELD_TYPE);
		$fieldName = $field->get_attribute(CONTACT_DBFIELD);

		$res = cm_deleteDbField( $type, $fieldType, $fieldName, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		// Unlink field node
		//
		$field->unlink_node();

		// Dump settings
		//
		$res = cm_setTypeDescription( $type, $dom, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		return null;
	}

	function cm_generateUniqueSectionID( $basename, &$xpath )
	//
	// Generates unique section identifier
	//
	//		Parameters:
	//			$basename - section ID base name
	//			$xpath - XPath object connected to the contact type description XML document
	//
	//		Returns string
	//
	{
		$index = "";
		do {
			$idExists = false;
			$curName = $basename.$index;

			$section = xpath_eval( $xpath, "/TYPE/FIELDGROUP[@ID='$curName']" );
			if ( count($section->nodeset) )
				$idExists = true;

			if ( !strlen($index) )
				$index = 2;
			else
				$index++;

		} while ( $idExists );

		return $curName;
	}

	function cm_addModSection( $type, $action, $sectionData, $sectionPosition, &$kernelStrings, $cmStrings )
	//
	// Adds or modifies section description
	//
	//		Parameters:
	//			$type - contact type identifier
	//			$action - form action
	//			$sectionData - section data
	//			$sectionPosition - section section
	//			$kernelStrings - Kernel localization data
	//			$cmStrings - Contact Manager localization data
	//
	//		Returns null or PEAR_Error
	//
	{
		$sectionData = trimArrayData( $sectionData );

		// Check required fields
		//
		$nameFields = array( CONTACT_FIELDGROUP_LONGNAME );

		foreach ( $nameFields as $nameField ) {
			// Check if section name is not empty
			//
			if ( PEAR::isError( $invalidField = findEmptyField( $sectionData[$nameField], array(LANG_ENG)) ) ) {
				$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];
				$invalidField->userinfo = sprintf( '[%s][%s]', $nameField, LANG_ENG );

				return $invalidField;
			}
		}

		$engLongName = $sectionData[CONTACT_FIELDGROUP_LONGNAME][LANG_ENG];

		foreach ( $nameFields as $nameField ) {
			// Check if section name contais allowed symbols only
			//
			if ( PEAR::isError( $invalidField = checkFieldInvalidSymbols( $sectionData[$nameField], array(LANG_ENG), CM_FIELDSYMBOLS) ) ) {
				$invalidField->message = $kernelStrings['app_invfieldchars_message'];
				$invalidField->userinfo = sprintf( '[%s][%s]', $nameField, LANG_ENG );

				return $invalidField;
			}

			foreach ( $sectionData[$nameField] as $lang_id=>$value )
				$sectionData[$nameField][$lang_id] = base64_encode($value);
		}

		$sectionData[CONTACT_FIELDGROUP_SHORTNAME] = $sectionData[CONTACT_FIELDGROUP_LONGNAME];

		// Load type description
		//
		$dom = &cm_getTypeDescriptionDOM( $type, $kernelStrings );
		if ( PEAR::isError($dom) )
			return $dom;

		$xpath = xpath_new_context($dom);

		// Find root element
		//
		$root = xpath_eval( $xpath, "/TYPE" );

		if ( !count($root->nodeset) )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$root = $root->nodeset[0];

		if ( $action == ACTION_NEW ) {
			// Generate section ID
			//
			$sectionID = cm_getNameLatinSymbols( $engLongName );

			$sectionID = cm_generateUniqueSectionID( $sectionID, $xpath );

			// Create new field element
			//
			$sectionData[CONTACT_GROUPID] = $sectionID;

			$sc = null;
			$section = &cm_createSectionDescription( $dom, $root, $sectionData, $sc );

			// Set section position
			//
			$res = cm_setSectionPosition( $xpath, $dom, $root, $section, $sectionPosition, $kernelStrings );
			if ( PEAR::isError($res) )
				return $res;

			// Dump settings
			//
			$res = cm_setTypeDescription( $type, $dom, $kernelStrings );

			if ( PEAR::isError($res) )
				return $res;
		} else {
			// Find section object
			//
			$targetSectionID = $sectionData[CONTACT_GROUPID];

			$section = xpath_eval( $xpath, "/TYPE/FIELDGROUP[@ID='$targetSectionID']" );

			if ( !count($section->nodeset) )
				return PEAR::raiseError( $kernelStrings[ERR_XML] );

			$section = $section->nodeset[0];

			// Load section data into array
			//
			$oldSectionDescription = cm_getSectionDescription( $xpath, $dom, $section, $kernelStrings );

			// Unlink field node
			//
			$section->unlink_node();

			// Copy section fields from old section
			//
			$sectionData[CONTACT_FIELDS] = $oldSectionDescription[CONTACT_FIELDS];

			// Set section position
			//
			$sc = null;
			$section = &cm_createSectionDescription( $dom, $root, $sectionData, $sc );

			// Set section position
			//
			$res = cm_setSectionPosition( $xpath, $dom, $root, $section, $sectionPosition, $kernelStrings );
			if ( PEAR::isError($res) )
				return $res;

			// Dump settings
			//
			$res = cm_setTypeDescription( $type, $dom, $kernelStrings );

			if ( PEAR::isError($res) )
				return $res;
		}

		return null;
	}

	function cm_loadSectionDescription( $type, $sectionID, &$sectionPosition, &$kernelStrings )
	//
	// Helper function for add/modify section screen. Loads section data into array
	//
	//		Parameters:
	//			$type - contact type identifier
	//			$sectionID - section identifier
	//			$sectionPosition - field position
	//			$kernelStrings - Kernel strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $wbs_languages;
		global $loc_str;

		// Load type description
		//
		$dom = &cm_getTypeDescriptionDOM( $type, $kernelStrings );
		if ( PEAR::isError($dom) )
			return $dom;

		$xpath = xpath_new_context($dom);

		// Find section element
		//
		$section = xpath_eval( $xpath, "/TYPE/FIELDGROUP[@ID='$sectionID']" );

		if ( !count($section->nodeset) )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$section = $section->nodeset[0];

		// Load section description
		//
		$sectionDescription = cm_getSectionDescription( $xpath, $dom, $section, $kernelStrings );
		if ( PEAR::isError($sectionDescription) )
			return $sectionDescription;

		// Populate name fields
		//
		if ( !is_array($sectionDescription[CONTACT_FIELDGROUP_LONGNAME]) ) {
			$longNameIndex = $sectionDescription[CONTACT_FIELDGROUP_LONGNAME];
			$shortNameIndex = $sectionDescription[CONTACT_FIELDGROUP_SHORTNAME];

			$sectionDescription[CONTACT_FIELDGROUP_LONGNAME] = array();
			$sectionDescription[CONTACT_FIELDGROUP_SHORTNAME] = array();

			foreach ( $wbs_languages as $lang_id=>$lang_data ) {
				$langStrings = $loc_str[$lang_id];

				if (isset($langStrings[$longNameIndex]))
					$sectionDescription[CONTACT_FIELDGROUP_LONGNAME][$lang_id] = $langStrings[$longNameIndex];
				else
					$sectionDescription[CONTACT_FIELDGROUP_LONGNAME][$lang_id] = null;

				if (isset($langStrings[$shortNameIndex]))
					$sectionDescription[CONTACT_FIELDGROUP_SHORTNAME][$lang_id] = $langStrings[$shortNameIndex];
				else
					$sectionDescription[CONTACT_FIELDGROUP_SHORTNAME][$lang_id] = null;
			}
		} else {
			foreach ( $wbs_languages as $lang_id=>$lang_data ) {
				if ( isset( $sectionDescription[CONTACT_FIELDGROUP_SHORTNAME][$lang_id] ) )
					$sectionDescription[CONTACT_FIELDGROUP_SHORTNAME][$lang_id] = base64_decode($sectionDescription[CONTACT_FIELDGROUP_SHORTNAME][$lang_id]);

				if ( isset( $sectionDescription[CONTACT_FIELDGROUP_LONGNAME][$lang_id] ) )
					$sectionDescription[CONTACT_FIELDGROUP_LONGNAME][$lang_id] = base64_decode($sectionDescription[CONTACT_FIELDGROUP_LONGNAME][$lang_id]);
			}
		}

		// Find section position
		//
		$sections = xpath_eval( $xpath, "/TYPE/FIELDGROUP" );

		$prevSection = null;
		foreach( $sections->nodeset as $sectionData ) {
			if ( $sectionData->get_attribute(CONTACT_GROUPID) == $sectionID )
				break;

			$prevSection = $sectionData;
		}

		if ( is_null($prevSection) )
			$sectionPosition = null;
		else
			$sectionPosition = $prevSection->get_attribute(CONTACT_GROUPID);

		return $sectionDescription;
	}

	function cm_deleteContactSection( $type, $sectionID, &$kernelStrings )
	//
	// Deletes field
	//
	//		Parameters:
	//			$type - contact type identifier
	//			$sectionID - field identifier
	//			$kernelStrings - Kernel strings
	//
	//		Returns null or PEAR_Error
	//
	{
		// Load type description
		//
		$dom = &cm_getTypeDescriptionDOM( $type, $kernelStrings );
		if ( PEAR::isError($dom) )
			return $dom;

		$xpath = xpath_new_context($dom);

		// Find section element
		//
		$section = xpath_eval( $xpath, "/TYPE/FIELDGROUP[@ID='$sectionID']" );

		if ( !count($section->nodeset) )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$section = $section->nodeset[0];

		// Protect Contact section
		//
		if ( $section->get_attribute(CONTACT_GROUPID) == CONTACT_CONTACTGROUP_ID )
			return null;

		// Delete section database fields
		//
		$sectionFields = xpath_eval( $xpath, CONTACT_FIELD, $section );
		foreach( $sectionFields->nodeset as $fieldElement )
			cm_deleteDbField( $type, $fieldElement->get_attribute(CONTACT_FIELD_TYPE), $fieldElement->get_attribute(CONTACT_DBFIELD), $kernelStrings );

		// Unlink section node
		//
		$section->unlink_node();

		// Dump settings
		//
		$res = cm_setTypeDescription( $type, $dom, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		return null;
	}

	function cm_sendEmailMessage( $messageData, &$kernelStrings, $cmStrings )
	//
	// Sends email message to contacts
	//
	//		Parameters:
	//			$messageData - array with message information
	//			$kernelStrings - Kernel localization strings
	//			$cmStrings - Contact Manager localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_cm_selectSpecificContacts;

		$messageData = trimArrayData( $messageData );

		// Check required fields
		//
		if ( PEAR::isError( $invalidField = findEmptyField( $messageData, array('TO', 'MESSAGE') ) ) ) {
			if ( $invalidField->getUserInfo() != 'TO' )
				$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];
			else
				$invalidField->message = $cmStrings['sm_recnotset_message'];

			return $invalidField;
		}

		$messageData['MESSAGE'] = str_replace( "\r\n", "\n", $messageData['MESSAGE'] );

		// Parse TO value
		//
		$toList = $messageData['TO'];
		$toList = explode( ";", $toList );

		$totalRecipients = 0;
		foreach ( $toList as $recipient ) {
			$recipient = trim($recipient);

			if ( strlen($recipient) )
				$totalRecipients++;
		}

		$recipientNum = EMAIL_MAX_RECEPIENTS_COUNT;

		if ( $totalRecipients > $recipientNum )
			return PEAR::raiseError( sprintf($cmStrings['sm_recipientlimit_message'], $recipientNum), ERRCODE_APPLICATION_ERR );

		// Send message to each recipient
		//
		foreach ( $toList as $recipient ) {
			$recipient = trim($recipient);

			if ( strlen($recipient) )
				sendWBSMail( null, $recipient, null, $messageData['SUBJECT'], $messageData['PRIORITY'], $messageData['MESSAGE'],
							$kernelStrings, null, null, null, false, $messageData['FROM'], null,
							true, false, true, false );
		}

		return null;
	}

	function cm_sendSMSMessage( $U_ID, $messageData, &$kernelStrings, $cmStrings )
	//
	// Sends email message to contacts
	//
	//		Parameters:
	//			$messageData - array with message information
	//			$kernelStrings - Kernel localization strings
	//			$cmStrings - Contact Manager localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_cm_selectSpecificContacts;

		$messageData = trimArrayData( $messageData );

		// Check required fields
		//
		if ( PEAR::isError( $invalidField = findEmptyField( $messageData, array('TO', 'MESSAGE') ) ) ) {
			if ( $invalidField->getUserInfo() != 'TO' )
				$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];
			else
				$invalidField->message = $cmStrings['sm_recnotset_message'];

			return $invalidField;
		}

		$messageData['MESSAGE'] = str_replace( "\r\n", "\n", $messageData['MESSAGE'] );

		// Parse TO value
		//
		$toList = $messageData['TO'];
		$toList = explode( ";", $toList );

		$totalRecipients = 0;
		foreach ( $toList as $recipient ) {
			$recipient = trim($recipient);

			if ( strlen($recipient) )
				$totalRecipients++;
		}

		if ( !is_null(MAX_SMS_RECIPIENT_NUM) )
			if ( $totalRecipients > MAX_SMS_RECIPIENT_NUM )
				return PEAR::raiseError( sprintf($cmStrings['sm_recipientlimit_message'], MAX_SMS_RECIPIENT_NUM), ERRCODE_APPLICATION_ERR );

		// Send message to each recipient
		//
		$result = array();
		foreach ( $toList as $recipient ) {
			$recipient = trim($recipient);

			if ( strlen($recipient) )
				if ( PEAR::isError( $ret = sendSMS( $U_ID, $recipient, $messageData['MESSAGE'], 'CM', $kernelStrings, getCompanyName() ) ) )
				{
					$temp_res = array();

					$temp_res["SMSH_PHONE"] = $recipient;
					$temp_res["SMSH_STATUS"] = "ERR";
					$temp_res["SMSH_STATUS_TEXT"] = $ret->getMessage();

					$ret = $temp_res;
				}

			$result[] = $ret;
		}

		return $result;
	}

	/**
	 * Get included contacts and return excluded
	 **/
	
	function cm_listLimitContacts ($currentUser, $kernelStrings, $included, $limit) {
		//
		//Exit if wrong parameters
		//
		if (!sizeof($included) || !is_numeric($limit)) return null;
		
		//
		//Init vars
		//
		global $qr_cm_selectAvailableContacts;
		global $qr_cm_selectAvailableContactsCount;
		global $qr_cm_selectAvailableContactsGlobal;
		global $qr_cm_selectAvailableContactsGlobalCount;
		global $qr_namesortclause;
		global $UR_Manager;
		
		//
		//Init params
		//
		$globalAdmin = $UR_Manager->IsGlobalAdministrator( $currentUser );
		$params = array( 'U_ID' => $currentUser);
		$limit = ($limit != 0)			//if limit == 0
		? sprintf (' LIMIT %s ', $limit )	//disable limitation
		: ''					//with ''. else
		;					//load limit settings
		
		//
		//Get total items count
		//
		$sql = $globalAdmin			//check if user is
		? $qr_cm_selectAvailableContactsGlobalCount //global admin
		: $qr_cm_selectAvailableContactsCount	//or simple user. sql
		;					//solve total counts
		
		$qr = db_query( $sql, $params );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		$row = db_fetch_array($qr);
		$ret['COUNT'] = $row['count'];
		
		$included = implode(", ",$included);
		
		//
		//Get included info
		//
		$sql = $globalAdmin ? $qr_cm_selectAvailableContactsGlobal : $qr_cm_selectAvailableContacts;
	
		//query, condition('not' or ''), included(array), sort, limit
		$query = sprintf($sql, '', $included, $qr_namesortclause, '');
		
		$qr = db_query( $query, $params );
		
		if ( PEAR::isError($qr) ) {
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}
		while ( $row = db_fetch_array($qr) ) {
			$result[$row['C_ID']] = getArrUserName($row, true, false, true);
		}
		
		$ret['INC'] = $result; unset($result);
		
		//
		//Get excluded info
		//
		
		//query, condition('not' or ''), included(array), sort, limit
		$query = sprintf($sql, 'NOT', $included, $qr_namesortclause, $limit);		
		
		$qr = db_query( $query, $params );
		
		if ( PEAR::isError($qr) ) {
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}
		
		while ( $row = db_fetch_array($qr) ) {
			$result[$row['C_ID']] = getArrUserName($row, true, false, true);
		}
		
		$ret['EXC'] = $result;  unset($result);
		//out array $ret consists of:
		//$ret['EXC'] - excluded ids
		//$ret['INC'] - included ids
		//$ret['COUNT'] - total ids count
		return $ret;
	}


	function cm_listAvailableContacts( $U_ID, &$kernelStrings)
	//
	// Returns a list of contacts which belongs to folders available to user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$limit - limit for select
	//			$array_ids - ID for select
	//			$sqlflag - false or true
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_cm_selectAvailableContacts;
		global $qr_cm_selectAvailableContactsCount;
		global $qr_cm_selectAvailableContactsGlobal;
		global $qr_cm_selectAvailableContactsGlobalCount;
		global $qr_namesortclause;
		global $UR_Manager;
		
		$result = array();
		
		$params = array( 'U_ID'=>$U_ID);
		$globalAdmin = $UR_Manager->IsGlobalAdministrator( $U_ID );
		
		$sql = $globalAdmin ? $qr_cm_selectAvailableContactsGlobal : $qr_cm_selectAvailableContacts;
		
		$query = sprintf($sql, $cond, $array_ids, $qr_namesortclause, $limit);
		
		$qr = db_query( $query, $params );
		
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		while ( $row = db_fetch_array($qr) )
			$result[$row['C_ID']] = getArrUserName($row, true, false, true);
		
		db_free_result($qr);
		
		return $result;
	}
	//
	// Signup functions
	//

	function cm_generateSignupForm( $fieldsData, $folder, $doptin, $lists, &$kernelStrings, &$cmStrings, $preview, $language )
	//
	// Generates the Contact Manager signup form
	//
	//		Parameters:
	//			$fieldsData - fields description
	//			$folder - folder to indert contact into
	//			$doptin - double opt-in flag
	//			$lists - a list of Contact Lists to include new contacts to
	//			$kernelStrings - Kernel localization strings
	//			$cmStrings - Contact Manager localization strings
	//			$preview - indicates that HTML code is generated for the preview window
	//			$language - user language
	//
	//		Returns string or PEAR_Error
	//
	{
		global $html_encoding;
		global $DB_KEY;
		global $CM_APP_ID;

		$typeDescription = getContactTypeDescription( CONTACT_BASIC_TYPE, $language, $kernelStrings, false );
		if ( PEAR::isError($typeDescription) )
			return $typeDescription;

		$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDescription, $kernelStrings, true );

		$filePath = sprintf( "%spublished/CM/includes/signupform.js", WBS_DIR );
		$javaScriptFile = implode( "", file($filePath) );

		$requiredFields = array();
		foreach ( $fieldsData as $fieldID=>$fieldData ) {
			$required = isset($fieldData[CM_SIGNUPFIELD_REQUIRED]) && $fieldData[CM_SIGNUPFIELD_REQUIRED];

			if ( $required )
				$requiredFields[] = $fieldID;
		}

		$result = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\r\n";
		$result .= "<html>\r\n";
		$result .= "<head>\r\n";
		$result .= "<title>Contact Manager Signup Form</title>\r\n";
		$result .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$html_encoding\">\r\n";
		$result .= "<script language=JavaScript>\r\n";
		$result .= $javaScriptFile;

		$result .= "\r\n";
		foreach ( $requiredFields as $field ) {
			$result .= sprintf("requiredFields[requiredFields.length] = \"%s\";\r\n", $field);
		}

		$result .= "</script>\r\n";
		$result .= "</head>\r\n\r\n";
		$result .= "<body onLoad=\"InitView();\">\r\n";

		$formAddress = cm_getSignupScriptAddress();

		$result .= "<form action='$formAddress' method=POST onSubmit=\"return ValidateForm();\">\r\n";

		$result .= "<h2>".$cmStrings['gsf_signupform_title']."</h2>\r\n";

		$result .= "<div id=errorblock style=\"padding-top:10px; padding-bottom:10px;display:none\">\r\n";
		$result .= "<script language=JavaScript>WriteErrorMessage();</script>";
		$result .= "</div>";

		$result .= "<div id=formblock style=\"padding-bottom:10px\">\r\n";
		$result .= "<table cellpadding=0 cellspacing=2 border=0>\r\n";
		foreach ( $fieldsData as $fieldID=>$fieldData ) {
			$required = isset($fieldData[CM_SIGNUPFIELD_REQUIRED]) && $fieldData[CM_SIGNUPFIELD_REQUIRED];

			$requiredMark = $required ? "*" : "";

			$result .= "<tr>\r\n";
			$result .= "<td>".prepareStrToDisplay($fieldData[CM_SIGNUPFIELD_NAME]).":$requiredMark&nbsp;</td>\r\n";

			$fieldDesc = $fieldsPlainDesc[$fieldID];

			if ($fieldDesc[CONTACT_FIELD_TYPE] != CONTACT_FT_MENU) {
				$fieldSize = $fieldData[CM_SIGNUPFIELD_WIDTH];
				$result .= "<td><input type=text id='contactData[$fieldID]' name='contactData[$fieldID]' size='$fieldSize'></td>\r\n";
			} else {
				$result .= "<td><select id='contactData[$fieldID] 'name='contactData[$fieldID]'>\r\n";

				$menuItems = $fieldDesc[CONTACT_MENU];
				$menuItems = explode( CONTACT_MENU_SEPARATOR, base64_decode($menuItems) );

				$result .= "<option value=\"\">".prepareStrToDisplay($kernelStrings['app_select_item'], true)."</option>\r\n";
				foreach ( $menuItems as $item ) {
					$item = prepareStrToDisplay($item, true);
					$result .= "<option value=\"$item\">$item</option>\r\n";
				}
				$result .= "</select></td>\r\n";
			}

			$result .= "</tr>\r\n";
		}
		$result .= "<tr><td colspan=2>&nbsp;</td></tr>\r\n";
		$result .= "</table>\r\n";

		if ( count($requiredFields) ) {
			$result .= "<div style=\"padding-bottom: 8px\">".$cmStrings['gsf_rqfields_label']."</div>\r\n";
		}

		$result .= "<input type=submit value=\"".$cmStrings['gsf_submit_btn']."\">\r\n";
		$result .= "</div>";

		$result .= "<div id=successblock style=\"padding-bottom:10px;display:none\">\r\n";
		$result .= "<p><b>".$cmStrings['gsf_thankyou_text']."</b></p>\r\n";

		if ( $doptin ) {
			$result .= "<div id=doptinblock style=\"display:none\">\r\n";
			$result .= "<p>".$cmStrings['gsf_conftext1_text']."<br>\r\n";
			$result .= $cmStrings['gsf_conftext2_text']." <b><span id=emaillabel></span></b><br>\r\n";
			$result .= $cmStrings['gsf_conftext3_text']."</p>\r\n";
			$result .= "</div>\r\n";
		}

		$result .= "</div>";

		$result .= "<input type=hidden name=DB_KEY value=\"".base64_encode($DB_KEY)."\">\r\n";
		$result .= "<input type=hidden name=lang value=\"".base64_encode($language)."\">\r\n";
		$result .= "<input type=hidden name=folder value=\"".base64_encode($folder)."\">\r\n";
		$result .= "<input type=hidden name=encoding value=\"".base64_encode($html_encoding)."\">\r\n";

		$result .= "<input type=hidden name=doptin value=\"".$doptin."\">\r\n";

		$lists = base64_encode( serialize($lists) );
		$result .= "<input type=hidden name=lists value=\"".$lists."\">\r\n";

		if ( $preview )
			$result .= "<input type=hidden name=preview value=\"1\">\r\n";

		$result .= "</form>\r\n";

		$result .= "</body>\r\n";
		$result .= "</html>";

		if ( !$preview ) {
			$signupData = array( 'fields'=>$fieldsData, 'folder'=>$folder, 'lists'=>$lists, 'language'=>$language, 'encoding'=>$html_encoding, 'doptin'=>$doptin );
			$signupData = base64_encode( serialize($signupData) );

			writeApplicationSettingValue( $CM_APP_ID, CM_SIGNUP_DATA, $signupData, $kernelStrings );
		}

		return $result;
	}

	function &cm_findSubscriberByEmail( $email, &$kernelStrings )
	//
	// Finds a subscriber contact by email
	//
	//		Parameters:
	//			$email - subscriber email
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns Contact object, or null, or PEAR_Error
	//
	{
		global $qr_cm_findSubscriberByEmail;

		$C_ID = db_query_result($qr_cm_findSubscriberByEmail, DB_FIRST, array('C_EMAILADDRESS'=>strtolower($email)));
		if ( PEAR::isError($C_ID) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( !strlen($C_ID) )
			return null;

		$Contact = new Contact( $kernelStrings );

		$res = $Contact->loadEntry( $C_ID, $kernelStrings );

		if ( PEAR::isError($res) )
			return $res;

		return $Contact;
	}

	function cm_signupContact( $DB_KEY, $CF_ID, $contactData, $lists, $doptin, &$kernelStrings, &$cmStrings, $createUsername = CM_SUBSCRIBERUSENAME  )
	//
	// Signes contact up
	//
	//		Parameters:
	//			$DB_KEY - database key
	//			$CF_ID - folder identifier
	//			$contactData - contact information
	//			$lists - array of lists to include contact
	//			$doptin - double opt-in flag
	//			$kernelStrings - Kernel localization strings
	//			$cmStrings - Contact manager localization strings
	//
	//		Returns C_ID or PEAR_Error
	//
	{
		global $qr_delete_contact_from_lists;
		global $html_encoding;
		global $wbs_robotemailaddress;
		global $cm_groupClass;

		$Contact = null;

		$action = ACTION_NEW;

		if ( isset($contactData[CONTACT_EMAILFIELD]) ) {
			$Contact = cm_findSubscriberByEmail( $contactData[CONTACT_EMAILFIELD], $kernelStrings );
			if ( PEAR::isError($Contact) )
				return $Contact;
		}

		if ( is_null($Contact) )
			$Contact = new Contact( $kernelStrings );
		else {
			$action = ACTION_EDIT;

			$contactData['C_ID'] = $Contact->C_ID;

			// Move contact to the new folder
			//
			if ( $CF_ID != $Contact->CF_ID ) {
				$typeDescription = getContactTypeDescription( CONTACT_BASIC_TYPE, LANG_ENG, $kernelStrings, false );
				$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDescription, $kernelStrings, true );

				$callbackParams = array( 'cmStrings'=>$cmStrings, 'fieldsPlainDesc'=>$fieldsPlainDesc );
				$res = $cm_groupClass->copyMoveDocuments( array($Contact->C_ID), $CF_ID, TREE_MOVEDOC, null, $kernelStrings,
															"cm_onAfterCopyMoveContact", null, $callbackParams,
															false, false );

				if ( PEAR::isError($res) )
					$errorStr = $res->getMessage();
			}

			// Remove contact from contact lists
			//
			db_query( $qr_delete_contact_from_lists, $Contact );
		}

		$contactData['C_MODIFYDATETIME'] = convertToSqlDateTime( time() );
		$contactData['C_MODIFYUSERNAME'] = $createUsername;

		if ( $action == ACTION_NEW ) {
			$contactData['C_CREATEDATETIME'] = convertToSqlDateTime( time() );
			$contactData['C_CREATEUSERNAME'] = $createUsername;
		}

		$C_ID = $Contact->addModContact( $action, $CF_ID, $contactData, $kernelStrings );
		if ( PEAR::isError($C_ID) )
			return $C_ID;

		if ( $action == ACTION_NEW ) {
			$isActiveSubscriber = $doptin ? false : true;

			$Contact->setSubscriberStatus( $isActiveSubscriber, $kernelStrings );
		}

		// Add contact to the contact lists
		//
		$ContactList = new ContactList();
		foreach ( $lists as $CL_ID )
			$ContactList->addContact( $CL_ID, $C_ID, CM_SUBSCRIBERUSENAME, $kernelStrings );

		if ( $doptin ) {
			// Send the confirmation message
			//
			$companyName = getCompanyName();
			//getSubscribeConfirmationLink( $DB_KEY, $Contact->C_ID );
			$confLink = Contact::getSubscribeLink($this->C_ID);

			$mail = new WBSMailer( false );

			$mail->SMTPAuth = false;

			$mail->CharSet = $html_encoding;;

			$mail->FromName = $companyName;
			$mail->From = $wbs_robotemailaddress;

			$mail->AddReplyTo($wbs_robotemailaddress);
			$mail->Sender = $wbs_robotemailaddress;

			$mail->IsHTML(false);

			$mail->Subject = $cmStrings['gsf_confemail_subject'];
			$mail->Body = sprintf( $cmStrings['gsf_confemail_text'], $companyName, $contactData[CONTACT_EMAILFIELD], $confLink );

			$mail->AddAddress($contactData[CONTACT_EMAILFIELD]);

			$mail->Send();
		}
		return $C_ID;
	}

	function cm_ubsubscribeContact( $C_ID, $emailHash, &$kernelStrings, &$cmStrings )
	//
	// Unsubscribes contact
	//
	//		Parameters:
	//			$C_ID - contact identifier
	//			$emailHash - email address hash function
	//			$kernelStrings - Kernel localization strings
	//			$cmStrings - Contact Manager localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_insert_unsubscriber;

		$Contact = new Contact($kernelStrings);
		$res = $Contact->loadEntry( $C_ID, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		if ( $emailHash != md5($Contact->C_EMAILADDRESS) )
			return;

		if ( $Contact->C_SUBSCRIBER == CM_SBST_ACTIVE || $Contact->C_SUBSCRIBER == CM_SBST_PENDING )
			aa_deleteContact( $C_ID, null, $kernelStrings, $cmStrings, LANG_ENG);

		$params = array();
		$params['ENS_EMAIL'] = trim( strtolower($Contact->C_EMAILADDRESS) );
		db_query($qr_insert_unsubscriber, $params);

		return null;
	}

	function cm_saveSubscriberProfile( $C_ID, $contactData, &$kernelStrings )
	//
	// Saves Contact Manager subscriber profile
	//
	//		Parameters:
	//			$C_ID - contact identifier
	//			$contactData - contact data to save
	//			$kernelStrings - Kernel localization strings
	//			$cmStrings - Contact Manager localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_cm_updateProfileFileds;

		$contactData['C_MODIFYUSERNAME'] = CM_SUBSCRIBERUSENAME;
		$contactData['C_ID'] = $C_ID;

		$res = db_query( $qr_cm_updateProfileFileds, $contactData );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	//
	// Report functions
	//

	function cm_getUnsubscribersList( $settings, &$kernelStrings, &$cmStrings )
	//
	// Returns array of the unsubscribed email recipients
	//
	//		Parameters:
	//			$settings - report settings
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_cm_selectUnsubscribedEmails;

		$sortModeIds = array( 0, 1, 2 );
		$sortModeNames = array( $cmStrings['runs_email_item'], $cmStrings['runs_dateasc_item'], $cmStrings['runs_datedesc_item'] );

		$sortStr = "ENS_EMAIL";

		switch ( $settings->sortMode )
		{
			case 0 : $sortStr = "ENS_EMAIL"; break;
			case 1 : $sortStr = "ENS_DATETIME DESC"; break;
			case 2 : $sortStr = "ENS_DATETIME ASC"; break;
		}

		if ( $settings->dateMode == 1 ) {
			$filter = sprintf("WHERE ENS_DATETIME >= '%s 00:00:00' AND ENS_DATETIME <= '%s 23:59:00'", $settings->from, $settings->to);
		} else
			$filter = null;

		$query = sprintf( $qr_cm_selectUnsubscribedEmails, $filter, $sortStr );

		$result = array();
		$qr = db_query( $query, array() );
		while ( $row = db_fetch_array($qr) ) {
			$row['ENS_DATETIME'] = convertToDisplayDateTime($row['ENS_DATETIME']);
			$result[] = $row;
		}

		db_free_result( $qr );

		return $result;
	}

	function cm_getSignupStatistics( $settings, &$kernelStrings, &$totalActive, &$totalInactive )
	//
	// Returns data for the Signup Statistics report
	//
	//		Parameters:
	//			$settings - report settings
	//			$kernelStrings - Kernel localization strings
	//			$cmStrings - Contact Manager localization strings
	//			$totalActive - total active subscribers
	//			$totalInactive - total inactive subscribers
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_cm_rep_selectActiveSubscribersByDate;
		global $qr_cm_rep_selectInctiveSubscribersByDate;
		global $qr_cm_rep_selectActiveSubscribersByDateRange;
		global $qr_cm_rep_selectInctiveSubscribersByDateRange;

		if ( $settings->type == 'days' ) {
			$activeSql = sprintf( $qr_cm_rep_selectActiveSubscribersByDate, $settings->days );
			$inactiveSql = sprintf( $qr_cm_rep_selectInctiveSubscribersByDate, $settings->days );
		} else {
			$fromDate = ($settings->from);
			$toDate = ($settings->to);
			$activeSql = sprintf( $qr_cm_rep_selectActiveSubscribersByDateRange, $fromDate, $toDate );
			$inactiveSql = sprintf( $qr_cm_rep_selectInctiveSubscribersByDateRange, $fromDate, $toDate );
		}

		$qr = db_query( $activeSql, array() );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$totalActive = 0;

		$result = array();

		while ( $row = db_fetch_array($qr) ) {
			$totalActive += $row['CNT'];
			$sqlDate = substr($row['C_CREATEDATETIME'], 0, 10);
			$date = convertToDisplayDate($sqlDate, true );

			if ( !isset($result[$sqlDate]) )
				$result[$sqlDate] = array( 'date'=>$date, 'active'=>$row['CNT'], 'inactive'=>null );
			else
				$result[$sqlDate]['active'] += $row['CNT'];
		}

		db_free_result( $qr );

		$qr = db_query( $inactiveSql, array() );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$totalInactive = 0;

		while ( $row = db_fetch_array($qr) ) {
			$totalInactive += $row['CNT'];
			$sqlDate = substr($row['C_CREATEDATETIME'], 0, 10);
			$date = convertToDisplayDate($sqlDate, true);

			if ( !isset($result[$sqlDate]) )
				$result[$sqlDate] = array( 'date'=>$date, 'active'=>null, 'inactive'=>$row['CNT'] );
			else
				$result[$sqlDate]['inactive'] += $row['CNT'];
		}

		ksort($result);
		db_free_result( $qr );

		return $result;
	}
	
	
	
	function cm_getUserOptionValue( $APP_ID, $user, $name )
	{
		if ( !isAdministratorID($user) )
			return getAppUserCommonValue( $APP_ID, $user, $name, null );
		else {
			if ( isset($_SESSION[$name]) )
				return $_SESSION[$name];
			else
				return null;
		}
	}

	function cm_setUserOptionValue( $APP_ID, $user, $name, $value, &$kernelStrings )
	{
		if ( !isAdministratorID($user) )
			setAppUserCommonValue( $APP_ID, $user, $name, $value, $kernelStrings );
		else
			$_SESSION[$name] = $value;
	}

?>
