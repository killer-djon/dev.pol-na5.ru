<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	//
	// Add/Modify Issue page script
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
	$itStrings = $it_loc_str[$language];
	$invalidField = null;
	$ITS_Data = null;
	
	$action = ACTION_EDIT;	
	
	$error = null;
	switch ( true ) {
		case true : {
				$senddata["P_ID"] = $P_ID;
				$senddata["PW_ID"] = $PW_ID;
				$issueList = $issues;
				foreach ($fields as $cKey => $cValue) {
					$senddata[$cKey] = $cValue;
				}
			
				//$senddata["ITL_ATTACHMENT"] = base64_encode($res);

				$senddata["I_ID"] = $I_ID;
				$senddata["U_ID_SENDER"] = $currentUser;

				// Add ITL record
				//
				$data = prepareArrayToStore($senddata, array("ITL_STATUS"));
				$status = $senddata["ITL_STATUS"];
				$ITL_IDS = it_senMultiIssueStatus( $issueList, $data, $kernelStrings, $itStrings, $currentUser, $ITS_Data, $STATUS );
				
				if ( PEAR::isError( $error = $ITL_IDS ) )
					break;
		}
	}

	
	//$issuedata["desc"] = "Hasd";
	if (PEAR::isError($error))
		$ajaxRes = array ("success" => false, "errorStr" => $error->getMessage());
	else {
		$viewdata = it_loadIssueListViewData( $currentUser, $kernelStrings );
		
		$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );
		$preproc->assign("kernelStrings", $kernelStrings);
		$preproc->assign("itStrings", $itStrings);
		$preproc->assign("manyProjects", $viewP_ID == "GBP");
		$preproc->assign("viewdata", $viewdata);
		
		$currentISSF_ID = it_getCurrentIssueFilter( $currentUser, $kernelStrings );
		
		$filterData = it_loadIssueFilterData( $currentUser, $currentISSF_ID, $itStrings );
		$filterType = it_getIssueFilterType( $filterData );
			
		$idata = it_getIssuePreparedData ($P_ID, $PW_ID, $issueList, $itStrings, $kernelStrings, $currentUser, $filterData);
		
		$issuesHTML = array ();
		foreach ($idata as $cRow) {
			$preproc->assign("listRecord", $cRow);
			$issuesHTML[$cRow["I_ID"]] = $preproc->fetch("ilist_oneissue.htm");
		}
			
		$ajaxRes = array ("success" => true, "P_ID" => $P_ID, "PW_ID" => $PW_ID, "issues" => $issueList, "issuesHTML" => $issuesHTML);
	}
	
	print $json->encode($ajaxRes);
?>