<?php

class FrontController {
	
	const PARSE_TYPE_FULL = 'PARSE_TYPE_FULL';
	const PARSE_TYPE_FRONT = 'PARSE_TYPE_FRONT';
	
	private $parseType = self::PARSE_TYPE_FULL;
	
	private $controllerPath = null;
	private $controlerName = null;
	
	private $requestParam = null;
	
	private $defaultController = null;
	
	public function __construct() 
	{
		$this->init();
	}
	
	private function init()
	{
		$this->defaultController = 'album'; 
	}
	
	public function setControllerPath($val) {
		$this->controllerPath = $val;		
		return $this;
	}
	public function getParam() {
		return $this->requestParam;
	}

	public function setParseType($type) {
		$this->parseType = $type;
	}
	
	private function parseRequest() {
		$this->controlerName = WebQuery::getParam('controller');
		$this->actionName = WebQuery::getParam('action');
		
		$this->requestParam['action'] = $this->actionName;
	}
	
	private function parseRequestFront() {
	    $param = array();
		$this->controlerName = 'frontend';
		if ( Wbs::getSystemObj()->isModeRewrite() == 1  || Wbs::isHosted() ) {
			$query = Env::Get('q', Env::TYPE_STRING, '');
			$queryArr = explode('/', $query);
			if ($queryArr && $queryArr[0]) {
    			for ( $i = 0; $i < count($queryArr); $i += 2 ) {
    				$param [$queryArr[$i]] = $queryArr[$i+1];
    				$param[$i] = $queryArr[$i];
    				$param[$i+1] = $queryArr[$i+1];
    			}
			}
		}
		else {
			$urlInfo = str_replace(array('=', '&'), array('/', '/'), $_SERVER['QUERY_STRING']);
			
		    $queryArr = explode('/', $urlInfo);
			$queryArr = array_filter($queryArr,'strlen');
			for ( $i=0; $i<count($queryArr); $i += 2 ) {
				$param [$queryArr[$i]] = $queryArr[$i+1];
				$param[$i] = $queryArr[$i];
				$param[$i+1] = $queryArr[$i+1];
			}
		}
		
		$this->requestParam = $param;
	}	
	
	private function getControllerName() {
		if ( !is_null( $this->controlerName ) ) {
			return $this->controlerName;
		}
		else if ( !is_null($this->defaultController) ) {
			return $this->defaultController;
		}
		else {
			throw new RuntimeException ('Controller undefined');
		}		
	}
	private function getControllerPath() {
		return $this->controllerPath ."/". $this->getControllerName() ."Controller.php";
	}
	public function run() {		
    	if ( $this->parseType == self::PARSE_TYPE_FRONT )
    		$this->parseRequestFront();
    	else
    		$this->parseRequest();
    		
    	if ( !file_exists( $this->getControllerPath() ) ) {
    		throw new RuntimeException ("Controller is not path " . $this->getControllerPath());
    	}
    	$controllerName = $this->getControllerPath();
    	include $controllerName;
    	$className = $this->getControllerName() . "Controller";
    	$controller = new $className();
    	$controller->setRequestParams($this->requestParam);
    					
    	$controller->run();
	}
}

?>