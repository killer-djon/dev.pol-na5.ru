<?php

	require_once '../../../common/html/includes/httpinit.php';
	require_once WBS_DIR . '/published/MM/mm.php';

	//
	// Authorization
	//
	$errorStr = null;
	$SCR_ID = "MM";
	pageUserAuthorization( $SCR_ID, $MM_APP_ID, false );

	//
	// Page variables setup
	//
	$kernelStrings = $loc_str[$language];
	$mmStrings = $mm_loc_str[$language];

	$forceReload = true;
	$mid = $MMM_ACCOUNT.'~'.$MMM_UID;

	if(empty($MMM_UID) || empty($MMM_ACCOUNT))
		$errorStr = $mmStrings['msg_notfound_error'];
	else
	{
		$account = mm_getAccountByEmail( $MMM_ACCOUNT );
		if(PEAR::isError($account))
			$errorStr = $account->getMessage();

		if($account['MMA_INTERNAL'])
			$replyFrom = trim($account['MMA_NAME'].' <'.$account['MMA_EMAIL'].'@'.$account['MMA_DOMAIN'].'>');
		else
			$replyFrom = trim($account['MMA_NAME'].' <'.$account['MMA_EMAIL'].'>');;

		$message = mm_getMessage($MMM_UID, $account);
		if( PEAR::isError( $message ) )
			$errorStr = $message->getMessage();

		if(!$message || $errorStr)
		{
			$new_msg = mm_checkMail(array($account));
			if(PEAR::isError($new_msg))
				exit($new_msg->getMessage());
			$errorStr = $mmStrings['msg_notfound_error'];

			$res = db_query( $qr_mm_deleteCacheMessage, array( 'MMM_UID'=>$MMM_UID, 'MMM_ACCOUNT'=>$MMM_ACCOUNT ) );
			if( PEAR::isError($res) )
				$errorStr = $message->getMessage();
		}
		else
		{
			$forceReload = false;
		}
	}

	$out = array();
	foreach($message as $key=>$val)
	{
		$key = str_replace('MMC_', 'MMM_', $key);
		$out[$key] = $val;
	}
	$message = $out;

	if($ts = strtotime($message['MMM_DATETIME']))
		$message['MMM_DATETIME'] = displayDateTime(convertTimestamp2Local($ts));

	$res = parseAddressString( $message['MMM_FROM'] . ', ' . $message['MMM_TO'] . ', ' . $message['MMM_CC'] . ', ' . $message['MMM_REPLY_TO'] );
	$out = array();
	if( is_array( $res['accepted'] ) )
		foreach( $res['accepted'] as $item )
			if( $item['email'] != $account['MMA_EMAIL'] )
				$out[] = trim( $item['name'] . ' <' . $item['email'] . '>' );
	$message['all_emails'] = join( ', ', $out );
	$message['all_emails'] = str_replace("'", "\'", stripslashes($message['all_emails']));

	$replyFrom = str_replace("'", "\'", stripslashes($replyFrom));
	
	$replyTo = $message['MMM_REPLY_TO'] ? $message['MMM_REPLY_TO'] : $message['MMM_FROM'];
	$replyTo = str_replace("'", "\'", stripslashes($replyTo));
	
	$attachments = array();
	if($message['MMM_ATTACHMENT'])
	{
		$url = str_ireplace('html/scripts/mailmaster.php', '2.0/getattach.php', $_SERVER['HTTP_REFERER']);
/*
		if(onWebasystServer())
			$url = '/MM/2.0/getattach.php';
		else
			$url = '/published/MM/2.0/getattach.php';
*/
		$sxml = new SimpleXMLElement(base64_decode($message['MMM_ATTACHMENT']));

		$i = 0;
		foreach($sxml->FILE as $file)
		{
			$fn = (string)$file->attributes()->FILENAME;
			$fs = (string)$file->attributes()->FILESIZE;
			$ft = (string)$file->attributes()->MIME_TYPE;

			$attachments[$i]['name'] = base64_decode($fn);
			$attachments[$i]['size'] = formatFileSize($fs);
			$attachments[$i]['type'] = $ft;
			$attachments[$i]['url'] = "$url?mid=$mid&file=".urlencode($fn);

			$i++;
		}
	}

	$message['MMM_STATUS'] = MM_STATUS_RECEIVED;

	//
	// Page implementation
	//
	if (!defined('USE_GETTEXT')) {
		define('USE_GETTEXT', 1);
	}

	if(!$message['MMM_REPLY_TO'])
		$message['MMM_REPLY_TO'] = $message['MMM_FROM'];

	$preproc = new WbsSmarty(WBS_DIR."published/MM/2.0/templates", 'MM', substr($language, 0, 2));		
	
	$preproc->assign('styleURL', '../../2.0/css/mm.css');

	$preproc->assign('message', $message);
	$preproc->assign('attachments', $attachments);
	$preproc->assign('errorStr', $errorStr);
	$preproc->assign('mid', $mid);
	$preproc->assign('uid', $MMM_UID);

	$preproc->assign('MM_STATUS_RECEIVED', MM_STATUS_RECEIVED);
	$preproc->assign( 'forceReload', $forceReload );
	$preproc->assign( 'replyFrom', $replyFrom );
	$preproc->assign( 'replyTo', $replyTo );

	$preproc->display( 'viewmessage.html' );

?>