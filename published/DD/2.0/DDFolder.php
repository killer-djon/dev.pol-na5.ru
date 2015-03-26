<?php
	class DDFolder extends WbsTreeFolder {
		public $DF_LINK_SLUG;
		public $DF_SPECIALSTATUS;
		
		public function loadRow($row) {
			parent::loadRow($row);
			$this->DF_LINK_SLUG = $row["DF_LINK_SLUG"];
			$this->DF_SPECIALSTATUS = $row["DF_SPECIALSTATUS"];
			if ($this->DF_SPECIALSTATUS == 11) // for projects files
				$this->Name = waLocale::getStr("dd", "projects_folder_label");				
		}
		
		public function asArray () {
			$row = parent::asArray ();
			$row["DF_SPECIALSTATUS"] = $this->DF_SPECIALSTATUS;
			$row["SHARE_LINK_URL"] = $this->getShareLinkUrl();
			return $row;
		}
		
		
		public function getRecords($filter = null, $sortParams = null, $limitParams = null) {
			$filter = new CSqlFilter();
			$filter->addConditions("DF_ID", $this->Id);
			$filter->addConditions("DL_STATUSINT=0");
			return $this->dataModel->getRecords($filter, $sortParams, $limitParams);			
		}
		
		
		public function getFilesPath () {
			$treeFilesPath = $this->tree->getFilesPath();
			$folderFilesPath = $treeFilesPath . "/" . substr($this->Id,0,-1) ;
			return $folderFilesPath;
		}
		
		public function getShareLink() {
			if ($this->DF_LINK_SLUG == null) {
				$slug = md5(rand());
				$this->update(array("DF_LINK_SLUG" => $slug));
				$this->reload();
			}
			if ($this->DF_LINK_SLUG == null)
				throw new RuntimeException ("Cannot create link for this file: " . $this->DL_ID);
			
			return $this->DF_LINK_SLUG;
		}
		
		public function removeShareLink() {
			$this->update(array("DF_LINK_SLUG" => null));
		}
		
		public function getShareLinkUrl($needCreate = false) {
			if (!$this->DF_LINK_SLUG) {
				if ($needCreate)
					$this->getShareLink();
				else
					return null;
			}
			if (Kernel::isHosted() && false)
				return WebQuery::getPublishedUrl("DD/f" . $this->DF_LINK_SLUG . "-" . base64_encode(Wbs::getDbkeyObj()->getDbkey()), false, true );				
			else
				return WebQuery::getPublishedUrl("DD/2.0/folder_link.php", array ("sl"=>$this->DF_LINK_SLUG, "DB_KEY"=> base64_encode(Wbs::getDbkeyObj()->getDbkey())), true );
		}
	}
?>