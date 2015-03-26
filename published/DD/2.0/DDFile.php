<?php
	class DDFile extends WbsRecord {
		public $DL_FILENAME;
		public $DL_FILESIZE;
		public $DL_FILETYPE;
		public $DL_DESC;
		public $DL_ID;
		public $DL_LINK_SLUG;
		public $DL_DISKFILENAME;
		public $DL_CHECKSTATUS;
		public $DL_CHECKUSERID;
		public $DL_CHECKDATETIME;
		public $DL_OWNER_U_ID;
		public $DL_FIRSTUPLOADUSERNAME;
		public $DL_UPLOADUSERNAME;
		public $VERSIONS_COUNT;
		public $DL_UPLOADDATETIME;
		public static $fields = array ("DL_FILENAME", "DL_FILESIZE", "DL_FILETYPE", "DL_DESC", "DL_ID", "DL_CHECKDATETIME", "DL_UPLOADUSERNAME", "DF_ID");
		public static $changableFields = array ("DL_DESC", "DL_LINK_SLUG");
		public static $idField = "DL_ID";
		public static $folderField = "DF_ID";
		
		public function __construct($dataModel, $row = null) {
			if ($row)
				$this->loadRow($row);
			return parent::__construct($dataModel);
		}
		
		public static function getFields() {
			return self::$fields;
		}
		
		public static function getIdField() {
			return self::$idField;
		}
		
		public static function getFolderField() {
			return self::$folderField;
		}
		
		public function loadRow($row) {
			$this->DL_LINK_SLUG = $row["DL_LINK_SLUG"];
			$this->DF_ID = $row["DF_ID"];
			$this->DL_DISKFILENAME = $row["DL_DISKFILENAME"];
			$this->DL_CHECKSTATUS = $row["DL_CHECKSTATUS"];
			parent::loadRow($row);
			$this->DL_MODIFYDATETIME = CDateTime::fromStr($row["DL_MODIFYDATETIME"]);
			$this->DL_CHECKUSERID = $row["DL_CHECKUSERID"];
			$this->DL_CHECKDATETIME =  CDateTime::fromStr($row["DL_CHECKDATETIME"]);
			$this->DL_UPLOADDATETIME= CDateTime::fromStr($row["DL_UPLOADDATETIME"]);
			if (!$row["DL_CHECKDATETIME"])
				$this->DL_CHECKDATETIME = $this->DL_UPLOADDATETIME;
			//if ($this->DL_CHECKUSERID)
			$this->DL_FIRSTUPLOADUSERNAME = $this->DL_UPLOADUSERNAME;
			if ($this->DL_CHECKUSERID)
				$this->DL_UPLOADUSERNAME = Users::getUsername($this->DL_CHECKUSERID);
			else
				$this->DL_UPLOADUSERNAME = $row["DL_UPLOADUSERNAME"];
		}
		
		public function asArray () {
			$res = parent::asArray ();
			$res["OPEN_URL"] = DDApplication::getInstance()->getScriptUrl("getfolderfile.php", array ("r" => rand(0,10000), "DL_ID" => base64_encode($this->DL_ID)));
			$res["DOWNLOAD_URL"] = $this->getDownloadUrl();
			$res["THUMBNAIL_URL"] = $this->getThumbnailUrl();
			$res["ZOHOEDIT_URL"] = $this->getZohoeditUrl();
			$res["ICON_URL"] = $this->getIconUrl();
			$res["SMALLICON_URL"] = $this->getIconUrl(16);
			$res["SHARE_LINK_URL"] = $this->getShareLinkUrl();
			$res["DL_CHECKDATETIME"] = $this->DL_CHECKDATETIME->display();
			$res["CHECKED_OUT"] = $this->isLocked();
			$res["CHECKED_OUT_INFO"] = $this->getCheckoutInfo();
			if ($this->VERSIONS_COUNT !== null)
				$res["VERSIONS_COUNT"] = $this->VERSIONS_COUNT;
			//$res["DL_UPLOADUSERNAME"] = ;//Users::getUsername($res["DL_OWNER_U_ID"]);
			$res["Rights"] = $this->getRights();
			return $res;
		}
		
		public function checkOut($user) {
			if (!$this->canChangeCheckStatus($user))
				throw new Exception("You have no rights to change status of this file");
			
			$date = CDateTime::now();
			$params = array ("DL_CHECKSTATUS" => "OUT", "DL_CHECKUSERID" => $user->getId(), "DL_CHECKDATETIME" => $date->toStr()); 
			
			$this->update($params, array_keys($params));
			$this->reload();
		}
		
		public function checkIn($user) {
			if (!$this->canChangeCheckStatus($user))
				throw new Exception(waLocale::getStr("dd", "message_no_rights_to_action"));
			
			$date = CDateTime::now();
			$params = array ("DL_CHECKSTATUS" => "IN", "DL_CHECKUSERID" => $user->getId(), "DL_CHECKDATETIME" => $date->toStr()); 
			
			$this->update($params, array_keys($params));
			$this->reload();
		}
		
		public function canChangeCheckStatus($user) {
			if (!$this->getFolder()->isFullRights() && ($this->isLocked() && $this->DL_CHECKUSERID != $user->getId()))
				return false;
			return true;
		}
		
		public function getCheckoutInfo() {
			if (!$this->isLocked())
				return "";
			else
				return Users::getUsername($this->DL_CHECKUSERID) . " " . $this->DL_CHECKDATETIME->display();
		}
		
		public function isLocked() {
			return $this->DL_CHECKSTATUS == "OUT";
		}
		
		public function reload() {
			$row = DDApplication::getInstance()->getFoldersTree()->getFileRow($this->DL_ID);
			$this->loadRow($row);
		}
		
		public function delete() {
			$path = $this->getFilePath();
			if (!$path)
				throw new RuntimeException("Cannot read file path");
			if (file_exists($path) && !unlink($path))
				throw new RuntimeException("Cannot delete file: " . $this->DL_ID);
			
			$sql = new CDeleteSqlQuery ("DOCLIST");
			$sql->addConditions("DL_ID", $this->DL_ID);
			Wdb::runQuery($sql);
		}
		
		public function getThumbnailUrl() {
			$filePath = $this->getFilePath();
			$ext = strtolower($this->DL_FILETYPE);
			$params = array ("basefile" => base64_encode($filePath), "ext" => base64_encode($ext));
			$params_arr = array();
			foreach ($params as $key => $val) {
			    $params_arr[] = $key . "=" . $val; 
			}
			$params = implode("&", $params_arr);
			return Url::get("/common/html/scripts/getfilethumb.php?" . $params);
		}
		
		public function getFilePath () {
			$folderPath = DDApplication::getInstance()->getFoldersTree()->getNode($this->DF_ID)->getFilesPath();
			$filePath = $folderPath . "/" . $this->DL_DISKFILENAME;
			return $filePath;
		}	
		
		public function getIconUrl($size = 32) {
			$iconFilename = strtolower($this->DL_FILETYPE) . ".win." . $size . ".gif";
			$folderPath = "common/html/thumbnails/";
			$filePath = Wbs::getSystemObj()->files()->getPublishedPath($folderPath . $iconFilename);
			if (!file_exists($filePath)) 
				$iconFilename = "common.win.". $size .".gif";
			
			return Url::get("/".$folderPath . $iconFilename);
		}
		
		public function update($data, $fields = null) {
			if ($fields == null)
				$fields = self::$changableFields;
			$sql = new CUpdateSqlQuery ("DOCLIST");
			$sql->addFields($data, $fields);
			$sql->addConditions("DL_ID", $this->DL_ID);
			Wdb::runQuery($sql);
		}
		
		public function getShareLink() {
			if ($this->DL_LINK_SLUG == null) {
				$slug = md5(rand());
				$this->update(array("DL_LINK_SLUG" => $slug));
				$this->reload();
			}
			if ($this->DL_LINK_SLUG == null)
				throw new RuntimeException ("Cannot create link for this file: " . $this->DL_ID);
			
			return $this->DL_LINK_SLUG;
		}
		
		public function removeShareLink() {
			$this->update(array("DL_LINK_SLUG" => null));
		}
		
		public function getFilesizeStr() {
			$fileSize = $this->DL_FILESIZE;
			if (!$fileSize)
				return "0.00 KB";
				
			$res = "";
			if ( $fileSize < 1024 )
				$res = $fileSize . " bytes";
			else if ( $fileSize < 1024*1024 )
				$res = round(100*(ceil($fileSize)/1024))/100 . " KB";
			else
				$res = round(100*ceil($fileSize)/(1024*1024))/100 . " MB";
			return $res;
			
			return $res;
		}
		
		public function getShareLinkUrl($needCreate = false) {
			if (!$this->DL_LINK_SLUG) {
				if ($needCreate)
					$this->getShareLink();
				else
					return null;
			}
			return WebQuery::getPublishedUrl("DD/2.0/file_link.php", array("sl" => $this->DL_LINK_SLUG, "DB_KEY" => base64_encode(Wbs::getDbkeyObj()->getDbkey()) ), true);
		}
		
		public function getZohoeditUrl() {
			if (!DDApplication::getInstance()->zohoEnabled())
				return "";
			return DDApplication::getInstance()->getScriptUrl("zohoedit.php", array ("DL_ID" => base64_encode($this->DL_ID)));
		}
		
		public function getDownloadUrl() {
			return DDApplication::getInstance()->getScriptUrl("getfolderfile.php", array ("DL_ID" => base64_encode($this->DL_ID), "force" => "download"));
		}
		
		public function getPublicDownloadUrl() {
			$code = md5_file($this->getFilePath());
			return DDApplication::getInstance()->getScriptUrl("getfolderfile_zoho.php", array ("DL_ID" => base64_encode($this->DL_ID), "ID" => $code, "DB_KEY" => base64_encode(Wbs::getDbkeyObj()->getDbkey())));
		}
		
		public function isImage() {
			return in_array(strtolower($this->DL_FILETYPE), array("jpg", "gif", "png"));
		}
		
		public function getVersionsData() {
			$sql = new CSelectSqlQuery ("DOCLISTHISTORY");
			$sql->addConditions("DL_ID", $this->DL_ID);
			$sql->setOrderBy("DLH_VERSION", "ASC");
			$data = Wdb::getData($sql);
			
			$prevRow = null;
			// hack for old versions data model
			foreach ($data as &$cRow) {
				if (!$prevRow) {
					$cRow["date"] = $this->DL_UPLOADDATETIME;
					$cRow["username"] = $this->DL_FIRSTUPLOADUSERNAME;
				}
				else {
					$cRow["date"] = CDate::fromStr($prevRow["DLH_DATETIME"]);
					$cRow["username"] = $prevRow["DLH_USERNAME"];
				}
				$prevRow = $cRow;
			}
			
			$data = array_reverse($data);
			
			return $data;
		}
	}
?>