<?php
	include_once("_ajax.init.php");
	Wbs::authorizeUser("DD");
	Kernel::incAppFile("DD", "dd_app");
	
	$app = DDApplication::getInstance();
	$foldersTree = $app->getFoldersTree();
	
	$json = new Services_JSON();
	try {
		$filesRecordset = null;
		
		// Read sort params
		$sortParams = WebQuery::getParam("sortColumn") ? 
			array ("column" => WebQuery::getParam("sortColumn"), "direction" => WebQuery::getParam("sortDirection")) :
			null;
		
		// Read limit params
		$limitParams = (WebQuery::getParam("limit")) ?
			array ("offset" => WebQuery::getParam("offset"), "limit" => WebQuery::getParam("limit")) : 
			null;
		
		$mode = WebQuery::getParam("mode");
		
		if ($mode == "folder") {
			// Load node
			$node = $foldersTree->getNode(WebQuery::getParam("folderId"));
			// Get files collection
			if ($node->canRead())
				$filesRecordset = $node->getRecords(array(),$sortParams, $limitParams);
			
			// Get folder data as array
			$folderData = $node->asArray ();
			$folderData["ENC_ID"] = base64_encode($folderData["ID"]); // For 1.0 version links
			
			// Get viewmode
			$viewMode = $foldersTree->getUserFolderViewmode(CurrentUser::getInstance(), $node->Id);
			
			// Set setting for next times
			CurrentUser::getInstance()->setSetting("DD", "LASTFOLDER", $node->Id);	
				
		} elseif ($mode == "search") {
			if (!WebQuery::getParam("searchString"))
				throw new RuntimeException("Empty search string");
			
			$searchString = WebQuery::getParam("searchString");
			
			CurrentUser::getInstance()->setSetting("DD", "DD_SEARCHSTRING", $searchString);
			
			$filesRecordset = $app->getDataModel()->searchFiles(WebQuery::getParam("searchString"), $sortParams, $limitParams);			
			$folderData = array ("ID" => "Search", "RIGHTS" => 1, "NAME" => sprintf(waLocale::getStr("dd", "search_results"), $searchString));
			
			$viewMode = "list";
		} else {
			throw new RuntimeException("Unknown mode: $mode");
		}
		
		if ($filesRecordset)
			$filesRecordset->loadVersionsHistory ();
	} catch (Exception $ex) {
		print $json->encode(array ("isError" => true, "errorStr" => $ex->getMessage()));
		exit;
	}
	
	print $json->encode(array ("files" => $filesRecordset ? $filesRecordset->asArray () : array(), "total" => $filesRecordset ? $filesRecordset->getTotalCount() : 0, "folder" => $folderData, "viewMode" => $viewMode));
?>