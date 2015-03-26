<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "CT";

	$processButtonTemplate = "javascript:processTextButton('%s', 'form')";
	$processAjaxButtonTemplate = "javascript:processAjaxButton('%s')";

	define( 'EXPAND', 'expand' );
	define( 'COLLAPSE', 'collapse' );
	define( 'HIDE_FOLDER', 'hidefolders' );
	define( 'ACTION_DELETEFOLDER', 'DELETE' );
	define( 'ACTION_SHOWALLUSERS', 'SHOWALLUSERS' );
	define( 'ACTION_SHOWALLGROUPS', 'SHOWALLGROUPS' );

	pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$ddStrings = $dd_loc_str[$language];
	
	// Show widgets
	$widgetManager = getWidgetManager ();
	$shddWidgets = $widgetManager->getUserWidgets ($currentUser, "DDList", "Link", "WG_DESC ASC");
	
	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$preproc->assign( PAGE_TITLE, $ddStrings['links_screen_name'] );
	
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "ddStrings", $ddStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );
	
	$preproc->assign( "links", $shddWidgets);

	if ($preproc->get_template_vars('ajaxAccess')) {
		require_once( "../../../common/html/includes/ajax.php" );
		$ajaxRes = array ();
		$ajaxRes["toolbarContent"] = "";
		$ajaxRes["rightContent"] = $preproc->fetch( "links_rightpanel.htm" );
		print simple_ajax_encode($ajaxRes);
		exit;
	}
	
	$preproc->display( "links.htm" );
?>