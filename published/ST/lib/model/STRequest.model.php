<?php

class STRequestModel extends DbModel
{
	protected $table = 'st_request';
	protected $table_log = 'st_request_log';
	protected $id = "id";
	protected $fields = array(
		'id' => 'i',
		'message_id' => 's',
		'source_type' => 's',
		'source' => 's',
		'app_id' => 's',
		'datetime' => 's',
		'state_id' => 'i',
		'priority' => 'i',
		'client_from' => 's',   
		'client_c_id' => 'i',  
		'assigned_c_id' => 'i',
        'read' => 'i',
		'subject' => 's',
		'text' => 's',
		'attachments' => 's'     
    );

	public function get($id, $field = false)
	{
		$data = $this->getById($id);
		if ($field) {
			return $data[$field];
		}
		if ($data && $data['attachments'] && substr($data['attachments'], 0 ,1) != '/') {
			$data['attachments'] = unserialize($data['attachments']);
			foreach ($data['attachments'] as &$row) {
				if (isset($row['content-id'])) {
					$row['content_id'] = $row['content-id'];
				} 
			}
		} elseif ($data && $data['attachments'] && substr($data['attachments'], 0 ,1) == '/') {
			$data['attachments'] = STRequest::moveAttachments($data['attachments'], $id);
		} else {
			$data['attachments'] = '';
		}
		return $data;
	}
	
	
	public function getByClass($class_id, $sort = '', $limit = 0) 
	{
		$sql = "SELECT * FROM ".$this->table." r 
				JOIN st_request_class rc ON r.id = rc.request_id
				WHERE rc.class_id = i:class_id  
				ORDER BY ".($sort ? $sort : "id DESC");
		if ($limit) {
			$sql .= " LIMIT ".$limit;
		}
		return $this->prepare($sql)->query(array('class_id' => $class_id))->fetchAll('id');
		
	}
	private function prepareSearch($search = false, &$filter = ""){
        /*
        if (is_numeric($search)) {
            $sql = " AND id = i:search ";
            $filter = $search;
        } else {
        */
            $sql = " AND (";
            $search = explode(" ", $search);
            foreach($search as $word){
                $sql .= " ((c.C_FULLNAME LIKE '%".$this->escape($word)."%') OR (id = '".$this->escape($word)."')) AND ";
            }
            $sql = substr($sql, 0, -4);
            $sql .= ") ";
            //$sql = " AND c.C_FULLNAME LIKE s:search ";
            //$filter = "%".$search."%";
        //}
        return $sql;
	}
	
    public function getLastId()
    {
        $sql = "SELECT id FROM ".$this->table."
                ORDER BY id DESC
                LIMIT 0, 1";
        return $this->prepare($sql)->query(array())->fetchField('id');
    }
    
