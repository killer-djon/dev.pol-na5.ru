<?php

class STActionModel extends DbModel
{
	protected $table = "st_action";
	protected $table_param = "st_action_param";
	protected $id = "id";
	
	protected $cache = array();
	
	public function get($action_id) 
	{
		$info = $this->getById($action_id);
		$info['name'] = $this->getName($info['name'], User::getLang());
		$info['log_name'] = $this->getName($info['log_name'], User::getLang());
		if ($info['properties']) {
			$info['properties'] = json_decode($info['properties'], true);
		}
		return $info;		
	}
	
	protected function getName($value, $lang)
	{
		if (substr($value, 0, 1) == '{') {
			$value = json_decode($value, true);
			if (isset($value[$lang])) {
				return $row['name'][$lang];
			} elseif (isset($value['all'])) {
				return _($value['all']);
			} else {
				return $value['eng'];
			}
		} else {
			return _($value);
		}		
	}
	
	public function getAll($ids = array())
	{
		if (!$ids && isset($this->cache['all'])) {
			return $this->cache['all'];
		}
		$sql = "SELECT * FROM ".$this->table; 
		if ($ids) {
			$sql .= " WHERE id IN ('".implode("', '", $this->escape($ids))."')";
		}
		$data = $this->query($sql);
		$result = array();
		$lang = User::getLang();
		foreach ($data as $row) {
			$row['name'] = $this->getName($row['name'], $lang);
			$row['log_name'] = $this->getName($row['log_name'], $lang);
			$result[$row['id']] = $row;
		}
		if (!$ids) {
			$this->cache['all'] = $result;
		}
		return $result;
	}
	
	public function getByStateType($state_id, $type = false)
	{
		$sql = "SELECT * FROM ".$this->table." 
				WHERE state_id = i:state_id ".($type ? " AND type = s:type LIMIT 1":"")."LIMIT 1";
		$info = $this->prepare($sql)->query(array('state_id' => $state_id, 'type' => $type, 'group' => $group))->fetch();
		$info['name'] = $this->getName($info['name'], User::getLang());
		$info['log_name'] = $this->getName($info['log_name'], User::getLang());
		return $info;		
	}
	
	public function getByType($type, $group = false)
	{
		$sql = "SELECT * FROM ".$this->table." 
				WHERE type = s:type
				".($group ? " AND `group` = s:group" : "")."
				LIMIT 1";
		$info = $this->prepare($sql)->query(array('type' => $type, 'group' => $group))->fetch();
		$info['name'] = $this->getName($info['name'], User::getLang());
		$info['log_name'] = $this->getName($info['log_name'], User::getLang());
		return $info;		
	}
	
	public function getParams($id, $params = array())
	{
		$sql = "SELECT name, value 
				FROM ".$this->table_param."
				WHERE action_id = i:id";
		if ($params) {
			if (is_array($params)) {
				$sql .= " AND name IN ('".implode("','", $this->escape($params))."')";
			} else {
				$sql .= " AND name = '".$this->escape($params)."'";
			}
		}
		$data = $this->prepare($sql)->query(array('id' => $id));
		if (!is_array($params) && $params) {
			return $data->fetchField('value');
		} else {
			return $data->fetchAll('name', true);
		}
	}	
}