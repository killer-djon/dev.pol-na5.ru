<?php

	//
	// Quick Notes DBMS functions
	//

	function qn_getViewOptions( $U_ID, &$visibleColumns, &$viewMode, &$recordsPerPage, &$showSharedPanel, &$contentLimit, $kernelStrings, $useCookies = false )
	//
	//	Returns view options for specified user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$visibleColumns - array of visible columns
	//			$viewMode - view mode (QN_GRID_VIEW, QN_LIST_VIEW)
	//			$recordsPerPage - number of files on one page
	//			$showSharedPanel - show shared panel in Document Depot window
	//			$contentLimit - visible content text length
	//			$kernelStrings - Kernel localization strings
	//			$useCookies - use cookies instead of database
	//
	//		Returns null
	//
	{
		global $dd_columns;
		global $QN_APP_ID;
		global $UR_Manager;

		$visibleColumns = array();

		$columns = getAppUserCommonValue( $QN_APP_ID, $U_ID, 'QN_VISIBLECOLUMNS', null, $useCookies );
		if ( $columns != "none" ) {
			if ( strlen($columns) )
				$visibleColumns = explode( ";", $columns );
			else
				$visibleColumns = array( QN_COLUMN_CONTENT, QN_COLUMN_ATTACHEDFILES, QN_COLUMN_MODIFYUSER, QN_COLUMN_MODIFYDATE );
		} else
			$visibleColumns = array();

		$viewMode = getAppUserCommonValue( $QN_APP_ID, $U_ID, 'QN_VIEWMODE', false, $useCookies );
		if ($viewMode === null || $viewMode === false ) {
			$viewMode = QN_LIST_VIEW;
		}

		$recordsPerPage = getAppUserCommonValue( $QN_APP_ID, $U_ID, 'QN_RECORDPERPAGE', null, $useCookies );
		if ( !strlen($recordsPerPage) )
			$recordsPerPage = 30;

		$showSharedPanel = $UR_Manager->GetUserRightValue( $U_ID, "/ROOT/QN/FOLDERS/VIEWSHARES" ) == UR_BOOL_TRUE;

		$contentLimit =  getAppUserCommonValue( $QN_APP_ID, $U_ID, 'QN_CONTENTLENGTH', null, $useCookies );

		return null;
	}

	function qn_setViewOptions( $U_ID, $visibleColumns, $viewMode, $recordsPerPage, $showSharedPanel, $contentLimit, $kernelStrings, $useCookies = false )
	//
	//	Saves view options for specified user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$visibleColumns - array of visible columns
	//			$viewMode - view mode (QN_GRID_VIEW, QN_LIST_VIEW)
	//			$recordsPerPage - number of files on one page
	//			$showSharedPanel - show shared panel in Document Depot window
	//			$contentLimit - visible content text length
	//			$kernelStrings - Kernel localization strings
	//			$useCookies - use cookies instead of database
	//
	//		Returns null
	//
	{
		global $QN_APP_ID;
		
		if ( !is_null($visibleColumns) ) {
			$visibleColumns = implode( ";", $visibleColumns );

			if ( !strlen($visibleColumns) )
				$visibleColumns = "none";

			setAppUserCommonValue( $QN_APP_ID, $U_ID, 'QN_VISIBLECOLUMNS', $visibleColumns, $kernelStrings, $useCookies );
		}

		if ( !is_null($viewMode) )
			setAppUserCommonValue( $QN_APP_ID, $U_ID, 'QN_VIEWMODE', $viewMode, $kernelStrings, $useCookies );

		if ( !is_null($recordsPerPage) )
			setAppUserCommonValue( $QN_APP_ID, $U_ID, 'QN_RECORDPERPAGE', $recordsPerPage, $kernelStrings, $useCookies );

		if ( !is_null($showSharedPanel) ) {
			if ( !$showSharedPanel )
				$showSharedPanel = 0;
			setAppUserCommonValue( $QN_APP_ID, $U_ID, $QN_APP_ID.TREE_SHOWWHAREDPANEL, $showSharedPanel, $kernelStrings, $useCookies );
		}

		if ( strlen( $contentLimit ) && !ereg("^[0-9]+$", $contentLimit ) )
			$contentLimit = "450";

		setAppUserCommonValue( $QN_APP_ID, $U_ID, 'QN_CONTENTLENGTH', $contentLimit, $kernelStrings, $useCookies );
	}

	function qn_noteAddingPermitted( &$kernelStrings, &$qnStrings, $action )
	//
	// Checks whether adding note is permitted
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$qnStrings - quick notes localization strings
	//			$action - form action
	//
	//		Returns null or PEAR_Error
	//
	{
		global $currentUser;

		$limit = getApplicationResourceLimits( 'QN' );
		if ( $limit === null )
			return null;

		$sql = "SELECT COUNT(*) FROM QUICKNOTES";

		$res = db_query_result( $sql, DB_FIRST, array() );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( $action == ACTION_NEW )
		{
			if ( $res >= $limit )
			{
				if ( hasAccountInfoAccess($currentUser) )
					$Message = sprintf( $qnStrings['app_notelimit_message'], $limit )." ".getUpgradeLink( $kernelStrings );
				else
					$Message = sprintf( $qnStrings['app_notelimit_message'], $limit )." ".$kernelStrings['app_referadmin_message'];

				return PEAR::raiseError( $Message, ERRCODE_APPLICATION_ERR );
			}
		}
		else
		{
			if ( $res > $limit )
			{
				if ( hasAccountInfoAccess($currentUser) )
					$Message = sprintf( $qnStrings['app_notelimit_message'], $limit )." ".getUpgradeLink( $kernelStrings );
				else
					$Message = sprintf( $qnStrings['app_notelimit_message'], $limit )." ".$kernelStrings['app_referadmin_message'];

				return PEAR::raiseError( $Message, ERRCODE_APPLICATION_ERR );
			}
		}

		return null;
	}

	function qn_addModNote( $action, $U_ID, $noteData, $kernelStrings, $qnStrings )
	//
	// Adds/modifies note record
	//
	//		Parameters:
	//			$action - form action type - add/modify
	//			$U_ID - user identifier
	//			$noteData - array containing note data
	//			$kernelStrings - kernel localization strings
	//			$qnStrings - quick notes localization strings
	//
	//		Returns new note identifier or PEAR_Error
	//
	{
		global $qn_treeClass;
		global $qr_qn_maxNoteID;
		global $qr_qn_insertNote;
		global $qr_qn_updateNote;

		$rights = $qn_treeClass->getIdentityFolderRights( $U_ID, $noteData['QNF_ID'], $kernelStrings );
		if ( PEAR::isError($rights) )
			return $rights;

		if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) )
			return PEAR::raiseError( $qnStrings['amn_screen_noaddrights_message'], ERRCODE_APPLICATION_ERR );

		$requiredFields = array( 'QN_SUBJECT' );

		if ( PEAR::isError( $invalidField = findEmptyField($noteData, $requiredFields) ) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		$noteData = trimArrayData($noteData);

		$noteData['QN_CONTENT'] = strtr(ereg_replace("\r\n", "\n", $noteData['QN_CONTENT']), "\r", "\n");

		$cnt = true;
		while ( !($cnt === false) ) {
			$noteData['QN_CONTENT'] = str_replace( "\n\n\n", "\n\n", $noteData['QN_CONTENT'] );
			$cnt = strpos( $noteData['QN_CONTENT'], "\n\n\n" );
		}

		$noteData['QN_MODIFYUSERNAME'] = getUserName( $U_ID, true );
		$noteData['QN_MODIFYDATETIME'] = convertToSqlDateTime( time() );
		$noteData['QN_STATUS'] = TREE_DLSTATUS_NORMAL;

		$res = qn_noteAddingPermitted( $kernelStrings, $qnStrings, $action );
		if ( PEAR::isError($res) )
			return $res;

		if ( $action == ACTION_NEW ) {
			$QN_ID = db_query_result( $qr_qn_maxNoteID, DB_FIRST );
			$QN_ID = incID($QN_ID);

			$noteData['QN_ID'] = $QN_ID;

			$res = db_query( $qr_qn_insertNote, $noteData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return $noteData['QN_ID'];
		} else {
			$res = db_query( $qr_qn_updateNote, $noteData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return $noteData['QN_ID'];
		}
	}

	function qn_deleteNote( $U_ID, $QN_ID, $QNF_ID, $kernelStrings, $qnStrings )
	//
	// Deletes note
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$QN_ID - note identifier
	//			$QNF_ID - folder identifier
	//			$kernelStrings - kernel localization strings
	//			$qnStrings - quick notes localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $QN_APP_ID;
		global $qn_treeClass;
		global $qr_qn_deleteNote;

		$rights = $qn_treeClass->getIdentityFolderRights( $U_ID, $QNF_ID, $kernelStrings );
		if ( PEAR::isError($rights) )
			return $rights;

		if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) )
			return PEAR::raiseError( $qnStrings['amn_screen_nodelrights_message'], ERRCODE_APPLICATION_ERR );

		$res = db_query( $qr_qn_deleteNote, array( 'QN_ID'=>$QN_ID ) );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Delete note files
		//
		$attachmentsPath = qn_getNoteAttachmentsDir( $QN_ID );

		if ( file_exists($attachmentsPath) ) {
			$fileCount = 0;
			$totalSize = 0;

			dirInfo( $attachmentsPath, $fileCount, $totalSize );

			if ( $totalSize > 0 ) {
				$QuotaManager = new DiskQuotaManager();
				$QuotaManager->AddDiskUsageRecord( SYS_USER_ID, $QN_APP_ID, -1*$totalSize );
				$QuotaManager->Flush( $kernelStrings );
			}
		}

		@removeDir( $attachmentsPath );

		return null;
	}

	function qn_deleteNotes( $docList, $U_ID, $kernelStrings, $qnStrings )
	//
	// Deletes notes
	//
	//		Parameters:
	//			$docList - list of documents to delete
	//			$U_ID - user identifier
	//			$kernelStrings - kernel localization strings
	//			$qnStrings - quick notes localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qn_treeClass;

		foreach ( $docList as $QN_ID ) {
			$noteData = $qn_treeClass->getDocumentInfo( $QN_ID, $kernelStrings );
			if ( PEAR::isError($noteData) )
				return $noteData;

			$res = qn_deleteNote( $U_ID, $QN_ID, $noteData['QNF_ID'], $kernelStrings, $qnStrings );
			if ( PEAR::isError( $res ) )
				return $res;
		}

		return null;
	}

	function qn_onAfterCopyMoveNote( $kernelStrings, $U_ID, $noteData, $operation, $params )
	//
	//	Completes note copy/move process
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$noteData - note data, record from QUICKNOTES table as array
	//			$operation - operation: TREE_COPYDOC, TREE_MOVEDOC
	//			$params - other parameters array
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_qn_insertNote;
		global $qr_qn_updateNoteLocation;

		extract( $params );

		if ( $operation == TREE_COPYDOC ) {
			$noteData['QN_MODIFYUSERNAME'] = getUserName( $U_ID, true );
			$noteData['QN_MODIFYDATETIME'] = convertToSqlDateTime( time() );
			$noteData['QN_STATUS'] = TREE_DLSTATUS_NORMAL;

			$res = db_query( $qr_qn_insertNote, $noteData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		} else {
			$noteData['QN_MODIFYUSERNAME'] = getUserName( $U_ID, true );
			$noteData['QN_MODIFYDATETIME'] = convertToSqlDateTime( time() );

			$res = db_query( $qr_qn_updateNoteLocation, $noteData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		return null;
	}

	function qn_onDeleteFolder( $QNF_ID, $params )
	//
	// Callback function to execute on folder delete
	//
	//		Parameters:
	//			$QNF_ID - folder identifier
	//			$params - other parameters
	//
	//		Returns null or PEAR_Error
	//
	{
		extract( $params );
		global $QN_APP_ID;
		global $qn_treeClass;

		$documents = $qn_treeClass->listFolderDocuments( $QNF_ID, $U_ID, "QN_ID ASC", $kernelStrings );
		if ( PEAR::isError($documents) )
			return $documents;


		$QuotaManager = new DiskQuotaManager();

		foreach ( $documents as $ID=>$document ) {
			$attachmentsPath = qn_getNoteAttachmentsDir( $ID );

			$fileCount = 0;
			$totalSize = 0;
			dirInfo( $attachmentsPath, $fileCount, $totalSize );
			$QuotaManager->AddDiskUsageRecord( SYS_USER_ID, $QN_APP_ID, -1*$totalSize );

			@removeDir( $attachmentsPath );
		}

		$QuotaManager->Flush( $kernelStrings );

		return null;
	}

	function qn_searchNotes( $searchString, $U_ID, $sorting, $kernelStrings, $entryProcessor = null )
	//
	// Searches notes
	//
	//		Parameters:
	//			$searchString - text to find
	//			$U_ID - user identifier
	//			$sortStr - sorting string
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array of note objects or PEAR_Error
	//
	{
		global $qr_qn_findNotes;
		global $qr_qn_findGroupNotes ;
		global $qr_qn_globalFindNotes;
		global $UR_Manager;

		$isGlobalAdmin = $UR_Manager->IsGlobalAdministrator( $U_ID );

		$searchString = "%".strtolower( $searchString )."%";

		$params = array( 'SEARCHSTR'=>$searchString, 'SEARCHSTR1'=>$searchString, 'U_ID'=>$U_ID );

		$result = array();

		if ( !$isGlobalAdmin )
			$qr = db_query( sprintf($qr_qn_findNotes, $sorting), $params );
		else
			$qr = db_query( sprintf($qr_qn_globalFindNotes, $sorting), $params );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) ) {
			if ( $isGlobalAdmin )
				$row->TREE_ACCESS_RIGHTS = TREE_WRITEREAD;

			if ( !is_null($entryProcessor) )
				$result[$row->QN_ID] = call_user_func( $entryProcessor, $row );
			else
				$result[$row->QN_ID] = $row;
		}

		db_free_result( $qr );

		return $result;
	}


	function qn_listSelectedNotes( $noteIDs, $U_ID, $sorting, $kernelStrings, $entryProcessor = null )
	//
	// Returns notes with given IDs
	//
	//		Parameters:
	//			$noteIDs - list of IDs
	//			$U_ID - user identifier
	//			$sortStr - sorting string
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array of note objects or PEAR_Error
	//
	{
		global $qr_qn_selectedNotes;
		global $UR_Manager;

		$ids = sprintf( "QN_ID in ('%s')", implode( "','", $noteIDs ) );

		$params = array();
		$params['U_ID'] = $U_ID;

		$qr = db_query( sprintf($qr_qn_selectedNotes, $ids, $sorting), $params );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array( $qr, DB_FETCHMODE_OBJECT ) ) {
			$row->TREE_ACCESS_RIGHTS = $UR_Manager->GetUserRightValue( $U_ID, "/ROOT/QN/FOLDERS/".$row->QNF_ID );

			if ( !is_null($entryProcessor) )
				$result[$row->QN_ID] = call_user_func( $entryProcessor, $row );
			else
				$result[$row->QN_ID] = $row;
		}

		db_free_result($qr);

		return $result;
	}


	function qn_getTemplateList( $U_ID, $kernelStrings )
	//
	// Returns list of print templates
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - kernel localization strings
	//
	//		Returns list of print templates
	//
	{
		global $qr_qn_tplList;

		$qr = db_query( $qr_qn_tplList );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array( $qr ) )
			$result[$row['QNT_ID']] = $row;

		db_free_result($qr);

		 return $result;
	}


	function qn_getTemplate( $QNT_ID, $U_ID, $kernelStrings )
	//
	// Returns print template
	//
	//		Parameters:
	//			$QNT_ID - template identifier
	//			$U_ID - user identifier
	//			$kernelStrings - kernel localization strings
	//
	//		Returns print template
	//
	{
		global $qr_qn_getTpl;

		$qr = db_query( $qr_qn_getTpl, array( "QNT_ID" => (int) $QNT_ID ) );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$row = db_fetch_array( $qr );

		db_free_result($qr);

		return $row;
	}

	function qn_deleteTpl( $U_ID, $QNT_ID, $kernelStrings )
	{
		global $qr_qn_deleteTpl;

		$qr = db_query( $qr_qn_deleteTpl, array( "QNT_ID" => (int) $QNT_ID ) );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return true;
	}

	function qn_addModTpl( $action, $U_ID, $tplData, $kernelStrings, $qnStrings )
	//
	// Adds/modifies print template record
	//
	//		Parameters:
	//			$action - form action type - add/modify
	//			$U_ID - user identifier
	//			$tplData - array containing template data
	//			$kernelStrings - kernel localization strings
	//			$qnStrings - quick notes localization strings
	//
	//		Returns true or PEAR:Error
	//
	{
		global $qn_treeClass;
		global $qr_qn_insertTpl;
		global $qr_qn_updateTpl;

		$tplData = trimArrayData($tplData);

		$requiredFields = array( "QNT_NAME", "QNT_HTML" );
		if ( PEAR::isError( $invalidField = findEmptyField($tplData, $requiredFields) ) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];
			return $invalidField;
		}

		$tplData['QNT_MODIFYUSERNAME'] = getUserName( $U_ID, true );
		$tplData['QNT_MODIFYDATETIME'] = convertToSqlDateTime( time() );


		if ( $action == ACTION_NEW )
		{
			$res = db_query( $qr_qn_insertTpl, $tplData );
			if ( PEAR::isError($res) )
			{
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}

			return true;
		} else {
			$res = db_query( $qr_qn_updateTpl, $tplData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return true;
		}
	}

?>