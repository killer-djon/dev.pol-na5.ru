<?php 

class CMPlugin 
{
	protected $apps = array();
	
	public function init($ignore_rights = false)
	{
		foreach ($this->apps as $app) {
			if (!Wbs::getDbkeyObj()->appExists($app) || (!$ignore_rights && !User::hasAccess($app))) {
				return false;
			}
		} 
		$this->register();
		return true;
	}
	
	public function getName()
	{
		return substr(get_class($this), 2, -6);
	}
	
	protected function register()
	{ 
		
	}
	
	protected function registerMethod($block, $method)
	{
		$options = Registry::get(CMPlugins::KEY);
		$options[$block][] = array(
			'plugin' => $this->getName(),
			'method' => $method
		);		
		Registry::set(CMPlugins::KEY, $options);
	}	
}

?>