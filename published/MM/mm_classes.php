<?php

	//
	// WebAsyst Mail Master common classes
	//

	class mm_documentFolderTree extends genericDocumentFolderTree
	{
		function mm_documentFolderTree( &$descriptor )
		{
			$this->folderDescriptor = $descriptor->folderDescriptor;
			$this->documentDescriptor = $descriptor->documentDescriptor;

			$this->globalPrefix = "MM";
		}

		function copyMoveDocuments( $documentList, $destID, $operation, $U_ID, $kernelStrings, $onAfterOperation, $onBeforeOperation = null, $callbackParams = null, $perFileCheck = true, $checkUserRights = true, $onFinishOperation = null, $suppressNotifications = false, $inboxMode = false )
		{
			global $_mmQuotaManager;
			global $MM_APP_ID;

			$_mmQuotaManager = new DiskQuotaManager();

			$TotalUsedSpace = $_mmQuotaManager->GetUsedSpaceTotal( $kernelStrings );
			if ( PEAR::isError($TotalUsedSpace) )
				return $TotalUsedSpace;

			if ( is_null($callbackParams) )
				$callbackParams = array();

			$callbackParams['TotalUsedSpace'] = $TotalUsedSpace;

			$res = parent::copyMoveDocuments( $documentList, $destID, $operation, $U_ID, $kernelStrings, $onAfterOperation, $onBeforeOperation, $callbackParams, $perFileCheck, $checkUserRights, $onFinishOperation, $suppressNotifications );

			$_mmQuotaManager->Flush( $kernelStrings );

			return $res;
		}

		function moveFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation = null, $onFolderCreate = null,
			$onFolderDelete = null, $callbackParams = null, $onFinishMove = null, $checkUserRights = true,
			$topLevel = true, $accessInheritance = ACCESSINHERITANCE_COPY, $mostTopRightsSource = null,
			$folderStatus = TREE_FSTATUS_NORMAL, $plainMove = false, $checkFolderName = true )
		{
			global $_mmQuotaManager;
			global $MM_APP_ID;

			$_mmQuotaManager = new DiskQuotaManager();

			$TotalUsedSpace = $_mmQuotaManager->GetUsedSpaceTotal( $kernelStrings );
			if ( PEAR::isError($TotalUsedSpace) )
				return $TotalUsedSpace;

			if ( is_null($callbackParams) )
				$callbackParams = array();

			$callbackParams['TotalUsedSpace'] = $TotalUsedSpace;

			$res = parent::moveFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation , $onFolderCreate,
			$onFolderDelete, $callbackParams, $onFinishMove, $checkUserRights,
			$topLevel, $accessInheritance, $mostTopRightsSource,
			$folderStatus, $plainMove, $checkFolderName );

			$_mmQuotaManager->Flush( $kernelStrings );

			return $res;
		}

		function copyFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation = null, $onFolderCreate = null, $callbackParams = null, $onFininshCopy = null, $accessInheritance = ACCESSINHERITANCE_COPY, $onBeforeFolderCreate = null, $checkFolderName = true, $copyChilds = true )
		{
			global $_mmQuotaManager;
			global $MM_APP_ID;

			$_mmQuotaManager = new DiskQuotaManager();

			$TotalUsedSpace = $_mmQuotaManager->GetUsedSpaceTotal( $kernelStrings );
			if ( PEAR::isError($TotalUsedSpace) )
				return $TotalUsedSpace;

			if ( is_null($callbackParams) )
				$callbackParams = array();

			$callbackParams['TotalUsedSpace'] = $TotalUsedSpace;

			$res = parent::copyFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation, $onFolderCreate, $callbackParams, $onFininshCopy, $accessInheritance, $onBeforeFolderCreate, $checkFolderName, $copyChilds );

			$_mmQuotaManager->Flush( $kernelStrings );

			return $res;
		}

	}

	// Global MM tree class
	//

	$mm_treeClass = new mm_documentFolderTree( $mm_TreeFoldersDescriptor );


	class mm_message extends wbsParameters
	{

		function mm_message( $params = null )
		{
			parent::wbsParameters($params);
		}

		function onBeforeSet( &$array, $params = null )
		{
			global $html_encoding;

			if ( is_array( $params ) )
				extract( $params );

			foreach ($array as $key=>$value)
			{
				$value = trim($value);

				if ( is_string($value) && !strlen($value) )
					$array[$key] = null;

				if ( isset( $source ) &&  $source == s_form )
				{
					if (get_magic_quotes_gpc())
						$value = stripslashes( $value );

					$array[$key] = $value;
				}

			}

			return true;
		}

		function onAfterSet( $array, $params = null )
		{
			global $mm_treeClass;

			if ( is_array( $params ) )
				extract( $params );

			if ( isset(  $source  ) )
			{
				if ( $source == s_form )
				{
					$this->MMM_CONTENT = preg_replace( '/(href=)("{0,1})(%7b)([^">]*)(%7d)("{0,1})/ui', '$1"{$4}"', $this->MMM_CONTENT );
				}
				else
				if ( $source == s_database )
					$this->FOLDERDATA = $mm_treeClass->getFolderInfo( $this->MMF_ID, $kernelStrings );
			}
			return true;
		}

		function loadEntry( $MMM_ID, $kernelStrings, $mmStrings )
		{
			global $mm_treeClass;

			$messageData = $mm_treeClass->getDocumentInfo( $MMM_ID, $kernelStrings );

			if ( PEAR::isError($messageData) )
				return $messageData;

			if ( is_null( $messageData ) )
				return false;

			$this->loadFromArray( $messageData, $kernelStrings, false, array( s_datasource => s_database, 'kernelStrings'=>$kernelStrings ) );

			return true;
		}

		function checkRights( $U_ID, $kernelStrings, $mmStrings )
		{
			global $mm_treeClass;

			$rights = $mm_treeClass->getIdentityFolderRights( $U_ID, $this->MMF_ID, $kernelStrings );
			if ( PEAR::isError($rights) )
				return $rights;

			if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) )
				return PEAR::raiseError( $mmStrings['amn_screen_noaddrights_message'], ERRCODE_APPLICATION_ERR );

		}

		function updateImageAttachment( $U_ID, $kernelStrings, $mmStrings )
		{
			global $qr_mm_updateMessageImages;

			if ( PEAR::isError( $rights = $this->checkRights(  $U_ID, $kernelStrings ) ) )
				return $rights;

			$res = db_query( $qr_mm_updateMessageImages, $this );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return $this->MMM_ID;
		}

		function saveSendStatisticsItem(  $MMMST_EMAIL, $MMMST_STATUS, $MMMST_OPENED )
		{
			global $qr_mm_insertSentItem;

			$res = db_query( $qr_mm_insertSentItem, array( 'MMM_ID'=>$this->MMM_ID, 'MMMST_EMAIL'=> $MMMST_EMAIL, 'MMMST_STATUS'=>$MMMST_STATUS, 'MMMST_OPENED'=>$MMMST_OPENED ) );

			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return null;
		}

		function saveSendStatistics( $U_ID, $statArray, $kernelStrings, $mmStrings )
		{

			if ( PEAR::isError( $rights = $this->checkRights(  $U_ID, $kernelStrings, $mmStrings  ) ) )
				return $rights;

			foreach ( $statArray['SENT'] as $sent )
			{
				$res = $this->saveSendStatisticsItem( $sent, 0, 0 );
				if ( PEAR::isError($res) )
					return $res;
			}

			foreach ( $statArray['BOUNCED'] as $bounced )
			{
				$res = $this->saveSendStatisticsItem( $bounced, 1, 0 );
				if ( PEAR::isError($res) )
					return $res;
			}

			return true;
		}

		function saveEntry( $U_ID, $action, $kernelStrings, $mmStrings )
		{
			global $mm_treeClass;
			global $qr_mm_maxNoteID;
			global $qr_mm_insertNote;
			global $qr_mm_updateNote;
			global $DB_KEY, $metric;

			if ( $this->MMF_ID != 'Outbox' && PEAR::isError( $rights = $this->checkRights(  $U_ID, $kernelStrings, $mmStrings  ) ) )
				return $rights;

			$this->MMM_USERID = $U_ID;
			$this->MMM_DATETIME = convertToSqlDateTime( time() );

			if ( $action == ACTION_NEW )
			{
				$res = mm_messageAddingPermitted( $kernelStrings );
				if ( PEAR::isError($res) )
					return $res;

				$MM_ID = db_query_result( $qr_mm_maxNoteID, DB_FIRST );
				$MM_ID = incID($MM_ID);

				$this->MMM_ID = $MM_ID;

				$res = db_query( $qr_mm_insertNote, $this );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

//				$metric->addAction($DB_KEY, $U_ID, 'MM', 'COMPOSE', 'ACCOUNT');

				return $this->MMM_ID;
			}
			else
			{
				$res = db_query( $qr_mm_updateNote, $this );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				return $this->MMM_ID;
			}
		}
	}

	class mm_mailSender extends arrayAdaptedClass
	{
		var $PRIORITY;
		var $SENDER;
		var $TOMORE;

		var $ENCODE;

		var $from;

		var $recipients = array();
		var $extraRecipients;
		var $docList = null;

		var $fullRecipientList = array();
		var $fullRecipientContactList = array();

		var $varList = array();

		var $filesDir;
		var $imagesDir;

		var $includeImages;

		var $imageUri;

		var $URI = null;

		var $maxRecipientNum = null;

		var $textService = null;

		var $save_stat = true;

		var $leaveHeaders = false;

		function mm_mailSender()
		{
			$this->dataDescrition = new dataDescription();

			$this->dataDescrition->addFieldDescription( 'PRIORITY', t_integer, true );
			$this->dataDescrition->addFieldDescription( 'SENDER', t_integer, true );
			$this->dataDescrition->addFieldDescription( 'TOMORE', t_string, false );
		}

		function _parseRecipientList( $rcpt )
		{
			$resultList = array();
			$i=0;
			foreach($rcpt as $list)
				foreach($list as $addr)
					$resultList['X'.$i++] = $addr;

			return $resultList;
		}

		function onValidateField( $array, $fieldName, $fieldValue, &$params )
		{
			global $_PEAR_default_error_mode;
			global $_PEAR_default_error_options;

			extract($params);

			// Validate additional recipient list
			//
			if ( $fieldName == 'TOMORE' )
			{
				$extraRecipients = $this->_parseRecipientList( $fieldValue );
				foreach ( $extraRecipients as $key=>$value )
					if ( !eregi('[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})', $value ) )
						return PEAR::raiseError( sprintf($ddStrings['sm_invalidemail_message'], $value),
											ERRCODE_APPLICATION_ERR,
											$_PEAR_default_error_mode,
											$_PEAR_default_error_options,
											$fieldName );

				if ( !count($this->recipients) && !count($extraRecipients) ) {
					return PEAR::raiseError( $kernelStrings[ERR_REQUIREDFIELDS],
											ERRCODE_APPLICATION_ERR,
											$_PEAR_default_error_mode,
											$_PEAR_default_error_options,
											'recipients' );
				}
			}
		}

		function onAfterSet( $array, $params = null )
		//
		// onAfterSet event handler
		//
		//		Parameters:
		//			$array - data source array
		//			$params - packed parameters. Must contain following keys:
		//				'source' - source of data - s_form, s_database
		//
		{
			extract( $params );

			$this->fillRecipients();

			$senders = mm_getSenders( $U_ID, $kernelStrings );

			if ( PEAR::isError( $senders ) )
				return $senders;

			if ( !in_array( $this->SENDER, array_keys( $senders ) ) )
				return PEAR::raiseError( $kernelStrings[ERR_REQUIREDFIELDS],
										ERRCODE_APPLICATION_ERR
									);

			$this->from = $senders[$this->SENDER];
		}

		function fillRecipients()
		{
			$this->tomoreRecipientList = $this->TOMORE;
			$extraRecipients = $this->_parseRecipientList( $this->TOMORE );

			$this->extraRecipients = array();
			foreach ( $extraRecipients as $key=>$row )
			{
				$addr = parseAddressString( $row );
				$extraEmails[$addr['accepted'][0][1].'@'.$addr['accepted'][0][2]] = $key;
			}
			foreach ( $extraEmails as $key )
				$this->extraRecipients[$key] = $extraRecipients[$key];

			// Load recipients emails
			//
			$fullRecipientList = $fullEmails = array();
			foreach ( $this->recipients as $key=>$row )
			{
				$rowArray = (array) $row;
				$contactName = df_contactname( $rowArray );

				$email = $rowArray['C_EMAILADDRESS'];
				$fullRecipientList[$key] = sprintf( "%s <%s>", $contactName, $email );
				$fullRecipientContactList[$key] = $rowArray;

				if(isset($extraEmails[$email]))
					unset($this->extraRecipients[$extraEmails[$email]]);
			}

			$this->fullRecipientList = $fullRecipientList;
			foreach ( $this->extraRecipients as $key=>$row )
				$this->fullRecipientList[$key] = $row;

			$this->fullRecipientContactList = $fullRecipientContactList;
		}

		function substVars( $id, $U_ID, $text, $kernelStrings )
		{
			global $DB_KEY;

			$text = str_replace( MM_VAR_UNSIBSCRIBE_URL, getSubscribeConfirmationLink( $DB_KEY, $id, $this->URI ), $text );

			if ( is_null( $this->textService ) )
				return $text;

			$contact = isset( $this->fullRecipientContactList[$id] ) ? $this->fullRecipientContactList[$id] : null;

			$textService = $this->textService;

			return $textService->ProcessText(  $text, $contact, $U_ID, $kernelStrings );
		}

		function send( $U_ID, $message, &$kernelStrings, &$mmStrings )
		//
		// Sends files by e-mail
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//			$ddStrings - Document Depot localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $mm_treeClass;
			global $html_encoding;
			global $mm_message_data_schema;

			@set_time_limit( 3600 );

			$finalFileList = listAttachedFiles( base64_decode( $message->MMM_ATTACHMENT ) );
			$finalImageList = listAttachedFiles( base64_decode( $message->MMM_IMAGES ) );

			$priorityMap = array( MM_PRIORITY_HIGH=>1, MM_PRIORITY_NORMAL=>3, MM_PRIORITY_LOW=>5 );
			$this->PRIORITY = $priorityMap[$this->PRIORITY];

			$messageData = $message->getValuesArray();

			$bouncedArr = array();
			$sentArr = array();

			$HTML_CONTENT = $message->MMM_CONTENT;

			foreach ( $finalImageList as $key=>$imageData )
			{
				/* used with original phpMailer only
				if ( $this->includeImages )
				{
					$cid = uniqid( $imageData['filedate'] );

					$HTML_CONTENT = preg_replace( '/(<[^>]*?(background|src)="{0,1})([^">]*)'.base64_encode( $imageData['diskfilename'] ).'([^">]*)("{0,1}[^>]*>)/ui', '$1'."cid:$cid".'$5', $HTML_CONTENT );
					$HTML_CONTENT = preg_replace( '/(url\({0,1})([^\)]*)'.base64_encode( $imageData['diskfilename'] ).'([^\)]*)/ui', '$1'."cid:$cid", $HTML_CONTENT );

					if ( strstr( $HTML_CONTENT, $cid ) )
					{
						$imageData['cid'] = $cid;
						$finalImageList[$key] = $imageData;
					}
				}
				*/
				if ( !$this->includeImages )
				{
					$HTML_CONTENT = preg_replace( '/(<[^>]*?(background|src)="{0,1})([^">]*)'.base64_encode( $imageData['diskfilename'] ).'([^">]*)("{0,1}[^>]*>)/ui', '$1'.$this->imageUri."&fileName=".base64_encode( $imageData['diskfilename'] ).'$5', $HTML_CONTENT );
					$HTML_CONTENT = preg_replace( '/(url\({0,1})([^\)]*)'.base64_encode( $imageData['diskfilename'] ).'([^\)]*)/ui', '$1'.$this->imageUri."&fileName=".base64_encode( $imageData['diskfilename'] ), $HTML_CONTENT );
				}
			}

			// Send message
			//
			$mail = new WBSMailer( false );

			$mail->SMTPAuth = false;

			if( trim( $this->from['MMS_ENCODING'] ) == '' )
				$this->from['MMS_ENCODING'] = $html_encoding;

			$mail->CharSet = $this->from['MMS_ENCODING'];

			$mail->FromName = isset( $this->from['MMS_FROM'] ) ? $this->from['MMS_FROM'] : "";
			$mail->From = $this->from['MMS_EMAIL'];

			$mail->AddReplyTo( $this->from['MMS_REPLYTO'] );
			$mail->Sender = $this->from['MMS_RETURNPATH'];

			foreach( $finalFileList as $fileData )
				$mail->AddAttachment( $this->filesDir.'/'.$fileData['diskfilename'], $fileData['diskfilename'], 'base64', $fileData['type'] );

			if( $this->includeImages )
				foreach ( $finalImageList as $key=>$imageData )
					$mail->AddEmbeddedImage( $this->imagesDir."/".$imageData['diskfilename'], $imageData['cid'], $imageData['diskfilename'], "base64", $imageData['type'] );


			foreach($this->tomoreRecipientList['TO'] as $addr)
				$mail->addAddress($addr);
			foreach($this->tomoreRecipientList['CC'] as $addr)
				$mail->addCC($addr);

			$cnt = 0;
			foreach( $this->fullRecipientList as $id=>$email )
			{
				if( !is_null( $this->maxRecipientNum ) && ++$cnt > $this->maxRecipientNum )
					break;

				$mail->IsHTML( true );
				$mail->Subject = $this->substVars( $id, $U_ID, trim( $message->MMM_SUBJECT, $kernelStrings ), $kernelStrings );
				$mail->Body = $this->substVars( $id, $U_ID, trim( str_replace( "\r\n", "\n", $HTML_CONTENT ) ), $kernelStrings );
				$mail->Priority = $this->PRIORITY;

				if( !is_array( $res = $mail->Send( $email, $this->leaveHeaders ) ) )
				{
					$bouncedArr[] = "$email\t($res)";

					if( $this->save_stat )
						$message->saveSendStatisticsItem( $email, 1, 0 );
				}
				else
				{
					$sentArr[] = $email;

					if( $this->save_stat )
						$message->saveSendStatisticsItem( $email, 0, 0 );
				}
			}
			return array( 'SENT'=>$sentArr, 'BOUNCED'=>$bouncedArr );
		}

	}

	class mm_sender extends wbsParameters
	{

		function mm_sender( $params = null )
		{
			parent::wbsParameters($params);
		}

		function onBeforeSet( &$array, $params = null )
		{
			extract( $params );

			foreach ($array as $key=>$value)
			{
				$value = trim($value);

				if ( is_string($value) && !strlen($value) )
					$array[$key] = null;

				if ( $source == s_form )
				{
					if (get_magic_quotes_gpc())
						$value = stripslashes( $value );

					$array[$key] = $value;
				}

			}
			return true;
		}

		function onAfterSet( $array, $params = null )
		{
			return true;
		}

		function loadEntry( $MMS_ID, $kernelStrings, $mmStrings )
		{
			global $qr_mm_selectSender;

			$res = db_query( $qr_mm_selectSender, array( 'MMS_ID' => $MMS_ID) );
			if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( is_null( $res ) )
				return false;

			$row = db_fetch_array( $res );

			$this->loadFromArray( $row, $kernelStrings, false, array( s_datasource => s_database ) );

			return true;
		}

		function saveEntry( $U_ID, $action, $kernelStrings, $mmStrings )
		{
			global $qr_mm_insertSender;
			global $qr_mm_updateSender;

			$this->MMM_USERID = $U_ID;
			$this->MMM_DATETIME = convertToSqlDateTime( time() );

			if ( $action == ACTION_NEW )
			{
				$res = db_query( $qr_mm_insertSender, $this );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				return $this->MMS_ID;
			}
			else
			{
				$res = db_query( $qr_mm_updateSender, $this );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING].mysql_error() );

				return $this->MMS_ID;
			}
		}

		function delete( $MMS_ID, $kernelStrings, $mmStrings )
		{
			global $qr_mm_deleteSender;

			$res = db_query( $qr_mm_deleteSender, array( 'MMS_ID'=>$MMS_ID ) );
			if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return true;
		}

	}

