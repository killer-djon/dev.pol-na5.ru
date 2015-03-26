<?php
/**
 * Class uses syntax Imagick lib for working with images.
 * @see http://ru2.php.net/imagick
 * @see http://ru2.php.net/gd
 */
class ImageLib 
{
    private $lib = null;
    private $isImagick = false;
    private $isGd = false;
    
    const LIB_GD = "gd"; 
    const LIB_IMAGICK = "imagick";
    
    public function __construct($filePath = null, $lib = "imagick") 
    {
        if (!is_null($filePath))
            $this->lib = $this->openImage($filePath, $lib);
    }
    /**
     * @param string $filePath
     * @return ImageLib
     */
    public function openImage($file, $lib)
    {
    	if ($lib == self::LIB_IMAGICK) {
	        if ( extension_loaded( "imagick" ) ) {
	            $this->isImagick = true;
	            return new Imagick($file);
	        }
	        else if( extension_loaded( "gd" ) ) {
	            $this->isGd = true;
	            return new ImageGdLib($file);
	        }
    	}
    	if ($lib == self::LIB_GD) {
    		if( extension_loaded( "gd" ) ) {
	            $this->isGd = true;
	            return new ImageGdLib($file);
	        }
	        else if ( extension_loaded( "imagick" ) ) {
	            $this->isImagick = true;
	            return new Imagick($file);
	        }
    	}
        return false;
    }
    /**
     * @return Boolen
     */
    public function isImagick()
    {
        return $this->isImagick;
    }
    /**
     * @return Boolen
     */
    public function isGd()
    {
        return $this->isGd;
    }
    /**
     * @return mixed - Imagick or Gd lib
     * @see isImagick() and isGd()
     */
    public function getLib()
    {
        $this->lib;
    }
    /**
     * @param mixed $lib - Imagick or Gd lib
     */
    public function setLib($lib)
    {
        $this->lib = $lib;
    }
    
	/**
     * @param int $width
     * @param int $height
     * @return ImageLib
     */    
    public function thumbnailImage($size) 
    {
    	$this->lib->thumbnailImage($size,$size);
//		$width = $this->getImageWidth();
//		$height = $this->getImageHeight();
//		
//		if ( $this->isImagick() )
//		{
//	    	$x = 0;
//	    	$y = 0;
//			//ресайз и обрезание
//			if ( $width > $height ) {
//				$this->lib->thumbnailImage(true, $height);
//				$x = ($height - $size)/2;
//			}
//			else {
//				$this->lib->thumbnailImage($width, true);
//				$y = ($width - $size)/2;
//			}
//	    	
//			$this->lib->setImagePage($size, $size, $x, $y);
//		}
        return $this;
    }
    
    /**
     * @param int $width
     * @param int $height
     * @return ImageLib
     */
    public function cropThumbnailImage($width, $height)
    {
    	if ( $this->isImagick() )
        	$this->lib->cropThumbnailImage($width, $height);
        else 	
			$this->lib->thumbnailImage($width, $height);
        return $this;        
    }
    
    public function cropImage($width  , $height  , $x  , $y)
    {
    	if ( $this->isImagick() )
        	$this->lib->cropImage($width  , $height  , $x  , $y);
			
        return $this; 
    }
    
    /**
     * @param int $degrees
     * @return ImageLib
	 */
    public function rotateImage($degrees)
    {
    	if( $this->isImagick )
        	$this->lib->rotateImage(new ImagickPixel(), $degrees);
        else
        	$this->lib->rotateImage($degrees);
        return $this;
    }

    /**
     * @return int
     */
    public function getImageWidth()
    {
        return $this->lib->getImageWidth();
    }
    /**
     * @return ImageLib
     */
    public function clone_()
    {         
        $image = new self();
        $image->setLib( $this->lib->clone() );
        return $image;
    }
    
    /**
     * @return int
     */
    public function getImageHeight()
    { 
        return $this->lib->getImageHeight(); 
    }

    /**
     * @param string $filename
     * @return ImageLib
     **/
    public function writeImage($filename) 
    {
        $this->lib->writeImage($filename); 
        return $this;
    }

    public function destroy()
    {
    	$this->lib->destroy();
    }
    
}

?>