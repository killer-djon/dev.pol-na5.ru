<?php

	//
	// Mail Master non-DMBS application functions
	//

	function mm_getNoteAttachmentsDir( $MM_ID, $type = MM_ATTACHMENTS )
	//
	// Returns directory containing attached message files
	//
	//		Parameters:
	//			$MM_ID - note identifier
	//
	//		Returns string containing path to directory
	//
	{
		if ( $type == MM_ATTACHMENTS )
			$attachmentsPath = fixDirPath( MM_ATTACHMENTS_DIR );
		else
			$attachmentsPath = fixDirPath( MM_IMAGES_DIR );

		return sprintf( "%s/%s", $attachmentsPath, $MM_ID );
	}

	function mm_processFileListEntry( $entry )
	//
	// Callback function to prepare some MM file attributes to show in list
	//
	//		Parameters:
	//			$entry - source entry
	//
	//		Returns processed entry
	//
	{
		global $mm_statusNames, $mm_priorityNames, $mmStrings;

		$entry->MMM_DATETIME = convertToDisplayDateTime($entry->MMM_DATETIME, false, true, true );

		if ( $entry->MMM_SUBJECT == '' )
			$entry->MMM_SUBJECT =  $mmStrings['app_nosubject_text'];

		$params = array();
		$params['MMF_ID'] = base64_encode($entry->MMF_ID);
		$params['MMM_ID'] = $entry->MMM_ID;
		$params[ACTION] = ACTION_EDIT;

		$entry->MSGSTATUS = $mmStrings[$mm_statusNames[$entry->MMM_STATUS]];
		$entry->MSGPRIORITY = $mmStrings[$mm_priorityNames[$entry->MMM_PRIORITY]];

		if ( isset($entry->TREE_ACCESS_RIGHTS) ) {
			if ( UR_RightsObject::CheckMask( $entry->TREE_ACCESS_RIGHTS, array( TREE_WRITEREAD, TREE_READWRITEFOLDER ) ) )
				$entry->ROW_URL = prepareURLStr( PAGE_MM_ADDMODMESSAGE, $params );
			else
				$entry->ROW_URL = prepareURLStr( PAGE_MM_MESSAGE, $params );
		}

		// Attachments
		//

		$attachmentsData = listAttachedFiles( base64_decode($entry->MMM_ATTACHMENT) );

		$attachedFiles = array();
		$attachedFiles_nolink = array();
		if ( count($attachmentsData) ) {
			for ( $i = 0; $i < count($attachmentsData); $i++ ) {
				$fileData = $attachmentsData[$i];
				$fileName = $fileData["name"];
				$fileSize = formatFileSizeStr( $fileData["size"] );

				$params = array( "MMF_ID"=>$entry->MMF_ID, "MMM_ID"=>$entry->MMM_ID, "fileName"=>base64_encode($fileName) );
				$fileURL = prepareURLStr( PAGE_MM_GETMSGFILE, $params );

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

		$entry->SENDTO = array();

		if( $entry->MMM_SIZE )
			$entry->MMM_SIZE = formatFileSizeStr($entry->MMM_SIZE);
		else
			$entry->MMM_SIZE = '';

		return $entry;
	}

	function mm_onCopyMoveNote( $MM_ID, $kernelStrings, $srcMMF_ID, $destMMF_ID, $operation, $messageData, $params )
	//
	//	Copies or moves message files
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$MM_ID - note identifier
	//			$srcMMF_ID - source folder identifier
	//			$destMMF_ID - destination folder identifier
	//			$operation - operation: TREE_COPYDOC, TREE_MOVEDOC
	//			$messageData - note data, record from MMMESSAGE table as array
	//			$params - other parameters array
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_mm_maxNoteID, $_mmQuotaManager, $MM_APP_ID;

		extract($params);

		$MMM_ID = db_query_result( $qr_mm_maxNoteID, DB_FIRST ) + 1;

		// check for inbox mode
		//
		$inboxMsg = explode("\t", $params['MMM_ID'], 2);
		if( isset( $inboxMsg[1] ) )
		{
			$account = mm_getAccountByEmail( $inboxMsg[0] );
			if ( PEAR::isError( $account ) )
				return PEAR::raiseError( $account );
			$message = mm_getMessage( $inboxMsg[1], $account );
			if( !PEAR::isError( $message ) && $message )
			{
				if($message['MMC_ATTACHMENT'])
				{
					$oldPath = mm_getNoteAttachmentsDir( $message['MMC_ACCOUNT'].'~'.$message['MMC_UID'], 0 );
					$newPath = mm_getNoteAttachmentsDir( $MMM_ID, 0 );
					rename($oldPath, $newPath);
				}
				if($message['MMC_IMAGES'])
				{
					$oldPath = mm_getNoteAttachmentsDir( $message['MMC_ACCOUNT'].'~'.$message['MMC_UID'], 1 );
					$newPath = mm_getNoteAttachmentsDir( $MMM_ID, 1 );
					rename($oldPath, $newPath);
				}
			}
			return array( 'SRC_MMF_ID'=>$srcMMF_ID, 'DST_MMF_ID'=>$destMMF_ID, 'SRC_MMM_ID'=>$old_doc_id, 'DST_MMM_ID'=>$MMM_ID, 'MMM_MESSAGE'=>$message );
		}

		if ( $operation == TREE_COPYDOC )
		{
			$msgAttachments = array( MM_ATTACHMENTS, MM_IMAGES );

			foreach ( $msgAttachments as $attType )
			{
				$sourcePath = mm_getNoteAttachmentsDir( $old_doc_id, $attType );
				$destPath = mm_getNoteAttachmentsDir( $MMM_ID, $attType );

				if ( !($handle = opendir($sourcePath)) )
					return array();

				while ( false !== ($name = readdir($handle)) )
				{

					if ( $name == "." || $name == ".." )
						continue;

					$fileName = $sourcePath.'/'.$name;

					$fileSize = filesize( $fileName );

					$TotalUsedSpace += $_mmQuotaManager->GetSpaceUsageAdded();

					// Check if the user disk space quota is not exceeded
					//
					if ( $_mmQuotaManager->SystemQuotaExceeded($TotalUsedSpace + $fileSize) )
						return $_mmQuotaManager->ThrowNoSpaceError( $kernelStrings );

					$destFilePath = $destPath.'/'.$name;

					if ( !file_exists( $destPath ) ) {
						$errStr = null;
						if ( !@forceDirPath( $destPath, $errStr ) )
							return PEAR::raiseError( $mmStrings['cm_screen_makedirerr_message'] );
					}

					if ( !@copy( $fileName, $destFilePath ) )
						return PEAR::raiseError( $mmStrings['cm_screen_copyerr_message'] );

					$_mmQuotaManager->AddDiskUsageRecord( SYS_USER_ID, $MM_APP_ID, $fileSize );
				}

				closedir( $handle );
			}
		}

		return array( 'SRC_MMF_ID'=>$srcMMF_ID, 'DST_MMF_ID'=>$destMMF_ID, 'SRC_MMM_ID'=>$old_doc_id, 'DST_MMM_ID'=>$MMM_ID );
	}

	function mm_prepareLocArray( $arr, $mmStrings )
	//
	//	Changes string keys for loaclization strings
	//
	//	Parameters:
	//
	//		$arr - Array for localization
	//		$mmStrings - Mail Master strings
	//
	//	Returns:
	//
	//		Prepared array
	//
	//
	{
		foreach( $arr as $key=>$value )
			$arr[$key] = isset ( $mmStrings[$value] ) ? $mmStrings[$value] : $value;

		return $arr;
	}

	function mm_add_moveAttachedFile(  $attachSizeLimit, $file, $RECORD_FILES, $PAGE_DELETED_FILES, $PAGE_ATTACHED_FILES, $kernelStrings, $mmStrings )
	{
		if ( is_null( $attachSizeLimit ) )
			return add_moveAttachedFile( $file, $PAGE_ATTACHED_FILES, WBS_TEMP_DIR, $kernelStrings, true, "mm" );

		$newAttachedFiles = listAttachedFiles( $PAGE_ATTACHED_FILES );
		$curRecordFiles = removeAttachedFileList( $RECORD_FILES, $PAGE_DELETED_FILES );
		$recordAttachedFiles = listAttachedFiles( $curRecordFiles );

		$attachedFiles = array_merge( $newAttachedFiles, $recordAttachedFiles );

		$size = $file["size"];

		foreach( $attachedFiles as $fileItem )
			$size += $fileItem["size"];

		if ( $size > $attachSizeLimit * 1024 )
			return PEAR::raiseError( mm_getLimitationMessage( MM_OPT_ATTACHMENT_SIZE_LIMIT, $attachSizeLimit, $kernelStrings, $mmStrings ) );

		return add_moveAttachedFile( $file, $PAGE_ATTACHED_FILES, WBS_TEMP_DIR, $kernelStrings, true, "mm" );
	}

	function mm_canManageUsers( $U_ID )
	//
	// Checks whether the user has rights for managing users
	//
	//		Parameters:
	//			$U_ID - user identifier
	//
	//		Returns boolean
	//
	{
		$screens = listUserScreens($U_ID);

		if ( !isset($screens['AA']) )
			return  false;

		return in_array('UNG', $screens['AA']);
	}

	function mm_getLimitationOption( $optionName, &$kernelStrings )
	//
	// Returns the Mail Master limitation option value
	//
	//		Parameters:
	//			$optionName - the option name to return
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns integer or null
	//
	{
		global $MM_APP_ID, $DB_KEY;

		$resourceName = $optionName;

		if ( $optionName == MM_OPT_DAILY_RECIPIENTS_LIMIT )
			$resourceName = null;

			$limit = getApplicationResourceLimits( $MM_APP_ID, $resourceName );
			if ( is_null($limit) ) {
					$limit = getApplicationVariable( $DB_KEY, $MM_APP_ID, $optionName, $kernelStrings);
			} else {
				if ( $optionName == MM_OPT_ATTACHMENT_SIZE_LIMIT) // Override this value for hosting only
					$limit = 100;
			}
		
		return $limit;
	}

	function mm_getLimitationMessage( $optionName, $optionValue, &$kernelStrings, &$mmStrings )
	//
	// Returns the Mail Master limitation message
	//
	//		Parameters:
	//			$optionName - the option name to return
	//			$optionValue - current option value
	//			$kernelStrings - Kernel localization strings
	//			$mmStrings - Mail Master strings
	//
	//		Returns string or null
	//
	{
		global $DB_KEY, $MM_APP_ID, $currentUser;

		$plan = getApplicationHostingPlan( $MM_APP_ID );
		if ( is_null($plan) )
		{
			$limit = getApplicationVariable( $DB_KEY, $MM_APP_ID, MM_OPT_DAILY_RECIPIENTS_LIMIT, $kernelStrings );
			$plan = $limit == 50 ? 'FREE' : 'PAID';
		} else
			$limit = mm_getLimitationOption( $optionName, $kernelStrings );
//			getApplicationResourceLimits( $MM_APP_ID, $optionName );

		$plan = $plan == 'FREE' ? 'FREE' : 'PAID';

		$messages = array( 'FREE'=>array(
								MM_OPT_RECIPIENTS_LIMIT=>'sm_recipientlimitfree_message',
								MM_OPT_DAILY_RECIPIENTS_LIMIT=>'sm_dailylimitfree_message',
								MM_OPT_SENDNOW_RECIPIENTS_LIMIT=>'sm_sendnowfree_message',
								MM_OPT_ATTACHMENT_SIZE_LIMIT=>'app_attachment_size_error'),
							'PAID'=>array(
								MM_OPT_RECIPIENTS_LIMIT=>'sm_recipient_exceeded_message',
								MM_OPT_DAILY_RECIPIENTS_LIMIT=>'sm_daily_quotaexceeded_message',
								MM_OPT_SENDNOW_RECIPIENTS_LIMIT=>'sm_sendnow_limitation_error',
								MM_OPT_ATTACHMENT_SIZE_LIMIT=>'app_attachment_size_error'),
							);

		if ( $optionName != MM_OPT_ATTACHMENT_SIZE_LIMIT )
		{
			if ( hasAccountInfoAccess($currentUser) )
				return sprintf( $mmStrings[$messages[$plan][$optionName]], $limit )." ".getUpgradeLink( $kernelStrings );
			else
				return sprintf( $mmStrings[$messages[$plan][$optionName]], $limit )." ".$kernelStrings['app_referadmin_message'];
		}
		else
			return sprintf( $mmStrings[$messages[$plan][$optionName]], $limit );
	}

// *****************************************************************************

	function mm_getMessage( $uid, $account )
	//
	// Connet to remote POP3/IMAP server, get one message and cache it
	//
	//		Parameters:
	//      $id - number of message (from 1 to n)
	//      $uid - Uniq ID (for check)
	//			$account - remote POP3/IMAP server parameters
	//
	//		Returns messages list
	//
	{
		global $kernelStrings, $mmStrings, $log;

		if( $account['MMA_INTERNAL'] )
			$account['MMA_EMAIL'] .= '@' . $account['MMA_DOMAIN'];

		$cache = new mm_cache();
		//
		// Query to cache first
		//
		$message = $cache->loadMessageFromCache( $account['MMA_EMAIL'], $uid );
		if( PEAR::isError( $message ) )
			return $message;

		if( !$message['MMC_CONTENT'] )
		{
			$params = mm_getConnectParams( $account );
			$params['uid'] = $uid;

			$connect = socketMailOpen( $params );
			if( PEAR::isError( $connect ) )
				return $connect;

			$newMsg = socketMailGetMsg( $connect, $params );

			socketMailClose( $connect, $params );

			if( !$newMsg ) {
				$query = "DELETE FROM MMCACHE WHERE MMC_ACCOUNT='"
					.mysql_real_escape_string($account['MMA_EMAIL'])."' AND MMC_UID='"
					.mysql_real_escape_string($uid)."' LIMIT 1";
				execPreparedQuery($query, array());
				return array();
			}
			$decoder = new mm_mimeDecode( $newMsg );
			$obj = $decoder->decode();

			if( preg_match( '/^\s*"?([a-z\/]+)"?;\s* charset="?([a-z0-9-]+)"?/i', $obj->headers['content-type'], $match ) )
			{
				$obj->headers['content-type'] = $match[1];
				$charset = $match[2];
			}
			$message['MMC_TO'] = decodeHeaderLine( $obj->headers['to'], $charset );
			if(!empty($obj->headers['cc']))
				$message['MMC_CC'] = decodeHeaderLine( $obj->headers['cc'], $charset );
			else
				$message['MMC_CC'] = '';
			if(!empty($obj->headers['reply-to']))
				$message['MMC_REPLY_TO'] = decodeHeaderLine( $obj->headers['reply-to'], $charset );
			else
				$message['MMC_REPLY_TO'] = decodeHeaderLine( $obj->headers['from'], $charset );

			$body = null;
			$body = parseBody( $obj, $account['MMA_EMAIL'], $uid, 0, true );

			prepare_body( &$body, $id, $account['MMA_EMAIL'], $uid ); // add attaches etc.
			$message['MMC_CONTENT'] = trim($body['text']);
			$message['MMC_LEAD'] = formatMsgLead( $body['text'] );

			$arr = splitBodyHeader( $newMsg );
			$message['MMC_HEADER'] = $arr[0];

			//
			// Save message to local DB (cache)
			//
			$attachments = '';
			$images = '';
			for($i=0; $i<count($body['attached_files']); $i++) // what is it?
			{
				$fileinfo = Array
				(
					'name' => $body['attached_files'][$i]['name'],
					'type' => $body['attached_files'][$i]['type_p'].'/'.$body['attached_files'][$i]['type_s'],
					'size' => $body['attached_files'][$i]['size']
				);
				if(empty($body['attached_files'][$i]['cid'])) // this is an attached file (not inline picture)
					$attachments = addAttachedFile( $attachments, $fileinfo);
				else
					$images = addAttachedFile( $images, $fileinfo);
			}
			$message['MMC_ATTACHMENT'] = base64_encode($attachments);
			$message['MMC_IMAGES'] = base64_encode($images);

			if( PEAR::isError( $ret = $cache->saveMessageToCache( $account['MMA_EMAIL'], $uid, $message ) ) )
				return $ret;
		}
		return $message;
	}

	function mm_getConnectParams( $account )
	{
		if( $account['MMA_INTERNAL'] )
		{
			$out['server'] = MAIL_SERVER;
			$out['user'] = $account['MMA_EMAIL'];
			$out['protocol'] = 'imap';
			$out['port'] = '143';
			$out['secure'] = '';
		}
		else
		{
			$out['server'] = $account['MMA_SERVER'];
			$out['user'] = $account['MMA_LOGIN'];
			$out['protocol'] = strtolower($account['MMA_PROTOCOL']);
			$out['port'] = $account['MMA_PORT'];
			$out['secure'] = $account['MMA_SECURE'];
		}
		$out['pass'] = $account['MMA_PASSWORD'];
		return $out;
	}

	function mm_deleteMail( $del, $mmStrings )
	{
		global $accounts, $log;
		$log = '';


		$attDir = mm_getNoteAttachmentsDir( '', 0 );
		$imgDir = mm_getNoteAttachmentsDir( '', 1 );


		foreach( $del as $account=>$uidl )
		{
			//
			// Query to remote server
			//
			$params = mm_getConnectParams($accounts[$account]);

			$connect = socketMailOpen($params);
			if( !PEAR::isError( $connect ) )
			{
				$server_uidl = socketMailUIDL( $connect, $params );
				if( !is_array($server_uidl) )
				{
					socketMailClose( $connect, $params );
					return false;
				}
				$server_uidl = array_flip($server_uidl);

				$ids = array();
				foreach( $uidl as $uid )
				{
					$ids[] = $server_uidl[$uid];
					// delete all attachments from file system
					unlinkRecursive($attDir.$account.'~'.$uid);
					unlinkRecursive($imgDir.$account.'~'.$uid);
				}

				socketMailDelete( $connect, $params, $ids );

				socketMailClose( $connect, $params );
			}
		}
		//
		// Query to cache
		//
		$cache = new mm_cache();

		$res = $cache->deleteMessagesFromCache( $del, $mmStrings );
		if( PEAR::isError( $res ) )
			return $res;

		return true;
	}

	function mm_checkMail( $accounts )
	{
		global $connectLimit, $ignoreDeletedMessages, $log;
		$log = '';
		$news = array();

		$cache = new mm_cache();

		foreach( $accounts as $acc )
		{
			$cache_uidl = $server_uidl = array();
			$list = false;

			if( $acc['MMA_INTERNAL'] )
				$acc['MMA_EMAIL'] .= '@' . $acc['MMA_DOMAIN'];

			//
			// Query to cache
			//
			$cache_uidl = $cache->getUIDL( $acc['MMA_EMAIL'] ); // Get Uniq IDs List for current account from cache
			if( !PEAR::isError( $cache_uidl ) )
			{
				//
				// Query to server
				//
				$params = mm_getConnectParams( $acc );

				$connect = socketMailOpen( $params );
				if( !PEAR::isError( $connect ) )
				{
					$server_uidl = socketMailUIDL( $connect, $params );
					
					//
					// Get headers from remote server, save it to local DB (cache) and delete exists messages from cache
					//
					if( is_array( $server_uidl ) )
						$ret = socketMailHeaders( $connect, $params, $server_uidl, $cache_uidl, $acc['MMA_EMAIL'] );
					socketMailClose( $connect, $params );

					if(!$ret)
						return PEAR::raiseError( 'socketMailHeaders error' );

					if( ( $new = count( $server_uidl ) - count( $cache_uidl ) ) &&
						( $new > 0 || !in_array($acc['MMA_SERVER'], $ignoreDeletedMessages) ) )
						$news[$acc['MMA_EMAIL']] = $new;
				} else {
					$news[$acc['MMA_EMAIL']] = $connect->getMessage();
				}
			}
		}
		return $news;
	}

	function mm_saveAttachment( $files, $MMM_ID, $type )
	{
		global $mmStrings;

		foreach($files as $f)
		{
			$destPath = mm_getNoteAttachmentsDir( $MMM_ID, $type );

			if(!file_exists($destPath))
			{
				$errStr = null;
				if( !forceDirPath( $destPath, $errStr ) )
					return PEAR::raiseError( $mmStrings['cm_screen_makedirerr_message'] );
			}
			if( !@file_put_contents( $destPath.'/'.$f['name'], $f['body'] ) )
				return PEAR::raiseError( $mmStrings['cm_screen_copyerr_message'] );
		}
		return true;
	}

	function mm_getNameFromAddress( $email )
	{
		$res = parseAddressString( $email );
		$name = preg_replace('/"\s*(.*)\s*"/', "$1", $res['accepted'][0]['name']);
		if(count($res['accepted']) > 1)
			$name .= ', ...';
		return chop_str( $name, 20 );
	}

	/**
	 * Recursively delete a directory
	 *
	 * @param string $dir Directory name
	 */
	function unlinkRecursive($dir)
	{
		if(!$dh = @opendir($dir)) {
			return;
		}

		while(false !== ($obj = readdir($dh))) {

			if($obj == '.' || $obj == '..') {
				continue;
			}

			if (!@unlink($dir . '/' . $obj)) {
				unlinkRecursive($dir.'/'.$obj);
			}
		}
		closedir($dh);
	   
		@rmdir($dir);
	   
		return;
	}

	function getUserDomainsList() {
		global $DB_KEY;
		//
		// SOAP requests
		//
 		$connection = aa_createSOAPConnection();
 		if (PEAR::isError($connection))
 			return $connection;
		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );

		$parameters = array(
			"U_ID"     => base64_encode(AA_MT_SOAP_USER),
			"PASSWORD" => base64_encode(AA_MT_SOAP_PWD),
			"DB_KEY"   => base64_encode($DB_KEY)
		);
		$res = $connection->call( 'mt_getUserDomainsList', $parameters, $mt_service_options );

		if ( PEAR::isError($res) )
			return PEAR::raiseError( $res->getMessage() );
		elseif ($res->error)
			return PEAR::raiseError( sprintf($kernelStrings['bill_error_soap_internal'],$res->error)  );
		else {
			$result = array();
			$result = unserialize(base64_decode($res->domainsList));
			if (is_array($res->domainsList)) 
				while (list($key, $row) = each($res->domainsList)) {
					$Row = get_object_vars($row);
					if (count($Row)) {
						foreach($Row as $key=>$val) $Row[$key] = base64_decode((string)$val);
						$result[] = $Row;		
					}
				}
		}
		return $result;

	}

	require_once 'SOAP/Client.php';

	function aa_createSOAPConnection () {
		global $kernelStrings;
		// Construct the endpoint URL
		//
		$mtEndpoint = "http://".MT_HOST_SERVER."/wbs/MT/soap/mt_webservice.php?DB_KEY=WEBASYST";
 
		// Create the SOAP client object
		//
		$connection = @new SOAP_Client( $mtEndpoint );
		if( PEAR::isError($connection) )
			return $connection;

		if ( !$connection )
			return PEAR::raiseError( $kernelStrings['bill_error_soap_connection'] );
		else {
			$connection->setOpt( 'timeout', 60 );
			return $connection;
		}
	}

?>