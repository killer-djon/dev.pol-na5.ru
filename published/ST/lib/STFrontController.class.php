<?php

/**
 * FrontController for ST application
 */
class STFrontController 
{
    protected $title = '';
    protected $content = '';
    protected $controller;
    protected $ajax = false;
    protected $prefix = 'ST';
    
    public function __construct()
    {
        // Get controller
		try {
			// Execute controller and actions
			$controller = $this->getController();
			if ($controller) {
				$controller->exec();
				$controller->display();
			}
		} catch (Exception $e) {
			// Ajax
			$json = Env::Server("HTTP_X_REQUESTED_WITH") == "XMLHttpRequest";
			if ($json) {
				// if not post and check controller
				if (!Env::isPost() && 
					(!isset($controller) || ($controller && !($controller instanceof JsonController)))) {
					$json = false;
				}
			} 
		    if ($json) {
		        echo json_encode(array("status" => "ERR", "error" => $e->getMessage(), "data" => ""));
		    } else {
	        	if (defined('DEVELOPER') && DEVELOPER) {
	    	    	echo $e;
	        	} else {
	        		$title = _s('Error');
	        		echo <<<HTML
<div class="ui-block-content">
<h1>{$title}</h1>	        		
<div>{$e->getMessage()}</div>
</div>
HTML;
	        	}
		    }
		}
    }
    
    protected function getController()
    {
	    $module = Env::Get("m", Env::TYPE_STRING, "index");
	    if ($module == 'plugins' && Env::Get('p')) {
	    	$response = STPlugins::getInstance()->exec(ucfirst(Env::Get('p')), Env::Get('act', Env::TYPE_STRING, 'default'), Env::Request());
	    	echo $response;
	    	return false;
	    }
	    $action = Env::Get("act", Env::TYPE_STRING, "");

	    $class_name = $this->prefix.ucfirst($module);
	    
	    // TODO Do a proper access check and make away with following workaraund
	    if (!in_array($class_name, array('STRequests','STIndex','STUsers','STContacts','STOrders')) 
	           && !User::hasAccess('ST','FUNCTIONS','ADMIN')) {
            throw new Exception(_s('Access denied.'), 403);
	    } else {
		    if ($action) {
		    	$class_name .= ucfirst($action);
		    }
		    $controller = $class_name."Controller";
		    if (class_exists($controller, true)) {
		    	return new $controller();
		    } elseif (class_exists($class_name."Action", true)) {
		    	$controller = $this->prefix."DefaultController";
		    	return new $controller($class_name."Action");
		    } else {
		    	throw new Exception(_s('The requested URL was not found.'), 404);
		    }
	    }
    }	    

}
?>