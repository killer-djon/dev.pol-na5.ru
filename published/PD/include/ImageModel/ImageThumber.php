<?php

class ImageThumber {
	public static $IMAGE_SIZE = array('1024', "512", "256", "96");
	
	private $publicdataPath = null;
	private $attachmentsPath = null;
	
	private $fullFileName = null;
	private $albumId = null;
	private $size = null;
	
	private $fileName = null;
	private $ext = null;
	private $thumbName = null;
	
	public function __construct ( $param = null ) {
		if ( is_array( $param ) ) {
			
			$this->fullFileName = base64_decode(rawurldecode($param['filename']));
			
//			$this->fullFileName = preg_replace("~\.[0-9]+~", "", $this->fullFileName);
			$this->albumId = str_replace(".", "", $param['albumId']);
			$this->size = ( array_key_exists('size', $param) ) ? $param['size'] : null;	
			
//			list($this->fileName, $this->ext) = explode(".", $this->fullFileName);
			
			$param = array();
	        preg_match('~(.+)\.(jpg|gif|png)~i', $this->fullFileName, $param);
	        
	        $this->fileName = $param[1];
	        $this->ext = $param[2];
			
			$this->thumbName = "{$this->fileName}.{$this->size}.{$this->ext}";
		}
		else if ( is_a( $param, PDImage ) ) {
			$this->fullFileName = $param->PL_DISKFILENAME;
			$this->albumId = $param->PF_ID;
			
			list($this->fileName, $this->ext) = explode(".", $this->fullFileName);
			
			$this->thumbName = "{$this->fileName}.{$this->size}.{$this->ext}";
		}
	}
	
	public function setPublicdataPath($path) {
		$this->publicdataPath = $path . $this->albumId . "/";
	}
	
	public function setAttachmentsPath($path) {
		$this->attachmentsPath = $path . "files/" . $this->albumId . "/";
	}
	
	private function getThumbName($size = null) {
		$size_ = ($size) ? $size : $this->size;
		return "{$this->fileName}.{$size_}.{$this->ext}";
	}
	
	private function header () {
		header('Content-Disposition: inline; filename="' . $this->thumbName . '"');
		header('Pragma: public');
		header("Cache-Control: private");
		header("Cache-Control: max-age=3600");
		if ( $this->ext == 'gif' )
			header( 'Content-type: image/gif' );
		else
			header( 'Content-type: image/jpeg' );
		
	}
	
	private function headerNginx () {
		header('Content-Disposition: inline; filename="' . $this->thumbName . '"');
		header('Pragma: public');
		header("Cache-Control: private");
		header("Cache-Control: max-age=3600");
		if ( $this->ext == 'gif' )
			header( 'Content-type: image/gif' );
		else
			header( 'Content-type: image/jpeg' );
		header("Connection: close");
	    
		//TODO: nginx!!!
		header("X-Accel-Redirect: " . $path);
	}
	
	/**
	 * @return Boolean
	 */
	private function isThumbInNewPath() {
		return file_exists( $this->publicdataPath . $this->thumbName );
	}
	private function getThumbNewPath($size = null) {
		return $this->publicdataPath . $this->getThumbName($size);
	}
	/**
	 * @return Boolean
	 */
	private function isThumbInOldPath() {
		return file_exists( $this->attachmentsPath . $this->thumbName );
	}
	private function getThumbOldPath($size = null) {
		return $this->attachmentsPath . $this->getThumbName($size);
	}	
	/**
	 * @return Boolean
	 */
	private function isOriginal() {
		if ( file_exists( $this->attachmentsPath . $this->fullFileName ) ||
			file_exists( iconv("UTF-8", "WINDOWS-1251", $this->attachmentsPath . $this->fullFileName )
			))
		{ 
			return true;
		}
		return false;
	}
	private function getOriginalPath() {
		return $this->attachmentsPath . $this->fullFileName;
	}	
	private function checkPaths($paths) {        
		$pathinfo = pathinfo($paths);
    	$paths = $pathinfo['dirname'];
    			
        $arrayPaths = explode("/", $paths);
        $tempPath = array_shift($arrayPaths);   
             
        foreach ( $arrayPaths as $path ) {
            $tempPath .= "/" . $path;
            if ( !file_exists($tempPath) ) {
                mkdir($tempPath);
            }
        }
        return true;
    }
	
