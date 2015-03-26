<?php 

class STRequestLogModel extends DbModel
{
    protected $id = 'id';
    protected $table = 'st_request_log';

    public function get($id, $field)
    {
    	return $this->getById($id);
    }
    
    public function getLastId()
    {
        $sql = "SELECT id FROM ".$this->table."
                ORDER BY id DESC
                LIMIT 0, 1";
        return $this->prepare($sql)->query(array())->fetchField('id');
    }
    
    public function getWithAttachments($request_id)
    {
    	$sql = "SELECT id, attachments FROM ".$this->table." 
    			WHERE request_id = i:request_id AND attachments != ''";
    	return $this->prepare($sql)->query(array('request_id' => $request_id))->fetchAll('id', true);
    }
    
    public function getWithAttachmentsByState($state_id)
    {
    	$sql = "SELECT l.id, l.attachments 
    			FROM ".$this->table." l 
    			JOIN st_request r ON l.request_id = r.id  
    			WHERE r.state_id = i:state_id AND l.attachments != ''";
    	return $this->prepare($sql)->query(array('state_id' => $state_id))->fetchAll('id', true);    		
    }
    
    public function getByRequest($request_id, $limit = false, $lastid = false, $actor_id = false, $action_ids = '', $order = 'ASC')
    {
        $where = 'WHERE request_id=i:request_id';
        if ($lastid) {
            $where .= " AND `id` > i:lastid";
        }
        if ($actor_id) {
            $where .= " AND `actor_c_id` != s:actor_id";
        }
        if (!empty($action_ids)) {
            $where .= " AND `action_id` IN ($action_ids)";
        }
        
        $sql = "SELECT * FROM ".$this->table."
                $where
                ORDER BY id ".$order;
        if ($limit) {
            $sql .= " LIMIT ".$limit;
        }
        
        return $this->prepare($sql)->query(
                  array('request_id' => $request_id, 'lastid' => $lastid, 'actor_id' => $actor_id)
              )->fetchAll();
    }
    
    public function getUpdatedRequests($lastid = false)
    {
		if ($lastid) {
			$sql = "SELECT st_request.* 
					FROM st_request 
					JOIN st_request_log ON st_request.id=st_request_log.request_id
					WHERE st_request_log.id > i:id 
					GROUP BY st_request_log.request_id";
			return $this->prepare($sql)->query(array('id' => $lastid))->fetchAll();
		} else {
			return array();
		}
    }
    
    public function add($request_id, $data)
    {
        if (isset($data['actor_c_id'])) {
            $request_model = new STRequestModel();
            $request_info = $request_model->get($request_id);
	        //$states_model = new STStateModel();
	        //$states = $states_model->getAll();
	        
            $action_model = new STActionModel();
            $action = $action_model->get($data['action_id']);
            
            // if current user is not assigned to the request set flag read = 0
            if (($data['actor_c_id'] != $request_info['assigned_c_id']) && ($action['group'] == 'system')) {
                $request_model->setRead($request_id, 0);
            }
        }
        
		if (isset($data['attachments']) && $data['attachments']) {
			$data['attachments'] = serialize($data['attachments']);
		}
        
        $sql = "INSERT INTO ".$this->table." 
                SET request_id = ".(int)$request_id; 
        if (!isset($data['datetime'])) {
            $sql .= ", datetime = '".date("YmdHis")."'";
        }
        foreach ($data as $name => $value) {
            $sql .= ", `".$this->escape($name)."` = '".$this->escape($value)."'";
        }
        return $this->query($sql)->lastInsertId();
    }
    
    /**
     * Returns previous state of the request for restore
     * 
     * @return int 
     */
    public function getPreviousState($request_id)
    {
        $sql = "SELECT state_id FROM ".$this->table."
                WHERE request_id=i:request_id 
                ORDER BY id DESC
                LIMIT 1, 1";
        return $this->prepare($sql)->query(array('request_id' => $request_id))->fetchField('state_id');
    }
    
    public function getLastByAction($request_id, $actions)
    {
    	$sql = "SELECT * FROM ".$this->table."
    			WHERE request_id = ".(int)$request_id." AND action_id IN ('".implode("','", $actions)."')
    			ORDER BY id DESC 
    			LIMIT 1";
    	return $this->query($sql)->fetch();
    }
    
    public function saveAttachments($id, $attachments)
    {
    	if ($attachments) {
    		$disk_usage_model = new DiskUsageModel();
    		foreach ($attachments as $file) {
    			$disk_usage_model->add('$SYSTEM', 'ST', $file['size']);
    		}
    		$attachments = serialize($attachments);
    	} else {
    		$attachments = '';
    	}
		$sql = "UPDATE ".$this->table." 
				SET attachments = '".$this->escape($attachments)."'
				WHERE id = ".(int)$id;
		return $this->exec($sql);    	
    }

}
?>