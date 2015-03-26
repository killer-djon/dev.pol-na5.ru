<?php
 
/**
 * Error handling function with use of FirePHP
 *
 * @author Grzegorz Godlewski 
 */

/**
 * Class for emulating Errors as Exceptions
 */
class wfErrorException extends Exception {
}

/**
 * Error handler class
 */
class wfErrorHandler {

    /**
     * Error handler options
     *
     * Available options:
     *
     * file - log file
     * mail - log mail
     * ignore_notices - if set to true Error handler ignores notices
     * ignore_warnings - if set to true Error handler ignores warnings
     * display - if set to true Error handler display error to output
     * firebug - if set to true Error handler send error to firebug
     */
    public static $options = array();

    /**
     * Construtor - object cannot be created
     */
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

        set_error_handler(array('wfErrorHandler', "errorHandler"), $flags);
        set_exception_handler(array('wfErrorHandler', "exceptionHandler"));
    }

    /**
     * Exception handler
     *
     * @param Exception $ex Exception
     */
    static public function exceptionHandler($ex) {
        $errMsg = $ex->getMessage();
        $backtrace = $ex->getTrace();

        if (!$ex instanceof wfErrorHandler) {
            $errMsg = get_class($ex).': '.$errMsg;
            array_unshift($backtrace, array('file'=>$ex->getFile(), 'line'=>$ex->getLine(),
               'function'=>'throw '.get_class($ex), 'args'=>array($errMsg, $ex->getCode()) ));
        }

        $errMsg .= ' | '.date("Y-m-d H:i:s");
        if (empty($_SERVER['HTTP_HOST'])) {
            $errMsg .= ' | '.implode(' ', $_SERVER['argv']);
        } else {
                $errMsg .= ' | '.$_SERVER['HTTP_HOST']." (".$_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT'].")"."\n";
        }

        $trace = '';
        foreach ($backtrace as $v) {
            $v['file'] = preg_replace('!^'.$_SERVER['DOCUMENT_ROOT'].'!', '' ,$v['file']);
            $trace .= $v['file']."\t".$v['line']."\t";
            if (isset($v['class'])) {
                $trace .= $v['class'].'::'.$v['function'].'(';
                if (isset($v['args'])) {
                    $errRow[] = $v['args'];
                    $separator = '';
                    foreach($v['args'] as $arg ) {
                        $trace .= $separator.self::getArgument($arg);
                        $separator = ', ';
                    }
                }
                $trace .= ')';
            } elseif (isset($v['function'])) {
                $trace .= $v['function'].'(';
                $errRow[] = $v['function'];
                if (!empty($v['args'])) {
                    $errRow[] = $v['args'];
                    $separator = '';
                    foreach($v['args'] as $arg ) {
                        $trace .= $separator.self::getArgument($arg);
                        $separator = ', ';
                    }
                }
                $trace .= ')';
            }
            $trace .= "\n";
        }

        if (!empty(self::$options['firebug'])) {
            FB::send($ex, FirePHP::EXCEPTION);
        }

        if (!empty(self::$options['display'])) {
            if (empty($_SERVER['HTTP_HOST'])) {
                echo "\33[1m".$errMsg."\33[0m"."\n".$trace;
            } else {
                echo "<"."pre style=\"background: #f55; color: #000; font-weight: bold; font-size: 13px; padding: 10px; margin: 10px; text-align: left; \">\n";
                echo $errMsg."\n".$trace;
                echo "<"."/pre>\n";
            }
        }

        if (!empty(self::$options['mail'])) {
            $headers = "Content-Type: text/html; charset=utf-8\r\n" .
                "Content-Transfer-Encoding: 8bit\r\n\r\n";
            @mail(self::$options['mail'], $errMsg, $errMsg."\n".$trace."\n", 'From: Error Handler', $headers);
        }
        if (!empty(self::$options['file'])) {
            try {
                $fp = fopen(self::$options['file'], "a+");
                if (flock($fp, LOCK_EX)) {
                    fwrite($fp, "----\n".$errMsg."\n".$trace."\n");
                    flock($fp, LOCK_UN);
                }
                fclose($fp);
            } catch (Exception $ex) {
                echo "\nError writing to file: ".self::$options['file']."\n";
            }
        }
        exit(1);
    }

    /**
     * Error handler
     *
     * @param int $errno Error code
     * @param string $errstr Error message
     */
    static public function errorHandler($errno, $errstr) {
        if (error_reporting() == 0) { // if error has been supressed with an @
            return;
        }
        $errorType = array (
            E_ERROR          => 'ERROR',
            E_WARNING        => 'WARNING',
            E_PARSE          => 'PARSING ERROR',
            E_NOTICE         => 'NOTICE',
            E_CORE_ERROR     => 'CORE ERROR',
            E_CORE_WARNING   => 'CORE WARNING',
            E_COMPILE_ERROR  => 'COMPILE ERROR',
            E_COMPILE_WARNING => 'COMPILE WARNING',
            E_USER_ERROR     => 'USER ERROR',
            E_USER_WARNING   => 'USER WARNING',
            E_USER_NOTICE    => 'USER NOTICE',
            E_STRICT         => 'STRICT NOTICE',
            E_RECOVERABLE_ERROR  => 'RECOVERABLE ERROR'
        );
        $errMsg = $errorType[$errno].': '.$errstr;
        throw new wfErrorException($errMsg);
    }

    /**
     * Converts variable into short text
     *
     * @param mixed $arg Variable
     * @return string
     */

    static protected function getArgument($arg) {
        switch (strtolower(gettype($arg))) {
            case 'string':
                return( '"'.str_replace( array("\n","\""), array('','"'), $arg ).'"' );
            case 'boolean':
                return (bool)$arg;
            case 'object':
                return 'object('.get_class($arg).')';
            case 'array':
                return 'array['.count($arg).']';
            case 'resource':
                return 'resource('.get_resource_type($arg).')';
            default:
                return var_export($arg, true);
        }
    }
}
?>