<?php

class STClassModel extends DbModel
{
	protected $table = 'st_class';
	protected $id = 'id';
	
	public function getAll()
	{
		$sql = "SELECT * FROM ".$this->table."
				ORDER BY class_type_id, sorting";
		return $this->query($sql)->fetchAll('id');
	}
    
    public function getByIds($ids)
    {
        $sql = "SELECT * FROM ".$this->table."
                WHERE `id` IN ($ids)
                ORDER BY sorting";
        return $this->query($sql)->fetchAll();
    }
    
    public function getByClassType($type_id)
    {
        $where = " class_type_id = i:type_id ";
        $sql = "SELECT * FROM ".$this->table."
                WHERE ".$where."
                ORDER BY sorting";
        return $this->prepare($sql)->query(array('type_id' => $type_id))->fetchAll();
    }
	
	public function add($type_id, $name, $sorting)
	{
		$sql = "INSERT INTO ".$this->table."
				SET class_type_id = i:type_id,
					name = s:name,
					sorting = i:sorting";
		return $this->prepare($sql)->query(array(
			'type_id' => $type_id,
			'name' => $name,
			'sorting' => $sorting
		))->lastInsertId();
	}

	public function get($id)
	{
		return $this->getById($id);
	}
	
    public function setSorting($id, $sorting)
    {
        $sql = "UPDATE ".$this->table." 
                SET sorting = i:sorting
                WHERE id = i:id";
        $this->prepare($sql)->exec(array('id' => $id, 'sorting' => $sorting));
    }
    
    /*public function setSorting($id, $sorting_old, $sorting_new)
    {
        $sql = "UPDATE ".$this->table." 
                SET sorting = i:sorting_new
                WHERE id = i:id";
        $this->prepare($sql)->exec(array('id' => $id, 'sorting_new' => $sorting_new));
        if ($sorting_new > $sorting_old){
            $sql = "UPDATE ".$this->table." 
                SET sorting = sorting - 1
                WHERE sorting > i:sorting_old AND sorting <= i:sorting_new
                    AND id != i:id";
        } else {
            $sql = "UPDATE ".$this->table." 
                SET sorting = sorting + 1
                WHERE sorting >= i:sorting_new AND sorting < i:sorting_old
                    AND id != i:id";
        }
        $this->prepare($sql)->exec(array('id' => $id, 'sorting_new' => $sorting_new, 'sorting_old' => $sorting_old));
    }*/
    
    public function setName($id, $name)
    {
        $sql = "UPDATE ".$this->table." 
                SET name = s:name
                WHERE id = i:id";
        return $this->prepare($sql)->exec(array('id' => $id, 'name' => $name));
    }
    

    public function replaceClassType($old_class_type_id, $new_class_type_id)
    {
        $sql = "UPDATE ".$this->table." 
                SET class_type_id = i:new_class_type_id
                WHERE class_type_id = i:old_class_type_id";
        return $this->prepare($sql)->query(array('new_class_type_id' => $new_class_type_id, 'old_class_type_id' => $old_class_type_id));
    }
    
	public function delete($id, $class_type_id = false) 
	{
        $row = array();
	    if ($class_type_id) {
            $sql = "DELETE st_request_class.* FROM st_request_class 
                    LEFT JOIN st_class ON st_request_class.class_id=st_class.id
                    WHERE `class_type_id` = ".$class_type_id;
            $this->exec($sql);
            $sql = "DELETE FROM ".$this->table." WHERE class_type_id = ".$class_type_id;
	    } else {
            $where = " = ".(int)$id; 
			/*$sql = "SELECT sorting FROM ".$this->table."
	                WHERE id = i:id";
			$row = $this->prepare($sql)->query(array('id' => $id))->fetchRow();
        
	        $sql = "UPDATE ".$this->table." 
	            SET sorting = sorting - 1
	            WHERE sorting > i:sorting";
	        $this->prepare($sql)->exec(array('sorting' => $row[0]));
	        */
	        $sql = "DELETE FROM st_request_class WHERE class_id ".$where;
	        $this->exec($sql);
	        
	        $sql = "DELETE FROM ".$this->table." WHERE id".$where;
	    }
        return $this->exec($sql);

	}
	
}