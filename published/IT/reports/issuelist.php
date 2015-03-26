<?php

	require_once( "../../common/reports/reportsinit.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	//
	// Issue list print report
	//

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;	
	$SCR_ID = "IL";
	$issueList = null;

	reportUserAuthorization( $SCR_ID, $IT_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];

	switch ( true ) {
		case true : {
			if ( $fatalError )
				break;

			$selectedIssues = null;

			if ( $printMode == 0 )
				if ( isset($issues) )
					$selectedIssues = unserialize( base64_decode($issues) );
				else
					$selectedIssues = array();

			// Load projects
			//
			$projectList = it_getUserAssignedProjects( $currentUser, $itStrings, IT_DEFAILT_MAX_PROJNAME_LEN, true, IT_DEFAILT_MAX_CUSTNAME_LEN );
			if ( PEAR::isError($projectList) ) {
				$errorStr = $itStrings[IT_ERR_LOADPROJECTS];

				$fatalError = true;
				break;
			}

			/*
			$P_IDs = array_keys($projectList);
			if ( isset($P_ID) && strlen($P_ID) ) {
				if ( !in_array( $P_ID, $P_IDs ) ) {
					$errorStr = $itStrings['il_norights_message'];
					$fatalError = true;

					break;
				}
			} else 
				$P_ID = null;
			*/
			if (!isset($P_ID)) $P_ID = null;

			$projmanData = it_getProjManData( $P_ID );
			if ( PEAR::isError($projmanData) ) {
				$errorStr = $itStrings[IT_ERR_LOADPROJMANDATA];

				$fatalError = true;
				break;
			}

			$projMan = isset( $projmanData["U_ID_MANAGER"] ) ? $projmanData["U_ID_MANAGER"] : null;
			$userIsProjman = is_array($projmanData) && $projMan == $currentUser;

			if ( !is_null($projMan) )
				$managerName = getArrUserName( $projmanData, true );
			else
				$managerName = null;

			$currentISSF_ID = @it_getCurrentIssueFilter( $currentUser, $kernelStrings );

			$filterData = @it_loadIssueFilterData( $currentUser, $currentISSF_ID, $itStrings );
			$filterType = it_getIssueFilterType( $filterData );

			// Load issue list view data
			//
			$viewdata = it_loadIssueListViewData( $currentUser, $kernelStrings );
			if ( PEAR::isError($viewdata) ) {
				$errorStr = $itStrings['il_errloadingviewopt_message'];

				$fatalError = true;
				break;
			}

			$addTransitionsData = $viewdata[IT_LV_DISPLAYISSUEHISTORY];

			// Load expanded works list
			//
			$collapsedWorks = it_getCollapsedWorkList( $currentUser, $P_ID, $kernelStrings );

			$fullWorkList = it_listActiveProjectUserWorks( $P_ID, $currentUser, $filterData );
			if ( PEAR::isError($fullWorkList) ) {
				$errorStr = $itStrings[IT_ERR_LOADWORKS];

				$fatalError = true;
				break;
			}

			if ( array_key_exists( $P_ID, $fullWorkList ) )
				$workList = $fullWorkList[$P_ID];
			else
				$workList = null;

			if ( $curPW_ID != 'GBT' )
				if ( count($workList) )
					$workList = array( $curPW_ID=>$workList[$curPW_ID] );
				else
					$workList = array();

			if ( is_array($workList) )
				foreach( $workList as $_PW_ID => $PW_DATA ) { 
					
					$workRecord = array( "PW_ID"=>$_PW_ID, "PW_DESC"=>$PW_DATA["PW_DESC"], "ROW_TYPE"=>1 );

					// Work expanding support
					//
					$isWorkCollapsed = in_array( $_PW_ID, $collapsedWorks );
					$workExpanded = ( $isWorkCollapsed ) ? IT_WORK_COLLAPSED : IT_WORK_EXPANDED;

					if ( is_null($selectedIssues) || it_workIssueCount( $P_ID, $_PW_ID, $filterData, $selectedIssues ) )
						$issueList[] = $workRecord;

					if ( $workExpanded == IT_WORK_EXPANDED ) {

						if ( !is_null( $res = it_addIssueListWorkRecords($P_ID, $_PW_ID, $issueList, $recordsAdded, $filterData, 
																			$kernelStrings, $itStrings, true, null, null, null, 
																			$selectedIssues, $addTransitionsData) ) ) {
							$errorStr = $res;

							break;
						} 

						if ( !is_null($currentISSF_ID) && !$recordsAdded && ($filterType == IT_FT_MIXED) )
							array_pop( $issueList );
					} else 
						if ( !is_null($currentISSF_ID) && ($filterType == IT_FT_MIXED) ) {
							$issueCount = it_workIssueCount( $P_ID, $_PW_ID, $filterData, $selectedIssues );
							if ( !$issueCount )
								array_pop( $issueList );
						}
				}

			if ( is_null($currentISSF_ID) )
				$filterInfo = $itStrings['il_nofilter_text'];
			else
				$filterInfo = prepareStrToDisplay( $filterData["ISSF_NAME"], true );

			$projName = it_getProjectName( $P_ID, null, true, IT_DEFAILT_MAX_CUSTNAME_LEN );
			if ( PEAR::isError($projName) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
				
			if (!$issueList && $P_ID == 'GBP') {
				$res = it_addIssueListWorkRecords($P_ID, $_PW_ID, $issueList, $recordsAdded, $filterData, 
																			$kernelStrings, $itStrings, true, null, null, null, 
																			$selectedIssues, $addTransitionsData);
			}

		}
	}

	$preprocessor = new print_preprocessor( $IT_APP_ID, $kernelStrings, $language );

	$preprocessor->assign( REPORT_TITLE, $itStrings['il_screen_long_name'] );
	$preprocessor->assign( ERROR_STR, $errorStr );
	$preprocessor->assign( FATAL_ERROR, $fatalError );

	if ( !$fatalError ) {
		$preprocessor->assign( "issueList", $issueList );
		$preprocessor->assign( "itStrings", $itStrings );
		$preprocessor->assign( "filterInfo", $filterInfo );
		$preprocessor->assign( "ISSF_ID", $currentISSF_ID );
		$preprocessor->assign( "managerName", $managerName );
		$preprocessor->assign( "projectName", prepareStrToDisplay($projName, true) );
		$preprocessor->assign( "P_ID", $P_ID );
		$preprocessor->assign( "addTransitionsData", $addTransitionsData );
	}

	$preprocessor->display( "issuelist.htm" );
?>