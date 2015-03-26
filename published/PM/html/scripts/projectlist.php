<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/PM/pm.php" );

	//
	// Authorization
	//

	$SCR_ID = "WL";
	$errorStr = null;
	$fatalError = false;

	pageUserAuthorization( $SCR_ID, $PM_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$pmStrings = $pm_loc_str[$language];

	$btnIndex = getButtonIndex( array(PM_BTN_CLOSEDPROJECTS, PM_BTN_ADDPROJECT, PM_BTN_ACTIVEPROJECTS, BTN_CANCEL), $_POST );

	switch( $btnIndex ) {
		case( 0 ) : {
			redirectBrowser( PAGE_PM_PROJECTLIST, array("folder"=>RS_DELETED, SORTING_COL=>$sorting, "currentPage"=>1) );

			break;
		}

		case( 1 ) : {
			redirectBrowser( PAGE_PM_ADDMODPROJECT, array("folder"=>$folder, OPENER=>PAGE_PM_PROJECTLIST, ACTION=>PM_ACTION_NEW, SORTING_COL=>$sorting, "currentPage"=>$currentPage) );

			break;
		}

		case( 2 ) : {
			redirectBrowser( PAGE_PM_PROJECTLIST, array("folder"=>RS_ACTIVE, SORTING_COL=>$sorting, "currentPage"=>1) );
			break;
		}
		
		case 3:
			// Cancel
			redirectBrowser( PAGE_PM_WORKLIST, array() );
			break;
	}

	switch ( true ) {
		case ( true ) : {
			if ( !isset($folder) || !strlen($folder) )
				$folder = RS_ACTIVE;

			if ( !isset($currentPage) || !strlen($currentPage) )
				$currentPage = 1;

			if ( !isset($sorting) || !strlen($sorting) )
				$sorting = "C_NAME asc";
			else
				$sorting = base64_decode( $sorting );

			$sortClause = $sorting;

			$sortData = parseSortStr($sortClause);

			if ( $sortData['field'] == 'MANAGER_NAME' )
				$sortClause = sprintf( "C_FIRSTNAME %s, C_MIDDLENAME %s, C_LASTNAME %s", $sortData['order'], $sortData['order'], $sortData['order'] );

			if ( !$folder )
				$screen = PAGE_PM_ADDMODPROJECT;
			else
				$screen = PAGE_PM_REOPENPROJECT;
			
			/*if ( !$folder )
				$query = $qr_pm_count_active_projects;
			else
				$query = $qr_pm_count_closed_projects;*/
			$query = $qr_pm_select_projects;

			$projects = array();

			$qr = db_query( sprintf($query, $sortClause) );
			
			if ( PEAR::isError($qr) ) {
				$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_PROJECTFIELD] );

				$fatalError = true;
				break;
			}

			while ( $row = db_fetch_array($qr) ) {
				$curRecord = prepareArrayToDisplay( $row );

				$params['folder'] = null;
				$params['currentPage'] = null;
				$params['P_ID'] = base64_encode( $curRecord["P_ID"] );
				$params[OPENER] = PAGE_PM_PROJECTLIST;
				$params[ACTION] = PM_ACTION_MODIFY;
				$params[SORTING_COL] = null;
				
				$curRecord["P_STARTDATE"] = convertToDisplayDate( $curRecord["P_STARTDATE"] );
				$curRecord["MANAGER"] = getUserName( $curRecord["U_ID_MANAGER"], true );
				$curRecord["ROW_URL"] = //prepareURLStr (PAGE_PM_WORKLIST, array ("projectData[P_ID]" => $curRecord["P_ID"]));
					prepareURLStr( PAGE_PM_ADDMODPROJECT, $params );
																		 
				
				$projects[count($projects)] = $curRecord;
			}

			@db_free_result( $qr );

			$projects = addPagesSupport( $projects, RECORDS_PER_PAGE, $showPageSelector, $currentPage, $pages, $pageCount );

			// Prepare pages links
			//
			foreach( $pages as $key => $value )
			{
				$params = array();
				$params[PAGES_CURRENT] = $value;

				$URL = prepareURLStr( PAGE_PM_PROJECTLIST, $params );
				$pages[$key] = array( $value, $URL );
			}

			// Tabs
			//
			/*$tabs = array();

			$checked = ($folder == RS_ACTIVE) ? "checked" : "unchecked";
			$tabs[] = array( 'caption'=>$pmStrings['pl_openprojects_tab'], 'link'=>sprintf( $processButtonTemplate, PM_BTN_ACTIVEPROJECTS )."||$checked" );

			$checked = ($folder == RS_DELETED) ? "checked" : "unchecked";
			$tabs[] = array( 'caption'=>$pmStrings['pl_completeprojects_tab'], 'link'=>sprintf( $processButtonTemplate, PM_BTN_CLOSEDPROJECTS )."||$checked" );*/
		}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $PM_APP_ID );

	$preproc->assign( "pmStrings", $pmStrings );
	$preproc->assign( PAGE_TITLE, $pmStrings['pl_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_PM_PROJECTLIST );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( SORTING_COL, $sorting );
	$preproc->assign( "folder", $folder );

	$preproc->assign( "genericLinkUnsorted", prepareURLStr(PAGE_PM_PROJECTLIST, array("folder"=>$folder, "currentPage"=>$currentPage)) );

	$preproc->assign( "projects", $projects );
	$preproc->assign( "projectNum", count($projects) );

	//$preproc->assign( "tabs", $tabs );

	$preproc->assign( PAGES_SHOW, $showPageSelector );
	$preproc->assign( PAGES_PAGELIST, $pages );
	$preproc->assign( PAGES_CURRENT, $currentPage );
	$preproc->assign( PAGES_NUM, $pageCount );
	
	$preproc->display("projectlist.htm");
?>