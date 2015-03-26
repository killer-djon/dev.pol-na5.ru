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

				redirectBrowser( PAGE_QP_ADDNEWTHEME, array() );
	}

	if ( isset( $action ) )
		switch ( $action )
		{

			case "share":
			case "unshare":

					$share = ( $action == "share" ) ? 1 : 0;

					$theme = qp_setThemeShareValue( intval( $QPT_ID ), $share, $kernelStrings );

					if ( PEAR::isError( $theme ) )
					{
						$errorStr = $theme->getMessage();
						break;
					}

					break;

			case "delete":

					$theme = qp_deleteTheme( $currentUser, intval( $QPT_ID ), $kernelStrings );

					if ( PEAR::isError( $theme ) )
					{
						$errorStr = $theme->getMessage();
						break;
					}

					break;
		}

	switch( true ) {

			case true: {

				$booksCount = getCountOfAppliedThemes($kernelStrings );

				if ( PEAR::isError( $booksCount ) )
				{
					$fatalError = true;
					$errorStr = $themes->getMessage();
					break;
				}

				$themes = qp_getThemesList( $currentUser, $kernelStrings );

				if ( PEAR::isError( $themes ) )
				{
					$fatalError = true;
					$errorStr = $themes->getMessage();
					break;
				}

				$def = readApplicationSettingValue( $QP_APP_ID, "defaulttheme", "1", $kernelStrings );

				foreach( $themes as $key=>$value )
				{
					$params = array();

					$params["QPT_ID"] = $value["QPT_ID"];
					$params[ACTION] = ACTION_EDIT;

					$value["ROW_URL"] = prepareUrlStr( PAGE_QP_ADDMODTHEME, $params );

					$params = array();
					$params["QPT_ID"] = $value["QPT_ID"];


					if ( $value["QPT_SHARED"] == 1 )
						$params["action"]="unshare";
					else
						$params["action"]="share";

					$value["SHARED"]= $params["action"];

					$value["SHARED_URL"] = prepareUrlStr( PAGE_QP_THEMES, $params );

					$value["BOOK_NUM"] = isset( $booksCount[$key] ) ? $booksCount[$key] : " ";
					if ( isset( $booksCount[$key] ) && intval( $booksCount[$key] ) )
					{
						$value["DELETE_TEXT"] = sprintf( $qpStrings["qpt_themedelete_message"], $value["BOOK_NUM"] );
						$value["UNSHARE_TEXT"] = sprintf( $qpStrings["qpt_themeunshare_message"], $value["BOOK_NUM"] );
					}
					else
						$value["DELETE_TEXT"] = $qpStrings["qpt_themedelete_nobook_message"];


					$params = array();
					$params["QPT_ID"] = $value["QPT_ID"];
					$params["action"]="setdefault";
					$value["DEFAULT_URL"] = prepareUrlStr( PAGE_QP_THEMES, $params );

					$params = array();
					$params["QPT_ID"] = $value["QPT_ID"];
					$params["action"]="delete";
					$value["DELETE_URL"] = prepareUrlStr( PAGE_QP_THEMES, $params );

					$value["DEFAULT"] = 0;

					$themes[$key] = $value;

				}

			}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QP_APP_ID );

	$preproc->assign( PAGE_TITLE, $qpStrings['qpt_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_QP_THEMES );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "qpStrings", $qpStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );

	if ( isset( $showPageSelector ) )
	{
		$preproc->assign( PAGES_SHOW, $showPageSelector );
		$preproc->assign( PAGES_PAGELIST, $pages );
		$preproc->assign( PAGES_CURRENT, $currentPage );
		$preproc->assign( PAGES_NUM, $pageCount );
	}

	if ( !$fatalError )
	{
		$preproc->assign( "themes", $themes );
	}

	$preproc->display( "themes.htm" );
?>
