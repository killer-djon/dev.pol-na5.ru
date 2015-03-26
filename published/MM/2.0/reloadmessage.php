<?php

	include_once '_screen.init.php';
	Wbs::authorizeUser('MM');
	include_once '../../common/html/scripts/tmp_functions.php';

	$app = MMApplication::getInstance();

	$mid = WebQuery::getParam('mid');
	$action = WebQuery::getParam('action');

	$message = $app->getMessage($mid);

	clearUploadedFiles();

	$attachments = array();

	if($message['MMM_ATTACHMENT'])
	{
		$url = WebQuery::getPublishedUrl('/MM/2.0/getattach.php', null, true);

		$sxml = new SimpleXMLElement(base64_decode($message['MMM_ATTACHMENT']));

		$i = 0;
		foreach($sxml->FILE as $file)
		{
			$fn = (string)$file->attributes()->FILENAME;
			$fs = (string)$file->attributes()->FILESIZE;
			$ft = (string)$file->attributes()->MIME_TYPE;

			$attachments[$i]['name'] = base64_decode($fn);
			$attachments[$i]['size'] = $fs;
			$attachments[$i]['type'] = $ft;
			$attachments[$i]['url'] = "$url?mid=$mid&file=$fn";

			$path = Wbs::getSystemObj()->files()->getDataPath().'/'.Wbs::getDbkeyObj()->getDbkey()
				.'/attachments/mm/attachments/'.$mid.'/'.$attachments[$i]['name'];

			$attachments[$i]['body'] = @file_get_contents($path);

			reloadFile($attachments[$i]);

			$attachments[$i]['size'] = formatFileSize($fs);

			$i++;
		}
	}

	$message['MMM_TO'] = addslashes($message['MMM_TO']);
	$message['MMM_CC'] = addslashes($message['MMM_CC']);
	$message['MMM_BCC'] = addslashes($message['MMM_BCC']);
	$message['MMM_SUBJECT'] = addslashes($message['MMM_SUBJECT']);

	if($message['MMM_STATUS'] == MM_STATUS_PENDING)
		$message['MMM_STATUS'] = MM_STATUS_DRAFT;

	if(strpos($message['MMM_CONTENT'], '{TEMPLATE_IMAGES_URL}') != 0)
		$message['MMM_CONTENT'] = str_replace('{TEMPLATE_IMAGES_URL}',
			'http://'.$_SERVER['HTTP_HOST'].Url::get(''), $message['MMM_CONTENT']);

//	$message['MMM_CONTENT'] = get_magic_quotes_gpc() ? stripslashes($message['MMM_CONTENT']) : $message['MMM_CONTENT'];

	//
	// Page implementation
	//
	$language = User::getLang();
	$preproc = new WbsSmarty(realpath(dirname(__FILE__))."/templates", 'MM', substr($language, 0, 2));

	$preproc->assign('message', $message);
	$preproc->assign('attachments', $attachments);
	$preproc->assign('lists', explode(',', $message['MMM_LISTS']));
	$preproc->assign('action', $action);

	$preproc->display('fillsendform.html');

?>