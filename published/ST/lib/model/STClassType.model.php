<?php

class STClassTypeModel extends DbModel
{
	protected $table = 'st_class_type';
	protected $id = 'id';
	
	public function getAll()
	{
		$sql = "SELECT * FROM ".$this->table." ORDER BY sorting";
		return $this->query($sql)->fetchAll('id');
	}
    
    public function getByIds($ids)
    {
        $sql = "SELECT * FROM ".$this->table."
                WHERE `id` IN ('".implode("','", $this->escape($ids))."')
                ORDER BY sorting";
        return $this->query($sql)->fetchAll();
    }
    
    
    public function add($name, $sorting)
    {
        $sql = "INSERT INTO ".$this->table."
                SET name = s:name,
                    sorting = s:sorting";
        return $this->prepare($sql)->query(array(
            'name' => $name,
            'sorting' => $sorting
        ))->lastInsertId();
    }
    public function relinkClasses($id, $link) 
    {
        $sql = "SELECT `id` FROM st_class 
                WHERE `class_type_id` = i:id";
        
        $rows = $this->prepare($sql)->query(array('id' => $id))->fetchAll();
        
        foreach ($rows as $row){
        $sql = "UPDATE st_request_class 
                SET class_id = i:new_class_id
                WHERE class_id = i:old_class_id";
        $success = $this->prepare($sql)->query(array('new_class_id' => $link, 'old_class_id' => $row['id']));
        }
        
        return $success;
    }
    public function delete($id) 
    {
        $row = array();
        if (is_array($id)) {
            $where = " IN ('".implode("', '", $this->escape($id))."')";
        } elseif ($id) {
            $where = " = ".(int)$id;        
        } else {
            return true;
        }
        /*$sql = "SELECT sorting FROM ".$this->table."
                WHERE id = i:id";
        
        $row = $this->prepare($sql)->query(array('id' => $id))->fetchRow();
        
        $sql = "UPDATE ".$this->table." 
            SET sorting = sorting - 1
            WHERE sorting > i:sorting";
        $this->prepare($sql)->exec(array('sorting' => $row[0]));
        */
        $sql = "DELETE st_request_class.* FROM st_request_class 
                LEFT JOIN st_class ON st_request_class.class_id=st_class.id
                WHERE `class_type_id` = ".$id;
        $this->exec($sql);
                
        $sql = "DELETE FROM `st_class` WHERE `class_type_id` ".$where;
        $this->exec($sql);
        
        $sql = "DELETE FROM ".$this->table." WHERE id".$where;
        return $this->exec($sql);
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
    
    public function save($id, $name, $multiple)
    {
        $sql = "UPDATE ".$this->table." 
                SET name = s:name,
                    multiple = s:multiple
                WHERE id = i:id";
        return $this->prepare($sql)->exec(
            array('id' => $id, 'name' => $name, 'multiple' => $multiple)
        );
    }
}