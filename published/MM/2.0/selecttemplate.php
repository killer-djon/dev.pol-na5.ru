<?php

	include_once '_screen.init.php';
	Wbs::authorizeUser('MM');

	$app = MMApplication::getInstance();

	$foldersIds = array_keys($app->getAvailableFolders());

	try {
		$sql = new CSelectSqlQuery('MMMESSAGE');
		$sql->setSelectFields('MMM_ID, MMM_SUBJECT');
		$sql->addConditions("MMM_STATUS='".MM_STATUS_TEMPLATE."'");
		$sql->addConditions("MMF_ID='".join("' OR MMF_ID='", $foldersIds)."'");
		$sql->setOrderBy('MMM_SUBJECT', 'ASC');
		$templatesList = Wdb::getData($sql);
	}
	catch (Exception $e) { exit(sprintf(ERROR_STRING, $e->getMessage())); }

	$path = '../img/tpl/ico';
	for($i=0; $i<count($templatesList); $i++) {
		$ico = $path.'/'.$templatesList[$i]['MMM_ID'].'.gif';
		if(is_file($ico)) {
			$templatesList[$i]['ico'] = $ico;
		} else {
			$templatesList[$i]['ico'] = $path.'/30.gif';
		}
	}

/*	
	$commonIds = $commonTemplates = $userTemplates = array();
	$path = '../img/tpl/ico';
	if(is_dir($path) && ($handle = opendir($path)))
	{
		while(false !== ($file = readdir($handle)))
		{ 
			if($file != '.' && $file != '..')
			{
				$file = explode('.', $file);
				if($file[0])
				{
					$commonIds[] = $file[0];
				}
			}
		}
		closedir($handle); 
	}
	if($commonIds)
	{
		$commonIds = array_flip($commonIds);
		foreach($templatesList as $tpl)
		{
			if(isset($commonIds[$tpl['MMM_ID']]))
			{
				$commonTemplates[] = $tpl;
			}
			else
			{
				$userTemplates[] = $tpl;
			}
		}
	}
*/

	//
	// Page implementation
	//
	$language = User::getLang();
	$preproc = new WbsSmarty(realpath(dirname(__FILE__))."/templates", 'MM', substr($language, 0, 2));

	$preproc->assign('templatesList', $templatesList);

	//$preproc->assign('commonTemplates', $commonTemplates);
	//$preproc->assign('userTemplates', $userTemplates);

	$preproc->display('selecttemplate.html');

?>