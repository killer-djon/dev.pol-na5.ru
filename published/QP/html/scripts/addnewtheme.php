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

	$btnIndex = getButtonIndex( array( 'okbtn', 'cancelbtn' ), $_POST );

	switch ($btnIndex)
	{
		case 0 :

				if ( $newtype == "copy" )
				{
					if ( !PEAR::isError( $res = qp_copyTheme( $currentUser, $DB_KEY, $fromTheme, $themeData["QPT_NAME"], $kernelStrings, $qpStrings ) ) )
					{
						$params = array( ACTION=>ACTION_EDIT, "QPT_ID" =>$res );
						redirectBrowser( PAGE_QP_ADDMODTHEME, $params );
					}
					else
					{
						$errorStr = $res->getMessage();

						if ( $res->getCode() == ERRCODE_INVALIDFIELD )
							$invalidField = $res->getUserInfo();

						break;
					}
				}

				$themeData['QPT_ID']="";
				$themeData['QPT_PROPERTIES']="";
				$themeData['QPT_SHARED']=1;

				$themeData['QPT_TYPE']=$newtype;
				$themeData['QPT_USERID']=$currentUser;

				$themeData['QPT_ATTACHMENTS']="";
				$themeData['QPT_UNIQID']="";
				$themeData['QPT_HEADER']=" ";

				$res = qp_addmodTheme( ACTION_NEW, $themeData['QPT_ID'], $themeData, $kernelStrings, $qpStrings );

				if ( PEAR::isError($res) )
				{
					$errorStr = $res->getMessage();

					if ( $res->getCode() == ERRCODE_INVALIDFIELD )
						$invalidField = $res->getUserInfo();

					break;
				}

				$themeData["QPT_UNIQID"] = qp_generateUniqueID( $currentUser, "THEME-".$res );
				$res = qp_addmodTheme( ACTION_EDIT, $res, $themeData, $kernelStrings, $qpStrings );

				if ( PEAR::isError($res) )
				{
					$errorStr = $res->getMessage();
					break;
				}

				$params = array( ACTION=>ACTION_EDIT, "QPT_ID" =>$res );

				redirectBrowser( PAGE_QP_ADDMODTHEME, $params );

		case 1 :
				redirectBrowser( PAGE_QP_THEMES, array() );
	}


	switch( true )
	{
			case true: {

				if ( !isset( $edited ) )
					$themeData = array();

				$themes = qp_getThemesList( $currentUser, $kernelStrings );

				if ( PEAR::isError( $themes ) )
				{
					$fatalError = true;
					$errorStr = $themes->getMessage();
					break;
				}
			}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QP_APP_ID );

	$preproc->assign( PAGE_TITLE, $qpStrings['qpt_add_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_QP_ADDNEWTHEME );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( "qpStrings", $qpStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );

	if ( !$fatalError )
	{
		$preproc->assign( "themeData", $themeData );
		$preproc->assign( "themes", $themes );
		$preproc->assign( "themesCount", count( $themes ) );
	}

	$preproc->display( "addnewtheme.htm" );
?>