	private function getImageContent() {
		
		if ( $this->fullFileName == 'thumb.jpg' ) {
			
			if ( !file_exists($this->publicdataPath . 'thumb.jpg') ) {
				readfile( './img/newalbum.gif' );
			}
			else
				readfile( $this->publicdataPath . 'thumb.jpg' );
			
			return 1;
		}
		
		if ( $this->isThumbInNewPath() ) {
//			print( "isThumbInNewPath" );	
			readfile( $this->getThumbNewPath() );
		}
		else if ( $this->isThumbInOldPath() ) {
//			print( "getThumbOldPath" );
			
			$this->checkPaths($this->getThumbNewPath());
			copy($this->getThumbOldPath(), $this->getThumbNewPath());
			readfile( $this->getThumbOldPath() );
		}
		
		else if ( $this->isOriginal() ) {
//			print( "isOriginal " );			

			if ( !file_exists( $this->attachmentsPath . $this->fullFileName ) && 
			      file_exists( iconv("UTF-8", "WINDOWS-1251", $this->attachmentsPath . $this->fullFileName ) ))
			{
				$this->fullFileName = iconv("UTF-8", "WINDOWS-1251", $this->fullFileName);
			}
			
			$imageModel = new PDWbsImage(  realpath($this->getOriginalPath() ));
			if ($_GET['mode'] == 'orig') {
				if ( max($imageModel->getImageWidth(), $imageModel->getImageHeight()) <= $this->size ) {
                    readfile( realpath($this->getOriginalPath()) );	                     
    			}
    			else {
    				PDImageFSModel::mkdir( dirname($this->getThumbNewPath()) );
					if ($this->size > 144) {
						PDImageFSModel::createThumbBySize($this->size, 
														  realpath($this->getOriginalPath()),
														  $this->getThumbNewPath()														  
														  );
					}
					else {
						PDImageFSModel::createThumbCropBySize($this->size, 
															  realpath($this->getOriginalPath()),
															  $this->getThumbNewPath()
															  );
					}
			        readfile($this->getThumbNewPath());
    			}
			}
			else {
				if ( max($imageModel->getImageWidth(), $imageModel->getImageHeight()) <= $this->size ) {
                    readfile( realpath($this->getOriginalPath()) );	                     
    			}
    			else {
    				PDImageFSModel::mkdir( dirname($this->getThumbNewPath()) );
					if ($this->size > 144) {
						PDImageFSModel::createThumbBySize($this->size, 
														  realpath($this->getOriginalPath()),
														  $this->getThumbNewPath()
														  );
					}
					else {
						PDImageFSModel::createThumbCropBySize($this->size, 
															  realpath($this->getOriginalPath()),
															  $this->getThumbNewPath()
															  );
					}
			        readfile($this->getThumbNewPath());
    			}
			}
			
			
			return ;

			$crop = $this->size <= 144;
			$imageModel = new PDWbsImage(  realpath($this->getOriginalPath() ));
			if ( $_GET['mode'] == 'create' ) {
    			if ( max($imageModel->getImageWidth(), $imageModel->getImageHeight()) < $this->size ) {
//    			    $this->go404();
    			}
			}
			else {
    			if ( max($imageModel->getImageWidth(), $imageModel->getImageHeight()) < $this->size ) {
                    readfile( realpath($this->getOriginalPath()) );	 
                    return true;
    			}
			}

			if ( $imageModel->getImageWidth() > $imageModel->getImageHeight() ) {
	        	if($crop)
	        		$imageModel->cropThumbnailImage($this->size, $this->size);
	        	else
	            	$imageModel->thumbnailImage($this->size, null);
	        }
	        else {
	        	if($crop)
	        		$imageModel->cropThumbnailImage($this->size, $this->size);
	        	else
	        		$imageModel->thumbnailImage(null, $this->size);
	        }
	        PDImageFSModel::mkdir( dirname($this->getThumbNewPath()) );
	        $imageModel->writeImage($this->getThumbNewPath());
	        readfile($this->getThumbNewPath());	        
				
		}
		else {
//			print "etc";
			include_once 'WBSImageUtilsGd.php';			
			foreach ( ImageThumber::$IMAGE_SIZE as $SIZE ) {
				
				if (file_exists( iconv("UTF-8", "WINDOWS-1251", $this->attachmentsPath ."{$this->fileName}.{$SIZE}.{$this->ext}" ))) {
					$this->fileName = iconv("UTF-8", "WINDOWS-1251", $this->fileName);
				}
				
				if (file_exists( $this->attachmentsPath ."{$this->fileName}.{$SIZE}.{$this->ext}") && $SIZE >= $this->size ) {

					PDImageFSModel::mkdir( dirname($this->getThumbNewPath()) );
					
					$img =  new WBSImageUtilsGd($this->attachmentsPath ."{$this->fileName}.{$SIZE}.{$this->ext}");
					$img->resize($this->size)
					    ->save($this->getThumbNewPath())
					    ->outputImage();
					return true;
				}
			}		
			$img =  new WBSImageUtilsGd("img/notimage.png");
			$img->resize($this->size)->outputImage();
			return true;
			
		}
	}
	
