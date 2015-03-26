<?php

class STStateActionModel extends DbModel
{
	protected $table = "st_state_action";
	
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
		
	public function getByState($state_id, $group = false)
	{
		$state_model = new STStateModel();
		$state_info = $state_model->get($state_id);
				
		$action_model = new STActionModel();
		$sql = "SELECT a.*, sa.sorting FROM ".$this->table." sa 
				JOIN ".$action_model->getTableName()." a 
				ON sa.action_id = a.id
				WHERE (sa.state_id = i:state_id OR sa.state_id = i:state_group)
				".($group ? " AND a.group = s:group" : "")."
				ORDER BY sa.sorting";
		$data = $this->prepare($sql)->query(array(
			'state_id' => $state_id, 'state_group' => $state_info['group'], 'group' => $group 
		));
		$result = array();
		$lang = User::getLang();
		foreach ($data as $row)	{
			if ($row['type'] == 'CLASSIFY') continue;
			$row['name'] = $this->getName($row['name'], $lang);
			$row['log_name'] = $this->getName($row['log_name'], $lang);
			$result[$row['id']] = $row;
		}
		return $result;
	}
	
	/**
	 * Return action available in the state by the type
	 * 
	 * @param int $state_id - state_id
	 * @param string $type
	 * @return array
	 */
	public function getByStateType($state_id, $type)
	{
		$state_model = new STStateModel();
		$state_info = $state_model->get($state_id);
		
		$action_model = new STActionModel();
		$sql = "SELECT a.* FROM ".$this->table." sa 
				JOIN ".$action_model->getTableName()." a
				ON sa.action_id = a.id
				WHERE (sa.state_id = i:state_id OR sa.state_id = i:state_group) AND a.type = s:type
				LIMIT 1";
		return $this->prepare($sql)->query(array(
			'state_id' => $state_id, 'type' => $type, 'state_group' => $state_info['group']
		))->fetch();
	}
}