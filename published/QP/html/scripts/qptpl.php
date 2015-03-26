<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/QP/qp.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QP";

	pageUserAuthorization( $SCR_ID, $QP_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$qpStrings = $qp_loc_str[$language];

	$btnIndex = getButtonIndex( array( 'addbtn' ), $_POST );

	switch ($btnIndex) {
		case 0 :
				redirectBrowser( PAGE_QP_QPADDMODTHEME, $commonRedirParams );
	}


	switch( true ) {
			case true: {


			}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QP_APP_ID );

	$preproc->assign( PAGE_TITLE, $qpStrings['qpb_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_QP_BOOKS );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "qpStrings", $qpStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );

	$preproc->assign( PAGES_SHOW, $showPageSelector );
	$preproc->assign( PAGES_PAGELIST, $pages );
	$preproc->assign( PAGES_CURRENT, $currentPage );
	$preproc->assign( PAGES_NUM, $pageCount );

	$preproc->assign( "isRoot", $isRootIdentity );

	if ( !$fatalError )
	{
		$preproc->assign( "themes", $books );
	}

	$preproc->display( "themes.htm" );
?>
