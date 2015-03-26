<?php

	//
	// WebAsyst Quick Notes common classes
	//

	class qn_documentFolderTree extends genericDocumentFolderTree
	{
		function qn_documentFolderTree( &$descriptor )
		{
			$this->folderDescriptor = $descriptor->folderDescriptor;
			$this->documentDescriptor = $descriptor->documentDescriptor;

			$this->globalPrefix = "QN";
		}

		function copyMoveDocuments( $documentList, $destID, $operation, $U_ID, $kernelStrings, $onAfterOperation, $onBeforeOperation = null, $callbackParams = null, $perFileCheck = true, $checkUserRights = true, $onFinishOperation = null, $suppressNotifications = false )
		{
			global $_qnQuotaManager;
			global $QN_APP_ID;

			$_qnQuotaManager = new DiskQuotaManager();

			$TotalUsedSpace = $_qnQuotaManager->GetUsedSpaceTotal( $kernelStrings );
			if ( PEAR::isError($TotalUsedSpace) )
				return $TotalUsedSpace;

			if ( is_null($callbackParams) )
				$callbackParams = array();

			$callbackParams['TotalUsedSpace'] = $TotalUsedSpace;

			$res = parent::copyMoveDocuments( $documentList, $destID, $operation, $U_ID, $kernelStrings, $onAfterOperation, $onBeforeOperation, $callbackParams, $perFileCheck, $checkUserRights, $onFinishOperation, $suppressNotifications );

			$_qnQuotaManager->Flush( $kernelStrings );

			return $res;
		}

		function moveFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation = null, $onFolderCreate = null,
			$onFolderDelete = null, $callbackParams = null, $onFinishMove = null, $checkUserRights = true,
			$topLevel = true, $accessInheritance = ACCESSINHERITANCE_COPY, $mostTopRightsSource = null,
			$folderStatus = TREE_FSTATUS_NORMAL, $plainMove = false, $checkFolderName = true )
		{
			global $_qnQuotaManager;
			global $QN_APP_ID;

			$_qnQuotaManager = new DiskQuotaManager();

			$TotalUsedSpace = $_qnQuotaManager->GetUsedSpaceTotal( $kernelStrings );
			if ( PEAR::isError($TotalUsedSpace) )
				return $TotalUsedSpace;

			if ( is_null($callbackParams) )
				$callbackParams = array();

			$callbackParams['TotalUsedSpace'] = $TotalUsedSpace;

			$res = parent::moveFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation , $onFolderCreate,
			$onFolderDelete, $callbackParams, $onFinishMove, $checkUserRights,
			$topLevel, $accessInheritance, $mostTopRightsSource,
			$folderStatus, $plainMove, $checkFolderName );

			$_qnQuotaManager->Flush( $kernelStrings );

			return $res;
		}

		function copyFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation = null, $onFolderCreate = null, $callbackParams = null, $onFininshCopy = null, $accessInheritance = ACCESSINHERITANCE_COPY, $onBeforeFolderCreate = null, $checkFolderName = true, $copyChilds = true )
		{
			global $_qnQuotaManager;
			global $QN_APP_ID;

			$_qnQuotaManager = new DiskQuotaManager();

			$TotalUsedSpace = $_qnQuotaManager->GetUsedSpaceTotal( $kernelStrings );
			if ( PEAR::isError($TotalUsedSpace) )
				return $TotalUsedSpace;

			if ( is_null($callbackParams) )
				$callbackParams = array();

			$callbackParams['TotalUsedSpace'] = $TotalUsedSpace;

			$res = parent::copyFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation, $onFolderCreate, $callbackParams, $onFininshCopy, $accessInheritance, $onBeforeFolderCreate, $checkFolderName, $copyChilds );

			$_qnQuotaManager->Flush( $kernelStrings );

			return $res;
		}

	}

	// Global QN tree class
	//
	$qn_treeClass = new qn_documentFolderTree( $qn_TreeFoldersDescriptor );

?>