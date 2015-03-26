<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	//
	// Issue filters page script
	//

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "IL";

	pageUserAuthorization( $SCR_ID, $IT_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$invalidField = null;
	define( "ANYUSER", "10" );

	$itStrings = $it_loc_str[$language];

	//
	// Form handling
	//

	$btnIndex = getButtonIndex( array(BTN_CANCEL, BTN_SAVE), $_POST );

	switch ( $btnIndex ) {
		case 0 : {
					redirectBrowser( $opener, array( "P_ID"=>$P_ID ) );
				}
	}

	//
	// Loading page data
	//

	switch ( true ) {
		case true : {
						if ( $fatalError )
							break;

						// Load status list
						//
						$statusList = it_getUserIssueStatusList( $currentUser, $kernelStrings );
						if ( PEAR::isError($statusList) ) {
							$errorStr = $statusList->getMessage();
							$fatalError = true;

							break;
						}

						// Load assignment list
						//
						$res = it_getUserProjectsAssignmentsList( $currentUser, $kernelStrings );
						if ( PEAR::isError($res) ) {
							$errorStr = $res->getMessage();
							$fatalError = true;

							break;
						}

						$assignmentList[ANYUSER] = sprintf( "&lt;%s&gt; ", $itStrings['mf_any_item']);
						$assignmentList[IT_FILTER_NOTASSIGED] = sprintf( "&lt;%s&gt; ", $itStrings['mf_notassigned_item']);
						$authorList = array( ANYUSER => sprintf( "&lt;%s&gt; ", $itStrings['mf_any_item']) );

						foreach( $res as $key=>$value ) {
							$assignmentList[$key] = $value;
							$authorList[$key] = $value;
						}

						$assignmentIDs = array_keys( $assignmentList );
						$assignmentValues = array_values( $assignmentList );

						$authorIDs = array_keys( $authorList );
						$authorValues = array_values( $authorList );

						if ( !isset($edited) || !$edited ) {
							if ( $action == ACTION_NEW ) {
								$filterData = array();
								$filterData["ISSF_WORKSTATE"] = IT_FILTER_ACTIVEWORKS;

								$shownStatuses = $statusList;
							}

							if ( $action == ACTION_EDIT ) {
								$filterData = it_loadIssueFilterData( $currentUser, $ISSF_ID, $itStrings );
								$stNames = $statusList;

								if ( strlen( $filterData["ISSF_HIDDENSTATES"] ) ) {
									$hiddenStatuses = explode( IT_FILTER_ISSUE_DELIMITER, $filterData["ISSF_HIDDENSTATES"] );

									if ( array($hiddenStatuses) ) {
										foreach( $stNames as $stId=>$stName )
											if ( !in_array( $stName, $hiddenStatuses ) )
												$shownStatuses[$stId] = $stName;
									} 
								} else
									$shownStatuses = $statusList;
							}
						}
		}
	}

	//
	// Form handling
	//

	switch ( $btnIndex ) {
		case 1 : {
					$statusNameList = array();
					if ( isset($hiddenStatuses) && is_array($hiddenStatuses) )
						foreach( $hiddenStatuses as $nameIndex=>$nameValue )
							$statusNameList[] = stripSlashes( $hiddenStatuses[$nameIndex] );

					$storeData = $filterData;
					$storeData["ISSF_HIDDENSTATES"] = $statusNameList;
					$storeData["U_ID"] = $currentUser;
					$storeData["ISSF_NAME"] = prepareStrToStore($filterData["ISSF_NAME"]);

					if ( $storeData["ISFF_U_ID_AUTHOR"] == ANYUSER )
						$storeData["ISFF_U_ID_AUTHOR"] = null;

					if ( $storeData["ISFF_U_ID_ASSIGNED"] == ANYUSER )
						$storeData["ISFF_U_ID_ASSIGNED"] = null;

					if ( $storeData["ISFF_U_ID_SENDER"] == ANYUSER )
						$storeData["ISFF_U_ID_SENDER"] = null;

					$ISSF_ID = it_addmodIssueFilter( $action, $storeData, $kernelStrings, $itStrings );
					if ( PEAR::isError( $ISSF_ID ) ) {
						$errorStr = $ISSF_ID->getMessage();

						if ( $ISSF_ID->getCode() == ERRCODE_INVALIDFIELD )
							$invalidField = $ISSF_ID->getUserInfo();

						break;
					}

					if ( $action == ACTION_NEW )
						setAppUserCommonValue( $IT_APP_ID, $currentUser, 'IT_CURRENTPAGE', 1, $kernelStrings, $readOnly );
	
					redirectBrowser( $opener, array( "P_ID"=>$P_ID ) );
				} 
	}

	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( PAGE_TITLE, ($action == ACTION_EDIT) ? $itStrings['mf_editfilter_title'] : $itStrings['mf_newfilter_title'] );
	$preproc->assign( FORM_LINK, PAGE_IT_ISSUEFILTERS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( ACTION, $action );
	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( "P_ID", $P_ID );
	$preproc->assign( "opener", $opener );

	if ( $action == ACTION_NEW )
		$preproc->assign( HELP_TOPIC, "newfilter.htm");
	else
		$preproc->assign( HELP_TOPIC, "modifyfilter.htm");

	if ( !$fatalError ) {
		$statusList = prepareArrayToDisplay($statusList);

		$preproc->assign( "statusList", $statusList );
		$preproc->assign( "assignmentIDs", $assignmentIDs );
		$preproc->assign( "assignmentValues", $assignmentValues );

		$preproc->assign( "authorIDs", $authorIDs );
		$preproc->assign( "authorValues", $authorValues );

		if ( isset($statusNames) )
			$preproc->assign( "statusNames", prepareArrayToDisplay($statusNames) );

		if ( isset($filterData) )
			$filterData = prepareArrayToDisplay( $filterData );

		if ( isset($shownStatuses) ) 
			if ( !isset($edited) || !$edited ) 
				$preproc->assign( "shownStatuses", prepareArrayToDisplay( $shownStatuses ) );
			else {
				reset( $shownStatuses );
				while ( list( $key, $val ) = each ( $shownStatuses ) )
					$shownStatuses[$key] = stripSlashes($val);

				$preproc->assign( "shownStatuses", prepareArrayToDisplay( $shownStatuses ) );
			}

		if ( isset($hiddenStatuses) )
			if ( !isset($edited) )
				$preproc->assign( "hiddenStatuses", prepareArrayToDisplay( $hiddenStatuses ) );
			else { 
				reset( $hiddenStatuses );
				while ( list( $key, $val ) = each ( $hiddenStatuses ) )
					$hiddenStatuses[$key] = stripSlashes($val);

				$preproc->assign( "hiddenStatuses", prepareArrayToDisplay( $hiddenStatuses ) );
			}

		if ( isset($filterData) )
			$preproc->assign( "filterData", $filterData );
	}

	$preproc->display( "issuefilters.htm" );
?>