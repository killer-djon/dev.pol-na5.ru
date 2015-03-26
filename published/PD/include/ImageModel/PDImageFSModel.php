<?php

class PDImageFSModel 
{
    const UPLOAD_ERR_OK = 0;
    const UPLOAD_ERR_INI_SIZE = 1;
    const UPLOAD_ERR_FORM_SIZE = 2;
    const UPLOAD_ERR_PARTIAL = 3;
    const UPLOAD_ERR_NO_FILE = 4;     
    
    private $publicDataAttachPath = null;
    private $dataAttachPath = null; 
    private $appName = null;
    private $dbKey = null;
    private $albumId = null;
    
    static $sharpen = array(
    	'970' => array(
    		'radius' => 0.4,
    		'sigma' => 0.2,
    		'quality' => 93,
    	),
    	'750' => array(
    		'radius' => 0.4,
    		'sigma' => 0.2,
    		'quality' => 93,
    	),
        '512' => array(
    		'radius' => 0.4,
    		'sigma' => 0.2,
    		'quality' => 95,
    	),
        '256' => array(
    		'radius' => 0.4,
    		'sigma' => 0.2,
    		'quality' => 95,
    	),
        '144' => array(
    		'radius' => 0.4,
    		'sigma' => 0.2,
    		'quality' => 96,
    	),
        '96' => array(
    		'radius' => 0.4,
    		'sigma' => 0.2,
    		'quality' => 96,
    	),
    );
    
    static $sharpenGd = array(
    	//$amount, $radius, $threshold, $quality
    	'970' => array(50, 0.5, 3, 93),
    	'750' => array(50, 0.5, 3, 93),
        '512' => array(40, 0.5, 3, 95),
        '256' => array(40, 0.5, 3, 95),
        '144' => array(35, 0.5, 3, 96),
        '96' => array(35, 0.5, 3, 96),
    );
    
    /**
     * @param PDApplication $app
     */
    public function __construct($app)
    {
        $this->publicDataAttachPath = $app->getPublicDataAttachPath();
        $this->dataAttachPath = $app->getDataAttachPath();
        $this->appName = $app->getAppName();
        $this->dbKey = Wbs::getDbkeyObj()->getDbkey();

        $this->checkPaths($this->getDataAttachPath());
        $this->checkPaths($this->getPublicDataAttachPath());
    }
    private function getDataAttachPath($idAlbum = null) 
    {
    	$path = $this->dataAttachPath;
  	    if ( !is_null($idAlbum) ) {
			$path .= "/".$idAlbum;
		}
        return $path;
    }
    private function getPublicDataAttachPath($idAlbum = null) 
    {
    	$path = $this->publicDataAttachPath;
  	    if ( !is_null($idAlbum) ) {
			$path .= "/".$idAlbum;
		}
        return $path;
    }
    
    
    private function checkPaths2($paths) {    
        $arrayPaths = explode("/", $paths);
        $tempPath = array_shift($arrayPaths);        
        foreach ( $arrayPaths as $path ) {
            $tempPath .= "/" . $path;
            if ( !file_exists($tempPath) ) {
                self::mkdir($tempPath);
            }
        }
        return true;
    }
    
    private function checkPaths($paths) {
        if ( DIRECTORY_SEPARATOR == "\\" ) {            
             $this->checkPaths2($paths);
             return true;
        }
             
        $wbsPath = Wbs::getSystemObj()->files()->getWbsPath();
        $paths = str_replace($wbsPath, '', $paths);
        $arrayPaths = explode("/", $paths);
        if ( substr($wbsPath, strlen($wbsPath)-1, 1 ) == '/' )
            $wbsPath = substr($wbsPath, 0, strlen($wbsPath)-1);
        //remove empty item
        //$tempPath = array_shift($arrayPaths);        
        foreach ( $arrayPaths as $path ) {
            $wbsPath .= '/' . $path ;            
            if ( !file_exists($wbsPath) ) {
                self::mkdir($wbsPath);
            }
        }
        return true;
        
    }
    /**
     * @param string $filePath
     * @return string - Unique filename
     */
    public function addImage($filePath, $fileName = null)
    {
//    	$quota = new DiskQuotaManager();
//    	$quota->addDiskUsageRecord(User::getId(), PDApplication::$APP_NAME, filesize($filePath) );
    	
        $fileInfo = pathinfo($filePath);
        
        $tempName = $fileInfo["basename"];
        //copy original
        if ( is_null( $fileName ) )
            $uniqueName = $this->getUniqueFileName($tempName);
        else
            $uniqueName = $this->getUniqueFileName($fileName);            
        $newOriginalImagePath = $this->getDataAttachPath($this->albumId) ."/". $uniqueName;
        
        $this->checkPaths( $this->getDataAttachPath($this->albumId) );
        copy($filePath, $newOriginalImagePath);
        
        return $newOriginalImagePath;
    }
    
    static function createThumbBySize($SIZE, $newOriginalImagePath, $newThumbImagePath)
    {
    	$isSharpen = self::isSharpenSetting();
    	if ( PDImageFSModel::isImagickSetting() ) {
		    $image = new Imagick($newOriginalImagePath);
		    $isW = $image->getImageWidth() > $image->getImageHeight();
		    if ($isW) 
	        	$image->thumbnailImage($SIZE, null);
	        else 
	        	$image->thumbnailImage(null, $SIZE);

		        self::writeIm_($image, $newThumbImagePath, $SIZE);
			
	        if ($isSharpen) 
    		if ( file_exists($newThumbImagePath) ) {
        	    $im = new Imagick( $newThumbImagePath );
        	    switch ($SIZE) {
        	    	case SIZE_970:
        	    		$im->sharpenImage(self::$sharpen[SIZE_970]['radius'], self::$sharpen[SIZE_970]['sigma']);
        	    		break;
        	    	case SIZE_750:
        	    		$im->sharpenImage(self::$sharpen[SIZE_750]['radius'], self::$sharpen[SIZE_750]['sigma']);
        	    		break;
        	    	case SIZE_512:
        	    		$im->sharpenImage(self::$sharpen[SIZE_512]['radius'], self::$sharpen[SIZE_512]['sigma']);
        	    		break;
        	    	case SIZE_256:
        	    		$im->sharpenImage(self::$sharpen[SIZE_256]['radius'], self::$sharpen[SIZE_256]['sigma']);
        	    		break;
        	    	case SIZE_144:
        	    		$im->sharpenImage(self::$sharpen[SIZE_144]['radius'], self::$sharpen[SIZE_144]['sigma']);
        	    		break;
        	    	case SIZE_96:
        	    		$im->sharpenImage(self::$sharpen[SIZE_96]['radius'], self::$sharpen[SIZE_96]['sigma']);
        	    		break;
        	    	default:
        	    		$im->sharpenImage(self::$sharpen[SIZE_970]['radius'], self::$sharpen[SIZE_970]['sigma']);
        	    		break;
        	    }               
        	     
		        self::writeIm_($im, $newThumbImagePath, $SIZE);
		    	$im->destroy();
			}
    		
    	}
    	else {
			$image = new WBSImageUtilsGd($newOriginalImagePath);
		    
		    $quality = 80;
		    
		    $isW = $image->getImageWidth() >= $image->getImageHeight();
		    if ( $isW ) 
    	        $image->thumbnailImage($SIZE, null);      
		    else 
    	        $image->thumbnailImage(null, $SIZE);      
//            if ($isSharpen)  		        
//				$image->unsharpMask(80, 0.5, 3);
    		if ($isSharpen) {  		        
            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['750'];  		        
				$image->unsharpMask($amount, $radius, $threshold);
    		}
				
		    $image->writeImage( $newThumbImagePath, $quality );
    	}
    }
    
    static function isSharpenSetting() {
    	$user_settings_model = new UserSettingsModel();    	
    	return $user_settings_model->get("", "PD", "SharpenUsaged") == 1;
    }
    
