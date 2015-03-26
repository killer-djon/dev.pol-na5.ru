<?php

	//
	// Quick Notes non-DMBS application functions
	//

	function qn_getNoteAttachmentsDir( $QN_ID )
	//
	// Returns directory containing attached note files
	//
	//		Parameters:
	//			$QN_ID - note identifier
	//
	//		Returns string containing path to directory
	//
	{
		$attachmentsPath = fixDirPath( QN_ATTACHMENTS_DIR );

		return sprintf( "%s/%s", $attachmentsPath, $QN_ID );
	}

	function qn_processFileListEntry( $entry )
	//
	// Callback function to prepare some DD file attributes to show in list
	//
	//		Parameters:
	//			$entry - source entry
	//
	//		Returns processed entry
	//
	{
		$entry->QN_MODIFYDATETIME = convertToDisplayDateTime($entry->QN_MODIFYDATETIME, false, true, true );

		$params = array();
		$params['QNF_ID'] = base64_encode($entry->QNF_ID);
		$params['QN_ID'] = $entry->QN_ID;
		$params[ACTION] = ACTION_EDIT;

		if ( isset($entry->TREE_ACCESS_RIGHTS) ) {
			if ( UR_RightsObject::CheckMask( $entry->TREE_ACCESS_RIGHTS, TREE_WRITEREAD ) )
				$entry->ROW_URL = prepareURLStr( PAGE_QN_ADDMODNOTE, $params );
			else
				$entry->ROW_URL = prepareURLStr( PAGE_QN_NOTE, $params );
		}

		// Attachments
		//
		$attachmentsData = listAttachedFiles( base64_decode($entry->QN_ATTACHMENT) );
		$attachedFiles = array();
		$attachedFiles_nolink = array();
		if ( count($attachmentsData) ) {
			for ( $i = 0; $i < count($attachmentsData); $i++ ) {
				$fileData = $attachmentsData[$i];
				$fileName = $fileData["name"];
				$fileSize = formatFileSizeStr( $fileData["size"] );

				$params = array( "QNF_ID"=>$entry->QNF_ID, "QN_ID"=>$entry->QN_ID, "fileName"=>base64_encode($fileName) );
				$fileURL = prepareURLStr( PAGE_QN_GETNOTEFILE, $params );

				$attachedFiles[] = sprintf( "<a href=\"%s\" target=\"_blank\">%s (%s)</a>", $fileURL, $fileData["screenname"], $fileSize );
				$attachedFiles_nolink[] = sprintf( "%s (%s)", $fileData["screenname"], $fileSize );
			}
		}
		if ( !count($attachedFiles) )
			$attachedFiles = null;
		else
			$attachedFiles = implode( ", ", $attachedFiles );

		$entry->ATTACHEDFILES = $attachedFiles;
		$entry->ATTACHEDFILES_NOLINK = implode( ", ", $attachedFiles_nolink );

		return $entry;
	}

	function qn_onCopyMoveNote( $QN_ID, $kernelStrings, $srcQNF_ID, $destQNF_ID, $operation, $noteData, $params )
	//
	//	Copies or moves note files
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$QN_ID - note identifier
	//			$srcQNF_ID - source folder identifier
	//			$destQNF_ID - destination folder identifier
	//			$operation - operation: TREE_COPYDOC, TREE_MOVEDOC
	//			$noteData - note data, record from QUICKNOTES table as array
	//			$params - other parameters array
	//
	//		Returns array or PEAR_Error
	//
	{
		global $_qnQuotaManager;
		global $QN_APP_ID;

		extract($params);

		if ( $operation == TREE_COPYDOC ) {
			$sourcePath = qn_getNoteAttachmentsDir( $old_doc_id );
			$destPath = qn_getNoteAttachmentsDir( $QN_ID );

			$res = qn_noteAddingPermitted( $kernelStrings, $qnStrings, ACTION_NEW );
			if ( PEAR::isError($res) )
				return $res;

			if ( !($handle = opendir($sourcePath)) )
				return array();

			while ( false !== ($name = readdir($handle)) ) {
				if ( $name == "." || $name == ".." )
					continue;

				$fileName = $sourcePath.'/'.$name;

				$fileSize = filesize( $fileName );

				$TotalUsedSpace += $_qnQuotaManager->GetSpaceUsageAdded();

				// Check if the user disk space quota is not exceeded
				//
				if ( $_qnQuotaManager->SystemQuotaExceeded($TotalUsedSpace + $fileSize) )
					return $_qnQuotaManager->ThrowNoSpaceError( $kernelStrings );

				$destFilePath = $destPath.'/'.$name;

				if ( !file_exists( $destPath ) ) {
					$errStr = null;
					if ( !@forceDirPath( $destPath, $errStr ) )
						return PEAR::raiseError( $qnStrings['cm_screen_makedirerr_message'] );
				}

				if ( !@copy( $fileName, $destFilePath ) )
					return PEAR::raiseError( $qnStrings['cm_screen_copyerr_message'] );

				$_qnQuotaManager->AddDiskUsageRecord( SYS_USER_ID, $QN_APP_ID, $fileSize );
			}

			closedir( $handle );
		}

		return array();
	}


	function qn_applyNameMapping( $template, $nameMap )
	//
	// Replaces all occurrences of the name map keys in file with the corresponding values
	//
	//		Parameters:
	//			$template - template text
	//			$nameMap - array with replacements
	//
	//		Returns string
	//
	{
		$template = trim( $template );

		foreach( $nameMap as $key=>$value )
			$template = str_replace($key, $value, $template );

		return $template;
	}

	function qn_applyTemplate( $qndata, $template )
	//
	// Apply template to a note
	//
	//		Parameters:
	//			$qndata - note's data
	//			$template - template text
	//
	//		Returns boolean
	//
	{
		$nameMap = array(
							"%FOLDER%" => $qndata['FOLDER_NAME'],
							"%SUBJECT%" => $qndata['QN_SUBJECT'],
							"%CONTENT%" => nl2br( $qndata['QN_CONTENT'] ),
							"%DATETIME%" => $qndata['QN_MODIFYDATETIME'],
							"%USERNAME%" => $qndata['QN_MODIFYUSERNAME'],
							"%FILELIST%" => $qndata['ATTACHEDFILES_NOLINK']
					);

		return qn_applyNameMapping( $template, $nameMap );
	}

?>