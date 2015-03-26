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

	$locStrings = $loc_str[$language];
	$kernelStrings = $loc_str[$language];
	$qpStrings = $qp_loc_str[$language];
	$invalidField = null;

	if ( isset( $newtype ) )
		$newtype = ( $newtype == 0 ) ? 0 : 1;
	else
		$newtype = 0;

	$action = ACTION_EDIT;

	switch (true)
	{
		case true :

				if ( $action == ACTION_EDIT )
				{

					$theme = qp_getTheme( $currentUser, $QPT_ID, $kernelStrings );

					if ( PEAR::isError( $theme ) )
					{
						$errorStr = $res->getMessage();
						$fatalError=true;
						break;
					}

					if ( is_null( $theme ) )
					{
						$errorStr = $qpStrings["qpt_wrong_theme_message"];
						$fatalError=true;
						break;
					}

					$themeName = $theme["QPT_NAME"];

				}
	}

	$btnIndex = getButtonIndex( array( 'savebtn', 'cancelbtn', 'editbtn' ), $_POST, false );

	$commonRedirParams = array();

	switch ($btnIndex)
	{

		case 'editbtn' :
		case 'savebtn' :

				if ( $type == 'tree' )
				{
					if ( !isset( $properties["tree_top_visible"] ) )
						$properties["tree_top_visible"] = 0;

					if ( !isset( $properties["tree_bname_visible"] ) )
						$properties["tree_bname_visible"] = 0;

					if ( !isset( $properties["tree_srch_visible"] ) )
						$properties["tree_srch_visible"] = 0;

					if ( !isset( $properties["tree_toc_wrap"] ) )
						$properties["tree_toc_wrap"] = 0;

					if ( !isset( $properties["tree_toc_pageicons"] ) )
						$properties["tree_toc_pageicons"] = 0;

					if ( !isset( $properties["tree_pn_visible"] ) )
						$properties["tree_pn_visible"] = 0;
				}
				else
				{
					if ( !isset( $properties["plain_top_visible"] ) )
						$properties["plain_top_visible"] = 0;

					if ( !isset( $properties["plain_bname_visible"] ) )
						$properties["plain_bname_visible"] = 0;

					if ( !isset( $properties["plain_tochdr_visible"] ) )
						$properties["plain_tochdr_visible"] = 0;

					if ( !isset( $properties["plain_toc_visible"] ) )
						$properties["plain_toc_visible"] = 0;

					if ( !isset( $properties["plain_pdelim_visible"] ) )
						$properties["plain_pdelim_visible"] = 0;

					if ( !isset( $properties["plain_ptitle_visible"] ) )
						$properties["plain_ptitle_visible"] = 0;

					if ( !isset( $properties["plain_toclink_visible"] ) )
						$properties["plain_toclink_visible"] = 0;
				}

				if ( $btnIndex == 'editbtn' )
				{
					session_register( "propArray" );

					$propArray = array();

					$propArray['$QPT_ID'] = $QPT_ID;

					$propArray['THEMEDATA'] = $themeData;
					$propArray['PROPERTIES'] = $properties;

					redirectBrowser( PAGE_QP_EDITFRAME, array( "QPT_ID"=>$QPT_ID ) );
					break;
				}


				$publishParams = new wbsParameters( $qp_publish_data_schema );

				$publishParams->loadFromXML( $theme['QPT_PROPERTIES'], $kernelStrings, true, null );

				$ret = $publishParams->loadFromArray( $properties, $kernelStrings, true, null );

				if ( PEAR::isError( $ret ) )
				{
					$fatalError = true;
					$errorStr = $ret->getMessage();
					break;
				}

				$xml = $publishParams->getValuesXML();

				$theme['QPT_PROPERTIES'] = $xml;

				$theme['QPT_NAME'] = $themeData['QPT_NAME'];

				$res = qp_addmodTheme( $action, $QPT_ID, $theme, $kernelStrings, $qpStrings );

				if ( PEAR::isError($res) )
				{
					$errorStr = $res->getMessage();

					if ( $res->getCode() == ERRCODE_INVALIDFIELD )
						$invalidField = $res->getUserInfo();

					break;
				}

				if ( isset( $themeData['QPT_DEFAULT'] ) )
					setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QPT_DEFAULT', $res, $kernelStrings, $readOnly );

		case 'cancelbtn' :

				redirectBrowser( PAGE_QP_THEMES, array() );
				break;
	}

	switch (true) {
		case true :

				if ( $fatalError )
					break;

				if ( session_is_registered( "propArray" ) )
				{
					$propArray = $_SESSION["propArray"];
					$themeData = $theme;
					$newtype = $themeData['QPT_TYPE'];
					$properties = $propArray['PROPERTIES'];

					session_unregister( "propArray" );

					$edited = 1;
				}

				if ( isset($edited) && $edited==1 )
				{
				}
				else
				{
					if ( $action == ACTION_EDIT )
					{
						$themeData = $theme;
						$newtype = $themeData['QPT_TYPE'];
					}
					else
					{
						$themeData = array();

						$themeData['QPT_ID']="";
						$themeData['QPT_PROPERTIES']="";
						$themeData['QPT_SHARED']=0;

						$themeData['QPT_TYPE']=$newtype;
						$themeData['QPT_USERID']=$currentUser;

						$themeData['QPT_ATTACHMENTS']="";
						$themeData['QPT_UNIQID']="";

					}

					$publishParams = new wbsParameters( $qp_publish_data_schema );

					if ( $action == ACTION_EDIT )
						$publishParams->loadFromXML( $themeData["QPT_PROPERTIES"], $kernelStrings, true, null );

					$properties = $publishParams->getValuesArray();
				}

				// Prepare form tabs
				//
				$tabs = array();

				$type = ( $newtype == 1 ) ? "tree" : "plain";

				if ( $type == "plain" )
				{
					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_topframe_title'],
										PT_PAGE_ID=>'TOP',
										PT_FILE=>'publishsetup_plain.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_bookname_title'],
										PT_PAGE_ID=>'BNAME',
										PT_FILE=>'publishsetup_plain.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_tocheader_title'],
										PT_PAGE_ID=>'TOCHEADER',
										PT_FILE=>'publishsetup_plain.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_tocbody_title'],
										PT_PAGE_ID=>'TOCBODY',
										PT_FILE=>'publishsetup_plain.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_pagetitle_title'],
										PT_PAGE_ID=>'PAGETITLE',
										PT_FILE=>'publishsetup_plain.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_h1_title'],
										PT_PAGE_ID=>'H1',
										PT_FILE=>'publishsetup_plain.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_h2_title'],
										PT_PAGE_ID=>'H2',
										PT_FILE=>'publishsetup_plain.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_h3_title'],
										PT_PAGE_ID=>'H3',
										PT_FILE=>'publishsetup_plain.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_pagetext_title'],
										PT_PAGE_ID=>'BODY',
										PT_FILE=>'publishsetup_plain.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_links_title'],
										PT_PAGE_ID=>'LINKS',
										PT_FILE=>'publishsetup_plain.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_toclink_title'],
										PT_PAGE_ID=>'TOCLINK',
										PT_FILE=>'publishsetup_plain.htm'
									);

				}
				else
				{
					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_topframe_title'],
										PT_PAGE_ID=>'TOP',
										PT_FILE=>'publishsetup_tree.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_bookname_title'],
										PT_PAGE_ID=>'BNAME',
										PT_FILE=>'publishsetup_tree.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_searchpanel_title'],
										PT_PAGE_ID=>'SPANEL',
										PT_FILE=>'publishsetup_tree.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_tocheader_title'],
										PT_PAGE_ID=>'TOCHEADER',
										PT_FILE=>'publishsetup_tree.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_tocbody_title'],
										PT_PAGE_ID=>'TOCBODY',
										PT_FILE=>'publishsetup_tree.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_curtocrow_title'],
										PT_PAGE_ID=>'TOCS',
										PT_FILE=>'publishsetup_tree.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_splitter_title'],
										PT_PAGE_ID=>'SPLITTER',
										PT_FILE=>'publishsetup_tree.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_h1_title'],
										PT_PAGE_ID=>'H1',
										PT_FILE=>'publishsetup_tree.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_h2_title'],
										PT_PAGE_ID=>'H2',
										PT_FILE=>'publishsetup_tree.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_h3_title'],
										PT_PAGE_ID=>'H3',
										PT_FILE=>'publishsetup_tree.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_pagetext_title'],
										PT_PAGE_ID=>'BODY',
										PT_FILE=>'publishsetup_tree.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_links_title'],
										PT_PAGE_ID=>'LINKS',
										PT_FILE=>'publishsetup_tree.htm'
									);

					$tabs[] = array( PT_NAME=>$qpStrings['qp_publish_settings_pn_title'],
										PT_PAGE_ID=>'PREVNEXT',
										PT_FILE=>'publishsetup_tree.htm'
									);

				}

	}

	$preproc = new php_preprocessor( $templateName, $locStrings, $language, $QP_APP_ID );

	$preproc->assign( PAGE_TITLE, prepareStrToDisplay( $qpStrings["qpt_screen_customizetheme_title"], true, true ) );

	$preproc->assign( FORM_LINK, PAGE_QP_ADDMODTHEME );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "qpStrings", $qpStrings );

	$preproc->assign( 'QPT_ID', $QPT_ID );

	$preproc->assign( 'action', $action );

	$preproc->assign( 'colorArray', $qp_colors );
	$preproc->assign( 'fontSizes', $qp_optFSize );
	$preproc->assign( 'fontStyles', $qp_optFWeight );
	$preproc->assign( 'fontNames', $qp_optFFamily );
	$preproc->assign( 'adjustNames', $qp_optAdjust );
	$preproc->assign( 'positionNames', qp_prepareLocArray( $qp_optPosition, $qpStrings ) );

	$preproc->assign( 'dataSchema', $qp_publish_data_schema );

	if ( !$fatalError )
	{
		$preproc->assign( 'topempty', trim( $theme["QPT_HEADER"] ) == "" ? "<br>".$qpStrings["qpt_topframe_empty"] : "" );

		$preproc->assign( 'themeData', $themeData );
		$preproc->assign( 'properties', $properties );
		$preproc->assign( "tabs", $tabs );
		$preproc->assign( "newtype", $newtype );
		$preproc->assign( "type", $type );
	}

	$preproc->display( "addmodtheme.htm" );

?>