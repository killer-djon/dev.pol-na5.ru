<?php

class ContactListModel extends DbModel
{
	const INSERT_LIMIT = 200;
	
	protected $table = "CLIST_CONTACT";
	

	/**
	 * Adds contacts to the list 
	 * 
	 * @param int $list_id
	 * @param int|array $contact_id
	 * @return bool
	 */
	public function add($list_id, $contact_ids)
	{
		if (!is_array($contact_ids)) {
			$contact_ids = array($contact_ids);
		}
		$values = array();
		$i = 0;
		foreach ($contact_ids as $cid) {
			$values[] = "(".(int)$list_id.", ".(int)$cid.")";
			if ($i++ == self::INSERT_LIMIT) {
				$this->exec("INSERT IGNORE INTO ".$this->table." (CL_ID, C_ID) VALUES ".implode(", ", $values));
				$values = array();
				$i = 0;	
			}		
		}
		if ($values) {
			$this->exec("INSERT IGNORE INTO ".$this->table." (CL_ID, C_ID) VALUES ".implode(", ", $values));
		}
		return $contact_ids ? true : false;
	}
	
	public function addToLists($contact_id, $list_ids) 
	{
		if (!is_array($list_ids)) {
			$list_ids = array($list_ids);
		}
		$values = array();
		$i = 0;
		foreach ($list_ids as $list_id) {
			$values[] = "(".(int)$list_id.", ".(int)$contact_id.")";
			if ($i++ == self::INSERT_LIMIT) {
				$this->exec("INSERT IGNORE INTO ".$this->table." (CL_ID, C_ID) VALUES ".implode(", ", $values));
				$values = array();
				$i = 0;	
			}		
		}
		if ($values) {
			$this->exec("INSERT IGNORE INTO ".$this->table." (CL_ID, C_ID) VALUES ".implode(", ", $values));
		}
		return $list_ids ? true : false; 
	}
	
	public function getContacts($list_id)
	{
		$sql = "SELECT C.*, U.U_ID 
				FROM ".$this->table." CL JOIN 
					 CONTACT C ON CL.C_ID = C.C_ID LEFT JOIN
					 WBS_USER U ON C.C_ID = U.C_ID
				WHERE CL.CL_ID = i:list_id";
		return $this->prepare($sql)->query(array('list_id' => $list_id))->fetchAll();
	}
	
	public function getContactIds($list_id)
	{
		$sql = "SELECT C_ID FROM ".$this->table." WHERE CL_ID = i:list_id";
		return $this->prepare($sql)->query(array('list_id' => $list_id))->fetchAll(false, true);
	}
	
	public function getLists($contact_id) 
	{
		$sql = "SELECT L.* 
				FROM ".$this->table." CL 
				JOIN CLIST L ON CL.CL_ID = L.CL_ID 
				WHERE CL.C_ID = i:contact_id";
		return $this->prepare($sql)->query(array('contact_id' => $contact_id))->fetchAll();	
	}
	
	public function getIds($contact_id)
	{
	    $sql = "SELECT CL_ID FROM ".$this->table." WHERE C_ID = i:contact_id";
	    $data = $this->prepare($sql)->query(array('contact_id' => $contact_id));
	    $result = array();
	    foreach ($data as $row) {
	        $result[] = $row['CL_ID'];
	    }
	    return $result;
	}
	
	public function delete($list_id, $contact_ids) 
	{
		if (!$contact_ids) {
			return true;
		}
		if (!is_array($contact_ids)) {
			$contact_ids = array($contact_ids);
		}
		
		$sql = "DELETE FROM ".$this->table." 
				WHERE CL_ID = i:list_id AND C_ID IN ('".implode("', '", $this->escape($contact_ids))."')";
		return $this->prepare($sql)->exec(array('list_id' => $list_id));	
	}
	
	public function deleteByContact($contact_id)
	{
		$sql = "DELETE FROM ".$this->table." WHERE C_ID = i:contact_id";
		return $this->prepare($sql)->exec(array('contact_id' => $contact_id));
	}
}

?>