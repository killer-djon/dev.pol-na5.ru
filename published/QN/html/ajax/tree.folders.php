<?
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );
	require_once( WBS_DIR."/published/QN/qn.php" );
	
	$fatalError = false;
	$error = null;
	$errorStr = null;
	$SCR_ID = "QN";
	pageUserAuthorization( $SCR_ID, $QN_APP_ID, false );
	
	$nodes = array ();
	
	$access = null;
	$hierarchy = null;
	$deletable = null;
	$statisticsMode = false;
	
	$folders = $qn_treeClass->listFolders( $currentUser, $node, $kernelStrings, 0, false,
															$access, $hierarchy, $deletable,
															null, null, false, null, true, null, $statisticsMode );
	
	foreach ($hierarchy as $level => $data) {
		$folderData = $folders[$level];
		
		if ($folderData->RIGHT > 1) {
			$iconCls="my-folder";
			$canMove = true;
		} else {
			$iconCls="gray-folder";
			$canMove = false;
		}
		$editable = ($folderData->RIGHT >= 7) ? true : false;
		
		$leaf = sizeof($data) ? false : true;
		
		$nodes[] = array (
			"id" => $folderData->ID,
			"text" => $folderData->NAME,
			"iconCls" => $iconCls,
			"editable" => $editable,
			"allowDrag" => $canMove, 
			"allowDrop" => $canMove,
			"link" => "?curQNF_ID=" . base64_encode($folderData->ID),
			"leaf" => $leaf
		);
	}
	
	print $json->encode($nodes);	
?>