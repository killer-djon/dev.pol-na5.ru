<?php

class STKnowledgeModel extends DBModel
{
	protected $table = 'st_kbase';
	protected $id = 'id';
	
	public function getBooks()
	{
		$sql = "SELECT * FROM ".$this->table;
		return $this->query($sql)->fetchAll();
	}
	
}