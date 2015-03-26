<?php
	//
	// Quick Pages non-DMBS application functions
	//


	function qp_getBookURL( $type = "", $level=3 )
	//
	// Returns url of published book
	//
	//		Parameters:
	//			$level - number of url level to reduce of th current url
	//
	//		Returns string containing path to directory
	//
	{
		$URL = dirname( getCurrentAddress() );

		$pathData = explodePath( $URL );

		if ( !strlen($pathData[count($pathData)-1]) )
			array_pop($pathData);

		for ( $i = 1; $i <= $level; $i++ )
			array_pop( $pathData );

		$fileName = "book";

		return implode("/", $pathData).'/QP/html/scripts/'.$fileName.'.php?';
	}

	function qp_getNoteAttachmentsDir( $QP_ID )
	//
	// Returns directory containing attached page files
	//
	//		Parameters:
	//			$QP_ID - page identifier
	//
	//		Returns string containing path to directory
	//
	{
		$attachmentsPath = fixDirPath( QP_ATTACHMENTS_DIR );

		return sprintf( "%s/%s", $attachmentsPath, $QP_ID );
	}

	function qp_processFileListEntry( $entry )
	//
	// Callback function to prepare some page attributes to show in list
	//
	//		Parameters:
	//			$entry - source entry
	//
	//		Returns processed entry
	//
	{
		global $currentBookID;

		$entry->QP_MODIFYDATETIME = convertToDisplayDateTime($entry->QP_MODIFYDATETIME, false, true, true );

		$params = array();

		$params['QPF_ID'] = base64_encode($entry->QPF_ID);
		$params['QP_ID'] = $entry->QP_ID;
		$params['currentBookID'] = base64_encode( $currentBookID );

		$params[ACTION] = ACTION_EDIT;

		if ( isset($entry->TREE_ACCESS_RIGHTS) ) {
			if ( UR_RightsObject::CheckMask( $entry->TREE_ACCESS_RIGHTS,  array( TREE_WRITEREAD, TREE_READWRITEFOLDER  ) ) )
				$entry->ROW_URL = prepareURLStr( PAGE_QP_ADDMODPAGE, $params );
			else
				$entry->ROW_URL = prepareURLStr( PAGE_QP_PAGE, $params );
		}

		// Attachments
		//
		$attachmentsData = listAttachedFiles( base64_decode($entry->QP_ATTACHMENT) );

		$attachedFiles = array();
		$attachedFiles_nolink = array();

		if ( count($attachmentsData) )
		{
			for ( $i = 0; $i < count($attachmentsData); $i++ ) {
				$fileData = $attachmentsData[$i];
				$fileName = $fileData["name"];
				$fileSize = formatFileSizeStr( $fileData["size"] );

				$params = array( "QPF_ID"=>base64_encode( $entry->QPF_ID ), "QP_ID"=>base64_encode( $entry->QP_ID ), "QPB_ID"=>base64_encode( $currentBookID ), "fileName"=>base64_encode($fileName) );
				$fileURL = prepareURLStr( PAGE_QP_GETPAGEFILE, $params );

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

	function qp_generateUniqueID( $U_ID, $folderID )
	//
	// Generates unique id for page
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$folderID - page identifier
	//
	//		Returns ID
	//
	{
		return base64_encode( $U_ID."+".time()."+".$folderID."+".uniqid( rand(), true ) );
	}

	function qp_isIn( $root, $node )
	//
	// Checks if $node is child of $parent
	//
	//		Parameters:
	//			$hierarchy - hierarchy of pages
	//			$parent - parent book identifier
	//			$hierarchy - node identifier
	//
	//		Returns boolean
	//
	{
		if ( !is_array( $root ) || is_null( $root ) )
			return false;

		foreach( $root as $key=>$value )
			if ( $key == $node || qp_isIn( $value, $node ) )
				return true;

		return false;
	}

	function qp_searchNode( $hierarchy, $parent )
	//
	// Search node in hierarchy
	//
	//		Parameters:
	//			$hierarchy - hierarchy of pages
	//			$parent - searched node
	//
	//		Returns false or node
	//
	{
		if ( !is_array( $hierarchy ) || is_null( $hierarchy ) )
			return false;

		foreach( $hierarchy as $key=>$value )
		{
			if ( $key == $parent )
				return $hierarchy[$key];

			if ( ( $node = qp_searchNode( $hierarchy[$key], $parent ) ) != false )
				return $node;
		}

		return false;
	}

	function qp_isChildOf( $hierarchy, $parent, $node )
	//
	// Checks if $node is child of $parent
	//
	//		Parameters:
	//			$hierarchy - hierarchy of pages
	//			$parent - parent book identifier
	//			$hierarchy - node identifier
	//
	//		Returns boolean
	//
	{
		if ( $parent == $node )
			return true;

		$pnode = qp_searchNode( $hierarchy, $parent );

		if ( !$pnode )
			return false;

		foreach( $pnode as $key=>$value )
			if ( $key == $node || qp_isIn( $value, $node ) )
				return true;

		return false;
	}

	function qp_getParentNode( $hierarchy, $search )
	//
	// Search node in hierarchy
	//
	//		Parameters:
	//			$hierarchy - hierarchy of pages
	//			$parent - searched node
	//
	//		Returns false or node
	//
	{
		if ( !is_array( $hierarchy ) || is_null( $hierarchy ) )
			return false;

		foreach( $hierarchy as $key=>$value )
		{
			if ( is_array( $value ) && in_array( $search, array_keys( $hierarchy ) ) )
				return $hierarchy;

			if ( ( $node = qp_getParentNode( $hierarchy[$key], $search ) ) != false )
				return $node;
		}

		return false;
	}


	function qp_getParentId( $hierarchy, $nodeid, &$currentid, $id="" )
	{
		if ( $nodeid == TREE_AVAILABLE_FOLDERS )
		{
			$currentid = TREE_AVAILABLE_FOLDERS;
			return true;
		}

		if ( $id == "" )
			$id= TREE_AVAILABLE_FOLDERS;

		if ( !is_array( $hierarchy ) || is_null( $hierarchy ) )
			return false;

		foreach( $hierarchy as $key=>$value )
		{
			if ( in_array( $nodeid, array_keys( $hierarchy ) ) )
			{
				$currentid = $id;
				return $hierarchy;
			}

			if ( ( $node = qp_getParentId( $hierarchy[$key], $nodeid, $currentid, $key ) ) != false )
				return $node;
		}

		return false;
	}


	function qp_hasItChilds( $hierarchy, $nodeid )
	//
	// Checks if $node is child of $parent
	//
	//		Parameters:
	//			$hierarchy - hierarchy of pages
	//			$parent - parent book identifier
	//			$hierarchy - node identifier
	//
	//		Returns boolean
	//
	{
		if ( $nodeid == TREE_AVAILABLE_FOLDERS )
			return true;

		if ( !( $ret = qp_getParentNode( $hierarchy, $nodeid ) ) )
			return false;

		return !( $ret[$nodeid] == NULL );
	}

	function qp_sortFolders( $a, $b )
	//
	// Sort folder function
	//
	//		Parameters:
	//			$a - page object
	//			$b - page object
	//
	//		Returns boolen
	//
	{
		if ($a->QPF_SORT == $b->QPF_SORT)
			return 0;

		return ($a->QPF_SORT > $b->QPF_SORT) ? -1 : 1;
	}


	function qp_copyFiles( $from, $to )
	//
	// Copy files from $from page to $to page.
	//
	//		Parameters:
	//			$from - from page identifier
	//			$to - to page identifier
	//
	//		Returns list of files or PEAR:error
	//
	{
		global $qr_qp_updateFolderBook;
		global $QP_APP_ID;

		$sourcePath = qp_getNoteAttachmentsDir( $from["QPF_UNIQID"] );
		$destPath = qp_getNoteAttachmentsDir( $to["QPF_UNIQID"] );

		$fromFiles = listAttachedFiles( base64_decode( $from["QPF_ATTACHMENT"] ) );

		$toList = base64_decode( $to["QPF_ATTACHMENT"] );

		$_qpQuotaManager = new DiskQuotaManager();

		$TotalUsedSpace = $_qpQuotaManager->GetUsedSpaceTotal( $kernelStrings );
		if ( PEAR::isError($TotalUsedSpace) )
			return $TotalUsedSpace;

		foreach( $fromFiles as $value )
		{

			$fileName = $sourcePath.'/'.$value["diskfilename"];

			$fileSize = filesize( $fileName );

			$TotalUsedSpace += $_qpQuotaManager->GetSpaceUsageAdded();

			// Check if the user disk space quota is not exceeded
			//
			if ( $_qpQuotaManager->SystemQuotaExceeded($TotalUsedSpace + $fileSize) )
			{
				$_qpQuotaManager->Flush( $kernelStrings );
				return $_qpQuotaManager->ThrowNoSpaceError( $kernelStrings );
			}

			$destFilePath = $destPath.'/'.$value["diskfilename"];

			$errStr = null;
			if ( !file_exists( $destPath ) )
				if ( !@forceDirPath( $destPath, $errStr ) )
				{
					$_qpQuotaManager->Flush( $kernelStrings );
					return PEAR::raiseError( $qpStrings['cm_screen_makedirerr_message'] );
				}

			if ( !@copy( $fileName, $destFilePath ) )
			{
				$_qpQuotaManager->Flush( $kernelStrings );
				return PEAR::raiseError( $qpStrings['cm_screen_copyerr_message'] );
			}

			$toList = addAttachedFile( $toList, $value );

			$_qpQuotaManager->AddDiskUsageRecord( SYS_USER_ID, $QP_APP_ID, $fileSize );
		}

		$_qpQuotaManager->Flush( $kernelStrings );

		return $toList;
	}

	function qp_archiveSupported()
	//
	// Checks if PHP has access to the ZLIB functions
	//
	//		Returns boolean
	//
	{
		return function_exists('gzopen');
	}

	function qp_preAddCallBack($p_event, &$p_header)
	//
	// Pre extract callback function
	//
	{
		global $qp_addTmpName__;

		$p_header['stored_filename'] = $qp_addTmpName__;

		return 1;
	}

	function qp_postExtractCallBack($p_event, &$p_header)
	//
	// Post extract callback function
	//
	{
		global $qp_extractTmpName__;

		$p_header['filename'] = $qp_extractTmpName__;

		return 1;
	}


	function qp_analyzeArchive( $filePath, $kernelStrings, $qpStrings )
	//
	// Analyzes archive. Returns number of files, images, folders, and total unpacked size
	//
	//		Parameters:
	//			$filePath - path to the archive
	//			$kernelStrings - Kernel localization strings
	//			$qpStrings - application localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		// Check if archives are supported
		//
		if ( !qp_archiveSupported() )
			return PEAR::raiseError( $qpStrings['app_nozlib_message'], ERRCODE_APPLICATION_ERR );

		// Load archive content
		//
		$zip = new PclZip($filePath);
			if ( ($list = $zip->listContent()) == 0 )
				return PEAR::raiseError( $qpStrings['rst_errorarchive_message'], ERRCODE_APPLICATION_ERR );

		// Count files and images
		//
		foreach ( $list as $index=>$element )
		{
				if ( $element['filename'] == "qp_backup_data" )
					return true;
		}

		return PEAR::raiseError( $qpStrings['rst_errorarchive_message'], ERRCODE_APPLICATION_ERR );

	}


	function qp_prepareLocArray( $arr, $qpStrings )
	{
		foreach( $arr as $key=>$value )
			$arr[$key] = isset ( $qpStrings[$value] ) ? $qpStrings[$value] : $value;

		return $arr;
	}
?>
