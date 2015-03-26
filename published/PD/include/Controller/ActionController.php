<?php
class ActionController
{
	/**
	 * @var array
	 */
	protected $requestParams = null;
	/**
	 * @var PDSmarty
	 */
	protected $view = null;
	/**
	 * @var string
	 */
	protected $defaultAction = null;	
	
	public function setRequestParams($param) 
	{
		$this->requestParams = $param;
	}
	
	public function setView($view)
	{
		$this->view = $view;
	}	
	
	protected function getActionName()
	{
		if ( !empty($this->requestParams[0]) ) {
			return $this->requestParams[0].'Action';
		}
		else if ( !empty($this->requestParams['action']) ) {
			return $this->requestParams['action'].'Action';
		}
		else if ( !is_null($this->defaultAction) ) {
			return $this->defaultAction . 'Action';
		}
		else {
			throw new RuntimeException ('Action undefined');
		}
	}
	
	public function run()
	{
		$this->init();		
		
		$this->preAction();
		
		if ( !method_exists($this, $this->getActionName() ) ) {
			throw new RuntimeException ("Action is not: " . $this->getActionName());
		}
		$this->{ $this->getActionName() }();
		
		$this->postAction();
	}
	
	protected  function preAction()
	{	
	}
	
	protected function postAction()
	{	
	}
}
?>