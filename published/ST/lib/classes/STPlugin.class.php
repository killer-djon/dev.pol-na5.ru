<?php 

class STPlugin 
{
	protected $apps = array();
	
	public function init()
	{
		foreach ($this->apps as $app) {
			if (!Wbs::getDbkeyObj()->appExists($app) || !User::hasAccess($app)) {
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
		$options = Registry::get(STPlugins::KEY);
		$options[$block][] = array(
			'plugin' => $this->getName(),
			'method' => $method
		);		
		Registry::set(STPlugins::KEY, $options);
	}
	
	protected function display(View $view, $template)
	{
		return $view->fetch('../plugins/'.$this->getName().'/templates/'.$template.'.html');
	}
}

?>