<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );
	
	$fatalError = false;
	$error = null;
	$errorStr = null;
	$SCR_ID = "CT";
	$ajaxRes = array ("success" => false, "errorStr" => "no result");
	
	pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );
	
	$kernelStrings = $loc_str[$language];
	$ddStrings = $dd_loc_str[$language];
	
	do {
		if (empty($filesIds)) {
			$error = PEAR::raiseError("Empty subtype");
			break;
		}
		
		$includedContacts = array();
		$sendData = prepareArrayToStore($sendData);

		$messageData["SUBJECT"] = $sendData["subject"];
		$messageData["MESSAGE"] = $sendData["message"];
		$messageData["COMPRESS"] = empty($needCompress) ? 0 : 1;
		$messageData["ARCHIVENAME"] = "attachment";
		
		$sender = new dd_fileSender();
		$sender->recepients = array ();
		$sender->fileList = $filesIds;
			

		$params = array();
		$params['ddStrings'] = $ddStrings;
		$params['kernelStrings'] = $kernelStrings;
		$params['U_ID'] = $currentUser;

		$res = $sender->loadFromArray( $messageData, $kernelStrings, true, $params );
		if ( PEAR::isError($error  = $res) )
			break;
		
		$toMails = getFullEmailsFromContactsString($sendData["to"]);
		
		if (!sizeof($toMails)) {
			$error = PEAR::raiseError($kernelStrings["sm_recipientnull_message"]);
			break;
		}
		
		if (sizeof($toMails) > EMAIL_MAX_RECEPIENTS_COUNT) {
			$error = PEAR::raiseError(sprintf($kernelStrings["sm_recipientlimit_message"], sizeof($toMails), EMAIL_MAX_RECEPIENTS_COUNT));
			break;
		}
		
		$sender->fullRecipientList = $toMails;
		$res = $sender->send( $currentUser, $kernelStrings, $ddStrings );
		if ( PEAR::isError($error = $res) ) 
			break;
		
		$resultStr = sprintf($kernelStrings["shsd_message_sended"], sizeof($toMails));
	} while (false);
	
	if (PEAR::isError($error)) {
		$ajaxRes["success"] = false;
		$ajaxRes["errorStr"] = $error->getMessage ();
	} else {
		$ajaxRes["success"] = true;
		$ajaxRes["resultStr"] = $resultStr;
	}	
	
	print $json->encode ($ajaxRes);
		
?>