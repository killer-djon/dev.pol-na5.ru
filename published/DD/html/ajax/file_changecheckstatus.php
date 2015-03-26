<?
	include_once("../../../../system/init.php");
	Wbs::authorizeUser("DD");
	Kernel::incAppFile("DD", "dd_app");
	
	$app = DDApplication::getInstance();
	$foldersTree = $app->getFoldersTree();
	
	if (!in_array(Wbs::getDbkeyObj()->getDbkey(), array("VW1151", "VT2065", "AJAX", "VT2033")))
		exit;
	
	$json = new Services_JSON();
	try {
		$fileObj = $foldersTree->getFile(WebQuery::getParam("id"));
		$status = WebQuery::getParam("status");
		$user = CurrentUser::getInstance();
		if ($status == "checkout")
			$res = $fileObj->checkOut($user);
		elseif ($status == "checkin")
			$res = $fileObj->checkIn($user);
		else
			throw new Exception("Try to set wrong status: $status");		
		$result = array ("success" => true, "CHECKED_OUT" => $fileObj->isLocked(), "CHECKED_OUT_INFO" => $fileObj->getCheckoutInfo());
	} catch (Exception $ex) {
		$result = array ("success" => false, "errorStr" => $ex->getMessage());		
	}
	
	print $json->encode($result);
?>