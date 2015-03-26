<?php

	include_once '_screen.init.php';
	Wbs::authorizeUser('MM');

	$app = MMApplication::getInstance();

	$showInput = WebQuery::getParam('showInput', 0);
	$searchString = trim(WebQuery::getParam('searchString', ''));
	$currentPage = WebQuery::getParam('currentPage', 0);
	$currentAction = WebQuery::getParam('currentAction');
	$document = Env::Post('document', Env::TYPE_ARRAY_INT, array());

	if($currentAction == 'delete') {
      $app->deleteMessages($document);
	  $currentPage = 0;
	}

	if($searchString != '') {
		$searchResults = $app->doSearch($searchString, $currentPage);
	}
	else {
		$searchResults = array();
	}

	//
	// Page implementation
	//
	$language = User::getLang();
	$preproc = new WbsSmarty(realpath(dirname(__FILE__))."/templates", 'MM', substr($language, 0, 2));

	$preproc->assign('showInput', $showInput);
	$preproc->assign('searchString', $searchString);
	$preproc->assign('searchResultsCount', $searchResults['count']);
	$preproc->assign('searchResults', $searchResults['content']);

	$preproc->assign('currentPage', $currentPage);
	$preproc->assign('currentAction', $currentAction);

	$preproc->assign('mm_statusNames', $mm_statusNames);
	$preproc->assign('mm_statusStyle', $mm_statusStyle);

	$preproc->display('search.html');

?>