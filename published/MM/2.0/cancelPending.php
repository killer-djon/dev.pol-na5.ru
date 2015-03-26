<?php

	include_once '_screen.init.php';
	Wbs::authorizeUser('MM');

	$mid = WebQuery::getParam('mid');

	try
	{
		$sql = new CUpdateSqlQuery('MMMESSAGE');
		$sql->addFields("MMM_STATUS='".MM_STATUS_DRAFT."'");
		$sql->addConditions("MMM_ID", $mid);
		Wdb::runQuery($sql);
	}
	catch (Exception $e) { exit($e->getMessage()); }

?>