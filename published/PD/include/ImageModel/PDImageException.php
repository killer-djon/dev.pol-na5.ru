<?php

    class PDImageException extends RuntimeException
    {
        const IMAGE_NO_FOUND = 1;
        
        public function __construct($message, $code) {
        	parent::__construct($message, $code);
    	}
    }
?>