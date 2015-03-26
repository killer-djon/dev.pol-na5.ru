<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "IL";
	$projectExists = true;

	pageUserAuthorization( $SCR_ID, $IT_APP_ID, false );

	//
	// Form handling
	//

	$btnIndex = getButtonIndex( array(BTN_RETURN), $_POST );

	switch ( $btnIndex ) {
		case 0 : redirectBrowser( PAGE_IT_ISSUELIST, array( "P_ID"=>$_SESSION['IT_LIST_PID'] ) );
	}

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];

	switch ( true ) {
		case true : 
					// Save project identifier to the session variable
					//
					if ( isset($SAVEPID) )
						$_SESSION['IT_LIST_PID'] = $P_ID;

					// Load project list
					//
					$closedProjects = false;
					$attachClosedTag = false;
					$allProjects = false;
					$addManagerName = false;
					$openFirst = false;
					$managerOnly = true;
					$projects = it_getUserAssignedProjects( $currentUser, $itStrings, 35, 
															true, IT_DEFAILT_MAX_CUSTNAME_LEN, $closedProjects, $attachClosedTag,
															$allProjects, $addManagerName, $openFirst, $managerOnly );
					if ( PEAR::isError($projects) ) {
						$errorStr = $projects->getMessage();

						$fatalError = true;
						break;
					}

					$projects = refinedMerge( array(null=>$itStrings['wfm_selectproject_item']), $projects );

					$projectIDs = array_keys($projects);
					$projectNames = array_values($projects);

					if ( !array_key_exists($P_ID, $projects) || !strlen($P_ID) ) {
						$projectExists = false;

						break;
					}

					// Load project name
					//
					$projName = it_getProjectName( $P_ID, null, true, IT_DEFAILT_MAX_CUSTNAME_LEN );
					if ( PEAR::isError($projName) ) {
						$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

						$fatalError = true;
						break;
					}

					// Load work list
					//
					$fullWorkList = it_listProjectWorks( $P_ID, $kernelStrings );
					if ( PEAR::isError($fullWorkList) ) {
						$errorStr = $fullWorkList->getMessage();

						$fatalError = true;
						break;
					}

					$workList = array();
					foreach( $fullWorkList as $key=>$work ) {
						if ( $work['COMPLETE'] )
							continue;

						$params['P_ID'] = $P_ID;
						$params['PW_ID'] = $work['PW_ID'];
						$work['URL'] = prepareURLStr( PAGE_IT_ISSUETRANSITIONSCHEMA, $params );

						$transitions = it_listWorkTransitions( $P_ID, $work['PW_ID'], $kernelStrings );
						$transList = array();
						foreach( $transitions as $trans_key=>$data ) {
							$trans_key = sprintf( "%s%s</b></font>", it_getIssueHTMLStyle( $data['ITS_COLOR'] ), prepareStrToDisplay($trans_key, true) );
							$transList[$trans_key] = $data;
						}
						
						$transitions = implode( ' - ', array_keys( $transList ) );
						if ( !strlen($transitions) )
							$transitions = prepareStrToDisplay($itStrings['wfm_notdefined_text'], true);

						$work['transitions'] = $transitions;

						$workList[$key] = $work;
					}

					$fullWorkList = $workList;

	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, $itStrings['wfm_page_title'] );
	$preproc->assign( FORM_LINK, prepareURLStr( PAGE_IT_WORKFLOWMANAGER, array() ) );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	if ( !$fatalError ) {
		$preproc->assign( "P_ID", $P_ID );
		$preproc->assign( "projectIDs", $projectIDs );
		$preproc->assign( "projectNames", $projectNames );
		$preproc->assign( "projectExists", $projectExists );

		if ( $projectExists ) {
			$preproc->assign( "projName", $projName );
			$preproc->assign( "fullWorkList", $fullWorkList );

		}
	}

	$preproc->display( "workflowmanager.htm" );

?>