    static function writeIm_(&$im, $path, $size = null) {
    	$im->setImageCompression(imagick::COMPRESSION_JPEG);
    	if ($size)
			$im->setImageCompressionQuality(  self::$sharpen[$size]['quality']  );
    	try {
	        $im->writeImage( $path );
		}
    	catch(ImagickException $e) {
	        $path2 = explode('/', $path);
	        $path2[ count($path2)-1 ] = 'temp';
	        $path2 = implode('/', $path2);
	        $im->writeImage( $path2 );
	        copy($path2, $path);
    	}
    }    
    public function writeIm(&$im, $path, $size = null) {
    	$im->setImageCompression(imagick::COMPRESSION_JPEG);
    	if ( $size )
			$im->setImageCompressionQuality(  self::$sharpen[$size]['quality']  );
		$im->writeImage( $path );
    }
    
    static function createThumbCropBySize($SIZE, $newOriginalImagePath, $newThumbImagePath)
    {
    	$isSharpen = self::isSharpenSetting();
    	if ( PDImageFSModel::isImagickSetting() ) {
    		$image = new Imagick($newOriginalImagePath);
		    
		    $W = $image->getImageWidth();
		    $H = $image->getImageHeight();
    		if ( $H > $SIZE && $W > $SIZE ) {
   		        $image->cropThumbnailImage($SIZE, $SIZE);
   		        self::writeIm_($image, $newThumbImagePath);
            }
            else if ( $H > $SIZE && $W < $SIZE ){
                $image->thumbnailImage(null, $SIZE);
                
				$canvas = new Imagick();
                $canvas->newImage( $SIZE, $SIZE, '#FFFFFF', 'png' );
                $geometry = $image->getImageGeometry();
                $x = ( $SIZE - $geometry['width'] ) / 2;
                $y = ( $SIZE - $geometry['height'] ) / 2;                    

                $canvas->compositeImage( $image, imagick::COMPOSITE_OVER, $x, $y );
                self::writeIm_($canvas, $newThumbImagePath);	                
	        }
	        else if ( $H < $SIZE ) {
            	$canvas = new Imagick();
                $canvas->newImage( $SIZE, $SIZE, '#FFFFFF', 'png' );
                $geometry = $image->getImageGeometry();
                $x = ( $SIZE - $geometry['width'] ) / 2;
                $y = ( $SIZE - $geometry['height'] ) / 2;                    

                $canvas->compositeImage( $image, imagick::COMPOSITE_OVER, $x, $y );
                self::writeIm_($canvas, $newThumbImagePath);	                
			}
			
			if ($isSharpen) 
    		if ( file_exists($newThumbImagePath) ) {
        	    $im = new Imagick( $newThumbImagePath );
                switch ($SIZE) {
                	case SIZE_970:
        	    		$im->sharpenImage(self::$sharpen[SIZE_970]['radius'], self::$sharpen[SIZE_970]['sigma']);
        	    		break;
        	    	case SIZE_750:
        	    		$im->sharpenImage(self::$sharpen[SIZE_750]['radius'], self::$sharpen[SIZE_750]['sigma']);
        	    		break;
        	    	case SIZE_512:
        	    		$im->sharpenImage(self::$sharpen[SIZE_512]['radius'], self::$sharpen[SIZE_512]['sigma']);
        	    		break;
        	    	case SIZE_256:
        	    		$im->sharpenImage(self::$sharpen[SIZE_256]['radius'], self::$sharpen[SIZE_256]['sigma']);
        	    		break;
        	    	case SIZE_144:
        	    		$im->sharpenImage(self::$sharpen[SIZE_144]['radius'], self::$sharpen[SIZE_144]['sigma']);
        	    		break;
        	    	case SIZE_96:
        	    		$im->sharpenImage(self::$sharpen[SIZE_96]['radius'], self::$sharpen[SIZE_96]['sigma']);
        	    		break;
        	    	default:
        	    		$im->sharpenImage(self::$sharpen[SIZE_970]['radius'], self::$sharpen[SIZE_970]['sigma']);
        	    		break;
        	    }               
                self::writeIm_($im, $newThumbImagePath, $SIZE);	                
                $im->destroy();
    		}
    	}
    	else {
    		$image = new WBSImageUtilsGd($newOriginalImagePath);
    		$quality = GD_COMPRESSION;
    		
    		$W = $image->getImageWidth();
		    $H = $image->getImageHeight();
    		
    		if ( $W >= $SIZE && $H >= $SIZE) {
				$image->cropThumbnailImage($SIZE, $SIZE);
		    	if ($isSharpen) {  		        
		            list($amount, $radius, $threshold, $quality) = self::$sharpenGd[$SIZE];  		        
					$image->unsharpMask($amount, $radius, $threshold);
		    	}
    		   	$image->writeImage( $newThumbImagePath, $quality );
		    }
		    else if ( $W >= $SIZE && $H < $SIZE ){
                $image->thumbnailImage($SIZE, null);
                    
                $canvas = new WbsImage(null, WbsImage::LIB_GD);
                $canvas->newImage( $SIZE, $SIZE, '#FFFFFF', 'png' );
                    
                $x = ( $SIZE - $image->getImageWidth() ) / 2;
                $y = ( $SIZE - $image->getImageHeight() ) / 2;

                $canvas->compositeImage( $image, null, $x, $y );
		    	if ($isSharpen) {  		        
		            list($amount, $radius, $threshold, $quality) = self::$sharpenGd[$SIZE];  		        
					$canvas->unsharpMask($amount, $radius, $threshold);
		    	}
                $canvas->writeImage( $newThumbImagePath, $quality  );
                $canvas->destroy();  	                                        
	        }
	        else if ( $W < $SIZE ) {
                $canvas = new WbsImage(null, WbsImage::LIB_GD);
                $canvas->newImage( $SIZE, $SIZE, '#FFFFFF', 'png' );
                    
                $x = ( $SIZE - $image->getImageWidth() ) / 2;
                $y = ( $SIZE - $image->getImageHeight() ) / 2;            

                $canvas->compositeImage( $image, null, $x, $y );
	       		if ($isSharpen) {  		        
		            list($amount, $radius, $threshold, $quality) = self::$sharpenGd[$SIZE];  		        
					$canvas->unsharpMask($amount, $radius, $threshold);
		    	}
                $canvas->writeImage( $newThumbImagePath, $quality  );
                $canvas->destroy();  	
			}
			$image->destroy();
			
    	}
    	
    }
    
    public function hasImageThumb($newOriginalImagePath) 
    {
    	$info = pathinfo($newOriginalImagePath);
        $newThumbImagePath = $this->getPublicDataAttachPath($this->albumId) ."/". $info['basename'];
    	return file_exists(self::prepareImagePath($newThumbImagePath, 144));
    }
    
