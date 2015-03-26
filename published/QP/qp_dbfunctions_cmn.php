<?php

	//
	// Quick Pages DBMS functions
	//


	function qp_onCreateMoveFolder( $QPF_ID, $params )
	//
	//	Callback function to execute on folder copy or move
	//
	//		Parameters:
	//			$QPF_ID - folder ID
	//			$params - callback parameters
	//
	//		Returns PEAR:error or true
	//
	{
		global $qr_qp_updateFolderBook;

		$folderInfo = (array) $params['originalData'];
		$folderInfo["oldQPF_ID"] = $folderInfo["QPF_ID"];
		$folderInfo["QPF_ID"] = $QPF_ID;

		$res = db_query( $qr_qp_updateFolderBook, $folderInfo );

		if ( PEAR::isError($res) )
			return $res;

		return true;
	}

	function qp_onCreateFolder( $QPF_ID, $params )
	//
	//	Callback function to execute on folder create
	//
	//		Parameters:
	//			$QPF_ID - folder ID
	//			$params - callback parameters
	//
	//		Returns PEAR:error or true
	//
	{
		global $QP_APP_ID;
		global $qr_qp_updateFolderBook;
		global $_qpQuotaManager;

		$kernelStrings = $params["kernelStrings"];
		$qpStrings = $params["qpStrings"];

		$TotalUsedSpace = $params['TotalUsedSpace'];

		$folderInfo = (array) $params['originalData'];
		$folderInfo["oldQPF_ID"] = $folderInfo["QPF_ID"];
		$folderInfo["QPF_ID"] = $QPF_ID;

		$folderInfo["QPF_NAME"] = $folderInfo["QPF_NAME"]." (".$qpStrings["app_add_copy_label"].")";
		$folderInfo["QPF_TITLE"] = $folderInfo["QPF_TITLE"]." (".$qpStrings["app_add_copy_label"].")";


		$i = 1;
		while ( true )
		{
			$ret = qp_checkTextID( $kernelStrings, $qpStrings, $folderInfo["QPF_TEXTID"].$i, $folderInfo["QPB_ID"], "fake qpf id" );

			if ( !PEAR::isError( $ret ) )
				break;

			++$i;
		}

		$folderInfo["QPF_TEXTID"] = $folderInfo["QPF_TEXTID"].$i;

		$sourcePath = qp_getNoteAttachmentsDir( $folderInfo["QPF_UNIQID"] );
		$folderInfo["QPF_UNIQID"] = qp_generateUniqueID( (array) $params["U_ID"], $QPF_ID );
		$destPath = qp_getNoteAttachmentsDir( $folderInfo["QPF_UNIQID"] );

		$res = db_query( $qr_qp_updateFolderBook, $folderInfo );

		if ( PEAR::isError($res) )
			return $res;

		if ( !($handle = opendir($sourcePath)) )
			return true;

		while ( false !== ($name = readdir($handle)) )
		{
			if ( $name == "." || $name == ".." )
				continue;

			$fileName = $sourcePath.'/'.$name;

			$fileSize = filesize( $fileName );

			$TotalUsedSpace += $_qpQuotaManager->GetSpaceUsageAdded();

			// Check if the user disk space quota is not exceeded
			//
			if ( $_qpQuotaManager->SystemQuotaExceeded($TotalUsedSpace + $fileSize) )
				return $_qpQuotaManager->ThrowNoSpaceError( $kernelStrings );

			$destFilePath = $destPath.'/'.$name;

			$errStr = null;
			if ( !file_exists( $destPath ) )
				if ( !@forceDirPath( $destPath, $errStr ) )
					return PEAR::raiseError( $qpStrings['cm_screen_makedirerr_message'], ERRCODE_APPLICATION_ERR );

			if ( !@copy( $fileName, $destFilePath ) )
				return PEAR::raiseError( $qpStrings['cm_screen_copyerr_message'], ERRCODE_APPLICATION_ERR );

			$_qpQuotaManager->AddDiskUsageRecord( SYS_USER_ID, $QP_APP_ID, $fileSize );

		}

		closedir( $handle );

		return true;
	}

	function qp_onDeleteFolder( $QPF_ID, $params )
	//
	// Callback function to execute on folder delete
	//
	//		Parameters:
	//			$QPF_ID - folder identifier
	//			$params - other parameters
	//
	//		Returns null
	//
	{
		global $QP_APP_ID;

		$old = (array) $params["deletedFolderData"];

		if ( isset( $old["QPF_UNIQID"] ) && !is_null( $old["QPF_UNIQID"] ) && trim( $old["QPF_UNIQID"] ) != "" )
		{
			$attachmentsPath = qp_getNoteAttachmentsDir( trim( $old["QPF_UNIQID"] ) );

			if ( file_exists($attachmentsPath) )
			{
				$fileCount = 0;
				$totalSize = 0;

				dirInfo( $attachmentsPath, $fileCount, $totalSize );

				if ( $totalSize > 0 )
				{
					$QuotaManager = new DiskQuotaManager();
					$QuotaManager->AddDiskUsageRecord( SYS_USER_ID, $QP_APP_ID, -1*$totalSize );
					$QuotaManager->Flush( $kernelStrings );
				}

				@removeDir( $attachmentsPath );
			}
		}
		else
			$res = $params["deletedFolderData"];
			if ( PEAR::isError($res) )
				PEAR::raiseError( "FATAL: Trying to delete page which consists empty UNIQ_ID! ".$res->getMessage()."]" );
			else
				PEAR::raiseError( "FATAL: Trying to delete page which consists empty UNIQ_ID! [".$params["deletedFolderData"]["QPF_UNIQID"]."]" );

		return null;
	}

	function qp_onAfterCopyMoveNote( $kernelStrings, $U_ID, $noteData, $operation, $params )
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
		global $qr_qp_insertNote;
		global $qr_qp_updateNoteLocation;

		extract( $params );

		if ( $operation == TREE_COPYDOC ) {
			$noteData['QP_MODIFYUSERNAME'] = getUserName( $U_ID, true );
			$noteData['QP_MODIFYDATETIME'] = convertToSqlDateTime( time() );
			$noteData['QP_STATUS'] = TREE_DLSTATUS_NORMAL;

			$res = db_query( $qr_qp_insertNote, $noteData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		} else {
			$noteData['QP_MODIFYUSERNAME'] = getUserName( $U_ID, true );
			$noteData['QP_MODIFYDATETIME'] = convertToSqlDateTime( time() );

			$res = db_query( $qr_qp_updateNoteLocation, $noteData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		return null;
	}

	function qp_searchedWord( $word, $totalWords )
	{
		foreach( $totalWords as $searched )
		{
			$searched = trim( $searched );

			if ( $searched == "" )
				continue;

			if ( stristr( $word, $searched ) != false )
				return true;
		}

		return false;
	}

	function qp_boldText( $totalWords, $text )
	//
	//	Auxiliary function which makes substring $string bold in $text
	//
	//		Parameters:
	//			$string - substring to bold
	//			$text - text to search substring in
	//
	//		Returns null or PEAR_Error
	//
	{
		foreach( $totalWords as $string )
		{
			$string = trim( $string );

			if ( $string == "" )
				continue;

			$len = strlen( $string );
			$curPos = 0;

			while( true )
			{
				if ( $curPos > strlen( $text ) )
					break;

				$pos = strpos( strtolower( $text ), strtolower( $string ), $curPos );

				if ( false === $pos )
					break;

				$text = substr( $text, 0, $pos ) . "<b>". substr( $text, $pos, $len ) ."</b>" . substr( $text, $pos+$len, strlen( $text ) );

				$curPos = $pos + $len + 8;
			}
		}

		return $text;
	}

	function qp_searchPages( $public, $searchString, $currentBookID, $DB_KEY, $kernelStrings, $textBookID=null, $entryProcessor = null, $charsAround = 50, $numEntrance = 2 )
	//
	//	Search function.
	//
	//		Parameters:
	//			$public - flag indicating where the search will be produced e.g. in public pages or not.
	//			$searchString - substring to search,
	//			$currentBookID - current book identifier,
	//			$DB_KEY - database key,
	//			$kernelStrings - Kernel localization strings,
	//			$entryProcessor - callback function to process each founded page,
	//			$charsAround - how much chracters must be around searched substring,
	//			$numEntrance - how much entrances of substring wiil be shown
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_qp_searchPublicPages;
		global $qr_qp_searchPages;

		$searchQuery = $public ? $qr_qp_searchPublicPages : $qr_qp_searchPages;

		$pattern = "/\"(?P<groups>[^\"]*)\"/ui";
		preg_match_all( $pattern, $searchString, $matches );

		$quotedWords = $matches['groups'];

		foreach ( $quotedWords as $group )
			$searchString = str_replace( '"'.$group.'"', "", $searchString );

		$totalWords = array_merge( $quotedWords, explode( " ", $searchString ) );

		$groupsToFind = array();

		foreach ( $totalWords as $value )
			if ( strlen(trim($value)) )
				$groupsToFind[] = $value;

		$nameConstraints = array();
		$contentConstraints = array();

		foreach ( $groupsToFind as $index=>$group ) {
			$group = strtolower($group);
			$nameConstraints[] = sprintf( "LOWER(QPF_NAME) LIKE '!group%s!'", $index );
			$contentConstraints[] = sprintf( "LOWER(QPF_TEXT) LIKE '!group%s!'", $index );
		}

		$nameConstraints = implode( " AND ", $nameConstraints );
		$contentConstraints = implode( " AND ", $contentConstraints );

		$params = array( 'QPB_ID'=>$currentBookID );

		foreach ( $groupsToFind as $index=>$group ) {
			$params[sprintf('group%s', $index)] = "%".strtolower($group)."%";
		}

		$qr = db_query( sprintf($searchQuery, $nameConstraints, $contentConstraints), $params );

		$result = array();
		$params = array();

		$params['DB_KEY'] = $DB_KEY;

		if ( is_null($textBookID ) )
			$params['currentBookID'] = base64_encode( $currentBookID );
		else
			$params['BookID'] = $textBookID;

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) )
		{
			if ( is_null($textBookID ) )
				$params['curQPF_ID'] = base64_encode( $row->QPF_ID );
			else
				$params['PageID'] = $row->QPF_TEXTID;

			$params['searchString'] = "";

			$row->ROW_URL = prepareURLStr( $public ? PAGE_QP_PUBLISHED : PAGE_QP_QUICKPAGES, $params );

			if ( !is_null($entryProcessor) )
				$indResult[$row->QPF_ID] = call_user_func( $entryProcessor, $row );
			else
				$indResult[$row->QPF_ID] = $row;

			$row = $indResult[$row->QPF_ID];
			$text = $row->QPF_TEXT;
			$textLen = strlen( $text );
			$sLen = strlen( $searchString );

			$res = "";

			$sentences = preg_split( "/\r\n|\n|(\.\s+)/u", $text );

			foreach( $sentences as $key=>$value )
			{
				if ( $sentences[$key] == "" )
					unset($sentences[$key]);
			}

			$sentence = reset( $sentences );

			$res = "";

			$prevWord = "";
			$total = 0;

			$y = 0;
			$z = 0;

			$temp = "";

			while( true )
			{
				if ( !$sentence )
					break;

				$words = preg_split( "/(\s+)/u", $sentence );

				$wordPos = 0;
				$temp = "";

				if ( $y != 0 )
					$res .= ". ";

				foreach ( $words as $word )
				{
					$temp .= " ".$word;
					++$wordPos;

					if ( qp_searchedWord( $word, $totalWords ) )
					{

						if ( $y == 0 )
						{
							$res .= ( $wordPos <=8 ) ? " ".$temp : "&nbsp; ...".$word;
							$y = 8;

							$total += $wordPos;
						}
						else
						{
							$res .= " ".$word;

							++$total;

							if ( $word != $prevWord )
								$y = 8;
							else
								--$y;

						}

						$prevWord = $word;
					}
					else
					if ( $y>0 )
					{
						$res .= " ".$word;
						++$total;

						if ( --$y == 0 )
							$res .= "... ";
					}

					if ( $total > 90 )
						break 2;
				}

				$sentence = next( $sentences );
			}

			$row->TEXT = qp_boldText( $totalWords, str_replace(array('<', '>'), array('&lt;', '&gt;'), $res) );
//			$row->TEXT = qp_boldText( $totalWords, $res );
			$row->NAME = qp_boldText( $totalWords, $row->QPF_NAME );

			$result[$row->QPF_ID] = $row;
		}

		db_free_result( $qr );

		return $result;
	}


	function qp_listPublicSelectedNotes( $pageIDs, $bookID, $kernelStrings, $entryProcessor=null )
	//
	// Returns notes with given IDs
	//
	//		Parameters:
	//			$pageIDs - list of IDs
	//			$bookID - current book ID
	//			$kernelStrings - Kernel localization strings
	//			$entryProcessor - callback function to process each item,
	//
	//		Returns array of note objects or PEAR_Error
	//
	{
		global $qr_qp_selectedPublicNotes;

		$ids = sprintf( "QPF_ID in ('%s')", implode( "','", $pageIDs ) );

		$params = array( 'QPB_ID' => $bookID );

		$qr = db_query( sprintf($qr_qp_selectedPublicNotes, $ids ), $params );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array( $qr, DB_FETCHMODE_OBJECT ) )
		{
			if ( !is_null($entryProcessor) )
				$result[$row->QPF_ID] = call_user_func( $entryProcessor, $row );
			else
				$result[$row->QPF_ID] = $row;
		}
		db_free_result($qr);

		return $result;
	}

	function qp_deleteBook( $currentUser, $QPB_ID, $kernelStrings, $admin = false )
	//
	//	Delete entire book from the database
	//
	//		Parameters:
	//			$currentUser - current user identifier
	//			$QPB_ID - current book identifier,
	//			$kernelStrings - Kernel localization strings,
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qp_treeClass;
		global $qp_pagesClass;

		if ( $admin )
			$currentUser = null;

		$qp_pagesClass->currentBookID = $QPB_ID;

		$access = null;
		$hierarchy = null;
		$deletable = null;
		$addavailableFoldersP = false;

		$folders = $qp_pagesClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
													$access, $hierarchy, $deletable, null,
													null, false, null, $addavailableFoldersP, null, false, false );

		if ( PEAR::isError($folders) )
			return $folders;

		$alreadyDeleted = array ();
		foreach ( $folders as $QPF_ID=>$folderData )
		{
			$params = array();
			$params['U_ID'] = $currentUser;
			$params['kernelStrings'] = $kernelStrings;
			
			if (in_array($folderData->QPF_ID_PARENT, $alreadyDeleted)) {
				$alreadyDeleted[] = $folderData->QPF_ID;
				continue;
			}
			
			$res = $qp_pagesClass->deleteFolder( $QPF_ID, $currentUser, $kernelStrings, $admin, "qp_onDeleteFolder", $params );

			if ( PEAR::isError($res) )
				$errorStr = $res->getMessage();
			
			$alreadyDeleted[] = $folderData->QPF_ID;
		}

		$res = $qp_treeClass->deleteFolder( $QPB_ID, $currentUser, $kernelStrings, $admin );

		return $res;
	}

	function qp_checkTextID( $kernelStrings, $qpStrings, $textID, $bookID, $pageID=null )
	//
	//	Checks if Text ID already exists in the databse.
	//
	//		Parameters:
	//			$textID - text ID to be checked
	//			$bookID - book ID to search in
	//			$pageID - current page ID (if applicable)
	//			$kernelStrings - Kernel localization strings,
	//			$qpStrings - Quick Pages localization strings,
	//
	//		Returns true or PEAR_Error
	//
	{
		global $qr_qp_checkTextIdPages;
		global $qr_qp_checkTextIdBook;

		if ( $textID == "" )
				return PEAR::raiseError ( $kernelStrings[ERR_REQUIREDFIELDS], ERRCODE_APPLICATION_ERR );

		if ( !ereg( "^[a-zA-Z0-9-]+$", $textID ) )
				return PEAR::raiseError ( $qpStrings["app_textid_wrong_error"], ERRCODE_APPLICATION_ERR );

		if ( is_null( $pageID ) )
		{
			$params = array( 'QPB_TEXTID' => $textID, 'QPB_ID' => $bookID );

			$qr = db_query_result( $qr_qp_checkTextIdBook, DB_FIRST, $params );
		}
		else
		{
			$params = array( 'QPF_TEXTID' => $textID, 'QPB_ID' => $bookID, 'QPF_ID' => $pageID );

			$qr = db_query_result( $qr_qp_checkTextIdPages, DB_FIRST, $params );
		}
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( $qr >= 1 )
			return PEAR::raiseError( $qpStrings["app_textid_exists_error"], ERRCODE_APPLICATION_ERR );

		return true;
	}

	function qp_getID( $type, $textID, $QPB_ID=null )
	//
	// Returns internal ID for textID
	//
	//		Parameters:
	//
	//			$type - indicates type of textID: "book" or "page"
	//			$textID - text ID to search
	//			$QPB_ID - book ID to search page Id in
	//
	//		Returns internal id or PEAR_Error
	//
	{
		global $qr_qp_BookIdOnTxtId;
		global $qr_qp_PageIdOnTxtId;

		if ( $type == "book" )
		{
			$params = array( 'QPB_TEXTID' => $textID );
			$query = $qr_qp_BookIdOnTxtId;
		}
		else
		{
			$params = array( 'QPF_TEXTID' => $textID, 'QPB_ID' => $QPB_ID );
			$query = $qr_qp_PageIdOnTxtId;
		}

		$qr = db_query_result( $query, DB_FIRST, $params );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $qr;
	}

	function qp_getLastModified( $bookID, $pageID=null )
	//
	// Returns "last nodified" date for entire book or for a page
	//
	//		Parameters:
	//
	//			$bookID - book ID
	//			$pageID - page ID, null if interesting date is for the book
	//
	//		Returns date or PEAR_Error
	//
	{
		global $qr_qp_getBookLastModifiedDate;
		global $qr_qp_getPageLastModifiedDate;

		if ( $pageID != null )
		{
			$params = array( 'QPB_ID' => $bookID, 'QPF_ID' => $pageID );
			$query = $qr_qp_getPageLastModifiedDate;
		}
		else
		{
			$params = array( 'QPB_ID' => $bookID );
			$query = $qr_qp_getBookLastModifiedDate;
		}

		$qr = db_query_result( $query, DB_FIRST, $params );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $qr;
	}

	function qp_changePagePublishState( $QPF_ID, $state )
	//
	// Changes publish state for a page.
	//
	//		Parameters:
	//
	//			$QPF_ID - page ID
	//			$state - state to change (1 or 0)
	//
	//		Returns true or PEAR_Error
	//
	{
		global $qr_qp_updatePublishPage;

		$folderData = array( "QPF_ID"=>$QPF_ID, "QPF_PUBLISHED"=> $state );

		$res = db_query( $qr_qp_updatePublishPage, $folderData );

		if ( PEAR::isError($res) )
			return $res;

		return true;
	}

	function qp_changeBookPublishState( $QPB_ID, $state, $theme )
	//
	// Changes publish state for a book.
	//
	//		Parameters:
	//
	//			$QPB_ID - book ID
	//			$state - state to change (1 or 0)
	//
	//		Returns true or PEAR_Error
	//
	{
		global $qr_qp_updatePublishBook;

		if ( $state == 1 )
			$folderData = array( "QPB_ID"=>$QPB_ID, "QPB_PUBLISHED"=> $state, "QPB_THEME"=> intval( $theme ) );
		else
			$folderData = array( "QPB_ID"=>$QPB_ID, "QPB_PUBLISHED"=> $state, "QPB_THEME"=> null );

		$res = db_query( $qr_qp_updatePublishBook, $folderData );

		if ( PEAR::isError($res) )
			return $res;

		return true;
	}

	function getCountOfAppliedThemes( $kernelStrings )
	//
	// Gets count of applied themes grouped by theme id
	//
	//		Parameters:
	//
	//			$kernelStrings - kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_qp_getCountOfAppliedThemes;

		$res = db_query( $qr_qp_getCountOfAppliedThemes, array() );

		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array( $res ) )
			$result[$row["QPB_THEME"]] = $row["CNT"];

		db_free_result($res);

		return $result;
	}

	function qp_restorePages( $currentUser, $parentID, $hierarchy, $zip, $BookID, $backupData, $kernelStrings, $qpStrings )
	{
		global $qp_pagesClass;
		global $qr_qp_updateFolderBook;
		global $qp_extractTmpName__;
		global $qp_restoreArchive_ttlsize;
		global $QP_APP_ID;
		global $_qpQuotaManager;

		global $DB_KEY;

		if ( !is_array( $hierarchy ) )
			return true;

		$folders = (array)$backupData["FOLDERS"];
		$files = (array)$backupData["FILES"];

		foreach( $hierarchy as $key=>$value )
		{
			if ( isset( $folders[$key] ) )
			{
				$folderData = (array)$folders[$key];

				$folderData["QPF_ID"] = "";
				$folderData["QPB_ID"] = $BookID;
				$folderData["QPB_ID_PARENT"] = $BookID;

				$uniqID = $folderData["QPF_UNIQID"];

				$pfID = $qp_pagesClass->addmodFolder( ACTION_NEW, $currentUser, $parentID, prepareArrayToStore( $folderData ),
															$kernelStrings, false, null, array('qpStrings'=>$qpStrings, 'action'=>ACTION_NEW), true, false, null, $checkFolderName = false, "qp_checkPermissionsCallback" );

				if ( PEAR::isError( $pfID ) )
					return $pfID;

				$folderData["QPF_ID"] = $pfID;

				$folderData["QPF_UNIQID"] = qp_generateUniqueID( $currentUser, $pfID );

				$imgPath = "../../../publicdata/$DB_KEY/attachments/qp/attachments/";

				$folderData["QPF_CONTENT"] = preg_replace( '/(<[^>]*?="{0,1})([^">]*)'.$uniqID.'([^">]*?[^>]*>)/u', '$1'.$imgPath.$folderData["QPF_UNIQID"].'$3', $folderData["QPF_CONTENT"] );

				$folderData["QPF_TEXT"] = html2text( $folderData["QPF_CONTENT"] );


				$res = db_query( $qr_qp_updateFolderBook, prepareArrayToStore( $folderData ) );

				if ( PEAR::isError($res) )
					return $res;

				if ( isset( $files[$uniqID] ) )
					foreach( $files[$uniqID] as $fileValue )
					{
						$attachmentsPath = qp_getNoteAttachmentsDir( $folderData["QPF_UNIQID"] );

						$res = @forceDirPath( $attachmentsPath, $fdError );
						if ( !$res )
							return PEAR::raiseError( $kernelStrings[ERR_CREATEDIRECTORY] );

						// Extract file to the temporary dir
						//
						$tmpFileName = uniqid( TMP_FILES_PREFIX );
						$srcPath = WBS_TEMP_DIR."/".$tmpFileName;
						$qp_extractTmpName__ = $srcPath;

						$zip->extract( PCLZIP_OPT_PATH, WBS_TEMP_DIR, PCLZIP_OPT_BY_NAME, $uniqID."/".$fileValue, PCLZIP_CB_PRE_EXTRACT, "qp_postExtractCallBack" );

						$qp_restoreArchive_ttlsize += $_qpQuotaManager->GetSpaceUsageAdded();

						$fileSize = filesize( $srcPath );

						// Check if the user disk space quota is not exceeded
						//
						if ( $_qpQuotaManager->SystemQuotaExceeded($qp_restoreArchive_ttlsize + $fileSize) )
							return $_qpQuotaManager->ThrowNoSpaceError( $kernelStrings );

						$dstPath = $attachmentsPath."/".$fileValue;

						if ( !@copy( $srcPath, $dstPath ) )
							return PEAR::raiseError( $qnStrings['cm_screen_copyerr_message'] );

						$_qpQuotaManager->AddDiskUsageRecord( SYS_USER_ID, $QP_APP_ID, $fileSize );

					}
			}

			if ( is_array( $value ) )
			{
				$res = qp_restorePages( $currentUser, $pfID, $value, $zip, $BookID, $backupData, $kernelStrings, $qpStrings, $TotalUsedSpace );

				if ( PEAR::isError($res) )
					return $res;
			}
		}

		return true;
	}


	function qp_restoreArchive( $currentUser, $id, $fileName, $filePath, $kernelStrings, $qpStrings, &$resultStats )
	{
		global $qp_treeClass;
		global $qp_pagesClass;
		global $qp_extractTmpName__;
		global $qr_qp_updateBook;
		global $QP_APP_ID;
		global $qp_restoreArchive_ttlsize;
		global $_qpQuotaManager;

		@set_time_limit( 3600 );

		$messageStack = array();

		// Check if archives are supported
		//

		if ( !qp_archiveSupported() )
			return PEAR::raiseError( $qpStrings['app_nozlib_message'], ERRCODE_APPLICATION_ERR );

		// Load archive content
		//
		$zip = new PclZip($filePath);

		if ( ($list = $zip->listContent()) == 0 )
			return PEAR::raiseError( $qpStrings['rst_errorarchive_message'], ERRCODE_APPLICATION_ERR );

		$tmpFileName = uniqid( TMP_FILES_PREFIX.md5( uniqid(rand(), true ) ) );
		$qp_backup_file = WBS_TEMP_DIR."/".$tmpFileName;
		$qp_extractTmpName__ = $qp_backup_file;

		$zip->extract( PCLZIP_OPT_PATH, WBS_TEMP_DIR, PCLZIP_OPT_BY_NAME, "qp_backup_data", PCLZIP_CB_PRE_EXTRACT, "qp_postExtractCallBack" );

		$content = file_get_contents( $qp_backup_file );

		if ( !$content )
			return PEAR::raiseError( $qpStrings['rst_fileopen_error'] );

		$backupData = unserialize( base64_decode( $content ) );

		$bookData = (array)$backupData["BOOK_DATA"];
		$bookData["QPB_TEXTID"] = $id;

		$folderID = $qp_treeClass->addmodFolder( ACTION_NEW, $currentUser, TREE_ROOT_FOLDER, prepareArrayToStore($bookData),
														$kernelStrings, false, null, null, true, false, null, $checkFolderName = false );

		if ( PEAR::isError( $folderID ) )
			return $folderID;

		$bookData["QPB_ID"] = $folderID;
		$bookData["QPB_THEME"] = null;

		$res = db_query( $qr_qp_updateBook, $bookData );

		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$qp_pagesClass->currentBookID = $folderID;

		$_qpQuotaManager = new DiskQuotaManager();

		$qp_restoreArchive_ttlsize = $_qpQuotaManager->GetUsedSpaceTotal( $kernelStrings );
		if ( PEAR::isError($qp_restoreArchive_ttlsize) )
			return $TotalUsedSpace;

		$res = qp_restorePages( $currentUser, TREE_ROOT_FOLDER, $backupData["HIERARCHY"], $zip, $folderID, $backupData, $kernelStrings, $qpStrings );

		$_qpQuotaManager->Flush( $kernelStrings );

		if ( PEAR::isError($res) )
			return $res;

		setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_CURRENT_BOOK', base64_encode( $folderID ), $kernelStrings, $readOnly );

		return $folderID;
	}

	function qp_checkPermissionsCallback( $params, &$kernelStrings )
	//
	// Checks whether adding page is permitted
	//
	//		Parameters:
	//			$params - callback parameters
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $currentUser;

		extract($params);

		$limit = getApplicationResourceLimits( 'QP' );
		if ( $limit === null )
			return null;

		$sql = "SELECT COUNT(*) FROM QPFOLDER";

		$res = db_query_result( $sql, DB_FIRST, array() );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( $action == ACTION_NEW )
		{
			if ( $res >= $limit )
			{
				if ( hasAccountInfoAccess($currentUser) )
					$Message = sprintf( $qpStrings['app_pageslimit_message'], $limit )." ".getUpgradeLink( $kernelStrings );
				else
					$Message = sprintf( $qpStrings['app_pageslimit_message'], $limit )." ".$kernelStrings['app_referadmin_message'];

				return PEAR::raiseError( $Message, ERRCODE_APPLICATION_ERR );
			}
		}
		else
		{
			if ( $res > $limit )
			{
				if ( hasAccountInfoAccess($currentUser) )
					$Message = sprintf( $qpStrings['app_pageslimit_message'], $limit )." ".getUpgradeLink( $kernelStrings );
				else
					$Message = sprintf( $qpStrings['app_pageslimit_message'], $limit )." ".$kernelStrings['app_referadmin_message'];

				return PEAR::raiseError( $Message, ERRCODE_APPLICATION_ERR );
			}
		}

		return null;
	}

	function qp_getThemesList( $U_ID, $kernelStrings )
	//
	// Gets full themes list
	//
	//		Parameters:
	//			$U_ID - current user id
	//			$kernelStrings - kernel localization strings
	//
	//		Returns array of themes or PEAR_Error
	//
	{
		global $qr_qp_getAllThemesList;

		$qr = db_query( $qr_qp_getAllThemesList, array( "QPT_USERID"=>$U_ID ) );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array( $qr ) )
			$result[$row["QPT_ID"]] = $row;

		db_free_result($qr);

		return $result;
	}

	function qp_getApplicableThemesList( $U_ID, $kernelStrings )
	//
	// Gets applicable themes list
	//
	//		Parameters:
	//			$U_ID - current user id
	//			$kernelStrings - kernel localization strings
	//
	//		Returns array of themes or PEAR_Error
	//
	{
		global $qr_qp_getApplicableThemesList;

		$qr = db_query( $qr_qp_getApplicableThemesList, array( "QPT_USERID"=>$U_ID ) );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array( $qr ) )
			$result[$row["QPT_ID"]] = $row;

		db_free_result($qr);

		return $result;
	}

	function qp_getTheme( $U_ID, $QPT_ID, $kernelStrings )
	//
	// Gets themes data
	//
	//		Parameters:
	//			$U_ID - current user id
	//			$QPT_ID - themes id
	//			$kernelStrings - kernel localization strings
	//
	//		Returns theme data or PEAR_Error
	//
	{
		global $qr_qp_getTheme;

		$qr = db_query( $qr_qp_getTheme, array( "QPT_USERID"=>$U_ID, "QPT_ID"=>$QPT_ID ) );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$row = db_fetch_array( $qr );

		db_free_result($qr);

		return $row;
	}


	function qp_setThemeShareValue( $QPT_ID, $share, $kernelStrings )
	//
	// Sets themes share value
	//
	//		Parameters:
	//			$QPT_ID - themes id
	//			$share - share ( 0 - not shared, 1 - shared )
	//			$kernelStrings - kernel localization strings
	//
	//		Returns true or PEAR_Error
	//
	{
		global $qr_qp_updateThemeShare;
		global $qr_qp_updateBookThemes;

		$themeData = array();

		$themeData['QPT_ID'] = $QPT_ID;
		$themeData['QPT_SHARED'] = $share;

		$res = db_query( $qr_qp_updateThemeShare, prepareArrayToStore( $themeData ) );

		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( $share == 0 )
		{
			$param = array();

			$param['OLD_THEME'] = $QPT_ID;
			$param['NEW_THEME'] = null;

			$res = db_query( $qr_qp_updateBookThemes, $param );

			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		return true;
	}

	function qp_deleteTheme( $U_ID, $QPT_ID, $kernelStrings )
	//
	// Delete theme
	//
	//		Parameters:
	//			$QPT_ID - themes id
	//			$kernelStrings - kernel localization strings
	//
	//		Returns true or PEAR_Error
	//
	{
		global $qr_qp_deleteTheme;
		global $qr_qp_updateBookThemes;
		global $QP_APP_ID;

		$themeData = array();

		$theme = qp_getTheme( $U_ID, $QPT_ID, $kernelStrings );

		if ( PEAR::isError( $theme ) || is_null( $theme ) )
			return( $theme );

		$res = db_query( $qr_qp_deleteTheme, prepareArrayToStore( $theme ) );

		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( isset( $theme["QPT_UNIQID"] ) && !is_null( $theme["QPT_UNIQID"] ) && trim( $theme["QPT_UNIQID"] ) != "" )
		{
			$attachmentsPath = qp_getNoteAttachmentsDir( trim( $theme["QPT_UNIQID"] ) );

			if ( file_exists($attachmentsPath) )
			{
				$fileCount = 0;
				$totalSize = 0;

				dirInfo( $attachmentsPath, $fileCount, $totalSize );

				if ( $totalSize > 0 )
				{
					$QuotaManager = new DiskQuotaManager();
					$QuotaManager->AddDiskUsageRecord( SYS_USER_ID, $QP_APP_ID, -1*$totalSize );
					$QuotaManager->Flush( $kernelStrings );
				}

				@removeDir( $attachmentsPath );
			}
		}
		else
			PEAR::raiseError( "FATAL: Trying to delete page which consists empty UNIQID! [".$theme["QPT_UNIQID"]."]" );

		$param = array();

		$param['OLD_THEME'] = $QPT_ID;
		$param['NEW_THEME'] = null;

		$res = db_query( $qr_qp_updateBookThemes, $param );

		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return true;
	}

	function qp_copyTheme( $U_ID, $DB_KEY, $fromTheme, $name, $kernelStrings, $qpStrings )
	//
	//	Copies a theme to another new one.
	//
	//		Parameters:
	//			$U_ID - Webasyst User ID
	//			$DB_KEY - DB Key
	//			$fromTheme - Theme ID for copiing
	//			$name - Name for the new theme
	//			$kernelStrings - WebAsyst kernel localization strings,
	//			$qpStrings - Quick Pages localization strings,
	//
	//		Returns true or PEAR_Error
	//
	{
		global $QP_APP_ID;

		$themeData['QPT_NAME'] = $name;

		if ( PEAR::isError( $res = qp_checkThemeName( $kernelStrings, $qpStrings, $themeData['QPT_NAME'] ) ) )
			return $res;

		$theme = qp_getTheme( $U_ID, $fromTheme, $kernelStrings );

		if ( PEAR::isError( $theme ) )
			return $theme;

		if ( is_null( $theme ) )
			return PEAR::raiseError( $qpStrings["qpt_wrong_theme_message"], ERRCODE_APPLICATION_ERR );

		$fromUniqId = $theme["QPT_UNIQID"];

		$theme["QPT_NAME"] = $name;
		$theme["QPT_ID"] = "";
		$theme["QPT_SHARED"] = 1;
		$theme["QPT_UNIQID"] = qp_generateUniqueID( $U_ID, "THEME-".date("m-d-y H:m:s") );

		$newId = qp_addmodTheme( ACTION_NEW, "", $theme, $kernelStrings, $qpStrings );

		if ( PEAR::isError($newId) )
			return $newId;

		$theme["QPT_UNIQID"] = qp_generateUniqueID( $U_ID, "THEME-".$newId );

		$imgPath = "../../../publicdata/$DB_KEY/attachments/qp/attachments/";

		$theme["QPT_HEADER"] = preg_replace( '/(<[^>]*?="{0,1})([^">]*)'.$fromUniqId.'([^">]*?[^>]*>)/u', '$1'.$imgPath.$theme["QPT_UNIQID"].'$3', $theme["QPT_HEADER"] );

		$res = qp_addmodTheme( ACTION_EDIT, $newId, $theme, $kernelStrings, $qpStrings );

		if ( PEAR::isError($res) )
			return $res;

		$sourcePath = qp_getNoteAttachmentsDir( $fromUniqId );
		$destPath = qp_getNoteAttachmentsDir( $theme["QPT_UNIQID"] );

		$_qpQuotaManager = new DiskQuotaManager();

		$TotalUsedSpace = $_qpQuotaManager->GetUsedSpaceTotal( $kernelStrings );
		if ( PEAR::isError($TotalUsedSpace) )
			return $TotalUsedSpace;

		if ( !($handle = opendir($sourcePath)) )
			return $newId;

		while ( false !== ($name = readdir($handle)) )
		{
			if ( $name == "." || $name == ".." )
				continue;

			$fileName = $sourcePath.'/'.$name;

			$fileSize = filesize( $fileName );

			$TotalUsedSpace += $_qpQuotaManager->GetSpaceUsageAdded();

			// Check if the user disk space quota is not exceeded
			//
			if ( $_qpQuotaManager->SystemQuotaExceeded($TotalUsedSpace + $fileSize) )
			{
				$_qpQuotaManager->Flush( $kernelStrings );
				return $_qpQuotaManager->ThrowNoSpaceError( $kernelStrings );
			}

			$destFilePath = $destPath.'/'.$name;

			$errStr = null;
			if ( !file_exists( $destPath ) )
				if ( !@forceDirPath( $destPath, $errStr ) )
				{
					$_qpQuotaManager->Flush( $kernelStrings );
					return PEAR::raiseError( $qpStrings['cm_screen_makedirerr_message'], ERRCODE_APPLICATION_ERR );
				}

			if ( !@copy( $fileName, $destFilePath ) )
			{
				$_qpQuotaManager->Flush( $kernelStrings );
				return PEAR::raiseError( $qpStrings['cm_screen_copyerr_message'], ERRCODE_APPLICATION_ERR );
			}

			$_qpQuotaManager->AddDiskUsageRecord( SYS_USER_ID, $QP_APP_ID, $fileSize );
		}

		$_qpQuotaManager->Flush( $kernelStrings );
		closedir( $handle );

		return $newId;
	}

	function qp_addmodTheme( $action, $QPT_ID, $themeData, $kernelStrings, $qpStrings )
	//
	// Adds new or modifies existing theme
	//
	//		Parameters:
	//			$action - ACTION_NEW or ACTION_EDIT
	//			$QPT_ID - themes id
	//			$themeData- themes data
	//			$qpStrings - Quick Pages localization strings
	//			$kernelStrings - kernel localization strings
	//
	//		Returns true or PEAR_Error
	//
	{
		global $qr_qp_addTheme;
		global $qr_qp_modTheme;

		$themeData['QPT_ID'] = $QPT_ID;

		$requiredFields = array( "QPT_NAME" );

		if ( PEAR::isError( $res = findEmptyField( $themeData, $requiredFields ) ) )
				return PEAR::raiseError ( $kernelStrings[ERR_REQUIREDFIELDS], ERRCODE_APPLICATION_ERR );

		if ( ( $action == ACTION_NEW ) && PEAR::isError( $res = qp_checkThemeName( $kernelStrings, $qpStrings, $themeData["QPT_NAME"] ) ) )
			return $res;

		if ( ( $action == ACTION_EDIT ) && PEAR::isError( $res = qp_checkThemeName( $kernelStrings, $qpStrings, $themeData["QPT_NAME"], $QPT_ID ) ) )
			return $res;

		if ( $action == ACTION_NEW )
			$res = db_query( $qr_qp_addTheme, prepareArrayToStore( $themeData ) );
		else
			$res = db_query( $qr_qp_modTheme, prepareArrayToStore( $themeData ) );

		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( $action == ACTION_NEW )
			return mysql_insert_id();
		else
			return $QPT_ID;
	}

	function qp_updateThemeTopData( $themeData, $kernelStrings )
	//
	// Updates themes top frame data
	//
	//		Parameters:
	//			$themeData- themes data
	//			$kernelStrings - kernel localization strings
	//
	//		Returns true or PEAR_Error
	//
	{
		global $qr_qp_modThemeTopData;

		$res = db_query( $qr_qp_modThemeTopData, prepareArrayToStore( $themeData ) );

		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return true;
	}

	function qp_checkThemeName( $kernelStrings, $qpStrings, $themeName, $QPT_ID = null )
	//
	//	Checks if Text ID already exists in the databse.
	//
	//		Parameters:
	//			$themeName - theme name
	//			$kernelStrings - Kernel localization strings,
	//			$qpStrings - Quick Pages localization strings,
	//			$QPT_ID - Theme ID excluded from the check
	//
	//		Returns true or PEAR_Error
	//
	{
		global $qr_qp_checkThemeName;
		global $qr_qp_checkThemeNameExist;

		$params = array( 'QPT_NAME' => $themeName );

		if ( trim( $themeName ) == "" )
			return PEAR::raiseError ( $kernelStrings[ERR_REQUIREDFIELDS], ERRCODE_APPLICATION_ERR );

		if ( $themeName == "<default>" )
			return PEAR::raiseError( $qpStrings["qpt_themename_exists_error"], ERRCODE_APPLICATION_ERR );

		if ( !is_null( $QPT_ID ) )
		{
			$params["QPT_ID"] = $QPT_ID;
			$qr = db_query_result( $qr_qp_checkThemeNameExist, DB_FIRST, $params );
		}
		else
			$qr = db_query_result( $qr_qp_checkThemeName, DB_FIRST, $params );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( $qr >= 1 )
			return PEAR::raiseError( $qpStrings["qpt_themename_exists_error"], ERRCODE_APPLICATION_ERR );

		return true;
	}


	function qp_publishedBookCheckLogin( $KEY, $BookID )
	{
		if ( !isset( $_SESSION["QPPUBL_DBKEY"] )  ||  !isset( $_SESSION["QPPUBL_TEXTID"] )  || !isset( $_SESSION["QPPUBL_UID"] ) ||
				base64_decode( $KEY ) !=  $_SESSION["QPPUBL_DBKEY"] || $BookID != $_SESSION["QPPUBL_TEXTID"]
		)
		{
			session_unregister( "QPPUBL_DBKEY" );
			session_unregister( "QPPUBL_TEXTID" );
			session_unregister( "QPPUBL_UID" );

			redirectBrowser( "qplogin.php", array( "DB_KEY"=>$KEY, "BookID"=>$BookID ) );
			die();
		}

	}

?>
