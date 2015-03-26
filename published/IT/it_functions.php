<?php

	//
	// Issue Tracking non-DMBS application functions
	//

	function it_getIssueHTMLStyle( $ITS_COLOR ) 
	//
	// Returns HTML tags for formatting issue text in accordance with settings of transitions scheme
	//
	//		Parameters:
	//			$ITS_COLOR - color code of the state
	//
	//		Returns HTML string
	//
	{
		global $it_styles;

		if ( !strlen($ITS_COLOR) )
			$ITS_COLOR = IT_MISSED_STATE_COLOR;

		$format = sprintf( "<font color=%s>", $it_styles[$ITS_COLOR][0] );
		if ($it_styles[$ITS_COLOR][1])
			$format = $format."<b>";

		return $format;
	}

	function it_getITLAttachmentsDir( $P_ID, $PW_ID, $I_ID, $ITL_ID )
	//
	// Returns directory containing attached issue transitions files
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$I_ID - issue identifier
	//			$ITL_ID - transition identifier
	//
	//		Returns string containing path to directory
	//
	{
		$attachmentsPath = fixDirPath( IT_ATTACHMENTS_DIR );

		return sprintf( "%s/%s/%s/%s/%s/%s", $attachmentsPath, $P_ID, $PW_ID, $I_ID, IT_TRANSITIONSDIR_NAME, $ITL_ID );
	}

	function it_getIssueAttachmentsDir( $P_ID, $PW_ID, $I_ID ) 
	//
	// Returns directory containing attached issue files
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$I_ID - issue identifier
	//
	//		Returns string containing path to directory
	//
	{
		$attachmentsPath = fixDirPath( IT_ATTACHMENTS_DIR );

		return sprintf( "%s/%s/%s/%s", $attachmentsPath, $P_ID, $PW_ID, $I_ID );
	}

	//
	// Reports functions
	//

	function it_listAllowedISMViews( $P_ID )
	//
	// Returns list of allowed Issue Statistics Matrix appearances
	//
	//		Parameters:
	//			$P_ID - project identifier
	//
	//		Returns array containing identifiers of appearances: IT_ISM_VIEW_PROJECT_STATUS..IT_ISM_VIEW_ASSIGNED_PRIORITY
	//
	{
		global $IT_ISM_VIEWS_ALLOWED;

		if ( $P_ID == IT_ISM_ALL_PROJECTS )
			return $IT_ISM_VIEWS_ALLOWED[0];
		else 
			return $IT_ISM_VIEWS_ALLOWED[1];
	}

	//
	// Work functions
	//

	function it_checkWorkEndDate( $PW_ENDDATE )
	//
	// Checks if the work is finished
	//
	//		Parameters:
	//			$PW_ENDDATE - work's end date
	//
	//		Returns value of boolean type
	//
	{
		if ( !strlen( $PW_ENDDATE ) )
			return false;

		return sqlTimestamp($PW_ENDDATE) < time();
	}

	function it_getStatusAllowedTransitions( $currentStatus, $transitionList )
	//
	// Returns issue states, to which transition from the current state is possible
	//
	//		Parameters:
	//			$currentStatus - current issue state
	//			$transitionList - issue transitions list, returned by function it_listWorkTransitions()
	//		
	//		Returns names of states in the form of an 
	//			array( "Prev Transition Name"=>IT_PREV_TRANSITION, "Next Transition Name"=>IT_NEXT_TRANSITION ), 
	//			or PEAR_Error
	//
	{
		if ( !isset($transitionList[$currentStatus]) )
			return array();

		$itsData = $transitionList[$currentStatus];

		$dest_allowed = explode( IT_TRANSITIONS_SEPARATOR , $itsData["ITS_ALLOW_DEST"]);

		$result = array();
		foreach( $dest_allowed as $statusName ) {
			if ( !strlen($statusName) )
				continue;

			if ( !array_key_exists( $statusName, $transitionList ) )
				continue;

			$result[] = $transitionList[$statusName];
		}

		return $result;
	}

	//
	// Issue transition schema chart functions
	//


	function it_getTransitionChartIndex( $transitionSchema, $statusName )
	//
	// Returns index of transition in transition schema chart, by transition status name
	//
	//		Parameters:
	//			$transitionSchema - transition schema, result of it_loadIssueTransitionSchema() function
	//			$statusName - the status name of transition
	//
	//		Returns transition index or null if it was not found
	//
	{
		$result = null;

		foreach( $transitionSchema as $index => $data )
			if ( strtoupper($data['ITS_STATUS']) == strtoupper($statusName) ) {
				$result = $index*2;
				break;
			}

		return $result;
	}

	function it_getTransitionChartColumnsData( $transitionSchema )
	//
	// Returns information about issue transitions schema chart columns - transition start row index, transition end row index
	//
	//		Parameters:
	//			$transitionSchema - transition schema, result of it_loadIssueTransitionSchema() function
	//
	//		Returns array
	//
	{
		$columns = array();

		foreach( $transitionSchema as $index => $data ) {
			if ( !strlen($data["ITS_ALLOW_DEST"]) )
				continue;

			$parity = ($index % 2) ? 1 : 0;

			$dests = explode( IT_TRANSITIONS_SEPARATOR, $data["ITS_ALLOW_DEST"] );

			foreach( $dests as $destTransitionName ) {
				$startIndex = it_getTransitionChartIndex( $transitionSchema, $data["ITS_STATUS"] );
				$endIndex = it_getTransitionChartIndex( $transitionSchema, $destTransitionName );
				$default = $destTransitionName == $data['ITS_DEFAULT_DEST'];

				$columns[] = array( IT_TRANSITION_START=>$startIndex, IT_TRANSITION_END=>$endIndex, IT_PARITY=>$parity, IT_TRANSITION_DEFAULT=>$default );
			}
		}

		return $columns;
	}

	function it_getTransitionShemaCharCellType( $transitionSchema, $rowIndex, $columnStartIndex, $columnEndIndex )
	//
	// Returns iccue transition schema chart cell type
	//
	//		Parameters:
	//			$transitionSchema - transition schema, result of it_loadIssueTransitionSchema() function
	//			$rowIndex - index of cell row
	//			$columnStartIndex - index of transition start row for a given column
	//			$columnEndIndex - index of transition end row for a given column
	//
	//		Returns cell type as integer
	//
	{
		$parity = ($rowIndex % 2 == 0) ? true : false;

		$upsidedown = $columnStartIndex > $columnEndIndex;

		$totalRows = count($transitionSchema)*2-3;

		if ( !( (!$upsidedown && $rowIndex >= $columnStartIndex && $rowIndex <= $columnEndIndex) ||
			    ($upsidedown && $rowIndex >= $columnEndIndex && $rowIndex <= ($columnStartIndex+1)) ) )
			return ($parity) ? (($totalRows == $rowIndex-1) ? 12 : 10) : 7;

		switch ( $parity ) {
			case true : 
						if ( $rowIndex == $columnStartIndex )
							return ($columnEndIndex > $rowIndex) ? 1 : 5;

						if ( $rowIndex == $columnEndIndex )
							return ($upsidedown) ? 10 : (($totalRows == $rowIndex-1) ? 11 : 3);
			case false : 
						$upperRow = $rowIndex - 1;
						$lowerRow = $rowIndex + 1;

						if ( $upperRow == $columnStartIndex )
							return ($columnEndIndex > $upperRow) ? (($rowIndex != 1) ? 2 : 6) : 8;

						if ( $upperRow == $columnEndIndex )
							return 4;

						if ( ($columnStartIndex < $upperRow && $columnEndIndex >= $lowerRow && !$upsidedown) ||
							($columnStartIndex > $upperRow && $columnEndIndex <= $lowerRow && $upsidedown) )
							return ($parity) ? 9 : 6;
		}

		return ($parity) ? 10 : 7;
	}

	function it_getTransitionShemaChartData( $transitionSchema, &$colNum, &$rowNum )
	//
	// Returns transition schema chart cells data 
	//
	//		Parameters:
	//			$transitionSchema - transition schema, result of it_loadIssueTransitionSchema() function
	//			$colNum - number of columns in chart
	//			$rowNum - number of rows in chart
	//
	//		Returns array
	//
	{
		$chartData = array();

		$rowNum = count($transitionSchema)*2 - 1;

		$columnsData = it_getTransitionChartColumnsData( $transitionSchema );
		$colNum = count($columnsData);

		for ( $rowIndex = 0; $rowIndex < $rowNum; $rowIndex++ )
			foreach ( $columnsData as $columnIndex => $colData ) {
				$columnStartIndex = $colData[IT_TRANSITION_START];
				$columnEndIndex = $colData[IT_TRANSITION_END];


				$cellType = it_getTransitionShemaCharCellType( $transitionSchema, $rowIndex, $columnStartIndex, $columnEndIndex );

				$chartData[$rowIndex][$columnIndex] = array( IT_PARITY=>$colData[IT_PARITY], IT_CELLTYPE=>$cellType, IT_TRANSITION_DEFAULT=>$colData[IT_TRANSITION_DEFAULT] );
			}

		if ( !count($columnsData) ) {
			$colNum = 1;
			for ( $rowIndex = 0; $rowIndex < $rowNum; $rowIndex++ ) {
				$columnIndex = 0;

				$cellType = it_getTransitionShemaCharCellType( $transitionSchema, $rowIndex, 0, 0 );

				$chartData[$rowIndex][$columnIndex] = array( IT_PARITY=>0, IT_CELLTYPE=>$cellType );
			}
		}

		return $chartData;
	}
	
	/*
			param $issues can be an array of ids or value
			returns array ();
	*/
	function it_getIssuePreparedData ($P_ID, $PW_ID, $issues, $itStrings, $kernelStrings, $U_ID, $filterData = null) {
		global $PMRightsManager;
		if (!$filterData)
			$filterData = array ();
		
		$addTransitionsData = true;
		$issueList = array ();
		$recordsAdded = null;
		$filterData["I_ID"] = $issues;
		$transitionList = it_listWorkTransitions( $P_ID, $PW_ID, $itStrings );
		$projmanData = it_getProjManData( $P_ID );
		$projMan = isset( $projmanData["U_ID_MANAGER"] ) ? $projmanData["U_ID_MANAGER"] : null;
		$userIsProjman = is_array($projmanData) && $projMan == $U_ID || UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($U_ID, $P_ID, $kernelStrings ), UR_TREE_FOLDER );
		$workIsClosed = it_workIsClosed($P_ID, $PW_ID, $kernelStrings);
		$userTaskRights = UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($U_ID, $P_ID, $kernelStrings ), UR_TREE_WRITE  );
		
		if ( !is_null( $res = it_addIssueListWorkRecords($P_ID, $PW_ID, $issueList, $recordsAdded, $filterData,
														$kernelStrings, $itStrings, false, $transitionList,
														$U_ID, $userIsProjman || $userTaskRights, null, $addTransitionsData, $workIsClosed ) ) );
		return $issueList;
	}

?>