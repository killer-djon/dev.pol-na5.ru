<?php

class STPlugins 
{
	const KEY = 'STPlugins';
	protected static $instance;
	protected  $plugins;
	protected function __construct()
	{
		if (Wbs::isHosted() && file_exists(WBS_APP_PATH."/plugins/_plugins.hosting.php")) {
			include(WBS_APP_PATH."/plugins/_plugins.hosting.php");
		} else {
			include(WBS_APP_PATH."/plugins/_plugins.php");
		}
		foreach ($plugins as $plugin_name) {
			$class_name = 'ST'.$plugin_name.'Plugin';
			$plugin = new $class_name;
			if ($plugin->init()) {
				$this->plugins[$plugin_name] = $plugin;
			}  
		}
	}
	
	protected function __clone() {}
	
	/**
	 * @return STPlugins
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}	
		return self::$instance;
	}
	
	
	public function getBlock($name, $params)
	{
		$plugins_options = Registry::get(self::KEY);
		if (!isset($plugins_options[$name])) {
			return array();
		}
		foreach ($plugins_options[$name] as $plugin_info) {
			$result = $this->exec($plugin_info['plugin'], $plugin_info['method'], $params);
			if ($result) {
				$data[] = $result;
			}
		}
		return $data;
	}
	
	public function exec($plugin, $method, $params)
	{
		if (!isset($this->plugins[$plugin])) {
			return false;
		}
		
		$plugin = $this->plugins[$plugin];
		$method .= 'Action';
		return $plugin->$method($params);
	}
	
}