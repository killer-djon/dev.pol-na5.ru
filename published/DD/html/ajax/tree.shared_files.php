<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );
	
	$fatalError = false;
	$error = null;
	$errorStr = null;
	$SCR_ID = "CT";
	pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );
	
	if (isset ($forUser))
		$currentUser = base64_decode($forUser);
	//$ajaxRes = array ("success" => false, "errorStr" => "no result");
	
	// Show widgets
	$widgetManager = getWidgetManager ();
	$shddWidgets = $widgetManager->getUserWidgets ($currentUser, "DDList", "Link", "WG_DESC ASC");
	
	$nodes = array ();
	foreach ($shddWidgets as $cWidget) {
		$link = "../../../WG/html/scripts/viewwidget.php?fapp=DD&WG_ID=" . base64_encode($cWidget["WG_ID"]) . "&interface_wrapper=1";
		$nodes[] = array ("text" => $cWidget["WG_DESC"], "leaf" => true, "allowDrag" => false, "allowDrop" => false, "id" => "wg-" . $cWidget["WG_ID"], "link" => $link, "type" => "shdd", "editable" => true);
	}	
	
	
	print $json->encode($nodes);	
?>