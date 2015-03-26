<?php
	Kernel::incPackage("data_model");
	include_once("DDFolder.php");
	include_once("DDFile.php");
	include_once("DDFilesRecordset.php");
	include_once("DDFoldersTree.php");
	include_once("DDProjectFoldersTree.php");

	
	
	class DDDataModel extends WbsDataModel {
		private $tree;
		private $app;
		
		public function __construct($app) {
			$this->app = $app;
		}
		
		public function getFoldersTree($projectId = false) {
			if ($this->tree)
				return $this->tree;
			if ($projectId) {
				$this->tree = new DDProjectFoldersTree($this->app, CurrentUser::getInstance(), $projectId);	
			} else {
				$this->tree = new DDFoldersTree($this->app, CurrentUser::getInstance());
			}
			return $this->tree;
		}
		
		public function getFolder($id) {
			return $this->getFoldersTree()->getFolder($id);
		}
		
		public function getRecord($id) {
			if (!$id)
				throw new RuntimeException ("Not setted record id");
			$sql = new CSelectSqlQuery ("DOCLIST");
			$sql->addConditions("DL_ID", $id);
			$row = Wdb::getRow($sql);
			$record = $this->createRecord();
			$record->loadRow($row);
			return $record;
		}
		
		public function createRecord($row = null) {
			$record = new DDFile($this, $row);
			return $record;
		}
		
		public function createRecordset() {
			return new DDFilesRecordset($this);
		}
		
		public function searchFiles($searchStr, $sortParams, $limitParams) {
			return $this->tree->searchFiles($searchStr, $sortParams, $limitParams);
		}
		
		public function getRecords($filter, $sortParams, $limitParams) {
			$sql = new CSelectSqlQuery ("DOCLIST");
			$sql->addConditions("DL_STATUSINT", 0);
			if ($filter)
				$sql->applyFilter($filter);
			
			$sql->setSelectFields("COUNT(*) AS c");
			$totalCount = Wdb::getFirstField($sql);
			
			$recordSet = $this->createRecordset();
			$recordSet->setTotalCount($totalCount);
			if (!$totalCount)
				return $recordSet;
			
			if ($sortParams && $sortParams["column"])
				$sql->setOrderBy($sortParams["column"], $sortParams["direction"]);
			
			if ($limitParams)
				$sql->setLimit($limitParams["offset"], $limitParams["limit"]);
			
			$sql->setSelectFields("*");
			
			$data = Wdb::getData($sql);
			$recordSet->loadFromData($data);
			return $recordSet;
		}
	}
?>