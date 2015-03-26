<?php


    class PDWbsImage extends WbsImage 
    {
        public function __construct($filePath = null) 
        {
            $this->lib = $this->openImage($filePath);
            $this->filePath = $filePath;
        }
        /**
         * @param string $filePath
         * @return WbsImage
         */
        public function openImage($file = null)
        {    	
        	
        	if (PDImageFSModel::isImagickSetting()) {
	            $this->isImagick = true;
	            return (is_null($file)) ? new Imagick() : new Imagick($file);
        	}
        	else {
	            $this->isGd = true;
	            return new WbsImageGd($file);
        	}
        }
    }
?>