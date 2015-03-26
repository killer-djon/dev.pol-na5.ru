<?php
	
	class PDAlbum extends WbsRecord {
		
		const STATE_PUBLIC = 1;
		const STATE_PRIVATE = 2;
		const STATE_TO_LINK = 3; //public but hide frontend album list
		
		public $PF_ID;
		public $PF_ID_PARENT; 
		public $PF_NAME;  	
		public $PF_STATUS;  
		public $PF_CREATEDATETIME;  
		public $PF_CREATEUSERNAME; 	
		public $PF_MODIFYDATETIME;  
		public $PF_MODIFYUSERNAME;	
		public $PF_DATESTR;
		public $PF_SETTING;
		public $PF_DESC;
		public $PF_THUMB;
		public static $outputFields = array ("PF_ID", "PF_NAME", "PF_DATESTR", "PF_SETTING");
		
		const ALBUMNAME_LENGTH = 10;
		
		/**
		 * is $obj array - loadRow( $obj )
		 * is $obj int - loadId( $obj ) 
		 * @param mixet $obj
		 */
		public function __construct($obj = null) {
			if ( is_array( $obj ) ) {
				$this->loadRow($obj);
			}
			else if ( is_numeric( $obj ) ) {
				$this->loadId( $obj );
			}
		}
		
		protected function loadRow($row) {
			$this->PF_ID = $row["PF_ID"];
			$this->PF_ID_PARENT = $row["PF_ID_PARENT"];
			$this->PF_NAME = $row["PF_NAME"];  	
			$this->PF_STATUS = $row["PF_STATUS"];  
			$this->PF_CREATEDATETIME = $row["PF_CREATEDATETIME"];  
			$this->PF_CREATEUSERNAME = $row["PF_CREATEUSERNAME"]; 	
			$this->PF_MODIFYDATETIME = $row["PF_MODIFYDATETIME"];  
			$this->PF_MODIFYUSERNAME = $row["PF_MODIFYUSERNAME"];
			$this->PF_DATESTR = $row["PF_DATESTR"];
			$this->PF_SETTING = $row["PF_SETTING"];
			$this->PF_DESC = $row["PF_DESC"];
			$this->PF_THUMB = $row["PF_THUMB"];
			
		}
		public function getAlbumListThumb($state = 0) {
			$sql = new CSelectSqlQuery("PIXFOLDER", "PF");
			$sql->setSelectFields(array(
				"PF.PF_ID", 
				"PF.PF_STATUS",
				"PF.PF_NAME", 
				"PF.PF_DATESTR",
				"PL.PL_ID",
				"PL.PL_DISKFILENAME",
				"PL.PL_FILETYPE",
				'PF.PF_LINK',
				'PF.PF_DESC',
				'PF.PF_THUMB',
				'PF.PF_SETTING',
				"COUNT(PL2.PL_ID) AS PHOTOS_COUNT"
			));
			
			$sql->setOrderBy("PF_SORT");
			$sql->leftJoin("PIXLIST", "PL", "PF.PF_THUMB = PL.PL_ID");
			$sql->leftJoin("PIXLIST", "PL2", "PL2.PF_ID = PF.PF_ID");
			$sql->setGroupBy("PF.PF_ID");
			
			if ( $state != 0 ) 
				$sql->addConditions("PF_STATUS", $state);
						
			$rows = Wdb::getData($sql);
			$sql = new CSelectSqlQuery("PIXLIST");
			$sql->setSelectFields('COUNT(PL_ID)');
			$imageCount = Wdb::getFirstField($sql);

			
			foreach ( $rows as &$row ) {
				if (strlen($row['PF_NAME']) > PDAlbum::ALBUMNAME_LENGTH ) {
						$row['PF_NAMESIMPLE'] = mb_substr( $row['PF_NAME'], 0, PDAlbum::ALBUMNAME_LENGTH-3 )."...";
				}
				$row['PF_NAME_H'] = htmlspecialchars($row['PF_NAME']);
										
				if ( false && $row['PF_THUMB'] == 0 ) {
					$sql = new CSelectSqlQuery('PIXLIST');
					$sql->setSelectFields('PL_ID, PL_DISKFILENAME');
					$sql->addConditions('PF_ID', $row['PF_ID']);
					$sql->setOrderBy('PL_ID');
					$sql->setLimit(0,1);
					
					$image = Wdb::getRow($sql);
					
					if( !$image )
						continue;
					
					$imageModel = new PDImageFSModel(PDApplication::getInstance());
					$imageModel->setAlbumThumb($row['PF_ID'], $image['PL_DISKFILENAME']);

					$sql = "UPDATE PIXFOLDER SET PF_THUMB = {$image['PL_ID']} WHERE PF_ID = {$row['PF_ID']}";
					Wdb::runQuery($sql);
				}
				if ( $row['PF_DATESTR'] == '' && ($row['PF_THUMB'] == '' || $row['PF_THUMB'] == 0)) {
				    
				    $sql = new CSelectSqlQuery('PIXLIST');
				    $sql->setSelectFields('PL_ID');
				    $sql->setLimit(0, 1);
				    $sql->addConditions('PF_ID', $row['PF_ID']);
				    $imageId = Wdb::getFirstField($sql);
				    if ($imageId) {                    
				    
    				    $sql = new CSelectSqlQuery('PIXEXIF');
    				    $sql->setSelectFields('UNIX_TIMESTAMP(PE_DATETIME) AS PE_DATETIME');
    				    $sql->setLimit(0, 1);
    				    $sql->addConditions('PL_ID', $imageId);
    				    $date = Wdb::getFirstField($sql);
    				    
    				    if ( $date != 0 ) {
    				        $date = WbsDateTime::getTime($date, false, '');
    				    }
    				    else {
    				        $sql = new CSelectSqlQuery('PIXLIST');
        				    $sql->setSelectFields('UNIX_TIMESTAMP(PL_UPLOADDATETIME) AS PL_UPLOADDATETIME');
        				    $sql->setLimit(0, 1);
        				    $sql->addConditions('PL_ID', $imageId);
        				    $date = Wdb::getFirstField($sql);
    				        $date = WbsDateTime::getTime($date, false, '');
    				    }
    				    
    					$sql = "UPDATE PIXFOLDER SET PF_DATESTR = '{$date}' WHERE PF_ID = {$row['PF_ID']}";
    					Wdb::runQuery($sql);
    				    $row['PF_DATESTR'] = $date;
				    }
				    
				}
			}
			
			$sqlrepair = 'SELECT PF_ID
                            FROM PIXFOLDER
                            GROUP BY PF_SORT
                            HAVING count( PF_SORT ) >1';
			$data = Wdb::getData($sqlrepair);
			
			if ( $data  && count( $data ) > 0 ) {
			    $this->repair();
			}
			
			return array(
				'status' => 'OK',
				'data' => array_values($rows),
				'imageCount' => $imageCount
			);			
		}
		
		public function repair($isInverse = false)
		{
			$sql = new CSelectSqlQuery("PIXFOLDER");
			if ($isInverse)
				$sql->setOrderBy("PF_CREATEDATETIME DESC");
			else
        		$sql->setOrderBy("PF_SORT");
        
        	$data = Wdb::getData($sql);
        
        	$num = 0;
        	foreach ($data as $row) {
        		$sql = new CUpdateSqlQuery("PIXFOLDER");
        		$sql->addConditions("PF_ID", $row['PF_ID']);
        		$sql->addFields("PF_SORT = ".$num++);
        
        		WDB::runQuery($sql);
        	}
            return;
		}
		
		public function getCount($id)
		{
			$sql = new CSelectSqlQuery("PIXLIST");
			$sql->setSelectFields('COUNT(*)');
        	$sql->addConditions("PF_ID", $id);
        	if ( $count = Wdb::getFirstField($sql) )
        		return $count;
        	else
        		return 0; 
		}
		
		public function repairImage($id)
		{
			$sql = new CSelectSqlQuery("PIXLIST");
        	$sql->addConditions("PF_ID", $id);
        	$sql->setOrderBy("PL_SORT");
        	$data = Wdb::getData($sql);
        
        	$num = 0;
        	foreach ($data as $row) {
        		$sql = new CUpdateSqlQuery("PIXLIST");
        		$sql->addConditions("PL_ID", $row['PL_ID']);
        		$sql->addFields("PL_SORT = ".$num++);
        		WDB::runQuery($sql);
        	}		    
		}
		
		public function getAlbumList() 
		{
			$sql = new CSelectSqlQuery("PIXFOLDER");
			$sql->setSelectFields(array(
				"PF_ID", 
				"PF_NAME"
			));
			$sql->setOrderBy("PF_NAME");
			
			$rows = Wdb::getData($sql);
			
			return $rows;
		}
		
		
		/**
		 * @param int $albumId
		 * @return PDAlbum
		 */
		public function loadId( $albumId ) {
			$sql = new CSelectSqlQuery ("PIXFOLDER");
			$sql->addConditions("PF_ID", $albumId);
			$this->loadRow(  Wdb::getRow( $sql )  );
			return $this;
		}

		public function getRecords($filters = null, $sortParams = null, $limitParams = null) {
			
			$sql = new CSelectSqlQuery ("PIXLIST");
			$sql->addConditions("PF_ID", $this->PF_ID);
			$sql->addConditions("PL_STATUSINT", 0);
			
			$sql->setSelectFields("COUNT(*) AS c");
			$totalCount = Wdb::getFirstField($sql);
			
			if ($sortParams && $sortParams["column"])
				$sql->setOrderBy($sortParams["column"], $sortParams["direction"]);
			
			if ($limitParams["limit"])
				$sql->setLimit($limitParams["offset"], $limitParams["limit"]);
			
			$sql->setSelectFields("*");
			$data = Wdb::getData($sql);
			
			$result = array ("data" => array(), "total" => $totalCount);
			foreach ($data as $cRow) {
				$result["data"][] = new PDImage($cRow);				
			}
			return $result;
		}
		
		public function getImageListDesc($albumId)
		{
		    $sql = new CSelectSqlQuery ("PIXLIST");
		    $sql->setSelectFields("PL_ID, PL_DESC, PF_ID, PL_DISKFILENAME");
		    $sql->addConditions("PF_ID", $albumId);
		    $sql->setOrderBy('PL_SORT');
		    
		    $rows = Wdb::getData($sql);

		    foreach ($rows as &$row) {
		        if ( file_exists( PDImage::getFilePath($row) ) )
                    $row['IMG_URL'] = PDImage::getUrl($row, 96);
                else
                    $row['IMG_URL'] = '';
                
                $row['PL_DESC'] = htmlspecialchars($row['PL_DESC']); 
		    }
		    
		    return $rows;
		}
		
		public function getImageListAndComments($filters = null, $offset = 0, $limit = 20, $noUpdate = false) {

		        
		    $sqlrepair = 'SELECT PL_ID
                            FROM PIXLIST
                            WHERE PF_ID ='.$this->PF_ID.'
                            GROUP BY PL_SORT
                            HAVING count( PL_SORT ) >1
                            ';
			$data = Wdb::getData($sqlrepair);
			
			if ( $data  && count( $data ) > 0 ) {
			    $this->repairImage($this->PF_ID);
			}
		    
		    $sql = new CSelectSqlQuery ("PIXLIST");
			$sql->addConditions("PF_ID", $this->PF_ID);
			$sql->setSelectFields("COUNT(*) AS Count");
			$totalCount = Wdb::getFirstField($sql);			
			
            $sql = new CSelectSqlQuery ("PIXLIST");
			$sql->addConditions("PF_ID", $this->PF_ID);
			$sql->setOrderBy("PL_SORT");			
			$sql->setLimit( intval($offset), intval($limit));
			
			$data = Wdb::getData($sql);
			
			if (!$noUpdate)
			foreach ( $data as $index => &$row_ ) {
			    if ($row_['PL_STATUSINT'] == 1) {
			    	$sql = new CDeleteSqlQuery('PIXLIST');
			    	$sql->addConditions('PL_ID', $row_['PL_ID']);
			    	Wdb::runQuery($sql);
			    	
			    	$sql = "UPDATE PIXLIST SET PL_SORT = PL_SORT - 1 WHERE PL_SORT > ".$row_['PL_SORT'];
			    	Wdb::runQuery($sql);
			    	
			    	$fs = new PDImageFSModel(PDApplication::getInstance());
			    	$fs->removeImage($row_['PL_DISKFILENAME'], $row_['PF_ID'] );
			    	
			    	$dm = new DiskUsageModel();
                    $dm->delete('$SYSTEM', 'PD', $row_['PL_FILESIZE'] );
			    	
			    	unset($data[$index]);
			    }
				
			    if ( $row_['PL_WIDTH'] == '' || $row_['PL_WIDTH'] == 0 || 
			         $row_['PL_HEIGHT'] == '' || $row_['PL_HEIGHT'] == 0) 
			    {
			    	try {
				        $img = new PDWbsImage(PDImage::getFilePath($row_));
				        $widht = $img->getImageWidth();
				        $height = $img->getImageHeight();
				        
				        $row_['PL_WIDTH'] = $widht;
				        $row_['PL_HEIGHT'] = $height;
				        
				        $sql_2 = new CUpdateSqlQuery('PIXLIST');
	            		$sql_2->addConditions("PL_ID", $row_['PL_ID']);
	            		$sql_2->addFields("PL_WIDTH  = ".$widht);
			            $sql_2->addFields("PL_HEIGHT = ".$height);
			            Wdb::runQuery($sql_2);
			    	} 
			    	catch (ImagickException $e)  {
			    		
			    	}
			    }
			    
			    $row_['PL_DISKFILENAME'] = rawurlencode(base64_encode($row_['PL_DISKFILENAME']));
			}
			
			$sql = new CSelectSqlQuery ("PIXFOLDER");
			$sql->setSelectFields(array(
				'PF_ID',
				'PF_NAME',
				'PF_DATESTR',
			 	'UNIX_TIMESTAMP(PF_CREATEDATETIME) AS PF_CREATEDATETIME',
				'PF_CREATEUSERNAME',  
				'PF_STATUS',
				'PF_LINK',			
				'PF_DESC',
			    'C_ID'				
			));
			$sql->addConditions("PF_ID", $this->PF_ID);
			$row =  Wdb::getRow($sql);
			
			$row['PF_CREATEDATETIME'] = WbsDateTime::getTime($row['PF_CREATEDATETIME']);
			
			$row['PF_DATESTR'] = htmlspecialchars($row['PF_DATESTR']);
			$row['PF_DESC'] = StringUtils::truncate(strip_tags($row['PF_DESC']), 100);
			
			$row['PF_LINK_MIN'] = StringUtils::minimize($row['PF_LINK']);
			
			$row['PF_LINK'] = urlencode($row['PF_LINK']);
			$row['PF_LINK_MIN'] = htmlspecialchars($row['PF_LINK_MIN']);
			
			if ( is_numeric($row['C_ID']) && $row['C_ID'] != 0 ) {
                $name = Contact::getName($album['C_ID']);
                if ( !empty($name) )
    			    $row['PF_CREATEUSERNAME'] = $name;
			}
			
			return array ("data" => $data, 
						  "total" => $totalCount,
						  'offset' => $offset, 
						  'limit' => $limit,
						  'album' => $row
            );
		}
		
		
		
		protected function getOutputFields() {
			return self::$outputFields;
		}

		public function getImageUrl($record, $size = 256) {			
			return Url::get('/')."PD/image.php?filename=" .$record['PL_DISKFILENAME']. 
				"&albumId=" .str_replace(".", "", $record['PF_ID']). 
				"&size=".$size;
		}
		public function getImageDataUrl($record, $size = 256) {
		    $name = $record['PL_DISKFILENAME'];
		    
		    //$name = str_replace('.1024.', '.', $name);
			return Url::get('/publicdata/'). Wbs::getDbkeyObj()->getDbkey()."/attachments/pd/"
				.$record['PF_ID'].'/'
				.preg_replace('~\.(jpg|gif|png)~i', '.'.$size.'.$1', $name);
		}
		
	    public function getThumbFrontendUrl($record) {
			return Url::get('/publicdata/'). Wbs::getDbkeyObj()->getDbkey()."/attachments/pd/"
				.$record['PF_ID'].'/thumbFront.jpg';
		}
		
		/**
		 * @return PDImage
		 */
		public function getFirstImage() {
			$sql = new CSelectSqlQuery ("PIXLIST");
			$sql->addConditions("PF_ID", $this->PF_ID);	
			$sql->setOrderBy("PL_SORT");
			$sql->setLimit(0, 1);		
			$data = Wdb::getRow($sql);
			if ($data)
				return new PDImage($data);
			else
				return null;			
		}
		
		public function getFirstImageUrl() {
			$img = $this->getFirstImage();
			if ($img)				
				return $img->getImageUrl();
			else 
				return ""; 			
		}
		/**
		 * @return array PDImage
		 */
		public function getImages() {
			$images = array();
			$sql = new CSelectSqlQuery ("PIXLIST");
			$sql->addConditions("PF_ID", $this->PF_ID);
			$sql->addConditions("PL_STATUSINT", 0);
			$data = Wdb::getData($sql);
			foreach($data as $row) {
				$images[] = new PDImage($row);
			}
			return	$images;
		}
		
		public function add($name) {
			
			$sql1 = "UPDATE PIXFOLDER SET PF_SORT = PF_SORT + 1";
			Wdb::runQuery($sql1);					
			
			$sql = new CSelectSqlQuery('PIXFOLDER');
			$sql->setSelectFields('MAX(PF_SORT)');
			$max = Wdb::getFirstField($sql);
			
			$sql = 'INSERT INTO PIXFOLDER SET PF_NAME = "'.$name.'", PF_SORT = 0,'.
			    'PF_ID_PARENT  = "ROOT", '.
				'PF_LINK = "", '.
				'PF_CREATEDATETIME = "'.CDateTime::now()->toStr().'", '.
				'PF_CREATEUSERNAME = "'.User::getName().'", '.
				'PF_MODIFYDATETIME = "'.CDateTime::now()->toStr().'", '.
				'C_ID = "'.User::getContactId().'", '.
				'PF_DATESTR = "", '.
				'PF_DESC  = "", '.
				'PF_SETTING  = "", '.
				'PF_THUMB = 0, '.
				'PF_MODIFYUSERNAME = "'.User::getName().'"';
			Wdb::runQuery($sql);			
			
			$albumId = Wdb::insertId();
			
			$user_settings_model = new UserSettingsModel();
			$jsonRight = $user_settings_model->get("", "PD", "NowRightAlbum");
			
		    $right = new Rights( User::getId() );
		    $right->set('PD', Rights::FOLDERS, $albumId, 7);
		    
			if ( !empty($jsonRight) ) {
    			$nowRightAlbum = json_decode($jsonRight);
    			$groupsRightsModel = new GroupsRightsModel();
    			
    			foreach ($nowRightAlbum as $groupId => $right) {
    				if ($right > 0) {
    					$groupsRightsModel->save($groupId, "/ROOT/PD/SCREENS", 'CT', 1);
    			    	$groupsRightsModel->save($groupId, "/ROOT/PD/FOLDERS", $albumId, $right);
    				}
    			}
			}			
			return $albumId;
		}
		
		public function remove($id)
		{
			$sql = new CSelectSqlQuery('PIXFOLDER');
			$sql->setSelectFields('PF_ID, PF_SORT');
			$sql->addConditions('PF_ID', $id);
			$row = Wdb::getRow($sql);			
			
			$sqldel = new CDeleteSqlQuery('PIXLIST');
			$sqldel->addConditions('PF_ID', $id);
			Wdb::runQuery($sqldel);
			
			$sqldel = new CDeleteSqlQuery('PIXFOLDER');
			$sqldel->addConditions('PF_ID', $id);
			Wdb::runQuery($sqldel);
			
			$imagefs = new PDImageFSModel( PDApplication::getInstance() );
			$imagefs->removeAlbum($id);
			
			$sql1 = new CUpdateSqlQuery("PIXFOLDER");
			$sql1->addFields("PF_SORT = PF_SORT - 1");
			$sql1->addConditions("PF_SORT > " . $row['PF_SORT'] );
			WDB::runQuery($sql1);

			$sqldel = new CDeleteSqlQuery('U_ACCESSRIGHTS');
			$sqldel->addConditions('AR_PATH', '/ROOT/PD/FOLDERS');
			$sqldel->addConditions('AR_OBJECT_ID', $id);
			Wdb::runQuery($sqldel);
			
			$sqldel = new CDeleteSqlQuery('UG_ACCESSRIGHTS');
			$sqldel->addConditions('AR_PATH', '/ROOT/PD/FOLDERS');
			$sqldel->addConditions('AR_OBJECT_ID', $id);
			Wdb::runQuery($sqldel);
			
		}
		
		public function changeState($state)
		{
			if ( !array_key_exists($state, array(self::STATE_PRIVATE, 
												self::STATE_PUBLIC, 
												self::STATE_TO_LINK)) ) 
			{
				throw new PDException ('Not access', PDException::INVALID_PARAM);				
			}
			
			$sql = new CUpdateSqlQuery('PIXFOLDER');
			$sql->addConditions('PF_ID', $this->PF_ID);
			$sql->addFields('PF_STATUS', $state);
			Wdb::runQuery($sql);
		}
		public function changeName($id, $name) 
		{
//			$sql = new CUpdateSqlQuery('PIXFOLDER');
//			$sql->addConditions('PF_ID', (int)$id);
//			$sql->addFields('PF_NAME', $name);
//            $isUnical = false;
//            while (!$isUnical) {
//                $sql = "SELECT * FROM PIXFOLDER WHERE PF_NAME = '{$name}'";
//                $row = Wdb::getRow($sql);
//                
//                if ( $row ) {
//                    $name = substr( $name, 0, strlen($name)-1  );
//                }
//                else
//                    $isUnical = true;
//                
//            }
		    
			//TODO: repair!
			$sql = "UPDATE PIXFOLDER SET PF_NAME = '". mysql_real_escape_string($name)."' WHERE PF_ID = {$id}";
			Wdb::runQuery($sql);
			
			return $name;
		}
		
		public function changeDesc($id, $desc, $datestr = null)
		{
		    $str = '';
		    if ( !is_null( $datestr ) )
		        $str = ", PF_DATESTR = '" . mysql_real_escape_string($datestr) . "'";
			$sql = "UPDATE PIXFOLDER SET PF_DESC = '".mysql_real_escape_string($desc)."' {$str} WHERE PF_ID = {$id}";
			Wdb::runQuery($sql);
		}
		
		
		public function setThumb($albumId, $thumbId, $thumbPath)
		{
			$sql = new CUpdateSqlQuery('PIXFOLDER');
			$sql->addConditions("PF_ID = {$albumId}");
			$sql->addFields("PF_THUMB = {$thumbId}");
			Wdb::runQuery($sql);
			
            $imageModel = new PDImageFSModel(PDApplication::getInstance());
			$imageModel->setAlbumThumb($albumId, base64_decode(rawurldecode($thumbPath)) );
		}
		
		public function sort()
		{
			//$offset =   WebQuery::getParam("offset") ;
			//$limit =  WebQuery::getParam("limit") ;
			$data = explode(",", WebQuery::getParam("data"));
			
			$sql = new CSelectSqlQuery("PIXFOLDER");
			$sql->setSelectFields(array("PF_ID", "PF_SORT"));
			//$sql->setLimit($offset, $limit);
			$sql->setOrderBy("PF_SORT");
			
			$rows = Wdb::getData($sql);
			
			$data_old = array();
			foreach($rows as $row) {
				$data_old[ $row["PF_ID"] ] = $row["PF_SORT"];
			}
			
			$arr = array();
			
			for($i=0; $i<count($data_old); $i++) {
				$arr[$i] = $data_old[ $data[ $i ] ];
			}
			
			$arr = $this->array_delete_clone($arr);
			
			$keys = array_keys($arr);
			
			if ( $arr[ $keys[0]] != $arr[ $keys[1] ] - 1 ) {
				$al1 =  $keys[0] - $arr[ $keys[0]] ;
				$al1_ = ( $al1 < 0 ) ? "- ".abs($al1) : "+ ".abs($al1);
				
				$al2 =  $keys[1] - $arr[ $keys[1]] ;
				$al2_ = ( $al2 < 0 ) ? "- ".abs($al2) : "+ ".abs($al2);
				
				if ($al2 != 0) {
					$sql1 = new CUpdateSqlQuery("PIXFOLDER");
					$sql1->addFields("PF_SORT = PF_SORT ".$al2_);
					$sql1->addConditions("PF_SORT >= " 
						.$arr[ $keys[1]] 
						." AND PF_SORT <= " 
						.$arr[ $keys[count($keys)-1] ]);
					
					Wdb::runQuery($sql1);
				}

				if ($al1 != 0) {
					$sql1 = new CUpdateSqlQuery("PIXFOLDER");
					$sql1->addFields("PF_SORT = PF_SORT ".$al1_ );
					$sql1->addConditions("PF_ID = ". array_search( $arr[$keys[0]], $data_old) );
					
					Wdb::runQuery($sql1);
				}				
			}
			else if ( $arr[$keys[count($keys)-2]] != $arr[$keys[count($keys)-1]] - 1 ) {
				$al1 =  $keys[count($keys)-1] - $arr[ $keys[count($keys)-1]] ;
				$al1_ = ( $al1 < 0 ) ? "- ".abs($al1) : "+ ".abs($al1);
				
				$al2 =  $keys[0] - $arr[ $keys[0]] ;
				$al2_ = ( $al2 < 0 ) ? "- ".abs($al2) : "+ ".abs($al2);				
				
				if ($al2 != 0) {
					$sql1 = new CUpdateSqlQuery("PIXFOLDER");
					$sql1->addFields("PF_SORT = PF_SORT ".$al2_);
					$sql1->addConditions("PF_SORT >= " 
						.$arr[ $keys[0]] 
						." AND PF_SORT <= " 
						.$arr[ $keys[count($keys)-2	] ]);
	
					Wdb::runQuery($sql1);
				}	
				
				if ($al1 != 0) {
					$sql1 = new CUpdateSqlQuery("PIXFOLDER");
					$sql1->addFields("PF_SORT = PF_SORT ".$al1_ );
					$sql1->addConditions("PF_ID = ". array_search( $arr[$keys[count($keys)-1]], $data_old) );
	
					Wdb::runQuery($sql1);
				}				
			}
		}
		
		private function array_delete_clone($arr) 
		{
			foreach ( $arr as $key => $value ) {
				if ( $key == $value ) {
					unset($arr[$key]);
				}
			}
			return $arr;
		}
		
		
		public function getIdByLink($link, $offset = 0, $limit = 10)
		{
			$sql = new CSelectSqlQuery('PIXFOLDER');
			$sql->setSelectFields('PF_ID, PF_STATUS, PF_NAME, PF_LINK, PF_DATESTR, PF_DESC');
			$sql->addConditions('PF_LINK', $link);
			$sql->setLimit($offset, $limit);
			return Wdb::getRow($sql);
		}
		
		public function getById($id)
		{
			$sql = new CSelectSqlQuery('PIXFOLDER');
			$sql->addConditions('PF_ID', $id);
			return Wdb::getRow($sql);
		}
		
		
		static function ago($time) 
		{
			$t = (time() - $time) / 60;
			if ($t <= 1) return "";
			$factor = array(
				"m" => 60,
				"h" => 24,
				"D" => 30,
				"M" => 12,
				"Y" => 1,
			);
			$result = "";
			foreach ($factor as $postfix => $n) {
				$result = (round($t) % $n).$postfix." ".$result;
				$t = $t/$n; 
				if ($t <= 1) {
				 break;
				}
			}
			return "(".$result."ago)";
		}

		public function sizeImagesByAlbum($albumId) 
		{
		    $sql = new CSelectSqlQuery('PIXLIST');
		    $sql->addConditions('PF_ID', $albumId);
		    $sql->setSelectFields('SUM(PL_FILESIZE) AS SIZE');
		    
		    return Wdb::getFirstField($sql);
		}
		
		public function isAlbumThumb($albumId) {
			$sql = new CSelectSqlQuery('PIXFOLDER');
			$sql->setSelectFields('PF_THUMB');
			$sql->addConditions('PF_ID', $albumId);
			$thumb = Wdb::getFirstField($sql);
			return ($thumb == 0) ? false : $thumb;
		}
	}	

?>
