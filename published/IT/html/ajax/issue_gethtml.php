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
	$metric = metric::getInstance();
	pageUserAuthorization( $SCR_ID, $IT_APP_ID, false );
	
	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];
	$error = null;
	
	switch (true) {
		case true:
			if ($action == ACTION_NEW) {
				list($P_ID, $PW_ID) = split('-', $fields["P_PW"]);
				$issuedata = it_initIssue( $P_ID, $PW_ID, $kernelStrings, $itStrings, $ITS_Data, $currentUser );
				if (PEAR::isError( $error = $issuedata ))
					break;

				$issuedata["U_ID_SENDER"] = $currentUser;
				$issuedata["I_STATUSCURRENT"] = base64_encode($issuedata["I_STATUSCURRENT"]);
				$RECORD_FILES = null;
				$metric->addAction($DB_KEY, $currentUser, 'IT', 'ADDISSUE', 'ACCOUNT');
			} else {
				$issuedata = array ("P_ID" => $P_ID, "PW_ID" => $PW_ID);
				
				// Load issue data
				$res = exec_sql( $qr_it_select_issue, array("I_ID"=>$I_ID), $issuedata, true );
				if ( PEAR::isError( $res ) ) {
					$error = PEAR::raiseError($itStrings[IT_ERR_LOADISSUEDATA]);
					break;
				}
			}
			
			$RECORD_FILES = $issuedata["I_ATTACHMENT"];
			
			// Move new attached file
			//
			$addFileList = "";
			$ifiles = $_FILES["issuefile"];
			$filesTotalSize = array_sum($_FILES["issuefile"]["size"]);
			foreach ($ifiles["name"] as $cKey => $cValue) {
				$fileInfo = array ("name" => $ifiles["name"][$cKey], "type"=>$ifiles["type"][$cKey], "tmp_name" => $ifiles["tmp_name"][$cKey], "size" => $ifiles["size"][$cKey]);
				$res = add_moveAttachedFile( $fileInfo,
												$addFileList,
												WBS_TEMP_DIR, $kernelStrings, true, "is" );
				$addFileList = $res;
			}
			if ( PEAR::isError( $error = $res ) ) 
				break;
			
			if ($filesTotalSize>0)
				$metric->addAction($DB_KEY, $currentUser, 'IT', 'ATTACH', 'ACCOUNT', $filesTotalSize);
			$addFileList = base64_encode($res);
			
			// Delete files if action is delete
			$PAGE_DELETED_FILES ="";
			if ($delfile) {
				
				$cbdeletenewfile = array();
				$cbdeleterecordfile = $delfile;
				$newDelFiles = ""; $newAttachFiles = "";
				$res = deleteAttachedFiles( base64_decode($RECORD_FILES), $newDelFiles, $newAttachFiles,
											$cbdeletenewfile, $cbdeleterecordfile, $kernelStrings );

				if ( PEAR::isError( $res ) ) {
					$errorStr =  $res->getMessage();

					break;
				}
				
				$PAGE_DELETED_FILES = base64_encode( $newDelFiles );
			}
			
			// Make issue attachments list
			$res = makeRecordAttachedFilesList( base64_decode($RECORD_FILES),
												base64_decode($PAGE_DELETED_FILES),
												base64_decode($addFileList),
												$kernelStrings );
			if ( PEAR::isError( $error = $res ) )
				break;
			
			$issuedata["I_ATTACHMENT"] = base64_encode($res);
			$issuedata["I_STARTDATE"] = convertToDisplayDate( $issuedata["I_STARTDATE"], true );

			$RECORD_FILES = $issuedata["I_ATTACHMENT"];

			foreach ($fields as $cKey => $cValue) {
				if ($action != ACTION_NEW && $cKey == "I_STATUSCURRENT")
					continue;
				$issuedata[$cKey] = $cValue;
			}
			
			// Save issue
			//
			$issuedata["P_ID"] = $P_ID;
			$issuedata["PW_ID"] = $PW_ID;
			if ($fields["I_STATUSCURRENT"] && $action == ACTION_NEW) {
				$curStatus = $fields["I_STATUSCURRENT"];
				$ITS_Data = it_loadITSData( $P_ID, $PW_ID, $curStatus );
				
				if ( PEAR::isError($ITS_Data) || is_null($ITS_Data) ) {
					$error = PEAR::raiseError($itStrings[IT_ERR_LOADITS]);
					break;
				}
			} elseif ($action == ACTION_EDIT) {
				$curStatus = $issuedata["I_STATUSCURRENT"];
				$ITS_Data = it_loadITSData( $P_ID, $PW_ID, $curStatus );
				if ( PEAR::isError($ITS_Data) || is_null($ITS_Data) ) {
					$error = PEAR::raiseError($itStrings[IT_ERR_LOADITS]);

					$fatalError = true;
					break;
				}
			}
			
			$recordData = $issuedata;
			//$recordData["I_STATUSCURRENT"] = base64_decode($recordData["I_STATUSCURRENT"]);

			$ID = it_addmodIssue( $action, prepareArrayToStore($recordData), $kernelStrings, $itStrings, $ITS_Data, $currentUser );
			if ( PEAR::isError( $error =$ID ) ) {
				/*$errorStr = $ID->getMessage();

				$errCode = $ID->getCode();
				if ( in_array($errCode, array(ERRCODE_INVALIDFIELD, ERRCODE_INVALIDLENGTH, ERRCODE_INVALIDDATE) ) )
					$invalidField = $ID->getUserInfo();*/

				break;
			}
			
			
			// Apply attachments
			//
			$attachmentsPath = it_getIssueAttachmentsDir( $P_ID, $PW_ID, $ID );
			$res = applyPageAttachments( base64_decode($addFileList),
											base64_decode($PAGE_DELETED_FILES),
											$attachmentsPath, $kernelStrings, $IT_APP_ID );
			if (PEAR::isError($error=$res))
				break;
	}
	
	if (PEAR::isError($error))
		$ajaxRes = array ("success" => false, "errorStr" => $error->getMessage());
	else {
		$viewdata = it_loadIssueListViewData( $currentUser, $kernelStrings );
		
		define( "COL_CB", "CHECKBOX" );
		$viewdata[IT_LV_COLUMNS] = array_merge( array(COL_CB), $viewdata[IT_LV_COLUMNS] );
		$it_list_columns_widths[COL_CB] = 10;

		$columnNames = array( COL_CB=>"&nbsp;" );
		foreach ( $viewdata[IT_LV_COLUMNS] as $key )
			if ( $key != COL_CB )
				$columnNames[$key] = $itStrings[$it_list_columns_names[$key]];
			
		
		
		$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );
		$preproc->assign("kernelStrings", $kernelStrings);
		$preproc->assign("itStrings", $itStrings);
		$idata = it_getIssuePreparedData ($P_ID, $PW_ID, $ID, $itStrings, $kernelStrings, $currentUser);
		
		$issueRow = $idata[0];
		$viewdata["SHOWPROJECTANDWORK"] = true;
		$issueRow["P_NAME"] = it_getProjectName($P_ID);
		$issueRow["PW_NAME"] = it_getWorkDescription($P_ID, $PW_ID);
		
		$preproc->assign("manyProjects", $viewP_ID == "GBP");
		$preproc->assign("viewdata", $viewdata);
		$preproc->assign("listRecord", $issueRow);
		$preproc->assign("columnNames", $columnNames);
		$preproc->assign("manyProjects", $viewP_ID == "GBP");
		$preproc->assign("onlyCells", true);
		$tplFilename = ($viewMode == "LIST") ? "ilist_oneissue.htm" : "ilist_grid_row.htm";
		$ihtml = $preproc->fetch($tplFilename);
		
		$ajaxRes = array ("success" => true, "P_ID" => $P_ID, "PW_ID" => $PW_ID, "I_ID" => $ID, "html" => $ihtml, "viewMode" => $viewMode);
	}
	
	print $json->encode($ajaxRes);	
?>