	public function getAll($sort = '', $limit = 0, $filters = array(), $search = false, $fields = "*", $all_states = false, $refreshedIds = array())
	{
	    if ($fields!="COUNT(*)") {
	        $fields = "req.".$fields;
	    }
		$sql = "SELECT $fields FROM ".$this->table." req WHERE";

        foreach($filters as $column=>&$val) {
            if ($column!='after_id'){
                if (!is_array($val)){
                    $sql .= " `".$column."` = ".$this->fields[$column].":".$column." AND";
                } else {
                    $sql .= " ( ";
                    foreach ($val as $f_v){
                        $sql .= " `".$column."` = ".$f_v." OR";
                    }
                }
            } else {
                if (isset($filters['state_id']) && is_array($filters['state_id'])) {
                    $sql = substr($sql, 0, -3);
                    $sql .= " ) AND ";
                }
                $sql .= " `id` > i:after_id AND";
            }
        }

        if (!isset($filters['state_id']) && !$all_states) {
        	$sql .= " state_id > 0";
        } else {
            if ($column=='after_id' ||
            (!isset($filters['state_id']) || !is_array($filters['state_id']))){
                $sql = substr($sql, 0, -4);
            } else {
                if (isset($filters['state_id'][0]) && isset($filters['assigned_c_id']) && $filters['assigned_c_id'] == User::getContactId()) {
                	$sql .= " `read` = 0 OR";
                }
            	
            	$sql = substr($sql, 0, -3);
                $sql .= " ) ";
            }
        }
     
        if (!empty($refreshedIds)){
            $refreshedIds = substr($refreshedIds,0, -1);
            $sql .= ' AND `id` IN ('.$refreshedIds.') ';
        }

        if ($search !== false){
            /*if (is_numeric($search)) {
                $sql .= $this->prepareSearch($search, $filters['search']);
            } else*/if (strpos($search,'|')>0) {
                $search = explode ('|', $search);
                $groupBy = false;
                $wmode = false; $umode = false;
                $sql = "SELECT $fields FROM ".$this->table." req ";
                if (in_array('wmode', $search)) {
                    $key = array_search('wmode', $search) + 1;
                    $wmode = intval($search[$key]);
                }
                if (in_array('umode', $search)) {
                    $key = array_search('umode', $search) + 1;
                    $umode = intval($search[$key]);
                }
                if ($wmode > 1 || $umode){
                    $sql .= " LEFT JOIN st_request_log log ON log.request_id=req.id ";
                    $groupBy = true;
                }
                if (in_array('name', $search) || in_array('email', $search)) {
                    $sql .= " LEFT JOIN CONTACT c ON c.C_ID=req.client_c_id ";
                } elseif ($umode) {
                    $sql .= " LEFT JOIN CONTACT c ON c.C_ID=log.actor_c_id ";
                }
                $sql .= " WHERE req.state_id > 0 ";
                
                if ($umode){
                    $action_model = new STActionModel();
                    $action = $action_model->getByType('REPLY');
                }
                switch ($umode) {
                    case 1:
                        $sql .= " AND (log.action_id = ".$action['id'].") ";
                    break;
                    case 2:
                        $sql .= " AND (log.action_id != ".$action['id'].") ";
                    break;
                }
                
                foreach($search as $key=>$param){
                    if ($key+1 < sizeof($search)) {
		                switch ($param) {
						    case 'name':
						        $from = $search[$key+1];
					            $sql .= " AND (";
				                $from = explode(" ", $from);
                                foreach($from as $word){
                                   $sql .= " (c.C_FULLNAME LIKE '%".$this->escape($word)."%') AND ";
                                }
					            $sql = substr($sql, 0, -4);
					            $sql .= ") ";
						        break;
                            case 'email':
                                $from = $search[$key+1];
                                $sql .= " AND (";
                                $sql .= " c.C_EMAILADDRESS LIKE '%".$this->escape($from)."%' ";
                                $sql .= ") ";
                                break;
                            case 'clientid':
                                $from = $search[$key+1];
                                $sql .= " AND (";
                                $sql .= " req.client_c_id = '".$this->escape($from)."' ";
                                $sql .= ") ";
                                break;
	                        case 'subject':
	                            $subject = $search[$key+1];
	                            if (in_array('words', $search)){
                                    $sql .= " AND ((";
                                } else {
                                    $sql .= " AND (";
                                }
	                            $subject = explode(" ", $subject);
	                            foreach($subject as $word){
	                                $sql .= " (req.subject LIKE '%".$this->escape($word)."%') AND ";
	                            }
	                            $sql = substr($sql, 0, -4);
	                            $sql .= ") ";
	                            break;
                            case 'user':
                                $user = $search[$key+1];
                                $sql .= " AND (";
                                $sql .= " log.actor_c_id = '".(int)$user."' ";
                                $sql .= ") ";
                                break;
						    case 'words':
	                            $words = $search[$key+1];
	                            $words = explode(" ", $words);
                                if (in_array('subject', $search)){
                                    $sql .= " OR (";
                                } else {
                                    $sql .= " AND (";
                                }
						        if ($wmode == 1){
		                            foreach($words as $word){
		                                $sql .= " (req.text LIKE '%".$this->escape($word)."%') AND ";
		                            }
						        } elseif ($wmode==2){
						            foreach($words as $word){
	                                    $sql .= " (log.text LIKE '%".$this->escape($word)."%') AND ";
	                                }
						        } elseif ($wmode==3) {
	                                foreach($words as $word){
	                                    $sql .= " (log.text LIKE '%".$this->escape($word)."%' OR req.text LIKE '%".$this->escape($word)."%') AND ";
	                                }
						        }
                                $sql = substr($sql, 0, -4);
                                $sql .= ") ";
		                        if (in_array('subject', $search)){
                                    $sql .= ") ";
                                }
						        break;
						}
                    }
                }
				if ($groupBy) $sql .= " GROUP BY req.id ";
            } else {
                $sql = "SELECT $fields FROM ".$this->table." req
                LEFT JOIN CONTACT c ON c.C_ID=req.client_c_id
                WHERE req.state_id > 0 ".$this->prepareSearch($search, $filters['search']);
            }
        }
        if ($sort){
			$sql .= " ORDER BY req.".($sort ? $sort : "");
			if ($limit) {
				$sql .= " LIMIT ".$limit;
			}
        }
        //echo $sql;die;
		return $this->prepare($sql)->query($filters)->fetchAll();
	}
	
	public function countAll($filters = array(), $search = false, $all_states = false)
	{
	    $result = $this->getAll(false, 0, $filters, $search, "COUNT(*)", $all_states);
		if (sizeof($result)==1){
			return $result[0]["COUNT(*)"];
		} else {
			return sizeof($result);
		}
	}	
	
