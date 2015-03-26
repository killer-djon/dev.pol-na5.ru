<?php
	class PDImage extends WbsRecord {
		public $PL_ID;  
		public $PF_ID;  	
		public $PL_DESC;  	
		public $PL_FILENAME;
		public $PL_FILETYPE;
		public $PL_FILESIZE ;
		public $PL_UPLOADDATETIME;
		public $PL_UPLOADUSERNAME; 
		public $PL_MIMETYPE;
		public $PL_DISKFILENAME;
		public $PL_DISKFILENAME_real;
		public $PL_DISKFILENAME_;  
		public $PL_MODIFYDATETIME;
		public $PL_MODIFYUSERNAME;
		public $PL_STATUSINT;
		public $PL_DELETE_U_ID;
		public $PL_DELETE_DATETIME; 
		public $PL_DELETE_USERNAME;  
		public $PL_CHECKSTATUS;  
		public $PL_CHECKDATETIME; 
		public $PL_CHECKUSERID; 
		public $PL_VERSIONCOMMENT;  
		public $PL_SORT;
		public $PL_ROTATE;
		 
		public static $outputFields = array ("PL_ID",
											 "PF_ID", 
											 "PL_FILENAME", 
											 "PL_FILETYPE", 
											 "PL_FILESIZE",
											 "PL_SORT",
											 "PL_DISKFILENAME", 
											 "PL_MODIFYDATETIME",
											 "PL_ROTATE",
		);
		private $album = null;
		
		public function __construct($row = null) {
			if ($row)
				$this->loadRow($row);
		}
		
		public function loadRow($row) {
			$this->PL_ID = $row["PL_ID"];  
			$this->PF_ID = $row["PF_ID"];  	
			$this->PL_DESC = $row["PL_DESC"];
			$this->PL_FILENAME = $row["PL_FILENAME"];
			$this->PL_FILETYPE = $row["PL_FILETYPE"];
			$this->PL_FILESIZE = $row["PL_FILESIZE"];
			$this->PL_UPLOADDATETIME = strtotime($row["PL_UPLOADDATETIME"]);
			$this->PL_UPLOADUSERNAME = $row["PL_UPLOADUSERNAME"];
			$this->PL_MIMETYPE = $row["PL_MIMETYPE"];
			$this->PL_DISKFILENAME =  preg_replace("~\.[0-9]+~", "", $row["PL_DISKFILENAME"]);			
			$this->PL_DISKFILENAME_real =  $row["PL_DISKFILENAME"];
			$this->PL_MODIFYDATETIME = strtotime($row["PL_MODIFYDATETIME"]); 
			$this->PL_MODIFYUSERNAME = $row["PL_MODIFYUSERNAME"];  
			$this->PL_STATUSINT = $row["PL_STATUSINT"];  
			$this->PL_DELETE_U_ID = $row["PL_DELETE_U_ID"]; 
			$this->PL_DELETE_DATETIME = strtotime($row["PL_DELETE_DATETIME"]); 
			$this->PL_DELETE_USERNAME = $row["PL_DELETE_USERNAME"];  
			$this->PL_CHECKSTATUS = $row["PL_CHECKSTATUS"];  
			$this->PL_CHECKDATETIME = strtotime($row["PL_CHECKDATETIME"]); 
			$this->PL_CHECKUSERID = $row["PL_CHECKUSERID"]; 
			$this->PL_VERSIONCOMMENT = $row["PL_VERSIONCOMMENT"];
			$this->PL_SORT = $row["PL_SORT"];
			$this->PL_ROTATE = $row["PL_ROTATE"];				
		}
		
		public function loadFromDb ($idImage) {
			$sql = new CSelectSqlQuery ("PIXLIST");
			$sql->addConditions("PL_ID", $idImage);
			$row = Wdb::getRow($sql);
			
			$this->loadRow($row);
		}
		
		public function getImage($id) 
		{
		    $sql = new CSelectSqlQuery ("PIXLIST");
			$sql->addConditions("PL_ID", $id);
			return Wdb::getRow($sql);
		}
		
		/**
		 * @param array $idList
		 */
		public function getImages($idList)
		{
		    if ( count($idList) <= 0 ) return false;
		    $sql = "SELECT * FROM PIXLIST WHERE PL_ID IN (".implode(',', $idList).")";
			return Wdb::getData($sql);
		}
		
		public function loadToPosition($pos, $idAlbum) {
			$sql = new CSelectSqlQuery ("PIXLIST");
			$sql->addConditions("PF_ID", $idAlbum);
			$sql->setOrderBy("PL_SORT");
			$sql->setLimit($pos , 2);
			$rows = Wdb::getData($sql);
			return $rows;
		}
		
		public static function updateInfoImage($row) {
		    $img = new PDWbsImage(PDImage::getFilePath($row));
	        $widht = $img->getImageWidth();
	        $height = $img->getImageHeight();
	        
	        $row['PL_WIDTH'] = $widht;
	        $row['PL_HEIGHT'] = $height;
	        
	        $sql_2 = new CUpdateSqlQuery('PIXLIST');
    		$sql_2->addConditions("PL_ID", $row['PL_ID']);
    		$sql_2->addFields("PL_WIDTH  = ".$widht);
            $sql_2->addFields("PL_HEIGHT = ".$height);
            Wdb::runQuery($sql_2);
            
            return $row;
		}
		
		protected function getOutputFields() {
			return self::$outputFields;
		}
		
		public function asArray() {
			$res = parent::asArray ();
			return $res;
		}
		
		public function getImageUrl($size = 96) {
			return "/published/PD/image.php?filename=" .$this->PL_DISKFILENAME_. 
				"&albumId=" .str_replace(".", "", $this->PF_ID). 
				"&size=".$size;
		}
		
		const MODE_ORIG = 'orig';
		const MODE_CREATE = 'create';
		const MODE_DEFAULT = '';
		
		public static function getUrl($row, $size = 96, $mode = self::MODE_DEFAULT)
		{
			$mode = ($mode != self::MODE_DEFAULT) ? '&mode='.$mode : '';
		    return Url::get('/')."PD/image.php?filename=" . rawurlencode( base64_encode($row['PL_DISKFILENAME'])). 
				"&albumId=" .str_replace(".", "", $row['PF_ID']). 
				"&size=".$size.$mode;
		    	
		}
		
	    public static function getFileUrl($row, $size = 96)
		{
		    return Url::get('/publicdata/'). Wbs::getDbkeyObj()->getDbkey()."/attachments/pd/"
				.$row['PF_ID'].'/'
				.preg_replace('~\.(jpg|png|gif)~i', '.'.$size.'.$1', $row['PL_DISKFILENAME']);
		}
		
		public static function getFilePath($row, $size = null) 
		{
		    if ( $size == null ) {
		    	if ( file_exists(AppPath::DATA_PATH() . '/' . Wbs::getDbkeyObj()->getDbkey() . '/attachments/pd/files/' . $row['PF_ID'] . '/' . $row['PL_DISKFILENAME']) )		    	
		        	return AppPath::DATA_PATH() . '/' . Wbs::getDbkeyObj()->getDbkey() . '/attachments/pd/files/' . $row['PF_ID'] . '/' . $row['PL_DISKFILENAME'];
		        else
		        	return AppPath::DATA_PATH() . '/' . Wbs::getDbkeyObj()->getDbkey() . '/attachments/pd/files/' . $row['PF_ID'] . '/' . iconv("UTF-8", "WINDOWS-1251", $row['PL_DISKFILENAME'] );
		    }
		    else {
		        return AppPath::PUBLISHED_PATH(). '/publicdata/' . Wbs::getDbkeyObj()->getDbkey() . '/attachments/pd/' . $row['PF_ID'] . '/' .  preg_replace('~.(jpg|png|gif)+~i', '.'.$size.'.$1', $row['PL_DISKFILENAME']);
		    }
		}
		public static function getFilePathFrontThumb($row) 
		{
	        return AppPath::PUBLISHED_PATH(). '/publicdata/' . Wbs::getDbkeyObj()->getDbkey() . '/attachments/pd/' . $row['PF_ID'] . '/thumbFront.jpg';
		}
		
		public function remove($imageId) 
		{
			$model = new DbModel();
			$sql = "SELECT * FROM PIXLIST 
					WHERE PL_ID = i:id";
			$image = $model->prepare($sql)->query(array('id' => $imageId))->fetch();
				
			$sql = "DELETE FROM PIXEXIF WHERE PL_ID = i:id";
			$model->prepare($sql)->exec(array('id' => $imageId));

			$sql = "DELETE FROM PIXLIST WHERE PL_ID = i:id";
			$model->prepare($sql)->exec(array('id' => $imageId));			

			$sql = "UPDATE PIXLIST SET PL_SORT = PL_SORT - 1
					WHERE PL_SORT > " . (int)$image['PL_SORT'];
			$model->exec($sql);
			
			$imagefs = new PDImageFSModel( PDApplication::getInstance() );
			$imagefs->removeImage($image['PL_DISKFILENAME'], $image['PF_ID']);
			
			return $image['PL_FILESIZE'];
		}
		
		public function pre_add($param)
		{
			$imageTableKey = array(
				'PF_ID' => 1,
				'PL_FILENAME' => 1,
				'PL_FILESIZE' => 1,
				'PL_DISKFILENAME' => 1,
				'PL_STATUSINT' => 1,
				'C_ID' => 1,
				'PL_SORT' => $maxSort,
			
				'PL_WIDTH' => 1,
				'PL_HEIGHT' => 1,
				'PL_DESC' => 1
			);
			$imageInsert = array_intersect_key($param, $imageTableKey);
			$imageParam = array();
			foreach ( $imageInsert as $k => $v ) {
				if ( is_string($v) )
					$imageParam[] = "$k = '$v'";
				elseif ( is_numeric($v) )
					$imageParam[] = "$k = '$v'";					
			}
			$insertData = join(", ", $imageParam);
			
			$sql = "INSERT INTO  PIXLIST SET ".$insertData;
			
			Wdb::runQuery($sql);
			return Wdb::insertId();
		}
		
		public function add($imageid, $param) 
		{
		    
			$imageTableKey = array(
				'PL_WIDTH' => 1,
				'PL_HEIGHT' => 1,
				'PL_DESC' => 1,
				'PL_UPLOADUSERNAME' => 1,
				'PL_MODIFYUSERNAME' => 1,
				'PL_UPLOADDATETIME' => 1,
				'PL_MODIFYDATETIME' => 1,
				
			);
			$imageInsert = array_intersect_key($param, $imageTableKey);
			$imageParam = array();
			foreach ( $imageInsert as $k => $v ) {
				if ( is_string($v) )
					$imageParam[] = "$k = '$v'";
				elseif ( is_numeric($v) )
					$imageParam[] = "$k = '$v'";					
			}
			$insertData = join(", ", $imageParam);
			
			$sql = "UPDATE PIXLIST SET ".$insertData." WHERE PL_ID = ".$imageid;
			
			Wdb::runQuery($sql);
			
			$exifTableKey = array(
				'PE_WIDTH' => 1,
				'PE_HEIGHT' => 1,
				'PE_DATETIME' => 1,
				'PE_FILENAME' => 1,
				'PE_FILESIZE' => 1,
				'PE_MAKE' => 1,
				'PE_MODEL' => 1,
				'PE_EXPOSURETIME' => 1,
				'PE_FNUMBER' => 1,
				'PE_ISOSPEEDRATINGS' => 1,
				'PE_FOCALLENGTH' => 1						
			);
			
			$exifInsert = array_intersect_key($param, $exifTableKey);
			if (count($exifInsert) > 0) {
				$exifInsert = array_merge($exifInsert,array(
					'PL_ID' => $imageid
				));
				
				$exifParam = array();
				foreach ( $exifInsert as $k => $v ) {
						$exifParam[] = "$k = '$v'";					
				}
				if (count($exifParam) > 0) {
					$insertData = join(", ", $exifParam);			
					
					$sql = "INSERT INTO PIXEXIF SET ".$insertData;
					Wdb::runQuery($sql);
				}
			}
			
			return $imageid;
		}

		public function sort()
		{
			$offset =   WebQuery::getParam("offset") ;
			$limit =  WebQuery::getParam("limit") ;
			$albumId = WebQuery::getParam("albumId") ;
			$data = explode(",", WebQuery::getParam("data"));
			
			$sql = new CSelectSqlQuery("PIXLIST");
			$sql->setSelectFields(array("PL_ID", "PL_SORT"));
			$sql->addConditions("PF_ID", $albumId);
			$sql->setLimit($offset, $limit);
			$sql->setOrderBy("PL_SORT");
			
			$rows = Wdb::getData($sql);
			
			$data_old = array();
			foreach($rows as $row) {
				$data_old[ $row["PL_ID"] ] = $row["PL_SORT"];
			}
			
			$arr = array();
			
			for($i=0; $i<count($data_old); $i++) {
				$arr[$i+$offset] = $data_old[ $data[ $i ] ];
			}
			
			$arr = $this->array_delete_clone($arr);
			
			$keys = array_keys($arr);
			
			if ( $arr[ $keys[0]] != $arr[ $keys[1] ] - 1 ) {
				$al1 =  $keys[0] - $arr[ $keys[0]] ;
				$al1_ = ( $al1 < 0 ) ? "- ".abs($al1) : "+ ".abs($al1);
				
				$al2 =  $keys[1] - $arr[ $keys[1]] ;
				$al2_ = ( $al2 < 0 ) ? "- ".abs($al2) : "+ ".abs($al2);
				
				if ($al2 != 0) {
					$sql1 = new CUpdateSqlQuery("PIXLIST");
					$sql1->addFields("PL_SORT = PL_SORT ".$al2_);
					$sql1->addConditions("PL_SORT >= " 
						.$arr[ $keys[1]] 
						." AND PL_SORT <= " 
						.$arr[ $keys[count($keys)-1] ]
						." AND PF_ID = ".$albumId);
					
					Wdb::runQuery($sql1);
				}
					
				if ($al1 != 0) {
					$sql1 = new CUpdateSqlQuery("PIXLIST");
					$sql1->addFields("PL_SORT = PL_SORT ".$al1_ );
					$sql1->addConditions("PL_ID = ". array_search( $arr[$keys[0]], $data_old) );
					
					Wdb::runQuery($sql1);
				}				
			}
			else if ( $arr[$keys[count($keys)-2]] != $arr[$keys[count($keys)-1]] - 1 ) {
				$al1 =  $keys[count($keys)-1] - $arr[ $keys[count($keys)-1]] ;
				$al1_ = ( $al1 < 0 ) ? "- ".abs($al1) : "+ ".abs($al1);
				
				$al2 =  $keys[0] - $arr[ $keys[0]] ;
				$al2_ = ( $al2 < 0 ) ? "- ".abs($al2) : "+ ".abs($al2);				
				
				if ($al2 != 0) {
					$sql1 = new CUpdateSqlQuery("PIXLIST");
					$sql1->addFields("PL_SORT = PL_SORT ".$al2_);
					$sql1->addConditions("PL_SORT >= " 
						.$arr[ $keys[0]] 
						." AND PL_SORT <= " 
						.$arr[ $keys[count($keys)-2	] ]
						." AND PF_ID = ".$albumId);
	
					Wdb::runQuery($sql1);
				}	
				
				if ($al1) {
					$sql1 = new CUpdateSqlQuery("PIXLIST");
					$sql1->addFields("PL_SORT = PL_SORT ".$al1_ );
					$sql1->addConditions("PL_ID = ". array_search( $arr[$keys[count($keys)-1]], $data_old) );
	
					Wdb::runQuery($sql1);
				}				
			}
		}
		
	    public function changeDesc($id, $desc)
		{
			$sql = "UPDATE PIXLIST SET PL_DESC = '".mysql_real_escape_string($desc)."' WHERE PL_ID = {$id}";
			Wdb::runQuery($sql);
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
		
		public function count($albumId)
		{
			$sql = new CSelectSqlQuery("PIXLIST");
			$sql->setSelectFields("COUNT(PL_ID)");
			$sql->addConditions('PF_ID', $albumId);
			return Wdb::getFirstField($sql);			
		}
		
		public function getImageAndPrevNext($imageId)
		{
			$sql = new CSelectSqlQuery ("PIXLIST");
			$sql->setSelectFields('*, UNIX_TIMESTAMP(PL_UPLOADDATETIME) AS UPLOADDATETIME');
			$sql->addConditions("PL_ID", $imageId);
			$row = Wdb::getRow($sql);
			
			
			if ( $row['PL_WIDTH'] == '' || $row['PL_WIDTH'] == 0 || 
		         $row['PL_HEIGHT'] == '' || $row['PL_HEIGHT'] == 0) 
		    {
		    	
		        $img = new PDWbsImage(PDImage::getFilePath($row));
		        $widht = $img->getImageWidth();
		        $height = $img->getImageHeight();
		        
		        $row['PL_WIDTH'] = $widht;
		        $row['PL_HEIGHT'] = $height;
		        
		        $sql_2 = new CUpdateSqlQuery('PIXLIST');
            	$sql_2->addConditions("PL_ID", $row['PL_ID']);
            	$sql_2->addFields("PL_WIDTH  = ".$widht);
	            $sql_2->addFields("PL_HEIGHT = ".$height);
	            Wdb::runQuery($sql_2);
		    }

			$prev = $row['PL_SORT'] - 1;
			$next = $row['PL_SORT'] + 1;
			$sql = "SELECT *, UNIX_TIMESTAMP(PL_UPLOADDATETIME) AS UPLOADDATETIME FROM PIXLIST WHERE ( PF_ID = {$row['PF_ID']} ) AND ( (PL_SORT = {$prev}) OR (PL_SORT = {$next}) ) ORDER BY PL_SORT";
			$row2 = Wdb::getData($sql);		
			
			$return = array();
			
			$return['c'] = $row;
			
			if ($row['PL_SORT'] == 0 && count($row2) == 1) {
				$return['r'] = $row2[0];
			}
			else if ($row['PL_SORT'] != 0 && count($row2) == 1 ) {
				$return['l'] = $row2[0];
			}
			else if ( isset($row2[0]) && $row2[1] ){
				$return['l'] = $row2[0];
				$return['r'] = $row2[1];
			}
			
			return $return;
		}

		public function getImageByAlbum($albumId, $idOnly = false)
		{
		    $sql = new CSelectSqlQuery ("PIXLIST");
			$sql->addConditions("PF_ID", $albumId);
			$sql->setOrderBy('PL_SORT');
			$rows =  Wdb::getData($sql);
			if ($idOnly) {
    			$list = array();
    			foreach ( $rows as $row) {
    			    $list[] = $row['PL_ID'];
    			}
    
    			return $list;
			}
			return $rows;
		}

		public function getImageByName($name)
		{
		    $sql = new CSelectSqlQuery ("PIXLIST");
			$sql->addConditions("PL_DISKFILENAME", $name);
			return Wdb::getRow($sql);
		}
		
		public function move($albumId, $imageList) {
		    
		    $sql = new CSelectSqlQuery('PIXLIST');
		    $sql->setSelectFields('MAX(PL_SORT)');
		    $sql->addConditions('PF_ID', $albumId);
		    $sort = Wdb::getFirstField($sql);
		    
		    $sql = new CSelectSqlQuery('PIXLIST');
		    $sql->addConditions('PL_ID', $imageList[0]);
		    $image = Wdb::getRow($sql);
		    
			$sql = "UPDATE PIXLIST SET PF_ID = {$albumId} , PL_SORT = ".(($sort > 0) ? $sort + 1  : 0)." WHERE PL_ID IN (" . implode(',', $imageList) .")";
		    Wdb::runQuery($sql);
		    
//			$sql = "UPDATE PIXLIST SET PL_SORT = PL_SORT - 1 WHERE PF_ID = " .$image['PF_ID']. " AND PL_SORT > ". $image['PL_SORT'];
//		    Wdb::runQuery($sql);
		    
		    $albumModel = new PDAlbum();
		    $albumModel->repairImage($albumId);
		    $albumModel->repairImage($image['PF_ID']);
		    
		}
		
		public function get($photos) {
		    if ( is_int($photos) )
		        return $this->getImage($photos);
            elseif ( is_array($photos) ) {
                $sql = new CSelectSqlQuery ("PIXLIST");
    			$sql->addConditions("PL_ID IN (". implode(',', $photos). ")");
    			return Wdb::getData($sql);
            }
		}


	}
?>