	public function go404(){
	    header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        echo "404 page not found";
		die();		        
	}
	
	public function rotate($alf) {
		if ( !is_numeric($alf) || $alf < - 160  || $alf > 360) 
			throw new RuntimeException ("Alf is not valide param ".$alf);
		
		$sql = new CUpdateSqlQuery("PIXLIST");
		$sql->addConditions("PL_ID", WebQuery::getParam('id'));
		$sql->addFields("PL_ROTATE = PL_ROTATE + 1");
		
		Wdb::runQuery($sql);
			
		include_once 'WBSImageUtilsIm.php';
		if ( file_exists( $this->getOriginalPath() ) ) {
			$image = new WBSImageUtilsIm( $this->getOriginalPath() );
			
			$image->rotate($alf)->save($this->getOriginalPath());

			@unlink ( $this->getThumbNewPath(SIZE_970) );
			@unlink ( $this->getThumbNewPath(512) );
			@unlink ( $this->getThumbNewPath(256) );
			@unlink ( $this->getThumbNewPath(144) );
			@unlink ( $this->getThumbNewPath(96) );
			
			@unlink ( $this->getThumbOldPath(SIZE_970) );
			@unlink ( $this->getThumbOldPath(512) );
			@unlink ( $this->getThumbOldPath(256) );
			@unlink ( $this->getThumbOldPath(144) );
			@unlink ( $this->getThumbOldPath(96) );			
			
		}
		print "OK";
	}
	
	public function crop($param) {
		$sql = new CUpdateSqlQuery("PIXLIST");
		$sql->addConditions("PL_ID", WebQuery::getParam('imageId'));
		$sql->addFields("PL_ROTATE = PL_ROTATE + 1");
		
		Wdb::runQuery($sql);
		
		include_once 'WBSImageUtilsIm.php';
		if ( file_exists( $this->getOriginalPath() ) ) {
			$image = new WBSImageUtilsIm( $this->getOriginalPath() );
			
			$image->crop($param['w'], $param['h'], $param['x'], $param['y'] )
				  ->save($this->getOriginalPath());

			@unlink ( $this->getThumbNewPath(SIZE_970) );
			@unlink ( $this->getThumbNewPath(512) );
			@unlink ( $this->getThumbNewPath(256) );
			@unlink ( $this->getThumbNewPath(144) );
			@unlink ( $this->getThumbNewPath(96) );
			
			@unlink ( $this->getThumbOldPath(SIZE_970) );
			@unlink ( $this->getThumbOldPath(512) );
			@unlink ( $this->getThumbOldPath(256) );
			@unlink ( $this->getThumbOldPath(144) );
			@unlink ( $this->getThumbOldPath(96) );			
			
		}
		print "OK";
	}
	
	public function outputImage_() {
		ob_end_clean();
		if ( !isset($_GET['error']) )
			$this->header();
		$this->getImageContent();
	}	
}

?>