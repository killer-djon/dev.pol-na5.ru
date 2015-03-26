<?php
	class DDFoldersTree extends WbsFoldersTree {
		private $viewModesValues = array ("columns" => 0, "list" => 1, "detail" => 2, "tile" => 3);
		private $defaultViewMode = "columns";
		
		protected $statData;
		
		public function __construct($app, $user) {
			$this->app = $app;
			parent::__construct($app->getDataModel(), $user, "DOCFOLDER", "DF_", "/ROOT/DD/FOLDERS");
		}
		
		public function loadTree($withStatInfo = false) {
			parent::loadTree();
			
			$statData = array ("files_count" => 0, "folders_count" => 0);
			if ($withStatInfo) {
				$ids = $this->getReadableNodesIds($this->getRootNode());
				if (!$ids)
					return;
				foreach ($ids as &$cId) {
					$cId = "'" . $cId . "'";
					$statData["folders_count"]++;
				}
				
				$sql = new CSelectSqlQuery ("DOCLIST");
				$sql->addConditions("DF_ID IN (" . join(", ", $ids) . ")");
				$sql->setSelectFields("DF_ID,COUNT(*) AS cnt");
				//$sql->addConditions("STATUS", 0);
				$sql->setGroupBy("DF_ID");
				$data = Wdb::getData($sql);
				
				
				foreach ($data as $cRow) {
					$node = $this->getNode($cRow["DF_ID"]);
					$node->FilesCount = $cRow["cnt"];
					$statData["files_count"] += $node->FilesCount;
				}	
			}		
			$this->statData = $statData;
		}
		
		public function getRootFolderLabel() {
			return waLocale::getStr("dd", "root_folder_label");
		}
		
		public function getStatInfo() {
			return $this->statData;
		}
		
		private function getAvailableNodesIds($parentNode) {
			$res = array ();
			if (!$parentNode->isAvailable())
				return array();
			$res[] = $parentNode->Id;
			$children = $parentNode->getChildren();
			foreach ($children as $cChild) {
				$res = array_merge($res, $this->getAvailableNodesIds($cChild));
			}
			return $res;
		}
		
		public function getReadableNodesIds() {
			$nodes = $this->getAvailableNodes();
			$ids = array ();
			foreach ($nodes as $cNode) {
				if ($cNode->Rights > 0)
					$ids[] = $cNode->Id;				
			}
			return $ids;
		}
		
		/**
		 * @return DDFolder
		 */
		protected function createNode($row) {
			$node = new DDFolder($this->dataModel, $row);
			return $node;
		}
		
		public function getFile($id) {
			return $this->dataModel->getRecord($id);
		}
		
		public function getFilesPath () {
			return $this->app->getAttachmentsPath("files");
		}
		
		public function deleteFiles($filesIds) {
			foreach ($filesIds as $cId) {
				$file = $this->getFile($cId);
				$file->delete();
			}
		}
		
		/**
		 * @return DDFolder
		 */
		public function getNodeBySlug($slug) {
			$sql = new CSelectSqlQuery ($this->tableName);
			$sql->addConditions("DF_LINK_SLUG", $slug);
			$row = Wdb::getRow($sql);
			
			if (!$row)
				return null;
			
			$node = $this->createNode($row);
			$node->checkUserRights();
			
			return $node;
		}
		
		public function getFilesByIds($ids) {
			$data = array ();
			if ($ids) {
				$sql = new CSelectSqlQuery ("DOCLIST");
				$sql->addConditions("DL_ID IN (" . join(",", $ids) . ")");
				$sql->addConditions("DL_STATUSINT", 0);
				$sql->setSelectFields("*");
				$sql->setOrderBy("DL_FILENAME", "ASC");
				$data = Wdb::getData($sql);
			}
			$result = array ("data" => array());
			foreach ($data as $cRow)
				$result["data"][] = $this->dataModel->createRecord($cRow);				
			return $result;
		}
		
		public function getFileBySlug($slug) {
			$sql = new CSelectSqlQuery("DOCLIST");
			$sql->addConditions("DL_LINK_SLUG", $slug);
			$row = Wdb::getRow($sql);
			if (!$row)
				throw new RuntimeException("Cannot find file slug $slug");
			return $this->dataModel->createRecord($row);
		}
		
		
		
		public function getFileRow($id) {
			$sql = new CSelectSqlQuery("DOCLIST");
			$sql->addConditions("DL_ID", $id);
			$row = Wdb::getRow($sql);
			return $row;
		}
		
		/**
		 * Returns ViewMode of the folder
		 * 
		 * @param WbsUser $user
		 * @param string $folderId
		 * 
		 * @return string
		 */
		public function getUserFolderViewmode($user, $folderId) {
			$view_mode = false;
			$view_port = $user->getAppSetting("DD", "FOLDERVIEWOPT", $this->defaultViewMode);
			if ($view_port == "global" || !$folderId) {
				$view_mode = $user->getAppSetting("DD", "FOLDERVIEWMODE", $this->defaultViewMode);
			}
			else {
				$view_mode = $user->getAppSetting("DD", "FOLDERVIEWMODE_".$folderId, $this->defaultViewMode);
			}
			foreach ($this->viewModesValues as $cMode => $cValue) {
				if ($cValue == $view_mode) {
					return $cMode;
				}
			}
			return $this->defaultViewMode;			
		}

		/**
		 * Set folder viewmode for user
		 * 
		 * @param WbsUser $user
		 */
		public function setUserFolderViewmode($user, $folderId, $mode) {
			if (!isset($this->viewModesValues[$mode]))
				throw new RuntimeException("Wrong viewmode '$mode'");
			
			// If user viewmode is a global setting (unlinked from folder) - set the global param
			if ($user->getAppSetting("DD", "FOLDERVIEWOPT") == "global") {
				$user->setSetting("DD", "FOLDERVIEWMODE", $this->viewModesValues[$mode]);
				return;
			} else {
				$user->setSetting("DD", "FOLDERVIEWMODE_".$folderId, $this->viewModesValues[$mode]);
			}
		}
		
		public function searchFiles($searchStr, $sortParams, $limitParams) {
			$this->loadTree();
			$foldersIds = $this->getReadableNodesIds($this->getRootNode());
			
			$filter = new CSqlFilter();
			if ($foldersIds) {
				foreach ($foldersIds as &$cId)
					$cId = "'" . $cId . "'";
				$filter->addConditions("DF_ID IN (" . join(",", $foldersIds) . ")");
			} else
				$filter->addConditions("false");
				
			if ($searchStr) {
				$searchParts = split(" ", $searchStr);
				$conds = array ();
				foreach ($searchParts as $cPart)
					$filter->addConditions("CONCAT(DL_FILENAME, DL_DESC) LIKE '%".mysql_real_escape_string($cPart)."%'");
			} else
				$filter->addConditions("false");
			
			
			return $this->dataModel->getRecords($filter, $sortParams, $limitParams);
		}
		
		protected function getLoadNodesOrderBySql () {
			return "DF.DF_SPECIALSTATUS ASC, DF.DF_NAME  ASC";
		}
	}
?>