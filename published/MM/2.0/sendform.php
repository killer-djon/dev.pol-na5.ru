<?php

	include_once '_screen.init.php';
	Wbs::authorizeUser('MM');
	include_once '../../common/html/scripts/tmp_functions.php';

	$app = MMApplication::getInstance();

	$currentObject = WebQuery::getParam('currentObject');
	$currentPage = WebQuery::getParam('currentPage', 0);
	$searchString = WebQuery::getParam('searchString');
	$mid = WebQuery::getParam('mid');
	$action = WebQuery::getParam('action');

	try
	{
		$user = CurrentUser::getInstance()->getData();

		$senders = array();
		$senders[] = Contact::getName(User::getContactId(), Contact::FORMAT_NAME_EMAIL);

		$senders = array_merge($senders, $app->getSenders(User::getId()));

		$currentSender = stripslashes(User::getSetting('CURRENT_SENDER', 'MM'));

		$lists = $app->getLists();
	}
	catch (Exception $ex) { exit(sprintf(ERROR_STRING, $ex->getMessage())); }

	$CMfolders = Contact::getFolders();

	if($searchString) {
		$limit = false;
	} else {
		$limit = ($currentPage * CONTACTS_PER_PAGE).', '.(CONTACTS_PER_PAGE + 1);
	}

	$contacts_model = new ContactsModel();
	if(!$currentObject || $currentObject==-1) {
		$contacts = $contacts_model->getWithEmailsByFolderId(false, $limit);
	} else {
		$contacts = $contacts_model->getWithEmailsByFolderId($currentObject, $limit);
	}

	$contactsForInclude = $contactsForShow = array();
	if(is_array($contacts))
		foreach($contacts as $item)
		{
			$str = joinContactAddress($item, true);
			if(!$searchString)
				$contactsForInclude[] = $contactsForShow[] = $str;
			elseif(stripos($str, $searchString) !== false)
			{
				$contactsForInclude[] = $str;
				$contactsForShow[] = preg_replace("/$searchString/i", '<span class="searchResults">'.$searchString.'</span>', $str);
			}
		}

	if(count($contactsForInclude) > CONTACTS_PER_PAGE)
		$showMore = true;
	else
		$showMore = false;

	if($searchString)
		$offset = $currentPage * CONTACTS_PER_PAGE;
	else
		$offset = 0;
	$contactsForInclude = array_slice( $contactsForInclude, $offset, CONTACTS_PER_PAGE, true );

	clearUploadedFiles(); // to ajax first load ???

	$now = CDateTime::now();
	$serverDateTime = date($now->displayFormat . ' H:i', $now->value);
	$nextTimestamp = $now->value + 3600;
	$nextDate = date($now->displayFormat, $nextTimestamp);
	$nextTime = date('H:00', $nextTimestamp);

	if($mid)
		$message = $app->getMessage($mid);
	else
		$message = false;

	try	{	$folders = $app->getAvailableFolders(2); } // minimalRights >= 2 (writeable)
	catch (Exception $ex) { exit(sprintf(ERROR_STRING, $ex->getMessage())); }

	unset($folders[0]);

	$vars = ContactType::getFieldsNames(false, false, true);
	$contactVariables = array('NAME' => _('Primary name'));
	$myVariables = array('MY_NAME' => _('Primary name'));
	foreach($vars as $key=>$val)
	{
		$key = preg_replace('/^C_/i', '', $key);
		$contactVariables[$key] = $val;
		$myVariables['MY_'.$key] = $val;
	}
	
	//
	// Page implementation
	//
	$language = User::getLang();

	$preproc = new WbsSmarty(realpath(dirname(__FILE__))."/templates", 'MM', substr($language, 0, 2));

	$preproc->assign('senders', $senders);
	$preproc->assign('currentSender', $currentSender);

	$preproc->assign('lists', $lists);
	$preproc->assign('CMfolders', $CMfolders);
	$preproc->assign('folders', $folders);
	$preproc->assign('contactsForInclude', $contactsForInclude);
	$preproc->assign('contactsForShow', $contactsForShow);
	$preproc->assign('currentObject', $currentObject);
	$preproc->assign('showMore', $showMore);

	$langTrans = array('rus'=>'ru', 'gem'=>'de');
	if(empty($langTrans[$language])) $lang = 'en';
	else $lang = $langTrans[$language];
	$preproc->assign('EDITOR_LANGUAGE', $lang);

	if(defined('USE_LOCALIZATION') && USE_LOCALIZATION)
		$preproc->assign('js_url', substr($language, 0, 2).'/');
	else
		$preproc->assign('js_url', 'source/');

	for($i=0; $i<24; $i++)
		$timeArray[] = sprintf("%02d:00", $i);
	$preproc->assign('timeArray', $timeArray);

	$preproc->assign('message', $message);

	$preproc->assign('reloadMessageURL', WebQuery::getPublishedUrl("/MM/2.0/reloadmessage.php"));
	$preproc->assign('c_autocompleteURL', WebQuery::getPublishedUrl("/MM/2.0/ajax/c_autocomplete.php"));

	$preproc->assign('contactVariables', $contactVariables);
	$preproc->assign('myVariables', $myVariables);
	$preproc->assign('companyVariables', $companyVariables);

	if($action == 'template' && !$mid) // compose new template
	{
		$preproc->assign('sendDisplay', 'none');
		$preproc->assign('templateDisplay', '');
	}
	else
	{
		$preproc->assign('sendDisplay', '');
		$preproc->assign('templateDisplay', 'none');
	}

	$preproc->assign('user_time', date('D,j M Y G:i:s', WbsDateTime::getTimeStamp(time())));

	// Calendar support
	//
	$dateFormat = WbsDateTime::$dateFormat['phpFormat'];
	if($dateFormat)
	{
		$monthNames = array(
			_('January'), _('February'), _('March'),
			_('April'), _('May'), _('June'),
			_('July'), _('August'), _('September'),
			_('October'), _('November'), _('December')
			);
		$weekdayNames = array(
			_('Mon'), _('Tue'), _('Wed'), _('Thu'), _('Fri'), _('Sat'), _('Sun')
			);
		$calendarStrings = array(
			'today' => _('Today'),
			'wk' => _('wk.'),
			'wk_tip' => _('Week Number'),
			'close' => _('Close'),
			'prevyear' => _('Previous Year'),
			'nextyear' => _('Next Year'),
			'prevmonth' => _('Previous Month'),
			'nextmonth' => _('Next Month')
			);
		$preproc->assign('dateformat', $dateFormat);
		$preproc->assign('monthNames', $monthNames );
		$preproc->assign('weekdayNames', $weekdayNames);
		$preproc->assign('calendarStrings', $calendarStrings);
	}

	$preproc->display('sendform.html');

?>