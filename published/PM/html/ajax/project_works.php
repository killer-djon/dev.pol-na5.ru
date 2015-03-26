<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );
	
	require_once( WBS_DIR."/published/PM/pm.php" );
	
	$fatalError = false;
	$error = null;
	$errorStr = null;
	$SCR_ID = "WL";
	pageUserAuthorization( $SCR_ID, $PM_APP_ID, false );
	
	$nodes = array ();
	
	$access = null;
	$hierarchy = null;
	$deletable = null;
	$statisticsMode = false;
	
	if (!$start)
		$start = 0;
	if (!$limit)
		$limit = PM_WORKS_ON_PAGE;
	
	$fieldsMap = array ("id" => "PW_ID", "desc" => "PW_DESC", "start" => "PW_STARTDATE", "due" => "PW_DUEDATE", "end" => "PW_ENDDATE", "estimate" => "PW_COSTESTIMATE", "assigned" => "WA_COUNT");
	if (!$sort || empty($fieldsMap[$sort]))
		$sort = "id";
	if (!$dir || !in_array ($dir, array ("ASC", "DESC")))
		$dir = "ASC";
	
	$project_works = array();
	
	writeUserCommonSetting( $currentUser, 'showCompleteTasks', $showComplete, $kernelStrings );

	$projectData = array ("P_ID" => $projectId);
	do {
		$sortField = $fieldsMap[$sort];
		$sortBy = $sortField . " " . $dir;
				
		//if (in_array ($sortField, array ("PW_STARTDATE", "PW_ENDDATE", "PW_DUEDATE", "PW_COSTESTIMATE")))
			//$sortBy = "($sortField IS NULL), " . $sortBy;
		
		// Build main query
		$sql = new CSelectSqlQuery ("PROJECTWORK", "PW");
		$sql->addConditions("PW.P_ID", $projectId);
		if ($workId)
			$sql->addConditions("PW.PW_ID", $workId);
		
		// Add conditions and setup query for get project works data
		if (!$workId && !$showComplete)
			$sql->addConditions ("PW_ENDDATE IS NULL OR PW_ENDDATE=0");
		
		
		// Get total project works info
		$sql->setSelectFields ("COUNT(*) AS TOTAL_COUNT, MIN(PW_STARTDATE) AS MINSTART, MIN(PW_ENDDATE) AS MINEND, MIN(PW_DUEDATE) AS MINDUE, MAX(PW_STARTDATE) AS MAXSTART, MAX(PW_ENDDATE) AS MAXEND, MAX(PW_DUEDATE) AS MAXDUE");
		$projectInfo = db_query_result($sql->getQuery (), DB_ARRAY);
		$totals = $projectInfo["TOTAL_COUNT"];
		$dateFields = array ("START", "DUE", "END");
		$minDate = null;
		$maxDate = null;
		for ($i = 0; $i < count($dateFields); $i++) {
			$field = $dateFields[$i];
			if ($projectInfo["MIN".$field] && ($projectInfo["MIN".$field] < $minDate || !$minDate))
				$minDate = $projectInfo["MIN" . $field];
			if ($projectInfo["MAX".$field] && $projectInfo["MAX".$field] > $maxDate)
				$maxDate = $projectInfo["MAX" . $field];
		}
		
		if ($sort == "assigned")
			$sql->setSelectFields ("PW.*, COUNT(WA.PW_ID) AS WA_COUNT");
		else
			$sql->setSelectFields("PW.*");
		if (!$noPaging)
			$sql->setLimit ($start, $limit);
		if ($sort == "assigned")
			$sql->leftJoin ("WORKASSIGNMENT", "WA", "PW.P_ID = WA.P_ID AND PW.PW_ID=WA.PW_ID");
	
		$sql->setOrderBy ($sortBy);
		$sql->setGroupBy ("PW.PW_ID");
		$qr = db_query($sql->getQuery (),array() );
		
		if ( PEAR::isError($qr) ) {
			$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

			$fatalError = true;
			break;
		}
		
		$asgmtSql = new CSelectSqlQuery ("WORKASSIGNMENT");
		$asgmtSql->setSelectFields ("PW_ID, U_ID");
		$asgmtSql->addConditions ("P_ID", $projectId);
		$asgmtSql->setOrderBy("U_ID");
		
		$asgmtQr = db_query($asgmtSql->getQuery (), $projectData );
		$assignments = array ();
		while ($row = db_fetch_array($asgmtQr)) {
			if (empty($assignments[$row["PW_ID"]]))
				$assignments[$row["PW_ID"]] = array ();
			$assignments[$row["PW_ID"]][] = $row["U_ID"];
		}
				
		while ( $row = db_fetch_array($qr) ) {
			$curRecord = $row;
			
			$curRecord["P_ID"] = $projectData["P_ID"];

			//$curRecord["ROW_URL"] = prepareURLStr( PAGE_PM_ADDMODWORK, array(ACTION=>PM_ACTION_MODIFY, SORTING_COL=>base64_encode($sorting), "P_ID"=>base64_encode($projectData["P_ID"]), "PW_ID"=>base64_encode($curRecord["PW_ID"]),  "firstIndex"=>$firstIndex, "opener"=>PAGE_PM_WORKLIST, "list_action"=>$action) );

			/*$work_users = array();
			
			$work_qr =  db_query( $qr_pm_select_work_asgn, $curRecord );
			if ( PEAR::isError($qr) ) {
				$errorStr = sprintf( $pmStrings[PM_ERR_EXTRACTION], $pmStrings[PM_ERR_ASGNFIELD] );

				$fatalError = true;
				break;
			}
			while ( $work_row = db_fetch_array($work_qr) )
				$work_users[count($work_users)] = $work_row["U_ID"];
			@db_free_result($work_qr);*/
			
			$work_users = (!empty($assignments[$curRecord["PW_ID"]])) ? $assignments[$curRecord["PW_ID"]] : array ();;
			
			$curRecord["ASGN_COUNT"] =  count ($work_users);
			$curRecord["ASGN"] = join(",", $work_users);
			if (!$curRecord["ASGN"])
				$curRecord["ASGN_COUNT"] = 0;
			$curRecord["PW_STARTDATE"] = convertToDisplayDateNT($curRecord["PW_STARTDATE"]);
			$curRecord["PW_DUEDATE"] = convertToDisplayDateNT($curRecord["PW_DUEDATE"]);
			$curRecord["PW_ENDDATE"] = convertToDisplayDateNT($curRecord["PW_ENDDATE"]);
			$curRecord["PW_BILLABLE"] = $curRecord["PW_BILLABLE"] ? true : false;
			
			$project_works[count($project_works)] = $curRecord;
		}
		
		@db_free_result($qr);
	} while (false);
	
	
	//$outWorks = (empty($noPaging)) ? array_slice ($project_works, $start, $limit) : $project_works;
	print $json->encode(array ("totalCount" => $totals, "minDate" => convertToDisplayDateNT($minDate), "maxDate" => convertToDisplayDateNT($maxDate), "works" => $project_works));	
	
	function sortByAssigned ($a, $b) {
		if ($a["ASGN_COUNT"] == $b["ASGN_COUNT"])
			return 0;
		return ($a["ASGN_COUNT"] > $b["ASGN_COUNT"]) ? 1 : -1;
	}
	
	function sortRByAssigned ($a, $b) {
		if ($a["ASGN_COUNT"] == $b["ASGN_COUNT"])
			return 0;
		return ($a["ASGN_COUNT"] < $b["ASGN_COUNT"]) ? 1 : -1;
	}
?>