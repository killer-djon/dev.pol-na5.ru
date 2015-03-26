<?php

class STStateModel extends DbModel
{
	protected $table = "st_state";
	protected $id = "id";
	
	protected $cache = array();
	
	protected $fields = array('name' => 's', 'properties' => 's', 'group' => 'i');
	
	public function get($id) 
	{
		return $this->getById($id);
	}
	
	public function getAll()
	{
		if (isset($this->cache['all'])) {
			return $this->cache['all'];
		}
		
		$sql = "SELECT * FROM ".$this->table;
		$data = $this->query($sql);
		$result = array();
		foreach ($data as $row) {
			if (substr($row['name'], 0, 1) == '{') {
				$row['name'] = json_decode($row['name'], true);
				$lang = User::getLang();
				if (isset($row['name'][$lang])) {
					$row['name'] = $row['name'][$lang];
				} elseif (isset($row['name']['all'])) {
					$row['name'] = _($row['name']['all']);
				} else {
					$row['name'] = $row['name']['eng'];
				}
			} else {
				$row['name'] = _($row['name']);
			}
			$row['properties'] = json_decode($row['properties']);
			$result[$row['id']] = $row;
		}
		$this->cache['all'] = $result;
		return $result;
	}
	
	public function getByGroup($group = "-101")
    {
        $sql = "SELECT * FROM ".$this->table."
                WHERE `group` = s:group";
        
        $data = $this->prepare($sql)->query(array('group' => $group));
        
        $result = array();
        foreach ($data as $row) {
            $row['properties'] = json_decode($row['properties']);
            $result[$row['id']] = $row;
        }
        return $result;
    }
    
	
	public function add($name, $params)
	{
		$query = "name = s:name";
		foreach ($params as $param => $value) {
			if (isset($this->fields[$param])) {
				$query .= ", ".$param." = ".$this->fields[$param].":".$param;
			}
		}
		$sql = "INSERT INTO ".$this->table." SET ".$query;
		$params['name'] = $name;
		return $this->prepare($sql)->query($params);
	}
	
}

?>