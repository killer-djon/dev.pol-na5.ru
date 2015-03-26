<?php

	include_once (WBS_DIR."/kernel/classes/class.metric.php");
	//
	// Mail Master DBMS functions
	//

	function mm_getViewOptions( $U_ID, &$visibleColumns, &$viewMode, &$recordsPerPage, &$showSharedPanel, &$contentLimit, $kernelStrings, $useCookies = false )
	//
	//	Returns view options for specified user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$visibleColumns - array of visible columns
	//			$viewMode - view mode (MM_GRID_VIEW, MMM_LIST_VIEW)
	//			$recordsPerPage - number of files on one page
	//			$showSharedPanel - show shared panel in Document Depot window
	//			$contentLimit - visible content text length
	//			$kernelStrings - Kernel localization strings
	//			$useCookies - use cookies instead of database
	//
	//		Returns null
	//
	{
		global $mm_columns;
		global $MM_APP_ID;
		global $UR_Manager;

		$visibleColumns = array();

		$columns = getAppUserCommonValue( $MM_APP_ID, $U_ID, 'MMM_VISIBLECOLUMNS', null, $useCookies );
		if ( $columns != "none" ) {
			if ( strlen($columns) ) {
				$visibleColumns = explode( ";", $columns );
				foreach ($visibleColumns as $cKey => $cColumn)
					if (!in_array($cColumn, $mm_columns))
						unset($visibleColumns[$cKey]);
			}
			else
				$visibleColumns = array( MM_COLUMN_ID, MM_COLUMN_PRIORITY, MM_COLUMN_ATTACHEDFILES, MM_COLUMN_SUBJECT, MM_COLUMN_STATUS, MM_COLUMN_CREATEUSER, MM_COLUMN_CREATEDATE );
		} else
			$visibleColumns = array();

		$recordsPerPage = getAppUserCommonValue( $MM_APP_ID, $U_ID, 'MMM_RECORDPERPAGE', null, $useCookies );
		if ( !strlen($recordsPerPage) )
			$recordsPerPage = 30;

		$showSharedPanel = $UR_Manager->GetUserRightValue( $U_ID, "/ROOT/MM/FOLDERS/VIEWSHARES" ) == UR_BOOL_TRUE;

		$contentLimit =  getAppUserCommonValue( $MM_APP_ID, $U_ID, 'MMM_CONTENTLENGTH', null, $useCookies );
		if ( !strlen($contentLimit) )
			$contentLimit = 450;

		return null;
	}

	function mm_setViewOptions( $U_ID, $visibleColumns, $viewMode, $recordsPerPage, $showSharedPanel, $contentLimit, $kernelStrings, $useCookies = false )
	//
	//	Saves view options for specified user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$visibleColumns - array of visible columns
	//			$viewMode - view mode (MM_GRID_VIEW, MMM_LIST_VIEW)
	//			$recordsPerPage - number of files on one page
	//			$showSharedPanel - show shared panel in Document Depot window
	//			$contentLimit - visible content text length
	//			$kernelStrings - Kernel localization strings
	//			$useCookies - use cookies instead of database
	//
	//		Returns null
	//
	{
		global $MM_APP_ID;

		if ( !is_null($visibleColumns) ) {
			$visibleColumns = implode( ";", $visibleColumns );

			if ( !strlen($visibleColumns) )
				$visibleColumns = "none";

			setAppUserCommonValue( $MM_APP_ID, $U_ID, 'MMM_VISIBLECOLUMNS', $visibleColumns, $kernelStrings, $useCookies );
		}

		if ( !is_null($recordsPerPage) )
			setAppUserCommonValue( $MM_APP_ID, $U_ID, 'MMM_RECORDPERPAGE', $recordsPerPage, $kernelStrings, $useCookies );

		if ( !is_null($showSharedPanel) ) {
			if ( !$showSharedPanel )
				$showSharedPanel = 0;
			setAppUserCommonValue( $MM_APP_ID, $U_ID, $MM_APP_ID.TREE_SHOWWHAREDPANEL, $showSharedPanel, $kernelStrings, $useCookies );
		}

		if ( !is_null($contentLimit) )
			setAppUserCommonValue( $MM_APP_ID, $U_ID, 'MMM_CONTENTLENGTH', $contentLimit, $kernelStrings, $useCookies );
	}

	function mm_messageAddingPermitted( &$kernelStrings )
	//
	// Checks whether adding message is permitted
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		return null;
	}

	function mm_deleteMessage( $U_ID, $MM_ID, $MMF_ID, $kernelStrings, $mmStrings )
	//
	// Deletes message
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$MM_ID - note identifier
	//			$MMF_ID - folder identifier
	//			$kernelStrings - kernel localization strings
	//			$mmStrings - quick notes localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $MM_APP_ID;
		global $mm_treeClass;
		global $qr_mm_deleteMessage;
		global $qr_mm_deleteSentTo;
		global $qr_mm_deleteStat;

			$rights = $mm_treeClass->getIdentityFolderRights( $U_ID, $MMF_ID, $kernelStrings );
			if ( PEAR::isError($rights) )
				return $rights;

		if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) && $MMF_ID != 0 )
			return PEAR::raiseError( $mmStrings['amn_screen_nodelrights_message'] . " ($MM_ID)", ERRCODE_APPLICATION_ERR );

		$res = db_query( $qr_mm_deleteMessage, array( 'MMM_ID'=>$MM_ID ) );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$res = db_query( $qr_mm_deleteSentTo, array( 'MMM_ID'=>$MM_ID ) );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Delete note files
		//
		$msgAttachments = array( MM_ATTACHMENTS, MM_IMAGES );

		foreach ( $msgAttachments as $attType )
		{
			$attachmentsPath = mm_getNoteAttachmentsDir( $MM_ID, $attType);

			if ( file_exists($attachmentsPath) ) {
				$fileCount = 0;
				$totalSize = 0;

				dirInfo( $attachmentsPath, $fileCount, $totalSize );

				if ( $totalSize > 0 ) {
					$QuotaManager = new DiskQuotaManager();
					$QuotaManager->AddDiskUsageRecord( SYS_USER_ID, $MM_APP_ID, -1*$totalSize );
					$QuotaManager->Flush( $kernelStrings );
				}
			}

			@removeDir( $attachmentsPath );
		}

		$res = db_query( $qr_mm_deleteStat, array( 'MMM_ID'=>$MM_ID ) );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function mm_deleteMessages( $docList, $U_ID, $kernelStrings, $mmStrings )
	//
	// Deletes messages
	//
	//		Parameters:
	//			$docList - list of documents to delete
	//			$U_ID - user identifier
	//			$kernelStrings - kernel localization strings
	//			$mmStrings - quick notes localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $mm_treeClass;

		$del = array();

		foreach ( $docList as $MM_ID )
		{
			$acc = explode("\t", $MM_ID);
			if(empty($acc[1]))
			{
				$messageData = $mm_treeClass->getDocumentInfo( $MM_ID, $kernelStrings );
				if ( PEAR::isError($messageData) )
					return $messageData;

				$res = mm_deleteMessage( $U_ID, $MM_ID, $messageData['MMF_ID'], $kernelStrings, $mmStrings );
				if ( PEAR::isError( $res ) )
					return $res;
			}
			else
			{
			  $del[$acc[0]][] = $acc[1];
			}
		}
		if($del)
		{
			$res = mm_deleteMail( $del, $mmStrings );
			if ( PEAR::isError( $res ) )
				return $res;
		}
		return null;
	}

	function mm_onAfterCopyMoveMessage( $kernelStrings, $U_ID, $messageData, $operation, $params )
	//
	//	Completes message copy/move process
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$messageData - note data, record from MMMESSAGE table as array
	//			$operation - operation: TREE_COPYDOC, TREE_MOVEDOC
	//			$params - other parameters array
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_mm_insertNote;
		global $qr_mm_updateNoteLocation;
		global $qr_mm_maxNoteID;
		global $mm_lastID;
		global $DB_KEY, $metric;
		extract( $params );

		// check for inbox mode
		//
		$inboxMsg = explode("\t", $params['SRC_MMM_ID']);
		if(isset($inboxMsg[1]))
		{
			$acc = $inboxMsg[0];
			$uid = $inboxMsg[1];
			$account = mm_getAccountByEmail( $acc );
			if( PEAR::isError( $account ) )
				return $account;
			$message = mm_getMessage( $uid, $account );
			if( !PEAR::isError( $message ) && $message )
			{
				$message['MMC_CONTENT'] = str_replace(
					'&msg='.$acc.'~'.$uid.'&file=',
					'&msg='.$DST_MMM_ID.'&file=',
					$message['MMC_CONTENT']
					);

				$query = "INSERT INTO MMMESSAGE SET
					MMM_ID='$DST_MMM_ID',
					MMF_ID='".$messageData['MMF_ID']."',
					MMM_STATUS='".MM_STATUS_RECEIVED."',
					MMM_DATETIME='".$message['MMC_DATETIME']."',
					MMM_PRIORITY='".$message['MMC_PRIORITY']."',
					MMM_FROM='".mysql_real_escape_string($message['MMC_FROM'])."',
					MMM_TO='".mysql_real_escape_string($message['MMC_TO'])."',
					MMM_CC='".mysql_real_escape_string($message['MMC_CC'])."',
					MMM_SUBJECT='".mysql_real_escape_string($message['MMC_SUBJECT'])."',
					MMM_LEAD='".mysql_real_escape_string($message['MMC_LEAD'])."',
					MMM_CONTENT='".mysql_real_escape_string($message['MMC_CONTENT'])."',
					MMM_ATTACHMENT='".$message['MMC_ATTACHMENT']."',
					MMM_IMAGES='".$message['MMC_IMAGES']."',
					MMM_SIZE='".$message['MMC_SIZE']."',
					MMM_USERID='$U_ID',
					MMM_HEADER='".mysql_real_escape_string($message['MMC_HEADER'])."'
				";
				$res = execPreparedQuery( $query, array () );
				if( PEAR::isError( $res ) )
					return $res;

				$query = "DELETE FROM MMCACHE WHERE MMC_ACCOUNT='"
					.mysql_real_escape_string($acc)."' AND MMC_UID='"
					.mysql_real_escape_string($uid)."' LIMIT 1";
				$res = execPreparedQuery( $query, array () );
				if ( PEAR::isError( $res ) )
					return $res;

				return null;
			}
		}

		if ( isset($setMsgStatus) )
		{
			$messageData['MMM_STATUS'] = $setMsgStatus;
			$messageData['MMM_TO']= null;
			$messageData['MMM_FROM']= null;
		}

		if ( $SRC_MMF_ID != $DST_MMF_ID )
		{
			$messageData['MMM_CONTENT'] = str_replace( "MMF_ID=".base64_encode( $SRC_MMF_ID ),  "MMF_ID=".base64_encode( $DST_MMF_ID ), $messageData['MMM_CONTENT']  );
		}

		if ( $operation == TREE_COPYDOC )
		{
			$messageData['MMM_DATETIME'] = convertToSqlDateTime( time() );

			$messageData['MMM_CONTENT'] = str_replace( "MMM_ID=".$SRC_MMM_ID,  "MMM_ID=".$DST_MMM_ID, $messageData['MMM_CONTENT']  );

			$res = db_query( $qr_mm_insertNote, $messageData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			$metric = metric::getInstance();
			$metric->addAction($DB_KEY, $U_ID, 'MM', 'COMPOSE', 'ACCOUNT');
		}
		else
		{
			$res = db_query( $qr_mm_updateNoteLocation, $messageData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		$mm_lastID = $messageData['MMM_ID'];

		return null;
	}

	function mm_onDeleteFolder( $MMF_ID, $params )
	//
	// Callback function to execute on folder delete
	//
	//		Parameters:
	//			$MMF_ID - folder identifier
	//			$params - other parameters
	//
	//		Returns null or PEAR_Error
	//
	{
		extract( $params );

		global $mm_treeClass;

		$documents = $mm_treeClass->listFolderDocuments( $MMF_ID, $U_ID, "MMM_ID ASC", $kernelStrings );
		if ( PEAR::isError($documents) )
			return $documents;

		foreach ( $documents as $ID=>$document )
		{
			$attachmentsPath = mm_getNoteAttachmentsDir( $ID, MM_ATTACHMENTS );
			@removeDir( $attachmentsPath );

			$attachmentsPath = mm_getNoteAttachmentsDir( $ID, MM_IMAGES );
			@removeDir( $attachmentsPath );
		}

		return null;
	}

	function mm_searchMessages( $searchString, $U_ID, $sorting, $kernelStrings, $entryProcessor = null )
	//
	// Searches messages
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
		global $qr_mm_findNotes;
		global $qr_mm_findNotesGlobal;
		global $UR_Manager;

		$globalAdmin = $UR_Manager->IsGlobalAdministrator( $U_ID );

		$searchString = "%".strtolower( $searchString )."%";

		$params = array( 'SEARCHSTR'=>$searchString, 'SEARCHSTR1'=>$searchString, 'U_ID'=>$U_ID );

		$result = array();

		$sql = $globalAdmin ? $qr_mm_findNotesGlobal : $qr_mm_findNotes;

		$qr = db_query( sprintf($sql, $sorting), $params );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) ) {
			if ( !is_null($entryProcessor) )
				$result[$row->MMM_ID] = call_user_func( $entryProcessor, $row );
			else
				$result[$row->MMM_ID] = $row;
		}

		db_free_result( $qr );

		return $result;
	}

	function mm_listSelectedMessages( $noteIDs, $U_ID, $sorting, $kernelStrings, $entryProcessor = null )
	//
	// Returns messages with given IDs
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
		global $qr_mm_selectedNotes;

		$ids = sprintf( "MMM_ID in ('%s')", implode( "','", $noteIDs ) );

		$params = array();
		$params['U_ID'] = $U_ID;

		$qr = db_query( sprintf($qr_mm_selectedNotes, $ids, $sorting), $params );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array( $qr, DB_FETCHMODE_OBJECT ) )
			if ( !is_null($entryProcessor) )
				$result[$row->MMM_ID] = call_user_func( $entryProcessor, $row );
			else
				$result[$row->MMM_ID] = $row;

		db_free_result($qr);

		return $result;
	}

	function mm_getUserEmail( $U_ID, &$kernelStrings )
	//
	// Returns user name and email address
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_selectUser;

		$userData = db_query_result( $qr_selectUser, DB_ARRAY, array('U_ID'=>$U_ID) );
		if ( PEAR::isError($userData) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();
		$result['name'] = getArrUserName( $userData );
		$result['email'] = $userData["C_EMAILADDRESS"];

		return $result;
	}

	function mm_getSenders( $U_ID, &$kernelStrings )
	//
	//	Returns array of Senders. Current user included
	//
	//	Parameters:
	//
	//		$U_ID - user identifier
	//		$kernelStrings - kernel localization strings
	//
	//	Returns array or PEAR_Error
	//
	{
		global $qr_mm_selectSenders;
		global $html_encoding;
		global $language;

		$senders = array();

		$userData = mm_getUserEmail( $U_ID, $kernelStrings );
		$emailName=  $userData['name'];
		$emailAddress = $userData['email'];

		if ( strlen( $emailAddress ) )
			$senders[-1] = array( "MMS_ID" => -1, "MMS_FROM" => $emailName, "MMS_EMAIL" => $emailAddress, "MMS_REPLYTO" => $emailAddress, "MMS_RETURNPATH" => $emailAddress, "MMS_ENCODING"=>$html_encoding, "MMS_LANGUAGE"=>$language );

		$qr = db_query( $qr_mm_selectSenders );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		while ( $row = db_fetch_array($qr) )
			$senders[$row['MMS_ID']] = $row;

		db_free_result($qr);

		return $senders;
	}

	function mm_getSentCount( $kernelStrings, $time=false )
	//
	//	Returns count of recipients of today sent messages
	//
	//	Parameters:
	//
	//		$MMM_ID - message id
	//		$kernelStrings - kernel localization strings
	//
	//	Returns array or PEAR_Error
	//
	{
		global $qr_mm_selectSentMessagesCount;
		global $qr_mm_selectPendMessagesCount;

		if(!$time)
			$time = time();
		$date = convertToSqlDate( $time );

		$sent = db_query_result( $qr_mm_selectSentMessagesCount, DB_FIRST, array('DATE'=>$date) );
		if ( PEAR::isError($sent) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		$pend = db_query_result( $qr_mm_selectPendMessagesCount, DB_FIRST, array('DATE'=>$date) );
		if ( PEAR::isError($pend) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return intval( $sent + $pend );
	}

	function  mm_getTemplatesInFolders( $folders, &$kernelStrings, &$mmStrings )
	//
	//	Returns Templates messages from specified folders
	//
	//	Parameters:
	//
	//		$folders - array of folders Ids
	//		$mmStrings - kernel localization strings
	//		$kernelStrings - Mail Master localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_mm_selectTplsInFolders;

		$sql = sprintf( $qr_mm_selectTplsInFolders, sprintf( "'%s'", implode("','", $folders) ) );

		$res = db_query( $sql, array( ) );

		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result=array();

		while ( $row = db_fetch_array( $res ) )
		{
			if ( $row['MMM_SUBJECT'] == '' )
				$row['MMM_SUBJECT'] = $mmStrings['app_nosubject_text'];

			$result[$row["MMM_ID"]] = $row;
		}

		db_free_result($res);

		return $result;
	}

	function mm_getSentRecipients( $MMM_ID, &$bounced, &$opened, &$kernelStrings )
	//
	//	Returns recipients' statistic of sent message
	//
	//	Parameters:
	//
	//		$MMM_ID - message id
	//		$bounced - (return) count of bounced emails
	//		$opened - (return) count of opens
	//		$kernelStrings - kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_mm_selectSendStat;

		$sentTotal = 0;
		$bounced = 0;
		$opened = 0;

		$res = db_query( $qr_mm_selectSendStat, array( 'MMM_ID'=>$MMM_ID ) );

		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array( );

		while ( $row = db_fetch_array( $res ) )
		{
			$result[$row["MMMST_EMAIL"]] = $row;

			if ( $row['MMMST_OPENED'] == 1)
				++$opened;

			if ( $row['MMMST_STATUS'] == 1)
				++$bounced;

		}

		db_free_result($res);

		return $result;
	}

	function mm_listObjects( $ID, $U_ID, &$kernelStrings, &$mmStrings )
	//
	//	Returns array of desired type options
	//
	//		Parameters:
	//			$ID - object type
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$mmStrings - Kernel localization strings
	//
	//		Returns array or PEAR::Error
	//
	{
		if ( $ID != CM_OT_USERGROUPS && $ID != CM_OT_LISTS )
			return mm_listUsersInFolder( $ID, $U_ID, $kernelStrings, $mmStrings );
		else
		if ( $ID == CM_OT_USERGROUPS  )
			return mm_getGroups( $U_ID, $kernelStrings, $mmStrings );
		else
		if ( $ID == CM_OT_LISTS )
			return mm_getLists( $U_ID, $kernelStrings, $mmStrings );

		return array();
	}

	function mm_listUsersInFolder( $ID, $U_ID, &$kernelStrings, &$mmStrings )
	//
	//	Returns array of users in $ID folder
	//
	//		Parameters:
	//			$ID - folder identifier
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$mmStrings - Kernel localization strings
	//
	//		Returns array or PEAR::Error
	//
	{
		global $cm_groupClass;
		global $qr_mm_selectFolderContacts ;
		global $qr_namesortclause;

		$rights = $cm_groupClass->getIdentityFolderRights( $U_ID, base64_decode( $ID ), $kernelStrings );
		if( PEAR::isError($rights) )
			return $rights;

		if( $rights == TREE_NOACCESS )
			return array();

		$typeDescription = $fieldsPlainDesc = null;
		$ContactCollection = new contactCollection( $typeDescription, $fieldsPlainDesc );
		$ContactCollection->loadAsArrays  = true;

		$callbackParams = null;

		$res = $ContactCollection->loadFromContactFolder( base64_decode( $ID ), $qr_namesortclause, null, null, $callbackParams, null, $kernelStrings, true, true );

		if( PEAR::isError( $res ) )
			return $res;

		foreach( $ContactCollection->items as $cData )
		{
			$encodedID = base64_encode($cData['C_ID']);
			$cData['ID'] = 'CONTACT'.$encodedID;
			$cData['NAME'] = df_contactname($cData,false,false,true);
			$cData['OFFSET_STR'] = "";

			$result[$cData['ID']] = array( 'TYPE' => 'CONTACT', 'ID'=>$cData['ID'], 'NAME'=>$cData['NAME'], 'OFFSET'=> '', 'STYLE' => 'contactOption' );
		}

		return $result;
	}

	function mm_getGroups( $U_ID, &$kernelStrings, &$mmStrings )
	//
	//	Returns array of available Groups for U_ID user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$mmStrings - Kernel localization strings
	//
	//		Returns array or PEAR::Error
	//
	{
		global $CM_APP_ID;

		$canManageUsers = mm_canManageUsers( $U_ID );

		$result = array();

		if( $canManageUsers )
		{
			$groups = listUserGroups( $kernelStrings );

			if ( PEAR::isError($groups) )
				return $groups;

			foreach( $groups as $UG_ID=>$groupData )
			{
				$encodedID = base64_encode($UG_ID);
				$groupData['ID'] = CM_OT_USERGROUPS.$encodedID;
				$groupData['NAME'] = $groupData['UG_NAME'];
				$groupData->OFFSET_STR = "&nbsp;&nbsp;";

				$result[$groupData['ID']] = array( 'TYPE' => CM_OT_USERGROUPS, 'ID'=>$groupData['ID'], 'NAME'=>$groupData['UG_NAME'], 'OFFSET'=> $mmStrings['app_group_label'].':', 'STYLE' => 'groupOption' );
			}

		}

		return $result;
	}

	function mm_getLists( $U_ID, &$kernelStrings, &$mmStrings )
	//
	//	Returns array of available Lists for U_ID user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$mmStrings - Kernel localization strings
	//
	//		Returns array or PEAR::Error
	//
	{
		global $CM_APP_ID;

		$listsIsSupported = contactListsIsSupported();

		$result = array();

		if( $listsIsSupported )
		{
			$ContactListCollection = new ContactListCollection();

			$callbackParams = null;
			$ContactListCollection->loadAsArrays  = true;

			$res = $ContactListCollection->loadContactLists( 'CL_NAME ASC', null, null, $U_ID, $callbackParams, null, $kernelStrings );
			if( PEAR::isError( $res ) )
				return $res;

			foreach( $ContactListCollection->items as $key=>$listData )
			{
				$encodedID = base64_encode($listData['CL_ID']);
				$listData['ID'] = CM_OT_LISTS.$encodedID;
				$listData['NAME'] = $listData['CL_NAME'];
				$listData['OFFSET_STR'] = "&nbsp;&nbsp;";

				$result[$listData['ID']] = array( 'TYPE' => CM_OT_LISTS, 'ID'=>$listData['ID'], 'NAME'=>$listData['CL_NAME'], 'OFFSET'=> $mmStrings['app_list_label'].':', 'STYLE' => 'listOption' );
			}
		}

		return $result;
	}

	function mm_prepareFGLList( $U_ID, &$kernelStrings, &$mmStrings )
	//
	//	Prepares and returns Folders-Groups-List array
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$mmStrings - Kernel localization strings
	//
	//		Returns array or PEAR::Error
	//
	{
		global $cm_groupClass;
		global $CM_APP_ID;

		$canManageUsers = mm_canManageUsers( $U_ID );

		$listsIsSupported = contactListsIsSupported();

		$result = array();
		$access = null;
		$hierarchy = null;
		$deletable = null;

		$folders = $cm_groupClass->listFolders(
								$U_ID, TREE_ROOT_FOLDER, $kernelStrings, 0,
								false, $access, $hierarchy,
								$deletable, TREE_ONLYREAD, null, false, null, true, null,
								true
		);

		if( PEAR::isError($folders) )
			return $folders;

		if ( $listsIsSupported )
			$result[CM_OT_LISTS] = array( 'ID'=>CM_OT_LISTS, 'TYPE' => CM_OT_LISTS, 'NAME'=> $mmStrings['sm_lists_text'], 'OFFSET'=> '', 'BGIMAGE' => '../../../common/html/classic/images/listpic.gif', 'ACCESS' => 1, 'COLOR'=> '#000' );

		if ( $canManageUsers )
			$result[CM_OT_USERGROUPS] = array( 'ID'=>CM_OT_USERGROUPS, 'TYPE' => CM_OT_USERGROUPS, 'NAME'=> $mmStrings['sm_usersgroup_text'], 'OFFSET'=> '', 'BGIMAGE' => '../../../common/html/classic/images/user-group.gif', 'ACCESS' => 1, 'COLOR'=> '#000' );

		foreach( $folders as $CF_ID=>$folderData )
		{

			$encodedID = base64_encode($CF_ID);
			$folderData->ID = CM_OT_FOLDERS.$encodedID;

			if( $CF_ID == TREE_AVAILABLE_FOLDERS )
				continue;

			$result[$encodedID] = array(  'ID'=>$encodedID, 'TYPE' => CM_OT_FOLDERS, 'NAME'=> $folderData->CF_NAME, 'OFFSET'=> str_replace( " ", "&nbsp;&nbsp;&nbsp;", substr( $folderData->OFFSET_STR, 1 ) ), 'BGIMAGE' => '../../../common/html/classic/images/folder.gif', 'ACCESS' => $folderData->TREE_ACCESS_RIGHTS, 'COLOR'=> $folderData->TREE_ACCESS_RIGHTS == -1 ? '#aaa' : '#000' ) ;
		}

		return $result;
	}

	function mm_getAccounts( $U_ID )
	//
	//	Returns array of Accounts.
	//
	//	Parameters:
	//
	//		$U_ID - user identifier
	//		$kernelStrings - kernel localization strings
	//
	//	Returns array or PEAR_Error
	//
	{
		global $qr_mm_selectAccountsList;

		$accounts = array();

		$qr = db_query( $qr_mm_selectAccountsList );

		if( PEAR::isError($qr) )
			return PEAR::raiseError( 'Error executing query' );

		while( $row = db_fetch_array($qr) )
			$accounts[$row['MMA_ID']] = $row;

		db_free_result($qr);

		return $accounts;
	}

	function mm_getAccountByEmail( $MMM_ACCOUNT, &$kernelStrings )
	//
	//	Returns account params.
	//
	//	Parameters:
	//
	//	$kernelStrings - kernel localization strings
	//
	//	Returns array or PEAR_Error
	//
	{
		global $qr_mm_selectAccountByEmail;

		$qr = db_query( $qr_mm_selectAccountByEmail, array( 'MMM_ACCOUNT' => $MMM_ACCOUNT) );

		if( PEAR::isError($qr) )
			return $qr;

		$row = db_fetch_array($qr);

		db_free_result($qr);

		return $row;
	}

	function mm_listVirtualFolder( $box, $U_ID, $sorting, $kernelStrings, $startIndex, $count )
	//
	// List messages
	//
	//		Parameters:
	//			$action - what to do
	//			$U_ID - user identifier
	//			$sortStr - sorting string
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array of note objects or PEAR_Error
	//
	{
		switch( $box )
		{
			case 'draftBox':
				$qr = "SELECT * FROM MMMESSAGE WHERE MMM_STATUS='" . MM_STATUS_DRAFT . "' AND MMM_USERID='$U_ID' AND MMF_ID=0 ORDER BY $sorting LIMIT $startIndex, $count";
				break;
			case 'pendingBox':
				$qr = "SELECT * FROM MMMESSAGE WHERE MMM_STATUS='" . MM_STATUS_PENDING . "' AND MMM_USERID='$U_ID' AND MMF_ID=0 ORDER BY $sorting LIMIT $startIndex, $count";
				break;
			case 'sentBox':
				$qr = "SELECT * FROM MMMESSAGE WHERE (MMM_STATUS='" . MM_STATUS_SENT . "' OR MMM_STATUS='" . MM_STATUS_SENDING . "' OR MMM_STATUS='" . MM_STATUS_ERROR . "') AND MMM_USERID='$U_ID' AND MMF_ID=0 ORDER BY $sorting LIMIT $startIndex, $count";
				break;
			case 'templateBox':
				$qr = "SELECT * FROM MMMESSAGE WHERE MMM_STATUS='" . MM_STATUS_TEMPLATE . "' AND (MMM_USERID='$U_ID' OR MMM_USERID='Demo User') AND MMF_ID=0 ORDER BY $sorting LIMIT $startIndex, $count";
				break;
			case 'unsortedBox':
				$qr = "SELECT * FROM MMMESSAGE WHERE MMF_ID=0 AND MMM_USERID='$U_ID' ORDER BY $sorting LIMIT $startIndex, $count";
				break;
			default:
			  return array();
		}

		$res = db_query( $qr, array() );
		if( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		while( $row = db_fetch_array($res, DB_FETCHMODE_OBJECT ) )
			$result[$row->MMM_ID] = $row;

		db_free_result( $res );

		return $result;
	}

	function virtualFolderDocumentCount( $box, $U_ID, $kernelStrings )
	{
		switch( $box )
		{
			case 'draftBox':
				$qr = "SELECT COUNT(*) FROM MMMESSAGE WHERE MMM_STATUS='" . MM_STATUS_DRAFT . "' AND MMM_USERID='!U_ID!' AND MMF_ID=0";
				break;
			case 'pendingBox':
				$qr = "SELECT COUNT(*) FROM MMMESSAGE WHERE MMM_STATUS='" . MM_STATUS_PENDING . "' AND MMM_USERID='!U_ID!' AND MMF_ID=0";
				break;
			case 'sentBox':
				$qr = "SELECT COUNT(*) FROM MMMESSAGE WHERE (MMM_STATUS='" . MM_STATUS_SENT . "' OR MMM_STATUS='" . MM_STATUS_SENDING . "') AND MMM_USERID='!U_ID!' AND MMF_ID=0";
				break;
			case 'templateBox':
				$qr = "SELECT COUNT(*) FROM MMMESSAGE WHERE MMM_STATUS='" . MM_STATUS_TEMPLATE . "' AND (MMM_USERID='!U_ID!' OR MMM_USERID='Demo User') AND MMF_ID=0";
				break;
			case 'unsortedBox':
				$qr = "SELECT COUNT(*) FROM MMMESSAGE WHERE MMF_ID=0 AND MMM_USERID='!U_ID!'";
		}

		$count = db_query_result( $qr, DB_FIRST, array('U_ID'=>$U_ID) );
		if( PEAR::isError( $count ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $count;
	}

	function mm_getSendingMessages()
	{
		$qr_mm_selectSendingMessages = "SELECT MMM_ID, MMM_STATUS FROM MMMESSAGE WHERE MMM_STATUS='".MM_STATUS_PENDING."' OR MMM_STATUS='".MM_STATUS_SENDING."' OR MMM_STATUS='".MM_STATUS_ERROR."'";
		$qr = db_query( $qr_mm_selectSendingMessages );
		if( PEAR::isError( $qr ) )
			exit( $qr->getMessage() );

		$sending = array();
		while( $row = db_fetch_array($qr) )
			$sending[$row['MMM_ID']] = $row['MMM_STATUS'];

		db_free_result( $qr );

		return $sending;
	}

?>
