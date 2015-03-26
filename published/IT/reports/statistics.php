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

			$param1 = base64_decode( $param1 );
			$param2 = base64_decode( $param2 );

			$UNASSIGNED  = "&lt;<b>?</b>&gt;";

			$p_qr = it_getISMIssueReportProjectsQuery( $V_ID, $param1, $param2, $P_ID, $itStrings );
			if ( PEAR::isError($p_qr) ) {
				$errorStr = $p_qr->getMessage();

				$fatalError = true;
				break;
			}

			$worksSQL = it_getISMIssueReportWorksQuery( $V_ID, $param1, $param2, $P_ID, $itStrings );
			if ( PEAR::isError($worksSQL) ) {
				$errorStr = $worksSQL->getMessage();

				$fatalError = true;
				break;
			}

			$issueList = array();

			// Adding projects
			//
			while ( $row = db_fetch_array( $p_qr ) ) {
				$w_qr = db_query( $worksSQL, array("P_ID"=>$row["P_ID"]) );
				$currentRecord = array();
				$currentRecord["ROW_TYPE"] = 2;
				$currentRecord["displayData"] = strTruncate( prepareStrToDisplay($row["C_NAME"], true), 30).". ".prepareStrToDisplay(strTruncate($row["P_DESC"], true), 30);

				$issueList[] = $currentRecord;

				// Adding works
				//
				$worksAdded = 0;
				while ( $w_row = db_fetch_array( $w_qr ) ) {
					$currentRecord = prepareArrayToDisplay($w_row);
					$currentRecord["ROW_TYPE"] = 1;
					$worksAdded++;
					$currentRecord["PW_DESC"] = prepareStrToDisplay($w_row["PW_DESC"], true);
					$currentRecord["index"] = $worksAdded;

					$issueList[] = $currentRecord;

					$i_qr = it_getISMIssueReportIssuesQuery( $row["P_ID"], $w_row["PW_ID"], $V_ID, $param1, $param2, $itStrings );

					// Adding issues
					//
					$issuesAdded = 0;
					while ( $i_row = db_fetch_array( $i_qr ) ) {
						$currentRecord = prepareArrayToDisplay($i_row, true);
						$currentRecord["ROW_TYPE"] = 0;
						$issuesAdded++;
						$currentRecord["DISPLAY_NUM"] = sprintf( "%s.%s", $w_row["PW_ID"], $i_row["I_NUM"] );
						$currentRecord["DESC_LEN"] = strlen( $i_row["I_DESC"] );
						$currentRecord["I_STARTDATE"] = convertToDisplayDate( $currentRecord["I_STARTDATE"], true );
						if ( $i_row["I_PRIORITY"] != IT_ISSUE_PRIORIY_NORMAL )
							$currentRecord["PRIORITY_NAME"] = $itStrings[$it_issue_priority_short_names[$i_row["I_PRIORITY"]]];

						// Assignments
						//
						if ( strlen( $currentRecord["U_ID_ASSIGNED"] ) ) {
							$assignedNameParts = array( "C_LASTNAME"=>$i_row["A_LASTNAME"], "C_MIDDLENAME"=>$i_row["A_MIDDLENAME"], "C_FIRSTNAME"=>$i_row["A_FIRSTNAME"], "C_EMAILADDRESS"=>$i_row["A_EMAILADDRESS"] );
							$currentRecord["U_ID_ASSIGNED"] = getArrUserName($assignedNameParts, true);
						} else
							$currentRecord["U_ID_ASSIGNED"] = $UNASSIGNED;

						if ( strlen( $currentRecord["U_ID_SENDER"] ) ) {
							$assignedNameParts = array( "C_LASTNAME"=>$i_row["S_LASTNAME"], "C_MIDDLENAME"=>$i_row["S_MIDDLENAME"], "C_FIRSTNAME"=>$i_row["S_FIRSTNAME"], "C_EMAILADDRESS"=>$i_row["S_EMAILADDRESS"] );
							$currentRecord["U_ID_SENDER"] = getArrUserName($assignedNameParts, true);
						} else
							$currentRecord["U_ID_SENDER"] = $UNASSIGNED;

						if ( strlen( $currentRecord["U_ID_AUTHOR"] ) ) {
							$assignedNameParts = array( "C_LASTNAME"=>$i_row["AU_LASTNAME"], "C_MIDDLENAME"=>$i_row["AU_MIDDLENAME"], "C_FIRSTNAME"=>$i_row["AU_FIRSTNAME"], "C_EMAILADDRESS"=>$i_row["AU_EMAILADDRESS"] );
							$currentRecord["U_ID_AUTHOR"] = getArrUserName($assignedNameParts, true);
						} else
							$currentRecord["U_ID_AUTHOR"] = $UNASSIGNED;

						$issueList[] = $currentRecord;
					}
					db_free_result( $i_qr );

					if ( !$issuesAdded ) {
						array_pop( $issueList );
						$worksAdded--;
					}
				}

				db_free_result( $w_qr );

				if ( !$worksAdded )
					array_pop( $issueList );
			}

			db_free_result( $p_qr );

			$colTitles = $IT_ISM_COLUMN_TITLES[$V_ID];

			$showProject = !in_array( $V_ID, array( IT_ISM_VIEW_PROJECT_STATUS, IT_ISM_VIEW_PROJECT_ASSIGNED, IT_ISM_VIEW_PROJECT_PRIORITY ) );
			if ( $showProject )
				$projName = ($P_ID == IT_ISM_ALL_PROJECTS) ? sprintf("&lt;%s&gt", $itStrings['iss_allprojects_item']) : it_getProjectName( $P_ID, null, true, 30 );
		}
	}
	
	
	$preprocessor = new print_preprocessor( $IT_APP_ID, $kernelStrings, $language );

	$preprocessor->assign( REPORT_TITLE, $itStrings['iss_screen_long_name'] );
	$preprocessor->assign( ERROR_STR, $errorStr );
	$preprocessor->assign( FATAL_ERROR, $fatalError );
	$preprocessor->assign( "itStrings", $itStrings );

	if ( !$fatalError ) {
		$preprocessor->assign( "issueList", $issueList );
		if ( isset($managerName) )
			$preprocessor->assign( "managerName", $managerName );
		$preprocessor->assign( "rowName", $itStrings[$colTitles[0]] );
		$preprocessor->assign( "colName", $itStrings[$colTitles[1]] );
		$preprocessor->assign( "col_title", base64_decode($col_title) );
		$preprocessor->assign( "row_title", base64_decode($row_title) );

		if ( $showProject )
			$preprocessor->assign( "projName", prepareStrToDisplay($projName, true) );
	}

	$preprocessor->display( "statistics.htm" );
?>