    public function createThumbToImage($newOriginalImagePath) 
    {
    	$info = pathinfo($newOriginalImagePath);
        $newThumbImagePath = $this->getPublicDataAttachPath($this->albumId) ."/". $info['basename'];
        
        $this->checkPaths($this->getPublicDataAttachPath($this->albumId));

	    $user_settings_model = new UserSettingsModel();
	    $isSharpen = $user_settings_model->get("", "PD", "SharpenUsaged") == "1";
        
		if ( PDImageFSModel::isImagickSetting() ) {
			
		    $image = new Imagick($newOriginalImagePath);
		    $image->setImageFormat('jpeg');
		    
		    $isW = $image->getImageWidth() > $image->getImageHeight();
		    $W = $image->getImageWidth();
		    $H = $image->getImageHeight();
		    
		    $cloneImage = $image->clone();
		    
		    if ( $isW ) {		        
		        
		        if ( $W > SIZE_970 ) {
		        	if ( ($H * SIZE_970)/$W < 2 ) {
		        		$w970 = (2 * $W)/$H;
						$image->thumbnailImage($w970, 2);
		        	}
		        	else
	                	$image->thumbnailImage(SIZE_970, null);
                	$this->writeIm($image, self::prepareImagePath($newThumbImagePath, SIZE_970), SIZE_970);	                
		        }
		        
		        $image = $cloneImage->clone();
		        if ( $W > SIZE_750 ) {
		        	if ( ($H * SIZE_750)/$W < 2 ) {
		        		$w750 = (2 * $W)/$H;
						$image->thumbnailImage($w750, 2);
		        	}
		        	else
	                	$image->thumbnailImage(SIZE_750, null);

                	$this->writeIm($image, self::prepareImagePath($newThumbImagePath, SIZE_750), 750);	                
		        }
		        
		        $image = $cloneImage->clone();
		        if ( $W > 512 ) {
		        	if ( ($H * 512)/$W < 2 ) {
		        		$w512 = (2 * $W)/$H;
						$image->thumbnailImage($w512, 2);
		        	}
		        	else
	                	$image->thumbnailImage(512, null);
                	$this->writeIm($image, self::prepareImagePath($newThumbImagePath, 512), 512);	                
		        }
		        
		        $image = $cloneImage->clone();
		        $image1 = $cloneImage->clone();
		        $image2 = $cloneImage->clone();

		        if ( $W > 256 ) {
		        	if ( ($H * 256)/$W < 2 ) {
		        		$w256 = (2 * $W)/$H;
						$image->thumbnailImage($w256, 2);
		        	}
		        	else
	                	$image->thumbnailImage(256, null);
                	$this->writeIm($image, self::prepareImagePath($newThumbImagePath, 256), 256);	                
		        }
	            
	            if ( $W >= 144 && $H >= 144 ) {
	            	$image1->cropThumbnailImage(144, 144);
	            	
	            	$canvas = new Imagick();
                    $canvas->newImage( 144, 144, '#FFFFFF', 'jpg' );
	            	$canvas->compositeImage( $image1, imagick::COMPOSITE_OVER, 0, 0 );
    		        
                	$this->writeIm($canvas, self::prepareImagePath($newThumbImagePath, 144), 144);	                
	            }
	            else if ( $W >= 144 && $H < 144 ){
	            	if ( ($H * 144)/$W < 2 ) {
		        		$w1 = (2 * $W)/$H;
						$image1->thumbnailImage($w1, 2);
		        	}
		        	else
	                	$image1->thumbnailImage(144, null);
	            	
	                $canvas = new Imagick();
                    $canvas->newImage( 144, 144, '#FFFFFF', 'jpg' );
                    $geometry = $image1->getImageGeometry();
                    $x = ( 144 - $geometry['width'] ) / 2;
                    $y = ( 144 - $geometry['height'] ) / 2;

                    $canvas->compositeImage( $image1, imagick::COMPOSITE_OVER, $x, $y );
                	$this->writeIm($canvas, self::prepareImagePath($newThumbImagePath, 144), 144);	                
	            }
	            else if ( $W < 144 ) {
                    $canvas = new Imagick();
                    $canvas->newImage( 144, 144, '#FFFFFF', 'jpg' );
                    $geometry = $image1->getImageGeometry();
                    $x = ( 144 - $geometry['width'] ) / 2;
                    $y = ( 144 - $geometry['height'] ) / 2;                    

                    $canvas->compositeImage( $image1, imagick::COMPOSITE_OVER, $x, $y );
                	$this->writeIm($canvas, self::prepareImagePath($newThumbImagePath, 144), 144);	                
	            }
	            
	            if ( $W >= 96 && $H >= 96 ) {
    		        $image2->cropThumbnailImage(96, 96);
    		       	$canvas = new Imagick();
                    $canvas->newImage( 96, 96, '#FFFFFF', 'jpg' );
	            	$canvas->compositeImage( $image2, imagick::COMPOSITE_OVER, 0, 0 );
    		        
                	$this->writeIm($canvas, self::prepareImagePath($newThumbImagePath, 96), 96);	                
	            }
		        else if ( $W >= 96 && $H < 96 ){
	            	if ( ($H * 96)/$W < 2 ) {
		        		$w2 = (2 * $W)/$H;
						$image2->thumbnailImage($w2, 2);
		        	}
		        	else
	                	$image2->thumbnailImage(96, null);
		        		                
                    $canvas = new Imagick();
                    $canvas->newImage( 96, 96, '#FFFFFF', 'jpg' );
                    $geometry = $image2->getImageGeometry();
                    $x = ( 96 - $geometry['width'] ) / 2;
                    $y = ( 96 - $geometry['height'] ) / 2;

                    $canvas->compositeImage( $image2, imagick::COMPOSITE_OVER, $x, $y );
                    $this->writeIm($canvas, self::prepareImagePath($newThumbImagePath, 96), 96);	                
		        }
	            else if ( $W < 96 ) {
                    $canvas = new Imagick();
                    $canvas->newImage( 96, 96, '#FFFFFF', 'jpg' );
                    $geometry = $image2->getImageGeometry();
                    $x = ( 96 - $geometry['width'] ) / 2;
                    $y = ( 96 - $geometry['height'] ) / 2;                    

                    $canvas->compositeImage( $image2, imagick::COMPOSITE_OVER, $x, $y );
                	$this->writeIm($canvas, self::prepareImagePath($newThumbImagePath, 96), 96);	                
	            }
		    }
		    else {

		        if ( $H > SIZE_970 ) {
		        	if ( ($W * SIZE_970)/$H < 2 ) {
		        		$h970 = (2 * $H)/$W;
						$image->thumbnailImage(2, $h970);
		        	}
		        	else
	            		$image->thumbnailImage(null, SIZE_970);
		        	
    		        $image->thumbnailImage(null, SIZE_970);
                	$this->writeIm($image, self::prepareImagePath($newThumbImagePath, 970), 970);	                
		        }
		        
		        $image = $cloneImage->clone();
		        if ( $H > SIZE_750 ) {
		        	if ( ($W * SIZE_750)/$H < 2 ) {
		        		$h750 = (2 * $H)/$W;
						$image->thumbnailImage(2, $h750);
		        	}
		        	else
	            		$image->thumbnailImage(null, SIZE_750);
                	$this->writeIm($image, self::prepareImagePath($newThumbImagePath, 750), 750);	                
		        }
		        
		        $image = $cloneImage->clone();
		        if ( $H > 512 ) {
		        	if ( ($W * 512)/$H < 2 ) {
		        		$h512 = (2 * $H)/$W;
						$image->thumbnailImage(2, $h512);
		        	}
		        	else
	            		$image->thumbnailImage(null, 512);
                	$this->writeIm($image, self::prepareImagePath($newThumbImagePath, 512), 512);	                
		        }
		        $image = $cloneImage->clone();
		        $image1 = $cloneImage->clone();
		        $image2 = $cloneImage->clone();
		        
		        if ( $H > 256 ) {
		        	if ( ($W * 256)/$H < 2 ) {
		        		$h256 = (2 * $H)/$W;
						$image->thumbnailImage(2, $h256);
		        	}
		        	else
	            		$image->thumbnailImage(null, 256);
                	$this->writeIm($image, self::prepareImagePath($newThumbImagePath, 256), 256);	                
		        }

		        if ( $H >= 144 && $W >= 144 ) {
    		        $image1->cropThumbnailImage(144, 144);
    		        $canvas = new Imagick();
                    $canvas->newImage( 144, 144, '#FFFFFF', 'jpg' );
	            	$canvas->compositeImage( $image1, imagick::COMPOSITE_OVER, 0, 0 );
                	$this->writeIm($canvas, self::prepareImagePath($newThumbImagePath, 144), 144);	                
		        }
	            else if ( $H >= 144 && $W < 144 ){
	            	if ( ($W * 144)/$H < 2 ) {
		        		$h1 = (2 * $H)/$W;
						$image1->thumbnailImage(2, $h1);
		        	}
		        	else
	            		$image1->thumbnailImage(null, 144);
	                
                    $canvas = new Imagick();
                    $canvas->newImage( 144, 144, '#FFFFFF', 'jpg' );
                    $geometry = $image1->getImageGeometry();
                    $x = ( 144 - $geometry['width'] ) / 2;
                    $y = ( 144 - $geometry['height'] ) / 2;                    

                    $canvas->compositeImage( $image1, imagick::COMPOSITE_OVER, $x, $y );
                	$this->writeIm($canvas, self::prepareImagePath($newThumbImagePath, 144), 144);	                
	            }
	            else if ( $H < 144 ) {
                    $canvas = new Imagick();
                    $canvas->newImage( 144, 144, '#FFFFFF', 'jpg' );
                    $geometry = $image1->getImageGeometry();
                    $x = ( 144 - $geometry['width'] ) / 2;
                    $y = ( 144 - $geometry['height'] ) / 2;                    

                    $canvas->compositeImage( $image1, imagick::COMPOSITE_OVER, $x, $y );
                	$this->writeIm($canvas, self::prepareImagePath($newThumbImagePath, 144), 144);	                
	            }
		        
		        if ( $H > 96 && $W > 96 ) {
    		        $image2->cropThumbnailImage(96, 96);
    		        $canvas = new Imagick();
                    $canvas->newImage( 96, 96, '#FFFFFF', 'jpg' );
	            	$canvas->compositeImage( $image2, imagick::COMPOSITE_OVER, 0, 0 );
                	$this->writeIm($canvas, self::prepareImagePath($newThumbImagePath, 96), 96);	                
		        }
		        else if ( $H > 96 && $W < 96 ){
		        	if ( ($W * 96)/$H < 2 ) {
		        		$h2 = (2 * $H)/$W;
						$image2->thumbnailImage(2, $h2);
		        	}
		        	else
	                	$image2->thumbnailImage(null, 96);
	                
                    $canvas = new Imagick();
                    $canvas->newImage( 96, 96, '#FFFFFF', 'jpg' );
                    $geometry = $image2->getImageGeometry();
                    $x = ( 96 - $geometry['width'] ) / 2;
                    $y = ( 96 - $geometry['height'] ) / 2;

                    $canvas->compositeImage( $image2, imagick::COMPOSITE_OVER, $x, $y );
                	$this->writeIm($canvas, self::prepareImagePath($newThumbImagePath, 96), 96);	                
		        }
	            else if ( $H < 96 ) {
                    $canvas = new Imagick();
                    $canvas->newImage( 96, 96, '#FFFFFF', 'jpg' );
                    $geometry = $image2->getImageGeometry();
                    $x = ( 96 - $geometry['width'] ) / 2;
                    $y = ( 96 - $geometry['height'] ) / 2;                    

                    $canvas->compositeImage( $image2, imagick::COMPOSITE_OVER, $x, $y );
                	$this->writeIm($canvas, self::prepareImagePath($newThumbImagePath, 96), 96);	                
	            }
		        
		    }		   
		    $image->destroy();
		    $image1->destroy();
		    $image2->destroy();
		    	    
	        if ($isSharpen) {
    	        
    		    if ( file_exists(self::prepareImagePath($newThumbImagePath, SIZE_970)) ) {
        		    $im = new Imagick( self::prepareImagePath($newThumbImagePath, SIZE_970) );
        	        $im->sharpenImage(self::$sharpen[SIZE_970]['radius'], self::$sharpen[SIZE_970]['sigma']);
                	$this->writeIm($im, self::prepareImagePath($newThumbImagePath, SIZE_970), 970);	                
                    $im->destroy();
    		    }
    		    if ( file_exists(self::prepareImagePath($newThumbImagePath, SIZE_750)) ) {
        		    $im = new Imagick( self::prepareImagePath($newThumbImagePath, SIZE_750) );
        	        $im->sharpenImage(self::$sharpen[SIZE_750]['radius'], self::$sharpen[SIZE_750]['sigma']);
                	$this->writeIm($im, self::prepareImagePath($newThumbImagePath, SIZE_750), 750);	                
                    $im->destroy();
    		    }
    		    
    		    if ( file_exists(self::prepareImagePath($newThumbImagePath, 512)) ) {
        	        $im = new Imagick( self::prepareImagePath($newThumbImagePath, 512) );
        	        $im->sharpenImage(self::$sharpen[512]['radius'], self::$sharpen[512]['sigma']);
                	$this->writeIm($im, self::prepareImagePath($newThumbImagePath, 512), 512);	                
                    $im->destroy();
    		    }
                
                if ( file_exists(self::prepareImagePath($newThumbImagePath, 256)) ) {
                    $im = new Imagick( self::prepareImagePath($newThumbImagePath, 256) );
        	        $im->sharpenImage(self::$sharpen[256]['radius'], self::$sharpen[256]['sigma']);
                	$this->writeIm($im, self::prepareImagePath($newThumbImagePath, 256), 256);	                
                    $im->destroy();
                }
                
                if ( file_exists(self::prepareImagePath($newThumbImagePath, 144)) ) {
                    $im = new Imagick( self::prepareImagePath($newThumbImagePath, 144) );
        	        $im->sharpenImage(self::$sharpen[144]['radius'], self::$sharpen[144]['sigma']);
                	$this->writeIm($im, self::prepareImagePath($newThumbImagePath, 144), 144);	                
                    $im->destroy();
                }
    
                if ( file_exists(self::prepareImagePath($newThumbImagePath, 96)) ) {
                    $im = new Imagick( self::prepareImagePath($newThumbImagePath, 96) );
        	        $im->sharpenImage(self::$sharpen[96]['radius'], self::$sharpen[96]['sigma']);
                	$this->writeIm($im, self::prepareImagePath($newThumbImagePath, 96), 96);	                
                    $im->destroy(); 
                }

	        }
		} 
		else  {
		    $image = new WBSImageUtilsGd($newOriginalImagePath);
		    
		    $quality = GD_COMPRESSION;
		    
		    $isW = $image->getImageWidth() >= $image->getImageHeight();
		    $W = $image->getImageWidth();
		    $H = $image->getImageHeight();
		    if ( $isW ) {
    		    if ( $W > SIZE_970 ) {
    		        $image->thumbnailImage(SIZE_970, null);      
    		        if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['970'];  		        
						$image->unsharpMask($amount, $radius, $threshold);
	    			}	        
    		        $image->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_970), $quality );
		        }
    		    if ( $W > SIZE_750 ) {
    		        $image->thumbnailImage(SIZE_750, null);   
    		        if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['750'];  		        
						$image->unsharpMask($amount, $radius, $threshold);
	    			}	         
    		        $image->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_750), $quality );
		        }
		        
    		    if ( $W > SIZE_512 ) {
    		        $image->thumbnailImage(SIZE_512, null);
    		        if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['512'];  		        
						$image->unsharpMask($amount, $radius, $threshold);
	    			}	        
    		        $image->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_512), $quality );
    		        $image->destroy();
		        }
		        
		        if ( file_exists( self::prepareImagePath($newThumbImagePath, SIZE_512) ) ) {
    		        $image = new WBSImageUtilsGd(self::prepareImagePath($newThumbImagePath, SIZE_512));
    		        $image1 = new WBSImageUtilsGd(self::prepareImagePath($newThumbImagePath, SIZE_512));
    		        $image2 = new WBSImageUtilsGd(self::prepareImagePath($newThumbImagePath, SIZE_512));
		        }
		        else if ( file_exists( self::prepareImagePath($newThumbImagePath, SIZE_970) ) ) {
    		        $image = new WBSImageUtilsGd(self::prepareImagePath($newThumbImagePath, SIZE_970));
    		        $image1 = new WBSImageUtilsGd(self::prepareImagePath($newThumbImagePath, SIZE_970));
    		        $image2 = new WBSImageUtilsGd(self::prepareImagePath($newThumbImagePath, SIZE_970));
		        }
		        else {
    		        $image = new WBSImageUtilsGd($newOriginalImagePath);
    		        $image1 = new WBSImageUtilsGd($newOriginalImagePath);
    		        $image2 = new WBSImageUtilsGd($newOriginalImagePath);
		        }
		        
    		    if ( $W > SIZE_256) {
    		        $image->thumbnailImage(SIZE_256, null);
    		        if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['256'];  		        
						$image->unsharpMask($amount, $radius, $threshold);
	    			}	        
    		        $image->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_256), $quality );
    		        $image->destroy();
		        }
		        
    		    if ( $W >= SIZE_144 && $H >= SIZE_144) {
    		        $image1->cropThumbnailImage(SIZE_144, SIZE_144);
    		        if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['144'];  		        
						$image1->unsharpMask($amount, $radius, $threshold);
	    			}
    		        $image1->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_144), $quality );
    		        $image1->destroy();
		        }
		        else if ( $W >= SIZE_144 && $H < SIZE_144 ){
                    $image1->thumbnailImage(SIZE_144, null);
                    
                    $canvas = new WbsImage(null, WbsImage::LIB_GD);
                    $canvas->newImage( SIZE_144, SIZE_144, '#FFFFFF', 'png' );
                    
                    $x = ( SIZE_144 - $image1->getImageWidth() ) / 2;
                    $y = ( SIZE_144 - $image1->getImageHeight() ) / 2;

                    $canvas->compositeImage( $image1, null, $x, $y );
                   if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['144'];  		        
						$canvas->unsharpMask($amount, $radius, $threshold);
	    			}	        
                    $canvas->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_144), $quality  );
                    $image1->destroy();
                    $canvas->destroy();  	                                        
	            }
	            else if ( $W < SIZE_144 ) {
                    $canvas = new WbsImage(null, WbsImage::LIB_GD);
                    $canvas->newImage( SIZE_144, SIZE_144, '#FFFFFF', 'png' );
                    
                    $x = ( SIZE_144 - $image1->getImageWidth() ) / 2;
                    $y = ( SIZE_144 - $image1->getImageHeight() ) / 2;            

                    $canvas->compositeImage( $image1, null, $x, $y );
                    if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['144'];  		        
						$canvas->unsharpMask($amount, $radius, $threshold);
	    			}	        
                    $canvas->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_144), $quality  );
                    $image1->destroy();
                    $canvas->destroy();  	
	            }
	                   
		        
    		    if ( $W >= SIZE_96 && $H >= SIZE_96) {
    		        $image2->cropThumbnailImage(SIZE_96, SIZE_96);
    		        if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['96'];  		        
						$image2->unsharpMask($amount, $radius, $threshold);
	    			}	        
    		        $image2->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_96), $quality );
    		        $image2->destroy();
		        }
		        else if ( $W >= SIZE_96 && $H < SIZE_96 ){
                    $image2->thumbnailImage(SIZE_96, null);
                    
                    $canvas = new WbsImage(null, WbsImage::LIB_GD);
                    $canvas->newImage( SIZE_96, SIZE_96, '#FFFFFF', 'png' );
                    
                    $x = ( SIZE_96 - $image2->getImageWidth() ) / 2;
                    $y = ( SIZE_96 - $image2->getImageHeight() ) / 2;

                    $canvas->compositeImage( $image2, null, $x, $y );
                   if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['96'];  		        
						$canvas->unsharpMask($amount, $radius, $threshold);
	    			}	        
                    $canvas->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_96), $quality  );
                    $image2->destroy();
                    $canvas->destroy();                                        
	            }
	            else if ( $W < SIZE_96 ) {
                    $canvas = new WbsImage(null, WbsImage::LIB_GD);
                    $canvas->newImage( SIZE_96, SIZE_96, '#FFFFFF', 'png' );
                    
                    $x = ( SIZE_96 - $image2->getImageWidth() ) / 2;
                    $y = ( SIZE_96 - $image2->getImageHeight() ) / 2;            
                    
                    $canvas->compositeImage( $image2, null, $x, $y );
                    if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['96'];  		        
						$canvas->unsharpMask($amount, $radius, $threshold);
	    			}	        
                    $canvas->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_96), $quality  );
                    $image2->destroy();
                    $canvas->destroy();                
	            }
		    }
		    else 
		    {
		        
    		    if ( $H > SIZE_970 ) {
     		        $image->thumbnailImage(null, SIZE_970);    
     		        if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['970'];  		        
						$image->unsharpMask($amount, $radius, $threshold);
	    			}	        
    		        $image->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_970), $quality );
            	}
		         if ( $H > SIZE_750 ) {
     		        $image->thumbnailImage(null, SIZE_750);    
     		        if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['750'];  		        
						$image->unsharpMask($amount, $radius, $threshold);
	    			}	        
    		        $image->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_750), $quality );
            	}
    	        
    		    if ( $H > SIZE_512 ) {
    		        $image->thumbnailImage(null, SIZE_512);    	
    		        if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['512'];  		        
						$image->unsharpMask($amount, $radius, $threshold);
	    			}	          
    		        $image->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_512), $quality );
    		        $image->destroy();
    	        }
    	        
		        if ( file_exists( self::prepareImagePath($newThumbImagePath, SIZE_512) ) ) {
    		        $image = new WBSImageUtilsGd(self::prepareImagePath($newThumbImagePath, SIZE_512));
    		        $image1 = new WBSImageUtilsGd(self::prepareImagePath($newThumbImagePath, SIZE_512));
    		        $image2 = new WBSImageUtilsGd(self::prepareImagePath($newThumbImagePath, SIZE_512));
		        }
		        else if ( file_exists( self::prepareImagePath($newThumbImagePath, SIZE_970) ) ) {
    		        $image = new WBSImageUtilsGd(self::prepareImagePath($newThumbImagePath, SIZE_970));
    		        $image1 = new WBSImageUtilsGd(self::prepareImagePath($newThumbImagePath, SIZE_970));
    		        $image2 = new WBSImageUtilsGd(self::prepareImagePath($newThumbImagePath, SIZE_970));
		        }
		        else {
    		        $image = new WBSImageUtilsGd($newOriginalImagePath);
    		        $image1 = new WBSImageUtilsGd($newOriginalImagePath);
    		        $image2 = new WBSImageUtilsGd($newOriginalImagePath);
		        }
    	        		        
    	        if ( $H > SIZE_256 ) {
   		            $image->thumbnailImage(null, SIZE_256);
    		        if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['256'];  		        
						$image->unsharpMask($amount, $radius, $threshold);
	    			}	        
    		        $image->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_256), $quality );
    		        $image->destroy();
    	        }
    		    if ( $H > SIZE_144 ) {
    		        $image1->cropThumbnailImage(SIZE_144, SIZE_144);
    		        if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['144'];  		        
						$image1->unsharpMask($amount, $radius, $threshold);
	    			}	         
    		        $image1->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_144), $quality );
    		        $image1->destroy();
                }
		        else if ( $H > SIZE_144 && $W < SIZE_144 ){
                    $image1->thumbnailImage(null, SIZE_144);
                    
                    $canvas = new WbsImage(null, WbsImage::LIB_GD);
                    $canvas->newImage( SIZE_144, SIZE_144, '#FFFFFF', 'png' );
                    
                    $x = ( SIZE_144 - $image1->getImageWidth() ) / 2;
                    $y = ( SIZE_144 - $image1->getImageHeight() ) / 2;

                    $canvas->compositeImage( $image1, null, $x, $y );
                    if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['144'];  		        
						$canvas->unsharpMask($amount, $radius, $threshold);
	    			}	        
                    $canvas->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_144), $quality  );
                    $image1->destroy();	       
                    $canvas->destroy();                                 
                }
	            else if ( $H < SIZE_144 ) {
                    $canvas = new WbsImage(null, WbsImage::LIB_GD);
                    $canvas->newImage( SIZE_144, SIZE_144, '#FFFFFF', 'png' );
                    
                    $x = ( SIZE_144 - $image1->getImageWidth() ) / 2;
                    $y = ( SIZE_144 - $image1->getImageHeight() ) / 2;            
                    
                    $canvas->compositeImage( $image1, null, $x, $y );
                    if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['144'];  		        
						$canvas->unsharpMask($amount, $radius, $threshold);
	    			}	        
                    $canvas->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_144), $quality  );
                    $image1->destroy();	
                    $canvas->destroy();
	            }
                
    		    if ( $H > SIZE_96 ) {
                    $image2->cropThumbnailImage(SIZE_96, SIZE_96);
                    if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['96'];  		        
						$image2->unsharpMask($amount, $radius, $threshold);
	    			}	        
                    $image2->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_96), $quality );
                    $image2->destroy();
		        }
		        else if ( $H > SIZE_96 && $W < SIZE_96 ){
                    $image2->thumbnailImage(null, SIZE_96);
                    
                    $canvas = new WbsImage(null, WbsImage::LIB_GD);
                    $canvas->newImage( SIZE_96, SIZE_96, '#FFFFFF', 'png' );
                    
                    $x = ( SIZE_96 - $image2->getImageWidth() ) / 2;
                    $y = ( SIZE_96 - $image2->getImageHeight() ) / 2;

                    $canvas->compositeImage( $image2, null, $x, $y );
                    if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['96'];  		        
						$canvas->unsharpMask($amount, $radius, $threshold);
	    			}	        
                    $canvas->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_96), $quality  );
                    $image2->destroy();	
                    $canvas->destroy();	                                        
	            }
	            else if ( $W < SIZE_96 ) {
                    $canvas = new WbsImage(null, WbsImage::LIB_GD);
                    $canvas->newImage( SIZE_96, SIZE_96, '#FFFFFF', 'png' );
                    
                    $x = ( SIZE_96 - $image2->getImageWidth() ) / 2;
                    $y = ( SIZE_96 - $image2->getImageHeight() ) / 2;            
                    
                    $canvas->compositeImage( $image2, null, $x, $y );
                    if ($isSharpen) {  		        
		            	list($amount, $radius, $threshold, $quality) = self::$sharpenGd['96'];  		        
						$canvas->unsharpMask($amount, $radius, $threshold);
	    			}	        
                    $canvas->writeImage( self::prepareImagePath($newThumbImagePath, SIZE_96), $quality  );
                    $image2->destroy();	
                    $canvas->destroy();                
	            }
		    }
		}
    }

    private function isImagick() {
        return extension_loaded( "imagick" );
    }

    private function isGd() {
        return extension_loaded( "gd" );
    }
    
	static function filePath($filePath)
	{
	 $fileParts = pathinfo($filePath);
	
	 if(!isset($fileParts['filename']))
	 {$fileParts['filename'] = substr($fileParts['basename'], 0, strrpos($fileParts['basename'], '.'));}
	 
	 return $fileParts;
	}

    static function prepareImagePath ($path, $size) {
        $pathInfo = self::filePath($path);
        return $pathInfo['dirname'] ."/". $pathInfo["filename"] .".". $size .".". $pathInfo["extension"];
    }
    
    /**
     * @param WbsImage $thumb
     * @param unknown_type $thumbPath
     * @param unknown_type $size
     * @param unknown_type $crop
     * @return unknown
     */
    private function createThumb($thumb, $thumbPath, $size, $crop = false) 
    {
        if ( $thumb->getImageWidth() > $thumb->getImageHeight() ) {
        	if($crop)
        		$thumb->cropThumbnailImage($size, $size);
        	else
            	$thumb->thumbnailImage($size, null);
        }
        else {
        	if($crop)
        		$thumb->cropThumbnailImage($size, $size);
        	else
        		$thumb->thumbnailImage(null, $size);
        }
        $pathInfo = pathinfo($thumbPath);
        $thumbPath = $pathInfo['dirname'] ."/". $pathInfo["filename"] .".". $size .".". $pathInfo["extension"];
        
        $thumb->writeImage($thumbPath);
           
        return $thumbPath;
    }
    
    public function uploadAll($albumId)
    {
        $this->albumId = $albumId;
            
        $return = array();
        foreach ( $_FILES["Filedata"]["tmp_name"] as $key => $val ) {
            
            $file = array(
                "tmp_name" => $_FILES["Filedata"]["tmp_name"][$key],
            	"size" => $_FILES["Filedata"]["size"][$key],
            	"name" => $_FILES["Filedata"]["name"][$key],
                "type" => $_FILES["Filedata"]["type"][$key],
            	"error" => $_FILES["Filedata"]["error"][$key],
            );
            
            if (is_uploaded_file($file["tmp_name"])) {
            	
            	$file["name"] = preg_replace('~\.jpeg$~i', '.jpg', $file["name"]);
                
                $fileParam = pathinfo($file['name']);
                $fileExt = mb_strtolower($fileParam['extension']);
                
                if( false === array_search($fileExt, array('jpg', 'jpeg', 'jpe', 'gif', 'png')) )
                    continue;
                    
                try {
                    $info = getimagesize( $file["tmp_name"] );
                    if (!$info)
                    	continue;
                    	
                    if ( $info['mime'] != 'image/png' && $info['mime'] != 'image/jpeg' && $info['mime'] != 'image/gif' )
                    	continue;
                    if ( Wbs::isHosted() && 
                    	 $info[0]*$info[1] > 25000000 
                    	)
                    	continue;
                }
                catch (Exception $e) {
                    continue;
                }

                $dm = new DiskUsageModel();
                if ( Limits::get('AA', 'SPACE') == 0 || Limits::get('AA', 'SPACE') * 1024 * 1024 > $dm->getAll() + $file['size']  ) {
                    $dm->add('$SYSTEM', 'PD', $file['size'] );
                    $name = StringUtils::translit($file['name']);
                    $name = preg_replace('~[^a-z0-9\-\_\.]~i', '', $name);
                    
                    $return[] = $this->addImage($file['tmp_name'], $name) ;
                }
            }
        }
        if ( count( $return ) > 0 )
            return $return;
    }
    
    public function upload($albumId) {
        $this->albumId = $albumId;
        $file = array(
            "tmp_name" => $_FILES["Filedata"]["tmp_name"],
        	"size" => $_FILES["Filedata"]["size"],
        	"name" => $_FILES["Filedata"]["name"],
            "type" => $_FILES["Filedata"]["type"],
        	"error" => $_FILES["Filedata"]["error"],
        );
        
        if ( $file['error'] == self::UPLOAD_ERR_OK ) {
            if (is_uploaded_file($file["tmp_name"])) {
            	
            	$file["name"] = preg_replace('~\.jpeg$~i', '.jpg', $file["name"]);
            	
                $fileParam = pathinfo($file['name']);
                $fileExt = mb_strtolower($fileParam['extension']);
                
                if( false === array_search($fileExt, array('jpg', 'jpeg', 'jpe', 'gif', 'png')) )
                    throw new RuntimeException ("File type not supported.");
                    
                try {
                    $info = getimagesize( $file["tmp_name"] );
                }
                catch (Exception $e) {
                    throw new RuntimeException ("File type not supported.");
                }
                if (!$info)
                    throw new RuntimeException ("File type not supported.");
                if ( $info['mime'] != 'image/png' && $info['mime'] != 'image/jpeg' && $info['mime'] != 'image/gif' )
                    throw new RuntimeException ("File type not supported.");
                    
                if ( Wbs::isHosted() && 
                    	 $info[0]*$info[1] > 25000000
                   	)
                    throw new RuntimeException ("Allowed memory");
                

                
                $dm = new DiskUsageModel();
                if ( Limits::get('AA', 'SPACE') == 0 || Limits::get('AA', 'SPACE') * 1024 * 1024 > $dm->getAll() + $file['size']  ) {
                    
                    $dm->add('$SYSTEM', 'PD', $file['size'] );
                    $name = StringUtils::translit($file['name']);
                    $name = preg_replace('~[^a-z0-9\-\_\.]~i', '', $name);
                    
                    $return = $this->addImage($file['tmp_name'], $name) ;
                }
                else {
                    throw new RuntimeException ("Checked disk space.");
                }
            }
            else {
                throw new RuntimeException ("File is not uploaded.");
            }
        }
        else if ($file['error'] == self::UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException ("File is not uploaded.");
        }
        else if ($file['error'] == self::UPLOAD_ERR_INI_SIZE) {
            throw new RuntimeException ("File is not uploaded.");
        }
        else {
            throw new RuntimeException ("File is not uploaded.");
        }
        return $return;
    }
    
    public function uploadSimple($albumId, $path)
    {
    	$this->albumId = $albumId;
    	return $this->addImage($path) ;
    }
    
    private function getUniqueFileName($fileName) 
    {
    	$param = array();
        preg_match('~(.+)\.(jpg|gif|png)~i', $fileName, $param);
        
        $name = $param[1];
        $ext = $param[2];
        
        return $name ."_". mb_substr( md5(time()), 0, 5 ) .".jpg";//. $ext;
    }
    
    public function copyImage($fileName, $oldIdAlbum, $newIdAlbum) {
        $this->copyFile($fileName, $this->getDataAttachPath( $oldIdAlbum ), $this->getDataAttachPath( $newIdAlbum ));
        
        $this->copyFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_970.".$1", $fileName), $this->getPublicDataAttachPath( $oldIdAlbum ), $this->getPublicDataAttachPath( $newIdAlbum ));
        $this->copyFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_750.".$1", $fileName), $this->getPublicDataAttachPath( $oldIdAlbum ), $this->getPublicDataAttachPath( $newIdAlbum ));
        $this->copyFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_512.".$1", $fileName), $this->getPublicDataAttachPath( $oldIdAlbum ), $this->getPublicDataAttachPath( $newIdAlbum ));
        $this->copyFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_256.".$1", $fileName), $this->getPublicDataAttachPath( $oldIdAlbum ), $this->getPublicDataAttachPath( $newIdAlbum ));
        $this->copyFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_144.".$1", $fileName), $this->getPublicDataAttachPath( $oldIdAlbum ), $this->getPublicDataAttachPath( $newIdAlbum ));
        $this->copyFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_96.".$1", $fileName), $this->getPublicDataAttachPath( $oldIdAlbum ), $this->getPublicDataAttachPath( $newIdAlbum ));
    }
    private function copyFile($fileName, $oldPath, $newPath)
    {
        if ( file_exists($oldPath."/".$fileName) &&
             $this->checkPaths($newPath) ) 
        {
            copy( $oldPath."/".$fileName,
                  $newPath."/".$fileName );
        } 
    }
    
    public function getImageSize($fileName, $albumId) {
    	return getimagesize( realpath($this->getDataAttachPath( $albumId )."/". $fileName));
    }

    public function moveImage($fileName, $oldIdAlbum, $newIdAlbum) {
        $this->moveFile($fileName, $this->getDataAttachPath( $oldIdAlbum ), $this->getDataAttachPath( $newIdAlbum ));
        
        $this->moveFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_970.".$1", $fileName), $this->getPublicDataAttachPath( $oldIdAlbum ), $this->getPublicDataAttachPath( $newIdAlbum ));
        $this->moveFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_750.".$1", $fileName), $this->getPublicDataAttachPath( $oldIdAlbum ), $this->getPublicDataAttachPath( $newIdAlbum ));
        $this->moveFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_512.".$1", $fileName), $this->getPublicDataAttachPath( $oldIdAlbum ), $this->getPublicDataAttachPath( $newIdAlbum ));
        $this->moveFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_256.".$1", $fileName), $this->getPublicDataAttachPath( $oldIdAlbum ), $this->getPublicDataAttachPath( $newIdAlbum ));
        $this->moveFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_144.".$1", $fileName), $this->getPublicDataAttachPath( $oldIdAlbum ), $this->getPublicDataAttachPath( $newIdAlbum ));
        $this->moveFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_96.".$1", $fileName), $this->getPublicDataAttachPath( $oldIdAlbum ), $this->getPublicDataAttachPath( $newIdAlbum ));
    }
    private function moveFile($fileName, $oldPath, $newPath)
    {
        if ( file_exists($oldPath."/".$fileName) &&
             $this->checkPaths($newPath) ) 
        {
            copy( $oldPath."/".$fileName,
                  $newPath."/".$fileName );
            unlink ( $oldPath ."/". $fileName );
        } 
    }
    
    public function removeImage($fileName, $idAlbum)
    {
        $this->removeFile($fileName, $this->getDataAttachPath($idAlbum) );
        
        $this->removeFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_970.".$1", $fileName), $this->getPublicDataAttachPath($idAlbum) );
        $this->removeFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_750.".$1", $fileName), $this->getPublicDataAttachPath($idAlbum));
        $this->removeFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_512.".$1", $fileName), $this->getPublicDataAttachPath($idAlbum));
        $this->removeFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_256.".$1", $fileName), $this->getPublicDataAttachPath($idAlbum));
        $this->removeFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_144.".$1", $fileName), $this->getPublicDataAttachPath($idAlbum));
        $this->removeFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_96.".$1", $fileName), $this->getPublicDataAttachPath($idAlbum));
    }
    private function removeFile($fileName, $path)
    {
    	if (file_exists( $path ."/". $fileName ))        
            unlink ( $path ."/". $fileName );     
    }
    
    public function removeThumb($fileName, $idAlbum)
    {
        $this->removeFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_970.".$1", $fileName), $this->getPublicDataAttachPath($idAlbum) );
        $this->removeFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_750.".$1", $fileName), $this->getPublicDataAttachPath($idAlbum));
        $this->removeFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_512.".$1", $fileName), $this->getPublicDataAttachPath($idAlbum));
        $this->removeFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_256.".$1", $fileName), $this->getPublicDataAttachPath($idAlbum));
        $this->removeFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_144.".$1", $fileName), $this->getPublicDataAttachPath($idAlbum));
        $this->removeFile(preg_replace('~\.(jpg|gif|png)~i', ".".SIZE_96.".$1", $fileName), $this->getPublicDataAttachPath($idAlbum));
    }
    
    public function createAlbum($albumId)  
    {
    	if ( is_numeric($albumId) ) {
    		if ( file_exists( $this->getDataAttachPath($albumId) ) )
    			self::mkdir( $this->getDataAttachPath($albumId) );
    		if ( file_exists( $this->getPublicDataAttachPath($albumId) ) )
    			self::mkdir( $this->getPublicDataAttachPath($albumId) );
    	}
    }
    
    public function removeAlbum($albumId)
    {
    	if ( is_numeric($albumId) ) {
			if ( file_exists( $this->getDataAttachPath($albumId) ) )
    			$this->removeDir( $this->getDataAttachPath($albumId) );
    		if ( file_exists( $this->getPublicDataAttachPath($albumId) ) )
    			$this->removeDir( $this->getPublicDataAttachPath($albumId) );
    	}
    }
    
    private function removeDir($path) 
    {
		$dir = opendir($path);
		while(($file = readdir($dir))) {
			if ( is_file ($path."/".$file)) {
				unlink ($path."/".$file);
			}
		}
		closedir ($dir);
		rmdir ($path);    		
    }
    
    public function cropImage($albumId, $filename, $w, $h, $x, $y)
    {
    	$imagePath = $this->checkAndGetFilePath($this->getDataAttachPath($albumId) . '/' . $filename);
    	$this->albumId = $albumId;
    	
    	$oldSize = filesize($imagePath);
    	
    	$image = new PDWbsImage($imagePath);
    	$image->cropImage($w, $h, $x, $y );
    	$image->writeImage($imagePath);
    	
    	$this->removeThumb($filename, $albumId);
    	$image->destroy();
    	
    	$this->createThumbToImage($imagePath);
    	
    	return array(
    		'new' => filesize($imagePath),
    		'old' => $oldSize
    	);
    	
    }
    
    public function rotateImage($albumId, $filename, $rotate)
    {
    	$imagePath = $this->checkAndGetFilePath($this->getDataAttachPath($albumId) . '/' . $filename);
    	
    	$this->albumId = $albumId;
    	
    	if ( !PDImageFSModel::isImagickSetting() ) {
    	    $rotate *= -1;
    	}
    	
    	$image = new PDWbsImage($imagePath);
    	$image->rotateImage( $rotate );
    	$image->writeImage($imagePath);
    	
    	if ( PDImageFSModel::isImagickSetting() )
    	    $return = array( 'width' => $image->getImageWidth(), 'height' => $image->getImageHeight());
    	else
    	    $return = array( 'width' => $image->getImageHeight(), 'height' => $image->getImageWidth());
    	$image->destroy();

    	$this->removeThumb($filename, $albumId);
    	
//    	$newThumbImagePath = $this->getPublicDataAttachPath($albumId) ."/". $filename;
    	
    	$this->createThumbToImage($imagePath);
		return $return;
    }
    
    public function checkAndGetFilePath($path)
    {
    	if ( !file_exists($path) )
    		if ( file_exists( iconv("UTF-8", "WINDOWS-1251", $path) ) )
    			$path =  iconv("UTF-8", "WINDOWS-1251", $path);
    	return $path;
    } 
    
    public function setAlbumThumb($albumId, $imageName)
    {    	
    	$imagePath = $this->checkAndGetFilePath($this->getDataAttachPath($albumId) . '/' . $imageName);
    	
    	self::mkdir($this->getPublicDataAttachPath($albumId));
    	
    	if ( self::isImagickSetting()) {
    	    $imageLib = new Imagick($imagePath);
			$maska = new Imagick('./img/mask.png');
			
			$canvas = new Imagick();
			
			$canvas->newImage( 170, 173, new ImagickPixel('#FFFFFF'), 'jpg' );
			
			$imageLib->cropThumbnailImage(142, 143);
            
			$this->checkPaths($this->getPublicDataAttachPath($albumId));
			$canvas->compositeImage( $imageLib, imagick::COMPOSITE_OVER, 17, 10 );
			$canvas->sharpenImage(0.4, 0.2);
			
			$canvas->compositeImage( $maska, imagick::COMPOSITE_OVER, 0, 0 );
			$canvas->setImageCompression(imagick::COMPRESSION_JPEG);
    		$canvas->setImageCompressionQuality(  96  );
			$canvas->writeImage($this->getPublicDataAttachPath($albumId).'/thumb.jpg');	
				   
			//frontend thumbnail
            $im = new Imagick($imagePath);
            $im->cropThumbnailImage(256,192);
            $im->writeImage($this->getPublicDataAttachPath($albumId).'/thumbFront.jpg');
            $im->destroy();
            
            $im = new Imagick($this->getPublicDataAttachPath($albumId).'/thumbFront.jpg');
            $im->sharpenImage(0.4, 0.2);
            $im->setImageCompression(imagick::COMPRESSION_JPEG);
    		$im->setImageCompressionQuality(  96  );
            $im->writeImage($this->getPublicDataAttachPath($albumId).'/thumbFront.jpg');
            
    	}
    	else {
    	    $canva = imagecreatetruecolor( 170, 173 );
    	    $bgColor = imagecolorallocate($canva, 255,255,255);
    	    imagefill($canva, 0, 0, $bgColor);
    	    
			$maska = imagecreatefrompng('./img/mask.png');

			if ( function_exists('imagecopyresampled') )
    		    imagecopyresampled ( $canva, $maska, 0, 0, 0, 0, 170, 173, 170, 173 );
    		else
    		    imagecopyresized ( $canva, $maska, 0, 0, 0, 0, 170, 173, 170, 173 );
    		    
    		imagejpeg($canva, $this->getPublicDataAttachPath($albumId).'/thumb.jpg' );
    		imagedestroy($maska);
    		
//    		$img = imagecreatefromjpeg($imagePath);
    	    $info = getimagesize( $imagePath );
			switch ($info[2]) {
		        case 1:
		            // Create recource from gif image
					$img = imagecreatefromgif( $imagePath );
		            break;
		        case 2:
		            // Create recource from jpg image
					$img = imagecreatefromjpeg( $imagePath );
					
		            break;
		        case 3:
		            // Create resource from png image
					$img = imagecreatefrompng( $imagePath );
		            break;
		        case 6:
		            // Create recource from bmp image imagecreatefromwbmp
					$img = imagecreatefromwbmp( $imagePath );
		            break;
		        default:
		            break;
			}
    		
    		$width = imagesx($img);
    		$height = imagesy($img);
    		
    		
    		
    		$w = 142;
    		$h = 142;
    	    $k = $width / $height;
	
			if ( $h - $w / $k < $w - $h * $k ) {
				$nW = $w;
				$nH = (int) ceil($w / $k);
				
				$x = 0;
				$y =  ( $nH - $h )/2 ;
			}
			else {
				$nW = (int) ceil($k * $h);
				$nH = $h;
				
				$x =  ( $nW - $w )/2 ;
				$y = 0;
			}
			
			$canva2 = imagecreatetruecolor( 142, 142 );
			$bgColor = imagecolorallocate($canva2, 255,255,255);
    	    imagefill($canva2, 0, 0, $bgColor);
    		
    		//imagecopyresampled ( $canva, $img, 17, 10, 0, 0, 142, 143, imagesx($img), imagesy($img) );
    		if ( function_exists('imagecopyresampled') ) {
				imagecopyresampled ( $canva2, $img, -$x, -$y, 0, 0, $nW, $nH, $width  , $height );
				
				imagecopyresampled ( $canva, $canva2, 17, 10, 0, 0, 142, 142, 142, 142 );
    		}
			else {
				imagecopyresized ( $canva2, $img, -$x, -$y, 0, 0, $nW, $nH, $width , $height );
				imagecopyresized ( $canva, $canva2, 17, 10, 0, 0, 142, 142, 142, 142 );
			}
				
			$canva = WBSImageUtilsGd::unsharpMaskStatic($canva, 35, 0.5, 3);
    		imagejpeg($canva, $this->getPublicDataAttachPath($albumId).'/thumb.jpg' , 96);

    		imagedestroy($canva2);
    		imagedestroy($img);
    		imagedestroy($canva);
			
    		$canva = imagecreatetruecolor( 256, 192 );
//    		$img = imagecreatefromjpeg($imagePath);
    	    $info = getimagesize( $imagePath );
			switch ($info[2]) {
		        case 1:
		            // Create recource from gif image
					$img = imagecreatefromgif( $imagePath );
		            break;
		        case 2:
		            // Create recource from jpg image
					$img = imagecreatefromjpeg( $imagePath );
					
		            break;
		        case 3:
		            // Create resource from png image
					$img = imagecreatefrompng( $imagePath );
		            break;
		        case 6:
		            // Create recource from bmp image imagecreatefromwbmp
					$img = imagecreatefromwbmp( $imagePath );
		            break;
		        default:
		            break;
			}
    		
    	    $width = imagesx($img);
    		$height = imagesy($img);
    		
    		$w = 256;
    		$h = 193;
    	    $k = $width / $height;
	
			if ( $h - $w / $k < $w - $h * $k ) {
				$nW = $w;
				$nH = (int) ceil($w / $k);
				
				$x = 0;
				$y =  ( $nH - $h )/2 ;
			}
			else {
				$nW = (int) ceil($k * $h);
				$nH = $h;
				
				$x =  ( $nW - $w )/2 ;
				$y = 0;
			}
			
    	    if ( function_exists('imagecopyresampled') ) {
				imagecopyresampled ( $canva, $img, -$x, -$y, 0, 0, $nW, $nH, $width  , $height );
    		}
			else {
				imagecopyresized ( $canva, $img, -$x, -$y, 0, 0, $nW, $nH, $width , $height );
			}

			$canva = WBSImageUtilsGd::unsharpMaskStatic($canva, 40, 0.5, 3);
			imagejpeg($canva, $this->getPublicDataAttachPath($albumId).'/thumbFront.jpg' , 95);
    	}
    }
    
    static public function isImagickSetting() {
    	$user_settings_model = new UserSettingsModel();
    	
    	if ( Wbs::isHosted() && $user_settings_model->get("", "PD", "ImageLib") != 'gd' && self::isImagick_() )
    		return true;
        	    
        return $user_settings_model->get("", "PD", "ImageLib") == 'imagick' && self::isImagick_();    
    }
    
    static public function isImagick_() {
        return extension_loaded( "imagick" );
    }
    
	static function mkdir($dir, $mode = 0775, &$errStr = '', $basedir = null )
     {
      //$currentDir=getcwd();
      if ( is_null($basedir) ){
       $basedir = WBS_DIR;
      }
      $mode &= 0775;
    
      $basedir = trim(preg_replace('@([/\\\\]+)@','/',$basedir.'/'));
      $dir = trim(preg_replace(array('@([/\\\\]+)@','@[/\\\\]$@'),array('/',''),$dir));
    
      if(strpos($dir,$basedir) === 0){
       $dir = substr( $dir,strlen( $basedir ) );
      }else{
       $basedir = '';
      }
    
      
      
      $path_parts = explode('/', $dir);
      $current_path = preg_replace('@[/\\\\]$@','',$basedir);
      $oldMask = @umask(0);
      foreach ($path_parts as $path_part) {
       if(strlen($path_part)){
        $current_path.=($current_path?'/':'').$path_part;
        if (file_exists($current_path)){
         if(!is_dir($current_path)){
          $errStr = sprintf( "Unable to create directory: %s is file", $current_path );
          break;
         }
        }else{
         if(!@mkdir($current_path, $mode)){
          $errStr = sprintf( "Unable to create directory %s", $current_path );
          break;
         }
          
        }
       }
      }
      @umask($oldMask);
      //chdir( $currentDir );
      return (strlen($errStr)>0)?false:true;
     }
    
}

?>
