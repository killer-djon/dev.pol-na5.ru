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

	$btnIndex = getButtonIndex( array(PM_BTN_DELETEDCUSTOMERS, PM_BTN_ADDCUSTOMER, 
										PM_BTN_ACTIVECUSTOMERS, 'inactivatebtn', 
										'deletebtn', 'reactivatebtn'), $_POST );

	switch( $btnIndex ) {
		case 0 :
				redirectBrowser( PAGE_PM_CUSTOMERLIST, array( "folder"=>RS_DELETED, SORTING_COL=>$sorting, "currentPage"=>1) );
		case 1 :
				redirectBrowser( PAGE_PM_ADDMODCUSTOMER, array(ACTION=>PM_ACTION_NEW, SORTING_COL=>$sorting, "currentPage"=>$currentPage) );
		case 2 :
				redirectBrowser( PAGE_PM_CUSTOMERLIST, array("folder"=>RS_ACTIVE, SORTING_COL=>$sorting, "currentPage"=>1) );
		case 3 : 
				if ( !isset($document) )
					break;

				$document = array_keys( $document );

				foreach ( $document as $C_ID ) {
					$customerData = array();
					$customerData['C_ID'] = $C_ID;
					$customerData["C_MODIFYUSERNAME"] = getUserName( $currentUser, true );

					pm_deleteCustomer( $customerData, $pmStrings, false, $language, $kernelStrings );
				}
		case 4 :
				if ( !isset($document) )
					break;

				$document = array_keys( $document );

				foreach ( $document as $C_ID ) {
					$customerData = array();
					$customerData['C_ID'] = $C_ID;
					$customerData["C_MODIFYUSERNAME"] = getUserName( $currentUser, true );

					pm_deleteCustomer( $customerData, $pmStrings, true, $language, $kernelStrings );
				}
		case 5 :
				if ( !isset($document) )
					break;

				$document = array_keys( $document );
				foreach ( $document as $C_ID ) {
					$customerData = array();
					$customerData['C_ID'] = $C_ID;
					$customerData["C_MODIFYUSERNAME"] = getUserName( $currentUser, true );

					pm_restoreCustomer( prepareArrayToStore($customerData), $pmStrings, $kernelStrings );
				}
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
			
			$sortData = parseSortStr( $sorting );

			if ( $sortData["field"] == "C_ADDRESS" ) {
				$order = $sortData["order"];

				$sortClause = sprintf( "C_ADDRESSSTREET %s, C_ADDRESSCITY %s, C_ADDRESSCOUNTRY %s", $order, $order, $order );
			}
			else
				$sortClause = $sorting;

			$pageTitle = $pmStrings['cl_screen_long_name'];
			if ( $folder )
				$pageTitle = $pmStrings['cl_inactivelist_title'];

			if ( !$folder )
				$screen = PAGE_PM_ADDMODCUSTOMER;
			else
				$screen = PAGE_PM_RESTORECUSTOMER;

			$customers = array();

			$qr = db_query( sprintf($qr_pm_select_customer_list, $folder, $sortClause) );
			if ( PEAR::isError($qr) ) {
				$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_CUSTOMERFIELD] );

				$fatalError = true;
				break;
			}
	
			while ( $row = db_fetch_array($qr) ) {
				$curRecord = prepareArrayToDisplay( $row );

				$curRecord["ROW_URL"] = prepareURLStr( $screen, array(ACTION=>PM_ACTION_MODIFY, SORTING_COL=>base64_encode($sorting), "C_ID"=>base64_encode($row["C_ID"]), "currentPage"=>$currentPage) );

				$curRecord["ACTIVE_NUM"] = db_query_result( $qr_pm_count_customer_active_projects, DB_FIRST, $row );
				$curRecord["TOTAL_NUM"] = db_query_result( $qr_pm_count_customer_projects, DB_FIRST, $row );

				$customers[count($customers)] = $curRecord;
			}

			@db_free_result( $qr );

			$customers = addPagesSupport( $customers, RECORDS_PER_PAGE, $showPageSelector, $currentPage, $pages, $pageCount );

			// Prepare pages links
			//
			foreach( $pages as $key => $value )
			{
				$params = array();
				$params[PAGES_CURRENT] = $value;

				$URL = prepareURLStr( PAGE_PM_CUSTOMERLIST, $params );
				$pages[$key] = array( $value, $URL );
			}

			// Customer menu
			//
			$custMenu = array();

			if ( !$folder ) {
				$custMenu[$pmStrings['cl_addcust_btn']] = sprintf( $processButtonTemplate, 'addcbtn' );
				
				$custMenu[$pmStrings['cl_inactivate_menu']] = sprintf( $processButtonTemplate, 'inactivatebtn' )."||confirmInactivate()";
				$custMenu[$pmStrings['cl_delete_menu']] = sprintf( $processButtonTemplate, 'deletebtn' )."||confirmDelete()";
				$custMenu[null] = "-";

				$importURL = prepareURLStr( PAGE_PM_IMPORT, array() );
				$custMenu[$pmStrings['cl_import_menu']] = $importURL;
			} else {
				$custMenu[$pmStrings['cl_reactivate_menu']] = sprintf( $processButtonTemplate, 'reactivatebtn' )."||confirmReactivate()";
				$custMenu[$pmStrings['cl_delete_menu']] = sprintf( $processButtonTemplate, 'deletebtn' )."||confirmDelete()";
			}

			// Tabs
			//
			$tabs = array();

			$checked = ($folder == RS_ACTIVE) ? "checked" : "unchecked";
			$tabs[] = array( 'caption'=>$pmStrings['cl_activecust_tab'], 'link'=>sprintf( $processButtonTemplate, PM_BTN_ACTIVECUSTOMERS )."||$checked" );

			$checked = ($folder == RS_DELETED) ? "checked" : "unchecked";
			$tabs[] = array( 'caption'=>$pmStrings['cl_inactivecust_tab'], 'link'=>sprintf( $processButtonTemplate, PM_BTN_DELETEDCUSTOMERS )."||$checked" );
		}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $PM_APP_ID );

	$preproc->assign( "pmStrings", $pmStrings );
	$preproc->assign( PAGE_TITLE, $pageTitle );
	$preproc->assign( FORM_LINK, PAGE_PM_CUSTOMERLIST );
	$preproc->assign( SORTING_COL, $sorting );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "folder", $folder );

	$preproc->assign( "genericLinkUnsorted", prepareURLStr( PAGE_PM_CUSTOMERLIST, array("folder"=>$folder, "currentPage"=>$currentPage) ) );

	if ( !$fatalError ) {
		$preproc->assign( "customers", $customers );
		$preproc->assign( "customerNum", count($customers) );
		$preproc->assign( "custMenu", $custMenu );
		$preproc->assign( "tabs", $tabs );
	}

	$preproc->assign( PAGES_SHOW, $showPageSelector );
	$preproc->assign( PAGES_PAGELIST, $pages );
	$preproc->assign( PAGES_CURRENT, $currentPage );
	$preproc->assign( PAGES_NUM, $pageCount );

	$preproc->display("customerlist.htm");
?>