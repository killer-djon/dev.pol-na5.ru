<?php

class STSourceModel extends DbModel
{
	
	const TYPE_EMAIL = 'email';
	
	protected $id = 'id';
	protected $table = 'st_source';
	protected $table_param = 'st_source_param';
	
	
	
	public function get($id)
	{
		return $this->getById($id);
	}
	
	public function getAll()
	{
		$sql = "SELECT * FROM ".$this->table." ORDER BY name";
		return $this->query($sql)->fetchAll('id'); 
	}
	
	public function getAllWithEmail()
	{
		$sql = "SELECT s.*, p.value 
				FROM ".$this->table." s
				LEFT JOIN ".$this->table_param." p 
				ON s.id = p.source_id AND p.name = 'email'";
		
		return $this->query($sql)->fetchAll();  
	}
	
	public function getEmails()
	{
		$sql = "SELECT s.name, p.value email
				FROM ".$this->table." s
				JOIN ".$this->table_param." p
				ON s.id = p.source_id 
				WHERE s.type = 'EMAIL' AND p.name = 'email'";
		$data = $this->query($sql);
		$result = array();
		foreach ($data as $row) {	
			$result[$row['email']] = $row['name'] . ' <' . $row['email'] . '>';
		}
		return $result;
	}
	
	public function getByEmail($email)
	{
		$sql = "SELECT source_id FROM ".$this->table_param."
				WHERE name = 'email' AND value = s:email";
		
		return $this->prepare($sql)->query(array('email' => $email))->fetchField('source_id');
	}
	
    public function getEmailByParam($param, $value, $limit)
    {
        $sql = "SELECT *
        FROM ".$this->table." s
        JOIN ".$this->table_param." p
        ON s.id = p.source_id 
        WHERE p.name = s:name 
          AND p.value = i:value ";
        if ($limit)
            $sql .= " LIMIT ".$limit;
        $data = $this->prepare($sql)->query(array('name' => $param, 'value' => $value));
        $result = array();
        foreach ($data as $row) {   
            $result[] = $this->getParams($row['id'], 'email');
        }
        return $result;
    }
    
	public function getParams($id, $params = array())
	{
		$sql = "SELECT name, value 
				FROM ".$this->table_param."
				WHERE source_id = i:id";
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
			if (Wbs::isHosted()) {
				$params = $data->fetchAll('name', true);
				if (isset($params['inner'])) {
					return $params;
				}
				$email = explode("@", $params['email'], '2');
				if (trim($email[1]) === Env::Server('HTTP_HOST')) {
					$params['inner'] = 1;
				}
				return $params;
			} else {
				return $data->fetchAll('name', true);
			}
		}
	}
	
	public function setParams($id, $params) 
	{
		$sql = "INSERT INTO ".$this->table_param." (source_id, name, value) VALUES ";
		$t = false;
		foreach ($params as $name => $value) {
			if ($t) {
				$sql .= ", ";
			} else {
				$t = true;
			}
			$sql .= "(".(int)$id. ", '".$this->escape($name)."', '".$this->escape($value)."')";
		}
		$sql .= " ON DUPLICATE KEY UPDATE value = VALUES(value)";
		return $this->exec($sql);
	}
	
	public function delParams($id, $params = false) 
	{
		$sql = "DELETE FROM ".$this->table_param." WHERE source_id = ".(int)$id;
		if ($params && is_array($params)) {
			$sql .= " AND name IN ('".implode("', '", $this->escape($params))."')";
		}
		return $this->exec($sql);
	}
	
	public function add($info, $params)
	{
		$sql = "INSERT INTO ".$this->table." 
				SET type = s:type,
					name = s:name";
		$id = $this->prepare($sql)->query($info)->lastInsertId();
		$this->setParams($id, $params);
		return $id;
	}
	
	public function save($id, $info) 
	{
		$info['id'] = $id;
		$sql = "UPDATE ".$this->table." 
				SET name = s:name
				WHERE id = i:id";
		return $this->prepare($sql)->exec($info);
	}
	
	public function delete($id)
	{
		$params = $this->getParams($id);
		// Delete hosted email
	    if (isset($params['inner']) && $params['inner']) {
			$email = explode('@', $params['email'], 2);
	    	$data = array(
				'MMA_EMAIL' => $email[0],
				'MMA_DOMAIN' => $email[1],
				'MMA_OWNER' => Wbs::getDbKey(),
	    		'MMA_PASSWORD' => $params['password'],
			);
			
			$xml = simplexml_load_file(WBS_ROOT_PATH . "/kernel/wbs.xml");
			$url = (string)$xml->MAILDAEMONDB->attributes()->SERVER_NAME;
			$url .= "/";
			$url .= (string)$xml->MAILDAEMONDB->attributes()->PAGE_URL;
			$url .= '?action=delete';
			foreach($data as $key => $val ) {
				$url .= "&" . rawurlencode( $key ) . "=" . rawurlencode( $val );
	        }
	        $result = file_get_contents($url); 
	        if ($result != 'OK') {
	        	throw new Exception("Could not delete email box (".$result.").");
	        }
	    } 
		if ($this->delParams($id)) {
			$sql = "DELETE FROM ".$this->table." 
					WHERE `id` = i:id";
			return $this->prepare($sql)->exec(array('id' => $id));
		} else {
			return false;
		}
	}
}