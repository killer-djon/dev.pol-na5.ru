<?php

class ContactNotesModel extends DbModel
{
	protected $table = "CONTACTNOTE";
	protected $id = 'CN_ID';
	
	public function add($contact_id, $note, $author)
	{
		$sql = "INSERT INTO ".$this->table." 
				SET CN_CID = i:contact_id, 
					CN_TEXT = s:note,
					CN_CREATECID = i:author,
					CN_CREATETIME = s:time";
		return $this->prepare($sql)->query(array(
			'contact_id' => $contact_id,
			'note' => $note,
			'author' => $author,
			'time' => date("YmdHis")
		))->lastInsertId();
	}
	
	public function getByContactId($contact_id, $limit = false)
	{
		$sql = "SELECT * FROM ".$this->table." 
				WHERE CN_CID = i:contact_id
				ORDER BY CN_CREATETIME DESC";
		if ($limit) {
			$sql .= " LIMIT " . $limit;
		}
		return $this->prepare($sql)->query(array('contact_id' => $contact_id))->fetchAll();
	}
	
	public function countByContactId($contact_id) 
	{
		$sql = "SELECT COUNT(*) N FROM ".$this->table."
				WHERE CN_CID = i:contact_id";
		return $this->prepare($sql)->query(array('contact_id' => $contact_id))->fetchField('N');	
	}
	
	public function save($id, $note)
	{
		$sql = "UPDATE ".$this->table." SET CN_TEXT = s:note WHERE CN_ID = i:id";
		return $this->prepare($sql)->exec(array('id' => $id, 'note' => $note));
	}
	
	public function delete($id) 
	{
		$sql = "DELETE FROM ".$this->table." WHERE CN_ID = i:id";
		return $this->prepare($sql)->exec(array('id' => $id));
	}
}

?>