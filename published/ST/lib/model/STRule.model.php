<?php

class STRuleModel extends DBModel
{
	protected $table = 'st_rule';
	protected $id = 'id';
	
	public function getAll()
	{
		$sql = "SELECT * FROM ".$this->table;
		return $this->query($sql)->fetchAll();
	}
	
	public function execActions(&$data, $action)
	{
		$actions = explode(";", $action);
		foreach ($actions as $act) {
			if (!$act) continue;
			$params = explode(':', $act);
			$a = array_shift($params);
			$this->execAction($data, $a, $params);
		}
	}
	
	
	protected function execAction(&$data, $a, $params)
	{
		switch ($a) {
			case 'classes':
				if (!isset($data['classes']) || !is_array($data['classes'])) {
					$data['classes'] = array();
				}
				$params = explode(',', $params[0]);
				foreach ($params as $class) {
					$data['classes'][] = (int)$class;
				}
				$data['classes'] = array_unique($data['classes']);
				break;
			default: 
				$data[$a] = $params[0];
		}
	}
}