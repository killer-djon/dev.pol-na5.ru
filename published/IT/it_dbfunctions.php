<?php

	//
	//
	// Issue Tracking application DBSM-dependent functions
	//
	// Realization for mySQL
	//

	function it_prepareIssueFilterSQL( $filterData )
	//
	// Prepares fragment of SQL query for issue list filtration
	//
	//		Parameters:
	//			$filterData - filter settings
	//
	//		Returns string.
	//
	{
		global $qr_it_filterStatesChunk;
		global $qr_it_filterAssignedChunk;
		global $qr_it_filterSenderChunk;
		global $qr_it_filterDescChunk;
		global $qr_it_filterDateChunk;
		global $qr_it_filterNotAssignedChunk;
		global $qr_it_filterStatusDateChunk;
		global $qr_it_filterAuthorChunk;
		global $qr_it_filterOpenIssuesChunk;
		global $qr_it_filterLastDaysChunk;
		global $qr_it_filterCompleteIssuesChunk;
		global $qr_it_openedWorksChunk;

		if ( !is_array($filterData) )
			return "";

		$filterChunks[] = $qr_it_openedWorksChunk;

		$filterChunks = array();
		if ( strlen($filterData["ISSF_HIDDENSTATES"]) ) { 
			$statesList = explode( "!^!", addSlashes($filterData["ISSF_HIDDENSTATES"]) );

			$implodeStr = sprintf( "%s,%s", SQL_STRING_DELITIMTER, SQL_STRING_DELITIMTER );
			$statesFilter = implode( $implodeStr, $statesList );
			$filterChunks[] = sprintf( $qr_it_filterStatesChunk, $statesFilter );
		}

		if ( strlen($filterData["ISFF_U_ID_ASSIGNED"]) )
			if ( $filterData["ISFF_U_ID_ASSIGNED"] != IT_FILTER_NOTASSIGED )
				$filterChunks[] = sprintf( $qr_it_filterAssignedChunk, $filterData["ISFF_U_ID_ASSIGNED"] );
			else
				$filterChunks[] = sprintf( $qr_it_filterNotAssignedChunk );

		if ( strlen($filterData["ISFF_U_ID_SENDER"]) )
			$filterChunks[] = sprintf( $qr_it_filterSenderChunk, $filterData["ISFF_U_ID_SENDER"] );
			
		if ( strlen($filterData["ISFF_U_ID_AUTHOR"]) && $filterData["ISFF_U_ID_AUTHOR"] != IT_FILTER_NOTASSIGED )
			$filterChunks[] = sprintf( $qr_it_filterAuthorChunk, $filterData["ISFF_U_ID_AUTHOR"] );
		
		if (!empty($filterData["I_ID"]) && is_array($filterData["I_ID"])) {
			$ids = addslashes(join(",", $filterData["I_ID"]));
			$filterChunks[] = "I_ID IN ($ids)";
		} elseif (strlen($filterData["I_ID"]))
			$filterChunks[] = "I_ID=" . addslashes($filterData["I_ID"]);
			
		if ( isset($filterData["ISSF_ISSUE_COMPLETE"]) && $filterData["ISSF_ISSUE_COMPLETE"] == IT_FILTER_OPENISSUES )
			$filterChunks[] = sprintf( $qr_it_filterOpenIssuesChunk );
		elseif (isset($filterData["ISSF_ISSUE_COMPLETE"]) && $filterData["ISSF_ISSUE_COMPLETE"] == IT_FILTER_COMPLETEISSUES )
			$filterChunks[] = sprintf( $qr_it_filterCompleteIssuesChunk );

		if ( strlen($filterData["ISSF_SEARCHSTRING"]) )
			$filterChunks[] = sprintf( $qr_it_filterDescChunk, addslashes(addslashes(strtoupper($filterData["ISSF_SEARCHSTRING"]))) );

		if ( $filterData["ISSF_WORKSTATE_CREATEDAY_OPT"] == 2 ) 
			if ( strlen($filterData["ISSF_DAYSAGO"]) )
				$filterChunks[] = sprintf( $qr_it_filterDateChunk, $filterData["ISSF_DAYSAGO"] );

		if ( $filterData["ISSF_WORKSTATE_CREATEDAY_OPT"] == 1 )
			if ( strlen($filterData["ISSF_LASTDAYS"]) )
				$filterChunks[] = sprintf( $qr_it_filterLastDaysChunk, $filterData["ISSF_LASTDAYS"] );

		if ( strlen($filterData["ISSF_PENDING"]) )
			$filterChunks[] = sprintf( $qr_it_filterStatusDateChunk, $filterData["ISSF_PENDING"] );

		if ( count($filterChunks) )
			return sprintf( " AND (%s) ", implode( " AND ", $filterChunks ) );
		else
			return "";
	}

	function it_getISMIssueReportIssuesQuery( $P_ID, $PW_ID, $V_ID, $param1, $param2, $itStrings )
	//
	// Returns result of issue list select query for issue list printed report of Issue Statistics Matrix
	//
	//		���������:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$V_ID - type of report. One of the following values: IT_ISM_VIEW_PROJECT_STATUS..IT_ISM_VIEW_ASSIGNED_PRIORITY
	//			$param1, $param2 - parameters, received from report Issue Statistics Matrix
	//			$itStrings - an array containing strings stored within it_localization.php in specific language
	//
	//	Returns query's identifier (object DB_Result), or PEAR_Error.
	//
	{
		global $qr_it_search_issues;
		global $qr_it_ism_issuerep_status_chunk;
		global $qr_it_ism_issuerep_assigned_chunk;
		global $qr_it_ism_issuerep_priority_chunk;
		global $qr_it_ism_issuerep_assigned_status_chunk;
		global $qr_it_ism_issuerep_assigned_priority_chunk;

		switch ($V_ID) {
			case IT_ISM_VIEW_PROJECT_STATUS :
			case IT_ISM_VIEW_WORK_STATUS : {
				$query = sprintf( $qr_it_search_issues, $qr_it_ism_issuerep_status_chunk );

				return execPreparedQuery( $query, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, 'I_STATUSCURRENT'=>addSlashes($param2) ) );
			}
			case IT_ISM_VIEW_PROJECT_ASSIGNED :
			case IT_ISM_VIEW_WORK_ASSIGNED : {
				$query = sprintf( $qr_it_search_issues, $qr_it_ism_issuerep_assigned_chunk );

				if (!strlen($param2))
					$query = sprintf( $query, "I.U_ID_ASSIGNED IS NULL" );
				else
					$query = sprintf( $query, sprintf( "I.U_ID_ASSIGNED = '%s'", $param2 ) );

				return execPreparedQuery( $query, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID ) );
			}
			case IT_ISM_VIEW_PROJECT_PRIORITY :
			case IT_ISM_VIEW_WORK_PRIORITY : {
				$query = sprintf( $qr_it_search_issues, $qr_it_ism_issuerep_priority_chunk );

				return execPreparedQuery( $query, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, 'I_PRIORITY'=>addSlashes($param2) ) );
			}
			case IT_ISM_VIEW_ASSIGNED_STATUS : {
				$query = sprintf( $qr_it_search_issues, $qr_it_ism_issuerep_assigned_status_chunk );

				if (!strlen($param1))
					$query = sprintf( $query, "I.U_ID_ASSIGNED IS NULL" );
				else
					$query = sprintf( $query, sprintf( "I.U_ID_ASSIGNED = '%s'", $param1 ) );

				return execPreparedQuery( $query, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, 'I_STATUSCURRENT'=>addSlashes($param2) ) );
			}
			case IT_ISM_VIEW_ASSIGNED_PRIORITY : {
				$query = sprintf( $qr_it_search_issues, $qr_it_ism_issuerep_assigned_priority_chunk );

				if (!strlen($param1))
					$query = sprintf( $query, "I.U_ID_ASSIGNED IS NULL" );
				else
					$query = sprintf( $query, sprintf( "I.U_ID_ASSIGNED = '%s'", $param1 ) );

				return execPreparedQuery( $query, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, 'I_PRIORITY'=>$param2 ) );
			}
		}

		return PEAR::raiseError( $itStrings[IT_ERR_ISMPARAMS] );
	}


	function it_getIDRData( $U_ID, $locStrings, $itStrings, $sortField, $sortOrder, &$P_ID, &$PW_ID, &$reportStatusList )
	//
	// Returns result of data selection for report Issue Dynamic Report
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$locStrings - an array containing strings stored within localization.php in specific language
	//			$itStrings - an array containing strings stored within it_localization.php in specific language
	//			$sortField - sorting field name
	//			$sortOrder - sorting direction - inc, desc
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$reportStatusList - list of states, chosen within report settings
	//
	//		Returns query result, or PEAR_Error.
	//
	{
		global $qr_it_idr_selectdata;
		global $qr_it_idr_project_chunk;
		global $qr_it_idr_work_chunk;
		global $qr_it_idr_status_chunk;

		$res = it_loadIDRSettings( $U_ID, $P_ID, $PW_ID, $statusList, $locStrings, $itStrings );
		if ( PEAR::isError($res) )
			return $itStrings['rem_errloadingsettings_message'];

		$reportStatusList = $statusList;

		// Apply filter
		//
		$filters = array();
		if ( $P_ID != IT_IDR_ALL_PROJECTS )
			$filters[] = sprintf($qr_it_idr_project_chunk, $P_ID);

		if ( $PW_ID != IT_IDR_ALL_WORKS )
			$filters[] = sprintf($qr_it_idr_work_chunk, $PW_ID);

		if ( is_array($statusList) || count($statusList) ) {
			for ( $i = 0; $i < count($statusList); $i++ )
				$statusList[$i] = addSlashes($statusList[$i]);

			$statusList = implode( "','", $statusList );
			$filterStr = sprintf( "'%s'", $statusList );

			$filters[] = sprintf($qr_it_idr_status_chunk, $filterStr);
		}

		$filterStr = sprintf( " AND %s", implode( " AND ", $filters ) );

		// Apply sorting
		//
		if ( $sortField == "open" )
			$sortClause = sprintf( "(now()-I.I_STARTDATE) %s", $sortOrder );
		elseif ( $sortField == "notmoving" )
			$sortClause = sprintf( "(now()-I.I_STATUSCURRENTDATE) %s", $sortOrder );
		elseif ( $sortField == "assigned" )
			$sortClause = sprintf( "CON.C_LASTNAME %s, CON.C_FIRSTNAME %s, CON.C_MIDDLENAME %s", $sortOrder, $sortOrder, $sortOrder );
		elseif ( $sortField == "displayNum" )			
			$sortClause = sprintf( "I.PW_ID %s, I.I_NUM %s", $sortOrder, $sortOrder );
		else
			$sortClause = sprintf( "%s %s", $sortField, $sortOrder );

		$sql = sprintf( $qr_it_idr_selectdata, $filterStr, $sortClause );

		$qr =  execPreparedQuery( $sql, array() );
		if (PEAR::isError($qr))
			return PEAR::raiseError( $locStrings[ERR_QUERYEXECUTING] );

		return $qr;
	}

	function it_prepareUserRemindNotification( $U_ID, $comment, $headersOnly, $issueList, $locStrings, $itStrings )
	//
	// Prepares text for reminding certain user about his/her issues
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$comment - comment to letter
	//			$headersOnly - drop issue descriptions
	//			$issueList - an array containing identifier of issues
	//			$locStrings - an array containing strings stored within localization.php in specific language
	//			$itStrings - an array containing strings stored within it_localization.php in specific language
	//		
	//		Returns notification text in HTML, or PEAR_Error.
	//
	{
		global $qr_it_idr_selectdata;
		global $qr_it_idr_user_reminder_chunk;
		global $it_issue_priority_names;

		$issueFilterStr = implode( ",", $issueList );
		$filter = sprintf( $qr_it_idr_user_reminder_chunk, $issueFilterStr, $U_ID );

		$sorting =  "C.C_NAME, P.P_DESC, I.I_PRIORITY desc, I.I_STARTDATE asc, I.I_NUM";
		$sql = sprintf( $qr_it_idr_selectdata, sprintf( " AND %s", $filter ), $sorting );
		$qr =  db_query( $sql, array() );
		if (PEAR::isError($qr))
			return PEAR::raiseError( $locStrings[ERR_QUERYEXECUTING] );
		
		$currentTime = time();
		$daySeconds = 86400;

		$mailFormat = "html";

		$viewdata = it_loadIssueListViewData( $U_ID, $locStrings );

		if ( $mailFormat == MAILFORMAT_HTML )
			$comment = prepareStrToDisplay( $comment, true );

		$comment = trim($comment);

		$pendingList = array();
		while ( $row = db_fetch_array( $qr ) ) {
			$curStamp = sqlTimestamp($row["I_STATUSCURRENTDATE"]);
			$days = floor(($currentTime - $curStamp)/$daySeconds);
			$row['days'] = $days;

			$projName = sprintf( "%s. %s", strTruncate( $row["C_NAME"], IT_DEFAILT_MAX_CUSTNAME_LEN ),
											strTruncate( $row["P_DESC"], IT_DEFAILT_MAX_PROJNAME_LEN ) );
			$workDesc = sprintf( "%s %s - %s", $itStrings['rem_task_label'], $row['PW_ID'], strTruncate( $row["PW_DESC"], 30 ) );

			if ( $viewdata[IT_LV_RESTRICTDESCRIPTION] )
				$row["I_DESC"] = strTruncate( $row["I_DESC"] , $viewdata[IT_LV_DESCLENGTH] );

			if ( $mailFormat == MAILFORMAT_HTML )
				$row["I_DESC"] = prepareStrToDisplay( $row["I_DESC"], true );

			$pendingList[$projName][$workDesc][] = $row;
		}

		db_free_result( $qr );

		$resultArr = array();
		foreach ( $pendingList as $projDesc => $taskList ) {

			$str = sprintf( "<b>%s</b><br>", $projDesc );

			$taskParts = array();
			foreach ( $taskList as $taskDesc => $issueList ) {
				$taskStr = $taskDesc."<br><br>";

				$issueParts = array();
				foreach( $issueList as $issueData ) {

					$priorityColors = array( IT_ISSUE_PRIORIY_LOW => "#0000FF", IT_ISSUE_PRIORIY_NORMAL => "#000000", IT_ISSUE_PRIORIY_HIGH => "#FF0000" );
					$priorityName = $itStrings[$it_issue_priority_names[$issueData["I_PRIORITY"]]];
					$priorityColor = $priorityColors[$issueData["I_PRIORITY"]];

					$sqlDate = sqlTimestamp( $issueData["I_STATUSCURRENTDATE"] );

					$issueColor = it_getIssueHTMLStyle( $issueData["ITS_COLOR"] );

					$issueHeaderFormat = "%s: %s.%s <font color='%s'><b>%s</b></font> [%s%s</b></font>] - %s &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; %s %s";
					$issueHeader = sprintf( $issueHeaderFormat,
											$itStrings['rem_issue_label'],
											$issueData['PW_ID'], $issueData['I_NUM'],
											$priorityColor, $priorityName, 
											$issueColor, $issueData['I_STATUSCURRENT'],
											textualDate( $sqlDate, DATEFORMAT_DMY, $locStrings ),
											$issueData['days'],
											$itStrings['rem_dayswaiting_text'] );

					$issueText = $issueHeader;

					if ( !$headersOnly ) {
						if ( $mailFormat == MAILFORMAT_HTML )
							$issue = "<table collspacing=0 collpadding=0 border=0><tr>
										<td style='font-size: 8pt;'>&nbsp;&nbsp;&nbsp;</td>
										<td style='font-size: 8pt;'>%s</td></tr></table>";
						else
							$issue = "   %s";

						$issue = sprintf( $issue, $issueData["I_DESC"] );
						$issueText .= "<br>".$issue;
					}

					$issueParts[] = $issueText;
				}

				if ( $headersOnly )
					$issueParts = implode( "<br>", $issueParts );
				else
					$issueParts = implode( "<br>", $issueParts );

				$taskStr .= $issueParts;

				$taskParts[] = $taskStr;
			}

			$taskParts = implode( "<br><br>", $taskParts );

			$resultArr[] = $str.$taskParts;
		}

		$result = implode( "<br><br>", $resultArr );

		if ( strlen($comment) )
			$result = $comment."<br><br>".$result;

		return $result;
	}

?>