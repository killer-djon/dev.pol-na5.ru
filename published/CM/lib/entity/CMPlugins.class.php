<?php

class CMPlugins 
{
	const KEY = 'CMPlugins';
	protected static $instance;
	protected  $plugins;
	protected function __construct($ignore_rights = false)
	{
		if (Wbs::isHosted() && file_exists(WBS_PUBLISHED_DIR."/CM/plugins/_plugins.host.php")) {
			include(WBS_PUBLISHED_DIR."/CM/plugins/_plugins.host.php");
		} elseif (file_exists(WBS_PUBLISHED_DIR."/CM/plugins/_plugins.php")) {
			include(WBS_PUBLISHED_DIR."/CM/plugins/_plugins.php");
			foreach ($plugins as $plugin_name) {
				$class_name = 'CM'.$plugin_name.'Plugin';
				$plugin = new $class_name;
				if ($plugin->init($ignore_rights)) {
					$this->plugins[$plugin_name] = $plugin;
				}  
			}
		}
	}
	
	protected function __clone() {}
	
	/**
	 * @return STPlugins
	 */
	public static function getInstance($ignore_rights = false)
	{
		if (!self::$instance) {
			self::$instance = new self($ignore_rights);
		}	
		return self::$instance;
	}
	
	
	public function getData($name, $params)
	{
		$plugins_options = Registry::get(self::KEY);
		if (!isset($plugins_options[$name])) {
			return array();
		}
		$data = array();
		foreach ($plugins_options[$name] as $plugin_info) {
			$result = $this->exec($plugin_info['plugin'], $plugin_info['method'], $params);
			if (is_array($result)) {
				$data = array_merge($data, $result);
			} else {
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