	public function countByContact($contact_id)
	{
		$sql = "SELECT COUNT(*) FROM ".$this->table." WHERE client_c_id = i:contact_id";
		return $this->prepare($sql)->query(array('contact_id' => $contact_id))->fetchField();
	}
		
	public function add($data)
	{
		if (isset($data['attachments'])) {
			$data['attachments'] = serialize($data['attachments']);
		}
		$sql = "INSERT INTO ".$this->table." 
				SET ";
		$t = false;
		foreach ($data as $name => $value) {
			if (!isset($this->fields[$name])) {
				continue;
			}
			if ($t) {
				$sql .= ", ";
			} else {
				$t = true;
			}
			
            $sql .= " `".$name."` = ".$this->fields[$name].":".$name;
		}
		return $this->prepare($sql)->query($data)->lastInsertId();
	}
	
	public function save($id, $data)
	{
		if (isset($data['attachments']) && $data['attachments']) {
			$data['attachments'] = serialize($data['attachments']);
		} elseif (isset($data['attachments'])) {
			$data['attachments'] = '';
		}
		$sql = "UPDATE ".$this->table." 
				SET ";
		$t = false;
		foreach ($data as $name => $value) {
			if (!isset($this->fields[$name])) {
				continue;
			}
			if ($t) {
				$sql .= ", ";
			} else {
				$t = true;
			}
			
            $sql .= " `".$name."` = ".$this->fields[$name].":".$name;
		}
		if ($t) {
			$sql .= ' WHERE id = i:id';
			$data['id'] = $id;
			return $this->prepare($sql)->exec($data);
		}
	}
	
	public function set($id, $state_id, $assigned = 0)
	{		
		$sql = "UPDATE ".$this->table." 
				SET state_id = i:state_id";
		if ($assigned > 0) {
			$sql .= ", assigned_c_id = i:assigned";
		} elseif ($assigned == -3) {
			$sql .= ", assigned_c_id = 0";
		} 
		$sql .= " WHERE id = i:id";
		return $this->prepare($sql)->exec(array(
			'id' => $id, 
			'state_id' => $state_id,
			'assigned' => $assigned
		));
	}
	
	public function delete($id)
	{
		// Delete from log
		$sql = "DELETE FROM ".$this->table_log." 
				WHERE request_id = i:id";
		$this->prepare($sql)->exec(array('id' => $id));
		// Delete from request_class
		$sql = "DELETE FROM st_request_class 
				WHERE request_id = i:id";
		$this->prepare($sql)->exec(array('id' => $id));
		// Delete request
		$sql = "DELETE FROM ".$this->table." 
				WHERE id = i:id";
		return $this->prepare($sql)->exec(array('id' => $id));
	}
    
    public function deleteByStateId($state_id)
    {
    	$disk_usage_model = new DiskUsageModel();
    	// Remove attahcments of logs
    	$request_log_model = new STRequestLogModel();
    	$data = $request_log_model->getWithAttachmentsByState($state_id);
    	foreach ($data as $row) {
			$attach = unserialize($row['attachments']);
			foreach  ($attach as $file) {
				$disk_usage_model->add('$SYSTEM', 'ST', -$file['size']);
			}
			STRequest::removeAttachments($row['request_id'], $row['id']);
    	}
        // Delete from log
        $sql = "DELETE FROM log USING ".$this->table." r, ".$this->table_log." log
                WHERE r.state_id = i:state_id AND r.id = log.request_id";
        $this->prepare($sql)->exec(array('state_id' => $state_id));
        
        // Delete from request_class
        $sql = "DELETE FROM class USING ".$this->table." r, st_request_class class
                WHERE r.state_id = i:state_id AND r.id = class.request_id";
        $this->prepare($sql)->exec(array('state_id' => $state_id));
        
        //Remove attachments of requests
        $data = $this->getByState($state_id);
        foreach ($data as $row) {
        	if ($row['attachments']) {
        		$attach = unserialize($row['attachments']);
				foreach ($attach as $file) {
					$disk_usage_model->add('$SYSTEM', 'ST', -$file['size']);
				}
        	}
        	STRequest::removeAttachments($row['id']);
        }
        
        // Delete requests
        $sql = "DELETE FROM ".$this->table." 
                WHERE state_id = i:state_id";
        return $this->prepare($sql)->exec(array('state_id' => $state_id));
    }
    
    public function getByState($state_id)
    {
    	return $this->getByKey('state_id', $state_id, true, true);
    }
    
	public function setRead($id, $read = 0)
	{
		$sql = "UPDATE ".$this->table." 
				SET `read` = s:read 
				WHERE id = i:id";
		return $this->prepare($sql)->exec(array('id' => $id, 'read' => $read));
	}
}