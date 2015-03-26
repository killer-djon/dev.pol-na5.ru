<?php

class PDException extends RuntimeException 
{	
	const NOT_EMPTY = 1;
	const INVALID_PARAM = 2;
	const PARAMS_NOT_FOUND = 3;
	
	public function __construct($message, $code) {
       	parent::__construct($message, $code);
   	}
}


?>