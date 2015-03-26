<?php
	class DDFilesRecordset extends WbsRecordset {
		
		public function loadVersionsHistory() {
			if ($this->isEmpty())
				return false;
			
			$sql = new CSelectSqlQuery("DOCLISTHISTORY");
			$sql->addConditions("DL_ID IN (" . join(", " , $this->getIds()) . ")");
			$sql->setGroupBy("DL_ID");
			$sql->setSelectFields("DL_ID, COUNT(*) AS cnt");
			$data = Wdb::getData($sql, "DL_ID");
			
			$records = $this->getRecords();
			foreach ($records as $cFile) {
				$cFile->VERSIONS_COUNT = isset($data[$cFile->DL_ID]) ? $data[$cFile->DL_ID]["cnt"] : 0;
			}
		}
	}
	
?>