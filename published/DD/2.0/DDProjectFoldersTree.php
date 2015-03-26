<?php
	class DDProjectFoldersTree extends DDFoldersTree {

		protected $projectId = false;
		
		public function __construct($app, $user, $projectId) {
			$this->projectId = $projectId;
			$this->app = $app;
			parent::__construct($app, $user);
		}
		
		protected function getLoadNodesSqlQuery($fieldId = null) {
		
			$sql = "SELECT 
				DF.* , 
				IF (NOT(AL.LINK_AR_PATH IS NULL),
					BIT_OR( DA2.AR_VALUE ) | IF (DAC2.AR_VALUE IS NULL , 0, DAC2.AR_VALUE),
					BIT_OR( DA.AR_VALUE ) | IF (DAC.AR_VALUE IS NULL , 0, DAC.AR_VALUE)) 
				AS USER_RIGHTS
			FROM TREE_FOLDER_TABLE DF 
				LEFT JOIN UGROUP_USER UGU ON UGU.U_ID = 'USER_ID_FIELD' 
				LEFT JOIN UG_ACCESSRIGHTS DA ON DA.AR_OBJECT_ID = DF.FOLDER_ID_FIELD AND DA.AR_ID = UGU.UG_ID AND DA.AR_PATH = 'RIGHTS_PATH' 
				LEFT JOIN U_ACCESSRIGHTS DAC ON DAC.AR_OBJECT_ID = DF.FOLDER_ID_FIELD AND DAC.AR_ID = 'USER_ID_FIELD' AND DAC.AR_PATH = 'RIGHTS_PATH' 
				LEFT JOIN ACCESSRIGHTS_LINK AL ON (AL.AR_PATH='RIGHTS_PATH' AND AL.AR_OBJECT_ID=DF.FOLDER_ID_FIELD)
				LEFT JOIN U_ACCESSRIGHTS DAC2 ON (DAC2.AR_OBJECT_ID = AL.LINK_AR_OBJECT_ID AND DAC2.AR_PATH = AL.LINK_AR_PATH AND DAC2.AR_ID = 'USER_ID_FIELD' )
				LEFT JOIN UG_ACCESSRIGHTS DA2 ON (DA2.AR_OBJECT_ID = AL.LINK_AR_OBJECT_ID AND DA2.AR_PATH = AL.LINK_AR_PATH AND DA2.AR_ID = UGU.UG_ID )
			WHERE 
				DF.FOLDER_STATUS_FIELD = 'FOLDER_STATUS' 
				AND AL.LINK_AR_OBJECT_ID = '{$this->projectId}'
			GROUP BY DF.FOLDER_ID_FIELD
			ORDER BY ORDER_BY_SQL
			";
			
			$orderBySql = $this->getLoadNodesOrderBySql();
			
			$sql = str_replace("USER_ID_FIELD", $this->user->getId(), $sql);
			$sql = str_replace("RIGHTS_PATH", $this->rightsPath, $sql);
			$sql = str_replace("FOLDER_STATUS_FIELD", $this->statusField, $sql);			
			$sql = str_replace("FOLDER_STATUS", 0, $sql);			
			$sql = str_replace("ORDER_BY_SQL", $orderBySql, $sql);
			$sql = str_replace("FOLDER_ID_FIELD", $this->idField, $sql);
			$sql = str_replace("TREE_FOLDER_TABLE", $this->tableName, $sql);
			
			return $sql;			
		}
		
		
		protected function loadFromList($rows) {
			$nodesList = array ();
			foreach ($rows as $cRow) {
				if ($cRow['DF_SPECIALSTATUS'] == 2) {
					$node = $this->createRootNode();
					$node->loadRow($cRow);
					$node->Name = $this->getRootFolderLabel();
					$node->DF_SPECIALSTATUS = $cRow['DF_SPECIALSTATUS'];
					
					$this->rootNode = $node;
				} else {
					$node = $this->createNode($cRow);
				}
				$node->initChildren();
				$nodesList[$node->Id] = $node;
			}
			foreach ($nodesList as $cNode) {
				if ($cNode->isRoot())
					continue;
				$parentNode = $nodesList[$cNode->ParentId];
				if (!$parentNode)
					throw new RuntimeException ("Cannot find node " . $cNode->ParentId . " witch defined as parent for " . $cNode->Id);
				$parentNode->appendChild($cNode);
			}
			$this->nodes = $nodesList;
		}
		
		
		public function getRootFolderLabel() {
			return waLocale::getStr("dd", "root_folder_project_label");
		}
		
	}
?>