// *****************************************************************************

	class mm_account extends wbsParameters
	{

		function mm_account( $params = null )
		{
			parent::wbsParameters( $params );
		}

		function onBeforeSet( &$array, $params = null )
		{
			extract( $params );

			foreach( $array as $key=>$value )
			{
				$value = trim($value);

				if( is_string($value) && !strlen($value) )
					$array[$key] = null;

				if( $source == s_form )
				{
					if (get_magic_quotes_gpc())
						$value = stripslashes( $value );

					$array[$key] = $value;
				}
			}
			return true;
		}

		function onAfterSet( $array, $params = null )
		{
			return true;
		}

		function loadEntry( $MMA_ID, $kernelStrings, $mmStrings )
		{
			global $qr_mm_selectAccount;

			$res = db_query( $qr_mm_selectAccount, array( 'MMA_ID' => $MMA_ID ) );
			if( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if( is_null( $res ) )
				return false;

			$row = db_fetch_array( $res );

			$this->loadFromArray( $row, $kernelStrings, false, array( s_datasource => s_database ) );

			return true;
		}

		function saveEntry( $U_ID, $action, $kernelStrings, $mmStrings )
		{
			global $qr_mm_insertAccount;
			global $qr_mm_updateAccount;
			global $qr_mm_updateAccount_wo_pass;

			$this->MMM_USERID = $U_ID;
			if (empty($this->MMA_USERID)) $this->MMA_USERID = $this->MMM_USERID;
			if (empty($this->MMA_SECURE)) $this->MMA_SECURE = 0;
			if (empty($this->MMA_INTERNAL)) $this->MMA_INTERNAL = 0;
			$this->MMM_DATETIME = convertToSqlDateTime( time() );

			if( $action == ACTION_NEW )
			{
				$this->MMA_USERID = $U_ID;
				$res = db_query( $qr_mm_insertAccount, $this );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], MM_ERROR_ACCOUNT_EXISTS );

				return $this->MMA_ID;
			}
			else
			{
				if($this->MMA_PASSWORD)
					$qr = $qr_mm_updateAccount;
				else
					$qr = $qr_mm_updateAccount_wo_pass;

				$res = db_query( $qr, $this );

				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING].mysql_error(), MM_ERROR_ACCOUNT_EXISTS );

				return $this->MMA_ID;
			}
		}

		function delete( $MMA_ID, $kernelStrings, $mmStrings )
		{
			global $qr_mm_deleteAccount;
			global $qr_mm_deleteCache;
			global $MM_APP_ID;
			global $currentUser;

			if( PEAR::isError( $this->loadEntry( $MMA_ID, $kernelStrings, $mmStrings ) ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$accountData = $this->getValuesArray();
			if( $accountData['MMA_INTERNAL'] )
				$accountData['MMA_EMAIL'] .= '@'.$accountData['MMA_DOMAIN'];

			$res = db_query( $qr_mm_deleteCache, array( 'MMC_ACCOUNT'=>$accountData['MMA_EMAIL'] ) );
			if( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$res = db_query( $qr_mm_deleteAccount, array( 'MMA_ID'=>$MMA_ID ) );
			if( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_MAILBOX', false, $kernelStrings, 0 );
			setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_INBOXMODE', true, $kernelStrings, 0 );
			setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_STATISTICSMODE', true, $kernelStrings, 0 );

			return true;
		}

		function getAccountId( $params, $kernelStrings )
		{
			$qr_mm_getAccountId = "SELECT MMA_ID FROM MMACCOUNT WHERE MMA_EMAIL='"
				. mysql_real_escape_string( $params['MMA_EMAIL'] ) . "' AND MMA_DOMAIN='"
				. mysql_real_escape_string( $params['MMA_DOMAIN'] ) . "'";
			$res = db_query_result( $qr_mm_getAccountId, DB_FIRST );
			if( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			return $res;
		}

	}

	class MailCommonDB
	{
		private function __construct() {}
		private function __clone() {}
	
		public function addAccount( $params )
		{
			return self::saveEntry( 'add', $params );
		}

		public function updateAccount( $params )
		{
			return self::saveEntry( 'update', $params );
		}
	
		public function deleteAccount( $params )
		{
			return self::saveEntry( 'delete', $params );
		}

		public function getDiskUsage( $params )
		{
			return self::saveEntry( 'getDiskUsage', $params );
		}

		//
		// Add, update or delete mail account from common database
		//
		private static function saveEntry( $action, $params )
		{
			//
			// Read XML config
			//
			if (!file_exists(WBS_DIR . MAIN_XML_CONFIG))
				return PEAR::raiseError( 'ERROR: File "'.MAIN_XML_CONFIG.'" doesn\'t exist' );

			$xml = file_get_contents( WBS_DIR . MAIN_XML_CONFIG );
			$sxml = new SimpleXMLElement( $xml );
			//
			// MAILDAEMONDB section doesn't exists or parameters not set
			//
			if( sizeof( (array)$sxml->MAILDAEMONDB->attributes() ) == 0 )
				return PEAR::raiseError( 'MAILDAEMONDB section doesn\'t exists or parameters not set' );
			//
			// Get data...
			//
			$serverName = (string)$sxml->MAILDAEMONDB->attributes()->SERVER_NAME;
			$pageUrl    = (string)$sxml->MAILDAEMONDB->attributes()->PAGE_URL;

			$pageUrl .= "?action=$action";

			foreach( $params as $key=>$val )
				$pageUrl .= "&" . rawurlencode( $key ) . "=" . rawurlencode( $val );

			//
			// Request remote url...
			//
			$ret = file_get_contents( "$serverName/$pageUrl" );

			if($ret != 'OK')
			{
				if(is_numeric($ret))
					return $ret;
				
				if( strpos( $ret, 'Duplicate entry' ) !== false )
					$ret = 1;
				else
				  $ret = 0;

				$error = PEAR::raiseError( 'Common database error' );
				$error->userinfo = $ret;
				return $error;
			}
			return null;
		}
	}

	class mm_inboxList
	{
		function mm_inboxList( $row, $account=false )
		{
			if( $account )
				$this->MMM_ACCOUNT = $account;
			else
				$this->MMM_ACCOUNT = $row['MMC_ACCOUNT'];

			$this->MMM_ID = $row['MMC_UID'];
			$this->MMM_UID = $row['MMC_UID'];
			$this->MMM_SUBJECT = $row['MMC_SUBJECT'];
			$this->MMM_LEAD = $row['MMC_LEAD'];

			$this->MMM_DATETIME = convertToDisplayDateTime( $row['MMC_DATETIME'], false, true, true );

			$this->MMM_FROM = $row['MMC_FROM'];
			$this->ATTACHEDFILES = $row['MMC_ATTACHMENT'] ? true : false;
			$this->MMM_SIZE = formatFileSizeStr( $row['MMC_SIZE'] );
			$this->MMM_FLAG = $row['MMC_FLAG'];
			$this->INBOX_MESSAGE = 1;
			$this->ROW_URL = "'$this->MMM_ACCOUNT', '{$row['MMC_UID']}', '{$row['MMC_SIZE']}'";
			$this->TREE_ACCESS_RIGHTS = 7;
			$this->MMM_PRIORITY = $row['MMC_PRIORITY'];
		}
	}

	class mm_cache
	{
		function loadHeadersFromCache( $account, $sorting, $startIndex, $count )
		{
			$sorting = str_replace( 'MMM_', 'MMC_',  $sorting );

			$notes = array();

			if( $account )
				$query = "SELECT MMC_UID, MMC_ACCOUNT, MMC_DATETIME, MMC_FROM, MMC_SUBJECT, MMC_LEAD, MMC_SIZE, MMC_ATTACHMENT, MMC_FLAG, MMC_PRIORITY
					FROM MMCACHE WHERE MMC_ACCOUNT='$account' ORDER BY $sorting LIMIT $startIndex, $count";
			else
				$query = "SELECT MMC_UID, MMC_ACCOUNT, MMC_DATETIME, MMC_FROM, MMC_SUBJECT, MMC_LEAD, MMC_SIZE, MMC_ATTACHMENT, MMC_FLAG, MMC_PRIORITY
					FROM MMCACHE ORDER BY $sorting LIMIT $startIndex, $count";

			$res = db_query( $query, array () );
			if( PEAR::isError( $res ) )
				return $res;

			while( $row = db_fetch_array($res) )
				$notes[] = new mm_inboxList( $row, $account );

			return $notes;
		}

		function loadMessageFromCache( $account, $uid )
		{
			$query = "SELECT * FROM MMCACHE WHERE
				MMC_ACCOUNT='" . mysql_real_escape_string($account) . "' AND
				MMC_UID='" . mysql_real_escape_string($uid) . "'";

			$msg = db_query_result( $query, DB_ARRAY );
			if( PEAR::isError( $msg ) )
				return $msg;

			return $msg;
		}

		function saveMessageToCache( $account, $uid, &$message )
		{
			$query = "UPDATE MMCACHE SET MMC_CONTENT='"
				. mysql_real_escape_string( $message['MMC_CONTENT'] ) . "', MMC_ATTACHMENT='"
				. mysql_real_escape_string( $message['MMC_ATTACHMENT'] ) . "', MMC_IMAGES='"
				. mysql_real_escape_string( $message['MMC_IMAGES'] ) . "', MMC_LEAD='"
				. mysql_real_escape_string( $message['MMC_LEAD'] ) . "', MMC_TO='"
				. mysql_real_escape_string( $message['MMC_TO'] ) . "', MMC_REPLY_TO='"
				. mysql_real_escape_string( $message['MMC_REPLY_TO'] ) . "', MMC_CC='"
				. mysql_real_escape_string( $message['MMC_CC'] ) . "', MMC_HEADER='"
				. mysql_real_escape_string( $message['MMC_HEADER'] ) . "', MMC_FLAG='1' WHERE MMC_UID='"
				. mysql_real_escape_string( $uid ) . "' AND MMC_ACCOUNT='"
				. mysql_real_escape_string( $account ) . "' LIMIT 1";
			$res = execPreparedQuery( $query, array () );
			return true;
		}

		function deleteMessagesFromCache( $del, $mmStrings )
		{
			foreach( $del as $account=>$uidl )
			{
				$qr = "MMC_UID='" . join( "' OR MMC_UID='", $uidl ) . "'";
				$limit = count( $uidl );
				$query = "DELETE FROM MMCACHE WHERE MMC_ACCOUNT='$account' AND ($qr) LIMIT $limit";

				$res = db_query( $query, array () );
				if( PEAR::isError( $res ) )
					return $res;
			}
			return true;
		}

		function getUIDL( $account )
		{
			$uidl = array();

			$query = "SELECT MMC_UID FROM MMCACHE WHERE MMC_ACCOUNT='$account'";
			$res = db_query( $query, array () );
			if( PEAR::isError( $res ) )
				return $res;

			while( $row = db_fetch_array($res) )
				$uidl[] = $row['MMC_UID'];

			return $uidl;
		}

		function getAccountsInfo()
		{
			$info = array();

			$query = "SELECT MMC_ACCOUNT, MMC_SIZE, MMC_FLAG FROM MMCACHE ORDER BY MMC_ACCOUNT ASC";
			$res = db_query( $query, array () );
			if( PEAR::isError( $res ) )
				return $res;

			while( $row = db_fetch_array($res) )
			{
				if( empty( $info[$row['MMC_ACCOUNT']]['count'] ) )
				{
					$info[$row['MMC_ACCOUNT']]['count'] = 0;
					$info[$row['MMC_ACCOUNT']]['size'] = 0;
					$info[$row['MMC_ACCOUNT']]['new'] = 0;
				}
				$info[$row['MMC_ACCOUNT']]['count']++;
				$info[$row['MMC_ACCOUNT']]['size'] += $row['MMC_SIZE'];
				if(empty($row['MMC_FLAG']))
					$info[$row['MMC_ACCOUNT']]['new']++;
			}
			return $info;
		}

		function getHeadersCountFromCache( $account )
		{
			$query = "SELECT COUNT(*) FROM MMCACHE WHERE MMC_ACCOUNT='$account'";

			$res = db_query_result( $query, DB_FIRST );
			if( PEAR::isError( $res ) )
				return $res;

			return $res;
		}

	}

?>