<?php

/**
 * Error handling function with use of FirePHP
 * @see http://arbit.pl/2008/06/09/ultimate-error-handler-with-use-of-firephp/
 */

class ErrorManager {

    /**
     * Error handler options

     * file - log file
     * ignore_notices - if set to true Error handler ignores notices
     * ignore_warnings - if set to true Error handler ignores warnings
     * firebug - if set to true Error handler send error to firebug
     */
    public static $options = array();

    private function __construct () {
    }

    /**
     * Factory method which setup handlers
     *
     * @param array $options Options for Error handler
     */
    static public function create($options) {
        self::$options = $options;

        $flags = E_ALL;
        if (!empty($options['ignore_notices'])) {
            $flags = $flags ^ E_NOTICE;
            $flags = $flags ^ E_USER_NOTICE;
        }
        if (!empty($options['ignore_warnings'])) {
            $flags = $flags ^ E_WARNING;
            $flags = $flags ^ E_USER_WARNING;
        }
        if (!empty($options['firebug'])) {
        	self::$firephp = FirePHP::getInstance(true);
        }
        
        set_error_handler('xxxxxx', $flags);
        
//        set_exception_handler(array('ErrorManager', "exceptionHandler"));
    }

    /**
     * Exception handler
     *
     * @param Exception $ex Exception
     */
    static public function exceptionHandler($ex) {
        
        exit(1);
    }

    public static $firephp = null;
    /**
     * Error handler
     *
     * @param int $errno Error code
     * @param string $errstr Error message
     */
    static public function errorHandler($args) {
        if (!empty(self::$options['firebug'])) {
			self::$firephp->fb(func_get_args(), FirePHP::ERROR);
        }

    	if (!empty(self::$options['file'])) {
			$fp = fopen(self::$options['file'], "a+");
			if (flock($fp, LOCK_EX)) {
			    fwrite($fp, "----\n". var_export( func_get_args(), true ) ."\n\n");
			    flock($fp, LOCK_UN);
			}
			fclose($fp);
        }
     	      
    }
    
    static public function _errorHandler($args = null) {
    	FB::log(func_get_args(), FirePHP::ERROR);
    }

}
function xxxxxx() {
	ErrorManager::_errorHandler( func_get_args() );
}
?>