<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/QN/qn.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QN";

	pageUserAuthorization( $SCR_ID, $QN_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$qnStrings = $qn_loc_str[$language];
	$invalidField = null;
	$contactCount = 0;

	$btnIndex = getButtonIndex( array( BTN_CANCEL, "addtlpbtn" ), $_POST );

	switch ($btnIndex) {
		case 0:
				redirectBrowser( PAGE_QN_QUICKNOTES, array() );
		case 1:
				redirectBrowser( PAGE_QN_ADDMODTPL, array() );
	}

	switch (true)
	{

		case true :

			$tplList = qn_getTemplateList( $currentUser, $kernelStrings );

			if ( PEAR::isError( $tplList ) )
			{
				$fatalError = 1;
				$errorStr = $tplList->getMessage();

				break;
			}

			$tpl1=array();
			foreach( $tplList as $key=>$value)
			{

				$row_url = prepareURLStr( PAGE_QN_ADDMODTPL, array( "QNT_ID"=> $value["QNT_ID"], ACTION=>ACTION_EDIT ) );
				$del_url = prepareURLStr( PAGE_QN_ADDMODTPL, array( "QNT_ID"=> $value["QNT_ID"], ACTION=>"delete" ) );

				$curRecord["ROW_URL"] = $row_url;
				$curRecord["DEL_URL"] = $del_url;

				$value["QNT_MODIFYDATETIME"] = convertToDisplayDateTime( $value["QNT_MODIFYDATETIME"], false, true, true );

				$tpl1[] = array_merge( prepareArrayToDisplay( $value ), $curRecord );

			}

			$tpls = addPagesSupport( $tpl1, RECORDS_PER_PAGE, $showPageSelector, $currentPage, $pages, $pageCount );

			// Prepare pages links
			//
			foreach( $pages as $key => $value )
			{
				$params = array();
				$params[PAGES_CURRENT] = $value;

				$URL = prepareURLStr( PAGE_QN_TPLLIST, $params );
				$pages[$key] = array( $value, $URL );
			}


	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QN_APP_ID );

	$preproc->assign( PAGE_TITLE, $qnStrings['qnt_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_QN_TPLLIST );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "qnStrings", $qnStrings );

	$preproc->assign( PAGES_SHOW, $showPageSelector );
	$preproc->assign( PAGES_PAGELIST, $pages );
	$preproc->assign( PAGES_CURRENT, $currentPage );
	$preproc->assign( PAGES_NUM, $pageCount );

	$preproc->assign( 'tplsNum', count( $tpl1 ) );

	$preproc->assign( HELP_TOPIC, "quicknotes.htm");

	if ( !$fatalError )
	{
		$preproc->assign( "tplList", $tpls );
	}

	$preproc->display( "tpllist.htm" );
?>
